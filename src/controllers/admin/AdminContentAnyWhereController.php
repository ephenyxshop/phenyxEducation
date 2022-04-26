<?php

require_once _PS_ROOT_DIR_ . '/modules/jscomposer/controllers/admin/AdminVcImagesController.php';
require_once _PS_ROOT_DIR_ . '/modules/jscomposer/classes/VcImageType.php';

if (!defined('_PS_JSCOMPOSER_IMPORT_DIR_')) {
	define('_PS_JSCOMPOSER_IMPORT_DIR_', _PS_ROOT_DIR_ . '/modules/jscomposer/import/');
}

class AdminContentAnyWhereController extends AdminController {

	public function __construct() {

		$this->table = 'contentanywhere';
		$this->className = 'ContentAnywhere';
		$this->lang = true;
		$this->publicName = $this->l('Gestion de contenu front office');
		$this->identifier = 'id_contentanywhere';
		$this->controller_name = 'AdminContentAnyWhere';
		$this->context = Context::getContext();

		$this->bootstrap = true;

		parent::__construct();

		$this->context = Context::getContext();

		if (Shop::isFeatureActive()) {
			Shop::addTableAssociation($this->table, ['type' => 'shop']);
		}

		EmployeeConfiguration::updateValue('EXPERT_VCCONTENT_FIELDS', Tools::jsonEncode($this->getcontentanywhereFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_VCCONTENT_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_VCCONTENT_FIELDS', Tools::jsonEncode($this->getcontentanywhereFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_VCCONTENT_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_VCCONTENT_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_VCCONTENT_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_VCCONTENT_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_VCCONTENT_SCRIPT');
		}

	}

