@props(['url'])

@php
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Str;

    $companySettings = function_exists('company_setting')
        ? company_setting()
        : null;

    $mailLogoUrl = null;

    if ($companySettings && filled($companySettings->logo_path)) {
        $path = ltrim($companySettings->logo_path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $mailLogoUrl = $path;
        } elseif (Storage::disk('public')->exists($path)) {
            $mailLogoUrl = asset('storage/' . $path);
        }
    }

    $companyDisplayName = filled($companySettings?->name)
        ? $companySettings->name
        : null;

    $brandingMode = $companySettings?->branding_mode ?? 'logo_and_name';

    $showLogo = in_array($brandingMode, ['logo_only', 'logo_and_name']);
    $showName = in_array($brandingMode, ['name_only', 'logo_and_name']);
@endphp

<tr>
    <td>
        <table class="header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <a href="{{ $url }}" style="display: inline-block; text-decoration: none;">

                        @if (trim($slot) === 'Geckommerce')
                            <div class="logo-container">

                                {{-- LOGO --}}
                                @if ($showLogo && $mailLogoUrl)
                                    <img
                                        src="{{ $mailLogoUrl }}"
                                        class="logo"
                                        alt="{{ $companyDisplayName ?? 'Logo' }}"
                                    >
                                @endif

                                {{-- NOMBRE --}}
                                @if ($showName && $companyDisplayName)
                                    <div class="logo-texto">
                                        {{ $companyDisplayName }}
                                    </div>
                                @endif

                                {{-- FALLBACK SISTEMA --}}
                                @if (!$mailLogoUrl && !$companyDisplayName)
                                    <img
                                        src="https://luio03.github.io/muniyauyos.github.io/imagen/logo-geckommerce.png"
                                        class="logo"
                                        alt="Geckommerce Logo"
                                    >

                                    <div class="logo-texto">
                                        <strong>Gecko</strong><span>mmerce</span>
                                    </div>
                                @endif

                            </div>
                        @else
                            {!! $slot !!}
                        @endif

                    </a>
                </td>
            </tr>
        </table>
    </td>
</tr>
