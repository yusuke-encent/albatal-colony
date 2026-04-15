<?php

use App\Http\Controllers\Admin\ManagedContentController;
use App\Http\Controllers\Admin\SaleReportController;
use App\Http\Controllers\Admin\UserManagementController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\MockGatewayController;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\Provider\SaleController;
use App\Models\User;
use App\Support\UserRole;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');
Route::get('/contents/{content}', [ContentController::class, 'show'])->name('contents.show');

Route::get('/login/{role}', function (string $role) {
    abort_unless(app()->environment('local'), 403);

    if (is_numeric($role)) {
        $user = User::query()->findOrFail((int) $role);
    } else {
        $user = match ($role) {
            UserRole::Admin => User::query()->where('role', UserRole::Admin)->firstOrFail(),
            UserRole::Provider => User::query()->where('role', UserRole::Provider)->latest('id')->firstOrFail(),
            UserRole::Customer => User::query()->where('role', UserRole::Customer)->latest('id')->firstOrFail(),
            default => abort(404),
        };
    }

    if ($user->email_verified_at === null) {
        $user->forceFill([
            'email_verified_at' => now(),
        ])->save();
    }

    Auth::login($user);

    return to_route('dashboard');
})->name('dev-login')->where('role', 'admin|provider|customer|\d+');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', DashboardController::class)->name('dashboard');
    Route::get('checkout/{content}', [CheckoutController::class, 'show'])->name('checkout.show');
    Route::post('checkout/{content}/start', [CheckoutController::class, 'start'])->name('checkout.start');
    Route::get('library', LibraryController::class)->name('library.index');
    Route::get('downloads/{purchase}', DownloadController::class)->name('purchases.download');

    Route::get('mock-gateway/sessions/{paymentSession}', [MockGatewayController::class, 'show'])->name('mock-gateway.show');
    Route::post('mock-gateway/sessions/{paymentSession}/complete', [MockGatewayController::class, 'complete'])->name('mock-gateway.complete');

    Route::prefix('admin')
        ->name('admin.')
        ->middleware('can:access-admin-panel')
        ->group(function (): void {
            Route::get('contents', [ManagedContentController::class, 'index'])->name('contents.index');
            Route::get('contents/create', [ManagedContentController::class, 'create'])->name('contents.create');
            Route::post('contents', [ManagedContentController::class, 'store'])->name('contents.store');
            Route::get('contents/{content}/edit', [ManagedContentController::class, 'edit'])->name('contents.edit');
            Route::put('contents/{content}', [ManagedContentController::class, 'update'])->name('contents.update');
            Route::delete('contents/{content}', [ManagedContentController::class, 'destroy'])->name('contents.destroy');

            Route::get('sales', SaleReportController::class)->name('sales.index');
            Route::get('users', [UserManagementController::class, 'index'])->name('users.index');
            Route::post('users', [UserManagementController::class, 'store'])->name('users.store');
            Route::patch('users/{user}/role', [UserManagementController::class, 'updateRole'])->name('users.update-role');
        });

    Route::prefix('provider')
        ->name('provider.')
        ->middleware('can:access-provider-panel')
        ->group(function (): void {
            Route::get('sales', SaleController::class)->name('sales.index');
        });
});

Route::post('payments/webhooks/mock', PaymentWebhookController::class)->name('payments.webhooks.mock');

require __DIR__.'/settings.php';
