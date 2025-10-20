<?php
// Obtener email y tipo desde la URL (enviados por verificar_email.php)
$email = isset($_GET['email']) ? htmlspecialchars($_GET['email']) : '';
$user_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'client';

// Si falta el email, detener.
if (empty($email)) {
    die("Error: Correo electrónico no proporcionado.");
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
    <link rel="stylesheet" href="../src/css/recuperarContrasenia.css">
    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="image-panel"></div>
        <div class="form-panel">
            <div class="form-content">
                <h1>Nueva Contraseña</h1>
                <p class="subtitle">Establece una nueva contraseña para <?php echo $email; ?></p>

                <form class="login-form" action="actualizar_contrasenia.php" method="post">
                    <input type="hidden" name="email" value="<?php echo $email; ?>">
                    <input type="hidden" name="user_type" value="<?php echo $user_type; ?>">

                    <div class="input-group">
                        <input type="password" name="nueva_contrasena" placeholder="Nueva Contraseña" required>
                    </div>
                    <div class="input-group">
                        <input type="password" name="confirmar_contrasena" placeholder="Confirmar Contraseña" required>
                    </div>
                    <button type="submit" class="submit-btn">Actualizar</button>
                </form>

                 <div class="helper-links">
                     <?php if ($user_type === 'admin'): ?>
                        <p><a href="../html/admin/login-admin.html">Volver a Iniciar Sesión</a></p>
                    <?php else: ?>
                        <p><a href="../html/client-login.html">Volver a Iniciar Sesión</a></p>
                    <?php endif; ?>
                 </div>
            </div>
        </div>
    </div>
</body>
</html>