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

/*
 RISPOSTA SERVER:

    cod_filiale_arrivo = 26
    descrizione_stato_sped_parte_1 = ""
    descrizione_stato_sped_parte_2 = ""
    filiale_arrivo = "PERUGIA"
    filiale_arrivo_url = "https://services.brt.it/it/filiale/perugia"
    porto = "Mittente"
    servizio = "EXPRESS"
    spedizione_data = "31.05.2024"
    spedizione_id = "169010109536"
    stato_sped_parte_1 = ""
    stato_sped_parte_2 = ""
    tipo_porto = "F"
    tipo_servizio = "C"
 */

namespace MpSoft\MpBrtInfo\Bolla;

if (!defined('_PS_VERSION_')) {
    exit;
}

class DatiSpedizione
{
    protected $cod_filiale_arrivo;
    protected $descrizione_stato_sped_parte_1;
    protected $descrizione_stato_sped_parte_2;
    protected $filiale_arrivo;
    protected $filiale_arrivo_url;
    protected $porto;
    protected $servizio;
    protected $spedizione_data;
    protected $spedizione_id;
    protected $stato_sped_parte_1;
    protected $stato_sped_parte_2;
    protected $tipo_porto;
    protected $tipo_servizio;

    public function __construct($dati_spedizione)
    {
        $this->cod_filiale_arrivo = $dati_spedizione['COD_FILIALE_ARRIVO'];
        $this->descrizione_stato_sped_parte_1 = $dati_spedizione['DESCRIZIONE_STATO_SPED_PARTE1'];
        $this->descrizione_stato_sped_parte_2 = $dati_spedizione['DESCRIZIONE_STATO_SPED_PARTE2'];
        $this->filiale_arrivo = $dati_spedizione['FILIALE_ARRIVO'];
        $this->filiale_arrivo_url = $dati_spedizione['FILIALE_ARRIVO_URL'];
        $this->porto = $dati_spedizione['PORTO'];
        $this->servizio = $dati_spedizione['SERVIZIO'];
        $this->spedizione_data = $dati_spedizione['SPEDIZIONE_DATA'];
        $this->spedizione_id = $dati_spedizione['SPEDIZIONE_ID'];
        $this->stato_sped_parte_1 = $dati_spedizione['STATO_SPED_PARTE1'];
        $this->stato_sped_parte_2 = $dati_spedizione['STATO_SPED_PARTE2'];
        $this->tipo_porto = $dati_spedizione['TIPO_PORTO'];
        $this->tipo_servizio = $dati_spedizione['TIPO_SERVIZIO'];
    }

    public function getCodFilialeArrivo()
    {
        return $this->cod_filiale_arrivo;
    }

    public function setCodFilialeArrivo($cod_filiale_arrivo)
    {
        $this->cod_filiale_arrivo = $cod_filiale_arrivo;
    }

    public function getDescrizioneStatoSpedParte1()
    {
        return $this->descrizione_stato_sped_parte_1;
    }

    public function setDescrizioneStatoSpedParte1($descrizione_stato_sped_parte_1)
    {
        $this->descrizione_stato_sped_parte_1 = $descrizione_stato_sped_parte_1;
    }

    public function getDescrizioneStatoSpedParte2()
    {
        return $this->descrizione_stato_sped_parte_2;
    }

    public function setDescrizioneStatoSpedParte2($descrizione_stato_sped_parte_2)
    {
        $this->descrizione_stato_sped_parte_2 = $descrizione_stato_sped_parte_2;
    }

    public function getFilialeArrivo()
    {
        return $this->filiale_arrivo;
    }

    public function setFilialeArrivo($filiale_arrivo)
    {
        $this->filiale_arrivo = $filiale_arrivo;
    }

    public function getFilialeArrivoUrl()
    {
        return $this->filiale_arrivo_url;
    }

    public function setFilialeArrivoUrl($filiale_arrivo_url)
    {
        $this->filiale_arrivo_url = $filiale_arrivo_url;
    }

    public function getPorto()
    {
        return $this->porto;
    }

    public function setPorto($porto)
    {
        $this->porto = $porto;
    }

    public function getServizio()
    {
        return $this->servizio;
    }

    public function setServizio($servizio)
    {
        $this->servizio = $servizio;
    }

    public function getSpedizioneData()
    {
        return $this->spedizione_data;
    }

    public function setSpedizioneData($spedizione_data)
    {
        $this->spedizione_data = $spedizione_data;
    }

    public function getSpedizioneId()
    {
        return $this->spedizione_id;
    }

    public function setSpedizioneId($spedizione_id)
    {
        $this->spedizione_id = $spedizione_id;
    }

    public function getStatoSpedParte1()
    {
        return $this->stato_sped_parte_1;
    }

    public function setStatoSpedParte1($stato_sped_parte_1)
    {
        $this->stato_sped_parte_1 = $stato_sped_parte_1;
    }

    public function getStatoSpedParte2()
    {
        return $this->stato_sped_parte_2;
    }

    public function setStatoSpedParte2($stato_sped_parte_2)
    {
        $this->stato_sped_parte_2 = $stato_sped_parte_2;
    }

    public function getTipoPorto()
    {
        return $this->tipo_porto;
    }

    public function setTipoPorto($tipo_porto)
    {
        $this->tipo_porto = $tipo_porto;
    }

    public function getTipoServizio()
    {
        return $this->tipo_servizio;
    }

    public function setTipoServizio($tipo_servizio)
    {
        $this->tipo_servizio = $tipo_servizio;
    }
}
