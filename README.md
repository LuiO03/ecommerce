<p align="center">
  	<img src="public/logo.png" alt="Logo del Proyecto" width="150">
</p>

<h1 align="center"><strong>Gecko</strong>merce</h1>

<p align="center">Tu tienda virtual inteligente en Laravel</p>

<p align="center">
  <img src="https://img.shields.io/badge/PHP-8.2-blue" alt="PHP 8.2">
  <img src="https://img.shields.io/badge/Laravel-12.x-red" alt="Laravel 12">
  <img src="https://img.shields.io/badge/Estado-En%20Desarrollo-yellow" alt="Estado del proyecto">
</p>

# 🛍️ Ecommerce Laravel

Proyecto de **Ecommerce** desarrollado con **Laravel 11**, pensado para ofrecer una base sólida de tienda en línea, con un panel de administración moderno y una estructura escalable.

---

## 🚀 Características principales

✅ Catálogo de productos con categorías y marcas
✅ Panel de administración con roles y permisos
✅ Sistema de autenticación con Laravel Breeze / Jetstream
✅ CRUD completo de productos, categorías y usuarios
✅ Soporte para imágenes y galería de productos
✅ Diseño responsive y moderno
✅ Integración con base de datos MySQL
✅ Preparado para futuras integraciones (pagos, carritos, etc.)

---

## ⚙️ Requisitos previos

Antes de comenzar, asegúrate de tener instalado:

- [PHP ^8.2](https://www.php.net/)
- [Composer](https://getcomposer.org/)
- [Node.js](https://nodejs.org/)
- [MySQL](https://www.mysql.com/)
- [Git](https://git-scm.com/)

---

## 💻 Instalación

Sigue estos pasos para clonar e instalar el proyecto localmente:

```bash
# 1️⃣ Clonar el repositorio
git clone https://github.com/LuiO03/ecommerce.git

# 2️⃣ Entrar al directorio del proyecto
cd ecommerce

# 3️⃣ Instalar dependencias de PHP
composer install

# 4️⃣ Instalar dependencias de Node y compilar assets
npm install && npm run dev

# 5️⃣ Copiar archivo de entorno y generar key
cp .env.example .env
php artisan key:generate

# 6️⃣ Configurar tu base de datos en .env y ejecutar migraciones
php artisan migrate --seed

# 7️⃣ Iniciar el servidor local
php artisan serve
```

Tu aplicación estará disponible en:
👉 **http://localhost:8000**

---

## 🧩 Estructura del proyecto

```
ecommerce/
├── app/
├── bootstrap/
├── config/
├── database/
│   ├── migrations/
│   ├── seeders/
├── public/
├── resources/
│   ├── views/
│   ├── js/
│   └── css/
├── routes/
├── storage/
└── tests/
```

---

## 🧠 Próximas mejoras

- 🛒 Carrito de compras dinámico
- 💳 Integración con pasarela de pago
- 📦 Gestión avanzada de inventarios
- 📈 Reportes y estadísticas del panel admin
- 🌐 Multilenguaje

---

## 👨‍💻 Autor

**Luis Alberto Quispe O.**
💼 Diseñador y programador web
📧 [luis@example.com] *(puedes poner tu correo real si deseas)*
🌐 [https://github.com/LuiO03](https://github.com/LuiO03)

---

## 🪪 Licencia

Este proyecto se distribuye bajo la licencia **MIT**.
Eres libre de usarlo, modificarlo y distribuirlo con fines educativos o personales.

---

✨ _"Construido con Laravel, pasión y muchas líneas de código."_ ❤️
