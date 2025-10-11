<?php
require_once 'conexion.php'; 
session_start();

$email_form = $_POST['email']; 
$contrasena_form = $_POST['password'];

// Consulta para buscar al cliente
$sql = "SELECT id_cliente, nombre, contrasena FROM Cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // ===== CAMBIO IMPORTANTE AQUÍ =====
    // Ahora comparamos el texto directamente, sin encriptación.
    if ($contrasena_form === $fila['contrasena']) {
        
        // ¡Login exitoso!
        $_SESSION['user_id'] = $fila['id_cliente'];
        $_SESSION['user_nombre'] = $fila['nombre'];
        
        header("Location: ../html/menu.php");
        exit();
    }
}

// Si algo falla, redirige con error.
header("Location: ../html/client-login.html?error=1");
exit();
?>