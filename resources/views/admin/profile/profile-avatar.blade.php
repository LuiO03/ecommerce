<div id="avatarModal" class="modal-avatar hidden">
    <div class="modal-avatar-content animate-in">
        <div class="info-header bg-accent" id="avatarModalHeader">
            <h6 id="avatarModalHeaderText">Cambiar foto de perfil</h6>
            <button type="button" id="closeAvatarModal" class="info-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data"
            id="profileImageFormModal" autocomplete="off" class="modal-avatar-body ripple-card">
            @csrf
            @method('PUT')
            <input type="hidden" name="only_image" value="1">
            <div class="modal-avatar-left">
                <div class="modal-avatar-preview" id="avatarPreview" style="flex-direction: column;">
                    @if ($user->image)
                        <img src="{{ asset('storage/' . $user->image) }}" alt="Foto actual" id="avatarPreviewImg">
                    @endif
                </div>
                <div id="avatarFileName"
                    class="avatar-file-name">
                    {{ basename($user->image) }}
                </div>
            </div>
            <input type="file" name="image" id="imageModal" accept="image/*" class="d-none">
            <div class="modal-avatar-right">
                <div class="modal-avatar-actions">
                    <button type="button" id="uploadSquareBtn" class="boton boton-primary">
                        <span class="boton-icon"><i class="ri-crop-line"></i></span>
                        <span class="boton-text">Subir imagen</span>
                    </button>
                    <button type="submit" class="boton boton-accent">
                        <span class="boton-icon"><i class="ri-image-edit-fill"></i></span>
                        <span class="boton-text">Guardar foto</span>
                    </button>
                    <button type="button" id="removeAvatarBtn" class="boton boton-danger">
                        <span class="boton-icon"><i class="ri-delete-bin-line"></i></span>
                        <span class="boton-text">Quitar foto</span>
                    </button>
                </div>
                <button type="button" class="boton boton-modal-close" id="cancelButtonAvatar">
                    <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                    <span class="boton-text">Cerrar</span>
                </button>
            </div>
        </form>
        <form method="POST" action="{{ route('admin.profile.removeImage') }}" id="removeAvatarForm"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
        <div class="progress-container" id="avatarModalProgressContainer" style="display:none;">
            <div class="progress-bar" id="avatarModalProgressBar"></div>
            <div class="progress-text" id="avatarModalProgressText">Se cerrará automáticamente</div>
        </div>
    </div>
</div>

<script>
    // Modal avatar con diseño modal-info
    const avatarModal = document.getElementById('avatarModal');
    const avatarDialog = avatarModal.querySelector('.modal-avatar-content');
    const closeAvatarModalBtn = document.getElementById('closeAvatarModal');
    const cancelButtonAvatar = document.getElementById('cancelButtonAvatar');
    document.getElementById('editAvatarBtn').addEventListener('click', function() {
        avatarModal.classList.remove('hidden');
        avatarModal.classList.add('flex');
        avatarDialog.classList.remove('animate-out');
        avatarDialog.classList.add('animate-in');
    });

    // Mover modal al final del body para garantizar z-index máximo
    if (avatarModal.parentElement !== document.body) {
        document.body.appendChild(avatarModal);
    }

    function closeAvatarModal() {
        avatarDialog.classList.remove('animate-in');
        avatarDialog.classList.add('animate-out');
        setTimeout(() => {
            avatarModal.classList.add('hidden');
            avatarModal.classList.remove('flex');
        }, 250);
    }
    closeAvatarModalBtn.addEventListener('click', closeAvatarModal);
    cancelButtonAvatar.addEventListener('click', closeAvatarModal);
    avatarModal.addEventListener('click', function(e) {
        if (e.target === avatarModal) {
            closeAvatarModal();
        }
    });
    // Previsualización de imagen seleccionada
    const imageInput = document.getElementById('imageModal');
    const avatarPreview = document.getElementById('avatarPreview');
    imageInput.addEventListener('change', function(e) {
        const file = e.target.files[0];
        const fileNameDiv = document.getElementById('avatarFileName');
        if (file) {
            const reader = new FileReader();
            reader.onload = function(ev) {
                let img = avatarPreview.querySelector('img');
                let icon = avatarPreview.querySelector('i');
                if (!img) {
                    img = document.createElement('img');
                    img.id = 'avatarPreviewImg';
                    if (icon) icon.remove();
                    avatarPreview.appendChild(img);
                }
                img.src = ev.target.result;
                if (fileNameDiv) fileNameDiv.textContent = file.name;
            };
            reader.readAsDataURL(file);
        } else {
            if (fileNameDiv) fileNameDiv.textContent = '';
        }
    });
    // Botón para subir imagen 1:1
    document.getElementById('uploadSquareBtn').addEventListener('click', function() {
        imageInput.click();
    });
    // Botón quitar foto
    document.getElementById('removeAvatarBtn').addEventListener('click', function() {
        document.getElementById('removeAvatarForm').submit();
    });
</script>
