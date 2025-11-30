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

# 🛍️ GECKОМERCE - Ecommerce Laravel

Plataforma de **Ecommerce** profesional desarrollada con **Laravel 12**, diseñada para ofrecer una experiencia de comercio electrónico completa con panel de administración moderno, sistema de catálogo jerárquico y herramientas avanzadas de gestión.

---

## 🎯 Stack Tecnológico

- **Backend:** Laravel 12 + PHP 8.2
- **Frontend:** Livewire 3 + TailwindCSS 3 + Flowbite
- **UI Components:** Jetstream (autenticación + perfiles)
- **Iconos:** Remix Icon
- **Base de datos:** MySQL
- **Tablas:** DataTables (responsive + filtros avanzados)
- **Exportación:** Maatwebsite Excel + Spatie Laravel PDF
- **Permisos:** Spatie Laravel Permission
- **Build Tool:** Vite 7

---

## 🚀 Características principales

### 📦 **Sistema de Catálogo Jerárquico**
* ✅ **Familias** → **Categorías** → **Productos** → **Variantes**
* ✅ Categorías con soporte para anidación (subcategorías ilimitadas)
* ✅ Slugs únicos auto-incrementales para SEO
* ✅ Gestión de características (`Features`) y opciones (`Options`)

### 🎨 **Panel de Administración Moderno**
* ✅ Interfaz responsive con sidebar colapsable
* ✅ Tema claro/oscuro con persistencia
* ✅ DataTables con búsqueda, ordenamiento y filtros personalizados
* ✅ Toggle de estado instantáneo (sin modales)
* ✅ Eliminación múltiple con confirmación inteligente
* ✅ Exportación a Excel, CSV y PDF

### 🔐 **Seguridad y Auditoría**
* ✅ Autenticación completa con Laravel Jetstream
* ✅ Sistema de roles y permisos (Spatie Permission)
* ✅ Auditoría automática: `created_by`, `updated_by`, `deleted_by`
* ✅ Soft Deletes en todos los modelos principales
* ✅ Protección CSRF en todas las operaciones
### 📊 **Exportación de Datos**
* ✅ Excel con estilos profesionales y auto-ajuste de columnas



