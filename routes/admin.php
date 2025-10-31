<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\FamilyController;
use Illuminate\Support\Facades\Route;
/* antes era '/admin' pero ahora se configuro un prefijo en app.php
Route::get('/', function () {
    return view('admin.dashboard'); // Aquí va la vista del dashboard del admin
})->name('admin.dashboard'); // El name() es para nombrar la ruta
*/
// antes era '/admin/users'
Route::get('/users', function () {
    return 'lista de usuarios';
})->name('admin.users');

Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
Route::get('/families', [FamilyController::class, 'index'])->name('admin.families.index');

