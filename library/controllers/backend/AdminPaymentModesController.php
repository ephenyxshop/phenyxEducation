<?php

/**
 * @property PaymentMode $object
 */
class AdminPaymentModesControllerCore extends AdminController {

	public $php_self = 'adminpaymentmodes';
	
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'payment_mode';
        $this->className = 'PaymentMode';
        $this->publicName = $this->la('Payment Mode');
        $this->lang = true;

        $this->context = Context::getContext();

        parent::__construct();

        EmployeeConfiguration::updateValue('EXPERT_PAYMENTMODES_SCRIPT', $this->generateParaGridScript(true));
        $this->paragridScript = EmployeeConfiguration::get('EXPERT_PAYMENTMODES_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_PAYMENTMODES_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_PAYMENTMODES_SCRIPT');
        }

        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PAYMENTMODES_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_PAYMENTMODES_FIELDS', Tools::jsonEncode($this->getPaymentModeFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_PAYMENTMODES_FIELDS'), true);
        }

    }

    public function setMedia() {

        parent::setMedia();
        Media::addJsDef([
            'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
        ]);

    }

    public function ajaxProcessinitController() {

        return $this->initGridController();
    }

    public function initContent() {

        $this->displayGrid = true;
        $this->paramGridObj = 'obj' . $this->className;
        $this->paramGridVar = 'grid' . $this->className;
        $this->paramGridId = 'grid_' . $this->controller_name;
        $ajaxlink = $this->context->link->getAdminLink($this->controller_name);

        $this->TitleBar = $this->la('Payment Mode List');

        $this->context->smarty->assign([
            'controller'     => Tools::getValue('controller'),
            'tabScript'      => $this->generateTabScript(Tools::getValue('controller')),
            'gridId'         => 'grid_AdminPaymentModes',
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

    public function generateParaGridScript($regenerate = false) {

        

        $paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);

        $paragrid->height = 700;
        $paragrid->showNumberCell = 0;
        $paragrid->create = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
        $paragrid->rowInit = 'function (ui) {
            return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
        $paragrid->complete = 'function(){
        window.dispatchEvent(new Event(\'resize\'));
        }';
        $paragrid->selectionModelType = 'row';
		$paragrid->toolbar = [
            'items' => [  
				[
					'type'     => '\'button\'',
					'label'    => '\'Ajouter un Client\'',
					'cls'      => '\'ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only\'',
					'listener' => 'addNewPaymentMode',
				],				         
            ],
        ];
        $paragrid->showTitle = 1;
        $paragrid->title = '\'' . $this->la('Géstion des modes de payment') .  '\'';
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
                            name : \'' . $this->la('Modifier ') . '\'' . '+rowData.name,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                             	editPaymentMode(rowData.id_payment_mode);
                            }
                        },


                        "sep2": "---------",
                        "delete": {
                            name: \'' . $this->la('Supprimer ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "delete",
                            visible: function(key, opt) {
                                if (selected == 1) {
                                    return true;
                                }
                            return false;
                            },
                            callback: function(itemKey, opt, e) {
                                deletePaymentMode(rowData.id_payment_mode);
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

    public function generateParaGridOption() {

        return '';

    }

    public function getPaymentModeRequest() {

        $paymentmodes = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('b.*, a.*, bdl.`name` as `diaryName`, bdl.`code` as `diaryCode`, ba.`code` as `bankCode`, ba.`bank_name`')
                ->from('payment_mode', 'a')
                ->leftJoin('payment_mode_lang', 'b', 'b.`id_payment_mode` = a.`id_payment_mode` AND b.`id_lang`  = ' . (int) $this->context->language->id)
                ->leftJoin('payment_type_lang', 'ptl', 'ptl.`id_payment_type` = a.`id_payment_type` AND ptl.`id_lang`  = ' . (int) $this->context->language->id)
                ->leftJoin('book_diary_lang', 'bdl', 'bdl.`id_book_diary` = a.`id_book_diary` AND bdl.`id_lang`  = ' . (int) $this->context->language->id)
                ->leftJoin('bank_account', 'ba', 'ba.`id_bank_account` = a.`id_bank_account`')
                ->orderBy('a.`id_payment_mode` ASC')
        );
        $paymentmodeLink = $this->context->link->getAdminLink($this->controller_name);

        foreach ($paymentmodes as &$paymentmode) {

            if ($paymentmode['id_module'] != 0) {
                $module = Module::getInstanceById((int) $paymentmode['id_module']);
                $paymentmode['module'] = $module->displayName;
            } else {
                $paymentmode['module'] = '--';
            }

            if ($paymentmode['active'] == 1) {
                $paymentmode['active'] = '<div class="toggle-active"></div>';
            } else {
                $paymentmode['active'] = '<div class="toggle-inactive"></div>';
            }

            $paymentmode['addLink'] = $paymentmodeLink . '&action=addObject&ajax=true&addpayment_mode';
            $paymentmode['openLink'] = $paymentmodeLink . '&id_payment_mode=' . $paymentmode['id_payment_mode'] . '&updatepayment_mode';

        }

        return $paymentmodes;

    }

    public function ajaxProcessgetPaymentModeRequest() {

        die(Tools::jsonEncode($this->getPaymentModeRequest()));

    }

    public function getPaymentModeFields() {

        return [
            [
                'title'      => $this->la('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_payment_mode',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'filter'     => [
                    'crules' => [['condition' => "begin"]],
                ],
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 50,
                'dataIndx'   => 'openLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'addLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->la('Code'),
                'width'    => 100,
                'exWidth'  => 40,
                'dataIndx' => 'code',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Name'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'name',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Bank Name'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'bank_name',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Diary Code'),
                'width'    => 120,
                'exWidth'  => 40,
                'dataIndx' => 'diaryCode',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Diary Name'),
                'width'    => 120,
                'exWidth'  => 40,
                'dataIndx' => 'diaryName',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Module'),
                'width'    => 150,
                'dataIndx' => 'module',
                'dataType' => 'string',
                'align'    => 'left',
                'editable' => false,
                'hidden'   => false,

            ],
            [
                'title'    => $this->la('Status'),
                'minWidth' => 100,
                'dataIndx' => 'active',
                'align'    => 'center',
                'valign'   => 'center',
                'dataType' => 'html',

            ],

        ];

    }

    public function ajaxProcessgetPaymentModeFields() {

        die(EmployeeConfiguration::get('EXPERT_PAYMENTMODES_FIELDS'));
    }

    public function ajaxProcessAddNewPaymentMode() {
		
		$_GET['addpayment_mode'] = "";
		
		
		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
		
	}
	
	public function ajaxProcessEditPaymentMode() {
		
		$idPaymentMode = Tools::getValue('idPaymentMode');
		$_GET['id_payment_mode'] = $idPaymentMode;
		$_GET['updatecms'] = "";
		
				
		$html = $this->renderForm();
		$result = [
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
	}

    public function renderForm() {

        
		if (!$this->loadObject(true)) {
            return '';
        }
		
		
        $paymentModules = [];
        $paymentModules[] = [
            'id_module' => 0,
            'name'      => $this->la('Sélectionner le module'),
        ];

        foreach (PaymentModule::getInstalledPaymentModules() as $pModule) {
            $module = Module::getInstanceById((int) $pModule['id_module']);
            $paymentModules[] = [
                'id_module' => $pModule['id_module'],
                'name'      => $module->displayName,
            ];
        }

        $paymentType = [];

        foreach (PaymentType::getPaymentTypes() as $type) {
            $paymentType[] = [
                'id_payment_type' => $type->id,
                'name'            => $type->name,
            ];
        }

        $bankaccount = [];

        foreach (BankAccount::getBankAccounts() as $bank) {
            $bankaccount[] = [
                'id_bank_account' => $bank->id,
                'name'            => $bank->bank_name,
            ];
        }

        $bookdiary = [];
        $bookdiary[] = [
            'id_book_diary' => 0,
            'name'          => $this->la('Seélectionner le code Journal'),
        ];

        foreach (BookDiary::getBookDiary() as $diary) {
            $bookdiary[] = [
                'id_book_diary' => $diary->id,
                'name'          => $diary->code,
            ];
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Mode de payment'),
                'icon'  => 'icon-money',
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
                    'label'    => $this->la('Nom du Mode de Payment'),
                    'name'     => 'name',
                    'col'      => 3,
                    'lang'     => true,
                    'required' => true,
                    'hint'     => $this->la('Invalid characters:') . ' <>;=#{}',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Code du mode'),
                    'name'     => 'code',
                    'maxchar'  => 8,
                    'col'      => 1,
                    'required' => true,
                    'hint'     => $this->la('4 chars max.'),
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Banque associée'),
                    'name'    => 'id_bank_account',
                    'col'     => 6,
                    'options' => [
                        'query' => $bankaccount,
                        'id'    => 'id_bank_account',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Journal'),
                    'name'    => 'id_book_diary',
                    'col'     => 6,
                    'options' => [
                        'query' => $bookdiary,
                        'id'    => 'id_book_diary',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Type de paiement'),
                    'name'    => 'id_payment_type',
                    'col'     => 6,
                    'options' => [
                        'query' => $paymentType,
                        'id'    => 'id_payment_type',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->la('Module associé'),
                    'name'    => 'id_module',
                    'col'     => 6,
                    'options' => [
                        'query' => $paymentModules,
                        'id'    => 'id_module',
                        'name'  => 'name',
                    ],
                    'hint'    => $this->la('This optionaly relay on existing payment module.'),
                ],
                [
                    'type'          => 'switch',
                    'label'         => $this->la('Etat'),
                    'name'          => 'active',
                    'required'      => false,
                    'is_bool'       => true,
                    'default_value' => 1,
                    'values'        => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],

        ];
		
		$this->fields_value['ajax'] = 1;
		if($this->object->id > 0) {
			$this->fields_value['action'] = 'updatePaymentMode';
		} else {
			$this->fields_value['action'] = 'addPaymentMode';
		}

        return parent::renderForm();
    }
	
	public function ajaxProcessAddPaymentMode() {
		
		$objet = new PaymentMode();
		
		foreach ($_POST as $key => $value) {
			if (property_exists($objet, $key) && $key != 'id_payment_mode') {
             	$objet->{$key}  = $value;
            }
        }
		 $classVars = get_class_vars(get_class($objet));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($objet->{$field}) || !is_array($objet->{$field})) {
                        	$objet->{$field} = [];
                        }
                        $objet->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		
		
        $result = $objet->add();
		
		$return = [
        	'success' => true,
            'message' => $this->la('Le mode de Payment a été ajouté avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
		
	}
	
	public function ajaxProcessUpdatePaymentMode() {
		
		$idPaymentMode = Tools::getValue('id_payment_mode');
		
		$objet = new PaymentMode($idPaymentMode);
		
		foreach ($_POST as $key => $value) {
			if (property_exists($objet, $key) && $key != 'id_payment_mode') {
             	$objet->{$key}  = $value;
            }
        }
		 $classVars = get_class_vars(get_class($objet));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

        	if (array_key_exists('lang', $params) && $params['lang']) {
				foreach (Language::getIDs(false) as $idLang) {
					if (Tools::isSubmit($field . '_' . (int) $idLang)) {
						if (!isset($objet->{$field}) || !is_array($objet->{$field})) {
                        	$objet->{$field} = [];
                        }
                        $objet->{$field}[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }
                }
            }
        }
		
		
        $result = $objet->update();
		
		$return = [
        	'success' => true,
            'message' => $this->la('Le mode de Payment a été mis à jour avec succès'),
        ];
		
		die(Tools::jsonEncode($return));
		
	}

}
