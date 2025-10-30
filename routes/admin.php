<?php
    use Illuminate\Support\Facades\Route;
     // antes era '/admin' pero ahora se configuro un prefijo en app.php
    Route::get('/', function () {
        return view('admin.dashboard'); // Aquí va la vista del dashboard del admin
    })->name('admin.dashboard');// El name() es para nombrar la ruta

    // antes era '/admin/users'
    Route::get('/users', function () {
        return 'lista de usuarios';
    })->name('admin.users');
?>