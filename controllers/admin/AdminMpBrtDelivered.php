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
use MpSoft\MpBrtInfo\Brt\BrtGetSoapTracking;
use MpSoft\MpBrtInfo\Brt\MpBrtDays;
use MpSoft\MpBrtInfo\Helpers\BrtInfoHelper;
use MpSoft\MpBrtInfo\Soap\BrtSoapAlerts;
use MpSoft\MpBrtInfo\Soap\BrtSoapShipmentInfo;
use MpSoft\MpBrtInfo\Sql\SqlValues;

class AdminMpBrtDeliveredController extends ModuleAdminController
{
    public $id_shop;
    public $id_lang;
    public $id_employee;
    protected $current_display;
    protected $eventi_list = [];
    protected $esiti_list = [];
    protected $adminClassName;
    /** @var BrtSoapAlerts */
    protected $soapAlerts;

    public function __construct()
    {
        $this->bootstrap = true;
        $this->className = 'ModelBrtDelivered';
        $this->controller_name = 'AdminMpBrtDelivered';
        parent::__construct();
        $this->id_lang = (int) $this->context->language->id;
        $this->id_shop = (int) $this->context->shop->id;
        $this->id_employee = (int) $this->context->employee->id;
        $this->soapAlerts = BrtSoapAlerts::getInstance();
    }

    public function init()
    {
        $this->initTable();
        $this->initFieldsList();
        parent::init();
    }

    public function initTable()
    {
        $this->_pagination = [20, 50, 100, 200, 500, 1000];
        $this->list_no_link = true;
        $this->table = ModelBrtDelivered::$definition['table'];
        $this->identifier = ModelBrtDelivered::$definition['primary'];
        $this->list_id = $this->table;
        $this->_defaultOrderBy = $this->identifier;
        $this->_defaultOrderWay = 'ASC';
        $this->_bulk_actions = [
            'print' => [
                'text' => $this->module->l('Print selected', $this->controller_name),
                'icon' => 'icon-print',
                'confirm' => $this->module->l('Print selected items?', $this->controller_name),
            ],
            'csvExport' => [
                'text' => $this->module->l('Export Excel', $this->controller_name),
                'icon' => 'icon-file',
                'confirm' => $this->module->l('Export selected items?', $this->controller_name),
            ],
        ];
        $this->_join = 'INNER JOIN ' . _DB_PREFIX_ . 'orders ord on (ord.id_order=a.id_order) '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'customer cust on (cust.id_customer=ord.id_customer) '
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'order_state_lang ostl on '
            . "(ostl.id_order_state=ord.current_state and ostl.id_lang={$this->id_lang}) "
            . 'LEFT JOIN ' . _DB_PREFIX_ . 'order_carrier ocar on (ocar.id_order=ord.id_order) ';
        $this->_select = 'ord.reference, ord.date_add, ord.id_carrier,'
            . "UPPER(CONCAT(UPPER(SUBSTRING(cust.firstname, 1, 1)), '. ', UPPER(cust.lastname))) as customer,"
            . ' ostl.name as order_state_name';
    }

