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
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . '/mpbrtinfo/vendor/autoload.php';
require_once _PS_MODULE_DIR_ . '/mpbrtinfo/models/autoload.php';

use MpSoft\MpBrtInfo\Dashboard\ChartPanel;
use MpSoft\MpBrtInfo\Dashboard\Dashboard;

class AdminMpBrtDeliveredController extends ModuleAdminController
{
    protected $id_shop;
    protected $id_lang;
    protected $id_employee;

    public function __construct()
    {
        $table = ModelBrtHistory::$definition['table'];
        $primary = ModelBrtHistory::$definition['primary'];

        $this->module = Module::getInstanceByName('mpbrtinfo');
        $this->bootstrap = true;
        $this->table = $table;
        $this->identifier = $primary;
        $this->className = 'ModelBrtHistory';
        $this->multilang = false;
        $this->translator = Context::getContext()->getTranslator();

        parent::__construct();

        $this->toolbar_title = $this->module->l('Storico spedizioni');

        $this->_defaultOrderBy = $primary;
        $this->_defaultOrderWay = 'DESC';

        $this->initTable();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
    }

    public function setMedia($isNewTheme = false)
    {
        parent::setMedia($isNewTheme);
        $path = $this->module->getLocalPath() . 'views/js/';
        $this->addJS([
            $path . 'chart.js/node_modules/chart.js/dist/chart.js',
        ]);

        $this->addCSS([
            $path . 'chart.css',
        ]);
    }

