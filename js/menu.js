document.addEventListener('DOMContentLoaded', () => {

    // --- LÓGICA DE FILTROS ---
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Obtener la categoría del botón
            const category = button.dataset.filter;

            // Actualizar el botón activo
            filterButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');

            // Filtrar las tarjetas de producto
            productCards.forEach(card => {
                const cardCategory = card.dataset.category;

                if (category === 'todos' || category === cardCategory) {
                    card.style.display = 'flex'; // 'flex' porque las tarjetas usan flexbox
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });

    // --- LÓGICA PARA EL MODAL DE PEDIDO DIRECTO ---
    const modalPedido = document.getElementById("modal-pedido-directo");

    // Es posible que el modal no exista si el usuario no ha iniciado sesión
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
            // alert("Función de pedido directo aún no implementada.");
            modalPedido.style.display = "none";
        });
    }
}); 