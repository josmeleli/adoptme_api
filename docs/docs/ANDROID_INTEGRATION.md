# 📱 Guía Completa Android Studio - AdoptMe API

## 🎯 URL Base de la API
```java
public static final String BASE_URL = "http://10.0.2.2/adopciones_api/"; // Emulador Android
// En dispositivo físico usar: http://TU_IP_LOCAL/adopciones_api/
```

---

## 📦 1. Dependencias (build.gradle)

```gradle
dependencies {
    // Volley para peticiones HTTP
    implementation 'com.android.volley:volley:1.2.1'
    
    // Gson para JSON
    implementation 'com.google.code.gson:gson:2.10.1'
    
    // Material Design (opcional, para UI mejorada)
    implementation 'com.google.android.material:material:1.11.0'
}
```

**Permisos en AndroidManifest.xml:**
```xml
<uses-permission android:name="android.permission.INTERNET" />
<uses-permission android:name="android.permission.ACCESS_NETWORK_STATE" />
```

---

## 🔧 2. Clase Helper de API (ApiHelper.java)

```java
package com.tuapp.adoptme.api;

import android.content.Context;
import com.android.volley.Request;
import com.android.volley.RequestQueue;
import com.android.volley.toolbox.JsonObjectRequest;
import com.android.volley.toolbox.Volley;
import org.json.JSONObject;

public class ApiHelper {
    private static final String BASE_URL = "http://10.0.2.2/adopciones_api/";
    private static ApiHelper instance;
    private RequestQueue requestQueue;
    private Context context;

    private ApiHelper(Context context) {
        this.context = context.getApplicationContext();
        this.requestQueue = getRequestQueue();
    }

    public static synchronized ApiHelper getInstance(Context context) {
        if (instance == null) {
            instance = new ApiHelper(context);
        }
        return instance;
    }

    public RequestQueue getRequestQueue() {
        if (requestQueue == null) {
            requestQueue = Volley.newRequestQueue(context);
        }
        return requestQueue;
    }

    public <T> void addToRequestQueue(Request<T> req) {
        getRequestQueue().add(req);
    }

    public String getUrl(String endpoint) {
        return BASE_URL + endpoint;
    }
}
```

---

## 📝 3. REGISTRO DE USUARIO

### 3.1 Layout (activity_register.xml)

```xml
<?xml version="1.0" encoding="utf-8"?>
<ScrollView xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:padding="24dp">

    <LinearLayout
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:orientation="vertical">

        <TextView
            android:layout_width="wrap_content"
            android:layout_height="wrap_content"
            android:text="Crear Cuenta"
            android:textSize="24sp"
            android:textStyle="bold"
            android:layout_marginBottom="24dp"/>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="Nombres">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etNombres"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="textPersonName"/>
        </com.google.android.material.textfield.TextInputLayout>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="Apellidos">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etApellidos"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="textPersonName"/>
        </com.google.android.material.textfield.TextInputLayout>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="DNI (8 dígitos)">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etDNI"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="number"
                android:maxLength="8"/>
        </com.google.android.material.textfield.TextInputLayout>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="Correo Electrónico">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etEmail"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="textEmailAddress"/>
        </com.google.android.material.textfield.TextInputLayout>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="Teléfono (9 dígitos)">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etTelefono"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="phone"
                android:maxLength="9"/>
        </com.google.android.material.textfield.TextInputLayout>

        <com.google.android.material.textfield.TextInputLayout
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:hint="Contraseña">
            <com.google.android.material.textfield.TextInputEditText
                android:id="@+id/etPassword"
                android:layout_width="match_parent"
                android:layout_height="wrap_content"
                android:inputType="textPassword"/>
        </com.google.android.material.textfield.TextInputLayout>

        <Button
            android:id="@+id/btnRegistrar"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:text="Registrarme"
            android:layout_marginTop="16dp"/>

        <ProgressBar
            android:id="@+id/progressBar"
            android:layout_width="wrap_content"
            android:layout_height="wrap_content"
            android:layout_gravity="center"
            android:visibility="gone"/>

    </LinearLayout>
</ScrollView>
```

### 3.2 Activity Java (RegisterActivity.java)

