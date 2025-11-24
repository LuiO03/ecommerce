<form method="POST" action="{{ route('admin.profile.password') }}" class="profile-form">
    @csrf
    @method('PUT')
    <div class="profile-field">
        <label for="current_password">Contraseña actual</label>
        <input type="password" name="current_password" id="current_password" class="input">
    </div>
    <div class="profile-field">
        <label for="password">Nueva contraseña</label>
        <input type="password" name="password" id="password" class="input">
    </div>
    <div class="profile-field">
        <label for="password_confirmation">Confirmar nueva contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="input">
    </div>
    <div class="profile-actions">
        <button type="submit" class="boton boton-success">Cambiar contraseña</button>
    </div>
</form>