	public function setMedia() {

		parent::setMedia();

		$this->addJqueryUi('ui.widget');
		$this->addJqueryPlugin('tagify');
		$this->addJqueryPlugin('autocomplete');

		MediaAdmin::addJsDef([
			'AjaxLinkAdminContentAnyWhere' => $this->context->link->getAdminLink('AdminContentAnyWhere'),

		]);

	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des géstions de contenu');

		$this->context->smarty->assign([

			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_' . $this->controller_name,
			'tableName'      => $this->table,
			'className'      => $this->className,
			'linkController' => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'       => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript' => $this->paragridScript,
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

			function addNewContent() {
				$.ajax({
				type: \'GET\',
				url: AjaxLinkAdminContentAnyWhere,
				data: {
					action: \'addContent\',
					updateContentAnywhere: 1,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#editContentAnywhere").html(data.html);
					$("#paragrid_' . $this->controller_name . '").slideUp();
					$("#editContentAnywhere").slideDown();

				}
				});
			}

			function editContent(idContent) {
				$.ajax({
				type: \'GET\',
				url: AjaxLinkAdminContentAnyWhere,
				data: {
					action: \'editContent\',
					id_vccontentanywhere: idContent,
					updateContentAnywhere: 1,
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#editContentAnywhere").html(data.html);
					$("#paragrid_' . $this->controller_name . '").slideUp();
					$("#editContentAnywhere").slideDown();

				}
				});
			}

			function deleteConten(idContent) {


			}


			',
		];

		$paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
		$paragrid->paramTable = $this->table;
		$paragrid->paramController = $this->controller_name;
		$paragrid->height = "800";
		$paragrid->showNumberCell = 0;
		$paragrid->pageModel = [
			'type'       => '\'local\'',
			'rPP'        => 100,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		$paragrid->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		$paragrid->complete = 'function(){
		window.dispatchEvent(new Event(\'resize\'));
		$("#ajax_running").hide();

        }';
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
		$paragrid->fillHandle = '\'all\'';
		$paragrid->toolbar = [
			'items' => [
				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Ajouter un nouveau contenu') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'function () {' . PHP_EOL . '
                           addNewContent();
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
		$paragrid->title = '\'' . $this->l('Gestion des contenus') . '\'';
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

                        "edit": {
                            name : \'' . $this->l('Modifier ') . '\'' . '+rowData.firstname+" "+rowData.title,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editContent(rowData.id_vccontentanywhere);
                            }
                        },



                        "sep1": "---------",
                        "delete": {
                            name: \'' . $this->l('Supprimer ') . '\'' . '+rowData.firstname+" "+rowData.title,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                var idStudent = rowData.id_student;
                                deleteConten(rowData.id_vccontentanywhere);
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

	public function getContentAnywhereRequest() {

		$content = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('v.*, vl.*, case when v.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as content_state')
				->from('vccontentanywhere', 'v')
				->leftJoin('vccontentanywhere_lang', 'vl', 'vl.`id_vccontentanywhere` = v.`id_vccontentanywhere` AND vl.`id_lang` = ' . $this->context->language->id)
				->orderBy('v.`id_vccontentanywhere` ASC')
		);

		return $content;

	}

	public function ajaxProcessgetContentAnywhereRequest() {

		die(Tools::jsonEncode($this->getContentAnywhereRequest()));

	}

	public function getContentAnywhereFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_vccontentanywhere',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'      => $this->l('Titre'),
				'dataIndx'   => 'title',
				'dataType'   => 'string',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'left',

			],
			[
				'title'      => $this->l('Hook'),
				'dataIndx'   => 'hook_name',
				'dataType'   => 'string',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'left',

			],
			[
				'title'      => $this->l('Position'),
				'width'      => 150,
				'dataIndx'   => 'position',
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
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
				'dataIndx' => 'content_state',
				'align'    => 'center',
				'dataType' => 'html',

			],

		];

	}

	public function ajaxProcessgetContentAnywhereFields() {

		$fields = EmployeeConfiguration::get('EXPERT_VCCONTENT_FIELDS');
		die($fields);
	}

	public function ajaxProcessEditContent() {

		$idContent = Tools::getValue('id_vccontentanywhere');
		$this->identifier = 'id_vccontentanywhere';
		$_GET['id_vccontentanywhere'] = $idContent;
		$_GET['updateContentAnywhere'] = 1;

		$scripHeader = $this->displayBackOfficeHeader = Hook::exec('displayBackOfficeHeader', []);
		$scriptFooter = $this->displayBackOfficeFooter = Hook::exec('displayBackOfficeFooter', []);

		$html = $scripHeader . PHP_EOL . $this->renderForm() . PHP_EOL . $scriptFooter;
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	public function renderForm() {

		$obj = $this->loadObject();

		$vc_is_edit = false;
		$vccanywhere = '';
		$prd_specify_values = '';
		$cat_specify_values = '';
		$cms_specify_values = '';
		$display_type_values = '';
		$prd_page_values = '';
		$cat_page_values = '';
		$cms_page_values = '';
		$exception_values = '';

		$exception = ContentAnywhere::displayModuleExceptionList();

		if ($id_vccontentanywhere = Tools::getvalue('id_vccontentanywhere')) {
			$contentanywhere = new ContentAnywhere(Tools::getvalue('id_vccontentanywhere'));
			$vc_is_edit = true;

			$vccanywhere = $contentanywhere;
			$prd_specify_values = $contentanywhere->prd_specify;

			$prd_specify_values = $contentanywhere->getFilterValueByContentAnyWhereId($contentanywhere->id, 1);

			$cat_specify_values = $contentanywhere->getFilterValueByContentAnyWhereId($contentanywhere->id, 2);

			$cat_sqlids = '';

			foreach ($cat_specify_values as $k => $id) {
				// print_r($id['id_specify_page']);

				if ($k > 0) {
					$cat_sqlids .= ',';
				}

				$cat_sqlids .= $id['id_specify_page'];
			}

			// print_r( $cat_sqlids);
			//            $prd_specify_values = $prd_specify_values;
			$cat_specify_values = $cat_sqlids;
			$cms_specify_values = $contentanywhere->cms_specify;
			$display_type_values = $contentanywhere->display_type;

			$prd_page_values = $contentanywhere->prd_page;
			$cat_page_values = $contentanywhere->cat_page;
			$cms_page_values = $contentanywhere->cms_page;
			$exception_values = $contentanywhere->exception;
		}

		$vccaw = new ContentAnywhere();
		$getAllCMSPage = $vccaw->getAllCMSPage();
//
		$GetAllHook = $vccaw->GetAllHooks();
		$vc_ajax_url = Context::getContext()->link->getAdminLink('VC_ajax') . '&hook_filter=1';
		$GetAlldisplayHooks = [];

		require_once _PS_MODULE_DIR_ . 'jscomposer/include/helpers/hook.php';

		$customhooks = $this->get_all_hooks_handle();

		$i = 0;

		if (isset($customhooks) && !empty($customhooks)) {

			foreach ($customhooks as $values) {

				$GetAlldisplayHooks[] = ['id' => $values, 'name' => $values];

				$i++;
			}

		}

		$this->fields_form = [
			'legend'  => [
				'title' => $this->l('Gestion du contenu'),
			],
			'input'   => [
				[
					'type' => 'hidden',
					'name' => 'action',
				],
				[
					'type' => 'hidden',
					'name' => 'ajax',
				],
				[
					'type'     => 'text',
					'label'    => $this->l('Title'),
					'name'     => 'title',
					'lang'     => true,
					'required' => true,
					'desc'     => $this->l('Enter Your Title'),
				],
				[
					'type'         => 'textarea',
					'label'        => $this->l('Content'),
					'name'         => 'content',
					'rows'         => 10,
					'cols'         => 62,
					'class'        => 'vc_content_class rte',
					'lang'         => true,
					'autoload_rte' => true,
					// 'required' => true,
					'desc'         => $this->l('Enter Your Description'),
				],
				[
					'type'             => 'exceptionfieldtype',
					'name'             => 'exceptionfieldtype',
					'vc_is_edit'       => $vc_is_edit,
					'exception_values' => $exception_values,
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Set Exception'),
					'name'     => 'exception_type',
					'required' => false,
					'class'    => 'exception_class',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'exception_id_1',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'exception_id_0',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],
				[
					'type'     => 'select',
					'label'    => $this->l('Select Exceptions'),
					'name'     => 'exception_temp',
					'class'    => 'exception_class',
					'id'       => 'exception_id',
					'multiple' => true,
					'options'  => [
						'query' => $exception,
						'id'    => 'id_exception',
						'name'  => 'name',
					],
				],
				// exception
				[
					'type'                => 'vc_content_type',
					'name'                => 'title',
					'vc_is_edit'          => $vc_is_edit,
					'prd_specify_values'  => $prd_specify_values,
					'cat_specify_values'  => $cat_specify_values,
					'cms_specify_values'  => $cms_specify_values,
					'display_type_values' => $display_type_values,
					'prd_page_values'     => $prd_page_values,
					'cat_page_values'     => $cat_page_values,
					'cms_page_values'     => $cms_page_values,
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Show All Page'),
					'name'     => 'display_type',
					'required' => false,
					'class'    => 'display_type_class',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'display_type_id_1',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'display_type_id_0',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				], [
					'type'     => 'switch',
					'label'    => $this->l('Show All Product Page'),
					'name'     => 'prd_page',
					'required' => false,
					'class'    => 'prd_page_class',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'prd_page_id_1',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'prd_page_id_0',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],

				[
					'type'     => 'switch',
					'label'    => $this->l('Show All Category Page'),
					'name'     => 'cat_page',
					'required' => false,
					'class'    => 'cat_page_class',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'cat_page_id_1',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'cat_page_id_0',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],

				[
					'type'     => 'switch',
					'label'    => $this->l('Show All CMS Page'),
					'name'     => 'cms_page',
					'required' => false,
					'class'    => 'cms_page_class',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'cms_page_id_1',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'cms_page_id_0',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],
				[
					'type'     => 'select',
					'label'    => $this->l('CMS Page'),
					'name'     => 'cms_specify_temp',
					'class'    => 'cms_specify_class',
					'id'       => 'cms_specify_id',
					'multiple' => true,
					'options'  => [
						'query' => $getAllCMSPage,
						'id'    => 'id_cms',
						'name'  => 'name',
					],
				],
				[
					'type'    => 'select',
					'label'   => $this->l('Select Display Hook'),
					'name'    => 'hook_name',
					'options' => [
						'query' => $GetAlldisplayHooks,
						'id'    => 'id',
						'name'  => 'name',
					],
					'desc'    => $this->l('Select Your Hook Position where you want to show this!'),
				],
				[
					'type'     => 'switch',
					'label'    => $this->l('Status'),
					'name'     => 'active',
					'required' => false,
					'class'    => 't',
					'is_bool'  => true,
					'values'   => [
						[
							'id'    => 'active',
							'value' => 1,
							'label' => $this->l('Enabled'),
						],
						[
							'id'    => 'active',
							'value' => 0,
							'label' => $this->l('Disabled'),
						],
					],
				],
			],
			'submit'  => [
				'title' => $this->l('Save And Close'),
				'class' => 'btn btn-default pull-right',
			],
			'buttons' => [
				'save-and-stay' => [
					'name'  => 'submitAdd' . $this->table . 'AndStay',
					'type'  => 'submit',
					'title' => $this->l('Save And Stay'),
					'class' => 'btn btn-default pull-right',
					'icon'  => 'process-icon-save',
				],
			],
		];

		if (Shop::isFeatureActive()) {
			$this->fields_form['input'][] = [
				'type'  => 'shop',
				'label' => $this->l('Shop association:'),
				'name'  => 'checkBoxShopAsso',
			];
		}

		if (!($contentanywhere = $this->loadObject(true))) {
			return;
		}

		$this->fields_form['submit'] = [
			'title' => $this->l('Save And Close'),
			'class' => 'btn btn-default pull-right',
		];

		if (!Tools::getValue('id_vccontentanywhere')) {
			$this->fields_value['content_type'] = 1;
			$this->fields_value['display_type'] = 1;
			$this->fields_value['prd_page'] = 1;
			$this->fields_value['cat_page'] = 1;
			$this->fields_value['cms_page'] = 1;
			$this->fields_value['prd_specify_temp'] = '';
			$this->fields_value['cat_specify_temp[]'] = '';
			$this->fields_value['cms_specify_temp[]'] = '';
			$this->fields_value['exception_temp[]'] = '';
		} else {
			$contentanywhere = new ContentAnywhere(Tools::getValue('id_vccontentanywhere'));
			$this->fields_value['prd_specify_temp'] = $contentanywhere->prd_specify;
			$this->fields_value['cat_specify_temp[]'] = $contentanywhere->cat_specify;
			$this->fields_value['cms_specify_temp[]'] = $contentanywhere->cms_specify;
			$this->fields_value['exception_temp[]'] = $contentanywhere->exception;
		}

		$this->fields_value['ajax'] = 1;

		if ($obj->id > 0) {
			$this->fields_value['action'] = 'updateContent';
		} else {
			$this->fields_value['action'] = 'addContent';
		}

		return parent::renderForm();
	}

	public function ajaxProcessAddContent() {

		$content = new ContentAnywhere();

		$classVars = get_class_vars(get_class($content));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($content->{$field}) || !is_array($content->{$field})) {
							$content->{$field}

							= [];
						}

						$content->{$field}

						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		$result = $content->add();
		$return = [
			'success' => true,
			'message' => 'Le contenu a été ajouté avec succès',
		];
		die(Tools::jsonEncode($return));
	}

	public function ajaxProcessUpdateContent() {

		$file = fopen("testProcessUpdateContent.txt", "w");

		$idContent = Tools::getValue('id_vccontentanywhere');
		fwrite($file, $idContent . PHP_EOL);
		$content = new ContentAnywhere($idContent);

		foreach ($_POST as $key => $value) {

			if (property_exists($content, $key) && $key != 'id_vccontentanywhere') {

				$content->{$key}
				= $value;

			}

		}

		$classVars = get_class_vars(get_class($content));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($content->{$field}) || !is_array($content->{$field})) {
							$content->{$field}
							= [];
						}

						$content->{$field}
						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}

		fwrite($file, print_r($content, true) . PHP_EOL);

		$result = $content->update();
		$return = [
			'success' => true,
			'message' => 'Le contenu a été mis à jour avec succès',
		];
		die(Tools::jsonEncode($return));

	}

	public function replaceImageIdsDuringExport($matches) {

		if (!(bool) preg_match('/(\d+,?)/', $matches[2]) || empty($matches[2])) {
			// to prevent unusual sql breakup. here id must be set.
			return "{$matches[1]}=\"\"";
		}

		$db = Db::getInstance();
		$images = $db->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'vc_media WHERE id_vc_media IN(' . $matches[2] . ') ORDER BY FIELD( id_vc_media, ' . $matches[2] . ')', true, false);

		$upload_path = vc_manager()->composer_settings['UPLOADS_DIR'];

		$imagenames = '';

		foreach ($images as $k => $image) {
			$current_path = $upload_path . $image['subdir'];

			if ($k > 0) {
				$imagenames .= ',';
			}

			if (!empty($image['subdir'])) {
				$subdirs = explode(DIRECTORY_SEPARATOR, $image['subdir']);
				$series = '';

				foreach ($subdirs as $sd) {
					$series .= $sd;

					if (!empty($sd) && $this->zipArc->locateName("uploads/{$series}") === FALSE) {
						$this->zipArc->addEmptyDir("uploads/{$series}");
					}

				}

			}

			$this->zipArc->addFile($current_path . $image['file_name'], 'uploads/' . $image['subdir'] . $image['file_name']);

			$imagenames .= $image['subdir'] . $image['file_name'];
		}

		return "{$matches[1]}=\"{$imagenames}\"";
	}

