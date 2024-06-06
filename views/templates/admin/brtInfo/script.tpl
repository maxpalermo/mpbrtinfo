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
    const ajax_controller = "{$ajax_controller}";
    const modalfetchBrt = $("#ModalFetchBrt");
    var controller;
    var signal;

    var current_target = null;
    var current_id_order = 0;
    var current_id_carrier = 0;
    var current_shipment_id = '';
    var current_shipment_year = "{date('Y')}";
    var total_shippings = 0;

    async function getBrtInfo(order_id, tracking, target) {
        current_target = target;
        current_id_order = order_id;
        //current_id_carrier = id_carrier;

        let data = {
            ajax: true,
            action: 'postInfoBySpedizioneId',
            order_id: order_id,
            spedizione_id: tracking,
        };

        var current_icon = $(target).find('img').attr('src');
        $(target).find('img').attr('src', '{$spinner}');

        const response = await fetch(ajax_controller, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data)
            })
            .then(response => response.json())
            .then(data => {
                $("#BrtBolla").remove();
                $(target).find('img').attr('src', current_icon);

                if (data.content.error == true) {
                    alert("(" + data.content.error_code + ") " + data.content.message);
                    return false;
                }

                $("body #content.bootstrap").append(data.content);

                $("#BrtBolla").modal('show');
                return data;
            });
    }

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
                        $(modalfetchBrt).find(".modal-body").append('<div id="progressText" class="alert alert-info">Recupero delle spedizioni in corso...</div>');
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

                    $(modalfetchBrt).find('#progressFetchInfo .progress-bar')
                        .css("width", percProgress + "%")
                        .attr("aria-valuenow", percProgress)
                        .html(percProgress + " %");


                    $(modalfetchBrt)
                        .find(".modal-body #progressText")
                        .html("<p>Processate " + current_processed + "/" + total_shipments + " spedizioni.</p><p>Spedizioni BRT cambiate: " + response.order_changed + ".</p><p>Tempo di esecuzione: " + response.elapsed_time + ".</p>");
                } else {
                    $(modalfetchBrt).find(".modal-body").append(
                        $("<div>").addClass('alert alert-danger').html("Errore durante il recupero delle spedizioni.")
                    );
                    return false;
                }

            }
            while (shipments_id.length > 0);

            return true;
        };

        const shipment_ids = await fetchTotalShippings();

        if (fetchTotalShippings === false) {
            $(modalfetchBrt).find(".modal-body").html(
                $("<div>").addClass('alert alert-danger').html("Errore durante il recupero delle spedizioni.")
            );
            return false
        }

        const response = await fetchShippingsInfo(shipment_ids)
            .catch(error => {
                console.log("FETCH ERROR: ", "NAME", error.name, "MESSAGE", error.message);
                if (error.name === 'AbortError') {
                    alert("Operazione annullata.");
                } else {
                    alert("Errore durante il recupero delle spedizioni.\n" + error.message);
                }
            })
            .finally(() => {
                console.log("Operazione completata.");
            });

        if (response === false) {
            $(modalfetchBrt).find(".modal-body").html(
                $("<div>").addClass('alert alert-danger').html("Errore durante il recupero delle spedizioni.")
            );
            return false
        }

        let html = "<div class='alert alert-success'>Operazione eseguita</div>";
        $(modalfetchBrt).find(".modal-body").append(html);
    }

    function displayProgress(progress) {
        let html = "<div id='progressFetchInfo' class='progress'>";
        html += "<div class='progress-bar' role='progressbar' style='width: " + progress + "%' aria-valuenow='" + progress + "' aria-valuemin='0' aria-valuemax='100'></div>";
        html += "</div>";

        return html;
    }

    function showModalBrtFetchInfo(content) {

        $(modalfetchBrt)
            .find(".modal-body")
            .html(content);
        $(modalfetchBrt).modal('show');
    }

    function removeProgress() {
        $("#progressFetchInfo").remove();
    }

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

    $(function() {
        $('body #content.bootstrap').append($("#ModalFetchBrt").detach());

        $("#ModalFetchBrt").on("hide.bs.modal", function() {
            controller.abort();
        });

        $("#ModalFetchBrt").on("show.bs.modal", function() {
            controller = new AbortController();
            signal = controller.signal;
        });

        $(".brt-info-button").on("click", function() {
            let tracking = $(this).data('tracking');
            let order_id = $(this).data('order_id');
            let rmn = $(this).data('rmn');
            let rma = $(this).data('rma');

            getBrtInfo(order_id, tracking, this);
        });

        $("#brt-fetch-orders").on('click', function(evt) {
                if (confirm("{l s='Aggiornare le spedizioni Bartolini?' mod='mpbrtinfo'}") == false) {
                evt.preventDefault();
                return false;
            }

            const pb = displayProgress(0); showModalBrtFetchInfo(pb);

            fetchBrtInfo();
        });


    });
</script>