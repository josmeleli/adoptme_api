# 📮 Sprint 3 - Ejemplos Postman

## 🎯 Guía Rápida de Testing

Base URL: `http://localhost/adopciones_api/`

---

## 👤 ENDPOINTS DE USUARIO

### 1. Verificar si Puede Aplicar (CA-002)

**GET** `http://localhost/adopciones_api/adoptions/checkActiveRequest.php?user_id=1&pet_id=5`

**Respuesta Exitosa (puede aplicar):**
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
  "request_id": 1,
  "current_status": "pendiente",
  "message": "Ya tienes una solicitud pendiente para esta mascota"
}
```

---

### 2. Crear Solicitud de Adopción

**POST** `http://localhost/adopciones_api/adoptions/createRequest.php`

**Headers:**
```
Content-Type: application/json
```

**Body:**
```json
{
  "user_id": 1,
  "pet_id": 5,
  
  "nombres_completos": "María Elena Rodríguez Torres",
  "email": "maria@example.com",
  "telefono": "987654321",
  "fecha_nacimiento": "1988-03-20",
  "direccion_completa": "Av. Larco 1050, Torre B, Piso 8",
  "ciudad": "Lima",
  "distrito": "Miraflores",
  
  "tipo_vivienda": "Departamento",
  "propietario_acepta_mascotas": "Sí",
  "miembros_familia": 2,
  "hay_ninos": "No",
  "alergias_familia": "No",
  
  "tiene_otras_mascotas": "No",
  "descripcion_otras_mascotas": null,
  "experiencia_previa": "Tuve un perro Beagle por 10 años hasta que falleció el año pasado",
  "tiempo_sola_mascota": "3-4 horas los días de semana",
  "tiene_veterinario": "Sí",
  "presupuesto_mensual": "S/ 300-400",
  
  "motivacion_adopcion": "Quiero darle un hogar amoroso a un perro que lo necesita. Mi anterior perro falleció y estoy lista para adoptar nuevamente",
  "conocimiento_raza": "He investigado sobre Labradores. Sé que son energéticos, necesitan ejercicio diario y son muy leales",
  "dispuesto_entrenar": "Sí",
  "compromiso_largo_plazo": "Me comprometo a cuidar al perro toda su vida, brindarle atención veterinaria, ejercicio diario y mucho amor"
}
```

**Respuesta Exitosa:**
```json
{
  "success": true,
  "request_id": 1,
  "message": "Solicitud enviada exitosamente. Será revisada en 3-4 días hábiles",
  "review_time": "3-4 días hábiles",
  "admins_notified": 1
}
```

**Errores Posibles:**

❌ Usuario no verificado:
```json
{
  "success": false,
  "message": "Debes verificar tu cuenta antes de enviar una solicitud"
}
```

❌ Solicitud duplicada (CA-002):
```json
{
  "success": false,
  "error_code": "DUPLICATE_REQUEST",
  "message": "Ya tienes una solicitud pendiente para esta mascota"
}
```

❌ Menor de 18 años:
```json
{
  "success": false,
  "message": "Debes ser mayor de 18 años para adoptar"
}
```

❌ Teléfono inválido:
```json
{
  "success": false,
  "message": "El teléfono debe tener 9 dígitos"
}
```

---

### 3. Obtener Mis Solicitudes

**GET** `http://localhost/adopciones_api/adoptions/getMyRequests.php?user_id=1`

**Con filtro por estado:**
```
http://localhost/adopciones_api/adoptions/getMyRequests.php?user_id=1&status=pendiente
```