	public function exportVCCanywhere() {

		$db = Db::getInstance();

		$data = [];
		$id_shop = (int) Context::getContext()->shop->id;
		$mainContents = $db->executeS('SELECT v.* FROM ' . _DB_PREFIX_ . 'contentanywhere v INNER JOIN `' . _DB_PREFIX_ . 'contentanywhere_shop` vs ON (v.`id_contentanywhere` = vs.`id_contentanywhere` AND vs.`id_shop` = ' . $id_shop . ')');

		if (!empty($mainContents)) {
			$vc_image_allowed_attr = JsComposer::$vc_image_allowed_attr . Hook::exec('VcAllowedImgAttrs');
			$filename = 'exportvccanywhere' . uniqid() . '.zip';
			$this->exportzippath = _PS_ROOT_DIR_ . "/upload/$filename";
			$pattern = '/(' . $vc_image_allowed_attr . ')\=\"([^"]+)\"+/';

			$this->zipArc = new ZipArchive();

			if ($this->zipArc->open($this->exportzippath, ZipArchive::CREATE) === TRUE) {

				$this->zipArc->addEmptyDir('uploads');

				foreach ($mainContents as $index => $content) {
					$id = $content['id_contentanywhere'];
					unset($content['id_contentanywhere']);
					$data[$index] = $content;
					$langContent = $db->executeS('SELECT title, content FROM ' . _DB_PREFIX_ . 'contentanywhere_lang WHERE id_contentanywhere=' . $id);

					foreach ($langContent as $n => $lang) {

						$langContent[$n]['content'] = preg_replace_callback($pattern, [$this, 'replaceImageIdsDuringExport'], $lang['content']);
					}

					$data[$index]['lang'] = $langContent;
				}

				$str = urlencode(Tools::jsonEncode($data));
				$this->zipArc->addFromString('export.txt', $str);
				$this->zipArc->close();
				$zipContent = Tools::file_get_contents($this->exportzippath);
				@unlink($this->exportzippath);
				header('Content-Description: File Transfer');
				header('Content-Type: application/zip; charset=UTF-8');
				header("Content-Disposition: attachment; filename=" . $filename . ";");
				header("Content-Transfer-Encoding: binary");
				header("Content-Length: " . strlen($zipContent));
				echo $zipContent;
			}

		} else {
			$url = $this->context->link->getAdminLink('Admincontentanywhere');
			Tools::redirectAdmin($url);
		}

		die();
	}

