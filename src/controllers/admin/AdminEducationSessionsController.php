<?php

/**
 * @property Gender $object
 */
class AdminEducationSessionsControllerCore extends AdminController {

	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_session';
		$this->className = 'EducationSession';
		$this->publicName = $this->l('Education Sessions');
		$this->context = Context::getContext();

		parent::__construct();

		$isSession = Configuration::get('PS_SESSION_FEATURE_ACTIVE');

		if ($isSession) {
			EducationSession::generateSessionDate();
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSESSIONS_FIELDS', Tools::jsonEncode($this->getEducationSessionFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONSESSIONS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSESSIONS_FIELDS', Tools::jsonEncode($this->getEducationSessionFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONSESSIONS_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSESSIONS_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONSESSIONS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSESSIONS_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONSESSIONS_SCRIPT');
		}

	}

	public function generateSessionDate() {

		$dateToCreates = EducationSession::getDatesToCreate();

		if (is_array($dateToCreates) && count($dateToCreates)) {

			foreach ($dateToCreates as $dateToCreate) {
				$session = new EducationSession();
				$session->session_date = $dateToCreate['session_date'];
				$session->name = $dateToCreate['name'];
				$session->add();

			}

		}

	}


	public function setAjaxMedia() {

		return $this->pushJS([
			$this->admin_webpath . '/js/sessionlist.js',
		]);
	}

	public function ajaxProcessOpenTargetController() {

		$targetController = $this->targetController;
		$data = $this->createTemplate('controllers/' . $this->table . '.tpl');
		$days = [
			0 => 'Dimanche',
			1 => 'Lundi',
			2 => 'Mardi',
			3 => 'Mercredi',
			4 => 'Jeudi',
			5 => 'Vendredi',
			6 => 'Samedi',
		];
		$data->assign([
			'paragridScript' => $this->generateParaGridScript(),
			'controller'     => $this->controller_name,
			'tableName'      => $this->table,
			'className'      => $this->className,
			'link'           => $this->context->link,
			'extraJs'        => $this->setAjaxMedia(),
			'days'           => $days,
		]);

		$li = '<li id="uper' . $targetController . '" data-controller="AdminDashboard"><a href="#content' . $targetController . '">' . $this->publicName . '</a><button type="button" class="close tabdetail" data-id="uper' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	

	public function generateParaGridScript() {

		$date = Db::getInstance()->getValue(
			(new DbQuery())
				->select('session_date')
				->from('education_session')
				->orderBy('`session_date` DESC')
		);

		$this->paramExtraFontcion = [
			'
			function generateFirstSessions() {

				$.fancybox.open($("#sessionManagement"), {
					touch: false,
					clickContent: false,
					clickSlide: false
				});

			}


		',

		];

		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$this->paramComplete = 'function(){
		//adjustEducationSessionGridHeight();
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';
		$this->paramSelectModelType = 'row';

		if (empty($date)) {
			$this->paramToolbar = [
				'items' => [
					[
						'type'     => '\'button\'',
						'label'    => '\'Ajouter une première Série de de session\'',
						'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
						'attr'     => '\'id="first_session_btn"\'',
						'listener' => 'generateFirstSessions',
					],

				],
			];
		}

		$this->paramChange = 'function(evt, ui) {
            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var dataEducation = updateData.rowData.id_education_session;
            $.ajax({
                type: "POST",
                url: AjaxLinkAdminEducationSessions,
                data: {
                    action: "updateByVal",
                    idEducation: dataEducation,
                    field: dataField,
                    fieldValue: dataValue,
                    ajax: true
                },
                async: true,
                dataType: "json",
                success: function(data) {
                    if (data.success) {
                        showSuccessMessage(data.message);
						reloadEducationSessionGrid();
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })
        }';

		$this->paramTitle = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$this->paramContextMenu = [
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
                         "active": {
                            name : \'' . $this->l('Activer la ') . '\'' . '+rowData.name,
                            icon: "view",
							 visible: function(key, opt) {
                                if (rowData.active == 0) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                activeSession(rowData.id_education_session);
                            }
                        },
						 "inactive": {
                            name : \'' . $this->l('Désactiver la ') . '\'' . '+rowData.name,
                            icon: "view",
							 visible: function(key, opt) {
                                if (rowData.active == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                inActiveSession(rowData.id_education_session);
                            }
                        },



                    },
                };
            }',
			]];

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationSessionRequest() {

		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*, case when sessionClosed = 0 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as session_state, case when sessionEnded = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as session_ended, case when active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as session_active')
				->from('education_session')
				->orderBy('`session_date` DESC')
		);
		$return = [];

		if (!empty($sessions)) {

			foreach ($sessions as $key => &$session) {

				$inscrits = (int) StudentEducation::getNbAttendees($session['id_education_session']);

				if ($inscrits > 0) {

					$session['nbInscription'] = (int) StudentEducation::getNbAttendees($session['id_education_session']);
					$session['turnover'] = StudentEducation::getSessionTurnover($session['id_education_session']);
					$return[] = $session;
				}

			}

		}

