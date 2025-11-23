<x-admin-layout>
    <x-slot name="title">
        <div class="page-icon card-success"><i class="ri-key-line"></i></div>
        Nuevo Permiso
    </x-slot>
    <x-slot name="action">
        <a href="{{ route('admin.permissions.index') }}" class="boton boton-secondary">
            <span class="boton-icon"><i class="ri-arrow-go-back-line"></i></span>
            <span class="boton-text">Volver</span>
        </a>
    </x-slot>
    <div class="card max-w-lg mx-auto">
        <div class="card-body">
            <form action="{{ route('admin.permissions.store') }}" method="POST" autocomplete="off">
                @csrf
                <div class="mb-4">
                    <label for="name" class="form-label">Nombre del permiso</label>
                    <input type="text" name="name" id="name" class="form-input" value="{{ old('name') }}" required maxlength="255">
                    @error('name')
                        <x-alert type="danger" :items="[$message]" />
                    @enderror
                </div>
                <div class="mb-4">
                    <label for="description" class="form-label">Descripción</label>
                    <input type="text" name="description" id="description" class="form-input" value="{{ old('description') }}" maxlength="255" placeholder="Breve descripción del permiso">
                    @error('description')
                        <x-alert type="danger" :items="[$message]" />
                    @enderror
                </div>
                <div class="flex justify-end gap-2">
                    <button type="submit" class="boton boton-primary">
                        <span class="boton-icon"><i class="ri-save-line"></i></span>
                        <span class="boton-text">Guardar</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-admin-layout>