	public function putImageIdsDuringImport($matches) {

		if (empty($matches[2])) {
			return "{$matches[1]}=\"\"";
		}

		$db = Db::getInstance();
		$imagelists = explode(',', $matches[2]);
		$upload_path = vc_manager()->composer_settings['UPLOADS_DIR'];
		$root_path = $this->exportzippath;
		$imagenames = '';
		$vcImages = new AdminVcImagesController();

		foreach ($imagelists as $k => $singlename) {
			$fname = basename($singlename);
//            $fname = "'{$singlename}'";
			$folders = false;

			if (strpos($singlename, '/') !== FALSE) {
				$folders = substr($singlename, 0, strrpos($singlename, $fname));
				$foldersarr = explode('/', $folders);
				$series = '';

				foreach ($foldersarr as $index => $folder) {

					if (!empty($folder)) {
						$series .= $folder;

						if (!is_dir($upload_path . $series)) {
							@mkdir($upload_path . $series);
						}

						$series .= '/';
					}

				}

			}

			$search = 'SELECT * FROM ' . _DB_PREFIX_ . 'vc_media WHERE file_name="' . $fname . '"';

			if ($folders) {
				$search .= " AND subdir='{$folders}'";
			}

			if ($k > 0) {
				$imagenames .= ',';
			}

			$result = $db->getRow($search, false);

			if (!empty($result)) {
				$imagenames .= $result['id_vc_media'];

				if (!file_exists($upload_path . $singlename)) {
					@copy("{$root_path}/uploads/{$singlename}", $upload_path . $singlename);
				}

			} else {

				if (file_exists("{$root_path}/uploads/{$singlename}")) {

					if ($folders) {
						$db->execute("INSERT INTO " . _DB_PREFIX_ . "vc_media(file_name, subdir) VALUES('{$fname}', '{$folders}')");
					} else {
						$db->execute("INSERT INTO " . _DB_PREFIX_ . "vc_media(file_name) VALUES('{$fname}')");
					}

					$imagenames .= $db->Insert_ID();
					@copy("{$root_path}/uploads/{$singlename}", $upload_path . $singlename);
				}

			}

		}

		$formats = VcImageType::getImagesTypes('active');
		$vcImages->_regenerateNewImages($upload_path, $formats);

		return "{$matches[1]}=\"{$imagenames}\"";
	}

