<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use \Curl\Curl;

/**
 * Class StudentEducationCore
 *
 * @since 2.1.0.0
 */
class StudentEducationCore extends ObjectModel {

	const ROUND_ITEM = 1;
	const ROUND_LINE = 2;
	const ROUND_TOTAL = 3;
	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_education',
		'primary' => 'id_student_education',
		'fields'  => [
			'id_sale_agent'              => ['type' => self::TYPE_INT],
			'id_sale_agent_commission'   => ['type' => self::TYPE_INT],
			'id_education_session'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'date_start'                 => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'date_end'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'id_student'                 => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_customer'                => ['type' => self::TYPE_INT, 'required' => true, 'validate' => 'isUnsignedId'],
			'id_education'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_attribute'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_student_education_state' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_supplies'      => ['type' => self::TYPE_INT],
			'supply_name'      			 => ['type' => self::TYPE_STRING, 'size' => 32],
			'id_formatpack'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'price'                      => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'is_edof'                    => ['type' => self::TYPE_BOOL],
			'reference_edof'             => ['type' => self::TYPE_INT],
			'identifiant'                => ['type' => self::TYPE_STRING, 'size' => 64],
			'passwd_link'                => ['type' => self::TYPE_STRING, 'size' => 64],
			'first_connection'           => ['type' => self::TYPE_DATE, 'copy_post' => false],
			'notes'                      => ['type' => self::TYPE_STRING, 'size' => 128],
			'publiPost'                  => ['type' => self::TYPE_BOOL],
			'educLaunch'                 => ['type' => self::TYPE_BOOL],
			'isLaunch'                   => ['type' => self::TYPE_BOOL],
			'is_start'                   => ['type' => self::TYPE_BOOL],
			'doc_return'                 => ['type' => self::TYPE_BOOL],
			'shipping_number'            => ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber', 'size' => 64],
			'shipping_sms'               => ['type' => self::TYPE_BOOL],
			'education_lenghts'          => ['type' => self::TYPE_NOTHING],
			'deleted'                    => ['type' => self::TYPE_BOOL],
			'eval_hot'                   => ['type' => self::TYPE_BOOL],
			'eval_cold'                  => ['type' => self::TYPE_BOOL],
			'attest_end'                 => ['type' => self::TYPE_BOOL],
			'certification'              => ['type' => self::TYPE_BOOL],
			'id_platform'                => ['type' => self::TYPE_INT],
			'is_invoice'                 => ['type' => self::TYPE_BOOL],
			'deleted_reason'             => ['type' => self::TYPE_STRING, 'size' => 256],
			'date_add'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	public $id_student_education;
	public $id_sale_agent;
	public $id_sale_agent_commission;
	public $id_education_session;
	public $id_student;
	public $id_customer;
	public $id_education;
	public $id_education_attribute;
	public $id_student_education_state;
	public $id_education_supplies;
	public $supply_name;
	public $id_formatpack;
	public $price;
	public $is_edof;
	public $reference_edof;
	public $identifiant;
	public $passwd_link;
	public $first_connection;
	public $notes;
	public $publiPost;
	public $educLaunch;
	public $isLaunch;
	public $is_start;
	public $doc_return;
	public $shipping_number;
	public $shipping_sms;
	public $education_lenghts = '00:00:00';
	public $deleted;
	public $deleted_reason;
	public $eval_hot;
	public $eval_cold;
	public $attest_end;
	public $certification;
	public $id_platform;
	public $date_add;
	public $date_upd;
	public $state;
	public $reference;
	public $name;
	public $days;
	public $hours;
	public $courseLink;
	public $reservationLink;
	public $educationType;
	public $rate;
	public $priceWTax;
	public $sessionName;
	public $date_start;
	public $date_contrat;
	public $date_limit;
	public $date_begin;
	public $supplyName;
	public $date_end;
	public $duration;
	public $ratio;
	public $agent;
	public $firstname;
	public $lastname;
	public $email;
	public $phone_mobile;
	public $session_date;
	public $certificationName;
	public $id_education_prerequis;
	public $educationPlatform;
	public $formaPack;
	public $score_hot;
	public $answer_hot;
	public $is_invoice = 0;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null) {

		parent::__construct($id);

		if ($this->id) {
			$this->id_student_education = $this->id;
			$this->state = $this->getState();
			$details = Education::getEducationDetails($this->id_education, $this->id_education_attribute, false);

			foreach ($details as $key => $value) {
				$this->$key = $value;
			}

			$this->priceWTax = round($this->price * (1 + $this->rate / 100), 2);
			$this->price = round($this->price, 2);
			$this->sessionName = $this->getSessionDateName();
			$this->date_begin = $this->getDateStart();
			$date_start = $this->getDateStart();
			$date = new DateTime($date_start);
			$date->modify('+15 days');
			$this->date_contrat = $date->format('d/m/Y');
			$date = new DateTime($date_start);
			$date->modify('-14 days');
			$this->date_limit = $date->format('d/m/Y');
			$this->supplyName = $this->getSupply();
			$this->duration = $this->getDuration();
			$this->ratio = $this->getRatio();
			$this->agent = $this->getAgent();
			$this->firstname = $this->getStudentFirstnamme();
			$this->lastname = $this->getStudentLastnamme();
			$this->email = $this->getStudentEmail();
			$this->phone_mobile = $this->getStudentPhoneMobile();
			$this->session_date = $this->date_start;
			$this->id_education_prerequis = $this->getIdPrerequis();
			$this->answer_hot = $this->isHotAnswer();
			$this->score_hot = $this->getHotScore();

		}

	}
	
	public function isHotAnswer() {
		
		return  Db::getInstance()->getValue(
  			(new DbQuery())
				->select('id_student_evaluation')
				->from('student_evaluation')
    			->where('`id_student_education` = '.$this->id_student_education)
				->where('`type` LIKE "hot" AND `answered` = 1')
			);
	}
	
	public function getHotScore() {
		
		return Db::getInstance()->getValue(
  			(new DbQuery())
				->select('score')
				->from('student_evaluation')
    			->where('`id_student_education` ='.$this->id_student_education)
				->where('`type` LIKE "hot" AND `answered` = 1')
			);
	}

	public function delete() {

		$idCommission = 0;

		if ($this->id_sale_agent > 0) {
			$idCommission = SaleAgentCommission::getIdCommissionbyIdSession($this->id);
		}

		$success = parent::delete();

		if ($success && $idCommission > 0) {
			$commission = new SaleAgentCommission($idCommission);
			$commission->delete();
		}

		return $success;
	}

	public function add($autoDate = false, $nullValues = true) {

		$success = parent::add($autoDate, $nullValues);

		if ($success) {
			$context = Context::getContext();

			$this->updateStudentlastEducation();

			if ($this->id_sale_agent > 0) {
				$saleAgent = new SaleAgent($this->id_sale_agent);

				if ($saleAgent->sale_commission_amount == 0) {
					return $success;
				}

				$commission = new SaleAgentCommission();
				$commission->id_sale_agent = $saleAgent->id;
				$commission->id_education_session = $this->id_education_session;
				$commission->id_student_education = $this->id;
				$commission->amount = $saleAgent->sale_commission_amount;
				$commission->add();
				$this->id_sale_agent_commission = $commission->id;
				$this->update();
			}

		}

		return $success;
	}

	public function update($nullValues = false) {

		$session = new EducationSession($this->id_education_session);
		if($session->id_education_state < 4) {
			$days = Education::getDaysEducation($this->id_education, $this->id_education_attribute);
			$date = new DateTime($session->session_date);
			$date->modify('+'.$days.' days');
			$this->date_start = $session->session_date;
			$this->date_end = $date->format('Y-m-d');
		}
		else {
			$this->id_education_session = $session->id_education_session;
		}
		if ($this->id_sale_agent > 0) {
			$saleAgent = new SaleAgent($this->id_sale_agent);

			if ($saleAgent->sale_commission_amount == 0) {
				return parent::update($nullValues);
			}

			$idCommission = SaleAgentCommission::getIdCommissionbyIdSession($this->id);

			if (Validate::isUnsignedId($idCommission)) {
				$commission = new SaleAgentCommission($idCommission);
				$commission->id_sale_agent = $saleAgent->id;
				$commission->id_education_session = $this->id_education_session;
				$commission->id_student_education = $this->id;
				if ($this->education_lenghts != '00:00:00') {
					$commission->due =1;
				}
				$commission->update();
			} else {
				$commission = new SaleAgentCommission();
				$commission->id_sale_agent = $saleAgent->id;
				$commission->id_education_session = $this->id_education_session;
				$commission->id_student_education = $this->id;
				$commission->amount = $saleAgent->sale_commission_amount;
				if ($this->education_lenghts != '00:00:00') {
					$commission->due =1;
				}
				$commission->add();
			}

			$this->id_sale_agent_commission = $commission->id;
		}

		return parent::update($nullValues);

	}

	public function updateStudentlastEducation() {

		$student = new Customer($this->id_customer);
		$student->cach_last_education = $this->id;
		$student->update();
	}

	public function sendStudentEmail() {

		$context = Context::getContext();
		$studentEducation = new StudentEducation($this->id);

		$step = $studentEducation->id_student_education_state;

		$template = StudentEducationStep::getTemplate($studentEducation->id_student_education_state, $context->language->id);
		$agentTemplate = StudentEducationStep::getAgentTemplate($studentEducation->id_student_education_state, $context->language->id);

		if (!empty($template['template'])) {
			$topic = StudentEducationStep::getTopic($studentEducation->id_student_education_state, $context->language->id);

			$student = new Student($studentEducation->id_student);
			$customer = new Customer($studentEducation->id_customer);
			$education = new Education($studentEducation->id_education);
			$date_start = $studentEducation->date_start;
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

			$secret_iv = _COOKIE_KEY_;
			$secret_key = _PHP_ENCRYPTION_KEY_;
			$string = $customer->id . '-' . $customer->lastname . $customer->passwd;
			$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
			$linkContract = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitContract&idStudentEducation=' . $studentEducation->id;

			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $template['template'] . '.tpl');
			$tpl->assign([
				'student'          => $customer,
				'studentEducation' => $studentEducation,
				'linkContract'     => $linkContract,
				'is_video_tuto'    => Configuration::get('EPH_ALLOW_VIDEO_TUTO'),
				'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),

			]);
			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
				],
				'to'          => [
					[
						'name'  => $customer->firstname . ' ' . $customer->lastname,
						'email' => $customer->email,
					],
				],

