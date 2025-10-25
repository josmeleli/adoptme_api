# 🚀 GUÍA RÁPIDA - Android + AdoptMe API

## 📱 URL BASE
```java
// Emulador Android
String BASE_URL = "http://10.0.2.2/adopciones_api/";

// Dispositivo físico (reemplaza con tu IP)
String BASE_URL = "http://192.168.1.100/adopciones_api/";
```

---

## ⚡ VALIDACIONES IMPORTANTES

| Campo | Validación | Regex Java |
|-------|------------|------------|
| Nombres | Solo letras y espacios | `^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$` |
| Apellidos | Solo letras y espacios | `^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$` |
| DNI | Exactamente 8 dígitos | `^\\d{8}$` |
| Email | Formato válido | `Patterns.EMAIL_ADDRESS` |
| Teléfono | Exactamente 9 dígitos | `^\\d{9}$` |
| Contraseña | Mínimo 6 caracteres | `length >= 6` |

---

## 🔥 FLUJO COMPLETO (3 pasos)

### 1️⃣ REGISTRO
```java
POST /register.php
{
  "nombres": "Juan",
  "apellidos": "Perez",
  "dni": "12345678",
  "email": "juan@email.com",
  "telefono": "987654321",
  "password": "123456"
}

✅ Respuesta:
{
  "success": true,
  "user_id": 1,
  "email_enviado": true,  // Si se envió email
  "verification_code": "123456",  // Solo si email_enviado=false (desarrollo)
  "message": "Usuario registrado..."
}
```

### 2️⃣ VERIFICACIÓN
```java
POST /verify.php
{
  "user_id": 1,
  "code": "123456"
}

✅ Respuesta:
{
  "success": true,
  "message": "Usuario verificado correctamente"
}
```

### 3️⃣ LOGIN
```java
POST /login.php
{
  "email": "juan@email.com",
  "password": "123456"
}

✅ Respuesta:
{
  "success": true,
  "token": "eyJ1c2VyX2lkIjoxLCJlbWFpbCI6Imp1YW4uLi4=",
  "user": {
    "id": 1,
    "email": "juan@email.com",
    "nombres": "Juan",
    "apellidos": "Perez",
    "dni": "12345678",
    "telefono": "987654321"
  }
}
```

---

## 💾 GUARDAR DATOS LOCALMENTE (SharedPreferences)

```java
// Después del login exitoso:
SharedPreferences prefs = getSharedPreferences("AdoptMePrefs", MODE_PRIVATE);
SharedPreferences.Editor editor = prefs.edit();

editor.putString("token", token);
editor.putInt("user_id", user.getInt("id"));
editor.putString("email", user.getString("email"));
editor.putString("nombres", user.getString("nombres"));
editor.putString("apellidos", user.getString("apellidos"));
editor.putString("dni", user.getString("dni"));
editor.putString("telefono", user.getString("telefono"));
editor.apply();
```

---

## 🔐 ENVIAR TOKEN EN PETICIONES

```java
@Override
public Map<String, String> getHeaders() {
    Map<String, String> headers = new HashMap<>();
    headers.put("Authorization", "Bearer " + token);
    return headers;
}
```

---

## ❌ MANEJO DE ERRORES

```java
error -> {
    String errorMessage = "Error";
    
    if (error.networkResponse != null && error.networkResponse.data != null) {
        try {
            String errorData = new String(error.networkResponse.data);
            JSONObject errorJson = new JSONObject(errorData);
            errorMessage = errorJson.optString("error", errorMessage);
        } catch (JSONException e) {
            e.printStackTrace();
        }
    }
    
    Toast.makeText(this, errorMessage, Toast.LENGTH_LONG).show();
}
```

---

## 🔄 CÓDIGOS HTTP

