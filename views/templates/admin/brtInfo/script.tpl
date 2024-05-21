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
    var current_target = null;
    var current_id_order = 0;
    var current_id_carrier = 0;
    var current_shipment_id = '';
    var current_shipment_year = "{date('Y')}";

    function getBrtInfo(order_id, tracking, target) {
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

        $.post( "{$ajax_controller}", data, function(response)
        {
            $("#BrtBolla").remove();
            $("body #content").append(response.content);

            $("#BrtBolla").modal('show');
            $(target).find('img').attr('src', current_icon);
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

    $(function() {
        $("#toolbar-nav li:last").after($("<li>").append($("#brt-toolbar-button").detach()));

        $(".brt-info-button").on("click", function() {
            let tracking = $(this).data('tracking');
            let order_id = $(this).data('order_id');
            getBrtInfo(order_id, tracking, this);
        });

        $("#brt-toolbar-button").on('click', function(evt) {
            if (confirm("{l s='Cercare le informazioni sulle spedizioni?' mod='mpbrtinfo'}") == false)
            {
                evt.preventDefault();
                return false;
            }

            return true;
        });
    });
</script>