@section('title', 'Editar conductor: ' . ($driver->user?->name ?? ''))

<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-warning"><i class="ri-edit-circle-line"></i></div>
        <div class="page-edit-title">
            <span class="page-subtitle">Editar Conductor</span>
            {{ $driver->user?->name }} {{ $driver->user?->last_name }}
        </div>
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.drivers.index') }}" class="boton-form boton-accent">
            <span class="boton-form-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-form-text">Volver</span>
        </a>
        @can('conductores.delete')
            <form action="{{ route('admin.drivers.destroy', $driver) }}" method="POST" class="delete-form"
                data-entity="conductor" style="margin: 0;">
                @csrf
                @method('DELETE')
                <button class="boton boton-danger" type="submit">
                    <span class="boton-icon"><i class="ri-delete-bin-6-fill"></i></span>
                    <span class="boton-text">Eliminar</span>
                </button>
            </form>
        @endcan
    </x-slot>

    <form action="{{ route('admin.drivers.update', $driver) }}" method="POST" class="form-container" autocomplete="off"
        id="driverForm">
        @csrf
        @method('PUT')

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
        <div class="form-body">
            <div class="form-row-fill">
                <div class="input-group">
                    <label for="user_id" class="label-form">
                        Usuario
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-user-line input-icon"></i>
                        <select name="user_id" id="user_id" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un usuario</option>
                            @foreach ($users as $user)
                                <option value="{{ $user->id }}" @selected(old('user_id', $driver->user_id) == $user->id)>
                                    {{ $user->name }} {{ $user->last_name }} - {{ $user->email }}
                                </option>
                            @endforeach
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="vehicle_type" class="label-form">
                        Tipo de vehículo
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-steering-2-line input-icon"></i>
                        <select name="vehicle_type" id="vehicle_type" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un tipo</option>
                            <option value="motorcycle" @selected(old('vehicle_type', $driver->vehicle_type) === 'motorcycle')>Motocicleta</option>
                            <option value="car" @selected(old('vehicle_type', $driver->vehicle_type) === 'car')>Auto</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>

                <div class="input-group">
                    <label for="vehicle_plate" class="label-form">Placa</label>
                    <div class="input-icon-container">
                        <i class="ri-hashtag input-icon"></i>
                        <input type="text" name="vehicle_plate" id="vehicle_plate" class="input-form"
                            value="{{ old('vehicle_plate', $driver->vehicle_plate) }}" placeholder="ABC-123"
                            data-validate="max:20">
                    </div>
                </div>

                <div class="input-group">
                    <label for="phone" class="label-form">Teléfono de contacto</label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input type="text" name="phone" id="phone" class="input-form"
                            value="{{ old('phone', $driver->phone ?? $driver->user?->phone) }}"
                            placeholder="Teléfono del conductor" data-validate="max:20">
                    </div>
                </div>

                <div class="input-group">
                    <label for="status" class="label-form">
                        Estado
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-focus-2-line input-icon"></i>
                        <select name="status" id="status" class="select-form" required
                            data-validate="required|selected">
                            <option value="" disabled>Seleccione un estado</option>
                            <option value="available" @selected(old('status', $driver->status) === 'available')>Disponible</option>
                            <option value="busy" @selected(old('status', $driver->status) === 'busy')>Ocupado</option>
                            <option value="inactive" @selected(old('status', $driver->status) === 'inactive')>Inactivo</option>
                        </select>
                        <i class="ri-arrow-down-s-line select-arrow"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer">
            <a href="{{ route('admin.drivers.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon">
                    <i class="ri-arrow-left-circle-fill"></i>
                </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
                <span class="boton-form-text">Limpiar</span>
            </button>
            <button class="boton-form boton-success" type="submit" id="submitBtn">
                <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
                <span class="boton-form-text">Actualizar Conductor</span>
            </button>
        </div>
    </form>

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const submitLoader = initSubmitLoader({
                    formId: 'driverForm',
                    buttonId: 'submitBtn',
                    loadingText: 'Guardando...'
                });

                const formValidator = initFormValidator('#driverForm', {
                    validateOnBlur: true,
                    validateOnInput: false,
                    scrollToFirstError: true,
                });
            });
        </script>
    @endpush
</x-admin-layout>
