<?php

/**
 * Class AdminLocalizationControllerCore
 *
 * @since 1.9.1.0
 */
class AdminLocalizationControllerCore extends AdminController {

    /**
     * AdminLocalizationControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'localisation';
        $this->publicName = $this->l('Données de localisation');
        parent::__construct();

        $this->fields_options = [
            'general'      => [
                'title'  => $this->l('Configuration'),
                'fields' => [
                    'EPH_LANG_DEFAULT'     => [
                        'title'      => $this->l('Default language'),
                        'hint'       => $this->l('The default language used in your shop.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'id_lang',
                        'list'       => Language::getLanguages(false),
                    ],
                    'EPH_DETECT_LANG'      => [
                        'title'      => $this->l('Set language from browser'),
                        'desc'       => $this->l('Set browser language as default language'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '1',
                    ],
                    'EPH_COUNTRY_DEFAULT'  => [
                        'title'      => $this->l('Default country'),
                        'hint'       => $this->l('The default country used in your shop.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'class'      => 'chosen',
                        'identifier' => 'id_country',
                        'list'       => Country::getCountries($this->context->language->id),
                    ],
                    'EPH_DETECT_COUNTRY'   => [
                        'title'      => $this->l('Set default country from browser language'),
                        'desc'       => $this->l('Set country corresponding to browser language'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '1',
                    ],
                    'EPH_CURRENCY_DEFAULT' => [
                        'title'      => $this->l('Default currency'),
                        'hint'       =>
                        $this->l('The default currency used in your shop.') . ' - ' . $this->l('If you change the default currency, you will have to manually edit every product price.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'id_currency',
                        'list'       => Currency::getCurrencies(false, true, true),
                    ],
                    'EPH_TIMEZONE'         => [
                        'title'      => $this->l('Time zone'),
                        'validation' => 'isAnything',
                        'type'       => 'select',
                        'class'      => 'chosen',
                        'list'       => Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS((new DbQuery())->select('`name`')->from('timezone')),
                        'identifier' => 'name',
                        
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
            'localization' => [
                'title'  => $this->l('Local units'),
                'icon'   => 'icon-globe',
                'fields' => [
                    'EPH_WEIGHT_UNIT'    => [
                        'title'      => $this->l('Weight unit'),
                        'hint'       => $this->l('The default weight unit for your shop (e.g. "kg" for kilograms, "lbs" for pound-mass, etc.).'),
                        'validation' => 'isWeightUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_DISTANCE_UNIT'  => [
                        'title'      => $this->l('Distance unit'),
                        'hint'       => $this->l('The default distance unit for your shop (e.g. "km" for kilometer, "mi" for mile, etc.).'),
                        'validation' => 'isDistanceUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_VOLUME_UNIT'    => [
                        'title'      => $this->l('Volume unit'),
                        'hint'       => $this->l('The default volume unit for your shop (e.g. "L" for liter, "gal" for gallon, etc.).'),
                        'validation' => 'isWeightUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_DIMENSION_UNIT' => [
                        'title'      => $this->l('Dimension unit'),
                        'hint'       => $this->l('The default dimension unit for your shop (e.g. "cm" for centimeter, "in" for inch, etc.).'),
                        'validation' => 'isDistanceUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
            'options'      => [
                'title'  => $this->l('Advanced'),
                'fields' => [
                    'EPH_LOCALE_LANGUAGE' => [
                        'title'      => $this->l('Language identifier'),
                        'hint'       => $this->l('The ISO 639-1 identifier for the language of the country where your web server is located (en, fr, sp, ru, pl, nl, etc.).'),
                        'validation' => 'isLanguageIsoCode',
                        'type'       => 'text',
                        
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_LOCALE_COUNTRY'  => [
                        'title'      => $this->l('Country identifier'),
                        'hint'       => $this->l('The ISO 3166-1 alpha-2 identifier for the country/region where your web server is located, in lowercase (us, gb, fr, sp, ru, pl, nl, etc.).'),
                        'validation' => 'isLanguageIsoCode',
                        'type'       => 'text',
                        
                        'class'      => 'fixed-width-sm',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];
    }

    public function ajaxProcessOpenTargetController() {

        $this->paragridScript = $this->generateParaGridScript();
        $this->setAjaxMedia();

        $data = $this->createTemplate($this->table . '.tpl');
        $options = $this->generateOptions();

        $data->assign([
            'controller'        => $this->controller_name,
            'tableName'         => $this->table,
            'className'         => $this->className,
            'link'              => $this->context->link, 'tabs' => $options,
            'localization_form' => $this->renderForm(),
        ]);

        $li = '<li id="uper' . $this->controller_name . '" data-controller="AdminDashboard"><a href="#content' . $this->controller_name . '">' . $this->publicName . '</a><button type="button" class="close tabdetail" data-id="uper' . $this->controller_name . '"><i class="icon icon-times-circle"></i></button></li>';
        $html = '<div id="content' . $this->controller_name . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,

            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function generateOptions() {

        $this->tpl_option_vars['titleList'] = $this->l('List') . ' ' . $this->toolbar_title[0];
        $this->tpl_option_vars['controller'] = Tools::getValue('controller');

        $tabs = [];
        $this->fields_options = [
            'general' => [
                'title'  => $this->l('Configuration'),
                'fields' => [
                    'EPH_LANG_DEFAULT'     => [
                        'title'      => $this->l('Default language'),
                        'hint'       => $this->l('The default language used in your shop.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'id_lang',
                        'list'       => Language::getLanguages(false),
                    ],
                    'EPH_DETECT_LANG'      => [
                        'title'      => $this->l('Set language from browser'),
                        'desc'       => $this->l('Set browser language as default language'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '1',
                    ],
                    'EPH_COUNTRY_DEFAULT'  => [
                        'title'      => $this->l('Default country'),
                        'hint'       => $this->l('The default country used in your shop.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'class'      => 'chosen',
                        'identifier' => 'id_country',
                        'list'       => Country::getCountries($this->context->language->id),
                    ],
                    'EPH_DETECT_COUNTRY'   => [
                        'title'      => $this->l('Set default country from browser language'),
                        'desc'       => $this->l('Set country corresponding to browser language'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                        'default'    => '1',
                    ],
                    'EPH_CURRENCY_DEFAULT' => [
                        'title'      => $this->l('Default currency'),
                        'hint'       =>
                        $this->l('The default currency used in your shop.') . ' - ' . $this->l('If you change the default currency, you will have to manually edit every product price.'),
                        'cast'       => 'intval',
                        'type'       => 'select',
                        'identifier' => 'id_currency',
                        'list'       => Currency::getCurrencies(false, true, true),
                    ],
                    'EPH_TIMEZONE'         => [
                        'title'      => $this->l('Time zone'),
                        'validation' => 'isAnything',
                        'type'       => 'select',
                        'class'      => 'chosen',
                        'list'       => Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS((new DbQuery())->select('`name`')->from('timezone')),
                        'identifier' => 'name',
                       
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],

        ];

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

        $tabs['general'] = [
            'key'     => $this->fields_options['general']['title'],
            'content' => $options,
        ];
        $this->fields_options = [
            'localization' => [
                'title'  => $this->l('Local units'),
                'icon'   => 'icon-globe',
                'fields' => [
                    'EPH_WEIGHT_UNIT'    => [
                        'title'      => $this->l('Weight unit'),
                        'hint'       => $this->l('The default weight unit for your shop (e.g. "kg" for kilograms, "lbs" for pound-mass, etc.).'),
                        'validation' => 'isWeightUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_DISTANCE_UNIT'  => [
                        'title'      => $this->l('Distance unit'),
                        'hint'       => $this->l('The default distance unit for your shop (e.g. "km" for kilometer, "mi" for mile, etc.).'),
                        'validation' => 'isDistanceUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_VOLUME_UNIT'    => [
                        'title'      => $this->l('Volume unit'),
                        'hint'       => $this->l('The default volume unit for your shop (e.g. "L" for liter, "gal" for gallon, etc.).'),
                        'validation' => 'isWeightUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_DIMENSION_UNIT' => [
                        'title'      => $this->l('Dimension unit'),
                        'hint'       => $this->l('The default dimension unit for your shop (e.g. "cm" for centimeter, "in" for inch, etc.).'),
                        'validation' => 'isDistanceUnit',
                        'required'   => true,
                        'type'       => 'text',
                        'class'      => 'fixed-width-sm',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],

        ];
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

        $tabs['localization'] = [
            'key'     => $this->fields_options['localization']['title'],
            'content' => $options,
        ];

        $this->fields_options = [
            'options' => [
                'title'  => $this->l('Advanced'),
                'fields' => [
                    'EPH_LOCALE_LANGUAGE' => [
                        'title'      => $this->l('Language identifier'),
                        'hint'       => $this->l('The ISO 639-1 identifier for the language of the country where your web server is located (en, fr, sp, ru, pl, nl, etc.).'),
                        'validation' => 'isLanguageIsoCode',
                        'type'       => 'text',
                       
                        'class'      => 'fixed-width-sm',
                    ],
                    'EPH_LOCALE_COUNTRY'  => [
                        'title'      => $this->l('Country identifier'),
                        'hint'       => $this->l('The ISO 3166-1 alpha-2 identifier for the country/region where your web server is located, in lowercase (us, gb, fr, sp, ru, pl, nl, etc.).'),
                        'validation' => 'isLanguageIsoCode',
                        'type'       => 'text',
                        
                        'class'      => 'fixed-width-sm',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],

        ];
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

        $tabs['options'] = [
            'key'     => $this->fields_options['options']['title'],
            'content' => $options,
        ];

        return $tabs;

    }

    public function ajaxProcessUpdateConfigurationOptions() {

        foreach ($_POST as $key => $value) {

            if ($key == 'action' || $key == 'ajax') {

                continue;
            }

            Configuration::updateValue($key, $value);

        }

        if (Tools::isSubmit('submitLocalizationPack')) {

            if (($isoLocalizationPack = Tools::getValue('iso_localization_pack')) && Validate::isFileName($isoLocalizationPack)) {
                $path = _EPH_ROOT_DIR_ . '/localization/' . $isoLocalizationPack . '.xml';

                if (!($pack = @file_get_contents($path))) {
                    $this->errors[] = Tools::displayError('Cannot load the localization pack.');
                }

                if (!$selection = Tools::getValue('selection')) {
                    $this->errors[] = Tools::displayError('Please select at least one item to import.');
                } else {

                    foreach ($selection as $selected) {

                        if (!Validate::isLocalizationPackSelection($selected)) {
                            $this->errors[] = Tools::displayError('Invalid selection');

                        }

                    }

                    if (!count($this->error)) {

                        $localizationPack = new LocalizationPack();

                        if (!$localizationPack->loadLocalisationPack($pack, $selection, false, $isoLocalizationPack)) {
                            $this->errors = array_merge($this->errors, $localizationPack->getErrors());
                        }

                    }

                }

            }

        }

        $result = [
            "success" => true,
            "message" => "Les options ont été mises à jour avec succès",
        ];

        die(Tools::jsonEncode($result));
    }

    /**
     * @since 1.9.1.0
     */
    public function postProcess() {

        if (_EPH_MODE_DEMO_) {
            $this->errors[] = Tools::displayError('This functionality has been disabled.');

            return;
        }

        if (!extension_loaded('openssl')) {
            $this->displayWarning($this->l('Importing a new language may fail without the OpenSSL module. Please enable "openssl.so" on your server configuration.'));
        }

        if (Tools::isSubmit('submitLocalizationPack')) {

            if (($isoLocalizationPack = Tools::getValue('iso_localization_pack')) && Validate::isFileName($isoLocalizationPack)) {
                $path = _EPH_ROOT_DIR_ . '/localization/' . $isoLocalizationPack . '.xml';

                if (!($pack = @file_get_contents($path))) {
                    $this->errors[] = Tools::displayError('Cannot load the localization pack.');
                }

                if (!$selection = Tools::getValue('selection')) {
                    $this->errors[] = Tools::displayError('Please select at least one item to import.');
                } else {

                    foreach ($selection as $selected) {

                        if (!Validate::isLocalizationPackSelection($selected)) {
                            $this->errors[] = Tools::displayError('Invalid selection');

                            return;
                        }

                    }

                    $localizationPack = new LocalizationPack();

                    if (!$localizationPack->loadLocalisationPack($pack, $selection, false, $isoLocalizationPack)) {
                        $this->errors = array_merge($this->errors, $localizationPack->getErrors());
                    } else {
                        Tools::redirectAdmin(static::$currentIndex . '&conf=23&token=' . $this->token);
                    }

                }

            }

        }

        parent::postProcess();
    }

