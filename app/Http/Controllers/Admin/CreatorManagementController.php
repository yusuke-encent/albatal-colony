<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreCreatorRequest;
use App\Http\Requests\Admin\UpdateCreatorRequest;
use App\Models\User;
use App\Services\Marketplace\GeneratesProductCodes;
use App\Services\Marketplace\EnsuresProviderPriceOptions;
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
        return Inertia::render('Admin/Creators/Create', [
            'defaultPriceOptions' => app(EnsuresProviderPriceOptions::class)->defaultPrices(),
        ]);
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

        if ($request->filled('new_price_option')) {
            $creator->priceOptions()->firstOrCreate([
                'price' => $request->integer('new_price_option'),
            ]);
        }

        return to_route('admin.creators.index')->with('success', 'Creator account has been updated.');
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
        $generator = app(GeneratesProductCodes::class);

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
                    'product_code' => $creator->apc_merchant_id === null
                        ? null
                        : $generator->forProviderPrice($creator->apc_merchant_id, $priceOption->price),
                ])
                ->all(),
        ];
    }
}
