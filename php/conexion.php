<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$servidor = '127.0.0.1';
$usuario_db = 'root';
$contrasena_db = 'mareli19';
$nombre_db = 'ProyectoSandys';

// Crear la conexión con la base de datos
$conexion = mysqli_connect($servidor, $usuario_db, $contrasena_db, $nombre_db);

// Verificar si la conexión falló
if (!$conexion) {
    die("Error fatal de conexión: " . mysqli_connect_error());
}

// Establecer el conjunto de caracteres a UTF-8
mysqli_set_charset($conexion, "utf8");
?>
