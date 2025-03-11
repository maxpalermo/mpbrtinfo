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

namespace MpSoft\MpBrtInfo\Dashboard;

if (!defined('_PS_VERSION_')) {
    exit;
}

class Dashboard
{
    protected $module;
    protected $context;
    protected $panels;

    const COLOR_GREEN = '#4CAF50';
    const COLOR_BLUE = '#2196F3';
    const COLOR_RED = '#F44336';
    const COLOR_YELLOW = '#FFC107';
    const COLOR_ORANGE = '#FF9800';
    const COLOR_BROWN = '#795548';

    const ICON_DELIVERED = 'check';
    const ICON_FERMOPOINT = 'where_to_vote';
    const ICON_TRANSIT = 'local_shipping';
    const ICON_REFUSED = 'block';
    const ICON_WAITING = 'pending';
    const ICON_ERROR = 'error';

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
    }
    public function renderDashboard()
    {
        $tpl = $this->context->smarty->createTemplate($this->getDashboardPath());
        $tpl->assign([
            'panels' => $this->panels
        ]);

        return $tpl->fetch();
    }

    public function getDashboardPath()
    {
        return $this->module->getLocalPath() . 'views/templates/Dashboard/dashboard.tpl';
    }

    public function addPanel($panel)
    {
        $this->panels[] = $panel;
    }

    public function clearPanels()
    {
        $this->panels = [];
    }
}