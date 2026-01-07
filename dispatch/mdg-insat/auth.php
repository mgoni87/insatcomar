<?php
session_start();
require 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['username'];
    $pass = $_POST['password'];

    // 1. Sentencia preparada para evitar Inyección SQL
    $stmt = $conexion->prepare("SELECT id, password_hash FROM mdg_users WHERE username = ?");
    $stmt->bind_param("s", $user);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($usuario = $resultado->fetch_assoc()) {
        // 2. Verificar la contraseña con el hash de la DB
        if (password_verify($pass, $usuario['password_hash'])) {
            // Login exitoso: crear sesión y regenerar ID por seguridad
            session_regenerate_id(true);
            $_SESSION['user_id'] = $usuario['id'];
            $_SESSION['username'] = $user;
            
            header("Location: dashboard.php");
            exit;
        }
    }
    
    // Error genérico para no dar pistas a atacantes
    die("Usuario o contraseña incorrectos. <a href='login.php'>Volver</a>");
}