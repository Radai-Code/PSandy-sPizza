<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Sandy's Pizzas</title>

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <!-- CSS del carrito -->
    <link rel="stylesheet" href="../src/css/carrito.css">

    <!-- Icono -->
    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

</head>

<body>

    <!-- Encabezado -->
    <header class="menu-header">
        <a href="../html/index.html" class="brand-logo">Sandy's Pizzas</a>
        <nav class="main-nav">
            <a href="../html/menu.php" class="menu-link">Menú</a>
        </nav>
    </header>

    <main class="cart-container">
        <h1>Tu Carrito de Compras</h1>

        <div class="cart-wrapper">

            <!-- Productos dinámicos -->
            <section class="cart-items" id="lista-carrito">
                <!-- JS insertará productos aquí -->
            </section>

            <!-- Resumen del pedido -->
            <aside class="order-summary">
                <h2>Resumen del Pedido</h2>

                <div class="summary-row">
                    <span>Subtotal:</span>
                    <span id="subtotal">$0.00</span>
                </div>

                <div class="summary-row">
                    <span>Envío:</span>
                    <span id="envio">$40.00</span>
                </div>

                    <div class="summary-total">
                        <span>Total:</span>
                        <span id="total">$40.00</span>
                    </div>

                    <!-- ALERTA BOOTSTRAP -->
                    <div id="alerta" class="alert fade" style="display:none;"></div>

                    <button class="checkout-btn" onclick="location.href='../php/pedidos/procesar_pedido.php'">
                            Confirmar pedido
                    </button>

                    <a href="../html/menu.php" class="continue-shopping">o Seguir Comprando</a>
                </aside>
            </div>
        </main>

    <!-- JavaScript del carrito -->
    <script src="../js/carrito.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
document.getElementById("btnPagar").addEventListener("click", () => {
    fetch("../php/pedidos/procesar_pedido.php", {
        method: "POST"
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.location.href = "../php/pedidos/ver_pedido.php?id=" + data.id_pedido;
        } else {
            alert(data.msg);
        }
    });
});
</script>

</body>

</html>
