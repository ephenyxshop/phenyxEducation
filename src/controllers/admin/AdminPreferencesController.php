<?php

/**
 * Class AdminPreferencesControllerCore
 *
 * @since 1.9.1.0
 */
class AdminPreferencesControllerCore extends AdminController {

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
		$this->publicName = $this->l('Paramètre généraux');

        // Prevent classes which extend AdminPreferences to load useless data

        if (get_class($this) == 'AdminPreferencesController') {
            $roundMode = [
                [
                    'value' => PS_ROUND_HALF_UP,
                    'name'  => $this->l('Round up away from zero, when it is half way there (recommended)'),
                ],
                [
                    'value' => PS_ROUND_HALF_DOWN,
                    'name'  => $this->l('Round down towards zero, when it is half way there'),
                ],
                [
                    'value' => PS_ROUND_HALF_EVEN,
                    'name'  => $this->l('Round towards the next even value'),
                ],
                [
                    'value' => PS_ROUND_HALF_ODD,
                    'name'  => $this->l('Round towards the next odd value'),
                ],
                [
                    'value' => PS_ROUND_UP,
                    'name'  => $this->l('Round up to the nearest value'),
                ],
                [
                    'value' => PS_ROUND_DOWN,
                    'name'  => $this->l('Round down to the nearest value'),
                ],
            ];
            $activities1 = [
                0  => $this->l('-- Please choose your main activity --'),
                2  => $this->l('Education'),
                3  => $this->l('Art and Culture'),
                4  => $this->l('Babies'),
                5  => $this->l('Beauty and Personal Care'),
                6  => $this->l('Cars'),
                7  => $this->l('Computer Hardware and Software'),
                8  => $this->l('Download'),
                9  => $this->l('Fashion and accessories'),
                10 => $this->l('Flowers, Gifts and Crafts'),
                11 => $this->l('Food and beverage'),
                12 => $this->l('HiFi, Photo and Video'),
                13 => $this->l('Home and Garden'),
                14 => $this->l('Home Appliances'),
                15 => $this->l('Jewelry'),
                1  => $this->l('Lingerie and Adult'),
                16 => $this->l('Mobile and Telecom'),
                17 => $this->l('Services'),
                18 => $this->l('Shoes and accessories'),
                19 => $this->l('Sport and Entertainment'),
                20 => $this->l('Travel'),
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
			
			
			$fields['PS_SSL_ENABLED'] = [
                 'title'      => $this->l('Enable SSL'),
                    'desc'       => $this->l('If you own an SSL certificate for your shop\'s domain name, you can activate SSL encryption (https://) for customer account identification and order processing.'),
                    'hint'       => $this->l('If you want to enable SSL on all the pages of your shop, activate the "Enable on all the pages" option below.'),
                    'validation' => 'isBool',
                    'cast'       => 'intval',
                    'type'       => 'bool',
                    'default'    => '0',
            ];

            $fields['PS_SSL_ENABLED_EVERYWHERE'] = [
                'title'      => $this->l('Enable SSL on all pages'),
                'desc'       => $this->l('When enabled, all the pages of your shop will be SSL-secured.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '0',
                'disabled'   => (Tools::getValue('PS_SSL_ENABLED', Configuration::get('PS_SSL_ENABLED'))) ? false : true,
            ];
			$fields['EPH_SHOP_MODE'] = [
                'title'      => $this->l('Passer en mode boutique en Ligne'),
                'desc'       => $this->l('Active les fonctionnalités Ephenyx Shop.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '1',
            ];
			$fields['EPH_EDUCATION_MODE'] = [
                'title'      => $this->l('Passer en mode Organisme de formation'),
                'desc'       => $this->l('Active les fonctionnalités Ephenyx Education.'),
                'validation' => 'isBool',
                'cast'       => 'intval',
                'type'       => 'bool',
                'default'    => '1',
            ];
            $fields = array_merge(
                $fields,
                [
                    'PS_TOKEN_ENABLE'               => [
                        'title'      => $this->l('Increase front office security'),
                        'desc'       => $this->l('Enable or disable token in the Front Office to improve ephenyx\' security.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                        'visibility' => Shop::CONTEXT_ALL,
                    ],
                    'PS_ALLOW_HTML_IFRAME'          => [
                        'title'      => $this->l('Allow iframes on HTML fields'),
                        'desc'       => $this->l('Allow iframes on text fields like product description. We recommend that you leave this option disabled.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                    ],
                    'PS_USE_HTMLPURIFIER'           => [
                        'title'      => $this->l('Use HTMLPurifier Library'),
                        'desc'       => $this->l('Clean the HTML content on text fields. We recommend that you leave this option enabled.'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '0',
                    ],
                    'PS_PRICE_ROUND_MODE'           => [
                        'title'      => $this->l('Round mode'),
                        'desc'       => $this->l('You can choose among 6 different ways of rounding prices. "Round up away from zero ..." is the recommended behavior.'),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $roundMode,
                        'identifier' => 'value',
                    ],
                    'PS_ROUND_TYPE'                 => [
                        'title'      => $this->l('Round type'),
                        'desc'       => $this->l('You can choose when to round prices: either on each item, each line or the total (of an invoice, for example).'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => [
                            [
                                'name' => $this->l('Round on each item'),
                                'id'   => StudentEducation::ROUND_ITEM,
                            ],
                            [
                                'name' => $this->l('Round on each line'),
                                'id'   => StudentEducation::ROUND_LINE,
                            ],
                            [
                                'name' => $this->l('Round on the total'),
                                'id'   => StudentEducation::ROUND_TOTAL,
                            ],
                        ],
                        'identifier' => 'id',
                    ],
                    'PS_PRICE_DISPLAY_PRECISION'    => [
                        'title'      => $this->l('Number of decimals'),
                        'desc'       => $this->l('Choose how many decimals you want to display'),
                        'validation' => 'isUnsignedInt',
                        'cast'       => 'intval',
                        'type'       => 'text',
                    ],
                    '_EPHENYX_THEME_VERSION_'         => [
                        'title' => $this->l('Version du Thème'),
                        'desc'  => $this->l('Dèrnière version du thème front office'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    '_EPHENYX_LICENSE_KEY_'         => [
                        'title' => $this->l('Ephenyx Shop licence Key'),
                        'desc'  => $this->l('Add the Ephenyx key to ensure update of your shop'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_MAPS_API_KEY'       => [
                        'title' => $this->l('Google Maps API Key'),
                        'desc'  => $this->l('Add an API key to display Google Maps properly'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_CAPTCHA_API_KEY'    => [
                        'title' => $this->l('Google Captcha API Key'),
                        'desc'  => $this->l('Add an API key to display Captcha for form validation'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
                    'EPH_GOOGLE_CAPTCHA_SECRET_KEY' => [
                        'title' => $this->l('Google Captcha Secret Key'),
                        'desc'  => $this->l('Add the secret Google Captcha key'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_SENDINBLUE_API' => [
                        'title' => $this->l('Clé API Send inBlue'),
                        'desc'  => $this->l('Ajouter votre clé Send In Blue'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_SMS_TITLE' => [
                        'title' => $this->l('Entête SMS'),
                        'desc'  => $this->l('Maximum 11 Caractère'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_HANDICAP_REFERENT' => [
                        'title' => $this->l('Référent Handicap'),
                        'desc'  => $this->l('Ajouter le nom de votré référent Handicap'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_ALLOW_VIDEO_TUTO'          => [
                        'title'      => $this->l('Utiliser vous une vidé tutoriel'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
					'EPH_TUTO_VIDEO' => [
                        'title' => $this->l('GIF Tutoriel Video'),
                        'desc'  => $this->l('Ajouter votre tutoriel Video'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],
					'EPH_TUTO_VIDEO_LINK' => [
                        'title' => $this->l('Lien Tutoriel Video'),
                        'desc'  => $this->l('Ajouter votre tutoriel Video'),
                        'cast'  => 'strval',
                        'type'  => 'text',
                    ],


                    'PS_MESSENGER_FEATURE_ACTIVE'   => [
                        'title'      => $this->l('Activer le module Messenger'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'PS_SESSION_FEATURE_ACTIVE'     => [
                        'title'      => $this->l('Activer la gestion de session automatique'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'PS_SESSION_DAY'                => [
                        'title'            => $this->l('Journée de départ de session'),
                        'form_group_class' => "session-day hidden",
                        'validation'       => 'isInt',
                        'cast'             => 'intval',
                        'type'             => 'select',
                        'list'             => $days2,
                        'identifier'       => 'value',
                    ],
                    'PS_SHOP_ACTIVITY'              => [
                        'title'      => $this->l('Main Shop Activity'),
                        'validation' => 'isInt',
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'list'       => $activities2,
                        'identifier' => 'value',
                    ],
                ]
            );
			if(Configuration::get('EPH_EDUCATION_MODE')) {
				
			}

            // No HTTPS activation if you haven't already.

            if (!Tools::usingSecureMode() && !Configuration::get('PS_SSL_ENABLED')) {
                $fields['PS_SSL_ENABLED']['type'] = 'disabled';
                $fields['PS_SSL_ENABLED']['disabled'] = '<a class="btn btn-link" href="https://' . Tools::getShopDomainSsl() . Tools::safeOutput($_SERVER['REQUEST_URI']) . '">' . $this->l('Please click here to check if your shop supports HTTPS.') . '</a>';
            }

            $this->fields_options = [
                'general' => [
                    'title'  => $this->l('General'),
                    'icon'   => 'icon-cogs',
                    'fields' => $fields,
                    'submit' => ['title' => $this->l('Save')],
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
                    'desc' => $this->l('Save'),
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
			
			if($key == 'PS_SESSION_DAY') {
				
				$value = serialize([$value]);
				Configuration::updateValue($key, $value);
				
			} else 	if($key == 'EPH_TUTO_VIDEO') {
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

    /**
     * Enable / disable multishop menu if multishop feature is activated
     *
     * @param string $value
     *
     * @since 1.9.1.0
     */
    public function updateOptionPsMultishopFeatureActive($value) {

        Configuration::updateValue('PS_MULTISHOP_FEATURE_ACTIVE', $value);

        $tab = EmployeeMenu::getInstanceFromClassName('AdminShopGroup');
        $tab->active = (bool) Configuration::get('PS_MULTISHOP_FEATURE_ACTIVE');
        $tab->update();
    }

}
