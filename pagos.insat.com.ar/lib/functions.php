<?php declare(strict_types=1);

/**
 * helpers y lógica de negocio compartida
 * - SIN get_result (no mysqlnd)
 * - SIN dependencia obligatoria de mbstring (usa fallback)
 * - Consulta directa con mysqli->query (escapando input)
 */

function h(string $s): string { return htmlspecialchars($s, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'); }

function safe_lower(string $s): string {
  if (function_exists('mb_strtolower')) return mb_strtolower($s, 'UTF-8');
  return strtolower($s);
}

/** Convierte a UTF-8 desde Latin1/Windows si viniera roto */
function to_utf8(?string $s): string {
  if ($s === null) return '';
  $s = str_replace("\xC2\xA0", ' ', $s); // NBSP -> espacio
  $s = trim($s);
  if ($s === '') return '';
  if (@preg_match('//u', $s)) return $s; // ya es UTF-8 válido
  if (function_exists('mb_convert_encoding')) return mb_convert_encoding($s, 'UTF-8', 'ISO-8859-1, Windows-1252, UTF-8');
  if (function_exists('iconv')) {
    $x = @iconv('ISO-8859-1', 'UTF-8//IGNORE', $s);
    return $x !== false ? $x : $s;
  }
  return $s;
}

function parse_amount(string $s): float {
  $s = trim($s);
  $clean = preg_replace('/[^\d,\.]/', '', $s);
  if ($clean === null || $clean === '') return 0.0;
  if (preg_match('/,\d{1,2}$/', $clean)) { $clean = str_replace('.', '', $clean); $clean = str_replace(',', '.', $clean); }
  else { $clean = str_replace(',', '', $clean); }
  return (float)$clean;
}

function parse_date(?string $s): ?string {
  if ($s === null) return null;
  $s = trim(str_replace('/', '-', $s));
  if ($s === '' || $s === '0') return null;
  if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $s)) return $s;
  $p = explode('-', $s);
  if (count($p) === 3 && strlen($p[2]) === 4) return sprintf('%04d-%02d-%02d', (int)$p[2], (int)$p[1], (int)$p[0]);
  return null;
}

function classify_tipo(string $origen): string {
  $o = safe_lower(trim(to_utf8($origen)));
  $o = preg_replace('/^\d+\s*/', '', $o); // ej: "6 Facturación Mensual"
  if (strpos($o, 'facturacion mensual') !== false || strpos($o, 'facturación mensual') !== false) return 'recurrente';
  return 'puntual';
}

/**
 * Busca por código de cliente (tolerante a ceros a la izquierda) y saldo > 0
 * IMPLEMENTACIÓN SIMPLE: mysqli->query + fetch_assoc (sin mysqlnd)
 *
 * @return array{recurrente: array<int, array>, puntual: array<int, array>}|null
 */
function find_cliente(mysqli $conn, string $term): ?array {
  $term = trim($term);
  if ($term === '') return null;

  $esc = $conn->real_escape_string($term);
  // Ajustá 14 si tu estándar es otro
  $sql = "SELECT uuid, id_cliente, codigo_cliente, nombre, domicilio, nodo, id_factura,
                 fecha_emision, periodo, vencimiento_1, vencimiento_2, vencimiento_3,
                 comprobante, saldo_vencido, origen, tipo
          FROM pagos_pendientes
          WHERE LPAD(codigo_cliente,14,'0') = LPAD('{$esc}',14,'0')
            AND saldo_vencido > 0
          ORDER BY COALESCE(vencimiento_1, '9999-12-31') ASC, id_factura ASC";

  $res = $conn->query($sql);
  if (!$res) {
    error_log('find_cliente() query error: '.$conn->error);
    return null;
  }

  $out = ['recurrente'=>[], 'puntual'=>[]];
  while ($r = $res->fetch_assoc()) {
    foreach (['nombre','domicilio','nodo','periodo','origen'] as $k) { if (isset($r[$k])) $r[$k] = to_utf8($r[$k]); }
    $tipo = classify_tipo($r['origen'] ?? '');
    $r['tipo'] = $tipo;
    $out[$tipo][] = $r;
  }
  $res->free();
  return $out;
}