```java
package com.tuapp.adoptme;

import android.content.Intent;
import android.os.Bundle;
import android.util.Patterns;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.google.android.material.textfield.TextInputEditText;
import com.tuapp.adoptme.api.ApiHelper;
import org.json.JSONException;
import org.json.JSONObject;
import java.util.regex.Pattern;

public class RegisterActivity extends AppCompatActivity {
    
    private TextInputEditText etNombres, etApellidos, etDNI, etEmail, etTelefono, etPassword;
    private Button btnRegistrar;
    private ProgressBar progressBar;
    private ApiHelper apiHelper;

    // Patrones de validación (deben coincidir con la API)
    private static final Pattern PATTERN_NOMBRES = Pattern.compile("^[a-zA-ZáéíóúÁÉÍÓÚñÑ\\s]+$");
    private static final Pattern PATTERN_DNI = Pattern.compile("^\\d{8}$");
    private static final Pattern PATTERN_TELEFONO = Pattern.compile("^\\d{9}$");

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_register);

        // Inicializar vistas
        etNombres = findViewById(R.id.etNombres);
        etApellidos = findViewById(R.id.etApellidos);
        etDNI = findViewById(R.id.etDNI);
        etEmail = findViewById(R.id.etEmail);
        etTelefono = findViewById(R.id.etTelefono);
        etPassword = findViewById(R.id.etPassword);
        btnRegistrar = findViewById(R.id.btnRegistrar);
        progressBar = findViewById(R.id.progressBar);

        apiHelper = ApiHelper.getInstance(this);

        btnRegistrar.setOnClickListener(v -> validarYRegistrar());
    }

    private void validarYRegistrar() {
        // Obtener valores
        String nombres = etNombres.getText().toString().trim();
        String apellidos = etApellidos.getText().toString().trim();
        String dni = etDNI.getText().toString().trim();
        String email = etEmail.getText().toString().trim();
        String telefono = etTelefono.getText().toString().trim();
        String password = etPassword.getText().toString();

        // VALIDACIONES (mismo orden que la API)

        // 1. Validar nombres
        if (nombres.isEmpty() || !PATTERN_NOMBRES.matcher(nombres).matches()) {
            etNombres.setError("Nombres inválidos. Solo letras y espacios");
            etNombres.requestFocus();
            return;
        }

        // 2. Validar apellidos
        if (apellidos.isEmpty() || !PATTERN_NOMBRES.matcher(apellidos).matches()) {
            etApellidos.setError("Apellidos inválidos. Solo letras y espacios");
            etApellidos.requestFocus();
            return;
        }

        // 3. Validar DNI (exactamente 8 dígitos)
        if (!PATTERN_DNI.matcher(dni).matches()) {
            etDNI.setError("DNI debe tener exactamente 8 dígitos");
            etDNI.requestFocus();
            return;
        }

        // 4. Validar email
        if (email.isEmpty() || !Patterns.EMAIL_ADDRESS.matcher(email).matches()) {
            etEmail.setError("Correo electrónico inválido");
            etEmail.requestFocus();
            return;
        }

        // 5. Validar teléfono (exactamente 9 dígitos)
        if (!PATTERN_TELEFONO.matcher(telefono).matches()) {
            etTelefono.setError("Teléfono debe tener exactamente 9 dígitos");
            etTelefono.requestFocus();
            return;
        }

        // 6. Validar contraseña (mínimo 6 caracteres)
        if (password.isEmpty() || password.length() < 6) {
            etPassword.setError("La contraseña debe tener al menos 6 caracteres");
            etPassword.requestFocus();
            return;
        }

        // Si todo es válido, hacer el registro
        registrarUsuario(nombres, apellidos, dni, email, telefono, password);
    }

    private void registrarUsuario(String nombres, String apellidos, String dni, 
                                  String email, String telefono, String password) {
        // Mostrar progress bar
        progressBar.setVisibility(View.VISIBLE);
        btnRegistrar.setEnabled(false);

        // Crear JSON con los datos
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("nombres", nombres);
            jsonBody.put("apellidos", apellidos);
            jsonBody.put("dni", dni);
            jsonBody.put("email", email);
            jsonBody.put("telefono", telefono);
            jsonBody.put("password", password);
        } catch (JSONException e) {
            e.printStackTrace();
            Toast.makeText(this, "Error al crear datos", Toast.LENGTH_SHORT).show();
            progressBar.setVisibility(View.GONE);
            btnRegistrar.setEnabled(true);
            return;
        }

        // Hacer petición POST
        String url = apiHelper.getUrl("register.php");
        
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST,
            url,
            jsonBody,
            response -> {
                // Respuesta exitosa
                progressBar.setVisibility(View.GONE);
                btnRegistrar.setEnabled(true);
                
                try {
                    boolean success = response.getBoolean("success");
                    
                    if (success) {
                        int userId = response.getInt("user_id");
                        String message = response.getString("message");
                        boolean emailEnviado = response.optBoolean("email_enviado", false);
                        
                        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
                        
                        // Ir a pantalla de verificación
                        Intent intent = new Intent(RegisterActivity.this, VerifyActivity.class);
                        intent.putExtra("user_id", userId);
                        intent.putExtra("email", email);
                        intent.putExtra("email_enviado", emailEnviado);
                        
                        // Si el email NO se envió, el código viene en la respuesta (desarrollo)
                        if (!emailEnviado && response.has("verification_code")) {
                            String code = response.getString("verification_code");
                            intent.putExtra("verification_code", code);
                        }
                        
                        startActivity(intent);
                        finish();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    Toast.makeText(this, "Error al procesar respuesta", Toast.LENGTH_SHORT).show();
                }
            },
            error -> {
                // Error en la petición
                progressBar.setVisibility(View.GONE);
                btnRegistrar.setEnabled(true);
                
                String errorMessage = "Error al registrar";
                
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
        );

        apiHelper.addToRequestQueue(request);
    }
}
```

