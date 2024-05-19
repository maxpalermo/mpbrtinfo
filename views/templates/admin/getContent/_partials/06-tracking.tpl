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
            <i class="icon icon-cogs"></i>&nbsp;{l s='Tracking' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label>{l s='Select Control Tracking state' mod='mpbrtinfo'}</label><br>
                <select name="{$controls.OS_TRACKING}[]" class="chosen fixed-width-xxl" multiple>
                    {foreach $order_states as $os}
                        <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if in_array($os.id_order_state, $values.OS_TRACKING)} selected {/if}>
                            {$os.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <sub>{l s='Select one or more order state to get Tracking.' mod='mpbrtinfo' }</sub>
            </div>
            <div class="form-group">
                <label>{l s='Select Delivered state' mod='mpbrtinfo'}</label><br>
                <select name="{$controls.OS_DELIVERED}[]" class="chosen fixed-width-xxl" multiple>
                    {foreach $order_states as $os}
                        <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if in_array($os.id_order_state, $values.OS_DELIVERED)} selected {/if}>
                            {$os.name|escape:'htmlall':'UTF-8'}</option>
                    {/foreach}
                </select>
                <sub>{l s='Select one or more order state to set as delivered.' mod='mpbrtinfo' }</sub>
            </div>
            <div class="form-group">
                <label>{l s='Select Tracking search' mod='mpbrtinfo'} {$values.SEARCH_TYPE}</label><br>
                <select name="{$controls.SEARCH_TYPE}" class="fixed-width-xxl">
                    <option value="RMN" {if $values.SEARCH_TYPE == 'RMN'} selected {/if}>
                        {l s='Search by Numeric Id (RMN)' mod='mpbrtinfo'}
                    </option>
                    <option value="RMA" {if $values.SEARCH_TYPE == 'RMA'} selected {/if}>
                        {l s='Search by Alphanumeric Id (RMA)' mod='mpbrtinfo'}
                    </option>
                    <option value="RMA" {if $values.SEARCH_TYPE == 'IDC'} selected {/if}>
                        {l s='Search by Shipment ID (IDC)' mod='mpbrtinfo'}
                    </option>
                </select>
            </div>
            <div class="form-group">
                <label>{l s='Where to search' mod='mpbrtinfo'} {$values.SEARCH_WHERE}</label><br>
                <select name="{$controls.SEARCH_WHERE}" class="fixed-width-xxl">
                    <option value="ID" {if $values.SEARCH_WHERE == 'ID'} selected {/if}>
                        {l s='Search Shipment id in Order ID' mod='mpbrtinfo'}
                    </option>
                    <option value="REFERENCE" {if $values.SEARCH_WHERE == 'REFERENCE'} selected {/if}>
                        {l s='Search Shipment id in Order REFERENCE' mod='mpbrtinfo'}
                    </option>
                </select>
            </div>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>