@section('title', 'Portadas')

<x-admin-layout :showMobileFab="true">
    <x-slot name="title">
        <div class="page-icon card-info">
            <i class="ri-image-2-line"></i>
        </div>
        Galería de Portadas
    </x-slot>
    <x-slot name="action">
        @can('portadas.create')
            <a href="{{ route('admin.covers.create') }}" class="boton boton-primary">
                <span class="boton-icon"><i class="ri-add-box-fill"></i></span>
                <span class="boton-text">Crear Portada</span>
            </a>
        @endcan
    </x-slot>

    <div class="options-wrapper">
            <!-- Barra de selección múltiple (oculta inicialmente) -->
            @can('portadas.delete')
                <div class="covers-selection-bar" id="selectionBar" style="display: none;">
                    <div class="selection-info">
                        <span id="selectionCount">0 seleccionados</span>
                    </div>
                    <div class="selection-actions">
                        <button id="deleteSelected" class="btn-selection btn-delete" title="Eliminar seleccionados">
                            <i class="ri-delete-bin-line"></i>
                            <span>Eliminar</span>
                        </button>
                    </div>
                    <button id="clearSelection" class="btn-close-selection" title="Deseleccionar todo">
                        <i class="ri-close-large-fill"></i>
                    </button>
                </div>
            @endcan

            <!-- Galería de tarjetas -->
            <div class="covers-gallery" id="coversGallery">
                @forelse ($covers as $cover)
                    <div class="cover-card" data-id="{{ $cover->id }}" data-title="{{ $cover->title }}"
                        data-position="{{ $cover->position }}" data-status="{{ $cover->status }}">
                        <!-- Checkbox de selección -->
                        @can('portadas.delete')
                            <div class="card-checkbox">
                                <div>
                                    <input type="checkbox" class="check-row" id="check-row-{{ $cover->id }}"
                                        value="{{ $cover->id }}">
                                </div>
                            </div>
                        @endcan

                        <!-- Imagen principal -->
                        <div class="card-image-wrapper">
                            <img src="{{ asset('storage/' . $cover->image_path) }}" alt="{{ $cover->title }}"
                                class="card-image" data-image-path="{{ $cover->image_path }}">
                            <div class="card-overlay">
                                <div class="overlay-actions">
                                    @can('portadas.edit')
                                        <a href="{{ route('admin.covers.edit', $cover->slug) }}"
                                            class="overlay-btn edit-btn" title="Editar">
                                            <i class="ri-pencil-line"></i>
                                        </a>
                                    @endcan
                                    @can('portadas.delete')
                                        <button type="button" class="overlay-btn delete-btn" title="Eliminar"
                                            data-url="{{ route('admin.covers.destroy', $cover->slug) }}"
                                            data-id="{{ $cover->id }}" data-name="{{ $cover->title }}">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    @endcan
                                </div>
                            </div>
                        </div>

                        <!-- Información de la tarjeta -->
                        <div class="card-content">
                            <h3 class="card-title">{{ $cover->title }}</h3>

                            <div class="card-meta">
                                <div class="">
                                    <span class="meta-item">
                                        <i class="ri-sort-number-asc"></i>
                                        Pos: <strong>{{ $cover->position }}</strong>
                                    </span>
                                    <span class="meta-item meta-date">
                                        <i class="ri-calendar-line"></i>
                                        {{ $cover->created_at ? $cover->created_at->format('d/m/Y') : '—' }}
                                    </span>
                                </div>
                                @can('portadas.update-status')
                                    <label class="switch-tabla">
                                        <input type="checkbox" class="switch-status" data-id="{{ $cover->id }}"
                                            data-key="{{ $cover->slug }}" {{ $cover->status ? 'checked' : '' }}>
                                        <span class="slider"></span>
                                    </label>
                                @else
                                    <span class="status-badge {{ $cover->status ? 'status-active' : 'status-inactive' }}">
                                        {{ $cover->status ? 'Activo' : 'Inactivo' }}
                                    </span>
                                @endcan
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="covers-empty">
                        <i class="ri-image-add-line empty-icon"></i>
                        <h3>No hay portadas</h3>
                        <p>Crea tu primera portada para comenzar</p>
                        @can('portadas.create')
                            <a href="{{ route('admin.covers.create') }}" class="btn-create-first">
                                <i class="ri-add-box-fill"></i>
                                <span>Crear Portada</span>
                            </a>
                        @endcan
                    </div>
                @endforelse
            </div>

            <!-- Mensaje cuando no hay resultados de búsqueda -->
            <div class="covers-no-results" id="noResultsMessage" style="display: none;">
                <i class="ri-search-line"></i>
                <h3>Sin resultados</h3>
                <p>No se encontraron portadas que coincidan con tu búsqueda</p>
            </div>
        </div>
</x-admin-layout>

@push('scripts')
    <script>

        document.addEventListener('DOMContentLoaded', function() {

            // Resaltar fila creada/editada
            @if (Session::has('highlightRow'))
                (function() {
                    const highlightId = {{ Session::get('highlightRow') }};
                    setTimeout(() => {
                        const card = document.querySelector(`[data-id="${highlightId}"]`);
                        if (card) {
                            card.classList.add('card-highlight');
                            card.scrollIntoView({
                                behavior: 'smooth',
                                block: 'nearest'
                            });
                            setTimeout(() => card.classList.remove('card-highlight'), 3000);
                        }
                    }, 100);
                })();
            @endif
        });

    </script>
@endpush
