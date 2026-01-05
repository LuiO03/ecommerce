# GalleryManager - Gestor de galerías de imágenes

## Descripción general

El módulo **gallery-manager.js** centraliza la gestión de galerías de imágenes tanto para **posts** como para **productos**.

Incluye:
- Dropzone personalizada (click, drag & drop, pegado desde portapapeles).
- Reordenamiento por drag & drop con animaciones.
- Marcado de imagen principal (portada) y sincronización con el backend.
- Integración con el validador de formularios para mostrar índices visuales correctos (#N).

**Archivo JS:** `resources/js/utils/gallery-manager.js`

Exporta distintos inicializadores, registrados globalmente en `resources/js/index.js`:

```js
import {
  initPostGalleryCreate,
  initPostGalleryEdit,
  initProductGalleryCreate,
  initProductGalleryEdit,
  initGalleryCreateWithConfig,
  initGalleryEditWithConfig,
} from './utils/gallery-manager.js';

window.initPostGalleryCreate = initPostGalleryCreate;
// ... etc
```

## Patrón general de galería

Cada inicializador asume:

- Un contenedor tipo dropzone (`customDropzone` o configurable).
- Un `<input type="file" multiple>` asociado.
- Un contenedor de previsualización (`previewContainer`).
- Un input oculto para indicar la imagen principal (`primaryImageInput`).

Internamente:

- Mantiene un array `galleryFiles` con `{ file, key }`.
- Usa `DataTransfer` para reconstruir `input.files` cuando se reordena o elimina una imagen.
- Gestiona estados especiales para la imagen principal (badge, desactivar drag, etc.).

## Inicializadores principales

### 1. `initPostGalleryCreate()`

Pensado para la creación de posts.

IDs esperados:

- `customDropzone`: contenedor clickable/drag & drop.
- `imageInput`: `<input type="file" multiple>`.
- `previewContainer`: contenedor de `.preview-item`.
- `primaryImageInput`: `<input type="hidden" name="primary_image">`.

Comportamiento:

- Permite seleccionar imágenes por:
  - Click en la dropzone.
  - Drag & drop.
  - Pegado desde portapapeles.
- Evita duplicados basándose en (nombre, tamaño, lastModified).
- Reordena por drag sobre el handle `.drag-handle`.
- Permite marcar una imagen como portada (mueve la portada al inicio con animación y actualiza `primaryImageInput`).

### 2. `initPostGalleryEdit()`

Similar a `initPostGalleryCreate`, pero soporta:

- Imágenes existentes (`data-type="existing"`) con IDs persistentes.
- Marcar imágenes para eliminación (rellenando un campo `deletedImages`).
- Mantener la portada actual y recalcularla tras eliminar.

### 3. `initProductGalleryCreate()` / `initProductGalleryEdit()`

Versiones equivalentes para productos, con IDs de elementos adaptados al formulario de productos.

### 4. `initGalleryCreateWithConfig(config)` / `initGalleryEditWithConfig(config)`

Inicializadores genéricos con configuración explícita:

```js
initGalleryCreateWithConfig({
  dropzoneId: 'myDropzone',
  inputId: 'myInput',
  previewContainerId: 'myPreview',
  primaryInputId: 'primaryImageInput',
  formId: 'myForm',
});
```

Permiten reutilizar el gestor de galería en otros módulos manteniendo el mismo comportamiento.

## Estructura de previsualización

Cada imagen se representa como:

```html
<div class="preview-item" data-key="new-..." data-type="new|existing">
  <button type="button" class="drag-handle">…</button>
  <img src="..." alt="Nombre del archivo">
  <div class="overlay">
    <span class="file-size">123 KB</span>
    <div class="overlay-actions">
      <button type="button" class="mark-main-btn">Portada</button>
      <button type="button" class="delete-btn">Eliminar</button>
    </div>
  </div>
  <span class="primary-badge">Portada</span>
</div>
```

El módulo se encarga de:

- Actualizar la numeración visual (badges de índice) a través de `galleryUpdateIndexBadges`.
- Sincronizar el input de archivo y el orden de `galleryFiles` tras cada cambio.

## Integración con FormValidator

El módulo registra una entrada en `window._galleryRegistries` vía `registerGalleryRegistry(...)`:

- Permite que el validador de formularios mapee un objeto `File` al índice visual (#N) actual.
- Útil para mostrar mensajes de error del tipo "La imagen #3 supera el tamaño máximo".

## Buenas prácticas

- Usar siempre los IDs esperados por cada inicializador o, en su defecto, los inicializadores con configuración.
- Mantener el CSS de `.preview-item`, `.drag-handle`, `.overlay`, `.primary-badge` alineado con la UX del dashboard.
- Asegurarse de enviar correctamente al backend:
  - Imágenes nuevas vía `imageInput[]`.
  - IDs de imágenes existentes a eliminar.
  - Referencia de portada (`primary_image`).
