<?php

declare(strict_types=1);

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\OAuthController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\VaultCollaborationAcceptController;
use App\Http\Controllers\VaultCollaborationController;
use App\Http\Controllers\VaultController;
use App\Http\Controllers\VaultExportController;
use App\Http\Controllers\VaultImportController;
use App\Http\Controllers\VaultNodeChildrenController;
use App\Http\Controllers\VaultNodeController;
use App\Http\Controllers\VaultNodeImportController;
use App\Http\Controllers\VaultNodeMoveController;
use App\Http\Controllers\VaultTemplatesNodeController;
use App\Http\Middleware\EnsureEmailIsConfigured;
use App\Http\Middleware\EnsureRegistrationIsEnabled;
use App\Http\Middleware\EnsureUserIsAdmin;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function (): void {
    Route::get('', [DashboardController::class, 'index'])->name('dashboard.index');

    Route::prefix('vaults')->name('vaults.')->group(function (): void {
        Route::get('', [VaultController::class, 'index'])->name('index');
        Route::post('', [VaultController::class, 'store'])->name('store');

        Route::prefix('{vault}')->group(function (): void {
            Route::get('', [VaultController::class, 'show'])->name('show');
            Route::patch('', [VaultController::class, 'update'])->name('update');
            Route::delete('', [VaultController::class, 'destroy'])->name('destroy');
            Route::get('export', VaultExportController::class)->name('export');
            Route::patch('templates-node', VaultTemplatesNodeController::class)->name('templates-node');
        });

        Route::post('import', VaultImportController::class)->name('import');
    });

    Route::prefix('vaults/{vault}')->name('vaults.nodes.')->group(function (): void {
        Route::post('nodes', [VaultNodeController::class, 'store'])->name('store');

        Route::prefix('nodes/{node}')->group(function (): void {
            Route::patch('', [VaultNodeController::class, 'update'])->name('update');
            Route::delete('', [VaultNodeController::class, 'destroy'])->name('destroy');
            Route::get('children', VaultNodeChildrenController::class)->name('children');
            Route::patch('move', VaultNodeMoveController::class)->name('move');
        })->scopeBindings();

        Route::post('import', VaultNodeImportController::class)->name('import');
    });

    Route::prefix('vaults/{vault}/collaborations')->name('vaults.collaborations.')->group(function (): void {
        Route::post('', [VaultCollaborationController::class, 'store'])->name('store');
        Route::post('accept', VaultCollaborationAcceptController::class)->name('accept');
    });

    Route::get('files/{vault}', [FileController::class, 'show'])->name('files.show');

    Route::delete('notifications/{notification}', [NotificationController::class, 'destroy'])
        ->name('notifications.destroy');

    Route::patch('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('password', [PasswordController::class, 'update'])->name('password.update');

    Route::middleware([EnsureUserIsAdmin::class])->group(function (): void {
        Route::patch('settings', [SettingController::class, 'update'])->name('settings.update');
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
