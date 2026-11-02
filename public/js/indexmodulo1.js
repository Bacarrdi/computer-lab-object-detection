document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("input-file");
    const preview = document.getElementById("preview");

    if (!input || !preview) return;

    let imageUrl = null;

    function limpiarPreview() {
        if (imageUrl) {
            URL.revokeObjectURL(imageUrl);
            imageUrl = null;
        }

        preview.replaceChildren();
    }

    input.addEventListener("change", () => {
        limpiarPreview();

        const file = input.files[0];
        if (!file) return;

        const tiposValidos = ["image/jpeg", "image/png"];

        if (!tiposValidos.includes(file.type) ||
            file.size > 10 * 1024 * 1024) {
            alert("Selecciona una imagen JPG o PNG de máximo 10 MB.");
            input.value = "";
            return;
        }

        imageUrl = URL.createObjectURL(file);

        const contenedor = document.createElement("div");
        contenedor.className = "file-container";

        const imagen = document.createElement("img");
        imagen.src = imageUrl;
        imagen.alt = "Vista previa";
        imagen.className = "preview-image-m1";

        const detalles = document.createElement("div");
        detalles.className = "file-details-m1";

        const nombre = document.createElement("strong");
        nombre.textContent = file.name;

        const tamano = document.createElement("small");
        tamano.textContent =
            (file.size / 1024 / 1024).toFixed(2) + " MB";

        const estado = document.createElement("span");
        estado.className = "status-text success";
        estado.textContent = "✓ Imagen seleccionada, lista para procesar";

        const eliminar = document.createElement("button");
        eliminar.type = "button";
        eliminar.className = "delete-btn";
        eliminar.textContent = "Quitar imagen";

        eliminar.addEventListener("click", () => {
            input.value = "";
            limpiarPreview();
        });

        detalles.append(nombre, estado, tamano);
        contenedor.append(imagen, detalles, eliminar);
        preview.appendChild(contenedor);
    });
});
