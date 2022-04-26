<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class AdminSaleAgentCommissionController extends AdminController {

	public function __construct() {

		$this->bootstrap = true;
		$this->className = 'SaleAgentCommission';
		$this->table = 'sale_agent_commission';
		$this->publicName = $this->l('Commissions des Agent Commerciaux');
		$this->lang = false;
		$this->identifier = 'id_sale_agent_commission';
		$this->controller_name = 'AdminSaleAgentCommission';
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT_SALEAGENTCOMMISSIONS_FIELDS', Tools::jsonEncode($this->getSaleAgentCommissionFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SALEAGENTCOMMISSIONS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_SALEAGENTCOMMISSIONS_FIELDS', Tools::jsonEncode($this->getSaleAgentCommissionFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SALEAGENTCOMMISSIONS_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_SALEAGENTCOMMISSIONS_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_SALEAGENTCOMMISSIONS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_SALEAGENTCOMMISSIONS_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_SALEAGENTCOMMISSIONS_SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminSaleAgentCommission' => $this->context->link->getAdminLink('AdminSaleAgentCommission'),

		]);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/commission.css', 'all', 0);
		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/saleagent_commission.js',
		]);

	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des commissions aux agents commerciaux');
		$sessions = EducationSession::getFilledEducationSession();
		$steps = StudentEducationStep::getEducationStep();
		$lastEducatinOpen = EducationSession::getLastEducatinOpen();

		$this->context->smarty->assign([
			'controller'       => Tools::getValue('controller'),
			'tabScript'        => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'           => 'grid_AdminSaleAgentCommission',
			'tableName'        => $this->table,
			'className'        => $this->className,
			'linkController'   => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'         => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript'   => $this->generateParaGridScript(),
			'titleBar'         => $this->TitleBar,
			'saleAgents'       => SaleAgent::getSaleAgents(),
			'sessions'         => $sessions,
			'lastEducatinOpen' => $lastEducatinOpen['id_education_session'],
			'bo_imgdir'        => '/themes/' . $this->bo_theme . '/img/',
			'idController'     => '',
		]);

		parent::initContent();
	}

	public function generateParaGridScript() {

		$context = Context::getContext();
		$controllerLink = $context->link->getAdminLink($this->controller_name);
		$paidSelector = '<div class="pq-theme"><select id="paidSelect"><option value="">' . $this->l('--Select--') . '</option>';
		$paidSelector .= '<option value="0">Non</option>';
		$paidSelector .= '<option value="1">Oui</option>';
		$paidSelector .= '</select></div>';

		$dueSelector = '<div class="pq-theme"><select id="dueSelect"><option value="">' . $this->l('--Select--') . '</option>';
		$dueSelector .= '<option value="0">Non</option>';
		$dueSelector .= '<option value="1">Oui</option>';
		$dueSelector .= '</select></div>';

		$gridExtraFunction = ['function buildEducationFilter(){
			var conteneur = $(\'#paidSelector\').parent().parent();
			$(conteneur).empty();
			$(conteneur).append(\'' . $paidSelector . '\');
			$(\'#paidSelect\' ).selectmenu({
        		"change": function(event, ui) {

					grid' . $this->className . '.filter({
    					mode: \'AND\',
    					rules: [
        					{ dataIndx: \'paid\', condition: \'equal\', value: ui.item.value}
    					]
					});
    			}
			});
			var dueconteneur = $(\'#dueSelector\').parent().parent();
			$(dueconteneur).empty();
			$(dueconteneur).append(\'' . $dueSelector . '\');
			$(\'#dueSelect\' ).selectmenu({
        		"change": function(event, ui) {

					grid' . $this->className . '.filter({
    					mode: \'AND\',
    					rules: [
        					{ dataIndx: \'due\', condition: \'equal\', value: ui.item.value}
    					]
					});
    			}
			});
			 $(\'#regAgentSessionSelect\' ).selectmenu({
			 	width: 300,
			 	classes: {
    				"ui-selectmenu-menu": "scrollable"
  				},
				change: function(event, ui) {

                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx: \'id_education_session\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
					$("#selectedSessionValue").val(ui.item.value);
					checkRegisterReglement();
                }
            });


			$("#agentSelect").selectmenu({
				classes: {
    				"ui-selectmenu-menu": "scrollable"
  				},
				width: 200,
                change: function(event, ui) {
					grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx: \'id_sale_agent\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
					$("#selectedAgentValue").val(ui.item.value);
					checkRegisterReglement();
                }
            });

        	}', ];

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
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Créer un règlement pour cette session') . '\'',
					'attr'     => '\'id="reglement-genere"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'genereReglement',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Recevoir par email les commissions dues') . '\'',
					'attr'     => '\'id="export-due"\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportDueStatement',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Recevoir par email une situation complète') . '\'',
					'attr'     => '\'id="export-statement"\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'exportStatement',
				],

			],
		];
		$paragrid->change = 'function(evt, ui) {
            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var idCommission = updateData.rowData.id_sale_agent_commission;
            $.ajax({
                type: \'POST\',
                url: AjaxLinkAdminSaleAgentCommission,
                data: {
                    action: \'updateByVal\',
                    idCommission: idCommission,
                    field: dataField,
                    fieldValue: dataValue,
                    ajax: true
                },
                async: true,
                dataType: \'json\',
                success: function(data) {
                    if (data.success) {
                        showSuccessMessage(data.message);
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })
        }';
		$paragrid->complete = 'function(){
		buildEducationFilter();
		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;

		$paragrid->fillHandle = '\'all\'';

		$paragrid->filterModel = [
			'on'          => true,
			'mode'        => '\'AND\'',
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
		$paragrid->title = '\'' . $this->l('Gestion commissions des agents commerciaux') . '\'';
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
                        "payment": {
                            name: \'' . $this->l('Régler cette commission') . ' \',
                            icon: "add",
							 visible: function(key, opt) {
                                if(rowData.due == 0) {
                                    return false;
                                }
								if(rowData.paid == 1) {
                                    return false;
                                }
                            return true;
                            },
                            callback: function(itemKey, opt, e) {
                                payCommission(rowData.id_sale_agent_commission);
                            }
                        },
						 "generateInv": {
                            name: \'' . $this->l('Enregistrer une facture pour les commissions sélectionnée') . ' \',
                            icon: "add",
							 visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
							   var pieceSelected = selgrid' . $this->className . '.getSelection();
							   var allowed = true;
							   var idAgent = $("#selectedAgentValue").val();
                                if(selected < 2) {
                                    return false;
                                }
								$.each(pieceSelected, function( index, value ) {
  									if(value.rowData.paid == 1) {
										allowed = false;
									}
  								});
								if(allowed == false) {
											return false;
										}
								if(idAgent  > 0) {
  									return true;
  								} else {
									return false;
								}

                            },
                            callback: function(itemKey, opt, e) {
                                generateBulkInvoice(selgrid' . $this->className . ');
                            }
                        },


                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer cette commission') . ' \',
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                deleteCommission(rowData.id_sale_agent_commission);
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
		return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getSaleAgentCommissionRequest() {

		$today = date("Y-m-d");

		$students = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('sac.id_sale_agent_commission, sac.amount, sac.due, sac.paid, sac.id_sale_agent, sac.invoice_number, sac.payment_date, CONCAT(sa.`firstname`, \' \', sa.`lastname`) AS `agent`, sa.email as agent_email, CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, se.`id_education_session`, es.name as sessionName, case when sac.due = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as sale_commission_due, case when sac.paid = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as sale_commission_paid, ba.bank_name, ba.iban')
				->from('sale_agent_commission', 'sac')
				->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
				->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->leftJoin('student', 's', 's.`id_student` = se.`id_student`')
				->leftJoin('bank_account', 'ba', 'ba.`id_bank_account` = sac.`id_bank_account`')
				->orderBy('es.`session_date` ')
				->where('es.`session_date` < \'' . $today . '\'')

		);

		return $students;

	}

	public function ajaxProcessgetSaleAgentCommissionRequest() {

		die(Tools::jsonEncode($this->getSaleAgentCommissionRequest()));

	}

	public function getSaleAgentCommissionFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_sale_agent_commission',
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
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'paid',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => ' ',
				'width'      => 50,
				'dataIndx'   => 'due',
				'dataType'   => 'string',
				'hidden'     => true,
				'hiddenable' => 'no',
			],
			[
				'title'      => 'id sale agent',
				'dataIndx'   => 'id_sale_agent',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => $this->l('Agent Commerciale'),
				'width'    => 150,
				'exWidth'  => 25,
				'dataIndx' => 'agent',
				'align'    => 'left',
				'editable' => false,
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'      => 'id educ session',
				'dataIndx'   => 'id_education_session',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
				'editable'   => false,
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'        => $this->l('Session'),
				'minWidth'     => 200,
				'dataIndx'     => 'sessionName',
				'valign'       => 'center',
				'dataType'     => 'string',
				'statement'    => true,
				'statementDue' => true,
				'hidden'       => false,
				'editable'     => false,
				'align'        => 'left',

			],
			[
				'title'        => $this->l('Etudiant'),
				'minWidth'     => 200,
				'dataIndx'     => 'student',
				'statement'    => true,
				'statementDue' => true,
				'valign'       => 'center',
				'dataType'     => 'string',
				'hidden'       => false,
				'editable'     => false,
				'vdi'          => true,
				'align'        => 'left',

			],
			[
				'title'        => $this->l('Montant de commission'),
				'width'        => 100,
				'dataIndx'     => 'amount',
				'statement'    => true,
				'statementDue' => true,
				'dataType'     => 'string',
				'editable'     => true,
				'hidden'       => false,

			],

			[
				'title'    => $this->l('Commission due'),
				'width'    => 150,
				'dataIndx' => 'sale_commission_due',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"dueSelector\", placeholder=" . $this->l('--Select--') . " readonly",
					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'    => $this->l('Commission payée'),
				'width'    => 150,
				'dataIndx' => 'sale_commission_paid',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"paidSelector\", placeholder=" . $this->l('--Select--') . " readonly",
					'crules' => [['condition' => "equal"]],
				],

			],
			[
				'title'     => $this->l('N° de Facture VDI'),
				'width'     => 250,
				'dataIndx'  => 'invoice_number',
				'statement' => true,
				'align'     => 'left',
				'dataType'  => 'string',
				'editable'  => true,

			],
			[
				'title'     => $this->l('Date de règlement'),
				'width'     => 150,
				'dataIndx'  => 'payment_date',
				'statement' => true,
				'align'     => 'left',
				'dataType'  => 'date',
				'format'    => 'dd/mm/yy',
				'editable'  => true,

			],
			[
				'title'     => $this->l('Banque'),
				'minWidth'  => 200,
				'dataIndx'  => 'bank_name',
				'statement' => true,
				'valign'    => 'center',
				'dataType'  => 'string',
				'hidden'    => false,
				'editable'  => false,
				'vdi'       => true,
				'align'     => 'left',

			],
			[
				'title'     => $this->l('Iban'),
				'minWidth'  => 200,
				'dataIndx'  => 'iban',
				'statement' => true,
				'valign'    => 'center',
				'dataType'  => 'string',
				'hidden'    => false,
				'editable'  => false,
				'vdi'       => true,
				'align'     => 'left',

			],

		];

	}

	public function ajaxProcessgetSaleAgentCommissionFields() {

		$fields = EmployeeConfiguration::get('EXPERT_SALEAGENTCOMMISSIONS_FIELDS');
		die($fields);
	}

	public function ajaxProcessUpdateByVal() {

		$idCommission = (int) Tools::getValue('idCommission');
		$field = Tools::getValue('field');
		$fieldValue = Tools::getValue('fieldValue');
		$commission = new SaleAgentCommission($idCommission);
		$classVars = get_class_vars(get_class($commission));

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		if (Validate::isLoadedObject($commission)) {

			$commission->$field = $fieldValue;
			$result = $commission->update();

			if (!isset($result) || !$result) {
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

	public function ajaxProcessPayCommission() {

		$idCommission = Tools::getValue('idCommission');
		$commission = new SaleAgentCommission($idCommission);
		$commission->paid = 1;
		$commission->update();

		$result = [
			'success' => true,
			'mesage'  => 'La commission a été réglée avec succès',
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessGenereReglement() {

		$idSession = Tools::getValue('idSession');
		$idAgent = Tools::getValue('idAgent');

		if (Validate::isUnsignedId($idSession) && Validate::isUnsignedId($idAgent)) {
			$agent = new SaleAgent($idAgent);
			$session = new EducationSession($idSession);

			if (Validate::isLoadedObject($agent) && Validate::isLoadedObject($session)) {
				$totalDue = 0;
				$commissions = SaleAgentCommission::getIdCommissionbyIdAgent($agent->id, $session->id);

				foreach ($commissions as $commission) {
					$totalDue = $totalDue + $commission['amount'];
				}

				$data = $this->createTemplate('controllers/sale_agent_commission/registerInvoice.tpl');
				$data->assign('agent', $agent);
				$data->assign('session', $session);
				$data->assign('commissions', $commissions);
				$data->assign('total', $totalDue);
				$data->assign('banks', SaleAgent::getBankAccount($agent->id));
				$result = [
					'html' => $data->fetch(),
				];

				die(Tools::jsonEncode($result));
			}

		}

	}

	public function ajaxProcessGenerateBulkInvoice() {

		$idCommissions = Tools::getvalue('idCommissions');
		$idAgent = Tools::getValue('idAgent');
		$agent = new SaleAgent($idAgent);

		$totalDue = 0;
		$saleCommissions = implode(",", $idCommissions);
		$commissions = SaleAgentCommission::getBulkCommissionbyId($saleCommissions);

		foreach ($commissions as $commission) {
			$totalDue = $totalDue + $commission['amount'];
		}

		$data = $this->createTemplate('controllers/sale_agent_commission/registerBulkInvoice.tpl');
		$data->assign('agent', $agent);
		$data->assign('saleCommissions', $saleCommissions);
		$data->assign('commissions', $commissions);
		$data->assign('total', $totalDue);
		$data->assign('banks', SaleAgent::getBankAccount($agent->id));
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessRegisterInvoice() {

		$idSession = Tools::getValue('id_session');
		$idAgent = Tools::getValue('id_sale_agent');
		$agent = new SaleAgent($idAgent);
		$id_bank_account = Tools::getValue('id_bank_account');

		$session = new EducationSession($idSession);
		$invoice_number = Tools::getValue('invoice_number');
		$payment_amount = Tools::getValue('payment_amount');
		$totalDue = Tools::getValue('totalDue');
		$agent->balance = $agent->balance + $totalDue - $payment_amount;

		$payment_date = Tools::getValue('payment_date');

		$commissions = SaleAgentCommission::getIdCommissionbyIdAgent($agent->id, $session->id);

		foreach ($commissions as $commission) {
			$saleCommission = new SaleAgentCommission($commission['id_sale_agent_commission']);
			$saleCommission->invoice_number = $invoice_number;
			$saleCommission->payment_date = $payment_date;
			$saleCommission->paid = 1;
			$saleCommission->id_bank_account = $id_bank_account;
			$saleCommission->update();
		}

		$agent->update();
		$result = [
			'success'   => true,
			'message'   => 'La facture a été enregistré avec succès',
			'idSession' => $idSession,
			'idAgent'   => $idAgent,
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessRegisterBulkInvoice() {

		$invoice_number = Tools::getValue('invoice_number');
		$idAgent = Tools::getValue('id_sale_agent');
		$agent = new SaleAgent($idAgent);

		$payment_date = Tools::getValue('payment_date');
		$saleCommissions = Tools::getValue('saleCommissions');
		$payment_amount = Tools::getValue('payment_amount');
		$id_bank_account = Tools::getValue('id_bank_account');
		$totalDue = Tools::getValue('totalDue');

		$agent->balance = $agent->balance + $totalDue - $payment_amount;

		$commissions = SaleAgentCommission::getBulkCommissionbyId($saleCommissions);

		foreach ($commissions as $commission) {
			$saleCommission = new SaleAgentCommission($commission['id_sale_agent_commission']);
			$saleCommission->invoice_number = $invoice_number;
			$saleCommission->payment_date = $payment_date;
			$saleCommission->paid = 1;
			$saleCommission->id_bank_account = $id_bank_account;
			$saleCommission->update();
		}

		$agent->update();
		$result = [
			'success' => true,
			'message' => 'Les factures a été enregistré avec succès',
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessExportStatement() {

		$fields = $this->getSaleAgentCommissionFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['statement']) && $field['statement'] == 1) {
				$titles[] = $field['title'];
				$dataIndx[] = $field['dataIndx'];
			}

		}

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
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

		if (file_exists(_PS_EXPORT_DIR_ . 'situationVDI.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'situationVDI.xlsx');
		}

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

		$commissions = SaleAgentCommission::getSaleAgentStatement();

		foreach ($commissions as $key => $commission) {
			$vdi = new SaleAgent($key);
			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, $vdi->firstname . ' ' . $vdi->lastname);
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getFont()->setSize(14);
			$i++;

			foreach ($commission['commissions'] as $education) {

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
				->setCellValue('A' . $i, 'Total Réglé à ' . $vdi->firstname . ' ' . $vdi->lastname . ' ' . $commission['totalPaid'] . ' €uros');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);
			$i++;
			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Commission due pour ' . $vdi->firstname . ' ' . $vdi->lastname . ' ' . $commission['totalDue'] . ' €uros');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);
			$i++;
		}

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'situationVDI.xlsx');

		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents(_PS_EXPORT_DIR_ . 'situationVDI.xlsx'))),
			'name'    => 'Fichier export situation VDI.xlsx',
		];

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/vdi_statement.tpl');

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				[
					'name'  => Context::getContext()->company->firstname,
					'email' => Configuration::get('PS_SHOP_ADMIN_EMAIL'),
				],
				
			],
			'subject'     => 'Export Global Situation des agents commerciaux',
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];

		Tools::sendEmail($postfields);

		$result = [
			'success' => true,
			'message' => 'Un email récapitulatif vient d’être envoyé à la direction avec le rapport de session pour les VDI',
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessExportDueStatement() {

		$fields = $this->getSaleAgentCommissionFields();
		$titles = [];
		$dataIndx = [];

		foreach ($fields as $field) {

			if (isset($field['statementDue']) && $field['statementDue'] == 1) {
				$titles[] = $field['title'];
				$dataIndx[] = $field['dataIndx'];
			}

		}

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
		$spreadsheet = new Spreadsheet();
		$spreadsheet->getActiveSheet()->getPageSetup()->setFitToWidth(1);

		if (file_exists(_PS_EXPORT_DIR_ . 'Commission Due Agent.xlsx')) {
			unlink(_PS_EXPORT_DIR_ . 'Commission Due Agent.xlsx');
		}

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

		$commissions = SaleAgentCommission::getSaleAgentDueStatement();

		foreach ($commissions as $key => $commission) {
			$vdi = new SaleAgent($key);
			$spreadsheet->setActiveSheetIndex(0)
				->setCellValue('A' . $i, 'Commission due pour ' . $vdi->firstname . ' ' . $vdi->lastname);
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i . ':' . $column . $i)->applyFromArray($vdiStyle);
			$spreadsheet->getActiveSheet()->getStyle('A1:' . $column . $i)->getFont()->setSize(14);
			$i++;

			foreach ($commission['commissions'] as $education) {

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
				->setCellValue('A' . $i, 'Total à payer à ' . $vdi->firstname . ' ' . $vdi->lastname . ' ' . $commission['totalDue'] . ' €uros');
			$spreadsheet->getActiveSheet()->mergeCells('A' . $i . ':' . $column . $i);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->applyFromArray($titleStyle);
			$spreadsheet->getActiveSheet()->getStyle('A' . $i)->getFont()->setSize(12);
			$i++;
		}

		$fileSave = new Xlsx($spreadsheet);
		$fileSave->save(_PS_EXPORT_DIR_ . 'Commission Due Agent.xlsx');

		$fileAttachement[] = [
			'content' => chunk_split(base64_encode(file_get_contents(_PS_EXPORT_DIR_ . 'Commission Due Agent.xlsx'))),
			'name'    => 'Fichier export commission à payer.xlsx',
		];

		$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/vdi_statement.tpl');

		$postfields = [
			'sender'      => [
				'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
				'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
			],
			'to'          => [
				
				[
					'name'  => Context::getContext()->company->firstname,
					'email' => Configuration::get('PS_SHOP_ADMIN_EMAIL'),
				],
				
			],
			'subject'     => 'Export Global des commissions dues aux agents commerciaux',
			"htmlContent" => $tpl->fetch(),
			'attachment'  => $fileAttachement,
		];

		Tools::sendEmail($postfields);

		$result = [
			'success' => true,
			'message' => 'Un email récapitulatif vient d’être envoyé à la direction avec le rapport de session pour les VDI',
		];
		die(Tools::jsonEncode($result));
	}

}
