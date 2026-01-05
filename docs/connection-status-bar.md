# ConnectionStatusBar - Barra de estado de conexión

## Descripción general

El módulo **connection-status.js** muestra una barra fija que indica si la aplicación tiene conexión a Internet o está offline.

Archivo JS: resources/js/utils/connection-status.js

Exportado en resources/js/index.js como initConnectionStatusBar y llamado en el DOMContentLoaded global.

## Comportamiento

- Detecta el estado inicial usando navigator.onLine (si existe).
- Escucha los eventos window online y offline.
- Muestra una barra flotante con estilos diferentes para online y offline.
- En modo online puede ocultarse automáticamente tras unos segundos.

Mensajes:

- Offline: "Sin conexión a Internet. Intentando reconectar...".
- Online: "Conexión a Internet restablecida".

## Estructura HTML requerida

En el layout principal (por ejemplo, layouts/admin.blade.php) se debe incluir:

```blade
<div id="connectionStatusBar" class="connection-status-bar">
    <i id="connectionStatusIcon" class="ri-wifi-line"></i>
    <span id="connectionStatusText"></span>
</div>
```

La visibilidad y colores se controlan con clases CSS:

- connection-visible
- connection-online
- connection-offline

## Uso

No requiere configuración adicional:

- Se importa en resources/js/index.js.
- Se inicializa con initConnectionStatusBar() dentro del DOMContentLoaded global.
