<?php
require_once 'conexion.php';
session_start();
header('Content-Type: application/json');

// Leer entrada JSON o GET
$input = json_decode(file_get_contents("php://input"), true);
$action = $input['action'] ?? ($_GET['action'] ?? null);
$id_producto = $input['id_producto'] ?? null;
$cantidad = $input['cantidad'] ?? 1;

// Verificar si el usuario está logueado
$usuario_logueado = isset($_SESSION['user_id']);
$id_cliente = $usuario_logueado ? $_SESSION['user_id'] : null;

// Inicializar carrito de sesión si no existe
if (!isset($_SESSION['carrito'])) $_SESSION['carrito'] = [];

// =======================================================================
// 1. AGREGAR PRODUCTO
// =======================================================================
if ($action === "add") {
    if (!$id_producto) { echo json_encode(["success" => false, "message" => "ID faltante"]); exit; }

    if ($usuario_logueado) {
        // --- MODO BASE DE DATOS ---
        $stmt = mysqli_prepare($conexion, "SELECT cantidad FROM carrito_compras WHERE id_cliente = ? AND id_producto = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id_cliente, $id_producto);
        mysqli_stmt_execute($stmt);
        $res = mysqli_stmt_get_result($stmt);
        
        if ($row = mysqli_fetch_assoc($res)) {
            $nueva_cant = $row['cantidad'] + $cantidad;
            $stmt_upd = mysqli_prepare($conexion, "UPDATE carrito_compras SET cantidad = ? WHERE id_cliente = ? AND id_producto = ?");
            mysqli_stmt_bind_param($stmt_upd, "iii", $nueva_cant, $id_cliente, $id_producto);
            mysqli_stmt_execute($stmt_upd);
        } else {
            $stmt_ins = mysqli_prepare($conexion, "INSERT INTO carrito_compras (id_cliente, id_producto, cantidad) VALUES (?, ?, ?)");
            mysqli_stmt_bind_param($stmt_ins, "iii", $id_cliente, $id_producto, $cantidad);
            mysqli_stmt_execute($stmt_ins);
        }
    } else {
        // --- MODO SESIÓN ---
        if (isset($_SESSION['carrito'][$id_producto])) {
            $_SESSION['carrito'][$id_producto] += $cantidad;
        } else {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        }
    }
    echo json_encode(["success" => true, "message" => "Producto agregado"]);
    exit;
}

// =======================================================================
// 2. OBTENER CARRITO (GET) - AQUÍ ESTABA EL ERROR
// =======================================================================
if ($action === "get") {
    $carrito_data = [];

    if ($usuario_logueado) {
        // --- CORRECCIÓN: Eliminamos 'p.imagen_url' de la consulta ---
        $query = "SELECT p.id_producto, p.nombre, p.precio_unitario, p.descuento_porcentaje, c.cantidad 
                  FROM carrito_compras c
                  INNER JOIN Producto p ON c.id_producto = p.id_producto
                  WHERE c.id_cliente = $id_cliente";
        $res = mysqli_query($conexion, $query);
    } else {
        // --- MODO SESIÓN ---
        if (empty($_SESSION['carrito'])) {
            echo json_encode(["success" => true, "carrito" => []]);
            exit;
        }
        $ids = implode(',', array_keys($_SESSION['carrito']));
        // --- CORRECCIÓN: Eliminamos 'imagen_url' de la consulta ---
        $query = "SELECT id_producto, nombre, precio_unitario, descuento_porcentaje FROM Producto WHERE id_producto IN ($ids)";
        $res = mysqli_query($conexion, $query);
    }

    if ($res) {
        while ($p = mysqli_fetch_assoc($res)) {
            $cant = $usuario_logueado ? $p['cantidad'] : $_SESSION['carrito'][$p['id_producto']];

            // Calcular precio con descuento
            $precio = $p['precio_unitario'];
            if ($p['descuento_porcentaje'] > 0) {
                $precio = $precio * (1 - ($p['descuento_porcentaje'] / 100));
            }

            $carrito_data[] = [
                "id" => $p["id_producto"],
                "nombre" => $p["nombre"],
                "precio" => $precio,
                "cantidad" => $cant,
                // --- CORRECCIÓN: Usamos siempre el logo por defecto ---
                "imagen" => "../src/imagenes/logo.png"
            ];
        }
    }
    echo json_encode(["success" => true, "carrito" => $carrito_data]);
    exit;
}

// =======================================================================
// 3. ACTUALIZAR CANTIDAD
// =======================================================================
if ($action === "update") {
    if (!$id_producto) exit(json_encode(["success" => false]));

    if ($usuario_logueado) {
        if ($cantidad > 0) {
            $stmt = mysqli_prepare($conexion, "UPDATE carrito_compras SET cantidad = ? WHERE id_cliente = ? AND id_producto = ?");
            mysqli_stmt_bind_param($stmt, "iii", $cantidad, $id_cliente, $id_producto);
            mysqli_stmt_execute($stmt);
        } else {
            $stmt = mysqli_prepare($conexion, "DELETE FROM carrito_compras WHERE id_cliente = ? AND id_producto = ?");
            mysqli_stmt_bind_param($stmt, "ii", $id_cliente, $id_producto);
            mysqli_stmt_execute($stmt);
        }
    } else {
        if ($cantidad > 0) {
            $_SESSION['carrito'][$id_producto] = $cantidad;
        } else {
            unset($_SESSION['carrito'][$id_producto]);
        }
    }
    echo json_encode(["success" => true]);
    exit;
}

// =======================================================================
// 4. ELIMINAR PRODUCTO
// =======================================================================
if ($action === "delete") {
    if (!$id_producto) exit(json_encode(["success" => false]));

    if ($usuario_logueado) {
        $stmt = mysqli_prepare($conexion, "DELETE FROM carrito_compras WHERE id_cliente = ? AND id_producto = ?");
        mysqli_stmt_bind_param($stmt, "ii", $id_cliente, $id_producto);
        mysqli_stmt_execute($stmt);
    } else {
        unset($_SESSION['carrito'][$id_producto]);
    }
    echo json_encode(["success" => true]);
    exit;
}

echo json_encode(["success" => false, "message" => "Acción inválida"]);
?>