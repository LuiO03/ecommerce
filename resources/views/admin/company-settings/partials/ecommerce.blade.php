<form method="POST" action="{{ route('admin.company-settings.update-shipping') }}" id="companySettingsShippingForm">
    @csrf
    <section id="companySettingsSectionShipping" data-section="shipping" role="tabpanel" aria-labelledby="tab-shipping"
        class="container-section">

        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Costos de envío</span>
                <p class="card-description">
                    Define los montos que aplican por tipo de entrega durante el checkout.
                </p>
            </div>

            <div class="form-row-fit">
                <div class="input-group">
                    <label for="shipping_cost_delivery" class="label-form">
                        Costo de delivery
                        <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-truck-line input-icon"></i>
                        <input type="number" step="0.01" min="0" max="9999.99" name="shipping_cost_delivery"
                            id="shipping_cost_delivery" class="input-form" required
                            value="{{ old('shipping_cost_delivery', $setting->shipping_cost_delivery ?? config('products.shipping_cost_delivery', 5)) }}"
                            placeholder="Ej. 5.00" data-validate="required|numeric|min:0|max:9999.99">
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer-static">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="shippingSubmitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
                <span class="boton-form-text">Guardar costos</span>
            </button>
        </div>
    </section>
</form>

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSubmitLoader({
                formId: 'companySettingsShippingForm',
                buttonId: 'shippingSubmitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#companySettingsShippingForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        });
    </script>
@endpush
