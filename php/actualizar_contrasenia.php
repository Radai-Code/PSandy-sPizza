<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $nueva_contrasena = $_POST["nueva_contrasena"];
    $confirmar_contrasena = $_POST["confirmar_contrasena"];
    $user_type = $_POST["user_type"];

    if (empty($email) || empty($nueva_contrasena) || empty($confirmar_contrasena)) {
        header("Location: ../html/Recuperar_Contrasenia.php?error=campos_vacios&type=$user_type");
        exit();
    }

    if ($nueva_contrasena !== $confirmar_contrasena) {
        header("Location: nueva_contrasenia.php?email=$email&type=$user_type&error=no_coinciden");
        exit();
    }

    $hash_contrasena = password_hash($nueva_contrasena, PASSWORD_DEFAULT);

    // LÓGICA DE ACTUALIZACIÓN SEGÚN TABLA
    if ($user_type === 'admin' || $user_type === 'empleado') {
        // Actualizamos en tabla empleado si el correo coincide con email O gmail
        $stmt = $conexion->prepare("UPDATE empleado SET contrasena = ? WHERE email = ? OR gmail = ?");
        $stmt->bind_param("sss", $hash_contrasena, $email, $email);
        $redirect_url = "../html/admin/login-admin.html?restablecido=1";
    } else {
        // Actualizamos en tabla cliente
        $stmt = $conexion->prepare("UPDATE cliente SET contrasena = ? WHERE email = ?");
        $stmt->bind_param("ss", $hash_contrasena, $email);
        $redirect_url = "../html/client-login.html?restablecido=1";
    }

    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        header("Location: " . $redirect_url);
        exit();
    } else {
        // Puede fallar si la contraseña nueva es idéntica a la anterior (MySQL no actualiza)
        // O si el correo realmente no existe (raro si ya pasó la verificación)
        header("Location: ../html/Recuperar_Contrasenia.php?error=no_encontrado&type=$user_type");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>