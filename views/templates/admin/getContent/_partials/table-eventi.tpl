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
        <span>{l s='Events list' mod='mpbrtinfo'}</span>
    </div>
    <div class="panel-body">
        <table class="table table-dark" id="table-eventi">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>ID EV</th>
                    <th class="text-center">EVENTO</th>
                    <th class="text-center">ERRORE</th>
                    <th class="text-center">TRANSITO</th>
                    <th class="text-center">CONSEGNATO</th>
                    <th class="text-center">RIFIUTATO</th>
                    <th class="text-center">GIACENZA</th>
                    <th class="text-center">FERMO POINT</th>
                    <th class="text-center">PARTITA</th>
                </tr>
            </thead>
            <tbody>
                {assign var="counter" value=1}
                {foreach $eventi as $evento}
                    <tr>
                        <td><span class="badge badge-info">{$counter++}</span></td>
                        <td>{$evento.id_mpbrtinfo_evento}</td>
                        <td>{$evento.id_evento}</td>
                        <td>{$evento.name}</td>
                        {assign var=ev value=$evento.id_mpbrtinfo_evento}
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_error" {if $evento.is_error}checked {/if} value="{$evento.is_error}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_transit" {if $evento.is_transit}checked {/if} value="{$evento.is_transit}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_delivered" {if $evento.is_delivered}checked {/if} value="{$evento.is_delivered}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_refused" {if $evento.is_refused}checked {/if} value="{$evento.is_refused}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_waiting" {if $evento.is_waiting}checked {/if} value="{$evento.is_waiting}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_fermopoint" {if $evento.is_fermopoint}checked {/if} value="{$evento.is_fermopoint}">
                        </td>
                        <td>
                            <input data-id_evento="{$ev}" type="checkbox" class="form-control text-center" name="is_sent" {if $evento.is_sent}checked {/if} value="{$evento.is_sent}">
                        </td>
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
        <a class="btn btn-default pull-right ml-4" href="javascript:updateEventi();" title="{l s='Update Eventi' mod='mpbrtinfo'}">
            <i class="process-icon-save"></i>
            <span>{l s='Update' mod='mpbrtinfo'}</span>
        </a>
        <a class="btn btn-default pull-right mr-4" href="javascript:insertEventiSQL();" title="{l s='Insert Eventi from Sql file' mod='mpbrtinfo'}">
            <i class="process-icon-download"></i>
            <span>{l s='Insert SQL' mod='mpbrtinfo'}</span>
        </a>
        <a class="btn btn-default pull-right" href="javascript:insertEventiSOAP();" title="{l s='Import Eventi from Brt Database' mod='mpbrtinfo'}">
            <i class="process-icon-database"></i>
            <span>{l s='Insert SOAP' mod='mpbrtinfo'}</span>
        </a>
    </div>
</div>

<script type="text/javascript">
    async function insertEventiSQL() {
        eventi = await fetch(adminControllerURL, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'insertEventiSQL',
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

    async function insertEventiSOAP() {
        eventi = await fetch(adminControllerURL, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'insertEventiSOAP',
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

    async function updateEventi() {
        if (!confirm("Confermi l'aggiornamento degli eventi?")) return;

        let chk_eventi = $("#table-eventi tbody tr input[type=checkbox]:checked").map(function() {
            return { name: $(this).attr("name"), checked: $(this).is(":checked"), id: $(this).data("id_evento") };
        });
        eventi = await fetch(adminControllerURL, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'updateEventi',
                    eventi: chk_eventi
                })
            })
            .then(response => response.json())
            .then(data => {
                if ("updated" in data) {
                    alert("Operazione eseguita. Modificati " + data.updated + " eventi.");
                }
                if ("error" in data && data.error.length > 0) {
                    alert("Errori durante l'aggiornamento: " + data.errors.length);
                }
            });
    }
</script>