@push('styles')
    @vite('resources/css/modules/profile.css')
@endpush

<x-admin-layout :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-danger">
            <i class="ri-user-3-line"></i>
        </div>
        Perfil de usuario
    </x-slot>
    <div class="profile-container">
        <div class="profile-header">
            <div class="profile-avatar-container {{ $user->background_style && $user->background_style !== '' ? $user->background_style : 'fondo-estilo-2' }}"
                style="position: relative;">
                @php
                    $profileHasImage = $user->image && Storage::disk('public')->exists($user->image);
                @endphp
                @if ($profileHasImage)
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

            <div class="text-center">
                <h1>{{ $user->name }} {{ $user->last_name }}</h1>
                <p class="profile-description">
                    Gestiona tu <strong>información personal</strong>, seguridad y descargas.
                </p>
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
               <button class="profile-tab-btn" data-tab="sessions" title="Sesiones activas">
                   <i class="ri-device-line"></i>
                   <span class="profile-tab-text">Sesiones</span>
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
               <div id="tab-sessions" class="profile-tab-content hidden">
                   @include('admin.profile.profile-sessions')
               </div>
        </div>
    </div>
    <!-- Modal para cambiar imagen -->
    @include('admin.profile.profile-avatar')

    <script>
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
                   // Guardar tab activo en localStorage
                   localStorage.setItem('profileActiveTab', this.dataset.tab);
            });
        });
            // Activar el tab guardado en localStorage o el primero por defecto
            const savedTab = localStorage.getItem('profileActiveTab');
            let initialTab = savedTab || 'info';
            if(window.location.hash === '#sessions') initialTab = 'sessions';
            document.querySelectorAll('.profile-tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById('tab-' + initialTab).classList.remove('hidden');
            document.getElementById('tab-' + initialTab).classList.add('fade-in');
            document.querySelectorAll('.profile-tab-btn').forEach(b => b.classList.remove('active'));
            document.querySelector('.profile-tab-btn[data-tab="' + initialTab + '"]').classList.add('active');
    </script>
</x-admin-layout>
