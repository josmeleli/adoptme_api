# 🗺️ MAPA COMPLETO DE LA API - AdoptMe

## 📊 Vista General del Sistema

```
AdoptMe API
│
├── 👤 AUTENTICACIÓN
│   ├── POST /registro.php
│   ├── POST /verify.php
│   ├── POST /login.php (✨ Ahora incluye 'role')
│   └── POST /logout.php
│
├── 🐾 MASCOTAS (Sprint 2)
│   ├── GET /pets/getPets.php
│   │   ├── ?search= (genérico: especie, raza, sexo, edad, nombre)
│   │   ├── ?especie=
│   │   ├── ?sexo=
│   │   ├── ?raza=
│   │   ├── ?edad= / ?edad_min= / ?edad_max=
│   │   ├── ?urgencia=
│   │   └── ?page= / ?per_page=
│   │
│   └── GET /pets/getPetDetails.php?id=X
│
├── 📝 SOLICITUDES DE ADOPCIÓN (Sprint 3 - NUEVO)
│   │
│   ├── 👤 USUARIO
│   │   ├── GET /adoptions/checkActiveRequest.php
│   │   │   └── Validación CA-002: ¿Puede aplicar?
│   │   │
│   │   ├── POST /adoptions/createRequest.php
│   │   │   ├── 29 campos (5 pantallas)
│   │   │   ├── Validaciones: usuario, mascota, edad, formatos
│   │   │   └── Notifica a admins
│   │   │
│   │   ├── GET /adoptions/getMyRequests.php
│   │   │   ├── ?user_id=X
│   │   │   ├── ?status= (opcional)
│   │   │   └── Retorna: lista + estadísticas
│   │   │
│   │   └── GET /adoptions/getRequestDetails.php
│   │       ├── ?request_id=X&user_id=Y
│   │       └── Retorna: datos organizados por pantallas
│   │
│   └── 👨‍💼 ADMINISTRADOR (SOLO role='admin')
│       ├── GET /admin/getAdoptionRequests.php
│       │   ├── ?admin_id=X (REQUERIDO)
│       │   ├── ?status= (pendiente, en_revision, aprobada, rechazada)
│       │   ├── ?urgencia= (Si, No)
│       │   ├── ?page= / ?per_page=
│       │   └── Retorna: lista + paginación + stats
│       │
│       ├── GET /admin/getRequestDetails.php
│       │   ├── ?admin_id=X&request_id=Y
│       │   └── Retorna: TODO + historial usuario
│       │
│       ├── POST /admin/updateRequestStatus.php
│       │   ├── Body: admin_id, request_id, new_status, notas_admin
│       │   ├── Estados: en_revision, aprobada, rechazada
│       │   └── Si aprueba:
│       │       ├── Mascota → "En Proceso de Adopción"
│       │       ├── Otras solicitudes → Auto-rechazadas
│       │       └── Todos notificados
│       │
│       └── GET /admin/getNotifications.php
│           ├── ?admin_id=X
│           ├── ?unread_only=true
│           └── Retorna: notificaciones + unread_count
│
└── 🔔 NOTIFICACIONES (In-App)
    └── Tabla: notifications
        ├── nueva_solicitud (para admins)
        ├── solicitud_en_revision (para usuarios)
        ├── solicitud_aprobada (para usuarios)
        └── solicitud_rechazada (para usuarios)
```

---

## 🗄️ BASE DE DATOS

### Tablas (9 total):

