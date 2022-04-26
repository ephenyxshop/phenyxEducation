<?php

/**
 * Class LanguesControllerCore
 *
 * @since 1.8.1.0
 */
class LanguesControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'langues';
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
    }

    public function initContent() {

        parent::initContent();

        $this->setTemplate(_PS_THEME_DIR_ . 'langues.tpl');
    }
}
