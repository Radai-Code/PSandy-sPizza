document.addEventListener('DOMContentLoaded', () => {

    // --- FILTROS ---
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            const category = button.dataset.filter;
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            productCards.forEach(card => {
                const cardCategory = card.dataset.category;
                card.style.display = (category === 'todos' || category === cardCategory) ? 'flex' : 'none';
            });
        });
    });

    // --- MODAL PEDIDO DIRECTO ---
    const modalPedido = document.getElementById("modal-pedido-directo");
    if (modalPedido) {
        const modalNombre = document.getElementById("modal-producto-nombre");
        const modalPrecio = document.getElementById("modal-producto-precio");
        const modalStock = document.getElementById("modal-producto-stock");
        const modalIdInput = document.getElementById("modal-producto-id");
        const modalCantidadInput = document.getElementById("modal-cantidad");
        const btnCerrarModal = document.querySelector(".close-pedido-modal");
        const formPedidoDirecto = document.getElementById("form-pedido-directo");
        const botonesPedir = document.querySelectorAll(".btn-pedir-directo");

        botonesPedir.forEach(boton => {
            boton.addEventListener("click", () => {
                const id = boton.dataset.id;
                const nombre = boton.dataset.nombre;
                const precio = parseFloat(boton.dataset.precio).toFixed(2);
                const stock = parseInt(boton.dataset.stock);

                modalNombre.textContent = nombre;
                modalPrecio.textContent = `$${precio}`;
                modalStock.textContent = stock;
                modalIdInput.value = id;
                modalCantidadInput.max = stock;
                modalCantidadInput.value = 1;

                modalPedido.style.display = "block";
            });
        });

        btnCerrarModal.addEventListener("click", () => {
            modalPedido.style.display = "none";
        });

        formPedidoDirecto.addEventListener("submit", (e) => {
            e.preventDefault();
            modalPedido.style.display = "none";
            Swal.fire({
                icon: 'success',
                title: 'Pedido confirmado',
                text: 'Tu pedido ha sido registrado correctamente.',
                timer: 1800,
                showConfirmButton: false
            });
        });
    }

    // --- AGREGAR AL CARRITO ---
    window.agregarAlCarrito = function (idProducto) {
        fetch('../php/carrito_crud.php', {
            method: 'POST',
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({
                action: "add",
                id_producto: idProducto,
                cantidad: 1
            })
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Producto agregado',
                        text: data.message,
                        timer: 1500,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message
                    });
                }
            })
            .catch(error => {
                console.error("Error al agregar al carrito:", error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo agregar el producto al carrito'
                });
            });
    }

});