    protected function initFieldsList()
    {
        $this->fields_list = [
            'id_order' => [
                'title' => $this->module->l('Id order', $this->controller_name),
                'align' => 'left',
                'width' => 'auto',
                'float' => true,
                'filter_key' => 'ord!id_order',
            ],
            'reference' => [
                'title' => $this->module->l('Reference', $this->controller_name),
                'align' => 'left',
                'width' => 'auto',
                'float' => true,
                'filter_key' => 'ord!reference',
            ],
            'customer' => [
                'title' => $this->module->l('Customer', $this->controller_name),
                'align' => 'left',
                'width' => 'auto',
                'filter_key' => 'cust!lastname',
            ],
            'order_state_name' => [
                'title' => $this->module->l('Current state', $this->controller_name),
                'align' => 'left',
                'width' => 'auto',
                'filter_key' => 'ostl!id_order_state',
                'type' => 'select',
                'list' => $this->getSelectOrderStates(),
            ],
            'tracking_number' => [
                'title' => $this->module->l('Tracking number', $this->controller_name),
                'align' => 'text-center',
                'class' => 'tracking_number',
                'width' => 'auto',
                'filter_key' => 'a!tracking_number',
                'float' => true,
            ],
            'date_add' => [
                'title' => $this->module->l('Order date', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'filter_key' => 'ord!date_add',
            ],
            'date_shipped' => [
                'title' => $this->module->l('Shipped', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'filter_key' => 'a!date_shipped',
            ],
            'date_delivered' => [
                'title' => $this->module->l('Delivered', $this->controller_name),
                'type' => 'datetime',
                'align' => 'text-center',
                'width' => 'auto',
                'filter_key' => 'a!date_delivered',
            ],
            'days' => [
                'title' => $this->module->l('Interval', $this->controller_name),
                'align' => 'text-right',
                'width' => 'auto',
                'type' => 'text',
                'float' => true,
                'filter_key' => 'a!days',
                'class' => 'fixed-width-sm',
            ],
        ];
    }

    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        $this->toolbar_title = $this->module->l('Elenco Spedizioni', $this->controller_name);
        $this->page_header_toolbar_btn['import'] = [
            'href' => $this->context->link->getAdminLink($this->controller_name) . '&action=import_delivered',
            'desc' => $this->module->l('Aggiorna gli ordini', $this->controller_name),
            'imgclass' => 'download',
        ];
    }

    public function getSelectOrderStates()
    {
        $states = OrderState::getOrderStates((int) Context::getContext()->language->id);
        $output = [];
        foreach ($states as $st) {
            $output[$st['id_order_state']] = $st['name'];
        }

        return $output;
    }

    public function getIdOrders()
    {
        $cookie = Context::getContext()->cookie;
        $id_order_history = (int) $cookie->id_order_history;
        $id_order_state = (int) $cookie->id_order_state;

        $db = Db::getInstance();
        $sql = new DbQuery();
        $query = new DbQuery();

        $sql->select('id_order')
            ->from('orders')
            ->where('current_state=' . (int) $id_order_state);
        $query->select('id_order_history')
            ->select('id_order')
            ->select('date_add')
            ->from('order_history')
            ->where('id_order_state=' . (int) $id_order_history)
            ->where('id_order in (' . $sql->build() . ')')
            ->orderBy('id_order ASC')
            ->orderBy('id_order_history DESC');
        $res = $db->executeS($query);
        $output = [];
        if ($res) {
            $current_id_order = 0;
            $parsed_id_order = 0;
            foreach ($res as $row) {
                if (count($output) == 0) {
                    $current_id_order = $row['id_order'];
                    $parsed_id_order = $row['id_order'];
                    $output[] = $row['id_order_history'];
                } else {
                    $parsed_id_order = $row['id_order'];
                    if ($current_id_order != $parsed_id_order) {
                        $output[] = $row['id_order_history'];
                        $current_id_order = $row['id_order'];
                    }
                }
            }

            return $output;
        }

        return [];
    }

    public function initContent()
    {
        parent::initContent();
    }

    public function getHistoryDate($id_order, $id_order_state)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('date_add')
            ->from('order_history')
            ->where('id_order_state=' . (int) $id_order_state)
            ->where('id_order=' . (int) $id_order)
            ->orderBy('date_add DESC');
        $date = $db->getValue($sql);
        if ($date) {
            return $date;
        }

        return false;
    }

    public function getHistoryYear($id_order, $id_order_state)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('YEAR(date_add)')
            ->from('order_history')
            ->where('id_order_state=' . (int) $id_order_state)
            ->where('id_order=' . (int) $id_order)
            ->orderBy('date_add DESC');
        $year = (int) $db->getValue($sql);
        if ($year) {
            return $year;
        }

