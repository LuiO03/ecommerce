# TextareaAutosize - Autoajuste de altura de textareas

## Descripción general

El módulo **textarea-autosize.js** ajusta automáticamente la altura de todos los `<textarea>` del dashboard para que crezcan según su contenido, con una transición suave.

Archivo JS: resources/js/utils/textarea-autosize.js

Exportado en resources/js/index.js como initTextareaAutosize y ejecutado en el DOMContentLoaded global.

## Comportamiento

- Recorre todos los `<textarea>` del documento y calcula la altura adecuada usando scrollHeight.
- Aplica una transición CSS (definida en los estilos) al cambiar la altura.
- Escucha eventos input delegados en document para ajustar la altura mientras el usuario escribe.
- Usa MutationObserver para detectar textareas agregados dinámicamente (Livewire, AJAX, etc.) y aplicarles el autoajuste.

## Uso

No requiere configuración en las vistas:

- Cualquier `<textarea>` dentro del layout admin será autoajustado.
- Puede convivir con validación y otros scripts sin configuración adicional.

Recomendación de CSS:

- Definir una transición en height para textareas (por ejemplo, transition: height 0.2s ease;).
