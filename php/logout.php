<?php
// 1. Iniciar la sesión.
session_start();

// 2. Borra todas las variables de la sesión.
$_SESSION = array();

// 3. Destruye la sesión.
session_destroy();

// 4. Redirige al usuario a tu página login.html.
header("Location: ../html/login.html");
exit();
?>