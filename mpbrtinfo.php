<?php

use Doctrine\ORM\QueryBuilder;

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

use MpSoft\MpBrtInfo\Ajax\AjaxInsertEsitiSQL;
use MpSoft\MpBrtInfo\Bolla\Evento;
use MpSoft\MpBrtInfo\Core\Grid\Column\Type\CarrierColumn;
use MpSoft\MpBrtInfo\Fetch\FetchConfigHandler;
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
        $this->ps_versions_compliancy = ['min' => '8.0', 'max' => _PS_VERSION_];
        $this->db = Db::getInstance();
        $this->link = $this->context->link;
        $this->InstallMenu = new MpSoft\MpBrtInfo\Helpers\InstallHelper();
        $this->id_lang = (int) Context::getContext()->language->id;
        $this->displayCarrier = new MpSoft\MpBrtInfo\Carriers\DisplayCarrier($this);
        $this->tpl = new MpSoft\MpBrtInfo\Helpers\SmartyTpl();
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
            'actionBeforeSendTrackingEmail',
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
            $this->InstallMenu->createOrderState(
                'BRT - Lasciato Avviso',
                '#FFA500',
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
                'warning'
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

    protected function isAdminOrdersController()
    {
        $controller = Tools::getValue('controller');
        if (!preg_match('/AdminOrders/i', $controller)) {
            return false;
        }
        if (Tools::getValue('id_order')) {
            return false;
        }

        return true;
    }

    protected function isAdminModulesController()
    {
        $controller = Tools::getValue('controller');
        if (!preg_match('/AdminModules/i', $controller)) {
            return false;
        }

        return true;
    }

    public function hookDashboardZoneTwo()
    {
        return '';
    }

    public function hookDashboardData()
    {
        // nothing
    }

    public function hookDisplayDashboardToolbarTopMenu($params)
    {
        if (!$this->isAdminOrdersController()) {
            return;
        }

        $path = $this->getLocalPath() . 'views/templates/admin/toolbar/buttons.tpl';
        $tpl = $this->context->smarty->createTemplate($path);
        $html = $tpl->fetch();

        return $html;
    }

    public function hookActionBeforeSendTrackingEmail($params)
    {
        // Permette ad altri moduli di modificare i dati dell'email
        return $params;
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
        $this->context->controller->addCSS([
            $path . 'css/icon.css',
            $path . 'css/material-icons.css',
        ]);

        if ($this->isAdminOrdersController() || $this->isAdminModulesController()) {
            $this->context->controller->addJS([
                $path . 'js/XmlBeautify/XmlBeautify.min.js',
                $path . 'js/swal2/sweetalert2.all.min.js',
                $path . 'js/htmx/htmx.min.js',
                $path . 'js/panels/brt-esiti.js',
                $path . 'js/scripts/AdminOrders.js',
                $path . 'js/tippy/popper-core2.js',
                $path . 'js/tippy/tippy.js',
            ]);
            $this->context->controller->addCSS([
                $path . 'js/swal2/sweetalert2.min.css',
                $path . 'css/spacer.bs.css',
                $path . 'css/style.css',
                $path . 'js/tippy/scale.css',
            ]);
        }
    }

    public function hookActionObjectOrderHistoryAddAfter($params)
    {
        // TODO:
    }

    public function hookDisplayBackOfficeFooter(&$params)
    {
        if (!$this->isAdminOrdersController()) {
            return;
        }

        $fetchController = $this->context->link->getModuleLink($this->name, 'FetchOrders');

        $data = [
            'id_order' => 0,
            'id_carrier' => 0,
            'fetchController' => $fetchController,
            'fetchShippingOrdersPath' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/js/WSDL/fetchShippingOrders.js',
            'GetTotalShippingsPath' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/js/modules/GetTotalShippings.js',
            'GetOrderTrackingPath' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/js/modules/GetOrderTracking.js',
            'GetOrderInfoPath' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/js/modules/GetOrderInfo.js',
            'BrtEsitiPath' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/js/panels/BrtEsiti.js',
            'module_dir' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/',
            'spinner' => $this->context->shop->getBaseUri() . 'modules/mpbrtinfo/views/img/spinner/spinner.gif',
        ];

        /*
        $tpl_modal = $this->context->smarty->createTemplate($this->getLocalPath() . 'views/templates/admin/brtInfo/modal_fetch.tpl');
        $tpl_modal->assign($data);
        $modal = $tpl_modal->fetch();
        */

        $tpl_script = $this->context->smarty->createTemplate($this->getLocalPath() . 'views/templates/admin/AdminOrderList/AdminScript.tpl');
        $tpl_script->assign($data);
        $script = $tpl_script->fetch();

        return $script;
    }

    public function displayCarrierIcon($id_order)
    {
        // $evento = Evento::getLastEventByOrderId($id_order);
        $evento = ModelBrtEvento::getEventFull($id_order);
        if (!$evento) {
            return false;
        }

        return $evento;
    }

    public function getContent()
    {
        if (!extension_loaded('soap')) {
            $this->context->controller->errors[] = $this->l('The SOAP extension for PHP is not installed. Please install it to use this module.');
        }

        return $this->renderForm();
    }

    public function renderForm()
    {
        $postProcess = $this->postProcess();

        $fields_form = [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                ],
                'input' => [
                    [
                        'col' => 6,
                        'type' => 'html',
                        'label' => $this->l('Test WSDL'),
                        'name' => 'test_wsdl',
                        'desc' => $this->l('Permette di testare il WSDL'),
                        'required' => false,
                        'html_content' => $this->renderTestWSDL(),
                    ],
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
                        'col' => 6,
                        'type' => 'html',
                        'label' => $this->l('Automazione'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_CRON_JOB,
                        'desc' => $this->l('Copia questo link e usalo per automatizzare le operazioni,'),
                        'required' => false,
                        'html_content' => $this->renderCronJob(),
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
                            'query' => Carrier::getCarriers($this->context->language->id),
                            'id' => 'name',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 3,
                        'type' => 'text',
                        'label' => $this->l('Inizia la ricerca da'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_START_FROM,
                        'desc' => $this->l('Le ricerca dello stato delle spedizioni inizierà da questo codice ordine'),
                        'required' => true,
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
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Seleziona gli stati SPEDITO'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_OS_SHIPPED . '[]',
                        'desc' => $this->l('Questi stati saranno considerati come SPEDITO'),
                        'required' => true,
                        'multiple' => true,
                        'options' => [
                            'query' => OrderState::getOrderStates($this->id_lang),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
                    ],
                    [
                        'col' => 6,
                        'type' => 'select',
                        'label' => $this->l('Seleziona gli stati CONSEGNATO'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_OS_DELIVERED . '[]',
                        'desc' => $this->l('Questi stati saranno considerati come CONSEGNATO'),
                        'required' => true,
                        'multiple' => true,
                        'options' => [
                            'query' => OrderState::getOrderStates($this->id_lang),
                            'id' => 'id_order_state',
                            'name' => 'name',
                        ],
                        'class' => 'chosen',
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
                        'type' => 'switch',
                        'label' => $this->l('Invia email di aggiornamento'),
                        'name' => ModelBrtConfig::MP_BRT_INFO_SEND_EMAIL,
                        'desc' => $this->l('Invia email al cliente quando lo stato della spedizione viene aggiornato'),
                        'values' => [
                            [
                                'id' => 'active_on',
                                'value' => 1,
                                'label' => $this->l('Abilitato'),
                            ],
                            [
                                'id' => 'active_off',
                                'value' => 0,
                                'label' => $this->l('Disabilitato'),
                            ],
                        ],
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
            'fields_value' => ModelBrtConfig::getConfigValues(),
        ];

        $htmlConfigForm = $helper->generateForm([$fields_form]);
        $htmlMessage = $this->renderHtmlMessage($postProcess);
        $htmlEventi = $this->renderEventsPanel();
        $htmlEsiti = $this->renderEsitiPanel();
        $htmlPreviewMail = $this->renderPreviewMail();
        $htmlTabWrapper = $this->renderTabWrapper($htmlConfigForm, $htmlEventi, $htmlEsiti, $htmlPreviewMail);

        return $htmlMessage . $htmlTabWrapper;
    }

    protected function renderCronJob()
    {
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/cron-job.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign('cronJobUrl', $this->context->link->getModuleLink($this->name, 'Cron'));

        return $tpl->fetch();
    }

    protected function renderHtmlMessage($postProcess)
    {
        if ($postProcess) {
            $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/html_message.tpl';
            $tpl = $this->context->smarty->createTemplate($file);

            return $tpl->fetch();
        }

        return '';
    }

    protected function getEmailEvents()
    {
        return [
            ['id' => 'tracking_assigned', 'name' => $this->l('Assegnazione numero tracking')],
            ['id' => 'in_transit', 'name' => $this->l('In transito')],
            ['id' => 'out_for_delivery', 'name' => $this->l('In consegna')],
            ['id' => 'delivered', 'name' => $this->l('Consegnato')],
            ['id' => 'exception', 'name' => $this->l('Eccezione di consegna')],
        ];
    }

    protected function renderTabWrapper($htmlConfigForm, $htmlEventi, $htmlEsiti, $htmlPreviewMail)
    {
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/tab-wrapper.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign([
            'htmlConfigForm' => $htmlConfigForm,
            'htmlEventi' => $htmlEventi,
            'htmlEsiti' => $htmlEsiti,
            'htmlPreviewMail' => $htmlPreviewMail,
        ]);

        return $tpl->fetch();
    }

    protected function renderSkipStates()
    {
        $skip_states = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []);
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/skip_states.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign('skip_states', $skip_states);
        $tpl->assign('order_states', OrderState::getOrderStates($this->context->language->id));
        $tpl->assign('input_skip_name', ModelBrtConfig::MP_BRT_INFO_OS_SKIP);

        return $tpl->fetch();
    }

    protected function renderEventsPanel()
    {
        $emails = FetchConfigHandler::getOptionsEmail();
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/table-eventi.tpl';
        $tplEventi = $this->context->smarty->createTemplate($file);
        $orderStates = OrderState::getOrderStates($this->context->language->id);
        $tplEventi->assign([
            'adminControllerURL' => $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name,
            'eventi' => ModelBrtEvento::getList(),
            'order_states' => $orderStates,
            'emails' => $emails,
        ]);
        $htmlEventi = $tplEventi->fetch();

        return $htmlEventi;
    }

    protected function renderEsitiPanel()
    {
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/table-esiti.tpl';
        $tplEsiti = $this->context->smarty->createTemplate($file);
        $tplEsiti->assign('adminControllerURL', $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name);
        $tplEsiti->assign('esiti', (new AjaxInsertEsitiSQL())->getList());
        $htmlEsiti = $tplEsiti->fetch();

        return $htmlEsiti;
    }

    protected function renderPreviewMail()
    {
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/preview-mail.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign('adminControllerURL', $this->context->link->getAdminLink('AdminModules', false) . '&configure=' . $this->name);
        $tpl->assign('emails', $this->getEmailTemplates());
        $htmlPreviewMail = $tpl->fetch();

        return $htmlPreviewMail;
    }

    protected function getEmailTemplates()
    {
        $path = $this->getLocalPath() . 'mails/it/';
        $files = glob($path . '*.html');
        $templates = [];
        foreach ($files as $file) {
            $templates[] = pathinfo($file, PATHINFO_FILENAME);
        }

        return $templates;
    }

    protected function renderTestWSDL()
    {
        $importPath = $this->getPathUri() . 'views/js/WSDL/fetchBrtWSDL.js';
        $adminControllerURL = $this->context->link->getModuleLink($this->name, 'Cron');
        $file = $this->getLocalPath() . 'views/templates/admin/getContent/_partials/test_wsdl.tpl';
        $tpl = $this->context->smarty->createTemplate($file);
        $tpl->assign('adminControllerURL', $adminControllerURL);
        $tpl->assign('importPath', $importPath);

        return $tpl->fetch();
    }

    public function postProcess()
    {
        if (Tools::isSubmit('submitForm')) {
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_USE_SSL, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_USE_SSL, false));
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER, ''));
            ModelBrtConfig::setBrtCarriers(Tools::getValue(ModelBrtConfig::MP_BRT_INFO_BRT_CARRIERS, []));
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_START_FROM, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_START_FROM, 0));
            ModelBrtConfig::setBrtOsSkip(Tools::getValue(ModelBrtConfig::MP_BRT_INFO_OS_SKIP, []));
            ModelBrtConfig::setBrtOsShipped(Tools::getValue(ModelBrtConfig::MP_BRT_INFO_OS_SHIPPED, []));
            ModelBrtConfig::setBrtOsDelivered(Tools::getValue(ModelBrtConfig::MP_BRT_INFO_OS_DELIVERED, []));
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE, 'RMN'));
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE, 'ID'));
            ModelBrtConfig::updateConfigValue(ModelBrtConfig::MP_BRT_INFO_SEND_EMAIL, Tools::getValue(ModelBrtConfig::MP_BRT_INFO_SEND_EMAIL, 0));

            return true;
        }

        return false;
    }

    public function getCarrierName($id_order)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $carrier = new Carrier($order->id_carrier, $id_lang);
        if (!Validate::isLoadedObject($carrier)) {
            return '';
        }

        return $carrier->name;
    }

    public function getCarrierImage(...$params)
    {
        foreach ($params as $key => $param) {
            $id_carrier = 0;
            $id_order = 0;
            if ($key == 'id_carrier') {
                $id_carrier = (int) $param;
            }
            if ($key == 'id_order') {
                $id_order = (int) $param;
            }
            if ($id_order && !$id_carrier) {
                $order = new Order($id_order);
                if (!Validate::isLoadedObject($order)) {
                    $id_carrier = 0;
                    $id_order = 0;
                } else {
                    $id_carrier = $order->id_carrier;
                }
            }
            if ($id_carrier) {
                return $this->context->link->getMediaLink('/img/s/' . $id_carrier . '.jpg');
            }
        }

        return $this->context->link->getMediaLink('/404.jpg');
    }

    public function getTrackingNumber($id_order)
    {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return '';
        }

        $sql = new DbQuery();
        $sql->select('tracking_number')
            ->from('order_carrier')
            ->where('id_order=' . (int) $order->id)
            ->where('id_carrier=' . (int) $order->id_carrier)
            ->orderBy('id_order_carrier DESC');

        return Db::getInstance()->getValue($sql);
    }

    public function getCarrierLink($id_carrier, $id_collo)
    {
        $carrier = new Carrier($id_carrier);
        if (!Validate::isLoadedObject($carrier)) {
            return 'javascript:void(0);';
        }

        return str_replace('@', $id_collo, $carrier->url);
    }
}
