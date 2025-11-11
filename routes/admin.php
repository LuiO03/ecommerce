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

Route::get('/families/create', [FamilyController::class, 'create'])->name('admin.families.create');

Route::post('/families', [FamilyController::class, 'store'])->name('admin.families.store');

Route::get('/families/{family}/edit', [FamilyController::class, 'edit'])->name('admin.families.edit');

Route::put('/families/{family}', [FamilyController::class, 'update'])->name('admin.families.update');

Route::delete('/families/{family}', [FamilyController::class, 'destroy'])->name('admin.families.destroy');

Route::delete('/families', [FamilyController::class, 'destroyMultiple'])->name('admin.families.destroy-multiple');

Route::patch('/families/{family}/status', [FamilyController::class, 'updateStatus'])
    ->name('admin.families.update-status');

Route::post('/families/export/excel', [FamilyController::class, 'exportExcel'])
    ->name('admin.families.export.excel');

Route::post('/families/export/pdf', [FamilyController::class, 'exportPdf'])
    ->name('admin.families.export.pdf');