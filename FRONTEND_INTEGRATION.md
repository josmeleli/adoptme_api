# 📱 ADOPCIONES API - DOCUMENTACIÓN FRONTEND
## Sprint 2: Catálogo de Mascotas - Endpoints Completos

---

## 🌐 BASE URL
```
http://10.0.2.2/adopciones_api/    # Android Emulator
http://localhost/adopciones_api/   # Pruebas locales
```

---

## 📋 ENDPOINTS DISPONIBLES

### 1. CATÁLOGO DE MASCOTAS (con filtros y paginación)
```
GET /pets/getPets.php
```

**Parámetros opcionales (query string)**:
- `especie` - String: "Perro" | "Gato"
- `tamano` - String: "Pequeño" | "Mediano" | "Grande"
- `categoria_edad` - String: "cachorro" (0-2 años) | "adulto" (3-7 años) | "senior" (8+ años)
- `edad_min` - Integer: edad mínima en años
- `edad_max` - Integer: edad máxima en años
- `distrito` - String: filtro por ubicación (búsqueda parcial)
- `urgentes` - String: "true" para solo urgentes, omitir o "false" para todas
- `page` - Integer: número de página (default: 1)
- `limit` - Integer: resultados por página (default: 6, mínimo: 6)

**Respuesta exitosa (200)**:
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
      "descripcion": "Perrita muy cariñosa y juguetona. Rescatada de las calles, necesita hogar urgente.",
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

**Ejemplos de uso**:
```
GET /pets/getPets.php                                           # Todas las mascotas
GET /pets/getPets.php?page=2                                    # Página 2
GET /pets/getPets.php?urgentes=true                             # Solo urgentes
GET /pets/getPets.php?especie=Perro                             # Solo perros
GET /pets/getPets.php?especie=Gato&tamano=Pequeño              # Gatos pequeños
GET /pets/getPets.php?categoria_edad=cachorro                   # Cachorros (0-2 años)
GET /pets/getPets.php?especie=Perro&tamano=Grande&categoria_edad=adulto  # Perros grandes adultos
```

---

### 2. DETALLES DE MASCOTA
```
GET /pets/getPetDetails.php?pet_id={id}
```

**Parámetros requeridos**:
- `pet_id` - Integer: ID de la mascota

**Respuesta exitosa (200)**:
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

**Error 404**:
```json
{
  "success": false,
  "message": "Mascota no encontrada"
}
```

**Error 400**:
```json
{
  "success": false,
  "message": "El parámetro pet_id es requerido"
}
```

---

## 📦 MODELOS DE DATOS (Java/Kotlin)

### Pet.java
```java
public class Pet {
    public int id;
    public String name;
    public String especie;          // "Perro" o "Gato"
    public String raza;
    public int edad;                // Edad en años
    public String tamano;           // "Pequeño", "Mediano", "Grande"
    public String sexo;             // "Macho" o "Hembra"
    public String descripcion;
    public String foto_url;
    public String distrito;
    public boolean is_urgent;
    public int priority;
    public String estado;           // "Disponible", "En proceso", "Adoptado"
    public String created_at;
    public String etiqueta_urgencia; // "URGENTE" o null
    public String categoria_edad;    // "Cachorro", "Adulto", "Senior"
}
```

### PetDetail.java
```java
public class PetDetail extends Pet {
    public Integer refugio_id;
    public String updated_at;
    public int solicitudes_pendientes;
    public boolean puede_adoptar;
}
```

### Pagination.java
```java
public class Pagination {
    public int current_page;
    public int total_pages;
    public int total_items;
    public int items_per_page;
    public boolean has_next_page;
    public boolean has_previous_page;
    public Integer next_page;        // null si no hay siguiente
    public Integer previous_page;    // null si no hay anterior
}
```

### FiltersApplied.java
```java
public class FiltersApplied {
    public String especie;
    public String tamano;
    public String distrito;
    public Integer edad_min;
    public Integer edad_max;
    public String categoria_edad;
    public boolean solo_urgentes;
}
```

### CatalogResponse.java
```java
public class CatalogResponse {
    public boolean success;
    public List<Pet> data;
    public Pagination pagination;
    public FiltersApplied filters_applied;
}
```

### PetDetailResponse.java
```java
public class PetDetailResponse {
    public boolean success;
    public PetDetail data;
}
```

---

## 🔌 RETROFIT INTERFACE (Java)

