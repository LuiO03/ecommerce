# 🎨 Guía de Badges - Sistema de Colores

## Badges Disponibles

El sistema incluye **8 variantes** de badges profesionales con soporte completo para modo claro y oscuro.

### 1️⃣ Badge Primary (Acento del tema)
```blade
<span class="badge badge-primary">Primary</span>
```
- **Modo Claro**: Fondo claro con texto del color de acento
- **Modo Oscuro**: Rojo oscuro (#8B0020) con texto rosa claro (#FFB3C6)
- **Uso**: Información principal, destacados importantes

---

### 2️⃣ Badge Info (Información/Cian)
```blade
<span class="badge badge-info">Info</span>
```
- **Modo Claro**: Azul claro (#E3F2FD) con texto azul oscuro (#0D47A1)
- **Modo Oscuro**: Cian oscuro (#004D5C) con texto cian claro (#80DEEA)
- **Uso**: Información general, notificaciones, datos informativos
- **Nota**: Completamente diferenciado de Primary en modo oscuro

---

### 3️⃣ Badge Secondary (Púrpura)
```blade
<span class="badge badge-secondary">Secondary</span>
```
- **Modo Claro**: Púrpura muy claro (#F3E5F5) con texto púrpura oscuro (#4A148C)
- **Modo Oscuro**: Púrpura oscuro (#4A148C) con texto púrpura claro (#CE93D8)
- **Uso**: Información secundaria, categorías alternativas

---

### 4️⃣ Badge Success (Verde)
```blade
<span class="badge badge-success">Success</span>
```
- **Modo Claro**: Verde claro (#E8F5E9) con texto verde oscuro (#1B5E20)
- **Modo Oscuro**: Verde oscuro (#1B5E20) con texto verde claro (#81C784)
- **Uso**: Operaciones exitosas, estados activos, confirmaciones

---

### 5️⃣ Badge Warning (Ámbar)
```blade
<span class="badge badge-warning">Warning</span>
```
- **Modo Claro**: Amarillo claro (#FFF8E1) con texto naranja (#E65100)
- **Modo Oscuro**: Naranja oscuro (#E65100) con texto ámbar claro (#FFCC80)
- **Uso**: Advertencias, pendientes, atención requerida

---

### 6️⃣ Badge Danger (Rojo)
```blade
<span class="badge badge-danger">Danger</span>
```
- **Modo Claro**: Rojo muy claro (#FFEBEE) con texto rojo oscuro (#B71C1C)
- **Modo Oscuro**: Rojo oscuro (#B71C1C) con texto rojo claro (#EF9A9A)
- **Uso**: Errores, eliminaciones, estados críticos

---

### 7️⃣ Badge Orange (Naranja)
```blade
<span class="badge badge-orange">Orange</span>
```
- **Modo Claro**: Naranja muy claro (#FFF3E0) con texto naranja oscuro (#E65100)
- **Modo Oscuro**: Naranja oscuro (#E65100) con texto naranja claro (#FFCC80)
- **Uso**: Notificaciones importantes, destacados especiales

---

### 8️⃣ Badge Pink (Rosa) 🆕
```blade
<span class="badge badge-pink">Pink</span>
```
- **Modo Claro**: Rosa muy claro (#FCE4EC) con texto rosa oscuro (#AD1457)
- **Modo Oscuro**: Rosa oscuro (#880E4F) con texto rosa claro (#F48FB1)
- **Uso**: Categorías especiales, destacados femeninos, marcadores únicos

---

### 9️⃣ Badge Gray (Gris/Neutral)
```blade
<span class="badge badge-gray">Gray</span>
```
- **Modo Claro**: Gris muy claro (#FAFAFA) con texto gris oscuro (#424242)
- **Modo Oscuro**: Gris oscuro (#2C2C2C) con texto gris claro (#BDBDBD)
- **Uso**: Estados inactivos, información neutral, datos sin categoría

---

## 🎯 Diferenciación en Modo Oscuro

### Antes (Problema)
- **badge-primary**: Azul oscuro (#0D1B4C)
- **badge-info**: Azul oscuro (#1A237E)
- ❌ Se veían muy similares

### Después (Solución) ✅
- **badge-primary**: Rojo oscuro (#8B0020) - Color de acento del tema
- **badge-info**: Cian oscuro (#004D5C) - Completamente diferente
- ✅ Claramente diferenciables

---

## 🌈 Características

- ✅ **Bordes redondeados** (pill style)
- ✅ **Bordes visibles** para mejor contraste
- ✅ **Efecto hover** con elevación y sombra
- ✅ **Transiciones suaves** (0.25s cubic-bezier)
- ✅ **Soporte para iconos** con Remix Icon
- ✅ **Responsive** y accesible

---

## 📋 Ejemplo Completo

```blade
{{-- Tabla de usuarios con roles --}}
<td class="column-role-td">
    @if($user->roles->isNotEmpty())
        <span class="badge badge-primary">{{ $user->roles->first()->name }}</span>
    @else
        <span class="badge badge-gray">Sin rol</span>
    @endif
</td>

{{-- Estados con iconos --}}
<span class="badge badge-success">
    <i class="ri-check-line"></i>
    Activo
</span>

<span class="badge badge-danger">
    <i class="ri-close-line"></i>
    Inactivo
</span>

<span class="badge badge-pink">
    <i class="ri-vip-crown-line"></i>
    Premium
</span>
```

---

## 📦 Variables CSS (Personalización)

Todas las variables están en `resources/css/base.css`:

```css
:root {
    --badge-pink-bg: #FCE4EC;
    --badge-pink-text: #AD1457;
    --badge-pink-border: #F48FB1;
    --badge-pink-hover-bg: #C2185B;
    --badge-pink-hover-text: #ffffff;
    --badge-pink-hover-border: #AD1457;
}

.dark {
    --badge-pink-bg: #880E4F;
    --badge-pink-text: #F48FB1;
    --badge-pink-border: #C2185B;
    --badge-pink-hover-bg: #AD1457;
    --badge-pink-hover-text: #ffffff;
    --badge-pink-hover-border: #880E4F;
}
```

---

## ✨ Última Actualización
- **Fecha**: 22 de noviembre de 2025
- **Cambios**: 
  - ✅ Diferenciados badge-primary e info en modo oscuro
  - ✅ Agregado badge-pink con todas sus variantes
  - ✅ Mejorado contraste y legibilidad
