# DataTableManager - Sistema Modular de Tablas

## 📋 Descripción

**DataTableManager** es una clase JavaScript reutilizable que centraliza toda la lógica de las tablas DataTables del sistema, incluyendo:

- ✅ Selección múltiple con checkboxes
- 📤 Exportación (Excel, CSV, PDF)
- 🔍 Filtros personalizados
- ⚙️ Toggle rápido de estado
- 📄 Paginación personalizada
- 🎨 Animaciones y feedback visual
- 🔄 Detección automática de columnas

## 🚀 Uso Básico

### 1. Estructura HTML Requerida

Tu tabla debe seguir la estructura estándar con las clases CSS apropiadas:

```html
<table id="tabla" class="tabla-general display">
    <thead>
        <tr>
            <th class="control"></th>
            <th class="column-check-th column-not-order">
                <div><input type="checkbox" id="checkAll"></div>
            </th>
            <th class="column-id-th">ID</th>
            <th class="column-name-th">Nombre</th>
            <th class="column-description-th">Descripción</th>
            <th class="column-status-th">Estado</th>
            <th class="column-date-th">Fecha</th>
            <th class="column-actions-th column-not-order">Acciones</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($items as $item)
            <tr data-id="{{ $item->id }}" data-name="{{ $item->name }}">
                <!-- Celdas -->
            </tr>
        @endforeach
    </tbody>
</table>
```

### 2. Controles de Filtrado (Opcionales)

```html
<div class="tabla-controles">
    <div class="tabla-buscador">
        <i class="ri-search-eye-line buscador-icon"></i>
        <input type="text" id="customSearch" placeholder="Buscar..." />
        <button type="button" id="clearSearch" class="buscador-clear">
            <i class="ri-close-circle-fill"></i>
        </button>
    </div>
    
    <div class="tabla-filtros">
        <select id="entriesSelect">
            <option value="10" selected>10/pág.</option>
            <option value="25">25/pág.</option>
            <option value="50">50/pág.</option>
        </select>
        
        <select id="sortFilter">
            <option value="">Ordenar por</option>
            <option value="name-asc">Nombre (A-Z)</option>
            <option value="name-desc">Nombre (Z-A)</option>
            <option value="date-desc">Más recientes</option>
            <option value="date-asc">Más antiguos</option>
        </select>
        
        <select id="statusFilter">
            <option value="">Todos los estados</option>
            <option value="1">Activos</option>
            <option value="0">Inactivos</option>
        </select>
    </div>
    
    <button type="button" id="clearFiltersBtn" class="boton-clear-filters">
        <span class="boton-icon"><i class="ri-filter-off-line"></i></span>
        <span class="boton-text">Limpiar filtros</span>
    </button>
</div>
```

### 3. Inicialización en JavaScript

```javascript
$(document).ready(function() {
    const tableManager = new DataTableManager('#tabla', {
        moduleName: 'families',
        entityNameSingular: 'familia',
        entityNamePlural: 'familias',
        deleteRoute: '/admin/families',
        statusRoute: '/admin/families/{id}/status',
        exportRoutes: {
            excel: '/admin/families/export/excel',
            csv: '/admin/families/export/csv',
            pdf: '/admin/families/export/pdf'
        },
        csrfToken: '{{ csrf_token() }}'
    });
});
```

## ⚙️ Opciones de Configuración

### Configuración Completa

