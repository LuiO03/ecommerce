<?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Http\Request;
    use App\Models\User;
    use App\Http\Controllers\Site\WellcomeController;
    use App\Http\Controllers\Site\FamilyController;
    use App\Http\Controllers\Site\SearchController;
    use App\Http\Controllers\Site\CategoryController;
    use App\Http\Controllers\Site\ProductController;
    use App\Http\Controllers\Site\WishlistController;
    use App\Http\Controllers\Site\CartController;
    use App\Http\Controllers\Site\RegisteredUserController;
    use App\Http\Controllers\Site\ShippingController;
    use App\Http\Controllers\Auth\GoogleController;
    use App\Mail\TestEmail;

    //para probar el email
    Route::get('/send-test-email', function () {
        Mail::to('lui.fenixand.1997@gmail.com')
            ->send(new TestEmail());
        return "Correo enviado";
    })->name('send-test-email');

    Route::get('/preview-email', function () {
        return new TestEmail();
    });

    // Verificación de correo electrónico (enlace desde el email de registro)
    Route::get('/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = User::findOrFail($id);

        if (! hash_equals(sha1($user->email), (string) $hash)) {
            abort(403, 'Enlace de verificación inválido.');
        }

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        return redirect()->route('login')->with('status', 'Tu correo ha sido verificado correctamente. Ahora puedes iniciar sesión.');
    })->middleware('signed')->name('verification.verify');

    Route::get('/google-auth/redirect', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.redirect');

    // Callback que Google llama tras el login (debe coincidir con GOOGLE_REDIRECT_URI)
    Route::get('/google-auth/callback', [GoogleController::class, 'handleGoogleCallback'])
    ->name('google.callback');

    // Endpoint para Google One Tap (recibe el ID token desde JS)
    Route::post('/google-auth/one-tap', [GoogleController::class, 'handleOneTap'])
    ->name('google.one-tap');

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

    // Rutas para el carrito de compras
    Route::get('/carts', [CartController::class, 'show'])->name('carts.show');
    Route::delete('/carts', [CartController::class, 'destroy'])->name('carts.destroy');
    Route::patch('/carts/items/{cartItem}', [CartController::class, 'updateItem'])->name('carts.items.update');
    Route::delete('/carts/items/{cartItem}', [CartController::class, 'destroyItem'])->name('carts.items.destroy');

    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');

    // Login administrativo (único login del sistema)
    Route::get('/login', function () {
        return view('auth.admin-login');
    })->name('login')->middleware('auth.guest');

    // Pantalla de confirmación tras enviar enlace de reset de contraseña
    Route::get('/forgot-password/sent', function () {
        return view('auth.admin-forgot-password-sent');
    })->name('password.email.sent')->middleware('auth.guest');

    // Registro de usuarios (solo invitados)
    Route::get('/register', [RegisteredUserController::class, 'create'])
        ->name('register')
        ->middleware('auth.guest');

    Route::post('/register', [RegisteredUserController::class, 'store'])
        ->middleware('auth.guest');

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

