@php
    $companySettings = function_exists('company_setting')
        ? company_setting()
        : null;

    $brandLogoUrl = null;

    if ($companySettings && $companySettings->logo_path) {
        $path = ltrim($companySettings->logo_path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $brandLogoUrl = $path;
        } elseif (Storage::disk('public')->exists($path)) {
            $brandLogoUrl = asset('storage/' . $path);
        }
    }

    $brandName = $companySettings->name ?? null;
@endphp

@if ($brandLogoUrl || $brandName)

    {{-- Logo personalizado --}}
    @if ($brandLogoUrl)
        <img
            src="{{ $brandLogoUrl }}"
            alt="{{ $brandName ?? 'Logo' }}"
            class="sidebar-logo"
        >
    @endif

    {{-- Nombre personalizado --}}
    @if ($brandName)
        <div class="sidebar-logo-texto tracking-wide">
            {{ $brandName }}
        </div>
    @endif

@else

    {{-- Fallback completo del sistema --}}
    <img
        src="{{ asset('images/logos/logo-geckommerce.png') }}"
        alt="Geckommerce Logo"
        class="sidebar-logo"
    >

    <div class="sidebar-logo-texto uppercase">
        Gecko<span>mmerce</span>
    </div>

@endif
