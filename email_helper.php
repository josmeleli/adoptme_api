<?php
// email_helper.php - Helper para enviar emails

/**
 * Enviar código de verificación por email
 * 
 * OPCIÓN 1: PHPMailer (Recomendado)
 * OPCIÓN 2: mail() de PHP (básico)
 * OPCIÓN 3: API externa (SendGrid, Mailgun, etc.)
 */

// OPCIÓN 1: Usando PHPMailer (RECOMENDADO)
// Instalación: composer require phpmailer/phpmailer

function enviarCodigoVerificacion($email, $nombres, $codigo) {
    // EJEMPLO CON PHPMAILER
    /*
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    require 'vendor/autoload.php';
    
    $mail = new PHPMailer(true);
    
    try {
        // Configuración del servidor SMTP
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';  // Servidor SMTP de Gmail
        $mail->SMTPAuth   = true;
        $mail->Username   = 'tu_email@gmail.com';  // Tu email
        $mail->Password   = 'tu_contraseña_app';   // Contraseña de aplicación de Gmail
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;
        
        // Configurar charset para caracteres especiales
        $mail->CharSet = 'UTF-8';
        
        // Remitente
        $mail->setFrom('noreply@adoptme.com', 'AdoptMe');
        
        // Destinatario
        $mail->addAddress($email, $nombres);
        
        // Contenido del email
        $mail->isHTML(true);
        $mail->Subject = 'Código de Verificación - AdoptMe';
        $mail->Body    = "
            <h2>Bienvenido a AdoptMe, {$nombres}!</h2>
            <p>Tu código de verificación es:</p>
            <h1 style='color: #4CAF50; font-size: 36px;'>{$codigo}</h1>
            <p>Este código expirará en 15 minutos.</p>
            <p>Si no solicitaste este código, ignora este mensaje.</p>
            <br>
            <p>Saludos,<br>El equipo de AdoptMe</p>
        ";
        $mail->AltBody = "Tu código de verificación es: {$codigo}. Expira en 15 minutos.";
        
        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Error al enviar email: {$mail->ErrorInfo}");
        return false;
    }
    */
    
    return false; // Por ahora retorna false
}

// OPCIÓN 2: Función básica con mail() de PHP
function enviarEmailBasico($email, $nombres, $codigo) {
    $asunto = 'Código de Verificación - AdoptMe';
    $mensaje = "
        Hola {$nombres},
        
        Tu código de verificación es: {$codigo}
        
        Este código expirará en 15 minutos.
        
        Saludos,
        El equipo de AdoptMe
    ";
    
    $headers = [
        'From: noreply@adoptme.com',
        'Reply-To: soporte@adoptme.com',
        'X-Mailer: PHP/' . phpversion(),
        'Content-Type: text/plain; charset=UTF-8'
    ];
    
    return mail($email, $asunto, $mensaje, implode("\r\n", $headers));
}

// OPCIÓN 3: Usando API de SendGrid
function enviarConSendGrid($email, $nombres, $codigo) {
    /*
    $apiKey = 'TU_API_KEY_DE_SENDGRID';
    
    $data = [
        'personalizations' => [[
            'to' => [['email' => $email, 'name' => $nombres]],
            'subject' => 'Código de Verificación - AdoptMe'
        ]],
        'from' => ['email' => 'noreply@adoptme.com', 'name' => 'AdoptMe'],
        'content' => [[
            'type' => 'text/html',
            'value' => "<h2>Hola {$nombres},</h2><p>Tu código es: <strong>{$codigo}</strong></p>"
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
    */
    
    return false;
}

?>
