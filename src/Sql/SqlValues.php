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

namespace MpSoft\MpBrtInfo\Sql;
if (!defined('_PS_VERSION_')) {
    exit;
}

require_once _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';

use MpSoft\MpBrtInfo\Soap\BrtSoapEsiti;
use MpSoft\MpBrtInfo\Soap\BrtSoapEventi;

class SqlValues
{
    protected $eventi_list;
    protected $esiti_list;
    /** @var \Context */
    protected $context;
    /** @var \ModuleAdminController */
    protected $controller;
    /** @var \Module */
    protected $module;
    protected $name;

    public function __construct()
    {
        $this->eventi_list = [];
        $this->esiti_list = [];
        $this->context = \Context::getContext();
        $this->controller = $this->context->controller;
        $this->module = \Module::getInstanceByName('mpbrtinfo');
        $this->name = 'SqlValues';
    }

    public function getSoapEventi($last_id = '')
    {
        $language = \Tools::getValue('language', '');

        $soap = new BrtSoapEventi();

        /** @var array */
        $eventi = $soap->getEventi($language, $last_id);

        if ($eventi && isset($eventi['ESITO']) && $eventi['ESITO'] == 100) {
            foreach ($eventi['LEGENDA'] as $evento) {
                if ($evento['ID']) {
                    $this->eventi_list[] = $evento;
                }
            }

            return $this->eventi_list;
        } elseif ($eventi && isset($eventi['ESITO']) && $eventi['ESITO'] == 0) {
            foreach ($eventi['LEGENDA'] as $evento) {
                $this->eventi_list[] = $evento;
            }
            $last_id = $evento['ID'];

            $this->getSoapEventi($last_id);
        } else {
            return false;
        }
    }

    public function getSoapEsiti($last_id = '')
    {
        $language = \Tools::getValue('language', '');

        $soap = new BrtSoapEsiti();

        /** @var array */
        $esiti = $soap->getEsiti($language, $last_id);

        if ($esiti && isset($esiti['ESITO']) && $esiti['ESITO'] == 100) {
            foreach ($esiti['LEGENDA'] as $esito) {
                if ($esito['ID']) {
                    $this->esiti_list[] = $esito;
                }
            }

            return $this->eventi_list;
        } elseif ($esiti && isset($esiti['ESITO']) && $esiti['ESITO'] == 0) {
            foreach ($esiti['LEGENDA'] as $esito) {
                $this->esiti_list[] = $esito;
            }
            $last_id = $esito['ID'];

            $this->getSoapEsiti($last_id);
        } else {
            return false;
        }
    }

    public function insertSoapEventi()
    {
        $this->getSoapEventi();
        $eventi = $this->eventi_list;
        if ($eventi) {
            foreach ($eventi as $event) {
                $model = new \ModelBrtEvento();
                $model->hydrate([
                    'id_evento' => $event['ID'],
                    'name' => $event['DESCRIZIONE'],
                ]);

                try {
                    $model->add();
                } catch (\Throwable $th) {
                    $this->controller->errors[] = $th->getMessage();
                }
            }
        }
        $this->controller->confirmations[] = $this->module->l('Operation Done', $this->name);
    }

    public function insertEsiti()
    {
        $this->getSoapEsiti();
        $esiti = $this->esiti_list;
        if ($esiti) {
            foreach ($esiti as $esito) {
                $model = \ModelBrtEsito::getByIdEsito($esito['ID']);
                if (!$model) {
                    $model = new \ModelBrtEsito();
                }
                $model->id_esito = $esito['ID'];
                $model->testo1 = $esito['TESTO1'];
                $model->testo2 = $esito['TESTO2'];

                try {
                    $model->add();
                } catch (\Throwable $th) {
                    $this->controller->errors[] = $th->getMessage();
                }
            }
        }
        $this->controller->confirmations[] = $this->module->l('Operation Done', $this->name);
    }

    public function InsertSqlEventi()
    {
        return $this->insertSql('eventi');
    }

    public function InsertSqlEsiti()
    {
        return $this->insertSql('esiti');
    }

    public function insertSql($filename)
    {
        $filepath = $this->module->getLocalPath() . 'sql/' . $filename . '.sql';
        if (!file_exists($filepath)) {
            return false;
        }
        $file = \Tools::file_get_contents($filepath);
        $sql = str_replace('PFX_', _DB_PREFIX_, $file);

        try {
            \Db::getInstance()->execute($sql);
        } catch (\Throwable $th) {
            \PrestaShopLogger::addLog($th->getMessage(), 2, $th->getCode(), 'SqlValues');
            $this->controller->errors[] = $th->getMessage();
        }

        $this->controller->confirmations[] = $this->module->l('Values inserted in table.', $this->name);
    }

    public function updateEventi($rows)
    {
        $errors = [];
        $table = ModelBrtEvento::$definition['table'];
        $primary = ModelBrtEvento::$definition['primary'];

        foreach ($rows as $row) {
            $id = (int) $row['id'];
            unset($row['id']);
            $row['id_evento'] = pSQL($row['id_evento']);
            $row['name'] = pSQL($row['name']);

            try {
                \Db::getInstance()->update(
                    $table,
                    $row,
                    $primary . '=' . $id
                );
            } catch (\Throwable $th) {
                $errors[] = $th->getMessage();
            }
        }

        return $errors;
    }
}
