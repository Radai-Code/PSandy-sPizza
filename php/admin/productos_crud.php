<?php
// Asegúrate que la ruta a conexion.php sea correcta
require_once '../conexion.php';

// Iniciar sesión para verificar al admin
session_start();
if (!isset($_SESSION['admin_id'])) {
    responderJson(['status' => 'error', 'message' => 'Acceso denegado. Inicia sesión.']);
}

// --- Funciones Helper (sin cambios) ---
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

// Se eliminó la función manejarSubidaImagen()

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

switch($action) {
    case 'listar':
        $query = mysqli_query($conexion, "SELECT * FROM Producto ORDER BY id_producto");
        if ($query) {
            $productos = mysqli_fetch_all($query, MYSQLI_ASSOC);
            responderJson($productos);
        } else {
            $response['message'] = 'Error al listar productos.';
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
        
        // No se maneja imagen

        if (!empty($nombre) && $precio !== false && !empty($tamano)) {
            // Se quita 'imagen_url' del INSERT
            $stmt = mysqli_prepare($conexion, "INSERT INTO Producto (nombre, descripcion, precio_unitario, tamaño, descuento_porcentaje) VALUES (?, ?, ?, ?, ?)");
            // Se quita 's' (para imagen) y $imagen_nombre_db del bind
            mysqli_stmt_bind_param($stmt, "ssdsi", $nombre, $descripcion, $precio, $tamano, $descuento);

            if (mysqli_stmt_execute($stmt)) {
                 responderTexto("✅ Producto agregado");
            } else {
                 responderTexto("❌ Error BD: " . mysqli_stmt_error($stmt));
            }
            mysqli_stmt_close($stmt);
        } else {
             responderTexto("❌ Error: Faltan datos o formato incorrecto (Nombre, Precio, Tamaño).");
        }
        break;

    case 'actualizar':
        $id = filter_input(INPUT_POST, 'id_producto_hidden', FILTER_VALIDATE_INT);
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = filter_input(INPUT_POST, 'precio_unitario', FILTER_VALIDATE_FLOAT);
        $tamano = trim($_POST['tamaño'] ?? '');
        $descuento = filter_input(INPUT_POST, 'descuento_porcentaje', FILTER_VALIDATE_INT) ?? 0;
        
        // No se maneja imagen

        if ($id && !empty($nombre) && $precio !== false && !empty($tamano)) {
             // Se quita 'imagen_url=?' del UPDATE
             $stmt = mysqli_prepare($conexion, "UPDATE Producto SET nombre=?, descripcion=?, precio_unitario=?, tamaño=?, descuento_porcentaje=? WHERE id_producto=?");
             // Se quita 's' (para imagen) y $imagen_nombre_db del bind
             mysqli_stmt_bind_param($stmt, "ssdsii", $nombre, $descripcion, $precio, $tamano, $descuento, $id);

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
            // No se borra imagen del servidor
            
            // Eliminar de la base de datos
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