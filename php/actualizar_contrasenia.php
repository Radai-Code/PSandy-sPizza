<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $nueva_contrasena = $_POST["nueva_contrasena"];
    $confirmar_contrasena = $_POST["confirmar_contrasena"];
    $user_type = $_POST["user_type"];

    // Validar campos vacíos
    if (empty($email) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        header("Location: /PSandy-sPizza/html/recuperarContrasenia.php?error=campos_vacios&type=$user_type");
        exit();
    }

    // Validar coincidencia de contraseñas
    if ($nueva_contrasena !== $confirmar_contrasena) {
        header("Location: /PSandy-sPizza/php/nuevaContrasenia.php?email=$email&type=$user_type&error=no_coinciden");
        exit();
    }

    // Hashear la nueva contraseña
    $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    // Determinar la tabla
    $tabla = ($user_type === 'admin') ? 'empleado' : 'cliente';

    $stmt = $conexion->prepare("UPDATE $tabla SET contrasena = ? WHERE email = ?");
    $stmt->bind_param("ss", $hash_contrasena, $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // ✅ Redirección según el tipo de usuario
        if ($user_type === 'admin') {
            header("Location: /PSandy-sPizza/html/admin/login-admin.html?restablecido=1&type=admin");
        } else {
            header("Location: /PSandy-sPizza/html/client-login.html?restablecido=1&type=client");
        }
        exit();
    } else {
        header("Location: /PSandy-sPizza/html/recuperarContrasenia.php?error=no_encontrado&type=$user_type");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>
