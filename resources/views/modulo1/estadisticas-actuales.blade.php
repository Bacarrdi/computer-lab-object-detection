@extends('layouts.app')

@section('title', 'Estadísticas de la comparación')

@section('content')
<link rel="stylesheet"
      href="{{ asset('css/resultadosmodulo1.css') }}">

<h1 class="title">Comparación guardada</h1>

@if (session('success'))
    <p>{{ session('success') }}</p>
@endif

<p>
    Estos gráficos corresponden a la comparación que acabas de guardar.
</p>

<div class="comparacion-acciones">
    <button type="button"
            id="mostrar-estadisticas"
            onclick="
                document.getElementById('graficos-actuales').hidden = false;
                this.hidden = true;
            ">
        Ver estadísticas de esta imagen
    </button>

    <a href="{{ route('modulo1.index') }}" id="volver-btn">
        Volver
    </a>
</div>

<div id="graficos-actuales" class="graficos-actuales" hidden>

    <section class="comparacion-panel">
        <h3>Comparación IA vs Usuario</h3>
        <img
            src="{{ $graficoComparacion }}"
            alt="Comparación de conteos">
    </section>

    <section class="comparacion-panel">
        <h3>Coincidencias y diferencias</h3>
        <img
            src="{{ $graficoError }}"
            alt="Diferencias de conteo">
    </section>

</div>
@endsection
