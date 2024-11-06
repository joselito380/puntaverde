<?php
// webhook.php

// Token de acceso para la API de Mercado Pago
$accessToken = 'APP_USR-3919828455143393-110409-8ce1e434e1b4e9c3ca2c89dcf4267378-1956837393';

// Función para manejar la notificación del webhook
function handleWebhook($data) {
    global $accessToken; // Accede a la variable global del token

    // Verifica si la notificación corresponde a un pago creado
    if (isset($data['action']) && $data['action'] === 'payment.created') {
        $paymentId = $data['data']['id'];

        // Espera 5 segundos antes de consultar el estado del pago
        sleep(5); // Retardo de 5 segundos

        // Consulta el estado del pago
        $paymentStatus = getPaymentStatus($paymentId);

        // Si el estado es aprobado, envía la señal para abrir la puerta
        if ($paymentStatus === 'approved') {
            openDoor();
        }
    }
}

// Función para obtener el estado del pago desde la API de Mercado Pago
function getPaymentStatus($paymentId) {
    global $accessToken;

    $url = "https://api.mercadopago.com/v1/payments/$paymentId";

    // Inicializa cURL
    $ch = curl_init($url);

    // Configura cURL
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "Authorization: Bearer $accessToken"
    ]);

    // Ejecuta la solicitud
    $response = curl_exec($ch);

    // Maneja errores de cURL
    if (curl_errno($ch)) {
        file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Error de cURL: " . curl_error($ch) . PHP_EOL, FILE_APPEND);
        curl_close($ch);
        return false;
    }

    // Cierra cURL
    curl_close($ch);

    // Procesa la respuesta
    $responseData = json_decode($response, true);

    // Retorna el estado del pago si está disponible
    return $responseData['status'] ?? false;
}

// Función para abrir la puerta
function openDoor() {
    // Aquí puedes agregar el comando o señal específica para abrir la puerta
    file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - ¡Pago aprobado! La puerta se ha abierto.\n", FILE_APPEND);
}

// Captura los datos de la notificación
$webhookData = json_decode(file_get_contents('php://input'), true);
handleWebhook($webhookData);

// Registro de la notificación en el archivo de log para depuración
file_put_contents('webhook_log.txt', date('Y-m-d H:i:s') . " - Notificación recibida: " . json_encode($webhookData) . PHP_EOL, FILE_APPEND);

// Responde con un código 200 a Mercado Pago para confirmar la recepción
http_response_code(200);
?>