**Respuesta:**
```json
{
  "success": true,
  "requests": [
    {
      "id": 1,
      "pet_id": 5,
      "status": "pendiente",
      "created_at": "2025-01-15 14:30:00",
      "updated_at": "2025-01-15 14:30:00",
      "fecha_revision": null,
      "notas_admin": null,
      "pet_name": "Rocky",
      "especie": "Perro",
      "raza": "Labrador Retriever",
      "edad": 2,
      "sexo": "Macho",
      "image_url": "https://images.dog.ceo/breeds/labrador/n02099712_1234.jpg",
      "urgencia": "Si",
      "status_text": "Pendiente de revisión",
      "status_color": "#FFA500",
      "created_at_formatted": "15/01/2025 14:30",
      "pet_edad_text": "2 años"
    }
  ],
  "stats": {
    "total": 1,
    "pendiente": 1,
    "en_revision": 0,
    "aprobada": 0,
    "rechazada": 0
  }
}
```

---

### 4. Ver Detalles de Mi Solicitud

**GET** `http://localhost/adopciones_api/adoptions/getRequestDetails.php?request_id=1&user_id=1`

**Respuesta:**
```json
{
  "success": true,
  "request_id": 1,
  "status": "pendiente",
  "status_text": "Pendiente de revisión",
  "created_at": "15/01/2025 14:30",
  "fecha_revision": null,
  "revisado_por": null,
  "notas_admin": null,
  
  "mascota": {
    "id": 5,
    "nombre": "Rocky",
    "especie": "Perro",
    "raza": "Labrador Retriever",
    "edad": 2,
    "sexo": "Macho",
    "color": "Dorado",
    "tamano": "Grande",
    "peso_aprox": "30 kg",
    "descripcion": "Rocky es un perro muy activo y cariñoso...",
    "image_url": "https://...",
    "urgencia": "Si",
    "estado": "Disponible"
  },
  
  "pantalla_1_personal": {
    "nombres_completos": "María Elena Rodríguez Torres",
    "email": "maria@example.com",
    "telefono": "987654321",
    "fecha_nacimiento": "20/03/1988",
    "edad": 36,
    "direccion_completa": "Av. Larco 1050, Torre B, Piso 8",
    "ciudad": "Lima",
    "distrito": "Miraflores"
  },
  
  "pantalla_2_hogar": {
    "tipo_vivienda": "Departamento",
    "propietario_acepta_mascotas": "Sí",
    "miembros_familia": 2,
    "hay_ninos": "No",
    "alergias_familia": "No"
  },
  
  "pantalla_3_experiencia": {
    "tiene_otras_mascotas": "No",
    "descripcion_otras_mascotas": null,
    "experiencia_previa": "Tuve un perro Beagle por 10 años...",
    "tiempo_sola_mascota": "3-4 horas los días de semana",
    "tiene_veterinario": "Sí",
    "presupuesto_mensual": "S/ 300-400"
  },
  
  "pantalla_4_motivacion": {
    "motivacion_adopcion": "Quiero darle un hogar amoroso...",
    "conocimiento_raza": "He investigado sobre Labradores...",
    "dispuesto_entrenar": "Sí",
    "compromiso_largo_plazo": "Me comprometo a cuidar al perro..."
  }
}
```

---

## 👨‍💼 ENDPOINTS DE ADMINISTRADOR

### 5. Login como Admin

**POST** `http://localhost/adopciones_api/login.php`

**Body:**
```json
{
  "email": "admin@adoptme.com",
  "password": "Admin123!"
}
```

**Respuesta:**
```json
{
  "success": true,
  "token": "eyJ1c2VyX2lkIjo5LCJlbWFpbCI6ImFkbWluQGFkb3B0bWUuY29tIiwiaWF0IjoxNzM2OTY3...",
  "user": {
    "id": 9,
    "email": "admin@adoptme.com",
    "nombres": "Admin",
    "apellidos": null,
    "dni": null,
    "telefono": null,
    "role": "admin"  // ⬅️ IMPORTANTE
  },
  "expires_at": "2025-01-22 14:30:00"
}
```

---

### 6. Panel de Solicitudes (Admin)

**GET** `http://localhost/adopciones_api/admin/getAdoptionRequests.php?admin_id=9`