---

## ✅ 4. VERIFICACIÓN DE CUENTA

### 4.1 Layout (activity_verify.xml)

```xml
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:orientation="vertical"
    android:padding="24dp"
    android:gravity="center">

    <ImageView
        android:layout_width="100dp"
        android:layout_height="100dp"
        android:src="@drawable/ic_email"
        android:layout_marginBottom="24dp"/>

    <TextView
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Verificar Cuenta"
        android:textSize="24sp"
        android:textStyle="bold"
        android:layout_marginBottom="8dp"/>

    <TextView
        android:id="@+id/tvInstrucciones"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Ingresa el código de 6 dígitos que enviamos a tu correo"
        android:textAlignment="center"
        android:layout_marginBottom="24dp"/>

    <com.google.android.material.textfield.TextInputLayout
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:hint="Código de Verificación">
        <com.google.android.material.textfield.TextInputEditText
            android:id="@+id/etCodigo"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:inputType="number"
            android:maxLength="6"
            android:textAlignment="center"
            android:textSize="24sp"/>
    </com.google.android.material.textfield.TextInputLayout>

    <Button
        android:id="@+id/btnVerificar"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:text="Verificar"
        android:layout_marginTop="16dp"/>

    <ProgressBar
        android:id="@+id/progressBar"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:layout_marginTop="16dp"
        android:visibility="gone"/>

</LinearLayout>
```

### 4.2 Activity Java (VerifyActivity.java)

