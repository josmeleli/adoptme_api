# 📱 API AdoptMe - Documentación para Android (Java)

## 🔗 URL Base
```
http://localhost/adopciones_api
```

---

## 📝 REGISTRO DE USUARIO

### Endpoint
```
POST /register.php
```

### Campos requeridos desde Android
```java
JSONObject jsonBody = new JSONObject();
jsonBody.put("nombres", "Juan");              // String - Solo letras y espacios
jsonBody.put("apellidos", "Perez Garcia");    // String - Solo letras y espacios
jsonBody.put("dni", "12345678");              // String - Exactamente 8 dígitos
jsonBody.put("email", "juan@example.com");    // String - Email válido
jsonBody.put("telefono", "987654321");        // String - Exactamente 9 dígitos
jsonBody.put("password", "mipassword123");    // String - Mínimo 6 caracteres
```

### Validaciones aplicadas

| Campo | Validación | Mensaje de error |
|-------|-----------|------------------|
| **nombres** | Solo letras y espacios, no vacío | "Nombres inválidos. Solo se permiten letras y espacios" |
| **apellidos** | Solo letras y espacios, no vacío | "Apellidos inválidos. Solo se permiten letras y espacios" |
| **dni** | Exactamente 8 dígitos numéricos | "DNI inválido. Debe tener exactamente 8 números" |
| **email** | Formato de email válido | "Correo electrónico inválido" |
| **telefono** | Exactamente 9 dígitos numéricos | "Teléfono inválido. Debe tener exactamente 9 números" |
| **password** | Mínimo 6 caracteres | "Contraseña debe tener al menos 6 caracteres" |

### Validaciones de duplicados

- ✅ Email único: "El correo electrónico ya está registrado" (HTTP 409)
- ✅ DNI único: "El DNI ya está registrado" (HTTP 409)

### Respuesta exitosa (HTTP 200)
```json
{
  "success": true,
  "user_id": "1",
  "nombres": "Juan",
  "apellidos": "Perez Garcia",
  "email": "juan@example.com",
  "message": "Usuario registrado exitosamente. Revisa tu correo para el código de verificación.",
  "verification_code": "123456"
}
```

### Ejemplo en Android (Java)
```java
// Crear el JSON body
JSONObject jsonBody = new JSONObject();
try {
    jsonBody.put("nombres", edtNombres.getText().toString().trim());
    jsonBody.put("apellidos", edtApellidos.getText().toString().trim());
    jsonBody.put("dni", edtDNI.getText().toString().trim());
    jsonBody.put("email", edtEmail.getText().toString().trim());
    jsonBody.put("telefono", edtTelefono.getText().toString().trim());
    jsonBody.put("password", edtPassword.getText().toString());
} catch (JSONException e) {
    e.printStackTrace();
}

// Hacer petición POST
String url = "http://localhost/adopciones_api/register.php";
JsonObjectRequest request = new JsonObjectRequest(
    Request.Method.POST, 
    url, 
    jsonBody,
    new Response.Listener<JSONObject>() {
        @Override
        public void onResponse(JSONObject response) {
            try {
                if (response.getBoolean("success")) {
                    int userId = response.getInt("user_id");
                    String verificationCode = response.getString("verification_code");
                    // Guardar userId y código para verificación
                    Toast.makeText(context, "Registro exitoso", Toast.LENGTH_SHORT).show();
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    },
    new Response.ErrorListener() {
        @Override
        public void onErrorResponse(VolleyError error) {
            try {
                String errorMsg = new JSONObject(new String(error.networkResponse.data))
                    .getString("error");
                Toast.makeText(context, errorMsg, Toast.LENGTH_LONG).show();
            } catch (Exception e) {
                Toast.makeText(context, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        }
    }
);

// Agregar a la cola de peticiones
RequestQueue queue = Volley.newRequestQueue(this);
queue.add(request);
```

---

## 🔐 LOGIN

### Endpoint
```
POST /login.php
```

### Campos requeridos desde Android
```java
JSONObject jsonBody = new JSONObject();
jsonBody.put("email", "juan@example.com");     // String - Email válido
jsonBody.put("password", "mipassword123");     // String - No vacío
```

### Validaciones aplicadas

| Campo | Validación | Mensaje de error |
|-------|-----------|------------------|
| **email** | No vacío, formato válido | "El correo electrónico es requerido" / "Correo electrónico inválido" |
| **password** | No vacío | "La contraseña es requerida" |

### Respuesta exitosa (HTTP 200)
```json
{
  "success": true,
  "token": "eyJ1c2VyX2lkIjoxLCJlbWFpbCI6Imp1YW5AZXhhbXBsZS5jb20iLCJpYXQiOjE3NjE0MDc5OTcsImV4cCI6MTc2MjAxMjc5N30=",
  "user": {
    "id": 1,
    "email": "juan@example.com",
    "nombres": "Juan",
    "apellidos": "Perez Garcia",
    "dni": "12345678",
    "telefono": "987654321"
  },
  "expires_at": "2025-11-01 16:59:57"
}
```

