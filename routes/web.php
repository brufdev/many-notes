<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VaultController;
use App\Http\Middleware\EnsureEmailIsConfigured;
use App\Http\Middleware\EnsureRegistrationIsEnabled;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Livewire\Vault\Index as VaultIndex;
use App\Livewire\Vault\Show as VaultShow;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('vaults')->group(function (): void {
        Route::get('/', VaultIndex::class)->name('vaults.index');
        Route::post('/', [VaultController::class, 'store'])->name('vaults.store');
        Route::get('/{vaultId}', VaultShow::class)->name('vaults.show');
    });

    Route::get('files/{vault}', [FileController::class, 'show'])->name('files.show');

    Route::post('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware([EnsureUserIsAdmin::class])->group(function (): void {
        Route::post('settings', [SettingController::class, 'update'])->name('settings.update');
    });

    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');
});

Route::middleware(['guest', 'throttle'])->group(function (): void {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->name('login.store');

    Route::middleware([EnsureRegistrationIsEnabled::class])->group(function (): void {
        Route::get('register', [RegisterController::class, 'create'])->name('register');
        Route::post('register', [RegisterController::class, 'store'])->name('register.store');
    });

    Route::middleware([EnsureEmailIsConfigured::class])->group(function (): void {
        Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('forgot.password');
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('forgot.password.store');

        Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
        Route::post('reset-password/{token}', [ResetPasswordController::class, 'store'])->name('password.reset.store');
    });

    Route::prefix('oauth')->group(function (): void {
        Route::get('{provider}', [OAuthController::class, 'create'])->name('oauth');
        Route::get('{provider}/callback', [OAuthController::class, 'store'])->name('oauth.store');
    });
});
