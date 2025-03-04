<?php
/**
 * Script di test per la classe GetIdSpedizioneByIdCollo
 * 
 * Questo script permette di testare la funzionalità di ricerca dell'ID spedizione BRT
 * tramite ID collo utilizzando la classe GetIdSpedizioneByIdCollo.
 * 
 * Utilizzo: 
 * - Da browser: /modules/mpbrtinfo/tests/test_get_id_spedizione_by_id_collo.php?collo_id=ID_COLLO&cliente_id=ID_CLIENTE
 * - Da CLI: php test_get_id_spedizione_by_id_collo.php ID_COLLO ID_CLIENTE
 */

// Inizializzazione dell'ambiente PrestaShop
if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', dirname(__FILE__) . '/../../../');
}

// Includi i file di configurazione di PrestaShop
$config_file = _PS_ROOT_DIR_ . '/config/config.inc.php';
if (file_exists($config_file)) {
    include_once $config_file;
    include_once _PS_ROOT_DIR_ . '/init.php';

    // Carica l'autoloader del modulo se esiste
    $autoload_file = _PS_MODULE_DIR_ . 'mpbrtinfo/models/autoload.php';
    if (file_exists($autoload_file)) {
        require_once $autoload_file;
    }
} else {
    // Ambiente di test standalone
    // Definisci _PS_VERSION_ se non è già definito (per i test standalone)
    if (!defined('_PS_VERSION_')) {
        define('_PS_VERSION_', '1.7.8.0');
    }

    // Includi manualmente le classi necessarie
    require_once dirname(__FILE__) . '/../src/Soap/BrtSoapClient.php';
    require_once dirname(__FILE__) . '/../src/Soap/GetIdSpedizioneByIdCollo.php';
    require_once dirname(__FILE__) . '/../src/Soap/TrackingByBRTshipmentID.php';

    // Verifica che la classe SoapClient esista
    if (!class_exists('SoapClient')) {
        die("<div class='alert alert-danger'>L'estensione SOAP di PHP non è installata o abilitata. Contattare l'amministratore del server.</div>");
    }

    // Definisci la classe Tools se non esiste
    if (!class_exists('Tools')) {
        class Tools
        {
            public static function getValue($key, $default_value = false)
            {
                return isset($_GET[$key]) ? $_GET[$key] : $default_value;
            }
        }
    }
}

// Importazione delle classi necessarie
use MpSoft\MpBrtInfo\Soap\GetIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\Soap\TrackingByBRTshipmentID;

