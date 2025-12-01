// js/carrito.js

document.addEventListener("DOMContentLoaded", () => {

    // Elementos del DOM
    const listaCarrito = document.getElementById("lista-carrito");
    const subtotalElem = document.getElementById("subtotal");
    const envioElem = document.getElementById("envio");
    const totalElem = document.getElementById("total");

    // Referencia al botón que ahora controlamos con JS
    const btnConfirmar = document.getElementById("btn-confirmar");

    // Cargar carrito al inicio
    cargarCarrito();

    /* ---------------------------------------------------------
       1. LÓGICA CONFIRMAR PEDIDO
    -----------------------------------------------------------*/
    if (btnConfirmar) {
        btnConfirmar.addEventListener("click", () => {

            // Validar que el carrito no esté vacío (Total > envío)
            const totalTexto = totalElem.textContent.replace('$', '').replace(',', '').trim();
            const totalValor = parseFloat(totalTexto);

            if (totalValor <= 40) {
                Swal.fire("Carrito vacío", "Agrega productos antes de confirmar.", "warning");
                return;
            }

            Swal.fire({
                title: '¿Confirmar pedido?',
                text: "Se procesará tu compra.",
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#006d3b',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Sí, comprar'
            }).then((result) => {
                if (result.isConfirmed) {
                    procesarCompra(); // Llama a la función que hace el fetch
                }
            });
        });
    }

    function procesarCompra() {
        // Ruta relativa desde php/carrito.php hacia php/pedidos/procesar_pedido.php
        fetch("pedidos/procesar_pedido.php", {
            method: "POST"
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Pedido Exitoso!',
                        text: 'Tu pedido #' + data.id_pedido + ' ha sido registrado.',
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Redirigir al menú
                        window.location.href = "../html/menu.php";
                    });
                } else {
                    Swal.fire("Error", data.message || "No se pudo procesar", "error");
                }
            })
            .catch(err => {
                console.error(err);
                Swal.fire("Error", "Error de conexión con el servidor", "error");
            });
    }

    /* ---------------------------------------------------------
       3. FUNCIONES CRUD DEL CARRITO (Mantenidas para la lógica)
    -----------------------------------------------------------*/
    function cargarCarrito() {
        fetch("carrito_crud.php?action=get")
            .then(res => res.json())
            .then(data => {
                if (!data.success || !data.carrito || data.carrito.length === 0) {
                    listaCarrito.innerHTML = "<p class='text-center'>Tu carrito está vacío 😔</p>";
                    actualizarTotales(0);
                    return;
                }
                renderizarProductos(data.carrito);
            })
            .catch(err => console.error("Error:", err));
    }

    function renderizarProductos(carrito) {
        listaCarrito.innerHTML = "";
        let subtotalCalculado = 0;

        carrito.forEach(p => {
            const totalProducto = parseFloat(p.precio) * parseInt(p.cantidad);
            subtotalCalculado += totalProducto;

            const imgPath = p.imagen && p.imagen !== "" ? p.imagen : "../src/imagenes/logo.png";

            listaCarrito.innerHTML += `
                <div class="cart-item" data-id="${p.id}">
                    <img src="${imgPath}" class="item-image" alt="Producto">
                    <div class="item-details">
                        <h2>${p.nombre}</h2>
                        <p class="item-price">$${parseFloat(p.precio).toFixed(2)}</p>
                        <button class="btn-eliminar remove-btn">Eliminar</button>
                    </div>
                    <div class="quantity-selector">
                        <button class="quantity-btn btn-restar">-</button>
                        <span class="quantity">${p.cantidad}</span>
                        <button class="quantity-btn btn-sumar">+</button>
                    </div>
                </div>
            `;
        });

        actualizarTotales(subtotalCalculado);
        asignarEventosItems();
    }

    function actualizarTotales(subtotal) {
        subtotalElem.textContent = `$${subtotal.toFixed(2)}`;
        const envio = 40.00;
        envioElem.textContent = `$${envio.toFixed(2)}`;
        const total = subtotal + envio;
        totalElem.textContent = `$${total.toFixed(2)}`;
    }

    function asignarEventosItems() {
        document.querySelectorAll(".btn-sumar").forEach(btn => {
            btn.onclick = (e) => modificarCantidad(e, 1);
        });
        document.querySelectorAll(".btn-restar").forEach(btn => {
            btn.onclick = (e) => modificarCantidad(e, -1);
        });
        document.querySelectorAll(".btn-eliminar").forEach(btn => {
            btn.onclick = (e) => eliminarProducto(e);
        });
    }

    function modificarCantidad(e, cambio) {
        const id = e.target.closest(".cart-item").dataset.id;
        const cantidadActual = parseInt(e.target.parentElement.querySelector(".quantity").textContent);
        const nuevaCantidad = cantidadActual + cambio;

        if (nuevaCantidad < 1) return;

        enviarActualizacion("update", id, nuevaCantidad);
    }

    function eliminarProducto(e) {
        const id = e.target.closest(".cart-item").dataset.id;
        Swal.fire({
            title: '¿Eliminar?',
            text: "El producto será removido del carrito.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar'
        }).then((result) => {
            if (result.isConfirmed) {
                enviarActualizacion("delete", id);
            }
        });
    }

    function enviarActualizacion(action, id, cantidad = 0) {
        fetch("carrito_crud.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ action, id_producto: id, cantidad })
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) cargarCarrito();
            });
    }
});