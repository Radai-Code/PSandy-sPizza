<?php
session_start();
require_once "../conexion.php";

// 1. Verificar login
if (!isset($_SESSION['user_id'])) {
    die("Error: Debes iniciar sesión.");
}

$id_cliente = $_SESSION['user_id'];

// 2. Verificar carrito
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    die("Error: Tu carrito está vacío.");
}

$carrito = $_SESSION['carrito'];

$fecha = date("Y-m-d");
$hora = date("H:i:s");
$estado = "Pendiente";

// 3. Calcular total usando la BD (correcto)
$total = 0;
foreach ($carrito as $id_producto => $cantidad) {

    $q = $conexion->query("SELECT precio_unitario FROM producto WHERE id_producto = $id_producto");
    $row = $q->fetch_assoc();

    if (!$row) {
        die("Error: Producto con ID $id_producto no existe.");
    }

    $precio = $row['precio'];
    $total += $precio * $cantidad;
}

// 4. Insertar pedido
$conexion->query("
    INSERT INTO pedido (fecha, hora, total, estado, id_cliente)
    VALUES ('$fecha', '$hora', '$total', '$estado', '$id_cliente')
");

$id_pedido = $conexion->insert_id;

// 5. Insertar detalle_pedido
foreach ($carrito as $id_producto => $cantidad) {

    $q = $conexion->query("SELECT precio_unitario FROM producto WHERE id_producto = $id_producto");
    $row = $q->fetch_assoc();
    $precio = $row['precio_unitario'];

    $importe = $precio * $cantidad;

    $conexion->query("
        INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, importe)
        VALUES ('$id_pedido', '$id_producto', '$cantidad', '$importe')
    ");
}

// 6. Vaciar carrito
unset($_SESSION['carrito']);

echo "<script>
        alert('Pedido confirmado con éxito');
        window.location.href='mis_pedidos.php';
      </script>";

?>