### AdoptMeAPI.java
```java
import retrofit2.Call;
import retrofit2.http.GET;
import retrofit2.http.Query;
import java.util.List;

public interface AdoptMeAPI {
    
    // Catálogo completo sin filtros
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalog();
    
    // Catálogo con paginación
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalogPage(
        @Query("page") int page,
        @Query("limit") int limit
    );
    
    // Catálogo con todos los filtros posibles
    @GET("pets/getPets.php")
    Call<CatalogResponse> getCatalogFiltered(
        @Query("especie") String especie,
        @Query("tamano") String tamano,
        @Query("categoria_edad") String categoriaEdad,
        @Query("edad_min") Integer edadMin,
        @Query("edad_max") Integer edadMax,
        @Query("distrito") String distrito,
        @Query("urgentes") String urgentes,
        @Query("page") Integer page,
        @Query("limit") Integer limit
    );
    
    // Solo mascotas urgentes
    @GET("pets/getPets.php")
    Call<CatalogResponse> getUrgentPets(
        @Query("urgentes") String urgentes,
        @Query("page") Integer page
    );
    
    // Detalles de una mascota
    @GET("pets/getPetDetails.php")
    Call<PetDetailResponse> getPetDetails(
        @Query("pet_id") int petId
    );
}
```

---

## 🚀 CONFIGURACIÓN RETROFIT (Java)

### RetrofitClient.java
```java
import retrofit2.Retrofit;
import retrofit2.converter.gson.GsonConverterFactory;
import com.google.gson.Gson;
import com.google.gson.GsonBuilder;

public class RetrofitClient {
    private static final String BASE_URL = "http://10.0.2.2/adopciones_api/";
    private static Retrofit retrofit = null;
    
    public static Retrofit getClient() {
        if (retrofit == null) {
            Gson gson = new GsonBuilder()
                .setLenient()
                .create();
            
            retrofit = new Retrofit.Builder()
                .baseUrl(BASE_URL)
                .addConverterFactory(GsonConverterFactory.create(gson))
                .build();
        }
        return retrofit;
    }
    
    public static AdoptMeAPI getAPI() {
        return getClient().create(AdoptMeAPI.class);
    }
}
```

---

## 💡 EJEMPLOS DE USO EN ACTIVITY/FRAGMENT

### 1. Obtener catálogo completo
```java
AdoptMeAPI api = RetrofitClient.getAPI();

Call<CatalogResponse> call = api.getCatalog();
call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        if (response.isSuccessful() && response.body() != null) {
            CatalogResponse catalogResponse = response.body();
            
            if (catalogResponse.success) {
                List<Pet> pets = catalogResponse.data;
                Pagination pagination = catalogResponse.pagination;
                
                // Actualizar RecyclerView
                adapter.setPets(pets);
                
                // Manejar paginación
                if (pagination.has_next_page) {
                    btnNextPage.setEnabled(true);
                    btnNextPage.setOnClickListener(v -> loadPage(pagination.next_page));
                } else {
                    btnNextPage.setEnabled(false);
                }
                
                if (pagination.has_previous_page) {
                    btnPrevPage.setEnabled(true);
                    btnPrevPage.setOnClickListener(v -> loadPage(pagination.previous_page));
                } else {
                    btnPrevPage.setEnabled(false);
                }
            }
        } else {
            Toast.makeText(context, "Error al cargar mascotas", Toast.LENGTH_SHORT).show();
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        Toast.makeText(context, "Error de conexión: " + t.getMessage(), Toast.LENGTH_SHORT).show();
    }
});
```

### 2. Filtrar por especie
```java
AdoptMeAPI api = RetrofitClient.getAPI();

String especie = "Perro"; // o "Gato"

Call<CatalogResponse> call = api.getCatalogFiltered(
    especie,  // especie
    null,     // tamano
    null,     // categoria_edad
    null,     // edad_min
    null,     // edad_max
    null,     // distrito
    null,     // urgentes
    1,        // page
    6         // limit
);

call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        if (response.isSuccessful() && response.body() != null && response.body().success) {
            List<Pet> perros = response.body().data;
            adapter.setPets(perros);
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        Log.e("API_ERROR", "Error al filtrar: " + t.getMessage());
    }
});
```

### 3. Solo mascotas urgentes
```java
AdoptMeAPI api = RetrofitClient.getAPI();

Call<CatalogResponse> call = api.getUrgentPets("true", 1);

call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        if (response.isSuccessful() && response.body() != null && response.body().success) {
            List<Pet> urgentes = response.body().data;
            // Mostrar en RecyclerView con badge "URGENTE"
            adapter.setPets(urgentes);
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        Log.e("API_ERROR", "Error: " + t.getMessage());
    }
});
```

