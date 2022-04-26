<?php

class AdminPartnersController extends AdminController {

	public function __construct() {

		$this->bootstrap = true;
		$this->className = 'Partner';
		$this->table = 'partner';
		$this->publicName = $this->l('Gestion des plateformes "ducative"');
		$this->lang = false;
		$this->identifier = 'id_partner';
		$this->controller_name = 'AdminPartners';
		$this->context = Context::getContext();

		
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PARTNERS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_PARTNERS_FIELDS', Tools::jsonEncode($this->getPartnerFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PARTNERS_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_PARTNERS_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_PARTNERS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_PARTNERS_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_PARTNERS_SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminPartners' => $this->context->link->getAdminLink('AdminPartners'),

		]);
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/student.css', 'all', 0);
		

	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des plateformes éducatives');

		$this->context->smarty->assign([
			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_AdminPartners',
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
		
		$gridExtraFunction = ['
		
		
               function editPartner(idPartner) {
                    $.ajax({
                        type: "GET",
                        url: AjaxLinkAdminPartners,
                        data: {
                            action: "editPartner",
                            idPartner: idPartner,
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            $("#partner-edit").html(data.html);
                            $("#content_AdminPartners").slideUp();
                            $("body").addClass("edit");
                            $("#partner-edit").slideDown();
                        }
                    });
                }

                function addNewPartner() {
                    $.ajax({
                        type: "POST",
                        url: AjaxLinkAdminPartners,
                        data: {
                            action: "addNewPartner",
                            idEducation: 0,
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            $("#partner-edit").html(data.html);
                            $("#content_AdminPartners").slideUp();
                            $("body").addClass("edit");
                            $("#partner-edit").slideDown();
                        }
                    });
                }
				
				function deletePartner(idPartner) {
                    $.ajax({
                        type: "GET",
                        url: AjaxLinkAdminPartners,
                        data: {
                            action: "deletePartnern",
                            idPartner: idPartner,
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            if (data.success) {
                				showSuccessMessage(data.message);
								gridPartner.refreshDataAndView();

            				} else {
                				showErrorMessage(data.message);
            				}
                        }
                    });
                }
				
				
            ', ];

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
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Ajouter une nouvelle Plateforme') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'addNewPartner',
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
                        "add": {
                            name: \'' . $this->l('Ajouter une nouvelle plateforme') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewPartner();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier ou visualiser ') . '\'' . '+rowData.company,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editPartner(rowData.id_partner);
                            }
                        },


                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . '\'' . '+rowData.company,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                deletePartner(rowData.id_partner);
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

	public function getPartnerRequest() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('sa.*, case when sa.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as partner_state')
				->from('partner', 'sa')
				->orderBy('sa.`id_partner` ASC')
		);

		

	}

	public function ajaxProcessgetPartnerRequest() {

		die(Tools::jsonEncode($this->getPartnerRequest()));

	}

	public function getPartnerFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_partner',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],

			

			[
				'title'    => $this->l('Société'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'company',
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
				'title'    => $this->l('État du compte'),
				'width'    => 100,
				'dataIndx' => 'partner_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetPartnerFields() {

		$fields = EmployeeConfiguration::get('EXPERT_PARTNERS_FIELDS');
		die($fields);
	}

	public function ajaxProcessAddNewPartner() {

		$data = $this->createTemplate('controllers/partners/newPartner.tpl');
		

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessEditPartner() {

		$id_partner = Tools::getValue('idPartner');
		$partner = new Partner($id_partner);

		$data = $this->createTemplate('controllers/partners/editPartner.tpl');		

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));

	}

	

	public function ajaxProcessNewPartner() {

		
		$file = fopen("testProcessNewPartner.txt","w");
		$partner = new Partner();

		foreach ($_POST as $key => $value) {

			if (property_exists($partner, $key) && $key != 'id_partner') {
				fwrite($file,$key.' => '.$value.PHP_EOL);
				
				$partner->{$key} = $value;
			}

		}

		
		$partner->active = 1;
		$mobile = str_replace(' ', '', $partner->phone_mobile);

		if (strlen($mobile) == 10) {
			$mobile = '+33' . substr($mobile, 1);
			$partner->phone_mobile = $mobile;
		}
		
		
		fwrite($file, print_r($partner, true));
		$result = $partner->add();
	
		if($result) {
			$return = [
				'success' => true,
				'message' => 'La plateforme éducative a été ajouté avec succès à la base de donnée.',
			];
		} else {
			$return = [
				'success' => false,
				'message' => 'Nous avons rencontré un problème pour ajouter la Plateforme dans la base de donnée.',
			];
		}

		

		die(Tools::jsonEncode($return));
	}

}
