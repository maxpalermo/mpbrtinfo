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
class ModelBrtEsito extends ObjectModel
{
    public $id_esito;
    public $testo1;
    public $testo2;
    public $date_add;
    public $date_upd;
    protected static $model_name = 'ModelBrtEsito';

    public static $definition = [
        'table' => 'mpbrtinfo_esito',
        'primary' => 'id_mpbrtinfo_esito',
        'multilang' => false,
        'fields' => [
            'id_esito' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => true,
            ],
            'testo1' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => true,
            ],
            'testo2' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 255,
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => true,
            ],
        ],
    ];

    public static function getEsiti()
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->orderBy('testo1');
        $rows = $db->executeS($sql);

        return $rows;
    }

    public function insertEsiti()
    {
        $module = Module::getInstanceByName('mpbrtinfo');
        $file = Tools::file_get_contents($module->getLocalPath() . 'sql/esiti.sql');
        $sql = str_replace('PFX_', _DB_PREFIX_, $file);

        try {
            Db::getInstance()->execute($sql);
        } catch (Throwable $th) {
            Context::getContext()->controller->errors[] = $th->getMessage();
        }
    }

    public static function getByIdEsito($id_esito)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select(self::$definition['primary'])
            ->from(self::$definition['table'])
            ->where('id_esito = \'' . pSQL($id_esito) . '\'');
        $id_row = (int) $db->getValue($sql);
        if ($id_row) {
            return new ModelBrtEsito($id_row);
        }

        return false;
    }
}
