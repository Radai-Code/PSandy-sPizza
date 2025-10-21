<?php
include 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitizar datos
    $nombre = trim($_POST["nombre"]);
    $apellidos = trim($_POST["apellidos"]);
    $telefono = trim($_POST["telefono"]);
    $calle = trim($_POST["calle"]);
    $colonia = trim($_POST["colonia"]);
    $codigo_postal = trim($_POST["codigo_postal"]);
    $email = trim($_POST["email"]);
    $contrasena = $_POST["contrasena"];
    $confirmar_contrasena = $_POST["confirmar_contrasena"];

    // Validaciones
    if (empty($nombre) || empty($apellidos) || empty($telefono) || empty($calle) || 
        empty($colonia) || empty($codigo_postal) || empty($email) || empty($contrasena) || empty($confirmar_contrasena)) {
        header("Location: ../html/registro_cliente.html?error=campos_vacios");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../html/registro_cliente.html?error=email_invalido");
        exit();
    }

    if (!preg_match('/^\d{10}$/', $telefono)) {
        header("Location: ../html/registro_cliente.html?error=telefono_invalido");
        exit();
    }

    if ($contrasena !== $confirmar_contrasena) {
        header("Location: ../html/registro_cliente.html?error=contrasenas_no_coinciden");
        exit();
    }

    // Verificar si el correo ya existe
    $query_check = "SELECT * FROM cliente WHERE email = ?";
    $stmt_check = $conn->prepare($query_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result = $stmt_check->get_result();

    if ($result->num_rows > 0) {
        header("Location: ../html/registro_cliente.html?error=correo_existente");
        exit();
    }

    // Hashear la contraseña antes de guardarla
    $hash_contrasena = password_hash($contrasena, PASSWORD_DEFAULT);

    // Insertar el cliente en la base de datos
    $query = "INSERT INTO cliente (nombre, apellidos, telefono, calle, colonia, codigo_postal, email, contrasena)
              VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ssssssss", $nombre, $apellidos, $telefono, $calle, $colonia, $codigo_postal, $email, $hash_contrasena);

    if ($stmt->execute()) {
        header("Location: ../html/login_cliente.html?registro=exitoso");
        exit();
    } else {
        header("Location: ../html/registro_cliente.html?error=bd");
        exit();
    }
}
?>