| Código | Significado | Acción |
|--------|-------------|--------|
| 200 | OK | Procesado correctamente |
| 400 | Bad Request | Datos inválidos - mostrar error |
| 401 | Unauthorized | Credenciales incorrectas |
| 403 | Forbidden | **Cuenta no verificada** - ir a VerifyActivity |
| 404 | Not Found | Recurso no existe |
| 500 | Server Error | Error del servidor |

---

## 📧 VERIFICACIÓN - 2 MODOS

### Modo Producción (Email enviado)
```json
{
  "success": true,
  "email_enviado": true,
  "message": "Revisa tu correo para el código..."
}
```
**El usuario debe revisar su email y copiar el código**

### Modo Desarrollo (Email no enviado)
```json
{
  "success": true,
  "email_enviado": false,
  "verification_code": "123456",
  "message": "...código en respuesta para desarrollo"
}
```
**El código viene en la respuesta - útil para testing sin email**

---

## 📂 ESTRUCTURA DE ARCHIVOS ANDROID

```
app/src/main/java/com/tuapp/adoptme/
├── api/
│   └── ApiHelper.java          (Singleton de Volley)
├── models/
│   └── User.java               (Modelo de usuario)
├── activities/
│   ├── SplashActivity.java     (Pantalla inicial)
│   ├── RegisterActivity.java   (Registro)
│   ├── VerifyActivity.java     (Verificación)
│   ├── LoginActivity.java      (Login)
│   └── MainActivity.java       (App principal)
└── utils/
    └── Validator.java          (Validaciones reutilizables)
```

---

## 🎯 CHECKLIST BÁSICO

- [ ] Añadir Volley a build.gradle
- [ ] Permisos de INTERNET en AndroidManifest.xml
- [ ] Crear ApiHelper.java con URL base
- [ ] Implementar RegisterActivity con validaciones
- [ ] Implementar VerifyActivity
- [ ] Implementar LoginActivity
- [ ] Guardar token en SharedPreferences
- [ ] Verificar sesión en SplashActivity
- [ ] Probar flujo completo

---

## 🆘 PROBLEMAS COMUNES

### ❌ "Unable to resolve host"
**Solución:** Verifica que XAMPP esté corriendo y la URL sea correcta
- Emulador: `10.0.2.2`
- Dispositivo: IP de tu PC (ej: `192.168.1.100`)

### ❌ "NetworkOnMainThreadException"
**Solución:** Ya está resuelto con Volley (hace peticiones en background)

### ❌ "Cleartext HTTP traffic not permitted"
**Solución:** Añade en AndroidManifest.xml:
```xml
<application
    android:usesCleartextTraffic="true"
    ...>
```

### ❌ Login falla con "Debes verificar tu correo"
**Solución:** El usuario debe completar la verificación primero
- Código HTTP: 403
- Acción: Redirigir a VerifyActivity con el user_id

---

## 📞 ENDPOINTS MÁS USADOS

```java
// Registro
POST /register.php

// Verificación
POST /verify.php

// Login
POST /login.php

// Perfil
GET /users/getUser.php?user_id=1
PUT /users/updateUser.php

// Logout
POST /logout.php
```

---

## 🎨 EJEMPLO DE VALIDACIÓN JAVA

```java
// Validar DNI
private boolean validarDNI(String dni) {
    Pattern pattern = Pattern.compile("^\\d{8}$");
    return pattern.matcher(dni).matches();
}

// Validar teléfono
private boolean validarTelefono(String telefono) {
    Pattern pattern = Pattern.compile("^\\d{9}$");
    return pattern.matcher(telefono).matches();
}

// Validar nombres/apellidos
private boolean validarNombre(String nombre) {
    Pattern pattern = Pattern.compile("^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$");
    return pattern.matcher(nombre).matches();
}
```

---

**📖 Documentación completa:** Ver `ANDROID_INTEGRATION.md`

**🧪 Pruebas con Postman:** Ver `POSTMAN_TESTS.md`

**📧 Configurar Email:** Ver `CONFIG_EMAIL.md`
