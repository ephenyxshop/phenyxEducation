<?php

class AdminEphAccessoriesControllerCore extends AdminController {

    public $bootstrap = true;
    protected $position_identifier = 'id_accessory_group';

    protected $media_js = [
        'admin_product_setting.js',
        'admin_behavior.js',
        'admin_multi_accessories.js',
        'admin_setting_multi_accessories.js',
        'hsma_filter_by_category.js',
        'hsma_display_style.js',
    ];

    protected $media_css = [
        'accessory_admin_tab.css',
        'adminpage.css',
    ];
    /**
     * Show result to view.
     *
     * @var type json
     */
    protected $ajax_json = [
        'success' => false,
        'message' => null,
    ];

    public $configuration_keys = [];

    public $configuration_lang_keys = [];

    /**
     * Start time regenerate image.
     *
     * @var int
     */
    protected $start_time = 0;

    /**
     * Max time regenerate images.
     *
     * @var type
     */
    protected $max_execution_time = 18000;

    public function __construct() {

        $this->table = 'accessory_group';
        $this->className = 'AccessoriesGroupAbstract';
        $this->lang = true;
        parent::__construct();

        $this->_defaultOrderBy = 'position';

        // check status visit welcome page, if != 1 go to welcome page
        $image_sizes = $this->getSizeOfImages();
        $this->fields_options = [
            'settings'             => [
                'title'  => $this->l('Settings'),
                'icon'   => 'icon-cogs',
                'fields' => [
                    'HSMA_DISPLAY_STYLE'               => [
                        'title'      => $this->l('display_style'),
                        'hint'       => $this->l('define_how_accessories_look_like_at_product_page'),
                        'type'       => 'select',
                        'id'         => 'HSMA_DISPLAY_STYLE',
                        'list'       => $this->getDisplayStyles(),
                        'identifier' => 'id',
                    ],
                    'HSMA_SHOW_IMAGES'                 => [
                        'title'      => $this->l('show_images'),
                        'hint'       => $this->l('display_images_along_with_each_accessory'),
                        'id'         => 'HSMA_SHOW_IMAGES',
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_APPLY_FANCYBOX_TO_IMAGE'     => [
                        'title'            => $this->l('apply_fancybox_to_images'),
                        'hint'             => $this->l('show_accessory_images_in_a_fancybox'),
                        'id'               => 'HSMA_APPLY_FANCYBOX_TO_IMAGE',
                        'form_group_class' => 'apply_fancybox',
                        'validation'       => 'isBool',
                        'cast'             => 'intval',
                        'type'             => 'bool',
                    ],
                    'HSMA_IMAGE_SIZE_IN_FANCYBOX'      => [
                        'title'            => $this->l('image_size_in_fancybox'),
                        'hint'             => $this->l('image_size_in_fancybox'),
                        'id'               => 'HSMA_IMAGE_SIZE_IN_FANCYBOX',
                        'form_group_class' => 'image_size',
                        'type'             => 'select',
                        'list'             => $image_sizes['image_sizes'],
                        'identifier'       => 'id',
                    ],
                    'HSMA_SHOW_PRICE'                  => [
                        'title'      => $this->l('show_price'),
                        'hint'       => $this->l('display_prices_along_with_each_accessory'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_SHOW_PRICE_TABLE'            => [
                        'title'      => $this->l('show_price_table'),
                        'hint'       => $this->l('tell_your_customers_a_summary'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_SHOW_COMBINATION'            => [
                        'title'      => $this->l('display_combination_info_in_price_table'),
                        'hint'       => $this->l('display_combination_info_in_price_table'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_CHANGE_MAIN_PRICE'           => [
                        'title'      => $this->l('change_the_main_item_s_price_accordingly'),
                        'hint'       => $this->l('whenever_an_accessory_is_added_or_removed_the_main_item_s_price_is_changed_and_your_customers_clearly_know_the_amount'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_ALLOW_CUSTOMER_CHANGE_QTY'   => [
                        'title'      => $this->l('allow_your_customers_to_change_item_quantity'),
                        'hint'       => $this->l('allow_your_customers_to_change_item_quantity'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => [
                        'title'      => $this->l('buy_main_product_accessories_together'),
                        'hint'       => $this->l('tell_your_customers_that_they_need_to_buy_main_product_and_accessories_together'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_SHOW_TOTAL_PRICE'            => [
                        'title'      => $this->l('show_total_price_instead_of_the_main_product_price_at_the_product_list_page'),
                        'hint'       => $this->l('show_total_price_main_product_price_required_accessories_price_instead_of_the_main_product_price_at_the_product_list_page'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_SHOW_ACCESSORIES_OFS'        => [
                        'title'      => $this->l('display_accessories_out_of_stock_at_the_front_end'),
                        'hint'       => $this->l('display_or_hide_accessories_out_of_stock_at_the_front_end'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_SHOW_ICON_OUT_OF_STOCK'      => [
                        'title'      => $this->l('display_icon_out_of_stock_at_the_front_end'),
                        'hint'       => $this->l('tell_your_customers_that_this_accessory_is_out_of_stock'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_COLLAPSE_EXPAND_GROUPS'      => [
                        'title'      => $this->l('collapse_expand_accessory_groups'),
                        'hint'       => $this->l('collapse_expand_accessory_groups'),
                        'type'       => 'select',
                        'list'       => $this->getSettingCollapseExpand(),
                        'identifier' => 'id',
                    ],
                    'HSMA_ALERT_MESSAGE'               => [
                        'title' => $this->l('alert_message'),
                        'hint'  => $this->l('tell_your_customer_when_they_dont_choose_any_accessories_to_buy_together_with_main_product'),
                        'class' => 'field-text-input',
                        'lang'  => true,
                        'type'  => 'textLang',
                    ],
                    'HSMA_EACH_ACCESSORY_TO_BASKET'    => [
                        'title'      => $this->l('add_each_accessory_to_basket'),
                        'hint'       => $this->l('allow_customer_add_separated_accessory_to_basket'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB' => [
                        'title'      => $this->l('open_accessories_in_a_new_tab'),
                        'validation' => 'isBool',
                        'cast'       => 'intval',
                        'type'       => 'bool',
                    ],
                    'HSMA_TITLE'                       => [
                        'title' => $this->l('title'),
                        'hint'  => $this->l('title_of_accessory_block_at_product_page'),
                        'id'    => 'HSMA_TITLE',
                        'class' => 'field-text-input',
                        'type'  => 'textLang',
                        'lang'  => true,
                        'class' => 'fixed-width-xxl',
                    ],
                    'HSMA_MESSAGE_AVAILABLE_LATER'     => [
                        'title' => $this->l('displayed_text_when_backordering_is_allowed'),
                        'hint'  => $this->l('if_the_text_displayed_text_when_backordering_is_allowed_in_product_edit_page_is_empty'),
                        'id'    => 'HSMA_MESSAGE_AVAILABLE_LATER',
                        'cast'  => 'strval',
                        'class' => 'field-text-input',
                        'type'  => 'textLang',
                        'lang'  => true,
                        'class' => 'fixed-width-xxl',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
            'hsma_add_accessories' => [
                'title'  => $this->l('add_multi_accessories_for_multi_products'),
                'fields' => $this->generateFormAddAccessories(),
            ],

        ];

        $this->fields_list = [
            'id_accessory_group' => [
                'title' => $this->l('id'),
                'width' => 25,
            ],
            'name'               => [
                'title'      => $this->l('group_name'),
                'width'      => 'auto',
                'filter_key' => 'b!name',
            ],
            'active'             => [
                'title'  => $this->l('active'),
                'width'  => 40,
                'align'  => 'center',
                'active' => 'status',
            ],
            'position'           => [
                'title'    => $this->l('Position'),
                'align'    => 'center',
                'position' => 'position',
            ],
        ];
        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->l('delete_selected_items'),
                'confirm' => $this->l('delete_selected_items') . '?',
            ],
        ];

        $this->configuration_keys = [
            'HSMA_DISPLAY_STYLE'               => 'isInt',
            'HSMA_SHOW_IMAGES'                 => 'isInt',
            'HSMA_SHOW_SHORT_DESCRIPTION'      => 'isInt',
            'HSMA_SHOW_PRICE'                  => 'isInt',
            'HSMA_SHOW_COMBINATION'            => 'isInt',
            'HSMA_SHOW_PRICE_TABLE'            => 'isInt',
            'HSMA_TITLE'                       => 'isString',
            'HSMA_MESSAGE_AVAILABLE_LATER'     => 'isString',
            'HSMA_EACH_ACCESSORY_TO_BASKET'    => 'isInt',
            'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB' => 'isInt',
            'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => 'isInt',
            'HSMA_SHOW_TOTAL_PRICE'            => 'isInt',
            'HSMA_ALERT_MESSAGE'               => 'isString',
            'HSMA_SHOW_CUSTOM_QUANTITY'        => 'isInt',
            'HSMA_ALLOW_CUSTOMER_CHANGE_QTY'   => 'isInt',
            'HSMA_CHANGE_MAIN_PRICE'           => 'isInt',
            'HSMA_APPLY_FANCYBOX_TO_IMAGE'     => 'isInt',
            'HSMA_IMAGE_SIZE_IN_FANCYBOX'      => 'isString',
            'HSMA_SHOW_ACCESSORIES_OFS'        => 'isInt',
            'HSMA_SHOW_ICON_OUT_OF_STOCK'      => 'isInt',
            'HSMA_COLLAPSE_EXPAND_GROUPS'      => 'isInt',
        ];

        $this->configuration_lang_keys = [
            'HSMA_TITLE',
            'HSMA_MESSAGE_AVAILABLE_LATER',
            'HSMA_ALERT_MESSAGE',
        ];
    }

    protected function generateFormAddAccessories() {

        return [
            'HSMA_ADD_ACCESSORIES' => [
                'type'            => 'hsma_add_accessores',
                'is_PhenyxShop16' => true,
            ],
        ];
    }

    /**
     * Render block categories tree
     * @param string $id_block_categories
     * @return html
     */
    protected function getCategoryTree($id_block_categories) {

        // Generate category selection tree
        $tree = new HelperTreeCategories($id_block_categories, $this->l('filter_by_category'));
        $tree->setId($id_block_categories);
        $tree->setUseSearch(true);
        $tree->setAttribute($id_block_categories, true)->setAttribute('base_url', preg_replace('#&id_category=[0-9]*#', '', self::$currentIndex) . '&token=' . $this->token)->setInputName('id-category')->setUseCheckBox(true)->setSelectedCategories([0]);
        return $tree->setRootCategory((int) Category::getRootCategory()->id)->render();
    }

    /**
     * The last call for any ajax action if method "displayAjax[ActionName]" is not found
     */
    public function displayAjax() {

        exit(Tools::jsonEncode($this->ajax_json));
    }

    /**
     * Process save all settings.
     */
    public function postProcess() {

        if (Tools::isSubmit('submitSetting')) {
            unset($this->configuration_keys['HSMA_SHOW_CUSTOM_QUANTITY']);

            foreach ($this->configuration_keys as $config_name => $config_validate) {

                if (!in_array($config_name, $this->configuration_lang_keys)) {
                    $config_validate = $config_validate; // fix validator ps not use

                    if (Validate::$config_validate(Tools::getValue($config_name))) {
                        Configuration::updateValue($config_name, Tools::getValue($config_name));
                    }

                }

            }

            $languages = $this->getLanguages();
            $title = [];
            $messages_available_later = [];
            $alert_message = [];

            foreach ($languages as $lang) {
                $title[$lang['id_lang']] = Tools::getValue('HSMA_TITLE_' . $lang['id_lang']);
                $messages_available_later[$lang['id_lang']] = Tools::getValue('HSMA_MESSAGE_AVAILABLE_LATER_' . $lang['id_lang']);

                if (Tools::getValue('HSMA_ALERT_MESSAGE_' . $lang['id_lang'])) {
                    $alert_message[$lang['id_lang']] = Tools::getValue('HSMA_ALERT_MESSAGE_' . $lang['id_lang']);
                }

            }

            Configuration::updateValue('HSMA_TITLE', $title);
            Configuration::updateValue('HSMA_MESSAGE_AVAILABLE_LATER', $messages_available_later);

            if (!empty($alert_message)) {
                Configuration::updateValue('HSMA_ALERT_MESSAGE', $alert_message);
            }

            $this->confirmations[] = $this->_conf[6];
        }

        parent::postProcess();
    }

    /**
     * Render all accesory groups.
     *
     * @return HTML string
     */
    public function renderList() {

        $this->addRowAction('edit');
        $this->addRowAction('delete');

        return parent::renderList();
    }

    public function getDisplayStyles($use_default = false) {

        $type_options = [
            [
                'id'   => MaDisplayStyle::CHECKBOX,
                'name' => $this->l('checkbox'),
            ],
            [
                'id'   => MaDisplayStyle::DROPDOWN,
                'name' => $this->l('dropdown'),
            ],
            [
                'id'   => MaDisplayStyle::RADIO,
                'name' => $this->l('radio'),
            ],
        ];

        if ($use_default) {
            $type_options = array_merge(
                [
                    [
                        'id'   => MaDisplayStyle::USE_DEFAULT,
                        'name' => $this->l('use_default'),
                    ],
                ],
                $type_options);
        }

        return $type_options;
    }

    protected function renderAlertMessage() {

        $fields_form = [];
        $alert_message = [
            'type'             => 'text',
            'class'            => 'field-text-input',
            'form_group_class' => 'alertmessage',
            'label'            => $this->l('alert_message'),
            'hint'             => $this->l('tell_your_customer_when_they_dont_choose_any_accessories_to_buy_together_with_main_product'),
            'name'             => 'HSMA_ALERT_MESSAGE',
            'lang'             => true,
        ];

        $fields_form = $alert_message;

        return $fields_form;
    }

    protected function getConfigurationValues() {

        $fields_values = [
            'HSMA_DISPLAY_STYLE'               => Tools::getValue('HSMA_DISPLAY_STYLE', Configuration::get('HSMA_DISPLAY_STYLE')),
            'HSMA_SHOW_IMAGES'                 => Tools::getValue('HSMA_SHOW_IMAGES', Configuration::get('HSMA_SHOW_IMAGES')),
            'HSMA_SHOW_PRICE'                  => Tools::getValue('HSMA_SHOW_PRICE', Configuration::get('HSMA_SHOW_PRICE')),
            'HSMA_SHOW_SHORT_DESCRIPTION'      => Tools::getValue('HSMA_SHOW_SHORT_DESCRIPTION', Configuration::get('HSMA_SHOW_SHORT_DESCRIPTION')),
            'HSMA_SHOW_PRICE_TABLE'            => Tools::getValue('HSMA_SHOW_PRICE_TABLE', Configuration::get('HSMA_SHOW_PRICE_TABLE')),
            'HSMA_EACH_ACCESSORY_TO_BASKET'    => Tools::getValue('HSMA_EACH_ACCESSORY_TO_BASKET', Configuration::get('HSMA_EACH_ACCESSORY_TO_BASKET')),
            'HSMA_OPEN_ACCESSORIES_IN_NEW_TAB' => Tools::getValue('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB', Configuration::get('HSMA_OPEN_ACCESSORIES_IN_NEW_TAB')),
            'HSMA_BUY_ACCESSORY_MAIN_TOGETHER' => Tools::getValue('HSMA_BUY_ACCESSORY_MAIN_TOGETHER', Configuration::get('HSMA_BUY_ACCESSORY_MAIN_TOGETHER')),
            'HSMA_SHOW_TOTAL_PRICE'            => Tools::getValue('HSMA_SHOW_TOTAL_PRICE', Configuration::get('HSMA_SHOW_TOTAL_PRICE')),
            'HSMA_SHOW_CUSTOM_QUANTITY'        => Tools::getValue('HSMA_SHOW_CUSTOM_QUANTITY', Configuration::get('HSMA_SHOW_CUSTOM_QUANTITY')),
            'HSMA_APPLY_FANCYBOX_TO_IMAGE'     => Tools::getValue('HSMA_APPLY_FANCYBOX_TO_IMAGE', Configuration::get('HSMA_APPLY_FANCYBOX_TO_IMAGE')),
            'HSMA_IMAGE_SIZE_IN_FANCYBOX'      => Tools::getValue('HSMA_IMAGE_SIZE_IN_FANCYBOX', Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX')),
            'HSMA_CHANGE_MAIN_PRICE'           => Tools::getValue('HSMA_CHANGE_MAIN_PRICE', Configuration::get('HSMA_CHANGE_MAIN_PRICE')),
            'HSMA_ALLOW_CUSTOMER_CHANGE_QTY'   => Tools::getValue('HSMA_ALLOW_CUSTOMER_CHANGE_QTY', Configuration::get('HSMA_ALLOW_CUSTOMER_CHANGE_QTY')),
            'HSMA_SHOW_COMBINATION'            => Tools::getValue('HSMA_SHOW_COMBINATION', Configuration::get('HSMA_SHOW_COMBINATION')),
            'HSMA_SHOW_ACCESSORIES_OFS'        => Tools::getValue('HSMA_SHOW_ACCESSORIES_OFS', Configuration::get('HSMA_SHOW_ACCESSORIES_OFS')),
            'HSMA_SHOW_ICON_OUT_OF_STOCK'      => Tools::getValue('HSMA_SHOW_ICON_OUT_OF_STOCK', Configuration::get('HSMA_SHOW_ICON_OUT_OF_STOCK')),
            'HSMA_COLLAPSE_EXPAND_GROUPS'      => Tools::getValue('HSMA_COLLAPSE_EXPAND_GROUPS', Configuration::get('HSMA_COLLAPSE_EXPAND_GROUPS')),
        ];

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $fields_values['HSMA_TITLE'][$lang['id_lang']] = Tools::getValue('HSMA_TITLE', Configuration::get('HSMA_TITLE', $lang['id_lang']));
            $fields_values['HSMA_MESSAGE_AVAILABLE_LATER'][$lang['id_lang']] = Tools::getValue('HSMA_MESSAGE_AVAILABLE_LATER', Configuration::get('HSMA_MESSAGE_AVAILABLE_LATER', $lang['id_lang']));
            $fields_values['HSMA_ALERT_MESSAGE'][$lang['id_lang']] = Tools::getValue('HSMA_ALERT_MESSAGE', Configuration::get('HSMA_ALERT_MESSAGE', $lang['id_lang']));
        }

        return $fields_values;
    }

    protected function getSizeOfImages() {

        $image_types = ImageType::getImagesTypes('products');
        $image_sizes = [];
        $i = 0;
        $default_image = [];
        $max_width = 0;
        $id_image_type_default = Configuration::get('HSMA_IMAGE_SIZE_IN_FANCYBOX');

        foreach ($image_types as $image_type) {

            if ($id_image_type_default > 0) {

                if ($image_type['name'] === $id_image_type_default) {
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } else {
                    $image_sizes[$i]['id'] = $image_type['name'];
                    $image_sizes[$i]['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                }

            } else {

                if ($max_width == 0) {
                    $max_width = $image_type['width'];
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } else if ($max_width < $image_type['width']) {
                    $max_width = $image_type['width'];
                    $image_sizes[$i] = $default_image;
                    $default_image['id'] = $image_type['name'];
                    $default_image['name'] = $image_type['name'] . ' (' . $image_type['width'] . 'x' . $image_type['height'] . ')';
                } else {
                    $image_sizes[$i] = $default_image;
                }

            }

            ++$i;
        }

        return ['image_sizes' => array_map('unserialize', array_unique(array_map('serialize', $image_sizes))), 'default' => $default_image];
    }

    public function getSettingCollapseExpand() {

        return [
            [
                'id'   => MaDisplayStyle::DISPLAY_GROUPS_NONE,
                'name' => $this->l('no')],
            [
                'id'   => MaDisplayStyle::DISPLAY_GROUPS_EXPAND,
                'name' => $this->l('expand_all_groups')],
            [
                'id'   => MaDisplayStyle::DISPLAY_GROUPS_EXPAND_FIRST,
                'name' => $this->l('expand_the_first_group')],
            [
                'id'   => MaDisplayStyle::DISPLAY_GROUPS_COLLAPSE,
                'name' => $this->l('collapse_all_groups')],
        ];
    }

    /**
     * Show form add a group.
     *
     * @return HTML string
     */
    public function renderForm() {

        if ($this->display == 'edit') {
            $this->toolbar_title = $this->l('edit_group');
        } else {
            $this->toolbar_title = $this->l('add_a_new_accessory_group');
        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->l('group'),
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->l('name'),
                    'name'     => 'name',
                    'lang'     => true,
                    'size'     => 33,
                    'hint'     => $this->l('invalid_characters') . ' <>;=#{}',
                    'required' => true,
                ],
                [
                    'type'    => 'select',
                    'label'   => $this->l('display_style'),
                    'name'    => 'display_style',
                    'hint'    => $this->l('define_how_accessories_look_like_at_product_page'),
                    'options' => [
                        'query' => $this->getDisplayStyles(true),
                        'id'    => 'id',
                        'name'  => 'name',
                    ],
                ],
                [
                    'type'          => 'switch',
                    'label'         => $this->l('active'),
                    'name'          => 'active',
                    'required'      => false,
                    'is_bool'       => true,
                    'default_value' => 1,
                    'values'        => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->l('enabled'),
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->l('disabled'),
                        ],
                    ],
                ],
            ],
        ];
        $this->fields_form['submit'] = [
            'title' => $this->l('save'),
        ];

        return parent::renderForm();
    }

    /**
     * Create button add new accessory group.
     */
    public function initPageHeaderToolbar() {

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_accessory_group'] = [
                'href' => self::$currentIndex . '&addaccessory_group&token=' . $this->token,
                'desc' => $this->l('add_a_new_accessory_group'),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    /**
     * Set Media file include when controller called.
     */
    public function setMedia() {

        parent::setMedia();
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/default/css/black-tie/jquery-ui.css');
        $this->addJquery('3.4.1');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/js/jquery-ui/jquery-ui.js');
        $this->addJs(__PS_BASE_URI__ . $this->admin_webpath . '/themes/js/jquery.tablednd.js');

        if (!empty($this->media_js) && is_array($this->media_js)) {

            foreach ($this->media_js as $js_file) {
                $this->addJs(__PS_BASE_URI__ . $this->admin_webpath . '/themes/js/' . $js_file);
            }

        }

        if (!empty($this->media_css) && is_array($this->media_css)) {
            $css_files = [];

            foreach ($this->media_css as $css_file) {
                $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/default/css/' . $css_file);
            }

        }

    }

    /**
     * Checking product is out of stock.
     *
     * @param int $id_product
     * @param int $id_product_attribute
     * @param int $quantity             default quantity
     *
     * @return bool
     */
    protected function isStockAvailable($id_product, $id_product_attribute, $quantity) {

        $flag = false;
        $stock_status = MaProduct::getStockStatus((int) $id_product, (int) $id_product_attribute, $this->context->shop);

        if (!empty($stock_status)) {

            if (Product::isAvailableWhenOutOfStock($stock_status['out_of_stock']) || (!Product::isAvailableWhenOutOfStock($stock_status['out_of_stock']) && $stock_status['quantity'] >= (int) $quantity)) {
                $flag = true;
            }

        }

        return $flag;
    }

    /**
     * Change display style.
     */
    public function displayAjaxChangeDisplayStyle() {

        $id_group = (int) Tools::getValue('id_group');
        $display_style = (int) Tools::getValue('display_style');

        if (!$id_group) {
            die(Tools::jsonEncode(false));
        }

        $group = new AccessoriesGroupAbstract($id_group);

        if (!Validate::isLoadedObject($group)) {
            die(Tools::jsonEncode(false));
        }

        $group->display_style = $display_style;
        $result = $group->update();
        die(Tools::jsonEncode($result));
    }

    /**
     * get the price after discount of an accessory
     * @param AccessoriesGroupProductAbstract $accessory
     * @param HsMaCartRule $cart_rule
     *
     * @return float
     */
    protected function getDiscountedPrice(AccessoriesGroupProductAbstract $accessory, HsMaCartRule $cart_rule) {

        $base_price = $accessory->getAccessoryPrice();

        if ($cart_rule->reduction_percent) {
            $final_price = $base_price - ($base_price * ($cart_rule->reduction_percent / 100));
        } else if ($cart_rule->reduction_amount) {
            $final_price = $base_price - $cart_rule->reduction_amount;
        } else {
            $final_price = $base_price;
        }

        return $final_price;
    }

    /**
     * Update object AccessoriesGroupProduct.
     *
     * @param int $id_accessory_group_product
     * @param int $required
     *
     * @return bool
     */
    protected function updateAccessoriesGroupProduct($id_accessory_group_product, $required) {

        if (!$id_accessory_group_product) {
            return false;
        }

        $accessory = new AccessoriesGroupProductAbstract((int) $id_accessory_group_product);

        if (!Validate::isLoadedObject($accessory)) {
            return false;
        }

        $accessory->required = (int) $required;

        return $accessory->update();
    }

    /**
     * Process change position of accessories.
     */
    public function ajaxProcessUpdateAccessoryProductPosition() {

        if ($this->tabAccess['edit'] === '0') {
            return die(Tools::jsonEncode(['error' => $this->l('you_do_not_have_the_right_permission')]));
        }

        $flag = false;

        if ($accessories_positions = Tools::getValue('accessories_positions')) {
            $flag = true;
            $accessories_positions = Tools::stripslashes($accessories_positions);
            $array_accessories_positions = Tools::jsonDecode($accessories_positions, true);

            foreach ($array_accessories_positions as $string_ids => $position) {
                $list_ids = explode('_', $string_ids);

                if (isset($list_ids[1])) {
                    $accessory = new AccessoriesGroupProductAbstract((int) $list_ids[1]);
                    $accessory->position = (int) $position;
                    $flag &= $accessory->update();
                }

            }

        }

        if ($flag) {
            $this->ajax_json['success'] = true;
            $this->ajax_json['message'] = $this->l('update_successful');
        } else {
            $this->jsonError(Tools::displayError($this->l('an_error_occurred_while_attempting_to_move_this_accessory')));
        }

    }

    /**
     * Update accessory group.
     */
    public function ajaxProcessupdatePositions() {

        $way = (int) Tools::getValue('way');
        $id_accessory_group = (int) Tools::getValue('id');
        $accessory_group_positions = Tools::getValue('accessory_group');

        if (is_array($accessory_group_positions)) {

            foreach ($accessory_group_positions as $position => $accessory_group) {
                $group = explode('_', $accessory_group);

                if ((isset($group[1]) && isset($group[2])) && (int) $group[2] === $id_accessory_group) {
                    $hs_accessories_group = new AccessoriesGroupAbstract((int) $group[2]);

                    if (Validate::isLoadedObject($hs_accessories_group)) {

                        if (isset($position) && $hs_accessories_group->updatePosition($way, $position)) {
                            $this->ajax_json['success'] = true;
                        }

                    }

                    break; // only need to detect the changed group, other groups' positions will be updated accordingly in AccessoriesGroupAbstract::updatePosition()
                }

            }

        }

        exit(Tools::jsonEncode($this->ajax_json));
    }

    public function initContent() {

        $st_hsmultiaccessories = [
            'lang' => $this->i18n,
        ];
        $this->context->smarty->assign([
            'is_PhenyxShop16'         => true,
            'st_hsmultiaccessories'   => Tools::jsonEncode($st_hsmultiaccessories),
            'category_tree_product'   => $this->getCategoryTree('is_category_filter_product'),
            'category_tree_accessory' => $this->getCategoryTree('is_category_filter_accessory'),
            'groups'                  => AccessoriesGroupAbstract::getGroups($this->context->language->id),
            'buy_together_default'    => MaProductSetting::BUY_TOGETHER_USE_DEFAULT,
            'is_ps17'                 => false,
            'is_product_page'         => false,
        ]);

        parent::initContent();
    }

    public function ajaxProcessFilterByCategories() {

        $this->ajax_json['success'] = false;
        $product_selected_id_categories = Tools::getValue('product_id_categories');
        $accessory_selected_id_categories = Tools::getValue('accessory_id_categories');
        $id_group = Tools::getValue('id_group');

        if (!$product_selected_id_categories || !$accessory_selected_id_categories) {
            exit;
        }

        $product_id_categories = $this->getChildrenCategories($product_selected_id_categories);
        $accessory_id_categories = $this->getChildrenCategories($accessory_selected_id_categories);

        if (empty($product_id_categories) || empty($accessory_id_categories)) {
            exit;
        }

        $products = [];

        if ($product_id_categories) {
            $products = MaSearch::searchProductsByCategories($product_id_categories, $this->context);
        }

        if ($accessory_id_categories) {
            $accessories = MaSearch::searchProductsByCategories($accessory_id_categories, $this->context);
        }

        $id_products = $this->getIdProducts($products);

        if (!empty($id_products)) {
            $product_accessories = AccessoriesGroupAbstract::getAccessoriesByIdGroup($id_group, $id_products, false, (int) $this->context->language->id);
            $group_accessories = [];

            if (!empty($product_accessories)) {
                $group_accessories = $product_accessories[$id_group];
            }

            if (!empty($group_accessories)) {

                foreach ($products as &$product) {
                    $is_added = [];

                    foreach ($group_accessories as $accessory) {
                        $id_product_id_accessories = $accessory['id_product'] . '_' . $accessory['id_accessory'];

                        if ($product['id_product'] == $accessory['id_product'] && !in_array($id_product_id_accessories, $is_added)) {
                            $product['accessories'][] = $accessory;
                            $is_added[] = $id_product_id_accessories;
                        }

                    }

                }

            }

        }

        $this->context->smarty->assign([
            'groups'                => AccessoriesGroupAbstract::getGroupById((int) $id_group, (int) $this->context->language->id),
            'show_custom_quantity'  => (int) Configuration::get('HSMA_SHOW_CUSTOM_QUANTITY'),
            'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'is_PhenyxShop16'       => true,
            'is_ps17'               => false,
            'buy_together_default'  => MaProductSetting::BUY_TOGETHER_USE_DEFAULT,
            'img_path'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/default/img/',
            'products'              => $products,
            'accessories'           => $accessories,
            'id_lang'               => $this->context->language->id,
            'link'                  => $this->context->link,
            'use_tax'               => Configuration::get('PS_TAX') && !Product::getTaxCalculationMethod((int) $this->context->cookie->id_customer),
        ]);
        $this->ajax_json['success'] = true;
        $template_path = 'controllers/products/multiaccessories/product_list.tpl';
        $this->ajax_json['data']['html'] = $this->context->smarty->fetch($template_path);
    }

    /**
     * Get children categories by id parent categories
     * @param array $id_categories
     * @return array
     */
    protected function getChildrenCategories($id_categories) {

        $children_id_categories = [];

        foreach ($id_categories as $id_category) {
            $category = new Category($id_category);
            $categories = $category->getAllChildren();

            foreach ($categories as $cat) {
                $children_id_categories[] = $cat->id_category;
            }

        }

        return array_unique(array_merge($children_id_categories, $id_categories));
    }

    /**
     * Get id_product from list products
     * @param array $products
     * @return array id_products
     */
    protected function getIdProducts($products) {

        $id_products = [];

        if (!empty($products)) {

            foreach ($products as $product) {
                $id_products[] = (int) $product['id_product'];
            }

        }

        return $id_products;
    }

    public function displayAjaxAssignAccessories() {

        $id_products = explode(',', Tools::getValue('id_products'));
        $id_accessories = explode(',', Tools::getValue('id_accessories'));
        $id_group = Tools::getValue('id_group');
        $this->ajax_json['success'] = false;

        if (empty($id_products) || empty($id_accessories) || !$id_group) {
            exit(Tools::jsonEncode($this->ajax_json));
        }

        $this->ajax_json['content'] = [];
        $this->ajax_json['success'] = true;

        foreach ($id_products as $id_product) {
            $id_accessories_exist = AccessoriesGroupProductAbstract::getIdAccessoriesByGroupProduct($id_group, $id_product);
            $id_accessories_without_id_product = array_diff($id_accessories, [$id_product]);
            $id_accessories_to_add = !empty($id_accessories_exist) ? array_diff($id_accessories_without_id_product, $id_accessories_exist) : $id_accessories_without_id_product;

            if (!empty($id_accessories_to_add)) {
                $this->ajax_json['content'][$id_product] = $this->proccessAssignAccessories($id_product, $id_accessories_to_add, $id_group);
            }

        }

        exit(Tools::jsonEncode($this->ajax_json));
    }

    protected function proccessAssignAccessories($id_main_product, $id_accessories, $id_group) {

        foreach ($id_accessories as $id_accessory) {
            $product = new MaProduct($id_accessory);

            if (Validate::isLoadedObject($product)) {
                $accessory = new AccessoriesGroupProductAbstract();
                $accessory->id_accessory = $id_accessory;
                $accessory->id_product = $id_main_product;
                $accessory->id_accessory_group = $id_group;
                $accessory->id_product_attribute = 0;
                $accessory->default_quantity = (int) $product->minimal_quantity;
                $accessory->min_quantity = (int) $accessory->default_quantity;
                $accessory->name = $product->name;
                $accessory->position = AccessoriesGroupProductAbstractAbstract::getHighestPosition($id_group, $id_main_product) + 1;
                $accessory->add();

                $image_products = Image::getImages($this->context->language->id, $id_accessory);
                $image_type = new ImageType((int) Configuration::get('HSMA_ID_IMAGE_TYPE'));
                $product_image_dir = _PS_PROD_IMG_DIR_;

                foreach ($image_products as $image_product) {
                    $image = new Image($image_product['id_image']);
                    $existing_image = $product_image_dir . $image->getExistingImgPath() . '.jpg';

                    if (file_exists($existing_image) && filesize($existing_image)) {

                        if (!file_exists($product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg')) {
                            ImageManager::resize($existing_image, $product_image_dir . $image->getExistingImgPath() . '-' . Tools::stripslashes($image_type->name) . '.jpg', (int) $image_type->width, (int) $image_type->height);
                        }

                    }

                }

                $id_images = MaProduct::getCover($product->id);
                $accessory->image = '';

                if (!empty($id_images)) {
                    $accessory->image = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getImageLink($product->link_rewrite[$this->context->language->id], $id_images['id_image'], MaImageType::getFormatedNameByPsVersion('small')));
                }

                $accessory->combinations = MaProduct::getCombinations($id_accessory, $this->context->shop->id, $this->context->language->id);

                foreach ($accessory->combinations as &$combination) {

                    if (empty($combination['id_image'])) {

                        if (!empty($id_images)) {
                            $combination['id_image'] = $id_images['id_image'];
                        }

                    }

                    $combination['image'] = str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getImageLink($product->link_rewrite[$this->context->language->id], $combination['id_image'], MaImageType::getFormatedNameByPsVersion('small')));
                }

                $accessory->id_accessory_group_product = $accessory->id;
                $specific_price_output = null;
                $accessory->old_price = Product::getPriceStatic($accessory->id_accessory, true, $accessory->id_product_attribute, (int) _PS_PRICE_DISPLAY_PRECISION_, null, false, true, 1, true, null, null, null, $specific_price_output, true, true, $this->context);
                $accessory->cart_rule = MaCartRule::getCartRule((array) $accessory, $id_main_product);
                $accessory->final_price = AccessoriesGroupAbstract::getFinalPrice($accessory->old_price, $accessory->cart_rule);
            }

        }

        $product_accessories = AccessoriesGroupAbstract::getAccessoriesByIdGroup($id_group, [$id_main_product], false, (int) $this->context->language->id);
        $group_accessories = [];

        if (!empty($product_accessories)) {
            $group_accessories = $product_accessories[$id_group];
        }

        $this->context->smarty->assign([
            'groups'                => AccessoriesGroupAbstract::getGroupById((int) $id_group, (int) $this->context->language->id),
            'id_main_product'       => $id_main_product,
            'product_accessories'   => $group_accessories,
            'is_PhenyxShop16'       => true,
            'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
            'show_custom_quantity'  => (int) Configuration::get('HSMA_SHOW_CUSTOM_QUANTITY'),
            'id_lang'               => $this->context->language->id,
            'img_path'              => __PS_BASE_URI__ . $this->admin_webpath . '/themes/default/img/',
            'is_ps17'               => false,
            'buy_together_default'  => MaProductSetting::getBuyTogetherDefault($id_main_product),
        ]);
        return $this->context->smarty->fetch('controllers/products/multiaccessories/setting_display_accessory.tpl');
    }

}
