# 🐾 PAWTITAS - Plataforma de Adopción de Perritos

Una plataforma web que conecta adoptantes responsables con perritos que necesitan un hogar, incluyendo currículums emocionales y perfiles detallados.

## 📋 Características

- ✅ Sistema de registro y login para adoptantes y refugios
- ✅ Currículums emocionales para adoptantes
- ✅ Perfiles detallados de perritos con fotos
- ✅ Sistema de solicitudes de adopción
- ✅ Perritos especiales (apoyo emocional/guía)
- ✅ Panel administrativo para refugios
- ✅ Diseño responsive y accesible

## 🛠️ Tecnologías

- **Backend**: PHP 8.0+, MariaDB
- **Frontend**: HTML5, CSS3, JavaScript ES6+
- **Base de datos**: MariaDB con PDO
- **Seguridad**: Sesiones seguras, CSRF protection, password hashing

## 📁 Estructura del Proyecto

\`\`\`
pawtitas/
├── assets/
│   ├── css/styles.css
│   └── js/main.js
├── config/
│   ├── config.php
│   └── database.php
├── includes/
│   ├── auth.php
│   ├── functions.php
│   ├── session.php
│   └── stats.php
├── pages/
│   ├── login.php
│   └── register.php
├── scripts/
│   ├── 01_create_database.sql
│   └── 02_insert_sample_data.sql
├── uploads/
├── index.php
├── .htaccess
└── README.md
\`\`\`

## 🚀 Instalación

1. **Clonar el repositorio**
   \`\`\`bash
   git clone https://github.com/tu-usuario/pawtitas.git
   cd pawtitas
   \`\`\`

2. **Configurar base de datos**
   - Crear base de datos en MariaDB
   - Ejecutar scripts en `scripts/`
   - Configurar credenciales en `config/config.php`

3. **Configurar servidor web**
   - Apache con mod_rewrite habilitado
   - PHP 8.0+ con extensiones: PDO, GD, mbstring

4. **Permisos de archivos**
   \`\`\`bash
   chmod 755 uploads/
   chmod 644 config/config.php
   \`\`\`

## 📖 Uso

1. **Para Adoptantes**:
   - Registrarse como adoptante
   - Completar currículum emocional
   - Explorar perritos disponibles
   - Enviar solicitudes de adopción

2. **Para Refugios**:
   - Registrarse como refugio
   - Subir perfiles de perritos
   - Gestionar solicitudes
   - Aprobar adopciones

## 🔧 Configuración

Editar `config/config.php` con tus datos:

\`\`\`php
define('DB_HOST', 'localhost');
define('DB_NAME', 'pawtitas_db');
define('DB_USER', 'tu_usuario');
define('DB_PASS', 'tu_password');
\`\`\`

## 👥 Equipo

- **Cruz López Iryan Sacnite**
- **García Monrroy Rafael**
- **Navarro Ramírez Ali Daniel**
- **Orozco Barrientos Ana Raquel**

---

Hecho con 💖 para conectar corazones con patitas.
