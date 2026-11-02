@extends('layouts.app')

@section('title', 'Resultados - Módulo 4')

@section('content')
<link rel="stylesheet" href="{{ asset('css/indexmodulo4.css') }}">

<main class="m4-shell">

    <h1>Resultados de detección</h1>

    <div class="m4-results-grid">

        <section class="m4-section">
            <h2>Imagen original</h2>

            <img
                class="m4-result-image"
                src="{{ asset($original) }}"
                alt="Imagen original"
            >
        </section>

        <section class="m4-section">
            <h2>Detecciones de YOLOv8</h2>

            <img
                class="m4-result-image"
                src="{{ asset($mostrar) }}"
                alt="Imagen procesada"
            >
        </section>

    </div>

    <section class="m4-section m4-counts">

        <h2>Conteo de objetos</h2>

        <p>
            Filtro:
            <strong>
                {{ $filtro ? ucfirst($filtro) : 'Todas las clases' }}
            </strong>
        </p>

        @if (!empty($prioridad))
            <p>
                Orden de prioridad:
                {{ implode(' → ', array_map('ucfirst', $prioridad)) }}
            </p>
        @endif

        @if (!empty($conteo))

            <ul class="m4-count-list">
                @foreach ($conteo as $objeto => $cantidad)
                    <li>
                        <span>{{ ucfirst($objeto) }}</span>
                        <strong>{{ $cantidad }}</strong>
                    </li>
                @endforeach
            </ul>

        @else
            <p>
                No se detectaron objetos de la clase seleccionada,
                o la imagen no contiene detecciones.
            </p>
        @endif

    </section>

    <div class="m4-actions">
        <a href="{{ route('modulo4.index') }}" class="m4-back">
            Volver
        </a>
    </div>

</main>
@endsection
