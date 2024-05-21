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

class Mittente
{
    protected $cap;
    protected $codice;
    protected $indirizzo;
    protected $localita;
    protected $ragione_sociale;
    protected $sigla_area;

    public function __construct($mittente)
    {
        $this->cap = $mittente['CAP'];
        $this->codice = $mittente['CODICE'];
        $this->indirizzo = $mittente['INDIRIZZO'];
        $this->localita = $mittente['LOCALITA'];
        $this->ragione_sociale = $mittente['RAGIONE_SOCIALE'];
        $this->sigla_area = $mittente['SIGLA_AREA'];
    }

    public function getCap()
    {
        return $this->cap;
    }

    public function setCap($cap)
    {
        $this->cap = $cap;
    }

    public function getCodice()
    {
        return $this->codice;
    }

    public function setCodice($codice)
    {
        $this->codice = $codice;
    }

    public function getIndirizzo()
    {
        return $this->indirizzo;
    }

    public function setIndirizzo($indirizzo)
    {
        $this->indirizzo = $indirizzo;
    }

    public function getLocalita()
    {
        return $this->localita;
    }

    public function setLocalita($localita)
    {
        $this->localita = $localita;
    }

    public function getRagioneSociale()
    {
        return $this->ragione_sociale;
    }

    public function setRagioneSociale($ragione_sociale)
    {
        $this->ragione_sociale = $ragione_sociale;
    }

    public function getSiglaArea()
    {
        return $this->sigla_area;
    }

    public function setSiglaArea($sigla_area)
    {
        $this->sigla_area = $sigla_area;
    }
}
