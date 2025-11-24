@push('styles')
    @vite('resources/css/modules/profile.css')
@endpush

<x-admin-layout :useSlotContainer="false">
<div class="profile-container">
    <div class="profile-header">
        <img src="{{ $user->image_url ?? asset('images/no-image.png') }}" alt="Foto de perfil" class="profile-avatar">
        <h1>{{ $user->name }} {{ $user->last_name }}</h1>
        <span class="profile-email">{{ $user->email }}</span>
        <span class="profile-description">
            Gestiona tu <strong>información personal</strong>, seguridad y descargas.
        </span>
    </div>
    <div class="profile-tabs">
        <button class="profile-tab-btn" data-tab="info">
            <i class="ri-user-3-line"></i>
            Información
        </button>
        <button class="profile-tab-btn" data-tab="password">
            <i class="ri-lock-password-line"></i>
            Cambiar contraseña
        </button>
        <button class="profile-tab-btn" data-tab="export">
            <i class="ri-download-line"></i>
            Descargar datos
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
    document.querySelectorAll('.profile-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.profile-tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
        });
    });
</script>
</x-admin-layout>
