@section('title', 'Permisos del rol: ' . $role->name)

<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-key-2-fill"></i></div>

        <div class="page-edit-title">
            <span class="page-subtitle">Gestione permisos para</span>
            {{ $role->name }}
        </div>
    </x-slot>

    <x-slot name="action">
        <a href="{{ route('admin.roles.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>

    <div class="form-container">
        <!-- Buscador + botón global -->
        <div class="actions-bar">
            <div class="module-search flex-1">
                <i class="ri-search-eye-line"></i>
                <input type="text" id="searchPerm" placeholder="Buscar módulo o acción..." autocomplete="off">
                <button type="button" class="module-search-clear" id="clearSearchPerm" aria-label="Limpiar búsqueda"
                    hidden>
                    <i class="ri-close-circle-fill"></i>
                </button>
            </div>

            <div class="tabla-select-wrapper" title="Ordenar permisos">
                <div class="selector">
                    <select id="permissionSort">
                        <option value="alpha_asc">A - Z</option>
                        <option value="alpha_desc">Z - A</option>
                    </select>
                    <i class="ri-sort-asc selector-icon"></i>
                </div>
            </div>

            <button type="button" class="boton boton-accent" id="toggleAllBtn">
                <span class="boton-icon"><i class="ri-checkbox-multiple-blank-fill"></i></span>
                <span class="boton-text" id="toggleAllText">Marcar todo</span>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" autocomplete="off"
            class="form-container" id="permissionForm">
            @csrf

            <div class="permissions-container">
                @forelse($modules as $module => $perms)
                    <div class="permissions-card ripple-card" data-module-name="{{ $module }}">
                        <div class="permissions-card-header">
                            <span class="card-title">{{ ucfirst($module) }}</span>

                            <div class="flex gap-1">
                                <button type="button" class="boton-single reset-module-btn is-hidden"
                                    data-module="{{ $module }}" title="Restablecer permisos del módulo">
                                    <i class="ri-reset-left-line"></i>
                                </button>

                                <button type="button" class="boton boton-danger select-all-btn"
                                    data-module="{{ $module }}">
                                    <span class="boton-icon"><i class="ri-checkbox-multiple-fill"></i></span>
                                    <span class="boton-text select-all-text">Selecc. todo</span>
                                </button>
                            </div>
                        </div>

                        <div class="card-body">
                            <ul class="p-0">
                                @foreach ($perms as $perm)
                                    @php
                                        $actionParts = explode('.', $perm['action']);
                                        $actionLabel = $actionParts[1] ?? $perm['action'];
                                        $createdTs = $perm['created_at'] ?? 0;
                                    @endphp
                                    <li class="perm-row perm-row-custom" data-module="{{ $module }}"
                                        data-action="{{ $actionLabel }}" data-label="{{ $actionLabel }}"
                                        data-created="{{ $createdTs }}">
                                        <div class="perm-info">
                                            <span class="permissions-name perm-highlight"
                                                title="{{ $perm['description'] ?? '' }}">
                                                {{ $actionLabel }}

                                            </span>

                                            @if (!empty($perm['description']))
                                                <span class="perm-desc perm-highlight">
                                                    {{ $perm['description'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <label class="switch-tabla perm-toggle"
                                            title="{{ $perm['description'] ?? '' }}">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm['id'] }}"
                                                class="toggle-estado perm-checkbox"
                                                {{ $perm['assigned'] ? 'checked' : '' }}>
                                            <span class="slider"></span>
                                        </label>
                                    </li>
                                @endforeach
                            </ul>
                        </div>

                    </div>
                @empty
                    <div class="text-muted">No hay permisos disponibles.</div>
                @endforelse
            </div>

            <div class="form-footer">
                <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                    <span class="boton-form-icon">
                        <i class="ri-arrow-left-circle-fill"></i>
                    </span>
                    <span class="boton-form-text">Cancelar</span>
                </a>
                <button class="boton-form boton-accent" type="submit" id="submitBtn">
                    <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                    <span class="boton-form-text">Guardar cambios</span>
                </button>
            </div>
        </form>
    </div>


    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {

                const searchInput = document.getElementById('searchPerm');
                const clearBtn = document.getElementById('clearSearchPerm');
                const sortSelect = document.getElementById('permissionSort');

                function handleSearch() {
                    if (!searchInput) return;

                    const val = (searchInput.value || '').toLowerCase();
                    const rows = document.querySelectorAll('.perm-row');

                    rows.forEach(row => {
                        const mod = (row.dataset.module || '').toLowerCase();
                        const act = (row.dataset.action || '').toLowerCase();
                        const match = !val || mod.includes(val) || act.includes(val);

                        row.querySelectorAll('.perm-highlight').forEach(el => {
                            const text = el.textContent;
                            if (val && text.toLowerCase().includes(val)) {
                                el.innerHTML = text.replace(new RegExp(`(${val})`, 'gi'),
                                    '<mark class="perm-mark">$1</mark>');
                            } else {
                                el.innerHTML = text;
                            }
                        });

                        row.style.display = match ? '' : 'none';
                    });

                    if (clearBtn) {
                        clearBtn.hidden = !val;
                    }
                }

                function compareRows(a, b, mode) {
                    const labelA = (a.dataset.label || '').toLowerCase();
                    const labelB = (b.dataset.label || '').toLowerCase();
                    const createdA = parseInt(a.dataset.created || '0', 10);
                    const createdB = parseInt(b.dataset.created || '0', 10);

                    switch (mode) {
                        case 'alpha_asc':
                            return labelA.localeCompare(labelB);
                        case 'alpha_desc':
                            return labelB.localeCompare(labelA);
                        case 'created_asc':
                            if (createdA === createdB) {
                                return labelA.localeCompare(labelB);
                            }
                            return createdA - createdB;
                        case 'created_desc':
                        default:
                            if (createdA === createdB) {
                                return labelA.localeCompare(labelB);
                            }
                            return createdB - createdA;
                    }
                }

                function sortPermissions(mode) {
                    const container = document.querySelector('.permissions-container');
                    if (!container) return;

                    const cards = Array.from(container.querySelectorAll('.permissions-card'));

                    cards.forEach(card => {
                        const list = card.querySelector('.card-body ul');
                        if (!list) return;

                        const items = Array.from(list.querySelectorAll('.perm-row'));
                        items.sort((a, b) => compareRows(a, b, mode));
                        items.forEach(item => list.appendChild(item));

                        const createdValues = items.map(item => parseInt(item.dataset.created || '0', 10));
                        const latest = createdValues.length ? Math.max(...createdValues) : 0;
                        const oldest = createdValues.length ? Math.min(...createdValues) : 0;
                        card.dataset.latestCreated = String(latest);
                        card.dataset.oldestCreated = String(oldest);
                    });

                    const comparator = buildCardComparator(mode);
                    if (comparator) {
                        cards.sort(comparator);
                        cards.forEach(card => container.appendChild(card));
                    }
                }

                function buildCardComparator(mode) {
                    switch (mode) {
                        case 'alpha_asc':
                            return (a, b) => (
                                (a.dataset.moduleName || '').toLowerCase()
                                    .localeCompare((b.dataset.moduleName || '').toLowerCase())
                            );
                        case 'alpha_desc':
                            return (a, b) => (
                                (b.dataset.moduleName || '').toLowerCase()
                                    .localeCompare((a.dataset.moduleName || '').toLowerCase())
                            );
                        case 'created_asc':
                            return (a, b) => {
                                const oldestA = parseInt(a.dataset.oldestCreated || '0', 10);
                                const oldestB = parseInt(b.dataset.oldestCreated || '0', 10);
                                if (oldestA === oldestB) {
                                    const nameA = (a.dataset.moduleName || '').toLowerCase();
                                    const nameB = (b.dataset.moduleName || '').toLowerCase();
                                    return nameA.localeCompare(nameB);
                                }
                                return oldestA - oldestB;
                            };
                        case 'created_desc':
                        default:
                            return (a, b) => {
                                const latestA = parseInt(a.dataset.latestCreated || '0', 10);
                                const latestB = parseInt(b.dataset.latestCreated || '0', 10);
                                if (latestA === latestB) {
                                    const nameA = (a.dataset.moduleName || '').toLowerCase();
                                    const nameB = (b.dataset.moduleName || '').toLowerCase();
                                    return nameA.localeCompare(nameB);
                                }
                                return latestB - latestA;
                            };
                    }
                }

                if (searchInput) {
                    searchInput.addEventListener('input', handleSearch);
                }

                if (clearBtn) {
                    clearBtn.addEventListener('click', () => {
                        if (!searchInput) return;
                        searchInput.value = '';
                        handleSearch();
                        searchInput.focus();
                        clearBtn.hidden = true;
                    });
                }

                if (sortSelect) {
                    sortSelect.addEventListener('change', () => {
                        sortPermissions(sortSelect.value);
                        handleSearch();
                    });
                }

                // Guardar estado original de cada permiso
                document.querySelectorAll('.perm-checkbox').forEach(cb => {
                    cb.dataset.originalChecked = cb.checked ? '1' : '0';
                });

                // --- Global toggle ---
                const toggleAllBtn = document.getElementById('toggleAllBtn');
                const toggleAllText = document.getElementById('toggleAllText');

                toggleAllBtn.addEventListener('click', function() {
                    const boxes = document.querySelectorAll('.perm-checkbox');
                    const allChecked = [...boxes].every(cb => cb.checked);

                    boxes.forEach(cb => cb.checked = !allChecked);

                    document.querySelectorAll('.permissions-card').forEach(card => {
                        card.classList.add('card-modified');
                        const resetBtn = card.querySelector('.reset-module-btn');
                        if (resetBtn) {
                            resetBtn.classList.remove('is-hidden');
                        }
                    });

                    updateAllBtnText();
                    updateModuleBtnText();
                });

                function updateAllBtnText() {
                    const boxes = document.querySelectorAll('.perm-checkbox');
                    const allChecked = [...boxes].every(cb => cb.checked);
                    toggleAllText.textContent = allChecked ? 'Desmarcar todo' : 'Marcar todo';
                }

                // --- Selección por módulo ---
                document.querySelectorAll('.select-all-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const module = btn.dataset.module;
                        const boxes = document.querySelectorAll(`.perm-row[data-module="${module}"] .perm-checkbox`);
                        const allChecked = [...boxes].every(cb => cb.checked);

                        boxes.forEach(cb => cb.checked = !allChecked);

                        const card = btn.closest('.permissions-card');
                        if (card) {
                            card.classList.add('card-modified');
                            const resetBtn = card.querySelector('.reset-module-btn');
                            if (resetBtn) {
                                resetBtn.classList.remove('is-hidden');
                            }
                        }

                        updateAllBtnText();
                        updateModuleBtnText();
                    });
                });

                // --- Restablecer por módulo ---
                document.querySelectorAll('.reset-module-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const module = btn.dataset.module;
                        const card = btn.closest('.permissions-card');
                        const boxes = card.querySelectorAll(`.perm-row[data-module="${module}"] .perm-checkbox`);

                        boxes.forEach(cb => {
                            const original = cb.dataset.originalChecked === '1';
                            cb.checked = original;
                        });

                        card.classList.remove('card-modified');
                        const resetBtn = card.querySelector('.reset-module-btn');
                        if (resetBtn) {
                            resetBtn.classList.add('is-hidden');
                        }
                        updateAllBtnText();
                        updateModuleBtnText();
                    });
                });

                function updateModuleBtnText() {
                    document.querySelectorAll('.permissions-card').forEach(card => {
                        const module = card.querySelector('.select-all-btn').dataset.module;
                        const boxes = card.querySelectorAll(`.perm-row[data-module="${module}"] .perm-checkbox`);
                        const txt = card.querySelector('.select-all-text');

                        const allChecked = [...boxes].length > 0 && [...boxes].every(cb => cb.checked);

                        txt.textContent = allChecked ? 'Deselecc. todo' : 'Selecc. todo';
                    });
                }

                document.querySelectorAll('.perm-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const card = cb.closest('.permissions-card');
                        card.classList.add('card-modified');
                        const resetBtn = card.querySelector('.reset-module-btn');
                        if (resetBtn) {
                            resetBtn.classList.remove('is-hidden');
                        }
                        updateAllBtnText();
                        updateModuleBtnText();
                    });
                });

                updateModuleBtnText();
                updateAllBtnText();
                if (sortSelect) {
                    sortPermissions(sortSelect.value);
                }
                handleSearch();

                const submitLoader = initSubmitLoader({
                    formId: 'permissionForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });
            });
        </script>
    @endpush
</x-admin-layout>
