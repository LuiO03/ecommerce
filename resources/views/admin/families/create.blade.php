<x-admin-layout>
    <x-slot name="title">
        Crear Nueva Familia
    </x-slot>

    <form action="{{ route('admin.families.store') }}" method="POST" class="form-container" autocomplete="off">
        @csrf

        <!-- === Nombre === -->
        <div class="input-group">
            <i class="ri-price-tag-3-line input-icon"></i>
            <input type="text" name="name" id="name" class="input-form" placeholder=" " required
                value="{{ old('name') }}">
            <label for="name" class="label-form">Nombre de la familia</label>
        </div>

        <!-- === Descripción === -->
        <div class="input-group">
            <i class="ri-file-text-line input-icon"></i>
            <textarea name="description" id="description" class="textarea-form" placeholder=" " rows="4" required>{{ old('description') }}</textarea>
            <label for="description" class="label-form label-textarea">Descripción de la familia</label>
        </div>

        <div class="flex justify-end gap-2 mt-6">
            <x-button type="button">Cancelar</x-button>
            <x-button type="submit">Crear Familia</x-button>
        </div>
    </form>
</x-admin-layout>
