@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $companySettings = function_exists('company_setting')
        ? company_setting()
        : null;

    $brandingMode = $companySettings?->branding_mode ?? 'logo_and_name';

    $brandLogoUrl = null;

    if ($companySettings && filled($companySettings->logo_path)) {
        $path = ltrim($companySettings->logo_path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $brandLogoUrl = $path;
        } elseif (Storage::disk('public')->exists($path)) {
            $brandLogoUrl = asset('storage/' . $path);
        }
    }

    $brandName = filled($companySettings?->name)
        ? $companySettings->name
        : null;

    $showLogo = in_array($brandingMode, ['logo_only', 'logo_and_name']);
    $showName = in_array($brandingMode, ['name_only', 'logo_and_name']);
@endphp

@if ($brandLogoUrl || $brandName)

    {{-- Logo --}}
    @if ($showLogo && $brandLogoUrl)
        <img
            src="{{ $brandLogoUrl }}"
            alt="{{ $brandName ?? 'Logo' }}"
            class="sidebar-logo"
        >
    @endif

    {{-- Nombre --}}
    @if ($showName && $brandName)
        <div class="sidebar-logo-texto tracking-wide">
            {{ $brandName }}
        </div>
    @endif

@else

    {{-- Fallback completo --}}
    <img
        src="{{ asset('images/logos/logo-geckommerce.png') }}"
        alt="Geckommerce Logo"
        class="sidebar-logo"
    >

    <div class="sidebar-logo-texto uppercase">
        Gecko<span>mmerce</span>
    </div>

@endif
