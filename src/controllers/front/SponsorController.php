<?php

/**
 * Class SponsorControllerCore
 *
 * @since 1.8.1.0
 */
class SponsorControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'sponsor';
	
	public $sponsor;
    
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

       	if ($idSponsor = (int) Tools::getValue('id_sponsor_partner')) {
            $this->sponsor = new SponsorPartner($idSponsor);
        } 
        parent::init();
		$this->student = $this->context->student;

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

        if (Validate::isLoadedObject($this->sponsor) && ($canonicalUrl = $this->context->link->getSponsorLink($this->sponsor, $this->sponsor->link_rewrite, $this->ssl))) {
            parent::canonicalRedirection($canonicalUrl);
        } 

    }

    public function setMedia() {

        parent::setMedia();

        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_PS_THEME_DIR_ . 'sponsor/assets-fc-lyon/css/partenariats.css');
        $this->addJS('https://www.google.com/recaptcha/api.js?render=6Lc2K44aAAAAABMpUzzwWjujdn4veuGthFmurSEs');
		$this->addJS(_PS_THEME_DIR_ . 'sponsor/assets-fc-lyon/js/partenariats.js');
        Media::addJsDef([
            'AjaxSponsorLink' => $this->context->link->getPageLink('sponsor', true),
			'isLogged'		=> $this->context->cookie->isLogged(),
        ]);
    }

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     */
    public function initContent() {

        parent::initContent();

        $cookie = $this->context->cookie;
		$nbChild = 0;
		$childs = [];
		
		if($this->student->id > 0) {
			$nbChild = Godchild::getNbChildByIdStudent($this->student->id, $this->sponsor->id);
			$nbChildValidate = Godchild::getNbChildValidateByIdStudent($this->student->id, $this->sponsor->id);
		}
		
		$this->context->smarty->assign(
            [
				'student'   => $this->student,
                'id_sponsor_partner' => $this->sponsor->id,
				'sponsor' => $this->sponsor,
				'cookie'  => $cookie,
				'nbChild' => $nbChild,
				'nbChildValidate' => $nbChildValidate,
				'childs' => Godchild::getChildsByIdStudent($this->student->id, $this->sponsor->id),

            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'sponsor.tpl');
    }
	
	public function ajaxProcessRegisterNewSponsor() {
		
		
		$file = fopen("testProcessRegisterNewSponsor.txt","w");
		$student = new Student();

        foreach ($_POST as $key => $value) {

            if (property_exists($student, $key) && $key != 'id_student') {
				fwrite($file, $key.' '.$value.PHP_EOL);

                if ($key == 'password' && Tools::getValue('id_student') && empty($value)) {
                    continue;
                }

                $student->{$key}
                = $value;
            }

        }

        $student->passwd = Tools::hash(Tools::getValue('password'));
        $student->password = Tools::getValue('password');
        $student->active = 1;
		$student->id_country = 8;

        $student->student_code = Student::generateStudentCode($student->id_country);
        $student->id_stdaccount = Student::generateStudentAccount($student->student_code);
        $student->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
        $student->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
        $student->newsletter = 1;
        $mobile = str_replace(' ', '', $student->phone_mobile);

        if (strlen($mobile) == 10 && $student->id_country == 8) {
            $mobile = '+33' . substr($mobile, 1);
            $student->phone_mobile = $mobile;
        }
		
		fwrite($file, print_r($student, true));
		
		try {
  			$result = $student->add();
		} catch(Exception $e) {
			fwrite($file, $e->getMessage());
		}
		
		
		
		if ($result) {
            $this->updateContext($student);
            $result = [
                'success' => true,
                'message' => $this->l('Votre compte a été crée avec succès'),	
				'student' => $student,
				
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('Nous avons rencontré une erreur lors de la création de votre compte'),
            ];
        }

        die(Tools::jsonEncode($result));
		
		
	}
	
	public function ajaxProcessSponsorConnect() {
		
		
		
		$passwd = trim(Tools::getValue('passwd'));

        $_POST['passwd'] = null;
        $email = Tools::convertEmailToIdn(trim(Tools::getValue('email')));

        if (empty($email)) {
            $this->errors[] = Tools::displayError('An email address required.');
        } else if (!Validate::isEmail($email)) {
            $this->errors[] = Tools::displayError('Invalid email address.');
        } else if (empty($passwd)) {
            $this->errors[] = Tools::displayError('Password is required.');
        } else if (!Validate::isPasswd($passwd)) {
            $this->errors[] = Tools::displayError('Invalid password.');
        } else {
            $student = new Student();
            $authentication = $student->getByEmail(trim($email), trim($passwd));

            if (isset($authentication->active) && !$authentication->active) {
                $this->errors[] = Tools::displayError('Your account isn\'t available at this time, please contact us');
            } else if (!$authentication || !$student->id) {
                $this->errors[] = Tools::displayError('Authentication failed.');
            } else {
                $this->updateContext($student);
            }

        }

        if (count($this->errors)) {
            $return = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			$idSponsorId = Tools::getValue('idSponsorId');
			
			$nbChild = Godchild::getNbChildByIdStudent($student->id, $idSponsorId);
			$nbChildValidate = Godchild::getNbChildValidateByIdStudent($student->id, $idSponsorId);
			
			$childs = Godchild::getChildsByIdStudent($student->id, $idSponsorId);
			$h4 = '<h4 class="valeur">

			<b>'.$nbChildValidate.'</b>/3<br><em class="potentiel"><span>'.$nbChild.'</span> étudiants potentiels enregistrés</em>';
			if($childs >0) {
				$h4 .= '<br><a href="javascript:void(0)" class="jsGoTo" data-cible="suiviDetails">Détails</a><br><br>';
			}
			$h4 .= '</h4>';
			
			$butonAction = 'Ajouter votre premier filleul';
			
			if($nbChild > 0) {
				$butonAction = 'Ajouter un nouveau filleul';
			}
			
			
			$boucle = '';
			
			foreach($childs as $child) {
				$boucle .= '<li class="'.$child->staut.'">
				<span class="statut"></span>
					<span class="nom">'.$child->firstname.' '.$child->lastname.'</span>
					<span class="date">Ajouté le '.$child->date_format.'</span>
				</li>';
			}
			
            
            $return = [
                'success' => true,
				'student' => $student,
                'message' => $this->l('Votre compte a été initialisé avec succès'),
				'h4' => $h4,
				'boucle' => $boucle,
				'butonAction' => $butonAction
            ];
        }

        die(Tools::jsonEncode($return));
	}
	
	protected function updateContext(Customer $student) {

        $this->context->student = $student;
        $student->last_accessed = date('Y-m-d H:i:s');
        $student->update();

        $this->context->cookie->id_student = (int) $student->id;
        $this->context->cookie->student_lastname = $student->lastname;
        $this->context->cookie->student_firstname = $student->firstname;
        $this->context->cookie->passwd = $student->passwd;

        

        $this->context->cookie->logged = 1;
        $this->context->cookie->__set('logged', 1);

        $student->logged = 1;
        $this->context->cookie->email = $student->email;
        $this->context->cookie->write();
    }
	
	public function ajaxProcessAddFilleul() {
		
		$student = new Godchild();

        foreach ($_POST as $key => $value) {

            if (property_exists($student, $key) && $key != 'id_godchild') {                

                $student->{$key}  = $value;
            }

        }
		
		$mobile = str_replace(' ', '', $student->phone_mobile);

        if (strlen($mobile) == 10 && $student->id_country == 8) {
            $mobile = '+33' . substr($mobile, 1);
            $student->phone_mobile = $mobile;
        }
		
		$result = $student->add();
		
		if ($result) {
			
			$html = '<li class="attente">
				<span class="statut"></span>
				<span class="nom">'.$student->firstname.' '.$student->lastname.'</span>
				<span class="date">Ajouté le '.date('d/m/Y').'</span>
			</li>';
             $result = [
                'success' => true,
                'message' => $this->l('Le filleul a été ajouté avec succès'),	
				 'html' => $html
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('Nous avons rencontré une erreur lors de la création de votre compte'),
            ];
        }

        die(Tools::jsonEncode($result));

		
		
	}

	
}
