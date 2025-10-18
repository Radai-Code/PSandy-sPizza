<?php
// 1. Iniciar la sesión. ESTO DEBE SER LO PRIMERO EN EL ARCHIVO.
session_start();
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menú - Sandy's Pizzas</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">

    <link rel="stylesheet" href="../src/css/menu.css">
    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
</head>

<body>

    <header class="menu-header">
        <a href="index.html" class="brand-logo">Sandy's Pizzas</a>
        <nav class="main-nav">
            <a href="carrito.html" class="cart-link">🛒 Mi Carrito</a>
            
            <?php if (isset($_SESSION['user_id'])): ?>
                
                <div class="user-profile">
                    <span>Hola, <?php echo htmlspecialchars($_SESSION['user_nombre']); ?></span>
                    <img src="../src/imagenes/icons/cliente.png" alt="Perfil">
                </div>
                <a href="../php/logout_cliente.php" class="login-link">Cerrar Sesión</a>

            <?php else: ?>
                
                <a href="login.html" class="login-link">Iniciar Sesión</a>

            <?php endif; ?>
            </nav>
    </header>

    <main class="menu-container">
        <h1 class="main-title">Nuestro Menú</h1>

        <div class="category-filters">
            <button class="filter-btn active" data-filter="todos">Todos</button>
            <button class="filter-btn" data-filter="pizzas">Pizzas</button>
            <button class="filter-btn" data-filter="bebidas">Bebidas</button>
            <button class="filter-btn" data-filter="combos">Combos</button>
            <button class="filter-btn" data-filter="hamburguesas">Hamburguesas</button>
            <button class="filter-btn" data-filter="espaguetis">Espaguetis</button>
        </div>

        <div class="product-grid">

            <div class="product-card" data-category="pizzas">
                <img src="" alt="Pizza de Peperoni" class="product-image">
                <div class="product-info">
                    <h2>Pizza de Peperoni</h2>
                    <p>La clásica que nunca falla, con extra queso y peperoni de primera.</p>
                    <span class="price">$150.00</span>
                </div>
                <button class="add-to-cart-btn">Agregar al Carrito</button>
            </div>
            
            <div class="product-card" data-category="pizzas">
                <img src="" alt="Pizza Hawaiana" class="product-image">
                <div class="product-info">
                    <h2>Pizza Hawaiana</h2>
                    <p>La controversial pero deliciosa combinación de jamón y piña.</p>
                    <span class="price">$160.00</span>
                </div>
                <button class="add-to-cart-btn">Agregar al Carrito</button>
            </div>

            <div class="product-card" data-category="bebidas">
                <img src="" alt="Refresco de Cola" class="product-image">
                <div class="product-info">
                    <h2>Refresco de Cola</h2>
                    <p>Refresco de 600ml bien frío para acompañar tu comida.</p>
                    <span class="price">$30.00</span>
                </div>
                <button class="add-to-cart-btn">Agregar al Carrito</button>
            </div>
            </div>
    </main>

    <script src="../js/menu.js"></script>
</body>
</html>