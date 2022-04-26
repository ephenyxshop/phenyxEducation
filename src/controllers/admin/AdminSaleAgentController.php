<?php

class AdminSaleAgentController extends AdminController {

	public function __construct() {

		$this->bootstrap = true;
		$this->className = 'SaleAgent';
		$this->table = 'sale_agent';
		$this->publicName = $this->l('Agent Commerciaux');
		$this->lang = false;
		$this->identifier = 'id_sale_agent';
		$this->controller_name = 'AdminSaleAgent';
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT_SALEAGENTS_FIELDS', Tools::jsonEncode($this->getSaleAgentFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SALEAGENTS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_SALEAGENTS_FIELDS', Tools::jsonEncode($this->getSaleAgentFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SALEAGENTS_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_SALEAGENTS_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_SALEAGENTS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_SALEAGENTS_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_SALEAGENTS_SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminSaleAgent' => $this->context->link->getAdminLink('AdminSaleAgent'),

		]);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/student.css', 'all', 0);
		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/saleagent.js',
		]);

	}
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			$this->admin_webpath . '/js/saleagent.js',
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des agents commerciaux');

		$this->context->smarty->assign([
			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_AdminSaleAgent',
			'tableName'      => $this->table,
			'className'      => $this->className,
			'linkController' => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'       => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript' => $this->generateParaGridScript(),
			'titleBar'       => $this->TitleBar,
			'bo_imgdir'      => '/themes/' . $this->bo_theme . '/img/',
			'idController'   => '',
		]);

		parent::initContent();
	}

	public function generateParaGridScript($regenerate = false) {

		$context = Context::getContext();
		$controllerLink = $context->link->getAdminLink($this->controller_name);

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = "550";
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
		$paragrid->title = '\'' . $this->l('Gestion des agents commerciaux') . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->rowDblClick = 'function( event, ui ) {
			var identifierlink = ui.rowData.' . $this->identifier . ';
			var datalink = \'' . $controllerLink . '&' . $this->identifier . '=\'+identifierlink+\'&id_object=\'+identifierlink+\'&update' . $this->table . '&action=initUpdateController&ajax=true\';
			openAjaxGridLink(datalink, identifierlink, \'' . $this->controller_name . '\', \'View' . $this->controller_name . '\');
		} ';
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
                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	//editSaleAgent(rowData.id_sale_agent);
								 editAjaxObject("' .$this->controller_name.'", rowData.id_sale_agent)
                            }
                        },


                        "sep2": "---------",
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


                    },
                };
            }',
			]];

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

	public function getSaleAgentRequest() {

		
		
		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('sa.*,  gl.name as title, ( SELECT SUM(amount) FROM `' . _DB_PREFIX_ . 'sale_agent_commission` o WHERE o.id_sale_agent = sa.id_sale_agent AND `due` = 1  ) as total_turnover, case when sa.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as sale_agent_state')
				->from('sale_agent', 'sa')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = sa.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->orderBy('`total_turnover` DESC')
		);

		foreach ($students as &$student) {

			if ($student['is_tax']) {
				$student['is_tax'] = "Assujetti TVA";
			} else {
				$student['is_tax'] = "Non assujetti";
			}

			if ($student['sale_commission_type'] == 'percent') {
				$student['commission_type'] = "%";
			} else {
				$student['commission_type'] = "Montant fixe";
			}

		}

		return $students;

	}

	public function ajaxProcessgetSaleAgentRequest() {

		die(Tools::jsonEncode($this->getSaleAgentRequest()));

	}

	public function getSaleAgentFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_sale_agent',
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
				'title'    => $this->l('Civilité'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'title',
				'align'    => 'center',
				'dataType' => 'string',
				'editable' => true,

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
				'title'    => $this->l('Téléphone'),
				'width'    => 150,
				'exWidth'  => 40,
				'dataIndx' => 'phone_mobile',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],
			[
				'title'    => $this->l('Montant de commission'),
				'width'    => 100,
				'dataIndx' => 'sale_commission_amount',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],
			[
				'title'    => $this->l('Type de commission'),
				'width'    => 100,
				'dataIndx' => 'commission_type',
				'dataType' => 'string',
				'editable' => false,
				'hidden'   => false,

			],
			[
				'title'    => $this->l('Chiffre d‘affaire'),
				'width'    => 150,
				'exWidth'  => 20,
				'dataIndx' => 'total_turnover',
				'halign'    => 'HORIZONTAL_RIGHT',
				'align'    => 'right',
				'dataType' => 'float',
				'format'   => '€ #,###.00',
				'numberFormat' => '#,##0.00_-"€"',
				'editable' => false,
			],
			[
				'title'    => $this->l('TVA'),
				'width'    => 100,
				'dataIndx' => 'is_tax',
				'align'    => 'right',
				'dataType' => 'sting',
				'editable' => false,
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
				'editable' => false,
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
				'title'    => $this->l('État du compte'),
				'width'    => 100,
				'dataIndx' => 'sale_agent_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetSaleAgentFields() {

		$fields = EmployeeConfiguration::get('EXPERT_SALEAGENTS_FIELDS');
		die($fields);
	}

	public function ajaxProcessaddNewSaleAgent() {

		$data = $this->createTemplate('controllers/sale_agent/newAgent.tpl');
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessUpdateObject() {

		$file = fopen("testUpdateCoach.txt", "w");
		
		
		
		$object = new SaleAgent(Tools::getValue('id_sale_agent'));
		$this->copyFromPost($this->object, $this->table);

		try {
			$result = $object->update();
		} catch (Exception $ex) {
			
			fwrite($file, $ex->getMessage());
		}

		if (!isset($result) || !$result) {
			$this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
		} else {

			
			$workingPlan = Tools::jsonDecode(Tools::getValue('workin_plan'), true);
			$workingPlan = Tools::jsonEncode($workingPlan);
			fwrite($file, $workingPlan.PHP_EOL);
			$conge = Tools::jsonDecode(Tools::getValue('workin_plan_exceptions'), true);		
		
			$conge = Tools::jsonEncode($conge);
			fwrite($file, $conge.PHP_EOL);
			$sql = 'UPDATE `' . _DB_PREFIX_ . 'sale_agent_settings` SET `working_plan` = \''.$workingPlan. '\', `working_break` = \''.$conge. '\' WHERE `id_sale_agent` = '.$object->id;
			Db::getInstance()->Execute($sql);
			
			

			$result = [
				'success' => true,
				'message' => $this->l('Le CEF a été mis à jour avec succès'),
			];

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

	public function ajaxProcessEditObject() {

        
		$targetController = $this->targetController;
		if ($this->tabAccess['edit'] == 1) {   
		$id_agent = Tools::getValue('idObject');
		$agent = new SaleAgent($id_agent);
		$student = new Customer($agent->id_customer);

		$titleTab = '';

		$data = $this->createTemplate('controllers/sale_agent/editAgent.tpl');
		$extraJs = $this->getJsContent([$this->admin_webpath . '/js/bank.js']);
		$address = null;
		$id_address = Address::getFirstAgentAddressId($agent->id);
        if ($id_address > 0) {
            $address = new Address((int) $id_address);
        }
		
		$extracss = $this->pushCSS([
            $this->admin_webpath  . '/js/trumbowyg/ui/trumbowyg.min.css',
			$this->admin_webpath . '/js/jquery-ui/general.min.css',

        ]);
		$pusjJs = $this->pushJS([
            $this->admin_webpath . '/js/bank.js',
			$this->admin_webpath . '/js/agent.js',
			$this->admin_webpath . '/js/trumbowyg/trumbowyg.min.js',			
            $this->admin_webpath. '/js/jquery-jeditable/jquery.jeditable.min.js',
            $this->admin_webpath . '/js/jquery-ui/jquery-ui-timepicker-addon.min.js',
			$this->admin_webpath . '/js/moment/moment.min.js',
            $this->admin_webpath . '/js/moment/moment-timezone-with-data.min.js',
			$this->admin_webpath . '/js/calendar/working_plan_exceptions_modal.min.js',
            $this->admin_webpath. '/js/datejs/date.min.js',

        ]);

		
		

		if (Validate::isLoadedObject($agent)) {
			$data->assign('genders', Gender::getGenders());
			$data->assign('countries', Country::getCountries($this->context->language->id, true));
			$data->assign('agent', $agent);
			$data->assign('student', $student);
			$data->assign('banks', SaleAgent::getBankAccount($agent->id));
			$data->assign('countries', Country::getCountries($this->context->language->id, true));
			$data->assign('link', $this->context->link);
			$data->assign([
				'EALang'             => Tools::jsonEncode($this->getEaLang()),
				'pusjJs'                      => $pusjJs,
				'extracss'                    => $extracss,
				'workin_plan'				=> Tools::jsonEncode($agent->workin_plan),
				'workin_break'				=> Tools::jsonEncode($agent->workin_break),
				'working_plan_exceptions'		=> Tools::jsonEncode($agent->working_plan_exceptions),
           		'countries'         => Country::getCountries($this->context->language->id, false),
				'default_country'   => Configuration::get('PS_COUNTRY_DEFAULT'),
            	'addresses'         => [$address],
           
        	]);
			
			


		} else {

		}

		$li = '<li id="uperEdit'.$targetController.'" data-controller="AdminDashboard"><a href="#contentEdit'.$targetController.'">Visualiser ou modifier un CEF</a><button type="button" class="close tabdetail" data-id="uperEdit'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEdit'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() .'</div>';

		$result = [
			'success' => true,
			'li'   => $li,
			'html' => $html,
		];
		} else {
            $result = [
				'success' => false,
				'message'   => 'Votre profile administratif ne vous permet pas d‘éditer les conseillers en formation',
			];
        }

		die(Tools::jsonEncode($result));

	}
	

	public function ajaxProcessAddSaleAgentBankAccount() {
		
		$bank = new BankAccount();

        foreach ($_POST as $key => $value) {

            if (property_exists($bank, $key) && $key != 'id_bank_account') {

                $bank->{$key} = $value;

            }

        }

        $result = $bank->add();
		
		if($result) {
			$html = '<tr id="bank_'.$bank->id.'"><td>'.$bank->bank_name.'</td><td>'.$bank->iban.'</td><td>'.$bank->swift.'</td></tr>';
			
			$return = [
            	'success' => true,
            	'message' => 'Le compte bancaire a été ajouté avec succès',
				'html'	=> $html
        	];
		} else {
			$return = [
            	'success' => false,
            	'message' => 'Un problème a été rencontré lors de la création du compte bancaire',
        	];
		}
		
        
        die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessremoveSaleAgentAccount() {
		
		$idAccount = Tools::getValue('idAccount');
		$bank = new BankAccount($idAccount);
		
		if($bank->isUsedForSaleAgent()) {
			$bank->delete();
			$return = [
            	'success' => true,
            	'message' => 'Le compte bancaire a été supprimé avec succès',
        	];
		} else {
			$return = [
            	'success' => false,
            	'message' => 'Ce compte bancaire est utilisé dans un règlement de commission et ne peut pas être supprimé',
        	];
		}
		
		die(Tools::jsonEncode($return));
	}

}
