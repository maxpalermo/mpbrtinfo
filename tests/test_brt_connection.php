<?php
/**
 * Script di test per verificare la connessione con i server BRT
 * 
 * Questo script permette di testare la connessione con i vari endpoint SOAP di BRT
 * e di diagnosticare eventuali problemi di connessione.
 * 
 * Utilizzo: 
 * - Da browser: /modules/mpbrtinfo/tests/test_brt_connection.php
 * - Da CLI: php test_brt_connection.php
 */

// Inizializzazione dell'ambiente PrestaShop
if (!defined('_PS_ROOT_DIR_')) {
    define('_PS_ROOT_DIR_', dirname(__FILE__) . '/../../../');
}

// Abilita la modalità sviluppo per ottenere più informazioni di debug
if (!defined('_PS_MODE_DEV_')) {
    define('_PS_MODE_DEV_', true);
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
    require_once dirname(__FILE__) . '/../src/Soap/GetIdSpedizioneByRMN.php';
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

// Definisci la classe per testare la connessione
class BrtConnectionTester
{
    private $endpoints = [];
    private $results = [];
    private $verify_ssl;

    public function __construct($verify_ssl = false)
    {
        $this->verify_ssl = $verify_ssl;

        // Definisci gli endpoint da testare
        $this->endpoints = [
            'TrackingByBRTshipmentID (HTTP)' => 'http://wsr.brt.it:10041/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl',
            'TrackingByBRTshipmentID (HTTPS)' => 'https://wsr.brt.it:10052/web/BRT_TrackingByBRTshipmentIDService/BRT_TrackingByBRTshipmentID?wsdl',
            'GetIdSpedizioneByRMN (HTTP)' => 'http://wsr.brt.it:10041/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl',
            'GetIdSpedizioneByRMN (HTTPS)' => 'https://wsr.brt.it:10052/web/GetIdSpedizioneByRMNService/GetIdSpedizioneByRMN?wsdl',
            'GetIdSpedizioneByIdCollo (HTTP)' => 'http://wsr.brt.it:10041/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl',
            'GetIdSpedizioneByIdCollo (HTTPS)' => 'https://wsr.brt.it:10052/web/GetIdSpedizioneByIdColloService/GetIdSpedizioneByIdCollo?wsdl',
        ];
    }

    public function testConnections()
    {
        foreach ($this->endpoints as $name => $endpoint) {
            $this->results[$name] = $this->testEndpoint($endpoint);
        }

        return $this->results;
    }

    private function testEndpoint($endpoint)
    {
        $result = [
            'endpoint' => $endpoint,
            'success' => false,
            'message' => '',
            'details' => '',
            'curl_info' => [],
        ];

        try {
            // Test con cURL
            $curl_result = $this->testWithCurl($endpoint);
            $result['curl_info'] = $curl_result;

            if ($curl_result['http_code'] >= 200 && $curl_result['http_code'] < 300) {
                $result['success'] = true;
                $result['message'] = 'Connessione riuscita (HTTP ' . $curl_result['http_code'] . ')';
            } else {
                $result['message'] = 'Errore di connessione: HTTP ' . $curl_result['http_code'];
            }

            // Test con SoapClient
            if ($result['success']) {
                $soap_result = $this->testWithSoapClient($endpoint);
                if ($soap_result['success']) {
                    $result['message'] .= ' - SoapClient: OK';
                } else {
                    $result['success'] = false;
                    $result['message'] .= ' - SoapClient: ' . $soap_result['message'];
                    $result['details'] = $soap_result['details'];
                }
            }
        } catch (Exception $e) {
            $result['success'] = false;
            $result['message'] = 'Eccezione: ' . $e->getMessage();
            $result['details'] = $e->getTraceAsString();
        }

        return $result;
    }

    private function testWithCurl($endpoint)
    {
        $ch = curl_init($endpoint);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_NOBODY, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        
        // Gestione SSL
        if (!$this->verify_ssl) {
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        }
        
        // Esegui la richiesta
        $response = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);
        
        // Aggiungi l'errore alle informazioni
        $info['error'] = $error;
        $info['response_head'] = substr($response, 0, 500) . (strlen($response) > 500 ? '...' : '');
        
        curl_close($ch);
        
        return $info;
    }

    private function testWithSoapClient($endpoint)
    {
        $result = [
            'success' => false,
            'message' => '',
            'details' => '',
        ];

        try {
            // Crea il contesto per la connessione SSL
            $context_options = [
                'ssl' => [
                    'verify_peer' => $this->verify_ssl,
                    'verify_peer_name' => $this->verify_ssl,
                    'allow_self_signed' => !$this->verify_ssl,
                ],
            ];
            
            $context = stream_context_create($context_options);
            
            // Opzioni per il client SOAP
            $options = [
                'trace' => true,
                'exceptions' => true,
                'connection_timeout' => 30,
                'stream_context' => $context,
                'cache_wsdl' => WSDL_CACHE_NONE,
            ];
            
            // Crea il client SOAP
            $client = new SoapClient($endpoint, $options);
            
            // Se arriviamo qui, la connessione è riuscita
            $result['success'] = true;
            $result['message'] = 'OK';
            $result['details'] = 'Client SOAP creato con successo';
            
            // Ottieni le funzioni disponibili
            $functions = $client->__getFunctions();
            $result['details'] .= "\nFunzioni disponibili: " . count($functions);
            if (count($functions) > 0) {
                $result['details'] .= "\n" . implode("\n", array_slice($functions, 0, 5));
                if (count($functions) > 5) {
                    $result['details'] .= "\n...e altre " . (count($functions) - 5) . " funzioni";
                }
            }
        } catch (SoapFault $e) {
            $result['message'] = 'Errore SOAP: ' . $e->getMessage();
            $result['details'] = $e->getTraceAsString();
        } catch (Exception $e) {
            $result['message'] = 'Eccezione: ' . $e->getMessage();
            $result['details'] = $e->getTraceAsString();
        }

        return $result;
    }
}

