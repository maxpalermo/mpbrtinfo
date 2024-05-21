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

class Assicurazione
{
    protected $assicurazione_divisa;
    protected $assicurazione_importo;

    public function __construct($assicurazione)
    {
        $this->assicurazione_divisa = $assicurazione['ASSICURAZIONE_DIVISA'];
        $this->assicurazione_importo = $assicurazione['ASSICURAZIONE_IMPORTO'];
    }

    public function getAssicurazioneDivisa()
    {
        return $this->assicurazione_divisa;
    }

    public function getAssicurazioneImporto()
    {
        return $this->assicurazione_importo;
    }

    public function setAssicurazioneDivisa($assicurazione_divisa)
    {
        $this->assicurazione_divisa = $assicurazione_divisa;
    }

    public function setAssicurazioneImporto($assicurazione_importo)
    {
        $this->assicurazione_importo = $assicurazione_importo;
    }
}
