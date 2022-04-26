<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class EducationAccessControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var string $php_self */
	public $php_self = 'education-access';

	public $courseLink;

	public $identifiant;

	public $passwd_link;
	// @codingStandardsIgnoreEnd

	public function initContent() {

		parent::initContent();
		$this->display_header = false;
		$this->display_footer = false;
		$this->context->smarty->assign(
			[
				'platformLink' => $this->courseLink,
				'identifiant'  => $this->identifiant,
				'passwd_link'  => $this->passwd_link,
			]
		);

		$this->setTemplate(_PS_THEME_DIR_ . 'educationaccess.tpl');
	}

	public function ajaxProcessLaunchEducationIframe() {

		$params = Tools::getValue('params');
		$this->courseLink = $params['courseLink'];
		$this->identifiant = $params['identifiant'];
		$this->passwd_link = $params['passwd_link'];

		$this->context->smarty->assign(
			[
				'platformLink' => $this->courseLink,
				'identifiant'  => $this->identifiant,
				'passwd_link'  => $this->passwd_link,
			]
		);

		$this->setTemplate(_PS_THEME_DIR_ . 'educationaccess.tpl');

		$return = [

			'platformLink' => $this->courseLink,
			'identifiant'  => $this->identifiant,
			'passwd_link'  => $this->passwd_link,
			'html'         => $this->context->smarty->fetch($this->template),
		];
		die(Tools::jsonEncode($return));

	}
}
