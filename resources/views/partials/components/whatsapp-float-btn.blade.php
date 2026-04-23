@php
    $companySettings = function_exists('company_setting') ? company_setting() : null;
    $waPhone = null;
    $waMsg = $waMsg ?? '¡Hola! Quiero más información.';
    if ($companySettings && !empty($companySettings->phone)) {
        $waPhone = preg_replace('/\D+/', '', $companySettings->phone);
        if ($waPhone && strlen($waPhone) === 9 && $waPhone[0] === '9') {
            $waPhone = '51' . $waPhone;
        }
        if ($waPhone && strlen($waPhone) > 0 && substr($waPhone, 0, 2) !== '51') {
            $waPhone = '51' . $waPhone;
        }
    }
@endphp
@if ($waPhone)
    <a href="https://wa.me/{{ $waPhone }}?text={{ urlencode($waMsg) }}" class="whatsapp-float-btn" target="_blank" rel="noopener" title="Contáctanos por WhatsApp">
        <i class="ri-whatsapp-line"></i>
    </a>
@endif
