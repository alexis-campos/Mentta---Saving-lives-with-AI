# Mentta - Apoyo Emocional con IA

Sistema de apoyo emocional 24/7 que combina IA conversacional con supervisión profesional de psicólogos.

## Requisitos

- PHP 8.0+
- MySQL 5.7+ / MariaDB 10.3+
- XAMPP o servidor web con soporte PHP

## Instalación

### 1. Crear la base de datos

```bash
# En MySQL/MariaDB
mysql -u root -p < database/schema.sql
mysql -u root -p mentta < database/seed.sql
```

O desde phpMyAdmin:
1. Crear base de datos `mentta`
2. Importar `database/schema.sql`
3. Importar `database/seed.sql`

### 2. Configurar la aplicación

Editar `includes/config.php`:
- Credenciales de base de datos
- API Key de Google Gemini

### 3. Crear carpeta de logs

```bash
mkdir logs
chmod 755 logs
```

### 4. Acceder a la aplicación

- URL: http://localhost/Mentta%20-%20Salvando%20vidas%20con%20la%20IA/

## Usuarios de Prueba

### Psicólogos
- psicologo1@mentta.com / Demo2025
- psicologo2@mentta.com / Demo2025

### Pacientes
- paciente1@mentta.com / Demo2025
- paciente2@mentta.com / Demo2025
- paciente3@mentta.com / Demo2025
- paciente4@mentta.com / Demo2025
- paciente5@mentta.com / Demo2025

## Estructura del Proyecto

```
mentta/
├── api/                 # Endpoints REST (JSON)
├── assets/              # CSS, JS, imágenes
├── database/            # Schema y seed SQL
├── includes/            # Core PHP (config, auth, db)
├── logs/                # Archivos de log
└── test/                # Scripts de prueba
```

## Testing

```bash
# Probar conexión a BD
php test/test-db.php

# Probar autenticación
php test/test-auth.php
```

## Licencia

Proyecto privado - Todos los derechos reservados.
