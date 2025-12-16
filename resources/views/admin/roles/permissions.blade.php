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

    <div class="actions-container">

        <!-- Buscador + botón global -->
        <div class="flex items-center gap-4 mb-2">
            <div class="module-search">
                <i class="ri-search-eye-line"></i>
                <input type="text" id="searchPerm" placeholder="Buscar módulo o acción..." autocomplete="off">
                <i class="ri-close-circle-fill" id="clearSearchPerm"></i>
            </div>

            <button type="button" class="boton boton-accent" id="toggleAllBtn">
                <span class="boton-icon"><i class="ri-checkbox-multiple-blank-fill"></i></span>
                <span class="boton-text" id="toggleAllText">Marcar todo</span>
            </button>
        </div>

        <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" autocomplete="off" class="form-container">
            @csrf

            <div class="permissions-container">
                @forelse($modules as $module => $perms)
                    <div class="permissions-card ripple-card">
                        <div class="card-header flex items-center justify-between mb-2">
                            <span class="card-title">{{ ucfirst($module) }}</span>

                            <button type="button" class="boton boton-danger select-all-btn" data-module="{{ $module }}">
                                <span class="boton-icon"><i class="ri-checkbox-multiple-fill"></i></span>
                                <span class="boton-text select-all-text">Selecc. todo</span>
                            </button>
                        </div>

                        <div class="card-body">
                            <ul class="p-0">
                                @foreach ($perms as $perm)
                                    <li class="perm-row perm-row-custom"
                                        data-module="{{ $module }}"
                                        data-action="{{ explode('.', $perm['action'])[1] ?? $perm['action'] }}
"
                                    >
                                        <div class="perm-info">
                                            <span class="permissions-name perm-highlight"
                                                title="{{ $perm['description'] ?? '' }}">
                                                {{ explode('.', $perm['action'])[1] ?? $perm['action'] }}

                                            </span>

                                            @if (!empty($perm['description']))
                                                <span class="perm-desc perm-highlight">
                                                    {{ $perm['description'] }}
                                                </span>
                                            @endif
                                        </div>

                                        <label class="switch-tabla perm-toggle"
                                            title="{{ $perm['description'] ?? '' }}">
                                            <input type="checkbox"
                                                name="permissions[]"
                                                value="{{ $perm['id'] }}"
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

            // --- Buscador dinámico ---
            searchInput.addEventListener('input', function() {
                const val = this.value.toLowerCase();

                document.querySelectorAll('.perm-row').forEach(row => {
                    const mod = row.dataset.module.toLowerCase();
                    const act = row.dataset.action.toLowerCase();

                    let match = mod.includes(val) || act.includes(val);

                    // Resaltar coincidencias
                    row.querySelectorAll('.perm-highlight').forEach(el => {
                        const text = el.textContent;
                        if (val && text.toLowerCase().includes(val)) {
                            el.innerHTML = text.replace(new RegExp(`(${val})`, 'gi'), '<mark class="perm-mark">$1</mark>');
                        } else {
                            el.innerHTML = text;
                        }
                    });

                    row.style.display = match ? '' : 'none';
                });
            });

            clearBtn.addEventListener('click', () => {
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
            });

            // --- Global toggle ---
            const toggleAllBtn = document.getElementById('toggleAllBtn');
            const toggleAllText = document.getElementById('toggleAllText');

            toggleAllBtn.addEventListener('click', function() {
                const boxes = document.querySelectorAll('.perm-checkbox');
                const allChecked = [...boxes].every(cb => cb.checked);

                boxes.forEach(cb => cb.checked = !allChecked);

                document.querySelectorAll('.permissions-card').forEach(card =>
                    card.classList.add('card-modified')
                );

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
                    if (card) card.classList.add('card-modified');

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
                    updateAllBtnText();
                    updateModuleBtnText();
                });
            });

            updateModuleBtnText();
            updateAllBtnText();
        });
    </script>
    @endpush
</x-admin-layout>
