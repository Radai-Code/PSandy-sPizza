<?php
// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'conexion.php'; 
session_start();

// Validar que los datos fueron enviados
if (!isset($_POST['email'], $_POST['password'])) {
    // Si alguien intenta acceder a este archivo directamente, lo redirigimos.
    header('Location: ../html/admin/login-admin.php');
    exit();
}

$email_form = $_POST['email']; 
$contrasena_form = $_POST['password'];

// La consulta busca en la tabla Empleado por la columna 'email'
$sql = "SELECT id_empleado, nombre, contrasena FROM Empleado WHERE email = ? AND rol = 'admin'";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

// LA LÓGICA CORREGIDA ESTÁ AQUÍ
if ($fila = mysqli_fetch_assoc($resultado)) {
    // Se encontró un usuario admin con ese email. Ahora, verificamos la contraseña.
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        // ----> CAMINO DEL ÉXITO <----
        // La contraseña coincide.
        $_SESSION['admin_id'] = $fila['id_empleado'];
        $_SESSION['admin_usuario'] = $fila['nombre'];
        
        // Redirigir al panel de administración
        header("Location: ../html/admin/dashboard.html");
        exit();
    } else {
        // ----> CAMINO DEL ERROR 1 <----
        // El usuario existe, pero la contraseña es incorrecta.
        header("Location: ../html/admin/login-admin.php?error=1");
        exit();
    }
} else {
    // ----> CAMINO DEL ERROR 2 <----
    // No se encontró ningún usuario admin con ese correo.
    header("Location: ../html/admin/login-admin.php?error=1");
    exit();
}

// Ya no es necesario cerrar la conexión aquí porque los 'exit()' detienen el script antes.
?>