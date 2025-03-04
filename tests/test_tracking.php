<?php
/**
 * Script di test per la classe TrackingByBRTshipmentID
 * 
 * Questo script permette di testare la funzionalità di tracking delle spedizioni BRT
 * utilizzando la classe TrackingByBRTshipmentID.
 * 
 * Utilizzo: 
 * - Da browser: /modules/mpbrtinfo/tests/test_tracking.php?tracking_number=NUMERO_SPEDIZIONE
 * - Da CLI: php test_tracking.php NUMERO_SPEDIZIONE
 */

// Inizializzazione dell'ambiente PrestaShop
include_once dirname(__FILE__) . '/../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../../init.php';

// Importazione delle classi necessarie
use MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID;

// Funzione per visualizzare i risultati in modo leggibile
function displayResult($data, $isError = false) {
    if (php_sapi_name() === 'cli') {
        // Output per CLI
        if ($isError) {
            echo "\033[31mERRORE:\033[0m " . print_r($data, true) . "\n";
        } else {
            echo print_r($data, true) . "\n";
        }
    } else {
        // Output per browser
        echo '<pre>';
        if ($isError) {
            echo '<strong style="color: red">ERRORE:</strong><br>';
            print_r($data);
        } else {
            print_r($data);
        }
        echo '</pre>';
    }
}

// Recupera il numero di tracking
$tracking_number = '';

if (php_sapi_name() === 'cli') {
    // Se eseguito da CLI, prendi il parametro dalla riga di comando
    global $argv;
    if (isset($argv[1])) {
        $tracking_number = $argv[1];
    }
} else {
    // Se eseguito da browser, prendi il parametro dall'URL
    $tracking_number = Tools::getValue('tracking_number', '');
}

// Verifica che il numero di tracking sia stato fornito
if (empty($tracking_number)) {
    displayResult('Nessun numero di spedizione fornito. Utilizzo: ?tracking_number=NUMERO_SPEDIZIONE', true);
    exit;
}

// Inizializza il client SOAP
try {
    $client = new TrackingByBRTshipmentID();
    
    // Effettua la chiamata al servizio BRT
    $result = $client->getTracking($tracking_number);
    
    if ($result === false) {
        // In caso di errore, mostra i messaggi di errore
        displayResult($client->getErrors(), true);
    } else {
        // In caso di successo, mostra i dati della spedizione
        echo '<h2>Dati della spedizione</h2>';
        displayResult($result);
        
        // Se disponibile, mostra anche i dati estratti
        if (method_exists($result, 'toArray')) {
            echo '<h2>Dati estratti</h2>';
            displayResult($result->toArray());
        }
    }
} catch (Exception $e) {
    // Gestione delle eccezioni
    displayResult('Eccezione: ' . $e->getMessage(), true);
}
