<?php
/**
 * Script di test per l'integrazione della classe TrackingByBRTshipmentID con PrestaShop
 * 
 * Questo script permette di testare la funzionalità di tracking delle spedizioni BRT
 * all'interno dell'ambiente di amministrazione di PrestaShop.
 * 
 * Utilizzo: 
 * - Da browser: /modules/mpbrtinfo/tests/test_admin.php?tracking_number=NUMERO_SPEDIZIONE&id_order=ID_ORDINE
 */

// Inizializzazione dell'ambiente PrestaShop
include_once dirname(__FILE__) . '/../../../config/config.inc.php';
include_once dirname(__FILE__) . '/../../../init.php';
require_once dirname(__FILE__) . '/../models/autoload.php';

// Verifica che l'utente sia autenticato come amministratore
if (!Context::getContext()->employee->isLoggedBack()) {
    Tools::redirect('index.php?controller=AdminLogin');
}

// Importazione delle classi necessarie
use MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID;

// Recupera i parametri
$tracking_number = Tools::getValue('tracking_number', '');
$id_order = (int) Tools::getValue('id_order', 0);

// Stile CSS per la pagina
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Tracking BRT</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Test Tracking BRT</h1>
';

// Form per inserire i dati di test
echo '
        <div class="card">
            <div class="card-header">Inserisci i dati per il test</div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="tracking_number">Numero Spedizione BRT:</label>
                        <input type="text" class="form-control" id="tracking_number" name="tracking_number" value="' . htmlspecialchars($tracking_number) . '" required>
                        <small class="form-text text-muted">Formato: FFFSSNNNNNNN (es. 0100123456789)</small>
                    </div>
                    <div class="form-group">
                        <label for="id_order">ID Ordine (opzionale):</label>
                        <input type="number" class="form-control" id="id_order" name="id_order" value="' . $id_order . '">
                    </div>
                    <button type="submit" class="btn btn-primary">Esegui Test</button>
                </form>
            </div>
        </div>
';

// Esegui il test se è stato fornito un numero di tracking
if (!empty($tracking_number)) {
    echo '<div class="card">';
    echo '<div class="card-header">Risultati del test</div>';
    echo '<div class="card-body">';

    try {
        // Inizializza il client SOAP
        $client = new TrackingByBRTshipmentID();

        // Imposta l'ID dell'ordine nel contesto se fornito
        if ($id_order > 0) {
            $_GET['id_order'] = $id_order;
        }

        // Effettua la chiamata al servizio BRT
        $result = $client->getTracking($tracking_number);

        if ($result === false) {
            // In caso di errore, mostra i messaggi di errore
            echo '<h4 class="error">Errori:</h4>';
            echo '<pre class="error">';
            print_r($client->getErrors());
            echo '</pre>';
        } else {
            // In caso di successo, mostra i dati della spedizione
            echo '<h4>Dati della spedizione:</h4>';
            echo '<pre>';
            print_r($result);
            echo '</pre>';

            // Mostra anche i dati estratti se disponibili
            if (method_exists($result, 'toArray')) {
                echo '<h4>Dati estratti:</h4>';
                echo '<pre>';
                print_r($result->toArray());
                echo '</pre>';
            }

            // Se è stato fornito un ID ordine, mostra informazioni sull'aggiornamento dello stato
            if ($id_order > 0) {
                echo '<h4>Aggiornamento stato ordine:</h4>';
                echo '<p>L\'ordine con ID ' . $id_order . ' è stato aggiornato con i dati della spedizione.</p>';
            }
        }
    } catch (Exception $e) {
        // Gestione delle eccezioni
        echo '<h4 class="error">Eccezione:</h4>';
        echo '<pre class="error">' . $e->getMessage() . '</pre>';
    }

    echo '</div></div>';
}

// Chiusura della pagina HTML
echo '
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
';
