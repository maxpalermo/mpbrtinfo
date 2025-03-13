{*
 * Copyright since 2007 PrestaShop SA and Contributors
 * PrestaShop is an International Registered Trademark & Property of PrestaShop SA
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Academic Free License version 3.0
 * that is bundled with this package in the file LICENSE.md.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/AFL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * @author    Massimiliano Palermo <maxx.palermo@gmail.com>
 * @copyright Since 2016 Massimiliano Palermo
 * @license   https://opensource.org/licenses/AFL-3.0 Academic Free License version 3.0
 *}

<script type="text/javascript">
    const baseAdminUrl = "{$baseAdminUrl}";
    const fetchController = "{$fetchController}";
    const ajax_controller = "{$ajax_controller}";
    const modalfetchBrt = $("#ModalFetchBrt");
    const orderId = "{$id_order}";
    const carrierId = "{$id_carrier}";
    const spinner = "{$spinner}";
</script>

<script type="module" defer>
    const baseAdminUrl = "{$baseAdminUrl}";
    const fetchController = "{$fetchController}";
    const ajax_controller = "{$ajax_controller}";
    const modalfetchBrt = $("#ModalFetchBrt");
    const orderId = "{$id_order}";
    const carrierId = "{$id_carrier}";
    const spinner = "{$spinner}";

    import * as fetchShippings from "{$module_dir}views/js/scripts/fetchShippings.js";
    // Inizializziamo il modulo fetchShippings passando l'URL del controller e le funzioni di callback
    document.addEventListener('DOMContentLoaded', () => {
        fetchShippings.init(fetchController, {
            updateProgressBar: updateProgressBar,
            completeProgress: completeProgress
        });
    });

    var controller;
    var signal;

    var current_target = null;
    var current_id_order = 0;
    var current_id_carrier = 0;
    var current_shipment_id = '';
    var current_shipment_year = "{date('Y')}";
    var total_shippings = 0;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll(".brt-info-button").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const id_order = btn.dataset.orderid;
                const tracking = btn.dataset.colloid;
                const rmn = btn.dataset.rmn;
                const rma = btn.dataset.rma;

                BrtEsiti.loadAndShowPanel(id_order, tracking);

                e.preventDefault();
                e.stopImmediatePropagation();
            });
        })

        // CONTROLLO LE SPEDIZIONI PER IL TRACKING E I CONSEGNATI
        document.getElementById("@brt-fetch-orders").addEventListener("click", async (e) => {
            Swal.fire({
                title: "Aggiornare le spedizioni Bartolini?",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sì',
                cancelButtonText: 'No'
            }).then(async (result) => {
                if (!result.isConfirmed) {
                    e.preventDefault();
                    return false;
                }

                // Inizializza il controller di abort per poter annullare le richieste
                controller = new AbortController();
                signal = controller.signal;

                // Mostra il pannello di progresso
                progressModal = Swal.fire({
                    title: 'Recupero spedizioni in corso',
                    html: '<div class="progress"><div class="progress-bar" role="progressbar" style="width: 0%" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div></div><div class="swal-progress-text">Inizializzazione...</div>',
                    showCancelButton: true,
                    showConfirmButton: false,
                    cancelButtonText: 'Annulla',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    },
                    willClose: () => {
                        if (controller) controller.abort();
                    }
                });

                try {
                    // Aggiorniamo le opzioni di fetchShippings con i valori attuali
                    fetchShippings.init(fetchController, {
                        signal: signal,
                        progressModal: progressModal,
                        updateProgressBar: updateProgressBar,
                        completeProgress: completeProgress
                    });

                    // Recupera il totale delle spedizioni
                    const shippings = await fetchShippings.fetchTotalShippings();
                    console.log("get Shippings", shippings);

                    if (!shippings) {
                        completeProgress("Errore", "Errore durante il recupero delle spedizioni.", true);
                        return;
                    }

                    // Aggiorna il pannello con il numero di spedizioni trovate
                    {literal}
                    updateProgressBar(
                        5,
                        `Trovate <strong>${shippings.list.getTracking.length}</strong> spedizioni senza tracking e <br> <strong>${shippings.list.getShipment.length}</strong> spedizioni da aggiornare`
                    );
                    {/literal}

                    //aspetto 2 secondi
                    await new Promise(resolve => setTimeout(resolve, 2000));

                    // Aggiorna le spedizioni che necessitano di tracking
                    do {
                        updateProgressBar(10, "Ricerca tracking per " + shippings.list.getTracking.length + " spedizioni");
                        shippings.list.getTracking = await fetchShippings.fetchTracking(shippings.list.getTracking);
                        if (shippings.list.getTracking === false) {
                            break;
                        }
                        console.log("get Tracking: remains" + shippings.list.getTracking.length);
                    } while (shippings.list.getTracking.length > 0);

                    // Aggiorna le informazioni delle spedizioni
                    do {
                        updateProgressBar(30, "Aggiornamento informazioni per " + shippings.list.getShipment.length + " spedizioni");
                        shippings.list.getShipment = await fetchShippings.fetchShippingsInfo(shippings.list.getShipment);
                        if (shippings.list.getShipment === false) {
                            completeProgress("Errore", "Si è verificato un errore durante l'aggiornamento delle spedizioni.", true);
                            break;
                        }
                        console.log("get Shipment: remains" + shippings.list.getShipment.length);
                    } while (shippings.list.getShipment.length > 0);

                    // Completa l'operazione
                    completeProgress("Operazione completata", "Tutte le spedizioni sono state aggiornate con successo! La pagina sarà aggiornata in 2 secondi.", false);

                    // Ricarica la pagina per mostrare i dati aggiornati
                    setTimeout(() => {
                        window.location.reload();
                    }, 2000);

                } catch (error) {
                    completeProgress("Errore", "Si è verificato un errore: " + error.message, true);
                }
            });
        });
    });



    function getTrackingManual() {
        current_shipment_id = $(document).find('#manual_shipment_id').val().trim();
        current_shipment_year = $(document).find('#manual_shipment_year').val().trim();

        if (current_shipment_id.length == 0) {
            alert ("{l s='Inserire un riferimento spedizione valido.' mod='mpbrtinfo'}");
            return false;
        }
        let data = {
            ajax: true,
            action: 'getTrackingNumber',
            id_shipment: current_shipment_id,
            id_order: current_id_order,
            id_carrier: current_id_carrier,
            year: current_shipment_year
        };

        $.post( "{$ajax_controller}", data, function(response)
        {
            $(".modal.fade.modal-brt").remove();
            let info = response;
            current_shipment_id = response.shipment_id;

            $('body').append(info.dialog);
            let modal = $(".modal.fade.modal-brt");
            $(modal).modal('show');
        });
    }

    // Variabile per memorizzare l'istanza del pannello di progresso
    let progressModal = null;

    // Funzione per aggiornare la barra di progresso
    function updateProgressBar(percentage, text) {
        const progressBar = document.querySelector('.progress-bar');
        if (progressBar) {
            progressBar.style.width = percentage + '%';
            progressBar.setAttribute('aria-valuenow', percentage);
        }

        const textElement = document.querySelector('.swal-progress-text');
        if (textElement) {
            textElement.innerHTML = text;
        }
    }

    // Funzione per completare il progresso
    function completeProgress(title, text, isError) {
        Swal.fire({
            title: title,
            text: text,
            icon: isError ? 'error' : 'success',
            confirmButtonText: 'OK'
        });
    }

    // Questa funzione è stata spostata nel modulo fetchShippings.js
    // Non è più necessaria qui

    function parseJson(json) {
        let html = "<ul>";
        $.each(json, function(key, value) {
            if (typeof value === 'object') {
                html += "<li>" + key + ": <strong>" + parseJson(value) + "</strong>\n";
            } else {
                html += "<li>" + key + ": <strong>" + value + "</strong>\n";
            }
        });
        html += "</ul>";

        return html;
    }
</script>