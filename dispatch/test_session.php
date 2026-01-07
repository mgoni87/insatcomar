<?php
session_start();

if (!isset($_SESSION['test_count'])) {
    $_SESSION['test_count'] = 0;
} else {
    $_SESSION['test_count']++;
}

echo "Conteo de sesiones: " . $_SESSION['test_count'] . "<br>";
echo "ID de sesión: " . session_id() . "<br>";
echo "Puede que necesites recargar la página para ver el conteo aumentar.";
echo "<br><a href='?'>Recargar</a>";
?>