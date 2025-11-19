# Image Upload Handler - Documentación

## 📦 Módulo Global para Manejo de Imágenes

Sistema reutilizable para carga, previsualización y gestión de imágenes en formularios de creación y edición.

---

## 🚀 Uso Rápido

### Modo CREATE (Crear Registro)

```javascript
const imageHandler = initImageUpload({
    mode: 'create'
});
```

### Modo EDIT (Editar Registro)

```javascript
const imageHandler = initImageUpload({
    mode: 'edit',
    hasExistingImage: true, // Booleano según si existe imagen
    existingImageFilename: 'categoria-123.jpg' // Nombre del archivo existente
});
```

---

## ⚙️ Configuración Completa

```javascript
const imageHandler = initImageUpload({
    // IDs de elementos del DOM (opcionales - usa defaults)
    inputId: 'image',                      // Input file
    previewZoneId: 'imagePreviewZone',     // Zona de previsualización
    placeholderId: 'imagePlaceholder',     // Placeholder inicial
    previewId: 'imagePreview',             // Img preview (existente)
    previewNewId: 'imagePreviewNew',       // Img preview (nueva)
    overlayId: 'imageOverlay',             // Overlay con botones
    changeBtnId: 'changeImageBtn',         // Botón cambiar
    removeBtnId: 'removeImageBtn',         // Botón eliminar
    filenameContainerId: 'imageFilename',  // Container nombre archivo
    filenameTextId: 'filenameText',        // Span nombre archivo
    errorContainerId: 'imageError',        // Container error imagen
    removeFlagId: 'removeImageFlag',       // Hidden input para flag eliminación
    
    // Configuración funcional
    mode: 'create',                        // 'create' o 'edit'
    hasExistingImage: false,               // Si hay imagen en modo edit
    existingImageFilename: ''              // Nombre archivo existente
});
```

---

## 🏗️ Estructura HTML Requerida

### Formulario CREATE

```html
<div class="image-upload-section">
    <label class="label-form">Imagen de la categoría</label>
    <input type="file" name="image" id="image" class="file-input" accept="image/*">

    <div class="image-preview-zone" id="imagePreviewZone">
        <!-- Placeholder inicial -->
        <div class="image-placeholder" id="imagePlaceholder">
            <i class="ri-image-add-line"></i>
            <p>Arrastra una imagen aquí</p>
            <span>o haz clic para seleccionar</span>
        </div>

        <!-- Preview de imagen -->
        <img id="imagePreview" class="image-preview image-pulse" 
             style="display: none;" alt="Vista previa">

        <!-- Overlay con botones -->
        <div class="image-overlay" id="imageOverlay" style="display: none;">
            <button type="button" class="overlay-btn" id="changeImageBtn">
                <i class="ri-upload-2-line"></i>
                <span>Cambiar</span>
            </button>
            <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn">
                <i class="ri-delete-bin-line"></i>
                <span>Eliminar</span>
            </button>
        </div>
    </div>

    <!-- Nombre del archivo -->
    <div class="image-filename" id="imageFilename" style="display: none;">
        <i class="ri-file-image-line"></i>
        <span id="filenameText"></span>
    </div>
</div>
```

### Formulario EDIT

```html
<div class="image-upload-section">
    <label class="label-form">Imagen de la categoría</label>
    <input type="file" name="image" id="image" class="file-input" accept="image/*">
    <input type="hidden" name="remove_image" id="removeImageFlag" value="0">

    <div class="image-preview-zone {{ $category->image ? 'has-image' : '' }}" 
         id="imagePreviewZone">
        
        @if($category->image && file_exists(public_path('storage/' . $category->image)))
            <!-- Imagen existente -->
            <img id="imagePreview" src="{{ asset('storage/' . $category->image) }}" 
                 class="image-preview" alt="Imagen actual">
        @elseif($category->image)
            <!-- Error: imagen no encontrada -->
            <div class="image-error" id="imageError">
                <i class="ri-image-line"></i>
                <p>Imagen no encontrada</p>
            </div>
        @else
            <!-- Placeholder -->
            <div class="image-placeholder" id="imagePlaceholder">
                <i class="ri-image-add-line"></i>
                <p>Arrastra una imagen aquí</p>
                <span>o haz clic para seleccionar</span>
            </div>
        @endif

        <!-- Preview nueva imagen -->
        <img id="imagePreviewNew" class="image-preview image-pulse" 
             style="display: none;" alt="Vista previa">

        <!-- Overlay -->
        <div class="image-overlay" id="imageOverlay" style="display: none;">
            <button type="button" class="overlay-btn" id="changeImageBtn">
                <i class="ri-upload-2-line"></i>
                <span>Cambiar</span>
            </button>
            <button type="button" class="overlay-btn overlay-btn-danger" id="removeImageBtn">
                <i class="ri-delete-bin-line"></i>
                <span>Eliminar</span>
            </button>
        </div>
    </div>

    <div class="image-filename" id="imageFilename" 
         style="{{ $category->image ? 'display: flex;' : 'display: none;' }}">
        <i class="ri-file-image-line"></i>
        <span id="filenameText">{{ $category->image ? basename($category->image) : '' }}</span>
    </div>
</div>
```

