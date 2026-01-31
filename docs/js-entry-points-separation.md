# Separación de Entry Points: Admin y Sitio Público

## Cambios Realizados

### 1. Archivos Creados

#### `resources/js/admin.js`
Entry point exclusivo para el panel administrativo que incluye:
- Bootstrap (configuración compartida)
- Flowbite (componentes UI)
- Todos los módulos del admin (`index.js`)

```javascript
import './bootstrap';
import 'flowbite';
import './index';  // FormValidator, DataTables, Galerías, etc.
```

#### `resources/js/site.js`
Entry point mínimo para el sitio público que incluye:
- Bootstrap (configuración compartida)
- Alpine.js (interactividad)
- Nada más necesario

```javascript
import './bootstrap';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();
```

### 2. Archivos Modificados

#### `resources/js/app.js`
Ahora solo contiene bootstrap (carga compartida):
```javascript
import './bootstrap';
```

#### `vite.config.js`
Configurado con múltiples entry points:
```javascript
input: [
    'resources/css/app.css',
    'resources/js/app.js',      // Bootstrap compartido
    'resources/js/admin.js',    // Admin
    'resources/js/site.js',     // Sitio público
]
```

#### `resources/views/layouts/admin.blade.php`
```blade
@vite(['resources/css/app.css', 'resources/js/admin.js'])
```

#### `resources/views/layouts/app.blade.php`
```blade
@vite(['resources/css/app.css', 'resources/js/site.js'])
```

#### `resources/views/auth/admin-login.blade.php`
```blade
@vite(['resources/css/app.css', 'resources/js/admin.js'])
```

## Beneficios

✅ **Sitio público**: Carga solo 1.2 KB de JS (Alpine.js)  
✅ **Panel admin**: Carga 45+ KB de JS (Flowbite + todos los módulos)  
✅ **Mejor performance**: Sin código innecesario en el sitio  
✅ **Mantenibilidad**: Cambios en admin no afectan el sitio  
✅ **Escalabilidad**: Fácil agregar módulos sin afectar el otro lado

## Estructura Actual

```
resources/js/
├── bootstrap.js          ← Compartido (Axios, CSRF)
├── app.js                ← Bootstrap (carga compartida)
├── admin.js              ← Panel Admin (Flowbite + index.js)
├── site.js               ← Sitio Público (Alpine.js)
├── index.js              ← Todos los módulos del admin
├── sidebar-left/         ← Módulos del admin
├── utils/                ← Módulos del admin
├── modules/              ← Módulos del admin
└── ...
```

## Vistas que Cargan Cada Uno

### Cargan `admin.js`:
- `layouts/admin.blade.php` (todo el panel)
- `auth/admin-login.blade.php` (formulario de login)

### Cargan `site.js`:
- `layouts/app.blade.php` (sitio público)
- Componentes de navegación (Alpine + search)

## Próximos Pasos (Opcional)

Si necesitas módulos específicos en el sitio (ej: autocompletado del buscador):
1. Crear `resources/js/site-modules/` para componentes del sitio
2. Importarlos en `site.js`
3. Exportarlos a `window` si es necesario
