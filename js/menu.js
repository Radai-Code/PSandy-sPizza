// Espera a que todo el contenido del HTML se cargue antes de ejecutar el script
document.addEventListener('DOMContentLoaded', () => {

    // 1. Seleccionar todos los elementos necesarios del DOM
    const filterButtons = document.querySelectorAll('.filter-btn');
    const productCards = document.querySelectorAll('.product-card');

    // 2. Añadir un "escuchador" de clics a cada botón de filtro
    filterButtons.forEach(button => {
        button.addEventListener('click', () => {
            // Obtener la categoría del atributo 'data-filter' del botón presionado
            const category = button.dataset.filter;

            // Actualizar el estilo del botón activo
            // Primero, se lo quitamos a todos
            filterButtons.forEach(btn => btn.classList.remove('active'));
            // Luego, se lo añadimos solo al que se hizo clic
            button.classList.add('active');

            // 3. Filtrar las tarjetas de producto
            productCards.forEach(card => {
                const cardCategory = card.dataset.category;

                // Comprobar si la tarjeta debe mostrarse
                if (category === 'todos' || category === cardCategory) {
                    card.style.display = 'flex'; // 'flex' porque las tarjetas usan flexbox
                } else {
                    card.style.display = 'none'; // Ocultar la tarjeta si no coincide
                }
            });
        });
    });
});