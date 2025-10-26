# 📋 Sprint 3 - HU-005: Sistema de Solicitud de Adopción

## 🎯 Resumen del Sprint
Sistema completo de solicitudes de adopción con formulario de 5 pantallas, validaciones, y panel de administración.

**Tiempo estimado:** 40 horas  
**Estado:** ✅ COMPLETADO

---

## 📊 Tareas Realizadas

### ✅ TR-08: Implementación del Formulario (15h)
- Tabla `adoption_requests` con 29 campos
- 5 pantallas de información estructurada
- Captura snapshot de datos del solicitante

### ✅ TR-09: Validaciones (5h)
- Usuario verificado
- Mascota disponible
- No solicitudes duplicadas activas (CA-002)
- Formatos de email y teléfono
- Mayor de 18 años

### ✅ TR-10: Registro en Base de Datos (10h)
- Inserción completa de formulario
- Relaciones con users y pets
- Auditoría con timestamps

### ✅ TR-11: Notificaciones a Administradores (10h)
- Sistema de notificaciones **in-app** (NO email)
- Alertas cuando se crea solicitud
- Notificaciones al cambiar estado
- Panel de administración completo

---

## 🗂️ Estructura de la Base de Datos

### Tabla: `adoption_requests`

```sql
CREATE TABLE adoption_requests (
    -- IDs y relaciones
    id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    pet_id INT NOT NULL,
    
    -- PANTALLA 1: Información Personal (7 campos)
    nombres_completos VARCHAR(200) NOT NULL,
    email VARCHAR(150) NOT NULL,
    telefono VARCHAR(15) NOT NULL,
    fecha_nacimiento DATE NOT NULL,
    direccion_completa TEXT NOT NULL,
    ciudad VARCHAR(100) NOT NULL,
    distrito VARCHAR(100) NOT NULL,
    
    -- PANTALLA 2: Información del Hogar (5 campos)
    tipo_vivienda ENUM('Casa', 'Departamento', 'Casa con jardín') NOT NULL,
    propietario_acepta_mascotas ENUM('Sí', 'No', 'Soy propietario') NOT NULL,
    miembros_familia INT NOT NULL,
    hay_ninos ENUM('Sí', 'No') NOT NULL,
    alergias_familia ENUM('Sí', 'No') NOT NULL,
    
    -- PANTALLA 3: Experiencia con Mascotas (6 campos)
    tiene_otras_mascotas ENUM('Sí', 'No') NOT NULL,
    descripcion_otras_mascotas TEXT,
    experiencia_previa TEXT NOT NULL,
    tiempo_sola_mascota VARCHAR(100) NOT NULL,
    tiene_veterinario ENUM('Sí', 'No') NOT NULL,
    presupuesto_mensual VARCHAR(100) NOT NULL,
    
    -- PANTALLA 4: Motivación y Compromiso (4 campos)
    motivacion_adopcion TEXT NOT NULL,
    conocimiento_raza TEXT NOT NULL,
    dispuesto_entrenar ENUM('Sí', 'No') NOT NULL,
    compromiso_largo_plazo TEXT NOT NULL,
    
    -- Control de Estado
    status ENUM('pendiente','en_revision','aprobada','rechazada') DEFAULT 'pendiente',
    notas_admin TEXT,
    fecha_revision DATETIME,
    revisado_por INT,
    
    -- Auditoría
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (pet_id) REFERENCES pets(id),
    FOREIGN KEY (revisado_por) REFERENCES users(id)
);
```

**Total: 29 campos** (sin contar id, created_at, updated_at)

---

## 🚀 Endpoints Implementados

### 👤 **Endpoints de Usuario**

#### 1. Verificar Solicitud Activa (CA-002)
```
GET /adoptions/checkActiveRequest.php
```

**Parámetros:**
```json
{
  "user_id": 1,
  "pet_id": 5
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "can_apply": true,
  "has_active_request": false,
  "pet_available": true,
  "message": "Puedes enviar la solicitud"
}
```

**Respuesta con Solicitud Activa:**
```json
{
  "success": true,
  "can_apply": false,
  "has_active_request": true,
  "request_id": 12,
  "current_status": "pendiente",
  "message": "Ya tienes una solicitud pendiente para esta mascota"
}
```

---

#### 2. Crear Solicitud de Adopción
```
POST /adoptions/createRequest.php
Content-Type: application/json
```

