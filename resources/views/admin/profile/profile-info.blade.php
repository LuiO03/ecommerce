<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="profile-form">
    @csrf
    @method('PUT')
    <div class="profile-field">
        <label for="name">Nombre</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="last_name">Apellido</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="email">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="dni">DNI</label>
        <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="phone">Teléfono</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="address">Dirección</label>
        <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="input">
    </div>
    <div class="profile-field">
        <label for="image">Foto de perfil</label>
        <input type="file" name="image" id="image" class="input">
        @if($user->image)
            <div class="profile-avatar-preview">
                <img src="{{ $user->image_url }}" alt="Foto actual" class="profile-avatar-small">
                <label><input type="checkbox" name="remove_image" value="1"> Eliminar foto</label>
            </div>
        @endif
    </div>
    <div class="profile-actions">
        <button type="submit" class="boton boton-primary">Guardar cambios</button>
    </div>
</form>
