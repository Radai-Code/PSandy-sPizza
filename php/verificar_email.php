<?php
require_once 'conexion.php';

$email = $_POST['email'];

// Check if the email exists in the Cliente table
$sql = "SELECT id_cliente FROM Cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($resultado) > 0) {
    // Email exists, redirect to the page to enter the new password
    // We pass the email in the URL so the next page knows who is changing the password
    header("Location: ../html/nueva_contrasenia.php?email=" . urlencode($email));
    exit();
} else {
    // Email not found, redirect back with an error message
    header("Location: ../html/recuperar_Contrasenia.html?error=no_encontrado");
    exit();
}
?>