<?php

/**
 * Class AdminStudentCompaniesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminStudentCompaniesControllerCore extends AdminController {

	// @codingStandardsIgnoreStart
	protected static $meaning_status = [];
	protected $delete_mode;
	protected $_defaultOrderBy = 'date_add';
	protected $tarifs_array = [];

	public $genderSelector;
	public $groupSelector;
	public $countrySelector;
	// @codingStandardsIgnoreEnd

	/**
	 * AdminStudentCompaniesControllerCore constructor.
	 *
	 * @since 1.8.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'student_company';
		$this->className = 'StudentCompany';
		$this->publicName = $this->l('Société partenaire');
		$this->lang = false;
		$this->identifier = 'id_student_company';
		$this->controller_name = 'AdminStudentCompanies';
		$this->context = Context::getContext();

		$this->default_form_language = $this->context->language->id;
		EmployeeConfiguration::updateValue('EXPERT_STUDENTCOMPANY_FIELDS', Tools::jsonEncode($this->getStudentCompanyFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTCOMPANY_FIELDS', Tools::jsonEncode($this->getStudentCompanyFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_STUDENTCOMPANY_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTCOMPANY_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminStudentCompanies' => $this->context->link->getAdminLink('AdminStudentCompanies'),
			'AjaxLinkAdminStates'           => $this->context->link->getAdminLink('AdminStates'),

		]);

	}
	
	

	/**
	 * Initialize content
	 *
	 * @return void
	 *
	 * @since 1.8.1.0
	 */
	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des sociétés');

		$this->context->smarty->assign([
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'allowExport'        => true,
			'fieldsExport'       => $this->getExportFields(),
			'controller'         => Tools::getValue('controller'),
			'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'             => 'grid_AdminStudentCompanies',
			'tableName'          => $this->table,
			'className'          => $this->className,
			'linkController'     => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript'     => $this->generateParaGridScript(),
			'titleBar'           => $this->TitleBar,
			'bo_imgdir'          => '/themes/' . $this->bo_theme . '/img/',
			'idController'       => '',
		]);

		parent::initContent();
	}

	public function generateParaGridScript($regenerate = false) {

		if (!empty($this->paragridScript) && !$regenerate) {
			return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
		}

		$context = Context::getContext();
		$controllerLink = $context->link->getAdminLink($this->controller_name);

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = 600;
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){

		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Ajouter Une société') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addNewStudentCompany();
						}',
				],

			],
		];
		$paragrid->filterModel = [
			'on'          => true,
			'mode'        => '\'OR\'',
			'header'      => true,
			'menuIcon'    => 0,
			'gridOptions' => [
				'numberCell' => [
					'show' => 0,
				],
				'width'      => '\'flex\'',
				'flex'       => [
					'one' => true,
				],
			],
		];
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Gestion des') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';

		$paragrid->contextMenu = [
			'#grid_' . $this->controller_name => [
				'selector'  => '\'.pq-body-outer .pq-grid-row\'',
				'animation' => [
					'duration' => 250,
					'show'     => '\'fadeIn\'',
					'hide'     => '\'fadeOut\'',
				],
				'build'     => 'function($triggerElement, e){

                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . 'grid' . $this->className . '.getRowData( {rowIndx: rowIndex} );
                selected = selgrid' . $this->className . '.getSelection().length;
                var dataLenght = grid' . $this->className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->l('Add new StudentCompany') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewCompany();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editCpmpany(rowData.id_student_company);
                            }
                        },

                    },
                };
            }',
			]];

		$option = $paragrid->generateParaGridOption();
		$script = $paragrid->generateParagridScript();

		if ($regenerate) {
			return $script;
		}

		$this->paragridScript = $script;
		return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getStudentCompanyRequest() {

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, gl.name as title, cl.name as countryName, case when s.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as company_state')
				->from('student_company', 's')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = s.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('country_lang', 'cl', 'cl.`id_country` = s.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
				->orderBy('s.`id_student_company` DESC')
		);

		return $students;

	}

	public function ajaxProcessgetStudentCompanyRequest() {

		die(Tools::jsonEncode($this->getStudentCompanyRequest()));

	}

	public function getStudentCompanyFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_student_company',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => $this->l('Nom de la société'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'company_name',
				'align'    => 'center',
				'dataType' => 'string',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'    => $this->l('Civilité'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'title',
				'align'    => 'center',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,

			],

			[
				'title'    => $this->l('Prénom du dirigeant'),
				'width'    => 150,
				'exWidth'  => 25,
				'dataIndx' => 'firstname',
				'align'    => 'left',
				'editable' => true,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],
			[
				'title'    => $this->l('Nom du dirigeant'),
				'width'    => 150,
				'exWidth'  => 25,
				'dataIndx' => 'lastname',
				'dataType' => 'string',
				'halign'   => 'HORIZONTAL_LEFT',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],
			],

			[
				'title'    => $this->l('Numéro Siret'),
				'width'    => 150,
				'dataIndx' => 'siret',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],
			[
				'title'    => $this->l('Code NAF ou APE'),
				'width'    => 150,
				'dataIndx' => 'ape',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],
			[
				'title'    => $this->l('Email'),
				'width'    => 150,
				'dataIndx' => 'email',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],

			[
				'title'    => $this->l('Adresse'),
				'width'    => 150,
				'dataIndx' => 'address1',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Adresse (suite)'),
				'width'    => 150,
				'dataIndx' => 'address2',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
			],

			[
				'title'    => $this->l('Code Postale'),
				'width'    => 150,
				'dataIndx' => 'zipcode',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Ville'),
				'width'    => 150,
				'dataIndx' => 'city',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Téléphone'),
				'width'    => 150,
				'dataIndx' => 'phone',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,

			],

			[
				'title'    => ' ',
				'dataIndx' => 'active',
				'dataType' => 'integer',
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'    => $this->l('Prospect ou actif'),
				'width'    => 100,
				'dataIndx' => 'company_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetStudentCompanyFields() {

		$fields = EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_FIELDS');
		die($fields);
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessUpdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTCOMPANY_FIELDS'), true);
		$visibility = Tools::getValue('visibilities');

		foreach ($headerFields as $key => $headerField) {
			$hidden = '';

			foreach ($headerField as $field => $value) {

				if ($field == 'dataIndx') {

					if ($visibility[$value] == 1) {
						$hidden = false;
					} else

					if ($visibility[$value] == 0) {
						$hidden = true;
					}

				}

			}

			$headerField['hidden'] = $hidden;

			$headerFields[$key] = $headerField;
		}

		$headerFields = Tools::jsonEncode($headerFields);
		EmployeeConfiguration::updateValue('EXPERT_STUDENTCOMPANY_FIELDS', $headerFields);
		$response = [
			'headerFields' => $headerFields,
		];

		die($headerFields);
	}

	public function ajaxProcessupdateJsonVisibility() {

		$visibility = Tools::getValue('visibilities');
	}

	public function ajaxProcessAddNewCompany() {

		$data = $this->createTemplate('controllers/student_companies/AddCompanies.tpl');
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));
		$data->assign('ranges', StudentCompany::getEmployeeRange());

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditStudentCompany() {

		$id_student = Tools::getValue('id_student');
		$student = new StudentCompany($id_student);

		$titleTab = '';

		$data = $this->createTemplate('controllers/students/editStudentCompany.tpl');

		if (Validate::isLoadedObject($student)) {
			$data->assign('genders', Gender::getGenders());
			$data->assign('countries', Country::getCountries($this->context->language->id, true));
			$data->assign('student', $student);
			$data->assign('courses', StudentCompany::getStudentCompanyEducations($student->id));
			$data->assign('deletedCourses', StudentCompany::getDeletedStudentCompanyEducations($student->id));
			$data->assign('jsLink', __PS_BASE_URI__ . $this->admin_webpath . '/js/editStudentCompany.js');
			$titleTab = $this->l('Edite student') . ' ' . $student->firstname . ' ' . $student->lastname;

		} else {

		}

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessaddStudentCompanyEducation() {

		$data = $this->createTemplate('controllers/students/newEducation.tpl');
		$id_student = Tools::getValue('id_student');
		$student = new StudentCompany($id_student);
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getEducationSupplies());
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessaddNewStudentCompanyEducation() {

		$data = $this->createTemplate('controllers/students/addEducation.tpl');
		$id_student = Tools::getValue('id_student');
		$student = new StudentCompany($id_student);
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getEducationSupplies());
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditStudentCompanyEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$student_education = new StudentCompanyEducation($id_student_education);
		$id_student = Tools::getValue('id_student');
		$student = new StudentCompany($id_student);
		$data = $this->createTemplate('controllers/students/editEducation.tpl');

		$data->assign('education', StudentCompany::getStudentCompanyEducationById($id_student_education));
		$data->assign('educationSteps', StudentCompanyEducationStep::getEducationStep());
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupply::getEducationSupplies());
		$data->assign('student_education', $student_education);

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSaveNewCompany() {

		$student = new StudentCompany();

		foreach ($_POST as $key => $value) {

			if (property_exists($student, $key) && $key != 'id_student_company') {

				$student->{$key}
				= $value;
			}

		}

		try {
			$student->add();
		} catch (Exception $e) {
			$errors[] = $e->getMessage();
		}

		$result = [
			'success' => true,
			'message' => 'L\'étudiants a été ajouté avec succès à la base de donnée.',
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessDeleteStudentCompanyEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$education = new StudentCompanyEducation($id_student_education);
		$reason = Tools::getValue('reason');
		$education->deleted = 1;
		$education->id_student_education_state = 5;
		$education->deleted_reason = $reason;
		$education->update();
		Db::getInstance()->execute(
			'DELETE FROM `' . _DB_PREFIX_ . 'student_pieces`
                WHERE `id_student_education` = ' . (int) $education->id
		);
		$return = [
			'success' => true,
			'message' => $this->l('La session de formation a été supprimée avec succès'),
		];
		die(Tools::jsonEncode($return));
	}

	public function initToolbarTitle() {

		parent::initToolbarTitle();

		switch ($this->display) {
		case '':
		case 'list':
			array_pop($this->toolbar_title);
			$this->toolbar_title[] = $this->l('Gérer les sociétés partenaires');
			break;
		case 'view':
			/** @var StudentCompany $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				array_pop($this->toolbar_title);
				$this->toolbar_title[] = sprintf($this->l('Information about StudentCompany: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			}

			break;
		case 'add':
		case 'edit':
			array_pop($this->toolbar_title);
			/** @var StudentCompany $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				$this->toolbar_title[] = sprintf($this->l('Editing StudentCompany: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			} else {
				$this->toolbar_title[] = $this->l('Creating a new StudentCompany');
			}

			break;
		}

		array_pop($this->meta_title);

		if (count($this->toolbar_title) > 0) {
			$this->addMetaTitle($this->toolbar_title[count($this->toolbar_title) - 1]);
		}

	}

	public function initPageHeaderToolbar() {

		$this->page_header_toolbar_btn['fields_edit'] = [
			'href'       => "javascript:void(0)",
			'desc'       => $this->l('Choose available Fields to display', null, null, false),
			'identifier' => 'field',
			'controller' => $this->controller_name,
			'icon'       => 'process-icon-excel',
		];

		if (empty($this->display) && $this->can_add_student) {
			$this->page_header_toolbar_btn['new_student'] = [
				'href'       => static::$currentIndex . '&addstudent&token=' . $this->token,
				'desc'       => $this->l('Add new student', null, null, false),
				'identifier' => 'new',
				'controller' => $this->controller_name,
				'icon'       => 'process-icon-new',
			];
		}

		parent::initPageHeaderToolbar();
	}

	public function ajaxProcessUpdateStudentCompany() {

		$id = (int) Tools::getValue('id_student');

		if (isset($id) && !empty($id)) {
			/** @var ObjectModel $object */
			$object = new $this->className($id);

			if (Validate::isLoadedObject($object)) {
				/* Specific to objects which must not be deleted */
				$this->copyFromPost($object, $this->table);
				$result = $object->update();
				$this->afterUpdate($object);

				if ($object->id) {
					$this->updateAssoShop($object->id);
				}

				if (!isset($result) || !$result) {
					$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
				} else {
					$result = [
						'success'   => true,
						'message'   => $this->l('Update successful'),
						'paragrid'  => $this->paragrid,
						'loadTabs'  => $this->loadTabs,
						'id_object' => $object->id,
					];

				}

				Logger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $object->id, true, (int) $this->context->employee->id);
			} else {
				$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
			}

		}

		$this->errors = array_unique($this->errors);

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		}

		die(Tools::jsonEncode($result));

	}

}
