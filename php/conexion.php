<?php
// ========== DATOS DE CONEXIÓN A LA BASE DE DATOS ==========

$servidor = "localhost";
$usuario_db = "root";                // Tu usuario de la base de datos (normalmente 'root')
$contrasena_db = "";                 // Tu contraseña de la base de datos (normalmente vacía)
$nombre_db = "sandys_pizzas_db";     // El nombre de tu base de datos

// ==========================================================


// Crear la conexión con la base de datos
$conexion = mysqli_connect($servidor, $usuario_db, $contrasena_db, $nombre_db);

// Verificar si la conexión falló y detener el script si es así
if (!$conexion) {
    die("Error fatal de conexión: " . mysqli_connect_error());
}

// Opcional: Establecer el conjunto de caracteres a UTF-8 para evitar problemas con acentos y ñ
mysqli_set_charset($conexion, "utf8");

?>