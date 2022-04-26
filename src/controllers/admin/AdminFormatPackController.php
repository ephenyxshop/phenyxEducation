<?php

/**
 * Class AdminCmsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminFormatPackControllerCore extends AdminController {

   
    /**
     * AdminFormatPackControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'formatpack';
        $this->className = 'FormatPack';
		$this->publicName = $this->l('Géstion des Format Pack');
        $this->lang = true;
       
        $this->context = Context::getContext();

		parent::__construct();

		//EmployeeConfiguration::updateValue('EXPERT_FORMATPACK_FIELDS', Tools::jsonEncode($this->getFormatPackFields()), $this->context->employee->id);
		$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_FORMATPACK_FIELDS', $this->context->employee->id), true);

		if (empty($this->configurationField)) {
			EmployeeConfiguration::updateValue('EXPERT_FORMATPACK_FIELDS', Tools::jsonEncode($this->getFormatPackFields()), $this->context->employee->id);
			$this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_FORMATPACK_FIELDS'), true);
		}

		EmployeeConfiguration::updateValue('EXPERT_FORMATPACK_SCRIPT', $this->generateParaGridScript(true), $this->context->employee->id);
		$this->paragridScript = EmployeeConfiguration::get('EXPERT_FORMATPACK_SCRIPT');

		if (empty($this->paragridScript)) {
			EmployeeConfiguration::updateValue('EXPERT_FORMATPACK_SCRIPT', $this->generateParaGridScript(true), $this->context->employee->id);
			$this->paragridScript = EmployeeConfiguration::get('EXPERT_FORMATPACK_SCRIPT');
		}
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

		
		$this->context->smarty->assign([
			'manageHeaderFields' => false,
			'controller'         => Tools::getValue('controller'),
			'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
			'gridId'             => 'grid_' . $this->controller_name,
			'tableName'          => $this->table,
			'className'          => $this->className,
			'linkController'     => $this->context->link->getAdminLink($this->controller_name),
			'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
			'paragridScript'     => $this->generateParaGridScript(),
			'bo_imgdir'          => '/themes/' . $this->bo_theme . '/img/',
		]);

		parent::initContent();
	}

	public function generateParaGridScript($regenerate = false) {

		$context = Context::getContext();
		$this->controller_name = 'AdminFormatPack';
		$controllerLink = $context->link->getAdminLink($this->controller_name);

		$sessions = EducationSession::getEducationSession();
		$sessionSelector = '<div class="pq-theme"><select id="sessionSelect"><option value="">' . $this->l('--Select--') . '</option>';

		foreach (EducationSession::getEducationSession() as $session) {
			$sessionSelector .= '<option value="' . $session['id_education_session'] . '">' . $session['name'] . '</option>';
		}

		$sessionSelector .= '</select></div>';

		$lenghtSelector = '<div class="pq-theme"><select id="lenghtSelect"><option value="">' . $this->l('--Select--') . '</option>';
		$lenghtSelector .= '<option value="0">Aucune durée</option>';
		$lenghtSelector .= '<option value="1">Durée positive</option>';
		$lenghtSelector .= '</select></div>';

		

		
		$this->paramPageModel = [
			'type'       => '\'local\'',
			'rPP'        => 100,
			'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
		];

		
		$this->paramChange = 'function(evt, ui) {
            var grid = this;
            var updateData = ui.updateList[0];
            var newRow = updateData.newRow;
            var dataField = Object.keys(newRow)[0].toString();
            var dataValue = newRow[dataField];
            var dataEducation = updateData.rowData.id_formatpack;
            $.ajax({
                type: \'POST\',
                url: AjaxLinkAdminFormatPacks,
                data: {
                    action: \'updateByVal\',
                    idEducation: dataEducation,
                    field: dataField,
                    fieldValue: dataValue,
                    ajax: true
                },
                async: true,
                dataType: \'json\',
                success: function(data) {
                    if (data.success) {
                        showSuccessMessage(data.message);
                     } else {
                        showErrorMessage(data.message);
                    }
                }
            })
        }';
		
        $this->paramToolbar = [
            'items' => [
                 
               
                [
                    'type'     => '\'button\'',
                    'icon'     => '\'ui-icon-disk\'',
                    'label'    => '\'' . $this->l('Ajouter un nouveau Format Pack') . '\'',
                    'cls'      => '\'buttonCleanProductPosition changes ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
                    'listener' => 'function () {' . PHP_EOL . '
                    	addAjaxObject("' . $this->controller_name.'");' . PHP_EOL . '
                    }' . PHP_EOL
                ],
				
				
            ],
        ];
		
		$this->windowHeight = '300';
		$this->paramTitle = '\'' . $this->l('Géstion des Format Pack') .  '\'';
		$this->rowInit = 'function (ui) {
			return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
		

		$this->paramContextMenu = [
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
                            name : \'' . $this->l('Modifier la session de formation de  ') . '\'' . '+rowData.name,
                            icon: "edit",
							visible: function(key, opt){
                               var selected = selgrid' . $this->className . '.getSelection().length;
                                if(selected > 1) {
                                    return false;
                                }
                                return true;
                            },
                            callback: function(itemKey, opt, e) {
                             	
								editAjaxObject("' . $this->controller_name.'", rowData.id_formatpack)
                            }
                        },
						"delete": {
                            name : \'' . $this->l('Supprimer la formation de  ') . '\'' . '+rowData.firstname+" "+rowData.lastname,
                            icon: "delete",
							callback: function(itemKey, opt, e) {
                             	deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer un format Pack", "Etes vous sure de vouloir supprimer ce Format Pack ?", "Oui", "Annuler",rowData.id_formatpack);
                            }
                        },
                    },
				}
            }',
		]];

		return parent::generateParaGridScript();
	}
	
	public function getFormatPackRequest() {
		
		$formatPack = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
			->select('a.id_formatpack, a.price, a.`id_tax_rules_group`, a.`reference`, b.`name`, tl.`name` as `Tax`, t.rate, case when a.`active` =1 then \'<div class="p-active"></div>\' else \'<div class="p-inactive"></div>\' end as `active`')
			->from('formatpack', 'a')
			->leftJoin('formatpack_lang', 'b', 'b.`id_formatpack` = a.`id_formatpack` AND b.`id_lang` = ' . $this->context->language->id)
			->leftJoin('tax_rules_group', 'tl', 'tl.`id_tax_rules_group` = a.`id_tax_rules_group`')
            ->leftJoin('tax', 't', 't.`id_tax` = tl.`id_tax_rules_group`')
		);
		
		foreach ($formatPack as &$product) {
          			
            $product['FinalPrice'] = $product['price'] * (1 + $product['rate'] / 100);

        }

		return $formatPack;

	}
	
	public function ajaxProcessgetFormatPackRequest() {

		die(Tools::jsonEncode($this->getFormatPackRequest()));

	}

	public function getFormatPackFields() {

		return [

           
            [
                'title'     => $this->l('ID'),
                'maxWidth'  => 70,
                'dataIndx'  => 'id_formatpack',
                'dataType'  => 'integer',
                'editable'  => false,
                'align'     => 'center',
                'valign'    => 'center',
                'filter'    => [
                    'crules' => [['condition' => "begin"]],
                ],
            ],
            
            [
                'title'     => $this->l('Réference'),
                'minWidth'  => 100,
                'exWidth'   => 30,
                'dataIndx'  => 'reference',
                'editable'  => false,
                'updatable' => false,
                'align'     => 'left',
                'halign'    => 'HORIZONTAL_LEFT',
                'valign'    => 'center',
                'dataType'  => 'string',
                'filter'    => [
                    'crules' => [['condition' => "contain"]],
                ],
            ],
            [
                'title'     => $this->l('Nom'),
                'minWidth'  => 200,
                'exWidth'   => 65,
                'dataIndx'  => 'name',
                'dataType'  => 'string',
                'align'     => 'left',
                'valign'    => 'center',
                'editable'  => true,

                'filter'    => [
                    'crules' => [['condition' => "contain"]],
                ],
            ],           
            [
                'title'        => $this->l('Tarif HT'),
                'dataIndx'     => 'price',
                'align'        => 'right',
                'valign'       => 'center',
                'dataType'     => 'float',
                'format'       => "#.###,00 € " . $this->l('HT.'),
            ],
           
			[

                'dataIndx'   => 'id_tax_rules_group',
                'dataType'   => 'integer',
                'editable'   => false,
                'hidden'     => true,
                'hiddenable' => 'no',
                'filter'     => [

                    'crules' => [['condition' => "equal"]],

                ],

            ],
            [
                'title'     => $this->l('Taux de TVA'),
                'minWidth'  => 100,
                'dataIndx'  => 'Tax',
                'align'     => 'right',
                'valign'    => 'center',
                'dataType'  => 'string',
                'editable'  => false,
				

            ],
            [
                'title'        => $this->l('Tarif TTC'),
                'dataIndx'     => 'FinalPrice',
                'align'        => 'right',
                'valign'       => 'center',
                'dataType'     => 'float',
                'editable'     => false,
                'format'       => "#.###,00 € " . $this->l('TTC.'),
                'updatable'    => false,
            ],
          
            [

                'dataIndx'   => 'enable',
                'dataType'   => 'integer',
                'align'      => 'center',
                'valign'     => 'center',
                'hidden'     => true,
                'hiddenable' => 'no',
                

            ],
            [
                'title'    => $this->l('Actif'),
                'minWidth' => 100,
                'dataIndx' => 'active',
                'align'    => 'center',
                'valign'   => 'center',
                'dataType' => 'html',
                'editable' => false,
                

            ],
			
           

        ];

	}
	
	public function ajaxProcessgetFormatPackFields() {

		$fields = EmployeeConfiguration::get('EXPERT_FORMATPACK_FIELDS', $this->context->employee->id);
		die($fields);
	}

    public function renderForm() {

        if (!$this->loadObject(true)) {
            return '';
        }
		
        $this->displayGrid = false;
		$address = new Address();
        $address->id_country = (int) $this->context->country->id;
		$taxRulesGroups = TaxRulesGroup::getTaxRulesGroups(true);
        $taxRates = [
            0 => [
                'id_tax_rules_group' => 0,
                'rates'              => [0],
                'computation_method' => 0,
            ],
        ];

        foreach ($taxRulesGroups as $taxRulesGroup) {
            $idTaxRulesGroup = (int) $taxRulesGroup['id_tax_rules_group'];
            $taxCalculator = TaxManagerFactory::getManager($address, $idTaxRulesGroup)->getTaxCalculator();
            $taxRates[$idTaxRulesGroup] = [
                'id_tax_rules_group' => $idTaxRulesGroup,
                'rates'              => [],
                'computation_method' => (int) $taxCalculator->computation_method,
            ];

            if (isset($taxCalculator->taxes) && count($taxCalculator->taxes)) {

                foreach ($taxCalculator->taxes as $tax) {
                    $taxRates[$idTaxRulesGroup]['rates'][] = (float) $tax->rate;
                }

            } else {
                $taxRates[$idTaxRulesGroup]['rates'][] = 0;
            }

        }
		
		       
        $this->fields_form = [
            'tinymce' => true,
            'legend'  => [
                'title' => $this->l('Format Pack'),
                'icon'  => 'icon-folder-close',
            ],
            'input'   => [
                // custom template
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
                    'label'    => $this->l('Référence'),
                    'name'     => 'reference',
                    'required' => true,
                ],
                
                [
                    'type'     => 'text',
                    'label'    => $this->l('Nom'),
                    'name'     => 'name',
                    'lang'     => true,
                    'required' => true,
                ],
				[
                    'type'     => 'text',
                    'label'    => $this->l('Tarif'),
                    'name'     => 'price',
                    'required' => true,
                ],
				 [
                    'type'    => 'select',
                    'label'   => $this->l('Taux de TVA'),
                    'name'    => 'id_tax_rules_group',
					 'default_value' => 1,
					'options'       => [
                        'query' => $taxRulesGroups,
                        'id'    => 'id_tax_rules_group',
                        'name'  => 'name',
                    ],
                    
                ],
				[
                    'type'     => 'text',
                    'label'    => $this->l('Coût de reviens'),
                    'name'     => 'wholesale_price',
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
                    'label'        => $this->l('Description'),
                    'name'         => 'description',
                    'autoload_rte' => true,
                    'lang'         => true,
                    'hint'         => $this->l('Invalid characters:') . ' <>;=#{}',
                ],
                
            ],
            'submit'  => [
                'title' => $this->l('Save'),
            ],
            
        ];

        
		$this->fields_value['ajax'] = 1;
		if($this->object->id > 0) {
			$this->fields_value['action'] = 'updateFormatPack';
			$this->editObject = 'Edition d‘un Format Pack';
		} else {
			$this->fields_value['action'] = 'addPageFormatPack';
			$this->editObject = 'Ajouter un nouveau FormatPack page CMS';
		}

        $this->tpl_form_vars = [
            'active' => $this->object->active,
            'PS_ALLOW_ACCENTED_CHARS_URL', (int) Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL'),
        ];

        return parent::renderForm();
    }
	
	public function ajaxProcessAddPageFormatPack() {
		
		$formatPack = new FormatPack();
		$file = fopen("testAddPageFormatPack.txt","w");

		foreach ($_POST as $key => $value) {

			if (property_exists($formatPack, $key) && $key != 'id_formatpack') {
				fwrite($file,$key.' '.$value.PHP_EOL);
				$formatPack->{$key}

				= $value;
			}

		}
		
		$classVars = get_class_vars(get_class($formatPack));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($formatPack->{$field}) || !is_array($formatPack->{$field})) {
							$formatPack->{$field}
							= [];
						}

						$formatPack->{$field}
						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}
		fwrite($file, print_r($formatPack, true));
		$result = $formatPack->add();
		
		if ($result) {
			$return = [
				'success' => true,
				'message' => $this->l('Le Format Pack a été ajoutée à jour avec succès'),
			];
		} else {
			$return = [
				'success' => false,
				'message' => $this->l('L‘ajout du Format Pack à échouée'),
			];
		}

		die(Tools::jsonEncode($return));
	}
	
	public function ajaxProcessUpdateFormatPack() {
		
		$idFormatPack = Tools::getValue('id_formatpack');
		$formatPack = new FormatPack($idFormatPack);

		foreach ($_POST as $key => $value) {

			if (property_exists($formatPack, $key) && $key != 'id_formatpack') {

				$formatPack->{$key}

				= $value;
			}

		}
		
		$classVars = get_class_vars(get_class($formatPack));
		$fields = [];

		if (isset($classVars['definition']['fields'])) {
			$fields = $classVars['definition']['fields'];
		}

		foreach ($fields as $field => $params) {

			if (array_key_exists('lang', $params) && $params['lang']) {

				foreach (Language::getIDs(false) as $idLang) {

					if (Tools::isSubmit($field . '_' . (int) $idLang)) {

						if (!isset($formatPack->{$field}) || !is_array($formatPack->{$field})) {
							$formatPack->{$field}
							= [];
						}

						$formatPack->{$field}
						[(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
					}

				}

			}

		}
		
		$result = $formatPack->update();
		
		if ($result) {
			$return = [
				'success' => true,
				'message' => $this->l('Le Format Pack a été mis à jour avec succès'),
			];
		} else {
			$return = [
				'success' => false,
				'message' => $this->l('La mis à jour du Format Pack à échouée'),
			];
		}

		die(Tools::jsonEncode($return));
		
	}

   
    

   
   

}
