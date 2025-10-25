# SPRINT 2 - HU-004: Catálogo de Mascotas
## Implementación Completa Backend

### 📋 Resumen de Implementación

Se han implementado **todos los requisitos** de la Historia de Usuario HU-004: Catálogo de Mascotas del Sprint 2.

---

## ✅ Criterios de Aceptación Implementados

### CA-001: Información Visible
✅ **COMPLETADO**: Cada mascota en el catálogo muestra:
- ✅ Foto (`foto_url`)
- ✅ Especie (`especie`)
- ✅ Edad (`edad` + `categoria_edad`: cachorro/adulto/senior)
- ✅ Ubicación (`distrito`)
- ✅ Nombre (`name`)
- ✅ Raza (`raza`)
- ✅ Tamaño (`tamano`)
- ✅ Sexo (`sexo`)
- ✅ Descripción (`descripcion`)

### CA-002: Prioridad de Mascotas Urgentes
✅ **COMPLETADO**: 
- ✅ Columna `is_urgent` (TINYINT) para marcar urgencia
- ✅ Columna `priority` (INT) para nivel de prioridad
- ✅ Ordenamiento: `is_urgent DESC, priority DESC, created_at DESC`
- ✅ Las mascotas urgentes **SIEMPRE** aparecen primero
- ✅ Etiqueta visual `etiqueta_urgencia: "URGENTE"`

### CA-003: Paginación
✅ **COMPLETADO**:
- ✅ Parámetro `page` (número de página, default: 1)
- ✅ Parámetro `limit` (resultados por página, **mínimo 6**, default: 6)
- ✅ Metadatos de paginación completos:
  - `current_page`, `total_pages`, `total_items`
  - `has_next_page`, `has_previous_page`
  - `next_page`, `previous_page`

---

## 🎯 Tareas Implementadas

### TR-05: Implementar Catálogo con Fotos y Detalles
✅ **COMPLETADO**:
- ✅ Endpoint: `GET /pets/getPets.php`
- ✅ Retorna array de mascotas con todos sus datos
- ✅ Fotos desde URLs (usando Unsplash para demo)
- ✅ Información completa y estructurada

### TR-06: Desarrollar Filtros de Búsqueda
✅ **COMPLETADO**:
- ✅ **Filtro por especie**: `?especie=Perro` o `?especie=Gato`
- ✅ **Filtro por tamaño**: `?tamano=Pequeño` / `Mediano` / `Grande`
- ✅ **Filtro por edad**: 
  - Por rango: `?edad_min=2&edad_max=5`
  - Por categoría: `?categoria_edad=cachorro` / `adulto` / `senior`
- ✅ **Filtro por ubicación**: `?distrito=San Miguel`
- ✅ **Solo urgentes**: `?urgentes=true`
- ✅ **Filtros combinados**: Se pueden usar varios filtros simultáneamente

### TR-07: Optimizar UI/UX
✅ **COMPLETADO (Backend)**:
- ✅ Respuestas JSON optimizadas y estructuradas
- ✅ Información de paginación clara
- ✅ Etiquetas categorizadas (`categoria_edad`, `etiqueta_urgencia`)
- ✅ Ordenamiento inteligente por prioridad
- ✅ Metadatos de `filters_applied` para feedback

---

## 📁 Archivos Creados/Modificados

### 1. Base de Datos
**Archivo**: `database_sprint2_update.sql`
```sql
-- Nuevas columnas en tabla pets:
ALTER TABLE pets 
ADD COLUMN is_urgent TINYINT(1) DEFAULT 0,
ADD COLUMN priority INT DEFAULT 0,
ADD COLUMN distrito VARCHAR(100),
ADD INDEX idx_priority (priority DESC, created_at DESC),
ADD INDEX idx_urgent (is_urgent, priority DESC);

-- 12 mascotas de ejemplo insertadas:
-- 3 URGENTES (Luna, Rocky, Michi)
-- 9 DISPONIBLES (Bella, Max, Pelusa, Toby, Nala, Choco, Simba, Laika, Garfield)
```

