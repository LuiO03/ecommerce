# DataTableManager — Modo Server-Side (opcional)

Este documento explica cómo usar **DataTables en modo server-side** a través de `DataTableManager`, para módulos con alto volumen de datos.

> Nota: esto aplica a **DataTableManager** (tablas), no a `FormValidator` (formularios).

## ¿Cuándo usar server-side?

Usa `serverSide: true` cuando:

- La tabla puede crecer a miles / cientos de miles de registros.
- No quieres renderizar todo el `<tbody>` desde Blade.
- Prefieres paginar/buscar/ordenar desde el backend (AJAX).

## Qué cambia en server-side

- DataTables enviará peticiones a un endpoint (`ajax`) con parámetros como `draw`, `start`, `length`, `search[value]`, `order[...]`.
- **Los filtros client-side basados en `$.fn.dataTable.ext.search` NO aplican** en server-side.
- `DataTableManager` inyecta automáticamente filtros adicionales en el request bajo `filters`.

## Configuración (JS)

### Opción A: simple (recomendado)

```js
$(document).ready(function () {
  const tableManager = new DataTableManager('#tabla', {
    moduleName: 'audits',
    entityNamePlural: 'auditorías',

    // Activa server-side
    serverSide: true,
    ajax: '/admin/audits/data',

    // (opcional) POST si quieres, y añadir CSRF desde ajaxData
    // ajaxMethod: 'POST',
    // ajaxData: (d) => { d._token = document.querySelector('meta[name="csrf-token"]').content },

    csrfToken: '{{ csrf_token() }}',
    features: {
      selection: true,
      statusToggle: false,
      responsive: true,
      export: true,
      filters: true,
    }
  });
});
```

### Opción B: avanzada (serverSide como objeto)

```js
serverSide: {
  enabled: true,
  processing: true,
  ajax: '/admin/audits/data',
  method: 'GET',
  extraParams: (d, manager) => {
    // aquí puedes enviar parámetros adicionales
    // d.filters.myExtra = 'x'
  },
  filtersSelector: '.tabla-filtros'
}
```

## Blade: no renderizar todo el tbody

En server-side, lo ideal es **NO** hacer `@foreach` masivo.

Deja el `<tbody>` vacío:

```blade
<tbody></tbody>
```

Mantén el `<thead>` (clases como `.control`, `.column-check-th`, `.column-id-th`, etc.), porque `DataTableManager` usa esas clases para aplicar clases en los `td` generados.

## ¿Qué filtros se envían al backend?

`DataTableManager` recolecta:

- Todos los `<select>` dentro de `.tabla-filtros .selector select` (menos `#entriesSelect`).
- Inputs dentro de `.tabla-filtros` (excepto `#customSearch`, porque DataTables ya envía `search[value]`).

Y los envía como:

- `filters`: objeto con pares `id/name => valor`

Ejemplo con Auditorías:

- `filters[eventFilter] = created|updated|deleted` (según el `<select id="eventFilter">`).

## Backend (Laravel): ejemplo Auditorías

Ya existe el endpoint listo para usar:

- `GET /admin/audits/data`

Implementado en `AuditController::data()` y registrado en rutas admin.

Qué soporta:

- Paginación (`start`, `length`)
- Búsqueda global (`search[value]`)
- Ordenamiento básico (`order[0][column]`, `order[0][dir]`)
- Filtro por evento vía `filters[eventFilter]`

### Formato esperado por DataTables

El endpoint debe retornar:

```json
{
  "draw": 1,
  "recordsTotal": 100,
  "recordsFiltered": 25,
  "data": [
    ["", "<input ...>", "1", "...", "..."],
    ...
  ]
}
```

## Aplicarlo a otros módulos

Pasos mínimos:

1. Agrega endpoint `GET /admin/<modulo>/data`.
2. En el controller, construye una query base y aplica:
   - filtros desde `$request->input('filters', [])`
   - búsqueda desde `$request->input('search.value')`
   - ordenamiento desde `$request->input('order.0.column')`
   - paginación con `start/length`
3. En el index blade, deja `<tbody></tbody>`.
4. En JS, habilita `serverSide: true` y define `ajax: '/admin/<modulo>/data'`.

## Notas importantes

- Selección múltiple: funciona mientras el backend retorne una columna con checkbox `.check-row` (como en Auditorías).
- Exportación: sigue funcionando (exporta `ids[]` seleccionados o `export_all`).
- Filtros ext.search (client-side): si activas server-side, esos filtros deben moverse al backend usando `filters[...]`.
