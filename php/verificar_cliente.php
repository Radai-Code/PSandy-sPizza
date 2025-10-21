<?php
require_once 'conexion.php'; 
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit('Acceso no permitido');
}

$email_form = $_POST['email'];
$contrasena_form = $_POST['password'];

// Buscar cliente
$sql = "SELECT id_cliente, nombre, contrasena FROM cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // ✅ Comparar con hash
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        $_SESSION['user_id'] = $fila['id_cliente'];
        $_SESSION['user_nombre'] = $fila['nombre'];
        header("Location: ../html/menu.php");
        exit();
    } else {
        // Contraseña incorrecta
        header("Location: ../html/client-login.html?error=2");
        exit();
    }
} else {
    // Correo no registrado
    header("Location: ../html/client-login.html?error=3");
    exit();
}
?>
