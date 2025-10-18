<?php
require_once 'conexion.php'; 
session_start();

// Validar que los datos fueron enviados
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit('Acceso no permitido');
}

$email_form = $_POST['email']; 
$contrasena_form = $_POST['password'];

// Buscar en la tabla 'Cliente' por el email proporcionado
$sql = "SELECT id_cliente, nombre, contrasena FROM Cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // ===== CAMBIO CRÍTICO AQUÍ =====
    // Comparamos directamente la contraseña del formulario con la de la base de datos (texto plano vs. texto plano).
    if ($contrasena_form === $fila['contrasena']) {
        
        // ¡Login exitoso!
        $_SESSION['user_id'] = $fila['id_cliente'];
        $_SESSION['user_nombre'] = $fila['nombre'];
        
        // Redirigir a la página del menú
        header("Location: ../html/menu.php");
        exit();
    }
}

// Si el usuario no existe o la contraseña es incorrecta, redirige con error.
header("Location: ../html/client-login.html?error=1");
exit();
?>