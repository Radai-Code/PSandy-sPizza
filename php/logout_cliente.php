<?php
// 1. Iniciar la sesión para poder acceder a ella.
session_start();

// 2. Borrar todas las variables de la sesión (user_id, user_nombre, etc.).
$_SESSION = array();

// 3. Destruir la sesión por completo.
session_destroy();

// 4. Redirigir al usuario a la página de inicio.
header("Location: ../html/index.html");
exit();
?>