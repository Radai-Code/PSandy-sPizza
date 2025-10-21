<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $password = $_POST["password"]; // Contraseña enviada desde el formulario
    $user_type = 'client'; // Como es login de cliente, forzamos 'client'

    // Validar campo vacío
    if (empty($email) || empty($password)) {
        header("Location: /PSandy-sPizza/html/client-login.html?error=2");
        exit();
    }

    // Seleccionamos la tabla de clientes
    $tabla = 'cliente';

    // Preparar consulta para verificar si el correo existe
    $stmt = $conexion->prepare("SELECT contrasena FROM $tabla WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $resultado = $stmt->get_result();

    if ($resultado->num_rows > 0) {
        $fila = $resultado->fetch_assoc();
        // Verificar contraseña
        if (password_verify($password, $fila['contrasena'])) {
            // Login exitoso, redirige al dashboard o interfaz del cliente
            header("Location: /PSandy-sPizza/html/menu.php");
            exit();
        } else {
            // Contraseña incorrecta
            header("Location: /PSandy-sPizza/html/client-login.html?error=2");
            exit();
        }
    } else {
        // Correo no registrado
        header("Location: /PSandy-sPizza/html/client-login.html?error=3");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>
