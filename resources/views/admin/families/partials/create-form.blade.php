<small class="form-warning">
    Los campos con asterisco (<i class="ri-asterisk text-accent"></i>) son obligatorios.
</small>
<form id="familyForm" action="{{ route('admin.families.store') }}" method="POST" enctype="multipart/form-data" class="form-container" autocomplete="off">
    @csrf
    <div class="flex w-full gap-4">
        <div class="w-6/12 flex gap-4 flex-col">
            <!-- === Nombre === -->
            <div class="input-group">
                <i class="ri-price-tag-3-line input-icon"></i>
                <input type="text" name="name" id="name" class="input-form" placeholder=" " required>
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
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
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
                <textarea name="description" id="description" class="textarea-form" placeholder=" " rows="4"></textarea>
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

    <div class="form-footer">
        <button type="reset" class="boton-form boton-warning">
            <span class="boton-form-icon"> <i class="ri-paint-brush-fill"></i> </span>
            <span class="boton-form-text">Limpiar</span>
        </button>

        <button class="boton-form boton-success" type="submit">
            <span class="boton-form-icon"> <i class="ri-save-3-fill"></i> </span>
            <span class="boton-form-text">Crear Familia</span>
        </button>
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
</form>