	public function ImportVccontent($theme_zip_file, $sandbox) {

		$this->exportzippath = $sandbox . 'uploaded/';

		if (!Tools::ZipExtract($theme_zip_file, $this->exportzippath)) {
			$this->errors[] = $this->l('Error during zip extraction');
		} else {

			if (!file_exists($sandbox . 'uploaded/export.txt')) {
				$this->errors[] = $this->l('Bad configuration file');
			} else {
				$vc_image_allowed_attr = JsComposer::$vc_image_allowed_attr . Hook::exec('VcAllowedImgAttrs');
				$pattern = '/(' . $vc_image_allowed_attr . ')\=\"([^"]+)\"+/';

				$str = Tools::file_get_contents($this->exportzippath . '/export.txt');
				$db = Db::getInstance();
				$contents = Tools::jsonDecode(urldecode($str), true);

				$vccanywhere = _DB_PREFIX_ . 'contentanywhere';
				$vccanywhere_lang = _DB_PREFIX_ . 'contentanywhere_lang';
				$vccanywhere_shop = _DB_PREFIX_ . 'contentanywhere_shop';

				if (!empty($contents)) {
					$id_shop = $this->context->shop->id;
					$languages = Language::getLanguages();

					foreach ($contents as $content) {

						//start from here...
						$langarray = $content['lang'];
						unset($content['lang']);

						if (isset($content['blg_page'])) {
							unset($content['blg_page']);
						}

						if (isset($content['blg_specify'])) {
							unset($content['blg_specify']);
						}

						$fields = array_keys($content);

						$mainsql = "INSERT INTO {$vccanywhere}(" . implode(',', $fields) . ") VALUES";
						$mainsql .= '(';
						$loop = 0;

						foreach ($content as $colname => $coldata) {

							if ($loop > 0) {
								$mainsql .= ',';
							}

							if (is_numeric($coldata)) {

								if ($colname == 'position') {
									$mainsql .= ContentAnywhere::getHigherPosition() + 1;
								} else {
									$mainsql .= $coldata;
								}

							} else
							if (is_string($coldata)) {
								$mainsql .= "'{$coldata}'";
							} else
							if (empty($coldata)) {
								$mainsql .= '" "';
							}

							$loop++;
						}

						$mainsql .= ')';

						$db->execute($mainsql);
						$id_contentanywhere = $db->Insert_ID();
						$shopsql = "INSERT INTO {$vccanywhere_shop}(`id_contentanywhere`,`id_shop`) VALUES({$id_contentanywhere},{$id_shop})";
						$db->execute($shopsql);

						$langsql = "INSERT INTO {$vccanywhere_lang} VALUES";

						foreach ($languages as $ind => $lang) {

							if ($ind > 0) {
								$langsql .= ',';
							}

							if (isset($langarray[$ind]) && !empty($langarray[$ind])) {
								$importlang = $langarray[$ind];
							} else {
								$importlang = $langarray[0];
							}

							$importlang['content'] = preg_replace_callback($pattern, [$this, 'putImageIdsDuringImport'], $importlang['content']);

							$langsql .= "({$id_contentanywhere},{$lang['id_lang']},'{$importlang['title']}','" . addcslashes($importlang['content'], '\'') . "')";
						}

						$db->execute($langsql);
					}

				}

				$this->removeTheExportDir($this->exportzippath);
			}

		}

	}

