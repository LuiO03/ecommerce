# 📍 Direcciones en Perfil de Usuario (Sitio)

## 🧭 Visión General

Este módulo gestiona las **direcciones de envío** del cliente desde la sección **"Mi cuenta" → "Mis direcciones"** en el frontend público.

Objetivos:
- Permitir al usuario **agregar, editar, eliminar y marcar como principal** sus direcciones.
- Hacerlo con **UX tipo SPA ligera**: sin recargar toda la página.
- Reutilizar los mismos patrones de **validación**, **toasts** y **modales** ya usados en otros módulos.

---

## 🧱 Backend

### Modelo

- **`App\Models\Addresses`**
  - Relacionado con `User` (`user_id`).
  - Campos relevantes:
    - `type` (`home` | `office`)
    - `address_line`, `district`, `reference`
    - `receiver_name`, `receiver_last_name`, `receiver_phone`
    - `is_default` (bool)

### Controlador de Perfil (Site)

**Archivo:** `app/Http/Controllers/Site/ProfileController.php`

Métodos relevantes para direcciones:

- `index(Request $request)`
  - Carga:
    - `$user`, `$orders`, `$addresses`, `$wishlistItems`, `$sessions`.
  - Pasa `activeSection` a la vista `site.profile.index`.
  - `$addresses` se obtiene siempre (resumen + pestaña de direcciones).

- `addresses()`
  - Simplemente hace redirect a `site.profile.index` con `section=addresses`.

- `storeAddress(Request $request)`
  - Requiere usuario autenticado.
  - Valida:
    - `type`: `required|in:home,office`
    - `address_line`: `required|string|min:5|max:255`
    - `district`: `required|string|max:120`
    - `reference`: `required|string|max:255`
    - `receiver_name`: `required|string|min:3|max:255`
    - `receiver_last_name`: `nullable|string|min:2|max:255`
    - `receiver_phone`: `required|string|min:6|max:20`
  - Normaliza textos (minúsculas + `ucfirst/ucwords`).
  - Calcula `is_default` en base a si el usuario ya tenía direcciones.
  - Crea la dirección.
  - Vuelve a cargar todas las direcciones del usuario ordenadas por:
    - `is_default` DESC, `id` DESC.
  - Respuesta:
    - **AJAX (`wantsJson`)**: JSON con:
      - `status: 'success'`
      - `html`: render de `site.profile.partials.addresses` (lista completa actualizada)
      - `toast`: mensaje de éxito (tipo, título, mensaje)
    - **No AJAX**: `Session::flash('toast', ...)` y redirect a `site.profile.index?section=addresses`.

- `updateAddress(Request $request, Addresses $address)`
  - Verifica que la dirección pertenezca al usuario autenticado.
  - Valida y normaliza campos igual que `storeAddress`.
  - Hace `update()` sobre la misma fila (no crea una nueva).
  - Recarga y devuelve las direcciones como en `storeAddress` (incluyendo `toast`).

- `setDefaultAddress(Request $request, Addresses $address)`
  - Verifica ownership.
  - Pone todas las direcciones del usuario en `is_default = false` y la seleccionada en `true`.
  - Recarga direcciones ordenadas (default primero).
  - Devuelve JSON con `status`, `html` y `toast` en AJAX; o `Session::flash` + redirect en navegación normal.

- `destroyAddress(Request $request, Addresses $address)`
  - Verifica ownership.
  - Elimina la dirección.
  - Recarga direcciones y responde igual que los otros métodos (JSON con `html` + `toast`, o redirect con `flash`).

### Rutas (Site)

**Archivo:** `routes/web.php`

Dentro del grupo autenticado del sitio:

```php
Route::middleware('auth')->group(function () {
    Route::get('/mi-cuenta', [SiteProfileController::class, 'index'])->name('site.profile.index');
    // ... otras secciones (detalles, pedidos, favoritos, seguridad)

    Route::get('/mi-cuenta/direcciones', [SiteProfileController::class, 'addresses'])->name('site.profile.addresses');
    Route::post('/mi-cuenta/direcciones', [SiteProfileController::class, 'storeAddress'])->name('site.profile.addresses.store');
    Route::put('/mi-cuenta/direcciones/{address}', [SiteProfileController::class, 'updateAddress'])->name('site.profile.addresses.update');
    Route::post('/mi-cuenta/direcciones/{address}/default', [SiteProfileController::class, 'setDefaultAddress'])->name('site.profile.addresses.default');
    Route::delete('/mi-cuenta/direcciones/{address}', [SiteProfileController::class, 'destroyAddress'])->name('site.profile.addresses.destroy');
});
```

