<?php
require_once 'conexion.php'; 
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit('Acceso no permitido');
}

// Limpiamos los datos recibidos
$email_form = trim($_POST['email']); 
$contrasena_form = trim($_POST['password']);

// Consulta preparada
$sql = "SELECT id_empleado, nombre, contrasena, rol 
        FROM empleado 
        WHERE email = ? OR gmail = ?";

$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

mysqli_stmt_bind_param($stmt, "ss", $email_form, $email_form);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    
    
    // Verificamos la contraseña
    if (password_verify($contrasena_form, $fila['contrasena'])) {
        
        // Guardamos la sesión
        $_SESSION['empleado_id'] = $fila['id_empleado'];
        $_SESSION['empleado_nombre'] = $fila['nombre'];
        $_SESSION['empleado_rol'] = strtolower(trim($fila['rol'])); // minúsculas y sin espacios
        
        // Redirección por rol
        switch ($_SESSION['empleado_rol']) {
            case 'admin':
                header("Location: ../html/admin/dashboard.html");
                break;
            case 'empleado':
                header("Location: ../html/empleado-productos.html");
                break;
            default:
                // Rol desconocido
                header("Location: ../html/admin/login-admin.html?error=4");
                break;
        }
        exit();
        
    } else {
        // Contraseña incorrecta
        header("Location: ../html/admin/login-admin.html?error=2");
        exit();
    }
} else {
    // Usuario no encontrado
    header("Location: ../html/admin/login-admin.html?error=3");
    exit();
}
?>
