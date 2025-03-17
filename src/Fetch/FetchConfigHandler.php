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

if (!defined('_PS_VERSION_')) {
    exit;
}

class FetchConfigHandler
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

    public function updateEventi($params)
    {
        $eventi = $params['eventi'];
        foreach ($eventi as $evento) {
            $model = new \ModelBrtEvento($evento['id']);
            $model->hydrate($evento);

            try {
                $model->save();
            } catch (\Throwable $th) {
                $this->ajaxRender([
                    'success' => false,
                    'message' => $th->getMessage(),
                ]);
            }
        }

        $this->ajaxRender([
            'success' => true,
            'message' => 'Eventi aggiornati con successo',
        ]);
    }

    public function insertEventiSQL($params)
    {
    }

    public function insertEventiSOAP($params)
    {
    }

    protected function getEmailTemplate($params)
    {
        $template = $params['template'] ?? '';

        if (!$template) {
            return [
                'success' => false,
                'message' => 'Template non specificato',
            ];
        }

        if (!preg_match('/\.html$/', $template)) {
            $template .= '.html';
        }
        $path = _PS_MODULE_DIR_ . 'mpbrtinfo/mails/it/' . $template;

        if (!file_exists($path)) {
            return [
                'success' => false,
                'message' => 'Template non trovato',
            ];
        }

        $content = file_get_contents($path);

        return [
            'success' => true,
            'content' => $content,
        ];
    }

    protected function saveEmailTemplate($params)
    {
        $template = $params['template'];
        $content = $params['content'];

        if (!$template || !$content) {
            return [
                'success' => false,
                'message' => 'Parametri mancanti',
            ];
        }

        $path = _PS_MODULE_DIR_ . 'mpbrtinfo/mails/it/' . $template;

        // Verifica che il file esista prima di sovrascriverlo
        if (!file_exists($path)) {
            return [
                'success' => false,
                'message' => 'Template non trovato',
            ];
        }

        // Crea una copia di backup
        $backupPath = $path . '.bak.' . date('YmdHis');
        copy($path, $backupPath);

        // Salva il nuovo contenuto
        if (file_put_contents($path, $content) !== false) {
            return [
                'success' => true,
                'message' => 'Template salvato con successo',
            ];
        } else {
            return [
                'success' => false,
                'message' => 'Errore durante il salvataggio del template',
            ];
        }
    }

    public function getEmailTemplate2($template)
    {
        $path = _PS_MODULE_DIR_ . 'mpbrtinfo/mails/it/' . $template;

        if (!file_exists($path)) {
            return [
                'success' => false,
                'message' => 'Template non trovato',
            ];
        }

        $content = file_get_contents($path);

        return [
            'success' => true,
            'content' => $content,
        ];
    }

    protected function getOrderStates()
    {
        $id_lang = (int) \Context::getContext()->language->id;
        $orderStates = \OrderState::getOrderStates($id_lang);

        return ['options' => $orderStates];
    }

    public static function getOptionsEmail()
    {
        return (new self)->getEmails();
    }

    protected function getEmails()
    {
        $module = \Module::getInstanceByName('mpbrtinfo');
        $path = $module->getLocalPath() . 'mails/it/';

        $files = scandir($path);
        $emails = [];
        foreach ($files as $file) {
            if (pathinfo($file, PATHINFO_EXTENSION) === 'html') {
                $extension = '.' . pathinfo($file, PATHINFO_EXTENSION);
                $filename = pathinfo($file, PATHINFO_BASENAME);
                $name = str_replace($extension, '', $filename);
                $emails[] = [
                    'value' => $filename,
                    'text' => $name,
                ];
            }
        }

        return ['options' => $emails];
    }

    protected function updateOrderState($params)
    {
        $id_evento = $params['id_evento'];
        $id_order_state = $params['id_order_state'];
        $result = false;
        $message = "Errore durante l'aggiornamento del stato";
        $state = "<span class='text-danger'>Errore</span>";

        $model = new \ModelBrtEvento($id_evento);
        if (\Validate::isLoadedObject($model)) {
            $model->id_order_state = $id_order_state;

            try {
                $result = $model->save();
                if (!$result) {
                    $message = \Db::getInstance()->getMsgError();
                } else {
                    $message = 'Stato aggiornato con successo';
                    $os = new \OrderState($id_order_state, (int) \Context::getContext()->language->id);
                    $state = "<span class='badge badge-default' style='border-color: {$os->color};'>{$os->name}</span>";
                }
            } catch (\Throwable $th) {
                $result = false;
                $message = $th->getMessage();
            }
        }

        $result = [
            'success' => $result,
            'message' => $message,
            'state' => $state,
        ];

        return $result;
    }

    protected function updateEmail($params)
    {
        $id_evento = $params['id_evento'];
        $email = $params['email'];

        $model = new \ModelBrtEvento($id_evento);
        if (\Validate::isLoadedObject($model)) {
            $model->email = $email;

            try {
                $result = $model->save();
                if (!$result) {
                    $message = \Db::getInstance()->getMsgError();
                } else {
                    $message = 'Stato aggiornato con successo';
                    $state = "<span class='badge badge-default'>{$email}</span>";
                }
            } catch (\Throwable $th) {
                $result = false;
                $message = $th->getMessage();
            }
        }

        $result = [
            'success' => $result,
            'message' => $message,
            'state' => $state,
        ];

        return $result;
    }

    protected function updateStatus($params)
    {
        $id_evento = $params['id_evento'];
        $field = $params['field'];
        $value = (int) $params['value'];
        $result = false;
        $message = '';

        $evento = new \ModelBrtEvento($id_evento);
        if (\Validate::isLoadedObject($evento)) {
            $evento->$field = !$value;

            try {
                $result = $evento->save();
                if (!$result) {
                    $message = \Db::getInstance()->getMsgError();
                } else {
                    $message = 'Stato aggiornato con successo';
                }
            } catch (\Throwable $th) {
                $result = false;
                $message = $th->getMessage();
            }
        }

        $result = [
            'success' => $result,
            'message' => $message,
            'icon' => !$value ? 'check_circle' : 'close',
            'color' => !$value ? 'text-success' : 'text-danger',
        ];

        return $result;
    }
}
