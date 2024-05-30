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

class Riferimenti
{
    protected $riferimento_mittente_alfabetico;
    protected $riferimento_mittente_numerico;
    protected $riferimento_partner_estero;

    public function __construct($riferimenti)
    {
        $this->riferimento_mittente_alfabetico = $riferimenti['RIFERIMENTO_MITTENTE_ALFABETICO'];
        $this->riferimento_mittente_numerico = $riferimenti['RIFERIMENTO_MITTENTE_NUMERICO'];
        $this->riferimento_partner_estero = $riferimenti['RIFERIMENTO_PARTNER_ESTERO'];
    }

    public function getRiferimentoMittenteAlfabetico()
    {
        return $this->riferimento_mittente_alfabetico;
    }

    public function setRiferimentoMittenteAlfabetico($riferimento_mittente_alfabetico)
    {
        $this->riferimento_mittente_alfabetico = $riferimento_mittente_alfabetico;
    }

    public function getRiferimentoMittenteNumerico()
    {
        return $this->riferimento_mittente_numerico;
    }

    public function setRiferimentoMittenteNumerico($riferimento_mittente_numerico)
    {
        $this->riferimento_mittente_numerico = $riferimento_mittente_numerico;
    }

    public function getRiferimentoPartnerEstero()
    {
        return $this->riferimento_partner_estero;
    }

    public function setRiferimentoPartnerEstero($riferimento_partner_estero)
    {
        $this->riferimento_partner_estero = $riferimento_partner_estero;
    }
}
