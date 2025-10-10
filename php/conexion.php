<?php
// ======================= CONFIGURACIÓN =======================
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$servidor = '127.0.0.1';      // o 'localhost'
$usuario_db = 'root';
$contrasena_db = 'mareli19';
$nombre_db = 'ProyectoSandys';

// ======================= CONEXIÓN ==========================
$conexion = mysqli_connect($servidor, $usuario_db, $contrasena_db, $nombre_db);

if (!$conexion) {
    die("Error fatal de conexión: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

echo "<h3>¡Conexión exitosa a la base de datos!</h3>";

// ======================= CONSULTA ===========================
// Mostrar los primeros 5 clientes
$query = "SELECT * FROM Cliente LIMIT 5";
$resultado = mysqli_query($conexion, $query);

if ($resultado) {
    echo "<h4>Clientes:</h4>";
    echo "<ul>";
    while ($fila = mysqli_fetch_assoc($resultado)) {
        echo "<li>ID: {$fila['id_cliente']}, Nombre: {$fila['nombre']}, Teléfono: {$fila['telefono']}</li>";
    }
    echo "</ul>";
} else {
    echo "Error en la consulta: " . mysqli_error($conexion);
}

// ======================= INSERCIÓN DE PRUEBA ================
// Descomenta las líneas siguientes para insertar un cliente de prueba
/*
$nombre = "Juan Pérez";
$telefono = "5551234567";
$calle = "Av. Siempre Viva 123";
$colonia = "Centro";

$insert = "INSERT INTO Cliente (nombre, telefono, calle, colonia) 
           VALUES ('$nombre', '$telefono', '$calle', '$colonia')";

if (mysqli_query($conexion, $insert)) {
    echo "<p>Cliente insertado correctamente.</p>";
} else {
    echo "Error al insertar: " . mysqli_error($conexion);
}
*/

// ======================= CERRAR CONEXIÓN ====================
mysqli_close($conexion);
?>
