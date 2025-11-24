<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-shield-user-line"></i></div>
        
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
        <!-- Buscador de módulos y acciones -->
        <div class="module-search">
            <i class="ri-search-eye-line"></i>
            <input type="text" id="searchPerm" placeholder="Buscar módulo o acción..." autocomplete="off">
            <i class="ri-close-circle-fill" id="clearSearchPerm" class="search-clear"></i>
        </div>
        <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" autocomplete="off">
            @csrf

            <div class="permissions-container">
                @forelse($modules as $module => $perms)
                    <div class="permissions-card">
                        <div class="card-header flex items-center justify-between mb-2">
                            <span class="card-title">
                                {{ ucfirst($module) }}
                            </span>
                            <button type="button" class="boton boton-danger select-all-btn"
                                data-module="{{ $module }}">
                                <span class="boton-icon"><i class="ri-checkbox-multiple-line"></i></span>
                                <span class="boton-text">Seleccionar todo</span>
                            </button>
                        </div>
                        <div class="card-body">
                            <ul class="p-0">
                                @foreach ($perms as $perm)
                                    <li class="perm-row perm-row-custom" data-module="{{ $module }}" data-action="{{ $perm['action'] }}">
                                        <div class="perm-info">
                                            <span class="permissions-name" title="{{ $perm['description'] ?? '' }}">{{ $perm['action'] }}</span>
                                            @if (!empty($perm['description']))
                                                <span class="perm-desc">{{ $perm['description'] }}</span>
                                            @endif
                                        </div>
                                        <label class="switch-tabla perm-toggle" title="{{ $perm['description'] ?? '' }}">
                                            <input type="checkbox" name="permissions[]" value="{{ $perm['id'] }}" class="toggle-estado perm-checkbox" {{ $perm['assigned'] ? 'checked' : '' }}>
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
            <div class="flex justify-end mt-8">
                <button type="submit" class="boton boton-primary">
                    <span class="boton-icon"><i class="ri-save-line"></i></span>
                    <span class="boton-text">Guardar cambios</span>
                </button>
            </div>
        </form>
    </div>
    </div>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Buscador de permisos
                const searchInput = document.getElementById('searchPerm');
                const clearBtn = document.getElementById('clearSearchPerm');
                searchInput.addEventListener('input', function() {
                    const val = this.value.toLowerCase();
                    document.querySelectorAll('.perm-row').forEach(row => {
                        const mod = row.getAttribute('data-module').toLowerCase();
                        const act = row.getAttribute('data-action').toLowerCase();
                        row.style.display = (mod.includes(val) || act.includes(val)) ? '' : 'none';
                    });
                });
                clearBtn.addEventListener('click', function() {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                });

                // Selección masiva por módulo
                document.querySelectorAll('.select-all-btn').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const module = btn.getAttribute('data-module');
                        const checkboxes = document.querySelectorAll('.perm-row[data-module="' +
                            module + '"] .perm-checkbox');
                        const allChecked = Array.from(checkboxes).every(cb => cb.checked);
                        checkboxes.forEach(cb => cb.checked = !allChecked);
                    });
                });
            });
        </script>
    @endpush
</x-admin-layout>
