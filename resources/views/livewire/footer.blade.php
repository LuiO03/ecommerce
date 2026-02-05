<footer class="site-footer">
    <div class="site-footer-container">
        <div class="site-footer-top">
            <div class="footer-brand">
                <div class="footer-logo">
                    <span class="footer-logo-mark">G</span>
                    <span class="footer-logo-text">GeckoMerce</span>
                </div>
                <p class="footer-description">
                    Tu tienda online con productos seleccionados, envíos rápidos y una experiencia de compra sin fricciones.
                </p>
                <div class="footer-social">
                    @php
                        $socialPlatforms = [
                            'facebook' => ['icon' => 'ri-facebook-fill', 'label' => 'Facebook'],
                            'instagram' => ['icon' => 'ri-instagram-line', 'label' => 'Instagram'],
                            'tiktok' => ['icon' => 'ri-tiktok-line', 'label' => 'TikTok'],
                            'twitter' => ['icon' => 'ri-twitter-x-line', 'label' => 'X'],
                            'youtube' => ['icon' => 'ri-youtube-line', 'label' => 'YouTube'],
                            'linkedin' => ['icon' => 'ri-linkedin-fill', 'label' => 'LinkedIn'],
                        ];
                    @endphp

                    @foreach ($socialPlatforms as $key => $platform)
                        @php
                            $url = $companySettings?->socialLink($key);
                        @endphp
                        @if (!empty($url))
                            <a href="{{ $url }}" class="footer-social-link" aria-label="{{ $platform['label'] }}" target="_blank" rel="noopener noreferrer">
                                <i class="{{ $platform['icon'] }}"></i>
                            </a>
                        @endif
                    @endforeach
                </div>
            </div>

            <div class="footer-links">
                <h4 class="footer-title">Tienda</h4>
                <ul>
                    <li><a href="#">Nuevos</a></li>
                    <li><a href="#">Ofertas</a></li>
                    <li><a href="#">Categorías</a></li>
                    <li><a href="#">Más vendidos</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4 class="footer-title">Ayuda</h4>
                <ul>
                    <li><a href="#">Centro de ayuda</a></li>
                    <li><a href="#">Envíos y devoluciones</a></li>
                    <li><a href="#">Métodos de pago</a></li>
                    <li><a href="#">Garantías</a></li>
                </ul>
            </div>

            <div class="footer-links">
                <h4 class="footer-title">Empresa</h4>
                <ul>
                    <li><a href="#">Sobre nosotros</a></li>
                    <li><a href="#">Trabaja con nosotros</a></li>
                    <li><a href="#">Blog</a></li>
                    <li><a href="#">Contacto</a></li>
                </ul>
            </div>

            <div class="footer-newsletter">
                <h4 class="footer-title">Recibe ofertas</h4>
                <p>Suscríbete y recibe novedades antes que nadie.</p>
                <form class="footer-form" action="#" method="POST">
                    <input type="email" name="email" placeholder="Tu correo" aria-label="Correo" required>
                    <button type="submit">Suscribirme</button>
                </form>
                <div class="footer-badges">
                    <span class="footer-badge"><i class="ri-shield-check-line"></i> Compra segura</span>
                    <span class="footer-badge"><i class="ri-truck-line"></i> Envíos rápidos</span>
                </div>
            </div>
        </div>

        <div class="site-footer-bottom">
            <p>© {{ date('Y') }} GeckoMerce. Todos los derechos reservados.</p>
            <div class="footer-bottom-links">
                <a href="#">Privacidad</a>
                <a href="#">Términos</a>
                <a href="#">Cookies</a>
            </div>
        </div>
    </div>
</footer>
