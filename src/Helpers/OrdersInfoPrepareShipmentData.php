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

class OrdersInfoPrepareShipmentData
{
    /**
     * Returns tracking info
     *
     * @param \MpSoft\MpBrtInfo\Bolla\Bolla $bolla
     *
     * @return array|false tracking info
     */
    public static function run($bolla)
    {
        if ($bolla && $bolla->getEsito() == 0) {
            $spedizione = $bolla->getDatiSpedizione();
            $consegna = $bolla->getDatiConsegna();
            $merce = $bolla->getDatiMerce();
            $eventi = $bolla->getEventi();
            $riferimenti = $bolla->getRiferimenti();

            // Estrai i dati dalla risposta SOAP
            return [
                'rmn' => $riferimenti->getRiferimentoMittenteNumerico() ?? '--',
                'rma' => $riferimenti->getRiferimentoMittenteAlfabetico() ?? '--',
                'data_spedizione' => $spedizione->getSpedizioneData() ?? '--',
                'id_spedizione' => $spedizione->getSpedizioneId() ?? '--',
                'porto' => $spedizione->getTipoPorto() ?? '--',
                'servizio' => $spedizione->getTipoServizio() ?? '--',
                'colli' => $merce->getColli() ?? 0,
                'peso' => ($merce->getPesoKg() ?? '0') . ' Kg',
                'volume' => ($merce->getVolumeM3() ?? '0') . ' m3',
                'natura' => $merce->getNaturaMerce() ?? '--',
                'data_consegna' => $consegna->getDataConsegnaMerce() ?? '--',
                'eventi' => $eventi,
                'json' => $bolla->getBollaJson(),
            ];
        } else {
            return false;
        }
    }
}
