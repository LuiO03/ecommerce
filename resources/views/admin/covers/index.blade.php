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
        <!-- Controles superiores -->
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar usuarios por nombre o email"
                    autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <div class="tabla-filtros">
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="statusFilter">
                            <option value="">Todos los estados</option>
                            <option value="1">Activos</option>
                            <option value="0">Inactivos</option>
                        </select>
                        <i class="ri-filter-3-line selector-icon"></i>
                    </div>
                </div>

                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="sortFilter">
                            <option value="">Ordenar por</option>
                            <option value="position-asc">Posición ↑</option>
                            <option value="position-desc">Posición ↓</option>
                            <option value="title-asc">Título (A-Z)</option>
                            <option value="title-desc">Título (Z-A)</option>
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>

                    <!-- Botón para limpiar filtros -->
                    <button type="button" id="clearFiltersBtn" class="boton-clear-filters"
                        title="Limpiar todos los filtros">
                        <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                        <span class="boton-text">Limpiar filtros</span>
                    </button>
                </div>
            </div>
        </div>
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

@push('styles')
    @vite(['resources/css/admin/modules/covers.css'])
@endpush

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // ==========================================
            // 🎨 GESTOR DE GALERÍA DE PORTADAS
            // ==========================================

            class CoversGalleryManager {
                constructor() {
                    this.gallery = document.getElementById('coversGallery');
                    this.searchInput = document.getElementById('customSearch');
                    this.clearSearchBtn = document.getElementById('clearSearch');
                    this.statusFilterSelect = document.getElementById('statusFilter');
                    this.sortFilterSelect = document.getElementById('sortFilter');
                    this.clearFiltersBtn = document.getElementById('clearFiltersBtn');
                    this.selectionBar = document.getElementById('selectionBar');
                    this.noResultsMessage = document.getElementById('noResultsMessage');

                    this.selectedItems = new Set();
                    this.allCards = Array.from(this.gallery.querySelectorAll('.cover-card'));

                    this.init();
                }

                init() {
                    this.bindEvents();
                    this.applyFilters();
                }

                bindEvents() {
                    // Búsqueda
                    this.searchInput.addEventListener('input', () => this.applyFilters());
                    this.clearSearchBtn.addEventListener('click', () => {
                        this.searchInput.value = '';
                        this.applyFilters();
                    });

                    // Filtros
                    this.statusFilterSelect.addEventListener('change', () => this.applyFilters());
                    this.sortFilterSelect.addEventListener('change', () => this.applyFilters());
                    this.clearFiltersBtn.addEventListener('click', () => {
                        this.searchInput.value = '';
                        this.statusFilterSelect.value = '';
                        this.sortFilterSelect.value = '';
                        this.applyFilters();
                    });

                    // Selección múltiple
                    this.gallery.addEventListener('change', (e) => {
                        if (e.target.classList.contains('check-row')) {
                            this.toggleCardSelection(e.target);
                        }
                    });

                    // Estado (toggle)
                    this.gallery.addEventListener('change', (e) => {
                        if (e.target.classList.contains('switch-status')) {
                            this.handleStatusChange(e.target);
                        }
                    });

                    // Seleccionar todo
                    document.addEventListener('keydown', (e) => {
                        if (e.ctrlKey && e.key === 'a') {
                            e.preventDefault();
                            const visibleCards = this.getVisibleCards();
                            visibleCards.forEach(card => {
                                const checkbox = card.querySelector('.check-row');
                                if (checkbox) {
                                    checkbox.checked = true;
                                    this.selectedItems.add(parseInt(checkbox.value));
                                }
                            });
                            this.updateSelectionBar();
                        }
                    });

                    // Botones de eliminación
                    document.getElementById('deleteSelected')?.addEventListener('click', () => this
                        .deleteSelected());
                    document.getElementById('clearSelection')?.addEventListener('click', () => this
                        .clearSelection());

                    // Botones de eliminar individual
                    this.gallery.addEventListener('click', (e) => {
                        if (e.target.closest('.delete-btn')) {
                            const btn = e.target.closest('.delete-btn');
                            const url = btn.dataset.url;
                            const name = btn.dataset.name;
                            this.deleteSingle(url, name);
                        }
                    });
                }

                toggleCardSelection(checkbox) {
                    const cardId = parseInt(checkbox.value);
                    const card = checkbox.closest('.cover-card');

                    if (checkbox.checked) {
                        this.selectedItems.add(cardId);
                        card.classList.add('selected');
                    } else {
                        this.selectedItems.delete(cardId);
                        card.classList.remove('selected');
                    }

                    this.updateSelectionBar();
                }

                updateSelectionBar() {
                    const count = this.selectedItems.size;

                    if (count > 0) {
                        this.selectionBar.style.display = 'flex';
                        document.getElementById('selectionCount').textContent =
                            count === 1 ? '1 portada seleccionada' : `${count} portadas seleccionadas`;
                    } else {
                        this.selectionBar.style.display = 'none';
                    }
                }

                handleStatusChange(checkbox) {
                    const cardId = checkbox.dataset.id;
                    const cardKey = checkbox.dataset.key;
                    const status = checkbox.checked ? 1 : 0;
                    const url = `/admin/covers/${cardKey}/status`;

                    fetch(url, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                            },
                            body: JSON.stringify({
                                status: status
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const card = checkbox.closest('.cover-card');
                                if (status) {
                                    card.dataset.status = '1';
                                    card.classList.add('cover-active');
                                } else {
                                    card.dataset.status = '0';
                                    card.classList.remove('cover-active');
                                }
                                showToast({
                                    type: 'success',
                                    title: 'Estado actualizado',
                                    message: data.message ||
                                        'El estado se actualizó correctamente.',
                                    duration: 3000
                                });
                            }
                        })
                        .catch(err => {
                            console.error('Error:', err);
                            checkbox.checked = !checkbox.checked;
                        });
                }

                deleteSelected() {
                    if (this.selectedItems.size === 0) return;

                    const count = this.selectedItems.size;
                    const entity = count === 1 ? 'portada' : 'portadas';

                    showConfirmModal({
                        title: `¿Eliminar ${count} ${entity}?`,
                        message: 'Esta acción no se puede deshacer.',
                        confirmText: 'Sí, eliminar',
                        cancelText: 'Cancelar',
                        type: 'danger',
                        onConfirm: () => {
                            const ids = Array.from(this.selectedItems);
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = '/admin/covers';
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                <input type="hidden" name="_method" value="DELETE">
                                <input type="hidden" name="covers" value="${ids.join(',')}">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }

                deleteSingle(url, name) {
                    showConfirmModal({
                        title: `¿Eliminar "${name}"?`,
                        message: 'Esta acción no se puede deshacer.',
                        confirmText: 'Sí, eliminar',
                        cancelText: 'Cancelar',
                        type: 'danger',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;
                            form.innerHTML = `
                                <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').content}">
                                <input type="hidden" name="_method" value="DELETE">
                            `;
                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                }

                clearSelection() {
                    this.selectedItems.clear();
                    this.gallery.querySelectorAll('.check-row').forEach(checkbox => {
                        checkbox.checked = false;
                    });
                    this.gallery.querySelectorAll('.cover-card').forEach(card => {
                        card.classList.remove('selected');
                    });
                    this.updateSelectionBar();
                }

                applyFilters() {
                    const searchTerm = this.searchInput.value.toLowerCase();
                    const statusFilter = this.statusFilterSelect.value;
                    const sortFilter = this.sortFilterSelect.value;

                    let visibleCards = this.allCards.filter(card => {
                        const title = card.dataset.title.toLowerCase();
                        const status = card.dataset.status;

                        const matchesSearch = !searchTerm || title.includes(searchTerm);
                        const matchesStatus = !statusFilter || status === statusFilter;

                        return matchesSearch && matchesStatus;
                    });

                    // Aplicar ordenamiento
                    if (sortFilter) {
                        visibleCards = this.sortCards(visibleCards, sortFilter);
                    }

                    // Actualizar visibilidad
                    this.allCards.forEach(card => card.style.display = 'none');
                    visibleCards.forEach(card => card.style.display = '');

                    // Mostrar mensaje de sin resultados
                    if (visibleCards.length === 0 && searchTerm) {
                        this.noResultsMessage.style.display = 'flex';
                    } else {
                        this.noResultsMessage.style.display = 'none';
                    }
                }

                getVisibleCards() {
                    return this.allCards.filter(card => card.style.display !== 'none');
                }

                sortCards(cards, sortType) {
                    const sorted = [...cards];

                    switch (sortType) {
                        case 'position-asc':
                            return sorted.sort((a, b) => parseInt(a.dataset.position) - parseInt(b.dataset
                                .position));
                        case 'position-desc':
                            return sorted.sort((a, b) => parseInt(b.dataset.position) - parseInt(a.dataset
                                .position));
                        case 'title-asc':
                            return sorted.sort((a, b) => a.dataset.title.localeCompare(b.dataset.title));
                        case 'title-desc':
                            return sorted.sort((a, b) => b.dataset.title.localeCompare(a.dataset.title));
                        case 'date-desc':
                            return sorted.reverse();
                        case 'date-asc':
                            return sorted;
                        default:
                            return sorted;
                    }
                }
            }

            // Inicializar el gestor
            new CoversGalleryManager();

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