```javascript
const tableManager = new DataTableManager('#tabla', {
    // ===== RUTAS Y MÓDULO =====
    moduleName: 'families',              // Nombre del módulo (extrae automáticamente de URL)
    entityNameSingular: 'familia',       // Nombre singular para mensajes
    entityNamePlural: 'familias',        // Nombre plural para mensajes
    deleteRoute: '/admin/families',      // Ruta para eliminación múltiple
    statusRoute: '/admin/families/{id}/status', // Ruta para cambiar estado
    exportRoutes: {
        excel: '/admin/families/export/excel',
        csv: '/admin/families/export/csv',
        pdf: '/admin/families/export/pdf'
    },
    csrfToken: '{{ csrf_token() }}',     // Token CSRF
    
    // ===== CONFIGURACIÓN DATATABLE =====
    pageLength: 10,                      // Registros por página
    lengthMenu: [5, 10, 25, 50],        // Opciones de paginación
    defaultOrder: [[2, 'desc']],        // Orden inicial (se detecta automáticamente)
    
    // ===== COLUMNAS (Detección automática por clases CSS) =====
    columns: {
        id: null,      // Auto-detecta .column-id-th
        name: null,    // Auto-detecta .column-name-th
        date: null,    // Auto-detecta .column-date-th
        status: null   // Auto-detecta .column-status-th
    },
    
    // ===== CARACTERÍSTICAS =====
    features: {
        selection: true,           // Selección múltiple
        export: true,              // Exportación
        filters: true,             // Filtros personalizados
        statusToggle: true,        // Toggle de estado
        responsive: true,          // Diseño responsive
        customPagination: true     // Paginación personalizada
    },
    
    // ===== CALLBACKS =====
    callbacks: {
        onDraw: () => {
            console.log('Tabla redibujada');
        },
        onStatusChange: (id, status, response) => {
            console.log(`Estado actualizado: ${id} -> ${status}`);
        },
        onDelete: () => {
            console.log('Registros eliminados');
        },
        onExport: (type, format, count) => {
            console.log(`Exportación: ${type} (${format})`);
        }
    }
});
```

## 🔧 API Pública

### Métodos Disponibles

```javascript
// Obtener instancia de DataTable
const dataTable = tableManager.getTable();

// Obtener items seleccionados (Map<id, name>)
const selected = tableManager.getSelectedItems();

// Refrescar la tabla
tableManager.refresh();

// Limpiar selección
tableManager.clearSelection();

// Destruir instancia
tableManager.destroy();
```

### Ejemplos de Uso

```javascript
// Obtener IDs seleccionados
const selectedIds = Array.from(tableManager.getSelectedItems().keys());
console.log('IDs seleccionados:', selectedIds);

// Seleccionar filas programáticamente
tableManager.getTable().row('#fila-5').select();

// Recargar datos
tableManager.refresh();

// Aplicar filtro personalizado
tableManager.getTable().search('término').draw();
```

## 📦 Integración con Otros Módulos

### Ejemplo: Módulo de Categorías

```javascript
// resources/views/admin/categories/index.blade.php
@push('scripts')
<script>
    $(document).ready(function() {
        const tableManager = new DataTableManager('#tabla', {
            moduleName: 'categories',
            entityNameSingular: 'categoría',
            entityNamePlural: 'categorías',
            deleteRoute: '/admin/categories',
            statusRoute: '/admin/categories/{id}/status',
            exportRoutes: {
                excel: '/admin/categories/export/excel',
                csv: '/admin/categories/export/csv',
                pdf: '/admin/categories/export/pdf'
            },
            csrfToken: '{{ csrf_token() }}',
            
            callbacks: {
                onStatusChange: (id, status) => {
                    // Lógica adicional al cambiar estado
                    console.log(`Categoría ${id} ahora está ${status ? 'activa' : 'inactiva'}`);
                }
            }
        });
        
        // Resaltar fila creada/editada
        @if (Session::has('highlightRow'))
            const highlightId = {{ Session::get('highlightRow') }};
            setTimeout(() => {
                const row = $(`#tabla tbody tr[data-id="${highlightId}"]`);
                if (row.length) {
                    row.addClass('row-highlight');
                    row[0].scrollIntoView({ behavior: 'smooth', block: 'center' });
                    setTimeout(() => row.removeClass('row-highlight'), 3000);
                }
            }, 100);
        @endif
    });
