<?php

    use Illuminate\Support\Facades\Route;
    use Illuminate\Support\Facades\Mail;
    use Illuminate\Support\Facades\Session;
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
    use App\Http\Controllers\Site\CheckoutController;
    use App\Http\Controllers\Site\LegalDocumentationController;
    use App\Http\Controllers\Site\ProfileController as SiteProfileController;
    use App\Http\Controllers\Site\BlogController;

    use App\Http\Controllers\Auth\GoogleController;

    use App\Mail\TestEmail;
    use App\Mail\UserRegistered;

    // Checkout
    Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
    Route::post('/checkout/paid', [CheckoutController::class, 'paid'])->name('checkout.paid');
    Route::post('/checkout/session-token', [CheckoutController::class, 'refreshSessionToken'])->name('checkout.session-token');
    // Ruta para la vista de compra exitosa y detalles del pedido
    Route::get('/checkout/success', [CheckoutController::class, 'success'])->name('checkout.success');
    Route::get('/checkout/failure', [CheckoutController::class, 'failure'])->name('checkout.failure');

    // Rutas para pruebas de email y verificación
    //para probar el email
    Route::get('/send-test-email', function () {
        Mail::to('lui.fenixand.1997@gmail.com')
            ->send(new TestEmail());
        return "Correo enviado";
    })->name('send-test-email');

    Route::get('/preview-email', function () {
        return new TestEmail();
    });

    // Reenvío de correo de verificación (flujo público)
    Route::post('/account/email/resend', function (Request $request) {
        $request->validate([
            'email' => ['required', 'email'],
        ]);

        // Cooldown simple por email usando sesión (5 minutos)
        $email = mb_strtolower(trim($request->input('email')));
        $sessionKey = 'verification_resend_' . sha1($email);
        $cooldownSeconds = 300; // 5 minutos

        $lastSentAt = Session::get($sessionKey);
        if ($lastSentAt && now()->diffInSeconds($lastSentAt) < $cooldownSeconds) {
            $remaining = $cooldownSeconds - now()->diffInSeconds($lastSentAt);

            return back()->with('toast', [
                'type' => 'warning',
                'title' => 'Espera antes de volver a intentar',
                'message' => 'Ya hemos enviado recientemente un correo de verificación. Por favor, inténtalo de nuevo en unos minutos.',
            ]);
        }

        $user = User::whereRaw('LOWER(email) = ?', [$email])->first();

        if (! $user || $user->email_verified_at) {
            // Respuesta genérica para no filtrar existencia de cuentas
            return back()->with('toast', [
                'type' => 'info',
                'title' => 'Si tu cuenta existe...',
                'message' => 'Si tu cuenta existe y aún no ha sido verificada, te enviaremos un nuevo correo en unos momentos.',
            ]);
        }

        Mail::to($user->email)->send(new UserRegistered($user));

        Session::put($sessionKey, now());

        return back()->with('toast', [
            'type' => 'success',
            'title' => 'Correo reenviado',
            'message' => 'Si el correo es correcto y tu cuenta no ha sido verificada, deberías recibir un nuevo enlace de verificación en breve.',
        ]);
    })->name('site.verification.resend');

    // Verificación de correo electrónico (enlace desde el email de registro - flujo público)
    Route::get('/account/email/verify/{id}/{hash}', function (Request $request, $id, $hash) {
        $user = User::findOrFail($id);

        // Si el hash del correo no coincide, mostramos vista de fallo genérica
        if (! hash_equals(sha1($user->email), (string) $hash)) {
            return response()->view('auth.admin-confirm-email-failure', [], 403);
        }

        if (! $user->email_verified_at) {
            $user->forceFill([
                'email_verified_at' => now(),
            ])->save();
        }

        // Vista de confirmación de correo exitosa
        return view('auth.admin-confirm-email-success', [
            'user' => $user,
        ]);
    })->middleware('signed')->name('site.verification.verify');
    //GOOGLE AUTH
    // 1) Rutas para autenticación con Google con Laravel Socialite
    Route::get('/google-auth/redirect', [GoogleController::class, 'redirectToGoogle'])
    ->name('google.redirect');
    // Callback que Google llama tras el login (debe coincidir con GOOGLE_REDIRECT_URI)
    Route::get('/google-auth/callback', [GoogleController::class, 'handleGoogleCallback'])
    ->name('google.callback');

    // 2) Endpoint para Google One Tap (recibe el ID token desde JS)
    Route::post('/google-auth/one-tap', [GoogleController::class, 'handleOneTap'])
    ->name('google.one-tap');

    // Rutas públicas del sitio
    Route::get('/', [WellcomeController::class, 'index'])->name('site.home');

    // Blog público
    Route::get('/blog', [BlogController::class, 'index'])->name('site.blog.index');
    Route::get('/blog/{post}', [BlogController::class, 'show'])->name('site.blog.show');

    // Documentación legal
    Route::get('/terminos-y-condiciones', [LegalDocumentationController::class, 'terms'])->name('site.legal.terms');
    Route::get('/politica-de-privacidad', [LegalDocumentationController::class, 'privacy'])->name('site.legal.privacy');
    Route::get('/libro-de-reclamaciones', [LegalDocumentationController::class, 'claims'])->name('site.legal.claims');

    // Páginas informativas
    Route::view('/nosotros', 'site.about.index')->name('about.index');
    Route::view('/contacto', 'site.contact.index')->name('contact.index');

    Route::get('/families/{family}', [FamilyController::class, 'show'])->name('families.show');
    Route::get('/categories/{category}', [CategoryController::class, 'show'])->name('categories.show');
    Route::get('/products/{product}', [ProductController::class, 'show'])->name('products.show');

    Route::get('/search', [SearchController::class, 'index'])->name('search.index');
    Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

    // Área de cuenta del cliente
    Route::middleware('auth')->group(function () {
        Route::get('/mi-cuenta', [SiteProfileController::class, 'index'])->name('site.profile.index');
        Route::get('/mi-cuenta/detalles', [SiteProfileController::class, 'details'])->name('site.profile.details');
        Route::put('/mi-cuenta/detalles', [SiteProfileController::class, 'updateDetails'])->name('site.profile.details.update');
        Route::put('/mi-cuenta/detalles/password', [SiteProfileController::class, 'updatePassword'])->name('site.profile.details.password');
        Route::get('/mi-cuenta/pedidos', [SiteProfileController::class, 'orders'])->name('site.profile.orders');
        Route::get('/mi-cuenta/favoritos', [SiteProfileController::class, 'wishlist'])->name('site.profile.wishlist');
        Route::get('/mi-cuenta/direcciones', [SiteProfileController::class, 'addresses'])->name('site.profile.addresses');
        Route::post('/mi-cuenta/direcciones', [SiteProfileController::class, 'storeAddress'])->name('site.profile.addresses.store');
        Route::put('/mi-cuenta/direcciones/{address}', [SiteProfileController::class, 'updateAddress'])->name('site.profile.addresses.update');
        Route::delete('/mi-cuenta/direcciones/{address}', [SiteProfileController::class, 'destroyAddress'])->name('site.profile.addresses.destroy');
        Route::get('/mi-cuenta/seguridad', [SiteProfileController::class, 'security'])->name('site.profile.security');
        Route::post('/mi-cuenta/seguridad/logout-session', [SiteProfileController::class, 'logoutSession'])->name('site.profile.logout-session');
    });

    // Rutas para la lista de deseos (wishlist)
    Route::get('/wishlists', [WishlistController::class, 'index'])->name('wishlists.index');
    Route::post('/wishlists', [WishlistController::class, 'store'])->name('wishlists.store');
    Route::delete('/wishlists', [WishlistController::class, 'destroyAll'])->name('wishlists.clear');
    Route::delete('/wishlists/{wishlistItem}', [WishlistController::class, 'destroy'])->name('wishlists.destroy');

    // Rutas para el carrito de compras
    Route::get('/carts', [CartController::class, 'show'])->name('carts.show');
    Route::delete('/carts', [CartController::class, 'destroy'])->name('carts.destroy');
    Route::patch('/carts/items/{cartItem}', [CartController::class, 'updateItem'])->name('carts.items.update');
    Route::delete('/carts/items/{cartItem}', [CartController::class, 'destroyItem'])->name('carts.items.destroy');

    // Rutas de envío (direcciones del cliente)
    Route::get('/shipping', [ShippingController::class, 'index'])->name('shipping.index');
    Route::post('/shipping/addresses', [ShippingController::class, 'store'])->name('shipping.addresses.store');
    Route::get('/shipping/addresses/{address}/edit', [ShippingController::class, 'edit'])->name('shipping.addresses.edit');
    Route::put('/shipping/addresses/{address}', [ShippingController::class, 'update'])->name('shipping.addresses.update');
    Route::delete('/shipping/addresses/{address}', [ShippingController::class, 'destroy'])->name('shipping.addresses.destroy');

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