        return false;
    }

    public function getIdOrderStates()
    {
        $orderStates = OrderState::getOrderStates($this->id_lang);
        $output = [];
        foreach ($orderStates as $os) {
            $output[] = [
                'id' => $os['id_order_state'],
                'name' => $os['name'],
            ];
        }

        return $output;
    }

    public function getIdOrderByTrackingNumber($tracking)
    {
        $sql = 'id_order '
        . 'from ' . _DB_PREFIX_ . 'order_carrier '
        . 'where tracking_number = \'' . pSQL($tracking) . '\'';
        $id_order = (int) Db::getInstance()->getValue($sql);

        return $id_order;
    }

    public function processBulkPrint()
    {
        $rows = [];
        foreach ($this->boxes as $box) {
            $class = new ModelBrtDelivered($box);
            $row = $class->getFields();
            $rows[] = $row;
            unset($row);
        }
        $template = $this->module->getLocalPath() . 'views/templates/admin/report.tpl';
        $this->context->smarty->assign('rows', $rows);
        $html = $this->context->smarty->fetch($template);
        $this->showReport($html);
    }

    public function processBulkExcelExport()
    {
        $rows = [];
        foreach ($this->boxes as $box) {
            $class = new ModelBrtDelivered($box);
            $row = $class->getFields();
            $rows[] = $row;
            unset($row);
        }
        $filename = rand(11111111, 99999999);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');

        if ($rows) {
            $head = $rows[0];
            $header = [];
            foreach ($head as $item) {
                $header[] = Tools::strtoupper(key($item));
            }
            echo implode(';', $header) . PHP_EOL;
        }

        foreach ($rows as $row) {
            echo implode(';', $row) . PHP_EOL;
        }
        exit;
    }

    protected function showReport($html)
    {
        $logo = _PS_ROOT_DIR_ . '/img/' . Configuration::get('PS_LOGO');
        $tcpdf_logo = $logo;
        if (!file_exists($tcpdf_logo)) {
            copy($logo, $tcpdf_logo);
            chmod($tcpdf_logo, 0775);
        }
        // create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        // set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Massimiliano Palermo');
        $pdf->SetTitle('Order delivered report');
        $pdf->SetSubject('Show a list of order delivered');
        $pdf->SetKeywords('TCPDF, PDF, prestashop, order, delivered');
        // set default header data
        $title = $this->module->l('Order delivered report', $this->controller_name);
        $date = Tools::displayDate(date('Y-m-d'));
        $pdf->SetHeaderData('shop_logo.jpg', 50, $title, $date);
        // set header and footer fonts
        $pdf->setHeaderFont([PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN]);
        $pdf->setFooterFont([PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA]);
        // set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        // set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        // set auto page breaks
        $pdf->SetAutoPageBreak(true, PDF_MARGIN_BOTTOM);
        // set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        // set some language-dependent strings (optional)
        $l = [];
        if (@file_exists(dirname(__FILE__) . '/lang/eng.php')) {
            require_once dirname(__FILE__) . '/lang/eng.php';
            $pdf->setLanguageArray($l);
        }
        // ---------------------------------------------------------
        // set font
        $pdf->SetFont('dejavusans', '', 10);
        // add a page
        $pdf->AddPage();
        // writeHTML($html, $ln=true, $fill=false, $reset=false, $cell=false, $align='')
        // writeHTMLCell($w,$h,$x,$y,$html='', $border=0, $ln=0, $fill=0, $reseth=true, $align='', $autopadding=true)
        // output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        // reset pointer to the last page
        $pdf->lastPage();
        // Close and output PDF document
        $filename = rand(11111111, 99999999);
        $pdf->Output($filename . '.pdf', 'I');
    }

    public function ajaxProcessGetTrackingNumber()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_carrier = (int) Tools::getValue('id_carrier');

        $this->response(BrtSoapShipmentInfo::getTrackingNumber($id_order, $id_carrier));
    }

    public function ajaxProcessGetBrtInfo()
    {
        $id_order = (int) Tools::getValue('id_order');
        $id_carrier = (int) Tools::getValue('id_carrier');
        $brtInfo = new BrtInfoHelper($id_order, $id_carrier);

        $this->response($brtInfo->getOrderInfo());
    }

    public function processGetSoapBrtInfo()
    {
        $soapTracking = new BrtGetSoapTracking($this->module);
        $trackings = $soapTracking->get();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminOrders'));
    }

    public function processGetSoapEsiti()
    {
        $sql = new SqlValues();
        $sql->getSoapEsiti();
        $sql->InsertSqlEsiti();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=mpbrtinfo');
    }

    public function processGetSoapEventi()
    {
        $sql = new SqlValues();
        $sql->getSoapEventi();
        $sql->insertSoapEventi();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=mpbrtinfo');
    }

    public function processInsertSqlEsiti()
    {
        $sql = new SqlValues();
        $sql->InsertSqlEsiti();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=mpbrtinfo');
    }

    public function processInsertSqlEventi()
    {
        $sql = new SqlValues();
        $sql->InsertSqlEventi();

        Tools::redirectAdmin($this->context->link->getAdminLink('AdminModules') . '&configure=mpbrtinfo');
    }

    public function processImportDelivered()
    {
        $file = 'MPBRTINFO_JSON_DELIVERED_' . $this->context->employee->id . '.dat';
        $path = $this->module->getLocalPath() . $file;
        $sent = [];
        $data = '';
        if (file_exists($path)) {
            $data = Tools::file_get_contents($path);
        }
        if ($data) {
            $sent = Tools::jsonDecode($data, true);
        }
        if (!$sent) {
            $os_sent = ModelBrtConfig::getConfigValue(ModelBrtConfig::CONFIG_OS_CHECK_FOR_TRACKING, true);
            $os_delivered = ModelBrtConfig::getConfigValue(ModelBrtConfig::CONFIG_OS_CHECK_FOR_DELIVERED, true);
            $db = Db::getInstance();
            $sql = new DbQuery();

            $sql->select('a.id_order, a.date_add, b.current_state, oc.tracking_number')
                ->from('order_history', 'a')
                ->innerJoin('orders', 'b', '(a.id_order=b.id_order)')
                ->leftJoin('order_carrier', 'oc', '(oc.id_order=a.id_order AND oc.id_carrier=b.id_carrier)')
                ->where('a.id_order_state in (' . implode(',', $os_sent) . ')')
                ->orderBy('a.date_add ASC');
            $rows = $db->executeS($sql);

            foreach ($rows as $row) {
                if (in_array($row['current_state'], $os_delivered)) {
                    $qry = 'SELECT date_add FROM ' . _DB_PREFIX_ . 'order_history ' .
                    "WHERE id_order={$row['id_order']} AND id_order_state={$row['current_state']} " .
                    'ORDER BY date_add DESC';
                    $date_add = $db->getValue($qry);
                    $row['date_delivered'] = $date_add;
                    $row['days'] = MpBrtDays::countDays($row['date_add'], $row['date_delivered']);
                } else {
                    $row['date_delivered'] = '';
                    $row['days'] = 0;
                }
                $sent[$row['id_order']] = $row;
            }
            unset($row);
            $data = Tools::jsonEncode($sent);

            file_put_contents($path, $data);
        }
        $counter = 0;
        foreach ($sent as $key => $row) {
            $model = new ModelBrtDelivered($row['id_order']);
            $model->tracking_number = $row['tracking_number'];
            $model->date_shipped = $row['date_add'];
            $model->date_delivered = $row['date_delivered'];
            $model->days = $row['days'];

            if (Validate::isLoadedObject($model)) {
                $model->update();
            } else {
                $model->force_id = true;
                $model->id = $row['id_order'];
                $model->add();
            }

            ++$counter;
            unset($sent[$key]);

            if ($counter == 1000) {
                $counter = 0;
                $data = Tools::jsonEncode($sent);

                file_put_contents($path, $data);
            }
        }
        unlink($path);
        $this->confirmations[] = $this->module->l('Operazione Eseguita.', $this->controller_name);
    }

    public function ajaxProcessUpdateEventi()
    {
        $rows = json_decode(Tools::getValue('rows', []), true);
        $errors = (new SqlValues())->updateEventi($rows);

        $this->response([
            'result' => true,
            'errors' => $errors,
        ]);
    }

    protected function response($params)
    {
        header('Content-Type: application/json; charset=utf-8');
        exit(json_encode($params));
    }
}
