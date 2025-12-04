document.addEventListener("DOMContentLoaded", () => {

    // --- Selectores Principales ---
    const tablaBody = document.getElementById("productos-tbody");
    const btnAgregar = document.getElementById("btn-agregar-producto");
    const modal = document.getElementById("form-producto-modal");

    // --- Referencias a Elementos del Modal ---
    const closeModalButton = modal.querySelector(".close-button");
    const cancelModalButton = modal.querySelector(".btn-cancelar");
    const productoForm = document.getElementById("producto-form");
    const modalTitle = document.getElementById("modal-title");

    // Inputs
    const productoIdInput = document.getElementById("producto-id");
    const productoNombreInput = document.getElementById("producto-nombre");
    const productoDescripcionInput = document.getElementById("producto-descripcion");
    const productoPrecioInput = document.getElementById("producto-precio");
    const productoDescuentoInput = document.getElementById("producto-descuento");
    const productoStockInput = document.getElementById("producto-stock");
    const productoTamanoSelect = document.getElementById("producto-tamano");
    const productoClasificacionSelect = document.getElementById("producto-clasificacion");

    // Nuevos selectores para Imagen
    const productoImagenInput = document.getElementById("producto-imagen"); // Asegúrate de tener este ID en tu HTML
    const previewImagenDiv = document.getElementById("preview-imagen");     // Asegúrate de tener este ID en tu HTML

    // --- Funciones ---
    function clearFormFields() {
        productoIdInput.value = "";
        productoNombreInput.value = "";
        productoDescripcionInput.value = "";
        productoPrecioInput.value = "";
        productoDescuentoInput.value = "";
        productoStockInput.value = "";
        productoTamanoSelect.value = "";
        productoClasificacionSelect.value = "";

        // Limpiar imagen
        if (productoImagenInput) productoImagenInput.value = "";
        if (previewImagenDiv) previewImagenDiv.innerHTML = "";
    }

    function abrirModal() { modal.style.display = "block"; }
    function cerrarModal() {
        modal.style.display = "none";
        clearFormFields();
    }

    // --- Eventos ---
    closeModalButton.addEventListener("click", cerrarModal);
    cancelModalButton.addEventListener("click", cerrarModal);
    modal.addEventListener("click", (event) => { if (event.target === modal) cerrarModal(); });

    if (btnAgregar) btnAgregar.style.display = 'none';

    // --- Carga Inicial ---
    cargarClasificaciones();
    cargarProductos();

    // --- Funciones Asíncronas ---
    async function cargarProductos() {
        try {
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php?action=listar");
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const productos = await response.json();

            tablaBody.innerHTML = "";
            productos.forEach(p => {
                const tr = document.createElement("tr");
                const precioOrig = parseFloat(p.precio_unitario || 0);
                const descuento = parseInt(p.descuento_porcentaje || 0);

                let precioHtml = descuento > 0
                    ? `<span class="precio-original" style="text-decoration: line-through; color: #888;">$${precioOrig.toFixed(2)}</span> 
                       <span class="precio-descuento" style="font-weight: 700;">$${(precioOrig * (1 - descuento / 100)).toFixed(2)}</span>`
                    : `<span>$${precioOrig.toFixed(2)}</span>`;

                // URL de imagen por defecto si no existe
                const imgUrl = p.imagen_url && p.imagen_url !== "" ? p.imagen_url : "../../src/imagenes/logo.png";

                tr.innerHTML = `
                    <td>${p.id_producto}</td>
                    <td><img src="${imgUrl}" alt="Prod" style="width: 50px; height: 50px; object-fit: cover; border-radius: 5px;"></td>
                    <td>${p.nombre || 'N/A'}</td>
                    <td>${p.descripcion || 'N/A'}</td>
                    <td>${precioHtml}</td>
                    <td>${descuento}%</td>
                    <td>${p.tamaño || 'N/A'}</td>
                    <td>${p.nombre_clasificacion || 'N/A'}</td>
                    <td>${p.stock || 0}</td>
                    <td>
                        <button class="btn-action btn-edit" data-id="${p.id_producto}">Editar</button>
                        <button class="btn-action btn-view" data-id="${p.id_producto}">Visualizar</button>
                        <button class="btn-action btn-delete" data-id="${p.id_producto}" style="display: none;">Eliminar</button>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });
            asignarListenersBotones();
        } catch (error) {
            console.error("Error cargando productos:", error);
            tablaBody.innerHTML = `<tr><td colspan="10">Error al cargar: ${error.message}</td></tr>`;
        }
    }

    async function cargarClasificaciones() {
        try {
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php?action=listar_clasificaciones");
            if (!response.ok) throw new Error("Fallo en la respuesta HTTP al cargar clasificaciones");
            const clasificaciones = await response.json();

            productoClasificacionSelect.innerHTML = '<option value="">Selecciona...</option>';
            if (clasificaciones.length === 0) productoClasificacionSelect.innerHTML += '<option value="" disabled>No hay clasificaciones en la BD</option>';

            clasificaciones.forEach(c => {
                const option = document.createElement("option");
                option.value = c.id_clasificacion;
                option.textContent = c.nombre_clasificacion;
                productoClasificacionSelect.appendChild(option);
            });

        } catch (error) {
            console.error("Error cargando clasificaciones:", error);
            productoClasificacionSelect.innerHTML = '<option value="">Error al cargar</option>';
        }
    }

    productoForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = productoIdInput.value;
        const action = id ? "actualizar" : "crear";

        if (action === "crear") {
            Swal.fire({
                icon: 'error',
                title: 'Permiso Denegado',
                text: 'Solo los administradores pueden crear nuevos productos.',
            });
            return;
        }

        try {
            const formData = new FormData(productoForm);
            formData.append("action", action);
            formData.append("id_producto", id);

            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const respuesta = await response.text();

            if (respuesta.includes("❌ Error") || respuesta.includes("Acceso denegado")) {
                Swal.fire({ icon: 'error', title: 'Error de Servidor', text: respuesta });
                return;
            }

            Swal.fire({ icon: 'success', title: 'Operación exitosa', text: respuesta, timer: 1800, showConfirmButton: false });
            cerrarModal();
            cargarProductos();
        } catch (error) {
            console.error(`Error al ${action} producto:`, error);
            Swal.fire({ icon: 'error', title: 'Error de Conexión', text: `Error: ${error.message}` });
        }
    });

    // --- Botones ---
    function asignarListenersBotones() {
        tablaBody.querySelectorAll(".btn-edit").forEach(btn => btn.addEventListener("click", handleEditClick));
        tablaBody.querySelectorAll(".btn-delete").forEach(btn => {
            btn.addEventListener("click", () => Swal.fire('Permiso Denegado', 'Solo los administradores pueden eliminar productos.', 'error'));
        });
        tablaBody.querySelectorAll(".btn-view").forEach(btn => btn.addEventListener("click", handleViewClick));
    }

    async function handleEditClick(event) {
        const id = event.target.dataset.id;
        clearFormFields();

        try {
            const formData = new FormData();
            formData.append("action", "obtener");
            formData.append("id_producto", id);

            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error("Fallo en la respuesta HTTP al obtener producto");

            const p = await response.json();
            if (p && p.id_producto) {
                productoIdInput.value = p.id_producto || "";
                productoNombreInput.value = p.nombre || "";
                productoDescripcionInput.value = p.descripcion || "";
                productoPrecioInput.value = p.precio_unitario || 0;
                productoDescuentoInput.value = p.descuento_porcentaje || 0;
                productoStockInput.value = p.stock || 0;

                // Previsualizar Imagen
                if (previewImagenDiv) {
                    if (p.imagen_url) {
                        previewImagenDiv.innerHTML = `<img src="${p.imagen_url}" style="width: 100px; margin-top: 10px; border-radius: 5px;">`;
                    } else {
                        previewImagenDiv.innerHTML = '<small style="color: #888;">Sin imagen actual</small>';
                    }
                }

                // Esperar a que el navegador procese los selects
                setTimeout(() => {
                    productoTamanoSelect.value = p.tamaño || "";
                    productoClasificacionSelect.value = p.id_clasificacion || "";
                }, 0);

                modalTitle.textContent = "Editar Producto";
                abrirModal();
            } else {
                Swal.fire({ icon: 'info', title: 'Producto no encontrado', text: 'No se pudieron cargar los datos del producto seleccionado.' });
            }
        } catch (error) {
            console.error("Error al obtener producto para editar:", error);
            Swal.fire({ icon: 'error', title: 'Error al cargar', text: error.message });
        }
    }

    async function handleViewClick(event) {
        const id = event.target.dataset.id;
        try {
            const formData = new FormData();
            formData.append("action", "obtener");
            formData.append("id_producto", id);
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const p = await response.json();

            if (p) {
                const imgHtml = p.imagen_url
                    ? `<br><img src="${p.imagen_url}" style="width: 150px; border-radius: 8px; margin-bottom: 10px;">`
                    : '';

                Swal.fire({
                    title: p.nombre || "Producto sin nombre",
                    html: `
                        ${imgHtml}
                        <br>
                        <div style="text-align: left; margin-left: 20px;">
                            <b>ID:</b> ${p.id_producto}<br>
                            <b>Descripción:</b> ${p.descripcion || ''}<br>
                            <b>Precio:</b> $${parseFloat(p.precio_unitario || 0).toFixed(2)}<br>
                            <b>Descuento:</b> ${p.descuento_porcentaje || 0}%<br>
                            <b>Stock:</b> ${p.stock || 0}<br>
                            <b>Tamaño:</b> ${p.tamaño || ''}<br>
                            <b>Clasificación:</b> ${p.id_clasificacion || ''}
                        </div>
                    `,
                    icon: 'info'
                });
            } else {
                Swal.fire({ icon: 'info', title: 'Sin datos', text: 'No se encontraron datos del producto.' });
            }
        } catch (error) {
            console.error("Error al visualizar:", error);
            Swal.fire({ icon: 'error', title: 'Error al visualizar', text: error.message });
        }
    }

});