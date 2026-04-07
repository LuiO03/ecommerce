@php
    $companySettings = function_exists('company_setting') ? company_setting() : null;

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
    @if ($brandLogoUrl)
        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName ?? 'Logo' }}" class="sidebar-logo">
    @else
        <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo" class="sidebar-logo">
    @endif

    @if ($brandName)
        <div class="sidebar-logo-texto tracking-wide">{{ $brandName }}</div>
    @else
        <div class="sidebar-logo-texto uppercase">Gecko<span>mmerce</span></div>
    @endif
@else
    <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo" class="sidebar-logo">
    <div class="sidebar-logo-texto uppercase">Gecko<span>mmerce</span></div>
@endif
