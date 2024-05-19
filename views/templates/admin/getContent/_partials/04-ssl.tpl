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
<div class="col-md-6">
    <div class="panel">
        <div class="panel-heading">
            <i class="icon icon-certificate"></i>&nbsp;{l s='SSL Webservices' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label>{l s='Use SSL API' mod='mpbrtinfo'}</label>
                <div class="input-group">
                    <span class="switch prestashop-switch fixed-width-lg">
                        <input type="radio" name="{$controls.USE_SSL}" id="{$controls.USE_SSL}_on" value="1" {if $values.USE_SSL} checked {/if}>
                        <label for="{$controls.USE_SSL}_on" class="radioCheck"><i class="icon icon-check text-success"></i></label>
                        <input type="radio" name="{$controls.USE_SSL}" id="{$controls.USE_SSL}_off" value="0" {if !$values.USE_SSL} checked {/if}>
                        <label for="{$controls.USE_SSL}_off" class="radioCheck"><i class="icon icon-times text-danger"></i></label>
                        <a class="slide-button btn"></a>
                    </span>
                </div>
            </div>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>