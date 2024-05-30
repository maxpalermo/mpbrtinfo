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

class Destinatario
{
    protected $cap;
    protected $indirizzo;
    protected $localita;
    protected $ragione_sociale;
    protected $referente_consegna;
    protected $sigla_nazione;
    protected $sigla_provincia;
    protected $telefono_referente;

    public function __construct($destinatario)
    {
        $this->cap = $destinatario['CAP'];
        $this->indirizzo = $destinatario['INDIRIZZO'];
        $this->localita = $destinatario['LOCALITA'];
        $this->ragione_sociale = $destinatario['RAGIONE_SOCIALE'];
        $this->referente_consegna = $destinatario['REFERENTE_CONSEGNA'];
        $this->sigla_nazione = $destinatario['SIGLA_NAZIONE'];
        $this->sigla_provincia = $destinatario['SIGLA_PROVINCIA'];
        $this->telefono_referente = $destinatario['TELEFONO_REFERENTE'];
    }

    public function getCap()
    {
        return $this->cap;
    }

    public function setCap($cap)
    {
        $this->cap = $cap;
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

    public function getReferenteConsegna()
    {
        return $this->referente_consegna;
    }

    public function setReferenteConsegna($referente_consegna)
    {
        $this->referente_consegna = $referente_consegna;
    }

    public function getSiglaNazione()
    {
        return $this->sigla_nazione;
    }

    public function setSiglaNazione($sigla_nazione)
    {
        $this->sigla_nazione = $sigla_nazione;
    }

    public function getSiglaProvincia()
    {
        return $this->sigla_provincia;
    }

    public function setSiglaProvincia($sigla_provincia)
    {
        $this->sigla_provincia = $sigla_provincia;
    }

    public function getTelefonoReferente()
    {
        return $this->telefono_referente;
    }

    public function setTelefonoReferente($telefono_referente)
    {
        $this->telefono_referente = $telefono_referente;
    }
}