### 2. Endpoint Principal: Catálogo
**Archivo**: `pets/getPets.php`
- **Método**: GET
- **URL**: `http://localhost/adopciones_api/pets/getPets.php`
- **Parámetros opcionales**:
  - `especie`: Perro | Gato
  - `tamano`: Pequeño | Mediano | Grande
  - `categoria_edad`: cachorro | adulto | senior
  - `edad_min`: número
  - `edad_max`: número
  - `distrito`: texto
  - `urgentes`: true | false
  - `page`: número (default: 1)
  - `limit`: número (default: 6, mínimo: 6)

### 3. Endpoint Detalles
**Archivo**: `pets/getPetDetails.php`
- **Método**: GET
- **URL**: `http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=X`
- **Retorna**: Información completa de la mascota + estado de adopción

### 4. Postman Collection
**Archivo**: `AdoptMe_API.postman_collection.json`
- ✅ Actualizado nombre: "AdoptMe API - Sprint 1 & 2"
- ✅ Agregados 6 nuevos endpoints (12-17):
  - 12: Catálogo completo
  - 13: Filtro por especie
  - 14: Filtros combinados
  - 15: Solo urgentes
  - 16: Paginación
  - 17: Detalles de mascota

---

## 🧪 Pruebas Realizadas

### ✅ Prueba 1: Catálogo Completo
```bash
GET http://localhost/adopciones_api/pets/getPets.php
```
**Resultado**: ✅ 12 mascotas, 6 por página, urgentes primero

### ✅ Prueba 2: Solo Urgentes
```bash
GET http://localhost/adopciones_api/pets/getPets.php?urgentes=true
```
**Resultado**: ✅ 3 mascotas urgentes (Luna, Rocky, Michi) ordenadas por priority DESC

### ✅ Prueba 3: Filtro por Especie
```bash
GET http://localhost/adopciones_api/pets/getPets.php?especie=Gato
```
**Resultado**: ✅ 5 gatos (Michi urgente primero, luego los demás)

### ✅ Prueba 4: Paginación
```bash
GET http://localhost/adopciones_api/pets/getPets.php?page=2
```
**Resultado**: ✅ Página 2 con 6 mascotas, metadatos correctos

### ✅ Prueba 5: Detalles
```bash
GET http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=1
```
**Resultado**: ✅ Información completa de Luna + estado de adopción

---

## 📊 Estructura de Respuesta

### Catálogo (`getPets.php`)
```json
{
  "success": true,
  "data": [
    {
      "id": 1,
      "name": "Luna",
      "especie": "Perro",
      "raza": "Mestizo",
      "edad": 2,
      "tamano": "Mediano",
      "sexo": "Hembra",
      "descripcion": "Perrita muy cariñosa...",
      "foto_url": "https://images.unsplash.com/photo-...",
      "distrito": "San Miguel",
      "is_urgent": true,
      "priority": 100,
      "estado": "Disponible",
      "created_at": "2025-10-25 13:59:11",
      "etiqueta_urgencia": "URGENTE",
      "categoria_edad": "Cachorro"
    }
  ],
  "pagination": {
    "current_page": 1,
    "total_pages": 2,
    "total_items": 12,
    "items_per_page": 6,
    "has_next_page": true,
    "has_previous_page": false,
    "next_page": 2,
    "previous_page": null
  },
  "filters_applied": {
    "especie": null,
    "tamano": null,
    "distrito": null,
    "edad_min": null,
    "edad_max": null,
    "categoria_edad": null,
    "solo_urgentes": false
  }
}
```

### Detalles (`getPetDetails.php`)
```json
{
  "success": true,
  "data": {
    "id": 1,
    "name": "Luna",
    "especie": "Perro",
    "raza": "Mestizo",
    "edad": 2,
    "tamano": "Mediano",
    "sexo": "Hembra",
    "descripcion": "Perrita muy cariñosa y juguetona...",
    "foto_url": "https://images.unsplash.com/...",
    "distrito": "San Miguel",
    "is_urgent": true,
    "priority": 100,
    "estado": "Disponible",
    "refugio_id": null,
    "created_at": "2025-10-25 13:59:11",
    "updated_at": "2025-10-25 13:59:11",
    "categoria_edad": "Cachorro",
    "solicitudes_pendientes": 0,
    "puede_adoptar": true
  }
}
```

