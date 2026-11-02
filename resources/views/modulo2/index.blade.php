@extends('layouts.app')

@section('title', 'Control por Voz')

@section('content')

<link rel="stylesheet" href="{{ asset('css/indexmodulo2.css') }}">

<main class="voice-page">

    <h1>Control por voz</h1>

    <p class="voice-intro">
        Activa el reconocimiento para navegar entre
        los módulos mediante comandos de voz.
    </p>

    <div class="voice-controls">
        <button id="voice-toggle-btn" type="button">
            <span id="btn-text">Activar Voz</span>
        </button>

        <div class="voice-feedback" aria-live="polite">
            <span id="voice-status">Desactivado</span>

            <p id="last-command">
                Último comando: ninguno
            </p>
        </div>
    </div>

    <section class="voice-commands">

        <h2>Comandos disponibles</h2>

        <h3>Navegación entre módulos</h3>

        @foreach ([
            ['ir a módulo 1', 'Ir al módulo de detección'],
            ['ir a módulo 2', 'Ir al módulo de voz'],
            ['ir a módulo 3', 'Ir al módulo de estadísticas'],
            ['ir a módulo 4', 'Ir al módulo de prioridades']
        ] as $comando)
            <div class="voice-row">
                <span>"{{ $comando[0] }}"</span>
                <p>{{ $comando[1] }}</p>
            </div>
        @endforeach

        <h3>Control del reconocimiento</h3>

        <div class="voice-row">
            <span>"apagar comando por voz"</span>
            <p>Desactivar el reconocimiento de voz</p>
        </div>

        <p class="voice-note">
            Pronuncia los comandos claramente en español.
        </p>

    </section>

</main>

@endsection
