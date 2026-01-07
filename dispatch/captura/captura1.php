<?php

echo "<h2>Iniciando script para obtener datos de MonitColasMini...</h2>";

// 1. URL y datos de la solicitud
$url = 'https://200.45.95.4/pbx/Phps/HTTPCon/MonitColasMini/calltr-mini.php';
$payload = 'uCT=hcrespo'; // Los datos que se envían en el POST

echo "<p><strong>URL de la solicitud:</strong> " . htmlspecialchars($url) . "</p>";
echo "<p><strong>Payload (datos POST):</strong> " . htmlspecialchars($payload) . "</p>";

// 2. Las cookies que obtuviste, formateadas para la cabecera 'Cookie'
$cookies_string = 'accoRaiz=8; AsterVoIP-q6fnxc22i1TjxJN=34; langes=6; menuClose--hcrespo--crmn=24; menuClose--hcrespo--usuan=25; themedefault=12';

echo "<p><strong>Cookies a enviar:</strong> " . htmlspecialchars($cookies_string) . "</p>";

// 3. Inicializar cURL
$ch = curl_init();
echo "<p>cURL inicializado.</p>";

// 4. Configurar opciones de cURL
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true); // Es una solicitud POST
curl_setopt($ch, CURLOPT_POSTFIELDS, $payload); // Los datos POST
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // Devolver la respuesta en lugar de imprimirla directamente
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/x-www-form-urlencoded', // Tipo de contenido para el payload
    'Cookie: ' . $cookies_string // Incluir las cookies
]);
// Opcional: Deshabilitar la verificación SSL/TLS si es una IP pública y el certificado no es válido
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

echo "<p>Opciones de cURL configuradas (POST, payload, cookies, cabeceras).</p>";
echo "<p>Verificación SSL/TLS de cURL deshabilitada (para IP pública, si el certificado no es válido).</p>";

// 5. Ejecutar la solicitud cURL
echo "<p>Intentando ejecutar la solicitud cURL...</p>";
$response = curl_exec($ch);

// 6. Manejo de errores de cURL
if (curl_errno($ch)) {
    echo "<p style='color: red;'><strong>Error cURL:</strong> " . curl_error($ch) . "</p>";
} else {
    echo "<h4>Resultados de la solicitud:</h4>";
    echo "<p>Solicitud cURL ejecutada con éxito.</p>";

    // Obtener el código de estado HTTP
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    echo "<p><strong>Código de estado HTTP:</strong> " . $http_code . "</p>";

    if ($http_code == 200) {
        echo "<p>Respuesta HTTP 200 OK.</p>";
        echo "<h4>Respuesta Bruta (XML recibido):</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>"; // Mostrar el XML original para depuración

        // --- Inicio de la lógica para inyectar en HTML ---

        // Load formato.html
        $html_template = file_get_contents('formato.html');
        if ($html_template === false) {
            die("<p style='color: red;'>Error: No se pudo cargar el archivo 'formato.html'. Asegúrate de que esté en el mismo directorio.</p>");
        }

        // Parse the XML response
        $xml = simplexml_load_string($response);
        if ($xml === false) {
            echo "<p style='color: red;'><strong>Error al parsear el XML:</strong></p>";
            foreach (libxml_get_errors() as $error) {
                echo "<p style='color: red;'> - " . htmlspecialchars($error->message) . "</p>";
            }
            libxml_clear_errors();
            echo "<h4>El script continuará con el HTML sin datos dinámicos inyectados debido al error de XML.</h4>";
            echo $html_template; // Print the original template
        } else {
            $queue_summary_data = '';
            // Get the first <Dato> from the first <Respuesta> block
            if (isset($xml->Respuesta[0]) && isset($xml->Respuesta[0]->Dato[0])) {
                $queue_summary_data = (string)$xml->Respuesta[0]->Dato[0];
            }

            // Prepare Queue Summary HTML for injection
            $queues_rows_html = '';
            $id_counter = 1; // For the new ID column
            if (!empty($queue_summary_data)) {
                $queues = explode(';', $queue_summary_data);
                foreach ($queues as $queue_str) {
                    $details = explode('|', $queue_str);
                    // Ensure enough columns and that the first part is not empty (sometimes a trailing semicolon creates empty entries)
                    if (count($details) >= 9 && !empty($details[0])) {
                        $queues_rows_html .= '<tr>';
                        $queues_rows_html .= '<td>' . $id_counter++ . '</td>'; // ID column
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[1]) . '</td>'; // Llamados en Cola (waiting calls)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[0]) . '</td>'; // Cola (Queue Name)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[4]) . '</td>'; // En Curso (calls in progress)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[6]) . '</td>'; // Espera Prom. (Average wait time)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[5]) . '</td>'; // Abandonos (abandoned calls)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[3]) . '</td>'; // Atendidas (answered calls)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[7]) . '</td>'; // Max. Espera (Max wait time)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[8]) . '</td>'; // Min. Espera (Min wait time - check data for this)
                        $queues_rows_html .= '<td>' . htmlspecialchars($details[8]) . '</td>'; // Total Agentes (agents in queue)
                        $queues_rows_html .= '</tr>';
                    }
                }
            }

            // Replace the tbody for queues
            $html_template = str_replace('<tbody id="queues_tb"></tbody>', '<tbody id="queues_tb">' . $queues_rows_html . '</tbody>', $html_template);

            echo "<hr>";
            echo "<h2>Contenido HTML final (para guardar en un archivo .html):</h2>";
            header('Content-Type: text/html');
            echo $html_template;
        }

    } else {
        echo "<p style='color: orange;'><strong>La solicitud cURL devolvió un código HTTP diferente a 200 OK:</strong> " . $http_code . "</p>";
        echo "<p>Esto podría indicar un problema de autenticación (cookies incorrectas/expiradas), redirección, o un error en el servidor remoto.</p>";
        echo "<h4>Respuesta Bruta recibida (si existe):</h4>";
        echo "<pre>" . htmlspecialchars($response) . "</pre>";
    }
}

// 8. Cerrar la sesión cURL
curl_close($ch);
echo "<p>cURL cerrado.</p>";
echo "<h2>Script finalizado.</h2>";

?>