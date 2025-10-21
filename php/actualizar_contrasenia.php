<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $nueva_contrasena = $_POST["nueva_contrasena"];
    $confirmar_contrasena = $_POST["confirmar_contrasena"];
    $user_type = $_POST["user_type"]; // 'client' o 'admin'

    // Validar campos vacíos
    if (empty($email) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        header("Location: ../html/Recuperar_Contrasenia.php?error=campos_vacios&type=$user_type");
        exit();
    }

    // Validar coincidencia de contraseñas
    if ($nueva_contrasena !== $confirmar_contrasena) {
        header("Location: ../html/Recuperar_Contrasenia.php?error=no_coinciden&type=$user_type");
        exit();
    }

    // Hashear la nueva contraseña
    $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    // Determinar tabla según el tipo de usuario
    $tabla = ($user_type === 'admin') ? 'empleado' : 'cliente';

    // Preparar la consulta
    $query = "UPDATE $tabla SET contrasena = ? WHERE email = ?";
    $stmt = $conexion->prepare($query);

    if (!$stmt) {
        die("Error al preparar la consulta: " . $conexion->error);
    }

    $stmt->bind_param("ss", $hash_contrasena, $email);
    $stmt->execute();

    // Verificar si se actualizó la contraseña
    if ($stmt->affected_rows > 0) {
        header("Location: ../html/login.html?restablecido=1&type=$user_type");
        exit();
    } else {
        header("Location: ../html/Recuperar_Contrasenia.php?error=no_encontrado&type=$user_type");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>
