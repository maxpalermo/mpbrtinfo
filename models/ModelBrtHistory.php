<?php

use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMN;

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

class ModelBrtHistory extends ObjectModel
{
    const SENT = 'SENT';
    const DELIVERED = 'DELIVERED';
    const TRANSIT = 'TRANSIT';
    const FERMOPOINT = 'FERMOPOINT';
    const REFUSED = 'REFUSED';
    const WAITING = 'WAITING';
    const ERROR = 'ERROR';

    protected static $errors = [];
    public $id_order;
    public $id_order_state;
    public $event_id;
    public $event_name;
    public $event_date;
    public $event_filiale_id;
    public $event_filiale_name;
    public $id_collo;
    public $rmn;
    public $rma;
    public $current_state;
    public $anno_spedizione;
    public $date_shipped;
    public $date_delivered;
    public $days;
    public $date_add;
    public $date_upd;

    public static $definition = [
        'table' => 'mpbrtinfo_history',
        'primary' => 'id_mpbrtinfo_history',
        'multilang' => false,
        'fields' => [
            'id_order' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'id_order_state' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => true,
            ],
            'event_id' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 16,
                'required' => false,
            ],
            'event_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 128,
                'required' => false,
            ],
            'event_date' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'event_filiale_id' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'event_filiale_name' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 128,
                'required' => false,
            ],
            'id_collo' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'size' => 128,
                'required' => false,
            ],
            'rmn' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'rma' => [
                'type' => self::TYPE_STRING,
                'validate' => 'isAnything',
                'required' => false,
                'size' => 128,
            ],
            'anno_spedizione' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'date_shipped' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_delivered' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'days' => [
                'type' => self::TYPE_INT,
                'validate' => 'isUnsignedInt',
                'required' => false,
            ],
            'date_add' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
            'date_upd' => [
                'type' => self::TYPE_DATE,
                'validate' => 'isDate',
                'required' => false,
            ],
        ],
    ];

    public static function getOrderHistory($id_order)
    {
        $sql = new DbQuery();
        $sql->select('o.id_order, o.id_order_state, o.date_add, o.date_upd, oh.id_brt_state, oh.tracking_number, oh.id_collo, oh.anno_spedizione')
            ->from('orders', 'o')
            ->leftJoin('mp_brtinfo_tracking_number', 'oh', 'o.id_order = oh.id_order')
            ->where('o.id_order_state = oh.id_order_state')
            ->where('o.id_order = ' . (int) $id_order)
            ->orderBy('a.' . self::$definition['primary'] . ' DESC');

        return Db::getInstance()->executeS($sql);
    }

    public static function addHistory($id_order, $id_order_state, $id_brt_state, $tracking_number, $id_collo, $anno_spedizione)
    {
        $history = new self();
        $history->id_order = (int) $id_order;
        $history->id_order_state = (int) $id_order_state;
        $history->id_brt_state = (int) $id_brt_state;
        $history->tracking_number = pSQL($tracking_number);
        $history->id_collo = (int) $id_collo;
        $history->anno_spedizione = (int) $anno_spedizione;
        $history->date_add = date('Y-m-d H:i:s');
        $history->date_upd = date('Y-m-d H:i:s');

        return $history->add();
    }

    public static function getOrderByLastOrderState(int $id_order_state)
    {
        $sql = new DbQuery();
        $sql->select('id_order')
            ->from(self::$definition['table'])
            ->where('id_order_state = ' . (int) $id_order_state)
            ->orderBy(self::$definition['primary'] . ' DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getOrdersByLastCurrentState(string $order_state)
    {
        $id_lang = (int) Context::getContext()->language->id;
        $sql = new DbQuery();

        switch ($order_state) {
            case self::SENT:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_SENT);

                break;
            case self::DELIVERED:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_DELIVERED);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_DELIVERED);

                break;
            case self::TRANSIT:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_TRANSIT);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_TRANSIT);

                break;
            case self::FERMOPOINT:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_FERMOPOINT);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_FERMOPOINT);

                break;
            case self::REFUSED:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_REFUSED);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_REFUSED);

                break;
            case self::WAITING:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_WAITING);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_WAITING);

                break;
            case self::ERROR:
                $order_state = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_EVENT_ERROR);
                $id_order_state = ModelBrtEvento::getOrderStatesByBrtState(ModelBrtEvento::EVENT_ERROR);

                break;
        }

        $sql->select('a.id_order, a.id_brt_state, a.id_order_state, a.id_collo, a.tracking_number, a.current_state, a.date_add, o.total_paid_tax_incl, c.email, concat(c.firstname, " ", c.lastname) as customer, evt.name as evento')
            ->from(self::$definition['table'], 'a')
            ->leftJoin('orders', 'o', 'a.id_order = o.id_order and o.id_order is not null')
            ->leftJoin('customer', 'c', 'o.id_customer = c.id_customer and c.id_customer is not null')
            ->leftJoin('mpbrtinfo_evento', 'evt', 'a.id_brt_state = evt.id_evento and evt.id_evento is not null')
            ->groupBy('a.id_order')
            ->where('a.id_brt_state in (' . implode(',', $id_order_state) . ')')
            ->having('a.date_add = MAX(a.date_add)')
            ->orderBy(self::$definition['primary'] . ' DESC')
            ->limit(50);
        $sql = $sql->build();
        $rows = Db::getInstance()->executeS($sql);

        if (!$rows) {
            return [];
        }

        foreach ($rows as &$row) {
            if ($row['id_collo'] && $row['tracking_number']) {
                $row['tracking_number'] = $row['id_collo'];
            }
        }

        return $rows;
    }

    public static function getTrackingNumberByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('tracking_number')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(self::$definition['primary'] . ' DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getTrackingNumberByIdCollo($id_collo, $anno)
    {
        $sql = new DbQuery();
        $sql->select('tracking_number')
            ->from(self::$definition['table'])
            ->where('id_collo = ' . (int) $id_collo)
            ->where('anno_spedizione = ' . (int) $anno)
            ->orderBy(self::$definition['primary'] . ' DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function getTrackingNumberByRMN($rmn)
    {
        $brt_id = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);
        $class = new BrtSoapClientIdSpedizioneByRMN();
        $res = $class->getSoapIdSpedizioneByRMN($brt_id, $rmn);
        if ($res['esito'] === 0) {
            $tracking = $res['spedizione_id'];

            return $tracking;
        }

        return false;
    }

    public static function getTrackingNumberByRMA($rma)
    {
        $brt_id = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_ID_BRT_CUSTOMER);
        $class = new BrtSoapClientIdSpedizioneByRMA();
        $res = $class->getSoapIdSpedizioneByRMA($brt_id, $rma);
        if ($res['esito'] === 0) {
            $tracking = $res['spedizione_id'];

            return $tracking;
        }

        return false;
    }

    public static function getLastRowByIdOrder($id_order)
    {
        $sql = new DbQuery();
        $sql->select('*')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(self::$definition['primary'] . ' DESC');

        return Db::getInstance()->getRow($sql);
    }

    public static function getIdOrderStateByIdOrder($id_order)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_order_state')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(self::$definition['primary'] . ' DESC');

        $value = (int) $db->getValue($sql);

        if (!$value) {
            $order = new Order($id_order);
            if (!\Validate::isLoadedObject($order) ) {
                return 0;
            }

            $id_brt_state = ModelBrtConfig::getBrtStateFromIdOrderState($order->current_state);
        }

        return $value;
    }

    public static function getIdColloByIdOrder($id_order)
    {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $tracking_by = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_TYPE);
        $tracking_on = ModelBrtConfig::getConfigValue(ModelBrtConfig::MP_BRT_INFO_SEARCH_WHERE);
        $rmn = '';
        $rma = '';

        if ($tracking_by == ModelBrtConfig::MP_BRT_INFO_SEARCH_BY_RMN && $tracking_on == ModelBrtConfig::MP_BRT_INFO_SEARCH_ON_ID) {
            $rmn = $order->id;
            $rma = '';
        } elseif ($tracking_by == ModelBrtConfig::MP_BRT_INFO_SEARCH_BY_RMN && $tracking_on == ModelBrtConfig::MP_BRT_INFO_SEARCH_ON_REFERENCE) {
            $rmn = $order->reference;
            $rma = '';
        } elseif ($tracking_by == ModelBrtConfig::MP_BRT_INFO_SEARCH_BY_RMA && $tracking_on == ModelBrtConfig::MP_BRT_INFO_SEARCH_ON_ID) {
            $rmn = '';
            $rma = $order->id;
        } elseif ($tracking_by == ModelBrtConfig::MP_BRT_INFO_SEARCH_BY_RMA && $tracking_on == ModelBrtConfig::MP_BRT_INFO_SEARCH_ON_REFERENCE) {
            $rmn = '';
            $rma = $order->reference;
        }

        $sql = new DbQuery();
        $sql->select('`id_collo` as `tracking_number`, `anno_spedizione`')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->where('id_collo is not null')
            ->orderBy(self::$definition['primary'] . ' DESC');

        $tracking = Db::getInstance()->getRow($sql);
        if ($tracking && strlen($tracking['tracking_number']) == 12) {
            return $tracking;
        }

        // Cerco il tracking nella tabella order_carrier
        $sql = new DbQuery();
        $sql->select("`tracking_number`, YEAR('date_add') as `anno_spedizione`")
            ->from('order_carrier')
            ->where('id_order = ' . (int) $id_order)
            ->where('tracking_number is not null')
            ->orderBy('id_order_carrier DESC');
        $tracking = Db::getInstance()->getRow($sql);

        if ($tracking && strlen($tracking['tracking_number']) == 12) {
            return $tracking;
        }

        // se l'ordine è più vecchio di 15 giorni dalla data odierna, il tracking non può più essere richiesto
        $db = Db::getInstance();
        $sql = 'SELECT date_add from ' . _DB_PREFIX_ . 'orders where id_order = ' . (int) $id_order;
        $date_add = $db->getValue($sql);
        if ($date_add && Validate::isDate($date_add)) {
            $date_add = new DateTime($date_add);
            $date_now = new DateTime();
            $diff = $date_add->diff($date_now);
            if ($diff->days > 15) {
                return false;
            }
        }

        // Cerco il tracking nel DB di BRT
        if ($rmn) {
            $tracking = self::getTrackingNumberByRMN($rmn);
        } elseif ($rma) {
            $tracking = self::getTrackingNumberByRMA($rma);
        }

        if ($tracking) {
            // Aggiorno il tracking nella tabella order_carrier
            $db = db::getInstance();
            $db->update(
                'order_carrier',
                [
                    'tracking_number' => pSQL($tracking),
                ],
                'id_order = ' . (int) $id_order
            );

            // Aggiorno il tracking nella tabella tracking_number
            $db = db::getInstance();
            $sql = new DbQuery();
            $sql->select('id_mpbrtinfo_tracking_number')
                ->from('mpbrtinfo_tracking_number')
                ->where('id_order = ' . (int) $id_order)
                ->where("current_state = 'SENT'")
                ->orderBy('id_mpbrtinfo_tracking_number DESC');
            $id_tracking = (int) $db->getValue($sql);
            $anno_spedizione = self::getAnnoSpedizione($id_order);

            $brtTracking = new ModelBrtTrackingNumber($id_tracking);
            $brtTracking->id_order = (int) $id_order;
            $brtTracking->id_order_state = (int) $order->current_state;
            $brtTracking->id_brt_state = self::getBrtIdState(ModelBrtEvento::EVENT_SENT);
            $brtTracking->date_event = date('Y-m-d H:i:s');
            $brtTracking->id_collo = $tracking;
            $brtTracking->tracking_number = $tracking;
            $brtTracking->rmn = $rmn;
            $brtTracking->rma = $rma;
            $brtTracking->current_state = 'SENT';
            $brtTracking->anno_spedizione = $anno_spedizione;
            $brtTracking->date_shipped = date('Y-m-d H:i:s');
            $brtTracking->date_delivered = null;
            $brtTracking->date_add = date('Y-m-d H:i:s');

            try {
                $brtTracking->save();
                $db->update(
                    'mpbrtinfo_tracking_number',
                    [
                        'id_collo' => $tracking,
                        'tracking_number' => $tracking,
                        'rmn' => $rmn,
                        'rma' => $rma,
                        'date_shipped' => date('Y-m-d H:i:s'),
                        'anno_spedizione' => $anno_spedizione,
                    ],
                    'id_order = ' . (int) $order->id
                );
            } catch (\Throwable $th) {
                self::$errors[] = $th->getMessage();
            }

            return [
                'tracking_number' => $tracking,
                'anno_spedizione' => $anno_spedizione,
            ];
        }

        return false;
    }

    public static function getIdColloByTrackingNumber($tracking_number, $anno)
    {
        $sql = new DbQuery();
        $sql->select('id_collo')
            ->from(self::$definition['table'])
            ->where('tracking_number = "' . pSQL($tracking_number) . '"')
            ->where('anno_spedizione = ' . (int) $anno)
            ->orderBy(self::$definition['primary'] . ' DESC');

        return Db::getInstance()->getValue($sql);
    }

    public static function setAsSent($id_order, $tracking)
    {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            return false;
        }

        $id_order_state = (int) $order->current_state;
        $id_order_state_sent = Configuration::get('MPBRTINFO_ORDER_STATE_SENT');
        $current_state = $tracking ? 'TRANSIT' : 'SENT';

        $model = new ModelBrtTrackingNumber();
        $model->id_order = (int) $id_order;
        $model->id_order_state = $id_order_state;
        $model->id_brt_state = self::getBrtIdState(ModelBrtEvento::EVENT_SENT);
        $model->id_collo = '';
        $model->rmn = $id_order;
        $model->tracking_number = $tracking;
        $model->current_state = $current_state;
        $model->anno_spedizione = date('Y');
        $model->date_add = date('Y-m-d H:i:s');

        $res = $model->add();
        if ($res) {
            $order = new Order($id_order);
            $order->setCurrentState($id_order_state_sent);
        }

        return $res;
    }

    public static function getBrtIdState($state = '')
    {
        $eventi = ModelBrtEvento::getEventi($state);
        if ($eventi) {
            foreach ($eventi as $evento) {
                return $evento->id_evento;
            }
        }

        return 0;
    }

    public static function getLastState($id_order)
    {
        $db = Db::getInstance();
        $sql = new DbQuery();
        $sql->select('id_brt_state')
            ->from(self::$definition['table'])
            ->where('id_order = ' . (int) $id_order)
            ->orderBy(self::$definition['primary'] . ' DESC');

        $value = $db->getValue($sql);
        if ($value) {
            return $value;
        }

        return false;
    }

    public static function countDays($date_start, $date_end)
    {
        if (!$date_start || !$date_end) {
            return 0;
        }
        if ($date_start) {
            $date_start = self::justDays($date_start);
        }
        if ($date_end) {
            $date_end = self::justDays($date_end);
        }

        return self::workingDays($date_start, $date_end) + 1;
    }

    public static function justDays($date)
    {
        $createDate = new DateTime($date);
        $strip = $createDate->format('Y-m-d');

        return $strip;
    }

    public static function workingDays($date_start, $date_end)
    {
        $holidayDays = [
            '*-01-01' => 'Capodanno',
            '*-01-06' => 'Epifania',
            '*-04-25' => 'Liberazione',
            '*-05-01' => 'Festa Lavoratori',
            '*-06-02' => 'Festa della Repubblica',
            '*-08-15' => 'Ferragosto',
            '*-11-01' => 'Tutti i Santi',
            '*-12-08' => 'Immacolata',
            '*-12-25' => 'Natale',
            '*-12-26' => 'Santo Stefano',
        ];

        $AnnoInizio = date('Y', strtotime($date_start));
        $pasquetta = self::pasquetta($AnnoInizio);
        $holidayDays[$pasquetta] = 'Pasquetta ' . $AnnoInizio;

        $AnnoFine = date('Y', strtotime($date_end));
        if ($AnnoFine != $AnnoInizio) {
            $pasquetta2 = self::pasquetta($AnnoFine);
            $holidayDays[$pasquetta2] = 'Pasquetta ' . $AnnoFine;
        }
        $working_days = self::numberOfWorkingDays($date_start, $date_end, $holidayDays);

        return $working_days;
    }

    public static function numberOfWorkingDays($from, $to, $holidayDays, $workingDays = [1, 2, 3, 4, 5])
    {
        $holidayDays = array_flip($holidayDays);

        $from = new DateTime($from);
        $from = new DateTime($from->format('Y-m-d'));

        $to = new DateTime($to);
        $to = new DateTime($to->format('Y-m-d'));

        $interval = new DateInterval('P1D');
        $periods = new DatePeriod($from, $interval, $to);

        $days = 0;
        foreach ($periods as $period) {
            if (!in_array($period->format('N'), $workingDays)) {
                continue;
            }
            if (in_array($period->format('Y-m-d'), $holidayDays)) {
                continue;
            }
            if (in_array($period->format('*-m-d'), $holidayDays)) {
                continue;
            }
            ++$days;
        }

        return $days;
    }

    public static function pasquetta($anno)
    {
        $nc = (int) ($anno / 100);
        $nn = $anno - 19 * (int) ($anno / 19);
        $nk = (int) (($nc - 17) / 25);
        $ni1 = $nc - (int) ($nc / 4) - (int) (($nc - $nk) / 3) + 19 * $nn + 15;
        $ni2 = $ni1 - 30 * (int) ($ni1 / 30);
        $ni3 = $ni2 - (int) ($ni2 / 28) * (1 - (int) ($ni2 / 28) * (int) (29 / ($ni2 + 1)) * (int) ((21 - $nn) / 11));
        $nj1 = $anno + (int) ($anno / 4) + $ni3 + 2 - $nc + (int) ($nc / 4);
        $nj2 = $nj1 - 7 * (int) ($nj1 / 7);
        $nl = $ni3 - $nj2;

        $pMese = 3 + (int) (($nl + 40) / 44);
        $pGiorno = $nl + 28 - 31 * (int) ($pMese / 4);

        if ($pMese == 3 and $pGiorno == 31) {
            $lMese = 4;
            $lGiorno = 1;
        } else {
            $lMese = $pMese;
            $lGiorno = $pGiorno + 1;
        }

        return date('Y') . '-' . $lMese . '-' . $lGiorno;
    }

    public static function getAnnoSpedizione($id_order)
    {
        $sql = new DbQuery();
        $sql->select('YEAR(date_add) as anno')
            ->from('order_carrier')
            ->where('id_order = ' . (int) $id_order)
            ->orderBy('date_add ASC');
        $year = (int) Db::getInstance()->getValue($sql);

        if ($year) {
            return $year;
        }

        return date('Y');
    }

    public static function getDateShipped($id_order)
    {
        $sent = ModelBrtConfig::get(ModelBrtConfig::MP_BRT_INFO_EVENT_SENT);
        if (is_array($sent)) {
            $sent = array_map('intval', $sent);
            $sent = implode(',', $sent);
        }

        $sql = new DbQuery();
        $sql->select('date_add')
            ->from('order_history')
            ->where('id_order = ' . (int) $id_order)
            ->where('id_order_state in (' . $sent . ')')
            ->orderBy('date_add ASC');
        $date = Db::getInstance()->getValue($sql);
        if ($date) {
            return $date;
        }

        return date('Y-m-d H:i:s');
    }

    public static function setDeliveredDays($id, $date, $date_add)
    {
        if ($date == '0000-00-00 00:00:00') {
            $date = $date_add;
        }

        $db = Db::getInstance();

        return $db->update(
            self::$definition['table'],
            [
                'date_delivered' => pSQL($date),
                'days' => self::countDays($date_add, $date),
                'date_upd' => date('Y-m-d H:i:s'),
            ],
            'id_mpbrtinfo_tracking_number = ' . (int) $id
        );
    }
}
