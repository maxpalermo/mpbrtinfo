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

namespace MpSoft\MpBrtInfo\Fetch;

use MpSoft\MpBrtInfo\Order\GetOrderShippingDate;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\WSDL\GetIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\WSDL\GetLegendaEsiti;
use MpSoft\MpBrtInfo\WSDL\GetLegendaEventi;
use MpSoft\MpBrtInfo\WSDL\GetTrackingByBrtShipmentId;


if (!defined('_PS_VERSION_')) {
    exit;
}

class FetchHandler
{
    protected $sessionJSON;

    public function __construct()
    {
        $data = file_get_contents('php://input');
        $this->sessionJSON = json_decode($data, true);
    }
    protected function ajaxRender($message)
    {
        header('Content-Type: application/json');
        exit(json_encode($message));
    }

    public function run()
    {
        if (isset($this->sessionJSON['action']) && isset($this->sessionJSON['ajax'])) {
            $action = $this->sessionJSON['action'];
            if (method_exists($this, $action)) {
                $this->ajaxRender($this->$action($this->sessionJSON));
                exit;
            }
        }

        return $this->displayAjaxError();
    }

    public function displayAjaxError()
    {
        $this->ajaxRender(['error' => 'NO METHOD FOUND']);
    }

    public function getLegendaEventi($params)
    {
        $lang = $params['lang'];
        $last_update = $params['last_update'];

        $client = new GetLegendaEventi();
        $risultati = $client->getLegendaEventi($lang, $last_update);

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            return $risultati;
        }
    }

    protected function getLegendaEsiti($params)
    {
        $lang = $params['lang'];
        $last_update = $params['last_update'];

        $client = new GetLegendaEsiti();
        $risultati = $client->getLegendaEsiti($lang, $last_update);

        if ($risultati === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            return $risultati;
        }
    }

    protected function getIdSpedizioneByRMN($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $rmn = $params['rmn'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByRMN();

        $risultato = $client->getIdSpedizione($brt_customer_id, $rmn);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    protected function getIdSpedizioneByRMA($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $rma = $params['rma'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByRMA();

        $risultato = $client->getIdSpedizione($brt_customer_id, $rma);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    /**
     * Ottiene l'ID di una spedizione BRT tramite ID collo
     * 
     * @param array $params Parametri della richiesta
     * @return array Risultato della chiamata SOAP
     */
    protected function getIdSpedizioneByIdCollo($params)
    {
        $brt_customer_id = $params['brt_customer_id'];
        $collo_id = $params['collo_id'];
        // Crea un'istanza della classe
        $client = new GetIdSpedizioneByIdCollo();

        $risultato = $client->getIdSpedizione($brt_customer_id, $collo_id);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }

    /**
     * Ottiene le informazioni di tracking di una spedizione BRT tramite l'ID spedizione BRT
     * 
     * @param array $params Parametri della richiesta
     * @return array Risultato della chiamata SOAP
     */
    protected function getTrackingByBrtShipmentId($params)
    {
        // Estrai i parametri dalla richiesta
        $spedizione_id = isset($params['spedizione_id']) ? $params['spedizione_id'] : '';
        $id_order = isset($params['id_order']) ? (int) $params['id_order'] : 0;
        $lingua_iso639_alpha2 = isset($params['lingua']) ? $params['lingua'] : 'IT';
        $anno = isset($params['spedizione_anno']) ? $params['spedizione_anno'] : '';
        // Crea un'istanza della classe
        $client = new GetTrackingByBrtShipmentId();

        // Esegui la chiamata al servizio
        $risultato = $client->getTracking($spedizione_id, $id_order, $lingua_iso639_alpha2, $anno);

        if ($risultato === false) {
            // Gestione errori
            return ['error' => implode(", ", $client->getErrors())];
        } else {
            // Elaborazione risultato
            return $risultato;
        }
    }
}