```java
package com.tuapp.adoptme;

import android.content.Intent;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.google.android.material.textfield.TextInputEditText;
import com.tuapp.adoptme.api.ApiHelper;
import org.json.JSONException;
import org.json.JSONObject;

public class VerifyActivity extends AppCompatActivity {
    
    private TextInputEditText etCodigo;
    private TextView tvInstrucciones;
    private Button btnVerificar;
    private ProgressBar progressBar;
    private ApiHelper apiHelper;
    
    private int userId;
    private String email;
    private boolean emailEnviado;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_verify);

        // Obtener datos del intent
        userId = getIntent().getIntExtra("user_id", 0);
        email = getIntent().getStringExtra("email");
        emailEnviado = getIntent().getBooleanExtra("email_enviado", false);
        
        // Si no se envió email (modo desarrollo), mostrar el código
        if (!emailEnviado && getIntent().hasExtra("verification_code")) {
            String code = getIntent().getStringExtra("verification_code");
            Toast.makeText(this, "Código de desarrollo: " + code, Toast.LENGTH_LONG).show();
        }

        // Inicializar vistas
        etCodigo = findViewById(R.id.etCodigo);
        tvInstrucciones = findViewById(R.id.tvInstrucciones);
        btnVerificar = findViewById(R.id.btnVerificar);
        progressBar = findViewById(R.id.progressBar);

        apiHelper = ApiHelper.getInstance(this);

        // Actualizar instrucciones
        tvInstrucciones.setText("Ingresa el código de 6 dígitos que enviamos a:\n" + email);

        btnVerificar.setOnClickListener(v -> verificarCodigo());
    }

    private void verificarCodigo() {
        String codigo = etCodigo.getText().toString().trim();

        // Validar que el código tenga 6 dígitos
        if (codigo.length() != 6) {
            etCodigo.setError("El código debe tener 6 dígitos");
            etCodigo.requestFocus();
            return;
        }

        // Mostrar progress bar
        progressBar.setVisibility(View.VISIBLE);
        btnVerificar.setEnabled(false);

        // Crear JSON
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("user_id", userId);
            jsonBody.put("code", codigo);
        } catch (JSONException e) {
            e.printStackTrace();
            return;
        }

        // Hacer petición POST
        String url = apiHelper.getUrl("verify.php");
        
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST,
            url,
            jsonBody,
            response -> {
                progressBar.setVisibility(View.GONE);
                btnVerificar.setEnabled(true);
                
                try {
                    boolean success = response.getBoolean("success");
                    String message = response.getString("message");
                    
                    if (success) {
                        Toast.makeText(this, message, Toast.LENGTH_LONG).show();
                        
                        // Ir a login
                        Intent intent = new Intent(VerifyActivity.this, LoginActivity.class);
                        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
                        startActivity(intent);
                        finish();
                    } else {
                        Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            },
            error -> {
                progressBar.setVisibility(View.GONE);
                btnVerificar.setEnabled(true);
                
                String errorMessage = "Error al verificar código";
                
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
        );

        apiHelper.addToRequestQueue(request);
    }
}
```

---

## 🔐 5. LOGIN

### 5.1 Layout (activity_login.xml)

```xml
<?xml version="1.0" encoding="utf-8"?>
<LinearLayout xmlns:android="http://schemas.android.com/apk/res/android"
    android:layout_width="match_parent"
    android:layout_height="match_parent"
    android:orientation="vertical"
    android:padding="24dp"
    android:gravity="center">

    <TextView
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="Iniciar Sesión"
        android:textSize="28sp"
        android:textStyle="bold"
        android:layout_marginBottom="32dp"/>

    <com.google.android.material.textfield.TextInputLayout
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:hint="Correo Electrónico">
        <com.google.android.material.textfield.TextInputEditText
            android:id="@+id/etEmail"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:inputType="textEmailAddress"/>
    </com.google.android.material.textfield.TextInputLayout>

    <com.google.android.material.textfield.TextInputLayout
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:hint="Contraseña">
        <com.google.android.material.textfield.TextInputEditText
            android:id="@+id/etPassword"
            android:layout_width="match_parent"
            android:layout_height="wrap_content"
            android:inputType="textPassword"/>
    </com.google.android.material.textfield.TextInputLayout>

    <Button
        android:id="@+id/btnLogin"
        android:layout_width="match_parent"
        android:layout_height="wrap_content"
        android:text="Iniciar Sesión"
        android:layout_marginTop="16dp"/>

    <TextView
        android:id="@+id/tvRegistrarse"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:text="¿No tienes cuenta? Regístrate"
        android:textColor="@color/colorPrimary"
        android:layout_marginTop="16dp"/>

    <ProgressBar
        android:id="@+id/progressBar"
        android:layout_width="wrap_content"
        android:layout_height="wrap_content"
        android:layout_marginTop="16dp"
        android:visibility="gone"/>

</LinearLayout>
```

### 5.2 Activity Java (LoginActivity.java)

