import os
import sys
import shutil
from collections import Counter

import cv2
from ultralytics import YOLO


BASE_DIR = os.path.dirname(
    os.path.dirname(os.path.abspath(__file__))
)

modelo_path = os.path.join(BASE_DIR, "archivos_yolo", "my_model", "my_model.pt")
ruta_subidos = os.path.join(BASE_DIR, "archivos_yolo", "archivos_subidos")
ruta_resultados = os.path.join(BASE_DIR, "archivos_yolo", "resultados")
ruta_publica = os.path.join(BASE_DIR, "public", "archivos_yolo", "resultados")


def detectar(nombre_archivo):
    path_entrada = os.path.join(ruta_subidos, nombre_archivo)
    nombre_salida = f"resultado_{nombre_archivo}"

    path_salida = os.path.join(ruta_resultados, nombre_salida)
    path_publico = os.path.join(ruta_publica, nombre_salida)

    if not os.path.isfile(path_entrada):
        raise FileNotFoundError(f"Archivo no encontrado: {path_entrada}")

    imagen = cv2.imread(path_entrada)

    if imagen is None:
        raise ValueError("No se pudo cargar la imagen.")

    modelo = YOLO(modelo_path)
    resultados = modelo(path_entrada, device="cpu", verbose=False)[0]

    # Colores por clase
    colores = {
        "monitor": (156, 99, 46),
        "silla": (95, 87, 150),
        "teclado": (53, 132, 176),
        "mouse": (124, 116, 50),
        "persona": (74, 132, 84),
        "laptop": (152, 82, 120),
        "torre": (75, 95, 163),
    }

    conteo = Counter()
    alto_imagen, ancho_imagen = imagen.shape[:2]

    grosor_caja = max(3, min(8, round(ancho_imagen / 240)))

    for box in resultados.boxes:
        clase = int(box.cls[0])
        nombre_clase = modelo.names[clase]
        confianza = float(box.conf[0])

        conteo[nombre_clase] += 1

        x1, y1, x2, y2 = map(int, box.xyxy[0])
        color = colores.get(nombre_clase, (100, 100, 100))

        cv2.rectangle(
            imagen, (x1, y1), (x2, y2), color, grosor_caja
        )

        etiqueta = f"{nombre_clase} {confianza:.2f}"
        fuente = cv2.FONT_HERSHEY_SIMPLEX
        escala = max(0.95, min(1.05, ancho_imagen / 2100))
        grosor = 3

        (ancho, alto), _ = cv2.getTextSize(
            etiqueta, fuente, escala, grosor
        )

        x = max(0, min(x1, ancho_imagen - ancho - 8))
        y = y1 - alto - 8 if y1 >= alto + 8 else y1 + 3
        y = max(0, min(y, alto_imagen - alto - 8))

        cv2.rectangle(
            imagen,
            (x, y),
            (x + ancho + 8, y + alto + 8),
            color,
            -1
        )

        cv2.putText(
            imagen,
            etiqueta,
            (x + 4, y + alto + 3),
            fuente,
            escala,
            (255, 255, 255),
            grosor,
            cv2.LINE_AA
        )

    # Guardar imagen procesada
    os.makedirs(ruta_resultados, exist_ok=True)
    os.makedirs(ruta_publica, exist_ok=True)

    if not cv2.imwrite(path_salida, imagen):
        raise RuntimeError("No se pudo guardar la imagen procesada.")

    shutil.copyfile(path_salida, path_publico)

    print("===CONTEO===")

    for clase, cantidad in conteo.items():
        print(f"{clase}: {cantidad}")


if __name__ == "__main__":
    try:
        if len(sys.argv) != 2:
            raise ValueError("Debe proporcionar el nombre de una imagen.")

        nombre_archivo = sys.argv[1].strip()

        if (
            os.path.basename(nombre_archivo) != nombre_archivo
            or not nombre_archivo.lower().endswith((".jpg", ".jpeg", ".png"))
        ):
            raise ValueError("El archivo debe ser una imagen JPG o PNG.")

        detectar(nombre_archivo)

    except Exception as error:
        print(f"Error: {error}", file=sys.stderr)
        sys.exit(1)
