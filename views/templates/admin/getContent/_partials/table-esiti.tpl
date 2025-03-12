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
<div class="panel">
    <div class="panel-heading">
        <i class="icon icon-list"></i>
        <span>{l s='Esiti list' mod='mpbrtinfo'}</span>
    </div>
    <div class="panel-body">
        <table class="table table-dark" id="table-esiti">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>ESITO</th>
                    <th>TESTO1</th>
                    <th>TESTO2</th>
                </tr>
            </thead>
            <tbody>
                {foreach $esiti as $key=>$esito}
                    <tr>
                        <td><span class="badge badge-info">{$key+1}</span></td>
                        <td>{$esito.id_mpbrtinfo_esito}</td>
                        <td>{$esito.id_esito}</td>
                        <td>{$esito.testo1}</td>
                        <td>{$esito.testo2}</td>
                    </tr>
                {/foreach}
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="8">
                        <hr>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
    <div class="panel-footer">
        <a class="btn btn-default pull-right" href="javascript:insertEsitiSQL();" title="{l s='Insert Esiti from Sql file' mod='mpbrtinfo'}">
            <i class="process-icon-download"></i>
            <span>{l s='Insert SQL' mod='mpbrtinfo'}</span>
        </a>
        <a class="btn btn-default pull-right" href="javascript:insertEsitiSOAP();" title="{l s='Import Esiti from Brt Database' mod='mpbrtinfo'}">
            <i class="process-icon-database"></i>
            <span>{l s='Insert SOAP' mod='mpbrtinfo'}</span>
        </a>
    </div>
</div>

<script type="text/javascript">
    async function insertEsitiSQL() {
        esiti = await fetch(adminControllerURL, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'insertEsitiSQL',
                })
            })
            .then(response => response.json())
            .then(data => {
                if ("inserted" in data) {
                    alert("Operazione eseguita. Inseriti " + data.inserted.length + " nuovi eventi.");
                }
                if ("error" in data && data.error.length > 0) {
                    alert("Errori durante l'inserimento: " + data.errors.length);
                }
            });
    }

    async function insertEsitiSOAP() {
        esiti = await fetch(adminControllerURL, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'insertEsitiSQL',
                })
            })
            .then(response => response.json())
            .then(data => {
                if ("inserted" in data) {
                    alert("Operazione eseguita. Inseriti " + data.inserted.length + " nuovi esiti.");
                }
                if ("error" in data && data.error.length > 0) {
                    alert("Errori durante l'inserimento: " + data.errors.length);
                }
            });
    }

    function updateEsiti() {
        let tr = $('#table-esiti tbody tr');
        let rows = [];
        $.each(tr, function() {
            let values = [];
            let value = false;
            for (let i = 0; i < $(this).find('td').length; i++) {
                const element = $(this).find('td:nth-child(' + (i + 1) + ')');
                value = String($(element).text()).trim();

                values.push(value);
            }
            let row = {
                id_mpbrtinfo_esito: values[0],
                testo1: values[1],
                testo2: values[2],
            };
            rows.push(row);
        });

        let data = {
            ajax: true,
            action: 'updateEsiti',
            rows: JSON.stringify(rows)
        };

        $.post( "{$adminControllerURL}", data, function(response)
        {
            if (response.errors.length) {
                $.growl.error({ title: "{l s='Update esiti' mod='mpbrtinfo'}", message: response.errors.join("<br>") });
            } else {
                $.growl.notice({ title: "{l s='Update esiti' mod='mpbrtinfo'}", message: "{l s='Update success' mod='mpbrtinfo'}" });
            }
        });
    }
</script>