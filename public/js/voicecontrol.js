class VoiceControl {
    constructor() {
        this.isActive = false;
        this.isStarting = false;
        this.recognition = null;

        this.buttons = document.querySelectorAll('#voice-toggle-btn');
        this.statusTexts = document.querySelectorAll('#voice-status');
        this.lastCommandTexts = document.querySelectorAll('#last-command');
        this.btnTexts = document.querySelectorAll('#btn-text');

        this.initEvents();
        this.initSpeechRecognition();

        if (!this.recognition) {
            this.checkCompatibility();
            return;
        }

        this.updateUI(false);

        // Intentar recuperar el reconocimiento al cambiar de módulo.
        if (sessionStorage.getItem('voice_active') === 'on') {
            this.toggleRecognition(true);
        }
    }

    initSpeechRecognition() {
        const SpeechRecognition =
            window.SpeechRecognition || window.webkitSpeechRecognition;

        if (!SpeechRecognition) {
            return;
        }

        try {
            this.recognition = new SpeechRecognition();
        } catch (error) {
            console.error('No se pudo inicializar la voz:', error);
            return;
        }

        this.recognition.lang = 'es-ES';
        this.recognition.continuous = true;
        this.recognition.interimResults = false;

        this.recognition.onstart = () => {
            this.isStarting = false;

            if (!this.isActive) {
                this.recognition.stop();
                return;
            }

            this.updateUI(true);
        };

        this.recognition.onend = () => {
            this.isStarting = false;

            if (this.isActive) {
                this.startRecognition();
            } else {
                this.updateUI(false);
            }
        };

        this.recognition.onerror = (event) => {
            this.handleError(event.error);
        };

        this.recognition.onresult = (event) => {
            this.processResult(event);
        };
    }

    initEvents() {
        this.buttons.forEach(button => {
            button.addEventListener('click', () => {
                this.toggleRecognition();
            });
        });
    }

    startRecognition() {
        if (!this.recognition || !this.isActive || this.isStarting) {
            return;
        }

        try {
            this.isStarting = true;
            this.recognition.start();
        } catch (error) {
            console.error('Error al iniciar reconocimiento:', error);

            this.isStarting = false;
            this.isActive = false;

            sessionStorage.setItem('voice_active', 'off');
            this.updateUI(false);

            this.updateLastCommandText(
                'No se pudo iniciar el reconocimiento'
            );
        }
    }

    toggleRecognition(force = null) {
        if (!this.recognition) {
            return;
        }

        const activar = force === null
            ? !this.isActive
            : Boolean(force);

        if (activar) {
            if (this.isActive) {
                return;
            }

            this.isActive = true;
            sessionStorage.setItem('voice_active', 'on');

            this.updateUI(true);
            this.startRecognition();

        } else {
            this.isActive = false;
            this.isStarting = false;

            sessionStorage.setItem('voice_active', 'off');
            this.updateUI(false);

            try {
                this.recognition.stop();
            } catch (error) {
                console.warn('No fue necesario detener el reconocimiento:', error);
            }
        }
    }

    normalizarComando(texto) {
        return texto
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .replace(/[.,;:!?¿¡]/g, '')
            .replace(/\s+/g, ' ')
            .trim();
    }

    processResult(event) {
        const resultado = event.results[event.results.length - 1];

        if (!resultado || !resultado[0]) {
            return;
        }

        const textoOriginal = resultado[0].transcript.trim();

        let comando = this.normalizarComando(textoOriginal);

        this.updateLastCommandText(`"${textoOriginal}"`);

        const numeros = {
            uno: '1',
            dos: '2',
            tres: '3',
            cuatro: '4'
        };

        comando = comando.replace(
            /\b(uno|dos|tres|cuatro)\b/g,
            palabra => numeros[palabra]
        );

        if (comando === 'apagar comando por voz') {
            this.toggleRecognition(false);
            return;
        }

        const coincidencia = comando.match(
            /^ir a(?:l)? modulo ([1-4])$/
        );

        if (coincidencia) {
            this.navigateToModule(coincidencia[1]);
            return;
        }

        console.warn('Comando no reconocido:', textoOriginal);

        this.updateLastCommandText(
            `Comando no reconocido: "${textoOriginal}"`
        );
    }

    navigateToModule(numero) {
        const rutas = {
            '1': '/modulo1',
            '2': '/modulo2',
            '3': '/modulo3',
            '4': '/modulo4'
        };

        const destino = rutas[numero];

        if (destino && window.location.pathname !== destino) {
            window.location.assign(destino);
        }
    }

    updateUI(active) {
        this.buttons.forEach(button => {
            button.classList.toggle('active', active);
        });

        this.statusTexts.forEach(elemento => {
            elemento.textContent = active ? 'Activado' : 'Desactivado';
            elemento.classList.toggle('bg-secondary', !active);
            elemento.classList.toggle('active', active);
        });

        this.btnTexts.forEach(elemento => {
            elemento.textContent = active ? 'Detener Voz' : 'Activar Voz';
        });

        const comandos = document.getElementById('voice-commands');

        if (comandos) {
            comandos.hidden = !active;
        }
    }

    updateLastCommandText(texto) {
        this.lastCommandTexts.forEach(elemento => {
            elemento.textContent = `Último comando: ${texto}`;
        });
    }

    handleError(error) {
        console.error('Error de reconocimiento:', error);

        const mensajes = {
            'not-allowed': 'Permiso del micrófono denegado',
            'service-not-allowed': 'Servicio de voz no disponible',
            'audio-capture': 'No se encontró un micrófono disponible',
            'network': 'Error de conexión del reconocimiento',
            'no-speech': 'No se detectó voz'
        };

        this.isActive = false;
        this.isStarting = false;

        sessionStorage.setItem('voice_active', 'off');
        this.updateUI(false);

        this.updateLastCommandText(
            mensajes[error] || 'Error en reconocimiento de voz'
        );
    }

    checkCompatibility() {
        if (this.recognition) {
            return;
        }

        sessionStorage.setItem('voice_active', 'off');

        this.buttons.forEach(button => {
            button.disabled = true;
            button.classList.remove('active');
        });

        this.btnTexts.forEach(elemento => {
            elemento.textContent = 'Voz no soportada';
        });

        this.statusTexts.forEach(elemento => {
            elemento.textContent = 'Navegador incompatible';
        });
    }
}

document.addEventListener('DOMContentLoaded', () => {
    window.voiceControl = new VoiceControl();
});
