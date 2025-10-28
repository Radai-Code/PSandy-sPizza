<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $user_type = $_POST["user_type"];

    if (empty($email)) {
        header("Location: /PSandy-sPizza/html/recuperarContrasenia.php?error=correo_vacio&type=$user_type");
        exit();
    }

    // Determinar tabla según el tipo de usuario
    $tabla = ($user_type === 'admin') ? 'empleado' : 'cliente';

    $stmt = $conexion->prepare("SELECT email FROM $tabla WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        // Si existe, redirigimos a la página de nueva contraseña
        header("Location: /PSandy-sPizza/html/nuevaContrasenia.php?email=$email&type=$user_type");
        exit();
    } else {
        // Correo no encontrado
        header("Location: /PSandy-sPizza/html/recuperarContrasenia.php?error=correo_no_registrado&type=$user_type");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>
