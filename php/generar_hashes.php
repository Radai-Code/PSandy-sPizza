<?php
require_once 'conexion.php';

// Arreglo temporal con clientes originales
$clientes = [
    ['id' => 1, 'password' => 'contraseña_cliente1'],
    ['id' => 2, 'password' => 'contraseña_cliente2'],
    // Agregar todos los clientes
];

// Actualizar clientes
foreach($clientes as $c){
    $hash = password_hash($c['password'], PASSWORD_DEFAULT);
    mysqli_query($conexion, "UPDATE cliente SET contrasena='$hash' WHERE id_cliente={$c['id']}");
}

// Arreglo temporal con admin original
$admins = [
    ['id' => 4, 'password' => 'contraseñaAdmin']
];

// Actualizar admin
foreach($admins as $a){
    $hash = password_hash($a['password'], PASSWORD_DEFAULT);
    mysqli_query($conexion, "UPDATE empleado SET contrasena='$hash' WHERE id_empleado={$a['id']}");
}

echo "✅ Hashes generados correctamente para todos los usuarios.";
?>
