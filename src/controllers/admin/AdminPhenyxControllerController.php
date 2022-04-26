<?php

/**
 * @property Gender $object
 */
class AdminPhenyxControllerControllerCore extends AdminController {

    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'phenyx_controller';
        $this->className = 'PhenyxController';
		$this->identifier = 'id_phenyx_controller';
		$this->controller_name = 'AdminPhenyxController';
		$this->publicName = $this->l('Gestion des Controlleurs');
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT_DATABASE_FIELDS', Tools::jsonEncode($this->getPhenyxControllerFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_DATABASE_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_DATABASE_FIELDS', Tools::jsonEncode($this->getPhenyxControllerFields()));
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_DATABASE_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_DATABASE_SCRIPT', $this->generateParaGridScript(true));
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_DATABASE_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_DATABASE_SCRIPT', $this->generateParaGridScript(true));
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_DATABASE_SCRIPT');
		}

		parent::__construct();
		
		
    }
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			$this->admin_webpath . '/js/ace/ace.js',
			$this->admin_webpath . '/js/ace/ext-language_tools.js',
			$this->admin_webpath . '/js/ace/snippets/mysql.js',
		]);
	}
	
	public function initContent() {

		$this->displayGrid = true;
		$this->paramGridObj = 'obj' . $this->className;
		$this->paramGridVar = 'grid' . $this->className;
		$this->paramGridId = 'grid_' . $this->controller_name;

		$this->TitleBar = $this->l('Liste des Tables de la base');

		$this->context->smarty->assign([
			'controller'     => Tools::getValue('controller'),
			'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'         => 'grid_'.$this->controller_name,
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
		$("#typeSelector").selectmenu({
				width: 250,
    			"change": function(event, ui) {
        			gridPhenyxController.filter({
            			mode: "AND",
                		rules: [
                			{ dataIndx:"type", condition: "equal", value: ui.item.value}
                 		]
            		});
        		}
			});

        }';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Régénérer les Controllers') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'regenerateControllers',
				],
				[
                    'type'     => '\'select\'',
                    'icon'     => '\'ui-icon-disk\'',
                     'attr'     => '\'id="typeSelector"\'',
					'options'	=>  '[
            			{"": "Sélectionner le type"},
						{"front": "Front Office"},
						{"back": "Back Office"},
						]'
                ],    

			],
		];
		
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Géstion des Controllers') .  '\'';
		$paragrid->fillHandle = '\'all\'';
		
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
		
		$paragrid->contextMenu = [
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
						
                		"edit": {
							name: \'' . $this->l('Editer la table : ') . '\'+rowData.name,
							icon: "edit",
                			callback: function(itemKey, opt, e) {
                				editController(rowData.id_phenyx_controller);
                			}
						},
						
						"sep2": "---------",
           				"delete": {
           					name: \'' . $this->l('Supprimer la table :') . '\'+rowData.name,
           					icon: "delete",
							visible: function(key, opt){
								return !rowData.hasSubmenu;
                            },
           					callback: function(itemKey, opt, e) {
								deleteController(rowData.id_phenyx_controller);


							}
						}
       				},
				};
			}',
			]];
		
		
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

	public function getPhenyxControllerRequest() {

		$controllers = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('phenyx_controller')
				->orderBy('`name` ASC')
		);
		
		foreach ($controllers as &$controller) {

			if ($controller['type'] == 'front') {
				$controller['typeName'] = 'Front Office';
			} else {
				$controller['typeName'] = 'Back Office';
			}
			
			if ($controller['is_synch'] == 1) {
				$controller['is_synch'] = '<div class="p-active" onClick="bddSynchOff('.$controller['id_phenyx_controller'].')"></div>';
			} else {
				$controller['is_synch'] = '<div class="p-inactive" onClick="bddSynchOn('.$controller['id_phenyx_controller'].')"></div>';
			}
			
			if ($controller['is_shop'] == 1) {
				$controller['is_shop'] = '<div class="p-active" onClick="shopNonAccess('.$controller['id_phenyx_controller'].')"></div>';
				$controller['shop'] = 1;
			} else {
				$controller['is_shop'] = '<div class="p-inactive" onClick="shopAccess('.$controller['id_phenyx_controller'].')"></div>';
				$controller['shop'] = 0;
			}
			
			if ($controller['is_education'] == 1) {
				$controller['is_education'] = '<div class="p-active" onClick="educationNonAccess('.$controller['id_phenyx_controller'].')"></div>';
				$controller['education'] = 1;
			} else {
				$controller['is_education'] = '<div class="p-inactive" onClick="educationAccess('.$controller['id_phenyx_controller'].')"></div>';
				$controller['education'] = 0;
			}
			
		}
		
		return $controllers;

	}

	public function ajaxProcessgetPhenyxControllerRequest() {

		die(Tools::jsonEncode($this->getPhenyxControllerRequest()));

	}
	
	public function ajaxProcessBddSynchOn() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_synch = 1;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais synchronisable',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessBddSynchOff() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_synch = 0;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table n‘est désormais plus synchronisable',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessShopAccess() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_shop = 1;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais intégré au domaine e commerce',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessShopNonAccess() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_shop = 0;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais exclue du domaine e commerce',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessEducationAccess() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_education = 1;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais intégré au domaine Organisme de Formation',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessEducationNonAccess() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxController($idDatabase);
		$base->is_education = 0;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais exclue du domaine des Organisme de Formation',
		];

		die(Tools::jsonEncode($result));
	}
	
	
	
	public function ajaxProcessDeleteController() {
		
		$idController = Tools::getValue('idController');
		$controller = new PhenyxController($idController);
		if($controller->type == 'back') {
			$filePath = _PS_ROOT_DIR_.'/app/controllers/admin/'.$controller->name;
		} else {
			$filePath = _PS_ROOT_DIR_.'/app/controllers/front/'.$controller->name;
		}
		
		if(unlink($filePath)) {
			$controller->delete();
			$result = [
				'success'   => true,
				'message' => 'Le Controller '.$controller->name.' a été supprimée avec succès',
			];
		} else {
			$result = [
				'success'   => false,
				'message' => 'Un beug est apparu lors de la tenative de suppression du fichier '.$controller->name,
			];
		}
		
		

		die(Tools::jsonEncode($result));
	}

	public function getPhenyxControllerFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_phenyx_controller',
				'maxWidth'    => 70,
				'dataType'   => 'integer',
				'editable'   => false,
				'hiddenable' => 'no',
				'align'      => 'center',
				'filter'     => [
					'crules' => [['condition' => "begin"]],
				],
			],
			[
				'title'    => $this->l('Nom du Controller'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'name',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,
				'filter'   => [
					'crules' => [['condition' => "begin"]],
				],

			],
			[
				
				'dataIndx' => 'type',
				'align'    => 'left',
				'dataType' => 'string',
				'hidden' => true,

			],
			[
				'title'    => $this->l('Type de Controller'),
				'width'    => 100,
				'exWidth'  => 15,
				'dataIndx' => 'typeName',
				'align'    => 'left',
				'dataType' => 'string',
				'editable' => false,

			],
			[
				'title'    => $this->l('Synchrnonisable'),
				'width'    => 50,
				'dataIndx' => 'is_synch',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'html',

			],
			[
				
				'dataIndx' => 'shop',
				'dataType'   => 'integer',
				'hidden'     => true,
                'hiddenable' => 'no',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],

			],
			[
				'title'    => $this->l('Fonctionnalité e Shop'),
				'width'    => 50,
				'dataIndx' => 'is_shop',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'html',

			],
			[
				'title'    => $this->l('Fonctionnalité OF'),
				'width'    => 50,
				'dataIndx' => 'is_education',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'html',

			],
			


		];

	}

	public function ajaxProcessgetPhenyxControllerFields() {

		$fields = EmployeeConfiguration::get('EXPERT_DATABASE_FIELDS');
		die($fields);
	}
	
	public function ajaxProcessRegenerateControlleurs() {
		
		$iterator = new AppendIterator();

		$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(_PS_ROOT_DIR_ . '/app/controllers/front/')));
		
		foreach ($iterator as $file) {
	
			$filePath = $file->getFilename();
			$filePath = str_replace(_PS_ROOT_DIR_, '', $filePath);
			if (in_array($file->getFilename(), ['.', '..', 'index.php', '.htaccess', 'dwsync.xml'])) {
				continue;
			}
			$controlleur = PhenyxController::getObjectByClasseName($filePath);
			
			if($controller->id > 0) {
				continue;
			} else {
				$controller = new PhenyxController();
				$controller->name = $filePath;
				$controller->type = 'front';
				$controller->add();
			}			
		}
		
		$iterator = new AppendIterator();

		$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(_PS_ROOT_DIR_ . '/app/controllers/admin/')));
		
		foreach ($iterator as $file) {
	
			$filePath = $file->getFilename();
			$filePath = str_replace(_PS_ROOT_DIR_, '', $filePath);
			if (in_array($file->getFilename(), ['.', '..', 'index.php', '.htaccess', 'dwsync.xml'])) {
				continue;
			}
			$controlleur = PhenyxController::getObjectByClasseName($filePath);
			
			if($controller->id > 0) {
				continue;
			} else {
				$controller = new PhenyxController();
				$controller->name = $filePath;
				$controller->type = 'back';
				$controller->add();
			}			
		}

		
	}

	public function ajaxProcessEditTable() {
		
		$idTable = Tools::getValue('idTable');
		$_GET['id_phenyx_controller'] = $idTable;
		$_GET['updatephenyx_controller'] = "";
		
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
                'title' => $this->l('Table de la base de donnée'),
                'icon'  => 'icon-male',
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
                    'label'    => $this->l('Nom de la table'),
                    'name'     => 'name',
                    'required' => true,
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->l('Table synchronisable'),
                    'name'     => 'is_synch',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'is_synch_on',
                            'value' => 1,
                            'label' => $this->l('Oui'),
                        ],
                        [
                            'id'    => 'is_synch_off',
                            'value' => 0,
                            'label' => $this->l('Non'),
                        ],
                    ],
                ],
				[
                    'type'     => 'switch',
                    'label'    => $this->l('Table e commerce'),
                    'name'     => 'is_shop',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'is_shop_on',
                            'value' => 1,
                            'label' => $this->l('Oui'),
                        ],
                        [
                            'id'    => 'is_shop_off',
                            'value' => 0,
                            'label' => $this->l('Non'),
                        ],
                    ],
                ],
				[
                    'type'     => 'switch',
                    'label'    => $this->l('Table Organisme de Formation'),
                    'name'     => 'is_education',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'is_education_on',
                            'value' => 1,
                            'label' => $this->l('Oui'),
                        ],
                        [
                            'id'    => 'is_education_off',
                            'value' => 0,
                            'label' => $this->l('Non'),
                        ],
                    ],
                ],
				[
                    'type'     => 'code',
					'id' => 'request',
                    'label'    => $this->l('Requête de création'),
                    'name'     => 'create_request',
                    'required' => true,
                ],
				[
                    'type'     => 'switch',
                    'label'    => $this->l('Données par défaut'),
                    'name'     => 'request_data',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'request_data_on',
                            'value' => 1,
                            'label' => $this->l('Oui'),
                        ],
                        [
                            'id'    => 'request_data_off',
                            'value' => 0,
                            'label' => $this->l('Non'),
                        ],
                    ],
                ],
				[
                    'type'     => 'code',
                    'label'    => $this->l('Requête de création'),
                    'name'     => 'base_data',
                ],
            ],
            'submit' => [
                'title' => $this->l('Save'),
            ],
        ];

        /** @var Gender $obj */

        
		$this->fields_value['ajax'] = 1;
		$this->fields_value['action'] = 'updatePhenyxController';


        return parent::renderForm();
    }

    public function displayGenderType($value, $tr) {

        return $this->fields_list['type']['list'][$value];
    }

    protected function postImage($id) {

        if (isset($this->fieldImageSettings['name']) && isset($this->fieldImageSettings['dir'])) {

            if (!Validate::isInt(Tools::getValue('img_width')) || !Validate::isInt(Tools::getValue('img_height'))) {
                $this->errors[] = Tools::displayError('Width and height must be numeric values.');
            } else {

                if ((int) Tools::getValue('img_width') > 0 && (int) Tools::getValue('img_height') > 0) {
                    $width = (int) Tools::getValue('img_width');
                    $height = (int) Tools::getValue('img_height');
                } else {
                    $width = null;
                    $height = null;
                }

                return $this->uploadImage($id, $this->fieldImageSettings['name'], $this->fieldImageSettings['dir'] . '/', false, $width, $height);
            }

        }

        return !count($this->errors) ? true : false;
    }

    protected function afterImageUpload() {

        parent::afterImageUpload();

        if (($id_gender = (int) Tools::getValue('id_gender')) &&
            isset($_FILES) && count($_FILES) && file_exists(_PS_GENDERS_DIR_ . $id_gender . '.jpg')) {
            $current_file = _PS_TMP_IMG_DIR_ . 'gender_mini_' . $id_gender . '_' . $this->context->shop->id . '.jpg';

            if (file_exists($current_file)) {
                unlink($current_file);
            }

        }

        return true;
    }

}
