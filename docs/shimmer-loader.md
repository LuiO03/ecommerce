# Shimmer Loader (Skeleton UI)

Este documento describe el **loader shimmer** utilizado en el panel admin para mostrar estados de carga tipo *skeleton* mientras se obtienen datos por AJAX (por ejemplo, en modales de detalle).

## 1. Objetivo

- Mejorar la **percepción de rendimiento** mostrando placeholders animados en lugar de espacios vacíos.
- Unificar el estilo de los loaders de contenido en todo el panel admin.
- Mantener la implementación en puro CSS, reutilizando variables de diseño globales.

## 2. Archivos y estilos

### 2.1 CSS base

Los estilos del shimmer se definen en:

- [resources/css/components/shimmer.css](resources/css/components/shimmer.css)

Clases principales:

- `.shimmer` – Contenedor principal con fondo base y overflow oculto.
- `.shimmer::after` – Capa animada con degradado que recorre el contenedor.
- `.shimmer-cell` – Bloques rectangulares que simulan líneas de texto o campos.
- `.shimmer-img` – Bloque más grande para simular imágenes o tarjetas.
- `.shimmer-title` – Variante de celda pensada para títulos centrados.

Utiliza variables CSS globales:

- `--shimmer-bg`, `--shimmer-gradient`, `--shimmer-highlight`  
  Definidas en el tema principal para soportar **modo claro/oscuro**.
- Radios de borde coherentes con el resto de la UI (`--radius-input`, `--radius-card`).

El archivo se importa desde el CSS principal:

- [resources/css/main.css](resources/css/main.css)

```css
@import "./components/shimmer.css";
```

Por lo tanto, las clases del shimmer están disponibles en todo el panel admin.

### 2.2 Uso en modales

Históricamente las clases se definieron dentro de `modal-show.css`; ahora se centralizaron en `shimmer.css` para poder reutilizarlas en más vistas.

Archivos relacionados:

- [resources/css/components/modal-show.css](resources/css/components/modal-show.css)
- Vista compilada de ejemplo (categorías): se puede ver el uso de `.shimmer` en el modal de detalle de categoría.

## 3. Uso básico

### 3.1 Estructura mínima

Ejemplo genérico de estructura HTML para un bloque shimmer:

```html
<div class="shimmer">
    <div class="shimmer-img"></div>
    <div class="shimmer-cell shimmer-title" style="width: 150px;"></div>
    <div class="shimmer-cell" style="width: 80%;"></div>
    <div class="shimmer-cell" style="width: 60%;"></div>
</div>
```

Recomendaciones:

- Ajusta el `width` de cada `.shimmer-cell` con estilos inline o utilidades CSS para simular diferentes longitudes de texto.
- Usa `.shimmer-img` para imágenes, banners o tarjetas.
- Coloca el contenedor shimmer donde normalmente iría el contenido real (por ejemplo, dentro del cuerpo del modal o en una tarjeta de detalle).

### 3.2 Ejemplo de uso en AJAX (detalle de categoría)

En el modal de detalle de categorías se usan celdas shimmer mientras llega la respuesta del servidor.  
La idea general es:

1. Antes de lanzar la petición AJAX, se rellenan los contenedores con HTML shimmer:
   ```js
   $('#category-name').html('<div class="shimmer shimmer-cell" style="width:120px;"></div>');
   $('#category-image').html('<div class="shimmer shimmer-img"></div>');
   ```
2. Cuando la respuesta llega, se reemplaza el contenido de esos contenedores con los datos reales (texto, imágenes, badges, etc.).

Este patrón se puede reutilizar en cualquier otro modal o sección que cargue datos de forma diferida.

## 4. Buenas prácticas

- **No abusar**: úsalo cuando el tiempo de carga pueda ser perceptible (peticiones remotas, consultas pesadas, etc.), no para todo cambio trivial.
- Mantén el shimmer **lo más parecido posible** a la estructura real (mismas proporciones de texto/imágenes) para que el usuario entienda qué está cargando.
- Combina el shimmer con mensajes de estado claros cuando haya errores (por ejemplo, mostrar una alerta si la petición falla y ocultar el shimmer).

## 5. Referencias relacionadas

- [docs/css-structure.md](docs/css-structure.md) – Organización general de CSS en el proyecto.
- [docs/material-ripple-effects.md](docs/material-ripple-effects.md) – Efectos de interacción complementarios.
- [docs/validation-visual-indicators.md](docs/validation-visual-indicators.md) – Patrones visuales de feedback que pueden convivir con estados de carga.
