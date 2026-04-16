<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCreatorRequest;
use App\Http\Requests\Admin\StoreProviderPriceOptionRequest;
use App\Http\Requests\Admin\UpdateCreatorRequest;
use App\Http\Requests\Admin\UpdateProviderPriceOptionRequest;
use App\Models\ProviderPriceOption;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class CreatorManagementController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Admin/Creators/Index', [
            'creators' => User::query()
                ->where('role', UserRole::Provider)
                ->with([
                    'priceOptions' => fn ($query) => $query->orderBy('price'),
                ])
                ->withCount(['providedContents', 'stockedContents'])
                ->orderBy('name')
                ->paginate(12)
                ->through(fn (User $creator): array => $this->creatorPayload($creator)),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/Creators/Create');
    }

    public function store(StoreCreatorRequest $request): RedirectResponse
    {
        $generatedPassword = Str::password(24);

        User::create([
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => UserRole::Provider,
            'email_verified_at' => now(),
            'password' => $generatedPassword,
        ]);

        return to_route('admin.creators.index')
            ->with('success', 'Creator account has been created.')
            ->with('generated_password', $generatedPassword);
    }

    public function edit(User $creator): Response
    {
        abort_unless($creator->isProvider(), 404);

        $creator->loadCount(['providedContents', 'stockedContents'])
            ->load([
                'priceOptions' => fn ($query) => $query->orderBy('price'),
            ]);

        return Inertia::render('Admin/Creators/Edit', [
            'creator' => $this->creatorPayload($creator),
        ]);
    }

    public function update(UpdateCreatorRequest $request, User $creator): RedirectResponse
    {
        abort_unless($creator->isProvider(), 404);

        $attributes = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'role' => UserRole::Provider,
            'apc_merchant_id' => $request->integer('apc_merchant_id'),
        ];

        if ($request->filled('password')) {
            $attributes['password'] = $request->string('password')->toString();
        }

        $creator->update($attributes);

        return to_route('admin.creators.index')->with('success', 'Creator account has been updated.');
    }

    public function storePriceOption(StoreProviderPriceOptionRequest $request, User $creator): RedirectResponse
    {
        abort_unless($creator->isProvider(), 404);

        $creator->priceOptions()->create([
            'price' => $request->integer('price'),
            'product_code' => $request->string('product_code')->toString(),
        ]);

        return back()->with('success', 'Creator price option has been added.');
    }

    public function updatePriceOption(
        UpdateProviderPriceOptionRequest $request,
        User $creator,
        ProviderPriceOption $priceOption,
    ): RedirectResponse {
        abort_unless($creator->isProvider(), 404);
        abort_unless($priceOption->provider_id === $creator->id, 404);

        $priceOption->update([
            'price' => $request->integer('price'),
            'product_code' => $request->string('product_code')->toString(),
        ]);

        return back()->with('success', 'Creator price option has been updated.');
    }

    public function destroyPriceOption(User $creator, ProviderPriceOption $priceOption): RedirectResponse
    {
        abort_unless($creator->isProvider(), 404);
        abort_unless($priceOption->provider_id === $creator->id, 404);

        if ($priceOption->contents()->exists() || $priceOption->stockedContents()->exists()) {
            return back()->with('error', 'Reassign or remove linked content before deleting this price option.');
        }

        $priceOption->delete();

        return back()->with('success', 'Creator price option has been deleted.');
    }

    public function destroy(User $creator): RedirectResponse
    {
        abort_unless($creator->isProvider(), 404);

        if (request()->user()?->is($creator)) {
            return to_route('admin.creators.index')->with('error', 'You cannot delete your own creator account.');
        }

        if ($creator->providedContents()->exists() || $creator->stockedContents()->exists()) {
            return to_route('admin.creators.index')->with('error', 'Reassign or remove this creator\'s contents before deleting the account.');
        }

        $creator->delete();

        return to_route('admin.creators.index')->with('success', 'Creator account has been deleted.');
    }

    /**
     * @return array<string, mixed>
     */
    private function creatorPayload(User $creator): array
    {
        return [
            'id' => $creator->id,
            'name' => $creator->name,
            'email' => $creator->email,
            'apc_merchant_id' => $creator->apc_merchant_id,
            'provided_contents_count' => $creator->provided_contents_count ?? 0,
            'stocked_contents_count' => $creator->stocked_contents_count ?? 0,
            'created_at' => $creator->created_at?->toDateTimeString(),
            'price_options' => $creator->priceOptions
                ->sortBy('price')
                ->values()
                ->map(fn ($priceOption): array => [
                    'id' => $priceOption->id,
                    'price' => $priceOption->price,
                    'formatted_price' => $priceOption->formatted_price,
                    'product_code' => $priceOption->product_code,
                ])
                ->all(),
        ];
    }
}
