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

<div id="eventi" class="modal fade modal-brt" tabindex="-1" role="dialog" aria-labelledby="eventi-title"
    aria-hidden="true" data-backdrop="false" data-keyboard="false">
    <style>
        .modal-brt .panel-heading {
            margin-bottom: 1rem;
            font-size: 2rem;
            font-weight: bold;
        }

        .modal-brt .text-right {
            text-align: right;
            padding-right: 1rem;
        }

        .pt-6 {
            padding-top: 6px;
        }

        .lbl {
            display: block;
            height: 31px;
            padding: 6px 8px;
            font-size: 12px;
            font-weight: normal !important;
            line-height: 1.42857;
            color: #555;
            background-color: #F5F8F9 !important;
            background-image: none;
            border: 1px solid #C7D6DB !important;
            border-radius: 3px;
            -webkit-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
            -o-transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
            transition: border-color ease-in-out 0.15s, box-shadow ease-in-out 0.15s;
            text-align: right;
        }

        #collapseOne fieldset {
            margin-bottom: 2rem;
        }
    </style>
    <div class="modal-dialog modal-dialog-centered modal-eventi" role="document">
        <div class="modal-content">
            <div class="modal-body" style="overflow-y: auto; max-height: 60vh;">
                <div class="accordion" id="accordionSpedizione">
                    <div class="panel">
                        <div class="panel-heading collapsed pointer" id="headingOne" data-toggle="collapse"
                            data-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                            <span class="icon icon-info text-info" style="margin-right: 8px;"></span>
                            <span>{l s="Informazioni sulla spedizione (fai click per aprire/chiudere)" mod="mpbrtinfo"}</span>
                        </div>
                        <div id="collapseOne" class="collapse" aria-labelledby="headingOne"
                            data-parent="#accordionSpedizione">
                            <div class="panel-body">
                                <fieldset>
                                    <legend>
                                        <span class="icon icon-truck" style="margin-right: 8px;"></span>
                                        <span>{l s="DATI SPEDIZIONE" mod='mpbrtinfo'}</span>
                                    </legend>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Spedizione ID' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.SPEDIZIONE_ID}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Data spedizione' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.SPEDIZIONE_DATA}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Porto' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.TIPO_PORTO} -
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.PORTO}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Servizio' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.TIPO_SERVIZIO} -
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.SERVIZIO}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Filiare di arrivo' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            ({$eventi.BOLLA.DATI_SPEDIZIONE.COD_FILIALE_ARRIVO})&nbsp;
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.FILIALE_ARRIVO}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Filiare di arrivo URL' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-8">
                                            <a target="_blank"
                                                href="{$eventi.BOLLA.DATI_SPEDIZIONE.FILIALE_ARRIVO_URL}">
                                                {$eventi.BOLLA.DATI_SPEDIZIONE.FILIALE_ARRIVO_URL}
                                            </a>
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Stato Spedizione (P1)' mod='mpbrtinfo'}
                                        </label>
                                        <label class="lbl col-md-8">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.STATO_SPED_PARTE1}
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.DESCRIZIONE_STATO_SPED_PARTE1}
                                        </label>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-4 pt-6 text-right">
                                            {l s='Stato Spedizione (P2)' mod='mpbrtinfo'}
                                        </label>
                                        <label class="lbl col-md-8">
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.STATO_SPED_PARTE2}
                                            {$eventi.BOLLA.DATI_SPEDIZIONE.DESCRIZIONE_STATO_SPED_PARTE2}
                                        </label>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>
                                        <span class="icon icon-list" style="margin-right: 8px;"></span>
                                        <span>{l s="RIFERIMENTI" mod='mpbrtinfo'}</span>
                                    </legend>
                                    <div class="form-group row">
                                        <label class="col-md-6 pt-6 text-right">
                                            {l s='Riferimento Mittente numerico' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.RIFERIMENTI.RIFERIMENTO_MITTENTE_NUMERICO}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-6 pt-6 text-right">
                                            {l s='Riferimento Mittente alfabetico' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.RIFERIMENTI.RIFERIMENTO_MITTENTE_ALFABETICO}
                                        </span>
                                    </div>
                                    <div class="form-group row">
                                        <label class="col-md-6 pt-6 text-right">
                                            {l s='Riferimento Partner Estero' mod='mpbrtinfo'}
                                        </label>
                                        <span class="lbl col-md-6">
                                            {$eventi.BOLLA.RIFERIMENTI.RIFERIMENTO_PARTNER_ESTERO}
                                        </span>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>
                                        <span class="icon icon-list" style="margin-right: 8px;"></span>
                                        <span>{l s="MERCE" mod='mpbrtinfo'}</span>
                                    </legend>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label class="pt-6 text-right col-md-3">
                                                {l s='Natura' mod='mpbrtinfo'}
                                            </label>
                                            <span class="lbl col-md-9">
                                                {$eventi.BOLLA.MERCE.NATURA_MERCE}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-3">
                                            <label class="pt-6 text-right col-md-4">
                                                {l s='Colli' mod='mpbrtinfo'}
                                            </label>
                                            <span class="lbl col-md-8">
                                                {$eventi.BOLLA.MERCE.COLLI}
                                            </span>
                                        </div>
                                        <div class="col-md-4">
                                            <label class="pt-6 text-right col-md-4 ">
                                                {l s='Peso' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-8">
                                                <input type="text" class="form-control text-right"
                                                    value="{$eventi.BOLLA.MERCE.PESO_KG}" readonly>
                                                <div class="input-group-addon">Kg</div>
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="pt-6 text-right col-md-4">
                                                {l s='Volume' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-8">
                                                <input type="text" class="form-control text-right"
                                                    value="{$eventi.BOLLA.MERCE.VOLUME_M3}" readonly>
                                                <div class="input-group-addon">
                                                    M<inf>3</inf>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend>
                                        <span class="icon icon-money" style="margin-right: 8px;"></span>
                                        <span>{l s="CONTRASSEGNO" mod='mpbrtinfo'}</span>
                                    </legend>
                                    <div class="form-group row">
                                        <div class="col-md-6">
                                            <label class="pt-6 text-right col-md-5">
                                                {l s='Importo' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-7">
                                                <input type="text" class="form-control text-right"
                                                    value="{Tools::displayPrice($eventi.BOLLA.CONTRASSEGNO.CONTRASSEGNO_IMPORTO)}"
                                                    readonly>
                                                <div class="input-group-addon">
                                                    {$eventi.BOLLA.CONTRASSEGNO.CONTRASSEGNO_DIVISA}
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="pt-6 text-right col-md-5">
                                                {l s='Assicurazione' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-7">
                                                <input type="text" class="form-control text-right"
                                                    value="{Tools::displayPrice($eventi.BOLLA.ASSICURAZIONE.ASSICURAZIONE_IMPORTO)}"
                                                    readonly>
                                                <div class="input-group-addon">
                                                    {$eventi.BOLLA.ASSICURAZIONE.ASSICURAZIONE_DIVISA}
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label class="pt-6 text-right col-md-4">
                                                {l s='Incasso' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-8">
                                                <input type="text" class="form-control text-right"
                                                    value="{$eventi.BOLLA.CONTRASSEGNO.CONTRASSEGNO_INCASSO}" readonly>
                                                <div class="input-group-addon">
                                                    <i class="icon icon-note"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group row">
                                        <div class="col-md-12">
                                            <label class="pt-6 text-right col-md-4">
                                                {l s='Particolarità' mod='mpbrtinfo'}
                                            </label>
                                            <div class="input-group col-md-8">
                                                <input type="text" class="form-control text-right"
                                                    value="{$eventi.BOLLA.CONTRASSEGNO.CONTRASSEGNO_PARTICOLARITA}"
                                                    readonly>
                                                <div class="input-group-addon">
                                                    <i class="icon icon-note"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="panel-heading">
                        <span class="icon icon-list" style="margin-right: 8px;"></span>
                        <span>{l s="EVENTI" mod='mpbrtinfo'}</span>
                    </div>
                    <div class="panel-body">
                        {foreach $eventi.LISTA_EVENTI as $evento}
                            {if $evento.EVENTO.ID}
                                <tr>
                                    <table class="table table-condensed mb-4">
                                        <tbody>
                                            <tr>
                                                <td rowspan="3" style="width: 96px; max-width: 96px; text-align: center;">
                                                    <img src="{$evento.EVENTO.ICON}" style="width: 78px; object-fit: contain;">
                                                </td>
                                                <td style="width: 96px; text-align: right; font-weight: bold; color: #306290;">
                                                    {l s='EVENTO' mod='mpbrtinfo'}
                                                </td>
                                                <td>
                                                    <strong>{$evento.EVENTO.ID}</strong>, {$evento.EVENTO.DESCRIZIONE}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 96px; text-align: right; font-weight: bold; color: #306290;">
                                                    {l s='DATA' mod='mpbrtinfo'}</td>
                                                <td>
                                                    {$evento.EVENTO.DATA} {$evento.EVENTO.ORA}
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width: 96px; text-align: right; font-weight: bold; color: #306290;">
                                                    {l s='FILIALE' mod='mpbrtinfo'}</td>
                                                <td><strong>{$evento.EVENTO.FILIALE}</strong></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </tr>
                            {/if}
                        {/foreach}
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button class="btn btn-info pull-left" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true"><i class="icon icon-times"></i></span>
                    <span>{l s='Close' mod='mpbrtinfo'}</span>
                </button>
            </div>
        </div>
    </div>
</div>