# Dashboard Scripts - Sidebar, iconos y gestos

## Descripción general

Los scripts en resources/js/dashboard/ manejan la interacción principal del layout admin: sidebar, iconos, tooltips y gestos táctiles.

Archivos clave:

- icon-hover-fill.js
- sidebar-submenus.js
- sidebar-toggle.js
- sidebar-tooltips.js
- sidebar-touch-gestures.js

Todos se importan en resources/js/index.js y se ejecutan automáticamente al cargar el dashboard.

## icon-hover-fill.js - Iconos Remix con hover activo

Función:

- Cambia iconos Remix de la barra lateral de versión "-line" a "-fill" al hacer hover.
- Mantiene el icono en "-fill" si el enlace está activo (clase active).

Uso esperado:

- Las clases de iconos deben seguir el patrón ri-*-line.
- Los iconos deben vivir dentro de .sidebar-link .sidebar-icon, .sidebar-sublink .sidebar-icon o .menu-item .sidebar-icon.

## sidebar-submenus.js - Submenús con estado persistente

Función:

- Controla la apertura y cierre de submenús en la sidebar.
- Usa localStorage (clave admin.sidebar.openSubmenu) para recordar el último submenú abierto.
- Desplaza suavemente la vista hasta el enlace activo al cargar.

Estructura:

- Botones de submenú con clase .submenu-btn.
- Contenedores de submenú con clase .sidebar-submenu (y un id único para persistencia).

Comportamiento:

- Solo un submenú abierto a la vez.
- Aplica clases visuales (open, rounded-top, rotate-180) a los elementos correspondientes.

## sidebar-toggle.js - Colapsar/expandir sidebar

Función:

- Permite colapsar o expandir el sidebar principal.
- Guarda el estado en localStorage bajo la clave sidebarCollapsed.
- Cambia el icono del botón de toggle con animación de giro.

Estructura:

- Sidebar principal con id logo-sidebar.
- Botón de toggle con id toggleSidebarWidth e ícono interno (<i>).

Clases usadas:

- collapsed en el sidebar.
- sidebar-collapsed en el body.

## sidebar-tooltips.js - Tooltips para sidebar colapsado

Función:

- Muestra tooltips flotantes cuando la sidebar está colapsada.
- Soporta enlaces principales (.sidebar-link), subenlaces (.sidebar-sublink), botones de submenú (.submenu-btn) y el botón de tema (#theme-toggle).

Estructura:

- Sidebar con clase .sidebar-principal.
- Elementos con atributo data-tooltip="Texto del tooltip".

Comportamiento:

- Posiciona el tooltip al lado derecho del sidebar, centrado verticalmente respecto al elemento.
- Anima entrada/salida con clases fade-in, fade-out y show.
- Oculta el tooltip al hacer scroll en .sidebar-contenido.

## sidebar-touch-gestures.js - Gestos táctiles para sidebars móviles

Función:

- Habilita gestos de arrastre desde los bordes de la pantalla para abrir/cerrar:
  - Sidebar izquierdo (logo-sidebar).
  - Sidebar de usuario derecho (userSidebar).
- Gestiona un overlay semitransparente para oscurecer el contenido cuando un sidebar está abierto.

Estructura:

- Sidebar izquierdo: id logo-sidebar (clase -translate-x-full cuando está oculto).
- Sidebar derecho: id userSidebar (clase translate-x-full cuando está oculto).
- Overlay global: id overlay.
- Botón hamburguesa de usuario: id userSidebarToggle.

Comportamiento principal:

- Detecta touchstart cerca de los bordes (< 40px) para iniciar el gesto.
- Ajusta transform: translateX(...) de los sidebars durante el drag.
- Usa umbrales de desplazamiento (±80px) para decidir apertura o cierre al finalizar el gesto.
- Solo oculta el overlay cuando ambos sidebars están cerrados.
