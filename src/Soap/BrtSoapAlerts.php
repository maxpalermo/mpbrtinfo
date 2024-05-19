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

namespace MpSoft\MpBrtInfo\Soap;

if (!defined('_PS_VERSION_')) {
    exit;
}
class BrtSoapAlerts
{
    private static $instance;
    private $confirmations;
    private $warnings;
    private $errors;

    private function __construct()
    {
        // nothing
    }

    private function init()
    {
        $this->confirmations = [];
        $this->warnings = [];
        $this->errors = [];
        $this->getValues();
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new BrtSoapAlerts();
            self::$instance->init();
        }

        return self::$instance;
    }

    public function clearAll()
    {
        $this->deleteValues();
        $this->init();
    }

    public function clear($type)
    {
        switch ($type) {
            case 'confirmation':
                $this->confirmations[] = [];

                break;
            case 'warning':
                $this->warnings[] = [];

                break;
            case 'error':
                $this->errors[] = [];

                break;
            default:
                return false;
        }

        return true;
    }

    public function clearConfirmations()
    {
        return $this->clear('confirmation');
    }

    public function clearWarnings()
    {
        return $this->clear('warning');
    }

    public function clearErrors()
    {
        return $this->clear('error');
    }

    public function addConfirmationMessage($message)
    {
        return $this->addMessage($message, 'confirmation');
    }

    public function addWarningMessage($message)
    {
        return $this->addMessage($message, 'warning');
    }

    public function addErrorMessage($message)
    {
        return $this->addMessage($message, 'error');
    }

    public function addMessage($message, $type)
    {
        switch ($type) {
            case 'confirmation':
                $this->confirmations[] = $message;

                break;
            case 'warning':
                $this->warnings[] = $message;

                break;
            case 'error':
                $this->errors[] = $message;

                break;
            default:
                return false;
        }

        $this->updateValues();

        return true;
    }

    private function updateValues()
    {
        $values = json_encode(
            [
                'confirmations' => $this->confirmations,
                'warnings' => $this->warnings,
                'errors' => $this->errors,
            ]
        );
        $values = str_replace(['<', '>'], ['_#_', '_##_'], $values);
        \Configuration::updateValue(
            'MPBRTINFO_MESSAGES',
            $values
        );
    }

    private function getValues()
    {
        $values = \Configuration::get('MPBRTINFO_MESSAGES');
        if ($values) {
            $values = str_replace(['_#_', '_##_'], ['<', '>'], $values);
            $values = json_decode($values, true);
            if (isset($values['confirmations'])) {
                $this->confirmations = $values['confirmations'];
            }
            if (isset($values['warnings'])) {
                $this->warnings = $values['warnings'];
            }
            if (isset($values['errors'])) {
                $this->errors = $values['errors'];
            }
        }
    }

    private function deleteValues()
    {
        $this->confirmations = [];
        $this->warnings = [];
        $this->errors = [];
        $this->updateValues();
    }

    public function displayMessages($controller)
    {
        if ($this->confirmations) {
            $controller->confirmations = $this->getConfirmationMessages();
        }
        if ($this->warnings) {
            $controller->warnings = $this->getWarningMessages();
        }
        if ($this->errors) {
            $controller->errors = $this->getErrorMessages();
        }
    }

    public function getConfirmationMessages()
    {
        return $this->getMessages('confirmation');
    }

    public function getWarningMessages()
    {
        return $this->getMessages('warning');
    }

    public function getErrorMessages()
    {
        return $this->getMessages('error');
    }

    public function getMessages($type)
    {
        switch ($type) {
            case 'confirmation':
                return $this->confirmations;
            case 'warning':
                return $this->warnings;
            case 'error':
                return $this->errors;
            default:
                return false;
        }
    }
}
