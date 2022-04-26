<?php

/**
 * Class AdminEducationTypesControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationTypesControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_type';
		$this->className = 'EducationType';
		$this->lang = true;
		$this->publicName = $this->l('Famille de formation');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTYPES_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONTYPES_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTYPES_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONTYPES_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTYPES_FIELDS', Tools::jsonEncode($this->getEducationTypeFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONTYPES_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATIONTYPES_FIELDS', Tools::jsonEncode($this->getEducationTypeFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONTYPES_FIELDS'), true);
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
			_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js',
			_PS_JS_DIR_ . 'admin/tinymce.inc.js',
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->l('Education session List');

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

		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 40,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$this->paramComplete = 'function(){
			grid' . $this->className . '.refreshView();
			window.dispatchEvent(new Event(\'resize\'));
        }';
		$this->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$this->paramTitle = '\'' . $this->l('Géstion des') . ' ' . $this->publicName . '\'';
		$this->paramContextMenu = [
			'#grid_' . $this->controller_name => [
				'selector'  => '\'.pq-grid-row\'',
				'animation' => [
					'duration' => 250,
					'show'     => '\'fadeIn\'',
					'hide'     => '\'fadeOut\'',
				],
				'build'     => 'function($triggerElement, e){
				var rowIndex = $($triggerElement).attr("data-rowIndx");
				var rowData = ' . 'grid' . $this->className . '.getRowData( {rowIndx: rowIndex} );
        		return {
            		callback: function(){},
            		items: {
						"add": {
							name: \'' . $this->l('Ajouter une nouvelle famille de formation') . '\',
							icon: "add",
                			callback: function(itemKey, opt, e) {
                				addAjaxObject("' . $this->controller_name . '");
                			}
							},
                		"edit": {
							name: \'' . $this->l('Editer ou modifier la famille  ') . '\'+rowData.name,
							icon: "edit",
                			callback: function(itemKey, opt, e) {
                				//editFamily(rowData.id_education_type);
								 editAjaxObject("' . $this->controller_name . '", rowData.id_education_type)
                			}
						},
						"sep1": "---------",

           				"delete": {
           					name: \'' . $this->l('Supprimer la famille de formation ') . '\'+rowData.name,
           					icon: "delete",
							visible: function(key, opt){
								return !rowData.hasSubmenu;
                            },
           					callback: function(itemKey, opt, e) {
								var datalink = rowData.deleteLink;
								//deleteFamily(rowData.id_education_type);
								deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une famille de Formation", "Etes vous sure de vouloir supprimer la formation "+rowData.name+ " ?", "Oui", "Annuler",rowData.id_customer);


							}
						}
       				},
				};
			}',
			]];

		return parent::generateParaGridScript();
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getEducationTypeRequest() {

		$educationTypes = Db::getInstance()->executeS(
			(new DbQuery())
				->select('et.`active`, etl.*')
				->from('education_type', 'et')
				->leftJoin('education_type_lang', 'etl', 'etl.`id_education_type` = et.`id_education_type` AND etl.`id_lang` = ' . (int) $this->context->language->id)
				->orderBy('`id_education_type` ASC')
		);

		foreach ($educationTypes as &$educationType) {

			$educationType['description'] = $this->recurseShortCode($educationType['description']);

			if ($educationType['active'] == 1) {
				$educationType['active'] = '<div class="p-active"></div>';
			} else {
				$educationType['active'] = '<div class="p-inactive"></div>';
			}

		}

		return $educationTypes;

	}

	public function ajaxProcessgetEducationTypeRequest() {

		die(Tools::jsonEncode($this->getEducationTypeRequest()));

	}

	public function getEducationTypeFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_type',
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
				'valign'   => 'top',
				'dataType' => 'string',
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Désignation'),
				'width'    => 150,
				'dataIndx' => 'description',
				'dataType' => 'html',
			],

			[
				'title'    => $this->l('Active'),
				'width'    => 100,
				'editable' => false,
				'dataIndx' => 'active',
				'align'    => 'center',
				'valign'   => 'center',
				'editable' => false,
				'dataType' => 'html',
			],

		];

	}

	public function ajaxProcessgetEducationTypeFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATIONTYPES_FIELDS'));
	}

	public function renderForm() {

		$this->displayGrid = false;

		/** @var Category $obj */
		$obj = $this->loadObject(true);
		$context = $this->context;

		if (!($obj = $this->loadObject(true))) {
			return;
		}

		$this->fields_form = [
			'tinymce' => true,
			'legend'  => [
				'title' => $this->l('Famille de formation'),
				'icon'  => 'icon-tags',
			],

			'input'   => [
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
					'lang'     => true,
					'required' => true,
					'class'    => 'copy2friendlyUrl',
					'hint'     => $this->l('Invalid characters:') . ' <>;=#{}',
				],
				[
					'type'    => 'select',
					'label'   => $this->l('Certification'),
					'name'    => 'id_certification',
					'col'     => '4',
					'options' => [
						'query' => Certification::getCertifications(),
						'id'    => 'id_certification',
						'name'  => 'name',
					],
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Displayed'),
					'name'     => 'active',
					'required' => false,
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'active_on',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'active_off',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],
				[
					'type'         => 'textarea',
					'label'        => $this->l('Description haut de page'),
					'name'         => 'description_up',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],

				[
					'type'         => 'textarea',
					'label'        => $this->l('Description'),
					'name'         => 'description',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],
				[
					'type'         => 'textarea',
					'label'        => $this->l('Description bas de page'),
					'name'         => 'description_bottom',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],

				[
					'type'    => 'text',
					'label'   => $this->l('Meta title'),
					'name'    => 'meta_title',
					'maxchar' => 70,
					'lang'    => true,
					'rows'    => 5,
					'cols'    => 100,
					'hint'    => $this->l('Forbidden characters:') . ' <>;=#{}',
				],
				[
					'type'    => 'textarea',
					'label'   => $this->l('Meta description'),
					'name'    => 'meta_description',
					'maxchar' => 300,
					'lang'    => true,
					'rows'    => 5,
					'cols'    => 100,
					'hint'    => $this->l('Forbidden characters:') . ' <>;=#{}',
				],

				[
					'type'     => 'text',
					'label'    => $this->l('Friendly URL'),
					'name'     => 'link_rewrite',
					'lang'     => true,
					'required' => true,
					'hint'     => $this->l('Only letters, numbers, underscore (_) and the minus (-) character are allowed.'),
				],

			],
			'submit'  => [
				'title' => $this->l('Save'),

			],
		];

		$this->fields_value['ajax'] = 1;

		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateEducationType';
			$this->editObject = 'Edition de la Famille ' . $pbj->name;
		} else {
			$this->fields_value['action'] = 'addEducationType';
			$this->editObject = 'Ajouter une Famille de Formation';
		}

		return parent::renderForm();
	}

	public function ajaxProcessaddEducationType() {

		$educationType = new EducationType();

		foreach ($_POST as $key => $value) {

			if (property_exists($educationType, $key) && $key != 'id_education_type') {
				$educationType->{$key}
				= $value;
			}

		}

		$classVars = get_class_vars(get_class($educationType));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($educationType->{$field}) || !is_array($educationType->{$field})) {
							$educationType->{$field}
							= [];
						}

						$educationType->{$field}
						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		$result = $educationType->add();

		$return = [
			'success' => true,
			'message' => $this->l('La famille de formation a été ajoutée avec succès'),
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessUpdateEducationType() {

		$idEducationType = Tools::getValue('id_education_type');

		$educationType = new EducationType($idEducationType);

		foreach ($_POST as $key => $value) {

			if (property_exists($educationType, $key) && $key != 'id_education_type') {
				$educationType->{$key}
				= $value;
			}

		}

		$classVars = get_class_vars(get_class($educationType));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($educationType->{$field}) || !is_array($educationType->{$field})) {
							$educationType->{$field}
							= [];
						}

						$educationType->{$field}
						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		try {
			$result = $educationType->update();

			if ($result) {
				$return = [
					'success' => true,
					'message' => $this->l('La famille de formation a été mise à jour avec succès'),
				];
			}

		} catch (Exception $e) {
			$return = [
				'success' => false,
				'message' => $e->getMessage(),
			];
		}

		$return = [
			'success' => true,
			'message' => $this->l('La famille de formation a été mise à jour avec succès'),
		];

		die(Tools::jsonEncode($return));

	}

}
