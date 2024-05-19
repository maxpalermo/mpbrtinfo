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
            <i class="icon icon-truck"></i>&nbsp;{l s='Brt Carriers' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label>{l s='Select Bartolini carriers' mod='mpbrtinfo'}</label>
                <br>
                <select name="{$controls.BRT_CARRIERS}[]" class="chosen" multiple>
                    {foreach $carriers as $c}
                        <option value="{$c.id_carrier}" {if in_array($c.id_carrier, $values.BRT_CARRIERS)} selected {/if}>
                            {$c.name}</option>
                    {/foreach}
                </select>
                <sub>{l s='Select all Bartolini carriers to use with this module.' mod='mpbrtinfo' }</sub>
            </div>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>