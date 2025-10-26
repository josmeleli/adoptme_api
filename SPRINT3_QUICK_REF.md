# ⚡ SPRINT 3 - QUICK REFERENCE

## 🎯 Lo Nuevo

### Base de Datos
```sql
-- Añadir role a users
ALTER TABLE users ADD role ENUM('user', 'admin') DEFAULT 'user';

-- Admin creado
Email: admin@adoptme.com
Pass: Admin123!
ID: 9
```

### Endpoints Usuario (4)
```
GET  /adoptions/checkActiveRequest.php?user_id=X&pet_id=Y
POST /adoptions/createRequest.php (29 campos JSON)
GET  /adoptions/getMyRequests.php?user_id=X
GET  /adoptions/getRequestDetails.php?request_id=X&user_id=Y
```

### Endpoints Admin (4)
```
GET  /admin/getAdoptionRequests.php?admin_id=X
GET  /admin/getRequestDetails.php?admin_id=X&request_id=Y
POST /admin/updateRequestStatus.php
GET  /admin/getNotifications.php?admin_id=X
```

### Login Actualizado
```json
{
  "user": {
    "role": "admin"  // ⬅️ NUEVO
  }
}
```

---

## 📋 Formulario de Adopción (29 campos)

### Pantalla 1: Personal (7)
- nombres_completos, email, telefono
- fecha_nacimiento
- direccion_completa, ciudad, distrito

### Pantalla 2: Hogar (5)
- tipo_vivienda, propietario_acepta_mascotas
- miembros_familia, hay_ninos, alergias_familia

### Pantalla 3: Experiencia (6)
- tiene_otras_mascotas, descripcion_otras_mascotas
- experiencia_previa, tiempo_sola_mascota
- tiene_veterinario, presupuesto_mensual

### Pantalla 4: Motivación (4)
- motivacion_adopcion, conocimiento_raza
- dispuesto_entrenar, compromiso_largo_plazo

---

## 🔄 Flujo Rápido

### Usuario
1. `checkActiveRequest` → can_apply?
2. Llenar formulario 5 pantallas
3. `createRequest` → Admins notificados
4. `getMyRequests` → Ver estado

### Admin
1. Login → verificar role='admin'
2. `getNotifications` → Ver nuevas
3. `getAdoptionRequests` → Panel
4. `updateRequestStatus` → Aprobar/Rechazar
   - Si aprueba → Mascota "En Proceso", otros rechazados

---

## ✅ Estados

```
pendiente → en_revision → aprobada/rechazada
#FFA500     #2196F3        #4CAF50/#F44336
```

---

## 🧪 Test Rápido

```bash
# Check
curl "http://localhost/adopciones_api/adoptions/checkActiveRequest.php?user_id=1&pet_id=5"

# Login Admin
curl -X POST http://localhost/adopciones_api/login.php \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@adoptme.com","password":"Admin123!"}'
```

---

## 📚 Docs Completas

- **SPRINT3_DOCUMENTATION.md** - Técnica
- **SPRINT3_POSTMAN_EXAMPLES.md** - Ejemplos
- **SPRINT3_COMPLETION.md** - Resumen
- **API_MAP.md** - Mapa visual

---

**Status:** ✅ COMPLETADO  
**Base URL:** http://localhost/adopciones_api/  
**Android URL:** http://10.0.2.2/adopciones_api/
