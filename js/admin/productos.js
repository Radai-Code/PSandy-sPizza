document.addEventListener("DOMContentLoaded", () => {

    // --- Selectores Principales ---
    const tablaBody = document.getElementById("productos-tbody");
    const btnAgregar = document.getElementById("btn-agregar-producto");

    // --- Crear Modal (solo una vez) ---
    const modal = crearModalProducto();
    document.body.appendChild(modal);

    // --- Referencias a Elementos del Modal ---
    const closeModalButton = modal.querySelector(".close-button");
    const cancelModalButton = modal.querySelector(".btn-cancelar");
    const productoForm = document.getElementById("producto-form");
    const modalTitle = document.getElementById("modal-title");
    const productoIdInput = document.getElementById("producto-id");
    const productoNombreInput = document.getElementById("producto-nombre");
    const productoDescripcionInput = document.getElementById("producto-descripcion");
    const productoPrecioInput = document.getElementById("producto-precio");
    const productoDescuentoInput = document.getElementById("producto-descuento");
    const productoStockInput = document.getElementById("producto-stock");
    const productoTamanoSelect = document.getElementById("producto-tamano");
    const productoClasificacionSelect = document.getElementById("producto-clasificacion");

    // --- Funciones para Abrir/Cerrar Modal ---
    function clearFormFields() {
        productoIdInput.value = "";
        productoNombreInput.value = "";
        productoDescripcionInput.value = "";
        productoPrecioInput.value = "";
        productoDescuentoInput.value = "";
        productoStockInput.value = "";
        productoTamanoSelect.value = "";
        productoClasificacionSelect.value = "";
    }

    function abrirModal() { modal.style.display = "block"; }
    function cerrarModal() {
        modal.style.display = "none";
        clearFormFields();
    }

    // --- Eventos del Modal ---
    closeModalButton.addEventListener("click", cerrarModal);
    cancelModalButton.addEventListener("click", cerrarModal);
    modal.addEventListener("click", (event) => { if (event.target === modal) cerrarModal(); });

    // Ocultar botón Agregar
    if (btnAgregar) {
        btnAgregar.style.display = 'none';
    }

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
                let precioHtml = '';

                if (descuento > 0) {
                    const precioNuevo = precioOrig * (1 - (descuento / 100));
                    precioHtml = `<span class="precio-original" style="text-decoration: line-through; color: #888;">$${precioOrig.toFixed(2)}</span> <span class="precio-descuento" style="font-weight: 700;">$${precioNuevo.toFixed(2)}</span>`;
                } else {
                    precioHtml = `<span>$${precioOrig.toFixed(2)}</span>`;
                }

                tr.innerHTML = `
                    <td>${p.id_producto}</td>
                    <td>${p.nombre || 'N/A'}</td>
                    <td>${p.descripcion || 'N/A'}</td>
                    <td>${precioHtml}</td>
                    <td>${p.descuento_porcentaje || 0}%</td>
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
            tablaBody.innerHTML = `<tr><td colspan="9">Error al cargar: ${error.message}</td></tr>`;
        }
    }

    async function cargarClasificaciones() {
        try {
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php?action=listar_clasificaciones");
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
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
        const formData = new FormData(productoForm);
        formData.append("action", action);
        formData.append("id_producto", id);

        if (action === "crear") {
            Swal.fire({
                icon: 'error',
                title: 'Permiso Denegado',
                text: 'Solo los administradores pueden crear nuevos productos.',
            });
            return;
        }

        try {
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const respuesta = await response.text();

            if (respuesta.includes("❌ Error") || respuesta.includes("Acceso denegado")) {
                Swal.fire({ icon: 'error', title: 'Error de Servidor', text: respuesta });
                return;
            }

            Swal.fire({
                icon: 'success',
                title: 'Operación exitosa',
                text: respuesta,
                timer: 1800,
                showConfirmButton: false
            });

            cerrarModal();
            cargarProductos();
        } catch (error) {
            console.error(`Error al ${action} producto:`, error);
            Swal.fire({
                icon: 'error',
                title: 'Error de Conexión',
                text: `Error: ${error.message}`,
            });
        }
    });

    // --- Botones de Acción ---
    function asignarListenersBotones() {
        tablaBody.querySelectorAll(".btn-edit").forEach(btn => {
            btn.removeEventListener('click', handleEditClick);
            btn.addEventListener("click", handleEditClick);
        });
        tablaBody.querySelectorAll(".btn-delete").forEach(btn => {
            btn.removeEventListener('click', handleDeleteClick);
            btn.addEventListener("click", handleDeleteClick);
        });
        tablaBody.querySelectorAll(".btn-view").forEach(btn => {
            btn.removeEventListener('click', handleViewClick);
            btn.addEventListener("click", handleViewClick);
        });
    }

    async function handleEditClick(event) {
        const id = event.target.dataset.id;
        clearFormFields();

        try {
            const formData = new FormData();
            formData.append("action", "obtener");
            formData.append("id_producto", id);
            const response = await fetch("/PSandy-sPizza/php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const p = await response.json();
            if (p) {
                productoIdInput.value = p.id_producto || "";
                productoNombreInput.value = p.nombre || "";
                productoDescripcionInput.value = p.descripcion || "";
                productoPrecioInput.value = p.precio_unitario || "";
                productoDescuentoInput.value = p.descuento_porcentaje || 0;
                productoStockInput.value = p.stock || 0;
                productoTamanoSelect.value = p.tamaño || "";
                productoClasificacionSelect.value = p.id_clasificacion || "";
                modalTitle.textContent = "Editar Producto";
                abrirModal();
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Producto no encontrado',
                    text: 'No se pudieron cargar los datos del producto seleccionado.',
                });
            }
        } catch (error) {
            console.error("Error al obtener producto para editar:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error al cargar',
                text: error.message
            });
        }
    }

    async function handleDeleteClick(event) {
        Swal.fire('Permiso Denegado', 'Solo los administradores pueden eliminar productos.', 'error');
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
                Swal.fire({
                    title: p.nombre || "Producto sin nombre",
                    html: `
                        <b>ID:</b> ${p.id_producto}<br>
                        <b>Descripción:</b> ${p.descripcion || ''}<br>
                        <b>Precio:</b> $${parseFloat(p.precio_unitario || 0).toFixed(2)}<br>
                        <b>Descuento:</b> ${p.descuento_porcentaje || 0}%<br>
                        <b>Stock:</b> ${p.stock || 0}<br>
                        <b>Tamaño:</b> ${p.tamaño || ''}<br>
                        <b>Clasificación:</b> ${p.id_clasificacion || ''}
                    `,
                    icon: 'info'
                });
            } else {
                Swal.fire({
                    icon: 'info',
                    title: 'Sin datos',
                    text: 'No se encontraron datos del producto.'
                });
            }
        } catch (error) {
            console.error("Error al visualizar:", error);
            Swal.fire({
                icon: 'error',
                title: 'Error al visualizar',
                text: error.message
            });
        }
    }

    // --- Crear Modal ---
    function crearModalProducto() {
        const div = document.createElement("div");
        div.id = "form-producto-modal";
        div.className = "modal";
        div.style.display = "none";
        div.innerHTML = `
        <div class="modal-content">
            <span class="close-button">&times;</span>
            <h3 id="modal-title">Agregar Producto</h3>
            <form id="producto-form"> 
                <input type="hidden" id="producto-id" name="id_producto_hidden">
                <div class="form-group">
                    <label for="producto-nombre">Nombre:</label>
                    <input type="text" id="producto-nombre" name="nombre" required>
                </div>
                <div class="form-group">
                    <label for="producto-descripcion">Descripción:</label>
                    <textarea id="producto-descripcion" name="descripcion" required></textarea>
                </div>
                <div class="form-group">
                    <label for="producto-precio">Precio Unitario:</label>
                    <input type="number" step="0.01" id="producto-precio" name="precio_unitario" required>
                </div>
                <div class="form-group">
                    <label for="producto-descuento">Descuento (%):</label>
                    <input type="number" id="producto-descuento" name="descuento_porcentaje" min="0" max="100" value="0">
                </div>
                <div class="form-group">
                    <label for="producto-stock">Stock (Disponibles):</label>
                    <input type="number" id="producto-stock" name="stock" min="0" value="0">
                </div>
                <div class="form-group">
                    <label for="producto-tamano">Tamaño:</label>
                    <select id="producto-tamano" name="tamaño" required>
                        <option value="">Selecciona...</option>
                        <option value="chica">Chica</option>
                        <option value="mediana">Mediana</option>
                        <option value="grande">Grande</option>
                        <option value="gigante">Gigante</option>
                        <option value="familiar">Familiar</option>
                    </select>
                </div>
                <div class="form-group">
                    <label for="producto-clasificacion">Clasificación:</label>
                    <select id="producto-clasificacion" name="id_clasificacion" required>
                        <option value="">Cargando...</option>
                    </select>
                </div>
                <div class="form-actions">
                    <button type="submit" class="btn-guardar">Guardar</button>
                    <button type="button" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>`;
        return div;
    }
});