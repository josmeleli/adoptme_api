<?php
// email_service.php - Servicio de envío de emails con PHPMailer

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/vendor/autoload.php';

/**
 * Configuración de email
 */
define('EMAIL_FROM', 'creacode21@gmail.com');
define('EMAIL_FROM_NAME', 'AdoptMe');
define('EMAIL_REPLY_TO', 'creacode21@gmail.com');

// Configuración SMTP para Gmail
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USERNAME', 'creacode21@gmail.com');
define('SMTP_PASSWORD', 'knnd ibdx eime usme');

/**
 * Enviar código de verificación por email
 * 
 * @param string $email Email del destinatario
 * @param string $nombres Nombre del usuario
 * @param string $codigo Código de verificación de 6 dígitos
 * @return bool True si se envió correctamente, false si falló
 */
function enviarCodigoVerificacion($email, $nombres, $codigo) {
    $asunto = 'Código de Verificación - AdoptMe 🐾';
    
    $mensaje_html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
            .code-box { background: white; border: 2px dashed #667eea; border-radius: 10px; padding: 20px; margin: 20px 0; text-align: center; }
            .code { font-size: 36px; font-weight: bold; color: #667eea; letter-spacing: 5px; }
            .footer { text-align: center; padding: 20px; color: #999; font-size: 12px; }
            .button { background: #667eea; color: white; padding: 12px 30px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🐾 AdoptMe</h1>
                <p>Plataforma de Adopción Responsable</p>
            </div>
            <div class='content'>
                <h2>¡Hola {$nombres}! 👋</h2>
                <p>Gracias por registrarte en AdoptMe. Para completar tu registro, necesitamos verificar tu correo electrónico.</p>
                
                <div class='code-box'>
                    <p style='margin: 0; color: #666;'>Tu código de verificación es:</p>
                    <div class='code'>{$codigo}</div>
                    <p style='margin: 10px 0 0 0; color: #999; font-size: 14px;'>Este código expirará en 15 minutos</p>
                </div>
                
                <p><strong>¿Qué sigue?</strong></p>
                <ol>
                    <li>Ingresa este código en la app AdoptMe</li>
                    <li>Completa tu perfil de adoptante</li>
                    <li>Comienza a buscar tu compañero perfecto 🐶🐱</li>
                </ol>
                
                <p style='color: #999; font-size: 14px; border-left: 3px solid #ffc107; padding-left: 15px; margin-top: 20px;'>
                    <strong>Nota:</strong> Si no solicitaste este código, puedes ignorar este mensaje de forma segura.
                </p>
            </div>
            <div class='footer'>
                <p>© 2025 AdoptMe - Adopción Responsable de Mascotas</p>
                <p>Este es un correo automático, por favor no respondas a este mensaje.</p>
                <p>¿Necesitas ayuda? Contacta a: " . EMAIL_REPLY_TO . "</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mensaje_texto = "
    ¡Hola {$nombres}!
    
    Gracias por registrarte en AdoptMe.
    
    Tu código de verificación es: {$codigo}
    
    Este código expirará en 15 minutos.
    
    Si no solicitaste este código, ignora este mensaje.
    
    Saludos,
    El equipo de AdoptMe
    ";
    
    // Intentar enviar con función mail() de PHP
    return enviarEmail($email, $nombres, $asunto, $mensaje_html, $mensaje_texto);
}

/**
 * Función genérica para enviar emails
 * 
 * @param string $to Email del destinatario
 * @param string $nombre Nombre del destinatario
 * @param string $asunto Asunto del email
 * @param string $mensaje_html Contenido HTML
 * @param string $mensaje_texto Contenido texto plano (fallback)
 * @return bool
 */
function enviarEmail($to, $nombre, $asunto, $mensaje_html, $mensaje_texto) {
    // Boundary para separar contenido HTML y texto
    $boundary = md5(uniqid(time()));
    
    // Headers
    // Enviar email usando PHPMailer
    try {
        $mail = new PHPMailer(true);
        
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        // Remitente y destinatario
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($to, $nombre);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje_html;
        $mail->AltBody = $mensaje_texto;
        
        // Enviar
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar email: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Enviar email de bienvenida después de verificación
 */
function enviarEmailBienvenida($email, $nombres) {
    $asunto = '¡Bienvenido a AdoptMe! 🎉';
    
    $mensaje_html = "
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <style>
            body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
            .container { max-width: 600px; margin: 0 auto; padding: 20px; }
            .header { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
            .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
        </style>
    </head>
    <body>
        <div class='container'>
            <div class='header'>
                <h1>🎉 ¡Cuenta Verificada!</h1>
            </div>
            <div class='content'>
                <h2>¡Hola {$nombres}!</h2>
                <p>Tu cuenta ha sido verificada exitosamente. Ahora puedes disfrutar de todas las funcionalidades de AdoptMe:</p>
                <ul>
                    <li>🔍 Buscar mascotas disponibles</li>
                    <li>❤️ Guardar tus favoritas</li>
                    <li>📝 Solicitar adopciones</li>
                    <li>💬 Chatear con refugios</li>
                    <li>📊 Seguimiento de tus solicitudes</li>
                </ul>
                <p>¡Comienza tu búsqueda ahora y encuentra a tu compañero perfecto!</p>
            </div>
        </div>
    </body>
    </html>
    ";
    
    $mensaje_texto = "¡Hola {$nombres}! Tu cuenta ha sido verificada. Ahora puedes usar AdoptMe.";
    
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($email, $nombres);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje_html;
        $mail->AltBody = $mensaje_texto;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar email de bienvenida: " . $mail->ErrorInfo);
        return false;
    }
}

/**
 * Enviar email de recuperación de contraseña
 */
function enviarEmailRecuperacion($email, $nombres, $codigo) {
    $asunto = 'Recuperación de Contraseña - AdoptMe';
    
    $mensaje_html = "
    <!DOCTYPE html>
    <html>
    <head><meta charset='UTF-8'></head>
    <body style='font-family: Arial, sans-serif;'>
        <div style='max-width: 600px; margin: 0 auto; padding: 20px;'>
            <h2>Recuperación de Contraseña</h2>
            <p>Hola {$nombres},</p>
            <p>Recibimos una solicitud para recuperar tu contraseña.</p>
            <div style='background: #f0f0f0; padding: 20px; text-align: center; margin: 20px 0;'>
                <p>Tu código de recuperación es:</p>
                <h1 style='color: #667eea; font-size: 36px;'>{$codigo}</h1>
                <p style='color: #666;'>Este código expirará en 15 minutos</p>
            </div>
            <p>Si no solicitaste este cambio, ignora este mensaje.</p>
        </div>
    </body>
    </html>
    ";
    
    $mensaje_texto = "Hola {$nombres}, tu código de recuperación es: {$codigo}. Expira en 15 minutos.";
    
    try {
        $mail = new PHPMailer(true);
        
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = SMTP_PORT;
        $mail->CharSet = 'UTF-8';
        
        $mail->setFrom(EMAIL_FROM, EMAIL_FROM_NAME);
        $mail->addAddress($email, $nombres);
        $mail->addReplyTo(EMAIL_REPLY_TO, EMAIL_FROM_NAME);
        
        $mail->isHTML(true);
        $mail->Subject = $asunto;
        $mail->Body = $mensaje_html;
        $mail->AltBody = $mensaje_texto;
        
        $mail->send();
        return true;
        
    } catch (Exception $e) {
        error_log("Error al enviar email de recuperación: " . $mail->ErrorInfo);
        return false;
    }
}

?>
