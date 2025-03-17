/**
 * Modulo per la gestione di una finestra modale SweetAlert2 con barra di progresso
 *
 * Questo modulo fornisce funzioni per creare e aggiornare una finestra modale
 * con una barra di progresso utilizzando la libreria SweetAlert2.
 */

/**
 * Crea una finestra modale SweetAlert2 con una barra di progresso
 *
 * @param {Object} options - Opzioni di configurazione
 * @param {string} options.title - Titolo della finestra modale
 * @param {string} options.text - Testo descrittivo (opzionale)
 * @param {number} options.initialProgress - Valore iniziale della barra di progresso (0-100)
 * @param {string} options.progressText - Testo da visualizzare sopra la barra di progresso
 * @param {boolean} options.allowClose - Se true, mostra il pulsante di chiusura (default: false)
 * @param {string} options.closeButtonText - Testo del pulsante di chiusura (default: 'Chiudi')
 * @param {Function} options.onClose - Callback da eseguire quando la finestra viene chiusa
 * @param {boolean} options.showCancelButton - Se true, mostra il pulsante di annullamento (default: false)
 * @param {string} options.cancelButtonText - Testo del pulsante di annullamento (default: 'Annulla')
 * @param {Function} options.onCancel - Callback da eseguire quando l'operazione viene annullata
 * @param {boolean} options.showDetails - Se true, mostra il pulsante per visualizzare i dettagli (default: false)
 * @param {string} options.detailsButtonText - Testo del pulsante dei dettagli (default: 'Dettagli')
 * @param {string} options.detailsText - Testo da visualizzare nella sezione dei dettagli (default: '')
 * @param {boolean} options.animateProgress - Se true, anima la barra di progresso (default: true)
 *
 * @returns {Object} Oggetto SweetAlert2 con metodi aggiuntivi per l'aggiornamento
 */
