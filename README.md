# Mi Minimarket — Sistema SaaS POS

Aplicación web para la gestión de un minimarket, construida con **Laravel 11 + MySQL**.
Esta primera entrega incluye: **login/registro**, **dashboard** con KPIs y gráficos, y el
**menú lateral con todos los módulos** del sistema (cada uno con su ruta y vista base).

El diseño usa **Tailwind CSS** y **Chart.js** vía CDN, por lo que **no necesitas compilar
nada con npm**: funciona apenas levantas el servidor.

---

## ✅ Requisitos

- PHP 8.2 o superior
- Composer
- MySQL 5.7+ / MariaDB (servidor en `localhost:3306`, usuario `root`)

---

## 🚀 Instalación (paso a paso)

Abre una terminal **dentro de la carpeta del proyecto** (`C:\SAAS\saas_minimarket`) y ejecuta:

```bash
# 1) Instalar dependencias de PHP
composer install
#    Si te pide generar el lock la primera vez, ejecuta en su lugar:
#    composer update

# 2) Crear la base de datos en MySQL (una sola vez)
#    Opción A — desde la terminal de MySQL:
mysql -u root -e "CREATE DATABASE IF NOT EXISTS saas_minimarket CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
#    Opción B — créala manualmente en phpMyAdmin / HeidiSQL con el nombre: saas_minimarket

# 3) Generar la clave de la aplicación
php artisan key:generate

# 4) Crear las tablas y cargar datos de demostración
php artisan migrate --seed

# 5) Levantar el servidor
php artisan serve
```

Luego abre en tu navegador: **http://localhost:8000**

---

## 🔑 Acceso de demostración

| Rol                     | Correo                     | Contraseña       |
|-------------------------|----------------------------|------------------|
| **Super Admin** (plataforma) | `superadmin@saas.test`     | `superadmin123`  |
| Administrador (empresa) | `admin@minimarket.test`    | `password`       |
| Cajero (empresa)        | `cajero@minimarket.test`   | `password`       |

> El **Super Admin** entra al panel de la plataforma (`/admin`) para gestionar empresas y planes.
> El administrador y el cajero pertenecen a la empresa demo "Minimarket Demo".

## 🏢 Funcionalidad SaaS (multiempresa)

El sistema es **multi-tenant**: varias empresas comparten la misma base de datos pero cada una
solo ve sus propios datos (filtrado automático por `tenant_id`).

- **Registro de empresa:** en `/register` cualquiera puede crear su empresa con **14 días de prueba**.
- **Planes:** Prueba, Básico y Pro, con límites de productos, usuarios y ventas/mes.
- **Suscripción:** cada empresa ve su plan, vencimiento y uso en *Mi Suscripción*. Al vencer, se
  bloquea el acceso a los módulos hasta renovar (gestión manual desde el panel Super Admin).
- **Panel Super Admin (`/admin`):** métricas de la plataforma, gestión de empresas (plan, estado,
  vigencia) y CRUD de planes.

> Tras actualizar, ejecuta `php artisan migrate` para crear las tablas `plans`, `tenants` y las
> columnas `tenant_id`. Tus datos existentes se asignan automáticamente a la empresa "Minimarket Demo".

---

## ⚙️ Configuración de la base de datos

Ya está preconfigurada en el archivo `.env`:

```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=saas_minimarket
DB_USERNAME=root
DB_PASSWORD=
```

> Si tu MySQL tiene contraseña para `root`, edita `DB_PASSWORD=` en `.env`.

---

## 🧩 Módulos del sistema

El menú lateral ya incluye todos los módulos previstos para un minimarket real:

- **Dashboard** — KPIs, ventas por día, ventas por categoría, forma de pago *(implementado)*
- **Punto de Venta (POS)** — cobro rápido
- **Operaciones**: Ventas · Compras · Caja
- **Inventario**: Productos · Categorías · Promociones
- **Contactos**: Clientes · Proveedores
- **SUNAT**: Facturación Electrónica · Resúmenes Diarios
- **Análisis**: Reportes
- **Sistema**: Usuarios · Configuración · Config. SUNAT · Backup

La base de datos (tablas de productos, categorías, ventas, clientes, proveedores, compras y
promociones) ya está creada y poblada con datos de ejemplo, lista para construir el CRUD de
cada módulo en las siguientes iteraciones.

---

## 📁 Estructura relevante

```
app/Http/Controllers/   → DashboardController, Auth/, ModuleController
app/Models/             → User, Product, Category, Sale, SaleItem, Customer, Supplier, Promotion
database/migrations/    → esquema de todas las tablas
database/seeders/       → datos demo (usuarios, productos, ventas de 30 días)
resources/views/
  layouts/app.blade.php → layout con menú lateral + topbar
  auth/                 → login y registro
  dashboard.blade.php   → panel con gráficos
  modules/              → vista base de módulos
routes/web.php          → rutas de auth, dashboard y módulos
```

---

## 🔄 Recargar datos de ejemplo

```bash
php artisan migrate:fresh --seed
```

> No ejecutes `php artisan route:cache` en desarrollo (las rutas de módulos usan closures).
