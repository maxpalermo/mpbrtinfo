<?php

namespace MpSoft\MpBrtInfo\Mail;

class Mailer
{
    /**
     * Costruttore
     */
    public function __construct()
    {
        // Costruttore semplificato senza riferimenti a MailHog
    }

    /**
     * Invia una email di aggiornamento tracking
     *
     * @param int $id_order ID dell'ordine
     * @param array $trackingData Dati di tracking
     *
     * @return bool Esito dell'invio
     */
    public function sendEmail($template, $id_order, $trackingData)
    {
        // Ottieni i dati dell'ordine
        $order = new \Order($id_order);
        $customer = new \Customer($order->id_customer);

        // Se il template ha un suffisso lo elimino
        $template = str_replace('.html', '', $template);

        if (!\Validate::isLoadedObject($order) || !\Validate::isLoadedObject($customer)) {
            return false;
        }

        // Prepara i dati per il template
        $templateVars = [
            '{firstname}' => $customer->firstname,
            '{lastname}' => $customer->lastname,
            '{id_order}' => $order->id,
            '{order_reference}' => $order->reference,
            '{tracking_number}' => $trackingData['tracking_number'] ?? '',
            '{last_update}' => $trackingData['last_update'] ?? '',
            '{reason}' => $trackingData['reason'] ?? '',
            '{tracking_url}' => $this->getTrackingUrl($order->id_carrier, $trackingData['tracking_number']),
        ];

        // Aggiungi variabili di shop standard
        $templateVars = array_merge($templateVars, $this->getStandardTemplateVars());

        // Prepara i parametri per l'hook
        $emailParams = [
            'id_order' => $id_order,
            'tracking_data' => $trackingData,
            'template_vars' => $templateVars,
            'customer' => $customer,
            'order' => $order,
        ];

        // Permetti ad altri moduli di modificare i parametri
        $hookResult = \Hook::exec('actionBeforeSendTrackingEmail', $emailParams, null, true);

        // Se l'hook ha restituito risultati, aggiorna i parametri
        if (is_array($hookResult) && !empty($hookResult)) {
            // Estrai i risultati dal primo modulo che ha risposto
            $hookData = reset($hookResult);

            // Aggiorna i parametri se sono stati modificati dall'hook
            if (isset($hookData['template_vars']) && is_array($hookData['template_vars'])) {
                $templateVars = $hookData['template_vars'];
            }

            // Controlla se l'hook ha deciso di annullare l'invio dell'email
            if (isset($hookData['cancel_email']) && $hookData['cancel_email'] === true) {
                return true; // Fingi che l'email sia stata inviata con successo
            }
        }

        // Invia l'email
        $mailDir = _PS_MODULE_DIR_ . 'mpbrtinfo/mails/';
        $event = \ModelBrtEvento::getEvento($trackingData['id_event']);
        if ($event && $event->isDelivered()) {
            $subject = sprintf('Ordine #%s consegnato', $order->id);
        } elseif ($event && $event->isShipped()) {
            $subject = sprintf('Avviso ordine #%s partito', $order->id);
        } else {
            $subject = sprintf('Aggiornamento spedizione ordine #%s', $order->id);
        }

        // Permetti all'hook di modificare anche l'oggetto dell'email
        if (isset($hookData['subject']) && !empty($hookData['subject'])) {
            $subject = $hookData['subject'];
        }

        // Nessuna modifica all'oggetto dell'email

        $result = \Mail::send(
            (int) $order->id_lang,
            $template, // nome del template selezionato in base allo stato
            $subject,
            $templateVars,
            $customer->email,
            $customer->firstname . ' ' . $customer->lastname,
            null, // from email (usa quello predefinito)
            null, // from name (usa quello predefinito)
            null, // file attachment
            null, // mode smtp
            $mailDir // percorso personalizzato ai template
        );

        // Log dell'invio email in modalità sviluppo
        if (_PS_MODE_DEV_) {
            \Tools::error_log('Email inviata a ' . $customer->email . '. Controlla il server SMTP per i dettagli.');
        }

        return $result;
    }

    /**
     * Ottiene l'URL di tracking BRT
     *
     * @param string $trackingNumber Numero di tracking
     *
     * @return string URL di tracking
     */
    private function getTrackingUrl($id_carrier, $trackingNumber)
    {
        $carrier = new \Carrier($id_carrier);
        if (!\Validate::isLoadedObject($carrier)) {
            return 'https://vas.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&ParNum=' . urlencode($trackingNumber);
        }

        $url = $carrier->url;
        if (empty($url)) {
            return 'https://vas.brt.it/vas/sped_det_show.hsm?referer=sped_numspe_par.htm&ParNum=' . urlencode($trackingNumber);
        }

        return str_replace('@', $trackingNumber, $url);
    }

    /**
     * Ottiene le variabili standard per i template email
     *
     * @return array Variabili standard
     */
    private function getStandardTemplateVars()
    {
        $context = \Context::getContext();
        $shop_name = \Configuration::get('PS_SHOP_NAME');
        $shop_url = $context->link->getBaseLink();
        $shop_logo = $context->link->getMediaLink(_PS_IMG_ . \Configuration::get('PS_LOGO'));

        return [
            '{shop_name}' => $shop_name,
            '{shop_url}' => $shop_url,
            '{shop_logo}' => $shop_logo,
        ];
    }
}
