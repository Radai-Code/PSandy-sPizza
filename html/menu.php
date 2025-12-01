<?php
// 1. INCLUIR LA CONEXIÓN Y EMPEZAR LA SESIÓN
require_once '../php/conexion.php'; 
session_start();

// 2. CONSULTAR TODOS LOS PRODUCTOS
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
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="../src/css/menu.css">
    <link rel="icon" type="image/png" href="../src/imagenes/logo.png">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>

    <header class="menu-header">
        <a href="index.html" class="brand-logo">Sandy's Pizzas</a>
        <nav class="main-nav">
            <a href="../php/carrito.php" class="cart-link">🛒 Mi Carrito</a>
             <a href="../php/pedidos/mis_pedidos.php" class="btn-mis-pedidos">Mis pedidos</a>
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
            <button class="filter-btn" data-filter="refrescos">Refrescos</button>
        </div>

        <div class="product-grid">
            <?php
            if ($resultado_productos && mysqli_num_rows($resultado_productos) > 0):
                while ($producto = mysqli_fetch_assoc($resultado_productos)):
                    $categoria_filtro = strtolower($producto['nombre_clasificacion'] ?? 'otros');
            ?>
            
            <div class="product-card" data-category="<?php echo htmlspecialchars($categoria_filtro); ?>">
                <div class="product-info">
                    <h2><?php echo htmlspecialchars($producto['nombre']); ?></h2>
                    <p><?php echo htmlspecialchars($producto['descripcion']); ?></p>
                    
                    <span class="price">
                    <?php
                    $precio_original = (float)$producto['precio_unitario'];
                    $descuento = (int)$producto['descuento_porcentaje'];
                    $precio_final = $precio_original;

                    if ($descuento > 0) {
                        $precio_final = $precio_original * (1 - ($descuento / 100));
                        echo '<del class="precio-original">$'.number_format($precio_original, 2).'</del> ';
                        echo '<strong class="precio-nuevo">$'.number_format($precio_final, 2).'</strong>';
                    } else {
                        echo '$'.number_format($precio_original, 2);
                    }
                    ?>
                    </span>
                </div>

                <?php if (isset($_SESSION['user_id'])): ?>
                    <button type="button" class="add-to-cart-btn btn-pedir-directo"
                            data-id="<?php echo $producto['id_producto']; ?>"
                            data-nombre="<?php echo htmlspecialchars($producto['nombre']); ?>"
                            data-precio="<?php echo $precio_final; ?>"
                            data-stock="<?php echo $producto['stock']; ?>">
                        Pedir Ahora
                    </button>
                    <button type="button" class="btn-agregar-carrito"
                      onclick="agregarAlCarrito(<?php echo $producto['id_producto']; ?>)">
                        🛒 Agregar al Carrito
                    </button>
                   

                <?php else: ?>
                    <a href="login.html" class="add-to-cart-btn btn-login-para-pedir">
                        Inicia sesión para Pedir
                    </a>
                <?php endif; ?>

            </div>

            <?php
                endwhile; 
            else:
                echo "<p>No hay productos disponibles.</p>";
            endif;
            mysqli_close($conexion);
            ?>
        </div>
    </main>

    <!-- MODAL -->
    <div id="modal-pedido-directo" class="modal-pedido">
        <div class="modal-pedido-content">
            <span class="close-pedido-modal">&times;</span>
            <h2 id="modal-producto-nombre">Nombre del Producto</h2>
            
            <form id="form-pedido-directo">
                <input type="hidden" id="modal-producto-id" name="id_producto">

                <div class="info-row">
                    <strong>Precio Final:</strong>
                    <span id="modal-producto-precio">$0.00</span>
                </div>
                <div class="info-row">
                    <strong>Disponibles:</strong>
                    <span id="modal-producto-stock">0</span>
                </div>

                <hr>

                <div class="form-group-pedido">
                    <label for="modal-promo-codigo">Código de Promoción:</label>
                    <input type="text" id="modal-promo-codigo" name="promo_codigo" placeholder="Ej: BIENVENIDO10">
                </div>

                <div class="form-group-pedido">
                    <label for="modal-cantidad">Cantidad:</label>
                    <input type="number" id="modal-cantidad" name="cantidad" value="1" min="1">
                </div>
                
                <button type="submit" class="btn-confirmar-pedido">Confirmar Pedido</button>
            </form>
        </div>
    </div>

    <script src="../js/menu.js"></script>

    <!-- Confirmación de pedido con diseño minimalista -->
    <script>
    document.addEventListener("DOMContentLoaded", () => {
        const formPedido = document.getElementById("form-pedido-directo");
        if (formPedido) {
            formPedido.addEventListener("submit", (e) => {
                e.preventDefault();

                Swal.fire({
                    title: "¿Confirmar pedido?",
                    text: "Una vez confirmado no podrás modificarlo.",
                    icon: "question",
                    showCancelButton: true,
                    confirmButtonText: "Sí, confirmar",
                    cancelButtonText: "Cancelar",
                    reverseButtons: true,
                    background: "#fff",
                    color: "#333",
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            icon: "success",
                            title: "Pedido confirmado",
                            text: "Tu pedido ha sido registrado correctamente.",
                            timer: 1800,
                            showConfirmButton: false
                        });
                        // Aquí puedes hacer el envío real con fetch o AJAX si quieres registrar el pedido en la BD
                    }
                });
            });
        }
    });
    </script>
</body>
</html>
