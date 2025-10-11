<?php
// Incluir el archivo de conexión.
require_once 'conexion.php'; 
session_start();

// Validar que los datos fueron enviados
if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit('Acceso no permitido');
}

$email_form = $_POST['email']; 
$contrasena_form = $_POST['password'];

// 1. CORRECCIÓN: La consulta ahora busca en la tabla Empleado por la columna 'email'
$sql = "SELECT id_empleado, nombre, contrasena FROM empleado WHERE email = ? AND rol = 'admin'";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "s", $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    // 2. CORRECCIÓN: Usar password_verify() para comparar la contraseña de forma segura
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        
        // ¡Éxito!
        $_SESSION['admin_id'] = $fila['id_empleado'];
        $_SESSION['admin_usuario'] = $fila['nombre'];
        
        header("Location: ../html/admin/dashboard.html");
        exit();
    }
}

// Si el usuario no existe o la contraseña es incorrecta, redirige con error.
header("Location: ../html/admin/dashboard.html");
exit();
?>