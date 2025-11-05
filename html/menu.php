<?php
// 1. INCLUIR LA CONEXIÓN A LA BASE DE DATOS Y EMPEZAR LA SESIÓN
require_once '../php/conexion.php'; // Asegúrate de que la ruta a tu conexión sea correcta
session_start();

// 2. CONSULTAR TODOS LOS PRODUCTOS DE LA BASE DE DATOS
$sql_productos = "SELECT p.*, c.nombre_clasificacion 
                  FROM Producto p
                  LEFT JOIN Clasificacion c ON p.id_clasificacion = c.id_clasificacion
                  ORDER BY p.nombre";

$resultado_productos = mysqli_query($conexion, $sql_productos);
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
            
            <?php if (isset($_SESSION['user_id'])): // Lógica de sesión del cliente ?>
                
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
            <button class="filter-btn" data-filter="refrescos">Refrescos</button>
        </div>

        <div class="product-grid">

            <?php
            if ($resultado_productos && mysqli_num_rows($resultado_productos) > 0):
                while ($producto = mysqli_fetch_assoc($resultado_productos)):
                    
                    $categoria_filtro = strtolower($producto['nombre_clasificacion'] ?? 'otros');
            ?>
            
            <div class="product-card" data-category="<?php echo htmlspecialchars($categoria_filtro); ?>">
                <form action="../php/agregar_al_carrito.php" method="post">
                    
                    <div class="product-info">
                        <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                        <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                        
                        <span class="price">
                        <?php
                        $precio_original = (float)$producto['precio_unitario'];
                        $descuento = (int)$producto['descuento_porcentaje'];

                        if ($descuento > 0) {
                            $precio_nuevo = $precio_original * (1 - ($descuento / 100));
                            // Mostrar precio original tachado y el nuevo precio
                            echo '<del class="precio-original">$'.number_format($precio_original, 2).'</del> ';
                            echo '<strong class="precio-nuevo">$'.number_format($precio_nuevo, 2).'</strong>';
                        } else {
                            // Mostrar solo el precio normal
                            echo '$'.number_format($precio_original, 2);
                        }
                        ?>
                        </span>
                        </div>

                    <input type="hidden" name="id_producto" value="<?php echo $producto['id_producto']; ?>">
                    <button type="submit" class="add-to-cart-btn">Agregar al Carrito</button>
                </form>
            </div>

            <?php
                endwhile; 
            else:
                echo "<p>No hay productos disponibles en este momento.</p>";
            endif;
            
            mysqli_close($conexion);
            ?>
            </div>
    </main>

    <script src="../js/menu.js"></script>
</body>
</html>