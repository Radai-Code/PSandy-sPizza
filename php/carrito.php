<?php
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Carrito de Compras - Sandy's Pizzas</title>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../src/css/carrito.css">
    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
</head>

<body>

    <header class="menu-header">
        <a href="../html/index.html" class="brand-logo">Sandy's Pizzas</a>
        <nav class="main-nav">
            <a href="../html/menu.php" class="menu-link">Menú</a>
            <?php if (isset($_SESSION['user_id'])): ?>
                <a href="logout_cliente.php" class="login-link">Cerrar Sesión</a>
            <?php else: ?>  
                <a href="../html/login.html" class="login-link">Iniciar Sesión</a>
            <?php endif; ?>
        </nav>
    </header>

    <main class="cart-container">
        <h1>Tu Carrito de Compras</h1>

        <div class="cart-wrapper">
            <section class="cart-items" id="lista-carrito">
                <div class="text-center p-5">
                    <div class="spinner-border text-success" role="status">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                </div>
            </section>

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
                    <span id="total">$0.00</span>
                </div>

                <div id="alerta" class="alert fade" style="display:none;"></div>

                <button id="btn-confirmar" class="checkout-btn">
                    Confirmar pedido
                </button>

                <a href="../html/menu.php" class="continue-shopping">o Seguir Comprando</a>
            </aside>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../js/carrito.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>