### 4. Filtros combinados (Perros grandes adultos)
```java
AdoptMeAPI api = RetrofitClient.getAPI();

Call<CatalogResponse> call = api.getCatalogFiltered(
    "Perro",       // especie
    "Grande",      // tamano
    "adulto",      // categoria_edad
    null,          // edad_min
    null,          // edad_max
    null,          // distrito
    null,          // urgentes
    1,             // page
    6              // limit
);

call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        if (response.isSuccessful() && response.body() != null && response.body().success) {
            List<Pet> perrosGrandesAdultos = response.body().data;
            adapter.setPets(perrosGrandesAdultos);
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        Log.e("API_ERROR", "Error: " + t.getMessage());
    }
});
```

### 5. Obtener detalles de mascota
```java
AdoptMeAPI api = RetrofitClient.getAPI();

int petId = 1; // ID de la mascota seleccionada

Call<PetDetailResponse> call = api.getPetDetails(petId);

call.enqueue(new Callback<PetDetailResponse>() {
    @Override
    public void onResponse(Call<PetDetailResponse> call, Response<PetDetailResponse> response) {
        if (response.isSuccessful() && response.body() != null) {
            if (response.body().success) {
                PetDetail pet = response.body().data;
                
                // Mostrar detalles
                tvName.setText(pet.name);
                tvEspecie.setText(pet.especie);
                tvRaza.setText(pet.raza);
                tvEdad.setText(pet.edad + " años (" + pet.categoria_edad + ")");
                tvTamano.setText(pet.tamano);
                tvSexo.setText(pet.sexo);
                tvDescripcion.setText(pet.descripcion);
                tvDistrito.setText(pet.distrito);
                
                // Cargar foto con Glide
                Glide.with(context)
                    .load(pet.foto_url)
                    .placeholder(R.drawable.placeholder_pet)
                    .error(R.drawable.error_image)
                    .into(ivPetPhoto);
                
                // Mostrar badge urgente
                if (pet.is_urgent) {
                    tvUrgentBadge.setVisibility(View.VISIBLE);
                    tvUrgentBadge.setText(pet.etiqueta_urgencia);
                } else {
                    tvUrgentBadge.setVisibility(View.GONE);
                }
                
                // Botón adoptar
                if (pet.puede_adoptar && pet.estado.equals("Disponible")) {
                    btnAdoptar.setEnabled(true);
                } else {
                    btnAdoptar.setEnabled(false);
                    btnAdoptar.setText("No disponible");
                }
                
                // Mostrar solicitudes pendientes
                if (pet.solicitudes_pendientes > 0) {
                    tvSolicitudes.setText(pet.solicitudes_pendientes + " personas interesadas");
                    tvSolicitudes.setVisibility(View.VISIBLE);
                }
            } else {
                Toast.makeText(context, "Mascota no encontrada", Toast.LENGTH_SHORT).show();
                finish();
            }
        }
    }
    
    @Override
    public void onFailure(Call<PetDetailResponse> call, Throwable t) {
        Toast.makeText(context, "Error de conexión: " + t.getMessage(), Toast.LENGTH_SHORT).show();
    }
});
```

### 6. Paginación (siguiente página)
```java
private void loadPage(int pageNumber) {
    AdoptMeAPI api = RetrofitClient.getAPI();
    
    // Mostrar loading
    progressBar.setVisibility(View.VISIBLE);
    
    Call<CatalogResponse> call = api.getCatalogPage(pageNumber, 6);
    
    call.enqueue(new Callback<CatalogResponse>() {
        @Override
        public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
            progressBar.setVisibility(View.GONE);
            
            if (response.isSuccessful() && response.body() != null && response.body().success) {
                List<Pet> pets = response.body().data;
                Pagination pagination = response.body().pagination;
                
                adapter.setPets(pets);
                
                // Actualizar UI de paginación
                tvPageInfo.setText("Página " + pagination.current_page + " de " + pagination.total_pages);
                btnNextPage.setEnabled(pagination.has_next_page);
                btnPrevPage.setEnabled(pagination.has_previous_page);
            }
        }
        
        @Override
        public void onFailure(Call<CatalogResponse> call, Throwable t) {
            progressBar.setVisibility(View.GONE);
            Toast.makeText(context, "Error: " + t.getMessage(), Toast.LENGTH_SHORT).show();
        }
    });
}
```

