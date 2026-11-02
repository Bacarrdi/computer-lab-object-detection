import argparse
from pathlib import Path

import matplotlib
matplotlib.use("Agg")

import matplotlib.pyplot as plt
import pandas as pd

BASE = Path(__file__).resolve().parent.parent


def generar(csv_path, output_dir, solo_comparacion=False):
    df = pd.read_csv(
        csv_path,
        header=None,
        names=["Fecha", "Objeto", "IA", "Manual"]
    )

    for columna in ["IA", "Manual"]:
        df[columna] = pd.to_numeric(
            df[columna], errors="coerce"
        )

    df = df.dropna(subset=["Fecha", "Objeto", "IA", "Manual"])
    df = df[(df["IA"] >= 0) & (df["Manual"] >= 0)]

    if df.empty:
        raise ValueError("No existen comparaciones válidas.")

    output_dir.mkdir(parents=True, exist_ok=True)

    conteos = df.groupby(
        "Objeto", sort=False
    )[["IA", "Manual"]].sum()

    objetos = [
        str(nombre).capitalize()
        for nombre in conteos.index
    ]

    posiciones = list(range(len(objetos)))
    ancho = 0.38

    fig, ax = plt.subplots(figsize=(8, 4.5))

    barras_ia = ax.bar(
        [x - ancho / 2 for x in posiciones],
        conteos["IA"],
        width=ancho,
        label="YOLOv8"
    )

    barras_manual = ax.bar(
        [x + ancho / 2 for x in posiciones],
        conteos["Manual"],
        width=ancho,
        label="Usuario"
    )

    ax.bar_label(barras_ia, padding=3, fmt="%.0f")
    ax.bar_label(barras_manual, padding=3, fmt="%.0f")

    ax.set_xticks(posiciones)
    ax.set_xticklabels(objetos)
    ax.set_ylabel("Cantidad de objetos")
    ax.set_title("Comparación por tipo de objeto")
    ax.legend()
    ax.margins(y=0.15)

    plt.tight_layout()
    plt.savefig(output_dir / "comparacion_total.png")
    plt.close()

    coincidencias = df[["IA", "Manual"]].min(axis=1).sum()
    diferencias = (df["IA"] - df["Manual"]).abs().sum()

    plt.figure(figsize=(5, 5))

    if coincidencias + diferencias > 0:
        plt.pie(
            [coincidencias, diferencias],
            labels=["Coincidencias", "Diferencias"],
            autopct="%1.1f%%"
        )
    else:
        plt.text(
            0.5, 0.5, "Sin objetos contados",
            ha="center", va="center"
        )
        plt.axis("off")

    plt.title("Coincidencias y diferencias de conteo")
    plt.tight_layout()
    plt.savefig(output_dir / "porcentaje_error.png")
    plt.close()

    if not solo_comparacion:
        # Diferencias absolutas por clase
        diferencias = (df["IA"] - df["Manual"]).abs()

        por_clase = diferencias.groupby(
            df["Objeto"], sort=False
        ).sum().sort_values(ascending=False)

        fig, ax = plt.subplots(figsize=(8, 4.5))

        barras = ax.bar(
            por_clase.index.astype(str).str.capitalize(),
            por_clase.values
        )

        ax.bar_label(barras, fmt="%.0f", padding=3)
        ax.set_title("Diferencias absolutas por tipo de objeto")
        ax.set_ylabel("Objetos de diferencia acumulada")
        ax.margins(y=0.15)

        plt.xticks(rotation=30, ha="right")
        plt.tight_layout()
        plt.savefig(
            output_dir / "diferencias_por_clase.png",
            dpi=150
        )
        plt.close()

        # Diferencias absolutas registradas por fecha
        fechas = df["Fecha"].astype(str).str[:10]

        por_fecha = diferencias.groupby(
            fechas, sort=True
        ).sum()

        fig, ax = plt.subplots(figsize=(8, 4.5))

        posiciones = list(range(len(por_fecha)))
        barras = ax.bar(posiciones, por_fecha.values)

        ax.bar_label(barras, fmt="%.0f", padding=3)
        ax.set_xticks(posiciones)
        ax.set_xticklabels(
            por_fecha.index,
            rotation=30,
            ha="right"
        )

        ax.set_title("Diferencias absolutas registradas por fecha")
        ax.set_ylabel("Objetos de diferencia acumulada")
        ax.margins(y=0.15)

        plt.tight_layout()
        plt.savefig(
            output_dir / "diferencias_por_fecha.png",
            dpi=150
        )
        plt.close()


if __name__ == "__main__":
    parser = argparse.ArgumentParser()
    parser.add_argument(
        "--csv",
        type=Path,
        default=BASE / "archivos_yolo/estadisticas/comparaciones_modulo1.csv"
    )
    parser.add_argument(
        "--output-dir",
        type=Path,
        default=BASE / "public/archivos_yolo/estadisticas/imagenes"
    )
    parser.add_argument(
        "--solo-comparacion",
        action="store_true"
    )

    args = parser.parse_args()
    generar(args.csv, args.output_dir, args.solo_comparacion)
