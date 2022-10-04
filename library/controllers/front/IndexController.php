<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class IndexControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var string $php_self */
	public $php_self = 'index';
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
		//$this->addCSS(_THEME_CSS_DIR_ . 'index.css');
		
		$this->addJS(_THEME_JS_DIR_ . 'index.js');

		if ($this->context->cookie->is_agent == 1) {
			$this->addCSS(_THEME_CSS_DIR_  . 'metnav2.css');
			$this->addCSS(_THEME_CSS_DIR_  . 'fonts.css');
			$this->addCSS(_THEME_CSS_DIR_  . 'bootstrap-table.min.css');
			$this->addCSS(_THEME_CSS_DIR_  . 'imageuploadify.css');
			$this->addCSS(_THEME_CSS_DIR_  . 'messenger.css');
			$this->addJS(_THEME_JS_DIR_ . 'jquery.metnav2.v1.2.js');
			$this->addJS(_THEME_JS_DIR_ . 'metro.js');
			$this->addJS(_THEME_JS_DIR_ . 'survey.js');
		} 

	}

	public function initContent() {

		$file = fopen("testIndexInitContent.txt","w");
		parent::initContent();
		
		$certifications = Certification::getCerificationCollection();

		$this->context->smarty->assign(
			[
				'HOOK_HOME'             => Hook::exec('displayHome'),
				'HOOK_HOME_TAB'         => Hook::exec('displayHomeTab'),
				'HOOK_HOME_TAB_CONTENT' => Hook::exec('displayHomeTabContent'),
				'homeVideo'             => Configuration::get('EPH_HOME_VIDEO_ACTIVE'),
				'videoLink'             => Configuration::get('EPH_HOME_VIDEO_LINK'),
				'homeParallax'             => Configuration::get('EPH_HOME_PARALLAX_ACTIVE'),
				'parallaxImage'         => Configuration::get('EPH_HOME_PARALLAX_FILE'),
				'certifications'		=> $certifications,
				'services'				=> Service::getServiceCollection()
			]
		);
		fwrite($file,_EPH_THEME_DIR_  . 'index.tpl'.PHP_EOL);
		$this->setTemplate(_EPH_THEME_DIR_ . 'index.tpl');

	}

}
