<div class="profile-section" id="profileAddressesSection" data-store-url="{{ route('site.profile.addresses.store') }}">
    <div class="form-body">
        <div class="card-header-container">
            <div class="card-header">
                <span class="card-title">Mis direcciones</span>
                <p class="card-description">Gestiona tus direcciones de envío favoritas.</p>
            </div>
            <div class="card-header-actions">
                <button type="button" class="boton-form boton-success" data-address-modal-open="create">
                    <span class="boton-form-icon"><i class="ri-map-pin-2-fill"></i></span>
                    <span class="boton-form-text">Agregar dirección</span>
                </button>
            </div>
        </div>
        @if (!isset($addresses) || $addresses->isEmpty())
            <div class="card-empty">
                <div class="wishlist-empty-icon">
                    <i class="ri-map-pin-line"></i>
                </div>
                <h3 class="card-title">Aún no tienes direcciones guardadas</h3>
                <p>Registra una dirección para agilizar tus próximas compras.</p>
                <button type="button" class="boton-form boton-success" data-address-modal-open="create">
                    <span class="boton-form-icon"><i class="ri-truck-fill"></i></span>
                    <span class="boton-form-text">Agregar dirección</span>
                </button>
            </div>
        @else
            <div class="addresses-grid">
                @foreach ($addresses as $address)
                    <article class="address-card">
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
                            <span class="card-title">
                                {{ $address->receiver_name }}
                                {{ $address->receiver_last_name }}
                            </span>
                            <ul>
                                <li class="address-line">{{ $address->address_line }}</li>
                                <li class="address-city">{{ $address->district }}</li>
                                <li class="address-reference">{{ $address->reference }}</li>
                                <li class="address-phone">{{ $address->receiver_phone }}</li>
                            </ul>
                        </div>
                        <div class="address-card-actions">
                            <button
                                type="button"
                                class="boton-pastel card-warning address-edit-btn"
                                title="Editar dirección"
                                aria-label="Editar dirección"
                                data-address-modal-open="edit"
                                data-address-id="{{ $address->id }}"
                                data-address-type="{{ $address->type }}"
                                data-address-line="{{ e($address->address_line) }}"
                                data-address-district="{{ e($address->district) }}"
                                data-address-reference="{{ e($address->reference) }}"
                                data-address-receiver-name="{{ e($address->receiver_name) }}"
                                data-address-receiver-last-name="{{ e($address->receiver_last_name) }}"
                                data-address-receiver-phone="{{ e($address->receiver_phone) }}"
                                data-update-url="{{ route('site.profile.addresses.update', $address) }}"
                            >
                                <i class="ri-pencil-fill"></i>
                            </button>
                            <form method="POST" action="{{ route('site.profile.addresses.destroy', $address) }}" class="address-delete-form">
                                @csrf
                                @method('DELETE')
                                <button
                                    type="submit"
                                    class="boton-pastel card-danger address-delete-btn"
                                    title="Eliminar dirección"
                                    aria-label="Eliminar dirección"
                                    data-address-delete-url="{{ route('site.profile.addresses.destroy', $address) }}"
                                >
                                    <i class="ri-delete-bin-5-fill"></i>
                                </button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
        @endif
    </div>
</div>
