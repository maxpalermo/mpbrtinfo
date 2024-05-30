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

namespace MpSoft\MpBrtInfo\Bolla;

if (!defined('_PS_VERSION_')) {
    exit;
}

class BrtParseInfo
{
    public static function parseTrackingInfo($tracking_info, $esiti)
    {
        $tracking = $tracking_info;

        $bolla = isset($tracking['BOLLA']) ? $tracking['BOLLA'] : [];
        $contatore_eventi = isset($tracking['CONTATORE_EVENTI']) ? $tracking['CONTATORE_EVENTI'] : 0;
        $contatore_note = isset($tracking['CONTATORE_NOTE']) ? $tracking['CONTATORE_NOTE'] : 0;
        $esito = isset($tracking['ESITO']) ? $tracking['ESITO'] : -99;
        $esito_desc = '';
        if ($esito == -99) {
            return [
                'ESITO' => -99,
                'ESITO_DESC' => 'Errore di comunicazione con il server BRT',
            ];
        }
        if (isset($esiti[$esito])) {
            $esito_desc = $esiti[$esito];
        } else {
            $esito_desc = 'Errore sconosciuto';
        }

        $eventi = isset($tracking['LISTA_EVENTI']) ? $tracking['LISTA_EVENTI'] : [];
        if (!$eventi) {
            return [
                'ESITO' => -98,
                'ESITO_DESC' => 'Errore di comunicazione con il server BRT: Nessun evento inviato',
            ];
        }
        $eventi = array_splice($eventi, 0, $contatore_eventi);

        $note = isset($tracking['LISTA_NOTE']) ? $tracking['LISTA_NOTE'] : [];
        if (!$note) {
            return [
                'ESITO' => -97,
                'ESITO_DESC' => 'Errore di comunicazione con il server BRT: Nessuna nota inviata',
            ];
        }
        $note = array_splice($note, 0, $contatore_note);

        $timestamp = isset($tracking['RISPOSTA_TIMESTAMP']) ? $tracking['RISPOSTA_TIMESTAMP'] : 0;
        $versione = isset($tracking['VERSIONE']) ? $tracking['VERSIONE'] : '';

        if ($esito == 0) {
            $info = [
                'BOLLA' => $bolla,
                'CONTATORE EVENTI' => $contatore_eventi,
                'CONTATORE NOTE' => $contatore_note,
                'ESITO' => $esito,
                'ESITO_DESC' => $esito_desc,
                'LISTA_EVENTI' => $eventi,
                'NOTE' => $note,
                'TIMESTAMP' => $timestamp,
                'VERSIONE' => $versione,
            ];
        } else {
            $info = [
                'ESITO' => $esito,
                'ESITO_DESC' => $esito_desc,
            ];
        }

        $bolla = new Bolla($info);

        return $bolla;
    }
}
