/**
 * Template per SweetAlert2 per visualizzare l'esito delle chiamate SOAP a Bartolini
 * @author Massimiliano Palermo
 * @version 1.0.0
 */

const BrtEsiti = {
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
    showPanel: function (data) {
        // Costruzione della tabella dello storico
        let storicoHtml = "";
        if (data.storico && data.storico.length > 0) {
            storicoHtml = `
                <div class="table-responsive mt-4">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-light">
                            <tr>
                                <th>#</th>
                                <th>Icona</th>
                                <th>Data</th>
                                <th>Evento</th>
                                <th>Filiale</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            data.storico.forEach((evento, index) => {
                const tipo = evento.tipo || "sconosciuto";
                const icon = evento.icon || "help";

                storicoHtml += `
                    <tr>
                        <td>${index + 1}</td>
                        <td style="width: 72px; text-align: center;"><i class="material-icons text-${evento.color}">${icon}</i></td>
                        <td>${evento.data}</td>
                        <td>${evento.descrizione}</td>
                        <td>${evento.filiale}</td>
                    </tr>
                `;
            });

            storicoHtml += `
                        </tbody>
                    </table>
                </div>
            `;
        } else {
            storicoHtml = '<div class="alert alert-info mt-4">Nessun evento storico disponibile</div>';
        }

        // Costruzione del contenuto HTML completo
        const html = `
            <div class="brt-esiti-container">
                <h4 class="text-center mb-4">BOLLA N. <strong>${data.tracking_number}</strong> DEL <strong>${data.data_spedizione}</strong></h4>
                <h5>ORDINE N. ${data.id_order}</h5>
                <div class="row">
                    <!-- Tabella SPEDIZIONE -->
                    <div class="col-md-6">
                        <table class="table table-bordered mb-0">
                            <tbody>
                            <tr><th colspan="2">SPEDIZIONE</th></tr>
                                <tr>
                                    <th scope="row" width="40%">PORTO</th>
                                    <td>${data.porto}</td>
                                </tr>
                                <tr>
                                    <th scope="row">SERVIZIO</th>
                                    <td>${data.servizio}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Tabella MERCE -->
                    <div class="col-md-6">
                        <table class="table table-bordered mb-0">
                            <tbody>
                                <tr><th colspan="2">MERCE</th></tr>
                                <tr>
                                    <th scope="row" width="40%">COLLI</th>
                                    <td>${data.colli}</td>
                                </tr>
                                <tr>
                                    <th scope="row">PESO</th>
                                    <td>${data.peso}</td>
                                </tr>
                                <tr>
                                    <th scope="row">NATURA</th>
                                    <td>${data.natura}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- Storico eventi -->
                <hr class="mt-2 mb-2" />
                <h3 class="mt-3 mb-3">STORICO EVENTI</h3>
                ${storicoHtml}
            </div>
        `;

        // Configurazione e visualizzazione di SweetAlert2
        Swal.fire({
            title: "Dettagli Spedizione BRT",
            html: html,
            width: "70%",
            showCloseButton: false,
            showCancelButton: true,
            showClass: {
                popup: "animate__animated animate__fadeInDown"
            },
            hideClass: {
                popup: "animate__animated animate__fadeOutUp"
            },
            closeOnCancel: true,
            focusConfirm: false,
            confirmButtonText: "Imposta come Consegnato",
            confirmButtonColor: "#28a745",
            cancelButtonText: "Chiudi",
            cancelButtonColor: "#6c757d",
            customClass: {
                container: "brt-swal-container",
                popup: "brt-swal-popup",
                content: "brt-swal-content"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                // Azione per impostare l'ordine come consegnato
                BrtEsiti.setOrderAsDelivered(data.id_order);
            }
        });
    },

    /**
     * Imposta l'ordine come consegnato
     * @param {number} id_order - ID dell'ordine
     */
    setOrderAsDelivered: function (id_order) {
        // Mostra un messaggio di conferma
        Swal.fire({
            title: "Conferma",
            text: `Sei sicuro di voler impostare l'ordine #${id_order} come consegnato?`,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Sì, conferma",
            cancelButtonText: "Annulla",
            confirmButtonColor: "#28a745",
            cancelButtonColor: "#6c757d"
        }).then((result) => {
            if (result.isConfirmed) {
                // Qui inserire la chiamata AJAX per impostare l'ordine come consegnato
                // Esempio:
                $.ajax({
                    url: baseAdminUrl + "&ajax=1&action=setOrderAsDelivered",
                    type: "POST",
                    data: {
                        id_order: id_order
                    },
                    dataType: "json",
                    success: function (response) {
                        if (response.success) {
                            Swal.fire({
                                title: "Successo",
                                text: "Ordine impostato come consegnato con successo!",
                                icon: "success",
                                confirmButtonColor: "#28a745"
                            }).then(() => {
                                // Ricarica la pagina per mostrare lo stato aggiornato
                                window.location.reload();
                            });
                        } else {
                            Swal.fire({
                                title: "Errore",
                                text: response.message || "Si è verificato un errore durante l'impostazione dell'ordine come consegnato.",
                                icon: "error",
                                confirmButtonColor: "#dc3545"
                            });
                        }
                    },
                    error: function () {
                        Swal.fire({
                            title: "Errore",
                            text: "Si è verificato un errore di comunicazione con il server.",
                            icon: "error",
                            confirmButtonColor: "#dc3545"
                        });
                    }
                });
            }
        });
    },

    /**
     * Carica i dati della spedizione e mostra il pannello
     * @param {number} id_order - ID dell'ordine
     * @param {string} tracking_number - Numero di tracking
     */
    loadAndShowPanel: function (id_order, tracking_number) {
        console.log("loadAndShowPanel", id_order, tracking_number);

        // Mostra un loader mentre si caricano i dati
        Swal.fire({
            title: "Caricamento in corso...",
            html: "Recupero informazioni sulla spedizione BRT",
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Chiamata AJAX per recuperare i dati della spedizione
        $.ajax({
            url: baseAdminUrl,
            type: "POST",
            data: {
                id_order: id_order,
                tracking_number: tracking_number,
                ajax: 1,
                action: "getBrtShipmentDetails"
            },
            dataType: "json",
            success: function (response) {
                if (response.success) {
                    // Chiudi il loader e mostra il pannello con i dati
                    Swal.close();
                    BrtEsiti.showPanel(response.data);
                } else {
                    Swal.fire({
                        title: "Errore",
                        text: response.message || "Impossibile recuperare i dettagli della spedizione.",
                        icon: "error",
                        confirmButtonColor: "#dc3545"
                    });
                }
            },
            error: function () {
                Swal.fire({
                    title: "Errore",
                    text: "Si è verificato un errore di comunicazione con il server.",
                    icon: "error",
                    confirmButtonColor: "#dc3545"
                });
            }
        });
    }
};

// Aggiungi stili CSS personalizzati per il pannello
$("head").append(`
    <style>
        .brt-swal-container {
            z-index: 9999;
        }
        .brt-swal-popup {
            padding: 1.25rem;
        }
        .brt-swal-content {
            font-size: 0.9rem;
        }
        .brt-esiti-container .table th {
            background-color: #f8f9fa;
        }
        .brt-esiti-container .card-header {
            padding: 0.5rem 1rem;
        }
        .brt-esiti-container .card-body {
            padding: 0;
        }
        .brt-esiti-container .table {
            margin-bottom: 0;
        }
        .brt-esiti-container .table td, 
        .brt-esiti-container .table th {
            padding: 0.5rem;
        }
    </style>
`);

// Esempio di utilizzo:
// BrtEsiti.loadAndShowPanel(123, 'ABC123456789');
