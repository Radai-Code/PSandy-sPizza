<?php
// Asegúrate que la ruta a conexion.php sea correcta
require_once '../conexion.php';

// Iniciar sesión para verificar al admin
session_start();
if (!isset($_SESSION['admin_id'])) {
    responderJson(['status' => 'error', 'message' => 'Acceso denegado. Inicia sesión.']);
}

// --- Funciones Helper ---
function responderJson($data) {
    global $conexion;
    header('Content-Type: application/json');
    if (isset($data['status']) && $data['status'] === 'error' && $conexion && mysqli_error($conexion)) {
        $data['db_error'] = mysqli_error($conexion);
    }
    echo json_encode($data);
    if ($conexion) mysqli_close($conexion);
    exit();
}
function responderTexto($mensaje) {
    global $conexion;
    header('Content-Type: text/plain');
    echo $mensaje;
    if ($conexion) mysqli_close($conexion);
    exit();
}
// --- Fin Funciones Helper ---

// (Función manejarSubidaImagen ELIMINADA)

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

switch($action) {
    case 'listar':
        // CORREGIDO: Hacer JOIN para obtener el nombre de la clasificación
        $sql = "SELECT p.*, c.nombre_clasificacion 
                FROM Producto p 
                LEFT JOIN Clasificacion c ON p.id_clasificacion = c.id_clasificacion 
                ORDER BY p.id_producto";
        $query = mysqli_query($conexion, $sql);
        if ($query) {
            $productos = mysqli_fetch_all($query, MYSQLI_ASSOC);
            responderJson($productos);
        } else {
            $response['message'] = 'Error al listar productos.';
            responderJson($response);
        }
        break;

    case 'listar_clasificaciones':
        // Esta acción es necesaria para el dropdown del modal
        $query = mysqli_query($conexion, "SELECT id_clasificacion, nombre_clasificacion FROM Clasificacion ORDER BY nombre_clasificacion");
        if ($query) {
            $clasificaciones = mysqli_fetch_all($query, MYSQLI_ASSOC);
            responderJson($clasificaciones);
        } else {
            $response['message'] = 'Error al listar clasificaciones.';
            responderJson($response);
        }
        break;

    case 'obtener':
        $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        if ($id) {
            $stmt = mysqli_prepare($conexion, "SELECT * FROM Producto WHERE id_producto = ?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            mysqli_stmt_execute($stmt);
            $resultado = mysqli_stmt_get_result($stmt);
            $producto = mysqli_fetch_assoc($resultado);
            mysqli_stmt_close($stmt);
            if ($producto) {
                responderJson($producto);
            } else {
                $response['message'] = 'Producto no encontrado.';
                responderJson($response);
            }
        } else {
             $response['message'] = 'ID de producto inválido para obtener.';
             responderJson($response);
        }
        break;

    case 'crear':
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = filter_input(INPUT_POST, 'precio_unitario', FILTER_VALIDATE_FLOAT);
        $tamano = trim($_POST['tamaño'] ?? '');
        $descuento = filter_input(INPUT_POST, 'descuento_porcentaje', FILTER_VALIDATE_INT) ?? 0;
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?? 0;
        $clasificacion = filter_input(INPUT_POST, 'id_clasificacion', FILTER_VALIDATE_INT);
        
        if ($clasificacion === false || $clasificacion === 0) { $clasificacion = null; }

        if (!empty($nombre) && $precio !== false && !empty($tamano)) {
            // CORREGIDO: Sin imagen_url
            $stmt = mysqli_prepare($conexion, "INSERT INTO Producto (nombre, descripcion, precio_unitario, tamaño, descuento_porcentaje, stock, id_clasificacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
            // CORREGIDO: Sin 's' de imagen, 'i' de stock, 'i' de clasificacion
            mysqli_stmt_bind_param($stmt, "ssdsiii", $nombre, $descripcion, $precio, $tamano, $descuento, $stock, $clasificacion);

            if (mysqli_stmt_execute($stmt)) {
                 responderTexto("✅ Producto agregado");
            } else {
                 responderTexto("❌ Error BD: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
             responderTexto("❌ Error: Faltan datos o formato incorrecto.");
        }
        break;

    case 'actualizar':
        $id = filter_input(INPUT_POST, 'id_producto_hidden', FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = filter_input(INPUT_POST, 'precio_unitario', FILTER_VALIDATE_FLOAT);
        $tamano = trim($_POST['tamaño'] ?? '');
        $descuento = filter_input(INPUT_POST, 'descuento_porcentaje', FILTER_VALIDATE_INT) ?? 0;
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?? 0;
        $clasificacion = filter_input(INPUT_POST, 'id_clasificacion', FILTER_VALIDATE_INT);
        
        if ($clasificacion === false || $clasificacion === 0) { $clasificacion = null; }
        
        if ($id && !empty($nombre) && $precio !== false && !empty($tamano)) {
             // CORREGIDO: Sin imagen_url
             $stmt = mysqli_prepare($conexion, "UPDATE Producto SET nombre=?, descripcion=?, precio_unitario=?, tamaño=?, descuento_porcentaje=?, stock=?, id_clasificacion=? WHERE id_producto=?");
             // CORREGIDO: Sin 's' de imagen
             mysqli_stmt_bind_param($stmt, "ssdsiiii", $nombre, $descripcion, $precio, $tamano, $descuento, $stock, $clasificacion, $id);

             if (mysqli_stmt_execute($stmt)) {
                  responderTexto("✏️ Producto actualizado");
             } else {
                  responderTexto("❌ Error BD: " . mysqli_stmt_error($stmt));
             }
             mysqli_stmt_close($stmt);
        } else {
             responderTexto("❌ Error: Faltan datos o ID inválido.");
        }
        break;

    case 'eliminar':
        $id = filter_input(INPUT_POST, 'id_producto', FILTER_VALIDATE_INT);
        if ($id) {
            // CORREGIDO: No se borra imagen
            $stmt = mysqli_prepare($conexion, "DELETE FROM Producto WHERE id_producto=?");
            mysqli_stmt_bind_param($stmt, "i", $id);
            if (mysqli_stmt_execute($stmt)) {
                responderTexto("🗑️ Producto eliminado");
            } else {
                 responderTexto("❌ Error BD: " . mysqli_stmt_error($stmt));
            }
             mysqli_stmt_close($stmt);
        } else {
             responderTexto("❌ Error: ID inválido.");
        }
        break;

    default:
        $response['message'] = "⚠️ Acción '$action' no reconocida.";
        responderJson($response);
}
if ($conexion) { mysqli_close($conexion); }

?>