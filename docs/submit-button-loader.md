# Submit Button Loader - Módulo Global

Sistema reutilizable para mostrar estado de carga en botones de submit de formularios.

---

## 📦 Instalación

El módulo ya está disponible globalmente a través de `window.initSubmitLoader` (exportado en `resources/js/index.js`).

---

## 🚀 Uso Rápido

### Modo Básico (crear)
```javascript
const submitLoader = initSubmitLoader({
    formId: 'myForm',
    buttonId: 'submitBtn',
    loadingText: 'Guardando...'
});
```

### Modo Actualización (editar)
```javascript
const submitLoader = initSubmitLoader({
    formId: 'editForm',
    buttonId: 'submitBtn',
    loadingText: 'Actualizando...'
});
```

---

## ⚙️ Configuración

### Parámetros Requeridos
| Parámetro | Tipo | Descripción |
|-----------|------|-------------|
| `formId` | `string` | **REQUERIDO** - ID del formulario |

### Parámetros Opcionales
| Parámetro | Tipo | Default | Descripción |
|-----------|------|---------|-------------|
| `buttonId` | `string` | `'submitBtn'` | ID del botón submit |
| `loadingText` | `string` | `'Guardando...'` | Texto durante loading |
| `loadingIcon` | `string` | `'ri-loader-4-line'` | Clase del icono de loading (Remix Icon) |

---

## 📋 Ejemplos de Uso

### Ejemplo 1: Formulario de Creación
```javascript
const submitLoader = initSubmitLoader({
    formId: 'familyForm',
    buttonId: 'submitBtn',
    loadingText: 'Guardando...'
});
```

### Ejemplo 2: Formulario de Edición
```javascript
const submitLoader = initSubmitLoader({
    formId: 'categoryForm',
    buttonId: 'updateBtn',
    loadingText: 'Actualizando categoría...'
});
```

### Ejemplo 3: Icono Personalizado
```javascript
const submitLoader = initSubmitLoader({
    formId: 'productForm',
    buttonId: 'submitBtn',
    loadingText: 'Procesando...',
    loadingIcon: 'ri-refresh-line'  // Otro icono de Remix
});
```

---

## 🎨 HTML Requerido

El botón debe seguir esta estructura:

```html
<button class="boton-form boton-success" type="submit" id="submitBtn">
    <span class="boton-form-icon">
        <i class="ri-save-3-fill"></i>  <!-- Icono original -->
    </span>
    <span class="boton-form-text">
        Crear Familia  <!-- Texto original -->
    </span>
</button>
```

**Importante:** El módulo busca:
- `.boton-form-icon i` → Para cambiar el icono
- `.boton-form-text` → Para cambiar el texto

---

## 🔄 Comportamiento

### Al hacer submit del formulario:
1. **Deshabilita el botón** (`disabled = true`)
2. **Reduce opacidad** (70%)
3. **Cambia cursor** (`not-allowed`)
4. **Cambia icono** al spinner con animación de rotación
5. **Cambia texto** al `loadingText` configurado

### Estado original guardado:
- Texto del botón
- Clase del icono original

---

## 🛠️ API Disponible

```javascript
const submitLoader = initSubmitLoader({ formId: 'myForm' });

// Mostrar loading manualmente (normalmente automático)
submitLoader.showLoading();

// Restaurar estado original del botón
submitLoader.resetButton();

// Destruir instancia
submitLoader.destroy();
```

---

## 📂 Integración en Blade Templates

### Formulario de Creación
```blade
<form id="familyForm" method="POST" action="{{ route('admin.families.store') }}">
    @csrf
    
    <!-- Campos del formulario... -->
    
    <button class="boton-form boton-success" type="submit" id="submitBtn">
        <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
        <span class="boton-form-text">Crear Familia</span>
    </button>
</form>

<script>
    // Inicializar loading
    const submitLoader = initSubmitLoader({
        formId: 'familyForm',
        loadingText: 'Guardando...'
    });
</script>
```

### Formulario de Edición
```blade
<form id="familyForm" method="POST" action="{{ route('admin.families.update', $family) }}">
    @csrf
    @method('PUT')
    
    <!-- Campos del formulario... -->
    
    <button class="boton-form boton-accent" type="submit" id="submitBtn">
        <span class="boton-form-icon"><i class="ri-loop-left-line"></i></span>
        <span class="boton-form-text">Actualizar Familia</span>
    </button>
</form>

<script>
    const submitLoader = initSubmitLoader({
        formId: 'familyForm',
        loadingText: 'Actualizando...'
    });
</script>
```

---

## 🎯 Casos de Uso

### ✅ Usar cuando:
- Formularios de creación de entidades
- Formularios de edición/actualización
- Cualquier formulario que requiera feedback visual de envío
- Prevenir múltiples clicks durante el submit

### ❌ NO usar cuando:
- Botones que no son submit de formulario
- Acciones AJAX que no envían formularios
- Formularios con validación client-side que previene el envío

---

## 🔧 Troubleshooting

### El loading no aparece
**Causa:** IDs incorrectos o elementos no encontrados
```javascript
// ❌ Incorrecto - IDs no coinciden
<form id="myFormulario">...</form>
initSubmitLoader({ formId: 'myForm' });  // ❌ ID diferente

// ✅ Correcto
<form id="myForm">...</form>
initSubmitLoader({ formId: 'myForm' });  // ✅ Coincide
```

### El icono no rota
**Causa:** Falta la animación CSS `spin` en tu archivo de estilos

Asegúrate de tener esta animación en tu CSS:
```css
@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}
```

### El texto no cambia
**Causa:** Estructura HTML incorrecta del botón
```html
<!-- ❌ Incorrecto - falta la clase -->
<button type="submit" id="submitBtn">
    <span>Guardar</span>
</button>

<!-- ✅ Correcto -->
<button type="submit" id="submitBtn">
    <span class="boton-form-text">Guardar</span>
</button>
```

---

## 📝 Notas

- **Automático:** El evento `submit` se captura automáticamente
- **No preventivo:** No previene el submit, solo muestra el loading
- **Restauración manual:** Si la validación falla, usa `resetButton()` para restaurar
- **Múltiples formularios:** Puedes tener múltiples instancias en la misma página

---

## 🔗 Archivos Relacionados

- **Módulo:** `resources/js/modules/submit-button-loader.js`
- **Exportación:** `resources/js/index.js`
- **Documentación:** `docs/submit-button-loader.md`
- **Ejemplos de uso:**
  - `resources/views/admin/families/create.blade.php`
  - `resources/views/admin/families/edit.blade.php`
  - `resources/views/admin/categories/create.blade.php`
  - `resources/views/admin/categories/edit.blade.php`

---

**Versión:** 1.0.0  
**Última actualización:** 19/11/2025
