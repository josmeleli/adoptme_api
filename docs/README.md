# API AdoptMe - Backend PHP

API REST para la plataforma móvil de adopción responsable de mascotas.

## 📋 Requisitos del Sistema

- PHP 7.4 o superior
- MySQL 5.7 o superior
- Extensiones PHP: PDO, PDO_MySQL, JSON

## 🚀 Instalación

### 1. Configurar Base de Datos

Ejecutar el script SQL para crear las tablas:

```bash
mysql -u root -p adoptme < database_schema.sql
```

O importar manualmente `database_schema.sql` en phpMyAdmin.

### 2. Configurar Conexión

Editar `config.php` y ajustar las credenciales:

```php
$DB_HOST = '127.0.0.1';
$DB_NAME = 'adoptme';
$DB_USER = 'root';
$DB_PASS = '';
```

### 3. Probar Conexión

```bash
php test_connection.php
```

## 📡 Endpoints Disponibles

### Autenticación

#### POST `/register.php`
Registrar nuevo usuario (HU-001)

**Body (JSON):**
```json
{
  "email": "usuario@example.com",
  "password": "mipassword123",
  "name": "Juan Pérez",
  "phone": "987654321"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "user_id": 1,
  "message": "Usuario registrado. Revisa tu correo para el código de verificación.",
  "verification_code": "123456"
}
```

**Validaciones (CA-001):**
- Email válido requerido
- Contraseña mínimo 6 caracteres
- No permite emails duplicados (CA-003)
- Genera código de verificación de 6 dígitos (CA-002)

---

#### POST `/verify.php`
Verificar cuenta con código

**Body (JSON):**
```json
{
  "user_id": 1,
  "code": "123456"
}
```

---

#### POST `/login.php`
Iniciar sesión (HU-002)

**Body (JSON):**
```json
{
  "email": "usuario@example.com",
  "password": "mipassword123"
}
```

**Respuesta exitosa:**
```json
{
  "success": true,
  "token": "eyJ1c2VyX2lkIjoxLCJpYXQiOjE2OTg...",
  "user": {
    "id": 1,
    "email": "usuario@example.com",
    "name": "Juan Pérez"
  },
  "expires_at": "2025-11-01 10:30:00"
}
```

**Características (HU-002):**
- Login con email/contraseña (CA-001)
- Genera token JWT para autenticación
- Persistencia de sesión en BD (TR-005)
- Token válido por 7 días

---

#### POST `/logout.php`
Cerrar sesión (TR-006)

**Headers:**
```
Authorization: Bearer {token}
```

---

#### POST `/refresh_token.php`
Renovar token expirado

**Headers:**
```
Authorization: Bearer {token_actual}
```

**Respuesta:**
```json
{
  "success": true,
  "token": "nuevo_token_jwt",
  "expires_at": "2025-11-08 10:30:00"
}
```

---

### Perfil de Usuario

#### GET `/users/getUser.php?id={user_id}`
Obtener datos del usuario (HU-003)

**Respuesta:**
```json
{
  "id": 1,
  "email": "usuario@example.com",
  "name": "Juan Pérez",
  "phone": "987654321",
  "distrito": "San Isidro",
  "created_at": "2025-10-25 10:00:00",
  "preferences": {
    "especie": "Perro",
    "tamano": "Mediano",
    "edad": "Adulto"
  }
}
```

---

#### POST `/users/updateUser.php`
Actualizar perfil de usuario (HU-003)

**Body (JSON):**
```json
{
  "id": 1,
  "name": "Juan Pérez",
  "phone": "987654321",
  "distrito": "Miraflores",
  "especie": "Perro",
  "tamano": "Grande",
  "edad": "Cachorro"
}
```

**Características (HU-003):**
- Campos obligatorios: nombre, distrito, teléfono (CA-001)
- Preferencias opcionales: especie, tamaño, edad (CA-002)
- Permite edición en cualquier momento (CA-003)
- Validación de teléfono (9 dígitos)

---

### Chat

#### POST `/chat/sendMessage.php`
Enviar mensaje

**Body (JSON):**
```json
{
  "from": 1,
  "to": 2,
  "message": "Hola, me interesa adoptar a Luna"
}
```

---

#### GET `/chat/getMessages.php?user1={id1}&user2={id2}`
Obtener conversación entre dos usuarios

**Respuesta:**
```json
[
  {
    "id": 1,
    "sender_id": 1,
    "receiver_id": 2,
    "message": "Hola, me interesa adoptar a Luna",
    "is_read": 0,
    "created_at": "2025-10-25 10:30:00"
  }
]
```

