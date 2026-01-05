# Efectos Material Ripple - Botones y tarjetas

## Descripción general

El módulo **material-design.js** agrega un efecto "ripple" (onda) tipo Material Design a los elementos interactivos marcados con clases especiales.

Archivo JS: resources/js/utils/material-design.js

Se importa en resources/js/index.js y se ejecuta inmediatamente al cargar.

## Clases soportadas

- ripple-btn: para botones, enlaces u otros elementos clicables.
- ripple-card: para tarjetas o contenedores grandes.

## Comportamiento

- Escucha pointerdown / pointerup / pointerleave y eventos táctiles equivalentes.
- Crea un <span class="ripple"> posicionado dentro del elemento, centrado en la posición del click.
- Anima la expansión de la onda y su desvanecimiento.
- Si el usuario mantiene presionado (hold), la onda sigue expandiéndose ligeramente.

## Estructura HTML mínima

Ejemplos:

```blade
<button type="button" class="boton-form ripple-btn">
    <span class="boton-form-icon"><i class="ri-save-3-fill"></i></span>
    <span class="boton-form-text">Guardar</span>
    <span class="ripple"></span> {{-- generado dinámicamente --}}
</button>

<div class="dashboard-card ripple-card">
    {{-- Contenido de la tarjeta --}}
</div>
```

## CSS recomendado

En el CSS global se debe definir el estilo base de .ripple, por ejemplo:

- Posicionamiento absoluto dentro del botón o tarjeta.
- Border-radius adecuado.
- Transition en transform y opacity.

El JS se encarga de:

- Calcular tamaño (en función del ancho y alto del elemento).
- Posicionar la onda en las coordenadas del click.
- Eliminar el span después de la animación de desvanecimiento.
