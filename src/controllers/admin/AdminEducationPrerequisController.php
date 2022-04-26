<?php

/**
 * Class AdminEducationPrerequisControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationPrerequisControllerCore extends AdminController {

	/**
	 * AdminEducationsControllerCore constructor.
	 *
	 * @since 1.9.1.0
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'education_prerequis';
		$this->className = 'EducationPrerequis';
		$this->lang = true;
		$this->publicName = $this->l('Prérequis des Formations');
		$this->context = Context::getContext();

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_EDUCATION_PREREQUIS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATION_PREREQUIS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATION_PREREQUIS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATION_PREREQUIS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_EDUCATION_PREREQUIS_FIELDS', Tools::jsonEncode($this->getEducationPrerequisFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATION_PREREQUIS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_EDUCATION_PREREQUIS_FIELDS', Tools::jsonEncode($this->getEducationPrerequisFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATION_PREREQUIS_FIELDS'), true);
		}

		$this->extracss = $this->pushCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/prerequis.css', 'all', 0);

	}

	public function setMedia() {

		parent::setMedia();
		$this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/prerequis.css', 'all', 0);
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

		$this->TitleBar = $this->l('Liste des prérequis');

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

			function duplicatePrerequis(idPrerequis) {

			$.ajax({
				type: \'POST\',
				url: AjaxLinkAdminEducationPrerequis,
				data: {
					action: \'duplicatePrerequis\',
					idPrerequis: idPrerequis,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailPrerequis").html(data.html);
					$("#paragrid_' . $this->controller_name . '").slideUp();
					gridEducationPrerequis.refreshDataAndView();
					$("body").addClass("edit");
					$("#detailPrerequis").slideDown();
				}
				});

			}




		',

		];

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = "650";
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 20,
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

		$paragrid->selectionModelType = 'row';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'Ajouter un prérequis\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                    	addAjaxObject("' . $this->controller_name . '");' . PHP_EOL . '
                    }' . PHP_EOL,
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
                var pieceType = rowData.pieceType;

                return {
                    callback: function(){},

                    items: {
						"duplicate": {
                            name: \'' . $this->l('Dupliquer le formulaire de prérequis ') . ' \'+rowData.name,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								duplicatePrerequis(rowData.id_education_prerequis);
                            }
                        },

                    "edit": {
                            name: \'' . $this->l('Modifier le formulaire de prérequis ') . ' \'+rowData.name,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
								return true;
                            },
                            callback: function(itemKey, opt, e) {

								 editAjaxObject("' . $this->controller_name . '", rowData.id_education_prerequis)
                            }
                        },

                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . ' \ : \'+rowData.name,
                            icon: "trash",

                            callback: function(itemKey, opt, e) {
                                //deletePrerequis(rowData.id_education_prerequis);
								deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer un prérequis", "Etes vous sure de vouloir supprimer ce prérequis ?", "Oui", "Annuler",rowData.id_education_prerequis);
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

	public function getEducationPrerequisRequest() {

		$education_prerequiss = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_prerequis')
				->orderBy('`id_education_prerequis` ASC')
		);
		$categoryLink = $this->context->link->getAdminLink($this->controller_name);

		foreach ($education_prerequiss as &$education_prerequis) {

			$education_prerequis['openLink'] = $categoryLink . '&id_education_prerequis=' . $education_prerequis['id_education_prerequis'] . '&updateeducation_prerequis';
			$education_prerequis['addLink'] = $categoryLink . '&addeducation_prerequis';
			$education_prerequis['numberFields'] = count(unserialize($education_prerequis['content']));

		}

		return $education_prerequiss;

	}

	public function ajaxProcessgetEducationPrerequisRequest() {

		die(Tools::jsonEncode($this->getEducationPrerequisRequest()));

	}

	public function getEducationPrerequisFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_education_prerequis',
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
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Nombre de questions'),

				'dataIndx' => 'numberFields',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Score minimum'),

				'dataIndx' => 'min_score',
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->l('Version'),
				'dataIndx' => 'version',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],

		];

	}

	public function ajaxProcessgetEducationPrerequisFields() {

		die(EmployeeConfiguration::get('EXPERT_EDUCATION_PREREQUIS_FIELDS'));
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

			'input'   => [

				[
					'type'         => 'textarea',
					'label'        => $this->l('Description haut de page'),
					'name'         => 'header',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],
				[
					'type'         => 'textarea',
					'label'        => $this->l('Description haut de page'),
					'name'         => 'tags',
					'autoload_rte' => true,
					'lang'         => true,
					'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
				],

			],
			'submit'  => [
				'title' => $this->l('Save'),

			],
		];

		$isoTinyMce = $this->context->language->iso_code;
		$isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');

		$this->tpl_form_vars = [
			'token'        => $this->token,
			'prerequis'    => $obj,
			'contents'     => $obj->content,
			'languages'    => $this->_languages,
			'ad'           => dirname($_SERVER['PHP_SELF']),
			'iso_tiny_mce' => $isoTinyMce,
			'id_lang'      => $this->context->language->id,
		];

		MediaAdmin::addJsDef([
			'contents' => $obj->content,
		]);

		return parent::renderForm();
	}

	public function ajaxProcessduplicatePrerequis() {

		$idPrerequis = Tools::getValue('idPrerequis');
		$prerequis = new EducationPrerequis($idPrerequis);
		$prerequis = $prerequis->duplicateObject();
		$prerequis->name = '';
		$prerequis->version = '';

		$data = $this->createTemplate('controllers/education_prerequis/editPrerequis.tpl');
		$data->assign(
			[
				'prerequis' => $prerequis,
				'tinymce'   => true,
				'iso'       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'  => _THEME_CSS_DIR_,
				'ad'        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages' => Language::getLanguages(false),
				'contents'  => $prerequis->content,
			]
		);

		$return = [
			'html' => $data->fetch(),
		];

		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessCancelDuplicate() {

		$id_education_prerequis = Tools::getValue('id_education_prerequis');
		$prerequis = new EducationPrerequis($id_education_prerequis);
		$prerequis->delete();

		$result = [
			'success' => true,
		];

		die(Tools::jsonEncode($result));

	}

	public function ajaxProcessEditObject() {

		$targetController = $this->targetController;
		if ($this->tabAccess['edit'] == 1) {   

		$idPrerequis = Tools::getValue('idObject');
		$prerequis = new EducationPrerequis($idPrerequis);

		$data = $this->createTemplate('controllers/education_prerequis/editPrerequis.tpl');
		$data->assign(
			[
				'prerequis' => $prerequis,
				'tinymce'   => true,
				'iso'       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'  => _THEME_CSS_DIR_,
				'ad'        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages' => Language::getLanguages(false),
				'contents'  => $prerequis->content,
			]
		);

		$li = '<li id="uperEdit' . $targetController . '" data-controller="AdminDashboard"><a href="#contentEdit' . $targetController . '">Modifier un prérequis</a><button type="button" class="close tabdetail" data-id="uperEdit' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentEdit' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'success' => true,
			'li'   => $li,
			'html' => $html,
		];
		} else {
            $result = [
				'success' => false,
				'message'   => 'Votre profile administratif ne vous permet pas d‘éditer les pré requis',
			];
        }
		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessAddObject() {

		$targetController = $this->targetController;

		$prerequis = new EducationPrerequis(1);

		$prerequis = $prerequis->duplicateObject();
		$prerequis->name = '';
		$prerequis->version = '';

		$data = $this->createTemplate('controllers/education_prerequis/duplicatePrerequis.tpl');
		$data->assign(
			[
				'prerequis' => $prerequis,
				'tinymce'   => true,
				'iso'       => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
				'path_css'  => _THEME_CSS_DIR_,
				'ad'        => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
				'languages' => Language::getLanguages(false),
				'contents'  => $prerequis->content,
			]
		);

		$li = '<li id="uperAdd' . $targetController . '" data-controller="AdminDashboard"><a href="#contentAdd' . $targetController . '">Ajouter un Prérequis</a><button type="button" class="close tabdetail" data-id="uperAdd' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="contentAdd' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,

			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSavePrerequisField() {

		$idPrerequis = Tools::getValue('idPrerequis');
		$prerequis = new EducationPrerequis($idPrerequis);
		$content = Tools::getValue('content');
		$key = count($content) - 1;
		$prerequis->content = serialize($content);
		$prerequis->update();

		$html = '<tr id="content_' . $key . '">
      					<td>' . $content[$key]['type'] . '</td>
      					<td>' . $content[$key]['name'] . '</td>
						<td>' . $content[$key]['values'] . '</td>
						<td align="center"><button class="buttonremoveFields" onClick="removeFields(' . $key . ')"><i class="icon icon-trash" aria-hidden="true"></i></button></td>
    				</tr>';

		$result = [
			'success' => true,
			'message' => 'Le champs a été ajouté avec succès',
			'html'    => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function ajaxProcessSubmitPrerequis() {

		$idPrerequis = Tools::getValue('id_education_prerequis');

		if (Validate::isUnsignedId($idPrerequis)) {
			$prerequis = new EducationPrerequis($idPrerequis);
		} else {
			$prerequis = new EducationPrerequis();
		}

		$_POST['content'] = json_decode($_POST['content'], true);

		foreach ($_POST as $key => $value) {

			if (property_exists($prerequis, $key) && $key != 'id_education_prerequis') {

				$prerequis->{$key}

				= $value;
			}

		}

		$classVars = get_class_vars(get_class($prerequis));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($prerequis->{$field}) || !is_array($prerequis->{$field})) {
							$prerequis->{$field}

							= [];
						}

						$prerequis->{$field}

						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		if (Validate::isUnsignedId($idPrerequis)) {
			$prerequis->update();
			$result = [
				'message' => 'Le prérequis été mis à jour avec succès',
				'success' => true,
			];
		} else {
			$prerequis->add();
		}

		die(Tools::jsonEncode($result));

	}

}
