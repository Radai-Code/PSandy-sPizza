<?php
require_once 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST["email"]);
    $user_type = $_POST["user_type"]; // Recibe 'client', 'admin' o 'empleado'

    if (empty($email)) {
        header("Location: ../html/Recuperar_Contrasenia.php?error=correo_vacio&type=$user_type");
        exit();
    }

    // LÓGICA DE SELECCIÓN DE TABLA Y COLUMNAS
    // Si el tipo es 'admin' o 'empleado', buscamos en la tabla de empleados
    if ($user_type === 'admin' || $user_type === 'empleado') {
        // Buscamos en AMBAS columnas: email O gmail
        $stmt = $conexion->prepare("SELECT id_empleado FROM empleado WHERE email = ? OR gmail = ?");
        $stmt->bind_param("ss", $email, $email);
        
        // Forzamos el tipo 'admin' para la redirección, para mantener consistencia en la URL
        $redirect_type = 'admin'; 
    } else {
        // Si es cliente, solo buscamos en email
        $stmt = $conexion->prepare("SELECT id_cliente FROM cliente WHERE email = ?");
        $stmt->bind_param("s", $email);
        $redirect_type = 'client';
    }

    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // ¡ENCONTRADO! Redirige a cambiar contraseña
        header("Location: nueva_contrasenia.php?email=$email&type=$redirect_type");
        exit();
    } else {
        // NO ENCONTRADO
        header("Location: ../html/Recuperar_Contrasenia.php?error=correo_no_registrado&type=$user_type");
        exit();
    }

    $stmt->close();
    $conexion->close();
}
?>