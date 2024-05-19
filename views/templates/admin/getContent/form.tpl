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

<style>
    .copy-clipboard:hover {
        cursor: pointer;
    }

    .readonly {
        background-color: #fefefe !important;
    }

    .modal-dialog-centered {
        display: flex;
        align-content: center;
        justify-content: center;
        align-items: center;
        min-height: calc(100% - 1rem);
        min-width: 600px;
        width: auto;
    }

    .modal-content {
        min-width: 600px;
        width: auto;
    }
</style>

{include file="./_partials/00-icons.tpl"}

<form method="post" id="getContentForm">
    <div class="row">
        {include file="./_partials/01-user_id.tpl"}
        {include file="./_partials/02-cron.tpl"}
        {include file="./_partials/03-carriers.tpl"}
        {include file="./_partials/04-ssl.tpl"}
    </div>
    <div class="row">
        {include file="./_partials/05-eventi.tpl"}
        {include file="./_partials/06-tracking.tpl"}
    </div>
    <div class="row">
        <div class="col-md-12">
            {include file="./_partials/table-eventi.tpl"}
        </div>
    </div>
    <div class="row">
        <div class="col-md-12">
            {include file="./_partials/table-esiti.tpl"}
        </div>
    </div>
</form>