<form method="POST" action="{{ route('admin.company-settings.update-social') }}" id="companySettingsSocialForm">
    @csrf
    @if ($errors->hasBag('social') && $errors->social->any())
        <div class="form-error-banner">
            <i class="ri-error-warning-line form-error-icon"></i>
            <div>
                <h4 class="form-error-title">Se encontraron los siguientes errores:</h4>
                <ul>
                    @foreach ($errors->social->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif
    <section id="companySettingsSectionSocial" data-section="social" role="tabpanel" aria-labelledby="tab-social"
        class="container-section">
        <div class="form-body">
            <div class="card-header">
                <span class="card-title">Redes sociales</span>
                <p class="card-description">
                    Configura los enlaces a las redes sociales de tu empresa y controla su visibilidad en el sitio web.
                </p>
            </div>
            <div class="form-row-fit">
                <div class="inputs-column">
                    <div class="input-group">
                        <label for="facebook_url" class="label-form">Facebook</label>
                        <div class="input-icon-container">
                            <i class="ri-facebook-circle-fill input-icon"></i>
                            <input type="url" name="facebook_url" id="facebook_url" class="input-form"
                                value="{{ old('facebook_url', $setting->facebook_url) }}"
                                placeholder="https://facebook.com/empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>

                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="facebook_enabled" value="0">

                                <input type="checkbox" name="facebook_enabled" id="facebook_enabled" class="switch-input" value="1"
                                    {{ old('facebook_enabled', $setting->facebook_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="facebook_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="facebook_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="instagram_url" class="label-form">Instagram</label>
                        <div class="input-icon-container">
                            <i class="ri-instagram-fill input-icon"></i>
                            <input type="url" name="instagram_url" id="instagram_url" class="input-form"
                                value="{{ old('instagram_url', $setting->instagram_url) }}"
                                placeholder="https://instagram.com/empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>
                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="instagram_enabled" value="0">

                                <input type="checkbox" name="instagram_enabled" id="instagram_enabled" class="switch-input" value="1"
                                    {{ old('instagram_enabled', $setting->instagram_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="instagram_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="instagram_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="twitter_url" class="label-form">Twitter (X)</label>
                        <div class="input-icon-container">
                            <i class="ri-twitter-x-fill input-icon"></i>
                            <input type="url" name="twitter_url" id="twitter_url" class="input-form"
                                value="{{ old('twitter_url', $setting->twitter_url) }}"
                                placeholder="https://x.com/empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>
                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="twitter_enabled" value="0">

                                <input type="checkbox" name="twitter_enabled" id="twitter_enabled" class="switch-input" value="1"
                                    {{ old('twitter_enabled', $setting->twitter_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="twitter_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="twitter_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="inputs-column">
                    <div class="input-group">
                        <label for="youtube_url" class="label-form">YouTube</label>
                        <div class="input-icon-container">
                            <i class="ri-youtube-fill input-icon"></i>
                            <input type="url" name="youtube_url" id="youtube_url" class="input-form"
                                value="{{ old('youtube_url', $setting->youtube_url) }}"
                                placeholder="https://youtube.com/@empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>
                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="youtube_enabled" value="0">

                                <input type="checkbox" name="youtube_enabled" id="youtube_enabled" class="switch-input" value="1"
                                    {{ old('youtube_enabled', $setting->youtube_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="youtube_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="youtube_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="tiktok_url" class="label-form">TikTok</label>
                        <div class="input-icon-container">
                            <i class="ri-tiktok-fill input-icon"></i>
                            <input type="url" name="tiktok_url" id="tiktok_url" class="input-form"
                                value="{{ old('tiktok_url', $setting->tiktok_url) }}"
                                placeholder="https://www.tiktok.com/@empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>
                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="tiktok_enabled" value="0">

                                <input type="checkbox" name="tiktok_enabled" id="tiktok_enabled" class="switch-input" value="1"
                                    {{ old('tiktok_enabled', $setting->tiktok_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="tiktok_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="tiktok_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>

                    <div class="input-group">
                        <label for="linkedin_url" class="label-form">LinkedIn</label>
                        <div class="input-icon-container">
                            <i class="ri-linkedin-box-fill input-icon"></i>
                            <input type="url" name="linkedin_url" id="linkedin_url" class="input-form"
                                value="{{ old('linkedin_url', $setting->linkedin_url) }}"
                                placeholder="https://www.linkedin.com/company/empresa" data-validate="url|max:255">
                        </div>
                        <div class="social-toggle">
                            <span class="social-toggle-label">Visibilidad</span>
                            <div class="binary-switch">
                                <!-- Checkbox real -->
                                <input type="hidden" name="linkedin_enabled" value="0">

                                <input type="checkbox" name="linkedin_enabled" id="linkedin_enabled" class="switch-input" value="1"
                                    {{ old('linkedin_enabled', $setting->linkedin_enabled) == 1 ? 'checked' : '' }} data-validate="required">

                                <!-- Labels visuales -->
                                <label for="linkedin_enabled" class="switch-label switch-label-on">
                                    <i class="ri-checkbox-circle-line"></i> Mostrar
                                </label>

                                <label for="linkedin_enabled" class="switch-label switch-label-off">
                                    <i class="ri-close-circle-line"></i> Ocultar
                                </label>

                                <div class="switch-slider"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-footer-static">
            <a href="{{ route('admin.dashboard') }}" class="boton-form boton-volver">
                <span class="boton-form-icon"><i class="ri-home-smile-2-fill"></i></span>
                <span class="boton-form-text">Volver al inicio</span>
            </a>
            <button class="boton-form boton-accent" type="submit" id="socialSubmitBtn">
                <span class="boton-form-icon"><i class="ri-save-3-line"></i></span>
                <span class="boton-form-text">Guardar Información</span>
            </button>
        </div>
    </section>
</form>
@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            initSubmitLoader({
                formId: 'companySettingsSocialForm',
                buttonId: 'socialSubmitBtn',
                loadingText: 'Actualizando...'
            });

            initFormValidator('#companySettingsSocialForm', {
                validateOnBlur: true,
                validateOnInput: false,
                scrollToFirstError: true
            });
        });
    </script>
@endpush
