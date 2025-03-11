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

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/models/autoload.php';

use Doctrine\ORM\QueryBuilder;
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEsitiSQL;
use MpSoft\MpBrtInfo\Ajax\AjaxInsertEventiSQL;
use MpSoft\MpBrtInfo\Core\Grid\Column\Type\CarrierColumn;
use PrestaShop\PrestaShop\Core\Grid\Filter\Filter;
use PrestaShop\PrestaShop\Core\Search\Filters\CustomerFilters;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

if (!defined('_MPBRTINFO_DIR_')) {
    define('_MPBRTINFO_DIR_', dirname(__FILE__) . '/');
}

if (!defined('_MPBRTINFO_URL_')) {
    define('_MPBRTINFO_URL_', __PS_BASE_URI__ . 'modules/mpbrtinfo/views/');
}

class MpBrtInfo extends Module
{
    protected $id_lang;
    protected $adminClassName;
    protected $link;
    protected $db;
    protected $config_form = false;
    protected $brtDb;
    protected $InstallMenu;
    protected $displayCarrier;
    protected $tpl;
    protected $soapAlerts;

    public function __construct()
    {
        $this->name = 'mpbrtinfo';
        $this->tab = 'shipping_logistics';
        $this->version = '1.8.0.2785';
        $this->author = 'Massimiliano Palermo';
        $this->need_instance = 0;
        $this->bootstrap = true;
        $this->module_key = '';

        parent::__construct();

        $this->displayName = $this->l('MP BRT Tracking & Delivery results with SOAP');
        $this->description = $this->l('Manage Bartolini tracking & delivery results, with state change');
        $this->adminClassName = 'AdminMpBrtDelivered';
        $this->ps_versions_compliancy = ['min' => '1.8.0', 'max' => _PS_VERSION_];
        $this->db = Db::getInstance();
        $this->link = $this->context->link;
        $this->InstallMenu = new MpSoft\MpBrtInfo\Helpers\InstallHelper();
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->displayCarrier = new MpSoft\MpBrtInfo\Carriers\DisplayCarrier($this);
        $this->tpl = new MpSoft\MpBrtInfo\Helpers\SmartyTpl();
        $this->soapAlerts = MpSoft\MpBrtInfo\Soap\BrtSoapAlerts::getInstance();
    }

    public function install()
    {
        $hooks = [
            'actionAdminControllerSetMedia',
            'actionObjectOrderHistoryAddAfter',
            'displayDashboardToolbarTopMenu',
            'displayBackOfficeFooter',
            'dashboardZoneTwo',
            'dashboardData',
            'actionOrderGridDefinitionModifier',
            'actionOrderGridQueryBuilderModifier',
        ];

        $install =
            parent::install()
            && $this->InstallMenu->installHooks($this, $hooks)
            && $this->InstallMenu->installMenu(
                $this->l('MP BRT Delivery Statistics'),
                $this->name,
                'AdminParentShipping',
                $this->adminClassName
            )
            && $this->InstallMenu->createTable(ModelBrtEvento::$definition)
            && $this->InstallMenu->createTable(ModelBrtEsito::$definition)
            && $this->InstallMenu->createTable(ModelBrtHistory::$definition);

        try {
            $this->InstallMenu->createOrderState(
                'BRT - Spedito',
                '#006090',
                false,
                '',
                $this->name,
                false,
                true,
                false,
                true,
                true,
                false,
                false,
                'sent'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Transito',
                '#0080A0',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                false, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'transit'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Consegnato',
                '#40A040',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                true, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'delivered'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Rifiutato',
                '#A04040',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                false, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'refused'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Consegnato Fermopoint',
                '#40A040',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                true, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'fermopoint'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Errore di consegna',
                '#C06060',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                false, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'error'
            );
            $this->InstallMenu->createOrderState(
                'BRT - Giacenza',
                '#60C060',
                false,
                '',
                $this->name,
                false, // hidden
                true, // logable
                false, // delivered
                true, // shipped
                true, // paid
                false, // pdf invoice
                false, // pdf delivery
                'waiting'
            );
            ModelBrtConfig::setDefaultValues();
        } catch (Throwable $th) {
            PrestaShopLogger::addLog($th->getMessage(), 2, $th->getCode(), 'MpBrtInfo');
            $this->_errors[] = $th->getMessage();
        }

        try {
            $sql = new MpSoft\MpBrtInfo\Sql\SqlValues();
            $sql->InsertSqlEsiti();
            $sql->InsertSqlEventi();
        } catch (Throwable $th) {
            PrestaShopLogger::addLog($th->getMessage(), 2, $th->getCode(), 'MpBrtInfo');
            $this->_errors[] = $th->getMessage();
        }

        return $install;
    }

