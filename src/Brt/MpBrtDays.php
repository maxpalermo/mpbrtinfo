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

namespace MpSoft\MpBrtInfo\Brt;

if (!defined('_PS_VERSION_')) {
    exit;
}
class MpBrtDays
{
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

        return self::workingDays($date_start, $date_end);
    }

    public static function justDays($date)
    {
        $createDate = new \DateTime($date);
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
            '*-11-01' => 'Tutti Santi',
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

        $from = new \DateTime($from);
        $from = new \DateTime($from->format('Y-m-d'));

        $to = new \DateTime($to);
        $to = new \DateTime($to->format('Y-m-d'));

        $interval = new \DateInterval('P1D');
        $periods = new \DatePeriod($from, $interval, $to);

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

        $p_mese = 3 + (int) (($nl + 40) / 44);
        $p_giorno = $nl + 28 - 31 * (int) ($p_mese / 4);

        if ($p_mese == 3 and $p_giorno == 31) {
            $l_mese = 4;
            $l_giorno = 1;
        } else {
            $l_mese = $p_mese;
            $l_giorno = $p_giorno + 1;
        }

        return date('Y') . '-' . $l_mese . '-' . $l_giorno;
    }

    public function parseDate($value)
    {
        if (is_array($value)) {
            $date = $value[0];
            $time = $value[1];
        } else {
            $date = $value;
            $time = '0000';
        }

        if (!$date) {
            return '';
        }
        if (!$time) {
            $time = '0000';
        }

        $time = str_pad($time, 4, '0', STR_PAD_LEFT);
        $year = \Tools::substr($date, 0, 4);
        $month = \Tools::substr($date, 4, 2);
        $day = \Tools::substr($date, 6, 2);
        $hour = \Tools::substr($time, 0, 2);
        $minutes = \Tools::substr($time, 2, 2);
        $res = $year . '-' . $month . '-' . $day . ' ' . $hour . ':' . $minutes . ':00';

        return $res;
    }
}
