import os
import hashlib
import numpy as np
from flask import Flask, request, jsonify, Response
import io

app = Flask(__name__)

def generate_product_code(merchant_id, price, variant_idx=1):
    seed_txt = f"{merchant_id}:{int(price)}:{variant_idx}"
    hash_hex = hashlib.md5(seed_txt.encode()).hexdigest()
    part1_val = int(hash_hex[:8], 16)
    digit1 = "1" if (part1_val % 2) == 0 else "2"
    part2_val = int(hash_hex[8:16], 16)
    suffix_val = part2_val % 10000
    return f"TT{digit1}000{suffix_val:04d}"

def calculate_portfolio(total_n, total_r, merchant_id):
    prices = np.array([480, 930, 1490, 1798, 2021, 3000, 3300, 4593, 9980, 12800], dtype=np.int64)
    num_p = len(prices)
    
    base_min = (total_n // 40) + 1
    lower_bound = int(np.ceil(base_min * 0.85))
    max_per_item = total_n // 4
    max_under_min = num_p // 2 + 1
    extra_min_p, extra_max_p = 1980, 35000
    extra_max_count_per_group = total_n // 3

    rng = np.random.default_rng(42)
    iterations = 50000  # クラウド実行用に少し軽量化
    
    target_existing_sums = total_n - rng.poisson(lam=total_n*0.1, size=iterations)
    target_existing_sums = np.clip(target_existing_sums, 0, total_n)
    weights = rng.random((iterations, num_p))
    counts = (weights / weights.sum(axis=1)[:, None] * target_existing_sums[:, None]).astype(np.int64)
    
    diffs = target_existing_sums - counts.sum(axis=1)
    for i in range(iterations):
        if diffs[i] > 0:
            indices = rng.choice(num_p, size=diffs[i], replace=True)
            for idx in indices:
                counts[i, idx] += 1

    valid_mask = np.all(counts <= max_per_item, axis=1)
    valid_mask &= (np.sum(counts < lower_bound, axis=1) <= max_under_min)
    
    existing_revenues = np.sum(counts * prices, axis=1)
    rem_n, rem_r = total_n - counts.sum(axis=1), total_r - existing_revenues
    
    can_fill_extra = (rem_n == 0) & (rem_r == 0)
    has_rem = rem_n > 0
    with np.errstate(divide='ignore', invalid='ignore'):
        avg_extra_p = np.where(has_rem, rem_r / rem_n, 0)
    can_fill_extra |= (has_rem & (avg_extra_p >= extra_min_p) & (avg_extra_p <= extra_max_p))
    valid_mask &= can_fill_extra

    if not np.any(valid_mask):
        return None

    valid_indices = np.where(valid_mask)[0]
    v_counts, v_rem_n, v_rem_r = counts[valid_indices], rem_n[valid_indices], rem_r[valid_indices]
    v_extra_groups = np.ceil(v_rem_n / (total_n/3 if total_n > 2 else 1)).astype(np.int64)
    v_zeros, v_var = np.sum(v_counts == 0, axis=1), np.var(v_counts, axis=1)
    
    score = (v_extra_groups * 1e12 + v_rem_n * 1e9 + v_zeros * 1e7 - v_var * 10)
    best_idx = np.argmin(score)
    
    f_counts, f_rem_n, f_rem_r = v_counts[best_idx], v_rem_n[best_idx], v_rem_r[best_idx]
    
    rows = []
    for p, c in zip(prices, f_counts):
        if c > 0:
            rows.append({"code": generate_product_code(merchant_id, p), "price": int(p), "count": int(c)})
    
    if f_rem_n > 0:
        p_base = f_rem_r // f_rem_n
        num_higher, num_lower = f_rem_r % f_rem_n, f_rem_n - (f_rem_r % f_rem_n)
        def add_extra(n, p):
            code = generate_product_code(merchant_id, p)
            while n > 0:
                take = min(n, extra_max_count_per_group)
                rows.append({"code": code, "price": int(p), "count": int(take)})
                n -= take
        if num_lower > 0: add_extra(num_lower, p_base)
        if num_higher > 0: add_extra(num_higher, p_base + 1)

    rows.sort(key=lambda x: x["price"])
    return rows

@app.route('/generate', methods=['GET', 'POST'])
def generate():
    # パラメータ取得 (GETでもPOST JSONでも対応)
    data = request.get_json(silent=True) or request.args
    try:
        total_n = int(data.get('n'))
        total_r = int(data.get('r'))
        merchant_id = data.get('m')
    except (TypeError, ValueError):
        return "Error: Please provide 'n' (count), 'r' (revenue), and 'm' (merchant_id).", 400

    results = calculate_portfolio(total_n, total_r, merchant_id)
    if results is None:
        return "No solution found.", 404

    # TSV形式のレスポンス作成
    output = io.StringIO()
    output.write("品番\t単価\t件数\n")
    for r in results:
        output.write(f"{r['code']}\t{r['price']}\t{r['count']}\n")
    
    return Response(output.getvalue(), mimetype='text/tab-separated-values')

if __name__ == "__main__":
    app.run(host='0.0.0.0', port=int(os.environ.get('PORT', 8080)))
