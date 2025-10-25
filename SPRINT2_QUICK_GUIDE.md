# 📱 SPRINT 2 - CATÁLOGO DE MASCOTAS
## Guía Rápida de Uso - Backend Completo

---

## 🎯 ENDPOINTS IMPLEMENTADOS

### 1️⃣ GET Catálogo Completo
**URL**: `http://localhost/adopciones_api/pets/getPets.php`
**Método**: GET
**Descripción**: Retorna todas las mascotas disponibles con paginación (6 por página)

**Ejemplo PowerShell**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php" -Method GET | Select-Object -ExpandProperty Content | ConvertFrom-Json | ConvertTo-Json -Depth 5
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPets.php"
```

**Respuesta Exitosa (200)**:
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
      "descripcion": "Perrita muy cariñosa y juguetona. Rescatada de las calles.",
      "foto_url": "https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400",
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

---

### 2️⃣ GET Solo Mascotas Urgentes
**URL**: `http://localhost/adopciones_api/pets/getPets.php?urgentes=true`
**Método**: GET
**Descripción**: Retorna SOLO mascotas marcadas como urgentes ordenadas por prioridad

**Ejemplo PowerShell**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?urgentes=true" -Method GET | Select-Object -ExpandProperty Content | ConvertFrom-Json | ConvertTo-Json -Depth 5
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPets.php?urgentes=true"
```

**Resultado**: 3 mascotas urgentes (Luna, Rocky, Michi) ordenadas por `priority DESC`

---

### 3️⃣ GET Filtrar por Especie
**URL**: `http://localhost/adopciones_api/pets/getPets.php?especie=Gato`
**Método**: GET
**Parámetros**: 
- `especie`: Perro | Gato

**Ejemplo PowerShell**:
```powershell
# Solo perros
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?especie=Perro" -Method GET | Select-Object -ExpandProperty Content

# Solo gatos
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?especie=Gato" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPets.php?especie=Perro"
```

---

### 4️⃣ GET Filtros Combinados
**URL**: `http://localhost/adopciones_api/pets/getPets.php?especie=Perro&tamano=Grande&categoria_edad=adulto`
**Método**: GET
**Parámetros disponibles**:
- `especie`: Perro | Gato
- `tamano`: Pequeño | Mediano | Grande
- `categoria_edad`: cachorro (0-2) | adulto (3-7) | senior (8+)
- `edad_min`: número (edad mínima)
- `edad_max`: número (edad máxima)
- `distrito`: texto (búsqueda parcial)
- `urgentes`: true | false

**Ejemplo PowerShell (Perros grandes adultos)**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?especie=Perro&tamano=Grande&categoria_edad=adulto" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo PowerShell (Gatos cachorros de San Miguel)**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?especie=Gato&categoria_edad=cachorro&distrito=San Miguel" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPets.php?especie=Perro&tamano=Grande&categoria_edad=adulto"
```

---

### 5️⃣ GET Paginación
**URL**: `http://localhost/adopciones_api/pets/getPets.php?page=2&limit=6`
**Método**: GET
**Parámetros**:
- `page`: número de página (default: 1)
- `limit`: resultados por página (default: 6, **mínimo: 6**)

**Ejemplo PowerShell (Página 2)**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?page=2" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo PowerShell (10 resultados por página)**:
```powershell
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPets.php?page=1&limit=10" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPets.php?page=2&limit=6"
```

---

### 6️⃣ GET Detalles de Mascota
**URL**: `http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=1`
**Método**: GET
**Parámetros**: 
- `pet_id`: ID de la mascota (requerido)

**Ejemplo PowerShell**:
```powershell
# Detalles de Luna (id=1)
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=1" -Method GET | Select-Object -ExpandProperty Content

# Detalles de Michi (id=3)
Invoke-WebRequest -Uri "http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=3" -Method GET | Select-Object -ExpandProperty Content
```

**Ejemplo cURL**:
```bash
curl -X GET "http://localhost/adopciones_api/pets/getPetDetails.php?pet_id=1"
```

**Respuesta Exitosa (200)**:
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
    "descripcion": "Perrita muy cariñosa y juguetona. Rescatada de las calles, necesita hogar urgente.",
    "foto_url": "https://images.unsplash.com/photo-1587300003388-59208cc962cb?w=400",
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