**Body (29 campos):**
```json
{
  "user_id": 1,
  "pet_id": 5,
  
  // Pantalla 1: Info Personal
  "nombres_completos": "Juan Carlos Pérez Gómez",
  "email": "juan@example.com",
  "telefono": "987654321",
  "fecha_nacimiento": "1990-05-15",
  "direccion_completa": "Av. Arequipa 1234, Dpto 501",
  "ciudad": "Lima",
  "distrito": "Miraflores",
  
  // Pantalla 2: Hogar
  "tipo_vivienda": "Departamento",
  "propietario_acepta_mascotas": "Sí",
  "miembros_familia": 3,
  "hay_ninos": "No",
  "alergias_familia": "No",
  
  // Pantalla 3: Experiencia
  "tiene_otras_mascotas": "Sí",
  "descripcion_otras_mascotas": "Tengo un gato de 2 años",
  "experiencia_previa": "He tenido perros toda mi vida",
  "tiempo_sola_mascota": "4-6 horas al día",
  "tiene_veterinario": "Sí",
  "presupuesto_mensual": "S/ 200-300",
  
  // Pantalla 4: Motivación
  "motivacion_adopcion": "Quiero darle un hogar amoroso a un perro rescatado",
  "conocimiento_raza": "He investigado sobre Labradores y sé que necesitan ejercicio",
  "dispuesto_entrenar": "Sí",
  "compromiso_largo_plazo": "Estoy comprometido a cuidar al perro toda su vida"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "request_id": 15,
  "message": "Solicitud enviada exitosamente. Será revisada en 3-4 días hábiles",
  "review_time": "3-4 días hábiles",
  "admins_notified": 2
}
```

**Validaciones:**
- ✅ Usuario existe y está verificado
- ✅ Mascota existe y está disponible
- ✅ NO tiene solicitud activa (CA-002)
- ✅ Email válido
- ✅ Teléfono 9 dígitos
- ✅ Mayor de 18 años

---

#### 3. Obtener Mis Solicitudes
```
GET /adoptions/getMyRequests.php?user_id=1&status=pendiente
```

**Parámetros:**
- `user_id` (required)
- `status` (optional): pendiente, en_revision, aprobada, rechazada

**Respuesta:**
```json
{
  "success": true,
  "requests": [
    {
      "id": 15,
      "pet_id": 5,
      "pet_name": "Max",
      "especie": "Perro",
      "raza": "Labrador",
      "edad": 2,
      "sexo": "Macho",
      "image_url": "https://...",
      "urgencia": "No",
      "status": "pendiente",
      "status_text": "Pendiente de revisión",
      "status_color": "#FFA500",
      "created_at_formatted": "15/01/2025 10:30",
      "notas_admin": null
    }
  ],
  "stats": {
    "total": 3,
    "pendiente": 1,
    "en_revision": 1,
    "aprobada": 0,
    "rechazada": 1
  }
}
```

---

#### 4. Obtener Detalles de Solicitud
```
GET /adoptions/getRequestDetails.php?request_id=15&user_id=1
```

**Respuesta:**
```json
{
  "success": true,
  "request_id": 15,
  "status": "pendiente",
  "status_text": "Pendiente de revisión",
  "created_at": "15/01/2025 10:30",
  "fecha_revision": null,
  "revisado_por": null,
  "notas_admin": null,
  
  "mascota": {
    "id": 5,
    "nombre": "Max",
    "especie": "Perro",
    "raza": "Labrador",
    ...
  },
  
  "pantalla_1_personal": {
    "nombres_completos": "Juan Carlos Pérez Gómez",
    "email": "juan@example.com",
    "telefono": "987654321",
    "fecha_nacimiento": "15/05/1990",
    "edad": 34,
    ...
  },
  
  "pantalla_2_hogar": { ... },
  "pantalla_3_experiencia": { ... },
  "pantalla_4_motivacion": { ... }
}
```

---

### 👨‍💼 **Endpoints de Administrador**

#### 5. Lista de Solicitudes (Panel Admin)
```
GET /admin/getAdoptionRequests.php?admin_id=9&status=pendiente&page=1
```

**Parámetros:**
- `admin_id` (required): Valida que sea admin
- `status` (optional): filtrar por estado
- `urgencia` (optional): filtrar mascotas urgentes
- `page` (optional): paginación
- `per_page` (optional): resultados por página (default: 20)

