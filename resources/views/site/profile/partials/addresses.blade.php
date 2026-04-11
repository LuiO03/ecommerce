<div class="profile-section">
    <div class="form-body">
        <div class="card-header-container">
            <div class="card-header">
                <span class="card-title">Mis direcciones</span>
                <p class="card-description">Gestiona tus direcciones de envío favoritas.</p>
            </div>
            <div class="card-header-actions">

                <a href="{{ route('shipping.index') }}" class="boton-form boton-success">
                    <span class="boton-form-icon"><i class="ri-map-pin-2-fill"></i></span>
                    <span class="boton-form-text">Agregar dirección</span>
                </a>
            </div>
        </div>
        @if (!isset($addresses) || $addresses->isEmpty())
            <div class="card-empty">
                <div class="wishlist-empty-icon">
                    <i class="ri-map-pin-line"></i>
                </div>
                <h3 class="card-title">Aún no tienes direcciones guardadas</h3>
                <p>Registra una dirección para agilizar tus próximas compras.</p>
                <a href="{{ route('shipping.index') }}" class="boton-form boton-success">
                    <span class="boton-form-icon"><i class="ri-truck-fill"></i></span>
                    <span class="boton-form-text">Agregar dirección</span>
                </a>
            </div>
        @else
            <div class="addresses-grid">
                @foreach ($addresses as $address)
                    <article class="address-card {{ $address->is_default ? 'address-card--default' : '' }}">
                        <header class="address-card-header">
                            <div class="address-card-title-wrapper"
                                title="{{ $address->type === 'office' ? 'Dirección de oficina' : 'Dirección de casa' }}">
                                @if ($address->type === 'office')
                                    <i class="ri-building-2-fill"></i>
                                    <span class="address-card-title">Oficina</span>
                                @else
                                    <i class="ri-home-2-fill"></i>
                                    <span class="address-card-title">Casa</span>
                                @endif
                            </div>
                        </header>
                        <div class="address-card-body">
                            <span class="card-title">{{ $address->receiver_name }}
                                {{ $address->receiver_last_name }}</span>
                            <p class="address-line">{{ $address->address_line }}</p>
                            <p class="address-city">{{ $address->district }}</p>
                            <p class="address-reference">{{ $address->reference }}</p>
                            <p class="address-phone">{{ $address->receiver_phone }}</p>
                            @if ($address->is_default)
                                <span class="badge badge-primary">
                                    <i class="ri-lock-star-fill"></i>
                                    Principal
                                </span>
                            @endif
                        </div>
                        <div class="address-card-actions">
                            <a href="" class="boton-pastel card-warning" title="Editar dirección"
                                aria-label="Editar dirección">
                                <i class="ri-pencil-fill"></i>
                            </a>
                            <form method="POST" action="">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="boton-pastel card-danger" title="Eliminar dirección"
                                    aria-label="Eliminar dirección">
                                    <i class="ri-delete-bin-5-fill"></i>
                                </button>
                            </form>
                            @if (!$address->is_default)
                                <button type="button" class="boton-pastel card-accent"
                                    title="Establecer como principal" aria-label="Establecer como principal">
                                    <i class="ri-star-fill"></i>
                                </button>
                            @endif
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