function createProgressModal(options = {}) {
    // Valori predefiniti
    const defaults = {
        title: "Operazione in corso...",
        text: "",
        initialProgress: 0,
        progressText: "Avanzamento: 0%",
        allowClose: false,
        closeButtonText: "Chiudi",
        onClose: null,
        showCancelButton: false,
        cancelButtonText: "Annulla",
        onCancel: null,
        showDetails: false,
        detailsButtonText: "Dettagli",
        detailsText: "",
        animateProgress: true
    };

    // Variabile per memorizzare se l'operazione è stata annullata
    let isCancelled = false;

    // Unisci le opzioni fornite con i valori predefiniti
    const settings = Object.assign({}, defaults, options);

    // Crea l'HTML per la barra di progresso e i dettagli
    let progressBarHtml = `
        <div class="swal-progress-container">
            <div class="swal-progress-text">${settings.progressText}</div>
            <div class="swal-progress-bar-container">
                <div class="swal-progress-bar ${settings.animateProgress ? "animated" : ""}" style="width: ${settings.initialProgress}%"></div>
            </div>
        </div>
    `;

    // Aggiungi la sezione dei dettagli se richiesta
    if (settings.showDetails) {
        progressBarHtml += `
            <div class="swal-details-container">
                <div class="swal-details-toggle" id="swal-details-toggle">
                    <span class="swal-details-toggle-icon">+</span> ${settings.detailsButtonText}
                </div>
                <div class="swal-details-content" id="swal-details-content" style="display: none;">
                    <pre>${settings.detailsText}</pre>
                </div>
            </div>
        `;
    }

    // Stile CSS per la barra di progresso e i dettagli
    const style = document.createElement("style");
    style.textContent = `
        .swal-progress-container {
            margin-top: 15px;
            width: 100%;
        }
        .swal-progress-text {
            text-align: center;
            margin-bottom: 5px;
            font-size: 14px;
        }
        .swal-progress-bar-container {
            width: 100%;
            background-color: #f0f0f0;
            border-radius: 4px;
            height: 20px;
            overflow: hidden;
        }
        .swal-progress-bar {
            height: 100%;
            background-color: #4CAF50;
            width: ${settings.initialProgress}%;
            transition: width 0.3s ease;
        }
        .swal-progress-bar.animated {
            background-image: linear-gradient(
                -45deg, 
                rgba(255, 255, 255, .2) 25%, 
                transparent 25%, 
                transparent 50%, 
                rgba(255, 255, 255, .2) 50%, 
                rgba(255, 255, 255, .2) 75%, 
                transparent 75%, 
                transparent
            );
            background-size: 30px 30px;
            animation: progress-bar-stripes 2s linear infinite;
        }
        @keyframes progress-bar-stripes {
            from { background-position: 30px 0; }
            to { background-position: 0 0; }
        }
        .swal-details-container {
            margin-top: 15px;
            width: 100%;
            border-top: 1px solid #e0e0e0;
            padding-top: 10px;
        }
        .swal-details-toggle {
            cursor: pointer;
            color: #3085d6;
            font-size: 14px;
            text-align: center;
        }
        .swal-details-toggle:hover {
            text-decoration: underline;
        }
        .swal-details-toggle-icon {
            display: inline-block;
            width: 16px;
            text-align: center;
            transition: transform 0.3s ease;
        }
        .swal-details-toggle.open .swal-details-toggle-icon {
            transform: rotate(45deg);
        }
        .swal-details-content {
            margin-top: 10px;
            padding: 10px;
            background-color: #f8f9fa;
            border-radius: 4px;
            max-height: 200px;
            overflow-y: auto;
            font-size: 12px;
            white-space: pre-wrap;
            word-break: break-all;
        }
        .swal-details-content pre {
            margin: 0;
            white-space: pre-wrap;
            font-family: monospace;
        }
    `;
    document.head.appendChild(style);

    // Configurazione SweetAlert2
    const swalConfig = {
        title: settings.title,
        html: (settings.text ? `<p>${settings.text}</p>` : "") + progressBarHtml,
        allowOutsideClick: false,
        allowEscapeKey: false,
        showConfirmButton: settings.allowClose,
        confirmButtonText: settings.closeButtonText,
        showCancelButton: settings.showCancelButton,
        cancelButtonText: settings.cancelButtonText,
        didOpen: () => {
            // Aggiungi l'event listener per il toggle dei dettagli
            if (settings.showDetails) {
                const detailsToggle = document.getElementById("swal-details-toggle");
                const detailsContent = document.getElementById("swal-details-content");

                if (detailsToggle && detailsContent) {
                    detailsToggle.addEventListener("click", () => {
                        const isOpen = detailsContent.style.display !== "none";
                        detailsContent.style.display = isOpen ? "none" : "block";
                        detailsToggle.classList.toggle("open", !isOpen);
                    });
                }
            }
        },
        didClose: () => {
            if (typeof settings.onClose === "function") {
                settings.onClose();
            }
        },
        willClose: () => {
            // Rimuovi lo stile CSS quando la finestra viene chiusa
            if (document.head.contains(style)) {
                document.head.removeChild(style);
            }
        }
    };

    // Crea l'istanza SweetAlert2
    const swalInstance = Swal.fire(swalConfig);

    // Aggiungi l'event listener per il pulsante di annullamento
    if (settings.showCancelButton && typeof settings.onCancel === "function") {
        Swal.getCancelButton().addEventListener("click", () => {
            isCancelled = true;
            settings.onCancel();
        });
    }

    // Aggiungi metodo per aggiornare la barra di progresso
    swalInstance.updateProgress = function (percent, text) {
        updateProgressBar(percent, text);
        return this;
    };

    // Aggiungi metodo per verificare se l'operazione è stata annullata
    swalInstance.isCancelled = function () {
        return isCancelled;
    };

    // Aggiungi metodo per aggiornare i dettagli
    swalInstance.updateDetails = function (detailsText) {
        const detailsContent = document.querySelector(".swal-details-content pre");
        if (detailsContent) {
            detailsContent.textContent = detailsText;
        }
        return this;
    };

    // Aggiungi metodo per completare la barra di progresso
    swalInstance.completeProgress = function (title, text, showClose = true) {
        updateProgressBar(100);

        if (title) {
            Swal.getTitle().textContent = title;
        }

        if (text) {
            const textElement = document.querySelector(".swal-progress-text");
            if (textElement) {
                textElement.textContent = text;
            }
        }

        if (showClose) {
            Swal.getConfirmButton().style.display = "inline-block";
        }

        return this;
    };

    // Funzione interna per aggiornare la barra di progresso
    function updateProgressBar(percent, text) {
        // Assicurati che il valore sia compreso tra 0 e 100
        percent = Math.min(Math.max(percent, 0), 100);

        // Aggiorna la larghezza della barra di progresso
        const progressBar = document.querySelector(".swal-progress-bar");
        if (progressBar) {
            progressBar.style.width = `${percent}%`;
        }

        // Aggiorna il testo se fornito
        if (text !== undefined) {
            const textElement = document.querySelector(".swal-progress-text");
            if (textElement) {
                textElement.textContent = text;
            }
        }
    }

    return swalInstance;
}

