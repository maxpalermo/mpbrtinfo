<?php
/**
 * Esempio di utilizzo della classe GetIdSpedizioneByRMN in un controller PrestaShop
 * 
 * Questo file mostra come integrare la classe GetIdSpedizioneByRMN in un controller
 * PrestaShop per implementare una funzionalità AJAX che recupera l'ID di una spedizione
 * tramite riferimento mittente numerico.
 */

/**
 * Esempio di metodo AJAX in un controller ModuleFrontController
 */
function displayAjaxGetBrtShipmentId()
{
    // Verifica che la richiesta sia di tipo AJAX
    if (!$this->isXmlHttpRequest()) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Richiesta non valida',
        ]));
    }

    // Recupera i parametri dalla richiesta
    $riferimento = Tools::getValue('riferimento', '');
    $cliente_id = Tools::getValue('cliente_id', '');

    // Verifica che i parametri siano validi
    if (empty($riferimento) || empty($cliente_id)) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Parametri mancanti: riferimento e cliente_id sono obbligatori',
        ]));
    }

    // Inizializza il client SOAP
    try {
        // Importa la classe GetIdSpedizioneByRMN
        require_once _PS_MODULE_DIR_ . 'mpbrtinfo/src/Soap/GetIdSpedizioneByRMN.php';
        
        // Crea un'istanza della classe
        $client = new MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByRMN();
        
        // Effettua la chiamata al servizio BRT
        $result = $client->getIdSpedizione($riferimento, $cliente_id);
        
        if ($result === false) {
            // In caso di errore, restituisci i messaggi di errore
            $this->ajaxDie(json_encode([
                'success' => false,
                'message' => 'Errore durante la ricerca dell\'ID spedizione',
                'errors' => $client->getErrors(),
            ]));
        } else {
            // In caso di successo, restituisci l'ID spedizione
            $this->ajaxDie(json_encode([
                'success' => true,
                'riferimento' => $riferimento,
                'cliente_id' => $cliente_id,
                'result' => $result,
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
 * Esempio di utilizzo combinato di GetIdSpedizioneByRMN e TrackingByBRTshipmentID
 * per recuperare i dettagli di una spedizione tramite riferimento mittente numerico
 */
function displayAjaxGetBrtShipmentDetailsByRMN()
{
    // Verifica che la richiesta sia di tipo AJAX
    if (!$this->isXmlHttpRequest()) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Richiesta non valida',
        ]));
    }

    // Recupera i parametri dalla richiesta
    $riferimento = Tools::getValue('riferimento', '');
    $cliente_id = Tools::getValue('cliente_id', '');
    $id_order = (int) Tools::getValue('id_order', 0);

    // Verifica che i parametri siano validi
    if (empty($riferimento) || empty($cliente_id)) {
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Parametri mancanti: riferimento e cliente_id sono obbligatori',
        ]));
    }

    // Verifica che l'ordine esista se fornito
    if ($id_order > 0) {
        $order = new Order($id_order);
        if (!Validate::isLoadedObject($order)) {
            $this->ajaxDie(json_encode([
                'success' => false,
                'message' => 'Ordine non trovato',
            ]));
        }
    }

    // Inizializza il client SOAP
    try {
        // Importa le classi necessarie
        require_once _PS_MODULE_DIR_ . 'mpbrtinfo/src/Soap/GetIdSpedizioneByRMN.php';
        require_once _PS_MODULE_DIR_ . 'mpbrtinfo/src/Soap/TrackingByBRTshipmentID.php';
        
        // Crea un'istanza della classe GetIdSpedizioneByRMN
        $client = new MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByRMN();
        
        // Effettua la chiamata per ottenere l'ID spedizione
        $result = $client->getIdSpedizione($riferimento, $cliente_id);
        
        if ($result === false) {
            // In caso di errore, restituisci i messaggi di errore
            $this->ajaxDie(json_encode([
                'success' => false,
                'message' => 'Errore durante la ricerca dell\'ID spedizione',
                'errors' => $client->getErrors(),
            ]));
        } else {
            // Verifica che sia stato trovato un ID spedizione
            if (empty($result['spedizione_id'])) {
                $this->ajaxDie(json_encode([
                    'success' => false,
                    'message' => 'Nessuna spedizione trovata con i riferimenti forniti',
                ]));
            }
            
            // Recupera l'ID spedizione
            $spedizione_id = $result['spedizione_id'];
            
            // Crea un'istanza della classe TrackingByBRTshipmentID
            $tracking_client = new MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID();
            
            // Imposta l'ID dell'ordine nel contesto se fornito
            if ($id_order > 0) {
                $_GET['id_order'] = $id_order;
            }
            
            // Effettua la chiamata per ottenere i dettagli della spedizione
            $tracking_result = $tracking_client->getTracking($spedizione_id);
            
            if ($tracking_result === false) {
                // In caso di errore, restituisci i messaggi di errore
                $this->ajaxDie(json_encode([
                    'success' => false,
                    'message' => 'Errore durante il recupero dei dettagli della spedizione',
                    'errors' => $tracking_client->getErrors(),
                ]));
            } else {
                // In caso di successo, restituisci i dati della spedizione
                $shipment_data = [];
                
                // Converti l'oggetto in array se disponibile il metodo
                if (method_exists($tracking_result, 'toArray')) {
                    $shipment_data = $tracking_result->toArray();
                } else {
                    // Altrimenti, estrai i dati manualmente
                    $shipment_data = $tracking_client->extractShipmentData($tracking_result);
                }
                
                // Restituisci i dati in formato JSON
                $this->ajaxDie(json_encode([
                    'success' => true,
                    'riferimento' => $riferimento,
                    'cliente_id' => $cliente_id,
                    'id_order' => $id_order,
                    'spedizione_id' => $spedizione_id,
                    'shipment_data' => $shipment_data,
                    'raw_data' => $tracking_result,
                ]));
            }
        }
    } catch (Exception $e) {
        // Gestione delle eccezioni
        $this->ajaxDie(json_encode([
            'success' => false,
            'message' => 'Eccezione: ' . $e->getMessage(),
        ]));
    }
}
