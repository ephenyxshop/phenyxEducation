<?php

/**
 * Class AdminActualitesControllerCore
 *
 * @since 1.9.1.0
 */
class AdminActualitesControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'actualite';
		$this->className = 'Actualite';
		$this->lang = true;
		$this->publicName = $this->l('Actualités');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_ACTUALITES_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_ACTUALITES_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_ACTUALITES_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_ACTUALITES_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_ACTUALITES_FIELDS', Tools::jsonEncode($this->getActualiteFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_ACTUALITES_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_ACTUALITES_FIELDS', Tools::jsonEncode($this->getActualiteFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_ACTUALITES_FIELDS'), true);
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

		$this->TitleBar = $this->l('Liste du flux des actualités');

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

		$gridExtraFunction = [
			'
			function addNewActualite() {

			$.ajax({
				type: \'GET\',
				url: AjaxLinkAdminActualites,
				data: {
					action: \'addNewActualite\',
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailActualite").html(data.html);
					$("body").addClass("edit");
					$("#paragrid_' . $this->controller_name . '").slideUp();
					gridActualite.refreshDataAndView();
					$("#detailActualite").slideDown();
				}
				});

			}
			function editActualite(idActualite) {

			$.ajax({
				type: \'POST\',
				url: AjaxLinkAdminActualites,
				data: {
					action: \'editActualite\',
					idActualite: idActualite,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailActualite").html(data.html);
					$("#paragrid_' . $this->controller_name . '").slideUp();
					gridActualite.refreshDataAndView();
					$("body").addClass("edit");
					$("#detailActualite").slideDown();
				}
				});

			}
			function deleteActualite(idActualite) {


				$.ajax({
					type: \'POST\',
					url: AjaxLinkAdminActualites,
					data: {
						action: \'deleteActualite\',
						idActualite: idActualite,
						ajax: true
					},
					async: false,
					dataType: \'json\',
					success: function(data) {
						if (data.success) {
							showSuccessMessage(data.message);
							gridActualite.refreshDataAndView();
						} else {
							showErrorMessage(data.message);
						}
					}
				});

			}


		',

		];

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
		//adjustActualiteGridHeight();
		grid' . $this->className . '.refreshView();
		window.dispatchEvent(new Event(\'resize\'));

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'Ajouter une actualitée\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'addNewActualite',
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
                var pieceType = rowData.pieceType;

                return {
                    callback: function(){},

                    items: {


                    "edit": {
                            name: \'' . $this->l('Modifier ') . ' \'+rowData.title,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								editActualite(rowData.id_actualite);
                            }
                        },

                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . ' \ : \'+rowData.title,
                            icon: "trash",

                            callback: function(itemKey, opt, e) {
                                deleteActualite(rowData.id_actualite);
                            }
                        },

                    },
                };
            }',
			]];

		$paragrid->gridExtraFunction = $gridExtraFunction;

		$option = $paragrid->generateParaGridOption();
		$this->paragridScript = $paragrid->generateParagridScript();
		return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
	}

	public function generateParaGridOption() {

		return '';

	}

	public function getActualiteRequest() {

		$actualites = Db::getInstance()->executeS(
			(new DbQuery())
				->select('et.*, etl.*')
				->from('actualite', 'et')
				->leftJoin('actualite_lang', 'etl', 'etl.`id_actualite` = et.`id_actualite` AND etl.`id_lang` = ' . (int) $this->context->language->id)
				->orderBy('et.`id_actualite` ASC')
		);
		$categoryLink = $this->context->link->getAdminLink($this->controller_name);

		foreach ($actualites as &$actualite) {

			if ($actualite['active'] == 1) {
				$actualite['active'] = '<div class="p-active"></div>';
				$actualite['enable'] = true;
			} else {
				$actualite['active'] = '<div class="p-inactive"></div>';
				$actualite['enable'] = false;
			}

			$actualite['openLink'] = $categoryLink . '&id_actualite=' . $actualite['id_actualite'] . '&updateactualite';
			$actualite['addLink'] = $categoryLink . '&addactualite';

		}

		return $actualites;

	}

	public function ajaxProcessgetActualiteRequest() {

		die(Tools::jsonEncode($this->getActualiteRequest()));

	}

	public function getActualiteFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_actualite',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->l('Titre'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'title',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Date'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'actualite_date',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'date',
				'format'   => 'dd/mm/yy',

			],

			[
				'title'      => $this->l('Active'),
				'width'      => 50,
				'dataIndx'   => 'enable',
				'dataType'   => 'bool',
				'editable'   => false,
				'align'      => 'left',
				'hidden'     => true,
				'hiddenable' => 'no',

			],
			[
				'title'    => $this->l('Enabled'),
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

	public function ajaxProcessgetActualiteFields() {

		die(EmployeeConfiguration::get('EXPERT_ACTUALITES_FIELDS'));
	}

	public function ajaxProcessEditActualite() {

		$idActualite = Tools::getValue('idActualite');
		$actualite = new Actualite($idActualite);
		$data = $this->createTemplate('controllers/actualites/editActualite.tpl');
		$data->assign(
			[
				'actualite' => $actualite,
				'tinymce'   => true,
				'iso'       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'  => _THEME_CSS_DIR_,
				'ad'        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages' => Language::getLanguages(false),
			]
		);

		$return = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessAddNewActualite() {

		$actualite = new Actualite();
		$data = $this->createTemplate('controllers/actualites/addActualite.tpl');
		$data->assign(
			[
				'actualite' => $actualite,
				'tinymce'   => true,
				'iso'       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'  => _THEME_CSS_DIR_,
				'ad'        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages' => Language::getLanguages(false),
			]
		);

		$return = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($return));
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
				'title' => $this->l('Actualité'),
				'icon'  => 'icon-tags',
			],
			'input'   => [
				[
					'type'     => 'text',
					'label'    => $this->l('Titre'),
					'name'     => 'title',
					'lang'     => true,
					'required' => true,
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
					'name'         => 'content',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],
				[
					'type'     => 'date',
					'label'    => $this->l('Date de l\'actualité'),
					'name'     => 'actualite_date',
					'required' => true,
				],

			],
			'submit'  => [
				'title' => $this->l('Save'),

			],
		];

		return parent::renderForm();
	}

	
	public function ajaxprocessUpdateActualite() {

		$idActualite = Tools::getValue('id_actualite');
		$actualite = new Actualite($idActualite);

		foreach ($_POST as $key => $value) {

			if (property_exists($actualite, $key) && $key != 'id_actualite') {

				$actualite->{$key}

				= $value;

			}

		}

		$classVars = get_class_vars(get_class($actualite));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($actualite->{$field}) || !is_array($actualite->{$field})) {
							$actualite->{$field}

							= [];
						}

						$actualite->{$field}

						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		$result = $actualite->update();
		$return = [
			'success' => true,
			'message' => 'Le file d‘actualité a été mis à jour avec succès',
		];
		die(Tools::jsonEncode($return));

	}

	public function ajaxProcessAddActualite() {

		$actualite = new Actualite();

		foreach ($_POST as $key => $value) {

			if (property_exists($actualite, $key) && $key != 'id_actualite') {

				$actualite->{$key}

				= $value;

			}

		}

		$classVars = get_class_vars(get_class($actualite));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($actualite->{$field}) || !is_array($actualite->{$field})) {
							$actualite->{$field}

							= [];
						}

						$actualite->{$field}

						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		$result = $actualite->add();
		$return = [
			'success' => true,
			'message' => 'Le file d‘actualité a été ajoutée avec succès',
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessDeleteActualite() {

		$idActualite = Tools::getValue('idActualite');
		$actualite = new Actualite($idActualite);
		$actualite->delete();
		$return = [
			'success' => true,
			'message' => 'Le file d‘actualité a été supprimée avec succès',
		];
		die(Tools::jsonEncode($return));
	}

}
