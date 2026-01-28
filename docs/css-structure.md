# Estructura CSS - GECKОМERCE

## 📁 Organización de Archivos

```
resources/css/
├── base.css              # Variables y resets globales (compartido)
├── admin.css            # Entry point para panel admin
├── site.css             # Entry point para sitio público
│
├── admin/               # CSS del Panel Admin
│   ├── layout.css      
│   ├── modules/         # dashboard.css, categories.css, etc.
│   └── components/      # table.css, form.css, etc.
│
├── site/                # CSS del Sitio Público
│   ├── layout.css       # Header, footer, grid principal
│   ├── modules/         # home.css, products.css, cart.css, checkout.css
│   └── components/      # navigation.css, product-card.css, filters.css
│
└── shared/              # Componentes compartidos entre admin y sitio
    ├── alert.css
    └── button.css
```

## 🎨 Cómo Usar

### En el Panel Admin
```blade
<!-- resources/views/layouts/admin.blade.php -->
@vite(['resources/css/base.css', 'resources/css/admin.css', 'resources/js/app.js'])
```

### En el Sitio Público
```blade
<!-- resources/views/layouts/site.blade.php -->
@vite(['resources/css/base.css', 'resources/css/site.css', 'resources/js/app.js'])
```

## 🔧 Clases CSS Disponibles - Sitio Público

### Layout Principal
```css
.site-container          /* Contenedor con max-width y padding */
.products-grid          /* Grid responsive para productos */
.site-header-sticky     /* Header fijo al hacer scroll */
```

### Navigation
```css
.site-header            /* Header principal */
.site-logo              /* Logo del sitio */
.nav-icon               /* Iconos del navbar (carrito, user, etc.) */
.nav-sidebar            /* Menú lateral */
.nav-overlay            /* Overlay oscuro del menú */
.menu-toggle            /* Botón hamburguesa */
```

### Product Card
```css
.product-card           /* Tarjeta de producto */
.product-card-image     /* Imagen del producto */
.product-card-body      /* Contenido de la tarjeta */
.product-card-title     /* Título del producto */
.product-card-price     /* Precio */
.product-card-old-price /* Precio anterior (tachado) */
.product-card-badge     /* Badge de descuento */
```

### Filters
```css
.filters-container      /* Contenedor de filtros */
.filter-section         /* Sección de filtro */
.filter-title           /* Título de sección */
.filter-option          /* Opción individual */
.price-range-slider     /* Slider de rango de precio */
```

### Home
```css
.hero-section           /* Banner principal */
.hero-content           /* Contenido del hero */
.hero-title             /* Título del hero */
.categories-section     /* Sección de categorías */
.category-card          /* Tarjeta de categoría */
.featured-section       /* Productos destacados */
.section-title          /* Título de sección */
```

### Products
```css
.products-layout        /* Layout principal (sidebar + grid) */
.products-sidebar       /* Sidebar de filtros */
.products-main          /* Grid de productos */
.product-detail         /* Vista de producto individual */
.product-main-image     /* Imagen principal */
.variant-selector       /* Selector de variantes */
.variant-option         /* Opción de variante */
```

### Cart
```css
.cart-container         /* Contenedor del carrito */
.cart-items             /* Lista de productos */
.cart-item              /* Item individual */
.cart-summary           /* Resumen del pedido */
.quantity-control       /* Controles de cantidad */
```

### Checkout
```css
.checkout-container     /* Contenedor principal */
.checkout-steps         /* Indicador de pasos */
.checkout-form          /* Formulario de checkout */
.payment-method         /* Método de pago */
```

## ✨ Ejemplo de Uso

```blade
<!-- Página de productos -->
<x-site-layout>
    <div class="site-container py-8">
        <div class="products-layout">
            <!-- Sidebar de filtros -->
            <aside class="products-sidebar">
                <div class="filters-container">
                    <div class="filter-section">
                        <h3 class="filter-title">Categorías</h3>
                        <label class="filter-option">
                            <input type="checkbox" class="filter-checkbox">
                            <span class="filter-label">Ropa</span>
                        </label>
                    </div>
                </div>
            </aside>

            <!-- Grid de productos -->
            <main class="products-main">
                <div class="products-grid">
                    <div class="product-card">
                        <img src="..." class="product-card-image">
                        <div class="product-card-body">
                            <h3 class="product-card-title">Producto</h3>
                            <span class="product-card-price">$99</span>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>
</x-site-layout>
```

## 🚀 Agregar Nuevos Módulos

### Para el Sitio Público

1. Crear archivo en `resources/css/site/modules/mi-modulo.css`
2. Agregar import en `resources/css/site.css`:
   ```css
   @import "./site/modules/mi-modulo.css";
   ```

### Para el Panel Admin

1. Crear archivo en `resources/css/admin/modules/mi-modulo.css`
2. Agregar import en `resources/css/admin.css`:
   ```css
   @import "./admin/modules/mi-modulo.css";
   ```

## 📝 Convenciones

- **Usar prefijos claros**: `site-*` para sitio, `admin-*` para admin, `nav-*` para navegación
- **Mobile-first**: Usar Tailwind con breakpoints `md:`, `lg:`
- **Componentes reutilizables**: Mover a `/shared` si se usa en ambos lados
- **BEM para clases complejas**: `product-card__title--featured`
- **Aprovechar Tailwind**: Usar `@apply` solo cuando se repita mucho

## 🔄 Migración del CSS Actual

Para migrar tu CSS existente del admin:

```bash
# Mover módulos
mv resources/css/modules/* resources/css/admin/modules/

# Mover componentes (excepto shared)
mv resources/css/components/table.css resources/css/admin/components/
mv resources/css/components/form.css resources/css/admin/components/
# etc...

# Copiar componentes compartidos
cp resources/css/components/alert.css resources/css/shared/
cp resources/css/components/button.css resources/css/shared/
```

## ⚠️ Importante

- El `vite.config.js` debe incluir los entry points:
  ```js
  input: [
      'resources/css/app.css',
      'resources/css/admin.css',  // Panel admin
      'resources/css/site.css',   // Sitio público
      'resources/js/app.js'
  ]
  ```

- No mezclar CSS del admin con el sitio para evitar conflictos
- Usar `site-container` en lugar de `container` para el sitio público
- Las variables CSS globales están en `base.css`