- **[PHP ^8.2](https://www.php.net/)** - Motor del backend
- **[Composer](https://getcomposer.org/)** - Gestor de dependencias PHP
- **[Node.js](https://nodejs.org/)** (v18+) - Build tool frontend
### Extensiones PHP requeridas:
```
php-mysql, php-mbstring, php-xml, php-curl, php-zip, php-gd
```

---

### Método 1: Setup Automático (Recomendado)

```bash
# 1️⃣ Clonar el repositorio
git clone https://github.com/LuiO03/ecommerce.git
cd ecommerce

# 2️⃣ Ejecutar setup completo
composer setup

# 3️⃣ Configurar base de datos en .env
# Edita las variables DB_DATABASE, DB_USERNAME, DB_PASSWORD

# 4️⃣ Iniciar servidor de desarrollo
composer dev
```

El comando `composer dev` inicia simultáneamente:
- ✅ Servidor PHP (puerto 8000)
- ✅ Queue listener (trabajos en segundo plano)
- ✅ Pail (logs en tiempo real)
- ✅ Vite (hot reload para assets)

### Método 2: Instalación Manual

```bash
# 1️⃣ Clonar repositorio
git clone https://github.com/LuiO03/ecommerce.git
cd ecommerce

# 2️⃣ Instalar dependencias PHP
composer install
# 3️⃣ Instalar dependencias Node
npm install

# 4️⃣ Configurar entorno
cp .env.example .env
# 6️⃣ Ejecutar migraciones con seeders

# 7️⃣ Compilar assets
npm run build

# 8️⃣ Iniciar servidor (en terminales separadas)
php artisan serve       # Terminal 1
npm run dev            # Terminal 2
```

**Acceso a la aplicación:**  
👉 **Frontend:** http://localhost:8000  
👉 **Admin Panel:** http://localhost:8000/admin  

### Credenciales por defecto (seeders):
```
Admin: admin@ecommerce.com / password
User:  user@ecommerce.com / password
```

---

## 🏗️ Arquitectura del Proyecto

```
┌─────────────┐
│   Family    │  Nivel 1: Familias (ej: "Electrónica", "Ropa")
       ▼
       ▼
┌─────────────┐
│   Variant   │  Nivel 4: Variantes (con Features)
└─────────────┘
```

### Estructura de Directorios

```
ecommerce/
├── app/
│   ├── Exports/              # Clases de exportación Excel/CSV
│   ├── Helpers/              # Helpers globales (fecha_hoy, etc.)
│   ├── Http/Controllers/
│   │   └── Admin/            # Controladores del panel admin
│   ├── Models/               # Eloquent Models con auditoría
│   └── View/Components/      # Blade Components (AdminLayout)
├── bootstrap/
│   └── app.php               # Configuración de rutas admin
├── config/
│   ├── permission.php        # Spatie Permission
│   └── laravel-pdf.php       # Configuración PDF
├── database/
│   ├── migrations/           # Migraciones con auditoría
│   └── seeders/              # RolePermissionSeeder, FamilySeeder
├── docs/                     # Documentación técnica
│   ├── multiple-delete-global.md
│   └── quick-status-toggle.md
├── resources/
│   ├── css/
│   │   ├── app.css           # TailwindCSS config
│   │   └── main.css          # Estilos del dashboard
│   ├── js/
│   │   ├── dashboard/        # Módulos UI (sidebar, theme, etc.)
│   │   ├── modals/           # modal-confirm.js (eliminación global)
│   │   ├── utils/            # datatable.js, material-design.js
│   │   ├── app.js            # Bootstrap + Flowbite
│   │   └── index.js          # Entry point (carga todos los módulos)
│   └── views/
│       ├── admin/            # Vistas del panel admin
│       └── layouts/
│           └── admin.blade.php
├── routes/
│   ├── web.php               # Rutas públicas
│   └── admin.php             # Rutas admin (prefix: /admin)
└── tests/
```
---

## 🔧 Comandos de Desarrollo

### Desarrollo local
```bash
# Servidor completo (recomendado)
composer dev

# O manualmente en terminales separadas:
php artisan serve              # Servidor PHP
npm run dev                    # Vite HMR
php artisan queue:listen       # Queue worker
php artisan pail               # Log viewer
```

### Testing
```bash
composer test                  # PHPUnit completo
php artisan test               # Alternativa con Artisan
```

### Code Quality
```bash
./vendor/bin/pint              # Laravel Pint (PSR-12)
php artisan pail --timeout=0   # Logs en tiempo real
```

### Base de datos
```bash
php artisan migrate            # Ejecutar migraciones
php artisan migrate:fresh --seed # Reset + seeders
php artisan db:seed --class=RolePermissionSeeder
```
### Construcción para producción
```bash
npm run build                  # Assets optimizados

Todos los modelos principales incluyen auditoría automática:
```php
protected $fillable = [
    'name', 'slug', 'description', 'status',
```

public static function generateUniqueSlug($name, $id = null) {
    $slug = Str::slug($name);
    while (self::where('slug', $slug)
        ->when($id, fn($q) => $q->where('id', '!=', $id))
        $slug = $originalSlug . '-' . $count++;
    }
}

public function getRouteKeyName() {
    return 'slug';
```
### Query Scopes
```php
public function scopeForTable($query) {
// routes/admin.php
// Prefix: /admin
Route::get('/entities', [EntityController::class, 'index'])
    ->name('admin.entities.index');
## 🎨 Componentes Reutilizables
### Admin Layout
```blade
<x-admin-layout :showMobileFab="true" :useSlotContainer="false">
    <x-slot name="title">
        <div class="page-icon card-success">
            <i class="ri-apps-line"></i>
        </div>
        Título de Página
    </x-slot>
    
    <x-slot name="action">
        <a href="{{ route('admin.entities.create') }}" class="boton boton-primary">
    </x-slot>
    <!-- Contenido -->
</x-admin-layout>
```

### JavaScript Global: Eliminación Múltiple
```javascript
handleMultipleDelete({
    selectedIds: selectedIds,             // Set o Array
    entityName: 'producto',               // Para mensajes
    deleteRoute: '/admin/products',       // Ruta destroy-multiple
    csrfToken: '{{ csrf_token() }}',
    buttonSelector: '#deleteSelectedBtn'
});
```

### Toggle de Estado Rápido
<label class="switch-tabla">
    <input type="checkbox" class="toggle-estado" 
           {{ $entity->status ? 'checked' : '' }}


- **[Multiple Delete Global](docs/multiple-delete-global.md)** - Sistema de eliminación múltiple reutilizable
- **[Quick Status Toggle](docs/quick-status-toggle.md)** - Toggle de estado instantáneo sin modales
- **[Copilot Instructions](.github/copilot-instructions.md)** - Guía completa para agentes de IA

---

## 🧠 Próximas mejoras

- 🛒 **Carrito de compras** con sesiones persistentes
- 💳 **Pasarelas de pago** (Stripe, PayPal, MercadoPago)
- 📦 **Gestión de inventario** con alertas de stock
- 📈 **Dashboard analítico** con gráficos (Chart.js)
- 🌐 **Multi-idioma** con Laravel Localization
- 📧 **Email Marketing** con Laravel Mailables
- 🔔 **Notificaciones** en tiempo real (Laravel Echo + Pusher)
- 📱 **API REST** con Laravel Sanctum
- 🖼️ **Galería de imágenes** con drag & drop
- ⭐ **Sistema de reviews** y ratings

---

## 🤝 Contribuciones

Las contribuciones son bienvenidas. Por favor:

1. Haz fork del proyecto
2. Crea una rama para tu feature (`git checkout -b feature/AmazingFeature`)
3. Commit tus cambios (`git commit -m 'Add some AmazingFeature'`)
4. Push a la rama (`git push origin feature/AmazingFeature`)
5. Abre un Pull Request

### Estándares de código:
- Seguir PSR-12 (usar `./vendor/bin/pint`)
- Comentar métodos complejos
- Mantener los patrones existentes (slugs, auditoría, scopes)

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

- **Laravel Team** - Framework increíble
- **Livewire** - Reactividad sin escribir JavaScript
- **TailwindCSS** - Utility-first CSS
- **Spatie** - Paquetes de calidad (Permission, PDF)
- **Maatwebsite** - Laravel Excel
- **Remix Icon** - Sistema de iconos limpio

---

<p align="center">
  <strong>✨ Construido con Laravel, pasión y muchas líneas de código ❤️</strong>
</p>

<p align="center">
  <sub>Desarrollado en Cochabamba, Bolivia 🇧🇴</sub>
</p>
