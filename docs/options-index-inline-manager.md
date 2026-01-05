# OptionsInlineManager - Gestión inline de valores de opción

## Descripción general

El módulo **options-index-inline-manager** permite administrar los valores (features) de una opción **directamente** desde el índice de opciones, sin salir de la pantalla.

Incluye:
- Formulario inline para agregar un nuevo valor.
- Sincronización especial para opciones de tipo color.
- Validación en vivo y feedback de éxito/error.

**Archivo JS:** `resources/js/modules/options-index-inline-manager.js`

Expuesto globalmente en `resources/js/index.js` como `window.initOptionInlineManager()`.

## Inicialización

```js
import { initOptionInlineManager } from './modules/options-index-inline-manager.js';

document.addEventListener('DOMContentLoaded', () => {
    initOptionInlineManager();
});
```

La función **no** recibe parámetros: busca automáticamente tarjetas con `data-option-inline`.

## Estructura HTML esperada

Cada tarjeta de opción en el índice debe tener:

```blade
<div
    class="option-inline-card"
    data-option-inline
    data-option-id="{{ $option->id }}"
    data-option-slug="{{ $option->slug }}"
    data-option-is-color="{{ $option->is_color ? 'true' : 'false' }}"
>
    {{-- Lista de valores existentes --}}
    <div data-role="feature-list">
        {{-- Píldoras renderizadas en Blade --}}
    </div>

    {{-- Texto de cantidad --}}
    <span
        data-role="feature-count"
        data-label-singular="valor"
        data-label-plural="valores"
    >
        0 valores
    </span>

    {{-- Fecha de actualización --}}
    <span data-role="updated-text">Actualizado hace un momento</span>

    {{-- Formulario inline --}}
    <form
        method="POST"
        action="{{ route('admin.options.features.store', $option) }}"
        data-role="feature-form"
        data-option-is-color="{{ $option->is_color ? 'true' : 'false' }}"
    >
        @csrf
        {{-- Inputs de valor / descripción / color --}}
        <button type="submit" data-role="feature-submit">Agregar</button>
        <div data-role="feature-feedback">
            <span data-role="feature-feedback-text"></span>
        </div>
    </form>
</div>
```

### Atributos de datos clave

- `data-option-inline`: marca la tarjeta como gestionable inline.
- `data-option-is-color`: controla si la opción es de tipo color.
- En el formulario:
  - `data-role="feature-form"`.
  - `data-role="feature-value"` → input principal del valor.
  - `data-role="feature-description"` → descripción opcional.
  - `data-role="feature-color"` y `data-role="feature-color-hex"` → controles visuales de color.
  - `data-role="feature-submit"` → botón submit.
  - `data-role="feature-feedback"` y `data-role="feature-feedback-text"` → mensaje de feedback.

## Dependencias

- `normalizeColorValue` desde `options-form-feature-manager.js`.
- `FormValidator` desde `resources/js/utils/form-validator.js`.
- `SubmitButtonLoader` desde `resources/js/utils/submit-button-loader.js`.

## Flujo de trabajo

1. `initOptionInlineManager` busca todas las tarjetas con `data-option-inline` y las marca como inicializadas (`data-inline-ready="true"`).
2. Para cada tarjeta:
   - Configura `FormValidator` sobre el formulario inline.
   - Configura `SubmitButtonLoader` en el botón de envío.
   - Si la opción es de tipo color, sincroniza valor HEX, input de color y etiqueta `data-role="feature-color-hex"` (`handleColorSync`).
   - Normaliza el texto de `feature-value` en blur cuando **no** es color (`normaliseValue`).
3. En el envío del formulario:
   - Previene el submit real y envía la petición vía `fetch`/AJAX al endpoint del formulario.
   - Si la respuesta es exitosa, reconstruye la lista visual de valores con `buildFeaturePill`.
   - Actualiza el contador (`data-role="feature-count"`) y el texto de última actualización (`data-role="updated-text"`).

> Nota: el módulo espera que el backend devuelva JSON con información del nuevo feature (id, value, description, is_color y texto humano de fecha/hora).

## Utilidades internas destacadas

- `buildFeaturePill(feature)`:
  - Crea un elemento `.option-feature-card` de solo lectura a partir de un objeto `feature`.
  - Soporta tanto opciones de color (con input `data-coloris`) como de texto.

- `updateCountDisplay(node, count)`:
  - Actualiza el contenido textual usando `data-label-singular` y `data-label-plural`.

- `handleColorSync(form, isColor)`:
  - Activa/desactiva controles de color.
  - Normaliza y sincroniza valor HEX entre inputs y etiqueta.

## Buenas prácticas

- Mantener la API JSON del backend alineada con lo que espera el módulo (id, value, description, is_color, `updated_human` o similar).
- Usar `data-validate` en los inputs para aprovechar la validación global.
- Ajustar `data-label-singular` y `data-label-plural` según el idioma/contexto del proyecto.
