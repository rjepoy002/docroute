<?php

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DepartmentController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\GoogleOAuthController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileCompletionController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', App\Http\Controllers\DashboardController::class)
    ->middleware(['auth', 'verified', 'active', 'profile.complete'])->name('dashboard');

Route::middleware(['auth', 'verified', 'active', 'profile.complete'])->prefix('documents')->name('documents.')->group(function () {
    Route::get('/', [DocumentController::class, 'index'])->name('index');
    foreach (['incoming', 'outgoing', 'pending', 'unseen'] as $folder) {
        Route::get($folder, [DocumentController::class, 'index'])->defaults('folder', $folder)->name($folder);
    }
    Route::get('create', [DocumentController::class, 'create'])->name('create');
    Route::post('/', [DocumentController::class, 'store'])->name('store');
    Route::get('{document}', [DocumentController::class, 'show'])->name('show');
    foreach (['receive', 'decline', 'forward', 'close'] as $action) {
        Route::put('{document}/'.$action, [DocumentController::class, $action])->name($action);
    }
});

Route::middleware('auth')->group(function () {
    Route::get('/profile/complete', [ProfileCompletionController::class, 'show'])->name('profile.complete');
    Route::post('/profile/complete', [ProfileCompletionController::class, 'store'])->name('profile.complete.store');
    Route::get('/auth/google/link', [GoogleOAuthController::class, 'link'])->middleware('password.confirm')->name('google.link');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'active', 'admin', 'profile.complete'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::resource('departments', DepartmentController::class)->except(['show', 'destroy']);
        Route::resource('users', UserController::class)->except(['show', 'destroy']);
        Route::put('users/{user}/password', [UserController::class, 'password'])
            ->middleware('throttle:6,1')->name('users.password');
        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('dashboard');
    });

require __DIR__.'/auth.php';
