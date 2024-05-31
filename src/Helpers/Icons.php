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

class Icons
{
    public const ICON_DELIVERED = 'MP_BRT_INFO_EVENT_DELIVERED';
    public const ICON_ERROR = 'MP_BRT_INFO_EVENT_ERROR';
    public const ICON_FERMOPOINT = 'MP_BRT_INFO_EVENT_FERMOPOINT';
    public const ICON_REFUSED = 'MP_BRT_INFO_EVENT_REFUSED';
    public const ICON_SHIPPED = 'MP_BRT_INFO_EVENT_SHIPPED';
    public const ICON_TRANSIT = 'MP_BRT_INFO_EVENT_TRANSIT';
    public const ICON_UNKNOWN = 'unknown';
    public const ICON_WAITING = 'MP_BRT_INFO_EVENT_WAITING';

    public static function getIcon64($icon)
    {
        return 'data:image/svg+xml;base64,' . base64_encode(file_get_contents(__DIR__ . '/../Resources/icons/' . $icon . '.svg'));
    }

    public static function getIcon($icon)
    {
        $icon = strtoupper($icon);
        if (file_exists(_MPBRTINFO_DIR_ . 'views/img/icons/' . $icon . '.png')) {
            return _MPBRTINFO_URL_ . 'img/icons/' . $icon . '.png';
        }

        return _MPBRTINFO_URL_ . 'img/icons/icon-unknown.png';
    }

    public static function getIconDelivered()
    {
        return self::getIcon(self::ICON_DELIVERED);
    }

    public static function getIconError()
    {
        return self::getIcon(self::ICON_ERROR);
    }

    public static function getIconFermopoint()
    {
        return self::getIcon(self::ICON_FERMOPOINT);
    }

    public static function getIconRefused()
    {
        return self::getIcon(self::ICON_REFUSED);
    }

    public static function getIconShipped()
    {
        return self::getIcon(self::ICON_SHIPPED);
    }

    public static function getIconTransit()
    {
        return self::getIcon(self::ICON_TRANSIT);
    }

    public static function getIconUnknown()
    {
        return self::getIcon(self::ICON_UNKNOWN);
    }

    public static function getIconWaiting()
    {
        return self::getIcon(self::ICON_WAITING);
    }
}
