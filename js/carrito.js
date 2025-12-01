// js/carrito.js
// Asegúrate que este archivo esté en /js/ y que carrito.php lo cargue con ../js/carrito.js

document.addEventListener("DOMContentLoaded", () => {

    const listaCarrito = document.getElementById("lista-carrito");
    const subtotalElem = document.getElementById("subtotal");
    const envioElem = document.getElementById("envio");
    const totalElem = document.getElementById("total");

    cargarCarrito();

    /* ---------------------------------------------------------
       1. CARGAR PRODUCTOS DEL CARRITO
    -----------------------------------------------------------*/
    function cargarCarrito() {
        // Desde carrito.php (ubicado en /php/) la ruta relativa al CRUD es "carrito_crud.php"
        fetch("carrito_crud.php?action=get")
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) {
                    // Para debug: mostrar en consola
                    console.warn("carrito_crud respuesta inválida:", data);
                    listaCarrito.innerHTML = "<p>Tu carrito está vacío.</p>";
                    subtotalElem.textContent = "$0.00";
                    totalElem.textContent = "$40.00";
                    return;
                }

                const carrito = data.carrito || [];
                if (carrito.length === 0) {
                    listaCarrito.innerHTML = "<p>Tu carrito está vacío.</p>";
                    subtotalElem.textContent = "$0.00";
                    totalElem.textContent = "$40.00";
                    return;
                }

                listaCarrito.innerHTML = "";
                let subtotal = 0;

                carrito.forEach(p => {
                    let totalProducto = parseFloat(p.precio) * parseInt(p.cantidad);
                    subtotal += totalProducto;

                    listaCarrito.innerHTML += `
                        <div class="cart-item" data-id="${p.id}">
                            <img src="${p.imagen || 'https://via.placeholder.com/100'}" class="item-image" alt="${escapeHtml(p.nombre)}">

                            <div class="item-details">
                                <h2>${escapeHtml(p.nombre)}</h2>
                                <p class="item-price">$${Number(p.precio).toFixed(2)}</p>
                                <button class="btn btn-sm btn-outline-danger remove-btn btn-eliminar">Eliminar</button>
                            </div>

                            <div class="quantity-selector">
                                <button class="btn btn-sm btn-secondary quantity-btn btn-restar">-</button>
                                <span class="quantity">${p.cantidad}</span>
                                <button class="btn btn-sm btn-secondary quantity-btn btn-sumar">+</button>
                            </div>
                        </div>
                    `;
                });

                subtotalElem.textContent = "$" + subtotal.toFixed(2);

                let envio = parseFloat(envioElem.textContent.replace(/[^0-9.-]+/g, "")) || 40;
                let total = subtotal + envio;

                totalElem.textContent = "$" + total.toFixed(2);

                activarBotones();
            })
            .catch(err => {
                console.error("Error cargando carrito:", err);
                listaCarrito.innerHTML = "<p>Error cargando carrito.</p>";
            });
    }

    /* ---------------------------------------------------------
       2. BOTONES (sumar, restar, eliminar)
    -----------------------------------------------------------*/
    function activarBotones() {

        // Eliminar producto
        document.querySelectorAll(".btn-eliminar").forEach(btn => {
            btn.removeEventListener("click", handlerEliminar); // evitar duplicados
            btn.addEventListener("click", handlerEliminar);
        });

        // Sumar
        document.querySelectorAll(".btn-sumar").forEach(btn => {
            btn.removeEventListener("click", handlerSumar);
            btn.addEventListener("click", handlerSumar);
        });

        // Restar
        document.querySelectorAll(".btn-restar").forEach(btn => {
            btn.removeEventListener("click", handlerRestar);
            btn.addEventListener("click", handlerRestar);
        });
    }

    function handlerEliminar(e) {
        const id = e.target.closest(".cart-item").dataset.id;
        // confirmación opcional
        if (!confirm("¿Eliminar este producto del carrito?")) return;
        eliminarProducto(id);
    }

    function handlerSumar(e) {
        const card = e.target.closest(".cart-item");
        const id = card.dataset.id;
        const cantidad = parseInt(card.querySelector(".quantity").textContent) + 1;
        actualizarCantidad(id, cantidad);
    }

    function handlerRestar(e) {
        const card = e.target.closest(".cart-item");
        const id = card.dataset.id;
        let cantidad = parseInt(card.querySelector(".quantity").textContent) - 1;
        if (cantidad <= 0) {
            // confirmar eliminación si llega a 0
            if (!confirm("La cantidad llegará a 0. ¿Eliminar producto?")) return;
            eliminarProducto(id);
            return;
        }
        actualizarCantidad(id, cantidad);
    }

    /* ---------------------------------------------------------
       3. UPDATE CANTIDAD
    -----------------------------------------------------------*/
    function actualizarCantidad(id, cantidad) {
        fetch("carrito_crud.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "update",
                id_producto: id,
                cantidad: cantidad
            })
        })
            .then(res => res.json())
            .then(data => {
                if (!data || !data.success) {
                    mostrarAlerta(data?.message || "No se pudo actualizar la cantidad", "danger");
                    return;
                }
                cargarCarrito();
            })
            .catch(err => {
                console.error("Error actualizar cantidad:", err);
                mostrarAlerta("Error de red al actualizar", "danger");
            });
    }

    /* ---------------------------------------------------------
       4. ELIMINAR PRODUCTO
    -----------------------------------------------------------*/
    function eliminarProducto(id) {
        fetch("carrito_crud.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                action: "delete",
                id_producto: id
            })
        })
            .then(res => res.json())
            .then(data => {
                if (data && data.success) {
                    mostrarAlerta(data.message || "Producto eliminado", "danger");
                } else {
                    mostrarAlerta(data?.message || "No se pudo eliminar", "danger");
                }
                cargarCarrito();
            })
            .catch(err => {
                console.error("Error eliminando producto:", err);
                mostrarAlerta("Error eliminando producto", "danger");
            });
    }

    /* ---------------------------------------------------------
       5. UTIL: mostrar alerta con bootstrap fade
    -----------------------------------------------------------*/
    function mostrarAlerta(msg, tipo = "success") {
        const alerta = document.getElementById("alerta");
        if (!alerta) {
            console.warn("No existe #alerta en el DOM");
            return;
        }

        // Forzar texto y clases bootstrap
        alerta.textContent = msg;
        alerta.className = `alert alert-${tipo} fade show`;
        alerta.style.display = "block";

        // Ocultar después de 2s con animación
        setTimeout(() => {
            alerta.classList.remove("show");
            setTimeout(() => alerta.style.display = "none", 300);
        }, 2000);
    }

    /* ---------------------------------------------------------
       6. UTIL: escape para texto mostrado
    -----------------------------------------------------------*/
    function escapeHtml(text) {
        if (!text) return "";
        return text.replace(/[&<>"']/g, function (m) {
            return ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[m];
        });
    }

});
