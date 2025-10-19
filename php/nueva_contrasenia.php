<?php
// Get the email sent via POST or GET (making it flexible)
$email = '';
if (isset($_POST['email'])) {
    $email = htmlspecialchars($_POST['email']);
} elseif (isset($_GET['email'])) {
    $email = htmlspecialchars($_GET['email']);
}

// If no email, stop.
if (empty($email)) {
    die("Error: No se ha proporcionado un correo electrónico.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Establecer Nueva Contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../src/css/RecuperarContrasenia.css">

    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="image-panel">
            </div>
        <div class="form-panel">
            <div class="form-content">
                <h1>Nueva Contraseña</h1>
                <p class="subtitle">Establece una nueva contraseña para <?php echo $email; ?></p>

                <form class="login-form" action="../php/actualizar_contrasenia.php" method="post">
                    <input type="hidden" name="email" value="<?php echo $email; ?>">

                    <div class="input-group">
                        <input type="password" name="nueva_contrasena" placeholder="Nueva Contraseña" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">Actualizar</button>
                </form>

                 <div class="helper-links">
                     <p><a href="../html/client-login.html">Volver a Iniciar Sesión</a></p>
                 </div>
            </div>
        </div>
    </div>
</body>
</html>