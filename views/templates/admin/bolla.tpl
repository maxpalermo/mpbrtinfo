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
    <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="BrtBollaTitle">
                    <span>BOLLA N. <strong>{$bolla->getDatiSpedizione()->getSpedizioneId()}</strong> DEL <strong>{$bolla->getDatiSpedizione()->getSpedizioneData()}</strong></span>
                </h4>
                <div class="modal-title">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <ul>
                                    <li class="mr-4">PORTO: <strong>{$bolla->getDatiSpedizione()->getTipoPorto()} {$bolla->getDatiSpedizione()->getPorto()}</strong></li>
                                    <li class="mr-4">SERVIZIO: <strong>{$bolla->getDatiSpedizione()->getTipoServizio()} {$bolla->getDatiSpedizione()->getServizio()}</strong></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <strong class="title">MERCE</strong>
                                <ul>
                                    <li>COLLI: <strong>{$bolla->getMerce()->getColli()}</strong> MC:<strong>{$bolla->getMerce()->getPesoKg()}</strong> PESO KG: <strong>{$bolla->getMerce()->getVolumeM3()}</strong></li>
                                    <li>NATURA: <strong>{$bolla->getMerce()->getNaturaMerce()}</strong></>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                {assign var="lastEvento" value=$bolla->getLastEvento()}
                {assign var="isDelivered" value=$lastEvento->isDelivered()}
                <div class="alert alert-{$bolla->getColorEvento()} show" role="alert">
                    <p>ID: [<strong>{$lastEvento->getId()}</strong>] <strong>{$lastEvento->getDescrizione()}</strong></p>
                    <p>DATA: <strong>{$lastEvento->getData()} {$lastEvento->getOra()}</strong></p>
                    <p>FILIALE: <strong>{$lastEvento->getFiliale()}</strong></p>
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
                            <p>ID: [<strong>{$evento->getId()}</strong>] <strong>{$evento->getDescrizione()}</strong></p>
                            <p>DATA: <strong>{$evento->getData()} {$evento->getOra()}</strong></p>
                            <p>FILIALE: <strong>{$evento->getFiliale()}</strong></p>
                        </div>
                    {/foreach}
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-warning" data-dismiss="modal">Chiudi</button>
                {if $isDelivered}
                    <button type="button" class="btn btn-info" id="setBrtDelivered">Segna come consegnato</button>
                {/if}
            </div>
        </div>
    </div>
</div>