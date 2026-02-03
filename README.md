<p align="center">
  	<img src="public/logo.png" alt="Logo del Proyecto" width="150">
</p>

<h1 align="center"><strong>GECKO</strong><i>MERCE</i></h1>

<p align="center">Tu tienda virtual inteligente en Laravel</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Livewire-3.x-purple" alt="Livewire 3">
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38bdf8" alt="TailwindCSS 3">
  <img src="https://img.shields.io/badge/Estado-En%20Desarrollo-yellow" alt="Estado del proyecto">
</p>

# 🛍️ GECKOMERCE - Ecommerce Laravel

Plataforma de **Ecommerce** profesional desarrollada con **Laravel 12**, diseñada para ofrecer una experiencia de comercio electrónico completa con panel de administración moderno, sistema de catálogo jerárquico y herramientas avanzadas de gestión.

---

## 🎯 Stack Tecnológico

- **Backend:** Laravel 12 + PHP 8.2 + MySQL
- **Frontend:** Blade + Livewire 3 + TailwindCSS 3 + Flowbite + Vite 7
- **Auth:** Laravel Jetstream (autenticación + perfiles + 2FA)
- **Permisos:** Spatie Laravel Permission (roles y permisos dinámicos)
- **Exportación:** Maatwebsite Excel + Spatie Laravel PDF
- **Tablas:** DataTables 2.3.4 (responsive + filtros + múltiples selecciones)
- **Auditoría:** Sistema automático de trazabilidad (created_by, updated_by, deleted_by)
- **Iconos:** Remix Icon (solo `ri-*`)
- **Utilidades:** Sortable.js (drag & drop), Axios, Puppeteer

---

## 🚀 Características Principales

### 📦 **Sistema de Catálogo Jerárquico**
* ✅ **Familias** → **Categorías** → **Productos** → **Variantes**
* ✅ Categorías con soporte para anidación (subcategorías ilimitadas)
* ✅ Slugs únicos auto-incrementales para SEO
* ✅ Gestión avanzada de **Options** (opciones de producto) y **Features** (características de variante)
* ✅ Soporte para múltiples imágenes por producto con reorder y crop

### 🎨 **Panel de Administración Moderno**
* ✅ Interfaz responsive con sidebar colapsable
* ✅ Tema claro/oscuro con persistencia en localStorage
* ✅ DataTables con búsqueda, ordenamiento y filtros personalizados
* ✅ Toggle de estado instantáneo (sin modales ni reload)
* ✅ Eliminación múltiple con confirmación inteligente
* ✅ Exportación a Excel, CSV y PDF con auditoria

### 📰 **Módulo de Blog/Posts**
* ✅ Sistema de posts con estados (draft, pending, published, rejected)
* ✅ Flujo de revisión: creador → revisor → publicación
* ✅ Notificaciones en tiempo real a revisores y autores
* ✅ Soporte para tags e imágenes destacadas
* ✅ Contador de vistas y permitir/denegar comentarios
* ✅ Soft deletes para recuperación

### 🎪 **Módulo de Portadas (Covers)**
* ✅ Slider de portadas con imagen, texto y botón CTA
* ✅ Posicionamiento de texto flexible (9 posiciones: top/center/bottom + left/center/right)
* ✅ Overlay personalizable (color, opacidad, fondo)
* ✅ Fechas de vigencia (start_at, end_at)
* ✅ Reorder de portadas por posición
* ✅ Status toggle rápido

### 🔐 **Seguridad y Auditoría**
* ✅ Autenticación completa con Laravel Jetstream
* ✅ Sistema de roles y permisos (Spatie Permission)
* ✅ **Auditoría automática:** created_by, updated_by, deleted_by en todos los modelos
* ✅ **Tabla `audits`:** registra todos los cambios (old_values, new_values, evento, IP, user_agent)
* ✅ Soft Deletes en Post, Cover y modelos principales
* ✅ Protección CSRF en todas las operaciones
* ✅ **Logs de Acceso:** tabla `access_logs` registra login, logout, fallos de autenticación
* ✅ **Visor de Auditorías:** panel admin con detalles completos de cambios

### 🔔 **Notificaciones en Tiempo Real**
* ✅ Sistema de notificaciones en BD (tabla `notifications`)
* ✅ Notificaciones para: aprobación de posts, rechazo de posts, cambios de permisos
* ✅ Badge de notificaciones no leídas en topbar
* ✅ Panel de notificaciones con historial

