@section('title', 'Editar rol: ' . $role->name)

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Rol</span>
            {{ $role->name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.roles.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
        <form action="{{ route('admin.roles.destroy', $role) }}" method="POST" class="delete-form" data-entity="rol"
            style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton-form boton-danger" type="submit">
                <span class="boton-form-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-form-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.roles.update', $role) }}" method="POST" class="form-container" autocomplete="off"
        id="roleForm">
        @csrf
        @method('PATCH')

        {{-- Banner de errores de backend (solo si JS fue omitido o falló) --}}
        @if ($errors->any())
            <div class="form-error-banner">
                <i class="ri-error-warning-line form-error-icon"></i>
                <div>
                    <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        @endif

        <x-note-alert type="info" :dismissible="true">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </x-note-alert>

        <div class="form-user">
            <!-- listar usuarios con este rol -->
            <div class="form-body">
                <div class="card-header">
                    <span class="card-title">Usuarios con este rol</span>
                    <p class="card-description">
                        Hay {{ $role->users_count }} {{ Str::plural('usuario', $role->users_count) }} asignados a este
                        rol.
                    </p>
                </div>
                @forelse($users as $user)
                    <div class="card-list-item">
                        <div class="card-list-tabla-info">
                            <div class="user-info">
                                @if ($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="{{ $user->name }}"
                                        class="user-avatar"
                                        onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="user-avatar-placeholder"
                                        style="display: none; background: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}">
                                        {{ $user->initials }}
                                    </div>
                                @else
                                    <div class="user-avatar-placeholder"
                                        style="background: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}">
                                        {{ $user->initials }}
                                    </div>
                                @endif
                                <strong>{{ $user->name }} {{ $user->last_name }}</strong>
                            </div>
                            <small>{{ $user->email }}</small>

                            @if ($user->status)
                                <span class="badge badge-success">
                                    <i class="ri-checkbox-circle-fill"></i>
                                    Activo
                                </span>
                            @else
                                <span class="badge badge-danger">
                                    <i class="ri-close-circle-fill"></i>
                                    Inactivo
                                </span>
                            @endif
                        </div>
                        <div class="card-list-tabla-botones">
                            <button type="button" class="boton-sm boton-info btn-ver-usuario"
                                data-slug="{{ $user->slug }}" title="Ver Usuario">
                                <i class="ri-eye-2-fill"></i>
                            </button>
                            @can('usuarios.edit')
                                <a href="{{ route('admin.users.edit', $user) }}" class="boton-sm boton-warning"
                                    title="Editar Usuario">
                                    <i class="ri-edit-circle-fill"></i>
                                </a>
                            @endcan
                        </div>
                    </div>
                @empty
                    <div class="data-empty">
                        <i class="ri-user-3-line"></i>
                        <span>No hay usuarios asignados a este rol.</span>
                    </div>
                @endforelse
            </div>
            <div class="form-body">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre del rol
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-shield-user-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $role->name) }}" placeholder="Ingrese el nombre" maxlength="100"
                            data-validate="required|min:3|max:100|alphanumeric">
                    </div>
                </div>

                <!-- No hay campo estado para roles -->

                <!-- === Descripción === -->
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción del rol</label>
                    <div class="input-icon-container">
                        <i class="ri-file-text-line input-icon"></i>
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            maxlength="500" data-validate="min:10|max:500">{{ old('description', $role->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.roles.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Atrás</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Rol</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'roleForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#roleForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
    @include('admin.users.modals.show-modal-user')
</x-admin-layout>
