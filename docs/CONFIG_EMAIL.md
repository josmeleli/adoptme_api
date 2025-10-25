@# 📧 Configuración de Envío de Emails - AdoptMe API

## 🎯 Estado Actual

✅ **Servicio de email implementado**
- `email_service.php` creado con 3 funciones principales
- Integrado en `register.php` y `verify.php`
- Templates HTML profesionales incluidos

⚠️ **En desarrollo**: El código se devuelve en la respuesta JSON si el email falla

---

## 📝 Emails Implementados

### 1. **Código de Verificación** (Registro)
- ✅ Template HTML con diseño atractivo
- ✅ Código de 6 dígitos destacado
- ✅ Expira en 15 minutos
- ✅ Versión texto plano (fallback)

### 2. **Email de Bienvenida** (Verificación)
- ✅ Se envía automáticamente al verificar cuenta
- ✅ Lista de funcionalidades disponibles
- ✅ Diseño profesional

### 3. **Recuperación de Contraseña** (Futuro)
- ✅ Template listo para implementar
- ⏳ Endpoint pendiente de crear

---

## ⚙️ Configuración Requerida

### **Opción 1: Gmail (Recomendado para desarrollo)**

1. **Editar `email_service.php` líneas 13-14:**
```php
define('SMTP_USERNAME', 'tu_email@gmail.com');
define('SMTP_PASSWORD', 'tu_contraseña_app');
```

2. **Generar contraseña de aplicación en Gmail:**
   - Ve a: https://myaccount.google.com/security
   - Activa "Verificación en 2 pasos"
   - En "Contraseñas de aplicaciones", crea una nueva
   - Usa esa contraseña en `SMTP_PASSWORD`

3. **PHP debe tener configurado sendmail o SMTP:**
   - En `php.ini` busca `[mail function]`
   - O usa una librería como PHPMailer (ver más abajo)

---

### **Opción 2: Servidor SMTP Propio**

Editar en `email_service.php`:
```php
define('SMTP_HOST', 'smtp.tudominio.com');
define('SMTP_PORT', 587);  // o 465 para SSL
define('SMTP_USERNAME', 'noreply@tudominio.com');
define('SMTP_PASSWORD', 'tu_contraseña');
```

---

### **Opción 3: PHPMailer (RECOMENDADO PARA PRODUCCIÓN)**

1. **Instalar PHPMailer:**
```bash
composer require phpmailer/phpmailer
```

2. **Crear archivo `email_service_phpmailer.php`:**
```php
<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'vendor/autoload.php';

function enviarCodigoVerificacion($email, $nombres, $codigo) {
    $mail = new PHPMailer(true);
    
    try {
        // Configuración SMTP
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'tu_email@gmail.com';
        $mail->Password = 'tu_contraseña_app';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom('noreply@adoptme.com', 'AdoptMe');
        $mail->addAddress($email, $nombres);
        
        // Contenido
        $mail->isHTML(true);
        $mail->Subject = 'Código de Verificación - AdoptMe 🐾';
        $mail->Body = generarHTMLVerificacion($nombres, $codigo);
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: {$mail->ErrorInfo}");
        return false;
    }
}
?>
```

3. **Actualizar `register.php`:**
```php
require_once __DIR__ . '/email_service_phpmailer.php';
```

---

### **Opción 4: Servicios de Email (SendGrid, Mailgun, etc.)**

#### **SendGrid** (Gratuito hasta 100 emails/día)

1. **Registrarse en:** https://sendgrid.com/
2. **Obtener API Key**
3. **Usar en `email_service.php`:**

```php
function enviarConSendGrid($email, $nombres, $codigo) {
    $apiKey = 'SG.xxxxxxxxxxxxxxxxxxxxxxxx';
    
    $data = [
        'personalizations' => [[
            'to' => [['email' => $email, 'name' => $nombres]],
            'subject' => 'Código de Verificación - AdoptMe'
        ]],
        'from' => ['email' => 'noreply@adoptme.com', 'name' => 'AdoptMe'],
        'content' => [[
            'type' => 'text/html',
            'value' => generarHTMLVerificacion($nombres, $codigo)
        ]]
    ];
    
    $ch = curl_init('https://api.sendgrid.com/v3/mail/send');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $apiKey,
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $httpCode === 202;
}
```

---

## 🧪 Probar el Envío de Emails

### **Test 1: Verificar configuración**
```bash
php -r "echo mail('tu_email@gmail.com', 'Test', 'Funciona') ? 'OK' : 'Error';"
```

### **Test 2: Registrar usuario y ver si llega el email**
```bash
# Postman o cURL
POST http://localhost/adopciones_api/register.php
{
  "nombres": "Test",
  "apellidos": "Email",
  "dni": "11111111",
  "email": "tu_email_real@gmail.com",
  "telefono": "999999999",
  "password": "123456"
}

# Revisar tu bandeja de entrada
```

---

## 📊 Respuesta de la API

### **Si el email se envía correctamente:**
```json
{
  "success": true,
  "user_id": "1",
  "nombres": "Juan",
  "apellidos": "Perez",
  "email": "juan@example.com",
  "message": "Usuario registrado exitosamente. Revisa tu correo...",
  "email_enviado": true
}
```

### **Si el email falla (desarrollo):**
```json
{
  "success": true,
  "user_id": "1",
  "nombres": "Juan",
  "apellidos": "Perez",
  "email": "juan@example.com",
  "message": "Usuario registrado exitosamente... (Email no configurado - código en respuesta para desarrollo)",
  "email_enviado": false,
  "verification_code": "123456"  // ← Solo en desarrollo
}
```

---

## ✅ Checklist de Producción

- [ ] Configurar credenciales SMTP reales
- [ ] Probar envío de emails
- [ ] Cambiar `EMAIL_FROM` a dominio real
- [ ] Eliminar `verification_code` de respuesta JSON
- [ ] Activar logging de errores de email
- [ ] Configurar rate limiting (evitar spam)
- [ ] Agregar template de email profesional con logo
- [ ] Implementar recuperación de contraseña
- [ ] Configurar SPF/DKIM para evitar spam

---

## 🔧 Troubleshooting

### **"mail() failed"**
- PHP no tiene configurado sendmail
- **Solución:** Usar PHPMailer o servicio externo

### **"SMTP connect() failed"**
- Credenciales incorrectas
- **Solución:** Verificar username/password, usar contraseña de aplicación

### **Email llega a spam**
- Falta configuración SPF/DKIM
- **Solución:** Usar servicio profesional (SendGrid, Mailgun)

### **Email no llega**
- Revisar logs de PHP: `error_log()`
- Verificar carpeta de spam
- Probar con otro email

---

## 📝 Próximos Pasos

1. ✅ **Servicio creado** - `email_service.php`
2. ✅ **Integrado en registro** - `register.php`
3. ✅ **Email de bienvenida** - `verify.php`
4. ⏳ **Recuperación de contraseña** - Por implementar
5. ⏳ **Notificaciones de adopción** - Por implementar

---

**Para desarrollo:** El sistema funciona sin configurar email, devolviendo el código en la respuesta JSON.

**Para producción:** Configura una de las opciones arriba y el email se enviará automáticamente.
