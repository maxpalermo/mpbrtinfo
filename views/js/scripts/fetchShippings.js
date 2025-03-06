let fetchController = "";
let abortSignal = null;
let progressModalInstance = null;
let updateProgressBarCallback = null;
let completeProgressCallback = null;

export async function init(fetchControllerUrl, options = {}) {
    fetchController = fetchControllerUrl;
    abortSignal = options.signal || null;
    progressModalInstance = options.progressModal || null;
    updateProgressBarCallback = options.updateProgressBar || null;
    completeProgressCallback = options.completeProgress || null;
}

export async function fetchTotalShippings() {
    let data = {
        ajax: true,
        action: "fetchTotalShippings"
    };

    const response = await fetch(fetchController, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        body: JSON.stringify(data)
    });

    const json = await response.json();

    if (json.status == "success") {
        return json;
    } else {
        return false;
    }
}

export async function fetchShippingsInfo(shipments_id) {
    var current_processed = 0;
    var total_shipments = shipments_id.length;
    const CHUNK_SIZE = 10;

    do {
        let chunk = shipments_id.splice(0, CHUNK_SIZE);
        let data = {
            ajax: true,
            action: "fetchShippingInfo",
            shipments_id: chunk
        };

        let response = await fetch(fetchController, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify(data),
            ...(abortSignal ? { signal: abortSignal } : {})
        });

        const json = await response.json();

        if (json.status == "success") {
            current_processed += chunk.length;
            let percProgress = Math.round((current_processed / total_shipments) * 100);

            // Verifica se l'operazione è stata annullata
            if (progressModalInstance && typeof progressModalInstance.isCancelled === "function" && progressModalInstance.isCancelled()) {
                return false;
            }

            // Aggiorna la barra di progresso
            if (updateProgressBarCallback) {
                updateProgressBarCallback(percProgress, "Processate " + current_processed + "/" + total_shipments + " spedizioni");
            }

            // Aggiorna il testo informativo nel pannello
            const textElement = document.querySelector(".swal-progress-text");
            if (textElement) {
                textElement.innerHTML = "<p>Processate " + current_processed + "/" + total_shipments + " spedizioni.</p>" + "<p>Spedizioni BRT cambiate: " + response.order_changed + ".</p>" + "<p>Tempo di esecuzione: " + response.elapsed_time + ".</p>";
            }

            // Aggiorna i dettagli tecnici
            if (progressModalInstance && typeof progressModalInstance.updateDetails === "function") {
                progressModalInstance.updateDetails("Elaborazione in corso\n" + "Processate: " + current_processed + "/" + total_shipments + "\n" + "Spedizioni cambiate: " + response.order_changed + "\n" + "Tempo di esecuzione: " + response.elapsed_time + "\n" + "Ultima risposta: " + JSON.stringify(response, null, 2));
            }
        } else {
            // Mostra un messaggio di errore nel pannello
            if (completeProgressCallback) {
                completeProgressCallback("Errore", "Errore durante il recupero delle spedizioni.", true);
            }
            return false;
        }
    } while (shipments_id.length > 0);

    return true;
}

export async function fetchTracking(list) {
    const chunk = list.splice(0, 10);
    const response = await fetch(fetchController, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-Requested-With": "XMLHttpRequest"
        },
        ...(abortSignal ? { signal: abortSignal } : {}),
        body: JSON.stringify({
            ajax: true,
            action: "fetchTracking",
            shipments_id: chunk
        })
    });

    const json = await response.json();

    if (json.status == "success") {
        return json;
    } else {
        return false;
    }
}

/**
 * Recupera le informazioni sulle spedizioni BRT
 * Utilizza il pannello SweetAlert2 con barra di progresso
 */
export async function fetchBrtInfo() {
    const shippings = await fetchTotalShippings();

    if (!shippings) {
        if (completeProgressCallback) {
            completeProgressCallback("Errore", "Errore durante il recupero delle spedizioni.", true);
        }
        return false;
    }

    //Ricerca tracking
    if (shippings.list.getTracking.length > 0) {
        if (progressModalInstance && typeof progressModalInstance.update === "function") {
            progressModalInstance.update({
                title: "Ricerca Tracking",
                text: "Aggiornamento di " + shippings.list.getTracking.length + " spedizioni in corso...",
                showConfirmButton: false
            });
        }
        await fetchTracking(shippings.list.getTracking);
    }

    //Ricerca informazioni spedizione
    if (shippings.list.getShipment.length > 0) {
        if (progressModalInstance && typeof progressModalInstance.update === "function") {
            progressModalInstance.update({
                title: "Ricerca Informazioni Spedizione",
                text: "Aggiornamento di " + shippings.list.getShipment.length + " spedizioni in corso...",
                showConfirmButton: false
            });
        }
        await fetchShippingsInfo(shippings.list.getShipment);
    }

    // Mostra un messaggio di successo nel pannello
    if (completeProgressCallback) {
        completeProgressCallback("Operazione completata", "Tutte le spedizioni sono state aggiornate con successo!", false);
    }

    // Aggiorna il pannello SweetAlert2
    if (progressModalInstance && typeof progressModalInstance.update === "function") {
        progressModalInstance.update({
            title: "Operazione completata",
            text: "Spedizioni aggiornate con successo",
            icon: "success",
            showConfirmButton: true,
            confirmButtonText: "OK"
        });
    }

    return true;
}
