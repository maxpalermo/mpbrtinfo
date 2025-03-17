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
        <span>{l s='Elenco Eventi BRT' mod='mpbrtinfo'}</span>
    </div>
    <div class="panel-body">
        <table class="table table-dark" id="table-eventi">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>ID</th>
                    <th>ID EV</th>
                    <th>EVENTO</th>
                    <th>CAMBIO STATO</th>
                    <th>EMAIL</th>
                    <th>ICONA</th>
                    <th>COLORE</th>
                    <th>SPEDITO</th>
                    <th>CONSEGNATO</th>
                </tr>
            </thead>
            <tbody>
                {assign var="counter" value=1}
                {foreach $eventi as $evento}
                    <tr>
                        <td class="text-center" style="width: 64px;"><span class="badge badge-info">{$counter++}</span></td>
                        <td class="text-left cell-id_mpbrtinfo_evento" style="width: 64px;">{$evento.id_mpbrtinfo_evento}</td>
                        <td class="text-left cell-id_evento" style="width: 64px;">{$evento.id_evento}</td>
                        <td style="width: 24rem;">{$evento.name}</td>
                        {assign var=ev value=$evento.id_mpbrtinfo_evento}
                        <td class="text-left cell-id_order_state pointer" style="width: auto;">
                            <span class="badge badge-default" name="id_order_state_change" style="border-color: {$evento.order_state_color};" data-id_evento="{$ev}" data-id_order_state="{$evento.id_order_state}">{$evento.order_state_name}</span>
                        </td>
                        <td class="text-left cell-email pointer" style="width: auto;">
                            {assign var="email" value={$evento.email|replace:'.html':''}}
                            <span class="badge badge-default" name="email_template" data-id_evento="{$ev}" data-email="{$evento.email}">{$email}</span>
                        </td>
                        <td class="text-left cell-icon pointer" style="width: auto;">
                            <div class="btn btn-default d-flex justify-content-center align-items-center" style="border: 4px double {$evento.color} !important;">
                                <span class="material-icons" style="color: {$evento.color}" name="icon" data-id_evento="{$ev}" data-icon="{$evento.icon}">{$evento.icon}</span>
                            </div>
                        </td>
                        <td class="text-left cell-color pointer" style="width: auto;">
                            <span class="badge badge-default" name="color" style="border-color: {$evento.color}; color: {$evento.color}" data-id_evento="{$ev}" data-color="{$evento.color}">{$evento.color}</span>
                        </td>
                        <td class="text-left cell-is_sent pointer" style="width: auto;">
                            <span class="material-icons is_shipped {if $evento.is_shipped}text-success{else}text-danger{/if}" name="is_shipped" data-id_evento="{$ev}" data-field="is_shipped" data-value="{$evento.is_shipped}">{if $evento.is_shipped}check_circle{else}close{/if}</span>
                        </td>
                        <td class="text-left cell-is_delivered pointer" style="width: auto;">
                            <span class="material-icons is_delivered {if $evento.is_delivered}text-success{else}text-danger{/if}" name="is_delivered" data-id_evento="{$ev}" data-field="is_delivered" data-value="{$evento.is_delivered}">{if $evento.is_delivered}check_circle{else}close{/if}</span>
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
        <button id="updateEventi" class="btn btn-default pull-right ml-4" title="{l s='Update Eventi' mod='mpbrtinfo'}">
            <div class="material-icons mr-1">save</div>
            <span>{l s='Aggiorna' mod='mpbrtinfo'}</span>
        </button>
        <button id="insertEventiSQL" class="btn btn-default pull-right mr-4" title="{l s='Insert Eventi from Sql file' mod='mpbrtinfo'}">
            <div class="material-icons mr-1">download</div>
            <span>{l s='Inserisci da SQL' mod='mpbrtinfo'}</span>
        </button>
        <button id="insertEventiSOAP" class="btn btn-default pull-right" title="{l s='Import Eventi from Brt Database' mod='mpbrtinfo'}">
            <div class="material-icons mr-1">web</div>
            <span>{l s='Importa da SOAP' mod='mpbrtinfo'}</span>
        </button>
    </div>
</div>

