<?php
session_start();
header('Content-Type: application/json');
require_once "conexion.php"; // Para obtener los datos del producto real

// Recibir JSON o parámetros GET
$inputJSON = file_get_contents("php://input");
$input = json_decode($inputJSON, true);

$action = $input['action'] ?? ($_GET['action'] ?? null);
$id_producto = $input['id_producto'] ?? null;
$cantidad = $input['cantidad'] ?? 1;

// Inicializar carrito
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = [];
}

/* ------------------------- 1. AGREGAR -------------------------- */
if ($action === "add") {

    if (!$id_producto) {
        echo json_encode(["success" => false, "message" => "ID no recibido"]);
        exit;
    }

    if (isset($_SESSION['carrito'][$id_producto])) {
        $_SESSION['carrito'][$id_producto] += $cantidad;
    } else {
        $_SESSION['carrito'][$id_producto] = $cantidad;
    }

    echo json_encode(["success" => true, "message" => "Producto agregado"]);
    exit;
}

/* ------------------------- 2. OBTENER -------------------------- */
if ($action === "get") {
    $carrito = [];

    foreach ($_SESSION['carrito'] as $id => $cant) {
        $query = "SELECT id_producto, nombre, precio_unitario, imagen FROM producto WHERE id_producto = $id";
        $res = mysqli_query($conexion, $query);

        if ($res && mysqli_num_rows($res) > 0) {
            $p = mysqli_fetch_assoc($res);

            $carrito[] = [
                "id" => $p["id_producto"],
                "nombre" => $p["nombre"],
                "precio" => $p["precio_unitario"],
                "cantidad" => $cant,
                "imagen" => $p["imagen"] ?? "https://via.placeholder.com/100"
            ];
        }
    }

    echo json_encode(["success" => true, "carrito" => $carrito]);
    exit;
}

/* ------------------------- 3. ACTUALIZAR ---------------------- */
if ($action === "update") {
    if (!$id_producto) { echo json_encode(["success" => false]); exit; }

    $_SESSION['carrito'][$id_producto] = $cantidad;
    echo json_encode(["success" => true]);
    exit;
}

/* ------------------------- 4. ELIMINAR ------------------------ */
if ($action === "delete") {
    if (!$id_producto) { echo json_encode(["success" => false]); exit; }

    unset($_SESSION['carrito'][$id_producto]);
    echo json_encode(["success" => true]);
    exit;
}

/* ------------------------ SI NO COINCIDE ---------------------- */
echo json_encode(["success" => false, "message" => "Acción inválida"]);
exit;