---

## 🎨 EJEMPLO DE RECYCLERVIEW ADAPTER

### PetAdapter.java
```java
public class PetAdapter extends RecyclerView.Adapter<PetAdapter.PetViewHolder> {
    
    private List<Pet> pets;
    private Context context;
    private OnPetClickListener listener;
    
    public interface OnPetClickListener {
        void onPetClick(Pet pet);
    }
    
    public PetAdapter(Context context, OnPetClickListener listener) {
        this.context = context;
        this.listener = listener;
        this.pets = new ArrayList<>();
    }
    
    public void setPets(List<Pet> pets) {
        this.pets = pets;
        notifyDataSetChanged();
    }
    
    @NonNull
    @Override
    public PetViewHolder onCreateViewHolder(@NonNull ViewGroup parent, int viewType) {
        View view = LayoutInflater.from(context).inflate(R.layout.item_pet, parent, false);
        return new PetViewHolder(view);
    }
    
    @Override
    public void onBindViewHolder(@NonNull PetViewHolder holder, int position) {
        Pet pet = pets.get(position);
        
        holder.tvName.setText(pet.name);
        holder.tvEspecie.setText(pet.especie);
        holder.tvEdad.setText(pet.edad + " años");
        holder.tvDistrito.setText(pet.distrito);
        
        // Mostrar badge urgente
        if (pet.is_urgent) {
            holder.tvUrgentBadge.setVisibility(View.VISIBLE);
            holder.tvUrgentBadge.setText(pet.etiqueta_urgencia);
        } else {
            holder.tvUrgentBadge.setVisibility(View.GONE);
        }
        
        // Cargar imagen con Glide
        Glide.with(context)
            .load(pet.foto_url)
            .placeholder(R.drawable.placeholder_pet)
            .error(R.drawable.error_image)
            .centerCrop()
            .into(holder.ivPhoto);
        
        // Click listener
        holder.itemView.setOnClickListener(v -> {
            if (listener != null) {
                listener.onPetClick(pet);
            }
        });
    }
    
    @Override
    public int getItemCount() {
        return pets.size();
    }
    
    static class PetViewHolder extends RecyclerView.ViewHolder {
        ImageView ivPhoto;
        TextView tvName, tvEspecie, tvEdad, tvDistrito, tvUrgentBadge;
        
        public PetViewHolder(@NonNull View itemView) {
            super(itemView);
            ivPhoto = itemView.findViewById(R.id.ivPetPhoto);
            tvName = itemView.findViewById(R.id.tvPetName);
            tvEspecie = itemView.findViewById(R.id.tvPetEspecie);
            tvEdad = itemView.findViewById(R.id.tvPetEdad);
            tvDistrito = itemView.findViewById(R.id.tvPetDistrito);
            tvUrgentBadge = itemView.findViewById(R.id.tvUrgentBadge);
        }
    }
}
```

---

## 📱 DEPENDENCIAS GRADLE

### build.gradle (Module: app)
```gradle
dependencies {
    // Retrofit
    implementation 'com.squareup.retrofit2:retrofit:2.9.0'
    implementation 'com.squareup.retrofit2:converter-gson:2.9.0'
    
    // Glide para cargar imágenes
    implementation 'com.github.bumptech.glide:glide:4.15.1'
    annotationProcessor 'com.github.bumptech.glide:compiler:4.15.1'
    
    // RecyclerView
    implementation 'androidx.recyclerview:recyclerview:1.3.1'
    
    // CardView
    implementation 'androidx.cardview:cardview:1.0.0'
}
```

### AndroidManifest.xml
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />

<application
    android:usesCleartextTraffic="true"
    ...>
```

---

## ✅ VALIDACIONES Y MANEJO DE ERRORES

### Validar respuesta
```java
if (response.isSuccessful()) {
    if (response.body() != null) {
        if (response.body().success) {
            // Datos válidos
            List<Pet> pets = response.body().data;
        } else {
            // API retornó success: false
            Toast.makeText(context, "Error al obtener datos", Toast.LENGTH_SHORT).show();
        }
    } else {
        // Body es null
        Toast.makeText(context, "Respuesta vacía del servidor", Toast.LENGTH_SHORT).show();
    }
} else {
    // HTTP error (404, 500, etc.)
    Toast.makeText(context, "Error HTTP: " + response.code(), Toast.LENGTH_SHORT).show();
}
```

### Manejo de estados
```java
// Mostrar loading
progressBar.setVisibility(View.VISIBLE);
recyclerView.setVisibility(View.GONE);
tvEmptyState.setVisibility(View.GONE);