**Respuesta:**
```json
{
  "success": true,
  "requests": [
    {
      "id": 15,
      "user_id": 1,
      "pet_id": 5,
      "solicitante_nombre": "Juan Carlos Pérez Gómez",
      "solicitante_email": "juan@example.com",
      "solicitante_telefono": "987654321",
      "ciudad": "Lima",
      "distrito": "Miraflores",
      "pet_name": "Max",
      "especie": "Perro",
      "raza": "Labrador",
      "urgencia": "Si",
      "status": "pendiente",
      "status_text": "Pendiente",
      "created_at_formatted": "15/01/2025 10:30",
      "dias_desde_solicitud": 2,
      "requiere_atencion": true
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 15,
    "total_pages": 1,
    "has_next": false,
    "has_prev": false
  },
  "stats": {
    "total": 15,
    "pendientes": 8,
    "en_revision": 3,
    "aprobadas": 2,
    "rechazadas": 2,
    "urgentes_pendientes": 5
  }
}
```

**Orden:**
1. Mascotas urgentes primero
2. Por estado (pendiente > en_revision > aprobada > rechazada)
3. Por fecha de creación (más recientes primero)

---

#### 6. Detalles Completos (Admin)
```
GET /admin/getRequestDetails.php?admin_id=9&request_id=15
```

**Respuesta:**
Incluye TODO lo de la solicitud MÁS:
- Información del usuario (perfil completo)
- Historial de otras solicitudes del usuario
- Detalles completos de la mascota
- Quién revisó (si aplica)

```json
{
  "success": true,
  "request_id": 15,
  "status": "pendiente",
  "dias_desde_solicitud": 2,
  
  "usuario": {
    "id": 1,
    "nombres": "Juan",
    "email": "juan@example.com",
    "registrado": "10/12/2024",
    "historial_solicitudes": [
      {
        "id": 10,
        "status": "aprobada",
        "pet_name": "Luna",
        "created_at_formatted": "20/12/2024"
      }
    ]
  },
  
  "mascota": { ... },
  "informacion_personal": { ... },
  "informacion_hogar": { ... },
  "experiencia_mascotas": { ... },
  "motivacion_compromiso": { ... }
}
```

---

#### 7. Actualizar Estado de Solicitud
```
POST /admin/updateRequestStatus.php
Content-Type: application/json
```

**Body:**
```json
{
  "admin_id": 9,
  "request_id": 15,
  "new_status": "aprobada",
  "notas_admin": "Solicitante cumple todos los requisitos. Excelente hogar."
}
```

**Estados válidos:**
- `en_revision`
- `aprobada`
- `rechazada`

**Respuesta Aprobada:**
```json
{
  "success": true,
  "message": "Solicitud marcada como aprobada",
  "request_id": 15,
  "new_status": "aprobada",
  "reviewed_by": "Admin",
  "user_notified": true,
  "pet_status_updated": "En Proceso de Adopción",
  "other_requests_rejected": 3
}
```

**Lógica cuando se APRUEBA:**
1. ✅ Actualiza solicitud a "aprobada"
2. ✅ Cambia mascota a "En Proceso de Adopción"
3. ✅ **Rechaza automáticamente** otras solicitudes pendientes para esa mascota
4. ✅ Notifica al solicitante aprobado
5. ✅ Notifica a otros solicitantes rechazados

**Respuesta Rechazada:**
```json
{
  "success": true,
  "message": "Solicitud marcada como rechazada",
  "request_id": 15,
  "new_status": "rechazada",
  "reviewed_by": "Admin",
  "user_notified": true
}
```

---

#### 8. Notificaciones del Admin
```
GET /admin/getNotifications.php?admin_id=9&unread_only=true
```

**Parámetros:**
- `admin_id` (required)
- `unread_only` (optional): "true" para solo no leídas

