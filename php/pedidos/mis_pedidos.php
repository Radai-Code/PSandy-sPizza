<?php
session_start();
require_once "../conexion.php";

if (!isset($_SESSION['user_id'])) {
    die("Error: Debes iniciar sesión.");
}

$id_cliente = $_SESSION['user_id'];

// Obtener pedidos
$sql = "SELECT * FROM pedido WHERE id_cliente = $id_cliente ORDER BY fecha DESC, hora DESC";
$res = $conexion->query($sql);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis pedidos</title>

    <!-- RUTA CORRECTA A TU CSS -->
    <link rel="stylesheet" href="../../src/css/mis_pedidos.css">
</head>

<body>

<h1 class="titulo">Mis pedidos</h1>

<div class="pedidos-container">
<?php
if ($res->num_rows == 0) {
    echo "<p class='sin-pedidos'>No tienes pedidos registrados.</p>";
} else {
    while ($p = $res->fetch_assoc()) {

        $id_pedido = $p["id_pedido"];

        // Obtener detalle del pedido
        $det = $conexion->query("
            SELECT dp.*, pr.nombre 
            FROM detalle_pedido dp
            INNER JOIN producto pr ON pr.id_producto = dp.id_producto
            WHERE dp.id_pedido = $id_pedido
        ");
        ?>

        <div class="pedido-card">
            <div class="pedido-header">
                <span class="pedido-id">Pedido #<?= $id_pedido ?></span>
                <span class="estado estado-<?= strtolower($p['estado']); ?>">
                    <?= $p["estado"] ?>
                </span>
            </div>

            <div class="pedido-info">
                <p><strong>Fecha:</strong> <?= $p["fecha"] ?></p>
                <p><strong>Hora:</strong> <?= $p["hora"] ?></p>
            </div>

            <ul class="productos-lista">
                <?php while ($row = $det->fetch_assoc()) { ?>
                    <li>
                        <?= $row["nombre"] ?> (<?= $row["cantidad"] ?>)
                        — $<?= number_format($row["importe"], 2) ?>
                    </li>
                <?php } ?>
            </ul>

            <p class="total">Total: $<?= number_format($p["total"], 2) ?></p>
        </div>

        <?php
    }
}
?>
</div>

</body>
</html>
