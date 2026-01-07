<?php
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

$json_data = file_get_contents('php://input');
$data = json_decode($json_data, true);

if (!$data || !isset($data['dni_asunto'])) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Datos insuficientes"]);
    exit;
}

$to = "hcrespo06@gmail.com";
$asunto = "Welcoming " . $data['dni_asunto'] . " " . $data['nombre_asunto'];
$from_email = "noreply@insat.com.ar";

// --- DISEÑO HTML DEL MAIL ---
$cuerpo = "
<html>
<head>
    <style>
        body { font-family: Arial, sans-serif; color: #333; line-height: 1.6; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; border: 1px solid #eee; border-radius: 10px; overflow: hidden; }
        .header { background-color: #0095f6; color: white; padding: 20px; text-align: center; }
        .content { padding: 20px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #eee; }
        th { background-color: #f8f8f8; color: #888; font-size: 12px; text-transform: uppercase; width: 30%; }
        .footer { background-color: #f4f4f4; color: #888; padding: 10px; text-align: center; font-size: 12px; }
    </style>
</head>
<body>
    <div class='container'>
        <div class='header'>
            <h2>Resumen de Verificación</h2>
        </div>
        <div class='content'>
            <p>Se ha recibido una nueva validación de datos del cliente <strong>" . htmlspecialchars($data['nombre_asunto']) . "</strong> (DNI: " . htmlspecialchars($data['dni_asunto']) . ").</p>
            <table>
                <tr>
                    <th>Validación de datos</th>
                    <td>" . ($data['personales'] ?? 'Sin cambios') . "</td>
                </tr>
                <tr>
                    <th>Forma de pago</th>
                    <td>" . ($data['pago'] ?? 'Sin cambios') . "</td>
                </tr>
                <tr>
                    <th>Domicilio</th>
                    <td>" . ($data['domicilio'] ?? 'Sin cambios') . "</td>
                </tr>
                <tr>
                    <th>Instalación antena</th>
                    <td>" . ($data['instalacion'] ?? 'No especificado') . "</td>
                </tr>
                <tr>
                    <th>Contratación</th>
                    <td>" . ($data['contratacion'] ?? 'No especificado') . "</td>
                </tr>
                <tr>
                    <th>Uso del servicio</th>
                    <td>" . ($data['plan'] ?? 'No especificado') . "</td>
                </tr>
            </table>
        </div>
        <div class='footer'>
            Este es un mensaje automático enviado por el sistema de Welcoming de INSAT.
        </div>
    </div>
</body>
</html>
";

// --- HEADERS PARA HTML ---
$headers = "From: Sistema Insat <" . $from_email . ">\r\n";
$headers .= "Reply-To: " . $from_email . "\r\n";
$headers .= "MIME-Version: 1.0\r\n";
$headers .= "Content-Type: text/html; charset=UTF-8\r\n"; // CLAVE: Cambiado de text/plain a text/html
$headers .= "X-Mailer: PHP/" . phpversion();

if (mail($to, $asunto, $cuerpo, $headers, "-f" . $from_email)) {
    echo json_encode(["status" => "success", "message" => "Mail enviado correctamente"]);
} else {
    http_response_code(500);
    echo json_encode(["status" => "error", "message" => "Error al enviar"]);
}
?>