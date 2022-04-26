<?php

/**
 * Class AuthControllerCore
 *
 * @since 1.8.1.0
 */
class AuthControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var bool $ssl */
    public $ssl = true;
    /** @var string $php_self */
    public $php_self = 'authentication';
    /** @var bool $auth */
    public $auth = false;
    /** @var bool create_account */
     

    /**
     * Start forms process
     * @see FrontController::postProcess()
     *
     * @since 1.8.1.0
     */
    public function postProcess() {

        parent::postProcess();

    }

    /**
     * Update context after student creation
     * @param Student $student Created student
     *
     * @since 1.8.1.0
     */
    protected function updateContext(Customer $student) {

        
        if ($student->is_agent) {
			if (!defined('_PS_CEF_URL_')) {
    			define('_PS_CEF_URL_', $this->context->shop->agent_url);
			}
			$cookie_lifetime = defined('_PS_ADMIN_DIR_') ? (int) Configuration::get('PS_COOKIE_LIFETIME_BO') : (int) Configuration::get('PS_COOKIE_LIFETIME_FO');

			if ($cookie_lifetime > 0) {
    			$cookie_lifetime = time() + (max($cookie_lifetime, 1) * 3600);
			}
			$force_ssl = Configuration::get('PS_SSL_ENABLED') && Configuration::get('PS_SSL_ENABLED_EVERYWHERE');
			$domains = [$this->context->shop->agent_url];
			
			$this->context->cookie = new Cookie('phenyx-sa' . $this->context->shop->id, '', $cookie_lifetime, $domains, false, $force_ssl);
            $this->context->cookie->is_agent = 1;
            $idAgent = SaleAgent::getSaleAgentbyIdStudent($student->id);
            $saleAgent = new SaleAgent($idAgent);
            $this->context->agent = $saleAgent;
            $saleAgent->log_in = 1;
            $saleAgent->last_timestamp = time();
            $saleAgent->update();
            $this->context->cookie->id_agent = $idAgent;
        }
		$this->context->customer = $student;
        $this->context->cookie->id_customer = (int) $student->id;
        $this->context->cookie->student_lastname = $student->lastname;
        $this->context->cookie->student_firstname = $student->firstname;
        $this->context->cookie->passwd = $student->passwd;

        $this->context->cookie->logged = 1;
        $this->context->cookie->__set('logged', 1);

        $student->logged = 1;
        $this->context->cookie->email = $student->email;
		
        $this->context->cookie->write();
    }

    
    protected function sendConfirmationMail(Customer $student) {

        $tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/account.tpl');
        $tpl->assign([
            'student' => $student,
        ]);
        $postfields = [
            'sender'      => [
                'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
                'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
            ],
            'to'          => [
                [
                    'name'  => $student->firstname . ' ' . $student->lastname,
                    'email' => $student->email,
                ],
            ],

            'subject'     => $student->firstname . ' ! Bienvenue sur '.Configuration::get('PS_SHOP_NAME'),
            "htmlContent" => $tpl->fetch(),
        ];

        $result = Tools::sendEmail($postfields);

        $tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/account_report.tpl');
        $tpl->assign([
            'student' => $student,
        ]);
        $postfields = [
            'sender'      => [
                'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
                'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
            ],
            'to'          => [
                [
                    'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
                    'email' => Configuration::get('PS_SHOP_EMAIL'),
                ],
            ],
            
            'subject'     => 'Nouvelle inscription de ' . $student->firstname . ' ' . $student->lastname,
            "htmlContent" => $tpl->fetch(),
        ];
        $result = Tools::sendEmail($postfields);

    }

    public function ajaxProcessNewStudent() {

        $student = new Customer();

        foreach ($_POST as $key => $value) {

            if (property_exists($student, $key) && $key != 'id_customer') {

                if ($key == 'password' && Tools::getValue('id_customer') && empty($value)) {
                    continue;
                }
				
				if ($key == 'birthday' && !empty($value)) {

					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

                $student->{$key}
                = $value;
            }

        }

        $student->passwd = Tools::hash(Tools::getValue('password'));
        $student->password = Tools::getValue('password');
        $student->active = 1;
		$student->id_shop_group = 1;
		$student->id_shop = 1;
		$student->id_country = 8;
        $student->customer_code = Customer::generateCustomerCode($student->id_country, $student->address_zipcode);
        $student->id_stdaccount = Customer::generateCustomerAccount($student);
        $student->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
        $student->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
        $student->newsletter = 1;
        $mobile = str_replace(' ', '', $student->phone_mobile);

        if (strlen($mobile) == 10 && $student->id_country == 8) {
            $mobile = '+33' . substr($mobile, 1);
            $student->phone_mobile = $mobile;
        }

        $checkEmail = Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_customer`')
                ->from('customer')
                ->where('`email` LIKE \'' . $student->email . '\'')
        );

        if ($checkEmail > 0) {
            $result = [
                'success' => false,
                'message' => 'L\'email de cette étudiont existe déjà dans la base donnée.',
            ];
            die(Tools::jsonEncode($result));
        }

        $result = $student->add();

        if ($result) {
			if(!empty($address_street = Tools::getValue('address_street'))) {
				$address = new Address();
				$address->id_country = 8;
				$address->id_customer = $student->id;
				$address->alias = 'Adresse de Facturation';
				$address->lastname = $student->lastname;
				$address->firstname = $student->firstname;
				$address->address1 = $address_street;
				$address->address2 = Tools::getValue('address_street2');
				$address->city = Tools::getValue('address_city');
				$address->postcode =Tools::getValue('address_zipcode');
				$address->phone_mobile = Tools::getValue('phone_mobile');
				$result = $address->add();				
			}		
            $this->updateContext($student);
            $this->sendConfirmationMail($student);
            $result = [
                'success' => true,
                'message' => $this->l('Votre compte a été crée avec succès'),
            ];
        } else {
            $result = [
                'success' => false,
                'message' => $this->l('Nous avons rencontré une erreur lors de la création de votre compte'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessSuggestPassword() {

        $return = [
            'password' => Tools::generateStrongPassword(),
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessCheckEmail() {

        $email = Tools::getValue('email');
        $checkExist = Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_customer`')
                ->from('customer')
                ->where('`email` LIKE \'' . $email . '\'')
        );

        if (isset($checkExist) && $checkExist > 0) {
            $return = [
                'success' => false,
            ];
        } else {
            $return = [
                'success' => true,
            ];
        }

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessLogStudent() {

        $file = fopen("testProcessLogStudent.txt","w");
		$passwd = trim(Tools::getValue('passwd'));
		fwrite($file, $passwd.PHP_EOL);

        $_POST['passwd'] = null;
        $email = Tools::convertEmailToIdn(trim(Tools::getValue('email')));
		fwrite($file, $email.PHP_EOL);
        if (empty($email)) {
            $this->errors[] = Tools::displayError('An email address required.');
        } else if (!Validate::isEmail($email)) {
            $this->errors[] = Tools::displayError('Invalid email address.');
        } else if (empty($passwd)) {
            $this->errors[] = Tools::displayError('Password is required.');
        } else if (!Validate::isPasswd($passwd)) {
            $this->errors[] = Tools::displayError('Invalid password.');
        } else {
			
            $student = new Customer();
            $authentication = $student->getByEmail(trim($email), trim($passwd));
			fwrite($file, print_r($authentication, true));
            if (isset($authentication->active) && !$authentication->active) {
                $this->errors[] = Tools::displayError('Your account isn\'t available at this time, please contact us');
            } else if (!$authentication || !$student->id) {
                $this->errors[] = Tools::displayError('Authentication failed.');
            } else {
				fwrite($file, 'Update Context'.PHP_EOL);
                $this->updateContext($student);
            }

        }

        if (count($this->errors)) {
			fwrite($file, 'Des erreurs'.PHP_EOL);
            $return = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			fwrite($file, 'Pas erreur'.PHP_EOL);
            $idEvaluation = $student->getUnansweredEvaluation();

            if ($idEvaluation > 0) {
                $evaluation = new StudentEvaluation($idEvaluation);
                $link = Context::getContext()->link->getPageLink('evaluation', true, Context::getContext()->language->id, ['idStudentEducation' => $evaluation->id_student_education, 'idEvaluation' => $idEvaluation], false, 1);

            } else {
				if (defined('_PS_CEF_URL_')) {
					$secret_iv = _COOKIE_KEY_;
					$secret_key = _PHP_ENCRYPTION_KEY_;
					$string = $this->context->cookie->id_customer . '-' . $this->context->customer->lastname . $this->context->customer->passwd;
					$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
					$link = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitCefLogin';

					
				} else {
					$link = Context::getContext()->link->getPageLink('index');
				}
                
            }
			fwrite($file, 'Link'.$link.PHP_EOL);
            $return = [
                'success' => true,
                'message' => $this->l('Votre compte a été initialisé avec succès'),
                'link'    => $link,
            ];
        }

        die(Tools::jsonEncode($return));

    }

    public function ajaxProcessRetrivePassword() {

        $email = Tools::getValue('email');

        if (!($email = trim(Tools::getValue('email'))) || !Validate::isEmail($email)) {
            $this->errors[] = Tools::displayError('Invalid email address.');
        } else {
            $student = new Customer();
            $student->getByemail($email);

            if (!Validate::isLoadedObject($student)) {
                $this->errors[] = Tools::displayError('There is no account registered for this email address.');
            } else {

                $token = md5($student->password);
                $tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/password_query.tpl');
                $tpl->assign([
                    'email'     => $student->email,
                    'lastname'  => $student->lastname,
                    'firstname' => $student->firstname,
                    'url'       => $this->context->link->getPageLink('ajax', true, null, 'token=' . $token . '&id_customer=' . (int) $student->id . '&action=generatePassword'),
                ]);

                $postfields = [
                    'sender'      => [
                        'name'  => "Automation CRM",
                        'email' => Configuration::get('PS_SHOP_EMAIL'),
                    ],
                    'to'          => [
                        [
                            'name'  => $student->firstname . ' ' . $student->lastname,
                            'email' => $student->email,
                        ],
                    ],
                    'subject'     => 'Vitre demande de régénération de mot de passe',
                    "htmlContent" => $tpl->fetch(),
                ];
                $result = Tools::sendEmail($postfields);

                $return = [
                    'success'      => true,
                    'redirectLink' => $this->context->link->getPageLink('index'),
                    'message'      => $this->l('Un email vient de vous être envoyé pour réinitialiser votre mot de passe'),
                ];
                die(Tools::jsonEncode($return));
            }

        }

        if (count($this->errors)) {
            $return = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        }

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessUpdateLastTimeStamp() {

        $ajaxIdAgent = Tools::getValue('ajaxIdAgent');
        $saleAgent = new SaleAgent($ajaxIdAgent);
        $saleAgent->last_timestamp = time();
        $saleAgent->update();
        die(true);
    }

    public function ajaxProcessUploadProfilPicture() {

        $id_student = $this->context->student->id;
        $dir = _PS_SALEAGENT_IMG_DIR_;
        $name = 'PicProfil';
        $type == 'profil';

        if ($croped_image = Tools::getValue($name)) {
            list($type, $croped_image) = explode(';', $croped_image);
            list(, $croped_image) = explode(',', $croped_image);
            $croped_image = base64_decode($croped_image);
            $uploadfile = $dir . basename($this->context->student->id . '.jpg');
            file_put_contents($uploadfile, $croped_image);
            ImageManager::resize($uploadfile, $uploadfile);
            die($this->context->student->id);
        }

    }

}