    public function uninstall()
    {
        $value = parent::uninstall()
            && $this->InstallMenu->uninstallMenu($this->adminClassName);

        return $value;
    }

    public function hookDashboardZoneTwo()
    {
        $fermopoint = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::FERMOPOINT);
        $delivered = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::DELIVERED);
        $transit = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::TRANSIT);
        $refused = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::REFUSED);
        $waiting = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::WAITING);
        $error = ModelBrtHistory::getOrdersByLastCurrentState(ModelBrtHistory::ERROR);

        $params = [
            'orders_fermopoint' => $fermopoint,
            'orders_delivered' => $delivered,
            'orders_transit' => $transit,
            'orders_refused' => $refused,
            'orders_waiting' => $waiting,
            'orders_error' => $error,
            'token' => Tools::getAdminTokenLite('AdminOrders'),
        ];

        $smarty = Context::getContext()->smarty;
        $smarty->assign($params);

        return $this->display(__FILE__, 'dashboard/order_info.tpl');
    }

    public function hookDashboardData()
    {
        // nothing
    }

    public function hookDisplayDashboardToolbarTopMenu($params)
    {
        if (Tools::strtolower($this->context->controller->controller_name) == 'adminorders' && !Tools::getValue('id_order')) {
            $path = $this->getLocalPath() . 'views/templates/admin/toolbar/buttons.tpl';
            $tpl = $this->context->smarty->createTemplate($path);
            $tpl->assign('ajax_controller', $this->context->link->getModuleLink($this->name, 'CronJobs'));
            $html = $tpl->fetch();

            return $html;
        }
    }

    public function hookActionOrderGridDefinitionModifier(array $params)
    {
        $choices = [];
        /** @var PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinition */
        $definition = $params['definition'];

        $order_column = $definition->getColumnById('id_order');

        $definition
            ->getColumns()
            ->addAfter(
                'id_order',
                (new CarrierColumn('id_carrier'))
                    ->setName($this->l('Corriere'))
                    ->setOptions([
                        'field' => 'id_order',
                        'callback_method' => 'displayCarrierIcon',
                        'callback_class' => $this,
                    ])
            );
        $carriers = Carrier::getCarriers((int) $this->id_lang);
        foreach ($carriers as $carrier) {
            $choices[$carrier['name']] = $carrier['name'];
        }
        // For search filter dropdown
        $definition->getFilters()->add(
            (new Filter('id_carrier', ChoiceType::class))
            ->setTypeOptions([
                'required' => false,
                'choices' => $choices, // This key added to show dropdown in search options
            ])
            ->setAssociatedColumn('id_carrier')
        );
    }

    public function displayCarrierIcon($id_order)
    {
        $icon = $this->displayCarrier->display($id_order);

        return $icon;
    }

    /**
     * Hook allows to modify Customers query builder and add custom sql statements.
     *
     * @param array $params
     */
    public function hookActionOrderGridQueryBuilderModifier(array &$params)
    {
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];
        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];
        
        $searchQueryBuilder
            ->addSelect('car.name as `carrier_name`')
            ->addSelect('o.id_carrier')
            ->leftJoin('o', _DB_PREFIX_ . 'carrier', 'car', 'o.id_carrier = car.id_carrier');
        foreach ($searchCriteria->getFilters() as $filterName => $filterValue) {
            if ($filterName == 'id_carrier') {
                $carrier_name = $filterValue;
                $db = Db::getInstance();
                $sql = 'select id_carrier from ' . _DB_PREFIX_ . 'carrier where name = "' . pSQL($carrier_name) . '"';
                $id_carriers = $db->executeS($sql);
                if ($id_carriers) {
                    $filterIdCarriers = array_column($id_carriers, 'id_carrier');
                    $inFilter = implode(',', $filterIdCarriers);
                } else {
                    $inFilter = [0];
                }
                $searchQueryBuilder->andWhere('o.id_carrier in (:id_carriers)');
                $searchQueryBuilder->setParameter('id_carriers', $inFilter);
            }
        }

        $params['search_query_builder'] = $searchQueryBuilder;
    }

    public function hookActionAdminControllerSetMedia($params)
    {
        $path = $this->getLocalPath() . 'views/';
        $this->context->controller->addJS([
            $path . 'js/XmlBeautify/XmlBeautify.min.js',
            $path . 'js/swal2/sweetalert2.all.min.js',
            $path . 'js/htmx/htmx.min.js',
            $path . 'js/panels/brt-esiti.js',
            $path . 'js/panels/swal-progress.js',
            $path . 'js/scripts/functions.js',
        ]);
        $this->context->controller->addCSS([
            $path . 'css/icon.css',
            $path . 'css/spacer.bs.css',
            $path . 'css/material-icons.css',
            $path . 'js/swal2/sweetalert2.min.css',
            $path . 'css/style.css',
        ]);
        // $this->context->controller->addCSS($this->getLocalPath() . 'views/css/bootstrap.min.css', 'all', 1000);
        // $this->context->controller->addJs($this->getLocalPath() . 'views/js/bootstrap.bundle.min.js');
    }

    public function hookActionObjectOrderHistoryAddAfter($params)
    {
        // TODO:
    }

    public function insertValueAtPosition($arr, $insertedArray, $position)
    {
        $i = 0;
        $new_array = [];
        foreach ($arr as $key => $value) {
            if ($i == $position) {
                foreach ($insertedArray as $iKey => $iValue) {
                    $new_array[$iKey] = $iValue;
                }
            }
            $new_array[$key] = $value;
            ++$i;
        }

        return $new_array;
    }

    public function hookDisplayBackOfficeFooter(&$params)
    {
        $controller = Tools::getValue('controller');
        if (!preg_match('/AdminOrders/i', $controller)) {
            return '';
        }
        if (Tools::getValue('id_order')) {
            return;
        }

        $ajax_controller = $this->context->link->getModuleLink($this->name, 'CronJobs');

        $data = [
            'id_order' => 0,
            'id_carrier' => 0,
            'ajax_controller' => $ajax_controller,
            'baseAdminUrl' => $ajax_controller,
            'fetchController' => $this->context->link->getModuleLink($this->name, 'FetchShipping'),
            'module_dir' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/',
            'spinner' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/img/spinner/spinner.gif',
        ];

        $tpl_modal = $this->context->smarty->createTemplate($this->getLocalPath() . 'views/templates/admin/brtInfo/modal_fetch.tpl');
        $tpl_modal->assign($data);
        $modal = $tpl_modal->fetch();

        $tpl_script = $this->context->smarty->createTemplate($this->getLocalPath() . 'views/templates/admin/brtInfo/script.tpl');
        $tpl_script->assign($data);
        $script = $tpl_script->fetch();

        return $modal . $script;
    }

    public function getFrontControllerLink($params = [])
    {
        return $this->context->link->getModuleLink($this->name, 'CronJobs', $params);
    }

    public function setTrackingLink($row)
    {
        $id_carrier = (int) $row['id_carrier'];
        $carrier = new Carrier($id_carrier);
        $url = $carrier->url;
        $tracking_number = $row['tracking_number'];
        $link = str_replace('@', $tracking_number, $url);
        $html = '<a href="' . $link . '" target="_blank">' . $tracking_number . '</a>';

        return $html;
    }

    public function setOrderLink($reference)
    {
        $sql = 'select id_order from ' . _DB_PREFIX_ . "orders where reference = '" . pSQL($reference) . "'";
        $id_order = (int) $this->db->getValue($sql);
        $url = $this->link->getAdminLink('AdminOrders') . '&id_order=' . $id_order . '&vieworder';
        $html = '<a href="' . $url . '" target="_blank">' . $reference . '</a>';

        return $html;
    }

    public function getContent()
    {
        if (!extension_loaded('soap')) {
            $this->context->controller->errors[] = $this->l('The SOAP extension for PHP is not installed. Please install it to use this module.');
        }

        return $this->renderForm();
    }

    protected function getIcons()
    {
        $icons = [
            'CONSEGNATO' => MpSoft\MpBrtInfo\Helpers\Icons::getIconDelivered(),
            'ERRORE' => MpSoft\MpBrtInfo\Helpers\Icons::getIconError(),
            'FERMOPOINT' => MpSoft\MpBrtInfo\Helpers\Icons::getIconFermopoint(),
            'RIFIUTATO' => MpSoft\MpBrtInfo\Helpers\Icons::getIconRefused(),
            'SPEDITO' => MpSoft\MpBrtInfo\Helpers\Icons::getIconShipped(),
            'IN TRANSITO' => MpSoft\MpBrtInfo\Helpers\Icons::getIconTransit(),
            'SCONOSCIUTO' => MpSoft\MpBrtInfo\Helpers\Icons::getIconUnknown(),
            'IN ATTESA' => MpSoft\MpBrtInfo\Helpers\Icons::getIconWaiting(),
        ];

        return $icons;
    }

    public function gridGetTrackingNumber($id_order)
    {
        return $this->brtDb->getOrderTracking($id_order);
    }

    public function gridGetTrans($type)
    {
        switch ($type) {
            case 'transit':
                return $this->l('Get shipment info.');
            case 'shipped':
                return $this->l('Get tracking number.');
            case 'delivered':
                return $this->l('Get delivered info.');
            default:
                return '';
        }
    }

    public function processCallbackDisplayCarrier($value, $row)
    {
        return $this->displayCarrier->display($row['id_order']);
    }

    public function renderForm()
    {
        $message = $this->postProcess();
        $order_states = OrderState::getOrderStates($this->id_lang);
        $carriers = Carrier::getCarriers($this->id_lang);
        $cronJobsClass = $this->context->link->getModuleLink($this->name, 'CronJobs');

        foreach ($carriers as &$carrier) {
            $carrier['id_carrier'] = $carrier['name'];
        }

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'type' => 'switch',
                        'label' => $this->l('Usa API SSL'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_USE_SSL,
                        'is_bool' => true,
                        'desc' => $this->l('Abilita l\'uso di SSL per le chiamate API'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Yes'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('No'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'text',
                        'label' => $this->l('Automazione'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_CRON_JOB,
                        'desc' => $this->l('Copia questo link e usalo per automatizzare le operazioni,'),
                        'required' => true,
                        'suffix' => '<i class="icon icon-edit"></i>',
                        'class' => 'copy-clipboard',
                        'col' => 6,
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Codice cliente'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER,
                        'desc' => $this->l('Inserisci il tuo codice cliente BRT'),
                        'required' => true,
                        'class' => 'fixed-width-md text-center',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'name' => ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS . '[]',
                        'label' => $this->l('Corrieri Bartolini'),
                        'desc' => $this->l('Seleziona i corrieri Bartolini'),
                        'required' => true,
                        'multiple' => true,
                        'options' => [
                            'query' => $carriers,
                            'id' => 'id_carrier',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 6,
                        'type' => 'html',
                        'label' => $this->l('Stati da saltare'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_OS_SKIP . '[]',
                        'desc' => $this->l('Spunta dall\'elenco tutti gli stati da non considerare durante la ricerca'),
                        'required' => true,
                        'html_content' => $this->renderSkipStates(),
                    ],
                    [
                        'type' => 'radio',
                        'label' => $this->l('Tipo di ricerca'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE,
                        'desc' => $this->l('Seleziona il tipo di ricerca'),
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'RMN',
                                'value' => 'RMN',
                                'label' => $this->l('Riferimento Mittente Numerico'),
                            ],
                            [
                                'id' => 'RMA',
                                'value' => 'RMA',
                                'label' => $this->l('Riferimento Mittente Alfabetico'),
                            ],
                        ],
                    ],
                    [
                        'type' => 'radio',
                        'label' => $this->l('Seleziona dove cercare'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE,
                        'desc' => $this->l('Indica quale parte dell\'ordine cercare'),
                        'required' => true,
                        'values' => [
                            [
                                'id' => 'ID',
                                'value' => 'ID',
                                'label' => $this->l('ID Ordine'),
                            ],
                            [
                                'id' => 'REFERENCE',
                                'value' => 'REFERENCE',
                                'label' => $this->l('Riferimento Ordine'),
                            ],
                        ],
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento spedito'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_SENT,
                        'desc' => $this->l('Seleziona l\'evento spedito'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento in transito'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT,
                        'desc' => $this->l('Seleziona l\'evento in transito'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento consegnato'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED,
                        'desc' => $this->l('Seleziona l\'evento consegnato'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento Fermopoint'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT,
                        'desc' => $this->l('Seleziona l\'evento Fermopoint'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento giacenza'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING,
                        'desc' => $this->l('Seleziona l\'evento in giacenza'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento rifiutato'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED,
                        'desc' => $this->l('Seleziona l\'evento rifiutato'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'select',
                        'label' => $this->l('Evento errore di spedizione'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR,
                        'desc' => $this->l('Seleziona l\'evento errore di spedizione'),
                        'required' => true,
                        'options' => [
                            'query' => $order_states,
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];

        $helper = new HelperForm();
        $helper->module = $this;
        $helper->name_controller = $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name;
        $helper->title = $this->displayName;
        $helper->show_toolbar = true;
        $helper->toolbar_scroll = true;
        $helper->submit_action = 'submitForm';
        $helper->toolbar_btn = [
            'save' => [
                'desc' => $this->l('Save'),
                'href' => $this->context->link->getAdminLink('AdminModules') . '&configure=' . $this->name . '&submitForm',
            ],
        ];
        $helper->tpl_vars = [
            'fields_value' => self::getConfigValues(),
        ];

        $form = $helper->generateForm([$fields_form]);

        $smarty = $this->context->smarty;

        $tpl_modal = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/modal-brt-soap.tpl';
        $smarty->assign('ajax_controller', $cronJobsClass);
        $modal = $smarty->fetch($tpl_modal);

        $tpl_icons = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/00-icons.tpl';
        $smarty->assign('icons', $this->getIcons());
        $tpl = $smarty->fetch($tpl_icons);

        $tpl_eventi = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/table-eventi.tpl';
        $smarty->assign('eventi', (new AjaxInsertEventiSQL)->getList());
        $tables = $smarty->fetch($tpl_eventi);

        $tpl_esiti = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/table-esiti.tpl';
        $smarty->assign('esiti', (new AjaxInsertEsitiSQL())->getList());
        $tables .= $smarty->fetch($tpl_esiti);

        $tpl_query = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/modal_query.tpl';
        $modal_query = $smarty->fetch($tpl_query);

        return $message . $modal . $tpl . $form . $tables . $modal_query;
    }

    public function renderSkipStates()
    {
        $skip_states = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []);
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/skip_states.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign('skip_states', $skip_states);
        $tpl->assign('order_states', OrderState::getOrderStates($this->context->language->id));
        $tpl->assign('input_skip_name', ModelBrtConfig::MP_BRT_INFO_OS_SKIP);
        return $tpl->fetch();
    }
    public function postProcess()
    {
        if (Tools::isSubmit('submitForm')) {
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_USE_SSL, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_USE_SSL, false));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER, ''));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS, json_encode(Tools::getValue(ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS, [])));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_SHIPPED_STATES, json_encode(Tools::getValue(ModelBrtConfig::MP_BRT_SHIPPED_STATES, [])));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE, 'RMN'));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE, 'ID'));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR, 0));
            Configuration::updateValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []));
            return $this->l('Configurazione aggiornata.');
        }

        return '';
    }

    public static function getConfigValues()
    {
        $cronTaskInfoShipping = Context::getContext()->link->getModuleLink('mpbrtinfo', 'Cron', ['action' => 'getShippingsInfo']);

        return [
            ModelBrtConfig::MP_BRT_INFO_USE_SSL => (int) Configuration::get(ModelBrtConfig::MP_BRT_INFO_USE_SSL),
            ModelBrtConfig::MP_BRT_INFO_CRON_JOB => $cronTaskInfoShipping,
            ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER => Configuration::get(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER),
            ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS . '[]' => json_decode(Configuration::get(ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS), true),
            ModelBrtConfig::MP_BRT_SHIPPED_STATES . '[]' => json_decode(Configuration::get(ModelBrtConfig::MP_BRT_SHIPPED_STATES), true),
            ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE => Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE),
            ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE => Configuration::get(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE),
            ModelBrtConfig::MP_BRT_INFO_EVENT_SENT => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT),
            ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT),
            ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED),
            ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT),
            ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING),
            ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED),
            ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR => Configuration::get(ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR),
        ];
    }
}
