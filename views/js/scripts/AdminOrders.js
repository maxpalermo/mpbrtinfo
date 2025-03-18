window.addEventListener("modulesReady", function () {
    //Tooltip con tippy

    tippy(".brt-info-button", {
        allowHTML: true,
        animation: "scale",
        placement: "top",
        delay: [1000, 0]
    });

    document.querySelectorAll(".brt-info-button").forEach((btn) => {
        btn.addEventListener("click", (e) => {
            const id_order = btn.dataset.id_order;
            const tracking = btn.dataset.id_collo;
            const rmn = btn.dataset.rmn;
            const rma = btn.dataset.rma;

            window.BrtEsitiInstance.loadAndShowPanel(id_order, tracking, btn);

            e.preventDefault();
            e.stopImmediatePropagation();
        });
    });

    // CONTROLLO LE SPEDIZIONI PER IL TRACKING E I CONSEGNATI
    document.getElementById("brt-fetch-orders").addEventListener("click", async (e) => {
        Swal.fire({
            title: "Aggiornare le spedizioni Bartolini?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sì",
            cancelButtonText: "No"
        }).then(async (result) => {
            if (!result.isConfirmed) {
                e.preventDefault();
                return false;
            }

            // Recupera il totale delle spedizioni
            signal = window.GetTotalShippingsInstance.setAbortSignal();
            await window.GetTotalShippingsInstance.fetchTotalShippings();
            console.log("GetTotalShippingsInstance", window.GetTotalShippingsInstance);
            // Se tutto va bene procedi
            if (window.GetTotalShippingsInstance.getStatus() == "success") {
                totalShippings = window.GetTotalShippingsInstance.getTotalShippings();
                Swal.fire({
                    title: "Ricerca spedizioni",
                    icon: "info",
                    text: `Trovate ${totalShippings} spedizioni`,
                    showConfirmButton: false,
                    showCloseButton: true,
                    loading: true,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {
                        Swal.fire({
                            title: "Operazione fermata",
                            icon: "warning",
                            text: "L'operazione è stata annullata dall'utente.",
                            showConfirmButton: false,
                            showCloseButton: true
                        });
                        return false;
                    }
                });

                // Recupera la lista delle spedizioni con tracking
                let trackingOrdersList = window.GetTotalShippingsInstance.getListTracking();
                window.GetOrderTrackingInstance.initProcess(trackingOrdersList.length);
                do {
                    let abortController = await window.GetOrderTrackingInstance.setAbortSignal();

                    trackingOrdersList = await window.GetOrderTrackingInstance.fetchOrdersTracking(trackingOrdersList);

                    let totalOrders = window.GetOrderTrackingInstance.getTotalOrders();
                    let processed = window.GetOrderTrackingInstance.getProcessed();
                    let percentage = window.GetOrderTrackingInstance.getPercentage();
                    let alert = `<div class="alert alert-info"> 
                                <p>Processate ${processed}/${totalOrders} spedizioni</p>
                                <p>Percentuale: ${percentage}%</p>
                            </div>`;

                    Swal.update({
                        title: "Ricerca Tracking",
                        icon: "info",
                        html: alert,
                        showCloseButton: true,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        willClose: () => {
                            if (abortController) abortController.abort();
                        }
                    });
                } while (trackingOrdersList.length > 0);

                let processed = window.GetOrderTrackingInstance.getProcessed();
                let total = window.GetOrderTrackingInstance.getTotalOrders();
                Swal.update({
                    html: `<div class="alert alert-success">${processed} spedizioni sono state aggiornate su un totale di ${total}!</div>`,
                    icon: "success",
                    showCloseButton: true,
                    showConfirmButton: false,
                    willClose: () => {
                        if (abortController) abortController.abort();
                    }
                });

                //aspetto 2 secondi
                await new Promise((resolve) => setTimeout(resolve, 2000));

                Swal.fire({
                    title: "Ricerca info spedizioni",
                    icon: "info",
                    text: "Ricerca info spedizioni in corso...",
                    showCloseButton: true,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {
                        if (abortController) abortController.abort();
                    }
                });

                //Recupero le informazioni delle spedizioni
                let infoOrdersList = window.GetTotalShippingsInstance.getListShipment();
                window.GetOrderInfoInstance.initProcess(infoOrdersList.length);
                do {
                    infoOrdersList = await window.GetOrderInfoInstance.fetchOrdersInfo(infoOrdersList);

                    let totalOrders = window.GetOrderInfoInstance.getTotalOrders();
                    let processed = window.GetOrderInfoInstance.getProcessed();
                    let percentage = window.GetOrderInfoInstance.getPercentage();
                    let alert = `<div class="alert alert-info"> 
                                <p>Processate ${processed}/${totalOrders} spedizioni</p>
                                <p>Percentuale: ${percentage}%</p>
                            </div>`;

                    Swal.update({
                        title: "Ricerca Info Spedizioni",
                        icon: "info",
                        html: alert,
                        showCloseButton: true,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        },
                        willClose: async () => {
                            await window.GetOrderInfoInstance.abortFetch();
                            infoOrdersList = [];
                            Swal.fire({
                                title: "Operazione fermata",
                                icon: "warning",
                                text: "L'operazione è stata annullata dall'utente.",
                                showConfirmButton: false,
                                showCloseButton: true
                            });
                        }
                    });
                } while (infoOrdersList.length > 0);

                let infoProcessed = window.GetOrderInfoInstance.getProcessed();
                let infoTotal = window.GetOrderInfoInstance.getTotalOrders();
                Swal.update({
                    html: `<div class="alert alert-success">${infoProcessed} spedizioni sono state aggiornate su un totale di ${infoTotal}!</div>`,
                    icon: "success",
                    showCloseButton: true,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.hideLoading();
                    },
                    willClose: () => {
                        //Nothing
                    }
                });
            }

            return;
        });
    });
});

/**
 * Mostra un pannello SweetAlert2 con barra di progresso
 * @param {number} progress - Percentuale iniziale della barra di progresso
 * @returns {Object} Istanza SweetAlert2 con metodi per l'aggiornamento
 */
function displayProgress(progress) {
    // Crea un nuovo controller di annullamento
    abortController = new AbortController();
    signal = abortController.signal;

    // Crea il pannello SweetAlert2 con barra di progresso
    progressModal = createProgressModal({
        title: "Aggiornamento spedizioni BRT",
        text: "Stiamo recuperando le informazioni sulle spedizioni...",
        initialProgress: progress,
        progressText: "Avanzamento: 0%",
        allowClose: false,
        showCancelButton: true,
        cancelButtonText: "Annulla",
        onCancel: () => {
            // Annulla tutte le richieste fetch in corso
            if (abortController) {
                abortController.abort();
            }

            // Mostra un messaggio di annullamento
            completeProgress("Operazione annullata", "L'operazione è stata annullata dall'utente.", true);
        },
        showDetails: true,
        detailsButtonText: "Dettagli tecnici",
        detailsText: "Inizializzazione operazione...",
        animateProgress: true
    });

    return progressModal;
}

/**
 * Completa la barra di progresso
 * @param {string} title - Nuovo titolo del pannello
 * @param {string} text - Nuovo testo del pannello
 * @param {boolean} showClose - Se mostrare il pulsante di chiusura
 */
function completeProgress(title, text, showClose = true) {
    if (progressModal) {
        progressModal.completeProgress(title, text, showClose);
    }
}

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
