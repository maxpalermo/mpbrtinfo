<?php
/**
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
 */

namespace MpSoft\MpBrtInfo\Bolla;
if (!defined('_PS_VERSION_')) {
    exit;
}

class TemplateBolla
{
    /** @var \Smarty */
    protected $smarty;
    /** @var Bolla */
    protected $bolla;

    public function __construct($bolla)
    {
        $this->smarty = \Context::getContext()->smarty;
        $this->bolla = $bolla;
    }

    protected function getTemplate()
    {
        if (_PS_VERSION_ < '1.7') {
            return _MPBRTINFO_DIR_ . 'views/templates/admin/bolla.tpl';
        } else {
            return 'module:mpbrtinfo/views/templates/admin/bolla.tpl';
        }
    }

    public function display()
    {
        $this->smarty->assign('bolla', $this->bolla);

        return $this->smarty->fetch($this->getTemplate());
    }
}