	private function removeTheExportDir($path) {

		$files = scandir($path);

		foreach ($files as $ff) {

			if ($ff != '.' && $ff != '..') {

				if (is_dir("{$path}/{$ff}")) {
					$this->removeTheExportDir("{$path}/{$ff}");
				} else {
					@unlink("{$path}/{$ff}");
				}

			}

		}

		return @rmdir($path);
	}

	public function renderImportContent() {

		$toolbar_btn['save'] = [
			'href' => '#',
			'desc' => $this->l('Save'),
		];

		$fields_form[0] = [
			'form' => [
				'tinymce' => false,
				'legend'  => [
					'title' => $this->l('Import from your computer'),
					'icon'  => 'icon-picture',
				],
				'input'   => [
					[
						'type'  => 'file',
						'label' => $this->l('Zip file'),
						'desc'  => $this->l('Browse your computer files and select the Zip file for your new theme.'),
						'name'  => 'contentanywherearchive',
					],
				],
				'submit'  => [
					'id'    => 'zip',
					'title' => $this->l('Save'),
				],
			],
		];

		$jscomposer_archive_server = [];
		$files = scandir(_PS_JSCOMPOSER_IMPORT_DIR_);
		$jscomposer_archive_server[] = '-';

		foreach ($files as $file) {

			if (is_file(_PS_JSCOMPOSER_IMPORT_DIR_ . $file) && substr(_PS_JSCOMPOSER_IMPORT_DIR_ . $file, -4) == '.zip') {
				$jscomposer_archive_server[] = [
					'id'   => basename(_PS_JSCOMPOSER_IMPORT_DIR_ . $file),
					'name' => basename(_PS_JSCOMPOSER_IMPORT_DIR_ . $file),
				];
			}

		}

		$fields_form[2] = [
			'form' => [
				'tinymce' => false,
				'legend'  => [
					'title' => $this->l('Import from FTP'),
					'icon'  => 'icon-picture',
				],
				'input'   => [
					[
						'type'    => 'select',
						'label'   => $this->l('Select the archive'),
						'name'    => 'jscomposer_archive_server',
						'desc'    => $this->l('This selector lists the Zip files that you uploaded in the \'/import\' folder.'),
						'options' => [
							'id'    => 'id',
							'name'  => 'name',
							'query' => $jscomposer_archive_server,
						],
					],
				],
				'submit'  => [
					'title' => $this->l('Save'),
				],
			],
		];

		$helper = new HelperForm();

		$helper->currentIndex = $this->context->link->getAdminLink('Admincontentanywhere', false) . '&action=importcontent';
		$helper->token = Tools::getAdminTokenLite('Admincontentanywhere');
		$helper->show_toolbar = true;
		$helper->toolbar_btn = $toolbar_btn;
		$helper->fields_value['contentanywherearchiveUrl'] = '';
		$helper->fields_value['jscomposer_archive_server'] = [];
		$helper->multiple_fieldsets = true;
		$helper->override_folder = $this->tpl_folder;
		$helper->languages = $this->getLanguages();
		$helper->default_form_language = (int) $this->context->language->id;

		return $helper->generateForm($fields_form);
	}

