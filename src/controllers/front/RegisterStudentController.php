<?php

/**
 * Class RegisterStudentControllerCore
 *
 * @since 1.8.1.0
 */
class RegisterStudentControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var bool $auth */
	public $auth = true;
	/** @var string $php_self */
	public $php_self = 'register-student';
	/** @var string $authRedirection */
	public $authRedirection = 'register-student';
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
		$this->addCSS(_AGENT_CSS_DIR_ . 'register.css');
		$this->addCSS(_AGENT_CSS_DIR_ . 'product.css');
		$this->addJS(_AGENT_JS_DIR_ . 'register.js');
		Media::addJsDef([
			'AjaxRegisterLink' => $this->context->link->getPageLink('register-student', true),

		]);
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

		$countries = Country::getCountries($this->context->language->id, true);
		$supplies = EducationSupplies::getEducationSupplies();

		$this->context->smarty->assign(
			[
				'slots'     => EducationSession::getNextEducationSlot(),
				'supplies'      => $supplies,
				'genders'   => Gender::getGenders(),
				'countries' => $countries,
			]
		);

		$this->setTemplate(_PS_AGENT_DIR_ . 'register-student.tpl');
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
			$student = new Customer($checkExist);
			$return = [
				'success'   => false,
				'idStudent' => $student->id,
				'email'     => $student->email,
				'firstname' => $student->firstname,
				'lastname'  => $student->lastname,
			];
		} else {
			$return = [
				'success' => true,
			];
		}

		die(Tools::jsonEncode($return));
	}

	public static function ajaxProcessGetAutoCompleteCity() {

		$query = Tools::getValue('search');

		$results = Address::getAutoCompleteCity($query);

		die(Tools::jsonEncode($results));

	}

	public static function ajaxProcessGetAutoCompleteEducation() {
		
		$context = Context::getContext();
		$results = [];
		$query = Tools::getValue('search');
		$sql = 'SELECT p.`id_education`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_education.`id_image_education` id_image, il.`legend`, p.`cache_default_attribute`, etl.`name` as educationFamily
		FROM `' . _DB_PREFIX_ . 'education` p
		LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` pl ON (pl.id_education = p.id_education AND pl.id_lang = ' . (int) $context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education` image_education
			ON (image_education.`id_education` = p.`id_education` AND image_education.cover=1)
		LEFT JOIN `' . _DB_PREFIX_ . 'education_type_lang` etl
			ON (p.`id_education_type` = etl.`id_education_type` AND etl.`id_lang` = ' . (int) $context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (image_education.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $context->language->id . ')
		WHERE p.active = 1 AND (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')' .
			' GROUP BY p.id_education';
		$items = Db::getInstance()->executeS($sql);

		if ($items) {

			foreach ($items as $item) {

				if ($item['cache_default_attribute']) {
					$sql = 'SELECT pa.`id_education_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name,
						a.`id_attribute`
					FROM `' . _DB_PREFIX_ . 'education_attribute` pa
					' . Shop::addSqlAssociation('education_attribute', 'pa') . '
					LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON pac.`id_education_attribute` = pa.`id_education_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $context->language->id . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $context->language->id . ')
					LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_image` pai ON pai.`id_education_attribute` = pa.`id_education_attribute`
					WHERE pa.`id_education` = ' . (int) $item['id_education'] . '
					GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
					ORDER BY pa.`id_education_attribute`';

					$combinations = Db::getInstance()->executeS($sql);

					if (!empty($combinations)) {

						foreach ($combinations as $k => $combination) {
							$results[$combination['id_education_attribute']]['id_education'] = $item['id_education'];
							$results[$combination['id_education_attribute']]['id_education_attribute'] = $combination['id_education_attribute'];
							$results[$combination['id_education_attribute']]['educationFamily'] = $item['educationFamily'];
							$results[$combination['id_education_attribute']]['image_link'] = $context->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], 'home_default');
							!empty($results[$combination['id_education_attribute']]['name']) ? $results[$combination['id_education_attribute']]['name'] .= ' ' . $combination['group_name'] . '-' . $combination['attribute_name']
							: $results[$combination['id_education_attribute']]['name'] = $item['name'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];

							if (!empty($combination['reference'])) {
								$results[$combination['id_education_attribute']]['ref'] = $combination['reference'];
							} else {
								$results[$combination['id_education_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
							}

						}

					} else {
						$education = [
							'id_education'           => (int) ($item['id_education']),
							'id_education_attribute' => 0,
							'name'                   => $item['name'],
							'educationFamily'        => $item['educationFamily'],
							'image_link'             => $context->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], 'home_default'),
							'ref'                    => (!empty($item['reference']) ? $item['reference'] : ''),

						];
					}

				} else {
					$education = [
						'id_education'           => (int) ($item['id_education']),
						'id_education_attribute' => 0,
						'name'                   => $item['name'],
						'educationFamily'        => $item['educationFamily'],
						'image_link'             => $context->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], 'home_default'),
						'ref'                    => (!empty($item['reference']) ? $item['reference'] : ''),
					];
					array_push($results, $education);
				}

			}

			$results = array_values($results);
		}

		die(Tools::jsonEncode($results));

	}

	public function ajaxProcessGetEducationDetails() {

		$id_education = Tools::getValue('id_education');
		$id_education_attribute = Tools::getValue('id_education_attribute');

		$education = Education::getEducationDetails($id_education, $id_education_attribute);

		die(Tools::jsonEncode(['education' => $education]));
	}

	public function ajaxProcessReserveEducation() {

		$education = new StudentEducation();
		$student = new Customer(Tools::getValue('id_student'));

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}
				if ($key == 'date_start' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}
				if ($key == 'date_end' && !empty($value)) {
					$date = DateTime::createFromFormat('d/m/Y', $value);
					$value = date_format($date, "Y-m-d");
				}

				$education->{$key}
				= $value;
			}

		}
		$education->id_customer = $student->id;
		$education->id_student_education_state = 2;
		$education->identifiant = $student->email;
		$education->passwd_link = $student->password;
		if($education->id_education_session > 0) {
			$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
			$session = new EducationSession($education->id_education_session);
			$date = new DateTime($session->session_date);
			$date->modify('+'.$days.' days');
			$education->date_start = $session->session_date;
			$education->date_end = $date->format('Y-m-d');
		}
		$education->id_formatpack = 1;
		$result = $education->add();

		if ($result) {

			$studentEducation = new StudentEducation($education->id);
			$agent = new SaleAgent($studentEducation->id_sale_agent);
			$this->senEducationEmail($studentEducation, $student, $agent);
			
			$suivie = new StudentEducationSuivie();
			$suivie->suivie_date = date('Y-m-d');
			$suivie->id_student_education = $studentEducation->id;
			$suivie->id_student_education_state = 10;
			$suivie->id_sale_agent = $agent->id;
			$suivie->id_employee = 0;
			$suivie->content = 'Inscription de '.$student->firstname.' '.$student->lastname.' a une formation"'.$studentEducation->name.' par '.$agent->firstname.' '.$agent->lastname;
			$suivie->add();

			$message = '<div class="form-contact toBeCleaned col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
			<h3>' . $student->firstname . ' ' . $student->lastname . ' a bien été enregistre pour une formation "' . $studentEducation->name . '" pour le ' . $studentEducation->date_start . '</h3><div class="form-group">
				<button id="otherStudent" class="et_pb_contact_submit et_pb_button">
							Enregistrer un autre étudiant / Formation
				</button>
				</div></div>';

			$message2 = '<div class="myFrow course">
							<div class="session">' . $studentEducation->sessionName . '</div>
							<div class="formation">' . $studentEducation->name . '</div>
							<div class="state">' . $studentEducation->state . '</div>
							<div class="duration">' . $studentEducation->duration . '</div>
							<div class="ratio">' . $studentEducation->ratio . ' %</div>
						</div>';

			$result = [
				'success'  => true,
				'message'  => $message,
				'message2' => $message2,
			];
		} else {
			$result = [
				'success' => false,
				'message' => "<div class='form-contact toBeCleaned col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3'><h3>Nous avons rencontré un problème technique, la formation n'a pas été ajoutée</h3></div>",
			];
		}

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessAddNewStudent() {

		$id_sale_agent = Tools::getValue('id_sale_agent');
		$agent = new SaleAgent($id_sale_agent);
		$student = new Customer();
		
		foreach ($_POST as $key => $value) {

			if (property_exists($student, $key) && $key != 'id_customer') {
			
				if ($key == 'password' && Tools::getValue('id_customer') && empty($value)) {
					continue;
				}
				
				

				$student->{$key} = $value;
			}

		}

		$student->firstname = ucfirst(strtolower($student->firstname));
		$student->id_country = 8;
		$password = Tools::generateStrongPassword();
		$student->passwd = Tools::hash($password);
		$student->password = $password;
		$student->active = 1;

		$student->customer_code = Customer::generateCustomerCode($student->id_country, Tools::getValue('postcode'));
		$student->id_stdaccount = Customer::generateCustomerAccount($student);
		$student->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
		$student->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
		$student->newsletter = 1;
		$student->id_default_group = 4;
		$student->groupBox = [3,4];
		

		$result = $student->add();
		
		if ($result) {
						
			$mobile = str_replace(' ', '', trim(Tools::getValue('phone_mobile')));

			if (strlen($mobile) == 10 && $student->id_country == 8) {
				$mobile = '+33' . substr($mobile, 1);
				$phone_mobile = $mobile;
			}
			
			$address = new Address();
			$address->id_country = 8;
			$address->id_customer = $student->id;
			$address->alias = 'Adresse de Facturation';
			$address->lastname = $student->lastname;
			$address->firstname = $student->firstname;
			$address->address1 = Tools::getValue('address1');
			$address->address2 = Tools::getValue('address2');
			$address->city = Tools::getValue('city');
			$address->postcode = Tools::getValue('postcode');
			$address->phone_mobile = $phone_mobile;
			try {
  				$result = $address->add(); 
			} catch(Exception $e) {
   				
			}
			
			$suivie = new StudentSuivie();
			$suivie->id_customer = $student->id;
			$suivie->id_sale_agent = $agent->id;
			$suivie->id_employee = 0;
			$suivie->content = 'Inscription de '.$student->firstname.' '.$student->lastname.' par '.$agent->firstname.' '.$agent->lastname;
			$suivie->add();
			
			
			
			$this->sendConfirmationMail($student, $agent);
			$message = '<div class="form-contact toBeCleaned col-sm-8 col-sm-offset-2 col-lg-6 col-lg-offset-3">
			<h3>Etudiant ajouté avec succès</h3>
			<div class="form-group">
				<button type="submit" name="sendContactForm" onClick="proceedNewEducation(' . $student->id . ');" class="et_pb_contact_submit et_pb_button">
							Je souhaite réserver une formation pour ' . $student->firstname . ' ' . $student->lastname . '
						</button>
					</div></div>';
			$result = [
				'success'   => true,
				'message'   => $message,
				'email'     => $student->email,
				'firstname' => $student->firstname,
				'lastname'  => $student->lastname,
				'idStudent' => $student->id,
			];
		} else {
			$result = [
				'success' => false,
				'message' => $this->l('Nous avons rencontré une erreur lors de la création du compte'),
			];
		}

		die(Tools::jsonEncode($result));
	}

	protected function sendConfirmationMail(Customer $student, SaleAgent $agent) {

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/account_agent.tpl');
		$tpl->assign([
			'student' => $student,
			'agent'   => $agent,

		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => "Service  Comptabilité ".Configuration::get('PS_SHOP_NAME'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				],
			],
			'subject'     => 'Nouvelle inscription de ' . $student->firstname . ' ' . $student->lastname,
			"htmlContent" => $tpl->fetch(),
		];
		$result = Tools::sendEmail($postfields);

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/agent_account.tpl');
		$tpl->assign([
			'student'            => $student,
			'agent_firstname'    => $agent->firstname,
			'agent_lastname'     => $agent->lastname,
			'agent_phone_mobile' => $agent->phone_mobile,
			'agent_email'        => $agent->email,
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

	}

	public function generateStudentCode($id_country, $postcode = null) {

		$cc = Db::getInstance()->getValue('SELECT `id_student` FROM `' . _DB_PREFIX_ . 'student` ORDER BY `id_student` DESC') + 1;

		if (isset($postcode)) {

			if ($id_country != 8) {
				$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
			} else {
				$iso_code = substr($postcode, 0, 2);

				if ($iso_code >= 97) {
					$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
				}

			}

			$Shop_iso = 'ST';
			return substr($postcode, 0, 2) . $Shop_iso . sprintf("%04s", $cc);
		} else {
			$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');

			$Shop_iso = 'ST_' . $iso_code;

			return $Shop_iso . sprintf("%04s", $cc);
		}

	}

	protected function senEducationEmail(StudentEducation $studentEducation, Customer $student, SaleAgent $agent) {

		$fileAttachement = null;
		$attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('fileName')
				->from('education_programme')
				->where('`id_education` = ' . (int) $studentEducation->id_education)
				->where('`id_education_attribute` = ' . (int) $studentEducation->id_education_attribute)
		);

		if ($attachement != '') {
				$fileName = _PS_PROGRAM_DIR_ . $attachement;
				$fileAttachement[] = [
					'content' => chunk_split(base64_encode(file_get_contents($fileName))),
					'name'    => $attachement,
				];
			}

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationRequestAgent.tpl');
		$tpl->assign([
			'student'         	=> $student,
			'studentEducation'	=> $studentEducation,
			'agent_firstname' 	=> $agent->firstname,
			'agent_lastname'  	=> $agent->lastname,
			'is_video_tuto'     => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
			'tutoVideo'       	=> Configuration::get('EPH_TUTO_VIDEO')
		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => $agent->email,
			],
			'to'          => [
				[
					'name'  => $student->firstname . ' ' . $student->lastname,
					'email' => $student->email,
				],
			],

			'subject'     => 'Votre formation ' . $studentEducation->name,
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];
		$result = Tools::sendEmail($postfields);

		$step = new StudentEducationStep(2, $this->context->language->id);

		if ($step->is_suivie == 1) {
			$suivie = new StudentEducationSuivie();
			$suivie->suivie_date = date('Y-m-d');
			$suivie->id_student_education = $studentEducation->id;
			$suivie->id_student_education_state = $step->id;
			$suivie->email_title = 'Votre formation "' . $studentEducation->name . '"';
			$suivie->email_content = $tpl->fetch();
			$suivie->content = $step->suivie;
			$suivie->add();

		}

		if ($agent->sale_commission_amount > 0) {
			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationAgentRequest.tpl');
			$tpl->assign([
				'title'           => $student->title,
				'lastname'        => $student->lastname,
				'birthname'       => $student->birthname,
				'firstname'       => $student->firstname,
				'education_name'  => $studentEducation->name,
				'date_start'      => $studentEducation->date_start,
				'date_end'        => $studentEducation->date_end,
				'last_days'       => $studentEducation->days,
				'last_hours'      => $studentEducation->hours,
				'supplyName'      => $studentEducation->supplyName,
				'agent_lastname'  => $agent->lastname,
				'agent_firstname' => $agent->firstname,
				'agent_com'       => $agent->sale_commission_amount,

			]);
			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
					'email' => $agent->email,
				],
				'to'          => [
					[
						'name'  => $agent->firstname . ' ' . $agent->lastname,
						'email' => $agent->email,
					],
				],

				'subject'     => 'Inscription de votre étudiant à une formation en ' . $studentEducation->name,
				"htmlContent" => $tpl->fetch(),
			];
			$result = Tools::sendEmail($postfields);

		}

	}

}