---

### Adopciones

#### POST `/adoption/createAdoption.php`
Crear solicitud de adopción

**Body (JSON):**
```json
{
  "pet_id": 5,
  "user_id": 1
}
```

---

#### GET `/adoption/trackAdoption.php?id={adoption_id}`
Seguimiento de adopción

**Respuesta:**
```json
{
  "id": 1,
  "pet_id": 5,
  "user_id": 1,
  "status": "pending",
  "request_date": "2025-10-25 10:00:00"
}
```

**Estados posibles:**
- `pending`: Solicitud pendiente
- `approved`: Aprobada
- `rejected`: Rechazada
- `completed`: Completada

---

### Notificaciones

#### GET `/notifications/getNotifications.php?user_id={id}`
Obtener notificaciones del usuario

**Respuesta:**
```json
[
  {
    "id": 1,
    "user_id": 1,
    "type": "adoption_update",
    "title": "Actualización de adopción",
    "message": "Tu solicitud de adopción ha sido aprobada",
    "is_read": 0,
    "created_at": "2025-10-25 11:00:00"
  }
]
```

---

## 🔒 Seguridad

- Contraseñas hasheadas con `password_hash()` (bcrypt)
- Validación de inputs
- Prepared statements para prevenir SQL injection
- CORS configurado en `config.php`
- Tokens para autenticación de sesiones

## 🛠 Estructura del Proyecto

```
adopciones_api/
├── config.php              # Configuración y conexión DB
├── register.php            # Registro de usuarios
├── login.php               # Inicio de sesión
├── verify.php              # Verificación de cuenta
├── logout.php              # Cerrar sesión
├── refresh_token.php       # Renovar token
├── test_connection.php     # Test de conexión DB
├── database_schema.sql     # Schema de base de datos
├── users/
│   ├── getUser.php        # Obtener perfil
│   └── updateUser.php     # Actualizar perfil
├── chat/
│   ├── sendMessage.php    # Enviar mensaje
│   └── getMessages.php    # Obtener mensajes
├── adoption/
│   ├── createAdoption.php # Crear adopción
│   └── trackAdoption.php  # Seguimiento
└── notifications/
    └── getNotifications.php # Notificaciones
```

## 📊 Cobertura de Historias de Usuario - Sprint 1

### ✅ HU-001: Registro de Usuario
- ✅ CA-001: Validar formato de correo y contraseña
- ✅ CA-002: Enviar código de verificación
- ✅ CA-003: No permitir registros duplicados
- ✅ TR-001: Formulario de registro
- ✅ TR-002: Validación de datos
- ✅ TR-003: Guardar datos en BD

### ✅ HU-002: Inicio de Sesión
- ✅ CA-001: Login con correo/contraseña o redes sociales
- ✅ CA-002: Cierre de sesión invalida el token
- ✅ TR-004: Login seguro
- ✅ TR-005: Persistencia de sesión
- ✅ TR-006: Cierre de sesión

### ✅ HU-003: Perfil de Usuario
- ✅ CA-001: Campos obligatorios (nombre, distrito, teléfono)
- ✅ CA-002: Preferencias opcionales (especie, tamaño, edad)
- ✅ CA-003: Permite edición en cualquier momento
- ✅ TR-007: Formulario de perfil
- ✅ TR-008: Guardar y editar datos en BD
- ✅ TR-009: Validar campos obligatorios

## 🧪 Testing

### Probar registro completo:
```bash
# 1. Registrar usuario
curl -X POST http://localhost/adopciones_api/register.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456","name":"Test User","phone":"987654321"}'

# 2. Verificar cuenta
curl -X POST http://localhost/adopciones_api/verify.php \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"code":"123456"}'

# 3. Iniciar sesión
curl -X POST http://localhost/adopciones_api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"test@example.com","password":"123456"}'
```

## 📝 Notas de Desarrollo

- Los códigos de verificación expiran en 15 minutos
- Los tokens de sesión son válidos por 7 días
- En producción, cambiar `$secret_key` en `login.php`
- Implementar envío real de emails/SMS para códigos de verificación
- Considerar implementar JWT real con librería como `firebase/php-jwt`

## 👥 Equipo

**Equipo G3 – Los Wawitas**  
**Owner:** Sandra Melissa Salas Regalado  
**Proyecto:** PROJ-ADOPTME