```java
package com.tuapp.adoptme;

import android.content.Intent;
import android.content.SharedPreferences;
import android.os.Bundle;
import android.view.View;
import android.widget.Button;
import android.widget.ProgressBar;
import android.widget.TextView;
import android.widget.Toast;
import androidx.appcompat.app.AppCompatActivity;
import com.android.volley.Request;
import com.android.volley.toolbox.JsonObjectRequest;
import com.google.android.material.textfield.TextInputEditText;
import com.tuapp.adoptme.api.ApiHelper;
import org.json.JSONException;
import org.json.JSONObject;

public class LoginActivity extends AppCompatActivity {
    
    private TextInputEditText etEmail, etPassword;
    private Button btnLogin;
    private TextView tvRegistrarse;
    private ProgressBar progressBar;
    private ApiHelper apiHelper;
    private SharedPreferences sharedPreferences;

    @Override
    protected void onCreate(Bundle savedInstanceState) {
        super.onCreate(savedInstanceState);
        setContentView(R.layout.activity_login);

        // Inicializar SharedPreferences
        sharedPreferences = getSharedPreferences("AdoptMePrefs", MODE_PRIVATE);

        // Verificar si ya hay sesión activa
        if (sharedPreferences.contains("token")) {
            irAMainActivity();
            return;
        }

        // Inicializar vistas
        etEmail = findViewById(R.id.etEmail);
        etPassword = findViewById(R.id.etPassword);
        btnLogin = findViewById(R.id.btnLogin);
        tvRegistrarse = findViewById(R.id.tvRegistrarse);
        progressBar = findViewById(R.id.progressBar);

        apiHelper = ApiHelper.getInstance(this);

        btnLogin.setOnClickListener(v -> hacerLogin());
        
        tvRegistrarse.setOnClickListener(v -> {
            startActivity(new Intent(LoginActivity.this, RegisterActivity.class));
        });
    }

    private void hacerLogin() {
        String email = etEmail.getText().toString().trim();
        String password = etPassword.getText().toString();

        // Validaciones básicas
        if (email.isEmpty()) {
            etEmail.setError("Ingresa tu correo");
            etEmail.requestFocus();
            return;
        }

        if (password.isEmpty()) {
            etPassword.setError("Ingresa tu contraseña");
            etPassword.requestFocus();
            return;
        }

        // Mostrar progress bar
        progressBar.setVisibility(View.VISIBLE);
        btnLogin.setEnabled(false);

        // Crear JSON
        JSONObject jsonBody = new JSONObject();
        try {
            jsonBody.put("email", email);
            jsonBody.put("password", password);
        } catch (JSONException e) {
            e.printStackTrace();
            return;
        }

        // Hacer petición POST
        String url = apiHelper.getUrl("login.php");
        
        JsonObjectRequest request = new JsonObjectRequest(
            Request.Method.POST,
            url,
            jsonBody,
            response -> {
                progressBar.setVisibility(View.GONE);
                btnLogin.setEnabled(true);
                
                try {
                    boolean success = response.getBoolean("success");
                    
                    if (success) {
                        // Guardar token y datos del usuario
                        String token = response.getString("token");
                        JSONObject user = response.getJSONObject("user");
                        
                        SharedPreferences.Editor editor = sharedPreferences.edit();
                        editor.putString("token", token);
                        editor.putInt("user_id", user.getInt("id"));
                        editor.putString("email", user.getString("email"));
                        editor.putString("nombres", user.getString("nombres"));
                        editor.putString("apellidos", user.getString("apellidos"));
                        editor.putString("dni", user.getString("dni"));
                        editor.putString("telefono", user.getString("telefono"));
                        editor.apply();
                        
                        Toast.makeText(this, "Bienvenido " + user.getString("nombres"), Toast.LENGTH_SHORT).show();
                        
                        irAMainActivity();
                    }
                } catch (JSONException e) {
                    e.printStackTrace();
                    Toast.makeText(this, "Error al procesar respuesta", Toast.LENGTH_SHORT).show();
                }
            },
            error -> {
                progressBar.setVisibility(View.GONE);
                btnLogin.setEnabled(true);
                
                String errorMessage = "Error al iniciar sesión";
                
                if (error.networkResponse != null) {
                    int statusCode = error.networkResponse.statusCode;
                    
                    // 403 = Cuenta no verificada
                    if (statusCode == 403 && error.networkResponse.data != null) {
                        try {
                            String errorData = new String(error.networkResponse.data);
                            JSONObject errorJson = new JSONObject(errorData);
                            errorMessage = errorJson.optString("error", errorMessage);
                            
                            // Si tiene user_id, redirigir a verificación
                            if (errorJson.has("user_id")) {
                                int userId = errorJson.getInt("user_id");
                                Toast.makeText(this, errorMessage, Toast.LENGTH_LONG).show();
                                
                                Intent intent = new Intent(LoginActivity.this, VerifyActivity.class);
                                intent.putExtra("user_id", userId);
                                intent.putExtra("email", email);
                                startActivity(intent);
                                return;
                            }
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    } else if (error.networkResponse.data != null) {
                        try {
                            String errorData = new String(error.networkResponse.data);
                            JSONObject errorJson = new JSONObject(errorData);
                            errorMessage = errorJson.optString("error", errorMessage);
                        } catch (JSONException e) {
                            e.printStackTrace();
                        }
                    }
                }
                
                Toast.makeText(this, errorMessage, Toast.LENGTH_LONG).show();
            }
        );

        apiHelper.addToRequestQueue(request);
    }

    private void irAMainActivity() {
        Intent intent = new Intent(LoginActivity.this, MainActivity.class);
        intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
        startActivity(intent);
        finish();
    }
}
```