### 📊 **Exportación de Datos**
* ✅ Excel con estilos profesionales y auto-ajuste de columnas
* ✅ CSV con delimitadores correctos
* ✅ PDF con Spatie Laravel PDF (tablas formateadas)
* ✅ Auditoría de exportaciones (qué datos, cuándo, por quién)

### 👥 **Gestión de Usuarios y Roles**
* ✅ CRUD de usuarios con roles dinámicos
* ✅ Creación y asignación de roles personalizados
* ✅ Gestión granular de permisos por módulo
* ✅ Roles predefinidos: Administrador, Superadministrador, Editor, Revisor
* ✅ Foto de perfil con actualización en tiempo real

### ⚙️ **Configuración de Empresa**
* ✅ Datos generales: nombre, eslogan, ruc, etc.
* ✅ Identidad visual: logo, colores primarios/secundarios
* ✅ Datos de contacto: email, teléfono, ubicación
* ✅ Redes sociales: Facebook, Instagram, Twitter, LinkedIn
* ✅ Contenido legal: privacidad, términos, sobre nosotros

---

## 📋 Requisitos Previos

### Requerimientos mínimos:
- **[PHP ^8.2](https://www.php.net/)** - Motor del backend
- **[Composer](https://getcomposer.org/)** - Gestor de dependencias PHP
- **[Node.js](https://nodejs.org/)** (v18+) - Build tool frontend
- **[MySQL](https://www.mysql.com/)** 5.7+ o MariaDB 10.2+

### Extensiones PHP requeridas:
```
php-mysql, php-mbstring, php-xml, php-curl, php-zip, php-gd
```

---

## 🔧 Instalación y Setup

### Método 1: Setup Automático (Recomendado)

```bash
# 1️⃣ Clonar el repositorio
git clone https://github.com/LuiO03/ecommerce.git
cd ecommerce

# 2️⃣ Ejecutar setup completo (instala dependencias, crea BD, migraciones, seeders)
composer setup

# 3️⃣ (Opcional) Configurar variables de entorno en .env
# Por defecto usa: MySQL local, root sin contraseña, DB "ecommerce"
# Edita .env si necesitas cambiar configuración

# 4️⃣ Iniciar servidor de desarrollo
composer dev
```

El comando `composer dev` inicia simultáneamente (con concurrently):
- ✅ Servidor PHP (puerto 8000)
- ✅ Queue listener (trabajos en segundo plano)
- ✅ Pail (logs en tiempo real)
- ✅ Vite (hot reload para assets CSS/JS)

### Método 2: Instalación Manual

```bash
# 1️⃣ Clonar repositorio
git clone https://github.com/LuiO03/ecommerce.git
cd ecommerce

# 2️⃣ Instalar dependencias PHP y Node
composer install
npm install

# 3️⃣ Configurar entorno
cp .env.example .env
php artisan key:generate

# 4️⃣ Crear base de datos y ejecutar migraciones
php artisan db:create ecommerce
php artisan migrate:fresh --seed

# 5️⃣ Compilar assets
npm run build

# 6️⃣ Iniciar servidor (en terminales separadas)
php artisan serve              # Terminal 1: http://localhost:8000
npm run dev                    # Terminal 2: Vite hot reload
php artisan queue:listen       # Terminal 3: Queue worker
php artisan pail               # Terminal 4: Logs en tiempo real
```

---

## 🚀 Acceso a la Aplicación

**Frontend Público:**  
👉 http://localhost:8000

**Panel de Administración:**  
👉 http://localhost:8000/admin

### Credenciales por defecto (del Seeder):
```
Admin:        admin@ecommerce.com / password
User estándar: user@ecommerce.com / password
```

> ⚠️ Cambia estas credenciales en producción o modifica [database/seeders/UserSeeder.php](database/seeders/UserSeeder.php)

---

## 📚 Documentación Técnica

El proyecto incluye ~30 archivos de documentación en [docs/](docs/):

### Módulos Principales:
- **[admin-modules-overview.md](docs/admin-modules-overview.md)** - Visión general de todos los módulos admin
- **[auditoria.md](docs/auditoria.md)** - Sistema de auditoría automática
- **[notifications-module.md](docs/notifications-module.md)** - Notificaciones en BD
- **[category-hierarchy-manager.md](docs/category-hierarchy-manager.md)** - Selector jerárquico de categorías
- **[product-variants-manager.md](docs/product-variants-manager.md)** - Gestión de variantes y opciones
- **[gallery-manager.md](docs/gallery-manager.md)** - Upload, reorder y crop de imágenes

### Frontend y UI:
- **[datatable-manager-usage.md](docs/datatable-manager-usage.md)** - Sistema modular de tablas DataTables
- **[form-validator-usage.md](docs/form-validator-usage.md)** - Validación de formularios en cliente
- **[status-toggle-handler.md](docs/status-toggle-handler.md)** - Toggle de estado sin reload
- **[multiple-delete-global.md](docs/multiple-delete-global.md)** - Eliminación múltiple global
- **[js-entry-points-separation.md](docs/js-entry-points-separation.md)** - Separación de entry points Vite

### Patrones y Convenciones:
- **[.github/copilot-instructions.md](.github/copilot-instructions.md)** - Guía para agentes IA

---

## 🏗️ Arquitectura del Catálogo

```
┌─────────────┐      ┌─────────────┐      ┌─────────────┐      ┌──────────────┐
│   Family    │───▶ │ Category    │───▶ │ Product     │───▶ │ Variant      │
└─────────────┘      └─────────────┘      └─────────────┘      └──────────────┘
                            │                    │                    │
                            │                    │                    │
                            ▼                    ▼                    ▼
                        (anidable)          Option          Feature ↔ Option
                                                             (color, talla)
```

### Componentes Clave:

**Family (Familia):**
- Agrupa categorías relacionadas (ej: "Electrónica", "Ropa")
- Puede tener imagen, slug, descripción

**Category (Categoría):**
- Anidable: una categoría puede tener subcategorías
- Relacionada a una familia
- Gestiona productos

**Product (Producto):**
- Pertenece a una categoría
- Puede tener múltiples imágenes (ProductImage)
- Puede tener múltiples opciones (Option)

**Variant (Variante):**
- Combinación específica de características de un producto
- Ej: "Camiseta azul talla M" es una variante de "Camiseta"
- Cada variante tiene Features (talla: M, color: azul)
- Stock individual por variante

**Option (Opción):**
- Define qué se puede personalizar (talla, color, etc.)
- Asociada a productos, no a categorías
- Puede tener múltiples valores

**Feature (Característica):**
- Valor específico de una opción para una variante
- Ej: Feature "talla: M" asociada a Option "talla"

---

## 🗂️ Estructura de Directorios

```
ecommerce/
├── app/
│   ├── Console/Commands/
│   │   └── CreateDatabase.php          # Comando db:create {name}
│   ├── Exports/                        # Clases de exportación Excel/CSV/PDF
│   ├── Helpers/                        # Helpers globales
│   ├── Http/Controllers/
│   │   └── Admin/                      # Controllers del panel admin
│   │       ├── ProductController.php
│   │       ├── PostController.php
│   │       ├── CoverController.php
│   │       ├── AuditController.php
│   │       ├── AccessLogController.php
│   │       └── ...
│   ├── Listeners/                      # Event listeners (login, logout, etc.)
│   ├── Models/                         # Eloquent Models con Auditable trait
│   │   ├── Family.php
│   │   ├── Category.php
│   │   ├── Product.php
│   │   ├── Variant.php
│   │   ├── Post.php
│   │   ├── Cover.php
│   │   ├── Audit.php
│   │   ├── AccessLog.php
│   │   └── ...
│   ├── Notifications/                  # Custom notifications
│   │   └── AdminDatabaseNotification.php
│   ├── Observers/                      # Eloquent observers
│   │   └── CoverObserver.php
│   ├── Traits/
│   │   └── Auditable.php               # Trait para auditoría automática
│   └── View/Components/                # Blade Components
│       └── AdminLayout.php
├── bootstrap/
│   └── app.php                         # Registro de rutas admin protegidas
├── config/
│   ├── permission.php                  # Spatie Permission config
│   ├── laravel-pdf.php                 # Spatie PDF config
│   └── ...
├── database/
│   ├── migrations/                     # ~30 migraciones
│   │   ├── create_families_table.php
│   │   ├── create_categories_table.php
│   │   ├── create_products_table.php
│   │   ├── create_variants_table.php
│   │   ├── create_posts_table.php
│   │   ├── create_covers_table.php
│   │   ├── create_audits_table.php
│   │   ├── create_access_logs_table.php
│   │   └── ...
│   └── seeders/                        # Database seeders
│       ├── DatabaseSeeder.php          # Ejecuta todos
│       ├── RolePermissionSeeder.php    # Roles y permisos
│       ├── UserSeeder.php              # Usuarios demo
│       ├── FamilySeeder.php
│       ├── CategorySeeder.php
│       └── ...
├── docs/                               # ~30 archivos de documentación
├── resources/
│   ├── css/
│   │   ├── app.css                     # Tailwind + custom styles
│   │   └── admin/
│   │       ├── layout.css
│   │       └── ...
│   ├── js/
│   │   ├── bootstrap.js                # Config global (Axios, CSRF token)
│   │   ├── app.js                      # Bootstrap compartido
│   │   ├── admin.js                    # Entry point admin
│   │   ├── site.js                     # Entry point frontend público
│   │   ├── modules/                    # Módulos reutilizables
│   │   │   ├── category-hierarchy-manager.js
│   │   │   ├── gallery-manager.js
│   │   │   └── ...
│   │   ├── utils/                      # Utilidades
│   │   │   ├── datatable-manager.js
│   │   │   ├── form-validator.js
│   │   │   └── ...
│   │   └── index.js                    # Carga todos los módulos
│   └── views/
│       ├── admin/                      # Vistas del panel admin
│       │   ├── families/
│       │   ├── categories/
│       │   ├── products/
│       │   ├── posts/
│       │   ├── covers/
│       │   ├── audits/
│       │   └── ...
│       ├── layouts/
│       │   ├── admin.blade.php         # Layout principal admin
│       │   └── app.blade.php           # Layout frontend
│       └── partials/                   # Componentes Blade reutilizables
├── routes/
│   ├── web.php                         # Rutas públicas
│   ├── admin.php                       # Rutas admin (prefix: /admin)
│   ├── api.php                         # Rutas API (futuro)
│   └── console.php
├── tests/
│   ├── Feature/                        # Feature tests
│   └── Unit/                           # Unit tests
├── .github/
│   └── copilot-instructions.md         # Guía para agentes IA
├── vite.config.js                      # Configuración Vite (3 entry points)
├── tailwind.config.js                  # Configuración Tailwind + Flowbite
├── package.json
├── composer.json
└── .env
```

---

## 🔧 Comandos de Desarrollo

### Base de Datos
```bash
# Crear base de datos (si no existe)
php artisan db:create ecommerce

# Ejecutar migraciones
php artisan migrate

# Resetear todo (elimina datos) y ejecutar seeders
php artisan migrate:fresh --seed

# Ejecutar solo seeders (sin migraciones)
php artisan db:seed

# Ejecutar un seeder específico
php artisan db:seed --class=RolePermissionSeeder
```

### Desarrollo
```bash
# Servidor completo (RECOMENDADO - inicia todo con concurrently)
composer dev

# O manualmente en terminales separadas:
php artisan serve                   # Servidor PHP (puerto 8000)
npm run dev                         # Vite con hot reload
php artisan queue:listen --tries=1  # Queue worker
php artisan pail --timeout=0        # Log viewer en tiempo real
```

### Testing
```bash
# Ejecutar todos los tests
composer test

# Alternativa con Artisan
php artisan test

# Tests específicos
php artisan test tests/Feature/ProductTest.php
```

### Code Quality
```bash
# Validar y formatear código PSR-12
./vendor/bin/pint

# Ver logs en tiempo real
php artisan pail --timeout=0

# Limpiar caches
php artisan cache:clear
php artisan config:clear
php artisan view:clear
```

### Build para Producción
```bash
npm run build    # Minifica y optimiza assets
```

---

## 🎨 Patrones y Convenciones

### Auditoría Automática
Todos los modelos principales usan el trait `Auditable`:

```php
// En modelo
use App\Traits\Auditable;

class Product extends Model {
    use Auditable;
}

// Se registra automáticamente:
// - created: quién creó, cuándo, valores iniciales
// - updated: quién cambió, qué cambió (old vs new), IP, user_agent
// - deleted: quién eliminó, valores previos
```

### Slugs Únicos
```php
// Genera slug único automáticamente, con contador si hay duplicados
public static function generateUniqueSlug($name, $id = null) {
    $slug = Str::slug($name);
    while (self::where('slug', $slug)
        ->when($id, fn($q) => $q->where('id', '!=', $id))
        ->exists()) {
        $slug = $originalSlug . '-' . ++$count;
    }
    return $slug;
}

// Route model binding por slug
public function getRouteKeyName() {
    return 'slug';
}
```

### Permisos en Controladores
```php
// NO usar middleware en rutas, usar __construct del controlador
public function __construct() {
    $this->middleware('can:familias.index')->only(['index']);
    $this->middleware('can:familias.create')->only(['create', 'store']);
    $this->middleware('can:familias.edit')->only(['edit', 'update']);
    // etc.
}
```

### DataTables
```blade
<!-- Configuración en HTML con data-* -->
<table id="tabla" class="tabla-general" 
       data-route="{{ route('admin.families.index') }}"
       data-delete-route="{{ route('admin.families.destroy-multiple') }}"
       data-status-route="{{ route('admin.families.update-status') }}">
```

```javascript
// Inicialización en JS
const table = new DataTableManager({
    tableId: 'tabla',
    // opciones...
});
```

---

## 📱 Módulos Administrativos Disponibles

| Módulo | URL | Características |
|--------|-----|-----------------|
| **Familias** | `/admin/families` | CRUD, exportación, status toggle |
| **Categorías** | `/admin/categories` | CRUD jerárquico, árbol visual, drag & drop |
| **Productos** | `/admin/products` | CRUD completo, variantes, opciones, imágenes |
| **Variantes** | `/admin/products/{id}/variants` | CRUD de variantes con features |
| **Posts** | `/admin/posts` | CRUD, flujo de revisión, notificaciones |
| **Portadas** | `/admin/covers` | CRUD, slider, overlay, fechas vigencia |
| **Opciones** | `/admin/options` | CRUD de opciones y sus valores (features) |
| **Usuarios** | `/admin/users` | CRUD, asignación de roles, foto de perfil |
| **Roles** | `/admin/roles` | CRUD, asignación de permisos granular |
| **Permisos** | `/admin/permissions` | CRUD de permisos por módulo |
| **Auditorías** | `/admin/audits` | Visor de cambios, exportación |
| **Logs de Acceso** | `/admin/access-logs` | Registro de login/logout/fallos |
| **Notificaciones** | Panel topbar | Centro de notificaciones |
| **Configuración** | `/admin/company-settings` | Datos empresa, identidad visual, redes sociales |

---

## 🚀 Características Próximas

- 🛒 **Carrito de compras** con sesiones persistentes
- 💳 **Pasarelas de pago** (Stripe, PayPal, MercadoPago)
- 📦 **Gestión de inventario** con alertas de stock bajo
- 📈 **Dashboard analítico** con gráficos (Chart.js)
- 🌐 **Multi-idioma** con Laravel Localization
- 📧 **Email Marketing** con Laravel Mailables
- 📱 **API REST** completa con Laravel Sanctum
- ⭐ **Sistema de reviews y ratings**
- 🎁 **Promociones y descuentos**
- 👥 **Sistema de clientes** con historial de compras

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Haz fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios siguiendo PSR-12: `git commit -m 'Add some AmazingFeature'`
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de código:
- Seguir **PSR-12** (ejecuta `./vendor/bin/pint` antes de commit)
- Comentar métodos complejos
- Mantener los patrones existentes (slugs, auditoría, query scopes)
- Agregar tests para features nuevas

---

## 👨‍💻 Autor

**Luis Alberto Quispe O.**  
💼 Diseñador y programador web  
📧 [70098517@institutocajas.info](mailto:70098517@institutocajas.info)  
🌐 [github.com/LuiO03](https://github.com/LuiO03)

---

## 📄 Licencia

Este proyecto se distribuye bajo la licencia **MIT**.  
Eres libre de usarlo, modificarlo y distribuirlo con fines educativos o comerciales.

Ver [LICENSE](LICENSE) para más información.

---

## 🙏 Agradecimientos

- **Laravel Team** - Framework extraordinario
- **Livewire** - Reactividad sin escribir JavaScript
- **TailwindCSS + Flowbite** - Utility-first CSS + componentes
- **Spatie** - Paquetes de excelente calidad (Permission, PDF, etc.)
- **Maatwebsite** - Laravel Excel profesional
- **Remix Icon** - Sistema de iconos limpio y moderno
- **DataTables** - Tablas interactivas potentes

---

<p align="center">
  <strong>✨ Construido con Laravel, pasión y muchas líneas de código ❤️</strong>
</p>

<p align="center">
  <sub>Desarrollado en Cochabamba, Bolivia 🇧🇴</sub>
</p>
