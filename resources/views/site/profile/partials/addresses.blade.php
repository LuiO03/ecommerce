<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Mis direcciones</span>
        <p class="card-description">Gestiona tus direcciones de envío favoritas.</p>
    </div>
    @if (!isset($addresses) || $addresses->isEmpty())
        <div class="card-empty">
            <div class="wishlist-empty-icon">
                <i class="ri-map-pin-line"></i>
            </div>
            <h3 class="card-title">Aún no tienes direcciones guardadas</h3>
            <p>Registra una dirección para agilizar tus próximas compras.</p>
            <a href="{{ route('shipping.index') }}" class="boton-form boton-success py-3 px-5">
                <span class="boton-form-icon"><i class="ri-truck-fill"></i></span>
                <span class="boton-form-text">Agregar dirección</span>
            </a>
        </div>
    @else
        <div class="profile-grid addresses-grid">
            @foreach ($addresses as $address)
                <article class="address-card {{ $address->is_default ? 'address-card--default' : '' }}">
                    <header class="address-card-header">
                        <div class="address-card-title-wrapper">
                            <i class="ri-map-pin-2-line"></i>
                            <h3 class="address-card-title">
                                {{ $address->type === 'office' ? 'Dirección de oficina' : 'Dirección de casa' }}
                            </h3>
                        </div>
                        @if ($address->is_default)
                            <span class="address-badge">Predeterminada</span>
                        @endif
                    </header>
                    <div class="address-card-body">
                        <p class="address-line">{{ $address->address_line }}</p>
                        <p class="address-city">{{ $address->district }}</p>
                        <p class="address-reference">{{ $address->reference }}</p>
                        <p class="address-receiver">{{ $address->receiver_name }} {{ $address->receiver_last_name }}</p>
                        <p class="address-phone">{{ $address->receiver_phone }}</p>
                    </div>
                </article>
            @endforeach
        </div>

        <div class="profile-section-footer">
            <a href="{{ route('shipping.index') }}" class="boton-form boton-accent py-3 px-5">
                <span class="boton-form-icon"><i class="ri-edit-2-line"></i></span>
                <span class="boton-form-text">Gestionar direcciones</span>
            </a>
        </div>
    @endif
</div>