---

## 🎨 Frontend (Blade + CSS)

### Sección "Mis direcciones"

**Archivo:** `resources/views/site/profile/partials/addresses.blade.php`

- Contenedor principal:

```blade
<div class="profile-section" id="profileAddressesSection" data-store-url="{{ route('site.profile.addresses.store') }}">
    {{-- encabezado y grid de direcciones --}}
</div>
```

- El atributo `data-store-url` se usa en JS como endpoint base para **crear** direcciones.
- Cada tarjeta (`address-card`) muestra los datos de la dirección y botones de acción.

Botones importantes:

- **Agregar dirección** (abre modal en modo create):

```blade
<button type="button" class="boton-form boton-success" data-address-modal-open="create">
    {{-- ... --}}
</button>
```

- **Editar dirección** (abre modal en modo edit rellenando datos desde `data-*`):

```blade
<button
    type="button"
    class="boton-pastel card-warning address-edit-btn"
    data-address-modal-open="edit"
    data-address-id="{{ $address->id }}"
    data-address-type="{{ $address->type }}"
    data-address-line="{{ e($address->address_line) }}"
    data-address-district="{{ e($address->district) }}"
    data-address-reference="{{ e($address->reference) }}"
    data-address-receiver-name="{{ e($address->receiver_name) }}"
    data-address-receiver-last-name="{{ e($address->receiver_last_name) }}"
    data-address-receiver-phone="{{ e($address->receiver_phone) }}"
    data-update-url="{{ route('site.profile.addresses.update', $address) }}"
>
    <i class="ri-pencil-fill"></i>
</button>
```

- **Eliminar dirección** (AJAX + modal de confirmación):

```blade
<form method="POST" action="{{ route('site.profile.addresses.destroy', $address) }}" class="address-delete-form">
    @csrf
    @method('DELETE')
    <button
        type="submit"
        class="boton-pastel card-danger address-delete-btn"
        data-address-delete-url="{{ route('site.profile.addresses.destroy', $address) }}"
    >
        <i class="ri-delete-bin-5-fill"></i>
    </button>
</form>
```

- **Marcar como principal** (solo cuando no es la principal actual):

```blade
<form method="POST" action="{{ route('site.profile.addresses.default', $address) }}" class="address-default-form">
    @csrf
    <button
        type="submit"
        class="boton-pastel card-success address-default-btn"
        data-address-default-url="{{ route('site.profile.addresses.default', $address) }}"
    >
        <i class="ri-star-fill"></i>
    </button>
</form>
```

### Modal de Direcciones

**Archivo:** `resources/views/site/profile/partials/address-modal.blade.php`

- Modal flotante reutilizable para **crear/editar** direcciones:
  - `id="profileAddressModal"`
  - Formulario `id="profileAddressForm"`.
  - Campo hidden que indica método semántico (`POST`/`PUT`) vía `data-profile-address-method`.
  - Título dinámico con `data-profile-address-title`.

Campos del formulario (resumen):
- `pa_type`: select `home|office` con `data-validate="selected"`.
- `pa_address_line`: input de dirección completa `data-validate="required|min:5|max:255"`.
- `pa_district`: distrito / ciudad `data-validate="required|min:3|max:120"`.
- `pa_reference`: referencia `data-validate="required|max:255"`.
- `pa_receiver_name`: nombre del receptor `data-validate="required|min:3|max:255"`.
- `pa_receiver_last_name`: apellido del receptor `data-validate="min:2|max:255"`.
- `pa_receiver_phone`: teléfono `data-validate="required|phone|max:20"`.

### Estilos de la Modal

**Archivo:** `resources/css/site/components/profile-address-modal.css`

Puntos clave:

- `.profile-address-modal`:
  - `position: fixed; inset: 0; display: none; z-index: 60;`
  - `overflow-y: auto; padding: 1.5rem 0;`
  - `.is-visible` → `display: flex;` (centra el diálogo).

- `.profile-address-dialog`:
  - Caja blanca centrada con `max-width: 620px`.
  - `max-height: calc(100vh - 3rem); overflow-y: auto;` → la propia modal es scrollable en pantallas pequeñas.

- Bloqueo de scroll del fondo al abrir la modal:

```css
html.profile-address-modal-open,
body.profile-address-modal-open {
    overflow: hidden;
}
```