</script>
@endpush
```

## 🎨 Clases CSS Importantes

### Columnas de Tabla

| Clase CSS | Descripción |
|-----------|-------------|
| `.column-id-th` | Columna de ID (se usa para orden por defecto) |
| `.column-name-th` | Columna de nombre (para filtros de ordenamiento) |
| `.column-date-th` | Columna de fecha (ordenamiento especial) |
| `.column-status-th` | Columna de estado (para toggle) |
| `.column-check-th` | Columna de checkbox de selección |
| `.column-actions-th` | Columna de acciones |
| `.column-not-order` | Desactiva ordenamiento en la columna |
| `.control` | Columna de control responsive |

### Estados y Feedback

| Clase CSS | Descripción |
|-----------|-------------|
| `.row-selected` | Fila seleccionada |
| `.row-highlight` | Fila resaltada (creación/edición) |
| `.active-cell` | Celda activa |
| `.filter-active` | Filtro activo |
| `.no-animate` | Deshabilita animaciones temporalmente |

## 🔄 Características Automáticas

### Detección de Columnas
El sistema detecta automáticamente las columnas importantes por sus clases CSS:
- `column-id-th` → Columna ID (orden por defecto)
- `column-name-th` → Columna nombre (filtros)
- `column-date-th` → Columna fecha (ordenamiento especial)
- `column-status-th` → Columna estado (toggle)

### Ordenamiento de Fechas
Las fechas con formato `dd/mm/yyyy hh:mm` se ordenan correctamente.
Los registros "Sin fecha" siempre van al final.

### Paginación Inteligente
- Botones de navegación adaptativos
- Ventana de páginas visibles configurable
- Info de registros en español

### Toggle de Estado
Actualiza el estado sin recargar la página, manteniendo la posición del scroll.

## 🛠️ Requisitos Backend

### Controlador

```php
// Eliminación múltiple
public function destroyMultiple(Request $request) {
    $request->validate(['ids' => 'required|array']);
    Entity::whereIn('id', $request->ids)->delete();
    return response()->json(['success' => true]);
}

// Actualizar estado
public function updateStatus(Request $request, Entity $entity) {
    $request->validate(['status' => 'required|boolean']);
    $entity->status = $request->status;
    $entity->save();
    return response()->json([
        'success' => true,
        'status' => $entity->status,
        'message' => 'Estado actualizado correctamente'
    ]);
}

// Exportar Excel
public function exportExcel(Request $request) {
    $ids = $request->input('ids');
    $exportAll = $request->input('export_all');
    
    if ($exportAll) {
        $entities = Entity::all();
    } else {
        $entities = Entity::whereIn('id', $ids)->get();
    }
    
    return Excel::download(new EntitiesExport($entities), 'entities.xlsx');
}
```

### Rutas

```php
// routes/admin.php
Route::get('/entities', [EntityController::class, 'index'])->name('admin.entities.index');
Route::delete('/entities', [EntityController::class, 'destroyMultiple'])->name('admin.entities.destroy-multiple');
Route::patch('/entities/{entity}/status', [EntityController::class, 'updateStatus'])->name('admin.entities.update-status');
Route::post('/entities/export/excel', [EntityController::class, 'exportExcel'])->name('admin.entities.export.excel');
Route::post('/entities/export/csv', [EntityController::class, 'exportCsv'])->name('admin.entities.export.csv');
Route::post('/entities/export/pdf', [EntityController::class, 'exportPdf'])->name('admin.entities.export.pdf');
```

## 🐛 Troubleshooting

### La tabla no se inicializa

**Problema:** No aparece nada en consola  
**Solución:** Verifica que el archivo esté importado en `resources/js/index.js`

```javascript
import './modules/datatable-manager.js';
```

### Los filtros no funcionan

**Problema:** Los selectores de filtros no hacen nada  
**Solución:** Verifica que los IDs coincidan:
- `#customSearch`
- `#sortFilter`
- `#statusFilter`
- `#entriesSelect`

### La selección múltiple no persiste

**Problema:** Al cambiar de página se pierden las selecciones  
**Solución:** Esto es el comportamiento esperado. El sistema usa un `Map` interno que mantiene los IDs seleccionados a través de las páginas.

### El resaltado de fila no funciona

**Problema:** La fila creada/editada no se resalta  
**Solución:** Asegúrate de que el controlador devuelva el ID en la sesión:

```php
return redirect()->route('admin.entities.index')->with('highlightRow', $entity->id);
```

## 📚 Referencias

- **Código fuente:** `resources/js/modules/datatable-manager.js`
- **Ejemplo de uso:** `resources/views/admin/families/index.blade.php`
- **DataTables Docs:** [datatables.net](https://datatables.net/)
- **Eliminación múltiple:** `docs/multiple-delete-global.md`
- **Toggle de estado:** `docs/quick-status-toggle.md`

## 📝 Changelog

### v1.0.0 (2025-11-16)
- ✨ Lanzamiento inicial
- ✅ Selección múltiple con persistencia
- 📤 Exportación masiva y selectiva
- 🔍 Filtros personalizados
- ⚙️ Toggle de estado AJAX
- 📄 Paginación personalizada
- 🎨 Detección automática de columnas
- 🔄 API pública para extensión

---

**Desarrollado para GECKОМERCE** 🦎
Laravel 12 + Livewire 3 + DataTables + TailwindCSS 3
