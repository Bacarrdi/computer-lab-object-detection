<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Symfony\Component\Process\Process;

class Module4Controller extends Controller
{
    public function index()
    {
        return view('modulo4.index');
    }

    public function procesar(Request $request)
    {
        $clases = [
            'silla', 'teclado', 'monitor',
            'persona', 'mouse', 'laptop', 'torre'
        ];

        $datos = $request->validate([
            'archivo' => [
                'required', 'image',
                'mimes:jpg,jpeg,png', 'max:10240'
            ],
            'tipo' => ['nullable', Rule::in($clases)],
            'prioridad' => ['nullable', 'array', 'max:7'],
            'prioridad.*' => [
                'nullable', Rule::in($clases)
            ],
        ]);

        $archivo = $request->file('archivo');

        $nombre = now()->format('YmdHis')
            . '_' . Str::random(12)
            . '.' . $archivo->extension();

        $directorioSubidas = base_path(
            'archivos_yolo/archivos_subidos'
        );

        $directorioOriginales = public_path(
            'archivos_yolo/originales'
        );

        $rutaOriginal = 'archivos_yolo/originales/'
            . $nombre;

        try {
            foreach ([$directorioSubidas, $directorioOriginales] as $directorio) {
                if (!is_dir($directorio)) {
                    mkdir($directorio, 0755, true);
                }
            }

            $archivo->move($directorioSubidas, $nombre);

            if (!copy(
                $directorioSubidas . '/' . $nombre,
                public_path($rutaOriginal)
            )) {
                throw new \RuntimeException(
                    'No se pudo copiar la imagen original.'
                );
            }

            $proceso = new Process([
                '/opt/venv/bin/python',
                base_path('python/detectar_yolo.py'),
                $nombre
            ], base_path());

            $proceso->setTimeout(180);
            $proceso->mustRun();

            $output = $proceso->getOutput();

            if (!preg_match(
                '/===CONTEO===\s*(.*)$/s',
                $output,
                $coincidencias
            )) {
                throw new \RuntimeException(
                    'Python no devolvió el conteo esperado.'
                );
            }

            $conteo = [];

            foreach (explode("\n", trim($coincidencias[1])) as $linea) {
                if (preg_match(
                    '/^([^:]+):\s*(\d+)$/',
                    trim($linea),
                    $datosLinea
                )) {
                    $conteo[trim($datosLinea[1])] =
                        (int) $datosLinea[2];
                }
            }

            $rutaResultado = 'archivos_yolo/resultados/'
                . 'resultado_' . $nombre;

            if (!is_file(public_path($rutaResultado))) {
                throw new \RuntimeException(
                    'No se generó la imagen procesada.'
                );
            }

            // Filtrado de conteos por clase
            $tipo = $datos['tipo'] ?? null;

            if ($tipo) {
                $conteo = array_filter(
                    $conteo,
                    fn($clase) => $clase === $tipo,
                    ARRAY_FILTER_USE_KEY
                );
            }

            // Ordenamiento según prioridades seleccionadas
            $prioridad = array_values(array_unique(
                array_filter($datos['prioridad'] ?? [])
            ));

            if ($prioridad) {
                $posiciones = array_flip($prioridad);

                uksort($conteo, function ($a, $b) use ($posiciones) {
                    return ($posiciones[$a] ?? PHP_INT_MAX)
                        <=> ($posiciones[$b] ?? PHP_INT_MAX);
                });
            }

            return view('modulo4.detectar', [
                'original' => $rutaOriginal,
                'mostrar' => $rutaResultado,
                'conteo' => $conteo,
                'filtro' => $tipo,
                'prioridad' => $prioridad
            ]);

        } catch (\Throwable $error) {
            report($error);

            return redirect()
                ->route('modulo4.index')
                ->withInput()
                ->with(
                    'error',
                    'No se pudo procesar la imagen. Revisa los registros de Laravel.'
                );
        }
    }
}
