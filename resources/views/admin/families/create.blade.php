<x-admin-layout>
    <x-slot name="title">
        Agregar Familia
    </x-slot>

    <form action="{{ route('admin.families.store') }}" method="POST" enctype="multipart/form-data" class="form-container"
        autocomplete="off">
        @csrf
        <small class="form-aviso">
            Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
        </small>
        <div class="flex w-full gap-4">
            <div class="w-6/12 flex gap-4 flex-col">
                <!-- === Nombre === -->
                <div class="input-group">
                    <i class="ri-price-tag-3-line input-icon"></i>
                    <input type="text" name="name" id="name" class="input-form" placeholder=" " required
                        value="{{ old('name') }}">
                    <label for="name" class="label-form">
                        Nombre de la familia
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                </div>
                <!-- === Estado === -->
                <div class="input-group select-group">
                    <i class="ri-focus-2-line input-icon"></i>
                    <select name="status" id="status" class="select-form" required>
                        <option value="" disabled selected hidden></option>
                        <option value="1" {{ old('status') == '1' ? 'selected' : '' }}>Activo</option>
                        <option value="0" {{ old('status') == '0' ? 'selected' : '' }}>Inactivo</option>
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
                    <textarea name="description" id="description" class="textarea-form" placeholder=" " rows="4" required>{{ old('description') }}</textarea>
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

        <div class="form-footer">
            <a href="{{ route('admin.families.index') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"> <i class="ri-arrow-left-circle-fill"></i> </span>
                <span class="boton-form-text">Cancelar</span>
            </a>
            <!-- boton para limpiar contenido -->
            <button type="reset" class="boton-form boton-warning">
                <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
                <span class="boton-form-text">Limpiar</span>
            </button>

            <button class="boton-form boton-success" type="submit">
                <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
                <span class="boton-form-text">Crear Familia</span>
            </button>
        </div>
    </form>
</x-admin-layout>
