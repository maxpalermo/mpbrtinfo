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
            <i class="icon icon-list"></i>&nbsp;{l s='Events States' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <div class="form-group">
                    <label>{l s='Select transit state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_TRANSIT}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_TRANSIT == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order when tracking number is acquired. This state will be checked to control if the order has been delivered' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select delivered state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_DELIVERED}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_DELIVERED == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order as delivered.' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select error state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_ERROR}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_ERROR == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this to set your order in an error state.' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select fermopoint state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_FERMOPOINT}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_FERMOPOINT == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order as parked in fermopoint.' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select refused state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_REFUSED}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_REFUSED == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order as refused by customer.' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select waiting state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_WAITING}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_WAITING == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order as waiting for delivering.' mod='mpbrtinfo' }</sub>
                </div>
                <div class="form-group">
                    <label>{l s='Select sent state' mod='mpbrtinfo'}</label>
                    <br>
                    <select name="{$controls.EVENT_SENT}" class="chosen chosen-single fixed-width-xxl">
                        {foreach $order_states as $os}
                            <option value="{$os.id_order_state|escape:'htmlall':'UTF-8'}" {if $values.EVENT_SENT == $os.id_order_state} selected {/if}>
                                {$os.name|escape:'htmlall':'UTF-8'}</option>
                        {/foreach}
                    </select>
                    <sub>{l s='Select this state to set your order as sent.' mod='mpbrtinfo' }</sub>
                </div>
            </div>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>