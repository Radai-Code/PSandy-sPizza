<?php
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$user_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'client';

if (empty($email)) {
    die("Error: Correo electrónico no proporcionado.");
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Contraseña</title>
    <link rel="stylesheet" href="/PSandy-sPizza/src/css/recuperarContrasenia.css">
    <link rel="icon" type="image/png" href="/PSandy-sPizza/src/imagenes/logo.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="image-panel"></div>
        <div class="form-panel">
            <div class="form-content">
                <h1>Nueva Contraseña</h1>
                <p class="subtitle">Establece una nueva contraseña para <strong><?php echo $email; ?></strong></p>

                <form action="/PSandy-sPizza/php/actualizar_Contrasenia.php" method="post">
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <input type="hidden" name="user_type" value="<?php echo $user_type; ?>">

                    <input type="password" name="nueva_contrasena" placeholder="Nueva Contraseña" required>
                    <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>

                    <button type="submit">Actualizar</button>
                </form>

                <div class="helper-links">
                    <?php if ($user_type === 'admin'): ?>
                        <p><a href="/PSandy-sPizza/html/admin/login-admin.html">Volver a Iniciar Sesión</a></p>
                    <?php else: ?>
                        <p><a href="/PSandy-sPizza/html/client-login.html">Volver a Iniciar Sesión</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