**Error 404 (Mascota no encontrada)**:
```json
{
  "success": false,
  "message": "Mascota no encontrada"
}
```

**Error 400 (pet_id faltante)**:
```json
{
  "success": false,
  "message": "El parámetro pet_id es requerido"
}
```

---

## 📊 ESTRUCTURA DE DATOS

### Objeto Pet (Mascota)
```json
{
  "id": 1,                          // ID único de la mascota
  "name": "Luna",                   // Nombre
  "especie": "Perro",              // Perro | Gato
  "raza": "Mestizo",               // Raza específica
  "edad": 2,                       // Edad en años
  "tamano": "Mediano",             // Pequeño | Mediano | Grande
  "sexo": "Hembra",                // Macho | Hembra
  "descripcion": "...",            // Descripción completa
  "foto_url": "https://...",       // URL de la foto
  "distrito": "San Miguel",        // Ubicación
  "is_urgent": true,               // true = urgente, false = normal
  "priority": 100,                 // Nivel de prioridad (0-100)
  "estado": "Disponible",          // Disponible | En proceso | Adoptado
  "created_at": "2025-10-25...",   // Fecha de creación
  "etiqueta_urgencia": "URGENTE",  // "URGENTE" o null
  "categoria_edad": "Cachorro"     // Cachorro | Adulto | Senior
}
```

### Objeto Pagination (Paginación)
```json
{
  "current_page": 1,           // Página actual
  "total_pages": 2,            // Total de páginas
  "total_items": 12,           // Total de mascotas encontradas
  "items_per_page": 6,         // Resultados por página
  "has_next_page": true,       // ¿Hay página siguiente?
  "has_previous_page": false,  // ¿Hay página anterior?
  "next_page": 2,              // Número de página siguiente (o null)
  "previous_page": null        // Número de página anterior (o null)
}
```

### Objeto FiltersApplied (Filtros Aplicados)
```json
{
  "especie": null,              // Filtro de especie aplicado (o null)
  "tamano": null,              // Filtro de tamaño aplicado (o null)
  "distrito": null,            // Filtro de distrito aplicado (o null)
  "edad_min": null,            // Filtro de edad mínima (o null)
  "edad_max": null,            // Filtro de edad máxima (o null)
  "categoria_edad": null,      // Filtro de categoría de edad (o null)
  "solo_urgentes": false       // Filtro solo urgentes (true/false)
}
```

---

## 🔧 INTEGRACIÓN ANDROID

### Retrofit Interface (Java)
```java
public interface AdoptMeAPI {
    
    // Catálogo completo
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalog();
    
    // Con paginación
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalogPage(
        @Query("page") int page,
        @Query("limit") int limit
    );
    
    // Con filtros
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalogFiltered(
        @Query("especie") String especie,
        @Query("tamano") String tamano,
        @Query("categoria_edad") String categoriaEdad,
        @Query("distrito") String distrito,
        @Query("urgentes") Boolean urgentes,
        @Query("page") int page,
        @Query("limit") int limit
    );
    
    // Detalles de mascota
    @GET("pets/getPetDetails.php")
    Call<PetDetailResponse> getPetDetails(@Query("pet_id") int petId);
}
```

### Modelos de Datos (Java)
```java
// Mascota
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
    public String created_at;
    public String etiqueta_urgencia;
    public String categoria_edad;
}

// Paginación
public class Pagination {
    public int current_page;
    public int total_pages;
    public int total_items;
    public int items_per_page;
    public boolean has_next_page;
    public boolean has_previous_page;
    public Integer next_page;
    public Integer previous_page;
}

// Filtros Aplicados
public class FiltersApplied {
    public String especie;
    public String tamano;
    public String distrito;
    public Integer edad_min;
    public Integer edad_max;
    public String categoria_edad;
    public boolean solo_urgentes;
}

// Respuesta del Catálogo
public class CatalogResponse {
    public boolean success;
    public List<Pet> data;
    public Pagination pagination;
    public FiltersApplied filters_applied;
}

// Respuesta de Detalles
public class PetDetailResponse {
    public boolean success;
    public PetDetail data;
}

// Detalle de Mascota
public class PetDetail extends Pet {
    public Integer refugio_id;
    public String updated_at;
    public int solicitudes_pendientes;
    public boolean puede_adoptar;
}
```

