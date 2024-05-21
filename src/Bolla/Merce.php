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

class Merce
{
    protected $colli;
    protected $natura_merce;
    protected $peso_kg;
    protected $volume_m3;

    public function __construct($merce)
    {
        $this->colli = $merce['COLLI'];
        $this->natura_merce = $merce['NATURA_MERCE'];
        $this->peso_kg = $merce['PESO_KG'];
        $this->volume_m3 = $merce['VOLUME_M3'];
    }

    public function getColli()
    {
        return $this->colli;
    }

    public function setColli($colli)
    {
        $this->colli = $colli;
    }

    public function getNaturaMerce()
    {
        return $this->natura_merce;
    }

    public function setNaturaMerce($natura_merce)
    {
        $this->natura_merce = $natura_merce;
    }

    public function getPesoKg()
    {
        return $this->peso_kg;
    }

    public function setPesoKg($peso_kg)
    {
        $this->peso_kg = $peso_kg;
    }

    public function getVolumeM3()
    {
        return $this->volume_m3;
    }

    public function setVolumeM3($volume_m3)
    {
        $this->volume_m3 = $volume_m3;
    }
}
