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
		$this->addCSS(_THEME_CSS_DIR_ . 'index.css');
		$this->addJS(_THEME_JS_DIR_ . 'jarallax.min.js');
		$this->addJS(_THEME_JS_DIR_ . 'jarallax-video.min.js');
		$this->addJS(_THEME_JS_DIR_ . 'index.js');

		if ($this->context->cookie->is_agent == 1) {
			$this->addCSS(_AGENT_CSS_DIR_ . 'metnav2.css');
			$this->addCSS(_AGENT_CSS_DIR_ . 'fonts.css');
			$this->addCSS(_AGENT_CSS_DIR_ . 'bootstrap-table.min.css');
			$this->addCSS(_AGENT_CSS_DIR_ . 'imageuploadify.css');
			$this->addCSS(_AGENT_CSS_DIR_ . 'messenger.css');
			$this->addJS(_AGENT_JS_DIR_ . 'jquery.metnav2.v1.2.js');
			$this->addJS(_AGENT_JS_DIR_ . 'metro.js');
			$this->addJS(_AGENT_JS_DIR_ . 'survey.js');
			$this->addJS(_AGENT_JS_DIR_ . 'messenger.js');
			$this->addJS(_AGENT_JS_DIR_ . 'messengeruploadify.js');
		}

	}

	public function initContent() {

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

		if ($this->context->cookie->is_agent == 1) {
			$this->setTemplate(_PS_AGENT_DIR_ . 'index.tpl');
		} else {
			$this->setTemplate(_PS_THEME_DIR_ . 'index.tpl');
		}

	}

}
