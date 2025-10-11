<?php
// prueba_login.php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once 'php/conexion.php'; // Usa la misma conexión

echo "<h1>Prueba de Diagnóstico de Login</h1>";

// --- DATOS QUE VAMOS A PROBAR ---
$email_a_probar = 'kevin@gmail.com';
$contrasena_a_probar = 'kevin123';
// --------------------------------

echo "<p><strong>Intentando iniciar sesión con:</strong></p>";
echo "<ul>";
echo "<li>Email: " . $email_a_probar . "</li>";
echo "<li>Contraseña: " . $contrasena_a_probar . "</li>";
echo "</ul><hr>";

// Buscamos al usuario en la base de datos
$sql = "SELECT * FROM Cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email_a_probar);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    echo "<p style='color: blue;'><strong>Usuario encontrado en la base de datos.</strong></p>";
    echo "<p>Hash guardado en la DB: " . htmlspecialchars($fila['contrasena']) . "</p>";
    echo "<p>Verificando si la contraseña '" . $contrasena_a_probar . "' coincide con el hash...</p>";

    // Verificamos la contraseña
    if (password_verify($contrasena_a_probar, $fila['contrasena'])) {
        echo "<h2><span style='color: green;'>✅ ¡ÉXITO! La contraseña es correcta.</span></h2>";
        echo "<p>El problema no está en tus datos, sino en cómo se comunican los archivos.</p>";
    } else {
        echo "<h2><span style='color: red;'>❌ FALLO: La contraseña NO es correcta.</span></h2>";
        echo "<p><strong>Causa del problema:</strong> El hash en tu base de datos no corresponde a la contraseña que estás probando ('" . $contrasena_a_probar . "').</p>";
    }
} else {
    echo "<h2><span style='color: red;'>❌ FALLO: No se encontró ningún usuario con el email '" . $email_a_probar . "' en la tabla 'Cliente'.</span></h2>";
    echo "<p><strong>Causa del problema:</strong> El email en tu base de datos no es exactamente 'kevin@gmail.com'.</p>";
}

mysqli_close($conexion);
?>