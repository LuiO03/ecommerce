<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryHierarchyController;
use App\Http\Controllers\Admin\FamilyController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

// ============================================================================
// FAMILIES
// ============================================================================
Route::controller(FamilyController::class)->group(function () {
    Route::get('/families', 'index')->name('admin.families.index');
    Route::get('/families/create', 'create')->name('admin.families.create');
    Route::post('/families', 'store')->name('admin.families.store');
    Route::get('/families/{family}/edit', 'edit')->name('admin.families.edit');
    Route::put('/families/{family}', 'update')->name('admin.families.update');
    Route::delete('/families/{family}', 'destroy')->name('admin.families.destroy');
    Route::delete('/families', 'destroyMultiple')->name('admin.families.destroy-multiple');
    Route::patch('/families/{family}/status', 'updateStatus')->name('admin.families.update-status');
    
    // Exports
    Route::post('/families/export/excel', 'exportExcel')->name('admin.families.export.excel');
    Route::post('/families/export/pdf', 'exportPdf')->name('admin.families.export.pdf');
    Route::post('/families/export/csv', 'exportCsv')->name('admin.families.export.csv');
});

// ============================================================================
// CATEGORIES
// ============================================================================
Route::controller(CategoryController::class)->group(function () {
    Route::get('/categories', 'index')->name('admin.categories.index');
    Route::get('/categories/create', 'create')->name('admin.categories.create');
    Route::post('/categories', 'store')->name('admin.categories.store');
    Route::get('/categories/{category}/edit', 'edit')->name('admin.categories.edit');
    Route::put('/categories/{category}', 'update')->name('admin.categories.update');
    Route::delete('/categories/{category}', 'destroy')->name('admin.categories.destroy');
    Route::delete('/categories', 'destroyMultiple')->name('admin.categories.destroy-multiple');
    Route::patch('/categories/{category}/status', 'updateStatus')->name('admin.categories.update-status');
    
    // Exports
    Route::post('/categories/export/excel', 'exportExcel')->name('admin.categories.export.excel');
    Route::post('/categories/export/pdf', 'exportPdf')->name('admin.categories.export.pdf');
    Route::post('/categories/export/csv', 'exportCsv')->name('admin.categories.export.csv');
});

// ============================================================================
// CATEGORY HIERARCHY MANAGER
// ============================================================================
Route::controller(CategoryHierarchyController::class)->prefix('categories/hierarchy')->group(function () {
    Route::get('/', 'index')->name('admin.categories.hierarchy');
    Route::get('/tree-data', 'getTreeData')->name('admin.categories.hierarchy.tree-data');
    Route::post('/bulk-move', 'bulkMove')->name('admin.categories.hierarchy.bulk-move');
    Route::post('/preview-move', 'previewMove')->name('admin.categories.hierarchy.preview-move');
    Route::post('/bulk-delete', 'bulkDelete')->name('admin.categories.hierarchy.bulk-delete');
    Route::post('/bulk-duplicate', 'bulkDuplicate')->name('admin.categories.hierarchy.bulk-duplicate');
    Route::post('/drag-move', 'dragMove')->name('admin.categories.hierarchy.drag-move');
});