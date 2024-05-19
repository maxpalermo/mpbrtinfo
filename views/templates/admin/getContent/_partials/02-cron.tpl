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
            <i class="icon icon-dashboard"></i>&nbsp;{l s='Cron tasks' mod='mpbrtinfo'}
        </div>
        <div class="panel-body">
            <div class="form-group">
                <label><i class="icon icon-truck"></i>&nbsp;{l s='Cron task for info Shipping' mod='mpbrtinfo'}</label>
                <br>
                <div class="input-group">
                    <input type="text" id="cron_task_tracking" value="{$cron_task_info_shipping}" readonly class="form-control readonly">
                    <div class="input-group-addon" title="{l s='Click to copy' mod='mpbrtinfo'}">
                        <span class="input-group-text copy-clipboard">
                            <i class="icon icon-edit"></i>
                        </span>
                    </div>
                </div>
                <sub>{l s='Use this link in your cron task to automate process.' mod='mpbrtinfo' }</sub>
            </div>
            <script type="text/javascript">
                function copyToClipboard(text) {
                    var dummy = document.createElement("textarea");
                    // to avoid breaking orgain page when copying more words
                    // cant copy when adding below this code
                    // dummy.style.display = 'none'
                    document.body.appendChild(dummy);
                    //Be careful if you use texarea. setAttribute('value', value), which works with "input" does not work with "textarea". – Eduard
                    dummy.value = text;
                    dummy.select();
                    document.execCommand("copy");
                    document.body.removeChild(dummy);
                }
                $(function() {
                    $('.copy-clipboard').on('click', function() {
                        let url = $(this).closest('div').prev().val();

                        /* Copy the text inside the text field */
                        copyToClipboard(url);

                        /* Alert the copied text */
                        let msg = "{l s='Copied the text' mod='mpbrtinfo'}" + ":\n" + url;
                        alert(msg);
                    });
                });
            </script>
        </div>
        {include file="./panel-footer.tpl"}
    </div>
</div>