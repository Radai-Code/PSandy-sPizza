<?php
// Get the user type from the URL, default to 'client' if not present
$user_type = isset($_GET['type']) ? htmlspecialchars($_GET['type']) : 'client';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Recuperar Contraseña</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../src/css/recuperarContrasenia.css">

    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
</head>
<body>
    <div class="login-wrapper">
        <div class="image-panel">
            </div>
        <div class="form-panel">
            <div class="form-content">
                <h1>Recuperar Contraseña</h1>
                <p class="subtitle">Ingresa tu correo para continuar</p>
                <?php if (isset($_GET['error'])): ?>
    <div class="error-message">
        <?php
        switch ($_GET['error']) {
            case 'correo_no_registrado':
                echo "❌ El correo no está registrado.";
                break;
            case 'correo_vacio':
                echo "⚠️ Debes ingresar un correo.";
                break;
            default:
                echo "⚠️ Ocurrió un error. Inténtalo nuevamente.";
                break;
        }
        ?>
    </div>
<?php endif; ?>


<form action="/PSandy-sPizza/php/verificar_cliente.php" method="post">
    <input type="email" name="email" placeholder="Correo" required>
    <input type="hidden" name="user_type" value="client">
    <button type="submit">Enviar</button>
</form>



                <div class="helper-links">
                    <?php if ($user_type === 'admin'): ?>
                        <p><a href="admin/login-admin.html">Volver a Iniciar Sesión</a></p>
                    <?php else: ?>
                        <p><a href="client-login.html">Volver a Iniciar Sesión</a></p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>