/**
 * Template per SweetAlert2 per visualizzare l'esito delle chiamate SOAP a Bartolini
 * @author Massimiliano Palermo
 * @version 1.0.0
 */

export class BrtEsiti {
    constructor(adminControllerURL, translations = {}, abortSignal = null) {
        this.adminControllerURL = adminControllerURL || "";
        this.translations = translations || {};
        this.abortSignal = abortSignal || null;
    }

    /**
     * Mostra il pannello con i dettagli della spedizione BRT
     * @param {Object} data - Dati della spedizione
     * @param {string} data.tracking_number - Numero di tracking
     * @param {string} data.data_spedizione - Data della spedizione
     * @param {string} data.porto - Tipo di porto
     * @param {string} data.servizio - Tipo di servizio
     * @param {number} data.colli - Numero di colli
     * @param {string} data.peso - Peso della spedizione
     * @param {string} data.natura - Natura della merce
     * @param {Object} data.stato_attuale - Stato attuale della spedizione
     * @param {string} data.stato_attuale.evento - Descrizione dell'evento
     * @param {string} data.stato_attuale.data - Data dell'evento
     * @param {string} data.stato_attuale.filiale - Filiale dell'evento
     * @param {string} data.stato_attuale.tipo - Tipo di evento (consegnato, transito, errore, ecc.)
     * @param {Array} data.storico - Storico degli eventi
     * @param {number} data.id_order - ID dell'ordine
     */
    async showPanel(html, btn) {
        // Configurazione e visualizzazione di SweetAlert2
        Swal.fire({
            title: "Dettagli Spedizione BRT",
            html: html,
            width: "70%",
            showCloseButton: true,
            showCancelButton: false,
            showClass: {
                popup: "animate__animated animate__fadeInDown"
            },
            hideClass: {
                popup: "animate__animated animate__fadeOutUp"
            },
            customClass: {
                container: "brt-swal-container",
                popup: "brt-swal-popup",
                content: "brt-swal-content"
            }
        }).then(async () => {
            const response = await fetch(this.adminControllerURL, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-Requested-With": "XMLHttpRequest"
                },
                body: JSON.stringify({
                    ajax: true,
                    action: "updateIcon",
                    id_order: btn.dataset.id_order
                })
            });
            const json = await response.json();

            //se c'è l'elemento img in btn lo elimino e lo cambio con l'icona
            const img = btn.querySelector("img");
            if (img) {
                img.remove();
            }
            //se c'è lelemento div in btn lo elimino e lo cambio con l'icona
            const div = btn.querySelector("div");
            if (div) {
                div.remove();
            }
            //sostituisco l'icona
            btn.innerHTML = `
                <div style="width: 48px; height: 48px; border: 4px double ${json.color}; display: flex; justify-content: center; align-items: center;">
                    <div class="material-icons" style="color: ${json.color};">
                        ${json.icon}
                    </div>
                </div>
            `;
        });
    }

    /**
     * Carica i dati della spedizione e mostra il pannello
     * @param {number} id_order - ID dell'ordine
     * @param {string} spedizione_id - Numero di tracking
     */
    async loadAndShowPanel(id_order, spedizione_id, btn) {
        // Mostra un loader mentre si caricano i dati
        Swal.fire({
            title: "Caricamento in corso...",
            html: "Recupero informazioni sulla spedizione BRT",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        const response = await fetch(this.adminControllerURL, {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                ajax: true,
                action: "showInfoPanel",
                id_order: id_order,
                spedizione_id: spedizione_id
            })
        });

        const json = await response.json();
        if (json.success) {
            Swal.close();
            this.showPanel(json.html, btn);
        } else {
            Swal.fire({
                title: "Errore",
                text: json.message || "Impossibile recuperare i dettagli della spedizione.",
                icon: "error",
                confirmButtonColor: "#dc3545"
            });
        }
    }
}

// Esempio di utilizzo:
// BrtEsiti.loadAndShowPanel(123, 'ABC123456789');
