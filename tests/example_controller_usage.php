<?php
/**
 * Esempio di utilizzo della classe TrackingByBRTshipmentID in un controller PrestaShop
 * 
 * Questo file mostra come integrare la classe TrackingByBRTshipmentID in un controller
 * PrestaShop per implementare una funzionalità AJAX che recupera i dettagli di una spedizione.
 */

/**
 * Esempio di metodo AJAX in un controller ModuleFrontController
 */
function displayAjaxGetBrtShipmentDetails()
{
    // Verifica che la richiesta sia di tipo AJAX
    if (!$this->isXmlHttpRequest()) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Richiesta non valida',
        ]));
    }

    // Recupera i parametri dalla richiesta
    $id_order = (int) Tools::getValue('id_order', 0);
    $tracking_number = Tools::getValue('tracking_number', '');

    // Verifica che i parametri siano validi
    if (!$id_order || empty($tracking_number)) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Parametri mancanti: id_order e tracking_number sono obbligatori',
        ]));
    }

    // Verifica che l'ordine esista
    $order = new Order($id_order);
    if (!Validate::isLoadedObject($order)) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Ordine non trovato',
        ]));
    }

    // Inizializza il client SOAP
    try {
        // Importa la classe TrackingByBRTshipmentID
        require_once _PS_MODULE_DIR_ . 'mpbrtinfo/src/Soap/TrackingByBRTshipmentID.php';

        // Crea un'istanza della classe
        $client = new MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID();

        // Effettua la chiamata al servizio BRT
        $result = $client->getTracking($tracking_number);

        if ($result === false) {
            // In caso di errore, restituisci i messaggi di errore
            $this->ajaxDie(json_encode([
                'success' => false,
                'message' => 'Errore durante il recupero dei dati della spedizione',
                'errors' => $client->getErrors(),
            ]));
        } else {
            // In caso di successo, restituisci i dati della spedizione
            $shipment_data = [];

            // Converti l'oggetto in array se disponibile il metodo
            if (method_exists($result, 'toArray')) {
                $shipment_data = $result->toArray();
            } else {
                // Altrimenti, estrai i dati manualmente
                $shipment_data = $client->extractShipmentData($result);
            }

            // Restituisci i dati in formato JSON
            $this->ajaxDie(json_encode([
                'success' => true,
                'tracking_number' => $tracking_number,
                'id_order' => $id_order,
                'shipment_data' => $shipment_data,
                'raw_data' => $result,
            ]));
        }
    } catch (Exception $e) {
        // Gestione delle eccezioni
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Eccezione: ' . $e->getMessage(),
        ]));
    }
}

/**
 * Esempio di utilizzo in un controller AdminController
 */
function initContent()
{
    parent::initContent();

    // Recupera l'ID dell'ordine dalla richiesta
    $id_order = (int) Tools::getValue('id_order', 0);

    if ($id_order > 0) {
        // Recupera l'ordine
        $order = new Order($id_order);

        if (Validate::isLoadedObject($order)) {
            // Recupera il numero di tracking BRT dall'ordine
            $tracking_number = ''; // Implementa la logica per recuperare il numero di tracking

            if (!empty($tracking_number)) {
                // Inizializza il client SOAP
                try {
                    // Importa la classe TrackingByBRTshipmentID
                    require_once _PS_MODULE_DIR_ . 'mpbrtinfo/src/Soap/TrackingByBRTshipmentID.php';

                    // Crea un'istanza della classe
                    $client = new MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID();

                    // Effettua la chiamata al servizio BRT
                    $result = $client->getTracking($tracking_number);

                    if ($result !== false) {
                        // Assegna i dati della spedizione al template
                        $this->context->smarty->assign([
                            'brt_shipment_data' => $result,
                            'brt_tracking_number' => $tracking_number,
                        ]);
                    } else {
                        // Assegna gli errori al template
                        $this->context->smarty->assign([
                            'brt_errors' => $client->getErrors(),
                            'brt_tracking_number' => $tracking_number,
                        ]);
                    }
                } catch (Exception $e) {
                    // Gestione delle eccezioni
                    $this->context->smarty->assign([
                        'brt_exception' => $e->getMessage(),
                        'brt_tracking_number' => $tracking_number,
                    ]);
                }
            }
        }
    }

    // Renderizza il template
    $this->setTemplate('path/to/your/template.tpl');
}
