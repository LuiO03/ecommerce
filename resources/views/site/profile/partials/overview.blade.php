<div class="profile-section">
    <div class="form-body">
        <div class="card-header">
            <span class="card-title">Resumen de tu cuenta</span>
            <p class="card-description">Una vista rápida de tu información principal.</p>
        </div>

        <div class="overview-grid">
            <article class="overview-card">
                <div class="overview-card-icon card-warning">
                    <i class="ri-user-3-fill"></i>
                </div>
                <div class="overview-card-body">
                    <h6>Datos personales</h6>
                    <p>{{ $user->name }} {{ $user->last_name }}</p>
                    <p class="overview-muted">{{ $user->email }}</p>
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-card-icon card-primary">
                    <i class="ri-shopping-bag-3-fill"></i>
                </div>
                <div class="overview-card-body">
                    <h6>Últimos pedidos</h6>
                    @if(isset($orders) && $orders->isNotEmpty())
                        <p>Has realizado {{ $orders->count() }} pedidos recientemente.</p>
                        <a href="{{ route('site.profile.index', ['section' => 'orders']) }}" class="overview-link">Ver historial completo</a>
                    @else
                        <p class="overview-muted">Aún no has realizado pedidos.</p>
                    @endif
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-card-icon card-pink">
                    <i class="ri-heart-3-fill"></i>
                </div>
                <div class="overview-card-body">
                    <h6>Favoritos</h6>
                    @if(isset($wishlistItems) && $wishlistItems->isNotEmpty())
                        <p>Tienes {{ $wishlistItems->count() }} productos en tu lista de deseos.</p>
                        <a href="{{ route('site.profile.index', ['section' => 'wishlist']) }}" class="overview-link">Ver todos los favoritos</a>
                    @else
                        <p class="overview-muted">Aún no has agregado productos a favoritos.</p>
                    @endif
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-card-icon card-success">
                    <i class="ri-map-pin-fill"></i>
                </div>
                <div class="overview-card-body">
                    <h6>Direcciones guardadas</h6>
                    @if(isset($addresses) && $addresses->isNotEmpty())
                        <p>{{ $addresses->count() }} direcciones guardadas.</p>
                        <a href="{{ route('site.profile.index', ['section' => 'addresses']) }}" class="overview-link">Gestionar direcciones</a>
                    @else
                        <p class="overview-muted">Aún no has registrado direcciones de envío.</p>
                    @endif
                </div>
            </article>

            <article class="overview-card">
                <div class="overview-card-icon card-danger">
                    <i class="ri-lock-2-line"></i>
                </div>
                <div class="overview-card-body">
                    <h6>Contraseña</h6>
                    <p class="overview-muted">Por motivos de seguridad, no mostramos tu contraseña.</p>
                    <a href="{{ route('site.profile.index', ['section' => 'security']) }}#password-section" class="overview-link">
                        <i class="ri-arrow-right-up-line"></i>
                        Cambiar contraseña desde panel seguro
                    </a>
                </div>
            </article>
        </div>

    </div>
</div>
