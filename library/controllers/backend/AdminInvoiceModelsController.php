<?php

/**
 * Class AdminInvoicesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminInvoiceModelsControllerCore extends AdminController
{
    public $php_self = 'admininvoicemodels';
	public $pieceFields = [];
	/**
     * AdminInvoicesControllerCore constructor.
     *
     * @since 1.8.1.0
     */
    public function __construct()
    {
        $this->bootstrap = true;
        $this->table = 'invoice_model';
		$this->className = 'InvoiceModel';
		$this->publicName = $this->la('Genérateur de document');
		$this->display = 'options';

        parent::__construct();

        $this->fields_options = [
            'general' => [
                'title'  => $this->la('Invoice options'),
                'fields' => [
                    'EPH_INVOICE'                 => [
                        'title' => $this->la('Enable invoices'),
                        'desc'  => $this->la('If enabled, your customers will receive an invoice for their purchase(s).'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_INVOICE_TAXES_BREAKDOWN' => [
                        'title' => $this->la('Enable tax breakdown'),
                        'desc'  => $this->la('Show a summary of tax rates when there are several taxes.'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                   
                    'EPH_INVOICE_USE_YEAR'        => [
                        'title' => $this->la('Add current year to invoice number'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_INVOICE_RESET'           => [
                        'title' => $this->la('Reset Invoice progressive number at beginning of the year'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_INVOICE_YEAR_POS'        => [
                        'title'      => $this->la('Position of the year number'),
                        'cast'       => 'intval',
                        'show'       => true,
                        'required'   => false,
                        'type'       => 'radio',
                        'validation' => 'isBool',
                        'choices'    => [
                            0 => $this->la('After the progressive number'),
                            1 => $this->la('Before the progressive number'),
                        ],
                    ],
                    
                    'EPH_INVOICE_LEGAL_FREE_TEXT' => [
                        'title' => $this->la('Legal free text'),
                        'desc'  => $this->la('Use this field to display additional text on your invoice, like specific legal information. It will appear below the payment methods summary.'),
                        'size'  => 50,
                        'type'  => 'textareaLang',
                    ],
                    'EPH_INVOICE_FREE_TEXT'       => [
                        'title' => $this->la('Footer text'),
                        'desc'  => $this->la('This text will appear at the bottom of the invoice, below your company details.'),
                        'size'  => 50,
                        'type'  => 'textLang',
                    ],
                    'EPH_INVOICE_MODEL'           => [
                        'title'      => $this->la('Invoice model'),
                        'desc'       => $this->la('Choose an invoice model.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $this->getInvoicesModels(),
                    ],
					 'EPH_STUDENT_MODEL'           => [
                        'title'      => $this->la('Modèle Facturation étudiant'),
                        'desc'       => $this->la('Choose an invoice model.'),
                        'type'       => 'select',
                        'identifier' => 'value',
                        'list'       => $this->getInvoicesModels(),
                    ],
					
                    'EPH_PDF_USE_CACHE'           => [
                        'title'      => $this->la('Use the disk as cache for PDF invoices'),
                        'desc'       => $this->la('Saves memory but slows down the PDF generation.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                ],
               
            ],
			
        ];
		
		$this->pieceFields = [
         	'product_reference'          => ['name' => $this->la('Référence'), 'format' => ''],
            'product_name'               => ['name' => $this->la('Libellé'), 'format' => '', 'required' => true],
            'product_quantity'           => ['name' => $this->la('Quantité'), 'talign' => 'center', 'align' => 'center', 'format' => ''],
            'reduction_percent'          => ['name' => $this->la('Réduction %'), 'align' => 'center', 'format' => ''],
            'reduction_amount_tax_incl'  => ['name' => $this->la('Montant de réduction HT'), 'align' => 'right', 'format' => 'monney'],
            'reduction_amount_tax_excl'  => ['name' => $this->la('Montant de réduction TTC'), 'align' => 'right', 'format' => 'monney'],
            'product_ean13'              => ['name' => $this->la('EAN13'), 'format' => ''],
            'product_upc'                => ['name' => $this->la('UPC'), 'format' => ''],
            'product_weight'             => ['name' => $this->la('Poids'), 'format' => ''],
            'tax_rate'                   => ['name' => $this->la('Taux de TVA'), 'align' => 'right', 'format' => 'percent'],
            'ecotax'                     => ['name' => $this->la('Eco participation'), 'align' => 'right', 'format' => 'monney'],
			'original_price_tax_excl'	 => ['name' => $this->la('Prix unitaire HT'), 'align' => 'right', 'format' => 'monney'],
			'original_price_tax_incl'	 => ['name' => $this->la('Prix unitaire TTC'), 'align' => 'right', 'format' => 'monney'],
            'unit_tax_incl'		         => ['name' => $this->la('Prix unitaire remisé HT'), 'align' => 'right', 'format' => 'monney'],
            'unit_tax_excl'              => ['name' => $this->la('Prix unitaire remisé TTC'), 'align' => 'right', 'format' => 'monney'],
            'total_tax_excl'             => ['name' => $this->la('Total HT'), 'align' => 'right', 'format' => 'monney'],
            'total_tax'                  => ['name' => $this->la('Total TVA'), 'align' => 'right', 'format' => 'monney'],
			'total_tax_incl'       		 => ['name' => $this->la('Total TTC'), 'align' => 'right', 'format' => 'monney'],
       
    	];
    }

	public function setAjaxMedia() {
		
		return $this->pushJS([
            '/js/colorpicker/jquery.colorpicker.js',
			'/js/colorpicker/i18n/jquery.ui.colorpicker-fr.js',
			'/js/colorpicker/swatches/jquery.ui.colorpicker-pantone.js',			
			'/js/colorpicker/swatches/jquery.ui.colorpicker-crayola.js',			
			'/js/colorpicker/swatches/jquery.ui.colorpicker-ral-classic.js',			
			'/js/colorpicker/swatches/jquery.ui.colorpicker-x11.js',			
			'/js/colorpicker/swatches/jquery.ui.colorpicker-copic.js',			
			'/js/colorpicker/swatches/jquery.ui.colorpicker-prismacolor.js',
			'/js/colorpicker/swatches/jquery.ui.colorpicker-isccnbs.js',
			'/js/colorpicker/swatches/jquery.ui.colorpicker-din6164.js',			
			'/js/colorpicker/parts/jquery.ui.colorpicker-rgbslider.js',
			'/js/colorpicker/parts/jquery.ui.colorpicker-memory.js',
			'/js/colorpicker/parts/jquery.ui.colorpicker-swatchesswitcher.js',			
			'/js/colorpicker/parsers/jquery.ui.colorpicker-cmyk-parser.js',
			'/js/colorpicker/parsers/jquery.ui.colorpicker-cmyk-percentage-parser.js',
		]);
	}
	
	
	public function ajaxProcessOpenTargetController() {
			
		$targetController = $this->targetController;				
		$data = $this->createTemplate($this->table.'.tpl');
		$this->setAjaxMedia();
		
		$extracss = $this->pushCSS([
             '/js/colorpicker/jquery.colorpicker.css',		
		]);
		
		$data->assign([
			'tabs'			   => $this->generateOptions(),			'invoiceModels'    => InvoiceModel::getInvoiceModels(),
			'metroColors'      => EmployeeMenu::getmetroTabColors(),
			'optionFields'     => $this->pieceFields,
			'defaultTemplates' => $this->pieceFields,
			'languages'        => Language::getLanguages(true),
			'defaultLang'      => $this->context->language->id,
			'controller'       => $this->controller_name,
			'tableName'        => $this->table,
			'className'        => $this->className,
			'link'             => $this->context->link,
			'extracss'         => $extracss,
			'extraJs'          => $this->push_js_files,
			
		]);
		
		$li = '<li id="uper'.$targetController.'" data-controller="AdminDashboard" data-self="'.$this->link_rewrite.'" data-name="'.$this->page_title.'"><a href="#content'.$targetController.'">'.$this->publicName.'</a><button type="button" class="close tabdetail" data-id="uper'.$targetController.'"><i class="icon icon-times-circle"></i></button></li>';
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

    

    
}