	public function processImportContent() {

		$this->display = 'importcontent';

		if (defined('_PS_HOST_MODE_')) {
			return true;
		}

		if (isset($_FILES['contentanywherearchive']) && isset($_POST['filename']) && Tools::isSubmit('jscomposer_archive_server')) {
			$uniqid = uniqid();
			$sandbox = _PS_CACHE_DIR_ . 'sandbox' . DIRECTORY_SEPARATOR . $uniqid . DIRECTORY_SEPARATOR;
			mkdir($sandbox);
			$archive_uploaded = false;

			if (Tools::getValue('filename') != '') {
				$uploader = new Uploader('contentanywherearchive');
				$uploader->setAcceptTypes(['zip']);
				$uploader->setSavePath($sandbox);
				$file = $uploader->process('uploaded.zip');

				if ($file[0]['error'] === 0) {

					if (Tools::ZipTest($sandbox . 'uploaded.zip')) {
						$archive_uploaded = true;
					} else {
						$this->errors[] = $this->l('Zip file seems to be broken');
					}

				} else {
					$this->errors[] = $file[0]['error'];
				}

			} else
			if (Tools::getValue('contentanywherearchiveUrl') != '') {

				if (!Validate::isModuleUrl($url = Tools::getValue('contentanywherearchiveUrl'), $this->errors)) {
					$this->errors[] = $this->l('Only zip files are allowed');
				} else
				if (!Tools::copy($url, $sandbox . 'uploaded.zip')) {
					$this->errors[] = $this->l('Error during the file download');
				} else
				if (Tools::ZipTest($sandbox . 'uploaded.zip')) {
					$archive_uploaded = true;
				} else {
					$this->errors[] = $this->l('Zip file seems to be broken');
				}

			} else
			if (Tools::getValue('jscomposer_archive_server') != '') {
				$filename = _PS_JSCOMPOSER_IMPORT_DIR_ . Tools::getValue('jscomposer_archive_server');

				if (substr($filename, -4) != '.zip') {
					$this->errors[] = $this->l('Only zip files are allowed');
				} else
				if (!copy($filename, $sandbox . 'uploaded.zip')) {
					$this->errors[] = $this->l('An error has occurred during the file copy.');
				} else
				if (Tools::ZipTest($sandbox . 'uploaded.zip')) {
					$archive_uploaded = true;
				} else {
					$this->errors[] = $this->l('Zip file seems to be broken');
				}

			} else {
				$this->errors[] = $this->l('You must upload or enter a location of your zip');
			}

			if ($archive_uploaded) {
				$this->ImportVccontent($sandbox . 'uploaded.zip', $sandbox);
			}

			Tools::deleteDirectory($sandbox);

			if (count($this->errors) > 0) {
				$this->display = 'importcontent';
			} else {
				Tools::redirectAdmin(Context::getContext()->link->getAdminLink('Admincontentanywhere') . '&conf=18');
			}

		}

	}

	public function processPosition() {

		if ($this->tabAccess['edit'] !== '1') {
			$this->errors[] = Tools::displayError('You do not have permission to edit this.');
		} else

		if (!Validate::isLoadedObject($object = new contentanywhere((int) Tools::getValue($this->identifier, Tools::getValue('id_contentanywhere', 1))))) {
			$this->errors[] = Tools::displayError('An error occurred while updating the status for an object.') . ' <b>' .
			$this->table . '</b> ' . Tools::displayError('(cannot load object)');
		}

		if (!$object->updatePosition((int) Tools::getValue('way'), (int) Tools::getValue('position'))) {
			$this->errors[] = Tools::displayError('Failed to update the position.');
		} else {
			$object->regenerateEntireNtree();
			Tools::redirectAdmin(self::$currentIndex . '&' . $this->table . 'Orderby=position&' . $this->table . 'Orderway=asc&conf=5' . (($id_contentanywhere = (int) Tools::getValue($this->identifier)) ? ('&' . $this->identifier . '=' . $id_contentanywhere) : '') . '&token=' . Tools::getAdminTokenLite('Admincontentanywhere'));
		}

	}

	public function ajaxProcessUpdatePositions() {

		$id_contentanywhere = (int) (Tools::getValue('id'));
		$way = (int) (Tools::getValue('way'));
		$positions = Tools::getValue($this->table);

		if (is_array($positions)) {

			foreach ($positions as $key => $value) {
				$pos = explode('_', $value);

				if ((isset($pos[1]) && isset($pos[2])) && ($pos[2] == $id_contentanywhere)) {
					$position = $key + 1;
					break;
				}

			}

		}

		$contentanywhere = new contentanywhere($id_contentanywhere);

		if (Validate::isLoadedObject($contentanywhere)) {

			if (isset($position) && $contentanywhere->updatePosition($way, $position)) {
				Hook::exec('actioncontentanywhereUpdate');
				die(true);
			} else {
				die('{"hasError" : true, errors : "Can not update contentanywhere position"}');
			}

		} else {
			die('{"hasError" : true, "errors" : "This contentanywhere can not be loaded"}');
		}

	}