---

## 🚀 Instrucciones de Uso

### 1. Actualizar Base de Datos
```powershell
cd C:\xampp
.\mysql\bin\mysql.exe -u root adoptme -e "source htdocs/adopciones_api/database_sprint2_update.sql"
```

### 2. Importar Colección Postman
1. Abrir Postman
2. Import → File → Seleccionar `AdoptMe_API.postman_collection.json`
3. Probar endpoints 12-17

### 3. Probar desde PowerShell
```powershell
# Catálogo completo
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php" -Method GET

# Solo urgentes
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?urgentes=true" -Method GET

# Filtrar por especie
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?especie=Gato" -Method GET

# Paginación
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?page=2" -Method GET

# Detalles
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=1" -Method GET
```

---

## 📱 Integración Android (Próximos pasos)

### Ejemplo de código Java para consumir el catálogo:

```java
// URL del endpoint
String url = "http://10.0.2.2/adopciones_api/pets/getPets.php";

// Con filtros
String urlConFiltros = url + "?especie=Perro&tamano=Mediano&page=1";

// Modelo de datos
public class Pet {
    public int id;
    public String name;
    public String especie;
    public String raza;
    public int edad;
    public String tamano;
    public String sexo;
    public String descripcion;
    public String foto_url;
    public String distrito;
    public boolean is_urgent;
    public int priority;
    public String estado;
    public String etiqueta_urgencia;
    public String categoria_edad;
}

// Respuesta del catálogo
public class CatalogResponse {
    public boolean success;
    public List<Pet> data;
    public Pagination pagination;
    public FiltersApplied filters_applied;
}
```

---

## 📝 Notas Importantes

1. **Fotos**: Actualmente usando URLs de Unsplash. En producción, implementar:
   - Endpoint `POST /pets/uploadPhoto.php` para subir fotos
   - Carpeta `uploads/pets/` para almacenamiento local
   - O integración con servicio de almacenamiento en la nube (AWS S3, Cloudinary, etc.)

2. **Ordenamiento por Urgencia**: 
   - El ordenamiento SQL garantiza que las mascotas urgentes **SIEMPRE** aparecen primero
   - Dentro de las urgentes, se ordenan por `priority` (mayor a menor)
   - Si no son urgentes, se ordenan por fecha de creación

3. **Validación de Límite de Paginación**:
   - El código asegura que `limit` nunca sea menor a 6 (CA-003)
   - `$limit = max(6, intval($_GET['limit']));`

4. **Categorización de Edad**:
   - Cachorro: 0-2 años
   - Adulto: 3-7 años
   - Senior: 8+ años

---

## ✅ Checklist de Verificación

- [x] CA-001: Muestra foto, especie, edad y ubicación
- [x] CA-002: Mascotas urgentes aparecen primero
- [x] CA-003: Paginación con mínimo 6 resultados
- [x] TR-05: Catálogo implementado con fotos y detalles
- [x] TR-06: Filtros de búsqueda funcionando
- [x] TR-07: Respuestas optimizadas (backend)
- [x] TR-010: Endpoint de catálogo creado
- [x] TR-011: Integración de fotos y detalles
- [x] TR-012: Paginación y orden por prioridad
- [x] Base de datos actualizada con columnas necesarias
- [x] 12 mascotas de ejemplo insertadas
- [x] Postman collection actualizada
- [x] Pruebas exitosas de todos los endpoints
- [x] Documentación completa

---

## 🎉 Estado Final

**SPRINT 2 - HU-004: COMPLETADO AL 100%** ✅

Todos los criterios de aceptación han sido implementados y probados exitosamente. El catálogo de mascotas está listo para ser consumido desde la aplicación Android.
