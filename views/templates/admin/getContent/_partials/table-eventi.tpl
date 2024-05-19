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
                {foreach $eventi as $key => $evento}
                    <tr>
                        <td><span class="badge badge-info">{$key+1}</span></td>
                        <td>{$evento.id_mpbrtinfo_evento}</td>
                        <td>{$evento.id_evento}</td>
                        <td>{$evento.name}</td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_error" {if $evento.is_error}checked {/if} value="{$evento.is_error}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_transit" {if $evento.is_transit}checked {/if} value="{$evento.is_transit}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_delivered" {if $evento.is_delivered}checked {/if} value="{$evento.is_delivered}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_refused" {if $evento.is_refused}checked {/if} value="{$evento.is_refused}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_waiting" {if $evento.is_waiting}checked {/if} value="{$evento.is_waiting}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_fermopoint" {if $evento.is_fermopoint}checked {/if} value="{$evento.is_fermopoint}">
                        </td>
                        <td>
                            <input type="checkbox" class="form-control text-center" name="is_sent" {if $evento.is_sent}checked {/if} value="{$evento.is_sent}">
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
        <a class="btn btn-default pull-right" href="{$url_sql_eventi}" title="{l s='Insert Eventi from Sql file' mod='mpbrtinfo'}">
            <i class="process-icon-download"></i>
            <span>{l s='Insert SQL' mod='mpbrtinfo'}</span>
        </a>
        <a class="btn btn-default pull-right" href="{$url_soap_eventi}" title="{l s='Import Eventi from Brt Database' mod='mpbrtinfo'}">
            <i class="process-icon-database"></i>
            <span>{l s='Insert SOAP' mod='mpbrtinfo'}</span>
        </a>
        <a class="btn btn-default pull-right" href="javascript:updateEventi();" title="{l s='Update Eventi' mod='mpbrtinfo'}">
            <i class="process-icon-save"></i>
            <span>{l s='Update' mod='mpbrtinfo'}</span>
        </a>
    </div>
</div>

<script type="text/javascript">
    async function updateEventi() {
        eventi = await fetch(ajax_controller, {
                method: 'POST',
                headers: new Headers({
                    'Content-Type': 'application/json; charset=UTF-8'
                }),
                body: JSON.stringify({
                    ajax: 1,
                    action: 'updateEventi',
                })
            })
            .then(response => response.json())
            .then(data => {
                if ("updated" in data) {
                    alert("Operazione eseguita. Inseriti " + data.updated.length + " nuovi eventi.");
                }
                if ("error" in data && data.error.length > 0) {
                    alert("Errori durante l'aggiornamento: " + data.errors.length);
                }
            });
    }
</script>