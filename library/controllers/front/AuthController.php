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
     * @param Student $customer Created student
     *
     * @since 1.8.1.0
     */
    protected function updateContext(Customer $customer) {

        
        if ($customer->is_agent) {
			$secret_iv = _COOKIE_KEY_;
			$secret_key = _PHP_ENCRYPTION_KEY_;
			$string = $customer->id . '-' . $customer->lastname . $customer->passwd;
			$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
			$link = Context::getContext()->link->getAgentPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitCefLogin';
			$return = [
                'success' => true,
                'message' => $this->l('Votre compte a été initialisé avec succès'),
                'link'    => $link,
            ];
			die(Tools::jsonEncode($return));
            //header('Location: '.$link);
			//Tools::redirect($link);
			
			
        } else {
			
			$this->context->customer = $customer;
            if($customer->is_admin) {
                $employee = new Employee($customer->id_employee);
                if(Validate::isLoadedObject($employee)) {
                    if (!defined('_EPH_ADMIN_DIR_')) {
                        define('_EPH_ADMIN_DIR_', _SHOP_ROOT_DIR_);
                    }
                    $this->context->cookie->id_employee = (int) $employee->id;
                    $this->context->employee = $employee;
                    $this->context->cookie->is_admin = 1;
                }
            
            }
        	$this->context->cookie->id_customer = (int) $customer->id;
        	$this->context->cookie->student_lastname = $customer->lastname;
        	$this->context->cookie->student_firstname = $customer->firstname;
        	$this->context->cookie->passwd = $customer->passwd;

        	$this->context->cookie->logged = 1;
        	$this->context->cookie->__set('logged', 1);

        	$customer->logged = 1;
        	$this->context->cookie->email = $customer->email;
		
        	$this->context->cookie->write();
		}
    }
	

    
    protected function sendConfirmationMail(Customer $customer) {

        $tpl = $this->context->smarty->createTemplate(_EPH_MAIL_DIR_ . '/fr/account.tpl');
        $tpl->assign([
            'student' => $customer,
        ]);
        $postfields = [
            'sender'      => [
                'name'  => "Service  Administratif ".Configuration::get('EPH_SHOP_NAME'),
                'email' => 'no-reply@'.Configuration::get('EPH_SHOP_URL'),
            ],
            'to'          => [
                [
                    'name'  => $customer->firstname . ' ' . $customer->lastname,
                    'email' => $customer->email,
                ],
            ],

            'subject'     => $customer->firstname . ' ! Bienvenue sur '.Configuration::get('EPH_SHOP_NAME'),
            "htmlContent" => $tpl->fetch(),
        ];

        $result = Tools::sendEmail($postfields);

        $tpl = $this->context->smarty->createTemplate(_EPH_MAIL_DIR_ . '/fr/account_report.tpl');
        $tpl->assign([
            'student' => $customer,
        ]);
        $postfields = [
            'sender'      => [
                'name'  => "Service  Administratif ".Configuration::get('EPH_SHOP_NAME'),
                'email' => Configuration::get('EPH_SHOP_EMAIL'),
            ],
            'to'          => [
                [
                    'name'  => "Service  Administratif ".Configuration::get('EPH_SHOP_NAME'),
                    'email' => Configuration::get('EPH_SHOP_EMAIL'),
                ],
            ],
            
            'subject'     => 'Nouvelle inscription de ' . $customer->firstname . ' ' . $customer->lastname,
            "htmlContent" => $tpl->fetch(),
        ];
        $result = Tools::sendEmail($postfields);

    }

    
	public function ajaxProcessNewStudent() {

        $customer = new Customer();

        foreach ($_POST as $key => $value) {

            if (property_exists($customer, $key) && $key != 'id_customer') {

                if ($key == 'password' && Tools::getValue('id_customer') && empty($value)) {
                    continue;
                }			

                $customer->{$key}
                = $value;
            }

        }

        $customer->passwd = Tools::hash(Tools::getValue('password'));
        $customer->password = Tools::getValue('password');
        $customer->active = 1;
		$customer->id_shop_group = 1;
		$customer->id_shop = 1;
		$customer->id_country = 8;
        $customer->customer_code = Customer::generateCustomerCode($customer->id_country, $customer->address_zipcode);
        $customer->id_stdaccount = Customer::generateCustomerAccount($customer);
        $customer->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
        $customer->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
        $customer->newsletter = 1;
        $mobile = str_replace(' ', '', $customer->phone_mobile);

        if (strlen($mobile) == 10 && $customer->id_country == 8) {
            $mobile = '+33' . substr($mobile, 1);
            $customer->phone_mobile = $mobile;
        }

        $checkEmail = Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_customer`')
                ->from('customer')
                ->where('`email` LIKE \'' . $customer->email . '\'')
        );

        if ($checkEmail > 0) {
            $result = [
                'success' => false,
                'message' => 'L‘email de cet étudiant existe déjà dans la base donnée.',
            ];
            die(Tools::jsonEncode($result));
        }

        $result = $customer->add();

        if ($result) {
			if(!empty($address_street = Tools::getValue('address_street'))) {
				$address = new Address();
				$address->id_country = 8;
				$address->id_customer = $customer->id;
				$address->alias = 'Adresse de Facturation';
				$address->lastname = $customer->lastname;
				$address->firstname = $customer->firstname;
				$address->address1 = $address_street;
				$address->address2 = Tools::getValue('address_street2');
				$address->city = Tools::getValue('address_city');
				$address->postcode =Tools::getValue('address_zipcode');
				$address->phone_mobile = Tools::getValue('phone_mobile');
				$result = $address->add();				
			}		
            $this->updateContext($customer);
            $this->sendConfirmationMail($customer);
			$suivie = new StudentEducationSuivie();
			$suivie->suivie_date = date('Y-m-d');
			$suivie->id_student_education_state = 10;
			$suivie->id_employee = 0;
			$suivie->content = 'Auto Inscription de '.$customer->firstname.' '.$customer->lastname.' sur le Front Office';
			$suivie->add();
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
			
            $customer = new Customer();
            $authentication = $customer->getByEmail(trim($email), trim($passwd));
			
            if (isset($authentication->active) && !$authentication->active) {
                $this->errors[] = Tools::displayError('Your account isn\'t available at this time, please contact us');
            } else if (!$authentication || !$customer->id) {
                $this->errors[] = Tools::displayError('Authentication failed.');
            } else {
				
                $this->updateContext($customer);
            }

        }

        if (count($this->errors)) {
			
            $return = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
			
            $idEvaluation = $customer->getUnansweredEvaluation();

            if ($idEvaluation > 0) {
                $evaluation = new StudentEvaluation($idEvaluation);
                $link = Context::getContext()->link->getPageLink('evaluation', true, Context::getContext()->language->id, ['idStudentEducation' => $evaluation->id_student_education, 'idEvaluation' => $idEvaluation], false, 1);

            } else {
				$link = Context::getContext()->link->getPageLink('index');                
            }
			
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
            $customer = new Customer();
            $customer->getByemail($email);

            if (!Validate::isLoadedObject($customer)) {
                $this->errors[] = Tools::displayError('There is no account registered for this email address.');
            } else {

                $token = md5($customer->password);
                $tpl = $this->context->smarty->createTemplate(_EPH_MAIL_DIR_ . '/fr/password_query.tpl');
                $tpl->assign([
                    'email'     => $customer->email,
                    'lastname'  => $customer->lastname,
                    'firstname' => $customer->firstname,
                    'url'       => $this->context->link->getPageLink('ajax', true, null, 'token=' . $token . '&id_customer=' . (int) $customer->id . '&action=generatePassword'),
                ]);
				$link = $this->context->link->getPageLink('password', true, null, 'token=' . $token . '&email=' .  $customer->email . '&confirmation=1');
                $postfields = [
                    'sender'      => [
                        'name'  => "Votre Sevice client",
                        'email' => Configuration::get('EPH_SHOP_EMAIL'),
                    ],
                    'to'          => [
                        [
                            'name'  => $customer->firstname . ' ' . $customer->lastname,
                            'email' => $customer->email,
                        ],
                    ],
                    'subject'     => 'Vitre demande de régénération de mot de passe',
                    "htmlContent" => $tpl->fetch(),
                ];
                $result = Tools::sendEmail($postfields);

                $return = [
                    'success'      => true,
                    'redirectLink' => $link,
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

    

}
