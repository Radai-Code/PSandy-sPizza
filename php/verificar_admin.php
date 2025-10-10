<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión.
// La ruta es '../' porque 'conexion.php' está un nivel arriba de la carpeta 'php'.
require_once '../conexion.php'; 

// Iniciar una sesión para el usuario.
session_start();

// Obtener los datos que el usuario envió desde el formulario
$usuario_form = $_POST['username'];
$contrasena_form = $_POST['password'];

// Preparar la consulta SQL
$sql = "SELECT id, usuario, contrasena FROM administradores WHERE usuario = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $usuario_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // Si se encontró un usuario, verificamos la contraseña
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        
        // ¡Contraseña correcta!
        $_SESSION['admin_id'] = $fila['id'];
        $_SESSION['admin_usuario'] = $fila['usuario'];
        
        // Redirigir al panel de administración
        header("Location: ../html/admin/dashboard.html");
        exit();

    } else {
        // Contraseña incorrecta, redirigir con error al archivo .php
        header("Location: ../html/admin/login-admin.php?error=1");
        exit();
    }
} else {
    // No se encontró el usuario, redirigir con error al archivo .php
    header("Location: ../html/admin/login-admin.php?error=1");
    exit();
}

// Cerrar las conexiones
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>