<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class FinancementControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'financement';
    // @codingStandardsIgnoreEnd

    /**
     * Initialize content
     *
     * @return void
     *
     * @since 1.8.1.0
     */

    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'financement.css');
    }

    public function initContent() {

        parent::initContent();

        $this->setTemplate(_PS_THEME_DIR_ . 'financement.tpl');
    }
}
