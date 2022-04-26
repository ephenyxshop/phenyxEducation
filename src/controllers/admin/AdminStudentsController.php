<?php

use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Class AdminStudentsControllerCore
 *
 * @since 1.8.1.0
 */
class AdminStudentsControllerCore extends AdminController {

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
	 * AdminStudentsControllerCore constructor.
	 *
	 * @since 1.8.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->required_fields = ['newsletter', 'optin'];
		$this->table = 'student';
		$this->className = 'Student';
		$this->publicName = $this->l('Student');
		$this->lang = false;
		$this->identifier = 'id_student';
		$this->controller_name = 'AdminStudents';
		$this->context = Context::getContext();

		$this->default_form_language = $this->context->language->id;
		//EmployeeConfiguration::updateValue('EXPERT_STUDENTS_FIELDS', Tools::jsonEncode($this->getStudentFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTS_FIELDS', $this->context->employee->id), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTS_FIELDS', Tools::jsonEncode($this->getStudentFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTS_FIELDS'), true);
		}

		//EmployeeConfiguration::updateValue('EXPERT_STUDENTS_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_STUDENTS_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_STUDENTS_SCRIPT');
		}

		parent::__construct();

	}

	public function setStudentCode() {

		$students = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student`')
				->from('student')
				->where('`student_code` = ""')
		);

		foreach ($students as $student) {
			$objet = new Student($student['id_student']);
			$objet->student_code = substr($objet->address_zipcode, 0, 2) . 'ST' . sprintf("%04s", $objet->id);
			$objet->update();
		}

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminStudents'          => $this->context->link->getAdminLink('AdminStudents'),
			'AjaxLinkAdminStudentEducations' => $this->context->link->getAdminLink('AdminStudentEducations'),

		]);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fancy_fileupload.css', 'all', 0);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/student.css', 'all', 0);

		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/student.js',

		]);

	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Students List');

		$this->context->smarty->assign([
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'allowExport'        => true,
			'fieldsExport'       => $this->getExportFields(),
			'controller'         => Tools::getValue('controller'),
			'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'             => 'grid_AdminStudents',
			'tableName'          => $this->table,
			'className'          => $this->className,
			'linkController'     => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript'     => $this->paragridScript,
			'titleBar'           => $this->TitleBar,
			'bo_imgdir'          => '/themes/' . $this->bo_theme . '/img/',
			'idController'       => '',
		]);

		parent::initContent();
	}

	public function generateParaGridScript($regenerate = false) {

		$context = Context::getContext();
		$controllerLink = $context->link->getAdminLink($this->controller_name);
		$this->genderSelector = '<div class="pq-theme"><select id="genderSelect"><option value="">' . $this->l('--Select--') . '</option>';

		foreach (Gender::getGenders() as $gender) {
			$this->genderSelector .= '<option value="' . $gender->id . '">' . $gender->name . '</option>';
		}

		$this->genderSelector .= '</select></div>';

		$gridExtraFunction = ['function buildStudentFilter(){
           	var conteneur = $(\'#genderSelector\').parent().parent();
			$(conteneur).empty();
			$(conteneur).append(\'' . $this->genderSelector . '\');
			$(\'#genderSelect\' ).selectmenu({
        		"change": function(event, ui) {

					grid' . $this->className . '.filter({
    					mode: \'AND\',
    					rules: [
        					{ dataIndx: \'id_gender\', condition: \'equal\', value: ui.item.value}
    					]
					});
    			}
			});

			var activeconteneur = $(\'#activeSelector\').parent().parent();
			$(activeconteneur).empty();
			$(activeconteneur).append(\'' . $this->activeSelector . '\');
			$(\'#activeSelect\' ).selectmenu({
        		"change": function(event, ui) {
					grid' . $this->className . '.filter({
    					mode: \'AND\',
    					rules: [
        					{ dataIndx:\'active\', condition: \'equal\', value: ui.item.value}
    					]
        			});
    			}
			});



        	}
			function adjustGridPosition() {

				var height = $(\'.pq-cont-inner.pq-cont-right\').innerHeight();
				height = parseInt(height)+380;

				setTimeout(function(){
					adjustPosition(height);
					}, 3000 );

				}

			function adjustPosition(height) {
				$(\'.pq-table-right.pq-table.pq-td-border-top.pq-td-border-right\').css({"height": height+"px"})
			}', ];

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = "800";
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 100,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){
		buildStudentFilter();
		window.dispatchEvent(new Event(\'resize\'));
		$("#ajax_running").hide();

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->toolbar = [
			'items' => [
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Add new Student') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addNewStudent();
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
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
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
                            name: \'' . $this->l('Add new Student') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewStudent();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editStudent(rowData.id_student);
                            }
                        },
						"new": {
                            name : \'' . $this->l('Ajouter un formation pour ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "educ",
							callback: function(itemKey, opt, e) {
                             	addNewStudentEducation(rowData.id_student);
                            }
                        },
						"suivi": {
                            name : \'' . $this->l('Fenêtre de suivis pour l‘étudiant ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "window",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }

                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								openStudentSuivie(rowData.id_student);
                            }
                        },


                        "sep2": "---------",
                        "select": {
                            name: \'' . $this->l('Select all item') . '\',
                            icon: "list-ul",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length;
                                var dataLenght = ' . 'grid' . $this->className . '.option(\'dataModel.data\').length;
                                if(dataLenght == selected) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                                selgrid' . $this->className . '.selectAll();
                            }
                        },
                        "unselect": {
                            name: \'' . $this->l('Unselect all item') . '\',
                            icon: "list-ul",
                            visible: function(key, opt){
                                var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 2) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
                                ' . 'grid' . $this->className . '.setSelection( null );
                            }
                        },
						 "sep1": "---------",
						"turnAgent": {
                            name : \'' . $this->l('Convertir en agent commercial ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "ass",
							visible: function(key, opt) {
                                if (rowData.is_agent == 1) {
                                    return false;
                                }
                                return true;
                            },
							callback: function(itemKey, opt, e) {
                             	turnAgent(rowData.id_student);
                            }
                        },

                        "sep3": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                var idStudent = rowData.id_student;
                                deleteStudentt(idStudent, rowIndex);
                            }
                        },
                        "bulkdelete": {
                            name: \'' . $this->l('Delete the selected student') . '\',
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected < 2) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
								proceedBulkDelete(selgrid' . $this->className . ');
                            }
                        },

                    },
                };
            }',
			]];

		$paragrid->gridExtraFunction = $gridExtraFunction;

		$option = $paragrid->generateParaGridOption();
		$script = $paragrid->generateParagridScript();

		$this->paragridScript = $script;
		return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getStudentRequest() {

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, gl.name as title, cl.name as countryName, case when s.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as student_state')
				->from('student', 's')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = s.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('country_lang', 'cl', 'cl.`id_country` = s.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
				->orderBy('s.`id_student` DESC')
		);

		foreach ($students as &$student) {

			$student['addLink'] = $this->context->link->getAdminLink($this->controller_name) . '&action=addNewStudent&ajax=true';

			$student['deleteLink'] = $this->context->link->getAdminLink($this->controller_name) . '&id_student=' . $student['id_student'] . '&action=deleteObject&ajax=true';

			$hasEducation = Student::getLastStudentEducation($student['id_student']);

			if ($hasEducation) {
				$student['lastEducation'] = '<div>' . $hasEducation['name'] . '</div>';
				$student['hasEducation'] = 1;
			} else {
				$student['lastEducation'] = '<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>';
				$student['hasEducation'] = 0;
			}

		}

		return $students;

	}

	public function ajaxProcessgetStudentRequest() {

		die(Tools::jsonEncode($this->getStudentRequest()));

	}

	public function getStudentFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_student',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'      => ' ',
				'dataIndx'   => 'is_agent',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => ' ',
				'dataIndx'   => 'hasEducation',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'addLink',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'openLink',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'deleteLink',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[
				'title'      => '',
				'dataIndx'   => 'id_gender',
				'dataType'   => 'integer',
				'editable'   => false,
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [

					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'    => $this->l('Code étudiant'),
				'width'    => 150,
				'dataIndx' => 'student_code',
				'align'    => 'left',
				'editable' => false,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Civilité'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'title',
				'align'    => 'center',
				'dataType' => 'string',
				'editable' => true,
				'filter'   => [
					'attr'   => "id=\"genderSelector\", placeholder=" . $this->l('--Select--') . " readonly",
					'crules' => [['condition' => "equal"]],
				],

			],

			[
				'title'    => $this->l('First Name'),
				'width'    => 150,
				'exWidth'  => 25,
				'dataIndx' => 'firstname',
				'align'    => 'left',
				'editable' => true,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Last Name'),
				'width'    => 150,
				'exWidth'  => 25,
				'dataIndx' => 'lastname',
				'dataType' => 'string',
				'halign'   => 'HORIZONTAL_LEFT',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Birth Last Name'),
				'width'    => 150,
				'dataIndx' => 'birthname',
				'dataType' => 'string',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Email address'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'email',
				'dataType' => 'string',
				'halign'   => 'HORIZONTAL_LEFT',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],

			],
			[
				'title'    => $this->l('Mot de passe Front Office'),
				'width'    => 100,
				'dataIndx' => 'password',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],

			[
				'title'    => $this->l('Address'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'address_street',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
			],
			[
				'title'    => $this->l('Address (follow)'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'address_street2',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
			],

			[
				'title'    => $this->l('Post Code'),
				'width'    => 150,
				'exWidth'  => 20,
				'dataIndx' => 'address_zipcode',
				'align'    => 'left',
				'halign'   => 'HORIZONTAL_LEFT',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('City'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'address_city',
				'halign'   => 'HORIZONTAL_LEFT',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Mobile Phone'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'phone_mobile',
				'halign'   => 'HORIZONTAL_LEFT',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => true,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => $this->l('Date d\'inscription'),
				'minWidth' => 150,
				'dataIndx' => 'date_add',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',
				'editable' => false,

			],
			[
				'title'    => $this->l('Dernière formation'),
				'width'    => 150,
				'dataIndx' => 'lastEducation',

				'dataType' => 'html',
				'editable' => false,
				'hidden'   => false,

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
				'title'    => $this->l('État du compte'),
				'width'    => 100,
				'dataIndx' => 'student_state',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"activeSelector\", placeholder=" . '--Selectionner--' . " readonly",
					'crules' => [['condition' => "equal"]],
				],
			],

		];

	}

	public function ajaxProcessgetStudentFields() {

		$fields = EmployeeConfiguration::get('EXPERT_STUDENTS_FIELDS');
		die($fields);
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessUpdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_STUDENTS_FIELDS'), true);
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
		EmployeeConfiguration::updateValue('EXPERT_STUDENTS_FIELDS', $headerFields);
		$response = [
			'headerFields' => $headerFields,
		];

		die($headerFields);
	}

	public function ajaxProcessupdateJsonVisibility() {

		$visibility = Tools::getValue('visibilities');
	}

	public function ajaxProcessImportStudent() {

		$studentEntry = Tools::getValue('studentEntry');

		foreach ($studentEntry as $students) {

			$student = new Student();

			foreach ($students as $key => $value) {

				if (property_exists($student, $key) && $key != 'id_student') {

					if ($key == 'passwd' && Tools::getValue('id_student') && empty($value)) {
						continue;
					}

					if ($key == 'birthday' && !empty($value)) {

						$date = DateTime::createFromFormat('d/m/Y', $value);
						$value = date_format($date, "Y-m-d");
					}

					$student->{$key} = trim($value);
				}

			}

			$password = Tools::generateStrongPassword();
			$student->passwd = Tools::hash($password);
			$student->password = $password;
			$student->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
			$student->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
			$student->newsletter = 1;
			$student->active = 1;
			$student->id_country = 8;
			$student->student_code = Student::generateStudentCode($student->id_country, $student->address_zipcode);
			$student->id_stdaccount = Student::generateStudentAccount($student->student_code);
			$mobile = str_replace(' ', '', $student->phone_mobile);

			if (strlen($mobile) == 10) {
				$mobile = '+33' . substr($mobile, 1);
				$student->phone_mobile = $mobile;
			}

			$checkEmail = Db::getInstance()->getValue(
				(new DbQuery())
					->select('`id_student`')
					->from('student')
					->where('`email` LIKE \'' . $student->email . '\'')
			);

			if ($checkEmail > 0) {
				continue;
			}

			try {
				$student->add();
				
				$suivie = new StudentSuivie();
				$suivie->id_customer = $student->id;
				$suivie->id_employee = $this->context->employee->id;
				$suivie->id_employee = 0;
				$suivie->content = 'Inscription de '.$student->firstname.' '.$student->lastname.' par '.$this->context->employee->firstname.' '.$this->context->employee->lastname;
			$suivie->add();
				$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/account.tpl');
				$tpl->assign([
					'firstname'       => $student->firstname,
					'lastname'        => $student->lastname,
					'email'           => $student->email,
					'student_code'    => $student->student_code,
					'address_street'  => $student->address_street,
					'address_zipcode' => $student->address_zipcode,
					'address_city'    => $student->address_city,
					'phone_mobile'    => $student->phone_mobile,
				]);
				$postfields = [
					'sender'      => [
						'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
						'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
					],
					'to'          => [
						[
							'name'  => "Service  Comptabilité ".Configuration::get('PS_SHOP_NAME'),
							'email' => 'comptabilite@ld-formation.fr',
						],
					],
					'subject'     => 'Nouvelle inscription de ' . $student->firstname . ' ' . $student->lastname,
					"htmlContent" => $tpl->fetch(),
				];
				$result = Tools::sendEmail($postfields);

			} catch (Exception $e) {
				$errors[] = $e->getMessage();
			}

		}

		if (empty($errors)) {

			$result = [
				'success' => true,
				'message' => 'Les étudiants ont été ajouté avec succès à la base de donnée.',
			];
		} else {
			$errors = array_unique($errors);
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $errors),
			];
		}

		die(Tools::jsonEncode($result));

	}
	
	public static function ajaxProcessgetAutoCompleteSession() {

		$query = Tools::getValue('search');

		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_education_session, session_date, name')
				->from('education_session')
				->where('name LIKE \'%' . pSQL($query) . '%\'')
		);
		die(Tools::jsonEncode($sessions));
	}

	public static function ajaxProcessGetAutoCompleteEducation() {

		$context = Context::getContext();
		$results = [];
		$query = Tools::getValue('search');
		$sql = 'SELECT p.`id_education`, pl.`link_rewrite`, p.`reference`, pl.`name`, p.`days`, image_education.`id_image_education` id_image, il.`legend`, p.`cache_default_attribute`
		FROM `' . _DB_PREFIX_ . 'education` p
		LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` pl ON (pl.id_education = p.id_education AND pl.id_lang = ' . (int) $context->language->id . ')
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education` image_education
			ON (image_education.`id_education` = p.`id_education` AND image_education.cover=1)
		LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (image_education.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $context->language->id . ')
		WHERE (pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')' .
			' GROUP BY p.id_education';

		$items = Db::getInstance()->executeS($sql);

		if ($items) {

			foreach ($items as $item) {

				if ($item['cache_default_attribute']) {
					$sql = 'SELECT pa.`id_education_attribute`, pa.`reference`, pa.`days`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name,
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
							$results[$combination['id_education_attribute']]['days'] = $combination['days'];
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
							'days'					 => $item['days'],
							'ref'                    => (!empty($item['reference']) ? $item['reference'] : ''),

						];
					}

				} else {
					$education = [
						'id_education'           => (int) ($item['id_education']),
						'id_education_attribute' => 0,
						'name'                   => $item['name'],
						'days'					 => $item['days'],
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

	public function ajaxProcessSuggestPassword() {

		$return = [
			'passwd' => Tools::generateStrongPassword(),
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessCheckEmail() {

		$email = Tools::getValue('email');

		$checkExist = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_student`')
				->from('student')
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

	public function ajaxProcessaddNewStudent() {

		$data = $this->createTemplate('controllers/students/newStudent.tpl');
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditStudent() {

		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);

		$titleTab = '';

		$data = $this->createTemplate('controllers/students/editStudent.tpl');
		
		$address = null;
        $addresses = Address::getBAddressesByStudentId($student->id);
		$id_address = Address::getFirstStudentAddressId($student->id);
        if ($id_address > 0) {
            $address = new Address((int) $id_address);
        }
		
		 $data->assign([
            'student'		=> $student,
            'countries'         => Country::getCountries($this->context->language->id, false),
			'default_country'   => Configuration::get('PS_COUNTRY_DEFAULT'),
           	'genders'           => Gender::getGenders(),
            'addresses'         => $addresses,
			'courses'            	=> Student::getStudentEducations($student->id),
            'link'              => $this->context->link,
           
        ]);

		if (Validate::isLoadedObject($student)) {
			
		
			$data->assign('deletedCourses', Student::getDeletedStudentEducations($student->id));
			$data->assign('jsLink', __PS_BASE_URI__ . $this->admin_webpath . '/js/editStudent.js');
			$titleTab = $this->l('Edite student') . ' ' . $student->firstname . ' ' . $student->lastname;

		} else {

		}

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));

	}

	

	public function ajaxProcessaddNewStudentEducation() {

		$isSession = Configuration::get('PS_SESSION_FEATURE_ACTIVE');
		if($isSession) {
			$data = $this->createTemplate('controllers/students/addEducation.tpl');
		} else {
			$data = $this->createTemplate('controllers/students/addEducationSession.tpl');
		}
		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);
		$data->assign('student', $student);
		$data->assign('slots', EducationSession::getNextEducationSlot());
		$data->assign('supplies', EducationSupplies::getEducationSupplies());
		$data->assign('agents', SaleAgent::getSaleAgents());
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditStudentEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$student_education = new StudentEducation($id_student_education);
		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);

		$data = $this->createTemplate('controllers/students/editEducation.tpl');

		$data->assign('education', Student::getStudentEducationById($id_student_education));
		$data->assign('educationSteps', StudentEducationStep::getEducationStep());
		$data->assign('student', $student);
		$data->assign('supplies', EducationSupplies::getEducationSupplies());
		$data->assign('student_education', $student_education);
		$data->assign('agents', SaleAgent::getSaleAgents());

		$result = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessNewStudent() {

		
		$student = new Student();

		foreach ($_POST as $key => $value) {

			if (property_exists($student, $key) && $key != 'id_student') {

				if ($key == 'passwd' && Tools::getValue('id_student') && empty($value)) {
					continue;
				}

				$student->{$key}
				= $value;
			}

		}

		$password = Tools::generateStrongPassword();
		$student->passwd = Tools::hash($password);
		$student->password = $password;
		$student->ip_registration_newsletter = pSQL(Tools::getRemoteAddr());
		$student->newsletter_date_add = pSQL(date('Y-m-d H:i:s'));
		$student->newsletter = 1;
		$student->active = 1;
		$student->id_country = 8;
		$student->student_code = Student::generateStudentCode($student->id_country, $student->address_zipcode);
		$student->id_stdaccount = Student::generateStudentAccount($student->student_code);
		$mobile = str_replace(' ', '', $student->phone_mobile);

		if (strlen($mobile) == 10) {
			$mobile = '+33' . substr($mobile, 1);
			$student->phone_mobile = $mobile;
		}

		$checkEmail = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_student`')
				->from('student')
				->where('`email` LIKE \'' . $student->email . '\'')
		);

		if ($checkEmail > 0) {
			$result = [
				'success' => false,
				'message' => 'L\'email de cette étudiont existe déjà dans la base donnée.',
			];
			die(Tools::jsonEncode($result));
		}

		
		try {
			$student->add();

			$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/admin_account.tpl');
			$tpl->assign([
				'student'   => $student,
				'firstname' => $student->firstname,
				'lastname'  => $student->lastname,
				'email'     => $student->email,
				'passwd'    => $student->password,
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
			$result = [
				'success' => true,
				'message' => 'L\'étudiants a été ajouté avec succès à la base de donnée.',
			];

		} catch (Exception $e) {

			$result = [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessUpdateEducation() {

		$id_student_education = Tools::getValue('id_student_education');

		$education = new StudentEducation($id_student_education);

		foreach ($_POST as $key => $value) {

			if (property_exists($education, $key) && $key != 'id_student_education') {

				if (Tools::getValue('id_student_education') && empty($value)) {
					continue;
				}

				$education->{$key}
				= $value;

			}

		}

		$result = $education->update();

		if ($result) {
			$education = new StudentEducation($id_student_education);
			$html = '<td>' . $education->name . '</td><td>' . $education->sessionName . '</td><td>' . $education->price . '</td><td>' . $education->priceWTax . '</td><td>' . $education->state . '</td><td><button class="button editEducation" onclick="editEducation(' . $education->id . ', ' . $education->id_student . ' )">Modifier</button></td>';
			$return = [
				'success'     => true,
				'html'        => $html,
				'idEducation' => $education->id,
				'message'     => $this->l('La session de formation a été mise à jour avec succès'),
			];
		} else {
			$return = [
				'success' => false,
				'message' => $this->l('Une erreur s\'est produite en essayant de mettre à jour cette session de formation'),
			];
		}

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessDeleteStudentEducation() {

		$id_student_education = Tools::getValue('id_student_education');
		$education = new StudentEducation($id_student_education);
		$education->delete();

		$return = [
			'success' => true,
			'message' => $this->l('La session de formation a été supprimée avec succès'),
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessBulkDelete() {

		$students = Tools::getValue('students');

		foreach ($students as $student) {
			$object = new Student($student);

			if (!$object->delete()) {
				$this->errors[] = Tools::displayError('An error occurred while deleting the student ' . $object->firstname);
			}

		}

		$this->errors = array_unique($this->errors);

		if (count($this->errors)) {
			$result = [
				'success' => false,
				'message' => implode(PHP_EOL, $this->errors),
			];
		} else {
			$result = [
				'success' => true,
				'message' => $this->l('Selection of students has been properly deleted'),
			];
		}

		die(Tools::jsonEncode($result));

	}

	public function postProcess() {

		if (!$this->can_add_student && $this->display == 'add') {
			$this->redirect_after = $this->context->link->getAdminLink('AdminStudents');
		}

		parent::postProcess();
	}

	public function initToolbarTitle() {

		parent::initToolbarTitle();

		switch ($this->display) {
		case '':
		case 'list':
			array_pop($this->toolbar_title);
			$this->toolbar_title[] = $this->l('Manage your Students');
			break;
		case 'view':
			/** @var Student $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				array_pop($this->toolbar_title);
				$this->toolbar_title[] = sprintf($this->l('Information about Student: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			}

			break;
		case 'add':
		case 'edit':
			array_pop($this->toolbar_title);
			/** @var Student $student */

			if (($student = $this->loadObject(true)) && Validate::isLoadedObject($student)) {
				$this->toolbar_title[] = sprintf($this->l('Editing Student: %s'), mb_substr($student->firstname, 0, 1) . '. ' . $student->lastname);
			} else {
				$this->toolbar_title[] = $this->l('Creating a new Student');
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

	public function initProcess() {

		parent::initProcess();

		if (Tools::isSubmit('submitGuestToStudent') && $this->id_object) {

			if ($this->tabAccess['edit'] === '1') {
				$this->action = 'guest_to_student';
			} else {
				$this->errors[] = Tools::displayError('You do not have permission to edit this.');
			}

		} else

		if (Tools::isSubmit('changeNewsletterVal') && $this->id_object) {

			if ($this->tabAccess['edit'] === '1') {
				$this->action = 'change_newsletter_val';
			} else {
				$this->errors[] = Tools::displayError('You do not have permission to edit this.');
			}

		} else

		if (Tools::isSubmit('changeOptinVal') && $this->id_object) {

			if ($this->tabAccess['edit'] === '1') {
				$this->action = 'change_optin_val';
			} else {
				$this->errors[] = Tools::displayError('You do not have permission to edit this.');
			}

		}

		// When deleting, first display a form to select the type of deletion

		if ($this->action == 'delete' || $this->action == 'bulkdelete') {

			if (Tools::getValue('deleteMode') == 'real' || Tools::getValue('deleteMode') == 'deleted') {
				$this->delete_mode = Tools::getValue('deleteMode');
			} else {
				$this->action = 'select_delete';
			}

		}

	}

	public function beforeAdd($student) {

		$student->id_shop = $this->context->shop->id;
	}

	public function ajaxProcessSearchStudents() {

		$searches = explode(' ', Tools::getValue('student_search'));
		$students = [];
		$searches = array_unique($searches);

		foreach ($searches as $search) {

			if (!empty($search) && $results = Student::searchByName($search, 50)) {

				foreach ($results as $result) {

					if ($result['active']) {
						$students[$result['id_student']] = $result;
					}

				}

			}

		}

		if (count($students)) {
			$toReturn = [
				'students' => $students,
				'found'    => true,
			];
		} else {
			$toReturn = ['found' => false];
		}

		$this->content = json_encode($toReturn);
	}

	public function ajaxProcessUpdateStudentNote() {

		if ($this->tabAccess['edit'] === '1') {
			$note = Tools::htmlentitiesDecodeUTF8(Tools::getValue('note'));
			$student = new Student((int) Tools::getValue('id_student'));

			if (!Validate::isLoadedObject($student)) {
				die('error:update');
			}

			if (!empty($note) && !Validate::isCleanHtml($note)) {
				die('error:validation');
			}

			$student->note = $note;

			if (!$student->update()) {
				die('error:update');
			}

			die('ok');
		}

	}

	public function ajaxProcessUpdateStudent() {

		$id = (int) Tools::getValue('id_student');
		
		if (isset($id) && !empty($id)) {
			/** @var ObjectModel $object */
			$object = new $this->className($id);

			if (Validate::isLoadedObject($object)) {
				/* Specific to objects which must not be deleted */
				$oldPasswd = $object->passwd;

				foreach ($_POST as $key => $value) {

					if (property_exists($object, $key) && $key != 'id_student') {
						fwrite($file, $key.' => '.$value.PHP_EOL);
						if ($key == 'passwd' && Tools::getValue('id_student') && empty($value)) {
							continue;
						}

						if ($key == 'passwd' && Tools::getValue('id_student') && !empty($value)) {
							$newPasswd = Tools::hash(Tools::getValue('passwd'));

							if ($newPasswd == $oldPasswd) {
								continue;
							}

							$value = $newPasswd;
							$object->password = Tools::getValue('passwd');
						}

						

						$object->{$key}	= $value;
					}

				}
				
				$result = $object->update();

				if (!isset($result) || !$result) {
					$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
				} else {
					$result = [
						'success' => true,
						'message' => $this->l('Update successful'),
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

	protected function processBulkDelete() {

		$this->_setDeletedMode();
		parent::processBulkDelete();
	}

	protected function afterDelete($object, $oldId) {

		$student = new Student($oldId);
		$addresses = $student->getAddresses($this->default_form_language);

		foreach ($addresses as $k => $v) {
			$address = new Address($v['id_address']);
			$address->id_student = $object->id;
			$address->save();
		}

		return true;
	}

	public function ajaxProcessTurnAgent() {

		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);

		$data = $this->createTemplate('controllers/sale_agent/newAgent.tpl');
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));
		$data->assign('student', $student);

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessNewAgent() {

		$agent = new SaleAgent();
		$id_student = Tools::getValue('id_student');
		$student = new Student($id_student);

		foreach ($_POST as $key => $value) {

			if (property_exists($agent, $key) && $key != 'id_sale_agent') {
				$agent->{$key}
				= $value;

			}

		}

		$agent->active = 1;
		$result = $agent->add();

		if ($result) {
			$student->is_agent = 1;
			$student->update();
			$return = [
				'success' => true,
				'message' => 'L\'étudiant a été transformé en agent avec succès.',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Jeff a merdé somewhere over the rainbow.',
			];
		}

		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessOpenStudentSuivie() {

		
		$idStudent = Tools::getValue('idStudent');
		$student = new Customer($idStudent);
	
		$context = Context::getContext();

		$linkContract = '';

		$suivies = StudentSuivie::getSuivieById($student->id, $student->id_customer);


		

		$employees = Employee::getEmployees();
		$data = $this->createTemplate('controllers/students/suivie.tpl');
		$data->assign('suivies', $suivies);
		$data->assign('student', $student);
		$data->assign('employees', $employees);
		$data->assign('idEmployee', $this->context->employee->id);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessAddStudentSuivie() {
		
		$file = fopen("testProcessAddStudentSuivie.txt","w");
		
		$student = new Student(Tools::getValue('id_student'));
		
		$suivie = new StudentSuivie();
		$suivie->id_student = $student->id;
		$suivie->id_customer = $student->id_customer;
		$suivie->id_employee = $this->context->employee->id;
		$suivie->content = Tools::getValue('content');
		$employee = new Employee($this->context->employee->id);
		fwrite($file, print_r($suivie, true));
		
		$suivie->add();
		$html = ' <tr id="suivie_' . $suivie->id . '"><td>Le ' . date('d/m/Y') . '</td><td>'.$employee->firstname.' '.$employee->lastname.'</td><td>' . $suivie->content . '</td><td><i class="icon icon-trash" style="color: red;" aria-hidden="true" onClick="deleteStudentSuivie({' . $suivie->id . ')"></i></td>
		 	<td><i class="icon icon-pencil-square-o" aria-hidden="true" onClick="editStudentSuivie(' . $suivie->id . ')"></i></td></tr>';
		$result = [
			'message' => 'Le suivis a été ajouté avec succès',
			'success' => true,
			'html'    => $html,
		];
		die(Tools::jsonEncode($result));
		
	}
	
	public function ajaxProcessEditStudentSuivie() {

		$idSuivie = Tools::getValue('idSuivie');
		$suivie = new StudentSuivie($idSuivie);
		$employee = new Employee($suivie->id_employee);
		$student = new Student($suivie->id_student);
		$data = $this->createTemplate('controllers/students/editEvent.tpl');
		$data->assign('suivie', $suivie);
		$data->assign('student', $student);
		$data->assign('contact', $employee);
		$data->assign('employees', Employee::getEmployees());
		$data->assign('idEmployee', $context->employee->id);
		$result = [
			'html'    => $data->fetch(),
			'success' => true,
		];
		die(Tools::jsonEncode($result));

	}
	
	public function ajaxProcessUpdateStudentSuivie() {
		
		$idSuivie = Tools::getValue('id_suivie');
		$suivie = new StudentSuivie($idSuivie);
		$suivie->content = Tools::getValue('content');
		$suivie->id_employee = $this->context->employee->id;
		$suivie->update();
		$html = '<td>Le ' . date('d/m/Y') . '</td><td>'.$employee->firstname.' '.$employee->lastname.'</td><td>' . $suivie->content . '</td><td><i class="icon icon-trash" style="color: red;" aria-hidden="true" onClick="deleteStudentSuivie({' . $suivie->id . ')"></i></td>
		 	<td><i class="icon icon-pencil-square-o" aria-hidden="true" onClick="editStudentSuivie(' . $suivie->id . ')"></i></td>';
		$result = [
			'message' => 'Le suivis a été mis à jour avec succès',
			'success' => true,
			'html'    => $html,
			'id' => $suivie->id
		];
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessdeleteSuivie() {
		
		$idSuivie = Tools::getValue('idSuivie');
		$suivie = new StudentSuivie($idSuivie);
		$suivie->delete();
		$result = [
			'success' => true,
		];
		die(Tools::jsonEncode($result));
		
	}


}
