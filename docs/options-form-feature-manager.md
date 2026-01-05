# OptionsFormFeatureManager - Gestión de valores de opción en formulario

## Descripción general

El módulo **options-form-feature-manager** encapsula toda la lógica de gestión de valores ("features") de una opción en el formulario de creación/edición de opciones.

Permite:
- Añadir, reordenar y eliminar valores de una opción.
- Soportar tanto opciones de **texto** como de **color** (HEX).
- Integración con validación de formularios y con el color picker `Coloris`.

**Archivo JS:** `resources/js/modules/options-form-feature-manager.js`

Es exportado en `resources/js/index.js` como función global `window.initOptionFeatureForm`.

## API

```js
import { initOptionFeatureForm } from './modules/options-form-feature-manager.js';

const manager = initOptionFeatureForm({
    containerId: 'optionFeaturesContainer',
    addButtonId: 'addFeatureBtn',
    templateId: 'featureRowTemplateNoColor',
    nameInputId: 'name',
    isColor: false,
});
```

### Parámetros

- `containerId` (string, requerido): ID del contenedor donde se insertan las tarjetas `.option-feature-card`.
- `addButtonId` (string, requerido): ID del botón para agregar un nuevo valor.
- `templateId` (string, opcional): **No usado directamente** (el módulo toma los templates por ID fijo `featureRowTemplateColor` y `featureRowTemplateNoColor`).
- `nameInputId` (string, opcional): ID del input de nombre de opción (solo para integraciones futuras).
- `isColor` (bool, opcional): Forzar modo "color". Si se omite, se lee de `data-is-color="true|false"` en el contenedor.

Retorna la instancia interna o `null` si el contenedor no existe.

## Estructura HTML requerida

### 1. Contenedor

```blade
<div
    id="optionFeaturesContainer"
    data-is-color="{{ $option->is_color ? 'true' : 'false' }}"
    data-color-slug="color"
    data-color-locked="{{ $option->is_color ? 'true' : 'false' }}"
>
    {{-- Aquí se renderizan las .option-feature-card iniciales --}}
</div>
```

### 2. Templates Blade

Se requieren dos `<template>` (o `<script type="text/template">`) con IDs fijos:

- `featureRowTemplateColor` para opciones de color.
- `featureRowTemplateNoColor` para opciones de texto.

Dentro del template se usan placeholders:

- `__INDEX__` → índice de la feature.
- `__ID__` → ID del registro (vacío para nuevos).
- `__NUMBER__` → número visible de la tarjeta.
- `__VALUE__` → valor (HEX o texto).
- `__DESCRIPTION__` → descripción opcional.

Ejemplo simplificado para modo texto:

```blade
<template id="featureRowTemplateNoColor">
    <div class="option-feature-card" data-feature-index="__INDEX__">
        <input type="hidden" name="features[__INDEX__][id]" value="__ID__">
        <div class="option-feature-card-header">
            <span class="option-feature-chip">
                Valor #<span data-role="feature-number">__NUMBER__</span>
            </span>
            <button type="button" class="boton-sm boton-danger option-feature-remove" data-action="remove-feature">
                <span class="boton-sm-icon"><i class="ri-delete-bin-2-fill"></i></span>
            </button>
        </div>
        {{-- Inputs de valor y descripción usando __VALUE__ y __DESCRIPTION__ --}}
    </div>
</template>
```

## Comportamiento principal

- **Reindexado automático** (`reindexFeatures`):
  - Actualiza `name="features[index][...]"` para `id`, `value`, `description`.
  - Actualiza el número visible `data-role="feature-number"`.
  - Deshabilita el botón de eliminar cuando solo queda una tarjeta.

- **Modo color vs texto** (`state.isColor`):
  - Lee de `isColor` (opción) o de `data-is-color` en el contenedor.
  - En modo color:
    - Muestra el wrapper `data-role="color-wrapper"`.
    - Normaliza el valor de color con `normalizeColorValue` (HEX 3/6 dígitos).
    - Sincroniza input de color visual (`data-role="color-input"`) y etiqueta `data-role="color-hex"`.
  - En modo texto:
    - Oculta el wrapper de color.
    - Normaliza el texto (capitalización de palabras) al cambiar de modo.

- **Integración con Coloris**:
  - Si `window.Coloris` está definido, llama a `Coloris({ el: '[data-coloris]' })` tras crear nuevas tarjetas.

- **Integración con FormValidator**:
  - Busca el `<form>` ancestro.
  - Si existe `window.initFormValidator`, reinicializa la validación tras agregar una feature.

## Eventos

- Click en `[data-action="remove-feature"]` dentro de `container` → elimina la tarjeta respetando el mínimo de 1.
- Click en el botón `addButtonId` → agrega una nueva tarjeta vacía siguiendo el template correspondiente.

## Utilidades exportadas

- `normalizeColorValue(value: string): string|null`
  - Normaliza un valor de color a `#RRGGBB`.
  - Devuelve `null` si el formato no es válido.

## Buenas prácticas

- Mantener siempre al menos una tarjeta para evitar que el formulario quede sin valores.
- Usar `data-validate` en inputs para integrar con el validador global.
- Asegurarse de que los IDs de templates (`featureRowTemplateColor` y `featureRowTemplateNoColor`) no se dupliquen.
