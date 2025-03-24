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

use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByIdCollo;

class ConvertIdColloToTracking
{
    public static function convert($idCollo)
    {
        if ($idCollo && strlen($idCollo) == 12) {
            return $idCollo;
        }

        if ($idCollo && strlen($idCollo) == 15) {
            // cerco il tracking number dall'ID COLLO
            $id_brt = \ModelBrtConfig::getBrtCustomerId();
            $id_collo = $idCollo;
            $class = new GetIdSpedizioneByIdCollo();
            $class->id_collo = $id_collo;
            $tracking_number = $class->getIdSpedizione($id_brt, $id_collo);
            if ($tracking_number) {
                return $tracking_number['spedizione_id'] ?? '';
            }
        }

        return '';
    }
}