**Respuesta:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 45,
      "type": "nueva_solicitud",
      "title": "Nueva solicitud de adopción",
      "message": "Juan Carlos Pérez Gómez quiere adoptar a Max",
      "related_id": 15,
      "is_read": 0,
      "created_at": "2025-01-15 10:30:00",
      "created_at_formatted": "15/01/2025 10:30",
      "time_ago": "2 horas"
    }
  ],
  "unread_count": 5,
  "total": 8
}
```

**Tipos de notificaciones:**
- `nueva_solicitud`: Cuando usuario envía solicitud
- `solicitud_en_revision`: Admin pone en revisión
- `solicitud_aprobada`: Admin aprueba
- `solicitud_rechazada`: Admin rechaza

---

## 🔐 Sistema de Roles

### Actualización de `users` table:
```sql
ALTER TABLE users 
ADD COLUMN role ENUM('user', 'admin') DEFAULT 'user' NOT NULL;
```

### Usuario Admin creado:
```
Email: admin@adoptme.com
Password: Admin123!
ID: 9
Role: admin
```

### Login actualizado:
El endpoint `/login.php` ahora retorna el campo `role`:

```json
{
  "success": true,
  "token": "...",
  "user": {
    "id": 9,
    "email": "admin@adoptme.com",
    "nombres": "Admin",
    "role": "admin"  // ⬅️ NUEVO
  }
}
```

---

## 📱 Flujo de Usuario (Frontend)

### 1️⃣ Usuario Navega Catálogo
```
GET /pets/getPets.php
```

### 2️⃣ Ve Detalles de Mascota
```
GET /pets/getPetDetails.php?id=5
```

### 3️⃣ Clic en "Adoptar" → Verificar si puede aplicar
```
GET /adoptions/checkActiveRequest.php?user_id=1&pet_id=5
```
- Si `can_apply = false` → Mostrar mensaje
- Si `can_apply = true` → Abrir formulario

### 4️⃣ Llenar Formulario (5 Pantallas)
**Pantalla 1:** Info personal  
**Pantalla 2:** Info del hogar  
**Pantalla 3:** Experiencia con mascotas  
**Pantalla 4:** Motivación  
**Pantalla 5:** Revisión (mostrar resumen)

### 5️⃣ Enviar Solicitud
```
POST /adoptions/createRequest.php
{
  // 29 campos del formulario
}
```

### 6️⃣ Ver Mis Solicitudes
```
GET /adoptions/getMyRequests.php?user_id=1
```

### 7️⃣ Ver Detalles de Mi Solicitud
```
GET /adoptions/getRequestDetails.php?request_id=15&user_id=1
```

### 8️⃣ Recibir Notificación (in-app)
Cuando admin actualiza estado → Usuario recibe notificación

---

## 👨‍💼 Flujo de Administrador

### 1️⃣ Login como Admin
```
POST /login.php
{
  "email": "admin@adoptme.com",
  "password": "Admin123!"
}
```
Respuesta incluye `role: "admin"`

### 2️⃣ Ver Panel de Solicitudes
```
GET /admin/getAdoptionRequests.php?admin_id=9
```
Ve todas las solicitudes ordenadas por urgencia

### 3️⃣ Ver Notificaciones No Leídas
```
GET /admin/getNotifications.php?admin_id=9&unread_only=true
```
Muestra badge con número de solicitudes nuevas

### 4️⃣ Abrir Detalles de Solicitud
```
GET /admin/getRequestDetails.php?admin_id=9&request_id=15
```
Ve TODO el formulario + info del usuario

### 5️⃣ Aprobar o Rechazar
```
POST /admin/updateRequestStatus.php
{
  "admin_id": 9,
  "request_id": 15,
  "new_status": "aprobada",
  "notas_admin": "Excelente perfil"
}
```

### 6️⃣ Sistema Automático:
- ✅ Notifica al usuario
- ✅ Si aprueba → Cambia mascota a "En Proceso"
- ✅ Si aprueba → Rechaza otras solicitudes para esa mascota
- ✅ Notifica a otros solicitantes rechazados

---

## ✅ Criterios de Aceptación

### CA-001: Formulario con validaciones ✅
- 5 pantallas implementadas
- 29 campos capturados
- Validaciones frontend (responsabilidad del equipo Android)
- Validaciones backend ✅

### CA-002: No solicitudes duplicadas ✅
- Endpoint `checkActiveRequest.php` verifica antes de abrir formulario
- Backend valida antes de insertar
- Solo permite 1 solicitud activa por usuario/mascota

### CA-003: Notificaciones a administradores ✅
- Sistema in-app (NO email como solicitó el usuario)
- Tabla `notifications` utilizada
- Admins reciben notificación cuando:
  - Se crea nueva solicitud
  - (Usuarios reciben cuando admin actualiza estado)

---

## 🎨 Estados y Colores (Frontend)

```javascript
const STATUS_CONFIG = {
  pendiente: {
    text: 'Pendiente de revisión',
    color: '#FFA500', // Naranja
    icon: 'clock'
  },
  en_revision: {
    text: 'En revisión',
    color: '#2196F3', // Azul
    icon: 'search'
  },
  aprobada: {
    text: 'Aprobada',
    color: '#4CAF50', // Verde
    icon: 'check-circle'
  },
  rechazada: {
    text: 'Rechazada',
    color: '#F44336', // Rojo
    icon: 'x-circle'
  }
};
```

---

## 🧪 Testing

### Datos de Prueba:

#### Usuario Normal:
```
Email: test@example.com
Password: Test123!
ID: 1
Role: user
```

#### Usuario Admin:
```
Email: admin@adoptme.com
Password: Admin123!
ID: 9
Role: admin
```

#### Mascota para Pruebas:
```
ID: 1 (Max - Labrador - Disponible)
ID: 2 (Luna - Gato - Disponible)
ID: 5 (Rocky - Labrador - Disponible - URGENTE)
```

### Escenarios de Prueba:

1. **✅ Usuario envía solicitud exitosa**
2. **✅ Usuario intenta solicitar misma mascota 2 veces** → Error CA-002
3. **✅ Usuario menor de 18 años** → Error validación
4. **✅ Email inválido** → Error validación
5. **✅ Admin aprueba solicitud** → Mascota cambia a "En Proceso", otros rechazados
6. **✅ Admin rechaza solicitud** → Usuario notificado
7. **✅ Notificaciones in-app funcionan** → NO emails

---

## 📦 Archivos Creados

### Base de Datos:
- `database_sprint3_roles.sql` - Añade role a users
- `database_sprint3_adoption_requests.sql` - Tabla completa

### Endpoints Usuario (`/adoptions/`):
- `checkActiveRequest.php` - CA-002 validation
- `createRequest.php` - Crear solicitud (TR-10)
- `getMyRequests.php` - Historial usuario
- `getRequestDetails.php` - Detalles completos

### Endpoints Admin (`/admin/`):
- `getAdoptionRequests.php` - Panel de solicitudes
- `getRequestDetails.php` - Detalles completos admin
- `updateRequestStatus.php` - Aprobar/Rechazar
- `getNotifications.php` - Notificaciones admin

### Actualizaciones:
- `login.php` - Ahora retorna campo `role`

---

## 🚀 Despliegue

### 1. Ejecutar SQL en MySQL:
```bash
# En phpMyAdmin o MySQL Workbench:
source database_sprint3_roles.sql;
source database_sprint3_adoption_requests.sql;
```

### 2. Verificar archivos copiados a XAMPP:
```
C:\xampp\htdocs\adopciones_api\
├── adoptions/
│   ├── checkActiveRequest.php
│   ├── createRequest.php
│   ├── getMyRequests.php
│   └── getRequestDetails.php
├── admin/
│   ├── getAdoptionRequests.php
│   ├── getRequestDetails.php
│   ├── updateRequestStatus.php
│   └── getNotifications.php
└── login.php (actualizado)
```

### 3. Probar en Postman:
Ver `SPRINT3_POSTMAN_EXAMPLES.md` (próximo archivo)

---

## ⚠️ Notas Importantes

### 1. NO Email (Solo In-App)
El usuario específicamente solicitó que **NO se envíen emails**, solo notificaciones in-app a través de la tabla `notifications`.

### 2. Snapshot Approach
Los datos del formulario son un **snapshot** del momento de aplicación, NO datos en vivo del perfil del usuario. Esto permite:
- Ver qué información proveyó el usuario en ese momento
- Auditoría histórica
- Datos no cambian si usuario actualiza perfil

### 3. Role-Based Access
- Endpoints `/admin/*` **REQUIEREN** `role = 'admin'`
- Frontend debe verificar role y mostrar/ocultar opciones
- Backend valida SIEMPRE el role

### 4. Automati zación
Cuando se aprueba una solicitud:
- Mascota → "En Proceso de Adopción"
- Otras solicitudes activas → Auto-rechazadas
- Todos los involucrados → Notificados

---

## 📞 Integración con Android

### Base URL:
```
http://10.0.2.2/adopciones_api/
```

### Headers necesarios:
```
Content-Type: application/json
```

### Manejo de Errores:
Todos los endpoints retornan:
- `success: true/false`
- `message: "Descripción del error"`
- HTTP status codes apropiados

Ver ejemplos completos en: `FRONTEND_INTEGRATION.md`

---

## ✨ Próximos Pasos (Sprint 4+)

### Posibles Mejoras:
1. Sistema de mensajería entre usuario y admin
2. Programar entrevistas
3. Upload de documentos (DNI, comprobante domicilio)
4. Seguimiento post-adopción
5. Calificaciones y reviews
6. Sistema de favoritos mejorado

---

**Documentación creada:** 15 Enero 2025  
**Sprint:** 3 - HU-005  
**API Version:** 1.3.0  
**Autor:** GitHub Copilot + Usuario
