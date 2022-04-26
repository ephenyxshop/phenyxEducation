<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class FaqControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'faq';
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
        $this->addJS(_THEME_JS_DIR_ . 'faq.js');
    }

    public function initContent() {

        parent::initContent();

        $this->setTemplate(_PS_THEME_DIR_ . 'faq.tpl');
    }
}