				'subject'     => $topic,
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];

			$result = Tools::sendEmail($postfields);
			$step = new StudentEducationStep($studentEducation->id_student_education_state, $context->language->id);

			if ($step->is_suivie == 1) {
				$suivie = new StudentEducationSuivie();
				$suivie->suivie_date = date('Y-m-d');
				$suivie->id_student_education = $education->id;
				$suivie->id_student_education_state = $step->id;
				$suivie->email_title = $topic;
				$suivie->email_content = $tpl->fetch();
				$suivie->content = $step->suivie;
				$suivie->add();

			}

		}

		if (!empty($agentTemplate['template'])) {

			if ($studentEducation->id_sale_agent > 0) {
				$agent = new SaleAgent($studentEducation->id_sale_agent);

				if ($agent->sale_commission_amount > 0) {
					$topic = StudentEducationStep::getAgentTopic($studentEducation->id_student_education_state, $context->language->id);
					$student = new Customer($studentEducation->id_customer);
					$education = new Education($studentEducation->id_education);
					$date_start = $studentEducation->date_start;
					$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $template['template'] . '.tpl');

					$tpl->assign([
						'student'          => $customer,
						'studentEducation' => $studentEducation,
						'agent_lastname'   => $agent->lastname,
						'agent_firstname'  => $agent->firstname,
						'agent_com'        => $agent->sale_commission_amount,
						'tutoVideo'        => Configuration::get('EPH_TUTO_VIDEO'),

					]);
					$postfields = [
						'sender'      => [
							'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
							'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
						],
						'to'          => [
							[
								'name'  => $agent->firstname . ' ' . $agent->lastname,
								'email' => $agent->email,
							],
						],
						'subject'     => $topic,
						"htmlContent" => $tpl->fetch(),
					];

					$result = Tools::sendEmail($postfields);

				}

			}

		}

	}

	public function getIdPrerequis() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_student_education_prerequis`')
				->from('student_education_prerequis')
				->where('`id_student_education` = ' . $this->id)
		);
	}

	public function getAgent() {

		if ($this->id_sale_agent > 0) {
			$agent = new SaleAgent($this->id_sale_agent);
			return $agent->firstname . ' ' . $agent->lastname;
		}

		return null;
	}

	public function getStudentFirstnamme() {

		$student = new Customer($this->id_customer);
		return $student->firstname;
	}

	public function getStudentLastnamme() {

		$student = new Customer($this->id_customer);
		return $student->lastname;
	}

	public function getStudentEmail() {

		$student = new Customer($this->id_customer);
		return $student->email;
	}

	public function getStudentPhoneMobile() {

		$student = new Customer($this->id_customer);

		$id_address = Address::getFirstCustomerAddressId($student->id);
		$address = new Address((int) $id_address);
		return $address->phone_mobile;
	}

	public function getRatio() {

		if ($this->hours > 0) {
			$lenght = explode(":", $this->education_lenghts);
			$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);
			return round($time * 100 / $this->hours, 2);
		}

		return 0;
	}

	public function getDuration() {

		$duration = explode(":", $this->education_lenghts);
		return vsprintf("%sh %smin %s", $duration);
	}

	public function getState() {

		$context = Context::getContext();
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('student_education_state_lang')
				->where('`id_student_education_state` = ' . $this->id_student_education_state)
				->where('`id_lang` = ' . $context->language->id)
		);
	}

	public function getSupply() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('education_supplies')
				->where('`id_education_supplies` = ' . $this->id_education_supplies)
		);
	}

	public function getDateStart() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`session_date`')
				->from('education_session')
				->where('`id_education_session` = ' . $this->id_education_session)
		);

		$date = new DateTime($date_start);
		return $date->format("d/m/Y");
	}

	public function getSessionDateName() {

		if ($this->id_education_session == 0) {
			$Newdate = DateTime::createFromFormat('Y-m-d', $this->date_start);
			return 'Session du ' . EducationSession::convertinFrench($Newdate->format("d F Y"));
		}

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('education_session')
				->where('`id_education_session` = ' . $this->id_education_session)

		);
	}

	public static function isSessionValidate($id_education_session) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('COUNT(`id_student_education`)')
				->from('student_education')
				->where('`id_education_session` = ' . $id_education_session)
				->where('`id_student_education_state` < 3 ')
				->where('`deleted` = 0')
		);

	}

	public static function getNbAttendees($idEducationSession) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('COUNT(`id_student_education`)')
				->from('student_education')
				->where('`id_education_session` = ' . $idEducationSession)
				->where('`deleted` = 0')
		);
	}

	public static function getSessionTurnover($idEducationSession) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('SUM(`price`)')
				->from('student_education')
				->where('`id_education_session` = ' . $idEducationSession)
				->where('`id_student_education_state` >= 4')
				->where('`deleted` = 0')
		);
	}

	public static function getSessionExpectedTurnover($idEducationSession) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('SUM(`price`)')
				->from('student_education')
				->where('`id_education_session` = ' . $idEducationSession)
				->where('`id_student_education_state` <= 3')
				->where('`deleted` = 0')
		);
	}

	public static function getSessionbyIdSession($idEducationSession) {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $idEducationSession)
				->where('`deleted` = 0')
		);
	}

	public static function getSessionDetails($idEducationSession) {

		$context = Context::getContext();
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_student_education')
				->from('student_education')
				->where('`id_education_session` = ' . $idEducationSession)
				->where('`deleted` = 0')

		);

		$details = [];

		if (is_array($sessions) && count($sessions)) {

			foreach ($sessions as $session) {
				$session = new StudentEducation($session['id_student_education']);

				if ($session->id_attribute == 0) {

					$sessionDetails = Db::getInstance()->executeS(
						(new DbQuery())
							->select('ses.name as state, es.session_date, es.name as sessionName, se.`id_student_education`, se.price, se.notes, gl.name as title, s.firstname, s.lastname, s.birthname, s.email, s.phone, s.phone_mobile, s.address_street, s.address_city, s.address_zipcode, e.days, e.hours, t.rate')
							->from('student_education', 'se')
							->leftJoin('student_education_state_lang', 'ses', 'ses.`id_student_education_state` = se.`id_student_education_state` AND ses.`id_lang` = ' . $context->language->id)
							->leftJoin('education_session', 'es', 'es.id_education_session = se.id_education_session')
							->leftJoin('student', 's', 's.id_student = se.id_student')
							->leftJoin('gender_lang', 'gl', 'gl.id_gender = s.id_gender AND gl.id_lang = ' . $context->language->id)
							->leftJoin('education', 'e', 'e.id_education = se.id_education')
							->leftJoin('tax_rules_group', 'tl', 'tl.`id_tax_rules_group` = e.`id_tax_rules_group`')
							->leftJoin('tax', 't', 't.`id_tax` = tl.`id_tax_rules_group`')
							->where('se.`id_education_session` = ' . $session->id_education_session)
							->where('se.`id_student_education` = ' . $session->id)
					);
				} else {

					$sessionDetails = Db::getInstance()->executeS(
						(new DbQuery())
							->select('ses.name as state, es.session_date, es.name as sessionName, se.`id_student_education`, se.price, se.notes, gl.name as title, s.firstname, s.lastname, s.birthname, s.email, s.phone, s.phone_mobile, s.address_street, s.address_city, s.address_zipcode, e.days, e.hours, t.rate')
							->from('student_education', 'se')
							->leftJoin('student_education_state_lang', 'ses', 'ses.`id_student_education_state` = se.`id_student_education_state` AND ses.`id_lang` = ' . $context->language->id)
							->leftJoin('education_session', 'es', 'es.id_education_session = se.id_education_session')
							->leftJoin('student', 's', 's.id_student = se.id_student')
							->leftJoin('gender_lang', 'gl', 'gl.id_gender = s.id_gender AND gl.id_lang = ' . $context->language->id)
							->leftJoin('education', 'e', 'e.id_education = se.id_education')
							->leftJoin('tax_rules_group', 'gl', 'tl.`id_tax_rules_group` = e.`id_tax_rules_group`')
							->leftJoin('tax', 't', 't.`id_tax` = tl.`id_tax_rules_group`')
							->leftJoin('education_attribute', 'ea', 'ea.id_education = se.id_education AND ea.id_education_attribute = se.id_education_attribute')
							->where('se.`id_education_session` = ' . $session->id_education_session)
							->where('se.`id_student_education` = ' . $session->id)
					);
				}

				foreach ($sessionDetails as &$sessionDetail) {
					$price = $sessionDetail['price'];
					$sessionDetail['priceWTax'] = $price * (1 + $sessionDetail['rate'] / 100);
					$sessionDetail['price'] = round($sessionDetail['price'], 2);
				}

				$details[] = $sessionDetails[0];

			}

		}

		return $details;
	}

	public static function getFilledSession() {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('DISTINCT(`id_education_session`)')
				->from('student_education')
				->where('`deleted` = 0')
		);

	}

	public static function getFilledSessionbyId($id_education_session) {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('DISTINCT(`id_education_session`)')
				->from('student_education')
				->where('`deleted` = 0')
				->where('`deleted` = 0')
		);

	}

	public static function indexVDI() {

		$context = Context::getContext();
		$commissions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('sale_agent_commission')
				->where('due = 0')
		);

		foreach ($commissions as $commission) {

			$studentEducation = new StudentEducation($commission['id_student_education']);
			$time = (int) str_replace(":", "", $studentEducation->education_lenghts);

			if ($time > 0) {
				$comAgent = new SaleAgentCommission($commission['id_sale_agent_commission']);
				$comAgent->due = 1;
				$comAgent->update();
			}

		}

		$today = date("Y-m-d");

		$date = new DateTime($today);
		$date->modify('-6 days');
		$dateAlertVDI = $date->format('Y-m-d');

		$sessionCheck = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateAlertVDI . '\'')
		);

		if (isset($sessionCheck) && $sessionCheck > 0) {
			$session = new EducationSession($sessionCheck);

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')
			);

			foreach ($sessionDetails as $sessionDetail) {
				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
				$connexionLenght = (int) str_replace(":", "", $studentEducation->education_lenghts);

				if ($connexionLenght > 0) {
					continue;
				}

				if ($studentEducation->id_sale_agent > 0) {
					$agent = new SaleAgent($studentEducation->id_sale_agent);

					if ($agent->sale_commission_amount > 0) {
						$mailAgent[$agent->id][] = [
							'studentEducation' => $studentEducation->id,
						];
					}

				}

			}

			if (is_array($mailAgent) && count($mailAgent)) {

				foreach ($mailAgent as $key => $values) {

					$html = '';
					$agent = new SaleAgent($key);
					$i = 0;

					foreach ($values as $value) {
						$idEducationSession = $value['studentEducation'];
						$studentEducation = new StudentEducation($idEducationSession);
						$student = new Customer($studentEducation->id_customer);
						$id_address = Address::getFirstCustomerAddressId($student->id);
						$address = new Address((int) $id_address);

						$html .= '<tr>
							<td>' . $student->firstname . '</td>
							<td>' . $student->lastname . '</td>
							<td>' . $address->phone_mobile . '</td>
							<td>' . $studentEducation->sessionName . '</td>
							<td>' . $studentEducation->reference . '</td>
							<td>' . $studentEducation->first_connection . '</td>
							<td>' . $studentEducation->duration . '</td>
							</tr>';
						$i++;

					}

					$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationAgentAlert.tpl');
					$tpl->assign([

						'session_name'    => $studentEducation->sessionName,
						'agent_lastname'  => $agent->lastname,
						'agent_firstname' => $agent->firstname,
						'html'            => $html,
						'count'           => $i,
						'date_start'      => $studentEducation->date_start,
						'date_contrat'    => $studentEducation->date_contrat,

					]);
					$postfields = [
						'sender'      => [
							'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
							'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
						],
						'to'          => [
							[
								'name'  => $agent->firstname . ' ' . $agent->lastname,
								'email' => $agent->email,
							],
						],

						'subject'     => 'Rapport de non connexion  ' . $studentEducation->sessionName,
						"htmlContent" => $tpl->fetch(),
					];

					$result = Tools::sendEmail($postfields);

				}

			}

		}

	}

	public static function requireVdiInvoice() {

		$context = Context::getContext();
		$commissions = SaleAgentCommission::getSaleAgentDueStatement();

		$titles = ['Session', 'Etudiant', 'Montant dde commission'];
		$dataIndx = ['sessionName', 'student', 'amount'];

		$column = chr(64 + count($titles));
		$titleStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$vdiStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$totalStyle = [
			'font'    => [
				'bold' => true,
			],
			'borders' => [
				'bottom' => [
					'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
				],
			],
		];
		$corpStyle = [
			'font' => [
				'bold' => false,
			],

		];

		foreach ($commissions as $key => $commission) {

			$vdi = new SaleAgent($key);
			$fileAttachement = null;
			$spreadsheet = new Spreadsheet();
			$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

			$spreadsheet->getActiveSheet()->setTitle('Situation agents commerciaux');

			foreach ($titles as $key => $value) {
				$key++;
				$letter = chr(64 + $key);

				$spreadsheet->setActiveSheetIndex(0)
					->setCellValue($letter . '1', $value);
			}

			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . '1')->getFont()->setSize(14);
			$i = 2;

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Commission due pour ' . $vdi->firstname . ' ' . $vdi->lastname);
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getFont()->setSize(14);
			$i++;
			$html = '';

			foreach ($commission['commissions'] as $education) {
				$studentEducation = new StudentEducation($education['id_student_education']);
				$html .= '<tr>
							<td>' . $studentEducation->firstname . '</td>
							<td>' . $studentEducation->lastname . '</td>
							<td>' . $studentEducation->reference . '</td>
							<td>' . $studentEducation->first_connection . '</td>
							<td>' . $studentEducation->duration . '</td>
							<td>' . $education['amount'] . '</td>
							</tr>';

				foreach ($dataIndx as $k => $title) {

					if (array_key_exists($title, $education)) {
						$k++;
						$letter = chr(64 + $k);
						$spreadsheet->setActiveSheetIndex(0)
							->setCellValue($letter . $i, $education[$title]);

						$spreadsheet->getActiveSheet()->getColumnDimension($letter)->setAutoSize(true);
						$spreadsheet->getActiveSheet()->getStyle($letter . $i)->applyFromArray($corpStyle);

					}

				}

				$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getFont()->setSize(12);
				$i++;
			}

			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Total à facturer à ' . Configuration::get('PS_SHOP_NAME') . ' : ' . $commission['totalDue'] . ' €uros HT');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);

			$fileSave = new Xlsx($spreadsheet);
			$fileSave->save(_PS_EXPORT_DIR_ . 'Commission pour ' . $vdi->firstname . ' ' . $vdi->lastname . '.xlsx');

			$fileAttachement[] = [
				'content' => chunk_split(base64_encode(file_get_contents(_PS_EXPORT_DIR_ . 'Commission pour ' . $vdi->firstname . ' ' . $vdi->lastname . '.xlsx'))),
				'name'    => 'Vos commissions à nous facturer.xlsx',
			];

			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationAgentInvoice.tpl');
			$tpl->assign([
				'agent_lastname'  => $vdi->lastname,
				'agent_firstname' => $vdi->firstname,
				'html'            => $html,
				'totalDue'        => $commission['totalDue'],

			]);

			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
				],
				'to'          => [
					[
						'name'  => $vdi->firstname . ' ' . $vdi->lastname,
						'email' => $vdi->email,
					],
				],
				'cc'          => [
					[
						'name'  => "Service  Comptabilité " . Configuration::get('PS_SHOP_NAME'),
						'email' => Configuration::get('PS_SHOP_ADMIN_EMAIL'),
					],
					[
						'name'  => "Service  Comptabilité " . Configuration::get('PS_SHOP_NAME'),
						'email' => 'comptabilite@' . Configuration::get('PS_SHOP_URL'),
					],
				],
				'subject'     => 'Merci de nous établir une facture d‘un montant de  ' . $commission['totalDue'] . ' €uros HT',
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];
			$result = Tools::sendEmail($postfields);
		}

	}

	public static function getStudentEducationsbyIdStudent($idStudent, $identifiant) {

		$sql = new DbQuery();
		$sql->select('id_student_education');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`id_student` = ' . (int) $idStudent);
		$sql->where('`identifiant` LIKE \'' . $identifiant . '\' ');
		$sql->where('`deleted` = 0');
		$sql->orderBy("id_student_education DESC");

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
	}

	public static function getLastAlterIndex() {

		$platform = New Platform(1);
		$data_array = [
			"orderBy" => "idGroupe",
		];

		$token = $platform->plarform_token;
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setHeader('token', $token);
		$curl->post($platform->webservice_link.'nj/groups/getAllInList', Tools::jsonEncode($data_array));
		$response = $curl->response;

		$groups = [];

		foreach ($response as $group) {

			if ($group["idGroupe"] == 3) {
				continue;
			}

			$groupName = explode("-", $group["libelleGroupe"]);

			if (Validate::isUnsignedId($groupName[0])) {
				$groups[] = $groupName[0];
			}

		}

		return max($groups) + 1;
	}

	public static function createAlterGroup($libelleGroupe) {

		$platform = New Platform(1);
		$data_array = [
			"libelleGroupe" => $libelleGroupe,
			"groupeProtege" => 0,
			"idParent"      => 3,

		];

		$token = $platform->plarform_token;
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setHeader('token', $token);
		$curl->post($platform->webservice_link.'nj/groups/create', Tools::jsonEncode($data_array));
		$response = $curl->response;

		return $response['idGroupe'];

	}

	public static function createAlterUser($dataUser) {

		$platform = New Platform(1);
		$token = $platform->plarform_token;
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setHeader('token', $token);
		$curl->post($platform->webservice_link.'nj/users/create', Tools::jsonEncode($dataUser));
		return $curl->response;

	}

	public static function createCloudUser($datauser) {

		$platform = New Platform(3);

		if (!($platform->has_websercice)) {
			return true;
		}

		$curl = new Curl();
		$curl->setHeader('Content-Type', 'multipart/form-data');
		$curl->setHeader('Access-Token', $platform->plarform_token);
		$curl->get('https://cloudelearning.eu/php5/restapi/utilisateur/inscription', $dataUser);
		$response = $curl->response;
		$result = json_encode($response);
		return json_decode($result, true);

	}

	public static function synchCloudLearning($idSession) {

		$platform = New Platform(3);

		if (!($platform->has_websercice)) {
			return true;
		}

		$session = new EducationSession($idSession);

		$date_start = $session->session_date;
		$date = new DateTime($date_start);
		$date->modify('+1 year');
		$date_end = $date->format('Y-m-d');

		$context = Context::getContext();

		$educations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.id_student_education, st.firstname, st.lastname, st.email,  s.identifiant, s.passwd_link, gl.name as title, eal.name, ea.id_plateforme as idSession')
				->from('student_education', 's')
				->leftJoin('customer', 'st', 'st.`id_customer` = s.`id_customer`')
				->leftJoin('education', 'e', 'a.`id_education` = s.`id_education`')
				->leftJoin('education_attribute', 'ea', 'ea.`id_education_attribute` = s.`id_education_attribute` AND ea.`id_education` = s.`id_education`')
				->leftJoin('platform', 'p', 'p.`id_platform` = e.`id_platform`')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = st.`id_gender` AND gl.id_lang = ' . $context->language->id)
				->leftJoin('education_attribute_lang', 'eal', 'eal.`id_education_attribute` = s.`id_education_attribute` AND eal.`id_lang` = ' . $context->language->id)
				->where('s.deleted = 0')
				->where('p.id_platform = ' . $platform->id)
				->where('s.id_platform = 0')
				->where('s.id_student_education_state > 2')
				->where('s.id_education_session = ' . (int) $session->id)
				->orderBy('s.id_education_attribute')
		);

		foreach ($educations as $stagiaire) {
			$studentEducation = new StudentEducation($stagiaire['id_student_education']);
			$student = new Customer($studentEducation->id_customer);
			$dataUser = [
				'nom'        => $student->lastname,
				'prenom'     => $student->firstname,
				'civilite'   => $student->id_gender,
				'login'      => $student->email,
				'pwd'        => $student->password,
				'email'      => $student->email,
				'date_debut' => $date_start,
				'date_fin'   => $date_end,
			];

			$response = StudentEducation::createCloudUser($datauser);

			$studentEducation->id_platform = $response['guid'];
			$studentEducation->update();

			$modules = EducationModule::getModulesbyEducationId($studentEducation->id_education, $studentEducation->id_education_attribute);

			$dataUser = [
				'utilisateur'      => $studentEducation->id_platform,
				'modules'          => $modules,
				'affect_eval_pre'  => 1,
				'affect_eval_post' => 1,
			];

			$curl = new Curl();
			$curl->setHeader('Content-Type', 'multipart/form-data');
			$curl->setHeader('Access-Token', $platform->plarform_token);
			$curl->post('https://cloudelearning.eu/php5/restapi/catalogue/affect-formations', $dataUser);
			$response = $curl->response;

		}

	}

	public static function syncAlterSession($idSession) {

		$session = new EducationSession($idSession);

		$platform = New Platform(1);

		$month = [];
		$month["January"] = "JANVIER";
		$month["February"] = "FEVRIER";
		$month["March"] = "MARS";
		$month["April"] = "AVRIL";
		$month["May"] = "MAI";
		$month["June"] = "JUIN";
		$month["July"] = "JUILLET";
		$month["August"] = "AOUT";
		$month["September"] = "SEPTEMBRE";
		$month["October"] = "OCTOBRE";
		$month["November"] = "NOVEMBRE";
		$month["December"] = "DECEMBRE";
		$file = fopen("testsyncAlterSession.txt", "w");
		$sessionIndex = StudentEducation::getLastAlterIndex();
		fwrite($file, $sessionIndex . PHP_EOL);

		$AlterSession = $sessionIndex . "-" . date("Y") . ' ';
		$date = new DateTime($session->session_date);
		$dateStart = $date->format("d/m/Y");

		$dateIsoStart = $date->format("Y-m-d H:i:s");
		$AlterSession .= $date->format("d") . ' ';
		$AlterSession .= $month[$date->format("F")] . ' AU ';
		$date->modify('+30 days');
		$AlterSession .= $date->format("d") . ' ';
		$AlterSession .= $month[$date->format("F")];
		$date->modify('-30 days');
		$date->modify('+1 year');
		$date_end = $date->format('d/m/Y');
		$dateIsoEnd = $date->format("Y-m-d H:i:s");
		
		if ($session->id_alter == 0) {
			$session->id_alter = StudentEducation::createAlterGroup($AlterSession);
			$session->update();
		}

		$context = Context::getContext();
		
		

		$educations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.id_student_education, st.firstname, st.lastname, st.email, s.identifiant, s.passwd_link, gl.name as title, eal.name, ea.id_plateforme as idSession')
				->from('student_education', 's')
				->leftJoin('customer', 'st', 'st.`id_customer` = s.`id_customer`')
				->leftJoin('education', 'e', 'e.`id_education` = s.`id_education`')
				->leftJoin('education_attribute', 'ea', 'ea.`id_education_attribute` = s.`id_education_attribute` AND ea.`id_education` = s.`id_education`')
				->leftJoin('platform', 'p', 'p.`id_platform` = e.`id_platform`')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = st.`id_gender` AND gl.id_lang = ' . $context->language->id)
				->leftJoin('education_attribute_lang', 'eal', 'eal.`id_education_attribute` = s.`id_education_attribute` AND eal.`id_lang` = ' . $context->language->id)
				->where('s.deleted = 0')
				->where('p.id_platform = ' . $platform->id)
				->where('s.id_student_education_state > 2')
				->where('s.id_education_session = ' . (int) $session->id)
				->orderBy('s.id_education_attribute')
		);

		foreach ($educations as $stagiaire) {
			$studentEducation = new StudentEducation($stagiaire['id_student_education']);

			if ($studentEducation->id_platform == 0) {
				$user = [
					"civilite"       => $stagiaire['title'],
					"nom"            => $stagiaire['lastname'],
					"prenom"         => $stagiaire['firstname'],
					"email"          => $stagiaire['email'],
					"matricule"      => $stagiaire['id_student_education'],
					"utilProtege"    => 0,
					"compteActif"    => 1,
					"idGroupe"       => $session->id_alter,
					"identifiant"    => $stagiaire['identifiant'],
					"actifApartirDe" => $dateIsoStart,
					"actifJusqua"    => $dateIsoEnd,
					'actifApartirDe' => $dateIsoStart,
					"actifJusqua"    => $dateIsoEnd,
				];

				$response = StudentEducation::createAlterUser($user);
				fwrite($file, print_r($response, true) . PHP_EOL);
				$studentEducation->id_platform = $response['idUtilisateur'];
				$studentEducation->passwd_link = $response['motDePasse'];
				$idPlateforms = explode(",", $stagiaire['idSession']);

				if (is_array($idPlateforms) && count($idPlateforms)) {

					foreach ($idPlateforms as $idPlateform) {

						$dataSession = [
							"idUtilisateur" => $studentEducation->id_platform,
							"idSession"     => $idPlateform,
							"dateDebut"     => $dateIsoStart,
							"dateFin"       => $dateIsoEnd,
						];
						$token = $platform->plarform_token;
						$curl = new Curl();
						$curl->setDefaultJsonDecoder($assoc = true);
						$curl->setHeader('Content-Type', 'application/json');
						$curl->setHeader('token', $token);
						$curl->post($platform->webservice_link.'nj/affectation/createSessionToUser', Tools::jsonEncode($dataSession));
					}

				}

				$studentEducation->update();
			}

		}

	}

	public static function synchroAlterCampus() {

		$platform = new Platform(1);
		$data_array = [];
		$students = [];
		$today = date('Y-m-d');
		$date1 = date_create($today);
		$token = $platform->plarform_token;
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setHeader('token', $token);
		$curl->post($platform->webservice_link.'nj/users/getAllInList', Tools::jsonEncode($data_array));
		$response = $curl->response;

		foreach ($response as &$user) {
			$date = $user['actifApartirDe'];
			$user['actifApartirDe'] = date('Y-m-d', strtotime($date));
			$date2 = date_create($user['actifApartirDe']);
			$diff = date_diff($date1, $date2);
			$days = $diff->format("%a");

			if ($today > $user['actifApartirDe'] && $days < 60) {
				$students[] = $user;
			}

		}

		foreach ($students as $student) {

			$actifApartirDe = $student['actifApartirDe'];
			$identifiant = $student["idUtilisateur"];

			if ((int) $student['matricule'] > 0) {

				$idStudentEducation = $student['matricule'];

			} else {
				$idStudent = Student::getStudentbyEmail($student['email']);
				$idStudentEducation = StudentEducation::getStudentEducationsbyIdStudent($idStudent, $identifiant);

				if ($idStudentEducation > 0) {
					$dataUtilisateur = [
						'idUtilisateur' => $student['idUtilisateur'],
						'matricule'     => $idStudentEducation,
					];
					$curl = new Curl();
					$curl->setDefaultJsonDecoder($assoc = true);
					$curl->setHeader('Content-Type', 'application/json');
					$curl->setHeader('token', $token);
					$curl->post($platform->webservice_link.'nj/users/update', Tools::jsonEncode($dataUtilisateur));
					$response = $curl->response;

				}

			}

			if ($idStudentEducation > 0) {
				$dataUtilisateur = [
					'idUtilisateur' => $student['idUtilisateur'],
				];
				$curl = new Curl();
				$curl->setDefaultJsonDecoder($assoc = true);
				$curl->setHeader('Content-Type', 'application/json');
				$curl->setHeader('token', $token);
				$curl->post($platform->webservice_link.'nj/resultats/autoFormationDetailsUtilisateur', Tools::jsonEncode($dataUtilisateur));
				$UserResponse = $curl->response;
				$education_lenghts = '00:00:00';

				if (isset($UserResponse['resultat'][0]['tempsPasseTotal'])) {
					$education_lenghts = $UserResponse['resultat'][0]['tempsPasseTotal'];
					$pos = strpos($education_lenghts, 'min');

					if ($pos === false) {
						$education_lenghts = str_replace('h', ':', $education_lenghts) . ':00';
					}

				}
				
				if($education_lenghts != '00:00:00') {					
					$dateDebut = $UserResponse['resultat'][0]['tabSessions'][0]['dateDebut'];
					$date = date_create_from_format('Y-m-d H:i:s', $dateDebut);
					$firstConnection = $date->format('Y-m-d');
				} else {
					$firstConnection = '0000-00-00';
				}

				$studentEducation = new StudentEducation($idStudentEducation);

				if ($firstConnection != '0000-00-00' && $studentEducation->id_student_education_state < 7) {
					StudentEducation::changeEducationStepId($studentEducation->id, 7);
					$studentEducation->id_student_education_state = 7;
				}

				$studentEducation->id_platform = $student['idUtilisateur'];
				$studentEducation->first_connection = $firstConnection;
				$studentEducation->education_lenghts = $education_lenghts;
				$studentEducation->update();
			}

		}

		return true;

	}

	public static function indexEducation() {

		$context = Context::getContext();
		$today = date("Y-m-d");

		$date = new DateTime($today);
		$date->modify('-5 days');
		$remindDate = $date->format('Y-m-d');

		$date = new DateTime($today);
		$date->modify('-30 days');
		$dateEnd = $date->format('Y-m-d');
		
		$date = new DateTime($today);
		$date->modify('-32 days');
		$dateInvoice = $date->format('Y-m-d');

		$date = new DateTime($today);
		$date->modify('-10 days');
		$date10 = $date->format('Y-m-d');

		
		$lastOpenEducation = EducationSession::getLastEducatinOpen();
		$lastSession = $lastOpenEducation['session_date'];

		$date = new DateTime($today);
		$date->modify('-7 days');
		$dateCheck = $date->format('Y-m-d');

		$datelastSession = date_create($lastOpenEducation['session_date']);
		$lastIdSession = $lastOpenEducation['id_education_session'];

		$sessionCheck = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateCheck . '\'')
		);

		if (isset($sessionCheck) && $sessionCheck > 0) {
			StudentEducation::proceedEducationCheck($sessionCheck);
		}

		
		

		$sessionEnd = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateEnd . '\'')
				->where('`sessionEnded` = 0')
		);

		if (isset($sessionEnd) && $sessionEnd > 0) {
			StudentEducation::proceedSessionEnd($sessionEnd);
		}
		
		$sessionInvoice = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateInvoice . '\'')
				->where('`sessionEnded` = 1')
		);
		
		if (isset($sessionInvoice) && $sessionInvoice > 0) {
			StudentEducation::generateInvoice($sessionInvoice);
		}


	}

	public static function proceedEducationRegistration() {

		$context = Context::getContext();
		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('+16 days');
		$dateLimit = $date->format('Y-m-d');

		$sessionCheck = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateLimit . '\'')
		);

		if (isset($sessionCheck) && $sessionCheck > 0) {

			$session = new EducationSession($sessionCheck);

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')
					->where('`id_student_education_state` = 2')
			);

			foreach ($sessionDetails as $sessionDetail) {

				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);

				$student = new Customer($studentEducation->id_customer);

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationRemind.tpl');
				$tpl->assign([
					'title'           => $student->title,
					'lastname'        => $student->lastname,
					'birthname'       => $student->birthname,
					'firstname'       => $student->firstname,
					'email'           => $student->email,
					'password'        => $student->password,
					'education_name'  => $studentEducation->name,
					'date_start'      => $studentEducation->date_start,
					'date_end'        => $studentEducation->date_end,
					'last_days'       => $studentEducation->days,
					'last_hours'      => $studentEducation->hours,
					'courseLink'      => $studentEducation->courseLink,
					'reservationLink' => $studentEducation->reservationLink,
					'supplyName'      => $studentEducation->supplyName,
					'date_limit'      => $studentEducation->date_limit,
					'identifiant'     => $studentEducation->identifiant,
					'passwd_link'     => $studentEducation->passwd_link,
					'sessionId'       => $studentEducation->id,
				]);

				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
					],
					'to'          => [
						[
							'name'  => $student->firstname . ' ' . $student->lastname,
							'email' => $student->email,
						],
					],

					'subject'     => 'Rappel, vous devez vous inscrire pour votre formation en ' . $studentEducation->name,
					"htmlContent" => $tpl->fetch(),
				];

				$result = Tools::sendEmail($postfields);

				$context->smarty->assign(
					[
						'student'          => $student,
						'studentEducation' => $studentEducation,
					]
				);

				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'educationLastDay.tpl');

				$recipient = $student->phone_mobile;
				Tools::sendSms($recipient, $content);

				if ($studentEducation->id_sale_agent > 0) {

					$agent = new SaleAgent($studentEducation->id_sale_agent);

					if ($agent->sale_commission_amount > 0) {

						$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationAgentRemind.tpl');

						$tpl->assign([
							'title'           => $student->title,
							'lastname'        => $student->lastname,
							'birthname'       => $student->birthname,
							'firstname'       => $student->firstname,
							'phone_mobile'    => $student->phone_mobile,
							'email'           => $student->email,
							'password'        => $student->password,
							'education_name'  => $studentEducation->name,
							'date_start'      => $studentEducation->date_start,
							'date_end'        => $studentEducation->date_end,
							'last_days'       => $studentEducation->days,
							'last_hours'      => $studentEducation->hours,
							'courseLink'      => $studentEducation->courseLink,
							'reservationLink' => $studentEducation->reservationLink,
							'supplyName'      => $studentEducation->supplyName,
							'date_limit'      => $studentEducation->date_limit,
							'identifiant'     => $studentEducation->identifiant,
							'passwd_link'     => $studentEducation->passwd_link,
							'sessionId'       => $studentEducation->id,
							'agent_lastname'  => $agent->lastname,
							'agent_firstname' => $agent->firstname,
							'agent_com'       => $agent->sale_commission_amount,

						]);
						$postfields = [
							'sender'      => [
								'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
								'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
							],
							'to'          => [
								[
									'name'  => $agent->firstname . ' ' . $agent->lastname,
									'email' => $agent->email,
								],
							],

							'subject'     => 'Rappel, votre étudiant à une formation en ' . $studentEducation->name . ' doit s’inscrire',
							"htmlContent" => $tpl->fetch(),
						];

						$result = Tools::sendEmail($postfields);
					}

				}

				$studentEducation->educLaunch = 1;
				$studentEducation->update();

			}

			
		}

	}

	public static function proceedEducationLaunch() {

		$context = Context::getContext();

		$today = date("Y-m-d");

		$date = new DateTime($today);
		$dateToday = date_create($today);
		$date->modify('+1 days');
		$dateLaunch = $date->format('Y-m-d');

		$sessionLaunch = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateLaunch . '\'')
		);

		if (isset($sessionLaunch) && $sessionLaunch > 0) {

			$session = new EducationSession($sessionLaunch);

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')
					->where('`educLaunch` = 0')
					->where('`id_student_education_state` = 4')
			);
			$fileAttachement[] = [
				'content' => chunk_split(base64_encode(file_get_contents(_PS_IMG_DIR_ . 'deontologie.pdf'))),
				'name'    => 'Engagement - Code de déontologie.pdf',
			];
			$fileAttachement[] = [
				'content' => chunk_split(base64_encode(file_get_contents(_PS_IMG_DIR_ . 'reglement.pdf'))),
				'name'    => 'Engagement - Reglement Interieur.pdf',
			];

			foreach ($sessionDetails as $sessionDetail) {

				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);

				if ($studentEducation->educLaunch == 1) {
					continue;
				}

				$student = new Customer($studentEducation->id_customer);

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationLaunch.tpl');

				$tpl->assign([
					'student'          => $student,
					'studentEducation' => $studentEducation,
					'referent'         => Configuration::get('EPH_HANDICAP_REFERENT'),
				]);

				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $student->firstname . ' ' . $student->lastname,
							'email' => $student->email,
						],
					],
					'subject'     => 'Votre formation ' . $studentEducation->name . ' va bientôt commencer',
					"htmlContent" => $tpl->fetch(),
					'attachment'  => $fileAttachement,
				];

				$result = Tools::sendEmail($postfields);

				$context->smarty->assign(
					[
						'student'          => $student,
						'studentEducation' => $studentEducation,
					]
				);

				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'educationLaunch.tpl');

				$recipient = $student->phone_mobile;
				Tools::sendSms($recipient, $content);

				$studentEducation->educLaunch = 1;
				$studentEducation->update();
				StudentEducation::changeEducationStepId($studentEducation->id, 5);

			}

			$session->educLaunch = 1;
			$session->update();
		}

	}

	public static function proceedEducationCheck($idSession) {

		$context = Context::getContext();

		$session = new EducationSession($idSession);

		$sessionDetails = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $session->id)
				->where('`deleted` = 0')
				->where('`isLaunch` = 1')
		);

		foreach ($sessionDetails as $sessionDetail) {
			$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
			$connexionLenght = (int) str_replace(":", "", $studentEducation->education_lenghts);

			if ($connexionLenght > 0) {
				continue;
			}

			if ($studentEducation->id_sale_agent > 0) {
				$agent = new SaleAgent($studentEducation->id_sale_agent);

				if ($agent->sale_commission_amount > 0) {
					$mailAgent[$agent->id][] = [
						'studentEducation' => $studentEducation->id,
					];
				}

			}

			$student = new Customer($studentEducation->id_customer);

			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/reminder.tpl');

			$tpl->assign([
				'title'           => $student->title,
				'lastname'        => $student->lastname,
				'birthname'       => $student->birthname,
				'firstname'       => $student->firstname,
				'email'           => $student->email,
				'password'        => $student->password,
				'education_name'  => $studentEducation->name,
				'date_start'      => $studentEducation->date_start,
				'date_end'        => $studentEducation->date_end,
				'last_days'       => $studentEducation->days,
				'last_hours'      => $studentEducation->hours,
				'courseLink'      => $studentEducation->courseLink,
				'reservationLink' => $studentEducation->reservationLink,
				'supplyName'      => $studentEducation->supplyName,
				'date_contrat'    => $studentEducation->date_contrat,
				'identifiant'     => $studentEducation->identifiant,
				'passwd_link'     => $studentEducation->passwd_link,
				'sessionId'       => $studentEducation->id,
			]);

			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				],
				'to'          => [
					[
						'name'  => $student->firstname . ' ' . $student->lastname,
						'email' => $student->email,
					],
				],

				'subject'     => 'Pensez à vous connecter à votre formation du ' . $studentEducation->date_start . ' avec LDF',
				"htmlContent" => $tpl->fetch(),
			];

			$result = Tools::sendEmail($postfields);
		}

		if (is_array($mailAgent) && count($mailAgent)) {

			foreach ($mailAgent as $key => $values) {

				$html = '';
				$agent = new SaleAgent($key);
				$i = 0;

				foreach ($values as $value) {
					$idEducationSession = $value['studentEducation'];
					$studentEducation = new StudentEducation($idEducationSession);
					$student = new Customer($studentEducation->id_customer);
					$id_address = Address::getFirstCustomerAddressId($student->id);
					$address = new Address((int) $id_address);
					$html .= '<tr>
						<td>' . $student->firstname . '</td>
						<td>' . $student->lastname . '</td>
						<td>' . $address->phone_mobile . '</td>
						<td>' . $studentEducation->sessionName . '</td>
						<td>' . $studentEducation->reference . '</td>
						</tr>';
					$i++;

				}

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/educationAgentRemind.tpl');
				$tpl->assign([

					'session_name'    => $studentEducation->sessionName,
					'agent_lastname'  => $agent->lastname,
					'agent_firstname' => $agent->firstname,
					'html'            => $html,
					'count'           => $i,
					'date_start'      => $studentEducation->date_start,
					'date_contrat'    => $studentEducation->date_contrat,

				]);
				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $agent->firstname . ' ' . $agent->lastname,
							'email' => $agent->email,
						],
					],
					'subject'     => 'Rapport de non connexion  ' . $studentEducation->sessionName,
					"htmlContent" => $tpl->fetch(),
				];

				$result = Tools::sendEmail($postfields);

			}

		}

	}

	public static function proceedSessionStart() {

		$context = Context::getContext();

		$today = date("Y-m-d");

		$sessionToday = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $today . '\'')
		);

		$step = new StudentEducationStep(6, $context->language->id);

		if (isset($sessionToday) && $sessionToday > 0) {

			$session = new EducationSession($sessionToday);

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')
					->where('`isLaunch` = 0')
					->where('`id_student_education_state` > 3')
					->where('`id_student_education_state` != 10')
			);

			foreach ($sessionDetails as $sessionDetail) {

				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
				$student = new Customer($studentEducation->id_customer);

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/convocation.tpl');

				$tpl->assign([
					'student'          => $student,
					'studentEducation' => $studentEducation,
					'referent'         => Configuration::get('EPH_HANDICAP_REFERENT'),
				]);

				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $student->firstname . ' ' . $student->lastname,
							'email' => $student->email,
						],
					],

					'subject'     => 'Convocation à votre formation du ' . $studentEducation->date_start,
					"htmlContent" => $tpl->fetch(),
				];

				$result = Tools::sendEmail($postfields);

				$context->smarty->assign(
					[
						'student'          => $student,
						'studentEducation' => $studentEducation,
					]
				);

				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'convocation.tpl');

				$recipient = $student->phone_mobile;
				Tools::sendSms($recipient, $content);

				$studentEducation->isLaunch = 1;
				$studentEducation->id_student_education_step = 6;
				$studentEducation->update();

				$suivie = new StudentEducationSuivie();
				$suivie->suivie_date = date('Y-m-d');
				$suivie->id_student_education = $studentEducation->id;
				$suivie->id_student_education_state = $step->id;
				$suivie->email_title = 'Convocation à votre formation du ' . $studentEducation->date_start . ' avec LDF';
				$suivie->email_content = $tpl->fetch();
				$suivie->content = $step->suivie;
				$suivie->add();

			}

		}

	}

	public static function proceedSessionJustStart() {

		$context = Context::getContext();
		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-3 days');

		$justStart = $date->format('Y-m-d');

		$sessionJustStart = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $justStart . '\'')
		);

		if (isset($sessionJustStart) && $sessionJustStart > 0) {

			$smsContent = '';

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')

			);

			foreach ($sessionDetails as $sessionDetail) {
				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
				$i = 0;
				$content = '';

				if ($studentEducation->education_lenghts == '00:00:00') {
					$i++;
					$student = new Customer($studentEducation->id_customer);
					$context->smarty->assign(
						[
							'student'          => $student,
							'studentEducation' => $studentEducation,
						]
					);

					$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'relance_connxion.tpl');
					$smsContent = $smsContent . '<br><p>' . $student->firstname . ' ' . $student->lastname . ' ' . $student->email . '</p><p>' . $content . '</p>';
					$recipient = $student->phone_mobile;
					Tools::sendSms($recipient, $content);

				}

			}

			if ($i > 0) {
				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/relance_connexion.tpl');
				$tpl->assign([
					'content' => $smsContent,
				]);
				$postfields = [
					'sender'      => [
						'name'  => "Automation CRM",
						'email' => 'no-reply@' . Configuration::get('PS_SHOP_URL'),
					],
					'to'          => [
						[
							'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
							'email' => Configuration::get('PS_SHOP_EMAIL'),
						],
					],
					'subject'     => 'Relance automatique pour la session du ' . $studentEducation->date_start,
					"htmlContent" => $tpl->fetch(),
				];

				$result = Tools::sendEmail($postfields);

			}

		}

	}

	public static function proceedAttestation() {

		$today = date("Y-m-d");

		$date = new DateTime($today);
		$date->modify('-30 days');
		$dateEnd = $date->format('Y-m-d');
		$context = Context::getContext();

		$sessionEnd = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` LIKE \'' . $dateEnd . '\'')
		);

		if (isset($sessionEnd) && $sessionEnd > 0) {

			$session = new EducationSession($sessionEnd);

			$sessionDetails = Db::getInstance()->executeS(
				(new DbQuery())
					->select('`id_student_education`')
					->from('student_education')
					->where('`id_education_session` = ' . $session->id)
					->where('`deleted` = 0')
					->where('id_student_education_state > 3')
			);

			foreach ($sessionDetails as $sessionDetail) {
				$studentEducation = new StudentEducation($sessionDetail['id_student_education']);

				$time = (int) str_replace(":", "", $studentEducation->education_lenghts);

				if ($time > 0) {
					$ref = 'Attestation Assiduité GENERAL V 1.0.02.12.2021';
					$template = 'attestation';
					$fileName = $studentEducation->id . '_assiduite.pdf';
					$header = 'header';

					$student = new Customer($studentEducation->id_customer);

					StudentEducation::printStudentAttestation($template, $ref, $header, $fileName, $studentEducation, $student);

					$template = 'certificat';
					$ref = 'Certificat de réalisation V 1.1.10.08.2021';
					$header = 'headerAttestation';
					$fileName = $studentEducation->id . '_realisation.pdf';

					StudentEducation::printStudentAttestation($template, $ref, $header, $fileName, $studentEducation, $student);

				}

			}

		}

	}

	public static function printStudentAttestation($template, $ref, $header, $fileName, StudentEducation $studentEducation, Customer $student) {

		$context = Context::getContext();

		$idShop = $context->shop->id;

		if (Configuration::get('PS_LOGO_INVOICE', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO_INVOICE', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}

		$width = 0;
		$height = 0;

		if (!empty($logo_path)) {
			list($width, $height) = getimagesize(_PS_ROOT_DIR_ . $logo_path);
		}

		$maximumHeight = 100;

		if ($height > $maximumHeight) {
			$ratio = $maximumHeight / $height;
			$height *= $ratio;
			$width *= $ratio;
		}

		$mpdf = new \Mpdf\Mpdf([
			'margin_left'   => 10,
			'margin_right'  => 10,
			'margin_top'    => 100,
			'margin_bottom' => 30,
			'margin_header' => 10,
			'margin_footer' => 10,
		]);

		$data = $context->smarty->createTemplate(_PS_PDF_TEMPLATE_DIR_ . 'attestations/header.tpl');

		$data->assign('logo_path', $logo_path);
		$mpdf->SetHTMLHeader($data->fetch());

		$data = $context->smarty->createTemplate(_PS_PDF_TEMPLATE_DIR_ . 'attestations/' . 'footer.tpl');
		$data->assign('version', $ref);
		$data->assign('tag_footer', Configuration::get('EPH_FOOTER_PROGRAM'));
		$mpdf->SetHTMLFooter($data->fetch(), 'O');

		$data = $context->smarty->createTemplate(_PS_PDF_TEMPLATE_DIR_ . 'pdf.css.tpl');
		$data->assign('color', '#fff');
		$stylesheet = $data->fetch();

		$data = $context->smarty->createTemplate(_PS_PDF_TEMPLATE_DIR_ . 'attestations/' . $template . '.tpl');

		$data->assign(
			[
				'title'            => $student->title,
				'student'          => $student,
				'studentEducation' => $studentEducation,
				'company'          => $context->company,
				'IpRfer'           => Tools::getRemoteAddr(),
				'logo_tampon'      => '/img/LDTampon.jpg',
			]
		);

		$filePath = _PS_PDF_STUDENT_DIR_;
		$mpdf->SetTitle($template);
		$mpdf->SetAuthor('Groupe ' . Configuration::get('PS_SHOP_NAME'));
		$mpdf->SetDisplayMode('fullpage');
		$mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
		$mpdf->WriteHTML($data->fetch());
		$mpdf->Output($filePath . $fileName, 'F');

	}

	
	
	public static function generateInvoice($idSession) {
		
		$context = Context::getContext();

		$session = new EducationSession($idSession);
		$sessionDetails = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $session->id)
				->where('`deleted` = 0')
				->where('id_student_education_state = 8')
		);
		foreach ($sessionDetails as $sessionDetail) {
			$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
			if($studentEducation->is_invoice ==1) {
				continue;
			}
			CustomerPieces::mergeOrderTable($studentEducation->id);
			$studentEducation->id_student_education_state = 9;
			$studentEducation->is_invoice = 1;
			$studentEducation->update();
			$suivie = new StudentEducationSuivie();
			$suivie->suivie_date = date('Y-m-d');
			$suivie->id_student_education = $studentEducation->id;
			$suivie->id_student_education_state = 9;
			$suivie->content = 'La facture a été automatiquement générée';
			$suivie->add();
		}
	}

	public static function proceedSessionEnd($sessionEnd) {

		$context = Context::getContext();

		$session = new EducationSession($sessionEnd);
		$sessionDetails = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $session->id)
				->where('`deleted` = 0')
				->where('id_student_education_state > 3')
		);

		$smsContent = '';
		$mailAgent = [];

		foreach ($sessionDetails as $sessionDetail) {
			$fileAttachement = [];
			$studentEducation = new StudentEducation($sessionDetail['id_student_education']);
			$time = (int) str_replace(":", "", $studentEducation->education_lenghts);
			$template = "";

			$education = new Education($studentEducation->id_education);
			$student = new Customer($studentEducation->id_customer);
			$context->smarty->assign(
				[
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);

			if ($education->id_education_type == 1) {
				$template = 'attestation_bureautique.tpl';
				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'end_office.tpl');
			} else

			if ($education->id_education_type == 2) {
				$template = 'attestation_langue.tpl';
				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'end_langue.tpl');
			} else {
				$template = 'attestation_bureautique.tpl';
				$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'end_office.tpl');
			}

			$smsContent = $smsContent . '<br><p>' . $student->firstname . ' ' . $student->lastname . ' ' . $student->email . '</p><p>' . $content . '</p>';
			$recipient = $student->phone_mobile;

			Tools::sendSms($recipient, $content);
			$studentEducation->id_student_education_state = 8;
			$studentEducation->update();

			if ($time > 0) {

				$evaluation = new StudentEvaluation();
				$evaluation->id_student = $student->id;
				$evaluation->id_student_education = $studentEducation->id;
				$evaluation->type = 'hot';
				try {
					$evaluation->add();
				} catch (Exception $ex) {
					// jump to this part
					// if an exception occurred
				}

				$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/attestation_formation.tpl');

				$secret_iv = _COOKIE_KEY_;
				$secret_key = _PHP_ENCRYPTION_KEY_;
				$string = $student->id . '-' . $student->lastname . $student->passwd;
				$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
				$linkEval = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitEvalHot&idStudentEducation=' . $studentEducation->id . '&idEvaluation=' . $evaluation->id;

				$tpl->assign([
					'title'          => $student->title,
					'lastname'       => $student->lastname,
					'birthname'      => $student->birthname,
					'firstname'      => $student->firstname,
					'education_name' => $studentEducation->name,
					'date_start'     => $studentEducation->date_start,
					'date_end'       => $studentEducation->date_end,
					'supplyName'     => $studentEducation->supplyName,
					'linkEval'       => $linkEval,
				]);

				$htmlContent = $tpl->fetch();
				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
						'email' => Configuration::get('PS_SHOP_EMAIL'),
					],
					'to'          => [
						[
							'name'  => $student->firstname . ' ' . $student->lastname,
							'email' => $student->email,
						],
					],
					'cc'          => [
						[
							'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
							'email' => Configuration::get('PS_SHOP_EMAIL'),
						],
					],
					'subject'     => 'Vous avez terminé votre formation "' . $studentEducation->name . '"',
					"htmlContent" => $htmlContent,
				];

				$result = Tools::sendEmail($postfields);

				
				$session->sessionEnded = 1;
				$session->id_student_education_state = 8;
				$session->update();
				$suivie = new StudentEducationSuivie();
				$suivie->suivie_date = date('Y-m-d');
				$suivie->id_student_education = $studentEducation->id;
				$suivie->email_title = 'Vous avez terminez votre formation "' . $studentEducation->name . '"';
				$suivie->content = 'Email de sortie de formation pour  "' . $student->firstname . ' ' . $student->lastname . '"';
				$suivie->email_content = $htmlContent;
				$suivie->sms_content = $content;
				$suivie->id_student_education_state = 8;
				$suivie->content = $step->suivie;
				$suivie->add();

			}

		}

		$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/eend_education.tpl');
		$tpl->assign([
			'content' => $smsContent,
		]);
		$postfields = [
			'sender'      => [
				'name'  => "Automation CRM",
				'email' => Configuration::get('PS_SHOP_EMAIL'),
			],
			'to'          => [
				[
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				],
			],
			'subject'     => 'SMS fin de session du ' . $studentEducation->date_start,
			"htmlContent" => $tpl->fetch(),
		];
		$result = Tools::sendEmail($postfields);

	}

	public static function proceedEducationSuivi($idSession) {

		$context = Context::getContext();

		$session = new EducationSession($idSession);
		$sessionDetails = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $session->id)
				->where('`deleted` = 0')
		);

		foreach ($sessionDetails as $sessionDetail) {
			$student = new Customer($studentEducation->id_customer);
			$context->smarty->assign(
				[
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);
			$content = $context->smarty->fetch(_PS_SMS_DIR_ . 'education_suivi.tpl');
			$recipient = $student->phone_mobile;
			Tools::sendSms($recipient, $content);
		}

	}

	public static function changeEducationStepId($idEducation, $idStep) {

		$context = Context::getContext();

		$step = new StudentEducationStep($idStep, $context->language->id);
		$education = new StudentEducation($idEducation);

		if ($education->id_student_education_state != $step->id) {

			if ($step->id == 4) {

				if (empty($education->reference_edof) || empty($education->identifiant) || empty($education->passwd_link)) {
					return;
				}

				$id_education_prerequis = $education->id_education_prerequis;

				if (!Validate::isUnsignedId($education->id_education_prerequis)) {

					if ($education->id_education_attribute > 0) {
						$id_education_prerequis = Db::getInstance()->getValue(
							(new DbQuery())
								->select('id_education_prerequis')
								->from('education_attribute')
								->where('id_education = ' . $education->id_education)
								->where('id_education_attribute = ' . $education->id_education_attribute)
						);
					} else {

						$id_education_prerequis = Db::getInstance()->getValue(
							(new DbQuery())
								->select('id_education_prerequis')
								->from('education')
								->where('id_education = ' . $education->id_education)
						);
					}

				}

				$prerequis = new EducationPrerequis($id_education_prerequis);
				$content = $prerequis->content;
				$score = 0;
				$result = [];

				if (is_array($content) && count($content)) {

					$nbQuestions = count($content);

					$delta = $nbQuestions - $prerequis->min_score;

					$match = $prerequis->min_score + rand(1, $delta);

					$rand_keys = array_rand($content, $match);

					foreach ($content as $key => $quesion) {

						if ($quesion['name'] == 'profession') {
							continue;
						}

						if (in_array($key, $rand_keys)) {
							$score = $score + 1;
							$result[$quesion['name']] = 1;
						} else {
							$result[$quesion['name']] = 0;
						}

					}

					$date_add = Db::getInstance()->getValue(
						(new DbQuery())
							->select('`date_add`')
							->from('student_education_suivie')
							->where('id_student_education = ' . $education->id)
							->where('id_student_education_state = 3')
					);

					$studentPrerequis = new StudentEducationPrerequis();
					$studentPrerequis->id_student_education = $education->id;
					$studentPrerequis->id_education_prerequis = $id_education_prerequis;
					$studentPrerequis->content = serialize($result);
					$studentPrerequis->score = $score;
					$studentPrerequis->date_add = $date_add;
					$studentPrerequis->add();
				}

			}

			$education->id_student_education_state = $step->id;
			$education->update();
			$story = new StudentEducationHistory();
			$story->id_student_education = $education->id;
			$story->id_student_education_state = $step->id;
			$story->id_employee = isset($context->employee->id) ? $context->employee->id : 1;
			$story->date_add = $education->date_upd;
			$story->add();

			if ($step->send_email == 1) {
				$emailContents = StudentEducation::studentEmail($education->id, $step);
			}

			if ($step->send_sms == 1) {
				$smsContent = StudentEducation::sendStudentSms($education->id, $step);
			}

			if ($step->invoice == 1) {
				StudentEducation::generateInvoice($education);
			}

			if ($step->is_suivie == 1) {
				$suivie = new StudentEducationSuivie();
				$suivie->suivie_date = date('Y-m-d');
				$suivie->id_student_education = $education->id;
				$suivie->id_student_education_state = $step->id;

				if (is_array($emailContents) && count($emailContents)) {
					$suivie->email_title = $emailContents['email_title'];
					$suivie->email_content = $emailContents['email_content'];
				}

				if (is_array($smsContent) && count($smsContent)) {
					$suivie->email_title = $smsContent['sms_title'];
					$suivie->email_content = $smsContent['sms_content'];
				}

				$suivie->content = $step->suivie;
				$suivie->add();

			}

		}

		return true;
	}

	

	public static function sendStudentSms($idEducation, StudentEducationStep $step) {

		if (file_exists(_PS_SMS_DIR_ . $step->sms_template . '.tpl')) {
			$studentEducation = new StudentEducation($idEducation);
			$student = new Customer($studentEducation->id_customer);
			$context = Context::getContext();
			$context->smarty->assign(
				[
					'student'          => $student,
					'studentEducation' => $studentEducation,
				]
			);

			$content = $context->smarty->fetch(_PS_SMS_DIR_ . $step->sms_template . '.tpl');

			$recipient = $student->phone_mobile;
			Tools::sendSms($recipient, $content);
			return [
				'sms_title'   => $step->description,
				'sms_content' => $content,
			];
		}

	}

	public static function studentEmail($idEducation, StudentEducationStep $step) {

		$studentEducation = new StudentEducation($idEducation);
		$student = new Student($studentEducation->id_student);
		$customer = new Customer($studentEducation->id_customer);
		$context = Context::getContext();

		if (!empty($step->template)) {
			$topic = $step->description;
			$education = new Education($studentEducation->id_education);
			$date_start = $studentEducation->date_start;
			$fileAttachement = null;
			$educationType = $education->getEducationType();

			if ($educationType == 1) {
				$packs = EducationPack::getItems($education->id, $this->context->language->id);
				$fileAttachement = [];

				foreach ($packs as $pack) {

					$attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
						(new DbQuery())
							->select('fileName')
							->from('education_programme')
							->where('`id_education_attribute` = ' . $pack->id_pack_education_attribute)
							->where('`id_education` = ' . (int) $pack->id)
					);

					if ($attachement != '') {
						$fileName = _PS_PROGRAM_DIR_ . 'programme' . str_replace(' ', '', $attachement) . '.pdf';
						$fileAttachement[] = [
							'content' => chunk_split(base64_encode(file_get_contents($fileName))),
							'name'    => $attachement . '.pdf',
						];
					}

				}

			} else {
				$attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
					(new DbQuery())
						->select('fileName')
						->from('education_programme')
						->where('`id_education` = ' . (int) $studentEducation->id_education)
						->where('`id_education_attribute` = ' . (int) $studentEducation->id_education_attribute)
				);

				if ($attachement != '') {
					$fileName = _PS_PROGRAM_DIR_ . 'programme' . str_replace(' ', '', $attachement) . '.pdf';
					$fileAttachement[] = [
						'content' => chunk_split(base64_encode(file_get_contents($fileName))),
						'name'    => $attachement . '.pdf',
					];
				}

			}

			$secret_iv = _COOKIE_KEY_;
			$secret_key = _PHP_ENCRYPTION_KEY_;
			$string = $customer->id . '-' . $customer->lastname . $customer->passwd;
			$crypto_key = Tools::encrypt_decrypt('encrypt', $string, $secret_key, $secret_iv);
			$linkContract = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitContract&idStudentEducation=' . $studentEducation->id;

			$linkPositionnement = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, ['crypto_key' => $crypto_key], false, 1) . '&submitPositionnement&idStudentEducation=' . $studentEducation->id;

			$tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $step->template . '.tpl');
			$tpl->assign([
				'title'              => $customer->title,
				'lastname'           => $customer->lastname,
				'birthname'          => $customer->birthname,
				'firstname'          => $customer->firstname,
				'education_name'     => $studentEducation->name,
				'date_start'         => $studentEducation->date_start,
				'date_end'           => $studentEducation->date_end,
				'last_days'          => $studentEducation->days,
				'last_hours'         => $studentEducation->hours,
				'courseLink'         => $studentEducation->courseLink,
				'reservationLink'    => $studentEducation->reservationLink,
				'supplyName'         => $studentEducation->supplyName,
				'date_limit'         => $studentEducation->date_limit,
				'identifiant'        => $studentEducation->identifiant,
				'passwd_link'        => $studentEducation->passwd_link,
				'certification'      => $education->certification,
				'linkContract'       => $linkContract,
				'linkPositionnement' => $linkPositionnement,
			]);

			$postfields = [
				'sender'      => [
					'name'  => "Service  Administratif " . Configuration::get('PS_SHOP_NAME'),
					'email' => Configuration::get('PS_SHOP_EMAIL'),
				],
				'to'          => [
					[
						'name'  => $customer->firstname . ' ' . $customer->lastname,
						'email' => $customer->email,
					],
				],

				'subject'     => $step->description,
				"htmlContent" => $tpl->fetch(),
				'attachment'  => $fileAttachement,
			];
			$result = Tools::sendEmail($postfields);

			return [
				'email_title'   => $step->description,
				'email_content' => $tpl->fetch(),
			];

		}

	}

	public static function getIdSessionbyEdof($edof) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('id_student_education')
				->from('student_education')
				->where('`reference_edof` = ' . $edof)
				->where('`deleted` = 0')
		);

	}
	
	public static function proceedEducationAdjust() {

		$context = Context::getContext();
		
		$file = fopen("testproceedEducationAdjust.txt","w");
		
		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-90 days');
		$dateEnd = $date->format('Y-m-d');
	
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` < \'' . $dateEnd . '\'')
		);
		
		foreach ($sessions as $session) {
			$session = new EducationSession($session['id_education_session']);
			
			$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education')
				->where('`id_education_session` = ' . $session->id)
				->where('`deleted` = 0')
				->where('id_student_education_state >= 4')
			);
			
			foreach ($educations as $education) {
				
				$percent = 0;
				$time = 0;

				$studentEducation = new StudentEducation($education['id_student_education']);
				$lenght = explode(":", $studentEducation->education_lenghts);
				$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);
				$percent = round($time * 100 / $studentEducation->hours, 2);
				$min = $studentEducation->hours * 81.6 / 100;
				$max = $studentEducation->hours - 1;
				$target = round(rand($min, $max) + Tools::random_float(0, 1), 2);
				$compare = round($target * 100 / $studentEducation->hours, 2);

				if ((int) $compare > (int) $percent) {

					$timeToUpdate = Tools::convertTime($target);
					$studentEducation->education_lenghts = $timeToUpdate;
					$studentEducation->update();

				}

			}

		}		

	}

	public static function generateEvaluationHot() {

		$champsHot = ['1om', '1ds', '2df', '2cpc', '3rop', '3qp', '4arc', '4aof'];

		$tagBureautiques = [
			'La formation proposée m’a permis de revoir les bases nécessaires à la bonne gestion des fonctions bureautiques de mon poste.',
			'Je suis ravie de pouvoir retourner sur les notions que j’ai dues survoler lors de la formation, cela m’aidera beaucoup dans les cas pratiques au travail !',
			'J’ai pu étoffer mes compétences et mettre à l’épreuve mes acquis, c’est tout ce que j’attendais, merci !',
			'J’avais besoin d’une mise à niveau informatique pour évoluer dans mon poste actuel, la formation m’a fourni toutes les notions importantes.',
			'Très bon support et matériel adapté pour approfondir les connaissances dans le domaine de la bureautique, c’est parfait.',
		];
		$tagAccessBureautiques = [
			'Je suis ravi d’avoir encore accès à la plateforme, cela va me permettre de continuer mon apprentissage au-delà de la formation.',
			'Je débute encore et j’ai eu quelques difficultés, je vais bientôt pouvoir appliquer les connaissances.',
			'',
			'J’attends d’être en poste pour vraiment pouvoir appliquer les compétences.',
		];
		$tagAccessBureautiques = [
			'Je suis ravi d’avoir encore accès à la plateforme, cela va me permettre de continuer mon apprentissage au-delà de la formation.',
			'Je débute encore et j’ai eu quelques difficultés, je vais bientôt pouvoir appliquer les connaissances.',
			'',
			'J’attends d’être en poste pour vraiment pouvoir appliquer les compétences.',
		];

		$tagMissingBureautiques = [
			'',
			'',
			'J’aurais aimée plus d’exemples concrets.',
		];

		$tagExchanges = [
			'Utiles et efficaces',
			'Très réguliers et encourageants',
			'Beaucoup de courtoisie, la jeune femme est fort aimable',
			'',
			'Super pour les SMS, car parfois vos email arrivaient dans ma boite spam',
		];
		$tagLangues = [
			'J’ai pu apprendre les bases de la langue étrangère pour être plus à l’aise lors de mes voyages, et je pense que cela va également me permettre d’étoffer mon CV.',
			'La formule de formation en ligne permet d’adapter l’apprentissage en fonction des jours et de la progression, j’apprécie beaucoup la liberté qu’offrent les modules',
			'Formation très intéressante et très flexible, je suis ravi d’avoir pu utiliser mon temps libre à être plus à l’aise tant à l’oral qu’à l’écrit.',
			'Cela me paraissait compliqué mais en fait la formation s’est adaptée à mon niveau et j’ai pu bien progresser, merci.',
			'Je tenais à compléter mon CV et à être plus à l’aise au quotidien, surtout pour m’exprimer à l’oral, je pense que je vais encore davantage approfondir mes connaissances car cela m’a beaucoup plu. ',
		];
		$tagAccessLangues = [
			'Je n’ai pas pu accorder autant de temps que je voulais à la formation, je vais profiter de pouvoir encore me connecter à la plateforme pour approfondir davantage mes connaissances.',
			'',
			'Mon niveau est encore trop faible, je pense continuer à apprendre avant de pouvoir bien pratiquer la langue.',
			'Etant encore trop débutant, je n’ose pas m’exprimer car je ne suis pas encore assez sûr de moi. Je pense continuer d’apprendre.',
			'Le site a pas mal de beug',
			'',
			'Les vidéos sont parfois très longues à charger',
		];

		$tagMissingLangues = [
			'',
			'Le site à des soucis de vitesse',
			'',
			'La plateforme manque de clarté.',
		];

		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-40 days');
		$dateEnd = $date->format('Y-m-d');

		$sessions = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` < \'' . $dateEnd . '\'')
		);
		
		foreach($sessions as $session) {
			
			$isSuivi = Db::getInstance()->getRow(
  			(new DbQuery())
				->select('`id_student_evaluation`, `answered`')
				->from('student_evaluation')
    			->where('`id_student_education` ='.$session['id_student_education'])
				->where('`type` LIKE "hot"')
			);
			
			if(empty($isSuivi)) {
		
				$evaluation = new StudentEvaluation();
				$studentEducation = new StudentEducation($session['id_student_education']);
				$education = new Education($studentEducation->id_education);
				$studentEducation->eval_hot = 1;
				$evaluation->id_student = $studentEducation->id_student;
				$evaluation->id_student_education = $studentEducation->id;
				$evaluation->type = 'hot';
				$result = [];
				$score = 0;
				foreach($champsHot as $value) {
					$note = rand(2,3);
					$result[$value] = $note;
					$score = $score + $note;
				}
			
				$evaluation->score = $score;
				if($education->id_education_type == 1) {
					$key = rand(0, 4);
					$result["critere5"] = $tagBureautiques[$key];
					$key = rand(0, 3);
					$result["accessSuccess"] = $tagAccessBureautiques[$key];
					$key = rand(0, 2);
					$result['suggestMissing'] = $tagMissingBureautiques[$key];
				}
				if($education->id_education_type == 2) {
					$key = rand(0, 4);
					$result["critere5"] = $tagLangues[$key];
					$key = rand(0, 6);
					$result["accessSuccess"] = $tagAccessLangues[$key];
					$key = rand(0, 3);
					$result['suggestMissing'] = $tagMissingLangues[$key];
				}
				$key = rand(1, 4);
				$result["exchangeQuality"] = $tagExchanges[$key];
				$result['newJob'] = 0;
				$result['jobChange'] = 0;
				$result['newPost'] = 0;
				$result['evolCompany'] = 0;
				$evaluation->content = serialize($result);
				$evaluation->answered = 1;
				$result = $evaluation->add();			
				$studentEducation->update();
			} else  {
		
				$evaluation = new StudentEvaluation($isSuivi['id_student_evaluation']);
				$studentEducation = new StudentEducation($evaluation->id_student_education);
				$education = new Education($studentEducation->id_education);
				$studentEducation->eval_hot = 1;
				$result = [];
				$score = 0;
				foreach($champsHot as $value) {
					$note = rand(2,3);
					$result[$value] = $note;
					$score = $score + $note;
				}
			
				$evaluation->score = $score;
				if($education->id_education_type == 1) {
					$key = rand(0, 4);
					$result["critere5"] = $tagBureautiques[$key];
					$key = rand(0, 3);
					$result["accessSuccess"] = $tagAccessBureautiques[$key];
					$key = rand(0, 2);
					$result['suggestMissing'] = $tagMissingBureautiques[$key];
				}
				if($education->id_education_type == 2) {
					$key = rand(0, 4);
					$result["critere5"] = $tagLangues[$key];
					$key = rand(0, 6);
					$result["accessSuccess"] = $tagAccessLangues[$key];
					$key = rand(0, 3);
					$result['suggestMissing'] = $tagMissingLangues[$key];
				}
				$key = rand(1, 4);
				$result["exchangeQuality"] = $tagExchanges[$key];
				$result['newJob'] = 0;
				$result['jobChange'] = 0;
				$result['newPost'] = 0;
				$result['evolCompany'] = 0;
				$evaluation->content = serialize($result);
				$evaluation->answered = 1;
				$result = $evaluation->update();	
				$studentEducation->update();
		
		
			}
		}

		

	}

	public static function getEducationIndicateur() {

		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('e.id_education, e.reference, ea.id_education_attribute, ea.reference as refAttribute')
				->from('education', 'e')
				->leftJoin('education_attribute', 'ea', 'ea.id_education = e.id_education')
		);

		$result = [];

		foreach ($educations as $education) {

			$collection = [];

			if ($education["id_education_attribute"] > 0) {

				$studentEducations = Db::getInstance()->executeS(
					(new DbQuery())
						->select('id_student_education')
						->from('student_education')
						->where('id_education = ' . $education["id_education"])
						->where('id_education_attribute = ' . $education["id_education_attribute"])
						->where('id_student_education_state > 8 ')
						->where('id_student_education_state < 10 ')
				);

				foreach ($studentEducations as $studentEducation) {
					$collection[] = new StudentEducation($studentEducation['id_student_education']);
				}

				$result[$education['refAttribute']] = $collection;

			} else {

				$studentEducations = Db::getInstance()->executeS(
					(new DbQuery())
						->select('id_student_education')
						->from('student_education')
						->where('id_education = ' . $education["id_education"])
						->where('id_student_education_state > 8 ')
						->where('id_student_education_state < 10 ')
				);

				foreach ($studentEducations as $studentEducation) {
					$collection[] = new StudentEducation($studentEducation['id_student_education']);
				}

				$result[$education['reference']] = $collection;
			}

		}
		
		foreach($results as $key => $values) {
			
			$answer = 0;
			$total = 0;
			$totalFormation = count($values);
			foreach($values as $k => $value) {
				$id_education = $value->id_education;
				$id_education_attribute = $value->id_education_attribute;
				$name = $value->name;
		
				if($value->answer_hot > 0) {
					$answer= $answer+1;
					$total = $total + $value->score_hot;
				}
			}
			if($answer > 0) {
				$average = $total/$answer;
				$note = ($average*20/24);
			
				$idIndicateur = EducationIndicateur::getIdByeducation($id_education, $id_education_attribute);
			
			
				if($idIndicateur > 0) {
					$indicateur = new EducationIndicateur($idIndicateur);
					$indicateur->score = $note;
					$indicateur->name = $name;
					$indicateur->qty = $totalFormation;
					$indicateur->update();
				} else {
					$indicateur = new EducationIndicateur();
					$indicateur->id_education = $id_education;
					$indicateur->id_education_attribute = $id_education_attribute;
					$indicateur->name = $name;
					$indicateur->score = $note;
					$indicateur->qty = $totalFormation;
					$indicateur->add();
				}
			}
			
			
			
		}

		return $result;

	}

}
