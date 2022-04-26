<?php

/**
 * Class MyAccountControllerCore
 *
 * @since 1.8.1.0
 */
class MyAccountControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var bool $auth */
    public $auth = true;
    /** @var string $php_self */
    public $php_self = 'my-account';
    /** @var string $authRedirection */
    public $authRedirection = 'my-account';
    /** @var bool $ssl */
    public $ssl = true;
    // @codingStandardsIgnoreEnd

    /**
     * Set media
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'my-account.css');
    }

    /**
     * Assign template vars related to page content
     *
     * @see   FrontController::initContent()
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        parent::initContent();

        $hasEducation = $this->context->student->hasEducation();

        $this->context->smarty->assign(
            [
                'hasEducation' => $hasEducation,
            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'my-account.tpl');
    }
}
