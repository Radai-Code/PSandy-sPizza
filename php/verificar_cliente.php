<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $user_type = $_POST["user_type"]; // 'client' o 'admin'

    if (empty($email)) {
        header("Location: ../html/recuperarContrasenia.php?error=correo_vacio&type=$user_type");
        exit();
    }

    // Seleccionamos la tabla según el tipo de usuario
    $tabla = ($user_type === 'admin') ? 'admin' : 'cliente';

    // Verificar si el correo existe
    $stmt = $conexion->prepare("SELECT * FROM $tabla WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Si existe, redirige a la página para actualizar contraseña
        header("Location: ../html/actualizarContrasenia.php?email=$email&type=$user_type");
        exit();
    } else {
        // Si no existe, regresa con error
        header("Location: ../html/recuperarContrasenia.php?error=correo_no_registrado&type=$user_type");
        exit();
    }
}
?>
