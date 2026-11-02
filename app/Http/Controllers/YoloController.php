<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Process\Process;

class YoloController extends Controller
{

    public function procesar(Request $request)
    {
        $request->validate([
            'archivo' => [
                'required',
                'file',
                'image',
                'mimes:jpg,jpeg,png',
                'max:10240',
            ],
        ]);

        $archivo = $request->file('archivo');

        // La extensión se determina por el contenido
        $extensiones = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
        ];

        $tipo = $archivo->getMimeType();

        if (!isset($extensiones[$tipo])) {
            return back()->with(
                'error',
                'El archivo debe ser una imagen JPG o PNG válida.'
            );
        }

        $nombre = Str::random(20) . '.' . $extensiones[$tipo];

        $rutaSubida = base_path('archivos_yolo/archivos_subidos');
        $rutaResultados = base_path('archivos_yolo/resultados');
        $directorioOriginales = public_path('archivos_yolo/originales');
        $directorioResultadosPublicos = public_path('archivos_yolo/resultados');

        $rutaOriginalPublica = 'archivos_yolo/originales/' . $nombre;
        $nombreResultado = 'resultado_' . $nombre;
        $rutaPublicaFinal = 'archivos_yolo/resultados/' . $nombreResultado;

        $archivoSubido = $rutaSubida . '/' . $nombre;
        $originalPublico = public_path($rutaOriginalPublica);
        $resultadoInterno = $rutaResultados . '/' . $nombreResultado;
        $resultadoPublico = public_path($rutaPublicaFinal);