    /**
     * @param array $a
     * @param array $b
     *
     * @return bool
     *
     * @since 1.9.1.0
     */
    public function sortLocalizationsPack($a, $b) {

        return ($a['name'] > $b['name']) ? -1 : 1;
    }

    /**
     * @return void
     *
     * @since 1.9.1.0
     */
    public function display() {

        $this->initContent();
        parent::display();
    }

    public function initContent() {

        $this->initTabModuleList();

        if (!$this->loadObject(true)) {
            return;
        }

        $this->initPageHeaderToolbar();
        $this->context->smarty->assign(
            [
                'localization_form'         => $this->renderForm(),
                'localization_options'      => $this->renderOptions(),
                'url_post'                  => static::$currentIndex . '&token=' . $this->token,
                'show_page_header_toolbar'  => $this->show_page_header_toolbar,
                'page_header_toolbar_title' => $this->page_header_toolbar_title,
                'page_header_toolbar_btn'   => $this->page_header_toolbar_btn,
                'bo_imgdir'                 => __EPH_BASE_URI__ . $this->admin_webpath . _EPH_ADMIN_THEME_DIR_ . $this->bo_theme . '/img/',
            ]
        );
    }

    /**
     * @return string|null
     *
     * @since 1.9.1.0
     */
    public function renderForm() {

        $localizationsPack = false;
        $this->tpl_option_vars['options_content'] = $this->renderOptions();

        $localizationFile = _EPH_ROOT_DIR_ . '/localization/localization.xml';

        if (file_exists($localizationFile)) {
            $xmlLocalization = @simplexml_load_file($localizationFile);
        }

        $remoteIsos = [];

        $i = 0;

        if (isset($xmlLocalization) && $xmlLocalization) {

            foreach ($xmlLocalization->pack as $key => $pack) {
                $remoteIsos[(string) $pack->iso] = true;
                $localizationsPack[$i]['iso_localization_pack'] = (string) $pack->iso;
                $localizationsPack[$i]['name'] = (string) $pack->name;
                $i++;
            }

        }

        // Add local localization .xml files to the list if they are not already there

        foreach (scandir(_EPH_ROOT_DIR_ . '/localization/') as $entry) {
            $m = [];

            if (preg_match('/^([a-z]{2})\.xml$/', $entry, $m)) {
                $iso = $m[1];

                if (empty($remoteIsos[$iso])) {
                    // if the pack is only there locally and not on PhenyxShop.com

                    $xmlPack = @simplexml_load_file(_EPH_ROOT_DIR_ . '/localization/' . $entry);

                    if (!$xmlPack) {
                        return $this->displayWarning($this->l(sprintf('%1s could not be loaded', $entry)));
                    }

                    $localizationsPack[$i]['iso_localization_pack'] = $iso;
                    $localizationsPack[$i]['name'] = sprintf($this->l('%s (local)'), (string) $xmlPack['name']);
                    $i++;
                }

            }

        }

        if (is_array($localizationsPack)) {
            usort($localizationsPack, [$this, 'sortLocalizationsPack']);
        }

        $selectionImport = [
            [
                'id'   => 'states',
                'val'  => 'states',
                'name' => $this->l('States'),
            ],
            [
                'id'   => 'taxes',
                'val'  => 'taxes',
                'name' => $this->l('Taxes'),
            ],
            [
                'id'   => 'currencies',
                'val'  => 'currencies',
                'name' => $this->l('Currencies'),
            ],
            [
                'id'   => 'languages',
                'val'  => 'languages',
                'name' => $this->l('Languages'),
            ],
            [
                'id'   => 'units',
                'val'  => 'units',
                'name' => $this->l('Units (e.g. weight, volume, distance)'),
            ],
            [
                'id'   => 'groups',
                'val'  => 'groups',
                'name' => $this->l('Change the behavior of the taxes displayed to the groups'),
            ],
        ];

        $this->fields_form = [
            'tinymce' => true,
            'legend'  => [
                'title' => $this->l('Import a localization pack'),
                'icon'  => 'icon-globe',
            ],
            'input'   => [
                [
                    'type'    => 'select',
                    'class'   => 'chosen',
                    'label'   => $this->l('Localization pack you want to import'),
                    'name'    => 'iso_localization_pack',
                    'options' => [
                        'query' => $localizationsPack,
                        'id'    => 'iso_localization_pack',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'   => 'checkbox',
                    'label'  => $this->l('Content to import'),
                    'name'   => 'selection[]',
                    'lang'   => true,
                    'values' => [
                        'query' => $selectionImport,
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'    => 'radio',
                    'label'   => $this->l('Download pack data'),
                    'desc'    => $this->l('If set to yes then the localization pack will be downloaded from ephenyx.com. Otherwise the local xml file found in the localization folder of your ephenyx installation will be used.'),
                    'name'    => 'download_updated_pack',
                    'is_bool' => true,
                    'values'  => [
                        [
                            'id'    => 'download_updated_pack_yes',
                            'value' => 1,
                            'label' => $this->l('Yes'),
                        ],
                        [
                            'id'    => 'download_updated_pack_no',
                            'value' => 0,
                            'label' => $this->l('No'),
                        ],
                    ],
                ],
            ],
            'submit'  => [
                'title' => $this->l('Import'),
                'icon'  => 'process-icon-import',
                'name'  => 'submitLocalizationPack',
            ],
        ];

        $this->fields_value = [
            'selection[]_states'     => true,
            'selection[]_taxes'      => true,
            'selection[]_currencies' => true,
            'selection[]_languages'  => true,
            'selection[]_units'      => true,
            'download_updated_pack'  => 1,
        ];

        $this->show_toolbar = true;

        return parent::renderForm();
    }

    /**
     * @since 1.9.1.0
     */
    public function beforeUpdateOptions() {

        $lang = new Language((int) Tools::getValue('EPH_LANG_DEFAULT'));

        if (!$lang->active) {
            $lang->active = 1;
            $lang->save();
        }

    }

    /**
     * @param string $value
     *
     * @since 1.9.1.0
     */
    public function updateOptionPsCurrencyDefault($value) {

        if ($value == Configuration::get('EPH_CURRENCY_DEFAULT')) {
            return;
        }

        Configuration::updateValue('EPH_CURRENCY_DEFAULT', $value);

      
         Currency::refreshCurrencies();

        
    }

}
