<?php
use MpSoft\MpBrtInfo\Brt\BrtGetSoapTracking;

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
if (!defined('_PS_VERSION_')) {
    exit;
}

use MpSoft\MpBrtInfo\Soap\BrtSoapClientEsiti;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientEventi;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByIdCollo;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMA;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientIdSpedizioneByRMN;
use MpSoft\MpBrtInfo\Soap\BrtSoapClientTrackingByShipmentId;

class MpBrtInfoCronJobsModuleFrontController extends ModuleFrontController
{
    /** @var string The name of the controller */
    public $name;

    public function response($params)
    {
        header('Content-Type: application/json');
        exit(json_encode($params));
    }

    public function __construct()
    {
        $this->name = 'CronJobs';
        $this->ajax = true;
        $this->auth = false;
        $this->guestAllowed = false;
        $this->ssl = (int) Configuration::get('PS_SSL_ENABLED');

        parent::__construct();

        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);
        if (isset($sessionJSON['action']) && isset($sessionJSON['ajax'])) {
            $action = 'displayAjax' . ucfirst($sessionJSON['action']);
            if (method_exists($this, $action)) {
                $this->$action();
                exit;
            }
        }
    }

    public function display()
    {
        if (Tools::isSubmit('action')) {
            $action = 'displayAjax' . Tools::ucfirst(Tools::getValue('action'));
            if (method_exists($this, $action)) {
                $this->$action();
                exit;
            }
        }

        $this->response('ACCESS DENIED');
    }

    public function displayAjax()
    {
        $this->ajaxRender('NO METHOD FOUND');
    }

    public function displayAjaxGetShippingInfo()
    {
        $soapTracking = new BrtGetSoapTracking($this->module);
        $trackings = $soapTracking->get();

        Tools::dieObject($trackings, false);
    }

    protected function getLegendaEsiti()
    {
        $class = new BrtSoapClientEsiti();
        $esiti = $class->getSoapLegendaEsiti();

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetLegendaEsiti()
    {
        $esiti = $this->getLegendaEsiti();
        if (isset($esiti['error'])) {
            $this->response($esiti);
        }
        $this->response(['esiti' => $esiti]);
    }

    public function getLegendaEventi()
    {
        $class = new BrtSoapClientEventi();
        $esiti = $class->getSoapLegendaEventi();

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetLegendaEventi()
    {
        $esiti = $this->getLegendaEventi();
        if (isset($esiti['error'])) {
            $this->response($esiti);
        }
        $this->response(['eventi' => $esiti]);
    }

    public function getIdSpedizioneByRMN($brt_customer_id, $rmn)
    {
        $class = new BrtSoapClientIdSpedizioneByRMN();
        $esiti = $class->getSoapIdSpedizioneByRMN($brt_customer_id, $rmn);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByRMN()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $rmn = $sessionJSON['brt_rmn'];
            $esiti = $this->getIdSpedizioneByRMN($brt_customer_id, $rmn);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function getIdSpedizioneByRMA($brt_customer_id, $rma)
    {
        $class = new BrtSoapClientIdSpedizioneByRMA();
        $esiti = $class->getSoapIdSpedizioneByRMA($brt_customer_id, $rma);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByRMA()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $rma = $sessionJSON['brt_rma'];
            $esiti = $this->getIdSpedizioneByRMA($brt_customer_id, $rma);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function getIdSpedizioneByIdCollo($brt_customer_id, $collo_id)
    {
        $class = new BrtSoapClientIdSpedizioneByIdCollo();
        $esiti = $class->getSoapIdSpedizioneByIdCollo($brt_customer_id, $collo_id);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxGetIdSpedizioneByIdCollo()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $brt_customer_id = $sessionJSON['brt_customer_id'];
            $collo_id = $sessionJSON['collo_id'];
            $esiti = $this->getIdSpedizioneByIdCollo($brt_customer_id, $collo_id);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function TrackingInfoByIdCollo($lang_iso, $spedizione_anno, $spedizione_id)
    {
        $class = new BrtSoapClientTrackingByShipmentId();
        $esiti = $class->getSoapTrackingByShipmentId($lang_iso, $spedizione_anno, $spedizione_id);

        if ($esiti === false) {
            return ['error' => $class->getErrors()];
        }

        return $esiti;
    }

    public function displayAjaxTrackingInfoByIdCollo()
    {
        $post_json = file_get_contents('php://input');
        $sessionJSON = json_decode($post_json, true);

        try {
            $spedizione_anno = $sessionJSON['spedizione_anno'];
            $spedizione_id = $sessionJSON['spedizione_id'];
            $lang_iso = $sessionJSON['lang_iso'];
            $esiti = $this->TrackingInfoByIdCollo($lang_iso, $spedizione_anno, $spedizione_id);
            if (isset($esiti['error'])) {
                $this->response($esiti);
            }
            $this->response(['response' => $esiti]);
        } catch (Exception $e) {
            $this->response(['error' => $e->getMessage()]);
        }
    }

    public function displayAjaxUpdateEventi()
    {
        $eventi = $this->getLegendaEventi();
        $exists = 'SELECT id_evento FROM ' . _DB_PREFIX_ . 'mp_brtinfo_evento ORDER BY id_evento ASC';
        $db = Db::getInstance();
        $results = $db->executeS($exists);
        $updated = [];
        $errors = [];

        if ($results) {
            $results = array_column($results, 'id_evento');
        }
        foreach ($eventi as $evento) {
            if (!in_array($evento['ID'], $results)) {
                $insert = 'INSERT IGNORE INTO ' . _DB_PREFIX_ . "mp_brtinfo_evento (id_evento, name) VALUES ('" . $evento['ID'] . "', '" . pSQL($evento['DESCRIZIONE']) . "');";

                try {
                    $res = $db->execute($insert);
                    if ($res) {
                        $updated[] = $evento;
                    } else {
                        $errors[] = ['ID' => $evento['ID'], 'DESCRIZIONE' => $evento['DESCRIZIONE'], 'error' => $db->getMsgError()];
                    }
                } catch (\Throwable $th) {
                    $this->response(['error' => $th->getMessage()]);
                }
            }
        }

        $this->response(['updated' => $updated, 'errors' => $errors]);
    }
}
