# Gestión de Portadas - Campos de Overlay

## Descripción

Las portadas (covers) ahora soportan texto dinámico superpuesto sobre las imágenes sin necesidad de editar los archivos gráficos. Esto permite:

- ✅ Cambiar mensajes sin re-subir imágenes
- ✅ Personalizar posiciones de texto
- ✅ Agregar botones CTA (Call-To-Action) con enlaces
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
| `button_text` | `string` | No | Texto del botón (máx. 100 caracteres). Ej: "Comprar ahora", "Ver más" |
| `button_link` | `string` | No | URL destino del botón. Debe ser una URL válida |
| `button_style` | `enum` | No | Estilo visual del botón. Opciones: `primary` (defecto), `secondary`, `outline`, `white` |

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

## Validaciones en Controlador

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
.cover-overlay {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    padding: 2rem;
    text-align: center;
}

/* Posiciones específicas */
.cover-overlay.top-left { justify-content: flex-start; align-items: flex-start; text-align: left; }
.cover-overlay.top-center { justify-content: flex-start; align-items: center; }
.cover-overlay.top-right { justify-content: flex-start; align-items: flex-end; text-align: right; }

.cover-overlay.center-left { justify-content: center; align-items: flex-start; text-align: left; }
.cover-overlay.center-center { justify-content: center; align-items: center; text-align: center; }
.cover-overlay.center-right { justify-content: center; align-items: flex-end; text-align: right; }

.cover-overlay.bottom-left { justify-content: flex-end; align-items: flex-start; text-align: left; }
.cover-overlay.bottom-center { justify-content: flex-end; align-items: center; }
.cover-overlay.bottom-right { justify-content: flex-end; align-items: flex-end; text-align: right; }
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
