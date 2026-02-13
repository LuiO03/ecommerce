<?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\Site\WellcomeController;
    use App\Http\Controllers\Site\FamilyController;
    use App\Http\Controllers\Site\SearchController;
    use App\Http\Controllers\Site\CategoryController;
    use App\Http\Controllers\Site\ProductController;
    use App\Http\Controllers\Site\WishlistController;

    Route::get('/', [WellcomeController::class, 'index'])->name('welcome.index');

    Route::get('/families/{family}', [FamilyController::class, 'show'])->name('families.show');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Rutas para la lista de deseos (wishlist)
    Route::get('/wishlists', [WishlistController::class, 'show'])->name('wishlists.show');
    Route::post('/wishlists', [WishlistController::class, 'store'])->name('wishlists.store');
    Route::delete('/wishlists/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlists.destroy');

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

