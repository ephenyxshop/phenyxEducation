<?php

/**
 * Class AdminPreferencesControllerCore
 *
 * @since 1.9.1.0
 */
class AdminPreferencesControllerCore extends AdminController {

	public $php_self = 'adminpreferences';
    /**
     * AdminPreferencesControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->className = 'Configuration';
        $this->table = 'preference';
		$this->publicName = $this->la('Paramètre généraux');

        // Prevent classes which extend AdminPreferences to load useless data

        if (get_class($this) == 'AdminPreferencesController') {
            $roundMode = [
                [
                    'value' => EPH_ROUND_HALF_UP,
                    'name'  => $this->la('Round up away from zero, when it is half way there (recommended)'),
                ],
                [
                    'value' => EPH_ROUND_HALF_DOWN,
                    'name'  => $this->la('Round down towards zero, when it is half way there'),
                ],
                [
                    'value' => EPH_ROUND_HALF_EVEN,
                    'name'  => $this->la('Round towards the next even value'),
                ],
                [
                    'value' => EPH_ROUND_HALF_ODD,
                    'name'  => $this->la('Round towards the next odd value'),
                ],
                [
                    'value' => EPH_ROUND_UP,
                    'name'  => $this->la('Round up to the nearest value'),
                ],
                [
                    'value' => EPH_ROUND_DOWN,
                    'name'  => $this->la('Round down to the nearest value'),
                ],
            ];
            $activities1 = [
                0  => $this->la('-- Please choose your main activity --'),
                2  => $this->la('Education'),
                3  => $this->la('Art and Culture'),
                4  => $this->la('Babies'),
                5  => $this->la('Beauty and Personal Care'),
                6  => $this->la('Cars'),
                7  => $this->la('Computer Hardware and Software'),
                8  => $this->la('Download'),
                9  => $this->la('Fashion and accessories'),
                10 => $this->la('Flowers, Gifts and Crafts'),
                11 => $this->la('Food and beverage'),
                12 => $this->la('HiFi, Photo and Video'),
                13 => $this->la('Home and Garden'),
                14 => $this->la('Home Appliances'),
                15 => $this->la('Jewelry'),
                1  => $this->la('Lingerie and Adult'),
                16 => $this->la('Mobile and Telecom'),
                17 => $this->la('Services'),
                18 => $this->la('Shoes and accessories'),
                19 => $this->la('Sport and Entertainment'),
                20 => $this->la('Travel'),
            ];
            $activities2 = [];

            $days = [
                0 => 'Dimanche',
                1 => 'Lundi',
                2 => 'Mardi',
                3 => 'Mercredi',
                4 => 'Jeudi',
                5 => 'Vendredi',
                6 => 'Samedi',
            ];
            $days2 = [];

            foreach ($days as $value => $name) {
                $days2[] = ['value' => $value, 'name' => $name];
            }

            foreach ($activities1 as $value => $name) {
                $activities2[] = ['value' => $value, 'name' => $name];
            }

            
			$fields = [
                'ajax' => [
                    'type'       => 'hidden',
                    'value'    => '1',
                ],
            ];
			
			
			$fields['EPH_SSL_ENABLED'] = [
                 'title'      => $this->la('Enable SSL'),
                    'desc'       => $this->la('If you own an SSL certificate for your shop\'s domain name, you can activate SSL encryption (https://) for customer account identification and order processing.'),
                    'hint'       => $this->la('If you want to enable SSL on all the pages of your shop, activate the "Enable on all the pages" option below.'),
                    'validation' => 'isBool',
                    'cast'       => 'intval',
                    'type'       => 'bool',
                    'default'    => '0',
            ];

            $fields['EPH_SSL_ENABLED_EVERYWHERE'] = [
                'title'      => $this->la('Enable SSL on all pages'),
                'desc'       => $this->la('When enabled, all the pages of your shop will be SSL-secured.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '0',
                'disabled'   => (Tools::getValue('EPH_SSL_ENABLED', Configuration::get('EPH_SSL_ENABLED'))) ? false : true,
            ];			
			$fields['EPH_PROGRAMME_COMBO_MODE'] = [
                'title'      => $this->la('Générer les programmes des déclinaisons en Combo'),
                'desc'       => $this->la('Si Activé, les programmes combos sont mixés au niveau des PDF.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '1',
            ];
			$fields['EPH_FULL_THEME_MANAGEMENT_MODE'] = [
                'title'      => $this->la('Activer les fonctionnalités de Thème avancée'),
                'desc'       => $this->la('Active les fonctionnalités avancé du thème Ephenyx.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '1',
            ];
            $fields = array_merge(
                $fields,
                [
                    'EPH_TOKEN_ENABLE'               => [
                        'title'      => $this->la('Increase front office security'),
                        'desc'       => $this->la('Enable or disable token in the Front Office to improve ephenyx\' security.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                    ],
                    'EPH_ALLOW_HTML_IFRAME'          => [
                        'title'      => $this->la('Allow iframes on HTML fields'),
                        'desc'       => $this->la('Allow iframes on text fields like product description. We recommend that you leave this option disabled.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                    ],
                    'EPH_USE_HTMLPURIFIER'           => [
                        'title'      => $this->la('Use HTMLPurifier Library'),
                        'desc'       => $this->la('Clean the HTML content on text fields. We recommend that you leave this option enabled.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                    ],
                    'EPH_PRICE_ROUND_MODE'           => [
                        'title'      => $this->la('Round mode'),
                        'desc'       => $this->la('You can choose among 6 different ways of rounding prices. "Round up away from zero ..." is the recommended behavior.'),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $roundMode,
                        'identifier' => 'value',
                    ],
                    'EPH_ROUND_TYPE'                 => [
                        'title'      => $this->la('Round type'),
                        'desc'       => $this->la('You can choose when to round prices: either on each item, each line or the total (of an invoice, for example).'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => [
                            [
                                'name' => $this->la('Round on each item'),
                                'id'   => CustomerPieces::ROUND_ITEM,
                            ],
                            [
                                'name' => $this->la('Round on each line'),
                                'id'   => CustomerPieces::ROUND_LINE,
                            ],
                            [
                                'name' => $this->la('Round on the total'),
                                'id'   => CustomerPieces::ROUND_TOTAL,
                            ],
                        ],
                        'identifier' => 'id',
                    ],
                    'EPH_PRICE_DISPLAY_PRECISION'    => [
                        'title'      => $this->la('Number of decimals'),
                        'desc'       => $this->la('Choose how many decimals you want to display'),
                        'validation' => 'isUnsignedInt',
                        'cast'       => 'intval',
                        'type'       => 'text',
                    ],
					'_EPHENYX_USE_BLOG_'         => [
                        'title' => $this->la('Souhaitez vous activer un blog'),
                        'validation' => 'isBool',
                		'cast'       => 'intval',
						'type'       => 'bool',
                		'default'    => '1',
                    ],
					'_COMPANY_QUALIOPI_NUMBER_'         => [
                        'title' => $this->la('Ncp Qualiopi'),
                        'desc'  => $this->la('Votre numéro Qualiopi'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'_COMPANY_QUALIOPI_DATE_'         => [
                        'title' => $this->la('Date d‘optention du Qualiopi'),
                        'cast'  => 'strval',
                        'type'  => 'datepicker',
                    ],
                    '_EPHENYX_THEME_VERSION_'         => [
                        'title' => $this->la('Version du Thème'),
                        'desc'  => $this->la('Dèrnière version du thème front office'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    '_EPHENYX_LICENSE_KEY_'         => [
                        'title' => $this->la('Ephenyx Shop licence Key'),
                        'desc'  => $this->la('Add the Ephenyx key to ensure update of your shop'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_MAEPH_API_KEY'       => [
                        'title' => $this->la('Google Maps API Key'),
                        'desc'  => $this->la('Add an API key to display Google Maps properly'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_CAPTCHA_API_KEY'    => [
                        'title' => $this->la('Google Captcha API Key'),
                        'desc'  => $this->la('Add an API key to display Captcha for form validation'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_CAPTCHA_SECRET_KEY' => [
                        'title' => $this->la('Google Captcha Secret Key'),
                        'desc'  => $this->la('Add the secret Google Captcha key'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_SENDINBLUE_API' => [
                        'title' => $this->la('Clé API Send inBlue'),
                        'desc'  => $this->la('Ajouter votre clé Send In Blue'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_SMS_TITLE' => [
                        'title' => $this->la('Entête SMS'),
                        'desc'  => $this->la('Maximum 11 Caractère'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_HANDICAP_REFERENT' => [
                        'title' => $this->la('Référent Handicap'),
                        'desc'  => $this->la('Ajouter le nom de votré référent Handicap'),
                        'cast'  => 'strval',
                        'type'  => 'text',
						'form_group_class' => 'hidden',
                    ],
					'EPH_ALLOW_VIDEO_TUTO'          => [
                        'title'      => $this->la('Utiliser vous une vidé tutoriel'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
						'form_group_class' => 'hidden',
                    ],
					'EPH_TUTO_VIDEO' => [
                        'title' => $this->la('GIF Tutoriel Video'),
                        'desc'  => $this->la('Ajouter votre tutoriel Video'),
                        'cast'  => 'strval',
                        'type'  => 'text',
						'form_group_class' => 'hidden',
                    ],
					'EPH_TUTO_VIDEO_LINK' => [
                        'title' => $this->la('Lien Tutoriel Video'),
                        'desc'  => $this->la('Ajouter votre tutoriel Video'),
                        'cast'  => 'strval',
                        'type'  => 'text',
						'form_group_class' => 'hidden',
                    ],


                    'EPH_SESSION_FEATURE_ACTIVE'     => [
                        'title'      => $this->la('Activer la gestion de session automatique'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'EPH_SESSION_DAY'                => [
                        'title'            => $this->la('Journée de départ de session'),
                        'validation'       => 'isInt',
                        'cast'             => 'intval',
                        'type'             => 'select',
                        'list'             => $days2,
                        'identifier'       => 'value',
                    ],
                    'EPH_SHOP_ACTIVITY'              => [
                        'title'      => $this->la('Main Shop Activity'),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $activities2,
                        'identifier' => 'value',
                    ],
                ]
            );
			

            // No HTTPS activation if you haven't already.

            if (!Tools::usingSecureMode() && !Configuration::get('EPH_SSL_ENABLED')) {
                $fields['EPH_SSL_ENABLED']['type'] = 'disabled';
                $fields['EPH_SSL_ENABLED']['disabled'] = '<a class="btn btn-link" href="https://' . Tools::getShopDomainSsl() . Tools::safeOutput($_SERVER['REQUEST_URI']) . '">' . $this->la('Please click here to check if your shop supports HTTPS.') . '</a>';
            }

            $this->fields_options = [
                'general' => [
                    'title'  => $this->la('General'),
                    'icon'   => 'icon-cogs',
                    'fields' => $fields,
                    'submit' => ['title' => $this->la('Save')],
                ],
            ];
        }

        parent::__construct();
		
		$this->ajaxOptions = $this->generateOptions();
    }

    public function setMedia() {

        parent::setMedia();

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
		
		foreach ($_POST as $key => $value) {
		
			if($key == 'action' || $key == 'ajax') {
				
				continue;
			}
			
			if($key == 'EPH_TUTO_VIDEO') {
				Configuration::updateValue($key, $value, true);
			} else {
				Configuration::updateValue($key, $value);
			}
			
		}
		$result = [
			"success" => true,
			"message" => "Les options ont été mises à jour avec succès"
		];
		
		die(Tools::jsonEncode($result));
	}

    

}
