<?php

/**
 * Class AdminInvoicesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminInvoicesControllerCore extends AdminController
{
    
	public $pieceFields = [];
	/**
     * AdminInvoicesControllerCore constructor.
     *
     * @since 1.8.1.0
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'invoice';
		$this->className = 'Configuration';
		$this->publicName = $this->l('Genérateur de document');
		$this->display = 'options';

        parent::__construct();

        $this->fields_options = [
            'general' => [
                'title'  => $this->l('Invoice options'),
                'fields' => [
                    'PS_INVOICE'                 => [
                        'title' => $this->l('Enable invoices'),
                        'desc'  => $this->l('If enabled, your customers will receive an invoice for their purchase(s).'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'PS_INVOICE_TAXES_BREAKDOWN' => [
                        'title' => $this->l('Enable tax breakdown'),
                        'desc'  => $this->l('Show a summary of tax rates when there are several taxes.'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                   
                    'PS_INVOICE_USE_YEAR'        => [
                        'title' => $this->l('Add current year to invoice number'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'PS_INVOICE_RESET'           => [
                        'title' => $this->l('Reset Invoice progressive number at beginning of the year'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'PS_INVOICE_YEAR_POS'        => [
                        'title'      => $this->l('Position of the year number'),
                        'cast'       => 'intval',
                        'show'       => true,
                        'required'   => false,
                        'type'       => 'radio',
                        'validation' => 'isBool',
                        'choices'    => [
                            0 => $this->l('After the progressive number'),
                            1 => $this->l('Before the progressive number'),
                        ],
                    ],
                    
                    'PS_INVOICE_LEGAL_FREE_TEXT' => [
                        'title' => $this->l('Legal free text'),
                        'desc'  => $this->l('Use this field to display additional text on your invoice, like specific legal information. It will appear below the payment methods summary.'),
                        'size'  => 50,
                        'type'  => 'textareaLang',
                    ],
                    'PS_INVOICE_FREE_TEXT'       => [
                        'title' => $this->l('Footer text'),
                        'desc'  => $this->l('This text will appear at the bottom of the invoice, below your company details.'),
                        'size'  => 50,
                        'type'  => 'textLang',
                    ],
                    'PS_INVOICE_MODEL'           => [
                        'title'      => $this->l('Invoice model'),
                        'desc'       => $this->l('Choose an invoice model.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $this->getInvoicesModels(),
                    ],
					 'PS_STUDENT_MODEL'           => [
                        'title'      => $this->l('Modèle Facturation étudiant'),
                        'desc'       => $this->l('Choose an invoice model.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $this->getInvoicesModels(),
                    ],
					
                    'PS_PDF_USE_CACHE'           => [
                        'title'      => $this->l('Use the disk as cache for PDF invoices'),
                        'desc'       => $this->l('Saves memory but slows down the PDF generation.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                ],
               
            ],
			
        ];
		
		$this->pieceFields = [
         	'product_reference'          => ['name' => $this->l('Référence'), 'format' => ''],
            'product_name'               => ['name' => $this->l('Libellé'), 'format' => '', 'required' => true],
            'product_quantity'           => ['name' => $this->l('Quantité'), 'talign' => 'center', 'align' => 'center', 'format' => ''],
            'reduction_percent'          => ['name' => $this->l('Réduction %'), 'align' => 'center', 'format' => ''],
            'reduction_amount_tax_incl'  => ['name' => $this->l('Montant de réduction HT'), 'align' => 'right', 'format' => 'monney'],
            'reduction_amount_tax_excl'  => ['name' => $this->l('Montant de réduction TTC'), 'align' => 'right', 'format' => 'monney'],
            'product_ean13'              => ['name' => $this->l('EAN13'), 'format' => ''],
            'product_upc'                => ['name' => $this->l('UPC'), 'format' => ''],
            'product_weight'             => ['name' => $this->l('Poids'), 'format' => ''],
            'tax_rate'                   => ['name' => $this->l('Taux de TVA'), 'align' => 'right', 'format' => 'percent'],
            'ecotax'                     => ['name' => $this->l('Eco participation'), 'align' => 'right', 'format' => 'monney'],
			'original_price_tax_excl'	 => ['name' => $this->l('Prix unitaire HT'), 'align' => 'right', 'format' => 'monney'],
			'original_price_tax_incl'	 => ['name' => $this->l('Prix unitaire TTC'), 'align' => 'right', 'format' => 'monney'],
            'unit_tax_incl'		         => ['name' => $this->l('Prix unitaire remisé HT'), 'align' => 'right', 'format' => 'monney'],
            'unit_tax_excl'              => ['name' => $this->l('Prix unitaire remisé TTC'), 'align' => 'right', 'format' => 'monney'],
            'total_tax_excl'             => ['name' => $this->l('Total HT'), 'align' => 'right', 'format' => 'monney'],
            'total_tax'                  => ['name' => $this->l('Total TVA'), 'align' => 'right', 'format' => 'monney'],
			'total_tax_incl'       		 => ['name' => $this->l('Total TTC'), 'align' => 'right', 'format' => 'monney'],
       
    	];
    }

	
	public function setMedia()
    {
        parent::setMedia();
		
        
    }
	
	public function ajaxProcessOpenTargetController() {
			
		$targetController = $this->targetController;				
		$data = $this->createTemplate('controllers/'.$this->table.'.tpl');
		
		$data->assign([
			'tabs'			   => $this->generateOptions(),
			'invoiceModels'    => InvoiceModel::getInvoiceModels(),
			'metroColors'      => EmployeeMenu::getmetroTabColors(),
			'optionFields'     => $this->pieceFields,
			'defaultTemplates' => $this->pieceFields,
			'languages'        => Language::getLanguages(true),
			'defaultLang'      => $this->context->language->id,
			'controller'       => $this->controller_name,
			'tableName'        => $this->table,
			'className'        => $this->className,
			'link'             => $this->context->link,
			
		]);
		
		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
		$html = '<div id="content'.$targetController.'" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

		$result = [
			'li'   => $li,
			'html' => $html,
		];

		die(Tools::jsonEncode($result));
		
	}
	
	public function generateOptions() {
    	      	
		
		$tabs = [];
		
		
		$helper = new HelperOptions();
        $this->setHelperDisplay($helper);      
        $helper->id = $this->id;
        $helper->tpl_vars = $this->tpl_option_vars;
        $options = $helper->generateOptions($this->fields_options);
			
		$tabs['general'] = [
			'key' => $this->fields_options['general']['title'],
			'content' => $options
		];
		
			
		
		
		return $tabs;
       
    }
    /**
     * Get invoice models
     *
     * @return array
     *
     * @since 1.8.1.0
     */
   public function getInvoicesModels() {
		
		$templates = new PhenyxShopCollection('InvoiceModel');
       
	
		$models = [];
		foreach($templates as $template) {
			$models[] = [
				'value' => $template->id,
                'name'  => ucfirst(strtolower(str_replace('_', ' ', str_replace('EPH_TEMPLATE_', '', $template->name)))),
			];
		}

        return $models;
    }
	
	public function ajaxProcessUpdateTemplateOptions() {
		
		Configuration::clearConfigurationCacheForTesting();
		foreach ($_POST as $key => $value) {		
			if($key == 'action' || $key == 'ajax') {				
				continue;
			}			
			Configuration::updateValue($key, $value);			
		}
		$result = [
			"success" => true,
			"message" => "Les options ont été mises à jour avec succès"
		];
		
		die(Tools::jsonEncode($result));		
	}
	
	protected function getInvoicesColors() {
        $metroColors = EmployeeMenu::getmetroTabColors();
        foreach (array_merge($templatesDefault, $templatesOverride) as $template) {
            $templateName = basename($template, '.tpl');
            $models[] = ['value' => $templateName, 'name' => $templateName];
        }

        return $models;
    }
	
	public function ajaxProcessNewTemplate() {
		
		
		$model = new InvoiceModel();
		$model->name = 'EPH_TEMPLATE_'.strtoupper(str_replace(' ', '_', Tools::getValue('template_name')));
		
		$templateFields = Tools::getValue('templateField');		
		$model->color = Tools::getValue('color');
		
		$field = [];
		foreach($templateFields as $key => $templateField) {
			$field[$templateField] = $this->pieceFields[$templateField];
		}
		$model->fields = Tools::jsonEncode($field);
		
		
		$model->add();
		
		$result = [
			"success" => true,
			"message" => "Le modèle de pièce a été ajouté avec succès"
		];
		
		die(Tools::jsonEncode($result));
	}
	
	public function ajaxProcessUpdateModel() {
		
		$id_invoice_model = Tools::getValue('id_invoice_model');
		$model = new InvoiceModel($id_invoice_model);
		
		$model->name = 'EPH_TEMPLATE_'.strtoupper(str_replace(' ', '_', Tools::getValue('template_name')));
		
		$templateFields = Tools::getValue('templateField');		
		$model->color = Tools::getValue('color');
		
		$field = [];
		foreach($templateFields as $key => $templateField) {
			$field[$templateField] = $this->pieceFields[$templateField];
		}
		
		$model->fields = Tools::jsonEncode($field);
		$model->update();
		
		$result = [
			"success" => true,
			"message" => "Le modèle de pièce a été mis à jour avec succès"
		];
		
		die(Tools::jsonEncode($result));
	}

    /**
     * Get invoice models from dir
     *
     * @param string $directory
     *
     * @return array|false
     *
     * @since 1.8.1.0
     */
    protected function getInvoicesModelsFromDir($directory)
    {
        $templates = false;

        if (is_dir($directory)) {
            $templates = glob($directory.'invoice-*.tpl');
        }

        if (!$templates) {
            $templates = [];
        }

        return $templates;
    }

    
    /**
     * Initialize page header toolbar
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initPageHeaderToolbar()
    {
        parent::initPageHeaderToolbar();
        unset($this->page_header_toolbar_btn['cancel']);
    }

   
    /**
     * Initialize toolbar title
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initToolbarTitle()
    {
        $this->toolbar_title = array_unique($this->breadcrumbs);
    }

    /**
     * Post processing
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function postProcess()
    {
        parent::postProcess();
    }

    /**
     * Before updating options
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function beforeUpdateOptions()
    {
        if ((int) Tools::getValue('PS_INVOICE_START_NUMBER') != 0 && (int) Tools::getValue('PS_INVOICE_START_NUMBER') <= Order::getLastInvoiceNumber()) {
            $this->errors[] = $this->l('Invalid invoice number.').Order::getLastInvoiceNumber().')';
        }
    }
}
