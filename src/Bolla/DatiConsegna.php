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

class DatiConsegna
{
    protected $data_consegna_merce;
    protected $data_cons_richiesta;
    protected $data_teorica_consegna;
    protected $descrizione_cons_richiesta;
    protected $firmatario_consegna;
    protected $ora_consegna_merce;
    protected $ora_cons_richiesta;
    protected $ora_teorica_consegna_a;
    protected $ora_teorica_consegna_da;
    protected $tipo_cons_richiesta;

    public function __construct($dati_consegna)
    {
        $this->data_consegna_merce = $dati_consegna['DATA_CONSEGNA_MERCE'];
        $this->data_cons_richiesta = $dati_consegna['DATA_CONS_RICHIESTA'];
        $this->data_teorica_consegna = $dati_consegna['DATA_TEORICA_CONSEGNA'];
        $this->descrizione_cons_richiesta = $dati_consegna['DESCRIZIONE_CONS_RICHIESTA'];
        $this->firmatario_consegna = $dati_consegna['FIRMATARIO_CONSEGNA'];
        $this->ora_consegna_merce = $dati_consegna['ORA_CONSEGNA_MERCE'];
        $this->ora_cons_richiesta = $dati_consegna['ORA_CONS_RICHIESTA'];
        $this->ora_teorica_consegna_a = $dati_consegna['ORA_TEORICA_CONSEGNA_A'];
        $this->ora_teorica_consegna_da = $dati_consegna['ORA_TEORICA_CONSEGNA_DA'];
        $this->tipo_cons_richiesta = $dati_consegna['TIPO_CONS_RICHIESTA'];
    }

    public function getDataConsegnaMerce()
    {
        return $this->data_consegna_merce;
    }

    public function setDataConsegnaMerce($data_consegna_merce)
    {
        $this->data_consegna_merce = $data_consegna_merce;
    }

    public function getDataConsRichiesta()
    {
        return $this->data_cons_richiesta;
    }

    public function setDataConsRichiesta($data_cons_richiesta)
    {
        $this->data_cons_richiesta = $data_cons_richiesta;
    }

    public function getDataTeoricaConsegna()
    {
        return $this->data_teorica_consegna;
    }

    public function setDataTeoricaConsegna($data_teorica_consegna)
    {
        $this->data_teorica_consegna = $data_teorica_consegna;
    }

    public function getDescrizioneConsRichiesta()
    {
        return $this->descrizione_cons_richiesta;
    }

    public function setDescrizioneConsRichiesta($descrizione_cons_richiesta)
    {
        $this->descrizione_cons_richiesta = $descrizione_cons_richiesta;
    }

    public function getFirmatarioConsegna()
    {
        return $this->firmatario_consegna;
    }

    public function setFirmatarioConsegna($firmatario_consegna)
    {
        $this->firmatario_consegna = $firmatario_consegna;
    }

    public function getOraConsegnaMerce()
    {
        return $this->ora_consegna_merce;
    }

    public function setOraConsegnaMerce($ora_consegna_merce)
    {
        $this->ora_consegna_merce = $ora_consegna_merce;
    }

    public function getOraConsRichiesta()
    {
        return $this->ora_cons_richiesta;
    }

    public function setOraConsRichiesta($ora_cons_richiesta)
    {
        $this->ora_cons_richiesta = $ora_cons_richiesta;
    }

    public function getOraTeoricaConsegnaA()
    {
        return $this->ora_teorica_consegna_a;
    }

    public function setOraTeoricaConsegnaA($ora_teorica_consegna_a)
    {
        $this->ora_teorica_consegna_a = $ora_teorica_consegna_a;
    }

    public function getOraTeoricaConsegnaDa()
    {
        return $this->ora_teorica_consegna_da;
    }

    public function setOraTeoricaConsegnaDa($ora_teorica_consegna_da)
    {
        $this->ora_teorica_consegna_da = $ora_teorica_consegna_da;
    }

    public function getTipoConsRichiesta()
    {
        return $this->tipo_cons_richiesta;
    }

    public function setTipoConsRichiesta($tipo_cons_richiesta)
    {
        $this->tipo_cons_richiesta = $tipo_cons_richiesta;
    }
}
