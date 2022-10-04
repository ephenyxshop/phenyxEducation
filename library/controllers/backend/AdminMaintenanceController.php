<?php

/**
 * Class AdminMaintenanceControllerCore
 *
 * @since 1.9.1.0
 */
class AdminMaintenanceControllerCore extends AdminController {

    /**
     * AdminMaintenanceControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->className = 'Maintenance';
        $this->table = 'maintenance';
		$this->publicName = $this->la('Paramètre de Maintenance');

        parent::__construct();

        $this->fields_options = [
            'general' => [
                'title'  => $this->la('General'),
                'fields' => [
                    'EPH_SHOP_ENABLE'      => [
                        'title'      => $this->la('Enable Shop'),
                        'desc'       => $this->la('Activate or deactivate your shop (It is a good idea to deactivate your shop while you perform maintenance. Please note that the webservice will not be disabled).'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'EPH_MAINTENANCE_IP'   => [
                        'title'      => $this->la('Maintenance IP'),
                        'hint'       => $this->la('IP addresses allowed to access the front office even if the shop is disabled. Please use a comma to separate them (e.g. 42.24.4.2,127.0.0.1,99.98.97.96)'),
                        'validation' => 'isGenericName',
                        'type'       => 'maintenance_ip',
                        'default'    => '',
                    ],
                    'EPH_MAINTENANCE_TEXT' => [
                        'title'      => $this->la('Custom maintenance text'),
                        'hint'       => $this->la('90 of 21844 characters allowed'),
                        'validation' => 'isCleanHtml',
                        'type'       => 'textareaLang',
                        'class'      => 'autoload_rte',
                        'lang'       => true,
                        'default'    => '',
                    ],
                ],
                'submit' => ['title' => $this->la('Save')],
            ],
        ];
		
		$this->ajaxOptions = $this->generateOptions();
    }

   
	
	public function setAjaxMedia() {
		
		return $this->pushJS([
			 '/js/tinymce/tinymce.min.js',
			 'js/tinymce.inc.js',
		]);
	}
	
	public function generateOptions() {
    	
      	if ($this->fields_options && is_array($this->fields_options)) {
						
            $helper = new HelperOptions();
            $this->setHelperDisplay($helper);
            $helper->toolbar_scroll = true;
            $helper->toolbar_btn = [
                'save' => [
                    'href' => '#',
                    'desc' => $this->la('Save'),
                ],
            ];
            $helper->id = $this->id;
            $helper->tpl_vars = $this->tpl_option_vars;
            $options = $helper->generateOptions($this->fields_options);

            return $options;
        }

        return '';
    }
	
	public function ajaxProcessUpdateConfigurationOptions() {
		
		$languages = Language::getLanguages(false);
        
        Configuration::updateValue('EPH_SHOP_ENABLE', Tools::getValue('EPH_SHOP_ENABLE'));
		Configuration::updateValue('EPH_MAINTENANCE_IP', Tools::getValue('EPH_MAINTENANCE_IP'));
        foreach ($languages as $lang) {
            $EPH_MAINTENANCE_TEXT[$lang['id_lang']] = Tools::getValue('EPH_MAINTENANCE_TEXT_'.$lang['id_lang']);
        }
        Configuration::updateValue('EPH_MAINTENANCE_TEXT', $EPH_MAINTENANCE_TEXT, true);
		
		$result = [
			"success" => true,
			"message" => "Les options de maintenances ont été mises à jour avec succès"
		];
		
		die(Tools::jsonEncode($result));
	}
}
