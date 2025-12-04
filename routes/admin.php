<?php

use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\CategoryHierarchyController;
use App\Http\Controllers\Admin\FamilyController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\ProfileController;
use App\Http\Controllers\Admin\PostController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');

Route::controller(ProfileController::class)->group(function () {
    Route::get('/profile', 'index')->name('admin.profile.index');
    Route::put('/profile', 'update')->name('admin.profile.update');
    Route::put('/profile/password', 'updatePassword')->name('admin.profile.password');

    // Quitar foto de perfil
    Route::delete('/profile/remove-image', 'removeImage')->name('admin.profile.removeImage');

    // Exportaciones
    Route::post('/profile/export/excel', 'exportExcel')->name('admin.profile.export.excel');
    Route::post('/profile/export/pdf', 'exportPdf')->name('admin.profile.export.pdf');
    Route::post('/profile/export/csv', 'exportCsv')->name('admin.profile.export.csv');
    // Cerrar sesión de otros dispositivos
    Route::post('/profile/logout-session', 'logoutSession')->name('admin.profile.logout-session');
});

// FAMILIES
Route::controller(FamilyController::class)->group(function () {
    // Mostrar datos completos de familia por slug
    Route::get('/families/{slug}/show', 'show')->name('admin.families.show');
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

// CATEGORIES
Route::controller(CategoryController::class)->group(function () {
    // Mostrar datos completos de categoría por slug
    Route::get('/categories/{slug}/show', 'show')->name('admin.categories.show');
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

// CATEGORY HIERARCHY MANAGER
Route::controller(CategoryHierarchyController::class)->prefix('categories/hierarchy')->group(function () {
    Route::get('/', 'index')->name('admin.categories.hierarchy');
    Route::get('/tree-data', 'getTreeData')->name('admin.categories.hierarchy.tree-data');
    Route::post('/bulk-move', 'bulkMove')->name('admin.categories.hierarchy.bulk-move');
    Route::post('/preview-move', 'previewMove')->name('admin.categories.hierarchy.preview-move');
    Route::post('/bulk-delete', 'bulkDelete')->name('admin.categories.hierarchy.bulk-delete');
    Route::post('/bulk-duplicate', 'bulkDuplicate')->name('admin.categories.hierarchy.bulk-duplicate');
    Route::post('/drag-move', 'dragMove')->name('admin.categories.hierarchy.drag-move');
});

// USERS
Route::controller(UserController::class)->group(function () {
    // Mostrar datos completos de usuarios por slug
    Route::get('/users/{slug}/show', 'show')->name('admin.users.show');
    Route::get('/users', 'index')->name('admin.users.index');
    Route::get('/users/create', 'create')->name('admin.users.create');
    Route::post('/users', 'store')->name('admin.users.store');
    Route::get('/users/{user}/edit', 'edit')->name('admin.users.edit');
    Route::put('/users/{user}', 'update')->name('admin.users.update');
    Route::delete('/users/{user}', 'destroy')->name('admin.users.destroy');
    Route::delete('/users', 'destroyMultiple')->name('admin.users.destroy-multiple');
    Route::patch('/users/{user}/status', 'updateStatus')->name('admin.users.update-status');
    // Exports
    Route::post('/users/export/excel', 'exportExcel')->name('admin.users.export.excel');
    Route::post('/users/export/pdf', 'exportPdf')->name('admin.users.export.pdf');
    Route::post('/users/export/csv', 'exportCsv')->name('admin.users.export.csv');
});

// POSTS
Route::controller(PostController::class)->group(function () {
    Route::get('/posts/{slug}/show', 'show')->name('admin.posts.show');
    Route::get('/posts', 'index')->name('admin.posts.index');
    Route::get('/posts/create', 'create')->name('admin.posts.create');
    Route::post('/posts', 'store')->name('admin.posts.store');
    Route::get('/posts/{post}/edit', 'edit')->name('admin.posts.edit');
    Route::put('/posts/{post}', 'update')->name('admin.posts.update');
    Route::delete('/posts/{post}', 'destroy')->name('admin.posts.destroy');
    Route::delete('/posts', 'destroyMultiple')->name('admin.posts.destroy-multiple');
    Route::patch('/posts/{post}/status', 'updateStatus')->name('admin.posts.update-status');
    Route::post('/posts/upload-temp-image', 'uploadTemp')->name('admin.posts.upload-temp-image');
    // Exports
    Route::post('/posts/export/excel', 'exportExcel')->name('admin.posts.export.excel');
    Route::post('/posts/export/pdf', 'exportPdf')->name('admin.posts.export.pdf');
    Route::post('/posts/export/csv', 'exportCsv')->name('admin.posts.export.csv');
});

// ROLES
Route::controller(App\Http\Controllers\Admin\RoleController::class)->group(function () {
    Route::get('/roles', 'index')->name('admin.roles.index');
    Route::get('/roles/create', 'create')->name('admin.roles.create');
    Route::post('/roles', 'store')->name('admin.roles.store');
    Route::get('/roles/{role}/permissions', 'permissions')->name('admin.roles.permissions');
    Route::post('/roles/{role}/permissions', 'updatePermissions')->name('admin.roles.update-permissions');
    Route::get('/roles/{role}/edit', 'edit')->name('admin.roles.edit');
    Route::patch('/roles/{role}', 'update')->name('admin.roles.update');
    Route::delete('/roles/{role}', 'destroy')->name('admin.roles.destroy');

    // Exports
    Route::post('/roles/export/excel', 'exportExcel')->name('admin.roles.export.excel');
    Route::post('/roles/export/pdf', 'exportPdf')->name('admin.roles.export.pdf');
    Route::post('/roles/export/csv', 'exportCsv')->name('admin.roles.export.csv');
});

// PERMISSIONS
Route::controller(App\Http\Controllers\Admin\PermissionController::class)->group(function () {
    Route::get('/permissions', 'index')->name('admin.permissions.index');
    Route::get('/permissions/create', 'create')->name('admin.permissions.create');
    Route::post('/permissions', 'store')->name('admin.permissions.store');
    Route::get('/permissions/{permission}/edit', 'edit')->name('admin.permissions.edit');
    Route::patch('/permissions/{permission}', 'update')->name('admin.permissions.update');
    Route::delete('/permissions/{permission}', 'destroy')->name('admin.permissions.destroy');
});