// Funzione per visualizzare i risultati in modo leggibile
function displayResults($results)
{
    if (php_sapi_name() === 'cli') {
        // Output per CLI
        foreach ($results as $name => $result) {
            echo "=== $name ===\n";
            echo "Endpoint: " . $result['endpoint'] . "\n";
            echo "Stato: " . ($result['success'] ? "\033[32mOK\033[0m" : "\033[31mERRORE\033[0m") . "\n";
            echo "Messaggio: " . $result['message'] . "\n";
            
            if (!empty($result['details'])) {
                echo "Dettagli: " . $result['details'] . "\n";
            }
            
            echo "Informazioni cURL:\n";
            foreach ($result['curl_info'] as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }
                echo "  $key: $value\n";
            }
            
            echo "\n";
        }
    } else {
        // Output per browser
        echo '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Connessione BRT</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body { padding: 20px; }
        .card { margin-bottom: 20px; }
        pre { background-color: #f8f9fa; padding: 15px; border-radius: 5px; max-height: 300px; overflow: auto; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <div class="container">
        <h1 class="mb-4">Test Connessione Server BRT</h1>
        <div class="alert alert-info">
            <p><strong>Informazioni sul test:</strong></p>
            <p>Questo test verifica la connessione con i vari endpoint SOAP di BRT utilizzando sia cURL che SoapClient.</p>
            <p>La verifica SSL è ' . ($verify_ssl ? 'attiva' : 'disattivata') . '.</p>
        </div>';
        
        foreach ($results as $name => $result) {
            echo '<div class="card">';
            echo '<div class="card-header">' . htmlspecialchars($name) . '</div>';
            echo '<div class="card-body">';
            
            echo '<p><strong>Endpoint:</strong> ' . htmlspecialchars($result['endpoint']) . '</p>';
            echo '<p><strong>Stato:</strong> <span class="' . ($result['success'] ? 'success' : 'error') . '">' . 
                ($result['success'] ? 'OK' : 'ERRORE') . '</span></p>';
            echo '<p><strong>Messaggio:</strong> ' . htmlspecialchars($result['message']) . '</p>';
            
            if (!empty($result['details'])) {
                echo '<p><strong>Dettagli:</strong></p>';
                echo '<pre>' . htmlspecialchars($result['details']) . '</pre>';
            }
            
            echo '<p><strong>Informazioni cURL:</strong></p>';
            echo '<div class="table-responsive">';
            echo '<table class="table table-sm table-bordered">';
            echo '<thead><tr><th>Parametro</th><th>Valore</th></tr></thead>';
            echo '<tbody>';
            
            foreach ($result['curl_info'] as $key => $value) {
                if (is_array($value) || is_object($value)) {
                    continue;
                }
                echo '<tr>';
                echo '<td>' . htmlspecialchars($key) . '</td>';
                echo '<td>' . htmlspecialchars($value) . '</td>';
                echo '</tr>';
            }
            
            echo '</tbody></table>';
            echo '</div>';
            
            echo '</div></div>';
        }
        
        echo '
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.1/dist/umd/popper.min.js"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>';
    }
}

// Parametri per il test
$verify_ssl = Tools::getValue('verify_ssl', '0') === '1';

// Esegui il test
$tester = new BrtConnectionTester($verify_ssl);
$results = $tester->testConnections();

// Visualizza i risultati
displayResults($results);