	public function get_all_hooks_handle() {

		$fonts = [];
		$font = @unserialize(Configuration::get('vc_custom_hook'));

		if (!empty($font)) {

			foreach ($font as $key => $value) {

				$fonts[] = $value;
			}

		}

		return $fonts;
	}

	public function initProcess() {

		if (Tools::getIsset('duplicate' . $this->table)) {

			if ($this->tabAccess['add'] === '1') {
				$this->action = 'duplicate';
			} else {
				$this->errors[] = Tools::displayError('You do not have permission to add this.');
			}

		}

		if (!$this->action) {
			parent::initProcess();
		} else {
			$this->id_object = (int) Tools::getValue($this->identifier);
		}

	}

	public function processDuplicate() {

		if (Validate::isLoadedObject($contentanywhere = new contentanywhere((int) Tools::getValue('id_contentanywhere')))) {
			$id_contentanywhere = (int) Tools::getValue('id_contentanywhere');
			unset($contentanywhere->id);
			$contentanywhere->active = 0;
			$contentanywhere->position = ContentAnywhere::getHigherPosition() + 1;

			if ($contentanywhere->add()) {
				$this->redirect_after = self::$currentIndex . '&conf=19&token=' . $this->token;
			} else {
				$this->errors[] = Tools::displayError('An error occurred while creating an object.');
			}

		}

	}

	public function processSave() {

		if (
			Tools::isSubmit('submitAddcontentanywhereAndStay') ||
			Tools::isSubmit('submitAddcontentanywhere')
		) {
			$object = parent::processSave();

			if (!is_object($object)) {
				return true;
			}

			$exception_type = Tools::getValue('exception_type');

			if ($exception_type == 0) {
				$object->exception = "";
			}

			//prd_page
			$prd_page = Tools::getValue('prd_page');
			$object->prd_specify = "";

			if ($prd_page != 1) {

				if (Tools::isSubmit('inputAccessories') && is_object($object)) {
					$prd_specify = Tools::getValue('inputAccessories');
					//$id_contentanywhere = Tools::getValue('id_contentanywhere');

					$accessories_id = array_unique(explode('-', $prd_specify));
					$this->changeAccessories($object, 1, $accessories_id);
					// here we need to save this value in filter table
				}

			}

			$cat_page = Tools::getValue('cat_page');

			if ($cat_page == 1) {
				$object->cat_specify = "";
			} else

			if (Tools::isSubmit('cat_specify_temp') && is_object($object)) {
				$cat_specify = Tools::getValue('cat_specify');

				$cat_accessories_id = explode(',', $cat_specify);
				$this->changeAccessories($object, 2, $cat_accessories_id);
				// here we need to save this value in filter table
			}

			$cms_page = Tools::getValue('cms_page');

			if ($cms_page == 1) {
				$object->cms_specify = "";
			} else

			if (Tools::isSubmit('cms_specify_temp') && is_object($object)) {
				$prd_specify = Tools::getValue('cms_specify');
				$accessories_id = array_unique(explode(',', $prd_specify));
				$this->changeAccessories($object, 3, $accessories_id);
				// here we need to save this value in filter table
			}

			$object->update();

			vc_manager()->vccClearCache();

			return $object;
		}

		return true;
	}

	/**
	 * Link accessories with product
	 *
	 * @param array $accessories_id Accessories ids
	 */
	public function changeAccessories($contentanywhere, $option_page, $accessories_id) {

		Db::getInstance()->delete($this->table . '_filter', '`' . bqSQL($this->identifier) . '` = ' . (int) $contentanywhere->id . " AND `page` = {$option_page}");
//        $contentanywhere->deleteContentAnywherProductAccessories($option_page);

		if (count($accessories_id)) {
//            array_pop($accessories_id);

			foreach ($accessories_id as $id_specify_page) {

				if ((int) $id_specify_page > 0) {
					Db::getInstance()->insert('contentanywhere_filter', [
						'id_contentanywhere' => (int) $contentanywhere->id,
						'id_specify_page'    => $id_specify_page,
						'page'               => $option_page,
					]);
				}

			}

		}

	}

	public function processDelete() {

		$object = parent::processDelete();
		$id = $object->id_contentanywhere;
		Db::getInstance()->delete('contentanywhere_filter', "id_contentanywhere={$id}");
		return $object;
	}

	public function processBulkDelete() {

		$result = parent::processBulkDelete();

		if (is_array($this->boxes) && !empty($this->boxes)) {

			foreach ($this->boxes as $id) {
				Db::getInstance()->delete('contentanywhere_filter', "id_contentanywhere={$id}");
			}

		}

		return $result;
	}

}
