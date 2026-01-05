# ProductVariantsManager - Generador de variantes de producto

## Descripción general

El módulo **product-variants-manager** gestiona la generación y edición de variantes de un producto a partir de las opciones y valores configurados (tallas, colores, etc.).

Permite:
- Construir combinaciones automáticas de variantes (producto por valores de opciones).
- Editar manualmente SKU, precio, stock y estado por variante.
- Reutilizar variantes iniciales (por ejemplo, al editar un producto existente).

Archivo JS: resources/js/modules/product-variants-manager.js

Exportado en resources/js/index.js como window.initProductVariantsManager.

## Inicialización

Ejemplo básico:

```js
import { initProductVariantsManager } from './modules/product-variants-manager.js';

document.addEventListener('DOMContentLoaded', () => {
  initProductVariantsManager({
    containerId: 'variantsManager',
    emptyStateId: 'variantsEmptyState',
    addButtonId: 'addVariantBtn',
    optionsContainerId: 'optionsSelectorContainer',
    generateButtonId: 'generateVariantsBtn',
    baseSkuInputId: 'sku',
  });
});
```

Parámetros principales:

- containerId (string, requerido): contenedor principal del gestor.
- emptyStateId (string, opcional): elemento que muestra el estado "sin variantes".
- addButtonId (string, opcional): botón para agregar variantes manuales (si aplica).
- optionsContainerId (string, opcional): contenedor donde se generan los selectores de opciones y valores.
- generateButtonId (string, opcional): botón para generar combinaciones automáticas.
- baseSkuInputId (string, opcional): input de SKU base del producto, usado para sugerir SKUs de variantes.

## Datos necesarios en el DOM

El contenedor principal debe definir dos atributos data-* con JSON:

```blade
<div
    id="variantsManager"
    data-options='@json($options)'
    data-initial-variants='@json($variants)'
>
    <table>
        <tbody data-role="variants-body">
            {{-- Filas iniciales de variantes (opcional) --}}
        </tbody>
    </table>
</div>
```

- data-options: array de opciones con sus features, por ejemplo:

```json
[
  {
    "id": 1,
    "name": "Talla",
    "is_color": false,
    "features": [
      { "id": 10, "value": "S" },
      { "id": 11, "value": "M" }
    ]
  },
  {
    "id": 2,
    "name": "Color",
    "is_color": true,
    "features": [
      { "id": 20, "value": "#FF0000", "description": "Rojo" }
    ]
  }
]
```

- data-initial-variants: variantes existentes al editar, incluyendo sus features.

## Estructura visual de variantes

Cada variante se renderiza como una fila tr con clase variant-row con:

- Celda de opciones seleccionadas:
  - Input oculto variants[index][id] con el ID de la variante (vacío para nuevas).
  - Texto con etiqueta generada (por ejemplo: "M / Rojo").
  - Contenedor oculto data-role="features-container" con inputs ocultos variants[index][features][] para IDs de features.

- Celda de SKU (variants[index][sku]).
- Celda de precio (variants[index][price]).
- Celda de stock (variants[index][stock]).
- Celda de estado (variants[index][status] como switch).
- Celda de acciones (botón "Eliminar").

El módulo se encarga de reindexar los name de variants[index][...] al agregar o eliminar filas.

## Selector de opciones y valores

Si se proporciona optionsContainerId, el módulo construye un panel interactivo:

- Una tarjeta por opción (product-option-card) con:
  - Checkbox para activar o desactivar la opción.
  - Lista de valores (feature-toggle) como píldoras clicables.
  - Vista compacta cuando hay muchos valores (oculta extras y muestra resumen).

Al pulsar el botón generateButtonId:

1. Construye un mapa optionId → [featureIds seleccionados].
2. Calcula el producto cartesiano de las selecciones.
3. Genera filas de variantes para cada combinación.
4. Reutiliza variantes existentes cuando es posible (para no perder precios y stock ya capturados).

## Generación de etiquetas y SKUs

- Etiqueta de variante (buildVariantLabel):
  - Usa los labels visibles de cada feature en orden.
  - Para colores, usa la description (por ejemplo: "Rojo").
  - Para otras opciones, usa el value (por ejemplo: S, M, L).
  - Ejemplo resultante: "M / Rojo".

- SKU sugerido (buildSkuSuggestion):
  - A partir de un SKU base (baseSkuInputId) o "VAR" si está vacío.
  - Genera segmentos tipo SEGMENTO usando slugifySegment sobre la etiqueta visible.
  - Ejemplo: base "CAMISA-001" + "M / Rojo" → "CAMISA-001-M-ROJO".

## Reindexado y estado vacío

- reindexVariantRows(container, emptyState):
  - Recorre todas las filas variant-row.
  - Ajusta los atributos name de variants[index][...] según su posición actual.
  - Muestra u oculta el elemento emptyState cuando no hay filas.

## Buenas prácticas de uso

- Asegurar que los datos de data-options incluyen correctamente is_color, value y description.
- Mantener el ID de contenedor y de botones consistente con lo que se pasa a initProductVariantsManager.
- Al procesar el formulario en backend, tener en cuenta que:
  - Algunas variantes pueden venir sin id (nuevas).
  - variants[index][features][] contiene IDs de features seleccionados para esa variante.
