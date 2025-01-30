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

<div id="BrtBolla" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="BrtBollaTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-height: 50vh;">
        <div class="modal-content">
            <div class="modal-body">
                <div class="card">
                    <div class="card-header">
                        <h3>{l s='BOLLA N.' mod='mpbrtinfo'} <strong>{$bolla->getDatiSpedizione()->getSpedizioneId()}</strong> {l s='DEL' mod='mpbrtinfo'} <strong>{$bolla->getDatiSpedizione()->getSpedizioneData()}</strong></h3>
                    </div>
                    <div class="card-body">
                        <h5 class="card-title">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong class="title">{l s='SPEDIZIONE' mod='mpbrtinfo'}</strong>
                                        <ul>
                                            <li class="mr-4">{l s='PORTO' mod='mpbrtinfo'}: <strong>{$bolla->getDatiSpedizione()->getTipoPorto()} {$bolla->getDatiSpedizione()->getPorto()}</strong></li>
                                            <li class="mr-4">{l s='SERVIZIO' mod='mpbrtinfo'}: <strong>{$bolla->getDatiSpedizione()->getTipoServizio()} {$bolla->getDatiSpedizione()->getServizio()}</strong></li>
                                        </ul>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <strong class="title">{l s='MERCE' mod='mpbrtinfo'}</strong>
                                        <ul>
                                            <li>{l s='COLLI' mod='mpbrtinfo'}: <strong>{$bolla->getMerce()->getColli()}</strong> {l s='MC' mod='mpbrtinfo'}:<strong>{$bolla->getMerce()->getPesoKg()}</strong> {l s='PESO KG' mod='mpbrtinfo'}: <strong>{$bolla->getMerce()->getVolumeM3()}</strong></li>
                                            <li>{l s='NATURA' mod='mpbrtinfo'}: <strong>{$bolla->getMerce()->getNaturaMerce()}</strong></>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="alert alert-info mb-4" role="alert">
                                Ordine: <strong>{$bolla->getRiferimenti()->getRiferimentoMittenteNumerico()}</strong>
                                <br>
                                Cliente: <strong>{$bolla->getCustomer()}</strong>
                            </div>
                        </h5>
                        <div class="card-text">
                            {assign var="lastEvento" value=$bolla->getLastEvento()}
                            {assign var="isDelivered" value=$lastEvento->isDelivered()}
                            <div class="alert alert-{$bolla->getColorEvento()} show" role="alert">
                                <p>{l s='EVENTO' mod='mpbrtinfo'}: [<strong>{$lastEvento->getId()}</strong>] <strong>{$lastEvento->getDescrizione()}</strong></p>
                                <p>{l s='DATA' mod='mpbrtinfo'}: <strong>{$lastEvento->getData()} {$lastEvento->getOra()}</strong></p>
                                <p>{l s='FILIALE' mod='mpbrtinfo'}: <strong>{$lastEvento->getFiliale()}</strong></p>
                            </div>

                            <div class="bg-info">

                            </div>

                            <div class="event_list" style="max-height: 20rem; overflow-y: auto; overflow-x: hidden;">
                                <h3>Elenco Eventi</h3>
                                {assign var="eventi" value=$bolla->getEventi()}
                                {assign var="last_event" value=array_shift($eventi)}
                                {foreach $eventi as $key=>$evento}
                                    {assign var="color" value=$evento->getColor()}
                                    <div class="alert alert-{$color} show mb-2" role="alert">
                                        <p>{l s='EVENTO' mod='mpbrtinfo'}: [<strong>{$evento->getId()}</strong>] <strong>{$evento->getDescrizione()}</strong></p>
                                        <p>{l s='DATA' mod='mpbrtinfo'}: <strong>{$evento->getData()} {$evento->getOra()}</strong></p>
                                        <p>{l s='FILIALE' mod='mpbrtinfo'}: <strong>{$evento->getFiliale()}</strong></p>
                                    </div>
                                {/foreach}
                            </div>
                        </div>
                    </div>
                    <div class="card-footer text-center">
                        <button type="button" class="btn btn-primary" data-dismiss="modal">
                            <i class="material-icons">close</i>
                            <span>{l s='Chiudi' mod='mpbrtinfo'}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>