**Con filtros:**
```
# Solo pendientes
?admin_id=9&status=pendiente

# Solo urgentes
?admin_id=9&urgencia=Si

# Paginación
?admin_id=9&page=1&per_page=10
```

**Respuesta:**
```json
{
  "success": true,
  "requests": [
    {
      "id": 1,
      "user_id": 1,
      "pet_id": 5,
      "status": "pendiente",
      "created_at": "2025-01-15 14:30:00",
      "updated_at": "2025-01-15 14:30:00",
      "fecha_revision": null,
      "solicitante_nombre": "María Elena Rodríguez Torres",
      "solicitante_email": "maria@example.com",
      "solicitante_telefono": "987654321",
      "ciudad": "Lima",
      "distrito": "Miraflores",
      "usuario_nombre": "María",
      "usuario_email": "maria@example.com",
      "pet_name": "Rocky",
      "especie": "Perro",
      "raza": "Labrador Retriever",
      "edad": 2,
      "sexo": "Macho",
      "image_url": "https://...",
      "urgencia": "Si",
      "status_text": "Pendiente",
      "created_at_formatted": "15/01/2025 14:30",
      "dias_desde_solicitud": 0,
      "requiere_atencion": true
    }
  ],
  "pagination": {
    "page": 1,
    "per_page": 20,
    "total": 1,
    "total_pages": 1,
    "has_next": false,
    "has_prev": false
  },
  "stats": {
    "total": 1,
    "pendientes": 1,
    "en_revision": 0,
    "aprobadas": 0,
    "rechazadas": 0,
    "urgentes_pendientes": 1
  }
}
```

**Error - No es admin:**
```json
{
  "success": false,
  "message": "Acceso denegado. Solo administradores pueden acceder"
}
```

---

### 7. Ver Detalles Completos (Admin)

**GET** `http://localhost/adopciones_api/admin/getRequestDetails.php?admin_id=9&request_id=1`

**Respuesta:**
```json
{
  "success": true,
  "request_id": 1,
  "status": "pendiente",
  "status_text": "Pendiente de revisión",
  "created_at": "15/01/2025 14:30",
  "dias_desde_solicitud": 0,
  "fecha_revision": null,
  "revisado_por": null,
  "notas_admin": null,
  
  "usuario": {
    "id": 1,
    "nombres": "María",
    "email": "maria@example.com",
    "telefono": "987654321",
    "registrado": "10/01/2025",
    "historial_solicitudes": []
  },
  
  "mascota": { ... },
  "informacion_personal": { ... },
  "informacion_hogar": { ... },
  "experiencia_mascotas": { ... },
  "motivacion_compromiso": { ... }
}
```

---

### 8. Aprobar Solicitud

**POST** `http://localhost/adopciones_api/admin/updateRequestStatus.php`

**Body:**
```json
{
  "admin_id": 9,
  "request_id": 1,
  "new_status": "aprobada",
  "notas_admin": "Solicitante cumple todos los requisitos. Excelente perfil para Rocky. Se contactará para coordinar entrevista."
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Solicitud marcada como aprobada",
  "request_id": 1,
  "new_status": "aprobada",
  "reviewed_by": "Admin",
  "user_notified": true,
  "pet_status_updated": "En Proceso de Adopción",
  "other_requests_rejected": 0
}
```

**¿Qué pasa automáticamente?**
1. ✅ Solicitud → "aprobada"
2. ✅ Rocky → "En Proceso de Adopción"
3. ✅ Usuario recibe notificación
4. ✅ Otras solicitudes pendientes para Rocky → Auto-rechazadas
5. ✅ Esos usuarios también reciben notificación

---

### 9. Rechazar Solicitud

**POST** `http://localhost/adopciones_api/admin/updateRequestStatus.php`

**Body:**
```json
{
  "admin_id": 9,
  "request_id": 1,
  "new_status": "rechazada",
  "notas_admin": "El espacio del departamento no es suficiente para un Labrador adulto. Recomendamos considerar razas de menor tamaño."
}
```

