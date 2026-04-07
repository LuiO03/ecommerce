<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Resumen de tu cuenta</span>
        <p class="card-description">Una vista rápida de tu información principal.</p>
    </div>

    <div class="profile-grid overview-grid">
        <article class="overview-card">
            <div class="overview-card-icon overview-card-icon--primary">
                <i class="ri-user-3-line"></i>
            </div>
            <div class="overview-card-body">
                <h3>Datos personales</h3>
                <p>{{ $user->name }} {{ $user->last_name }}</p>
                <p class="overview-muted">{{ $user->email }}</p>
            </div>
        </article>

        <article class="overview-card">
            <div class="overview-card-icon overview-card-icon--success">
                <i class="ri-shopping-bag-3-line"></i>
            </div>
            <div class="overview-card-body">
                <h3>Últimos pedidos</h3>
                @if(isset($orders) && $orders->isNotEmpty())
                    <p>Has realizado {{ $orders->count() }} pedidos recientemente.</p>
                    <a href="{{ route('site.profile.orders') }}" class="overview-link">Ver historial completo</a>
                @else
                    <p class="overview-muted">Aún no has realizado pedidos.</p>
                @endif
            </div>
        </article>

        <article class="overview-card">
            <div class="overview-card-icon overview-card-icon--accent">
                <i class="ri-heart-3-line"></i>
            </div>
            <div class="overview-card-body">
                <h3>Favoritos</h3>
                @if(isset($wishlistItems) && $wishlistItems->isNotEmpty())
                    <p>Tienes {{ $wishlistItems->count() }} productos en tu lista de deseos.</p>
                    <a href="{{ route('site.profile.wishlist') }}" class="overview-link">Ver todos los favoritos</a>
                @else
                    <p class="overview-muted">Aún no has agregado productos a favoritos.</p>
                @endif
            </div>
        </article>

        <article class="overview-card">
            <div class="overview-card-icon overview-card-icon--warning">
                <i class="ri-map-pin-line"></i>
            </div>
            <div class="overview-card-body">
                <h3>Direcciones guardadas</h3>
                @if(isset($addresses) && $addresses->isNotEmpty())
                    <p>{{ $addresses->count() }} direcciones guardadas.</p>
                    <a href="{{ route('site.profile.addresses') }}" class="overview-link">Gestionar direcciones</a>
                @else
                    <p class="overview-muted">Aún no has registrado direcciones de envío.</p>
                @endif
            </div>
        </article>
    </div>
</div>
