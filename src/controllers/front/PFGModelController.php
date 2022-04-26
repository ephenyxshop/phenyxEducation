<?php

/**
 * Class PFGModelControllerCore
 *
 * @since 1.8.1.0
 */
class PFGModelControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'pfgmodel';
	
	public $pfg;
    
    public $ssl = true;
    // @codingStandardsIgnoreEnd

    /**
     * Initialize CMS controller
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function init() {

       	if ($idPFGModel = (int) Tools::getValue('id_pfg')) {
            $this->pfg = new PFGModel($idPFGModel, $this->context->language->id);
        } 
        parent::init();

        $this->canonicalRedirection();        

    }

    /**
     * Canonical redirection
     *
     * @param string $canonicalUrl
     *
     * @since 1.8.1.0
     */
    public function canonicalRedirection($canonicalUrl = '') {

        if (Tools::getValue('live_edit')) {
            return;
        }

        if (Validate::isLoadedObject($this->pfg) && ($canonicalUrl = $this->context->link->getPFGLink($this->pfg, $this->pfg->link_rewrite, $this->ssl))) {
            parent::canonicalRedirection($canonicalUrl);
        } 

    }

    public function setMedia() {

        parent::setMedia();

        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'contact-form.css');
        $this->addJS('https://www.google.com/recaptcha/api.js?render=6Lc2K44aAAAAABMpUzzwWjujdn4veuGthFmurSEs');
        $this->addJS(_THEME_JS_DIR_ . 'form.js');
        Media::addJsDef([
            'AjaxPFGModelLink' => $this->context->link->getPageLink('pfgmodel', true),

        ]);
    }

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     */
    public function initContent() {

        parent::initContent();

        $renderer = new PFGRenderer($this->pfg->id);
		
		$form = $renderer->displayForm();
		
		$this->context->smarty->assign(
            [

                'form'   => $form,
				'title'  => $this->pfg->title,
				'idForm' => $this->pfg->id 

            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . $this->pfg->template);
    }
	
	public function ajaxProcessProceedCustomForm() {
		
		$id_pfg = (int)Tools::getValue('pfg_form_id');
		if (!(int)Tools::getValue('pfg_form_id')) {
			die(false);
		}
		$submit = new PFGRenderer($id_pfg);
		$result = $submit->processSubmit();
		if($result) {
			$context = Context::getContext();
			$firstname = Tools::getValue('firstname');
			$lastname = Tools::getValue('lastname');
			$email = Tools::getValue('email');
			$education = Tools::getValue('education');
		
			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/match.tpl');
			$tpl->assign([
				'lastname'        => $lastname,
				'firstname'       => $firstname,
				'education'  => $education,

			]);
		
			$postfields = [
				'sender'	=> [
					'name'  => "Service  Jeu concour ".Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
				],
				'to' 		=> [
					[
						'name'  => $firstname . ' ' . $lastname,
						'email' => $email,
					],
				],

				'subject'     => 'Vous êtes inscrit pour gagner une formation '.$education,
				"htmlContent" => $tpl->fetch()
			];

			$result = Tools::sendEmail($postfields);
			
			$return = [
				'success' => true
			];
		} else {
			$return = [
				'success' => false
			];
		}
		
		die(Tools::jsonEncode($return));
	}

}
