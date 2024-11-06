<?php
$access_token = "APP_USR-3919828455143393-110409-8ce1e434e1b4e9c3ca2c89dcf4267378-1956837393";  // Tu Access Token
$url = "https://api.mercadopago.com/checkout/preferences";

// Crea el contenido JSON
$data = array(
    "items" => array(
        array(
            "title" => "Baño Punta Verde",
            "quantity" => 1,
            "unit_price" => 10.00  // Cambia este precio según tu producto
        )
    ),
    "notification_url" => "https://saodesign.online/webhook.php"
);

$options = array(
    "http" => array(
        "header"  => "Content-type: application/json\r\n" .
                     "Authorization: Bearer $access_token\r\n",
        "method"  => "POST",
        "content" => json_encode($data),
    )
);

$context  = stream_context_create($options);
$response = file_get_contents($url, false, $context);

// Decodifica la respuesta y maneja errores
if ($response === FALSE) {
    die("Error en la solicitud.");
}

$result = json_decode($response);

// Verifica si init_point está disponible
if (isset($result->init_point)) {
    echo "Enlace de pago: " . $result->init_point;
} else {
    // Muestra el error completo para ayudar en la depuración
    echo "Error en la respuesta:<br>";
    echo "<pre>" . print_r($result, true) . "</pre>";
}
?>