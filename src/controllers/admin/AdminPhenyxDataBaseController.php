<?php

/**
 * @property Gender $object
 */
class AdminPhenyxDataBaseControllerCore extends AdminController {

    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'phenyx_database';
        $this->className = 'PhenyxDataBase';
		$this->identifier = 'id_phenyx_database';
		$this->controller_name = 'AdminPhenyxDataBase';
		$this->publicName = $this->l('Gestion des bases de données');
		$this->context = Context::getContext();

		EmployeeConfiguration::updateValue('EXPERT_DATABASE_FIELDS', Tools::jsonEncode($this->getPhenyxDataBaseFields()));
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_DATABASE_FIELDS'), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_DATABASE_FIELDS', Tools::jsonEncode($this->getPhenyxDataBaseFields()));
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
		$("#shopSelector").selectmenu({
				width: 250,
    			"change": function(event, ui) {
        			gridPhenyxDataBase.filter({
            			mode: "AND",
                		rules: [
                			{ dataIndx:"shop", condition: "equal", value: ui.item.value}
                 		]
            		});
        		}
			});

        }';
		$paragrid->toolbar = [
			'items' => [

				[
					'type'     => '\'button\'',
					'label'    => '\'' . $this->l('Régénérer la base des Tables') . '\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'regenerateTables',
				],
				[
                    'type'     => '\'select\'',
                    'icon'     => '\'ui-icon-disk\'',
                     'attr'     => '\'id="shopSelector"\'',
					'options'	=>  '[
            			{"": "Les Tables Boutiques"},
						{"1": "Oui"},
						{"0": "Non"},
						]'
                ],    

			],
		];
		
		$paragrid->selectionModelType = 'row';
		$paragrid->showTitle = 1;
		$paragrid->title = '\'' . $this->l('Géstion des tables de la base de donnée') .  '\'';
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
                				editTable(rowData.id_phenyx_database);
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
								deleteTable(rowData.id_phenyx_database);


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

	public function getPhenyxDataBaseRequest() {

		$databases = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('phenyx_database')
				->orderBy('`name` ASC')
		);
		
		foreach ($databases as &$database) {

			
			
			if ($database['is_synch'] == 1) {
				$database['is_synch'] = '<div class="p-active" onClick="bddSynchOff('.$database['id_phenyx_database'].')"></div>';
			} else {
				$database['is_synch'] = '<div class="p-inactive" onClick="bddSynchOn('.$database['id_phenyx_database'].')"></div>';
			}
			
			if ($database['is_shop'] == 1) {
				$database['is_shop'] = '<div class="p-active" onClick="shopNonAccess('.$database['id_phenyx_database'].')"></div>';
				$database['shop'] = 1;
			} else {
				$database['is_shop'] = '<div class="p-inactive" onClick="shopAccess('.$database['id_phenyx_database'].')"></div>';
				$database['shop'] = 0;
			}
			
			if ($database['is_education'] == 1) {
				$database['is_education'] = '<div class="p-active" onClick="educationNonAccess('.$database['id_phenyx_database'].')"></div>';
				$database['education'] = 1;
			} else {
				$database['is_education'] = '<div class="p-inactive" onClick="educationAccess('.$database['id_phenyx_database'].')"></div>';
				$database['education'] = 0;
			}
			if ($database['request_data'] == 1) {
				$database['request_data'] = '<div class="p-active" onClick="noNeedData('.$database['id_phenyx_database'].')"></div>';
			} else {
				$database['request_data'] = '<div class="p-inactive" onClick="needData('.$database['id_phenyx_database'].')"></div>';
			}
		}
		
		return $databases;

	}

	public function ajaxProcessgetPhenyxDataBaseRequest() {

		die(Tools::jsonEncode($this->getPhenyxDataBaseRequest()));

	}
	
	public function ajaxProcessBddSynchOn() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxDataBase($idDatabase);
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
		$base = new PhenyxDataBase($idDatabase);
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
		$base = new PhenyxDataBase($idDatabase);
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
		$base = new PhenyxDataBase($idDatabase);
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
		$base = new PhenyxDataBase($idDatabase);
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
		$base = new PhenyxDataBase($idDatabase);
		$base->is_education = 0;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est désormais exclue du domaine des Organisme de Formation',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessNeedData() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxDataBase($idDatabase);
		$base->request_data = 1;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table possède des données de bases',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessNoNeedData() {
		
		$idDatabase = Tools::getValue('idDatabase');
		$base = new PhenyxDataBase($idDatabase);
		$base->request_data = 0;
		$base->update();
		
		$result = [
			'success'   => true,
			'message' => 'Cette Table est purement dynamique',
		];

		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessDeleteTable() {
		
		$idTable = Tools::getValue('idTable');
		$base = new PhenyxDataBase($idTable);
		
		$sql = 'DROP TABLE IF EXISTS `'._DB_PREFIX_.$base->name.'`';
		if(Db::getInstance()->execute($sql)) {
			$base->delete();
			$result = [
				'success'   => true,
				'message' => 'La Table '.$base->name.' a été supprimée avec succès',
			];
		} else {
			$result = [
				'success'   => false,
				'message' => 'Un beug est apparu lors de la tenative de suppression de La Table '.$base->name,
			];
		}
		
		

		die(Tools::jsonEncode($result));
	}

	public function getPhenyxDataBaseFields() {

		return [
			[
				'title'      => $this->l('ID'),
				'dataIndx'   => 'id_phenyx_database',
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
				'title'    => $this->l('Nom de la Base de donnée'),
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
			[
				'title'    => $this->l('Possède des données de base'),
				'width'    => 50,
				'dataIndx' => 'request_data',
				'editable' => false,
				'align'    => 'center',
				'valign'   => 'center',
				'dataType' => 'html',

			],


		];

	}

	public function ajaxProcessgetPhenyxDataBaseFields() {

		$fields = EmployeeConfiguration::get('EXPERT_DATABASE_FIELDS');
		die($fields);
	}
	
	public function ajaxProcessRegenerateTables() {
		
		$tables = Db::getInstance()->executeS('SHOW TABLES');
		
		foreach ($tables as $table) {
			$tablename = str_replace(_DB_PREFIX_, '', $table['Tables_in_' . _DB_NAME_]);
			$base = PhenyxDataBase::getObjectByTableName($tablename);
			$table = current($table);
			$schema = Db::getInstance()->executeS('SHOW CREATE TABLE `' . $table . '`');
			$requete = str_replace('CREATE TABLE', 'CREATE TABLE IF NOT EXISTS', $schema[0]['Create Table']);
			$base->create_request = $requete;
			if($base->id > 0) {
				if($base->request_data) {
					$data = Db::getInstance()->query('SELECT * FROM `' . $schema[0]['Table'] . '`');
    				$sizeof = DB::getInstance()->NumRows();
    				$lines = explode("\n", $schema[0]['Create Table']);
					$insert = 'INSERT INTO `' . $schema[0]['Table'] . "` VALUES".PHP_EOL;
					$i = 1;
    				while ($row = DB::getInstance()->nextRow($data)) {
						$s = '(';
        				foreach ($row as $field => $value) {
							$tmp = "'" . pSQL($value, true) . "',";
            				if ($tmp != "'',") {
								$s .= $tmp;
            				} else {
								foreach ($lines as $line) {
									if (strpos($line, '`' . $field . '`') !== false) {
										if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
                        					$s .= "'',";
                        				} else {
                        					$s .= 'NULL,';
                        				}
                        				break;
                    				}
                				}
            				}
        				}
        				$s = rtrim($s, ',');
        				if ($i % 200 == 0 && $i < $sizeof) {
							$s .= ");\nINSERT INTO `" . $schema[0]['Table'] . "` VALUES\n";
        				} else if ($i < $sizeof) {
        					$s .= "),\n";
        				} else {
        					$s .= ");\n";
        				}
						$insert .= $s;
					}
					$base->base_data = $insert;
					
				}
				$base->update();
			} else {
				$base->name = $tablename;
				$base->add();
			}
		}
		
	}

	public function ajaxProcessEditTable() {
		
		$idTable = Tools::getValue('idTable');
		$_GET['id_phenyx_database'] = $idTable;
		$_GET['updatephenyx_database'] = "";
		
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
		$this->fields_value['action'] = 'updatePhenyxDataBase';


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