```sql
adoptme
│
├── users (actualizada en Sprint 3)
│   ├── id, email, nombres, apellidos, dni, telefono
│   ├── password_hash, is_verified
│   └── role ENUM('user', 'admin') DEFAULT 'user'  ⬅️ NUEVO
│
├── verification_codes
│   └── Códigos de verificación por email
│
├── sessions
│   └── Tokens de autenticación
│
├── pets (12 mascotas de ejemplo)
│   ├── name, especie, raza, edad, sexo
│   ├── color, tamano, peso_aprox
│   ├── descripcion, personalidad, cuidados_especiales
│   ├── image_url, urgencia, prioridad
│   └── estado ENUM('Disponible', 'Adoptado', 'En Proceso de Adopción')
│
├── user_preferences
│   └── Preferencias de búsqueda de usuarios
│
├── adoptions
│   └── Registro histórico de adopciones completadas
│
├── messages
│   └── Sistema de mensajería
│
├── notifications
│   ├── id, user_id, type, title, message
│   ├── related_id, is_read
│   └── created_at
│
└── adoption_requests ⬅️ NUEVO Sprint 3
    ├── IDs: id, user_id, pet_id
    │
    ├── PANTALLA 1: Información Personal
    │   ├── nombres_completos
    │   ├── email, telefono
    │   ├── fecha_nacimiento
    │   └── direccion_completa, ciudad, distrito
    │
    ├── PANTALLA 2: Información del Hogar
    │   ├── tipo_vivienda
    │   ├── propietario_acepta_mascotas
    │   ├── miembros_familia
    │   ├── hay_ninos
    │   └── alergias_familia
    │
    ├── PANTALLA 3: Experiencia con Mascotas
    │   ├── tiene_otras_mascotas
    │   ├── descripcion_otras_mascotas
    │   ├── experiencia_previa
    │   ├── tiempo_sola_mascota
    │   ├── tiene_veterinario
    │   └── presupuesto_mensual
    │
    ├── PANTALLA 4: Motivación y Compromiso
    │   ├── motivacion_adopcion
    │   ├── conocimiento_raza
    │   ├── dispuesto_entrenar
    │   └── compromiso_largo_plazo
    │
    └── CONTROL
        ├── status ENUM('pendiente','en_revision','aprobada','rechazada')
        ├── notas_admin
        ├── fecha_revision
        ├── revisado_por (FK → users.id)
        └── created_at, updated_at
```

**Total: 29 campos de formulario + control**

---

## 🔐 SISTEMA DE ROLES

### Role: `user` (default)
**Puede:**
- ✅ Ver catálogo de mascotas
- ✅ Ver detalles de mascotas
- ✅ Verificar si puede aplicar (checkActiveRequest)
- ✅ Crear solicitud de adopción
- ✅ Ver sus propias solicitudes
- ✅ Ver detalles de sus solicitudes
- ✅ Recibir notificaciones

**NO puede:**
- ❌ Acceder a `/admin/*`
- ❌ Ver solicitudes de otros usuarios
- ❌ Aprobar/rechazar solicitudes
- ❌ Cambiar estado de mascotas

### Role: `admin`
**Puede TODO lo de user +:**
- ✅ Acceder a `/admin/*`
- ✅ Ver TODAS las solicitudes
- ✅ Ver detalles completos de cualquier solicitud
- ✅ Aprobar/rechazar solicitudes
- ✅ Cambiar estado de mascotas (automático al aprobar)
- ✅ Ver notificaciones de nuevas solicitudes
- ✅ Ver historial de usuarios

### Usuario Admin Creado:
```
Email: admin@adoptme.com
Password: Admin123!
ID: 9
Role: admin
is_verified: 1
```

---

## 🔄 FLUJOS PRINCIPALES

### Flujo 1: Registro y Login

```
1. POST /registro.php
   └── Crea usuario con role='user' (default)

2. POST /verify.php
   └── Verifica email

3. POST /login.php
   └── Retorna: { user: { role: "user" } }

Frontend: Guardar role localmente
```

---

### Flujo 2: Buscar y Ver Mascotas

```
1. GET /pets/getPets.php?search=labrador
   └── Busca en: especie, raza, sexo, edad, nombre

2. GET /pets/getPetDetails.php?id=5
   └── Detalles completos de Rocky

3. Usuario decide adoptar → Ir a Flujo 3
```

---

### Flujo 3: Solicitar Adopción (Usuario)

```
1. GET /adoptions/checkActiveRequest.php?user_id=1&pet_id=5
   ├── can_apply: true → Continuar
   └── can_apply: false → Mostrar error

2. Frontend: Formulario 5 pantallas
   ├── Pantalla 1: Info Personal (7 campos)
   ├── Pantalla 2: Hogar (5 campos)
   ├── Pantalla 3: Experiencia (6 campos)
   ├── Pantalla 4: Motivación (4 campos)
   └── Pantalla 5: Revisión (mostrar resumen)

3. POST /adoptions/createRequest.php
   ├── Validaciones backend
   ├── Inserta en BD
   └── Notifica a admins

4. Frontend: Mostrar mensaje de éxito
   "Tu solicitud será revisada en 3-4 días hábiles"

5. Usuario puede ver estado:
   GET /adoptions/getMyRequests.php?user_id=1
```

