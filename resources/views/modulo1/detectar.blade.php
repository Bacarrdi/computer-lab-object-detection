@extends('layouts.app')

@section('title', 'Resultados de Detección - Módulo 1')

@section('content')
<link rel="stylesheet" href="{{ asset('css/indexmodulo1.css') }}">
<link rel="stylesheet" href="{{ asset('css/resultadosmodulo1.css') }}">

<h1 class="title">Resultados de Detección</h1>

@if (session('error'))
    <p style="color: red;">{{ session('error') }}</p>
@endif

@if (session('success'))
    <p style="color: green;">{{ session('success') }}</p>
@endif

@if (isset($mostrar, $original, $comparacionId))

    @php
        $catalogo = [
            'monitor', 'silla', 'mouse', 'teclado',
            'laptop', 'torre', 'persona'
        ];

        $detectados = [];

        foreach ($catalogo as $clase) {
            $cantidad = (int) ($conteo[$clase] ?? 0);

            if ($cantidad > 0) {
                $detectados[$clase] = $cantidad;
            }
        }

        $faltantes = array_values(
            array_diff($catalogo, array_keys($detectados))
        );
    @endphp

    <form method="POST" action="{{ route('modulo1.comparar') }}">
        @csrf

        <input type="hidden"
               name="comparacion_id"
               value="{{ $comparacionId }}">

        <div class="comparacion-grid">

            <section class="comparacion-panel">
                <h3>Imagen original</h3>

                <img src="{{ asset($original) }}"
                     alt="Imagen original">

                <div class="comparacion-detalle">
                    <h3>¿Cuántos objetos viste tú?</h3>

                    @if (count($detectados) > 0)
                        <div class="campos-manuales">

                            @foreach ($detectados as $objeto => $cantidad)
                                <div class="campo-manual">
                                    <label for="manual-{{ $objeto }}">
                                        {{ ucfirst($objeto) }}
                                    </label>

                                    <input
                                        type="number"
                                        id="manual-{{ $objeto }}"
                                        name="manual[{{ $objeto }}]"
                                        min="0"
                                        max="10000"
                                        placeholder="Cantidad"
                                        required>
                                </div>
                            @endforeach

                        </div>
                    @else
                        <p>
                            YOLO no detectó objetos.
                            Puedes registrar los que observaste.
                        </p>
                    @endif

                    @if (count($faltantes) > 0)
                        <details class="objetos-no-detectados">
                            <summary>
                                Agregar objetos que YOLO no detectó
                            </summary>

                            <p>
                                Completa solo las clases adicionales
                                que observaste en la imagen.
                            </p>

                            <div class="campos-manuales">

                                @foreach ($faltantes as $objeto)
                                    <div class="campo-manual">
                                        <label for="extra-{{ $objeto }}">
                                            {{ ucfirst($objeto) }}
                                        </label>

                                        <input
                                            type="number"
                                            id="extra-{{ $objeto }}"
                                            name="manual[{{ $objeto }}]"
                                            min="1"
                                            max="10000"
                                            placeholder="Opcional">
                                    </div>
                                @endforeach

                            </div>
                        </details>
                    @endif
                </div>
            </section>

            <section class="comparacion-panel">
                <h3>Detecciones de YOLOv8</h3>

                <img src="{{ asset($mostrar) }}"
                     alt="Imagen procesada">

                <div class="comparacion-detalle">
                    <h3>Conteo automático</h3>

                    @if (count($detectados) > 0)
                        <ul class="conteo-lista">

                            @foreach ($detectados as $objeto => $cantidad)
                                <li>
                                    <span>{{ ucfirst($objeto) }}</span>
                                    <strong>{{ $cantidad }}</strong>
                                </li>
                            @endforeach

                        </ul>
                    @else
                        <p>No se detectaron objetos.</p>
                    @endif
                </div>
            </section>

        </div>

        <div class="comparacion-acciones">
            <button type="submit" id="guardar-comparacion-btn">
                Guardar comparación
            </button>

            <a href="{{ route('modulo1.index') }}" id="volver-btn">
                Volver
            </a>
        </div>

    </form>

@else
    <p>No se pudo mostrar la comparación de imágenes.</p>

    <div class="comparacion-acciones">
        <a href="{{ route('modulo1.index') }}" id="volver-btn">
            Volver
        </a>
    </div>
@endif

@endsection