**Respuesta:**
```json
{
  "success": true,
  "message": "Solicitud marcada como rechazada",
  "request_id": 1,
  "new_status": "rechazada",
  "reviewed_by": "Admin",
  "user_notified": true
}
```

---

### 10. Poner en Revisión

**POST** `http://localhost/adopciones_api/admin/updateRequestStatus.php`

**Body:**
```json
{
  "admin_id": 9,
  "request_id": 1,
  "new_status": "en_revision",
  "notas_admin": "Solicitud en proceso de evaluación. Se está verificando referencias."
}
```

---

### 11. Ver Notificaciones (Admin)

**GET** `http://localhost/adopciones_api/admin/getNotifications.php?admin_id=9`

**Solo no leídas:**
```
?admin_id=9&unread_only=true
```

**Respuesta:**
```json
{
  "success": true,
  "notifications": [
    {
      "id": 1,
      "user_id": 9,
      "type": "nueva_solicitud",
      "title": "Nueva solicitud de adopción",
      "message": "María Elena Rodríguez Torres quiere adoptar a Rocky",
      "related_id": 1,
      "is_read": 0,
      "created_at": "2025-01-15 14:30:00",
      "created_at_formatted": "15/01/2025 14:30",
      "time_ago": "Ahora"
    }
  ],
  "unread_count": 1,
  "total": 1
}
```

---

## 🔄 FLUJO COMPLETO DE PRUEBA

### Escenario 1: Adopción Exitosa ✅

```
1. Usuario verifica si puede aplicar
   GET /adoptions/checkActiveRequest.php?user_id=1&pet_id=5
   → can_apply: true

2. Usuario envía solicitud
   POST /adoptions/createRequest.php
   → request_id: 1, admins_notified: 1

3. Admin recibe notificación
   GET /admin/getNotifications.php?admin_id=9
   → 1 nueva solicitud

4. Admin ve lista de solicitudes
   GET /admin/getAdoptionRequests.php?admin_id=9
   → 1 solicitud pendiente

5. Admin abre detalles
   GET /admin/getRequestDetails.php?admin_id=9&request_id=1
   → Ve todo el formulario

6. Admin aprueba
   POST /admin/updateRequestStatus.php
   → Mascota "En Proceso", usuario notificado

7. Usuario ve sus solicitudes
   GET /adoptions/getMyRequests.php?user_id=1
   → Status: "aprobada"

8. Usuario ve detalles
   GET /adoptions/getRequestDetails.php?request_id=1&user_id=1
   → Ve notas del admin
```

---

### Escenario 2: Solicitud Duplicada ❌

```
1. Usuario envía primera solicitud
   POST /adoptions/createRequest.php (pet_id: 5)
   → request_id: 1 ✅

2. Usuario intenta enviar otra para la misma mascota
   GET /adoptions/checkActiveRequest.php?user_id=1&pet_id=5
   → can_apply: false, has_active_request: true ❌

3. Usuario intenta forzar creación
   POST /adoptions/createRequest.php (pet_id: 5)
   → error_code: "DUPLICATE_REQUEST" ❌
```

---

### Escenario 3: Múltiples Solicitantes

```
Usuario A solicita Rocky (pet_id: 5)
Usuario B solicita Rocky (pet_id: 5)
Usuario C solicita Rocky (pet_id: 5)

Admin aprueba solicitud de Usuario B
→ Rocky cambia a "En Proceso de Adopción"
→ Solicitudes de A y C auto-rechazadas
→ A y C reciben notificación de rechazo
```

---

## 🎯 Checklist de Testing

### Usuario:
- [ ] Verificar si puede aplicar (checkActiveRequest)
- [ ] Enviar solicitud con todos los campos válidos
- [ ] Intentar solicitar misma mascota 2 veces (debe fallar)
- [ ] Ver historial de solicitudes
- [ ] Ver detalles de una solicitud
- [ ] Filtrar solicitudes por estado

