@guest
<div id="authWishlistModal" class="auth-wishlist-modal" aria-hidden="true">
    <div class="auth-wishlist-backdrop" data-auth-wishlist-close></div>

    <div class="auth-wishlist-dialog" role="dialog" aria-modal="true" aria-labelledby="authWishlistModalTitle">
        <button type="button" class="auth-wishlist-close" data-auth-wishlist-close aria-label="Cerrar">
            <i class="ri-close-line"></i>
        </button>

        <div class="auth-wishlist-header">
            <div class="auth-wishlist-icon">
                <i class="ri-poker-hearts-fill"></i>
            </div>
            <div>
                <h3 id="authWishlistModalTitle" class="auth-wishlist-title">
                    Inicia sesión para guardar tus favoritos
                </h3>
                <hr class="w-full my-0 border-default">
                <p class="auth-wishlist-text">
                    Para agregar productos a tu lista de deseos necesitas iniciar sesión en tu cuenta.
                </p>
                <p class="auth-wishlist-text">
                    Así podrás guardar tus productos favoritos, revisarlos más tarde y acceder a ellos desde cualquier dispositivo.
                </p>
            </div>
        </div>


        <div class="auth-wishlist-actions">
            <a href="{{ route('register') }}" class="site-btn site-btn-outline">
                Crear cuenta
            </a>
            <a href="{{ route('login') }}" class="site-btn site-btn-primary">
                Iniciar sesión
            </a>
        </div>
    </div>
</div>
@endguest
