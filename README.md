# CREATINET - Plataforma de Portfolios para Creativos

CREATINET es una aplicación web diseñada para que artistas, diseñadores y otros profesionales creativos puedan construir y mostrar su portfolio de trabajos de una manera sencilla y profesional. La plataforma permite a los usuarios registrarse, subir sus proyectos, y recibir interacciones de otros usuarios.

## ✨ Características Principales

- **Gestión de Usuarios:** Sistema completo de registro, inicio de sesión, cierre de sesión y recuperación de contraseña.
- **Perfiles Personalizados:** Cada usuario tiene su propia página de perfil donde se muestran sus trabajos.
- **Gestión de Portfolio:** Funcionalidad para subir y eliminar trabajos de forma dinámica.
- **Galería Interactiva:** Explora los trabajos de toda la comunidad de creativos.
- **Sistema de "Me Gusta":** Permite a los usuarios interactuar con los trabajos que más les inspiran.
- **Comunicación por Correo:** Notificaciones por email para la recuperación de contraseñas utilizando PHPMailer.

## 🛠️ Tecnologías Utilizadas

- **Backend:** PHP
- **Frontend:** HTML5, CSS3, JavaScript
- **Base de Datos:** MySQL / MariaDB
- **Servidor Web:** Apache (Recomendado a través de XAMPP)
- **Librerías:**
  - [PHPMailer](https://github.com/PHPMailer/PHPMailer) para el envío de correos electrónicos.

## 🌐 Acceso a la Aplicación

Puedes acceder a la versión en producción del proyecto en la siguiente URL:

**[https://www.alumnalaura.com](https://www.alumnalaura.com)**

## 🚀 Guía de Instalación para Desarrollo Local

Si deseas contribuir al proyecto o ejecutar una copia en tu propio entorno, sigue estos pasos.

### 1. Requisitos Previos

- Tener instalado un entorno de desarrollo web como [XAMPP](https://www.apachefriends.org/es/index.html), WAMP o MAMP. Esto proporcionará Apache, MySQL y PHP.
- Un gestor de bases de datos como phpMyAdmin (incluido en XAMPP).

### 2. Instalación

1.  **Clonar el repositorio** en el directorio `htdocs` de tu instalación de XAMPP:
    ```bash
    git clone <URL_DEL_REPOSITORIO> C:/xampp/htdocs/CREATINET
    ```

2.  **Configurar la Base de Datos:**
    - Inicia los servicios de Apache y MySQL desde el panel de control de XAMPP.
    - Abre phpMyAdmin en tu navegador (`http://localhost/phpmyadmin`).
    - Crea una nueva base de datos. Puedes llamarla `creatinet_db`.
    - Selecciona la base de datos recién creada y ve a la pestaña "Importar".
    - Sube e importa el archivo `dbs14399138.sql` que se encuentra en la raíz del proyecto.

3.  **Configurar la Conexión:**
    - Abre el archivo `config/database.php`.
    - Modifica las credenciales de conexión (`$servername`, `$username`, `$password`, `$dbname`) para que coincidan con tu configuración de MySQL y el nombre de la base de datos que creaste en el paso anterior.

4.  **Acceder a la versión local:**
    - ¡Listo! Abre tu navegador y visita `http://localhost/CREATINET/`.

## 📂 Estructura del Proyecto

```
CREATINET/
├── api/            # Endpoints para llamadas asíncronas (likes, eliminar trabajos)
├── config/         # Configuración de la base de datos
├── controllers/    # Lógica de negocio y control de vistas
├── css/            # Hojas de estilo
├── data/           # Clases de acceso a datos y librerías (PHPMailer)
├── img/            # Recursos gráficos (logos, trabajos subidos)
├── js/             # Scripts de JavaScript para el frontend
├── .htaccess       # Reglas de reescritura de URL para Apache
├── index.php       # Punto de entrada principal de la aplicación
├── login.php       # Página de inicio de sesión
└── ...             # Otros archivos y directorios
```
