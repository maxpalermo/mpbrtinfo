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
            <i class="icon icon-puzzle-piece"></i>&nbsp;{l s='Bartolini User' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label><i class="icon icon-user"></i>&nbsp;
                    {l s='User id' mod='mpbrtinfo'}</label><br>
                <input type="text" name="{$controls.ID_BRT_CUSTOMER}" value="{$values.ID_BRT_CUSTOMER}">
                <sub>
                    {l s='Insert your Bartolini customer ID.' mod='mpbrtinfo' }</sub>
            </div>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>