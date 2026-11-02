<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', 'Computer Lab Object Detection')</title>

    <link rel="stylesheet" href="{{ asset('css/layout.css') }}">
    @yield('extra-css')

    <link rel="stylesheet" href="{{ asset('css/refinamiento.css') }}?v=1">

    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
</head>
<body>

    <!-- MENU LATERAL -->
    <div class="sidebar">

        <h3 class="menu-title">COMPUTER LAB</h3>

        <div class="menu-items">

            <div class="menu-item">
                <a href="{{ route('modulo1.index') }}" class="opcion-menu">
                    | Módulo 1: Detección
                </a>
            </div>

            <div class="menu-item">
                <a href="{{ route('modulo2.index') }}" class="opcion-menu">
                    | Módulo 2: Navegación por voz
                </a>
            </div>

            <div class="menu-item">
                <a href="{{ route('modulo3.index') }}" class="opcion-menu">
                    | Módulo 3: Estadísticas
                </a>
            </div>

            <div class="menu-item">
                <a href="{{ route('modulo4.index') }}" class="opcion-menu">
                    | Módulo 4: Prioridades
                </a>
            </div>

            <!-- CONTROL GLOBAL DE VOZ -->
            @if (!Request::is('modulo2'))
                <div class="menu-item text-center mt-3 px-2">

                    <button id="voice-toggle-btn" class="btn btn-success w-100">
                        <i class="fas fa-microphone"></i>
                        <span id="btn-text">Activar Voz</span>
                    </button>

                    <small id="voice-status" class="text-light d-block mt-1">
                        Desactivado
                    </small>

                    <p id="last-command" class="text-white small mt-1"></p>
                    <div id="voice-commands" hidden>
                        <h4>Comandos disponibles</h4>

                        <ul>
                            <li>Ir a módulo 1</li>
                            <li>Ir a módulo 2</li>
                            <li>Ir a módulo 3</li>
                            <li>Ir a módulo 4</li>
                            <li>Apagar comando por voz</li>
                        </ul>
                    </div>
                </div>
            @endif

        </div>
    </div>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="main-panel">
        @yield('content')
    </div>

    <!-- FOOTER -->
    <footer class="footer-global">
        <span>Computer Lab Object Detection</span>
        <span>Hora actual: <span id="footer-datetime"></span></span>
    </footer>

    <!-- SCRIPTS -->
    <script>
        function updateDateTime() {
            const now = new Date();

            document.getElementById('footer-datetime').textContent =
                now.toLocaleString('es-ES');
        }

        setInterval(updateDateTime, 1000);
        updateDateTime();

        const currentPath = window.location.pathname.replace(/\/+$/, "");

        document.querySelectorAll(".opcion-menu").forEach(link => {
            const route = new URL(link.href).pathname.replace(/\/+$/, "");

            if (currentPath === route || currentPath.startsWith(route + "/")) {
                link.classList.add("activo");
                link.setAttribute("aria-current", "page");
            }
        });
    </script>

    <script src="{{ asset('js/voicecontrol.js') }}"></script>

    @yield('extra-js')
    @stack('scripts')

</body>
</html>