// Funzione per visualizzare i risultati in modo leggibile
function displayResult($data, $isError = false)
{
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

// Recupera i parametri
$collo_id = '';
$cliente_id = '';

if (php_sapi_name() === 'cli') {
    // Se eseguito da CLI, prendi i parametri dalla riga di comando
    global $argv;
    if (isset($argv[1])) {
        $collo_id = $argv[1];
    }
    if (isset($argv[2])) {
        $cliente_id = $argv[2];
    }
} else {
    // Se eseguito da browser, prendi i parametri dall'URL
    $collo_id = Tools::getValue('collo_id', '');
    $cliente_id = Tools::getValue('cliente_id', '');
}

// Verifica che i parametri siano stati forniti
if (empty($collo_id) || empty($cliente_id)) {
    if (php_sapi_name() === 'cli') {
        echo "Utilizzo: php test_get_id_spedizione_by_id_collo.php ID_COLLO ID_CLIENTE\n";
    } else {
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test GetIdSpedizioneByIdCollo</title>
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
        <h1 class="mb-4">Test GetIdSpedizioneByIdCollo</h1>
        <div class="card">
            <div class="card-header">Inserisci i dati per il test</div>
            <div class="card-body">
                <form method="GET" action="">
                    <div class="form-group">
                        <label for="collo_id">ID Collo:</label>
                        <input type="text" class="form-control" id="collo_id" name="collo_id" required>
                        <small class="form-text text-muted">Inserisci l\'ID del collo BRT</small>
                    </div>
                    <div class="form-group">
                        <label for="cliente_id">ID Cliente BRT:</label>
                        <input type="text" class="form-control" id="cliente_id" name="cliente_id" required>
                        <small class="form-text text-muted">Inserisci l\'ID cliente BRT</small>
                    </div>
                    <button type="submit" class="btn btn-primary">Esegui Test</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>';
    }
    exit;
}

// Inizializza il client SOAP
try {
    $client = new GetIdSpedizioneByIdCollo();

    // Effettua la chiamata al servizio BRT
    $result = $client->getIdSpedizione($collo_id, $cliente_id);

    if ($result === false) {
        // In caso di errore, mostra i messaggi di errore
        displayResult($client->getErrors(), true);
    } else {
        // In caso di successo, mostra l'ID spedizione
        if (php_sapi_name() !== 'cli') {
            echo '<div class="container">';
            echo '<h1 class="mb-4">Risultato della ricerca</h1>';
            echo '<div class="card">';
            echo '<div class="card-header">ID Spedizione BRT</div>';
            echo '<div class="card-body">';
        }

        displayResult($result);

        // Se è stato trovato un ID spedizione, offri la possibilità di visualizzare i dettagli
        if (!empty($result['spedizione_id'])) {
            $spedizione_id = $result['spedizione_id'];

            if (php_sapi_name() === 'cli') {
                echo "\nVuoi visualizzare i dettagli della spedizione? (s/n): ";
                $handle = fopen('php://stdin', 'r');
                $line = trim(fgets($handle));
                if (strtolower($line) === 's') {
                    echo "\nRecupero dettagli spedizione $spedizione_id...\n";
                    $tracking_client = new TrackingByBRTshipmentID();
                    $tracking_result = $tracking_client->getTracking($spedizione_id);

                    if ($tracking_result === false) {
                        displayResult($tracking_client->getErrors(), true);
                    } else {
                        displayResult($tracking_result);
                    }
                }
            } else {
                echo '<div class="mt-4">';
                echo '<h4>Dettagli Spedizione</h4>';
                echo '<p>È possibile visualizzare i dettagli della spedizione utilizzando l\'ID trovato:</p>';
                echo '<a href="test_tracking.php?tracking_number=' . urlencode($spedizione_id) . '" class="btn btn-success">Visualizza Dettagli Spedizione</a>';
                echo '</div>';
            }
        }

        if (php_sapi_name() !== 'cli') {
            echo '</div></div></div>';

            // Aggiungi il form per un nuovo test
            echo '<div class="container mt-4">';
            echo '<div class="card">';
            echo '<div class="card-header">Esegui un nuovo test</div>';
            echo '<div class="card-body">';
            echo '<form method="GET" action="">';
            echo '<div class="form-group">';
            echo '<label for="collo_id">ID Collo:</label>';
            echo '<input type="text" class="form-control" id="collo_id" name="collo_id" value="' . htmlspecialchars($collo_id) . '" required>';
            echo '<small class="form-text text-muted">Inserisci l\'ID del collo BRT</small>';
            echo '</div>';
            echo '<div class="form-group">';
            echo '<label for="cliente_id">ID Cliente BRT:</label>';
            echo '<input type="text" class="form-control" id="cliente_id" name="cliente_id" value="' . htmlspecialchars($cliente_id) . '" required>';
            echo '<small class="form-text text-muted">Inserisci l\'ID cliente BRT</small>';
            echo '</div>';
            echo '<button type="submit" class="btn btn-primary">Esegui Test</button>';
            echo '</form>';
            echo '</div></div></div>';
        }
    }
} catch (Exception $e) {
    // Gestione delle eccezioni
    displayResult('Eccezione: ' . $e->getMessage(), true);
}

// Chiusura della pagina HTML se in modalità browser
if (php_sapi_name() !== 'cli') {
    echo '
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    </body></html>';
}
