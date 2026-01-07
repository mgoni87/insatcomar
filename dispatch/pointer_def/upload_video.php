<?php
// upload_video.php
// Guarda el archivo recibido (MP4 o WebM) y los metadatos en /uploads.
// Devuelve JSON con el nombre del archivo guardado.

header('Content-Type: application/json; charset=utf-8');

function fail($code, $msg) {
  http_response_code($code);
  echo json_encode(['ok'=>false,'error'=>$msg], JSON_UNESCAPED_UNICODE);
  exit;
}

try {
  if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    fail(405, 'Method not allowed');
  }
  if (empty($_FILES['video']['tmp_name'])) {
    fail(400, 'No video');
  }

  // Ajustá estos límites en php.ini/.htaccess si hace falta.
  if ($_FILES['video']['size'] > 128 * 1024 * 1024) {
    fail(413, 'Too large');
  }

  $dir = __DIR__ . '/uploads';
  if (!is_dir($dir)) { mkdir($dir, 0755, true); }

  // Tomamos la extensión del nombre original.
  $originalName = $_FILES['video']['name'] ?? 'video.bin';
  $ext = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
  if (!in_array($ext, ['mp4','webm'])) { $ext = 'mp4'; } // fallback seguro

  $base = 'apuntamiento_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4));
  $dst  = $dir . '/' . $base . '.' . $ext;

  if (!move_uploaded_file($_FILES['video']['tmp_name'], $dst)) {
    fail(500, 'Save failed');
  }

  // Guardar metadatos (opcional)
  $meta = $_POST['meta'] ?? '';
  if ($meta) {
    file_put_contents($dst . '.json', $meta);
  }

  echo json_encode([
    'ok'   => true,
    'file' => basename($dst),
    'size' => filesize($dst),
    'mime' => mime_content_type($dst)
  ], JSON_UNESCAPED_UNICODE);

} catch (Throwable $e) {
  fail(500, $e->getMessage());
}
