<?php
/**
 * Script di test per l'integrazione della classe GetIdSpedizioneByRMN con PrestaShop
 * 
 * Questo script permette di testare la funzionalità di ricerca dell'ID spedizione BRT
 * tramite riferimento mittente numerico all'interno dell'ambiente di amministrazione di PrestaShop.
 * 
 * Utilizzo: 
 * - Da browser: /modules/mpbrtinfo/tests/test_admin_rmn.php?riferimento=RIFERIMENTO&cliente_id=ID_CLIENTE
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
use MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID;

// Recupera i parametri
$riferimento = Tools::getValue('riferimento', '');
$cliente_id = Tools::getValue('cliente_id', '');

// Stile CSS per la pagina
echo '
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Ricerca ID Spedizione BRT</title>
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
        <h1 class="mb-4">Test Ricerca ID Spedizione BRT</h1>
';

// Form per inserire i dati di test
echo '
        <div class="card">
            <div class="card-header">Inserisci i dati per il test</div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="riferimento">Riferimento Mittente Numerico:</label>
                        <input type="text" class="form-control" id="riferimento" name="riferimento" value="' . htmlspecialchars($riferimento) . '" required>
                        <small class="form-text text-muted">Inserisci il riferimento numerico del mittente</small>
                    </div>
                    <div class="form-group">
                        <label for="cliente_id">ID Cliente BRT:</label>
                        <input type="text" class="form-control" id="cliente_id" name="cliente_id" value="' . htmlspecialchars($cliente_id) . '" required>
                        <small class="form-text text-muted">Inserisci l\'ID cliente BRT</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Esegui Ricerca</button>
                </form>
            </div>
        </div>
';

// Esegui il test se sono stati forniti i parametri
if (!empty($riferimento) && !empty($cliente_id)) {
    echo '<div class="card">';
    echo '<div class="card-header">Risultati della ricerca</div>';
    echo '<div class="card-body">';
    
    try {
        // Inizializza il client SOAP
        $client = new GetIdSpedizioneByRMN();
        
        // Effettua la chiamata al servizio BRT
        $result = $client->getIdSpedizione($riferimento, $cliente_id);
        
        if ($result === false) {
            // In caso di errore, mostra i messaggi di errore
            echo '<h4 class="error">Errori:</h4>';
            echo '<pre class="error">';
            print_r($client->getErrors());
            echo '</pre>';
        } else {
            // In caso di successo, mostra l'ID spedizione
            echo '<h4>ID Spedizione BRT:</h4>';
            echo '<pre>';
            print_r($result);
            echo '</pre>';
            
            // Se è stato trovato un ID spedizione, offri la possibilità di visualizzare i dettagli
            if (!empty($result['spedizione_id'])) {
                $spedizione_id = $result['spedizione_id'];
                
                echo '<div class="mt-4">';
                echo '<h4>Dettagli Spedizione</h4>';
                echo '<p>È possibile visualizzare i dettagli della spedizione utilizzando l\'ID trovato:</p>';
                echo '<a href="test_admin.php?tracking_number=' . urlencode($spedizione_id) . '" class="btn btn-success">Visualizza Dettagli Spedizione</a>';
                
                // Opzione per visualizzare i dettagli direttamente
                echo '<div class="mt-3">';
                echo '<h5>Dettagli Immediati</h5>';
                
                // Recupera i dettagli della spedizione
                $tracking_client = new TrackingByBRTshipmentID();
                $tracking_result = $tracking_client->getTracking($spedizione_id);
                
                if ($tracking_result === false) {
                    echo '<pre class="error">';
                    print_r($tracking_client->getErrors());
                    echo '</pre>';
                } else {
                    echo '<pre>';
                    print_r($tracking_result);
                    echo '</pre>';
                }
                
                echo '</div>';
                echo '</div>';
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