### Ejemplo de Uso en Activity (Java)
```java
// Inicializar Retrofit
Retrofit retrofit = new Retrofit.Builder()
    .baseUrl("http://10.0.2.2/adopciones_api/")
    .addConverterFactory(GsonConverterFactory.create())
    .build();

AdoptMeAPI api = retrofit.create(AdoptMeAPI.class);

// Obtener catálogo completo
Call<CatalogResponse> call = api.getCatalog();
call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        if (response.isSuccessful() && response.body().success) {
            List<Pet> pets = response.body().data;
            Pagination pagination = response.body().pagination;
            
            // Actualizar RecyclerView
            adapter.setPets(pets);
            
            // Manejar paginación
            if (pagination.has_next_page) {
                // Mostrar botón "Siguiente"
            }
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        Toast.makeText(context, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
    }
});

// Obtener solo urgentes
Call<CatalogResponse> urgentesCall = api.getCatalogFiltered(
    null, null, null, null, true, 1, 6
);

// Filtrar por especie y tamaño
Call<CatalogResponse> filteredCall = api.getCatalogFiltered(
    "Perro", "Grande", null, null, false, 1, 6
);

// Obtener detalles de mascota
Call<PetDetailResponse> detailCall = api.getPetDetails(1);
detailCall.enqueue(new Callback<PetDetailResponse>() {
    @Override
    public void onResponse(Call<PetDetailResponse> call, Response<PetDetailResponse> response) {
        if (response.isSuccessful() && response.body().success) {
            PetDetail pet = response.body().data;
            // Mostrar detalles en pantalla
        }
    }
    
    @Override
    public void onFailure(Call<PetDetailResponse> call, Throwable t) {
        // Manejar error
    }
});
```

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

### Backend (COMPLETADO ✅)
- [x] Tabla `pets` actualizada con `is_urgent`, `priority`, `distrito`
- [x] Endpoint `GET /pets/getPets.php` con filtros y paginación
- [x] Endpoint `GET /pets/getPetDetails.php` para detalles
- [x] 12 mascotas de ejemplo insertadas (3 urgentes, 9 normales)
- [x] Ordenamiento por urgencia y prioridad
- [x] Paginación con mínimo 6 resultados
- [x] Filtros por especie, tamaño, edad, distrito
- [x] Respuestas JSON estructuradas
- [x] Postman collection actualizada con 6 nuevos endpoints

### Frontend Android (PENDIENTE ⏳)
- [ ] Crear modelos de datos (Pet, Pagination, etc.)
- [ ] Configurar Retrofit con AdoptMeAPI interface
- [ ] Crear RecyclerView para catálogo
- [ ] Implementar adaptador con ViewHolder
- [ ] Cargar imágenes con Glide/Picasso
- [ ] Implementar filtros (Spinner/Chip para especie, tamaño, etc.)
- [ ] Implementar paginación (scroll infinito o botones)
- [ ] Mostrar etiqueta "URGENTE" en mascotas urgentes
- [ ] Crear Activity/Fragment de detalles de mascota
- [ ] Manejar estados de carga y errores

---

## 🎉 RESUMEN EJECUTIVO

**SPRINT 2 - HU-004 COMPLETADO AL 100%** ✅

✅ **CA-001**: Cada mascota muestra foto, especie, edad y ubicación
✅ **CA-002**: Mascotas urgentes aparecen primero ordenadas por prioridad
✅ **CA-003**: Paginación implementada con mínimo 6 resultados por página
✅ **TR-05**: Catálogo con fotos y detalles completos
✅ **TR-06**: 7 filtros diferentes implementados (especie, tamaño, edad, distrito, urgentes)
✅ **TR-07**: Respuestas JSON optimizadas y estructuradas

**Total de mascotas en base de datos**: 12
- 3 urgentes (Luna, Rocky, Michi)
- 9 disponibles (Bella, Max, Pelusa, Toby, Nala, Choco, Simba, Laika, Garfield)

**Endpoints disponibles**: 2
1. `GET /pets/getPets.php` - Catálogo con filtros y paginación
2. `GET /pets/getPetDetails.php` - Detalles de una mascota específica

**El backend está listo para ser consumido desde Android Studio** 🚀
