<?php
use \Curl\Curl;

/**
 * Class MyAccountControllerCore
 *
 * @since 1.8.1.0
 */
class MyEducationControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var bool $auth */
	public $auth = true;
	/** @var string $php_self */
	public $php_self = 'my-education';
	/** @var string $authRedirection */
	public $authRedirection = 'my-education';
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
		$this->addCSS(_THEME_CSS_DIR_ . 'index.css');
		$this->addJS(_THEME_JS_DIR_ . 'education.js');
		Media::addJsDef([
			'AjaxEducationLink'       => $this->context->link->getPageLink('my-education', true),
			'AjaxEducationAccessLink' => $this->context->link->getPageLink('education-access', true),

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

		if (!$this->ajax) {
			$educations = $this->context->customer->getEducations();
			$archived = $this->context->customer->getArchivedEducations();
			$this->context->smarty->assign(
				[
					'educations' => $educations,
					'archived'   => $archived,
				]
			);
		}

		$this->setTemplate(_PS_THEME_DIR_ . 'my-education.tpl');
	}

	public function ajaxProcessEditStudentEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$student_education = new StudentEducation($id_student_education);
		$id_student = Tools::getValue('id_student');
		$student = new Customer($id_student);
		$logo = '';
		$educationLink = '';

		$logo = 'pipplet-white.png';
		$this->params = [
			'type' => $student_education->educationType,
			'courseLink'            => $student_education->courseLink,
			'identifiant'           => $student_education->identifiant,
			'passwd_link'           => $student_education->passwd_link,
		];

		$educationLink = '<button class="connectPlatform" onClick="openEducationIframe(\'' . $this->context->link->getPageLink('education-access', true) . '\')" style="color:white;">Accèes vers la platerome</button>';

		$this->setTemplate(_PS_THEME_DIR_ . 'showEducation.tpl');

		$this->context->smarty->assign(
			[
				'education'         => Student::getStudentEducationById($id_student_education),
				'student'           => $student,
				'student_education' => $student_education,
				'postageFiles'      => $this->getPubliPostageMaterial($id_student_education),
				'logo'              => $logo,
				'educationLink'     => $educationLink,
				'params'			=> $this->params

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
			'assiduite'   => $this->l('Attestation Assiduité'),
			'realisation' => $this->l('Certificat de réalisation'),
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
		$student = new Customer($this->context->customer->id);

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}

				$education->{$key}
				= $value;
			}

		}
		if($education->id_education_session > 0) {
			$days = Education::getDaysEducation($education->id_education, $education->id_education_attribute);
			$session = new EducationSession($education->id_education_session);
			$date = new DateTime($session->session_date);
			$date->modify('+'.$days.' days');
			$education->date_start = $session->session_date;
			$education->date_end = $date->format('Y-m-d');
		}

		$education->id_customer = $student->id;
		$education->id_student_education_state = 2;
		$education->identifiant = $student->email;
		$education->passwd_link = $student->password;

		$education->add();
		$studentEducation = new StudentEducation($education->id);
		
		$suivie = new StudentEducationSuivie();
		$suivie->suivie_date = date('Y-m-d');
		$suivie->id_student_education = $studentEducation->id;
		$suivie->id_student_education_state = 10;
		$suivie->id_sale_agent = 0;
		$suivie->id_employee = 0;
		$suivie->content = 'Auto Inscription de '.$student->firstname.' '.$student->lastname.' a une formation "'.$studentEducation->name.' sur le Front Office ';
		$suivie->add();

		$education->sendStudentEmail();

		$studentEducation = new StudentEducation($education->id);

		$return = Tools::jsonEncode([
			'success'  => true,
			'redirect' => $studentEducation->reservationLink,
		]);

		die($return);

	}

	public function ajaxProcessGeneratePrerequis() {

		$idPrerequis = Tools::getValue('idPrerequis');
		$prerequis = new EducationPrerequis($idPrerequis, 1);

		$this->setTemplate(_PS_THEME_DIR_ . 'prerequis.tpl');

		$this->context->smarty->assign(
			[
				'prerequis' => $prerequis,

			]
		);

		$result = [
			'html' => $this->context->smarty->fetch($this->template),
		];

		die(Tools::jsonEncode($result));
	}

}
