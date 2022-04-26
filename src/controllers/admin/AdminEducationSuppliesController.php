<?php

/**
 * Class AdminSuppliersControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationSuppliesControllerCore extends AdminController {

	public $bootstrap = true;

	/** @var Supplier $object */
	public $object;

	/**
	 * AdminSuppliersControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->table = 'education_supplies';
		$this->className = 'EducationSupplies';
		$this->publicName = $this->l('Matérielle pédagogique');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSUPPLIES_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONSUPPLIES_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSUPPLIES_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONSUPPLIES_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSUPPLIES_FIELDS', Tools::jsonEncode($this->getEducationSuppliesFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONSUPPLIES_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONSUPPLIES_FIELDS', Tools::jsonEncode($this->getEducationSuppliesFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONSUPPLIES_FIELDS'), true);
		}

	}

	public function setMedia() {

		parent::setMedia();
		MediaAdmin::addJsDef([
			'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
		]);
	}

	public function setAjaxMedia() {

		return $this->pushJS([
			$this->admin_webpath . '/js/educationsupply.js',
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->l('Materiel pédagogique');

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

		if (!empty($this->paragridScript)) {
			return '<script type="text/javascript">' . PHP_EOL . MediaAdmin::packJS($this->paragridScript) . PHP_EOL . '</script>';
		}

		$gridExtraFunction = ['


            function editSupply(idSupply) {
                    $.ajax({
                        type: "POST",
                        url: AjaxLinkAdminEducationSupplies,
                        data: {
                            action: "editSupply",
                            idSupply: idSupply,
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            $("#editEducationSupplies").html(data.html);
                            $("#grid_AdminEducationSupplies").slideUp();
                            $("body").addClass("edit");
                            $("#editEducationSupplies").slideDown();
                        }
                    });
                }

                function addNewSupply() {
                    $.ajax({
                        type: "POST",
                        url: AjaxLinkAdminEducationSupplies,
                        data: {
                            action: "addNewSupply",
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            $("#editEducationSupplies").html(data.html);
                            $("#grid_AdminEducationSupplies").slideUp();
                            $("body").addClass("edit");
                            $("#editEducationSupplies").slideDown();
                        }
                    });
                }

				function deleteSupply(idSupply) {
                    $.ajax({
                        type: "POST",
                        url: AjaxLinkAdminEducations,
                        data: {
                            action: "deleteSupply",
                            idSupply: idSupply,
                            ajax: true
                        },
                        async: false,
                        dataType: "json",
                        success: function success(data) {
                            if (data.success) {
                				showSuccessMessage(data.message);
								gridEducationSupplies.refreshDataAndView();

            				} else {
                				showErrorMessage(data.message);
            				}
                        }
                    });
                }


            '];

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
		$paragrid->create = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'icon'     => '\'ui-icon-disk\'',
					'label'    => '\'' . $this->l('Ajouter un nouvelle équipement') . '\'',
					'cls'      => '\'buttonCleanProductPosition changes ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
					'listener' => 'addNewSupply',
				],

			],
		];
		$paragrid->selectionModelType = 'row';

		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Gestion du matérielle pédagogique') . '\'';
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
                            name: \'' . $this->l('Ajouter une nouvelle équipement') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                addNewSupply();
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier l‘équipement : ') . '\'' . '+rowData.name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                editSupply(rowData.id_education_supplies);
                            }
                        },
						"sep1": "---------",
						"delete": {
                            name : \'' . $this->l('Supprimer l‘équipement : : ') . '\'' . '+rowData.name,
                            icon: "delete",
                            callback: function(itemKey, opt, e) {
                                deleteSupply(rowData.id_education_supplies);
                            }
                        },



                    },
                };
            }',
			]];

		$paragrid->gridExtraFunction = $gridExtraFunction;

		$option = $paragrid->generateParaGridOption();
		return $paragrid->generateParagridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationSuppliesRequest() {

		$educationTypes = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_supplies')
				->orderBy('`id_education_supplies` ASC')
		);

		return $educationTypes;

	}

	public function ajaxProcessgetEducationSuppliesRequest() {

		die(Tools::jsonEncode($this->getEducationSuppliesRequest()));

	}

	public function getEducationSuppliesFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_supplies',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->l('Nom'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],

		];

	}

	public function ajaxProcessgetEducationSuppliesFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONSUPPLIES_FIELDS'));
	}

	public function ajaxProcessAddNewSupply() {

		$_GET['addeducation_supplies'] = "";

		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddEducationSupply() {

		$suply = new EducationSupplies();

		foreach ($_POST as $key => $value) {

			if (property_exists($suply, $key) && $key != 'id_attribute') {
				$suply->{$key}
				= $value;
			}

		}

		$result = $suply->add();

		$return = [
			'success' => true,
			'message' => $this->l('Le nouveau matériel pédagogique a été ajoutée avec succès'),
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessEditSupply() {

		$idSupply = Tools::getValue('idSupply');

		$this->identifier = 'id_education_supplies';
		$_GET['id_education_supplies'] = $idSupply;
		$_GET['updateeducation_supplies'] = "";

		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessUpdateEducationSupply() {

		$file = fopen("testProcessUpdateEducationSupply.txt", "w");

		$idSupply = Tools::getValue('id_education_supplies');
		fwrite($file, $idSupply . PHP_EOL);

		$supply = new EducationSupplies($idSupply);

		foreach ($_POST as $key => $value) {

			if (property_exists($supply, $key) && $key != 'id_education_supplies') {
				fwrite($file, $key . ' => ' . $value . PHP_EOL);
				$supply->{$key}
				= $value;
			}

		}

		fwrite($file, print_r($supply, true) . PHP_EOL);
		$result = $supply->update();

		$return = [
			'success' => true,
			'message' => $this->l('Le matériel pédagogique a été mis à jour avec succès'),
		];

		die(Tools::jsonEncode($return));
	}

	/**
	 * Render form
	 *
	 * @return string
	 *
	 * @since 1.9.1.0
	 */
	public function renderForm() {

		// loads current warehouse

		if (!($obj = $this->loadObject(true))) {
			return '';
		}

		$this->fields_form = [
			'legend' => [
				'title' => $this->l('Supplies'),
				'icon'  => 'icon-truck',
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
					'type'     => 'text',
					'label'    => $this->l('Name'),
					'name'     => 'name',
					'required' => true,
					'col'      => 4,
					'hint'     => $this->l('Invalid characters:') . ' &lt;&gt;;=#{}',
				],

				[
					'type'      => 'text',
					'label'     => $this->l('Identifiant'),
					'name'      => 'viewbox',
					'maxlength' => 128,
					'col'       => 6,
					'required'  => true,
				],
				[
					'type'     => 'textarea',
					'label'    => $this->l('Fichier Svg'),
					'name'     => 'svg_file',
					'required' => true,
					'col'      => 4,
					'hint'     => $this->l('Invalid characters:') . ' &lt;&gt;;=#{}',
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Fourniture étudiant'),
					'name'     => 'is_furniture',
					'required' => false,
					'class'    => 't',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'is_furniture_on',
							'value' => 1,
							'label' => $this->l('Oui'),
						],
						[
							'id'    => 'is_furniture_off',
							'value' => 0,
							'label' => $this->l('Non'),
						],
					],
				],

			],
			'submit' => [
				'title' => $this->l('Save'),
			],
		];

		$this->fields_value['ajax'] = 1;

		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateEducationSupply';
		} else {
			$this->fields_value['action'] = 'addEducationSupply';
		}

		return parent::renderForm();
	}

	
	public function ajaxProcessOpenNewInvoice() {

		$data = $this->createTemplate('controllers/education_supplies/addInvoice.tpl');
		$data->assign('suppliers', EducationSupplier::getSupplier());
		$data->assign('countries', Country::getCountries($this->context->language->id, true));

		$result = [
			'html' => $data->fetch(),
		];
		die(Tools::jsonEncode($result));
	}

}
