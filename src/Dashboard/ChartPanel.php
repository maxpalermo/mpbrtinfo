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

class ChartPanel
{
    protected $module;
    protected $context;

    const CHART_TYPE_BAR = 'bar';
    const CHART_TYPE_LINE = 'line';
    const CHART_TYPE_PIE = 'pie';
    const CHART_TYPE_DOUGHNUT = 'doughnut';

    public function __construct($module)
    {
        $this->module = $module;
        $this->context = \Context::getContext();
    }

    public function renderChartPanel($title, $data, $options, $chartType = self::CHART_TYPE_BAR, $column_size = 'col-md-4')
    {
        $tpl = $this->context->smarty->createTemplate($this->getChartPath());
        $tpl->assign([
            'title' => $title,
            'chartType' => $chartType,
            'data' => $data,
            'options' => $options,
            'column_size' => $column_size
        ]);
        return $tpl->fetch();
    }

    public function getChartPath()
    {
        return $this->module->getLocalPath() . 'views/templates/Dashboard/chart.tpl';
    }

    public function renderTestChart($type = self::CHART_TYPE_BAR, $column_size = 'col-md-4')
    {
        $title = "TEST GRAFICO";
        $data = [
            'labels' => ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
            'datasets' => [
                [
                    'label' => 'Spedizioni',
                    'data' => [10, 20, 30, 40, 50, 60, 70, 80, 90, 100, 110, 120],
                    'backgroundColor' => 'rgba(76, 82, 135, 0.5)',
                    'borderColor' => 'rgba(76, 82, 135, 1)',
                    'borderWidth' => 1
                ]
            ]
        ];
        $options = [
            'responsive' => true,
            'maintainAspectRatio' => false
        ];
        return $this->renderChartPanel($title, $data, $options, $type, $column_size);
    }

}