		return $sessions;

	}

	public function ajaxProcessgetEducationSessionRequest() {

		die(Tools::jsonEncode($this->getEducationSessionRequest()));

	}

	public function ajaxProcessUpdateByVal() {

		$idEducation = (int) Tools::getValue('idEducation');
		$field = Tools::getValue('field');
		$fieldValue = Tools::getValue('fieldValue');

		if ($field == 'session_date') {
			$date = DateTime::createFromFormat('m/d/Y', $fieldValue);

			$fieldValue = $date->format('Y-m-d');
		}

		$education = new EducationSession($idEducation);
		$classVars = get_class_vars(get_class($education));

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		if (Validate::isLoadedObject($education)) {

			$education->$field = $fieldValue;
			$result = $education->update();

			if (!isset($result) || !$result) {
				$result = [
					'success' => false,
					'message' => Tools::displayError('An error occurred while updating the field.'),
				];
				$this->errors[] = Tools::displayError('An error occurred while updating the product.');
			} else {
				$result = [
					'success' => true,
					'message' => $this->l('Update successful'),
				];
			}

		} else {

			$this->errors[] = Tools::displayError('An error occurred while loading the product.');
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

	public function getEducationSessionFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 50,
				'dataIndx'   => 'id_education_session',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'hiddenable' => 'no',
			],

			[
				'title'       => $this->l('Session date'),
				'minWidth'    => 150,
				'exWidth'     => 20,
				'dataIndx'    => 'session_date',
				'align'       => 'center',
				'valign'      => 'center',
				'dataType'    => 'date',
				'format'      => 'dd/mm/yy',
				'editable'    => true,
				'hidden'      => false,
				'cls'         => 'pq-calendar pq-side-icon',
				'editor'      => [
					'type'    => "textbox",
					'init'    => 'dateEditor',
					'getData' => 'getDataDate',
				],
				'validations' => [
					'type'  => 'regexp',
					'value' => '^[0-9]{2}/[0-9]{2}/[0-9]{4}$',
					'msg'   => 'Mauvais format de date',
				],

			],
			[
				'title'    => $this->l('Désignation'),
				'width'    => 150,
				'dataIndx' => 'name',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',
			],
			[
				'title'    => $this->l('Session ouverte'),
				'width'    => 150,
				'align'    => 'center',
				'valign'   => 'center',
				'dataIndx' => 'session_state',
				'dataType' => 'html',
			],
			[
				'title'    => $this->l('Session Terminée'),
				'width'    => 150,
				'dataIndx' => 'session_ended',
				'dataType' => 'html',
			],
			[
				'title'    => $this->l('Nombre étudiant inscrits'),
				'maxWidth' => 150,
				'dataIndx' => 'nbInscription',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',
			],
			[
				'title'        => $this->l('Chiffre d\'affaire HT'),
				'maxWidth'     => 150,
				'dataIndx'     => 'turnover',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],
			[
				'title'    => ' ',
				'dataIndx' => 'active',
				'dataType' => 'integer',
				'hidden'   => true,

			],
			[
				'title'    => $this->l('Status'),
				'width'    => 100,
				'dataIndx' => 'session_active',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetEducationSessionFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONSESSIONS_FIELDS'));
	}

	

	public function ajaxProcessViewSessionDetail() {

		$id_education_session = Tools::getValue('id_education_session');
		$session = new EducationSession($id_education_session);
		$return = [
			'title' => $this->l('Management of ') . $session->name,
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessGetDetailSessionRequest() {

		$idEducationSession = Tools::getValue('idSession');
		$details = StudentEducation::getSessionDetails($idEducationSession);
		die(Tools::jsonEncode($details));

	}

	public function getDetailSessionFields() {

		return [

			[
				'title'      => '',

				'dataIndx'   => 'id_student_education',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'    => $this->l('Social title'),
				'width'    => 100,
				'dataIndx' => 'title',
				'align'    => 'center',
				'dataType' => 'string',
				'editable' => false,

			],
			[
				'title'    => $this->l('First Name'),
				'width'    => 150,
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
				'dataIndx' => 'lastname',
				'dataType' => 'string',
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
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],

			],
			[
				'title'    => $this->l('Address'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'address_street',
				'align'    => 'left',
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
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('City'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'address_city',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Formation'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'sessionName',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Nombre d\'heure'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'hours',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Nombre dde jour'),
				'width'    => 150,
				'exWidth'  => 30,
				'dataIndx' => 'days',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'        => $this->l('Tarif HT'),
				'dataIndx'     => 'price',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],
			[
				'title'        => $this->l('Tarif TTC'),
				'dataIndx'     => 'priceWTax',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],

		];
	}

	public function ajaxProcessActiveSession() {

		$id_education_session = Tools::getValue('id_education_session');

		$session = new EducationSession($id_education_session);
		$session->active = 1;
		$session->update();

		$return = [
			'success' => true,
			'message' => 'La session a été activée avec succès',
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessGetDetailSessionFields() {

		$details = $this->getDetailSessionFields();
		die(Tools::jsonEncode($details));
	}

	public function ajaxProcessManageBeginningSession() {

		$sessionDays = Tools::getValue('sessionDays');
		Configuration::updateValue('PS_SESSION_DAY', serialize($sessionDays));

		$first_session_date = Tools::getValue('first_session_date');
		$date = str_replace('/', '-', $first_session_date);
		$date = date('Y-m-d', strtotime($date));
		$Newdate = DateTime::createFromFormat('Y-m-d', $date);
		$sessionName = 'Session du ' . EducationSession::convertinFrench($Newdate->format("d F Y"));
		$session = new EducationSession();
		$session->session_date = $date;
		$session->name = $sessionName;
		$session->id_education_session_state = 1;
		$result = $session->add();

		if ($result) {
			EducationSession::generateSessionDate();
		}

		die(true);

	}

}
