<?php
require_once 'conexion.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] != "POST") {
    exit("Acceso no permitido");
}

// =================== RECIBIR DATOS ===================
$nombre = trim($_POST['nombre']);
$apellidos = trim($_POST['apellidos']);
$telefono = trim($_POST['telefono']);
$calle = trim($_POST['calle']);
$colonia = trim($_POST['colonia']);
$codigo_postal = trim($_POST['codigo_postal']);
$email = trim($_POST['email']);
$contrasena = $_POST['contrasena'];
$confirm_contrasena = $_POST['confirm_contrasena'];

// =================== VALIDACIONES ===================

// Campos vacíos
if (empty($nombre) || empty($apellidos) || empty($telefono) || empty($calle) || empty($colonia) || empty($codigo_postal) || empty($email) || empty($contrasena) || empty($confirm_contrasena)) {
    header("Location: ../html/registro.html?error=1");
    exit();
}

// Teléfono 10 dígitos
if (!preg_match("/^\d{10}$/", $telefono)) {
    header("Location: ../html/registro.html?error=2");
    exit();
}

// Contraseñas iguales
if ($contrasena !== $confirm_contrasena) {
    header("Location: ../html/registro.html?error=3");
    exit();
}

// Validar email único
$stmt = mysqli_prepare($conexion, "SELECT id_cliente FROM cliente WHERE email = ?");
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
mysqli_stmt_store_result($stmt);
if (mysqli_stmt_num_rows($stmt) > 0) {
    header("Location: ../html/registro.html?error=4");
    exit();
}

// =================== GUARDAR CLIENTE ===================

// Hashear contraseña
$hash_password = password_hash($contrasena, PASSWORD_DEFAULT);

// Insertar en la base de datos
$stmt = mysqli_prepare($conexion, "INSERT INTO cliente (nombre, apellidos, telefono, calle, colonia, codigo_postal, email, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
mysqli_stmt_bind_param($stmt, "ssssssss", $nombre, $apellidos, $telefono, $calle, $colonia, $codigo_postal, $email, $hash_password);

if (mysqli_stmt_execute($stmt)) {
    header("Location: ../html/client-login.html?success=1");
    exit();
} else {
    header("Location: ../html/registro.html?error=5");
    exit();
}
?>
