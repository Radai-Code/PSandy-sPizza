<?php
// 1. Incluir conexión y iniciar sesión PRIMERO
require_once '../conexion.php'; 
session_start();

// 2. ===== VERIFICACIÓN DE SESIÓN =====
// Si la variable de sesión del admin no existe, significa que no ha iniciado sesión.
if (!isset($_SESSION['admin_id'])) {
    // Enviar una respuesta de error JSON y detener el script
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Acceso denegado: No has iniciado sesión como administrador.']);
    exit(); 
}
// ===================================

// 3. El resto de tu código CRUD (sin cambios)
header('Content-Type: application/json'); // Respuesta por defecto JSON

function responderJson($data) {
    global $conexion; 
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

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Acción no válida o faltan datos.']; 

switch($action) {
    // ... (Tus cases 'listar', 'obtener', 'crear', 'actualizar', 'eliminar', 'listar_clasificaciones') ...
    // Asegúrate de que los nombres de tabla 'Producto' y 'Clasificacion' sean correctos.

    case 'listar':
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
        $clasificacion = filter_input(INPUT_POST, 'id_clasificacion', FILTER_VALIDATE_INT);

        // Permitir clasificación NULL si no se selecciona o es inválido
        if ($clasificacion === false || $clasificacion === 0) {
            $clasificacion = null; 
        }

        if (!empty($nombre) && $precio !== false && !empty($tamano)) {
            // Asegurar que la consulta maneje NULL para id_clasificacion
            $stmt = mysqli_prepare($conexion, "INSERT INTO Producto (nombre, descripcion, precio_unitario, tamaño, id_clasificacion) VALUES (?, ?, ?, ?, ?)");
            // 'i' para integer si es un ID válido, 's' si es NULL (ajustado en bind_param)
             mysqli_stmt_bind_param($stmt, "ssdsi", $nombre, $descripcion, $precio, $tamano, $clasificacion); // Usar 'i' para ID o NULL

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
        $clasificacion = filter_input(INPUT_POST, 'id_clasificacion', FILTER_VALIDATE_INT);
        
        // Permitir clasificación NULL
        if ($clasificacion === false || $clasificacion === 0) {
            $clasificacion = null; 
        }

        if ($id && !empty($nombre) && $precio !== false && !empty($tamano)) {
             // Asegurar que la consulta maneje NULL para id_clasificacion
             $stmt = mysqli_prepare($conexion, "UPDATE Producto SET nombre=?, descripcion=?, precio_unitario=?, tamaño=?, id_clasificacion=? WHERE id_producto=?");
             mysqli_stmt_bind_param($stmt, "ssdssi", $nombre, $descripcion, $precio, $tamano, $clasificacion, $id); // Usar 'i' para ID o NULL

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