### Admin:
- [ ] Login como admin (verificar role: "admin")
- [ ] Ver panel de solicitudes
- [ ] Filtrar por estado (pendiente, en_revision, etc.)
- [ ] Filtrar por urgencia
- [ ] Ver detalles completos de solicitud
- [ ] Poner solicitud en revisión
- [ ] Aprobar solicitud (verificar que mascota cambie a "En Proceso")
- [ ] Rechazar solicitud
- [ ] Ver notificaciones no leídas
- [ ] Verificar que al aprobar se rechacen otras solicitudes

### Validaciones:
- [ ] Usuario no verificado → error
- [ ] Menor de 18 años → error
- [ ] Email inválido → error
- [ ] Teléfono inválido (no 9 dígitos) → error
- [ ] Mascota no disponible → error
- [ ] Usuario no admin intenta acceder /admin → error 403
- [ ] Campo requerido faltante → error 400

---

## 📦 Colección Postman (Importar)

```json
{
  "info": {
    "name": "AdoptMe API - Sprint 3",
    "schema": "https://schema.getpostman.com/json/collection/v2.1.0/collection.json"
  },
  "item": [
    {
      "name": "Usuario - Check Active Request",
      "request": {
        "method": "GET",
        "url": "http://localhost/adopciones_api/adoptions/checkActiveRequest.php?user_id=1&pet_id=5"
      }
    },
    {
      "name": "Usuario - Create Request",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "url": "http://localhost/adopciones_api/adoptions/createRequest.php",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"user_id\": 1,\n  \"pet_id\": 5,\n  \"nombres_completos\": \"María Elena Rodríguez Torres\",\n  \"email\": \"maria@example.com\",\n  \"telefono\": \"987654321\",\n  \"fecha_nacimiento\": \"1988-03-20\",\n  \"direccion_completa\": \"Av. Larco 1050\",\n  \"ciudad\": \"Lima\",\n  \"distrito\": \"Miraflores\",\n  \"tipo_vivienda\": \"Departamento\",\n  \"propietario_acepta_mascotas\": \"Sí\",\n  \"miembros_familia\": 2,\n  \"hay_ninos\": \"No\",\n  \"alergias_familia\": \"No\",\n  \"tiene_otras_mascotas\": \"No\",\n  \"experiencia_previa\": \"Tuve un perro por 10 años\",\n  \"tiempo_sola_mascota\": \"3-4 horas\",\n  \"tiene_veterinario\": \"Sí\",\n  \"presupuesto_mensual\": \"S/ 300-400\",\n  \"motivacion_adopcion\": \"Quiero darle un hogar\",\n  \"conocimiento_raza\": \"He investigado sobre la raza\",\n  \"dispuesto_entrenar\": \"Sí\",\n  \"compromiso_largo_plazo\": \"Me comprometo totalmente\"\n}"
        }
      }
    },
    {
      "name": "Admin - Login",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "url": "http://localhost/adopciones_api/login.php",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"email\": \"admin@adoptme.com\",\n  \"password\": \"Admin123!\"\n}"
        }
      }
    },
    {
      "name": "Admin - Get Requests",
      "request": {
        "method": "GET",
        "url": "http://localhost/adopciones_api/admin/getAdoptionRequests.php?admin_id=9"
      }
    },
    {
      "name": "Admin - Update Status",
      "request": {
        "method": "POST",
        "header": [
          {
            "key": "Content-Type",
            "value": "application/json"
          }
        ],
        "url": "http://localhost/adopciones_api/admin/updateRequestStatus.php",
        "body": {
          "mode": "raw",
          "raw": "{\n  \"admin_id\": 9,\n  \"request_id\": 1,\n  \"new_status\": \"aprobada\",\n  \"notas_admin\": \"Excelente perfil\"\n}"
        }
      }
    }
  ]
}
```

---

**Última actualización:** 15 Enero 2025  
**Base URL:** http://localhost/adopciones_api/  
**Android URL:** http://10.0.2.2/adopciones_api/
