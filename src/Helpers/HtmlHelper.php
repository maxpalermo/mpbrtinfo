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
class HtmlHelper
{
    public static function addParagraph($string)
    {
        return "<p>$string</p>";
    }

    public static function addStrong($string)
    {
        return "<strong>$string</strong>";
    }

    public static function addSpan($string)
    {
        return "<span>$string</span>";
    }

    public static function addH1($string)
    {
        return "<h1>$string</h1>";
    }

    public static function addH2($string)
    {
        return "<h2>$string</h2>";
    }

    public static function addH3($string)
    {
        return "<h3>$string</h3>";
    }
}
