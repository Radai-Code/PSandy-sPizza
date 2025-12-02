<?php
// Asegúrate que la ruta a conexion.php sea correcta
require_once '../conexion.php';

// Iniciar sesión para verificar al admin/empleado
session_start();
// Se asume que el rol se guarda en $_SESSION['empleado_rol'] (establecido en verificar_admin.php)
$rol_actual = strtolower(trim($_SESSION['empleado_rol'] ?? ''));
$es_admin = ($rol_actual === 'admin');
$es_empleado = ($rol_actual === 'empleado');
$esta_logueado = $es_admin || $es_empleado;

if (!$esta_logueado) {
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

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$response = ['status' => 'error', 'message' => 'Acción no válida.'];

switch($action) {
    case 'listar':
    case 'listar_clasificaciones':
    case 'obtener':
        // ACCIONES DE LECTURA (Permitidas para ambos: Admin y Empleado)
        if ($action === 'listar') {
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
        } elseif ($action === 'listar_clasificaciones') {
            $query = mysqli_query($conexion, "SELECT id_clasificacion, nombre_clasificacion FROM Clasificacion ORDER BY nombre_clasificacion");
            if ($query) {
                $clasificaciones = mysqli_fetch_all($query, MYSQLI_ASSOC);
                responderJson($clasificaciones);
            } else {
                $response['message'] = 'Error al listar clasificaciones.';
                responderJson($response);
            }
        } elseif ($action === 'obtener') {
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
        }
        break;

    case 'crear':
        // ACCIÓN RESTRINGIDA (Solo Admin)
        if (!$es_admin) {
            responderTexto("❌ Acceso denegado. Solo Administrador puede crear productos.");
        }
        
        $nombre = trim($_POST['nombre'] ?? '');
        $descripcion = trim($_POST['descripcion'] ?? '');
        $precio = filter_input(INPUT_POST, 'precio_unitario', FILTER_VALIDATE_FLOAT);
        $tamano = trim($_POST['tamaño'] ?? '');
        $descuento = filter_input(INPUT_POST, 'descuento_porcentaje', FILTER_VALIDATE_INT) ?? 0;
        $stock = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT) ?? 0;
        $clasificacion = filter_input(INPUT_POST, 'id_clasificacion', FILTER_VALIDATE_INT);
        
        if ($clasificacion === false || $clasificacion === 0) { $clasificacion = null; }

        if (!empty($nombre) && $precio !== false && !empty($tamano)) {
            $stmt = mysqli_prepare($conexion, "INSERT INTO Producto (nombre, descripcion, precio_unitario, tamaño, descuento_porcentaje, stock, id_clasificacion) VALUES (?, ?, ?, ?, ?, ?, ?)");
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
        // ACCIÓN PERMITIDA (Admin y Empleado)
        
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
             $stmt = mysqli_prepare($conexion, "UPDATE Producto SET nombre=?, descripcion=?, precio_unitario=?, tamaño=?, descuento_porcentaje=?, stock=?, id_clasificacion=? WHERE id_producto=?");
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
        // ACCIÓN RESTRINGIDA (Solo Admin)
        if (!$es_admin) {
             responderTexto("❌ Acceso denegado. Solo Administrador puede eliminar productos.");
        }
        
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