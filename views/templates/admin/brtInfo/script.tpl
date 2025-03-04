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
    const ajax_controller = "{$ajax_controller}";
    const modalfetchBrt = $("#ModalFetchBrt");
    const orderId = "{$id_order}";
    const carrierId = "{$id_carrier}";
    const spinner = "{$spinner}";

    var controller;
    var signal;

    var current_target = null;
    var current_id_order = 0;
    var current_id_carrier = 0;
    var current_shipment_id = '';
    var current_shipment_year = "{date('Y')}";
    var total_shippings = 0;

    document.addEventListener('DOMContentLoaded', () => {
        $("#ModalFetchBrt").on("hide.bs.modal", function() {
            controller.abort();
        });

        $("#ModalFetchBrt").on("show.bs.modal", (e) => {
            e.preventDefault();
            e.stopImmediatePropagation();

            controller = new AbortController();
            signal = controller.signal;
        });

        document.querySelectorAll(".brt-info-button").forEach((btn) => {
            btn.addEventListener("click", (e) => {
                const id_order = btn.dataset.order_id;
                const tracking = btn.dataset.tracking;
                const rmn = btn.dataset.rmn;
                const rma = btn.dataset.rma;

                BrtEsiti.loadAndShowPanel(id_order, tracking);

                e.preventDefault();
                e.stopImmediatePropagation();

                //getBrtInfo(order_id, tracking, this);
            });
        })

        document.getElementById("brt-fetch-orders").addEventListener("click", (e) => {
            Swal.fire({
                title: "{l s='Aggiornare le spedizioni Bartolini?' mod='mpbrtinfo'}",
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sì',
                cancelButtonText: 'No'
            }).then((result) => {
                if (!result.isConfirmed) {
                    e.preventDefault();
                    return false;
                }

                const pb = displayProgress(0);
                showModalBrtFetchInfo(pb);

                fetchBrtInfo();
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

    /**
     * Recupera le informazioni sulle spedizioni BRT
     * Utilizza il pannello SweetAlert2 con barra di progresso
     */
    async function fetchBrtInfo() {
        const fetchTotalShippings = async () => {
            let data = {
                ajax: true,
                action: 'fetchTotalShippings'
            };

            return await fetch(ajax_controller, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                })
                .then(response => response.json())
                .then(data => {
                    if (data.status == 'success') {
                        // Aggiorna il testo informativo nel pannello
                        const textElement = document.querySelector('.swal-progress-text');
                        if (textElement) {
                            textElement.textContent = '{l s="Recupero delle spedizioni in corso..." mod="mpbrtinfo"}';
                        }
                        total_shippings = data['total_shippings'];
                        return total_shippings;
                    } else {
                        return false;
                    }
                });
        };

        const fetchShippingsInfo = async (shipments_id) => {
            var current_processed = 0;
            var total_shipments = shipments_id.length;
            const CHUNK_SIZE = 10;

            do {
                let chunk = shipments_id.splice(0, CHUNK_SIZE);

                let data = {
                    ajax: true,
                    action: 'fetchShippingInfo',
                    shipments_id: chunk
                };

                let response = await fetch(ajax_controller, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify(data),
                        signal: signal
                    })
                    .then(response => response.json())
                    .then(data => {
                        return data;
                    });

                if (response.status == 'success') {
                    current_processed += chunk.length;
                    let percProgress = Math.round((current_processed / total_shipments) * 100);

                    // Verifica se l'operazione è stata annullata
                    if (progressModal && progressModal.isCancelled()) {
                        return false;
                    }

                    // Aggiorna la barra di progresso
                    updateProgressBar(
                        percProgress,
                        '{l s="Processate" mod="mpbrtinfo"} ' + current_processed + '/' + total_shipments + ' {l s="spedizioni" mod="mpbrtinfo"}'
                    );

                    // Aggiorna il testo informativo nel pannello
                    const textElement = document.querySelector('.swal-progress-text');
                    if (textElement) {
                        textElement.innerHTML =
                            '<p>{l s="Processate" mod="mpbrtinfo"} ' + current_processed + '/' + total_shipments + ' {l s="spedizioni" mod="mpbrtinfo"}.</p>' +
                            '<p>{l s="Spedizioni BRT cambiate" mod="mpbrtinfo"}: ' + response.order_changed + '.</p>' +
                            '<p>{l s="Tempo di esecuzione" mod="mpbrtinfo"}: ' + response.elapsed_time + '.</p>';
                    }

                    // Aggiorna i dettagli tecnici
                    if (progressModal) {
                        progressModal.updateDetails(
                            '{l s="Elaborazione in corso" mod="mpbrtinfo"}\n' +
                            '{l s="Processate" mod="mpbrtinfo"}: ' + current_processed + '/' + total_shipments + '\n' +
                            '{l s="Spedizioni cambiate" mod="mpbrtinfo"}: ' + response.order_changed + '\n' +
                            '{l s="Tempo di esecuzione" mod="mpbrtinfo"}: ' + response.elapsed_time + '\n' +
                            '{l s="Ultima risposta" mod="mpbrtinfo"}: ' + JSON.stringify(response, null, 2)
                        );
                    }
                } else {
                    // Mostra un messaggio di errore nel pannello
                    completeProgress(
                        '{l s="Errore" mod="mpbrtinfo"}',
                        '{l s="Errore durante il recupero delle spedizioni." mod="mpbrtinfo"}',
                        true
                    );
                    return false;
                }

            }
            while (shipments_id.length > 0);

            return true;
        };

        const shipment_ids = await fetchTotalShippings();

        if (fetchTotalShippings === false) {
            // Mostra un messaggio di errore nel pannello
            completeProgress(
                '{l s="Errore" mod="mpbrtinfo"}',
                '{l s="Errore durante il recupero delle spedizioni." mod="mpbrtinfo"}',
                true
            );
            return false;
        }

        const response = await fetchShippingsInfo(shipment_ids)
            .catch(error => {
                console.log("FETCH ERROR: ", "NAME", error.name, "MESSAGE", error.message);
                if (error.name === 'AbortError') {
                    completeProgress(
                        "{l s="Operazione annullata" mod="mpbrtinfo"}",
                        "{l s="L\'operazione è stata annullata." mod="mpbrtinfo"}",
                        true
                    );
                } else {
                    completeProgress(
                        "{l s="Errore" mod="mpbrtinfo"}",
                        "{l s="Errore durante il recupero delle spedizioni:" mod="mpbrtinfo"} " + error.message,
                        true
                    );
                }
            })
            .finally(() => {
                console.log("Operazione completata.");
            });

        if (response === false) {
            // Mostra un messaggio di errore nel pannello
            completeProgress(
                "{l s="Errore" mod="mpbrtinfo"}",
                "{l s="Errore durante il recupero delle spedizioni." mod="mpbrtinfo"}",
                true
            );
            return false;
        }

        // Mostra un messaggio di successo nel pannello
        completeProgress(
            "{l s="Operazione completata" mod="mpbrtinfo"}",
            "{l s="Tutte le spedizioni sono state aggiornate con successo!" mod="mpbrtinfo"}",
            true
        );

        // Mostra una notifica toast
        showToast({
            title: "{l s="Operazione completata" mod="mpbrtinfo"}",
            text: "{l s="Spedizioni aggiornate con successo" mod="mpbrtinfo"}",
            icon: 'success',
            duration: 5000
        });
    }

    // Variabile per memorizzare l'istanza del pannello di progresso
    let progressModal = null;

    // Variabile per memorizzare il controller di annullamento
    let abortController = null;

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