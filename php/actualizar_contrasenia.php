<?php
require_once 'conexion.php'; // Incluir la conexión

// Habilitar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Verificar que se recibieron todos los datos necesarios
if (!isset($_POST['email'], $_POST['nueva_contrasena'], $_POST['confirmar_contrasena'], $_POST['user_type'])) {
    // Si faltan datos, detener y mostrar el mensaje de error
    die("Error: Datos incompletos."); 
}

// Obtener los datos del formulario
$email = $_POST['email'];
$nueva_contrasena = $_POST['nueva_contrasena'];
$confirmar_contrasena = $_POST['confirmar_contrasena'];
$user_type = $_POST['user_type'];

// Verificar que las contraseñas coincidan
if ($nueva_contrasena !== $confirmar_contrasena) {
    // Si no coinciden, redirigir de vuelta a nueva_contrasenia.php con un error
    header("Location: nueva_contrasenia.php?email=" . urlencode($email) . "&type=" . $user_type . "&error=no_coincide");
    exit();
}

// Determinar la tabla a actualizar
$table = ($user_type === 'admin') ? 'Empleado' : 'Cliente';

// Preparar la consulta SQL para actualizar la contraseña (texto plano)
$sql = "UPDATE $table SET contrasena = ? WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

// Vincular los parámetros
mysqli_stmt_bind_param($stmt, "ss", $nueva_contrasena, $email);

// Ejecutar la actualización
if (mysqli_stmt_execute($stmt)) {
    // Éxito: Redirigir a la página de login correspondiente
    $redirect_url = ($user_type === 'admin') ? '../html/admin/login-admin.html' : '../html/client-login.html';
    header("Location: " . $redirect_url . "?reset=exitoso");
    exit();
} else {
    // Falla al actualizar
    echo "Error al actualizar la contraseña: " . mysqli_error($conexion);
}

// Cerrar la conexión
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>