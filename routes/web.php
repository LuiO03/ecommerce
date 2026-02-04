<?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Site\WellcomeController;

    Route::get('/', [WellcomeController::class, 'index'])->name('welcome.index');

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

