<div id="profileAddressModal" class="profile-address-modal" aria-hidden="true">
    <div class="profile-address-backdrop" data-profile-address-close></div>

    <div class="profile-address-dialog" role="dialog" aria-modal="true" aria-labelledby="profileAddressModalTitle">
        <button type="button" class="profile-address-close" data-profile-address-close aria-label="Cerrar">
            <i class="ri-close-line"></i>
        </button>

        <div class="card-header">
            <span class="card-title" id="profileAddressModalTitle" data-profile-address-title>Agregar dirección</span>
            <p class="card-description">Completa los datos de envío para guardar esta dirección en tu cuenta.</p>
        </div>

        <form id="profileAddressForm" class="profile-address-form" novalidate>
            @csrf
            <input type="hidden" name="_method" value="POST" data-profile-address-method>

            <div class="form-row-fit">
                <div class="input-group">
                    <label class="label-form" for="pa_type">
                        Tipo de dirección <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-home-4-line input-icon"></i>
                        <select id="pa_type" class="select-form" name="type" data-validate="selected">
                            <option value="home">Casa</option>
                            <option value="office">Oficina</option>
                        </select>
                    </div>
                </div>

                <div class="input-group">
                    <label class="label-form" for="pa_address_line">
                        Dirección completa <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-map-pin-line input-icon"></i>
                        <input id="pa_address_line" type="text" class="input-form" name="address_line"
                               placeholder="Av. Siempre Viva 742, Interior 3"
                               data-validate="required|min:5|max:255" autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="form-row-fit">
                <div class="input-group">
                    <label class="label-form" for="pa_district">
                        Distrito / Ciudad <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-building-2-line input-icon"></i>
                        <input id="pa_district" type="text" class="input-form" name="district"
                               placeholder="Ej: Miraflores, Lima" data-validate="required|min:3|max:120"
                               autocomplete="off" />
                    </div>
                </div>

                <div class="input-group">
                    <label class="label-form" for="pa_reference">
                        Referencia <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-map-pin-2-line input-icon"></i>
                        <textarea id="pa_reference" class="input-form" name="reference"
                                  placeholder="Ej: Casa de fachada azul, portón negro, cerca al parque"
                                  data-validate="required|max:255"></textarea>
                    </div>
                </div>
            </div>

            <h4 class="profile-address-section-title">Datos personales</h4>

            <div class="form-row-fill">
                <div class="input-group">
                    <label class="label-form" for="pa_receiver_name">
                        Nombre completo del receptor <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-user-3-line input-icon"></i>
                        <input id="pa_receiver_name" type="text" class="input-form" name="receiver_name"
                               placeholder="Nombre de quien recibirá"
                               data-validate="required|min:3|max:255" autocomplete="off" />
                    </div>
                </div>
                <div class="input-group">
                    <label class="label-form" for="pa_receiver_last_name">
                        Apellido del receptor
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-user-3-line input-icon"></i>
                        <input id="pa_receiver_last_name" type="text" class="input-form" name="receiver_last_name"
                               placeholder="Apellido de quien recibirá" data-validate="min:2|max:255"
                               autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="form-row-fill">
                <div class="input-group">
                    <label class="label-form" for="pa_receiver_phone">
                        Teléfono de contacto <i class="ri-asterisk text-accent"></i>
                    </label>
                    <div class="input-icon-container">
                        <i class="ri-phone-line input-icon"></i>
                        <input id="pa_receiver_phone" type="text" class="input-form" name="receiver_phone"
                               placeholder="Celular o teléfono de contacto"
                               data-validate="required|phone|max:20" autocomplete="off" />
                    </div>
                </div>
            </div>

            <div class="profile-address-footer">
                <button type="button" class="site-btn site-btn-outline" data-profile-address-close>
                    Cancelar
                </button>
                <button type="submit" class="site-btn site-btn-primary" id="profileAddressSubmitBtn">
                    Guardar dirección
                </button>
            </div>
        </form>
    </div>
</div>
