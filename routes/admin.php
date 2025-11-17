<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryHierarchyController;
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

Route::post('/families/export/csv', [FamilyController::class, 'exportCsv'])
    ->name('admin.families.export.csv');

// Rutas para categorias aquí
Route::get('/categories', [CategoryController::class, 'index'])->name('admin.categories.index');
Route::get('/categories/create', [CategoryController::class, 'create'])->name('admin.categories.create');
Route::post('/categories', [CategoryController::class, 'store'])->name('admin.categories.store');
Route::get('/categories/{category}/edit', [CategoryController::class, 'edit'])->name('admin.categories.edit');
Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('admin.categories.update');
Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('admin.categories.destroy');
Route::delete('/categories', [CategoryController::class, 'destroyMultiple'])->name('admin.categories.destroy-multiple');
Route::patch('/categories/{category}/status', [CategoryController::class, 'updateStatus'])
    ->name('admin.categories.update-status');
Route::post('/categories/export/excel', [CategoryController::class, 'exportExcel'])
    ->name('admin.categories.export.excel');
Route::post('/categories/export/pdf', [CategoryController::class, 'exportPdf'])
    ->name('admin.categories.export.pdf');
Route::post('/categories/export/csv', [CategoryController::class, 'exportCsv'])
    ->name('admin.categories.export.csv');

// Rutas para Administrador Jerárquico de Categorías
Route::get('/categories/hierarchy', [CategoryHierarchyController::class, 'index'])
    ->name('admin.categories.hierarchy');
Route::get('/categories/hierarchy/tree-data', [CategoryHierarchyController::class, 'getTreeData'])
    ->name('admin.categories.hierarchy.tree-data');
Route::post('/categories/hierarchy/bulk-move', [CategoryHierarchyController::class, 'bulkMove'])
    ->name('admin.categories.hierarchy.bulk-move');
Route::post('/categories/hierarchy/preview-move', [CategoryHierarchyController::class, 'previewMove'])
    ->name('admin.categories.hierarchy.preview-move');
Route::post('/categories/hierarchy/bulk-delete', [CategoryHierarchyController::class, 'bulkDelete'])
    ->name('admin.categories.hierarchy.bulk-delete');
Route::post('/categories/hierarchy/bulk-duplicate', [CategoryHierarchyController::class, 'bulkDuplicate'])
    ->name('admin.categories.hierarchy.bulk-duplicate');