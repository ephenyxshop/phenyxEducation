<?php

class AdminPFGFieldsController extends AdminController {

	private $pfg_model;

	public $id_pfg;

	/**
	 * Initialize the controller based on the given form id
	 */
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'pfg_fields';
		$this->className = 'PFGFieldModel';

		parent::__construct();
		$this->context = Context::getContext();
		EmployeeConfiguration::updateValue('EXPERT_PFGFILEDS_SCRIPT', $this->generateParaGridScript());
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_PFGFILEDS_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_PFGFILEDS_SCRIPT', $this->generateParaGridScript());
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_PFGFILEDS_SCRIPT');
		}

		EmployeeConfiguration::updateValue('EXPERT_PFGFILEDS_FIELDS', Tools::jsonEncode($this->getPFGFieldModelFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PFGFILEDS_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_PFGFILEDS_FIELDS', Tools::jsonEncode($this->getPFGFieldModelFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PFGFILEDS_FIELDS'), true);
		}

	}

	public function setMedia() {

		parent::setMedia();
		Media::addJsDef([
			'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
		]);
	}

	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;
		$ajaxlink = $this->context->link->getAdminLink($this->controller_name);

		$this->TitleBar = $this->la('Liste du formulaires');

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
			'bo_imgdir'      => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/',
			'idController'   => '',
		]);

		parent::initContent();

	}

	public function generateParaGridScript() {

		$gridExtraFunction = [
			'
			function backFormList() {
				$("#paragrid_AdminPFG").slideDown();
				$("#detailFormulaire").slideUp();
				$("#detailFormulaire").html("");
			
			}
			
			function addNewField() {

			$.ajax({
				type: \'GET\',
				url: AjaxLinkAdminPFGFields,
				data: {
					action: \'addNewField\',
					ajax: true
				},
				async: false,
				dataType: \'json\',
				success: function(data) {
					$("#detailFieldForm").html(data.html);
					$("body").addClass("edit");
					$("#paragrid_' . $this->controller_name . '").slideUp();
					$("#detailFieldForm").slideDown();
				}
				});

			}
			function editActualite(idActualite) {

			$.ajax({
				type: \'POST\',
				url: AjaxLinkAdminPFGFields,
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
					url: AjaxLinkAdminPFGFields,
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
					'label'    => '\'Ajouter un nouveau champ de formulaire\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'addNewField',
				],
				[
					'type'     => '\'button\'',
					'label'    => '\'Retour à la liste des formulaires\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'backFormList',
				],

			],
		];
		$paragrid->dragModel = [
			'on'          => true,
			'diHelper'    => "['position']",
			'clsHandle'   => '\'dragHandle\'',
			'dragNodes'   => 'function(rd, evt){
                var checkNodes = this.Tree().getCheckedNodes();
                return (checkNodes.length && checkNodes.indexOf(rd)>-1 )? checkNodes: [ rd ];
            }',
			'isDraggable' => 'function(ui){
                return !(ui.rowData.pq_gsummary || ui.rowData.pq_level == 0);
            }',
		];
		$paragrid->dropModel = [
			'on'          => true,
			'isDroppable' => 'function(evt, uiDrop){

                var Drag = uiDrop.helper.data(\'Drag\'),
                    uiDrag = Drag.getUI(),
                    rdDrag = uiDrag.rowData,
                    rdDrop = uiDrop.rowData,
                    Tree = this.Tree(),
                    denyDrop = (
                        rdDrop == rdDrag ||
                        rdDrop.pq_gsummary ||
                        Tree.isAncestor( rdDrop,  rdDrag)
                    );

                return !denyDrop;
            }',
		];
		$paragrid->moveNode = 'function(event, ui) {
			 		console.log(ui);
                var startIndex = ui.args[0][0].fieldPosition;
				var idAttribute = ui.args[0][0].id_pfg_field;
				var idParent = ui.args[0][0].d_pfg;
                var stopIndex = parseInt(ui.args[1]);
                var way = (startIndex < stopIndex) ? 1 : 0;
				processAttributePosition(idAttribute, idParent, way, startIndex, stopIndex)

                }';

		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->la('Gestion des formulaires') . '\'';
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
                            name: \'' . $this->la('Modifier ') . ' \'+rowData.title,
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
                            name: \'' . $this->la('Supprimer ') . ' \ : \'+rowData.title,
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

	public function getPFGFieldModelRequest($idForm) {

	
		$formulaires = Db::getInstance()->executeS(
			(new DbQuery())
				->select('et.*, etl.*')
				->from('pfg_fields', 'et')
				->leftJoin('pfg_fields_lang', 'etl', 'etl.`id_pfg_field` = et.`id_pfg_field` AND etl.`id_lang` = ' . (int) $this->context->language->id)
				->where('id_pfg = ' . $idForm)
				->orderBy('et.`position` ASC')
		);

		foreach ($formulaires as &$formulaire) {

			$formulaire['fieldPosition'] = $formulaire['position'];
			$formulaire['position'] = '<div class="dragGroup"><div class="valueField positions" data-id="' . $formulaire['id_pfg_field'] . '">' . $formulaire['position'] . '</div></div>';

		}
	

		return $formulaires;

	}

	public function ajaxProcessGetPFGFieldModelRequest() {

		$idForm = Tools::getValue('id_pfg');
		die(Tools::jsonEncode($this->getPFGFieldModelRequest($idForm)));

	}

	public function getPFGFieldModelFields() {

		return [
			[
				'title'      => $this->la('ID'),
				'maxWidth'   => 100,
				'dataIndx'   => 'id_pfg_field',
				'dataType'   => 'integer',
				'editable'   => false,
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
				'hiddenable' => 'no',
			],
			[
				'title'      => '',
				'width'      => 0,
				'dataIndx'   => 'id_pfg',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->la('Label'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'label',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->la('Nom'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'name',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],
			[
				'title'    => $this->la('Type'),
				'minWidth' => 150,
				'exWidth'  => 20,
				'dataIndx' => 'type',
				'align'    => 'left',
				'valign'   => 'center',
				'dataType' => 'string',

			],

			[
				'title'      => ' ',
				'width'      => 10,
				'dataIndx'   => 'fieldPosition',
				'dataType'   => 'integer',
				'hidden'     => true,
				'hiddenable' => 'no',
			],

			[
				'title'    => $this->la('Position'),
				'minWidth' => 100,
				'maxWidth' => 100,
				'dataIndx' => 'position',
				'cls'      => 'pointer dragHandle',
				'dataType' => 'html',
				'align'    => 'center',
			],

		];

	}

	public function ajaxProcessgetPFGFieldModelFields() {

		die(EmployeeConfiguration::get('EXPERT_PFGFILEDS_FIELDS'));
	}

	/**
	 * Update the position of two fields via an Ajax request
	 */
	public function ajaxProcessUpdateFieldFormPosition() {

		
		$idField = (int) Tools::getValue('idField');
		$idForm = Tools::getValue('idForm');
		$stopIndex = Tools::getValue('stopIndex');
		$stopIndex--;

		$object = new PFGFieldModel($idField);

		if (Validate::isLoadedObject($object)) {
			$initPosition = $object->position;
			if ($initPosition > $stopIndex) {
				$objects = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
					(new DbQuery())
						->select('id_pfg_field,  `position` ')
						->from('pfg_fields')
						->where('`id_pfg` = ' . (int) $idForm . ' AND `position` >= ' . (int) $stopIndex . ' AND `position` <= ' . (int) $initPosition)
						->orderBy('`position` ASC')
				);

				if (!empty($objects)) {
					$k = $stopIndex + 1;

					foreach ($objects as $moveObject) {

						if ($moveObject['id_pfg_field'] == $idField) {
							$result = Db::getInstance()->execute(
								'UPDATE `' . _DB_PREFIX_ . 'pfg_fields`
								SET `position`= ' . (int) $stopIndex . '
								WHERE `id_pfg_field` =' . (int) $idField);
							continue;
						}

						$result = Db::getInstance()->execute(
							'UPDATE `' . _DB_PREFIX_ . 'pfg_fields`
							SET `position`= ' . (int) $k . '
							WHERE `id_pfg_field` =' . (int) $moveObject['id_pfg_field']);

						if (!$result) {
							$this->errors[] = Tools::displayError('A problem occur with Field position update ' . $moveObject['id_pfg_field']);
						} else {
							$k++;
						}

					}

				}

			} else

			if ($initPosition < $stopIndex) {

				$objects = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
					(new DbQuery())
						->select('id_pfg_field,  `position` ')
						->from('pfg_fields')
						->where('`id_pfg` = ' . (int) $idForm . ' AND `position` >= ' . (int) $initPosition . ' AND `position` <= ' . $stopIndex)
						->orderBy('`position` ASC')
				);

				if (!empty($objects)) {

					$k = $initPosition;

					foreach ($objects as $moveObject) {

						if ($moveObject['id_pfg_field'] == $idField) {

							$result = Db::getInstance()->execute(
								'UPDATE `' . _DB_PREFIX_ . 'pfg_fields`
								SET `position`= ' . (int) $stopIndex . '
								WHERE `id_pfg_field` =' . (int) $idField);
								continue;
							}

							$result = Db::getInstance()->execute(
								'UPDATE `' . _DB_PREFIX_ . 'pfg_fields`
								SET `position`= ' . (int) $k . '
								WHERE`id_pfg_field` =' . (int) $moveObject['id_pfg_field']);

							if (!$result) {
								$this->errors[] = Tools::displayError('A problem occur with Field position update ' . $moveObject['id_pfg_field']);
							} else {

								$k++;
							}

						}

					}

				}

			}

			if (empty($this->errors)) {
				$result = [
					'success' => true,
					'message' => $this->la('Field position has been successfully updated.'),
				];
			} else {
				$this->errors = array_unique($this->errors);
				$result = [
					'success' => false,
					'message' => implode(PHP_EOL, $this->errors),
				];

			}

		die(Tools::jsonEncode($result));

	}

		
	public function ajaxProcessAddPFGField() {
		
		
		$pgfield = new PFGFieldModel();
		
		foreach ($_POST as $key => $value) {
			if (property_exists($pgfield, $key) && $key != 'id_pfg_field') {
             	$pgfield->{$key}  = $value;
            }
        }

        $classVars = get_class_vars(get_class($pgfield));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($pgfield->{$field}) || !is_array($pgfield->{$field})) {
                        	$pgfield->{$field} = [];
                        }
                        $pgfield->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		
		
		
        $result = $pgfield->add();
		
		$return = [
        	'success' => true,
            'message' => $this->la('Le champs a été ajouté avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessUpdatePFGField() {
		
		$id_pfg = Tools::getValue('id_pfg_field');
		
		$pgfield = new PFGFieldModel($id_pfg);
		
		foreach ($_POST as $key => $value) {
			if (property_exists($pgfield, $key) && $key != 'id_pfg_field') {
             	$pgfield->{$key}  = $value;
            }
        }

        $classVars = get_class_vars(get_class($pgfield));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($pgfield->{$field}) || !is_array($pgfield->{$field})) {
                        	$pgfield->{$field} = [];
                        }
                        $pgfield->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		
		
		
        $result = $pgfield->update();
		
		$return = [
        	'success' => true,
            'message' => $this->la('Le champs a été mis à jour avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
		
	}

	
	
	public function ajaxProcessAddNewField() {
		
		$_GET['addpfg_fields'] = "";

		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
		
	}
	
	public function ajaxProcessEditField() {
		
		$file = fopen("testProcessEditField.txt","w");
		$idField = Tools::getValue('idField');
		fwrite($file,$idField.PHP_EOL);

		$this->identifier = 'id_pfg_field';
		$_GET['id_pfg_field'] = $idField;
		$_GET['updatepfg_fields'] = "";
		
		$this->object = new PFGFieldModel((int)$idField);
		
		fwrite($file, print_r($this->object, true));

		$html = $this->renderForm();
		fwrite($file,$html);
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

	/**
	 * Renders the form using FormHelper
	 *
	 * @see AdminController::renderForm
	 */
	public function renderForm() {

		if (!$this->loadObject(true)) {
			return;
		}

		if (Validate::isLoadedObject($this->object)) {
			$this->display = 'edit';
		} else {
			$this->display = 'add';
		}

		

		$context = Context::getContext();
		

		$field_types = [
			['value' => 'text', 'name' => $this->la('Text')],
			['value' => 'number', 'name' => $this->la('Number')],
			['value' => 'email', 'name' => $this->la('Email')],
			['value' => 'url', 'name' => $this->la('URL')],
			['value' => 'textarea', 'name' => $this->la('Textarea')],
			['value' => 'select', 'name' => $this->la('Select')],
			['value' => 'radio', 'name' => $this->la('Radio')],
			['value' => 'checkbox', 'name' => $this->la('Checkbox')],
			['value' => 'multicheckbox', 'name' => $this->la('Multiple Checkbox')],
			['value' => 'file', 'name' => $this->la('File')],
			['value' => 'hidden', 'name' => $this->la('Hidden field')],
			['value' => 'separator', 'name' => $this->la('Separator')],
			['value' => 'static', 'name' => $this->la('Static field')],
			['value' => 'legend', 'name' => $this->la('Legend (Fieldset)')],
		];

		if ($this->isGdEnabled()) {
			$field_types[] = ['value' => 'captcha', 'name' => $this->la('Captcha')];
		}

		$field_related = [
			['value' => '', 'name' => $this->la('Nothing')],
			['value' => 'email', 'name' => $this->la('Email of the sender')],
			['value' => 'subject', 'name' => $this->la('Subject of the email')],
			['value' => 'newsletter', 'name' => $this->la('Newsletter opt-in')],
		];

		$this->fields_form = [
			'tinymce' => false,
			'legend'  => [
				'title' => $this->la('Powerful Form Generator'),
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
                    'type' => 'hidden',
                    'name' => 'id_pfg',
                ],
				[
					'type'     => 'text',
					'label'    => $this->la('Label :'),
					'name'     => 'label',
					'lang'     => true,
					'required' => true,
					'class'    => 'fixed-width-xl label-field',
					'size'     => 50,
				],
				[
					'type'     => 'text',
					'label'    => $this->la('Name :'),
					'name'     => 'name',
					'required' => true,
					'class'    => 'fixed-width-xl',
					'size'     => 50,
					'desc'     => html_entity_decode($this->la('This value will not be shown to your customer and will only be used internally or as variables for the messages sent/showed by the form.<br />Alphanumerical value only. (No space or special characters)')),
				],
				[
					'type'    => 'select',
					'label'   => $this->la('Type'),
					'id'      => 'pfg-field-select-types',
					'name'    => 'type',
					'options' => [
						'query' => $field_types,
						'id'    => 'value',
						'name'  => 'name',
					],
					'desc'    => $this->la('Type of field.'),
				],
				[
					'type'     => 'text',
					'label'    => $this->la('Values :'),
					'name'     => 'values',
					'lang'     => true,
					'required' => true,
					'class'    => 'fixed-width-xl pfg-fields-values',
					'desc'     => html_entity_decode($this->la("<span class='field-select-items field-select field-radio'>Comma separated list of options.</span><span class='field-select-items field-checkbox'>Message to display at the right of the checkbox.</span><span class='field-select-items field-file'>Comma separated list of accepted files formats (WITHOUT the extension).</span><span class='field-select-items field-static'>Message to show as a static field.</span>", 'AdminPFGFields', false, false)),
					'size'     => 50,
				],
				[
					'type'     => 'radio',
					'label'    => $this->la('Required:'),
					'name'     => 'required',
					'required' => false,
					'is_bool'  => true,
					'class'    => 't',
					'values'   => [
						[
							'id'    => 'active_on',
							'value' => 1,
							'label' => $this->la('Yes'),
						],
						[
							'id'    => 'active_off',
							'value' => 0,
							'label' => $this->la('No'),
						],
					],
				],
				[
					'type'     => 'text',
					'label'    => $this->la('Class :'),
					'name'     => 'class',
					'required' => false,
					'class'    => 'fixed-width-xl',
					'size'     => 50,
					'desc'     => $this->la('This class will be affected to the container of the input, not the input directly.'),
				],
				[
					'type'     => 'text',
					'label'    => $this->la('Style (css) :'),
					'name'     => 'style',
					'required' => false,
					'class'    => 'fixed-width-xl',
					'size'     => 50,
				],
				[
					'type'     => 'text',
					'label'    => $this->la('Extra :'),
					'name'     => 'extra',
					'required' => false,
					'class'    => 'fixed-width-xl',
					'size'     => 50,
				],
				[
					'type'    => 'select',
					'label'   => $this->la('Link this field to :'),
					'name'    => 'related',
					'options' => [
						'query' => $field_related,
						'id'    => 'value',
						'name'  => 'name',
					],
					'desc'    => $this->la('This will link this field to a specific value of the form.'),
					'class'   => 'fixed-width-xl',
				],
			],
			'submit'  => [
				'title' => $this->la('Save'),
				'class' => 'btn btn-default pull-right',
			],
		];

		$this->tpl_form_vars = [
			'required' => $this->object->required,
			'EPH_ALLOW_ACCENTED_CHARS_URL', (int) Configuration::get('EPH_ALLOW_ACCENTED_CHARS_URL'),
		];
		$this->fields_value['ajax'] = 1;
		if($this->object->id > 0) {
			$this->fields_value['action'] = 'updatePFGField';
		} else {
			$this->fields_value['action'] = 'addPFGField';
		}
		$this->fields_value['id_pfg'] = $this->id_pfg;

		$languages = Language::getLanguages(true);

		if (count($languages) > 1) {
			$this->warnings[] = $this->la('You use more than one language on your shop. Don\t forget to mention a value for each language before submitting this form.');
		}

		if (!$this->isGdEnabled()) {
			$this->warnings[] = $this->la('Missing GD library with jpeg support. Captcha will not work.');
		}

		return parent::renderForm();
	}

	/**
	 * Returns true weither the GD library is enabled or not
	 *
	 * @return boolean
	 */
	private function isGdEnabled() {

		return (function_exists('ImageCreate') && function_exists('ImageJpeg'));
	}

	/**
	 * Process to the validation of the submitted HelperForm
	 */
	protected function _childValidation() {

		if (!preg_match('/^[a-z0-9_\[\]]+$/', Tools::getValue('name'))) {
			$this->errors[] = $this->la('Please use only alphanumerical (a-z, 0-9) and underscore ("_") caracters for the name field.');
		}

		// We check that the name does not exists already :

		if (PFGFieldModel::isNameAlreadyTaken(Tools::getValue('name'), Tools::getValue('id_pfg'),
			(Tools::isSubmit('id_field') ? Tools::getValue('id_field') : null))) {
			$this->errors[] = $this->la('This name is already taken.');
		}

		$languages = Language::getLanguages(true);
		// if select || radio : values required

		if (in_array(Tools::getValue('type'), ['select', 'radio', 'checkbox', 'multicheckbox', 'hidden'])) {

			foreach ($languages as $language) {
				$value = Tools::getValue('values_' . $language['id_lang']);

				if (empty($value)) {
					$this->errors[] = sprintf($this->la('You must indicates at least one value for "%s".'), $language['name']);
				}

			}

		} else
		if (Tools::getValue('type') === 'file') {

			foreach ($languages as $language) {
				$value = Tools::getValue('values_' . $language['id_lang']);

				if (empty($value)) {
					$this->errors[] = sprintf($this->la('You must indicates at least one file extension (without the dot) for "%s".'), $language['name']);
				} else {
					$extensions = explode(',', $value);

					foreach ($extensions as $ext) {
						$ext = trim($ext);

						if (Tools::substr($ext, 0, 1) === '.') {
							$this->errors[] = $this->la('Values must contains valid file extensions (without the dot).');
						}

						break;
					}

				}

			}

		}

	}

}
