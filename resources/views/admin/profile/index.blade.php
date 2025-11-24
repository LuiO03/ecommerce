@push('styles')
    @vite('resources/css/modules/profile.css')
@endpush
<x-admin-layout :showMobileFab="true">
<div class="max-w-2xl mx-auto mt-8">
    <div class="flex flex-col items-center">
        <img src="{{ $user->image_url ?? asset('images/no-image.png') }}" alt="Foto de perfil" class="w-32 h-32 rounded-full shadow mb-4">
        <h2 class="text-2xl font-bold mb-1">{{ $user->name }} {{ $user->last_name }}</h2>
        <span class="text-gray-500 mb-4">{{ $user->email }}</span>
    </div>
    <div class="mt-6">
        <div class="flex justify-center gap-4 mb-6">
            <button class="tab-btn" data-tab="info">Información</button>
            <button class="tab-btn" data-tab="password">Cambiar contraseña</button>
            <button class="tab-btn" data-tab="export">Descargar datos</button>
        </div>
        <div id="tab-info" class="tab-content">
            @include('admin.profile.profile-info')
        </div>
        <div id="tab-password" class="tab-content hidden">
            @include('admin.profile.profile-password')
        </div>
        <div id="tab-export" class="tab-content hidden">
            @include('admin.profile.profile-export')
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            document.querySelectorAll('.tab-content').forEach(tab => tab.classList.add('hidden'));
            document.getElementById('tab-' + this.dataset.tab).classList.remove('hidden');
        });
    });
</script>
</x-admin-layout>
