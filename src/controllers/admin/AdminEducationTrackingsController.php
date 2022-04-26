<?php
use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

/**
 * Class AdminEducationTrackingsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationTrackingsControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_tracking';
		$this->className = 'EducationTracking';
		$this->lang = true;
		$this->publicName = $this->l('Gestion des Expédition du matériel Pédagogique');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTRACKINGS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONTRACKINGS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTRACKINGS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONTRACKINGS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTRACKINGS_FIELDS', Tools::jsonEncode($this->getEducationTrackingFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONTRACKINGS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTRACKINGS_FIELDS', Tools::jsonEncode($this->getEducationTrackingFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONTRACKINGS_FIELDS'), true);
		}

	}

	public function setMedia() {

		parent::setMedia();
		MediaAdmin::addJsDef([
			'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->l('Liste des lien EDOF');

		$this->context->smarty->assign([
			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_' . $this->controller_name,
			'tableName'      => $this->table,
			'className'      => $this->className,
			'linkController' => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'       => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript' => $this->generateParaGridScript(),
			'titleBar'       => $this->TitleBar,
			'bo_imgdir'      => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
			'idController'   => '',
		]);

		parent::initContent();

	}

	public function generateParaGridScript() {

		$currentMonth = (int) date('m');
		$monthSelector = '{"0": "Exporter le fichier d‘expédition"},';
		$countries = Country::getCountries($this->context->language->id, false, true, false);

		for ($i = $currentMonth; $i <= 12; $i++) {
			$monthSelector .= '{"' . $i . '": "' . Tools::getMonthById($i) . '"},';
		}

		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$this->paramToolbar = [
			'items' => [

				[
					'type'    => '\'select\'',
					'icon'    => '\'ui-icon-disk\'',
					'attr'    => '\'id="monthTSelector"\'',
					'options' => '[
            			' . $monthSelector . '
						]',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Mettre à jour le Listing') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           updateListing();
						}',
				],

			],
		];

		$this->paramComplete = 'function(){
			$("#monthTSelector").selectmenu({
				width: 300,
				"change": function(event, ui) {
					if(ui.item.value > 0) {
						exportExpeditionMonth(ui.item.value);
					}
					$("#monthTSelector").val(0);
					$("#monthTSelector").selectmenu("refresh");

        		}

			});

        }';

		$this->paramTitle = '\'' . $this->l('Gestion des expéditions') . '\'';

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationTrackingRequest() {

		$trackings = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_tracking')
				->orderBy('`id_education_tracking` DESC')
		);

		foreach ($trackings as &$tracking) {

			$license = new License($tracking['id_license']);
			$tracking['organisme'] = $license->website;
		}

		return $trackings;

	}

	public function ajaxProcessgetEducationTrackingRequest() {

		die(Tools::jsonEncode($this->getEducationTrackingRequest()));

	}

	public function getEducationTrackingFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_tracking',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'left',
				'valign'     => 'center',
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->l('Organisme'),
				'width'    => 150,
				'dataIndx' => 'organisme',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Session'),
				'width'    => 150,
				'dataIndx' => 'date_begin',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
			],
			[
				'title'    => $this->l('Titre'),
				'width'    => 150,
				'dataIndx' => 'title',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Nom de Famille'),
				'width'    => 150,
				'dataIndx' => 'lastname',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Nom Civile'),
				'width'    => 150,
				'dataIndx' => 'birthname',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Prénom'),
				'width'    => 150,
				'dataIndx' => 'firstname',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Email'),
				'width'    => 150,
				'dataIndx' => 'email',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Adresse'),
				'width'    => 150,
				'dataIndx' => 'address1',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Adresse (suite)'),
				'width'    => 150,
				'dataIndx' => 'address2',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Code Postale'),
				'width'    => 150,
				'dataIndx' => 'postcode',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Ville'),
				'width'    => 150,
				'dataIndx' => 'city',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Téléphone'),
				'width'    => 150,
				'dataIndx' => 'phone_mobile',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Matériel'),
				'width'    => 150,
				'dataIndx' => 'supplyName',
				'dataType' => 'string',
				'align'    => 'left',
				'valign'   => 'center',
			],
			[
				'title'    => $this->l('Tracking'),
				'width'    => 150,
				'dataIndx' => 'tracking',
				'dataType' => 'string',
				'editable' => true,
				'align'    => 'left',
				'valign'   => 'center',
			],

		];

	}

	public function ajaxProcessgetEducationTrackingFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONTRACKINGS_FIELDS'));
	}

	public function ajaxProcessExportExpeditionMonth() {

		$file = fopen("testExportExpeditionMonth.txt", "w");
		$idMonth = Tools::getValue('idMonth');

		$dateStart = date("Y") . '-' . $idMonth . '-01';
		$date = new DateTime($dateStart);
		$dateEnd = $date->format("Y-m-t");

		$headerStyle = [
			'font' => [
				'bold' => true,
			],
		];
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
		$licenses = License::getLiceneCollection();
		$expeditions = [];

		foreach ($licenses as $licence) {
			$expeditions[$licence->id] = EducationTracking::getTrackingByMonth($licence->id, $dateStart, $dateEnd);
		}

		$titles = [
			'title'        => 'Civilité',
			'lastname'     => 'Nom',
			'birthname'    => 'Nom de naissance',
			'firstname'    => 'Prénom',
			'email'        => 'Email',
			'address1'     => 'Adresse',
			'address2'     => 'Adresse(suite)',
			'postcode'     => 'Code Postale',
			'city'         => 'Ville',
			'phone_mobile' => 'Téléphone Portable',
			'date_begin'   => 'Date de démarrage',
			'supplyName'   => 'Matériel à envoyer',
		];

		$column = chr(64 + count($titles));
		$spreadsheet = new Spreadsheet();
		$i = 0;

		foreach ($expeditions as $key => $student) {
			$license = new License($key);
			$spreadsheet->createSheet();
			$spreadsheet->setActiveSheetIndex($i);
			$spreadsheet->getActiveSheet()->setTitle('Expéditions ' . $license->website);
			$i++;
		}

		$j = 0;

		foreach ($expeditions as $key => $education) {
			$i = 1;
			$k = 0;
			$spreadsheet->getSheet($j);

			foreach ($titles as $key => $value) {
				$k++;
				$letter = chr(64 + $k);

				$spreadsheet->setActiveSheetIndex($j)->setCellValue($letter . $i, $value);
				$spreadsheet->getActiveSheet($j)->getColumnDimension($letter)->setAutoSize(true);

			}

			$spreadsheet->getActiveSheet($j)->getStyle('A1:' . $column . $i)->getAlignment()->setVertical(Alignment::HORIZONTAL_CENTER);
			$spreadsheet->getActiveSheet($j)->getStyle('A1:' . $column . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet($j)->getStyle('A1:' . $column . $i)->getFont()->setSize(12);

			$j++;
		}

		$j = 0;

		foreach ($expeditions as $key => $education) {
			$i = 2;
			$spreadsheet->getSheet($j);

			foreach ($education as $k => $student) {

				$index = 0;

				foreach ($student as $key => $value) {
					fwrite($file, $key . PHP_EOL);

					if (array_key_exists($key, $titles)) {
						$index++;

						if ($key == 'date_begin') {

							if ($student[$key] == '0000-00-00') {
								$student[$key] = 'Inconnue';
							} else {
								$date = new DateTime($student[$key]);
								$student[$key] = $date->format('d/m/Y');
							}

						}

						$letter = chr(64 + $index);
						$spreadsheet->setActiveSheetIndex($j)
							->setCellValue($letter . $i, $student[$key]);

						$spreadsheet->getActiveSheet($j)->getColumnDimension($letter)->setAutoSize(true);
						$spreadsheet->getActiveSheet($j)->getStyle('K' . $i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_DATE_DDMMYYYY);
						$spreadsheet->getActiveSheet($j)->getStyle('J' . $i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
						$spreadsheet->getActiveSheet($j)->getStyle('H' . $i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_TEXT);
					}

				}

				$i++;
			}

			$j++;
		}

		$tag = date("H-i-s");
		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'Export candidats' . $tag . '.xlsx');
		$response = [
			'fileExport' => 'fileExport' . DIRECTORY_SEPARATOR . 'Export candidats' . $tag . '.xlsx',
		];
		die(Tools::jsonEncode($response));
	}

	public function ajaxProcessUpdateListing() {

		$licenses = License::getLiceneCollection();
		$expeditions = [];

		foreach ($licenses as $licence) {
			$expeditions[$licence->id] = $licence->getExpeditionFile();
		}

		foreach ($expeditions as $key => $sessions) {
			$licence = new License($key);

			foreach ($sessions as $date => $value) {
				$phpdate = strtotime($date);
				$mysqldate = 'Expédition pour la Session du ' . date('d/m/Y', $phpdate);

				foreach ($value as $val) {

					if (!EducationTracking::trackingExist($licence->id, $val['idStudentEducation'])) {
						$tracking = new EducationTracking();
						$tracking->id_license = $licence->id;
						$tracking->id_student_education = $val['idStudentEducation'];
						$tracking->date_begin = $val['date_begin'];
						$tracking->title = $val['student']['title'];
						$tracking->lastname = $val['student']['lastname'];
						$tracking->birthname = $val['student']['birthname'];
						$tracking->firstname = $val['student']['firstname'];
						$tracking->email = $val['student']['email'];
						$tracking->address1 = $val['student']['address1'];
						$tracking->address2 = $val['student']['address2'];
						$tracking->postcode = $val['student']['postcode'];
						$tracking->city = $val['student']['city'];
						$tracking->phone_mobile = $val['student']['phone_mobile'];
						$tracking->supplyName = $val['supplyName'];
						$tracking->session = $mysqldate;
						$tracking->add();
					}

				}

			}

		}

		$response = [
			'success' => true,
		];
		die(Tools::jsonEncode($response));
	}

}
