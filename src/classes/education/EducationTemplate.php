<?php

/**
 * @since 1.5
 */
class EducationTemplateCore extends TCPDF {

    public $pieceHeader;
    public $logoHeader;
    public $pieceContent;
    public $pieceFooter;

    public function __construct($orientation = 'P', $unit = 'mm', $format = 'A4', $useCache = false) {

        parent::__construct($orientation, $unit, $format, true, 'UTF-8', $useCache);
        $this->setRTL(Context::getContext()->language->is_rtl);
    }

    public function Header() {

        $img_file = _PS_IMG_DIR_ . '/pdf/upperBackGround.png';
        $this->Image($img_file, 80, 0, 130, 90, 'PNG', '', '', false, 300, '', false, false, 0);
        $this->Image($this->getLogo(), 10, 10, 60, 20, '', '', '', false, 300, '', false, false, 0);
        $this->writeHTML($this->pieceHeader, false, true, false, true);
    }

    public function Footer() {

        $this->SetY(-25);
        // Set font
        $this->SetFont('helvetica', 'I', 8);

        $this->writeHTML($this->pieceFooter, false, true, false, true);
    }

    protected function getLogo() {

        $logo = '';

        $idShop = (int) $this->shop->id;

        if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
            $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
        } else if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
            $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
        }

        return $logo;
    }

    protected function getTemplate($templateName) {

        $template = false;
        $defaultTemplate = _PS_PDF_TEMPLATE_DIR_ . $templateName . '.tpl';

        if (file_exists($defaultTemplate)) {
            $template = $defaultTemplate;
        }

        return $template;
    }

}
