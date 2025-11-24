<form method="POST" action="{{ route('admin.profile.password') }}" class="space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label for="current_password" class="block font-medium">Contraseña actual</label>
        <input type="password" name="current_password" id="current_password" class="input w-full">
    </div>
    <div>
        <label for="password" class="block font-medium">Nueva contraseña</label>
        <input type="password" name="password" id="password" class="input w-full">
    </div>
    <div>
        <label for="password_confirmation" class="block font-medium">Confirmar nueva contraseña</label>
        <input type="password" name="password_confirmation" id="password_confirmation" class="input w-full">
    </div>
    <div class="flex justify-end">
        <button type="submit" class="boton boton-success">Cambiar contraseña</button>
    </div>
</form>
