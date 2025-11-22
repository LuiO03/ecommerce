
<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-primary"><i class="ri-shield-user-line"></i></div>
        Gestione permisos
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.roles.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>
    <div class="w-full">
        <div class="card-body">
            {{-- Notificación por toast, no banner --}}
            <h2 class="mb-4">Rol: <span class="badge badge-primary">{{ $role->name }}</span></h2>
            <form method="POST" action="{{ route('admin.roles.update-permissions', $role) }}" autocomplete="off">
                @csrf
                <table class="table table-permissions-modal w-full">
                    <thead>
                        <tr>
                            <th>Módulo</th>
                            <th>Permisos</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($modules as $module => $perms)
                            <tr>
                                <td><strong>{{ $module }}</strong></td>
                                <td>
                                    <div class="">
                                        @foreach($perms as $perm)
                                            <label class="switch-tabla">
                                                <input type="checkbox" name="permissions[]" value="{{ $perm['id'] }}" class="toggle-estado" {{ $perm['assigned'] ? 'checked' : '' }}>
                                                <span class="slider"></span>
                                            </label>
                                            <span class="badge badge-accent">{{ $perm['action'] }}</span>
                                        @endforeach
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="2" class="text-gray-400">No hay permisos disponibles.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
                <div class="flex justify-end mt-6">
                    <button type="submit" class="boton boton-primary">
                        <span class="boton-icon"><i class="ri-save-line"></i></span>
                        <span class="boton-text">Guardar cambios</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>