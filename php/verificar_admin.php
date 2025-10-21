<?php
require_once 'conexion.php'; 
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit('Acceso no permitido');
}

$email_form = $_POST['email']; 
$contrasena_form = $_POST['password'];

// Buscar usuario admin
$sql = "SELECT id_empleado, nombre, contrasena FROM empleado WHERE email = ? AND rol = 'admin'";
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
        $_SESSION['admin_id'] = $fila['id_empleado'];
        $_SESSION['admin_usuario'] = $fila['nombre'];
        header("Location: ../html/admin/dashboard.html");
        exit();
    } else {
        // Contraseña incorrecta
        header("Location: ../html/admin/login-admin.html?error=2");
        exit();
    }
} else {
    // Correo no registrado
    header("Location: ../html/admin/login-admin.html?error=3");
    exit();
}
?>