### Errores posibles

| HTTP | Error | Descripción |
|------|-------|-------------|
| 400 | "El correo electrónico es requerido" | Email vacío |
| 400 | "Correo electrónico inválido" | Formato de email incorrecto |
| 400 | "La contraseña es requerida" | Password vacío |
| 401 | "Correo electrónico o contraseña incorrectos" | Credenciales inválidas |
| 403 | "Debes verificar tu correo antes de iniciar sesión" | Cuenta no verificada |

### Ejemplo en Android (Java)
```java
// Crear el JSON body
JSONObject jsonBody = new JSONObject();
try {
    jsonBody.put("email", edtEmail.getText().toString().trim());
    jsonBody.put("password", edtPassword.getText().toString());
} catch (JSONException e) {
    e.printStackTrace();
}

// Hacer petición POST
String url = "http://localhost/adopciones_api/login.php";
JsonObjectRequest request = new JsonObjectRequest(
    Request.Method.POST, 
    url, 
    jsonBody,
    new Response.Listener<JSONObject>() {
        @Override
        public void onResponse(JSONObject response) {
            try {
                if (response.getBoolean("success")) {
                    String token = response.getString("token");
                    JSONObject user = response.getJSONObject("user");
                    
                    // Guardar token y datos de usuario
                    SharedPreferences prefs = getSharedPreferences("AdoptMe", MODE_PRIVATE);
                    SharedPreferences.Editor editor = prefs.edit();
                    editor.putString("token", token);
                    editor.putInt("user_id", user.getInt("id"));
                    editor.putString("nombres", user.getString("nombres"));
                    editor.putString("apellidos", user.getString("apellidos"));
                    editor.putString("email", user.getString("email"));
                    editor.apply();
                    
                    // Ir a MainActivity
                    Intent intent = new Intent(LoginActivity.this, MainActivity.class);
                    startActivity(intent);
                    finish();
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        }
    },
    new Response.ErrorListener() {
        @Override
        public void onErrorResponse(VolleyError error) {
            try {
                String errorMsg = new JSONObject(new String(error.networkResponse.data))
                    .getString("error");
                Toast.makeText(LoginActivity.this, errorMsg, Toast.LENGTH_LONG).show();
            } catch (Exception e) {
                Toast.makeText(LoginActivity.this, "Error de conexión", Toast.LENGTH_SHORT).show();
            }
        }
    }
);

RequestQueue queue = Volley.newRequestQueue(this);
queue.add(request);
```

---

## ✅ VALIDACIONES EN ANDROID (antes de enviar)

```java
// Validar nombres
if (nombres.isEmpty() || !nombres.matches("[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+")) {
    edtNombres.setError("Solo se permiten letras y espacios");
    return false;
}

// Validar apellidos
if (apellidos.isEmpty() || !apellidos.matches("[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+")) {
    edtApellidos.setError("Solo se permiten letras y espacios");
    return false;
}

// Validar DNI (exactamente 8 dígitos)
if (!dni.matches("\\d{8}")) {
    edtDNI.setError("El DNI debe tener exactamente 8 números");
    return false;
}

// Validar email
if (!android.util.Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
    edtEmail.setError("Correo electrónico inválido");
    return false;
}

// Validar teléfono (exactamente 9 dígitos)
if (!telefono.matches("\\d{9}")) {
    edtTelefono.setError("El teléfono debe tener exactamente 9 números");
    return false;
}

// Validar contraseña (mínimo 6 caracteres)
if (password.length() < 6) {
    edtPassword.setError("La contraseña debe tener al menos 6 caracteres");
    return false;
}
```

---

## 📦 Dependencias necesarias en Android

### build.gradle (Module: app)
```gradle
dependencies {
    // Volley para peticiones HTTP
    implementation 'com.android.volley:volley:1.2.1'
    
    // Gson para parsear JSON (opcional)
    implementation 'com.google.code.gson:gson:2.10.1'
}
```

### AndroidManifest.xml
```xml
<!-- Permiso de internet -->
<uses-permission android:name="android.permission.INTERNET" />

<!-- Si usas HTTP en desarrollo (no HTTPS) -->
<application
    android:usesCleartextTraffic="true"
    ...>
```

---

## 🎯 Resumen de cambios vs documentación anterior

| Antes | Ahora |
|-------|-------|
| `name` | `nombres` + `apellidos` |
| `phone` | `telefono` (exactamente 9 dígitos) |
| N/A | `dni` (exactamente 8 dígitos, único) |
| Validación básica | Validaciones estrictas con regex |

---

## ⚠️ Notas importantes

1. **Cambiar URL en producción**: Reemplaza `localhost` por tu IP o dominio
2. **HTTPS recomendado**: En producción usa HTTPS
3. **Guardar token**: Usa SharedPreferences para persistir la sesión
4. **Manejar errores**: Siempre captura y muestra los errores al usuario
5. **Validar en cliente**: Valida los campos ANTES de enviar para mejor UX
