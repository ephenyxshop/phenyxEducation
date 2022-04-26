<?php

/**
 * Class AttestationControllerCore
 *
 * @since 1.8.1.0
 */
class AttestationControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	public $auth = true;
	/** @var string $php_self */
	public $php_self = 'attestation';
	/** @var bool $ssl */
	public $ssl = true;
	// @codingStandardsIgnoreEnd
	protected $student;

	public $template;

	public function init() {

		parent::init();
		$this->student = $this->context->student;
	}

	/**
	 * Start forms process
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function postProcess() {

		parent::postProcess();

		if (isset($this->student->id)) {
			$_POST = array_map('stripslashes', $this->student->getFields());
		}

	}

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
		$this->addCSS(_THEME_CSS_DIR_ . 'contract.css');
		$this->addJS(_THEME_JS_DIR_ . 'contract.js');
		Media::addJsDef([
			'AjaxAttestationLink' => $this->context->link->getPageLink('attestation', true),

		]);
	}

	/**
	 * Assign template vars related to page content
	 *
	 * @see FrontController::initContent()
	 *
	 * @since 1.8.1.0
	 */
	public function initContent() {

		parent::initContent();

		$idStudentEducation = Tools::getValue('idStudentEducation');
		$studentEducation = new StudentEducation($idStudentEducation);

		if (Validate::isLoadedObject($studentEducation)) {

			if ($studentEducation->attest_end == 1) {
				Tools::redirect('index.php');
			} else {

				$this->context->smarty->assign(
					[
						'studentEducation' => $studentEducation,
						'student'          => $this->student,
						'error'            => false,
					]
				);

			}

		} else {
			$this->context->smarty->assign(
				[
					'error'   => true,
					'student' => $this->student,
				]
			);
		}

		$this->setTemplate(_PS_THEME_DIR_ . 'attestation.tpl');

	}

	public function ajaxProcessGetConfirmationCode() {

		$code = '';

		for ($i = 0; $i < 6; $i++) {
			$code .= mt_rand(0, 9);
		}

		$student = $this->context->student;
		$return = [
			'success' => true,
			'code'    => $code,
		];

		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessSendSmsCode() {

		$code = Tools::getValue('code');

		if (file_exists(_PS_SMS_DIR_ . 'smsCode.tpl')) {
			$student = $this->context->student;
			$this->context->smarty->assign(
				[
					'student' => $student,
					'code'    => $code,
				]
			);

			$content = $this->context->smarty->fetch(_PS_SMS_DIR_ . 'smsCode.tpl');

			$recipient = $student->phone_mobile;

			Tools::sendSms($recipient, $content);
			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/contract.tpl');
			$tpl->assign([
				'code'      => $code,
				'firstname' => $student->firstname,
				'lastname'  => $student->lastname,
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
				'subject'     => 'Votre code confidentiel',
				"htmlContent" => $tpl->fetch(),
			];
			$result = Tools::sendEmail($postfields);

			$return = [
				'success' => true,
			];
			die(Tools::jsonEncode($return));
		}

	}

	public function ajaxProcessValidateContract() {

		$idStudentEducation = Tools::getValue('idStudentEducation');
		$studentEducation = new StudentEducation($idStudentEducation);
		$studentEducation->doc_return = 1;
		$studentEducation->update();
		$student = $this->context->student;
		$education = new Education($studentEducation->id_education);

		$this->context->smarty->assign([
			'student'          => $student,
			'logo_path'        => _PS_ROOT_DIR_ . '/img/LogoLd.png',
			'img_ps_dir'       => 'http://' . Tools::getMediaServer(_PS_IMG_) . _PS_IMG_,
			'studentEducation' => $studentEducation,
			'education'        => $education,
			'checkbox'         => _PS_ROOT_DIR_ . '/img/checkbox.png',
			'launchRef'        => 'Matériel pédagogique V 1.4.27.04.21',
			'logo_tampon'      => _PS_ROOT_DIR_ . '/img/LDTampon.png',
			'date'             => date("d/m/Y"),
			'IpRfer'           => Tools::getRemoteAddr(),
		]);

		$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetMargins(10, 2, 10, true);
		$pdf->setPrintHeader(false);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage();
		$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
		$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'cpmateriel.tpl');
		$pdf->writeHTML($html, false);
		$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_cpmateriel.pdf', 'F');

		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_cpmateriel.pdf'))),
			'name'    => 'document materiel pédagogique.pdf',
		];
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/materiel_pdagogique.tpl');
		$tpl->assign([
			'title'          => $student->title,
			'lastname'       => $student->lastname,
			'birthname'      => $student->birthname,
			'firstname'      => $student->firstname,
			'education_name' => $studentEducation->name,
			'date_start'     => $studentEducation->date_start,
			'date_end'       => $studentEducation->date_end,
			'supplyName'     => $studentEducation->supplyName,
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

			'subject'     => 'Vous allez recevoir votre ' . $studentEducation->supplyName,
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];

		$result = Tools::sendEmail($postfields);

		$return = [
			'success' => true,
		];
		die(Tools::jsonEncode($return));

	}

}
