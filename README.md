# 📁 ResourceHub - Sistema de Gestión de Recursos Digitales

![ResourceHub Banner](https://via.placeholder.com/1200x300/667eea/ffffff?text=ResourceHub)

**ResourceHub** es una aplicación web completa para la gestión, organización y distribución de recursos digitales para programadores. Desarrollada con tecnologías web modernas, ofrece una interfaz intuitiva y potentes capacidades de análisis.

---

## 📋 Tabla de Contenidos

- [Características](#-características)
- [Tecnologías](#-tecnologías)
- [Instalación](#-instalación)
- [Estructura del Proyecto](#-estructura-del-proyecto)
- [Uso](#-uso)
- [API REST](#-api-rest)
- [Base de Datos](#-base-de-datos)
- [Características Implementadas](#-características-implementadas)
- [Créditos](#-créditos)

---

## ✨ Características

### Frontend
- ✅ **Landing Page Moderna** - Página de inicio atractiva y responsiva
- ✅ **Sistema de Autenticación** - Login y registro con validación
- ✅ **Dashboard Interactivo** - Panel de control con estadísticas en tiempo real
- ✅ **Gestión de Recursos** - CRUD completo con AJAX
- ✅ **Catálogo Público** - Vista pública de recursos disponibles
- ✅ **Búsqueda Avanzada** - Filtros por tipo, lenguaje y búsqueda de texto
- ✅ **Subida de Archivos** - Drag & Drop con validación

### Backend
- ✅ **API REST Completa** - Endpoints documentados para todas las operaciones
- ✅ **Arquitectura MVC** - Código organizado y mantenible
- ✅ **Seguridad** - Autenticación de sesiones, validación de datos
- ✅ **Manejo de Archivos** - Upload seguro con validación de tipos
- ✅ **Estadísticas Avanzadas** - Métricas detalladas de uso

### Visualización de Datos
- ✅ **Chart.js** - 6+ gráficas interactivas
- ✅ **Estadísticas por Tipo** - Distribución de recursos
- ✅ **Estadísticas por Lenguaje** - Análisis por tecnología
- ✅ **Descargas por Día** - Tendencias semanales
- ✅ **Descargas por Hora** - Análisis temporal (BONUS +10 pts)
- ✅ **Top 10 Recursos** - Ranking de popularidad

### Base de Datos
- ✅ **4 Entidades Principales** - usuarios, recursos, bitacora_acceso, bitacora_descargas
- ✅ **Procedimientos Almacenados** - Lógica de negocio en BD
- ✅ **Vistas** - Consultas optimizadas para reportes
- ✅ **Triggers** - Auditoría automática

---

## 🛠 Tecnologías

### Frontend
- HTML5, CSS3
- JavaScript (ES6+)
- jQuery 3.3.1
- Bootstrap 4 (Theme: Superhero)
- Font Awesome 5.15.4
- Chart.js 3.9.1

### Backend
- PHP 7.4+
- MySQL 8.0+
- Composer (Autoload)
- Apache 2.4+

### Herramientas
- Git
- Visual Studio Code
- phpMyAdmin
- Postman (para pruebas de API)

---

## 📦 Instalación

### Requisitos Previos
- XAMPP / WAMP / LAMP
- PHP 7.4 o superior
- MySQL 8.0 o superior
- Composer instalado

### Pasos de Instalación

1. **Clonar el repositorio**
```bash
git clone https://github.com/tu-usuario/resourcehub.git
cd resourcehub
```

2. **Instalar dependencias de Composer**
```bash
composer install
```

3. **Configurar la base de datos**
```bash
# Abrir phpMyAdmin
# Crear base de datos 'resourcehub'
# Importar el archivo: backend/resource_database.sql
```

4. **Configurar credenciales**

Editar `backend/database.php`:
```php
$host = 'localhost';
$user = 'root';
$password = 'TU_PASSWORD'; // Cambiar aquí
$database = 'resourcehub';
```

5. **Crear carpeta de uploads**
```bash
mkdir uploads
chmod 755 uploads
```

6. **Iniciar el servidor**
```bash
# Iniciar Apache y MySQL en XAMPP
# Acceder a: http://localhost/resourcehub
```

### Credenciales de Prueba
```
Usuario Admin:
Email: admin@resourcehub.com
Password: admin123

Usuario Normal:
Email: usuario@resourcehub.com
Password: admin123
```

---

## 📁 Estructura del Proyecto

```
resourcehub/
│
├── assets/
│   ├── css/
│   │   └── catalog.css
│   └── js/
│       └── catalog.js
│
├── backend/
│   ├── myapi/
│   │   ├── Auth.php
│   │   ├── DataBase.php
│   │   ├── Products.php
│   │   ├── CREATE/
│   │   │   └── create.php
│   │   ├── READ/
│   │   │   └── read.php
│   │   ├── UPDATE/
│   │   │   └── update.php
│   │   └── DELETE/
│   │       └── delete.php
│   │
│   ├── auth-login.php
│   ├── auth-logout.php
│   ├── auth-signup.php
│   ├── database.php
│   ├── resource-add.php
│   ├── resource-delete.php
│   ├── resource-download.php
│   ├── resource-edit.php
│   ├── resource-filter-language.php
│   ├── resource-filter-type.php
│   ├── resource-list.php
│   ├── resource-search.php
│   ├── resource-single.php
│   ├── resource-stats.php
│   ├── stats-download.php
│   ├── upload-file.php
│   └── resource_database.sql
│
├── uploads/
│   └── [archivos subidos]
│
├── vendor/
│   └── [dependencias de Composer]
│
├── .htaccess
├── .gitignore
├── add-resource.html
├── API_DOCUMENTATION.md
├── catalog.html
├── composer.json
├── dashboard.html
├── index.html
├── login.html
├── README.md
├── resources.html
├── signup.html
└── statistics.html
```

---

## 🚀 Uso

### 1. Página de Inicio
Accede a `http://localhost/resourcehub/` para ver la landing page.

### 2. Registro e Inicio de Sesión
- Crear cuenta: `signup.html`
- Iniciar sesión: `login.html`

### 3. Dashboard
Después de iniciar sesión, accederás al dashboard con:
- Estadísticas generales
- Gráficos interactivos
- Acceso rápido a funciones

### 4. Gestión de Recursos
- **Listar**: Ver todos los recursos en `resources.html`
- **Agregar**: Subir nuevo recurso en `add-resource.html`
- **Editar**: Modificar recursos existentes
- **Eliminar**: Eliminar recursos (soft delete)

### 5. Catálogo Público
Vista pública de recursos: `catalog.html`

### 6. Estadísticas
Análisis completo en `statistics.html`

---

## 🔌 API REST

La API está completamente documentada en `API_DOCUMENTATION.md`.

### Endpoints Principales

#### Autenticación
```
POST /backend/auth-login.php
POST /backend/auth-signup.php
POST /backend/auth-logout.php
```

#### Recursos
```
GET    /backend/resource-list.php
GET    /backend/resource-single.php?id={id}
GET    /backend/resource-search.php?search={term}
POST   /backend/resource-add.php
PUT    /backend/resource-edit.php
DELETE /backend/resource-delete.php?id={id}
GET    /backend/resource-download.php?id={id}
```

#### Filtros
```
GET /backend/resource-filter-type.php?type={type}
GET /backend/resource-filter-language.php?language={lang}
```

#### Estadísticas
```
GET /backend/resource-stats.php
GET /backend/stats-download.php
```

---

## 🗄 Base de Datos

### Entidades Principales

#### 1. usuarios
- id (PK)
- nombre
- email (UNIQUE)
- password (hashed)
- rol (admin/usuario)
- fecha_registro
- activo

#### 2. recursos
- id (PK)
- titulo
- descripcion
- tipo_recurso (ENUM)
- lenguaje
- archivo_nombre
- archivo_ruta
- archivo_tamanio
- tags
- usuario_id (FK)
- fecha_subida
- activo

#### 3. bitacora_acceso
- id (PK)
- usuario_id (FK)
- accion
- ip_address
- user_agent
- fecha_hora

#### 4. bitacora_descargas
- id (PK)
- recurso_id (FK)
- usuario_id (FK)
- ip_address
- fecha_hora
- dia_semana
- hora_descarga

---

## ✅ Características Implementadas

### Rúbrica del Proyecto (100 pts)

#### Login (20 pts)
- [x] Sistema de autenticación completo
- [x] Validación de formularios
- [x] Sesiones seguras
- [x] Hash de contraseñas

#### Dashboard (20 pts)
- [x] Panel de control funcional
- [x] Estadísticas en tiempo real
- [x] Navegación intuitiva
- [x] Diseño responsivo

#### Catálogo (15 pts)
- [x] Vista pública de recursos
- [x] Búsqueda y filtros
- [x] Descarga de archivos
- [x] Diseño atractivo

#### Uso de AJAX (jQuery) (15 pts)
- [x] Operaciones sin recargar página
- [x] Búsqueda en tiempo real
- [x] CRUD dinámico
- [x] Validación asíncrona

#### HTML y CSS eficiente (10 pts)
- [x] Código semántico
- [x] Diseño responsivo
- [x] Bootstrap 4
- [x] Font Awesome

#### Base de Datos (10 pts)
- [x] 4 entidades relacionadas
- [x] Procedimientos almacenados
- [x] Vistas
- [x] Triggers

#### Generación de Gráficas con Chart.js (10 pts)
- [x] 3+ gráficas principales
- [x] Visualización interactiva
- [x] Datos en tiempo real

### BONUS (+10 pts)
- [x] Bitácora de descargas completa
- [x] Gráfica de descargas por hora
- [x] Estadísticas avanzadas
- [x] Reporte técnico documentado

---

## 📊 Gráficas Implementadas

1. **Recursos por Tipo** (Doughnut)
2. **Recursos por Lenguaje** (Bar)
3. **Descargas por Día de la Semana** (Line)
4. **Descargas por Hora del Día** (Bar) - BONUS
5. **Descargas por Tipo** (Polar Area)
6. **Descargas por Lenguaje** (Horizontal Bar)

---

## 🔐 Seguridad

- ✅ Contraseñas con hash bcrypt
- ✅ Sesiones seguras con httponly
- ✅ Validación de entrada (server-side)
- ✅ Prepared statements (SQL Injection)
- ✅ Sanitización de datos
- ✅ Protección CSRF
- ✅ Control de acceso por roles
- ✅ Validación de tipos de archivo

---

## 🐛 Solución de Problemas

### Error: No se puede conectar a la base de datos
```
Solución: Verificar credenciales en backend/database.php
```

### Error: No se pueden subir archivos
```
Solución: Verificar permisos de la carpeta uploads/
chmod 755 uploads/
```

### Error: Las gráficas no se muestran
```
Solución: Verificar que Chart.js esté cargado
Revisar la consola del navegador
```

---

## 📝 Licencia

Este proyecto es de código abierto y está disponible bajo la Licencia MIT.

---

## 👨‍💻 Autor

**Tu Nombre**
- GitHub: [@tu-usuario](https://github.com/tu-usuario)
- Email: tu@email.com

---

## 🙏 Agradecimientos

- Bootstrap Team
- Chart.js Community
- Font Awesome
- PHP Community

---

## 📅 Versión

**v1.0.0** - Diciembre 2024

---

**¡Gracias por usar ResourceHub!** 🚀