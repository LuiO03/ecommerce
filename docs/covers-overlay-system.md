# Gestión de Portadas - Campos de Overlay

## Descripción

Las portadas (covers) ahora soportan texto dinámico superpuesto sobre las imágenes sin necesidad de editar los archivos gráficos. Esto permite:

- ✅ Cambiar mensajes sin re-subir imágenes
- ✅ Personalizar posiciones de texto (9 posiciones con selector visual)
- ✅ Agregar botones CTA (Call-To-Action) con enlaces
- ✅ Preview en tiempo real mientras editas
- ✅ Alineación automática según posición
- ✅ Mejor SEO (texto indexable)
- ✅ Multiidioma más sencillo

## Campos Agregados

### Texto Superpuesto

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `overlay_text` | `text` | No | Texto principal a mostrar sobre la imagen (máx. 500 caracteres) |
| `overlay_subtext` | `text` | No | Texto secundario/complementario (máx. 500 caracteres) |
| `text_position` | `enum` | No | Posición del texto en la imagen. Opciones: `top-left`, `top-center`, `top-right`, `center-left`, `center-center` (defecto), `center-right`, `bottom-left`, `bottom-center`, `bottom-right` |
| `text_color` | `string` | No | Color HEX del texto (formato `#FFFFFF`, defecto: `#FFFFFF`) |

### Botón CTA

| Campo | Tipo | Requerido | Descripción |
|-------|------|-----------|-------------|
| `button_text` | `string` | Condicional* | Texto del botón (máx. 100 caracteres). Ej: "Comprar ahora", "Ver más" |
| `button_link` | `string` | Condicional* | URL destino del botón. Debe ser una URL válida |
| `button_style` | `enum` | Condicional* | Estilo visual del botón. Opciones: `primary` (defecto), `secondary`, `outline`, `white` |

**Nota:** Los tres campos del botón son interdependientes. Si se llena uno, todos deben ser completados (validación `requiredWith`).

## Características UX

### Preview en Tiempo Real
- Al escribir texto, cambiar color o posición, se actualiza instantáneamente sobre la imagen
- Preview visible tanto en creación como edición
- Sincronización automática con todos los campos del overlay

### Selector Visual de Posición
- Grid 3x3 interactivo para seleccionar posición del texto
- Feedback visual con estado activo
- Reemplaza el dropdown tradicional para mejor UX

### Alineación Automática
- **Posiciones centro** (top-center, center-center, bottom-center): elementos centrados horizontalmente
- **Posiciones derecha** (top-right, center-right, bottom-right): elementos alineados a la derecha
- **Posiciones izquierda** (top-left, center-left, bottom-left): elementos alineados a la izquierda

## Modelos de Datos

### Migración

```php
// Nuevos campos en tabla covers
$table->text('overlay_text')->nullable();
$table->text('overlay_subtext')->nullable();
$table->enum('text_position')->default('center-center');
$table->string('text_color')->default('#FFFFFF');
$table->string('button_text')->nullable();
$table->string('button_link')->nullable();
$table->enum('button_style')->default('primary');

// Campo removido
$table->dropColumn('description');
```

### Fillable en Model

```php
protected $fillable = [
    // ... otros campos
    'overlay_text',
    'overlay_subtext',
    'text_position',
    'text_color',
    'button_text',
    'button_link',
    'button_style',
];
```

## Validaciones

### Validación Backend (Controlador)

```php
$request->validate([
    'overlay_text' => 'nullable|string|max:500',
    'overlay_subtext' => 'nullable|string|max:500',
    'text_position' => 'nullable|in:top-left,top-center,top-right,center-left,center-center,center-right,bottom-left,bottom-center,bottom-right',
    'text_color' => 'nullable|string|size:7|starts_with:#',
    'button_text' => 'nullable|string|max:100',
    'button_link' => 'nullable|url',
    'button_style' => 'nullable|in:primary,secondary,outline,white',
]);
```

### Validación Frontend (FormValidator)

Los campos del botón usan la regla `requiredWith` para validar que si uno se completa, todos deben completarse:

```blade
<!-- Texto del botón -->
<input type="text" name="button_text" id="button_text"
    data-validate="max:100|requiredWith:button_link,button_style">

<!-- URL del botón -->
<input type="url" name="button_link" id="button_link"
    data-validate="url|requiredWith:button_text,button_style">

<!-- Estilo del botón -->
<select name="button_style" id="button_style"
    data-validate="requiredWith:button_text,button_link">
```

**Regla `requiredWith` en form-validator.js:**
- Si alguno de los campos relacionados tiene valor, todos deben tener valor
- Mensaje de error muestra los campos faltantes
- Evita CTAs incompletos (botón sin link, link sin texto, etc.)

## Ejemplos de Uso en Frontend

### Renderizar Overlay de Texto

```blade
@if($cover->overlay_text)
    <div class="cover-overlay {{ $cover->text_position }}" style="color: {{ $cover->text_color }}">
        <h2>{{ $cover->overlay_text }}</h2>
        @if($cover->overlay_subtext)
            <p>{{ $cover->overlay_subtext }}</p>
        @endif
        
        @if($cover->button_text && $cover->button_link)
            <a href="{{ $cover->button_link }}" class="btn btn-{{ $cover->button_style }}">
                {{ $cover->button_text }}
            </a>
        @endif
    </div>
@endif
```

### CSS para Posiciones

```css
.cover-overlay-preview {
    position: absolute;
    inset: 0;
    display: flex;
    padding: 1.5rem;
    color: var(--overlay-text-color, #ffffff);
}

.cover-overlay-content {
    display: flex;
    flex-direction: column;
    gap: 6px;
    align-items: flex-start; /* Default izquierda */
}

/* Alineación automática por posición */
.cover-overlay-preview.pos-top-center .cover-overlay-content,
.cover-overlay-preview.pos-center-center .cover-overlay-content,
.cover-overlay-preview.pos-bottom-center .cover-overlay-content {
    align-items: center; /* Centrado horizontal */
}

.cover-overlay-preview.pos-top-right .cover-overlay-content,
.cover-overlay-preview.pos-center-right .cover-overlay-content,
.cover-overlay-preview.pos-bottom-right .cover-overlay-content {
    align-items: flex-end; /* Alineación derecha */
}
```

## Vistas Actualizadas

### Create (`resources/views/admin/covers/create.blade.php`)

- Removida sección "Descripción"
- Agregados campos: overlay_text, overlay_subtext, text_position, text_color
- Agregados campos de botón: button_text, button_link, button_style

### Edit (`resources/views/admin/covers/edit.blade.php`)

- Mismos campos que Create
- Posición ahora editable (para reordenar)

## Migration Rollback

Para revertir a estructura anterior (con `description`):

```bash
php artisan migrate:rollback --step=1
```

Esto:
- Re-agregará la columna `description`
- Removerá todos los campos de overlay
