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
        <div class="tabla-controles">
            <div class="tabla-buscador">
                <i class="ri-search-eye-line buscador-icon"></i>
                <input type="text" id="customSearch" placeholder="Buscar portadas por nombre" autocomplete="off" />
                <button type="button" id="clearSearch" class="buscador-clear" title="Limpiar búsqueda">
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <div class="tabla-filtros">
                <div class="tabla-select-wrapper">
                    <div class="selector">
                        <select id="sortFilter">
                            <option value="">Ordenar por</option>
                            <option value="name-asc">Nombre (A-Z)</option>
                            <option value="name-desc">Nombre (Z-A)</option>
                            <option value="date-desc">Más recientes</option>
                            <option value="date-asc">Más antiguos</option>
                        </select>
                        <i class="ri-sort-asc selector-icon"></i>
                    </div>
                </div>

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
                <!-- Botón para limpiar filtros -->
                <button type="button" id="clearFiltersBtn" class="boton-clear-filters"
                    title="Limpiar todos los filtros">
                    <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
                    <span class="boton-text">Limpiar filtros</span>
                </button>
            </div>
        </div>

        <!-- Galería de tarjetas -->
        <div class="covers-gallery" id="coversGallery">
            @forelse ($covers as $cover)
                <div class="cover-card" data-id="{{ $cover->id }}" data-title="{{ $cover->title }}"
                    data-position="{{ $cover->position }}" data-status="{{ $cover->status }}"
                    data-created="{{ $cover->created_at ? $cover->created_at->timestamp : 0 }}">
                    <!-- Imagen principal -->
                    <div class="card-image-wrapper">
                        <img src="{{ asset('storage/' . $cover->image_path) }}" alt="{{ $cover->title }}"
                            class="card-image" data-image-path="{{ $cover->image_path }}">

                        <!-- Overlay de texto/CTA de la portada -->
                        @if ($cover->overlay_text || $cover->overlay_subtext || $cover->button_text)
                            <div class="cover-overlay-preview pos-{{ $cover->text_position ?? 'center-center' }} {{ $cover->overlay_bg_enabled ? 'has-overlay-bg' : '' }}"
                                style="--overlay-text-color: {{ $cover->text_color ?? '#FFFFFF' }}; --overlay-bg-opacity: {{ $cover->overlay_bg_opacity ?? 0.35 }};">
                                <div class="cover-overlay-content">
                                    @if ($cover->overlay_text)
                                        <span class="cover-overlay-title">{{ $cover->overlay_text }}</span>
                                    @endif
                                    @if ($cover->overlay_subtext)
                                        <span class="cover-overlay-subtext">{{ $cover->overlay_subtext }}</span>
                                    @endif
                                    @if ($cover->button_text)
                                        <span
                                            class="cover-overlay-button is-{{ $cover->button_style ?? 'primary' }}">{{ $cover->button_text }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif

                        <div class="card-overlay">
                            <div class="overlay-actions">
                                @can('portadas.edit')
                                    <a href="{{ route('admin.covers.edit', $cover->slug) }}"
                                        class="boton-form boton-warning" title="Editar">
                                        <i class="ri-pencil-fill"></i>
                                    </a>
                                @endcan
                                @can('portadas.delete')
                                    <button type="button" class="boton-form boton-danger" title="Eliminar"
                                        data-url="{{ route('admin.covers.destroy', $cover->slug) }}"
                                        data-id="{{ $cover->id }}" data-name="{{ $cover->title }}">
                                        <i class="ri-delete-bin-fill"></i>
                                    </button>
                                @endcan
                            </div>
                        </div>
                    </div>

                    <!-- Información de la tarjeta -->
                    <div class="card-content">
                        <h3 class="card-title">{{ $cover->title }}</h3>

                        <div class="card-meta">
                            <div class="meta-info">
                                <span class="meta-item">
                                    <i class="ri-hashtag"></i>{{ $cover->position }}
                                </span>
                                <span class="meta-item meta-date">
                                    <i class="ri-calendar-fill"></i>
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

    <script>
        // Script INLINE INMEDIATO
        document.addEventListener('change', function(e) {
            if (e.target.tagName === 'INPUT' && e.target.classList.contains('switch-status')) {
                const slug = e.target.dataset.key;
                const isChecked = e.target.checked;
                const token = document.querySelector('meta[name="csrf-token"]').content;

                fetch(`/admin/covers/${slug}/status`, {
                        method: 'PATCH',
                        headers: {
                            'X-CSRF-TOKEN': token,
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            status: isChecked ? 1 : 0
                        })
                    })
                    .then(r => r.json())
                    .then(d => {
                        if (d.success) {
                            if (typeof window.showToast === 'function') {
                                window.showToast({
                                    type: 'success',
                                    title: 'Actualizado',
                                    message: d.message
                                });
                            }
                        }
                    })
                    .catch(e => {
                        e.target.checked = !isChecked;
                    });
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            const gallery = document.getElementById('coversGallery');
            if (!gallery) return;

            const searchInput = document.getElementById('customSearch');
            const clearSearch = document.getElementById('clearSearch');
            const sortFilter = document.getElementById('sortFilter');
            const statusFilter = document.getElementById('statusFilter');
            const clearFiltersBtn = document.getElementById('clearFiltersBtn');
            const noResults = document.getElementById('noResultsMessage');

            const cards = Array.from(gallery.querySelectorAll('.cover-card'));

            cards.forEach(card => {
                const titleEl = card.querySelector('.card-title');
                if (titleEl && !titleEl.dataset.original) {
                    titleEl.dataset.original = titleEl.textContent;
                }
            });

            const applySearchHighlight = (card, query) => {
                const titleEl = card.querySelector('.card-title');
                if (!titleEl) return;
                const original = titleEl.dataset.original || titleEl.textContent;

                if (!query) {
                    titleEl.textContent = original;
                    return;
                }

                const escaped = query.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
                const regex = new RegExp(`(${escaped})`, 'gi');
                titleEl.innerHTML = original.replace(regex, '<mark class="perm-mark">$1</mark>');
            };

            const applyFilters = () => {
                const query = (searchInput?.value || '').trim().toLowerCase();
                const status = statusFilter?.value ?? '';

                let visibleCount = 0;

                cards.forEach(card => {
                    const title = (card.dataset.title || '').toLowerCase();
                    const matchSearch = !query || title.includes(query);
                    const matchStatus = !status || card.dataset.status === status;

                    const isVisible = matchSearch && matchStatus;
                    card.style.display = isVisible ? '' : 'none';
                    if (isVisible) visibleCount += 1;

                    applySearchHighlight(card, query);
                });

                if (clearSearch) {
                    clearSearch.style.display = query ? 'inline-flex' : 'none';
                }

                if (noResults) {
                    if (cards.length === 0) {
                        noResults.style.display = 'none';
                    } else {
                        noResults.style.display = visibleCount === 0 ? 'flex' : 'none';
                    }
                }
            };

            const sortCards = (mode) => {
                const sorted = [...cards].sort((a, b) => {
                    const titleA = (a.dataset.title || '').toLowerCase();
                    const titleB = (b.dataset.title || '').toLowerCase();
                    const createdA = parseInt(a.dataset.created || '0', 10);
                    const createdB = parseInt(b.dataset.created || '0', 10);

                    switch (mode) {
                        case 'name-asc':
                            return titleA.localeCompare(titleB);
                        case 'name-desc':
                            return titleB.localeCompare(titleA);
                        case 'date-asc':
                            return createdA - createdB;
                        case 'date-desc':
                        default:
                            return createdB - createdA;
                    }
                });

                sorted.forEach(card => gallery.appendChild(card));
            };

            // === Buscador ===
            searchInput?.addEventListener('input', applyFilters);
            clearSearch?.addEventListener('click', () => {
                if (!searchInput) return;
                searchInput.value = '';
                applyFilters();
            });

            // === Filtros ===
            statusFilter?.addEventListener('change', applyFilters);
            sortFilter?.addEventListener('change', () => {
                sortCards(sortFilter.value);
                applyFilters();
            });
            clearFiltersBtn?.addEventListener('click', () => {
                if (searchInput) searchInput.value = '';
                if (statusFilter) statusFilter.value = '';
                if (sortFilter) sortFilter.value = '';
                sortCards('date-desc');
                applyFilters();
            });

            // === Eliminar individual con modal ===
            gallery.querySelectorAll('button[data-url][data-id]').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    const url = btn.dataset.url;
                    const name = btn.dataset.name || 'portada';

                    if (typeof window.showConfirm !== 'function') {
                        return;
                    }

                    window.showConfirm({
                        type: 'danger',
                        header: 'Confirmar eliminación',
                        title: '¿Eliminar portada?',
                        message: `¿Estás seguro de que deseas eliminar la portada <strong>"${name}"</strong>?<br>Esta acción no se puede deshacer.`,
                        confirmText: 'Sí, eliminar',
                        cancelText: 'No, cancelar',
                        onConfirm: () => {
                            const form = document.createElement('form');
                            form.method = 'POST';
                            form.action = url;

                            const csrf = document.createElement('input');
                            csrf.type = 'hidden';
                            csrf.name = '_token';
                            csrf.value = '{{ csrf_token() }}';
                            form.appendChild(csrf);

                            const method = document.createElement('input');
                            method.type = 'hidden';
                            method.name = '_method';
                            method.value = 'DELETE';
                            form.appendChild(method);

                            document.body.appendChild(form);
                            form.submit();
                        }
                    });
                });
            });

            // Inicializar estado
            sortCards(sortFilter?.value || 'date-desc');
            applyFilters();
        });

        // Resaltar fila creada/editada
        @if (Session::has('highlightRow'))
            (function() {
                const highlightId = {{ Session::get('highlightRow') }};

                // Resaltar y hacer scroll a la tarjeta
                setTimeout(() => {
                    const card = document.querySelector(`[data-id="${highlightId}"]`);
                    if (card) {
                        card.classList.add('card-highlight');
                        card.scrollIntoView({
                            behavior: 'smooth',
                            block: 'nearest'
                        });

                        // Remover la clase después de 4 segundos
                        setTimeout(() => {
                            card.classList.remove('card-highlight');
                        }, 4000);
                    }
                }, 100);
            })();
        @endif
    </script>
</x-admin-layout>
