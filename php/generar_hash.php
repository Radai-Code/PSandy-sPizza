<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Asegúrate de que la ruta a tu archivo de conexión sea correcta
require_once 'php/conexion.php'; 

echo "<h1>Prueba Final de Diagnóstico</h1>";

// --- DATOS EXACTOS QUE VAMOS A PROBAR ---
$email_a_probar = 'kevin@gmail.com';
$contrasena_a_probar = 'cliente123'; // La contraseña que pusiste en generar_hash.php
// -----------------------------------------

echo "<p>Buscando usuario con email: <strong>" . $email_a_probar . "</strong></p>";

// Buscamos al usuario en la base de datos
$sql = "SELECT contrasena FROM Cliente WHERE email = ?";
$stmt = mysqli_prepare($conexion, $sql);
mysqli_stmt_bind_param($stmt, "s", $email_a_probar);
mysqli_stmt_execute($stmt);
$resultado = mysqli_stmt_get_result($stmt);

if ($fila = mysqli_fetch_assoc($resultado)) {
    echo "<p><strong>Usuario encontrado.</strong></p>";
    $hash_guardado = $fila['contrasena'];
    echo "<p>Hash en la DB: " . htmlspecialchars($hash_guardado) . "</p>";
    echo "<p>Verificando si la contraseña '<strong>" . $contrasena_a_probar . "</strong>' coincide...</p>";

    // Verificamos la contraseña
    if (password_verify($contrasena_a_probar, $hash_guardado)) {
        echo "<h2><span style='color: green;'>✅ ¡ÉXITO! La contraseña y el hash coinciden.</span></h2>";
        echo "<p>Si ves esto, el problema está en los datos que se envían desde el formulario.</p>";
    } else {
        echo "<h2><span style='color: red;'>❌ FALLO: La contraseña NO coincide con el hash.</span></h2>";
        echo "<p><strong>Problema confirmado:</strong> El hash en tu base de datos está mal copiado o no corresponde a la contraseña que estás probando.</p>";
    }
} else {
    echo "<h2><span style='color: red;'>❌ FALLO: No se encontró ningún usuario con ese email.</span></h2>";
}

mysqli_close($conexion);
?>