@push('styles')
    @vite('resources/css/modules/profile.css')
@endpush

<x-admin-layout :useSlotContainer="false">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar-container {{ $user->background_style && $user->background_style !== '' ? $user->background_style : 'fondo-estilo-2' }}"
                style="position: relative;">
                @if ($user->image)
                    <img src="{{ asset('storage/' . $user->image) }}" alt="Foto de perfil" class="profile-avatar">
                @else
                    <div class="profile-avatar"
                        style="background-color: {{ $user->avatar_colors['background'] }}; color: {{ $user->avatar_colors['color'] }}; border-color: {{ $user->avatar_colors['color'] }}; display: flex; align-items: center; justify-content: center; font-size: 4.5rem; font-weight: bold;">
                        <i class="ri-user-3-line"></i>
                    </div>
                @endif
                <!-- Botón flotante para cambiar imagen -->
                <button id="editAvatarBtn" type="button" class="boton-form boton-accent avatar-edit-btn">
                    <i class="ri-image-edit-fill avatar-edit-icon"></i>
                </button>
            </div>
            <!-- Modal para cambiar imagen -->
            <div id="avatarModal" class="info-modal hidden">
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
                        <div class="modal-avatar-preview">
                            @if ($user->image)
                                <img src="{{ asset('storage/' . $user->image) }}" alt="Foto actual">
                            @else
                                <i class="ri-user-4-line modal-avatar-preview-icon"></i>
                            @endif
                        </div>
                        <input type="file" name="image" id="imageModal" accept="image/*"
                            class="hidden">
                        <div class="modal-avatar-actions">
                            <button type="button" id="uploadSquareBtn" class="boton boton-primary">
                                <span class="boton-icon"><i class="ri-crop-line"></i></span>
                                <span class="boton-text">Subir imagen</span>
                            </button>
                            <button type="submit" class="boton boton-accent">
                                <span class="boton-icon"><i class="ri-image-edit-fill"></i></span>
                                <span class="boton-text">Guardar foto</span>
                            </button>
                            <button type="button" class="boton boton-modal-close" id="cancelButtonAvatar">
                                <span class="boton-icon text-base"><i class="ri-close-line"></i></span>
                                <span class="boton-text">Cerrar</span>
                            </button>
                        </div>
                    </form>
                    <div class="progress-container" id="avatarModalProgressContainer" style="display:none;">
                        <div class="progress-bar" id="avatarModalProgressBar"></div>
                        <div class="progress-text" id="avatarModalProgressText">Se cerrará automáticamente</div>
                    </div>
                </div>
            </div>
            <div class="text-center">
                <h1>{{ $user->name }} {{ $user->last_name }}</h1>
                <span class="profile-rol">{{ $user->role_list ?: 'Sin rol' }}</span>
                <span class="profile-description">
                    Gestiona tu <strong>información personal</strong>, seguridad y descargas.
                </span>
            </div>
        </div>
        <div class="profile-tabs">
            <button class="profile-tab-btn" data-tab="info" title="Información">
                <i class="ri-user-3-line"></i>
                <span class="profile-tab-text">Información</span>
            </button>
            <button class="profile-tab-btn" data-tab="password" title="Cambiar contraseña">
                <i class="ri-lock-password-line"></i>
                <span class="profile-tab-text">Cambiar contraseña</span>
            </button>
            <button class="profile-tab-btn" data-tab="export" title="Descargar datos">
                <i class="ri-download-line"></i>
                <span class="profile-tab-text">Descargar datos</span>
            </button>
        </div>
        <div class="profile-content">
            <div id="tab-info" class="profile-tab-content">
                @include('admin.profile.profile-info')
            </div>
            <div id="tab-password" class="profile-tab-content hidden">
                @include('admin.profile.profile-password')
            </div>
            <div id="tab-export" class="profile-tab-content hidden">
                @include('admin.profile.profile-export')
            </div>
        </div>
    </div>

    <script>
        // Modal avatar con diseño modal-info
        const avatarModal = document.getElementById('avatarModal');
        const avatarDialog = avatarModal.querySelector('.info-dialog');
        const closeAvatarModalBtn = document.getElementById('closeAvatarModal');
        document.getElementById('editAvatarBtn').addEventListener('click', function() {
            avatarModal.classList.remove('hidden');
            avatarModal.classList.add('flex');
            avatarDialog.classList.remove('animate-out');
            avatarDialog.classList.add('animate-in');
        });
        closeAvatarModalBtn.addEventListener('click', function() {
            avatarDialog.classList.remove('animate-in');
            avatarDialog.classList.add('animate-out');
            setTimeout(() => {
                avatarModal.classList.add('hidden');
                avatarModal.classList.remove('flex');
            }, 250);
        });
        avatarModal.addEventListener('click', function(e) {
            if (e.target === avatarModal) {
                avatarDialog.classList.remove('animate-in');
                avatarDialog.classList.add('animate-out');
                setTimeout(() => {
                    avatarModal.classList.add('hidden');
                    avatarModal.classList.remove('flex');
                }, 250);
            }
        });
        // Botón para subir imagen 1:1
        document.getElementById('uploadSquareBtn').addEventListener('click', function() {
            alert('Funcionalidad para subir imagen 1:1 (cuadrada) aquí.');
            // Aquí puedes integrar cropper.js o lógica para forzar imagen cuadrada
        });
        document.querySelectorAll('.profile-tab-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.profile-tab-content').forEach(tab => {
                    tab.classList.add('hidden');
                    tab.classList.remove('fade-in');
                });
                const activeTab = document.getElementById('tab-' + this.dataset.tab);
                activeTab.classList.remove('hidden');
                activeTab.classList.add('fade-in');
                document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
                this.classList.add('active');
            });
        });
        // Activar el primer tab por defecto
        document.querySelector('.profile-tab-btn[data-tab="info"]').classList.add('active');
    </script>
</x-admin-layout>
