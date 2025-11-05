<?php
require_once 'conexion.php'; // Es mejor usar require_once
session_start(); // ¡PASO 1: Iniciar la sesión!

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"]; // Contraseña enviada desde el formulario

    // Validar campo vacío
    if (empty($email) || empty($password)) {
        header("Location: ../html/client-login.html?error=2"); // Ruta relativa
        exit();
    }

    // Seleccionamos la tabla de clientes (asegúrate de que el nombre 'Cliente' sea exacto)
    $tabla = 'Cliente';

    // ¡PASO 2: Pedir id_cliente y nombre en la consulta!
    $stmt = $conexion->prepare("SELECT id_cliente, nombre, contrasena FROM $tabla WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        // Verificar contraseña
        if (password_verify($password, $fila['contrasena'])) {
            
            // ¡PASO 3: GUARDAR LOS DATOS EN LA SESIÓN!
            // Estas son las líneas que faltaban
            $_SESSION['user_id'] = $fila['id_cliente'];
            $_SESSION['user_nombre'] = $fila['nombre'];
            
            // Login exitoso, ahora sí redirige
            header("Location: ../html/menu.php"); // Ruta relativa
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: ../html/client-login.html?error=2"); // Ruta relativa
            exit();
        }
    } else {
        // Correo no registrado
        header("Location: ../html/client-login.html?error=3"); // Ruta relativa
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>