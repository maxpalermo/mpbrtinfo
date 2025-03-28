<?php

use MpSoft\MpBrtInfo\Fetch\FetchConfigHandler;

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
if (!defined('_PS_VERSION_')) {
    exit;
}

class MpBrtInfoConfigModuleFrontController extends ModuleFrontController
{
    public function __construct()
    {
        $this->name = 'Config';
        $this->ajax = true;
        $this->auth = false;
        $this->guestAllowed = true;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');

        parent::__construct();
    }

    public function display()
    {
        $handler = new FetchConfigHandler();
        $handler->run();

        return false;
    }
}
