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

namespace MpSoft\MpBrtInfo\Bolla;

if (!defined('_PS_VERSION_')) {
    exit;
}
class Contrassegno
{
    protected $contrassegno_divisa;
    protected $contrassegno_importo;
    protected $contrassegno_incasso;
    protected $contrassegno_particolarita;

    public function __construct($contrassegno)
    {
        $this->contrassegno_divisa = $contrassegno['CONTRASSEGNO_DIVISA'];
        $this->contrassegno_importo = $contrassegno['CONTRASSEGNO_IMPORTO'];
        $this->contrassegno_incasso = $contrassegno['CONTRASSEGNO_INCASSO'];
        $this->contrassegno_particolarita = $contrassegno['CONTRASSEGNO_PARTICOLARITA'];
    }

    public function getContrassegnoDivisa()
    {
        return $this->contrassegno_divisa;
    }

    public function setContrassegnoDivisa($divisa)
    {
        $this->contrassegno_divisa = $divisa;
    }

    public function getContrassegnoImporto()
    {
        return $this->contrassegno_importo;
    }

    public function setContrassegnoImporto($importo)
    {
        $this->contrassegno_importo = $importo;
    }

    public function getContrassegnoIncasso()
    {
        return $this->contrassegno_incasso;
    }

    public function setContrassegnoIncasso($incasso)
    {
        $this->contrassegno_incasso = $incasso;
    }

    public function getContrassegnoParticolarita()
    {
        return $this->contrassegno_particolarita;
    }

    public function setContrassegnoParticolarita($particolarita)
    {
        $this->contrassegno_particolarita = $particolarita;
    }
}
