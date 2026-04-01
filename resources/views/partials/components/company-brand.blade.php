

@if ($brandLogoUrl || $brandName)
    @if ($brandLogoUrl)
        <img src="{{ $brandLogoUrl }}" alt="{{ $brandName ?? 'Logo' }}" class="sidebar-logo-default">
    @else
        <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo" class="sidebar-logo-default">
    @endif

    @if ($brandName)
        <div class="sidebar-logo-texto tracking-wide"><strong>{{ $brandName }}</strong></div>
    @else
        <div class="sidebar-logo-texto"><strong>Gecko</strong><span>mmerce</span></div>
    @endif
@else
    <img src="{{ asset('images/logos/logo-geckommerce.png') }}" alt="Logo" class="sidebar-logo-default">
    <div class="sidebar-logo-texto"><strong>Gecko</strong><span>mmerce</span></div>
@endif
