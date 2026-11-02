@extends('layouts.app')

@section('title', 'Detección de Objetos - Módulo 1')

@section('content')

<link rel="stylesheet" href="{{ asset('css/indexmodulo4.css') }}">
<link rel="stylesheet" href="{{ asset('css/indexmodulo1.css') }}">

<main class="m4-shell">

    <h1>Detección y conteo de objetos</h1>

    <p class="m4-intro">
        Selecciona una fotografía de un laboratorio
        para detectar y contar objetos mediante YOLOv8.
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

    <form action="{{ route('modulo1.detectar') }}"
          method="POST"
          enctype="multipart/form-data">

        @csrf

        <section class="m4-section">
            <h2>Imagen del laboratorio</h2>

            <p>
                Selecciona una imagen JPG o PNG (máximo 10 MB).
            </p>

            <div class="upload-area">
                <label for="input-file"
                       id="upload-btn"
                       class="btn-upload">
                    Seleccionar archivo
                </label>

                <input type="file"
                       id="input-file"
                       name="archivo"
                       accept=".jpg,.jpeg,.png"
                       required
                       style="display: none;">
            </div>

            <div id="preview"
                 class="preview-area"
                 aria-live="polite"></div>
        </section>

        <div class="m4-actions">
            <button id="detect-btn" type="submit">
                Detectar objetos
            </button>
        </div>

    </form>

</main>

<script src="{{ asset('js/indexmodulo1.js') }}"></script>
@endsection