---

### Flujo 4: Revisar Solicitudes (Admin)

```
1. POST /login.php (admin@adoptme.com)
   └── Verifica: user.role === 'admin'

2. GET /admin/getNotifications.php?admin_id=9&unread_only=true
   └── Badge: "5 solicitudes nuevas"

3. GET /admin/getAdoptionRequests.php?admin_id=9&status=pendiente
   └── Lista ordenada por:
       ├── 1. Urgencia de mascota (Si primero)
       ├── 2. Estado (pendiente > en_revision > etc)
       └── 3. Fecha (más recientes primero)

4. Admin hace clic en solicitud:
   GET /admin/getRequestDetails.php?admin_id=9&request_id=15
   └── Ve TODO el formulario + historial usuario

5. Admin decide:

   OPCIÓN A - APROBAR:
   POST /admin/updateRequestStatus.php
   {
     "admin_id": 9,
     "request_id": 15,
     "new_status": "aprobada",
     "notas_admin": "Excelente perfil"
   }
   
   Sistema automático:
   ├── Solicitud → "aprobada"
   ├── Mascota → "En Proceso de Adopción"
   ├── Otras solicitudes para esa mascota → "rechazada"
   ├── Usuario aprobado → Notificación: "¡Aprobada!"
   └── Otros usuarios → Notificación: "Rechazada (mascota adoptada)"

   OPCIÓN B - RECHAZAR:
   POST /admin/updateRequestStatus.php
   {
     "admin_id": 9,
     "request_id": 15,
     "new_status": "rechazada",
     "notas_admin": "Espacio insuficiente"
   }
   
   Sistema:
   ├── Solicitud → "rechazada"
   └── Usuario → Notificación con motivo
```

---

## 📱 INTEGRACIÓN FRONTEND

### Android URLs:
```kotlin
// Desarrollo local
const val BASE_URL = "http://10.0.2.2/adopciones_api/"

// Producción
const val BASE_URL = "http://tu-servidor.com/adopciones_api/"
```

### Headers requeridos:
```kotlin
"Content-Type: application/json"
```

### Manejo de Roles:
```kotlin
// Al hacer login
data class LoginResponse(
    val success: Boolean,
    val user: User
)

data class User(
    val id: Int,
    val email: String,
    val nombres: String,
    val role: String  // "user" o "admin"
)

// Guardar localmente
SharedPreferences.put("user_role", user.role)

// Condicional en UI
if (userRole == "admin") {
    // Mostrar botón "Panel de Admin"
}

// Validar antes de llamar endpoints admin
if (userRole != "admin") {
    // No permitir acceso a /admin/*
}
```

### Ejemplo de Llamada (Kotlin):
```kotlin
// Check si puede aplicar
suspend fun checkActiveRequest(userId: Int, petId: Int): CanApplyResponse {
    return api.get("adoptions/checkActiveRequest.php") {
        parameter("user_id", userId)
        parameter("pet_id", petId)
    }
}

// Crear solicitud
suspend fun createRequest(request: AdoptionRequestBody): CreateResponse {
    return api.post("adoptions/createRequest.php") {
        contentType(ContentType.Application.Json)
        setBody(request)
    }
}

// Panel admin (solo si role='admin')
suspend fun getAdminRequests(adminId: Int, status: String? = null): AdminRequestsResponse {
    return api.get("admin/getAdoptionRequests.php") {
        parameter("admin_id", adminId)
        status?.let { parameter("status", it) }
    }
}
```

---

## 🎨 ESTADOS Y COLORES

### Estados de Solicitud:
```kotlin
enum class RequestStatus(val text: String, val color: String) {
    PENDIENTE("Pendiente de revisión", "#FFA500"),      // Naranja
    EN_REVISION("En revisión", "#2196F3"),             // Azul
    APROBADA("Aprobada", "#4CAF50"),                   // Verde
    RECHAZADA("Rechazada", "#F44336")                  // Rojo
}
```