    public function initContent()
    {
        $options = [
            'responsive' => true,
            'maintainAspectRatio' => false,
        ];
        $colors = [
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(255, 99, 132, 0.5)',
            'rgba(54, 162, 235, 0.5)',
            'rgba(255, 206, 86, 0.5)',
            'rgba(75, 192, 192, 0.5)',
            'rgba(153, 102, 255, 0.5)',
            'rgba(255, 159, 64, 0.5)',
            'rgba(200, 100, 200, 0.5)',
            'rgba(100, 200, 100, 0.5)',
            'rgba(50, 150, 200, 0.5)',
            'rgba(200, 150, 50, 0.5)',
        ];

        $borderColors = [
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
            'rgba(200, 100, 200, 1)',
            'rgba(100, 200, 100, 1)',
            'rgba(50, 150, 200, 1)',
            'rgba(200, 150, 50, 1)',
            'rgba(255, 99, 132, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 206, 86, 1)',
            'rgba(75, 192, 192, 1)',
            'rgba(153, 102, 255, 1)',
            'rgba(255, 159, 64, 1)',
        ];

        $months = [
            'Non specificato',
            'Gennaio',
            'Febbraio',
            'Marzo',
            'Aprile',
            'Maggio',
            'Giugno',
            'Luglio',
            'Agosto',
            'Settembre',
            'Ottobre',
            'Novembre',
            'Dicembre',
        ];

        $dashboard = new MpSoft\MpBrtInfo\Dashboard\Dashboard($this->module);
        $panel = new MpSoft\MpBrtInfo\Dashboard\Panel($this->module);
        $chartPanel = new MpSoft\MpBrtInfo\Dashboard\ChartPanel($this->module);

        $totalDelivered = $this->getTotalShippedByType(['%CONSEGNAT%']);
        $totalFermoPoint = $this->getTotalShippedByType(['RITIRATA%FERMOPOINT']);
        $totalInTransit = $this->getTotalShippedByType(['PARTITA']);
        $totalRefused = $this->getTotalShippedByType(['%RIFIUTA%']);
        $totalWaiting = $this->getTotalShippedByType(['%ATTESA%']);
        $totalAlerts = $this->getTotalShippedByType(['%AVVISO%']);

        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_GREEN, Dashboard::ICON_DELIVERED, $totalDelivered, 'Consegnati', 'Spedizioni'));
        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_BLUE, Dashboard::ICON_FERMOPOINT, $totalFermoPoint, 'FermoPoint', 'Spedizioni'));
        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_ORANGE, Dashboard::ICON_TRANSIT, $totalInTransit, 'In transito', 'Spedizioni'));
        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_BROWN, Dashboard::ICON_REFUSED, $totalRefused, 'Rifiutati', 'Spedizioni'));
        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_YELLOW, Dashboard::ICON_WAITING, $totalWaiting, 'In attesa', 'Spedizioni'));
        $dashboard->addPanel($panel->renderPanel(Dashboard::COLOR_RED, Dashboard::ICON_ALERT, $totalAlerts, 'Avvisi', 'Spedizioni'));

        $this->content = $dashboard->renderDashboard();

        $dashboard->clearPanels();

        $shippedDays = $this->getShippedDays('max');
        $shippedDaysData = [];
        if ($shippedDays) {
            foreach ($shippedDays as $shippedDay) {
                $shippedDaysData['labels'][] = $shippedDay['event_name'];
                $shippedDaysData['datasets'][0]['label'] = $shippedDay['event_name'];
                $shippedDaysData['datasets'][0]['data'][] = $shippedDay['days'];
                $shippedDaysData['datasets'][0]['backgroundColor'][] = $colors[rand(0, count($colors) - 1)];
                $shippedDaysData['datasets'][0]['borderColor'][] = $borderColors[rand(0, count($borderColors) - 1)];
                $shippedDaysData['datasets'][0]['borderWidth'][] = 1;
            }
        }
        $dashboard->addPanel($chartPanel->renderChartPanel('Massimo giorni di consegna', $shippedDaysData, $options, ChartPanel::CHART_TYPE_PIE, 'col-md-4'));

        $shippedDays = $this->getShippedDays('min');
        $shippedDaysData = [];
        if ($shippedDays) {
            foreach ($shippedDays as $shippedDay) {
                $shippedDaysData['labels'][] = $shippedDay['event_name'];
                $shippedDaysData['datasets'][0]['label'] = $shippedDay['event_name'];
                $shippedDaysData['datasets'][0]['data'][] = $shippedDay['days'];
                $shippedDaysData['datasets'][0]['backgroundColor'][] = $colors[rand(0, count($colors) - 1)];
                $shippedDaysData['datasets'][0]['borderColor'][] = $borderColors[rand(0, count($borderColors) - 1)];
                $shippedDaysData['datasets'][0]['borderWidth'][] = 1;
            }
        }
        $dashboard->addPanel($chartPanel->renderChartPanel('Minimo giorni di consegna', $shippedDaysData, $options, ChartPanel::CHART_TYPE_PIE, 'col-md-4'));

        $shippedMonths = $this->getShippedOrdersByMonth();
        $shippedMonthsData = [];
        if ($shippedMonths) {
            foreach ($shippedMonths as $shippedMonth) {
                $month = $months[$shippedMonth['month']];
                $shippedMonthsData['labels'][] = $month;
                $shippedMonthsData['datasets'][0]['label'] = "{$month}: {$shippedMonth['orders']}";
                $shippedMonthsData['datasets'][0]['data'][] = $shippedMonth['orders'];
                $shippedMonthsData['datasets'][0]['backgroundColor'][] = $colors[rand(0, count($colors) - 1)];
                $shippedMonthsData['datasets'][0]['borderColor'][] = $borderColors[rand(0, count($borderColors) - 1)];
                $shippedMonthsData['datasets'][0]['borderWidth'][] = 1;
            }
        }
        $dashboard->addPanel($chartPanel->renderChartPanel('Ordini consegnati per mese', $shippedMonthsData, $options, ChartPanel::CHART_TYPE_BAR, 'col-md-4'));

        $this->content .= $dashboard->renderDashboard();

        parent::initContent();
    }

    protected function initTable()
    {
        $primary = ModelBrtHistory::$definition['primary'];

        $this->fields_list = [
            $primary => [
                'title' => $this->module->l('ID'),
                'width' => 100,
            ],
            'id_order' => [
                'title' => $this->module->l('ID Order'),
                'width' => 100,
            ],
            'id_order_state' => [
                'title' => $this->module->l('Stato Ordine'),
                'type' => 'select',
                'list' => $this->getOrderStates(),
                'filter_key' => 'a!id_order_state',
            ],
            'date_shipped' => [
                'title' => $this->module->l('Data Spedizione'),
                'width' => 100,
                'type' => 'date',
            ],
            'date_delivered' => [
                'title' => $this->module->l('Data Consegna'),
                'width' => 100,
                'type' => 'date',
            ],
            'days' => [
                'title' => $this->module->l('Giorni'),
                'width' => 100,
                'align' => 'text-center',
                'callback' => 'callbackFormatDays',
            ],
            'event_id' => [
                'title' => $this->module->l('Evento'),
                'width' => 64,
                'align' => 'text-center',
                'callback' => 'callbackFormatEventId',
            ],
            'event_name' => [
                'title' => $this->module->l('Nome Evento'),
                'width' => 'auto',
            ],
            'event_date' => [
                'title' => $this->module->l('Data Evento'),
                'width' => 100,
                'type' => 'date',
            ],
            'event_filiale_id' => [
                'title' => $this->module->l('Filiale'),
                'width' => 100,
            ],
            'event_filiale_name' => [
                'title' => $this->module->l('Nome Filiale'),
                'width' => 'auto',
            ],
            'id_collo' => [
                'title' => $this->module->l('ID Collo'),
                'width' => 100,
            ],
            'rmn' => [
                'title' => $this->module->l('RMN'),
                'width' => 100,
            ],
            'rma' => [
                'title' => $this->module->l('RMA'),
                'width' => 100,
            ],
            'anno_spedizione' => [
                'title' => $this->module->l('Anno Spedizione'),
                'width' => 64,
            ],
        ];
    }

    protected function getOrderStates()
    {
        $states = OrderState::getOrderStates($this->context->language->id);
        $options = [];
        foreach ($states as $state) {
            $options[$state['id_order_state']] = $state['name'];
        }

        return $options;
    }

    protected function getTotalShippedByType(array $types)
    {
        $query = new DbQuery();
        $query->select('COUNT(b.id_mpbrtinfo_history) as total')
            ->from('mpbrtinfo_history', 'b')
            ->innerJoin('mpbrtinfo_evento', 'e', 'e.id_evento = b.event_id')
            ->groupBy('b.event_id')
            ->orderBy('b.id_mpbrtinfo_history DESC');

        foreach ($types as $type) {
            $query->where('e.name LIKE \'%' . pSQL($type) . '\'');
        }

        $sql = $query->build();

        $result = Db::getInstance()->executeS($sql);
        if ($result) {
            $total = 0;
            foreach ($result as $row) {
                $total += (int) $row['total'];
            }

            return $total;
        }

        return 0;
    }

    public function getShippedDays($type = 'max')
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('b.event_name, ' . $type . '(b.days) as days')
            ->from('mpbrtinfo_history', 'b')
            ->innerJoin('mpbrtinfo_evento', 'e', 'e.id_evento = b.event_id')
            ->groupBy('b.event_id')
            ->where('e.is_delivered = 1')
            ->where('b.days > 0')
            ->orderBy('b.id_mpbrtinfo_history DESC');

        $result = $db->executeS($sql);

        return $result ? $result : [];
    }

    public function getShippedOrdersByMonth()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('month(b.date_shipped) as month, count(b.id_mpbrtinfo_history) as orders')
            ->from('mpbrtinfo_history', 'b')
            ->innerJoin('mpbrtinfo_evento', 'e', 'e.id_evento = b.event_id')
            ->groupBy('month(b.date_shipped)')
            ->where('YEAR(b.date_shipped) = ' . (int) date('Y'))
            ->where('b.days > 0')
            ->orderBy('b.id_mpbrtinfo_history DESC');

        $result = $db->executeS($sql);

        return $result ? $result : [];
    }

    public function callbackFormatDays($value)
    {
        if ($value) {
            return "<span class='label label-success'>{$value}</span>";
        }

        return '--';
    }

    public function callbackFormatEventId($value)
    {
        if ($value) {
            return "<span class='label label-info'>{$value}</span>";
        }

        return '--';
    }

    public function callbackFormatEventName($value)
    {
        return $value;
    }

    /**
     * Process the collation change for all tables in the database
     * 
     * @return void
     */
    public function processCollate()
    {
        $this->setCollationForAllTables();
    }

    /**
     * Set collation for all tables in the database using a stored procedure
     * 
     * @param string $charset The character set to use
     * @param string $collation The collation to use
     *
     * @return void
     */
    private function setCollationForAllTables($charset = 'utf8mb4', $collation = 'utf8mb4_unicode_ci')
    {
        $database = _DB_NAME_;

        // Check if the procedure exists, create it if not
        $checkProcedure = Db::getInstance()->executeS("SHOW PROCEDURE STATUS WHERE Db = '$database' AND Name = 'convert_database_charset_collate'");

        if (empty($checkProcedure)) {
            // Load and execute the procedure creation SQL
            $sqlFile = _PS_MODULE_DIR_ . 'mpbrtinfo/sql/convert_charset_collate.sql';
            if (file_exists($sqlFile)) {
                $sql = file_get_contents($sqlFile);
                // Split by delimiter to execute multi-statement SQL
                $statements = explode('DELIMITER ;', $sql);
                if (isset($statements[0])) {
                    $procedureSQL = str_replace('DELIMITER //', '', $statements[0]);
                    $queries = explode('//', $procedureSQL);
                    foreach ($queries as $query) {
                        $query = trim($query);
                        if (!empty($query)) {
                            Db::getInstance()->execute($query);
                        }
                    }
                }
            }
        }

        // Call the stored procedure
        $result = Db::getInstance()->execute("CALL convert_database_charset_collate('$database', '$charset', '$collation')");

        if ($result) {
            $this->confirmations[] = $this->module->l('Database charset and collation successfully updated to ') . $charset . '/' . $collation;
        } else {
            $this->errors[] = $this->module->l('Error updating database charset and collation');
        }
    }
}