---

## 👤 6. PERFIL DE USUARIO

### 6.1 Obtener Perfil (GET)

```java
private void obtenerPerfil() {
    SharedPreferences prefs = getSharedPreferences("AdoptMePrefs", MODE_PRIVATE);
    String token = prefs.getString("token", "");
    int userId = prefs.getInt("user_id", 0);

    String url = apiHelper.getUrl("users/getUser.php?user_id=" + userId);
    
    JsonObjectRequest request = new JsonObjectRequest(
        Request.Method.GET,
        url,
        null,
        response -> {
            try {
                boolean success = response.getBoolean("success");
                
                if (success) {
                    JSONObject user = response.getJSONObject("user");
                    
                    // Datos básicos
                    String nombres = user.getString("nombres");
                    String apellidos = user.getString("apellidos");
                    String dni = user.getString("dni");
                    String email = user.getString("email");
                    String telefono = user.getString("telefono");
                    String distrito = user.optString("distrito", "No especificado");
                    
                    // Preferencias (si existen)
                    if (user.has("preferencias")) {
                        JSONObject prefs = user.getJSONObject("preferencias");
                        String especie = prefs.optString("especie_preferida", "Cualquiera");
                        String tamano = prefs.optString("tamano_preferido", "Cualquiera");
                        String edad = prefs.optString("edad_preferida", "Cualquiera");
                        
                        // Actualizar UI con los datos
                        // ...
                    }
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        },
        error -> {
            // Manejar error
            Toast.makeText(this, "Error al cargar perfil", Toast.LENGTH_SHORT).show();
        }
    ) {
        @Override
        public Map<String, String> getHeaders() {
            Map<String, String> headers = new HashMap<>();
            headers.put("Authorization", "Bearer " + token);
            return headers;
        }
    };

    apiHelper.addToRequestQueue(request);
}
```

### 6.2 Actualizar Perfil (PUT)

