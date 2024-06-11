{**
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

<div id="ModalQueryBrt" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="ModalQueryBrtTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content" style="height: 90vh;">
            <div class="modal-header" styel="height: auto;">
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h3 class="modal-title" id="ModalQueryBrtTitle">Inserisci una query</h3>
            </div>
            <div class="modal-body" style="height: auto; overflow: hidden;">
                <div class="form-group">
                    <div class="alert alert-warning" role="alert">
                        Attenzione! L'uso improprio di questa funzione potrebbe compromettere il corretto funzionamento del database.
                    </div>
                    <textarea id="query-text" class="form-control" name="query-text" rows="4" cols="40" style="font-family: 'Courier New', Courier, monospace;"></textarea>
                </div>
            </div>
            <div class="modal-body mt-4" style="height: 47vh; overflow-y: auto;">
                <div id="query-result" style="font-family:'Courier New', Courier, monospace, width: 100%; height: 100%; overflow-y: auto;"></div>
            </div>
            <div class="modal-footer" style="height: 48px;">
                <button class="btn btn-default pull-right" type="button" id="btn-query">
                    <i class="process-icon-database"></i>
                    <span>{l s='Esegui Query' mod='mpbrtinfo'}</span>
                </button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    async function execQuery(query) {
            const response = await fetch('{$ajax_controller}',
            {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    ajax: true,
                    action: 'execQuery',
                    query: query
                })
            })
        .then(response => response.json())
        .then(data => {
            if (data.status == 'success') {
                $("#query-result").html("<div class='alert alert-success'>Righe interessate: " + data.rows_affected + "</div><p>Risultato:</p><pre>" + JSON.stringify(data.result, undefined, 4) + "</pre></div>");
                alert('Query eseguita correttamente');
            } else {
                $("#query-result").html("<br><div class='alert alert-danger'>Errore:\n" + data.message + "<br>" + data.error + "</div>");
                alert("Errore durante l\'esecuzione della query");
            }
        });
    }
    $(function() {
        $(document).keypress("q", function(evt) {
            if (evt.ctrlKey && evt.shiftKey) {
                $("#ModalQueryBrt").modal('show');
            }
        });

        $("#btn-query").on('click', function() {
            if (confirm('Sei sicuro di voler eseguire questa query?') == false) {
                return;
            }

            var query = $("#query-text").val();
            execQuery(query);
        });
    });
</script>