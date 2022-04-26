<?php

/**
 * Class AgentDashboardControllerCore
 *
 * @since 1.8.1.0
 */
class AgentDashboardControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var bool $auth */
	public $auth = true;
	/** @var string $php_self */
	public $php_self = 'agent-dashboard';
	/** @var string $authRedirection */
	public $authRedirection = 'agent-dashboard';
	/** @var bool $ssl */
	public $ssl = true;

	public $params;
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
		$this->addCSS(_AGENT_CSS_DIR_ . 'index.css');
		$this->addCSS(_AGENT_CSS_DIR_ . 'dashboard.css');
		$this->addJS(_AGENT_JS_DIR_ . 'dashboard.js');
		Media::addJsDef([
			'AjaxAgentDashboardLink' => $this->context->link->getPageLink('agent-dashboard', true),

		]);
	}

	public function postProcess() {

		parent::postProcess();

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
		$agent = new SaleAgent($this->context->cookie->id_agent);
		$educations = $agent->getCurrentEducation();
		$hystories = $agent->getHistoryCommissions();

		$this->context->smarty->assign(
			[
				'educations' => $educations,
				'hystories'  => $hystories,
			]
		);

		$this->setTemplate(_PS_AGENT_DIR_ . 'dashboard.tpl');
	}

	public function ajaxProcessEditStudentEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$student_education = new StudentEducation($id_student_education);
		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);
		$logo = '';
		$educationLink = '';

		if ($student_education->educationType == 1) {
			$logo = 'TOSA-White.png';
			$educationLink = '<a href="' . $student_education->courseLink . '" target=”_blank” style="color:white;">Accèes vers la platerome</a>';
		} else
		if ($student_education->educationType == 2) {
			$logo = 'pipplet-white.png';
			$this->params = ['type' => $student_education->educationType,
				'courseLink'            => $student_education->courseLink,
				'identifiant'           => $student_education->identifiant,
				'passwd_link'           => $student_education->passwd_link,
			];

			$educationLink = '<button class="connectPlatform" onClick="openEducationIframe(\'' . $this->context->link->getPageLink('education-access', true) . '\')" style="color:white;">Accèes vers la platerome</button>';
		}

		$this->setTemplate(_PS_THEME_DIR_ . 'showEducation.tpl');

		$this->context->smarty->assign(
			[
				'education'         => Student::getStudentEducationById($id_student_education),
				'student'           => $student,
				'student_education' => $student_education,
				'postageFiles'      => $this->getPubliPostageMaterial($id_student_education),
				'logo'              => $logo,
				'educationLink'     => $educationLink,

			]
		);

		$result = [
			'html'   => $this->context->smarty->fetch($this->template),
			'params' => $this->params,
		];

		die(Tools::jsonEncode($result));
	}

	public function getPubliPostageMaterial($id_student_education) {

		$return = [];
		$pdfTemplate = [
			'launch'      => $this->l('Bien démarrer sa formation '),
			'cpmateriel'  => $this->l('Condition de remise de matériel'),
			'tosa'        => $this->l('Formation Tosa'),
			'pipplet'     => $this->l('Formation Pipplet'),
			'attestation' => $this->l('Attestation de fin de formation'),
		];

		foreach ($pdfTemplate as $key => $template) {
			$file = _PS_PDF_STUDENT_DIR_ . $id_student_education . '_' . $key . '.pdf';

			if (file_exists($file)) {
				$return[] = [
					'template' => $template,
					'link'     => _PDF_STUDENT_DIR_ . $id_student_education . '_' . $key . '.pdf',
				];
			}

		}

		return $return;
	}

	public function ajaxProcessRequestEducation() {

		$education = new StudentEducation();
		$student = new Student($this->context->student->id);

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}

				$education->{$key}
				= $value;
			}

		}

		$education->id_student = $student->id;
		$education->id_customer = $student->id_customer;
		$education->id_student_education_state = 1;
		$education->identifiant = $student->email;
		$education->passwd_link = $student->password;

		$education->add();
		$education->sendStudentEmail();

		$education = new StudentEducation($education->id);
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/registrationReport.tpl');
		$tpl->assign([
			'title'           => $student->title,
			'lastname'        => $student->lastname,
			'birthname'       => $student->birthname,
			'firstname'       => $student->firstname,
			'address_street'  => $student->address_street,
			'address_city'    => $student->address_city,
			'address_zipcode' => $student->zipcode,
			'phone'           => $student->phone,
			'phone_mobile'    => $student->phone_mobile,
			'education_name'  => $education->name,
			'date_start'      => $education->date_start,
			'date_end'        => $education->date_end,
			'last_days'       => $education->days,
			'last_hours'      => $education->hours,
			'supplyName'      => $education->supplyName,
			'sessionName'     => $education->sessionName,

		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => 'Grand Kalif',
					'email' => 'nacer.lillouche@ld-formation.fr',
				],
			],
			'subject'     => 'Inscription d’un étudiant à une formation',
			"htmlContent" => $tpl->fetch(),
		];
		$result = Tools::sendEmail($postfields);

		$return = Tools::jsonEncode([
			'success'  => true,
			'redirect' => $this->context->link->getPageLink('my-education', true),
		]);
		die($return);

	}

}