Esta clase se añade/elimina desde JS al abrir/cerrar la modal.

---

## ⚙️ Frontend (JS)

### Módulo `profile-addresses.js`

**Archivo:** `resources/js/site-modules/profile-addresses.js`

Se importa en el entrypoint del sitio `resources/js/site.js`:

```javascript
import './site-modules/profile-addresses';
```

Responsabilidades:

1. **Inicializar FormValidator** sobre `#profileAddressForm`:
   - Usa `initFormValidator('#profileAddressForm', { ... })` con:
     - `validateOnBlur: true`
     - `showSuccessIndicators: true`
     - `scrollToFirstError: true`

2. **Gestionar estados globales:**
   - `profileAddressMode`: `'create'` o `'edit'`.
   - `profileAddressUpdateUrl`: URL del endpoint de actualización (cuando se edita).
   - `profileAddressIsSubmitting`: evita submits duplicados.
   - Flags para no registrar listeners y validador múltiples veces.

3. **Abrir/Cerrar modal:**
   - `openCreateModal()` → limpia el formulario, ajusta textos y pone modo `create`.
   - `openEditModal(button)` → lee `data-*` del botón y rellena el formulario, pone modo `edit`.
   - `openModal()` / `closeModal()` → añaden/quitan `.is-visible` en la modal y la clase `profile-address-modal-open` en `<html>`/`<body>` para bloquear el scroll del fondo.

4. **Envío del formulario (crear/editar) vía AJAX:**
   - Listener `submit` en `#profileAddressForm`:
     - Valida con `FormValidator`.
     - Construye `FormData` con datos del formulario.
     - Selecciona URL:
       - **create:** `data-store-url` del contenedor `#profileAddressesSection`.
       - **edit:** `profileAddressUpdateUrl` leído del botón de editar.
     - Envía `POST` con `_method = 'POST'` o `'PUT'`.
     - Espera JSON `{ status, html, toast }`.
     - Reemplaza **completa** la sección `#profileAddressesSection` con `data.html`.
     - Cierra la modal (`closeModal()`).
     - Si existe `toast`, lo pasa a `window.showToast(toast)`.
     - Llama a `setTimeout(setupProfileAddresses, 0)` para reenganchar listeners a la nueva sección.

5. **Eliminar y marcar como principal (AJAX + modal global de confirmación):**
   - Escucha clics en:
     - `.address-delete-btn`
     - `.address-default-btn`
   - **Eliminar:**
     - Muestra `window.showConfirm({...})` (definido en `partials/admin/modal-confirm.blade.php`).
     - En `onConfirm`, llama a `sendAddressRequest(url, { method: 'DELETE' })`.
   - **Marcar como principal:**
     - Llama directamente a `sendAddressRequest(url, { method: 'POST' })`.
   - `sendAddressRequest`:
     - Envía `POST` con `_token` y `_method` según sea necesario.
     - Reemplaza de nuevo `#profileAddressesSection` con `data.html`.
     - Si hay `data.toast`, lo muestra con `window.showToast`.
     - Vuelve a ejecutar `setupProfileAddresses()`.

> 💡 Gracias a que el backend siempre devuelve el **partial completo** de direcciones, tanto crear/editar/eliminar como marcar como principal mantienen la lista totalmente sincronizada sin recargar la página.

---

## 🔁 Resumen de Flujo (Crear / Editar)

1. Usuario hace clic en **"Agregar dirección"** o **"Editar"**:
   - JS abre la modal en modo correspondiente y prepara los datos.
2. Usuario completa el formulario:
   - FormValidator valida campos y muestra feedback visual.
3. Al hacer submit:
   - Se envía petición AJAX a `storeAddress` o `updateAddress`.
   - El backend valida, guarda y responde con HTML + toast.
4. JS reemplaza la sección de direcciones, cierra la modal y muestra el toast.

---

## 🧪 Buenas Prácticas

- Mantener la estructura de `data-*` de los botones de editar en sincronía con los campos del formulario de la modal.
- Si se agregan nuevos campos a `Addresses`, extender tanto el backend (validación + fill) como:
  - `address-modal.blade.php`
  - Relleno en `fillFormFromDataset` de `profile-addresses.js`
  - Atributos `data-*` de los botones de editar en `addresses.blade.php`.
- Usar siempre los endpoints existentes (`storeAddress`, `updateAddress`, `setDefaultAddress`, `destroyAddress`) en lugar de crear rutas duplicadas.
