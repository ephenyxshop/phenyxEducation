<?php

/**
 * Class EvaluationControllerCore
 *
 * @since 1.8.1.0
 */
class EvaluationControllerCore extends FrontController {

	// @codingStandardsIgnoreStart
	/** @var string $php_self */
	public $auth = true;

	public $php_self = 'evaluation';
	/** @var bool $ssl */
	public $ssl = true;
	// @codingStandardsIgnoreEnd
	protected $student;

	public $template;

	public $critere = [];

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
		$this->addCSS(_THEME_CSS_DIR_ . 'evaluation.css');
		$this->addJS(_THEME_JS_DIR_ . 'evaluation.js');
		Media::addJsDef([
			'AjaxEvaluationLink' => $this->context->link->getPageLink('evaluation', true),

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
		$idEvaluation = Tools::getValue('idEvaluation');
		$evaluation = new StudentEvaluation($idEvaluation);

		if (Validate::isLoadedObject($studentEducation)) {

			if ($evaluation->answered == 1) {
				Tools::redirect('index.php');
			}

			if ($evaluation->type == 'hot' && $studentEducation->eval_hot == 1) {
				Tools::redirect('index.php');
			} else
			if ($evaluation->type == 'cold' && $studentEducation->eval_cold == 1) {
				Tools::redirect('index.php');
			} else {

				$this->context->smarty->assign(
					[
						'studentEducation' => $studentEducation,
						'student'          => $this->student,
						'idEvaluation'     => $idEvaluation,
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

		if ($evaluation->type == 'hot') {
			$this->setTemplate(_PS_THEME_DIR_ . 'evaluation.tpl');
		} else
		if ($evaluation->type == 'cold') {
			$this->setTemplate(_PS_THEME_DIR_ . 'evaluation_cold.tpl');
		}

	}

	public function ajaxProcessSaveEvaluation() {

		$idStudentEducation = Tools::getValue('idSession');
		$idEvaluation = Tools::getValue('idEvaluation');
		$content = [];

		$evaluation = new StudentEvaluation($idEvaluation);
		$score = Tools::getValue('score');

		$studentEducation = new StudentEducation($idStudentEducation);
		$student = $this->context->student;

		$WordSection1 = Tools::getValue('WordSection1');
		$dataForms = Tools::getValue('dataForms');

		foreach ($dataForms as $key => $value) {
			$this->context->smarty->assign($key, trim($value));
		}

		$this->context->smarty->assign([
			'launchRef'        => '	Fiche d?????valuation ?? chaud V.1.1.16.04.21',
			'score'            => $score,
			'logo_path'        => _PS_ROOT_DIR_ . '/img/LogoLd.png',
			'student'          => $student,
			'studentEducation' => $studentEducation,
		]);
		$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetMargins(10, 2, 10, true);
		$pdf->setPrintHeader(false);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage();
		$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
		$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'evaluation.tpl');
		$pdf->writeHTML($html, false);
		$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_evaluation.pdf', 'F');
		$fileName = _PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_evaluation.pdf';

		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents($fileName))),
			'name'    => 'Evaluation ) froid.pdf',
		];

		$evaluation->id_student = $student->id;
		$evaluation->id_student_education = $studentEducation->id;
		$evaluation->score = $score;

		if ($evaluation->type == 'hot') {
			$studentEducation->eval_hot = 1;
			$template = "evaluation_hot";
			$this->critere = [
				'critereOm'          => 'Organisation mat??riel',
				'critereDs'          => 'Documents et supports',
				'critereDf'          => 'Concernant la dur??e de la formation',
				'critereCpc'         => 'Contenu de la plateforme de cours',
				'critereRop'         => 'Relations entre l???organisme et les participants',
				'critereQp'          => 'Qualit?? de l???information pr??alable et du suivi p??dagogique',
				'critereArc'         => 'Concernant les acquis par rapport aux connaissances initiales',
				'critereAof'         => 'Atteinte des objectifs d??crits au d??but de la formation',
				'critere5'           => 'Appr??ciation Globale de la Formation',
				'accessSuccess'      => 'Mon acc??s ?? la formation ?? ??t?? facile ? (SMS, Courriels, Appels???)',
				'exchangeQuality'    => 'Nos ??changes t??l??phoniques et notre communication ont ??t?? utiles et efficaces (SMS, Courriels, Appels???)',
				'suggestMissing'     => 'Manque-t-il des ??l??ments que vous souhaiteriez voir ajouter dans cette formation ?',
				'haveSuggestion'     => 'Avez-vous des suggestions pour am??liorer cette action de formation ?',
				'accesCertif'        => 'L???acc??s ?? la certification ?? ??t?? claire et simple ?',
				'newJob'             => 'La formation m???a permis de retrouver un emploi',
				'newJob_detail'      => 'Nouvel Emploi',
				'jobChange'          => 'La formation m???a permis de changer de m??tier',
				'jobChange_detail'   => 'Nouveau m??tier',
				'newPost'            => 'La formation m???a permis d???acc??der ?? un nouveau poste',
				'newPost_detail'     => 'Nouveau poste',
				'evolCompany'        => 'La formation m???a permis d???evoluer au sein de mon entreprise',
				'evolCompany_detail' => 'Nouvel ??volution de carri??re',
			];
		}

		foreach ($dataForms as $key => $value) {

			if (array_key_exists($key, $this->critere)) {
				$content[$this->critere[$key]] = $value;
			}

		}

		$html = '';

		foreach ($content as $key => $value) {
			$html .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
		}

		$evaluation->content = serialize($content);
		$evaluation->answered = 1;
		$result = $evaluation->update();

		$studentEducation->update();
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/' . $template . '.tpl');
		$tpl->assign([
			'score'          => $score,
			'lastname'       => $student->lastname,
			'firstname'      => $student->firstname,
			'education_name' => $studentEducation->name,
			'date_start'     => $studentEducation->date_start,
			'date_end'       => $studentEducation->date_end,
			'session'        => $studentEducation->id,
			'html'           => $html,

		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
					'email' => 'lucie.allias@ld-formation.fr',
				],
			],

			'subject'     => 'Evaluation ?? chaud Etudiant : ' . $student->firstname . ' ' . $student->lastname,
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];

		$result = Tools::sendEmail($postfields);

		$return = [
			'success' => true,
			'link'    => Context::getContext()->link->getPageLink('my-education'),
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessSaveEvaluationCold() {

		$idStudentEducation = Tools::getValue('idSession');
		$idEvaluation = Tools::getValue('idEvaluation');
		$content = [];

		$evaluation = new StudentEvaluation($idEvaluation);
		$score = Tools::getValue('score');

		$studentEducation = new StudentEducation($idStudentEducation);
		$student = $this->context->student;

		$dataForms = Tools::getValue('dataForms');

		foreach ($dataForms as $key => $value) {
			$this->context->smarty->assign($key, trim($value));
			//$content[$key] = $value;
		}

		$this->context->smarty->assign([
			'launchRef'        => 'Fiche d?????valuation ?? froid V.1.1.22.04.21',
			'score'            => $score,
			'logo_path'        => _PS_ROOT_DIR_ . '/img/LogoLd.png',
			'student'          => $student,
			'studentEducation' => $studentEducation,
		]);
		$pdf = new EducationTemplate('P', PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
		$pdf->SetMargins(10, 2, 10, true);
		$pdf->setPrintHeader(false);
		$pdf->SetFont('helvetica', '', 9);
		$pdf->AddPage();
		$pdf->pieceFooter = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'footer.tpl');
		$html = $this->context->smarty->fetch(_PS_PDF_TEMPLATE_DIR_ . 'evaluation_cold.tpl');
		$pdf->writeHTML($html, false);
		$pdf->Output(_PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_evaluation_cold.pdf', 'F');
		$fileName = _PS_PDF_STUDENT_DIR_ . $studentEducation->id . '_evaluation_cold.pdf';

		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents($fileName))),
			'name'    => 'Evaluation a chaud.pdf',
		];

		$evaluation->id_student = $student->id;
		$evaluation->id_student_education = $studentEducation->id;
		$evaluation->score = $score;

		$studentEducation->eval_cold = 1;
		$template = "evaluation_cold";
		$this->critere = [
			'critereacaf'        => 'Avez-vous pu appliquer les connaissances que vous aviez acquises lors de la formation ?',
			'critereofcb'        => 'Pensez-vous que les objectifs op??rationnels d??finis avant la formation correspondaient bien ?? vos besoins ?',
			'critere1ds'         => 'Diriez-vous que la formation vous a permis de d??velopper votre niveau de comp??tences ?',
			'criteredfpepp'      => 'Diriez-vous que cette formation vous a permis d?????voluer sur le plan personnel ?',
			'criterefavmc'       => 'La formation vous a-t-elle permis d???accro??tre votre valeur sur le march?? du travail ?',
			'critereimperin'     => 'Estimez-vous que la formation a eu un impact sur votre performance individuelle ?',
			'criterepereq'       => 'Et sur la performance de votre ??quipe ?',
			'critere5'           => 'Appr??ciation Globale de la Formation',

			'mqOpp'              => 'Manque d???opportunit??',
			'fNAsapt'            => 'La formation n?????tait pas adapt??e ?? mes besoins',
			'changeMind'         => 'Changement de fonction / d???objectifs',
			'Other'              => 'Autre (pr??ciser)',

			'newJob'             => 'La formation m???a permis de retrouver un emploi',
			'newJob_detail'      => 'Nouvel Emploi',
			'jobChange'          => 'La formation m???a permis de changer de m??tier',
			'jobChange_detail'   => 'Nouveau m??tier',
			'newPost'            => 'La formation m???a permis d???acc??der ?? un nouveau poste',
			'newPost_detail'     => 'Nouveau poste',
			'evolCompany'        => 'La formation m???a permis d???evoluer au sein de mon entreprise',
			'evolCompany_detail' => 'Nouvel ??volution de carri??re',
		];

		foreach ($dataForms as $key => $value) {

			if (array_key_exists($key, $this->critere)) {
				$content[$this->critere[$key]] = $value;
			}

		}

		$html = '';

		foreach ($content as $key => $value) {

			if (is_numeric($value)) {

				if ($value == 0) {
					$value = 'Non';
				} else
				if ($value == 1) {
					$value = 'Oui';
				}

			}

			$html .= '<tr><td>' . $key . '</td><td>' . $value . '</td></tr>';
		}

		$evaluation->content = serialize($content);
		$evaluation->answered = 1;
		$result = $evaluation->update();

		$studentEducation->update();
		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/evaluation_cold.tpl');
		$tpl->assign([
			'score'          => $score,
			'lastname'       => $student->lastname,
			'firstname'      => $student->firstname,
			'education_name' => $studentEducation->name,
			'date_start'     => $studentEducation->date_start,
			'date_end'       => $studentEducation->date_end,
			'session'        => $studentEducation->id,
			'html'           => $html,

		]);
		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
					'email' => 'lucie.allias@ld-formation.fr',
				],
			],

			'subject'     => 'Evaluation ?? chaud Etudiant : ' . $student->firstname . ' ' . $student->lastname,
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];
		$result = Tools::sendEmail($postfields);
		$return = [
			'success' => true,
			'link'    => Context::getContext()->link->getPageLink('my-education'),
		];
		die(Tools::jsonEncode($return));

	}

}
