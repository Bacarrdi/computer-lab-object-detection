<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YoloController;
use App\Http\Controllers\Module3Controller;
use App\Http\Controllers\Module4Controller;

// Página principal
Route::redirect('/', '/modulo1');

// Módulo 1: Detección de objetos
Route::view('/modulo1', 'modulo1.index')
    ->name('modulo1.index');

Route::post('/modulo1/detectar', [YoloController::class, 'procesar'])
    ->name('modulo1.detectar')
    ->block(300, 15);

Route::post('/modulo1/comparar', [YoloController::class, 'comparar'])
    ->name('modulo1.comparar')
    ->block(300, 15);

Route::get('/modulo1/estadisticas-actuales', function (
    \Illuminate\Http\Request $request
) {
    $id = $request->query('comparacion');

    $guardadas = $request->session()->get('m1_guardadas', []);

    if (
        !is_string($id) ||
        !preg_match('/\A[A-Za-z0-9]{40}\z/', $id) ||
        !in_array($id, $guardadas, true)
    ) {
        return redirect()
            ->route('modulo1.index')
            ->with('error', 'No se encontró esta comparación.');
    }

    $directorio = public_path(
        'archivos_yolo/estadisticas/actual/' . $id
    );

    $rutaComparacion = $directorio . '/comparacion_total.png';
    $rutaError = $directorio . '/porcentaje_error.png';

    if (!is_file($rutaComparacion) || !is_file($rutaError)) {
        return redirect()
            ->route('modulo1.index')
            ->with(
                'error',
                'Los gráficos de esta comparación no están disponibles.'
            );
    }

    $urlBase = 'archivos_yolo/estadisticas/actual/' . $id;

    return view('modulo1.estadisticas-actuales', [
        'graficoComparacion' =>
            asset($urlBase . '/comparacion_total.png')
            . '?v=' . filemtime($rutaComparacion),

        'graficoError' =>
            asset($urlBase . '/porcentaje_error.png')
            . '?v=' . filemtime($rutaError),
    ]);
})->name('modulo1.estadisticasActuales');

// Módulo 2: Navegación por voz
Route::view('/modulo2', 'modulo2.index')
    ->name('modulo2.index');

// Módulo 3: Estadísticas
Route::get('/modulo3', [Module3Controller::class, 'index'])
    ->name('modulo3.index');

// Módulo 4: Prioridad de conteo
Route::get('/modulo4', [Module4Controller::class, 'index'])
    ->name('modulo4.index');

Route::post('/modulo4/procesar', [Module4Controller::class, 'procesar'])
    ->name('modulo4.process');
