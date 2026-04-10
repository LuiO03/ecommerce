<div class="profile-section">
    <div class="card-header">
        <span class="card-title">Seguridad de la cuenta</span>
        <p class="card-description">Revisa tu correo, contraseña y opciones de acceso.</p>
    </div>

    <div class="security-grid">
        <article class="security-card">
            <div class="security-card-icon">
                <i class="ri-mail-line"></i>
            </div>
            <div class="security-card-body">
                <h3>Correo de acceso</h3>
                <p>{{ $user->email }}</p>
                <p class="overview-muted">Este es el correo que utilizas para iniciar sesión.</p>
            </div>
        </article>

        <article class="security-card">
            <div class="security-card-icon">
                <i class="ri-lock-2-line"></i>
            </div>
            <div class="security-card-body">
                <h3>Contraseña</h3>
                <p class="overview-muted">Por motivos de seguridad, no mostramos tu contraseña.</p>
                <a href="{{ route('site.profile.details') }}#password-section" class="security-link">
                    <i class="ri-arrow-right-up-line"></i>
                    Cambiar contraseña desde panel seguro
                </a>
            </div>
        </article>
    </div>
</div>
