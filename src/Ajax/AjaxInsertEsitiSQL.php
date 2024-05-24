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

namespace MpSoft\MpBrtInfo\Ajax;

if (!defined('_PS_VERSION_')) {
    exit;
}

class AjaxInsertEsitiSQL extends AjaxTemplate
{
    public function get($id_esito)
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from('mpbrtinfo_esito', 'a')
            ->where('a.id_esito = ' . (int) $id_esito)
            ->orderBy('a.id_esito ASC');
        $results = $db->executeS($sql);

        if ($results) {
            $out = [];
            foreach ($results as $result) {
                $out[$result['id_esito']] = $result;
            }

            return $out;
        }

        return [];
    }

    public function getList()
    {
        $db = \Db::getInstance();
        $sql = new \DbQuery();
        $sql->select('*')
            ->from('mpbrtinfo_esito', 'a')
            ->orderBy('a.id_esito ASC');
        $results = $db->executeS($sql);

        if ($results) {
            return $results;
        }

        return [];
    }

    public function add($esito)
    {
        $evt = new \ModelBrtEsito();
        $evt->name = $esito['name'];
        $evt->testo1 = $esito['testo1'];
        $evt->testo2 = $esito['testo2'];
        $evt->date_add = date('Y-m-d H:i:s');
        $evt->date_upd = date('Y-m-d H:i:s');

        try {
            $res = $evt->add();
            if (!$res) {
                return ['error' => 'Errore durante l\'inserimento'];
            }

            return ['success' => 'Esito inserito'];
        } catch (\Throwable $th) {
            return ['error' => $th->getMessage()];
        }
    }

    public function update($id_evento, $evento)
    {
        $evt = new \ModelBrtEvento($id_evento);
        if (!\Validate::isLoadedObject($evt)) {
            return ['error' => 'Evento non trovato'];
        }

        $evt->name = $evento['name'];
        $evt->is_error = $evento['is_error'];
        $evt->is_transit = $evento['is_transit'];
        $evt->is_delivered = $evento['is_delivered'];
        $evt->is_fermopoint = $evento['is_fermopoint'];
        $evt->is_waiting = $evento['is_waiting'];
        $evt->is_refused = $evento['is_refused'];
        $evt->is_sent = $evento['is_sent'];
        $evt->date_upd = date('Y-m-d H:i:s');

        try {
            $res = $evt->update();
            if (!$res) {
                return ['error' => 'Errore durante l\'aggiornamento'];
            }

            return ['success' => 'Evento aggiornato'];
        } catch (\Throwable $th) {
            return ['error' => $th->getMessage()];
        }
    }

    public function updateEventState($id_evento, $state, $value)
    {
        $evt = new \ModelBrtEvento($id_evento);
        if (!\Validate::isLoadedObject($evt)) {
            return ['error' => 'Evento non trovato'];
        }

        $evt->$state = $value;
        $evt->date_upd = date('Y-m-d H:i:s');

        try {
            $res = $evt->update();
            if (!$res) {
                return ['error' => 'Errore durante l\'aggiornamento'];
            }

            return ['success' => 'Evento aggiornato'];
        } catch (\Throwable $th) {
            return ['error' => $th->getMessage()];
        }
    }
}