        try {
            foreach ([
                $rutaSubida,
                $rutaResultados,
                $directorioOriginales,
                $directorioResultadosPublicos,
            ] as $directorio) {
                if (!is_dir($directorio) &&
                    !mkdir($directorio, 0755, true) &&
                    !is_dir($directorio)) {
                    throw new \RuntimeException(
                        'No se pudo preparar el directorio de archivos.'
                    );
                }
            }

            $archivo->move($rutaSubida, $nombre);

            if (!copy($archivoSubido, $originalPublico)) {
                throw new \RuntimeException(
                    'No se pudo copiar la imagen original.'
                );
            }

            $proceso = new Process([
                '/opt/venv/bin/python',
                base_path('python/detectar_yolo.py'),
                $nombre,
            ]);

            $proceso->setTimeout(120);
            $proceso->mustRun();

            $salida = $proceso->getOutput();

            $marcador = '===CONTEO===';

            if (!str_contains($salida, $marcador)) {
                throw new \RuntimeException(
                    'Python no devolvió el conteo esperado.'
                );
            }

            $contenidoConteo = explode($marcador, $salida, 2)[1];
            $conteo = [];

            foreach (preg_split('/\r\n|\r|\n/', trim($contenidoConteo)) as $linea) {
                if (preg_match('/^([^:]+):\s*(\d+)$/', trim($linea), $coincidencia)) {
                    $clase = trim($coincidencia[1]);
                    $cantidad = (int) $coincidencia[2];

                    $conteo[$clase] = $cantidad;
                }
            }

            if (!is_file($resultadoPublico)) {
                throw new \RuntimeException(
                    'YOLO no generó la imagen de resultados.'
                );
            }

            // Clases que utiliza la interfaz del proyecto.
            $clases = [
                'monitor',
                'silla',
                'mouse',
                'teclado',
                'laptop',
                'torre',
                'persona',
            ];

            $conteoCompleto = array_fill_keys($clases, 0);

            foreach ($conteo as $clase => $cantidad) {
                if (array_key_exists($clase, $conteoCompleto)) {
                    $conteoCompleto[$clase] = (int) $cantidad;
                }
            }

            $comparacionId = Str::random(40);

            $pendientes = $request->session()->get('m1_pendientes', []);
            $pendientes = array_slice($pendientes, -4, null, true);

            $pendientes[$comparacionId] = $conteoCompleto;

            $request->session()->put('m1_pendientes', $pendientes);

            return view('modulo1.detectar', [
                'mostrar' => $rutaPublicaFinal,
                'original' => $rutaOriginalPublica,
                'conteo' => $conteoCompleto,
                'comparacionId' => $comparacionId,
            ]);
        } catch (\Throwable $error) {
            report($error);

            foreach ([
                $archivoSubido,
                $originalPublico,
                $resultadoInterno,
                $resultadoPublico,
            ] as $ruta) {
                if (is_file($ruta)) {
                    @unlink($ruta);
                }
            }

            return redirect()
                ->route('modulo1.index')
                ->with(
                    'error',
                    'No se pudo procesar la imagen. Verifica el archivo e inténtalo nuevamente.'
                );
        }
    }

    public function comparar(Request $request)
    {
        $catalogo = [
            'monitor', 'silla', 'mouse', 'teclado',
            'laptop', 'torre', 'persona'
        ];

        $validador = \Illuminate\Support\Facades\Validator::make(
            $request->all(),
            [
                'comparacion_id' => 'required|string|size:40',
                'manual' => 'required|array|min:1|max:7',
                'manual.*' => 'nullable|integer|min:0|max:10000',
            ]
        );

        if ($validador->fails()) {
            return redirect()->route('modulo1.index')
                ->with('error', 'Los conteos manuales no son válidos.');
        }

        $datos = $validador->validated();
        $manual = $datos['manual'];
        $id = $datos['comparacion_id'];

        if (array_diff(array_keys($manual), $catalogo)) {
            return redirect()->route('modulo1.index')
                ->with('error', 'Hay clases no permitidas.');
        }

        $pendientes = $request->session()->get('m1_pendientes', []);

        if (!isset($pendientes[$id]) || !is_array($pendientes[$id])) {
            return redirect()->route('modulo1.index')
                ->with('error', 'La comparación expiró o ya fue guardada.');
        }

        $automatico = $pendientes[$id];
        $fecha = now()->toDateTimeString();
        $filas = [];

        foreach ($catalogo as $clase) {
            $cantidadYolo = (int) ($automatico[$clase] ?? 0);
            $cantidadUsuario = $manual[$clase] ?? null;

            if ($cantidadYolo > 0 && $cantidadUsuario === null) {
                return redirect()->route('modulo1.index')
                    ->with('error', "Falta contar la clase: $clase.");
            }

            if (
                $cantidadYolo === 0 &&
                ($cantidadUsuario === null || (int) $cantidadUsuario === 0)
            ) {
                continue;
            }

            $filas[] = [
                $fecha,
                $clase,
                $cantidadYolo,
                (int) $cantidadUsuario,
            ];
        }

        if (empty($filas)) {
            return redirect()->route('modulo1.index')
                ->with('error', 'No hay objetos para comparar.');
        }

        $directorio = base_path('archivos_yolo/estadisticas');
        $individuales = $directorio . '/individuales';

        $historico = $directorio . '/comparaciones_modulo1.csv';
        $actual = $individuales . '/' . $id . '.csv';

        $graficosActuales = public_path(
            'archivos_yolo/estadisticas/actual/' . $id
        );

        $graficosHistoricos = public_path(
            'archivos_yolo/estadisticas/imagenes'
        );

        $temporalHistorico = public_path(
            'archivos_yolo/estadisticas/.temporal-' . $id
        );

        $python = '/opt/venv/bin/python';
        $script = base_path('python/generar_graficos.py');

        $mutex = null;
        $historicoGuardado = false;

        try {
            foreach ([
                $directorio,
                $individuales,
                $graficosActuales,
                $graficosHistoricos,
                $temporalHistorico,
            ] as $carpeta) {
                if (
                    !is_dir($carpeta) &&
                    !mkdir($carpeta, 0755, true) &&
                    !is_dir($carpeta)
                ) {
                    throw new \RuntimeException(
                        'No se pudo crear un directorio de estadísticas.'
                    );
                }
            }

            $archivo = fopen($actual, 'w');

            if ($archivo === false) {
                throw new \RuntimeException(
                    'No se pudo crear el CSV individual.'
                );
            }

            try {
                foreach ($filas as $fila) {
                    if (fputcsv($archivo, $fila) === false) {
                        throw new \RuntimeException(
                            'Error al escribir el CSV individual.'
                        );
                    }
                }
            } finally {
                fclose($archivo);
            }

            $mutex = fopen($directorio . '/historico.lock', 'c');

            if ($mutex === false || !flock($mutex, LOCK_EX)) {
                throw new \RuntimeException(
                    'No se pudo bloquear el histórico.'
                );
            }

            $archivo = fopen($historico, 'c+');

            if ($archivo === false) {
                throw new \RuntimeException(
                    'No se pudo abrir el CSV histórico.'
                );
            }

            try {
                if (!flock($archivo, LOCK_EX)) {
                    throw new \RuntimeException(
                        'No se pudo bloquear el archivo CSV.'
                    );
                }

                if (fseek($archivo, 0, SEEK_END) !== 0) {
                    throw new \RuntimeException(
                        'No se pudo posicionar el CSV.'
                    );
                }

                $posicionInicial = ftell($archivo);

                if ($posicionInicial === false) {
                    throw new \RuntimeException(
                        'No se pudo consultar el tamaño del CSV.'
                    );
                }

                try {
                    foreach ($filas as $fila) {
                        if (fputcsv($archivo, $fila) === false) {
                            throw new \RuntimeException(
                                'Error al añadir datos al histórico.'
                            );
                        }
                    }

                    if (!fflush($archivo)) {
                        throw new \RuntimeException(
                            'No se pudo confirmar la escritura del CSV.'
                        );
                    }
                } catch (\Throwable $error) {
                    ftruncate($archivo, $posicionInicial);
                    throw $error;
                }

                $historicoGuardado = true;

            } finally {
                flock($archivo, LOCK_UN);
                fclose($archivo);
            }

            unset($pendientes[$id]);

            $request->session()->put('m1_pendientes', $pendientes);

            $guardadas = $request->session()->get('m1_guardadas', []);
            $guardadas[] = $id;

            $request->session()->put(
                'm1_guardadas',
                array_values(array_unique($guardadas))
            );

            $procesoActual = new Process([
                $python,
                $script,
                '--csv', $actual,
                '--output-dir', $graficosActuales,
                '--solo-comparacion',
            ]);

            $procesoActual->setTimeout(120);
            $procesoActual->mustRun();

            foreach ([
                'comparacion_total.png',
                'porcentaje_error.png',
            ] as $nombreGrafico) {
                if (!is_file($graficosActuales . '/' . $nombreGrafico)) {
                    throw new \RuntimeException(
                        'Falta un gráfico de la comparación.'
                    );
                }
            }

            $procesoHistorico = new Process([
                $python,
                $script,
                '--csv', $historico,
                '--output-dir', $temporalHistorico,
            ]);

            $procesoHistorico->setTimeout(120);
            $procesoHistorico->mustRun();

            $imagenesGeneradas = glob($temporalHistorico . '/*.png');

            if (!$imagenesGeneradas) {
                throw new \RuntimeException(
                    'No se generaron los gráficos históricos.'
                );
            }

            foreach ($imagenesGeneradas as $imagen) {
                $destino = $graficosHistoricos . '/' . basename($imagen);

                if (!rename($imagen, $destino)) {
                    throw new \RuntimeException(
                        'No se pudo publicar un gráfico histórico.'
                    );
                }
            }

            @rmdir($temporalHistorico);

        } catch (\Throwable $error) {
            report($error);

            $mensaje = $historicoGuardado
                ? 'La comparación se guardó, pero falló la generación de gráficos.'
                : 'No se pudo guardar la comparación.';

            return redirect()
                ->route('modulo1.index')
                ->with('error', $mensaje);

        } finally {
            if (is_resource($mutex)) {
                flock($mutex, LOCK_UN);
                fclose($mutex);
            }
        }

        return redirect()
            ->route('modulo1.estadisticasActuales', [
                'comparacion' => $id
            ])
            ->with('success', 'Comparación guardada correctamente.');
    }
}
