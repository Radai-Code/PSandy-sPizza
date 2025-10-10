<?php
// 1. Incluir el archivo de conexión.
// require_once se asegura de que el archivo se incluya una sola vez.
// ¡Esta línea hace todo el trabajo de conexión por nosotros!
require_once 'conexion.php';

// Iniciar una sesión para el usuario.
session_start();


// 2. Obtener los datos que el usuario envió desde el formulario
$usuario_form = $_POST['username'];
$contrasena_form = $_POST['password'];

// 3. Preparar la consulta SQL para buscar al administrador
$sql = "SELECT id, usuario, contrasena FROM administradores WHERE usuario = ?";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $usuario_form);

// 4. Ejecutar la consulta
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// 5. Verificar el resultado
if ($fila = mysqli_fetch_assoc($resultado)) {
    // Si se encontró un usuario, verificamos la contraseña
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        
        // ¡Contraseña correcta!
        $_SESSION['admin_id'] = $fila['id'];
        $_SESSION['admin_usuario'] = $fila['usuario'];
        
        // Redirigir al panel de administración
        header("Location: html/admin/dashboard.html");
        exit();

    } else {
        // Contraseña incorrecta
        echo "Error: Usuario o contraseña incorrectos.";
    }
} else {
    // No se encontró el usuario
    echo "Error: Usuario o contraseña incorrectos.";
}

// 6. Cerrar las conexiones
mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>