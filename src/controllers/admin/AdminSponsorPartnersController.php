<?php

/**
 * Class AdminStudentCompaniesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminSponsorPartnersControllerCore extends AdminController {

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
		$this->table = 'sponsor_partner';
		$this->className = 'SponsorPartner';
		$this->publicName = $this->l('Sponsor partenaire');
		$this->lang = false;
		$this->identifier = 'id_sponsor_partner';
		$this->controller_name = 'AdminSponsorPartners';
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT_SPONSORPARTNER_FIELDS', Tools::jsonEncode($this->getSponsorPartnerFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT__FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_SPONSORPARTNER_FIELDS', Tools::jsonEncode($this->getSponsorPartnerFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_SPONSORPARTNER_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_SPONSORPARTNER_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_SPONSORPARTNER_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_SPONSORPARTNER_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_SPONSORPARTNER_SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminSponsorPartners' => $this->context->link->getAdminLink('AdminSponsorPartners'),

		]);

		

	}
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
			_PS_JS_DIR_ . 'admin/tinymce.inc.js',
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

		$this->TitleBar = $this->l('Liste des sponsors');

		$this->context->smarty->assign([
			'controller'         => Tools::getValue('controller'),
			'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'             => 'grid_AdminSponsorPartners',
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
		$paragrid->change = 'function(evt, ui) {
		console.log(ui);
			if(ui.source == "add") {
				return false;
			}
            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var dataSponsor = updateData.rowData.id_sponsor_partner;
            $.ajax({
                type: \'POST\',
                url: AjaxLinkAdminSponsorPartners,
                data: {
                    action: \'updateByVal\',
                    idSponsor: dataSponsor,
                    field: dataField,
                    fieldValue: dataValue,
                    ajax: true
                },
                async: true,
                dataType: \'json\',
                success: function(data) {
                    if (data.success) {
                        showSuccessMessage(data.message);
						gridSponsorPartner.refreshDataAndView();
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })
        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->toolbar = [
			'items' => [

				
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Ajouter Un Sponsor') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addNewSponsorPartnerLine();
						}',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Supprimer ce sponsor') . '\'',
					'attr'     => '\'id="deleteSponsor"\'',
					'style'    => '\'display:none;\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'deleteSponsor',
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
		
		$paragrid->rowSelect ='function( event, ui ) {
			
			$("#idSponsorPartner").val(ui.addList[0].rowData.id_sponsor_partner);
			$("#deleteSponsor").slideDown();
           
        } ';
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
                            name: \'' . $this->l('Ajouter un nouvel Etat') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewSponsor();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Voir ou éditer l‘état : ') . '\'' . '+rowData.name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                editSpnsor(rowData.id_sponsor_partner)
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
	
	public function ajaxProcessaddNewSponsorLine() {
		
		$sponsor = new SponsorPartner();
		$sponsor->name = 'Nouveau Sponsor';
		$sponsor->add();
		
		$result = [
			'idSponsor' => $sponsor
		];
		
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessUpdateByVal() {

		$idSponsor = (int) Tools::getValue('idSponsor');
		$field = Tools::getValue('field');
		$fieldValue = Tools::getValue('fieldValue');
		$sponsor = new SponsorPartner($idSponsor);
		$classVars = get_class_vars(get_class($sponsor));

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		if (Validate::isLoadedObject($sponsor)) {

			$sponsor->$field = $fieldValue;
			$result = $sponsor->update();

			if (!isset($result) || !$result) {
				$this->errors[] = Tools::displayError('An error occurred while updating the product.');
			} else {
				$result = [
					'success' => true,
					'message' => $this->l('Le champ a été mis à jour avec succès'),
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

	public function generateParaGridOption() {

		return true;

	}

	public function ajaxProcessinitController() {

		return $this->initGridController();
	}

	public function getSponsorPartnerRequest() {

		$partners = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('sponsor_partner')
		);
		
		$shop = new Shop($this->context->shop->id);
		$url = 'https://' . $shop->domain_ssl;
		
		foreach ($partners as &$partner) {

			
			$partner['form_link'] = $url.'/formulaire/'.$partner['id_sponsor_partner'].'-'.$partner['link_rewrite'];
			$partner['logo'] = '<img src="'.$partner['logo'].'" style="width:100px" >';
			$partner['button_logo'] = '<button classe="ui-button ui-widget ui-corner-all" onClick="addSponsorLogo('.$partner['id_sponsor_partner'].')">Ajouter un logo</button>';
				
				

		}

		return $partners;

	}

	public function ajaxProcessgetSponsorPartnerRequest() {

		die(Tools::jsonEncode($this->getSponsorPartnerRequest()));

	}

	public function getSponsorPartnerFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_sponsor_partner',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'valign'   => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => $this->l('Nom du sponsor'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'name',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',
				'editable' => true,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'    => $this->l('Friendly Url'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'link_rewrite',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',
				'editable' => false,
				'filter'   => [
					'crules' => [['condition' => "contain"]],
				],

			],
			[
				'title'    => $this->l('Lien Front Office'),
				'minWidth' => 250,
				'exWidth'  => 20,
				'dataIndx' => 'form_link',
				'align'    => 'left',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Objetcif'),
				'minWidth' => 250,
				'exWidth'  => 20,
				'dataIndx' => 'target',
				'align'    => 'center',
				'valign'   => 'center',
				'editable' => true,
				'dataType' => 'integer',

			],
			[
				'title'    => $this->l('Description'),
				'minWidth' => 250,
				'exWidth'  => 20,
				'dataIndx' => 'description',
				'align'    => 'center',
				'valign'   => 'center',
				'editable' => true,
				'dataType' => 'html',

			],
			[
				'title'    => $this->l('Logo'),
				'minWidth' => 250,
				'exWidth'  => 20,
				'dataIndx' => 'logo',
				'align'    => 'center',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'html',

			],
			[
				'title'    => $this->l('Action'),
				'minWidth' => 250,
				'dataIndx' => 'button_logo',
				'align'    => 'center',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'html',

			],
		

		];

	}

	public function ajaxProcessgetSponsorPartnerFields() {

		$fields = EmployeeConfiguration::get('EXPERT_SPONSORPARTNER_FIELDS');
		die($fields);
	}
	
	public function ajaxProcessAddSponsorLogo() {
		
		$idSponsor = Tools::getValue('idSponsor');
		$sponsor = new SponsorPartner($idSponsor);
		$data = $this->createTemplate('controllers/sponsor_partners/addSponsorLogo.tpl');
		$data->assign('sponsor', $sponsor);
		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessUploadSponsorLogo() {
		
		
		$id_sponsor = Tools::getValue('id_sponsor');
		$sponsor = new SponsorPartner($id_sponsor);
		$imageUploader = new HelperImageUploader('logoSponsor');
		$imageUploader->setAcceptTypes(['jpeg', 'png', 'jpg']);
        $files = $imageUploader->process();
		if(is_array($files) && count($files)) {
			foreach ($files as $image) {
				$type = pathinfo($image['name'] , PATHINFO_EXTENSION);
				$data = file_get_contents( $image['save_path'] );
				$base64_code = base64_encode($data);
				$base64_str = 'data:image/' . $type . ';base64,' . $base64_code;
				$sponsor->logo = $base64_str;
					
			}
			$sponsor->update();
		}
		
		$result = [
			'success' => true,
		];
		die(Tools::jsonEncode($result));
	}

	
	public function ajaxProcessSaveNewSponsor() {

		$company = new SponsorPartner();

		foreach ($_POST as $key => $value) {

			if (property_exists($company, $key) && $key != 'id_sponsor_partner') {

				$company->{$key}
				= $value;
			}

		}

		$company->add();

		$result = [
			'success' => true,
			'message' => 'La société a été ajouté avec succès à la base de donnée.',
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessDeleteSponsor() {

		$idSponsor = Tools::getValue('idSponsor');

		$sponsor = new SponsorPartner($idSponsor);

		$sponsor->delete();
		
		$result = [
			'success' => true,
			'message' => 'Le sponsor a été supprimé avec succès.',
		];
		
		die(Tools::jsonEncode($result));
	}
	
	function ajaxProcessAddNewSponsor() {
		
		$_GET['addsponsor_partner'] = "";

		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
		
	}
	
	public function ajaxProcessEditSpnsor() {
		
		$idSponsor = Tools::getValue('idSponsor');
		$_GET['id_sponsor_partner'] = $idSponsor;
		$_GET['updatesponsor_partner'] = "";
		
		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}
	
	public function renderForm() {
		
		 if (!($obj = $this->loadObject(true))) {
            return '';
        }
		
		$this->fields_form = [
            'legend' => [
                'title' => $this->l('Détail du Sponsor'),
                'icon'  => 'icon-globe',
            ],
            'input'  => [
				[
					'type' => 'hidden',
					'name' => 'ajax',
				],
				[
					'type' => 'hidden',
					'name' => 'action',
				],
                [
                    'type'      => 'text',
                    'label'     => $this->l('Name'),
                    'name'      => 'name',
                    'maxlength' => 32,
                    'required'  => true,
                    'hint'      => $this->l('Provide the State name to be display in addresses and on invoices.'),
                ],
				[
                    'type'     => 'text',
                    'label'    => $this->l('Friendly URL'),
                    'name'     => 'link_rewrite',
                    'required' => true,
                    'hint'     => $this->l('Only letters and the hyphen (-) character are allowed.'),
                ],
				[
                    'type'      => 'text',
                    'label'     => $this->l('Objectif'),
                    'name'      => 'target',
                    'required'  => true,
                ],
               [
                    'type'         => 'textarea',
                    'label'        => $this->l('Description courte'),
                    'name'         => 'description_short',
                    'autoload_rte' => true,
                    'rows'         => 5,
                    'cols'         => 40,
                ],
                [
                    'type'         => 'textarea',
                    'label'        => $this->l('Description'),
                    'name'         => 'description',
                    'autoload_rte' => true,
                    'rows'         => 5,
                    'cols'         => 40,
                ],
               [
                    'type'         => 'textarea',
                    'label'        => $this->l('Message au Parrain'),
                    'name'         => 'message_sponsor',
                    'autoload_rte' => true,
                    'rows'         => 5,
                    'cols'         => 40,
                ],
                [
                    'type'         => 'textarea',
                    'label'        => $this->l('Message au Filleul'),
                    'name'         => 'message_child',
                    'autoload_rte' => true,
                    'rows'         => 5,
                    'cols'         => 40,
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];
		
		$this->fields_value['ajax'] = 1;
		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateSponsor';
			
		} else {
			$this->fields_value['action'] = 'addSPonsor';
		}


        return parent::renderForm();
	}
	
	public function ajaxProcessUpdateSponsor() {
		
		$idSponsor = Tools::getValue('id_sponsor_partner');
		
		$sponsor = new SponsorPartner($idSponsor);
		
		foreach ($_POST as $key => $value) {
			if (property_exists($sponsor, $key) && $key != 'id_cms' && $key != 'id_parent') {
             	$sponsor->{$key}  = $value;
            }
        }
		
		$result = $sponsor->update();
		
		$return = [
        	'success' => true,
            'message' => $this->l('La Sponsor a été mis à jour avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessAddSponsor() {	
		
		
		$sponsor = new SponsorPartner();
		
		foreach ($_POST as $key => $value) {
			if (property_exists($sponsor, $key) && $key != 'id_cms' && $key != 'id_parent') {
             	$sponsor->{$key}  = $value;
            }
        }
		
		$result = $sponsor->add();
		
		$return = [
        	'success' => true,
            'message' => $this->l('La Sponsor a été ajouté avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
	}


}