---

## 🎯 Funcionalidades

### ✅ Modo CREATE
- Upload de imagen con drag & drop
- Previsualización instantánea
- Botón cambiar imagen
- Botón eliminar imagen (limpia preview)
- Muestra nombre de archivo seleccionado
- Click en zona vacía abre selector

### ✅ Modo EDIT
- Muestra imagen existente al cargar
- Permite cambiar por nueva imagen
- Botón eliminar marca flag `remove_image=1`
- Restaura imagen original si se limpia nueva selección
- Muestra error si imagen no existe en servidor
- Preserva nombre de archivo original

---

## 🔧 Backend Integration

### Controller CREATE

```php
public function store(Request $request)
{
    $request->validate([
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $imagePath = null;
    if ($request->hasFile('image')) {
        $ext = $request->file('image')->getClientOriginalExtension();
        $filename = $slug . '-' . time() . '.' . $ext;
        $imagePath = 'categories/' . $filename;
        $request->file('image')->storeAs('categories', $filename, 'public');
    }

    Category::create([
        'image' => $imagePath,
        // ...otros campos
    ]);
}
```

### Controller EDIT

```php
public function update(Request $request, Category $category)
{
    $request->validate([
        'image' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
    ]);

    $imagePath = $category->image;

    // Eliminar imagen
    if ($request->input('remove_image') == '1') {
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }
        $imagePath = null;
    }
    // Nueva imagen
    elseif ($request->hasFile('image')) {
        if ($category->image && Storage::disk('public')->exists($category->image)) {
            Storage::disk('public')->delete($category->image);
        }

        $ext = $request->file('image')->getClientOriginalExtension();
        $filename = $slug . '-' . time() . '.' . $ext;
        $imagePath = 'categories/' . $filename;
        $request->file('image')->storeAs('categories', $filename, 'public');
    }

    $category->update([
        'image' => $imagePath,
        // ...otros campos
    ]);
}
```

---

## 📋 Ejemplos Reales

### Families - Create
```javascript
const imageHandler = initImageUpload({
    mode: 'create'
});
```

### Families - Edit
```javascript
const imageHandler = initImageUpload({
    mode: 'edit',
    hasExistingImage: {{ $family->image && file_exists(public_path('storage/' . $family->image)) ? 'true' : 'false' }},
    existingImageFilename: '{{ $family->image ? basename($family->image) : '' }}'
});
```

### Categories - Create
```javascript
const imageHandler = initImageUpload({
    mode: 'create'
});
```

---

## 🎨 CSS Classes Usadas

- `.image-preview-zone` - Container principal
- `.image-placeholder` - Placeholder inicial
- `.image-preview` - Imagen preview
- `.image-overlay` - Overlay con botones
- `.image-filename` - Container nombre archivo
- `.has-image` - Clase cuando hay imagen
- `.drag-over` - Clase durante drag over

---

## 🐛 Troubleshooting

### Imagen no se muestra en modo EDIT
- Verificar que `hasExistingImage` sea `true`
- Comprobar que la imagen exista en `storage/app/public/`
- Verificar el symbolic link: `php artisan storage:link`

### Drag & Drop no funciona
- Verificar que el evento `dragover` tenga `preventDefault()`
- Comprobar que el input file acepte el tipo de archivo

### Botón eliminar no funciona en modo EDIT
- Verificar que exista el hidden input `#removeImageFlag`
- Comprobar que el backend procese `$request->input('remove_image')`

---

## 📦 Archivos Relacionados

- **Módulo:** `resources/js/modules/image-upload-handler.js`
- **Import:** `resources/js/index.js`
- **CSS:** `resources/css/components/form.css` (estilos `.image-*`)
- **Ejemplos:** 
  - `resources/views/admin/families/create.blade.php`
  - `resources/views/admin/families/edit.blade.php`
  - `resources/views/admin/categories/create.blade.php`
