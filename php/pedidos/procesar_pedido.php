<?php
// Ruta a tu archivo de conexión
require_once '../../php/conexion.php'; 

session_start();
header('Content-Type: application/json');

// 1. Validar Sesión
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión.']);
    exit();
}
$id_cliente = $_SESSION['user_id'];

// 2. Obtener productos del carrito (DESDE LA BASE DE DATOS)
$sql_carrito = "SELECT c.id_producto, c.cantidad, p.precio_unitario, p.descuento_porcentaje, p.stock 
                FROM carrito_compras c
                INNER JOIN Producto p ON c.id_producto = p.id_producto
                WHERE c.id_cliente = $id_cliente";
$res_carrito = mysqli_query($conexion, $sql_carrito);
$items_carrito = mysqli_fetch_all($res_carrito, MYSQLI_ASSOC);

if (empty($items_carrito)) {
    echo json_encode(['success' => false, 'message' => 'Tu carrito está vacío.']);
    exit;
}

$fecha = date('Y-m-d');
$hora = date('H:i:s');
$total_final = 0;
$id_servicio = 1; // 1 = Local

// INICIAR TRANSACCIÓN
mysqli_begin_transaction($conexion);

try {
    // 3. Calcular total y Verificar Stock
    foreach ($items_carrito as $item) {
        $id_prod = $item['id_producto'];
        $cantidad = $item['cantidad'];
        
        // Bloquear fila para evitar concurrencia
        $stmt_lock = mysqli_prepare($conexion, "SELECT stock FROM Producto WHERE id_producto = ? FOR UPDATE");
        mysqli_stmt_bind_param($stmt_lock, "i", $id_prod);
        mysqli_stmt_execute($stmt_lock);
        mysqli_stmt_close($stmt_lock); // Lo cerramos antes de la siguiente consulta

        // Verificar Stock
        if ($item['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para el producto ID $id_prod.");
        }

        // Calcular Precio
        $precio = $item['precio_unitario'];
        if ($item['descuento_porcentaje'] > 0) {
            $precio = $precio * (1 - ($item['descuento_porcentaje'] / 100));
        }
        $total_final += ($precio * $cantidad);
    }
    
    $total_final += 40; // Envío (ajustado en base al HTML que leíste)

    // 4. Insertar Pedido
    $stmt_ped = mysqli_prepare($conexion, "INSERT INTO Pedido (fecha, hora, total, estado, id_cliente, id_servicio) VALUES (?, ?, ?, 'pendiente', ?, ?)");
    mysqli_stmt_bind_param($stmt_ped, "ssdii", $fecha, $hora, $total_final, $id_cliente, $id_servicio);
    if (!mysqli_stmt_execute($stmt_ped)) throw new Exception("Error al crear pedido.");
    
    $id_pedido = mysqli_insert_id($conexion);
    mysqli_stmt_close($stmt_ped);

    // 5. Insertar Detalles y Restar Stock
    $descripcion_item = "Compra Web"; // Descripción por defecto
    
    foreach ($items_carrito as $item) {
        $id_prod = $item['id_producto'];
        $cantidad = $item['cantidad'];
        
        // Recalcular importe para Detalle_Pedido
        $precio = $item['precio_unitario'];
        if ($item['descuento_porcentaje'] > 0) {
            $precio = $precio * (1 - ($item['descuento_porcentaje'] / 100));
        }
        $importe = $precio * $cantidad;

        // --- CORRECCIÓN DEL ERROR ArgumentCountError ---
        // Insertar detalle: 5 placeholders (?, ?, ?, ?, ?)
        $stmt_det = mysqli_prepare($conexion, "INSERT INTO Detalle_Pedido (id_pedido, id_producto, cantidad, importe, descripcion) VALUES (?, ?, ?, ?, ?)");
        // 5 tipos (iiids) y 5 variables
        mysqli_stmt_bind_param($stmt_det, "iiids", $id_pedido, $id_prod, $cantidad, $importe, $descripcion_item);
        
        if (!mysqli_stmt_execute($stmt_det)) {
             throw new Exception("Error al crear detalle del pedido: " . mysqli_error($conexion));
        }
        mysqli_stmt_close($stmt_det);

        // Restar Stock
        $stmt_stock = mysqli_prepare($conexion, "UPDATE Producto SET stock = stock - ? WHERE id_producto = ?");
        mysqli_stmt_bind_param($stmt_stock, "ii", $cantidad, $id_prod);
        mysqli_stmt_execute($stmt_stock);
        mysqli_stmt_close($stmt_stock);
    }

    // 6. Vaciar Carrito en BD
    $stmt_del = mysqli_prepare($conexion, "DELETE FROM carrito_compras WHERE id_cliente = ?");
    mysqli_stmt_bind_param($stmt_del, "i", $id_cliente);
    mysqli_stmt_execute($stmt_del);
    mysqli_stmt_close($stmt_del);

    // Confirmar Transacción
    mysqli_commit($conexion);
    
    // Limpiar sesión
    if(isset($_SESSION['carrito'])) unset($_SESSION['carrito']);

    echo json_encode(['success' => true, 'id_pedido' => $id_pedido]);

} catch (Exception $e) {
    // Si algo falla, revertir cambios
    mysqli_rollback($conexion);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

mysqli_close($conexion);
?>