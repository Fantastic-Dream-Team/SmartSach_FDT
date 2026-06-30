# smartSACH 🗑️♻️

> Sistema inteligente de gestión de recolección de residuos sólidos para la provincia de Chiriquí, Panamá.

[![Deploy en Railway](https://railway.app/button.svg)](https://railway.app)

---

## 📋 Descripción

**smartSACH** es una plataforma web que conecta a ciudadanos, gestores y conductores del servicio de recolección de residuos de SACH (Servicios Ambientales de Chiriquí). Permite:

- 🗺️ **Seguimiento en tiempo real** de la ruta del camión recolector sobre el mapa
- 💳 **Gestión de pagos** con historial, estado Paz y Salvo / Moroso
- 📍 **Registro de ubicaciones de servicio** georeferenciadas por el usuario
- 📝 **Reportes e incidencias** con seguimiento y respuesta del gestor
- 🔔 **Notificaciones** de respuesta a reportes en tiempo real
- 👥 **Tres roles de usuario**: Cliente, Gestor y Conductor

---

## 🛠️ Tecnologías Utilizadas

| Capa | Tecnología |
|---|---|
| Frontend | PHP (server-side rendering), HTML5, Tailwind CSS CDN, JavaScript Vanilla |
| Autenticación | Supabase Auth (JWT + bcrypt) |
| Base de datos | PostgreSQL via Supabase (PostGIS, Triggers, Stored Procedures, Views) |
| Backend | PHP 8+ con arquitectura MVC (Controllers, Models, Routes) |
| Mapas | Leaflet.js + Leaflet Routing Machine |
| Despliegue | Railway (producción) |
| Dependencias PHP | Composer (autoload PSR-4) |

---

## ⚙️ Variables de Entorno Requeridas

Para ejecutar el proyecto necesitas configurar las siguientes variables de entorno (en Railway o en un archivo `.env` local):

```env
# Conexión a la base de datos PostgreSQL (Supabase)
DB_HOST=tu-host-supabase.supabase.co
DB_PORT=5432
DB_NAME=postgres
DB_USER=postgres
DB_PASSWORD=tu-password
DB_SSLMODE=require

# Supabase Auth (para el cliente JS)
SUPABASE_URL=https://tu-proyecto.supabase.co
SUPABASE_ANON_KEY=tu-anon-key-publica
```

> ⚠️ **Nunca subas credenciales reales al repositorio.** Usa variables de entorno en Railway para producción.

---

## 🚀 Instalación y Ejecución Local

### Prerrequisitos
- [XAMPP](https://www.apachefriends.org/) (PHP 8.0+ con Apache)
- [Composer](https://getcomposer.org/)
- Extensiones PHP requeridas: `pdo`, `pdo_pgsql`, `pgsql`

### Pasos

**1. Clonar el repositorio**
```bash
git clone https://github.com/Fantastic-Dream-Team/SmartSach_FDT.git
cd SmartSach_FDT
```

**2. Habilitar la extensión `pdo_pgsql` en XAMPP**

Abrir `C:\xampp\php\php.ini`, buscar y descomentar:
```ini
extension=pdo_pgsql
extension=pgsql
```
Luego reiniciar Apache en el Panel de Control de XAMPP.

**3. Instalar dependencias de PHP**
```bash
composer install
```

**4. Configurar variables de entorno**

Crear un archivo `.env` en la raíz del proyecto con las variables listadas arriba, o configurarlas directamente en el sistema antes de iniciar Apache.

**5. Iniciar el servidor**

Copiar la carpeta del proyecto a `C:\xampp\htdocs\SmartSach_FDT` y acceder desde el navegador:
```
http://localhost/SmartSach_FDT/public/
```

**6. Migrar la base de datos**

Acceder a la ruta de migración para crear las tablas:
```
http://localhost/SmartSach_FDT/public/migrate
```

---

## 🗄️ Estructura del Proyecto

```
SmartSach_FDT/
├── backend/
│   ├── config/
│   │   └── database.php          # Conexión PDO a PostgreSQL
│   └── src/
│       ├── controllers/          # Lógica de negocio (MVC)
│       ├── models/               # Modelos con consultas PDO
│       └── routes/
│           └── web.php           # Router central
├── database/
│   └── database.sql              # Script SQL (tablas, triggers, vistas, SP)
├── docs/
│   └── prototipos/               # Prototipos y diagramas
├── frontend/
│   └── src/
│       ├── components/           # header.php, footer.php
│       ├── pages/                # Vistas PHP (auth, dashboard, profile, etc.)
│       └── services/
│           └── supabaseClient.js # Cliente Supabase JS
├── public/
│   ├── assets/
│   │   └── css/style.css         # Solo estilos sin equivalente Tailwind
│   └── index.php                 # Front Controller
├── vendor/                       # Dependencias de Composer (no versionar)
├── composer.json
└── Procfile                      # Configuración de Railway
```

---

## 🔐 Seguridad

- **Prepared Statements PDO** en todos los modelos → protección contra SQL Injection
- **`session_regenerate_id(true)`** en cada login → protección contra Session Fixation
- **Cookies HttpOnly + Secure** → protección contra XSS y MITM
- **Supabase Auth** maneja hashing de contraseñas (bcrypt), tokens JWT y verificación de email
- **Validaciones** tanto en frontend (regex + JS) como en backend (PHP + `filter_input`)
- **Try-catch** en todos los controllers para manejo controlado de excepciones

---

## 👥 Equipo — Fantastic Dream Team

| Integrante | Módulo Principal |
|---|---|
| Juan B. | Infraestructura, Auth, Despliegue Railway |
| [Integrante 2] | Dashboard, Rastreo de camión |
| [Integrante 3] | Pagos, Perfil de usuario |
| [Integrante 4] | Reportes, Notificaciones |
| [Integrante 5] | Gestor, Noticias, Conductor |

---

## 📦 Despliegue en Railway

El proyecto se despliega automáticamente en Railway desde la rama `main`.

- El `Procfile` define el servidor web
- Las variables de entorno se configuran en el Dashboard de Railway
- La migración de BD se ejecuta una sola vez desde `/migrate`

---

*Proyecto académico — Servicios Ambientales de Chiriquí (SACH) — 2026*
