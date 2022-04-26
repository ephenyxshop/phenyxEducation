<?php

/**
 * Class AdminStudentCompaniesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminPartnerCompaniesControllerCore extends AdminController {

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
		$this->table = 'partner_company';
		$this->className = 'PartnerCompany';
		$this->publicName = $this->l('Société partenaire');
		$this->lang = false;
		$this->identifier = 'id_partner_company';
		$this->controller_name = 'AdminPartnerCompanies';
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT__FIELDS', Tools::jsonEncode($this->getPartnerCompanyFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT__FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT__FIELDS', Tools::jsonEncode($this->getPartnerCompanyFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT__FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT__SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT__SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT__SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT__SCRIPT');
		}

		parent::__construct();

	}

	public function setMedia() {

		parent::setMedia();

		MediaAdmin::addJsDef([
			'AjaxLinkAdminPartnerCompanies' => $this->context->link->getAdminLink('AdminPartnerCompanies'),

		]);

		$this->addJS([
			__PS_BASE_URI__ . $this->admin_webpath . '/js/partnercompany.js',

		]);

	}
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			$this->admin_webpath . '/js/partnercompany.js'
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

		$this->TitleBar = $this->l('Liste des filiales');

		$this->context->smarty->assign([
			'manageHeaderFields' => true,
			'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
			'controller'         => Tools::getValue('controller'),
			'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'             => 'grid_AdminPartnerCompanies',
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
                           addNewPartnerCompany();
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
                            name: \'' . $this->l('Ajouter une filliale') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewPartnerCompany();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.company_name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editCompany(rowData.id_partner_company);
                            }
                        },
						"addLicense": {
                            name : \'' . $this->l('Ajouter une license pour ') . '\'' . '+rowData.company_name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	addLicense(rowData.id_partner_company);
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

	public function getPartnerCompanyRequest() {

		$partners = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('s.*, gl.name as title, cl.name as countryName, case when s.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as company_staten, l.purchase_key')
				->from('partner_company', 's')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = s.`id_gender` AND gl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('country_lang', 'cl', 'cl.`id_country` = s.`id_country` AND cl.`id_lang` = ' . $this->context->language->id)
				->leftJoin('license', 'l', 'l.`id_partner_company` = s.`id_partner_company`')
				->orderBy('s.`id_partner_company` DESC')
		);

		return $partners;

	}

	public function ajaxProcessgetPartnerCompanyRequest() {

		die(Tools::jsonEncode($this->getPartnerCompanyRequest()));

	}

	public function getPartnerCompanyFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_partner_company',
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
				'title'    => $this->l('Licence'),
				'width'    => 150,
				'dataIndx' => 'purchase_key',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,

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
				'title'    => $this->l('Active'),
				'width'    => 100,
				'dataIndx' => 'company_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetPartnerCompanyFields() {

		$fields = EmployeeConfiguration::get('EXPERT__FIELDS');
		die($fields);
	}

	public function manageFieldsVisibility($fields) {

		return parent::manageFieldsVisibility($fields);
	}

	public function ajaxProcessUpdateVisibility() {

		$headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT__FIELDS'), true);
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
		EmployeeConfiguration::updateValue('EXPERT__FIELDS', $headerFields);
		$response = [
			'headerFields' => $headerFields,
		];

		die($headerFields);
	}

	public function ajaxProcessupdateJsonVisibility() {

		$visibility = Tools::getValue('visibilities');
	}

	public function ajaxProcessAddNewCompany() {

		$data = $this->createTemplate('controllers/partner_companies/AddCompanies.tpl');
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));
		$data->assign('ranges', PartnerCompany::getEmployeeRange());

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSaveNewCompany() {

		$company = new PartnerCompany();

		foreach ($_POST as $key => $value) {

			if (property_exists($company, $key) && $key != 'id_partner_company') {

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

	public function ajaxProcessEditCompany() {

		$idFiliale = Tools::getValue('idFiliale');

		$studentCompany = new PartnerCompany($idFiliale);

		$titleTab = '';

		$data = $this->createTemplate('controllers/partner_companies/editCompanies.tpl');
		$data->assign('studentCompany', $studentCompany);
		$data->assign('genders', Gender::getGenders());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));
		$data->assign('ranges', PartnerCompany::getEmployeeRange());

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddLicense() {

		$idFiliale = Tools::getValue('idFiliale');

		$studentCompany = new PartnerCompany($idFiliale);

		$titleTab = '';

		$data = $this->createTemplate('controllers/partner_companies/addLicense.tpl');
		$data->assign('studentCompany', $studentCompany);
		$data->assign('purchase_key', License::generateLicenceKey());

		$result = [
			'html'     => $data->fetch(),
			'titleTab' => $titleTab,
		];
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddNewLicence() {

		$license = new License();

		foreach ($_POST as $key => $value) {

			if (property_exists($license, $key) && $key != 'id_license') {

				$license->{$key}
				= $value;
			}

		}

		$license->add();

		$result = [
			'success' => true,
			'message' => 'La société a été ajouté avec succès à la base de donnée.',
		];

		die(Tools::jsonEncode($result));
	}

}
