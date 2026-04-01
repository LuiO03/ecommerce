@props(['url'])

@php
    // Usar configuración de empresa si existe (helper company_setting) para logo/nombre en correos
    $companySettings = function_exists('company_setting') ? company_setting() : null;

    $mailLogoUrl = null;

    if ($companySettings && $companySettings->logo_path) {
        $path = ltrim($companySettings->logo_path, '/');

        if (Str::startsWith($path, ['http://', 'https://'])) {
            $mailLogoUrl = $path;
        } elseif (Storage::disk('public')->exists($path)) {
            $mailLogoUrl = asset('storage/' . $path);
        }
    }

    $companyDisplayName = $companySettings->name ?? null;
@endphp

<tr>
    <td>
        <table class="header" align="center" width="570" cellpadding="0" cellspacing="0" role="presentation">
            <tr>
                <td align="center">
                    <a href="{{ $url }}" style="display: inline-block;">
                        @if (trim($slot) === 'Geckommerce')
                            <div class="logo-container">
                                @if ($mailLogoUrl || $companyDisplayName)
                                    @if ($mailLogoUrl)
                                        <img src="{{ $mailLogoUrl }}" class="logo" alt="{{ $companyDisplayName ?? 'Logo' }}">
                                    @else
                                        <img src="https://luio03.github.io/muniyauyos.github.io/imagen/logo-geckommerce.png" class="logo" alt="Geckommerce Logo">
                                    @endif

                                    @if ($companyDisplayName)
                                        <div class="logo-texto">{{ $companyDisplayName }}</div>
                                    @else
                                        <div class="logo-texto"><strong>Gecko</strong><span>mmerce</span></div>
                                    @endif
                                @else
                                    <img src="https://luio03.github.io/muniyauyos.github.io/imagen/logo-geckommerce.png" class="logo" alt="Geckommerce Logo">
                                    <div class="logo-texto"><strong>Gecko</strong><span>mmerce</span></div>
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
