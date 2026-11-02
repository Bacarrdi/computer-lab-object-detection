@extends('layouts.app')

@section('title', 'Módulo 4 - Prioridades')

@section('content')
<link rel="stylesheet" href="{{ asset('css/indexmodulo4.css') }}">

<main class="m4-shell">

    <h1>Detección con prioridades</h1>

    <p class="m4-intro">
        Selecciona una imagen y configura cómo deseas
        visualizar el conteo de objetos detectados.
    </p>

    @if (session('error'))
        <div class="m4-alert">
            {{ session('error') }}
        </div>
    @endif

    @if ($errors->any())
        <div class="m4-alert">
            {{ $errors->first() }}
        </div>
    @endif

    <form action="{{ route('modulo4.process') }}"
          method="POST"
          enctype="multipart/form-data"
          id="m4-form">

        @csrf

        <section class="m4-section">
            <h2>Imagen del laboratorio</h2>

            <p>Selecciona una imagen JPG o PNG (máximo 10 MB).</p>

            <input
                type="file"
                name="archivo"
                id="input-file"
                class="m4-file-input"
                accept=".jpg,.jpeg,.png"
                required
            >

            <div id="preview" aria-live="polite"></div>
        </section>

        <section class="m4-section">
            <h2>Filtrar por tipo de objeto</h2>

            <p>
                Opcional. Permite mostrar el conteo
                de una sola clase.
            </p>

            <select name="tipo" class="m4-select">
                <option value="">Todas las clases</option>

                @foreach ([
                    'silla', 'teclado', 'monitor',
                    'persona', 'mouse', 'laptop', 'torre'
                ] as $clase)
                    <option value="{{ $clase }}"
                        @selected(old('tipo') === $clase)>
                        {{ ucfirst($clase) }}
                    </option>
                @endforeach
            </select>
        </section>

        <section class="m4-section">
            <h2>Establecer prioridades</h2>

            <p>
                Opcional. Ordena las clases según su prioridad.
                Deja los campos vacíos para utilizar el orden normal.
            </p>

            <div class="m4-priorities">

                @for ($i = 0; $i < 7; $i++)

                    <div class="m4-priority-field">
                        <label for="prioridad-{{ $i }}">
                            Prioridad {{ $i + 1 }}
                        </label>

                        <select
                            id="prioridad-{{ $i }}"
                            name="prioridad[]"
                            class="m4-select"
                        >
                            <option value="">Sin prioridad</option>

                            @foreach ([
                                'silla', 'teclado', 'monitor',
                                'persona', 'mouse', 'laptop', 'torre'
                            ] as $clase)
                                <option value="{{ $clase }}"
                                    @selected(old('prioridad.' . $i) === $clase)>
                                    {{ ucfirst($clase) }}
                                </option>
                            @endforeach

                        </select>
                    </div>

                @endfor

            </div>
        </section>

        <div class="m4-actions">
            <button type="submit" id="detect-btn">
                Detectar objetos
            </button>
        </div>

    </form>

</main>

<script src="{{ asset('js/indexmodulo4.js') }}"></script>
@endsection
