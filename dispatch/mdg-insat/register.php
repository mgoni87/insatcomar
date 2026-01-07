<?php
require 'db.php';

$mensaje = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = trim($_POST['username']);
    $pass = $_POST['password'];

    if (!empty($user) && !empty($pass)) {
        // Encriptar la contraseña
        $password_segura = password_hash($pass, PASSWORD_DEFAULT);

        try {
            // Preparar la consulta para evitar SQL Injection
            $stmt = $conexion->prepare("INSERT INTO mdg_users (username, password_hash) VALUES (?, ?)");
            $stmt->bind_param("ss", $user, $password_segura);
            
            if ($stmt->execute()) {
                $mensaje = "Usuario registrado con éxito. <a href='login.php'>Ir al Login</a>";
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Código de error para entrada duplicada
                $mensaje = "Error: El nombre de usuario ya existe.";
            } else {
                $mensaje = "Error al registrar: " . $e->getMessage();
            }
        }
    } else {
        $mensaje = "Por favor, completa todos los campos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuarios</title>
</head>
<body>
    <h2>Crear Nuevo Usuario</h2>
    
    <?php if (!empty($mensaje)) echo "<p>$mensaje</p>"; ?>

    <form action="register.php" method="POST">
        <div>
            <label>Nombre de Usuario:</label>
            <input type="text" name="username" required>
        </div>
        <div>
            <label>Contraseña:</label>
            <input type="password" name="password" required>
        </div>
        <button type="submit">Registrar Usuario</button>
    </form>
    <br>
    <a href="login.php">Volver al Login</a>
</body>
</html>