```java
private void actualizarPerfil(String nombres, String apellidos, String distrito, 
                              String telefono, String especie, String tamano, String edad) {
    SharedPreferences prefs = getSharedPreferences("AdoptMePrefs", MODE_PRIVATE);
    String token = prefs.getString("token", "");
    int userId = prefs.getInt("user_id", 0);

    JSONObject jsonBody = new JSONObject();
    try {
        jsonBody.put("user_id", userId);
        jsonBody.put("nombres", nombres);
        jsonBody.put("apellidos", apellidos);
        jsonBody.put("distrito", distrito);
        jsonBody.put("telefono", telefono);
        jsonBody.put("especie_preferida", especie);
        jsonBody.put("tamano_preferido", tamano);
        jsonBody.put("edad_preferida", edad);
    } catch (JSONException e) {
        e.printStackTrace();
        return;
    }

    String url = apiHelper.getUrl("users/updateUser.php");
    
    JsonObjectRequest request = new JsonObjectRequest(
        Request.Method.PUT,
        url,
        jsonBody,
        response -> {
            try {
                boolean success = response.getBoolean("success");
                String message = response.getString("message");
                
                if (success) {
                    Toast.makeText(this, message, Toast.LENGTH_SHORT).show();
                    
                    // Actualizar SharedPreferences
                    SharedPreferences.Editor editor = prefs.edit();
                    editor.putString("nombres", nombres);
                    editor.putString("apellidos", apellidos);
                    editor.putString("telefono", telefono);
                    editor.apply();
                }
            } catch (JSONException e) {
                e.printStackTrace();
            }
        },
        error -> {
            String errorMessage = "Error al actualizar perfil";
            
            if (error.networkResponse != null && error.networkResponse.data != null) {
                try {
                    String errorData = new String(error.networkResponse.data);
                    JSONObject errorJson = new JSONObject(errorData);
                    errorMessage = errorJson.optString("error", errorMessage);
                } catch (JSONException e) {
                    e.printStackTrace();
                }
            }
            
            Toast.makeText(this, errorMessage, Toast.LENGTH_SHORT).show();
        }
    ) {
        @Override
        public Map<String, String> getHeaders() {
            Map<String, String> headers = new HashMap<>();
            headers.put("Authorization", "Bearer " + token);
            return headers;
        }
    };

    apiHelper.addToRequestQueue(request);
}
```

---

## 🔄 7. LOGOUT

```java
private void cerrarSesion() {
    SharedPreferences prefs = getSharedPreferences("AdoptMePrefs", MODE_PRIVATE);
    String token = prefs.getString("token", "");

    JSONObject jsonBody = new JSONObject();
    try {
        jsonBody.put("token", token);
    } catch (JSONException e) {
        e.printStackTrace();
    }

    String url = apiHelper.getUrl("logout.php");
    
    JsonObjectRequest request = new JsonObjectRequest(
        Request.Method.POST,
        url,
        jsonBody,
        response -> {
            // Limpiar SharedPreferences
            SharedPreferences.Editor editor = prefs.edit();
            editor.clear();
            editor.apply();
            
            // Ir a login
            Intent intent = new Intent(this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        },
        error -> {
            // Aunque falle, limpiar sesión local
            SharedPreferences.Editor editor = prefs.edit();
            editor.clear();
            editor.apply();
            
            Intent intent = new Intent(this, LoginActivity.class);
            intent.setFlags(Intent.FLAG_ACTIVITY_NEW_TASK | Intent.FLAG_ACTIVITY_CLEAR_TASK);
            startActivity(intent);
            finish();
        }
    ) {
        @Override
        public Map<String, String> getHeaders() {
            Map<String, String> headers = new HashMap<>();
            headers.put("Authorization", "Bearer " + token);
            return headers;
        }
    };

    apiHelper.addToRequestQueue(request);
}
```

---

## 📱 8. FLUJO COMPLETO DE LA APP

```
┌─────────────────┐
│  SplashActivity │ (Verificar si hay token guardado)
└────────┬────────┘
         │
         ├─ Token existe ──────> MainActivity
         │
         └─ No token ──────> LoginActivity
                               │
                               ├─ Login exitoso ──────> MainActivity
                               │
                               ├─ No verificado ──────> VerifyActivity
                               │
                               └─ No tiene cuenta ────> RegisterActivity
                                                           │
                                                           └─> VerifyActivity ──> LoginActivity ──> MainActivity
```

---

## 🔒 9. CLASE USUARIO (Modelo)

