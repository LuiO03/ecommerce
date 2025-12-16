<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-add-circle-line"></i></div>
        Nuevo Rol
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.roles.index') }}" class="boton-form boton-action">
            <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
    </x-slot>

    <form action="{{ route('admin.roles.store') }}" method="POST" class="form-container" autocomplete="off" id="roleForm">
        @csrf
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

        <x-alert type="info" title="Información:" :dismissible="true" :items="['Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios.']" />

        <div class="form-row">
            <div class="form-column">
                <!-- === Nombre === -->
                <div class="input-group">
                    <label for="name" class="label-form">
                        Nombre del rol
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-shield-user-line input-icon"></i>
                        <input type="text" name="name" id="name" class="input-form" required
                            value="{{ old('name') }}" placeholder="Ingrese el nombre"
                            maxlength="100" data-validate="required|min:3|max:100|alphanumeric">
                    </div>
                </div>

                <!-- No hay campo estado para roles -->

                <!-- === Descripción === -->
                <div class="input-group">
                    <label for="description" class="label-form label-textarea">Descripción del rol</label>
                    <div class="input-icon-container">
                        <i class="ri-file-text-line input-icon"></i>
                        <textarea name="description" id="description" class="textarea-form" placeholder="Ingrese la descripción" rows="4"
                            maxlength="500" data-validate="min:10|max:500">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ url()->previous() }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"><i class="ri-paint-brush-fill"></i></span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                <span class="boton-form-text">Crear Rol</span>
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
                    loadingText: 'Guardando...'
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
</x-admin-layout>
