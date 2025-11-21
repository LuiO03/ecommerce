# Componente Alert - Documentación

## Descripción

Componente reutilizable de Laravel Blade para mostrar banners contextuales de información, advertencia, error o éxito. Incluye soporte para título, lista de items, slot personalizado y botón de cierre opcional.

## Ubicación de Archivos

- **Componente PHP**: `app/View/Components/Alert.php`
- **Vista Blade**: `resources/views/components/alert.blade.php`
- **Estilos CSS**: `resources/css/components/alert.css`
- **JavaScript**: `resources/js/components/alert.js`

## Tipos de Alerta

- `info` - Información general (azul)
- `warning` - Advertencias (amarillo/naranja)
- `danger` - Errores o acciones críticas (rojo)
- `success` - Confirmaciones exitosas (verde)

## Uso Básico

### 1. Con Lista de Items

```blade
<x-alert 
    type="info" 
    title="Guía rápida:" 
    :items="[
        'Los campos con asterisco (<i class=\'ri-asterisk text-accent\'></i>) son obligatorios',
        'Primero selecciona la <strong>familia</strong>',
        'Luego elige su ubicación en la jerarquía'
    ]"
/>
```

### 2. Con Contenido Personalizado (Slot)

```blade
<x-alert type="warning" title="Advertencia importante">
    Esta categoría tiene <strong>5 subcategorías</strong>. 
    Si cambias su familia, todas se verán afectadas.
</x-alert>
```

### 3. Alert Dismissible (con botón cerrar)

```blade
<x-alert 
    type="success" 
    title="¡Registro exitoso!" 
    :dismissible="true"
>
    La familia se ha creado correctamente.
</x-alert>
```

### 4. Alert con Persistencia (no vuelve a aparecer)

```blade
<x-alert 
    type="info" 
    title="Nuevas funciones disponibles" 
    :dismissible="true"
    data-persist-key="welcome-banner-2025"
>
    Ahora puedes arrastrar y soltar categorías en la jerarquía.
</x-alert>
```

### 5. Auto-cierre Después de X Segundos

```blade
<x-alert 
    type="success" 
    title="Guardado exitoso" 
    :dismissible="true"
    data-auto-dismiss="5000"
>
    Los cambios se han guardado automáticamente.
</x-alert>
```

### 6. Ícono Personalizado

```blade
<x-alert 
    type="info" 
    title="Dato curioso" 
    icon="ri-lightbulb-flash-line"
>
    Puedes usar atajos de teclado para navegar más rápido.
</x-alert>
```

## Parámetros

| Parámetro | Tipo | Por Defecto | Descripción |
|-----------|------|-------------|-------------|
| `type` | string | `'info'` | Tipo de alerta: `info`, `warning`, `danger`, `success` |
| `title` | string | `''` | Título del banner |
| `items` | array\|null | `null` | Lista de items a mostrar (array de strings con HTML) |
| `dismissible` | bool | `false` | Si se puede cerrar con botón X |
| `icon` | string\|null | Auto | Ícono Remix Icon (ej: `ri-lightbulb-line`) |

## Atributos Data Opcionales

| Atributo | Tipo | Descripción |
|----------|------|-------------|
| `data-persist-key` | string | Clave única para localStorage (no vuelve a mostrarse después de cerrar) |
| `data-auto-dismiss` | number | Tiempo en milisegundos para auto-cierre (ej: `5000` = 5 segundos) |

## Íconos por Defecto

- **info**: `ri-lightbulb-line`
- **warning**: `ri-information-line`
- **danger**: `ri-error-warning-line`
- **success**: `ri-checkbox-circle-line`

## Ejemplos de Migración

### ANTES (form-info-banner)

```blade
<div class="form-info-banner">
    <i class="ri-lightbulb-line form-info-icon"></i>
    <div>
        <h4 class="form-info-title">Guía rápida:</h4>
        <ul>
            <li>Los campos con asterisco son obligatorios</li>
            <li>Primero selecciona la familia</li>
        </ul>
    </div>
</div>
```

### DESPUÉS (componente alert)

```blade
<x-alert 
    type="info" 
    title="Guía rápida:" 
    :items="[
        'Los campos con asterisco son obligatorios',
        'Primero selecciona la familia'
    ]"
/>
```

---

### ANTES (subcategories-warning)

```blade
<div class="subcategories-warning">
    <i class="ri-information-line"></i>
    <div>
        <span>Importante:</span>
        <p>Esta categoría tiene 5 subcategorías...</p>
    </div>
</div>
```

### DESPUÉS (componente alert)

```blade
<x-alert type="warning" title="Importante:">
    Esta categoría tiene <strong>{{ count($subcategories) }} subcategoría(s)</strong>.
    Si cambias su familia, todas se verán afectadas.
</x-alert>
```

## JavaScript - Funcionalidades

El archivo `resources/js/components/alert.js` proporciona:

1. **Cierre manual**: Click en botón X
2. **Auto-cierre**: Después de X milisegundos (configurar con `data-auto-dismiss`)
3. **Persistencia**: Guardar estado en localStorage (configurar con `data-persist-key`)
4. **Animación suave**: Fade out + slide al cerrar

### Inicialización Automática

El módulo se inicializa automáticamente cuando el DOM está listo. No requiere código adicional en las vistas.

## Personalización Avanzada

### Cambiar Colores

Editar `resources/css/components/alert.css`:

```css
.alert-info {
    background: linear-gradient(135deg, #yourColor1, #yourColor2);
    border-left-color: #yourBorderColor;
}
```

### Agregar Nuevo Tipo

1. **En CSS** (`alert.css`):

```css
.alert-custom {
    background-color: var(--color-custom-pastel);
    border-left-color: var(--color-custom);
}

.alert-custom .alert-icon {
    color: var(--color-custom);
}
```

2. **En Componente PHP** (`Alert.php`):

```php
private function getDefaultIcon(string $type): string
{
    return match ($type) {
        'info' => 'ri-lightbulb-line',
        'warning' => 'ri-information-line',
        'danger' => 'ri-error-warning-line',
        'success' => 'ri-checkbox-circle-line',
        'custom' => 'ri-your-icon-here', // ← AGREGAR
        default => 'ri-information-line',
    };
}
```

## Responsive

El componente es totalmente responsive:

- En móviles (< 768px): padding reducido, iconos más pequeños
- En tablets/desktop: diseño completo

## Compatibilidad

- ✅ Laravel 11+
- ✅ Blade Components
- ✅ TailwindCSS (variables CSS custom)
- ✅ Remix Icons
- ✅ Vanilla JavaScript (sin dependencias)

## Notas

- El componente NO reemplaza el banner de errores de validación (`form-error-banner`)
- Usa las variables CSS del proyecto (ej: `--color-info`, `--color-warning`)
- Compatible con contenido HTML en items (usa `{!! !!}` internamente)
- La animación de cierre dura 300ms (modificar CSS y JS simultáneamente si cambias)

---

**Última actualización**: 21 de noviembre de 2025
