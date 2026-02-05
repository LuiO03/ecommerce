<?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Site\WellcomeController;
    use App\Http\Controllers\Site\FamilyController;

    Route::get('/', [WellcomeController::class, 'index'])->name('welcome.index');

    Route::get('/families/{family}', [FamilyController::class, 'show'])->name('families.show');

    // Login administrativo (único login del sistema)
    Route::get('/login', function () {
        return view('auth.admin-login');
    })->name('login')->middleware('auth.guest');

    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    });
    /*
    Route::middleware([
        'auth:sanctum',
        config('jetstream.auth_session'),
        'verified',
    ])->group(function () {
        Route::get('/dashboard', function () {
            return view('dashboard');
        })->name('dashboard');
    });
     */

