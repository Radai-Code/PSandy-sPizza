<?php
require_once 'conexion.php';

$email = $_POST['email'];
$nueva_contrasena = $_POST['nueva_contrasena'];
$confirmar_contrasena = $_POST['confirmar_contrasena'];

// Check if passwords match
if ($nueva_contrasena !== $confirmar_contrasena) {
    // Redirect back with error if they don't match
    header("Location: ../html/nueva_contrasenia.php?email=" . urlencode($email) . "&error=no_coincide");
    exit();
}

// Update the password (plain text)
$sql = "UPDATE Cliente SET contrasena = ? WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);

if (!$stmt) { die("Error: " . mysqli_error($conexion)); }

mysqli_stmt_bind_param($stmt, "ss", $nueva_contrasena, $email);

if (mysqli_stmt_execute($stmt)) {
    // Redirect to login page on success
    header("Location: ../html/client-login.html?reset=exitoso");
    exit();
} else {
    echo "Error al actualizar: " . mysqli_error($conexion);
}

mysqli_stmt_close($stmt);
mysqli_close($conexion);
?>