<script type="text/javascript">
    const adminControllerURL = "{Context::getContext()->link->getModuleLink('mpbrtinfo', 'Config')}";
    let selectOrderStates = null;
    let selectEmails = null;
    let currentOrderStateInstance = null;
    let currentEmailInstance = null;

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('.cell-id_order_state').forEach((el) => {
            el.addEventListener('click', () => { handleOrderStateClick(el); });
        });

        document.querySelectorAll('.cell-email').forEach((el) => {
            el.addEventListener('click', () => { handleEmailClick(el); });
        });

        document.querySelectorAll('.is_shipped, .is_delivered').forEach((el) => {
            el.addEventListener('click', () => { handleChangeStatusClick(el); });
        });
    })

    async function showSwalOrderStates(tr, id_event, id_order_state) {
        if (!selectOrderStates) {
            const select = document.createElement("select");
            const response = await fetch(adminControllerURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-PS-Module': 'mpbrtinfo',
                    'X-PS-Action': 'getOrderStates',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    id_evento: id_event,
                    ajax: 1,
                    action: "getOrderStates"
                })
            });
            const data = await response.json();
            const optionNull = document.createElement("option");
            optionNull.value = "";
            optionNull.textContent = "Non cambiare stato";
            select.appendChild(optionNull);
            data.options.forEach(option => {
                const optionElement = document.createElement("option");
                optionElement.value = option.id_order_state;
                optionElement.textContent = option.name;
                select.appendChild(optionElement);
            });

            select.classList.add("id_order_states", "select2");
            select.style.width = "95%";
            select.style.margin = "0 auto";
            select.name = "id_order_state";
            select.id = "id_order_state";
            select.value = id_order_state;
            selectOrderStates = select;
        } else {
            selectOrderStates.value = id_order_state;
        }

        const brtStateName = tr.querySelector('td:nth-child(5)').textContent.trim();
        Swal.fire({
            title: 'Aggiorna lo stato <br>' + brtStateName,
            html: selectOrderStates,
            showCancelButton: true,
            confirmButtonText: 'Aggiorna',
            cancelButtonText: 'Annulla',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                const response = await fetch(adminControllerURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-PS-Module': 'mpbrtinfo',
                        'X-PS-Action': 'updateOrderState',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        id_evento: id_event,
                        id_order_state: selectOrderStates.value,
                        ajax: 1,
                        action: "updateOrderState"
                    })
                });
                const data = await response.json();
                return data;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aggiornato',
                        text: result.value.message,
                    });

                    tr.querySelector('td.cell-id_order_state').innerHTML = result.value.state;

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: result.value.message,
                    });
                }
            }
        });
    }

    async function showSwalEmails(tr, id_event, email) {
        if (!selectEmails) {
            const select = document.createElement("select");
            const response = await fetch(adminControllerURL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-PS-Module': 'mpbrtinfo',
                    'X-PS-Action': 'getEmails',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({
                    id_evento: id_event,
                    ajax: 1,
                    action: "getEmails"
                })
            });
            const data = await response.json();
            const optionNull = document.createElement("option");
            optionNull.value = "";
            optionNull.textContent = "Non inviare Email";
            select.appendChild(optionNull);
            data.options.forEach(option => {
                const optionElement = document.createElement("option");
                optionElement.value = option.value;
                optionElement.textContent = option.text;
                select.appendChild(optionElement);
            });

            select.classList.add("chosen", "id_order_states");
            select.name = "email_template";
            select.id = "email_template";
            select.value = email;
            selectEmails = select;
        } else {
            selectEmails.value = email;
        }

        const brtEmail = tr.querySelector('td:nth-child(6)').textContent.trim();
        Swal.fire({
            title: 'Aggiorna l\'email <br>' + brtEmail,
            html: selectEmails,
            showCancelButton: true,
            confirmButtonText: 'Aggiorna',
            cancelButtonText: 'Annulla',
            showLoaderOnConfirm: true,
            preConfirm: async () => {
                const response = await fetch(adminControllerURL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-PS-Module': 'mpbrtinfo',
                        'X-PS-Action': 'updateEmail',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    body: JSON.stringify({
                        id_evento: id_event,
                        email: selectEmails.value,
                        ajax: 1,
                        action: "updateEmail"
                    })
                });
                const data = await response.json();
                return data;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                if (result.value.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Aggiornato',
                        text: result.value.message,
                    });

                    tr.querySelector('td.cell-email').innerHTML = result.value.state;

                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Errore',
                        text: result.value.message,
                    });
                }
            }
        });
    }

    async function handleOrderStateClick(el) {
        const span = el.querySelector('span');
        if (span) {
            const tr = el.closest('tr');
            const id_evento = span.dataset.id_evento;
            const id_order_state = span.dataset.id_order_state || 0;
            const order_state_name = span.textContent || '';
            await showSwalOrderStates(tr, id_evento, id_order_state, order_state_name);
        }
    }

    async function handleEmailClick(el) {
        const span = el.querySelector('span');
        if (span) {
            const tr = el.closest('tr');
            const id_evento = span.dataset.id_evento;
            const email = span.dataset.email || '';
            await showSwalEmails(tr, id_evento, email);
        }
    }

    async function handleChangeStatusClick(el) {
        const id_evento = el.dataset.id_evento;
        const field = el.dataset.field;
        const value = el.dataset.value;
        const response = await fetch(adminControllerURL, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-PS-Module': 'mpbrtinfo',
                'X-PS-Action': 'updateStatus',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                id_evento: id_evento,
                field: field,
                value: value,
                ajax: 1,
                action: "updateStatus"
            })
        });
        const data = await response.json();

        if (data.success) {
            el.textContent = data.icon;
            el.classList.remove('text-success', 'text-danger');
            el.classList.add(data.color);
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Errore',
                text: data.message,
            });
        }
        return data;
    }
</script>