@extends('layouts.app')

@section('title', 'Estadísticas históricas - Módulo 3')

@section('content')

<link rel="stylesheet"
      href="{{ asset('css/indexmodulo3.css') }}">

<h1 class="title">Estadísticas históricas</h1>

<p>
    Comparación acumulada entre las detecciones de YOLOv8
    y los conteos manuales registrados.
</p>

@php
    $csv = base_path(
        'archivos_yolo/estadisticas/comparaciones_modulo1.csv'
    );

    $graficos = [
        [
            'titulo' => 'Conteos por tipo de objeto',
            'archivo' => 'comparacion_total.png',
            'descripcion' => 'Conteo acumulado de IA y usuario por clase.'
        ],
        [
            'titulo' => 'Coincidencias y diferencias',
            'archivo' => 'porcentaje_error.png',
            'descripcion' => 'Proporción agregada de coincidencias y discrepancias en los conteos.'
        ],
        [
            'titulo' => 'Diferencias por clase',
            'archivo' => 'diferencias_por_clase.png',
            'descripcion' => 'Suma de las diferencias absolutas entre IA y usuario para cada clase.'
        ],
        [
            'titulo' => 'Diferencias por fecha',
            'archivo' => 'diferencias_por_fecha.png',
            'descripcion' => 'Suma de las diferencias absolutas de los registros de cada fecha.'
        ]
    ];
@endphp

@if (!is_file($csv) || filesize($csv) === 0)

    <p>
        Todavía no existen comparaciones históricas.
        Guarda una comparación desde el módulo 1.
    </p>

@else

    <div class="graficos-container">

        @foreach ($graficos as $grafico)

            @php
                $ruta = public_path(
                    'archivos_yolo/estadisticas/imagenes/'
                    . $grafico['archivo']
                );
            @endphp

            <section class="grafico">

                <h3>{{ $grafico['titulo'] }}</h3>

                @if (is_file($ruta))
                    <img
                        src="{{ asset(
                            'archivos_yolo/estadisticas/imagenes/'
                            . $grafico['archivo']
                        ) }}?v={{ filemtime($ruta) }}"
                        alt="{{ $grafico['titulo'] }}"
                        class="grafico-img"
                    >
                @else
                    <p>
                        Gráfico no generado todavía.
                    </p>
                @endif

                <p class="grafico-descripcion">
                    {{ $grafico['descripcion'] }}
                </p>

            </section>

        @endforeach

    </div>

    <p class="nota-estadistica">
        Las coincidencias y diferencias comparan cantidades
        registradas. No representan una evaluación formal
        de precisión del modelo YOLOv8.
    </p>

@endif

@endsection
