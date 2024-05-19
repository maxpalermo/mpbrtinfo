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

namespace MpSoft\MpBrtInfo\Helpers;

if (!defined('_PS_VERSION_')) {
    exit;
}

class QueryHelper
{
    public function getControllerName($controller)
    {
        $class = get_class($controller);
        $name = $class;
        if ($name) {
            $name = explode('\\', $class);
            if ($name) {
                $name = $name[count($name) - 1];
            } else {
                $name = $class;
            }
        }
        if (preg_match('/(.*)Controller$/', $name)) {
            $name = preg_replace('/(.*)Controller$/', '$1', $name);
        }

        return $name;
    }

    public static function check($filename, $value)
    {
        $file = pathinfo($filename);
        $extension = $file['extension'];

        return $extension == $value;
    }
    public static $filename;

    public static function getLastOs($id_order_state_history)
    {
        $db = \Db::getInstance();
        $sub = new \DbQuery();
        $sub->select('id_order')
            ->from('order_state_history')
            ->where('id_order_state_history=' . (int) $id_order_state_history);

        $sql = new \DbQuery();
        $sql->select('id_order_state')
            ->from('order_state_history')
            ->where('id_order in (' . $sub->build() . ')')
            ->where('id_order_state_history != ' . (int) $id_order_state_history)
            ->orderBy('id_order_state_history DESC');

        return (int) $db->getValue($sql);
    }

    public static function existsImport($id_product, $id_product_attribute, $filename)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select(\ModelMpStockAvailableHistory::$definition['primary'])
            ->from(\ModelMpStockAvailableHistory::$definition['table'])
            ->where('id_product=' . (int) $id_product)
            ->where('id_product_attribute=' . (int) $id_product_attribute)
            ->where('filename=\'' . pSQL($filename) . '\'');

