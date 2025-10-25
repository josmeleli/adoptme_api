# 🧪 Ejemplos de JSON para Testing - AdoptMe API

## 📋 ÍNDICE
1. [Registro](#registro)
2. [Verificación](#verificación)
3. [Login](#login)
4. [Perfil](#perfil)
5. [Respuestas de Error](#respuestas-de-error)

---

## 1️⃣ REGISTRO

### ✅ Registro Válido
```json
POST http://localhost/adopciones_api/register.php

{
  "nombres": "María",
  "apellidos": "García López",
  "dni": "87654321",
  "email": "maria@example.com",
  "telefono": "987654321",
  "password": "password123"
}
```

**Respuesta Exitosa (Email enviado):**
```json
{
  "success": true,
  "user_id": "1",
  "nombres": "María",
  "apellidos": "García López",
  "email": "maria@example.com",
  "message": "Usuario registrado exitosamente. Revisa tu correo para el código de verificación.",
  "email_enviado": true
}
```

**Respuesta Exitosa (Email NO enviado - Desarrollo):**
```json
{
  "success": true,
  "user_id": "1",
  "nombres": "María",
  "apellidos": "García López",
  "email": "maria@example.com",
  "verification_code": "564821",
  "email_enviado": false,
  "message": "Usuario registrado exitosamente. Revisa tu correo para el código de verificación. (Email no configurado - código en respuesta para desarrollo)"
}
```

### ❌ Errores Comunes

**DNI inválido (no 8 dígitos):**
```json
{
  "dni": "1234567"  // ❌ Solo 7 dígitos
}

Respuesta:
{
  "error": "DNI debe tener exactamente 8 dígitos numéricos"
}
```

**Teléfono inválido (no 9 dígitos):**
```json
{
  "telefono": "98765432"  // ❌ Solo 8 dígitos
}

Respuesta:
{
  "error": "Teléfono debe tener exactamente 9 dígitos"
}
```

**Email duplicado:**
```json
{
  "email": "maria@example.com"  // Ya existe
}

Respuesta:
{
  "error": "El correo electrónico ya está registrado"
}
```

**DNI duplicado:**
```json
{
  "dni": "87654321"  // Ya existe
}

Respuesta:
{
  "error": "El DNI ya está registrado"
}
```

---

## 2️⃣ VERIFICACIÓN

### ✅ Verificación Válida
```json
POST http://localhost/adopciones_api/verify.php

{
  "user_id": 1,
  "code": "564821"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Usuario verificado correctamente"
}
```

### ❌ Errores Comunes

**Código incorrecto:**
```json
{
  "user_id": 1,
  "code": "999999"
}

Respuesta:
{
  "error": "Código de verificación inválido"
}
```

**Código expirado:**
```json
{
  "user_id": 1,
  "code": "123456"  // Más de 15 minutos
}

Respuesta:
{
  "error": "El código de verificación ha expirado. Solicita uno nuevo"
}
```

---

## 3️⃣ LOGIN

### ✅ Login Válido
```json
POST http://localhost/adopciones_api/login.php

{
  "email": "maria@example.com",
  "password": "password123"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Login exitoso",
  "token": "eyJ1c2VyX2lkIjoxLCJlbWFpbCI6Im1hcmlhQGV4YW1wbGUuY29tIiwiaWF0IjoxNzI5ODY3MjAwLCJleHAiOjE3MzA0NzIwMDB9",
  "user": {
    "id": "1",
    "email": "maria@example.com",
    "nombres": "María",
    "apellidos": "García López",
    "dni": "87654321",
    "telefono": "987654321"
  }
}
```

### ❌ Errores Comunes

**Credenciales incorrectas:**
```json
{
  "email": "maria@example.com",
  "password": "wrongpassword"
}

Respuesta (HTTP 401):
{
  "error": "Correo electrónico o contraseña incorrectos"
}
```

**Cuenta no verificada:**
```json
{
  "email": "maria@example.com",
  "password": "password123"
}

Respuesta (HTTP 403):
{
  "error": "Debes verificar tu correo antes de iniciar sesión",
  "user_id": 1
}
```

---

## 4️⃣ PERFIL

### 📖 Obtener Perfil
```json
GET http://localhost/adopciones_api/users/getUser.php?user_id=1

Headers:
Authorization: Bearer eyJ1c2VyX2lkIjoxLCJlbWFpbCI6...
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "user": {
    "id": "1",
    "email": "maria@example.com",
    "nombres": "María",
    "apellidos": "García López",
    "dni": "87654321",
    "telefono": "987654321",
    "distrito": "Miraflores",
    "preferencias": {
      "especie_preferida": "Perro",
      "tamano_preferido": "Mediano",
      "edad_preferida": "Adulto"
    }
  }
}
```

### ✏️ Actualizar Perfil
```json
PUT http://localhost/adopciones_api/users/updateUser.php

Headers:
Authorization: Bearer eyJ1c2VyX2lkIjoxLCJlbWFpbCI6...

Body:
{
  "user_id": 1,
  "nombres": "María Elena",
  "apellidos": "García López",
  "distrito": "San Isidro",
  "telefono": "912345678",
  "especie_preferida": "Gato",
  "tamano_preferido": "Pequeño",
  "edad_preferida": "Cachorro"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Perfil actualizado correctamente"
}
```

---

## 5️⃣ LOGOUT

```json
POST http://localhost/adopciones_api/logout.php

Headers:
Authorization: Bearer eyJ1c2VyX2lkIjoxLCJlbWFpbCI6...

Body:
{
  "token": "eyJ1c2VyX2lkIjoxLCJlbWFpbCI6..."
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "message": "Logout exitoso"
}
```

---

## 🔴 RESPUESTAS DE ERROR COMUNES

### HTTP 400 - Bad Request (Datos inválidos)
```json
{
  "error": "Nombres inválidos. Solo se permiten letras y espacios"
}
```

### HTTP 401 - Unauthorized (No autorizado)
```json
{
  "error": "Correo electrónico o contraseña incorrectos"
}
```

### HTTP 403 - Forbidden (Cuenta no verificada)
```json
{
  "error": "Debes verificar tu correo antes de iniciar sesión",
  "user_id": 1
}
```

### HTTP 404 - Not Found
```json
{
  "error": "Usuario no encontrado"
}
```

### HTTP 500 - Internal Server Error
```json
{
  "error": "Error al crear el usuario: [detalles técnicos]"
}
```

---

## 📝 VALIDACIONES - RESUMEN

| Campo | Validación | Ejemplo Válido | Ejemplo Inválido |
|-------|------------|----------------|------------------|
| **nombres** | Solo letras y espacios | "María Elena" | "María123" ❌ |
| **apellidos** | Solo letras y espacios | "García López" | "García-López" ❌ |
| **dni** | Exactamente 8 dígitos | "87654321" | "8765432" ❌ (7 dígitos) |
| **email** | Formato válido | "maria@example.com" | "maria@" ❌ |
| **telefono** | Exactamente 9 dígitos | "987654321" | "98765432" ❌ (8 dígitos) |
| **password** | Mínimo 6 caracteres | "password123" | "pass" ❌ (4 caracteres) |

---

## 🧪 CONJUNTO DE PRUEBAS COMPLETO

### Test 1: Registro exitoso
```bash
curl -X POST http://localhost/adopciones_api/register.php \
  -H "Content-Type: application/json" \
  -d '{"nombres":"Juan","apellidos":"Perez","dni":"12345678","email":"juan@test.com","telefono":"987654321","password":"test1234"}'
```

### Test 2: Verificación exitosa
```bash
curl -X POST http://localhost/adopciones_api/verify.php \
  -H "Content-Type: application/json" \
  -d '{"user_id":1,"code":"564821"}'
```

### Test 3: Login exitoso
```bash
curl -X POST http://localhost/adopciones_api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"juan@test.com","password":"test1234"}'
```

### Test 4: Obtener perfil
```bash
curl -X GET "http://localhost/adopciones_api/users/getUser.php?user_id=1" \
  -H "Authorization: Bearer TU_TOKEN_AQUI"
```

---

## 🎯 CASOS DE USO ANDROID

### Caso 1: Nuevo Usuario
1. POST /register.php → Obtener user_id
2. POST /verify.php → Verificar código
3. POST /login.php → Obtener token
4. Guardar token en SharedPreferences
5. GET /users/getUser.php → Cargar perfil

### Caso 2: Usuario Existente
1. Recuperar token de SharedPreferences
2. GET /users/getUser.php → Verificar sesión
3. Si falla: POST /login.php → Renovar token

### Caso 3: Actualizar Perfil
1. PUT /users/updateUser.php con nuevos datos
2. Actualizar SharedPreferences localmente
3. Mostrar confirmación al usuario

---

**✅ Estos ejemplos están listos para copiar y pegar en tu código Android**
