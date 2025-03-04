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
        title: '{l s="Aggiornamento spedizioni BRT" mod="mpbrtinfo"}',
        text: '{l s="Stiamo recuperando le informazioni sulle spedizioni..." mod="mpbrtinfo"}',
        initialProgress: progress,
        progressText: '{l s="Avanzamento: 0%" mod="mpbrtinfo"}',
        allowClose: false,
        showCancelButton: true,
        cancelButtonText: '{l s="Annulla" mod="mpbrtinfo"}',
        onCancel: () => {
            // Annulla tutte le richieste fetch in corso
            if (abortController) {
                abortController.abort();
            }

            // Mostra un messaggio di annullamento
            completeProgress('{l s="Operazione annullata" mod="mpbrtinfo"}', '{l s="L\'operazione è stata annullata dall\'utente." mod="mpbrtinfo"}', true);
        },
        showDetails: true,
        detailsButtonText: '{l s="Dettagli tecnici" mod="mpbrtinfo"}',
        detailsText: '{l s="Inizializzazione operazione..." mod="mpbrtinfo"}',
        animateProgress: true
    });

    return progressModal;
}

async function getBrtInfo(order_id, tracking, target) {
    current_target = target;
    current_id_order = order_id;
    //current_id_carrier = id_carrier;

    let data = {
        ajax: true,
        action: "postInfoBySpedizioneId",
        order_id: order_id,
        spedizione_id: tracking
    };

    var current_icon = $(target).find("img").attr("src");
    $(target).find("img").attr("src", spinner);

    const response = await fetch(baseAdminUrl, {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify(data)
    })
        .then((response) => response.json())
        .then((data) => {
            $("#BrtBolla").remove();
            $(target).find("img").attr("src", current_icon);

            if (data.content.error == true) {
                alert("(" + data.content.error_code + ") " + data.content.message);
                return false;
            }

            $("body").append(data.content);

            $("#BrtBolla").modal("show");
            return data;
        });
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
 * Funzione legacy per compatibilità con il codice esistente
 * @param {string} content - Contenuto HTML da mostrare (non utilizzato)
 */

function showModalBrtFetchInfo(content) {
    // Non fa nulla, mantenuta per compatibilità
    // Il pannello viene già mostrato dalla funzione displayProgress
}

function removeProgress() {
    $("#progressFetchInfo").remove();
}
