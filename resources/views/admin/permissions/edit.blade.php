<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-info"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Permiso</span>
            {{ $permission->name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.permissions.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
        <form action="{{ route('admin.permissions.destroy', $permission) }}" method="POST" class="delete-form" data-entity="permiso" style="margin: 0;">
            @csrf
            @method('DELETE')
            <button class="boton boton-danger" type="submit">
                <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-text">Eliminar</span>
            </button>
        </form>
    </x-slot>

    <form action="{{ route('admin.permissions.update', $permission) }}" method="POST" class="form-container" autocomplete="off" id="permissionForm">
        @csrf
        @method('PATCH')

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-row">
            <div class="form-column">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre del permiso
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-key-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name', $permission->name) }}" placeholder="Ingrese el nombre"
                            maxlength="100" data-validate="required|min:3|max:100|alphanumeric">
                    </div>
                </div>

                <!-- === Módulo === -->
                <div class="input-group">
                    <label for="modulo" class="label-form">Módulo <i class="ri-asterisk text-accent"></i></label>
                    <div class="input-icon-container">
                        <i class="ri-apps-line input-icon"></i>
                        <input type="text" name="modulo" id="modulo" class="input-form" required
                            value="{{ old('modulo', $permission->modulo) }}" placeholder="Ejemplo: productos, usuarios, categorías"
                            maxlength="50" data-validate="required|min:3|max:50|alphanumeric">
                    </div>
                    @error('modulo')
                        <x-alert type="danger" :items="[$message]" />
                    @enderror
                </div>

                <!-- === Descripción === -->
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción del permiso</label>
                    <div class="input-icon-container">
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            maxlength="500" data-validate="min:10|max:500">{{ old('description', $permission->description) }}</textarea>
                        <i class="ri-file-text-line input-icon"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.permissions.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button class="boton-form boton-accent" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Permiso</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Inicializar submit loader PRIMERO
                const submitLoader = initSubmitLoader({
                    formId: 'permissionForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Actualizando...'
                });

                // 2. Inicializar validación de formulario DESPUÉS
                const formValidator = initFormValidator('#permissionForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true
                });
            });
        </script>
    @endpush
</x-admin-layout>
</x-admin-layout>