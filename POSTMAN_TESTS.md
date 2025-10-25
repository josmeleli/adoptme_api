# 🚀 Guía de Pruebas con Postman - API AdoptMe

## 📍 URL Base
```
http://localhost/adopciones_api
```

---

## 🧪 PRUEBAS PASO A PASO

### ✅ 1. Test de Conexión
**Método:** GET  
**URL:** `http://localhost/adopciones_api/test_connection.php`  

**Respuesta esperada:**
```json
{
  "status": "success",
  "message": "Database connection successful!",
  "database": "adoptme",
  "host": "127.0.0.1"
}
```

---

### ✅ 2. Registro de Usuario (HU-001)
**Método:** POST  
**URL:** `http://localhost/adopciones_api/register.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "email": "juan.perez@example.com",
  "password": "mipassword123",
  "name": "Juan Pérez",
  "phone": "987654321"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "user_id": 1,
  "message": "Usuario registrado. Revisa tu correo para el código de verificación.",
  "verification_code": "123456"
}
```

**⚠️ IMPORTANTE:** Guarda el `user_id` y `verification_code` para los siguientes pasos.

---

### ✅ 3. Verificar Cuenta
**Método:** POST  
**URL:** `http://localhost/adopciones_api/verify.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "user_id": 1,
  "code": "123456"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Usuario verificado correctamente"
}
```

---

### ✅ 4. Login (HU-002)
**Método:** POST  
**URL:** `http://localhost/adopciones_api/login.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "email": "juan.perez@example.com",
  "password": "mipassword123"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "token": "eyJ1c2VyX2lkIjoxLCJlbWFpbCI6Imp...",
  "user": {
    "id": 1,
    "email": "juan.perez@example.com",
    "name": "Juan Pérez"
  },
  "expires_at": "2025-11-01 10:30:00"
}
```

**⚠️ IMPORTANTE:** Guarda el `token` para las peticiones autenticadas.

---

### ✅ 5. Obtener Perfil (HU-003)
**Método:** GET  
**URL:** `http://localhost/adopciones_api/users/getUser.php?id=1`  

**Respuesta esperada:**
```json
{
  "id": 1,
  "email": "juan.perez@example.com",
  "name": "Juan Pérez",
  "phone": "987654321",
  "distrito": null,
  "created_at": "2025-10-25 10:00:00",
  "preferences": {
    "especie": null,
    "tamano": null,
    "edad": null
  }
}
```

---

### ✅ 6. Actualizar Perfil (HU-003)
**Método:** POST  
**URL:** `http://localhost/adopciones_api/users/updateUser.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "id": 1,
  "name": "Juan Pérez López",
  "distrito": "San Isidro",
  "phone": "987654321",
  "especie": "Perro",
  "tamano": "Mediano",
  "edad": "Adulto"
}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Perfil actualizado correctamente"
}
```

---

### ✅ 7. Verificar Perfil Actualizado
**Método:** GET  
**URL:** `http://localhost/adopciones_api/users/getUser.php?id=1`  

**Respuesta esperada:**
```json
{
  "id": 1,
  "email": "juan.perez@example.com",
  "name": "Juan Pérez López",
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

### ✅ 8. Enviar Mensaje (Chat)
**Método:** POST  
**URL:** `http://localhost/adopciones_api/chat/sendMessage.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "from": 1,
  "to": 2,
  "message": "Hola, me interesa adoptar a Luna"
}
```

---

### ✅ 9. Obtener Mensajes
**Método:** GET  
**URL:** `http://localhost/adopciones_api/chat/getMessages.php?user1=1&user2=2`  

---

### ✅ 10. Crear Adopción
**Método:** POST  
**URL:** `http://localhost/adopciones_api/adoption/createAdoption.php`  
**Headers:**
```
Content-Type: application/json
```

**Body (raw JSON):**
```json
{
  "pet_id": 1,
  "user_id": 1
}
```

---

### ✅ 11. Cerrar Sesión
**Método:** POST  
**URL:** `http://localhost/adopciones_api/logout.php`  
**Headers:**
```
Content-Type: application/json
Authorization: Bearer {tu_token_aqui}
```

**Respuesta esperada:**
```json
{
  "success": true,
  "message": "Sesión cerrada correctamente"
}
```

---

## 🎯 PRUEBAS DE VALIDACIÓN

### ❌ Registro con email duplicado
**Método:** POST  
**URL:** `http://localhost/adopciones_api/register.php`  
**Body:**
```json
{
  "email": "juan.perez@example.com",
  "password": "123456",
  "name": "Otro Usuario",
  "phone": "999888777"
}
```

**Respuesta esperada (409):**
```json
{
  "error": "El email ya está registrado"
}
```

---

### ❌ Login con credenciales incorrectas
**Método:** POST  
**URL:** `http://localhost/adopciones_api/login.php`  
**Body:**
```json
{
  "email": "juan.perez@example.com",
  "password": "passwordincorrecto"
}
```

**Respuesta esperada (401):**
```json
{
  "error": "Credenciales inválidas"
}
```

---

### ❌ Email inválido
**Método:** POST  
**URL:** `http://localhost/adopciones_api/register.php`  
**Body:**
```json
{
  "email": "emailinvalido",
  "password": "123456",
  "name": "Test",
  "phone": "987654321"
}
```

**Respuesta esperada (400):**
```json
{
  "error": "Email válido es requerido"
}
```

---

## 📦 Importar Colección a Postman

Si quieres, puedo crear un archivo `.json` de colección de Postman para importar directamente.

---

## ✅ Checklist de Pruebas

- [ ] Test de conexión DB
- [ ] Registro de usuario nuevo
- [ ] Verificación de cuenta
- [ ] Login exitoso
- [ ] Obtener perfil
- [ ] Actualizar perfil con preferencias
- [ ] Verificar perfil actualizado
- [ ] Enviar mensaje
- [ ] Crear adopción
- [ ] Cerrar sesión
- [ ] Validar email duplicado
- [ ] Validar credenciales incorrectas
- [ ] Validar email inválido

---

## 🔧 Troubleshooting

**Si recibes error de conexión:**
1. Verifica que XAMPP/Apache esté corriendo
2. Verifica que MySQL esté corriendo
3. Ejecuta: `http://localhost/adopciones_api/test_connection.php`

**Si recibes error 404:**
1. Verifica que los archivos estén en `C:\xampp\htdocs\adopciones_api\`
2. Verifica la URL: debe ser `http://localhost/adopciones_api/` (sin xampp)

**Si recibes error de BD:**
1. Verifica que la BD `adoptme` exista
2. Ejecuta: `php install_database.php` en la carpeta del proyecto
