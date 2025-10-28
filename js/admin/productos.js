document.addEventListener("DOMContentLoaded", () => {

    // --- Selectores Principales ---
    const tablaBody = document.getElementById("productos-tbody");
    const btnAgregar = document.getElementById("btn-agregar-producto");

    // --- Crear Modal (solo una vez) ---
    const modal = crearModalProducto();
    document.body.appendChild(modal);

    // --- Referencias a Elementos del Modal ---
    const modalContent = modal.querySelector(".modal-content");
    const closeModalButton = modal.querySelector(".close-button");
    const cancelModalButton = modal.querySelector(".btn-cancelar");
    const productoForm = document.getElementById("producto-form");
    const modalTitle = document.getElementById("modal-title");
    const productoIdInput = document.getElementById("producto-id");
    const productoNombreInput = document.getElementById("producto-nombre");
    const productoDescripcionInput = document.getElementById("producto-descripcion");
    const productoPrecioInput = document.getElementById("producto-precio");
    const productoTamanoSelect = document.getElementById("producto-tamano");
    // No classification select needed

    // --- Funciones para Abrir/Cerrar Modal ---
    function abrirModal() { modal.style.display = "block"; }
    function cerrarModal() { modal.style.display = "none"; productoForm.reset(); }

    // --- Eventos del Modal ---
    closeModalButton.addEventListener("click", cerrarModal);
    cancelModalButton.addEventListener("click", cerrarModal);
    modal.addEventListener("click", (event) => { if (event.target === modal) cerrarModal(); });
    btnAgregar.addEventListener("click", () => {
        productoIdInput.value = "";
        modalTitle.textContent = "Agregar Producto";
        abrirModal();
    });

    // --- Carga Inicial de Datos ---
    cargarProductos();
    // No need to load classifications

    // --- Funciones Asíncronas ---

    async function cargarProductos() {
        try {
            const response = await fetch("../../php/admin/productos_crud.php?action=listar");
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const productos = await response.json();

            tablaBody.innerHTML = "";
            productos.forEach(p => {
                const tr = document.createElement("tr");
                // Removed classification cell
                tr.innerHTML = `
                    <td>${p.id_producto}</td>
                    <td>${p.nombre || 'N/A'}</td>
                    <td>${p.descripcion || 'N/A'}</td>
                    <td>$${parseFloat(p.precio_unitario || 0).toFixed(2)}</td>
                    <td>${p.tamaño || 'N/A'}</td>
                    <td>
                        <button class="btn-action btn-edit" data-id="${p.id_producto}">Editar</button>
                        <button class="btn-action btn-view" data-id="${p.id_producto}">Visualizar</button>
                        <button class="btn-action btn-delete" data-id="${p.id_producto}">Eliminar</button>
                    </td>
                `;
                tablaBody.appendChild(tr);
            });
            asignarListenersBotones();
        } catch (error) {
            console.error("Error cargando productos:", error);
            tablaBody.innerHTML = `<tr><td colspan="6">Error al cargar: ${error.message}</td></tr>`; // Colspan reduced
        }
    }

    productoForm.addEventListener("submit", async (e) => {
        e.preventDefault();
        const id = productoIdInput.value;
        const action = id ? "actualizar" : "crear";
        const formData = new FormData(productoForm);
        formData.append("action", action);
        formData.append("id_producto", id);
        // Classification is not appended

        try {
            const response = await fetch("../../php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const respuesta = await response.text();
            alert(respuesta);
            cerrarModal();
            cargarProductos();
        } catch (error) {
            console.error(`Error al ${action} producto:`, error);
            alert(`Error: ${error.message}`);
        }
    });

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
        try {
            const formData = new FormData();
            formData.append("action", "obtener");
            formData.append("id_producto", id);
            const response = await fetch("../../php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const p = await response.json();
            if (p) {
                productoIdInput.value = p.id_producto || "";
                productoNombreInput.value = p.nombre || "";
                productoDescripcionInput.value = p.descripcion || "";
                productoPrecioInput.value = p.precio_unitario || "";
                productoTamanoSelect.value = p.tamaño || "";
                // No classification field to set
                modalTitle.textContent = "Editar Producto";
                abrirModal();
            } else {
                alert("Producto no encontrado.");
            }
        } catch (error) {
            console.error("Error al obtener producto para editar:", error);
            alert(`Error: ${error.message}`);
        }
    }

    async function handleDeleteClick(event) {
        const id = event.target.dataset.id;
        if (confirm(`¿Estás seguro de eliminar el producto ID ${id}?`)) {
            const formData = new FormData();
            formData.append("action", "eliminar");
            formData.append("id_producto", id);
            try {
                const response = await fetch("../../php/admin/productos_crud.php", { method: "POST", body: formData });
                if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                const respuesta = await response.text();
                alert(respuesta);
                cargarProductos();
            } catch (error) {
                console.error("Error al eliminar:", error);
                alert(`Error: ${error.message}`);
            }
        }
    }

    async function handleViewClick(event) {
        const id = event.target.dataset.id;
        try {
            const formData = new FormData();
            formData.append("action", "obtener");
            formData.append("id_producto", id);
            const response = await fetch("../../php/admin/productos_crud.php", { method: "POST", body: formData });
            if (!response.ok) throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            const p = await response.json();
            if (p) {
                // Removed classification from alert
                alert(
                    `ID: ${p.id_producto}\n` +
                    `Nombre: ${p.nombre || ''}\n` +
                    `Descripción: ${p.descripcion || ''}\n` +
                    `Precio: $${parseFloat(p.precio_unitario || 0).toFixed(2)}\n` +
                    `Tamaño: ${p.tamaño || ''}`
                );
            } else {
                alert("No se encontraron datos.");
            }
        } catch (error) {
            console.error("Error al visualizar:", error);
            alert(`Error: ${error.message}`);
        }
    }

    // --- Función Helper para Crear el Modal ---
    function crearModalProducto() {
        const div = document.createElement("div");
        div.id = "form-producto-modal";
        div.className = "modal";
        div.style.display = "none";
        // Removed classification form group
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
                <div class="form-actions">
                    <button type="submit" class="btn-guardar">Guardar</button>
                    <button type="button" class="btn-cancelar">Cancelar</button>
                </div>
            </form>
        </div>`;
        return div;
    }
});