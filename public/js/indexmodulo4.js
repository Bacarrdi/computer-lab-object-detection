document.addEventListener("DOMContentLoaded", () => {
    const input = document.getElementById("input-file");
    const preview = document.getElementById("preview");

    let imageUrl = null;

    function clearPreview() {
        if (imageUrl) {
            URL.revokeObjectURL(imageUrl);
            imageUrl = null;
        }

        preview.replaceChildren();
    }

    input.addEventListener("change", () => {
        clearPreview();

        const file = input.files[0];

        if (!file) return;

        const validTypes = ["image/jpeg", "image/png"];
        const maxSize = 10 * 1024 * 1024;

        if (!validTypes.includes(file.type) || file.size > maxSize) {
            input.value = "";
            alert("Selecciona una imagen JPG o PNG de máximo 10 MB.");
            return;
        }

        imageUrl = URL.createObjectURL(file);

        const card = document.createElement("div");
        card.className = "m4-file-preview";

        const image = document.createElement("img");
        image.src = imageUrl;
        image.alt = "Vista previa de la imagen seleccionada";

        const details = document.createElement("div");
        details.className = "m4-file-details";

        const name = document.createElement("strong");
        name.textContent = file.name;

        const status = document.createElement("p");
        status.className = "m4-ready";
        status.textContent = "✓ Imagen seleccionada, lista para procesar";

        const size = document.createElement("small");
        size.textContent = (file.size / 1024 / 1024).toFixed(2) + " MB";

        const remove = document.createElement("button");
        remove.type = "button";
        remove.className = "m4-remove";
        remove.textContent = "Quitar imagen";

        remove.addEventListener("click", () => {
            input.value = "";
            clearPreview();
        });

        details.append(name, status, size, remove);
        card.append(image, details);
        preview.appendChild(card);
    });

    const priorities = [
        ...document.querySelectorAll("select[name='prioridad[]']")
    ];

    function updatePriorities() {
        const selected = priorities
            .map(select => select.value)
            .filter(Boolean);

        priorities.forEach(select => {
            [...select.options].forEach(option => {
                option.disabled =
                    option.value !== "" &&
                    option.value !== select.value &&
                    selected.includes(option.value);
            });
        });
    }

    priorities.forEach(select => {
        select.addEventListener("change", updatePriorities);
    });

    updatePriorities();
});