```java
package com.tuapp.adoptme.models;

public class User {
    private int id;
    private String email;
    private String nombres;
    private String apellidos;
    private String dni;
    private String telefono;
    private String distrito;
    
    // Constructor vacío
    public User() {}
    
    // Constructor completo
    public User(int id, String email, String nombres, String apellidos, 
                String dni, String telefono, String distrito) {
        this.id = id;
        this.email = email;
        this.nombres = nombres;
        this.apellidos = apellidos;
        this.dni = dni;
        this.telefono = telefono;
        this.distrito = distrito;
    }
    
    // Getters y Setters
    public int getId() { return id; }
    public void setId(int id) { this.id = id; }
    
    public String getEmail() { return email; }
    public void setEmail(String email) { this.email = email; }
    
    public String getNombres() { return nombres; }
    public void setNombres(String nombres) { this.nombres = nombres; }
    
    public String getApellidos() { return apellidos; }
    public void setApellidos(String apellidos) { this.apellidos = apellidos; }
    
    public String getDni() { return dni; }
    public void setDni(String dni) { this.dni = dni; }
    
    public String getTelefono() { return telefono; }
    public void setTelefono(String telefono) { this.telefono = telefono; }
    
    public String getDistrito() { return distrito; }
    public void setDistrito(String distrito) { this.distrito = distrito; }
    
    public String getNombreCompleto() {
        return nombres + " " + apellidos;
    }
}
```

---

## ✅ 10. CHECKLIST DE IMPLEMENTACIÓN

### **Paso 1: Configuración**
- [ ] Añadir dependencias en build.gradle
- [ ] Añadir permisos de internet en AndroidManifest.xml
- [ ] Crear clase ApiHelper.java

### **Paso 2: Registro**
- [ ] Crear layout activity_register.xml
- [ ] Crear RegisterActivity.java
- [ ] Implementar validaciones (nombres, apellidos, DNI 8 dígitos, email, teléfono 9 dígitos, contraseña min 6)
- [ ] Probar registro

### **Paso 3: Verificación**
- [ ] Crear layout activity_verify.xml
- [ ] Crear VerifyActivity.java
- [ ] Probar verificación con código de email

### **Paso 4: Login**
- [ ] Crear layout activity_login.xml
- [ ] Crear LoginActivity.java
- [ ] Implementar SharedPreferences para guardar token
- [ ] Probar login

### **Paso 5: Perfil**
- [ ] Implementar obtener perfil
- [ ] Implementar actualizar perfil
- [ ] Probar actualización

### **Paso 6: Logout**
- [ ] Implementar cerrar sesión
- [ ] Limpiar SharedPreferences
- [ ] Probar logout

---

## 🌐 11. ENDPOINTS DISPONIBLES

| Método | Endpoint | Requiere Token | Descripción |
|--------|----------|----------------|-------------|
| POST | `/register.php` | No | Registrar usuario + envío de email |
| POST | `/verify.php` | No | Verificar código + email bienvenida |
| POST | `/login.php` | No | Iniciar sesión |
| POST | `/logout.php` | Sí | Cerrar sesión |
| POST | `/refresh_token.php` | Sí | Renovar token |
| GET | `/users/getUser.php` | Sí | Obtener perfil |
| PUT | `/users/updateUser.php` | Sí | Actualizar perfil |
| POST | `/chat/sendMessage.php` | Sí | Enviar mensaje |
| GET | `/chat/getMessages.php` | Sí | Obtener mensajes |
| POST | `/adoption/createAdoption.php` | Sí | Crear solicitud |
| GET | `/adoption/trackAdoption.php` | Sí | Seguimiento |
| GET | `/notifications/getNotifications.php` | Sí | Notificaciones |

---

## 🎨 12. COLORES Y RECURSOS

### colors.xml
```xml
<?xml version="1.0" encoding="utf-8"?>
<resources>
    <color name="colorPrimary">#667eea</color>
    <color name="colorPrimaryDark">#764ba2</color>
    <color name="colorAccent">#FF4081</color>
    <color name="white">#FFFFFF</color>
    <color name="error">#F44336</color>
</resources>
```

---

## 📝 NOTAS IMPORTANTES

1. **Emulador Android:** Usa `10.0.2.2` en lugar de `localhost`
2. **Dispositivo físico:** Usa tu IP local (ej: `192.168.1.100`)
3. **Validaciones:** Deben coincidir exactamente con las del backend
4. **Token:** Guardar en SharedPreferences de forma segura
5. **Email:** En desarrollo puede que el código venga en la respuesta JSON si el email no se envió
6. **Errors HTTP:**
   - 400 = Datos inválidos
   - 401 = No autorizado
   - 403 = Cuenta no verificada
   - 404 = No encontrado
   - 500 = Error del servidor

---

¡Tu API de AdoptMe está lista para integrarse con Android! 🚀🐾