        return (int) $db->getValue($sql);
    }

    public static function getDefaultEmployee()
    {
        if (isset(\Context::getContext()->employee)) {
            $employee = \Context::getContext()->employee;
        } else {
            $employee = \Employee::getEmployees()[0];
        }

        return $employee;
    }

    public static function setFilename($filename = null)
    {
        // static $current_filename = null;
        if (!self::$filename || $filename) {
            self::$filename = $filename;
        }

        return self::$filename;
    }

    public static function setQuantityBefore($quantity = null)
    {
        static $current_quantity = null;
        if (!$current_quantity || $quantity) {
            $current_quantity = $quantity;
        }

        return $current_quantity;
    }

    public static function setStockMovementReason($id_mvt_reason = null)
    {
        static $current_id = null;
        if (!$current_id || $id_mvt_reason) {
            $current_id = $id_mvt_reason;
        }

        return $current_id;
    }

    public static function setDetail($detail = null)
    {
        static $current_detail = null;
        if (!$current_detail || $detail) {
            $current_detail = $detail;
        }

        return $current_detail;
    }

    public static function getTaxRate($id_tax_rules_group)
    {
        static $current_id = null;
        static $tax_rate = null;

        if (!$current_id || $current_id != $id_tax_rules_group) {
            $current_id = $id_tax_rules_group;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('b.rate')
                ->from('tax_rule', 'a')
                ->innerJoin('tax', 'b', 'a.id_tax=b.id_tax')
                ->where('id_tax_rules_group=' . (int) $current_id);
            $sql = $sql->build();
            $tax_rate = (float) $db->getValue($sql);
        }

        return $tax_rate;
    }

    public static function addTax($price, $tax_rate)
    {
        return number_format($price * ((100 + $tax_rate) / 100), 2);
    }

    public static function withoutTax($price, $tax_rate)
    {
        return number_format($price / ((100 + $tax_rate) / 100), 6);
    }

    public static function getProductAttribute($id_product, $id_product_attribute)
    {
        static $current_id_product_attribute = null;
        static $pa = null;
        if (!$current_id_product_attribute || $current_id_product_attribute != $id_product_attribute) {
            $current_id_product_attribute = $id_product_attribute;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('*')
                ->from('product_attribute')
                ->where('id_product = ' . (int) $id_product)
                ->where('id_product_attribute = ' . (int) $id_product_attribute);
            $pa = $db->getRow($sql);
        }

        return $pa;
    }

    public static function getReference($id_product, $id_product_attribute)
    {
        static $current_id_product_attribute = null;
        static $reference = null;
        if (!$current_id_product_attribute || $current_id_product_attribute != $id_product_attribute) {
            $current_id_product_attribute = $id_product_attribute;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('reference')
                ->from('product_attribute')
                ->where('id_product = ' . (int) $id_product)
                ->where('id_product_attribute = ' . (int) $id_product_attribute);
            $reference = $db->getValue($sql);
        }

        return $reference;
    }

    public static function getEan13($id_product, $id_product_attribute)
    {
        static $current_id_product_attribute = null;
        static $ean13 = null;
        if (!$current_id_product_attribute || $current_id_product_attribute != $id_product_attribute) {
            $current_id_product_attribute = $id_product_attribute;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('ean13')
                ->from('product_attribute')
                ->where('id_product = ' . (int) $id_product)
                ->where('id_product_attribute = ' . (int) $id_product_attribute);
            $ean13 = $db->getValue($sql);
        }

        return $ean13;
    }

    public static function getUpc($id_product, $id_product_attribute)
    {
        static $current_id_product_attribute = null;
        static $upc = null;
        if (!$current_id_product_attribute || $current_id_product_attribute != $id_product_attribute) {
            $current_id_product_attribute = $id_product_attribute;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('upc')
                ->from('product_attribute')
                ->where('id_product = ' . (int) $id_product)
                ->where('id_product_attribute = ' . (int) $id_product_attribute);
            $upc = $db->getValue($sql);
        }

        return $upc;
    }

    public static function getStockMovements($asSimpleArray = false)
    {
        static $rows = null;
        static $out = null;

        if (!$rows) {
            $id_lang = (int) \Context::getContext()->language->id;
            $db = \Db::getInstance();
            $sql = new \DbQuery();
            $sql->select('a.*, b.name')
                ->from('stock_mvt_reason', 'a')
                ->innerJoin('stock_mvt_reason_lang', 'b', 'a.id_stock_mvt_reason=b.id_stock_mvt_reason')
                ->where('b.id_lang=' . (int) $id_lang)
                ->where('a.deleted = 0')
                ->orderBy('b.name');
            $rows = $db->executeS($sql);
            array_unshift(
                $rows,
                [
                    'id_stock_mvt_reason' => -1,
                    'sign' => 0,
                    'name' => '--',
                    'date_add' => '',
                    'date_upd' => '',
                    'deleted' => 0,
                ]
            );
        }

        if ($asSimpleArray) {
            if (!$out) {
                $out = [];
                foreach ($rows as $row) {
                    $out[(int) $row['id_stock_mvt_reason']] = \Tools::strtoupper($row['name']);
                }
            }

            return $out;
        }

        return $rows;
    }

    public static function getDefaultMvtReason($quantity, $type = '')
    {
        if ($type) {
            switch ($type) {
                case 'return':
                    return (int) \Configuration::get('PS_STOCK_MVT_REASON_RETURN');
                case 'increment':
                    return (int) \Configuration::get('PS_STOCK_MVT_INC_REASON_DEFAULT');
                case 'decrement':
                    return (int) \Configuration::get('PS_STOCK_MVT_DEC_REASON_DEFAULT');
            }
        }
        $id_mvt_reason_inc = (int) \Configuration::get('PS_STOCK_MVT_INC_REASON_DEFAULT');
        $id_mvt_reason_dec = (int) \Configuration::get('PS_STOCK_MVT_DEC_REASON_DEFAULT');
        if ($quantity < 0) {
            return $id_mvt_reason_dec;
        }

        return $id_mvt_reason_inc;
    }

    public static function getProductName($id_product)
    {
        /** @var int */
        static $current_id_product;
        /** @var \Product */
        static $product;
        $id_lang = (int) \Context::getContext()->language->id;

        if (!$current_id_product || $current_id_product != $id_product) {
            $current_id_product = $id_product;
            $product = new \Product($id_product, false, $id_lang);
        }

        $product_name = $product->name;

        return $product_name;
    }

    public static function getProductAttributesName($id_product, $id_product_attribute)
    {
        /** @var int */
        static $current_id_product;
        /** @var \Product */
        static $product;
        $id_lang = (int) \Context::getContext()->language->id;

        if (!$current_id_product || $current_id_product != $id_product) {
            $current_id_product = $id_product;
            $product = new \Product($id_product, false, $id_lang);
        }

        $product_name = $product->name;
        $combination = [];
        $attributes = $product->getAttributeCombinationsById($id_product_attribute, $id_lang);
        foreach ($attributes as $attribute) {
            $combination[] = $attribute['attribute_name'];
        }

        return implode(',', $combination);
    }

    public static function getMvtReason($id_mvt_reason)
    {
        static $current_id_mvt_reason;
        static $mvt_reason;
        $id_lang = (int) \Context::getContext()->language->id;
        if (!$current_id_mvt_reason || $id_mvt_reason != $current_id_mvt_reason) {
            $current_id_mvt_reason = $id_mvt_reason;
            $mvt_reason = new \StockMvtReason($id_mvt_reason, $id_lang);
        }

        return $mvt_reason->name;
    }

    public static function getSign($id_mvt_reason)
    {
        $mvt = new \StockMvtReason($id_mvt_reason);
        if (\Validate::isLoadedObject($mvt)) {
            return $mvt->sign;
        }

        return false;
    }

    public static function getEmployees($asSimpleArray = false)
    {
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \Employee::getEmployees();
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_employee']] = \Tools::strtoupper($row['firstname'] . ' ' . $row['lastname']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getOrderStates($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \OrderState::getOrderStates($id_lang);
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_order_state']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getIdEmployee()
    {
        if (isset(\Context::getContext()->employee)) {
            return (int) \Context::getContext()->employee->id;
        }

        $table = _DB_PREFIX_ . 'employee';
        $default_id = (int) \Db::getInstance()->getValue(
            "SELECT id_employee FROM $table WHERE firstname='AAAA' AND lastname = 'DEFAULT'"
        );

        return $default_id;
    }

    public static function getEmployeeName($id_employee)
    {
        $list = self::getEmployees(true);
        if (isset($list[$id_employee])) {
            return $list[$id_employee];
        } else {
            $employee = new \Employee($id_employee);
            if (!\Validate::isLoadedObject($employee)) {
                return '--';
            }

            return \Tools::strtoupper($employee->firstname . ' ' . $employee->lastname);
        }
    }

    public static function getSuppliers($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \Supplier::getSuppliers(false, $id_lang);
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_supplier']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getTaxRates($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \TaxRulesGroup::getTaxRulesGroups();
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_tax_rules_group']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getLanguages($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \Language::getLanguages();
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_lang']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getCurrencies($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \Currency::getCurrencies();
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_currency']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getSupplyOrderStates($asSimpleArray = false)
    {
        $id_lang = (int) \Context::getContext()->language->id;
        static $rows = null;
        static $list = null;

        if (!$rows) {
            $rows = \SupplyOrderState::getSupplyOrderStates(null, $id_lang);
            $list = [];
            foreach ($rows as $row) {
                $list[(int) $row['id_supply_order_state']] = \Tools::strtoupper($row['name']);
            }
        }

        if ($asSimpleArray) {
            return $list;
        }

        return $rows;
    }

    public static function getSupplierName($id_supplier)
    {
        $list = self::getSuppliers(true);
        if (isset($list[$id_supplier])) {
            return $list[$id_supplier];
        }

        return '--';
    }

    public static function getProductCoverImage($id_product, $id_product_attribute = 0)
    {
        /** @var array */
        $cover = \Image::getCover($id_product);
        $image = new \Image((int) $cover['id_image']);
        if (\Validate::isLoadedObject($image)) {
            $image_path = $image->getImgPath();
            $image_format = \ImageType::getFormattedName('small') . $image->image_format;
            $img_src = \Context::getContext()->shop->getBaseURI() . 'img/p/' . $image_path . $image_format;

            return $img_src;
        }

        return false;
    }
}
