<x-admin-layout>
    <x-slot name="title">
        Editar {{ $family->name }}
    </x-slot>

    <!-- === FORMULARIO DE ACTUALIZACIÓN === -->
    <form action="{{ route('admin.families.update', $family) }}" method="POST" enctype="multipart/form-data"
        class="form-container" autocomplete="off">
        @csrf
        @method('PUT')

        <small class="form-aviso">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </small>

        <div class="flex w-full gap-4">
            <div class="w-6/12 flex gap-4 flex-col">
                <!-- === Nombre === -->
                <div class="input-group">
                    <i class="ri-price-tag-3-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form" placeholder=" " required
                        value="{{ old('name', $family->name) }}">
                    <label for="name" class="label-form">
                        Nombre de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                </div>

                <!-- === Estado === -->
                <div class="input-group select-group">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" id="status" class="select-form" required>
                        <option value="" disabled hidden></option>
                        <option value="1" {{ old('status', $family->status) == '1' ? 'selected' : '' }}>Activo
                        </option>
                        <option value="0" {{ old('status', $family->status) == '0' ? 'selected' : '' }}>Inactivo
                        </option>
                    </select>
                    <label for="status" class="label-form">
                        Estado de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <i class="ri-arrow-down-s-line select-arrow"></i>
                </div>

                <!-- === Descripción === -->
                <div class="input-group">
                    <i class="ri-file-text-line input-icon"></i>
                    <textarea name="description" id="description" class="textarea-form" placeholder=" " rows="4" required>{{ old('description', $family->description) }}</textarea>
                    <label for="description" class="label-form label-textarea">Descripción de la familia</label>
                </div>
            </div>

            <div class="w-6/12 flex gap-2 flex-col">
                <!-- === Imagen === -->
                <div class="file-group">
                    <input type="file" name="image" id="image" class="file-input" accept="image/*">
                    <label for="image" class="file-label">
                        <span class="file-text">Seleccionar imagen</span>
                        <i class="ri-upload-2-line upload-icon"></i>
                    </label>
                </div>
            </div>
        </div>

        <!-- === FOOTER DE ACCIONES === -->
        <div class="form-footer">
            <a href="{{ route('admin.families.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-arrow-left-circle-fill"></i></span>
                <span class="boton-form-text">Cancelar</span>
            </a>

            <button class="boton-form boton-accent" type="submit">
                <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
                <span class="boton-form-text">Actualizar Familia</span>
            </button>
        </div>
    </form>
    <hr class="w-full my-0 border-default">
    <!-- === FORMULARIO SEPARADO PARA ELIMINAR === -->
    <div class="form-footer-delete">
        <form action="{{ route('admin.families.destroy', $family) }}" method="POST" class="delete-form" data-entity="familia">
            @csrf
            @method('DELETE')
            <button class="boton-form boton-danger" type="submit">
                <span class="boton-form-icon"><i class="ri-delete-bin-6-fill"></i></span>
                <span class="boton-form-text">Eliminar Familia</span>
            </button>
        </form>
    </div>
    <script>
        document.getElementById('image').addEventListener('change', function() {
            const fileLabel = document.querySelector('.file-label');
            const fileText = fileLabel.querySelector('.file-text');
            if (this.files && this.files[0]) {
                fileText.textContent = this.files[0].name;
                fileLabel.classList.add('selected');
            } else {
                fileText.textContent = 'Seleccionar imagen';
                fileLabel.classList.remove('selected');
            }
        });
    </script>
</x-admin-layout>