### Estados de Mascota:
```kotlin
enum class PetStatus(val text: String) {
    DISPONIBLE("Disponible"),
    EN_PROCESO("En Proceso de Adopción"),
    ADOPTADO("Adoptado")
}
```

### Urgencia:
```kotlin
if (pet.urgencia == "Si") {
    // Mostrar badge "URGENTE" en rojo
    // Priorizar en lista
}
```

---

## 🧪 TESTING CHECKLIST

### Usuario Normal:
- [ ] Registro y verificación
- [ ] Login (verificar que retorne role: "user")
- [ ] Ver catálogo con filtro genérico ?search=
- [ ] Ver detalles de mascota
- [ ] Verificar si puede aplicar (checkActiveRequest)
- [ ] Intentar solicitar 2 veces la misma mascota → debe fallar
- [ ] Crear solicitud exitosa (29 campos)
- [ ] Ver historial de solicitudes
- [ ] Ver detalles de solicitud
- [ ] Filtrar por estado (pendiente, aprobada, etc.)
- [ ] Recibir notificación cuando admin responda

### Admin:
- [ ] Login (verificar que retorne role: "admin")
- [ ] Ver panel de solicitudes
- [ ] Ver badge de notificaciones no leídas
- [ ] Filtrar por estado
- [ ] Filtrar por urgencia
- [ ] Ordenamiento correcto (urgencia > estado > fecha)
- [ ] Ver detalles completos de solicitud
- [ ] Ver historial del usuario solicitante
- [ ] Poner en revisión
- [ ] Aprobar solicitud
  - [ ] Verificar que mascota cambie a "En Proceso"
  - [ ] Verificar que otras solicitudes se rechacen
  - [ ] Usuario aprobado recibe notificación
  - [ ] Otros usuarios reciben notificación de rechazo
- [ ] Rechazar solicitud
  - [ ] Usuario recibe notificación con motivo
- [ ] Ver notificaciones

### Validaciones:
- [ ] Usuario no verificado → error
- [ ] Menor de 18 años → error
- [ ] Email inválido → error
- [ ] Teléfono no 9 dígitos → error
- [ ] Mascota no disponible → error
- [ ] Campo requerido faltante → error 400
- [ ] Usuario normal intenta /admin/* → error 403
- [ ] Solicitud duplicada → error CA-002

---

## 📊 ESTADÍSTICAS DEL PROYECTO

### Endpoints Totales: **18**
- Autenticación: 4
- Mascotas: 2
- Adopciones Usuario: 4
- Adopciones Admin: 4
- Otros: 4

### Archivos PHP: **18**
### Tablas BD: **9**
### Documentación: **4 archivos** (95+ KB)

### Sprint 3 Específicamente:
- **Archivos creados:** 8 (4 usuario + 4 admin)
- **Líneas de código:** ~2,000
- **Campos de formulario:** 29
- **Validaciones:** 10+
- **Notificaciones automáticas:** 4 tipos

---

## 🚀 ESTADO DEL PROYECTO

### ✅ Completado:
- Sprint 1: Autenticación y Base
- Sprint 2: Catálogo de Mascotas
- Sprint 3: Sistema de Solicitud de Adopción

### 🔄 En Desarrollo:
- Frontend Android (equipo Android)

### 📋 Pendiente (Sprints Futuros):
- Sprint 4: Mensajería usuario-admin
- Sprint 5: Upload de documentos
- Sprint 6: Seguimiento post-adopción

---

## 📞 SOPORTE

### Documentación:
1. **SPRINT3_DOCUMENTATION.md** - Técnica completa
2. **SPRINT3_POSTMAN_EXAMPLES.md** - Ejemplos de uso
3. **SPRINT3_COMPLETION.md** - Resumen ejecutivo
4. **API_MAP.md** - Este archivo (mapa visual)

### Contacto:
- GitHub Copilot - Asistente de desarrollo
- Repositorio: `c:\Users\USUARIO\Desktop\adopciones api\adopciones_api\`
- Producción: `C:\xampp\htdocs\adopciones_api\`

---

**Última actualización:** 15 Enero 2025  
**Versión API:** 1.3.0  
**Estado:** LISTO PARA INTEGRACIÓN

🐾 **AdoptMe - Sistema completo de adopción de mascotas** 🐾
