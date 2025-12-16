<div id="avatarModal" class="modal-avatar hidden">
    <div class="modal-avatar-content animate-in">
        <div class="info-header bg-accent" id="avatarModalHeader">
            <h6 id="avatarModalHeaderText">Cambiar foto de perfil</h6>
            <button type="button" id="closeAvatarModal" class="info-close ripple-btn">
                <i class="ri-close-line"></i>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data"
            id="profileImageFormModal" autocomplete="off" class="modal-avatar-body">
            @csrf
            @method('PUT')
            <input type="hidden" name="only_image" value="1">
            <div class="modal-avatar-left">
                <div class="modal-avatar-preview" id="avatarPreview">
                    @php
                        $exists = $user->image && Storage::disk('public')->exists($user->image);
                        $registered = $user->image != null;
                    @endphp

                    @if ($exists)
                        {{-- Imagen válida --}}
                        <img src="{{ asset('storage/' . $user->image) }}" alt="Foto actual" id="avatarPreviewImg">
                    @elseif ($registered && !$exists)
                        {{-- Imagen registrada pero perdida --}}
                        <i class="ri-file-close-line"></i>
                    @else
                        {{-- Sin imagen --}}
                        <i class="ri-user-4-fill"></i>
                    @endif
                </div>

                <div id="avatarFileName" class="avatar-file-name">
                    @if ($exists)
                        {{ basename($user->image) }}
                    @else
                        Sin imagen
                    @endif
                </div>
            </div>
            <input type="file" name="image" id="imageModal" accept="image/*" class="hidden">
            <div class="modal-avatar-actions">
                <button type="button" id="uploadSquareBtn" class="boton-form boton-primary">
                    <span class="boton-form-icon"><i class="ri-image-circle-ai-fill"></i></span>
                    <span class="boton-form-text">Subir imagen</span>
                </button>
                <hr class="w-full my-0 border-default">
                <button type="submit" class="boton-form boton-accent">
                    <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
                    <span class="boton-form-text">Guardar foto</span>
                </button>
                <hr class="w-full my-0 border-default">
                @if ($exists)
                    <button type="button" id="removeAvatarBtn" class="boton-form boton-danger">
                        <span class="boton-form-icon"><i class="ri-delete-bin-fill"></i></span>
                        <span class="boton-form-text">Quitar foto</span>
                    </button>
                @endif
            </div>
        </form>
        <div class="modal-show-footer">
            <button type="button" class="boton boton-modal-close" id="cancelButtonAvatar">
                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                <span class="boton-text">Cerrar</span>
            </button>
        </div>
        <form method="POST" action="{{ route('admin.profile.removeImage') }}" id="removeAvatarForm"
            style="display:none;">
            @csrf
            @method('DELETE')
        </form>
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
