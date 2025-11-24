<form method="POST" action="{{ route('admin.profile.update') }}" enctype="multipart/form-data" class="space-y-4">
    @csrf
    @method('PUT')
    <div>
        <label for="name" class="block font-medium">Nombre</label>
        <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="input w-full">
    </div>
    <div>
        <label for="last_name" class="block font-medium">Apellido</label>
        <input type="text" name="last_name" id="last_name" value="{{ old('last_name', $user->last_name) }}" class="input w-full">
    </div>
    <div>
        <label for="email" class="block font-medium">Email</label>
        <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="input w-full">
    </div>
    <div>
        <label for="dni" class="block font-medium">DNI</label>
        <input type="text" name="dni" id="dni" value="{{ old('dni', $user->dni) }}" class="input w-full">
    </div>
    <div>
        <label for="phone" class="block font-medium">Teléfono</label>
        <input type="text" name="phone" id="phone" value="{{ old('phone', $user->phone) }}" class="input w-full">
    </div>
    <div>
        <label for="address" class="block font-medium">Dirección</label>
        <input type="text" name="address" id="address" value="{{ old('address', $user->address) }}" class="input w-full">
    </div>
    <div>
        <label for="image" class="block font-medium">Foto de perfil</label>
        <input type="file" name="image" id="image" class="input w-full">
        @if($user->image)
            <div class="mt-2">
                <img src="{{ $user->image_url }}" alt="Foto actual" class="w-16 h-16 rounded-full">
                <label class="ml-2"><input type="checkbox" name="remove_image" value="1"> Eliminar foto</label>
            </div>
        @endif
    </div>
    <div class="flex justify-end">
        <button type="submit" class="boton boton-primary">Guardar cambios</button>
    </div>
</form>