/**
 * Aggiorna la barra di progresso di una finestra modale SweetAlert2 esistente
 *
 * @param {number} percent - Percentuale di completamento (0-100)
 * @param {string} text - Testo da visualizzare sopra la barra di progresso (opzionale)
 */
function updateProgressBar(percent, text) {
    // Assicurati che il valore sia compreso tra 0 e 100
    percent = Math.min(Math.max(percent, 0), 100);

    // Aggiorna la larghezza della barra di progresso
    const progressBar = document.querySelector(".swal-progress-bar");
    if (progressBar) {
        progressBar.style.width = `${percent}%`;
    }

    // Aggiorna il testo se fornito
    if (text !== undefined) {
        const textElement = document.querySelector(".swal-progress-text");
        if (textElement) {
            textElement.textContent = text;
        }
    }
}

/**
 * Mostra una notifica toast utilizzando SweetAlert2
 *
 * @param {Object} options - Opzioni di configurazione
 * @param {string} options.title - Titolo della notifica
 * @param {string} options.text - Testo della notifica
 * @param {string} options.icon - Icona della notifica (success, error, warning, info, question)
 * @param {number} options.duration - Durata in millisecondi (default: 3000)
 * @param {string} options.position - Posizione della notifica (default: 'top-end')
 */
function showToast(options = {}) {
    const defaults = {
        title: "",
        text: "",
        icon: "info",
        duration: 3000,
        position: "top-end"
    };

    const settings = Object.assign({}, defaults, options);

    const Toast = Swal.mixin({
        toast: true,
        position: settings.position,
        showConfirmButton: false,
        timer: settings.duration,
        timerProgressBar: true,
        didOpen: (toast) => {
            toast.addEventListener("mouseenter", Swal.stopTimer);
            toast.addEventListener("mouseleave", Swal.resumeTimer);
        }
    });

    Toast.fire({
        icon: settings.icon,
        title: settings.title,
        text: settings.text
    });
}

/**
 * Esempio di utilizzo:
 *
 * // Crea una finestra modale con barra di progresso
 * const modal = createProgressModal({
 *     title: 'Aggiornamento spedizioni BRT',
 *     text: 'Stiamo recuperando le informazioni sulle spedizioni...',
 *     initialProgress: 0,
 *     progressText: 'Elaborazione: 0%',
 *     allowClose: false,
 *     showCancelButton: true,
 *     cancelButtonText: 'Annulla operazione',
 *     onCancel: () => {
 *         // Gestisci l'annullamento dell'operazione
 *         console.log('Operazione annullata dall\'utente');
 *     },
 *     showDetails: true,
 *     detailsButtonText: 'Mostra dettagli tecnici',
 *     detailsText: 'Inizializzazione operazione...'
 * });
 *
 * // Aggiorna la barra di progresso
 * function updateProgress(percent) {
 *     // Verifica se l'operazione è stata annullata
 *     if (modal.isCancelled()) {
 *         return false; // Interrompi l'operazione
 *     }
 *
 *     // Aggiorna la barra di progresso
 *     modal.updateProgress(percent, `Elaborazione: ${percent}%`);
 *
 *     // Aggiorna i dettagli tecnici
 *     modal.updateDetails(`Elaborazione al ${percent}%\nUltima operazione: ...`);
 *
 *     return true; // Continua l'operazione
 * }
 *
 * // Completa l'operazione
 * function completeOperation() {
 *     modal.completeProgress(
 *         'Operazione completata',
 *         'Tutte le spedizioni sono state aggiornate con successo!',
 *         true
 *     );
 *
 *     // Mostra una notifica toast
 *     showToast({
 *         title: 'Operazione completata',
 *         text: 'Spedizioni aggiornate con successo',
 *         icon: 'success'
 *     });
 * }
 */
