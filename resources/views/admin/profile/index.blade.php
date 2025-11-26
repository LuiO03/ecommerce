@push('styles')
    @vite('resources/css/modules/profile.css')
@endpush

<x-admin-layout :useSlotContainer="false">
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar-container {{ $user->background_style && $user->background_style !== '' ? $user->background_style : 'fondo-estilo-2' }}" style="position: relative;">
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
            <div id="avatarModal" class="modal-avatar">
                <div class="modal-avatar-content">
                    <button type="button" id="closeAvatarModal" class="modal-avatar-close"><i class="ri-close-line"></i></button>
                    <form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" id="profileImageFormModal" autocomplete="off">
                        @csrf
                        @method('PUT')
                        <div class="modal-avatar-form">
                            <div class="modal-avatar-preview">
                                @if ($user->image)
                                    <img src="{{ asset('storage/' . $user->image) }}" alt="Foto actual">
                                @else
                                    <i class="ri-user-3-line modal-avatar-preview-icon"></i>
                                @endif
                            </div>
                            <input type="file" name="image" id="imageModal" accept="image/*" class="input-form modal-avatar-input">
                        </div>
                        <div class="form-footer">
                            <button type="submit" class="boton-form boton-accent">
                                <span class="boton-form-icon"><i class="ri-image-edit-fill"></i></span>
                                <span class="boton-form-text">Guardar foto</span>
                            </button>
                        </div>
                    </form>
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
                // Modal avatar
                document.getElementById('editAvatarBtn').addEventListener('click', function() {
                    document.getElementById('avatarModal').style.display = 'flex';
                });
                document.getElementById('closeAvatarModal').addEventListener('click', function() {
                    document.getElementById('avatarModal').style.display = 'none';
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
