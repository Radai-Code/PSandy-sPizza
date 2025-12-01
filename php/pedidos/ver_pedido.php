<?php
include "../conexion.php";

$id_pedido = $_GET['id'];

$sql = "
    SELECT dp.*, p.nombre, p.imagen
    FROM detalle_pedido dp
    INNER JOIN producto p ON p.id_producto = dp.id_producto
    WHERE dp.id_pedido = $id_pedido
";

$res = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <title>Detalle del pedido</title>
</head>
<body class="p-4">

<h2>Detalle del pedido #<?= $id_pedido ?></h2>

<?php while ($d = $res->fetch_assoc()): ?>
<div class="card mb-3">
    <div class="card-body d-flex align-items-center">
        <img src="<?= $d['imagen'] ?>" width="90" height="90" class="me-3 rounded">
        <div>
            <h5><?= $d['nombre'] ?></h5>
            <p>Cantidad: <?= $d['cantidad'] ?></p>
            <p>Precio: $<?= number_format($d['importe'],2) ?></p>
        </div>
    </div>
</div>
<?php endwhile; ?>

</body>
</html>
