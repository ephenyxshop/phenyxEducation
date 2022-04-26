<?php

/**
 * Class TutoControllerCore
 *
 * @since 1.8.1.0
 */
class TutorielControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'tutoriel';
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
		
		$this->context->smarty->assign(
            [

               'tutoLink' => Configuration::get('EPH_TUTO_VIDEO_LINK')

            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'tutoriel.tpl');
    }
}
