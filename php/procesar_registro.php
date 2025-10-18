<?php
// 1. Incluir el archivo de conexión a la base de datos
require_once 'conexion.php';

// 2. Recibir todos los datos del formulario de registro
$nombre = $_POST['nombre'];
$apellidos = $_POST['apellidos'];
$telefono = $_POST['telefono'];
$calle = $_POST['calle'];
$colonia = $_POST['colonia'];
$cp = $_POST['codigo_postal'];
$email = $_POST['email'];
$contrasena = $_POST['contrasena']; // Se toma la contraseña tal cual la escribe el usuario

// 3. Se eliminó la línea que encriptaba la contraseña (password_hash)

// 4. Preparar la consulta SQL para insertar el nuevo cliente
$sql = "INSERT INTO Cliente (nombre, apellidos, telefono, calle, colonia, codigo_postal, email, contrasena) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) {
    die("Error al preparar la consulta: " . mysqli_error($conexion));
}

// 5. Vincular los datos a la consulta (usando la contraseña plana)
mysqli_stmt_bind_param($stmt, "ssssssss", $nombre, $apellidos, $telefono, $calle, $colonia, $cp, $email, $contrasena);

// 6. Ejecutar la consulta y verificar el resultado
if (mysqli_stmt_execute($stmt)) {
    // Redirigir al usuario a la página de login
    header("Location: ../html/client-login.html?registro=exitoso");
    exit();
} else {
    // Mostrar error si la inserción falla
    echo "Error al registrar el usuario: " . mysqli_error($conexion);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>