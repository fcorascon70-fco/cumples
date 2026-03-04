# Visor de Cumpleaños

Aplicación web sencilla para visualizar y filtrar cumpleaños de miembros desde una base de datos MySQL.

## Requisitos
- Servidor PHP 7.4+
- Base de datos MySQL

## Instalación

1. Clona este repositorio.
2. Copia el archivo `config.php.example` a `config.php`.
   ```bash
   cp config.php.example config.php
   ```
3. Edita `config.php` con tus credenciales de base de datos.
4. Asegúrate de tener una tabla `usuarios` y una tabla `miembros` en tu base de datos.
5. Sube los archivos a tu servidor web.

## Seguridad
- El archivo `config.php` está en el `.gitignore` para evitar subir credenciales sensibles.
- Las contraseñas se manejan mediante sesiones de PHP.
- Se incluyen cabeceras de seguridad básicas para proteger contra ataques comunes.

## Licencia
MIT
