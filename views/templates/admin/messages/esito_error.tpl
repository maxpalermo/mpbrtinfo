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

<div id="esito" class="bootstrap modal fade modal-brt" tabindex="-1" role="dialog" aria-labelledby="esito-title"
    aria-hidden="true" data-backdrop="false" data-keyboard="false">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="esito-title">
                    <span class="icon icon-exclamation" style="color: #903030;"></span>
                    <span>{l s='Operation Fault' mod='mpbrtinfo'}</span>
                </h3>
            </div>
            <div class="modal-body">
                <div class="alert alert-{if $id_esito<0}danger{else}warning{/if}" role="alert"
                    style="overflow: hidden;">
                    <h4>{l s='Error code' mod='mpbrtinfo'}: {$id_esito}</h4>
                    <p>{$testo1}</p>
                    <p>{$testo2}</p>
                </div>
            </div>
            <div class="modal-footer">
                <div class="col-md-8" style="border-right: 1px solid #c0c0c0; padding-right: 1rem;">
                    {if isset($get_tracking) && $get_tracking}
                        <div class="form-group">
                            <div class=" mb-2">
                                <label
                                    class="pull-left mr-2 label-fixed-md">{l s='Insert Shipment Id' mod='mpbrtinfo'}</label>
                                <input id="manual_shipment_id" class="form-control fixed-width-md text-right" type="text">
                                <span style="width: 12px;"></span>
                            </div>
                            <div class="mb-2">
                                <label class="pull-left mr-2 label-fixed-md">{l s='Shipment Year' mod='mpbrtinfo'}</label>
                                <input id="manual_shipment_year" class="form-control fixed-width-md text-right" type="text"
                                    value="{date('Y')}">
                            </div>
                            <div class="mb-2">
                                <button class="btn btn-default" type="button" onclick="getTrackingManual();">
                                    <i class="icon icon-search"></i>
                                    <span>{l s='Search' mod='mpbrtinfo'}</span>
                                </button>
                            </div>
                        </div>
                    {/if}
                </div>
                <div class="col-md-4">
                    <button class="btn btn-danger pull-right" data-dismiss="modal" aria-label="Close"
                        style="margin-top: 16px;">
                        <span aria-hidden="true"><i class="process-icon-close"></i></span>
                        <span>{l s='Close' mod='mpbrtinfo'}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>