call.enqueue(new Callback<CatalogResponse>() {
    @Override
    public void onResponse(Call<CatalogResponse> call, Response<CatalogResponse> response) {
        progressBar.setVisibility(View.GONE);
        
        if (response.isSuccessful() && response.body() != null && response.body().success) {
            List<Pet> pets = response.body().data;
            
            if (pets.isEmpty()) {
                // Sin resultados
                tvEmptyState.setVisibility(View.VISIBLE);
                tvEmptyState.setText("No se encontraron mascotas");
                recyclerView.setVisibility(View.GONE);
            } else {
                // Mostrar resultados
                recyclerView.setVisibility(View.VISIBLE);
                adapter.setPets(pets);
            }
        } else {
            tvEmptyState.setVisibility(View.VISIBLE);
            tvEmptyState.setText("Error al cargar datos");
        }
    }
    
    @Override
    public void onFailure(Call<CatalogResponse> call, Throwable t) {
        progressBar.setVisibility(View.GONE);
        tvEmptyState.setVisibility(View.VISIBLE);
        tvEmptyState.setText("Error de conexión");
        Log.e("API_ERROR", "Error: " + t.getMessage());
    }
});
```

---

## 🎯 CARACTERÍSTICAS ESPECIALES

### Ordenamiento Automático
- Las mascotas urgentes **SIEMPRE** aparecen primero
- Se ordenan por: `is_urgent DESC → priority DESC → created_at DESC`
- No necesitas ordenar en el frontend

### Paginación
- Mínimo 6 resultados por página (validado en backend)
- Metadatos completos para navegación
- Funciona con todos los filtros

### Filtros
- Se pueden combinar múltiples filtros
- Búsqueda por distrito es parcial (no necesita ser exacto)
- Categoría de edad calculada automáticamente

### Categorización de Edad
- **Cachorro**: 0-2 años
- **Adulto**: 3-7 años
- **Senior**: 8+ años

---

## 📊 DATOS DE PRUEBA

**Total de mascotas en DB**: 12
- **Urgentes (3)**: Luna (Perro), Rocky (Perro), Michi (Gato)
- **Disponibles (9)**: Bella, Max, Pelusa, Toby, Nala, Choco, Simba, Laika, Garfield

**Distribución**:
- Perros: 7 | Gatos: 5
- Pequeño: 4 | Mediano: 4 | Grande: 4
- Cachorros: 5 | Adultos: 6 | Senior: 1

---

## 🚨 NOTAS IMPORTANTES

1. **URL Base para Emulador**: Usar `http://10.0.2.2/adopciones_api/`
2. **Timeout**: Configurar timeout en Retrofit si hay problemas de conexión
3. **Imágenes**: Usar Glide o Picasso para cargar `foto_url`
4. **Loading States**: Siempre mostrar indicador de carga
5. **Empty States**: Manejar cuando no hay resultados
6. **Error Handling**: Capturar errores de red y HTTP

---

## ✅ CHECKLIST DE IMPLEMENTACIÓN

- [ ] Agregar dependencias de Retrofit, Gson y Glide
- [ ] Crear modelos de datos (Pet, Pagination, etc.)
- [ ] Crear interface AdoptMeAPI con Retrofit
- [ ] Crear RetrofitClient singleton
- [ ] Agregar permisos de INTERNET en AndroidManifest
- [ ] Habilitar cleartext traffic en AndroidManifest
- [ ] Crear RecyclerView con adaptador
- [ ] Implementar carga de catálogo
- [ ] Implementar filtros (Spinner/Chip)
- [ ] Implementar paginación
- [ ] Crear pantalla de detalles
- [ ] Cargar imágenes con Glide
- [ ] Mostrar badge "URGENTE"
- [ ] Manejar estados de carga/error/vacío
- [ ] Probar con diferentes filtros

---

## 🎉 ESTADO DEL BACKEND

✅ **COMPLETAMENTE FUNCIONAL**
- Todos los endpoints probados y funcionando
- 12 mascotas de ejemplo disponibles
- Filtros, paginación y ordenamiento implementados
- Criterios de aceptación HU-004 cumplidos al 100%

**¡Listo para integrar con Android!** 🚀
