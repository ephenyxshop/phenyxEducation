<?php

/**
 * Class AdminStoresControllerCore
 *
 * @since 1.8.1.0
 */
class AdminCompanyControllerCore extends AdminController {

    /**
     * AdminStoresControllerCore constructor.
     *
     * @since 1.8.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'company';
        $this->className = 'Company';
        $this->publicName = $this->la('Configuration de la société');
        $this->lang = false;

        $this->context = Context::getContext();

        EmployeeConfiguration::updateValue('EXPERT_COMPANY_SCRIPT', $this->generateParaGridScript(true));
        $this->paragridScript = EmployeeConfiguration::get('EXPERT_COMPANY_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_COMPANY_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_COMPANY_SCRIPT');
        }

        EmployeeConfiguration::updateValue('EXPERT_COMPANY_FIELDS', Tools::jsonEncode($this->getCompanyFields()));
        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_COMPANY_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_COMPANY_FIELDS', Tools::jsonEncode($this->getCompanyFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_COMPANY_FIELDS'), true);
        }

        $countryList = [];
        $countryList[] = ['id' => '0', 'name' => $this->la('Choose your country')];

        foreach (Country::getCountries($this->context->language->id) as $country) {
            $countryList[] = ['id' => $country['id_country'], 'name' => $country['name']];
        }

        $stateList = [];
        $stateList[] = ['id' => '0', 'name' => $this->la('Choose your state (if applicable)')];

        foreach (State::getStates($this->context->language->id) as $state) {
            $stateList[] = ['id' => $state['id_state'], 'name' => $state['name']];
        }

        $this->fields_options = [
            'contact'              => [
                'title'  => $this->la('Identité de l’entreprise'),
                'icon'   => 'icon-user',
                'submit' => ['title' => $this->la('Enregistrer')],
            ],
            'store'                => [
                'title'  => $this->la('Liste des points de ventes'),
                'icon'   => 'icon-map-marker',
                'submit' => ['title' => $this->la('Enregistrer')],
            ],
            'accounting_date'      => [
                'title' => $this->la('Période de saisie'),
                'icon'  => 'icon-user',
            ],
            'accounting_outsiders' => [
                'title' => $this->la('Tiers'),
                'icon'  => 'icon-user',

            ],
            'general'              => [
                'title'  => $this->la('Paramètres'),
                'fields' => [
                    'EPH_STORES_DISPLAY_FOOTER'  => [
                        'title' => $this->la('Display in the footer'),
                        'hint'  => $this->la('Display a link to the store locator in the footer.'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_STORES_DISPLAY_SITEMAP' => [
                        'title' => $this->la('Display in the sitemap page'),
                        'hint'  => $this->la('Display a link to the store locator in the sitemap page.'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_STORES_SIMPLIFIED'      => [
                        'title' => $this->la('Show a simplified store locator'),
                        'hint'  => $this->la('No map, no search, only a store directory.'),
                        'cast'  => 'intval',
                        'type'  => 'bool',
                    ],
                    'EPH_STORES_CENTER_LAT'      => [
                        'title' => $this->la('Default latitude'),
                        'hint'  => $this->la('Used for the initial position of the map.'),
                        'cast'  => 'floatval',
                        'type'  => 'text',
                        'size'  => '10',
                    ],
                    'EPH_STORES_CENTER_LONG'     => [
                        'title' => $this->la('Default longitude'),
                        'hint'  => $this->la('Used for the initial position of the map.'),
                        'cast'  => 'floatval',
                        'type'  => 'text',
                        'size'  => '10',
                    ],
                ],
                'submit' => ['title' => $this->la('Save')],
            ],
            'contact_fo'           => [
                'title'  => $this->la('Contact details'),
                'icon'   => 'icon-user',
                'fields' => [
                    'EPH_SHOP_NAME'       => [
                        'title'      => $this->la('Shop name'),
                        'hint'       => $this->la('Displayed in emails and page titles.'),
                        'validation' => 'isGenericName',
                        'required'   => true,
                        'type'       => 'text',
                        'no_escape'  => true,
                    ],
                    'EPH_SHOP_EMAIL'      => [
                        'title'      => $this->la('Shop email'),
                        'hint'       => $this->la('Displayed in emails sent to customers.'),
                        'validation' => 'isEmail',
                        'required'   => true,
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_DETAILS'    => [
                        'title'      => $this->la('Registration number'),
                        'hint'       => $this->la('Shop registration information (e.g. SIRET or RCS).'),
                        'validation' => 'isGenericName',
                        'type'       => 'textarea',
                        'cols'       => 30,
                        'rows'       => 5,
                    ],
                    'EPH_SHOP_ADDR1'      => [
                        'title'      => $this->la('Shop address line 1'),
                        'validation' => 'isAddress',
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_ADDR2'      => [
                        'title'      => $this->la('Shop address line 2'),
                        'validation' => 'isAddress',
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_CODE'       => [
                        'title'      => $this->la('Zip/postal code'),
                        'validation' => 'isGenericName',
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_CITY'       => [
                        'title'      => $this->la('City'),
                        'validation' => 'isGenericName',
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_COUNTRY_ID' => [
                        'title'        => $this->la('Country'),
                        'validation'   => 'isInt',
                        'type'         => 'select',
                        'list'         => $countryList,
                        'identifier'   => 'id',
                        'cast'         => 'intval',
                        'defaultValue' => (int) $this->context->country->id,
                    ],
                    'EPH_SHOP_STATE_ID'   => [
                        'title'      => $this->la('State'),
                        'validation' => 'isInt',
                        'type'       => 'select',
                        'list'       => $stateList,
                        'identifier' => 'id',
                        'cast'       => 'intval',
                    ],
                    'EPH_SHOP_PHONE'      => [
                        'title'      => $this->la('Phone'),
                        'validation' => 'isGenericName',
                        'type'       => 'text',
                    ],
                    'EPH_SHOP_FAX'        => [
                        'title'      => $this->la('Fax'),
                        'validation' => 'isGenericName',
                        'type'       => 'text',
                    ],
                ],
                'submit' => ['title' => $this->la('Save')],
            ],
        ];

        parent::__construct();

    }

    public function setMedia() {

        parent::setMedia();

        $this->addJS(__EPH_BASE_URI__ . '/js/company.js');
        $this->addCSS(_EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/css/company.css', 'all', 0);
        Media::addJsDef([
            'AjaxLink' . $this->controller_name => $this->context->link->getAdminLink($this->controller_name),
        ]);

    }

    public function initContent() {

        $this->paramGridObj = 'obj' . $this->className;
        $this->paramGridVar = 'grid' . $this->className;
        $this->paramGridId = 'grid_' . $this->controller_name;

        $ajaxlinkMeta = $this->context->link->getAdminLink($this->controller_name);

        $this->TitleBar = $this->la('Stores List');

        $this->context->smarty->assign([
            'controller'      => Tools::getValue('controller'),
            'tableName'       => $this->table,
            'className'       => $this->className,
            'linkController'  => $this->context->link->getAdminLink($this->controller_name),
            'AjaxLink'        => $this->context->link->getAdminLink($this->controller_name),
            'paragridScript'  => $this->generateParaGridScript(),
            'titleBar'        => $this->TitleBar,
            'bo_imgdir'       => _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/',
            'idController'    => '',
            'controller'      => Tools::getValue('controller'),
            'tabScript'       => $this->generateTabScript(Tools::getValue('controller')),
            'gridId'          => 'grid_AdminStores',
            'titleBar'        => $this->TitleBar, dmin_webpath . _EPH_ADMIN_THEME_DIR_.  $this->bo_theme . '/img/',
            'current_id_lang' => $this->context->language->id,
        ]);

        parent::initContent();

    }

    public function generateParaGridScript($regenerate = false) {

        if (!empty($this->paragridScript) && !$regenerate) {
            return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
        }

        $paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
        $paragrid->paramGridId = 'grid_AdminStores';
        $paragrid->paramController = 'AdminCompany';

        $paragrid->height = 700;
        $paragrid->showNumberCell = 0;
        $paragrid->create = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_AdminCompany\', \'AdminCompany\');
        }';
        $paragrid->rowInit = 'function (ui) {
            return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLinkAdminCompany+\'" data-class="Store" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.id_store+\' "\',
            };
        }';
        $paragrid->complete = 'function(){
        window.dispatchEvent(new Event(\'resize\'));
        }';
        $paragrid->selectionModelType = 'row';
        $paragrid->showTitle = 0;

        $paragrid->fillHandle = '\'all\'';

        $option = $paragrid->generateParaGridOption();
        $script = $paragrid->generateParagridScript();

        if ($regenerate) {
            return $script;
        }

        $this->paragridScript = $script;
        return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
    }

    public function generateParaGridOption() {

        return '';

    }

    public function getCompanyRequest() {

        $stores = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('a.* , sl.*, cl.`name` as `country`, st.`name` as `state`')
                ->from('store', 'a')
                ->leftJoin('store_lang', 'sl', 'sl.`id_store` = a.`id_store`')
                ->leftJoin('country_lang', 'cl', 'cl.`id_country` = a.`id_country` AND cl.`id_lang` = ' . (int) $this->context->language->id)
                ->leftJoin('state', 'st', 'st.`id_state` = a.`id_state`')
                ->orderBy('a.`id_store` ASC')
        );
        $storeLink = $this->context->link->getAdminLink($this->controller_name);

        foreach ($stores as &$store) {

            if ($store['active'] == 1) {
                $store['active'] = '<div class="p-active"></div>';
            } else {
                $store['active'] = '<div class="p-inactive"></div>';
            }

        }

        return $stores;
    }

    public function ajaxProcessgetCompanyRequest() {

        die(Tools::jsonEncode($this->getCompanyRequest()));

    }

    public function getCompanyFields() {

        return [
            [
                'title'      => $this->la('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_store',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],

            [
                'title'      => $this->la('Name'),
                'width'      => 200,
                'dataIndx'   => 'name',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'      => $this->la('Email'),
                'width'      => 200,
                'dataIndx'   => 'email',
                'cls'        => 'name-handle',
                'align'      => 'left',
                'editable'   => false,
                'dataType'   => 'string',
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->la('Address'),
                'width'    => 150,
                'dataIndx' => 'address1',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('City'),
                'width'    => 200,
                'dataIndx' => 'city',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Zip/postal code'),
                'width'    => 150,
                'dataIndx' => 'postcode',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('State'),
                'width'    => 200,
                'dataIndx' => 'state',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Country'),
                'width'    => 200,
                'dataIndx' => 'country',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Phone'),
                'width'    => 200,
                'dataIndx' => 'phone',
                'align'    => 'left',
                'editable' => false,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->la('Enabled'),
                'width'    => 200,
                'dataIndx' => 'active',
                'align'    => 'center',
                'editable' => false,
                'dataType' => 'html',
            ],

        ];
    }

    public function ajaxProcessgetCompanyFields() {

        die(EmployeeConfiguration::get('EXPERT_COMPANY_FIELDS'));
    }

    public function renderOptions() {

        if ($this->fields_options && is_array($this->fields_options)) {

            $idCompany = !empty(Configuration::get('EPH_COMPANY_ID')) ? Configuration::get('EPH_COMPANY_ID') : 0;
            $company = new Company($idCompany);
            $stdAccountController = new AdminStdAccountsController();
            $this->tpl_option_vars['stdAccountForm'] = $stdAccountController->renderObjectForm();

            $this->tpl_option_vars['parameters'] = $this->renderParameters();

            $this->tpl_option_vars['titleList'] = $this->la('List') . ' ' . $this->toolbar_title[0];
            $this->tpl_option_vars['controller'] = Tools::getValue('controller');

            $this->tpl_option_vars['company'] = $company;
            $this->tpl_option_vars['countries'] = Country::getCountries($this->context->language->id, true);
            $this->tpl_option_vars['tabScript'] = $this->generateTabScript(Tools::getValue('controller'));
            $this->tpl_option_vars['EPH_STUDENT_AFFECTATION'] = Configuration::get('EPH_STUDENT_AFFECTATION');
            $this->tpl_option_vars['EPH_STUDENT_AFFECTATION_1_TYPE'] = Configuration::get('EPH_STUDENT_AFFECTATION_1_TYPE');
            $this->tpl_option_vars['EPH_STUDENT_COMMON_ACCOUNT'] = Configuration::get('EPH_STUDENT_COMMON_ACCOUNT');
            $this->tpl_option_vars['EPH_STUDENT_COMMON_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_STUDENT_COMMON_ACCOUNT'));

            $this->tpl_option_vars['EPH_SUPPLIER_AFFECTATION'] = Configuration::get('EPH_SUPPLIER_AFFECTATION');
            $this->tpl_option_vars['EPH_SUPPLIER_AFFECTATION_1_TYPE'] = Configuration::get('EPH_STUDENT_AFFECTATION_1_TYPE');
            $this->tpl_option_vars['EPH_SUPPLIER_COMMON_ACCOUNT'] = Configuration::get('EPH_SUPPLIER_COMMON_ACCOUNT');
            $this->tpl_option_vars['EPH_SUPPLIER_COMMON_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_SUPPLIER_COMMON_ACCOUNT'));

            $this->tpl_option_vars['EPH_PROFIT_DEFAULT_ACCOUNT'] = Configuration::get('EPH_PROFIT_DEFAULT_ACCOUNT');
            $this->tpl_option_vars['EPH_STUDENT_DEFAULT_ACCOUNT'] = Configuration::get('EPH_STUDENT_DEFAULT_ACCOUNT');
            $this->tpl_option_vars['EPH_SUPPLIER_DEFAULT_ACCOUNT'] = Configuration::get('EPH_SUPPLIER_DEFAULT_ACCOUNT');
            $this->tpl_option_vars['EPH_PURCHASE_DEFAULT_ACCOUNT'] = Configuration::get('EPH_PURCHASE_DEFAULT_ACCOUNT');
            $this->tpl_option_vars['EPH_COLLECTED_VAT_DEFAULT_ACCOUNT'] = StdAccount::getAccountValueById(Configuration::get('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT'] = StdAccount::getAccountValueById(Configuration::get('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT'));

            $this->tpl_option_vars['EPH_PROFIT_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_PROFIT_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_STUDENT_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_STUDENT_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_SUPPLIER_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_SUPPLIER_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_PURCHASE_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_PURCHASE_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_COLLECTED_VAT_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT'));
            $this->tpl_option_vars['EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT_VALUE'] = StdAccount::getAccountValueById(Configuration::get('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT'));

            $this->tpl_option_vars['ajaxlink'] = $this->context->link->getAdminLink($this->controller_name);

            $helper = new HelperOptions($this);
            $this->setHelperDisplay($helper);
            $helper->toolbar_scroll = true;

            $helper->id = $this->id;
            $helper->tpl_vars = $this->tpl_option_vars;
            $options = $helper->generateOptions($this->fields_options);

            return $options;
        }

        return '';
    }

    public function renderParameters() {

        $idCompany = Configuration::get('EPH_COMPANY_ID');
        $_POST['id_company'] = $idCompany;

        if (!($company = new Company($idCompany))) {
            return '';
        }

        $data = $this->createTemplate('controllers/company/parameters.tpl');

        $data->assign('company', $company);
        $data->assign('EPH_FIRST_ACCOUNT_START', Configuration::get('EPH_FIRST_ACCOUNT_START'));
        $data->assign('EPH_FIRST_ACCOUNT_END', Configuration::get('EPH_FIRST_ACCOUNT_END'));
        $data->assign('EPH_N-1_ACCOUNT_START', Configuration::get('EPH_N-1_ACCOUNT_START'));
        $data->assign('EPH_N-1_ACCOUNT_END', Configuration::get('EPH_N-1_ACCOUNT_END'));
        $data->assign('EPH_N_ACCOUNT_START', Configuration::get('EPH_N_ACCOUNT_START'));
        $data->assign('EPH_N_ACCOUNT_END', Configuration::get('EPH_N_ACCOUNT_END'));
        $data->assign('EPH_N1_ACCOUNT_START', Configuration::get('EPH_N1_ACCOUNT_START'));
        $data->assign('EPH_N1_ACCOUNT_END', Configuration::get('EPH_N1_ACCOUNT_END'));
        $data->assign('EPH_POST_ACCOUNT_START', Configuration::get('EPH_POST_ACCOUNT_START'));
        $data->assign('EPH_POST_ACCOUNT_END', Configuration::get('EPH_POST_ACCOUNT_END'));

        $html = $data->fetch();

        return $html;
    }

    public function ajaxProcessUpdateOutsiders() {

        Configuration::updateValue('EPH_STUDENT_AFFECTATION', Tools::getValue('EPH_STUDENT_AFFECTATION'));
        Configuration::updateValue('EPH_STUDENT_AFFECTATION_1_TYPE', Tools::getValue('EPH_STUDENT_AFFECTATION_1_TYPE'));
        Configuration::updateValue('EPH_STUDENT_COMMON_ACCOUNT', Tools::getValue('EPH_STUDENT_COMMON_ACCOUNT'));
        Configuration::updateValue('EPH_SUPPLIER_AFFECTATION', Tools::getValue('EPH_SUPPLIER_AFFECTATION'));
        Configuration::updateValue('EPH_SUPPLIER_AFFECTATION_1_TYPE', Tools::getValue('EPH_SUPPLIER_AFFECTATION_1_TYPE'));
        Configuration::updateValue('EPH_SUPPLIER_COMMON_ACCOUNT', Tools::getValue('EPH_SUPPLIER_COMMON_ACCOUNT'));
        Configuration::updateValue('EPH_STUDENT_DEFAULT_ACCOUNT', Tools::getValue('EPH_STUDENT_DEFAULT_ACCOUNT'));
        Configuration::updateValue('EPH_PROFIT_DEFAULT_ACCOUNT', Tools::getValue('EPH_PROFIT_DEFAULT_ACCOUNT'));
        Configuration::updateValue('EPH_SUPPLIER_DEFAULT_ACCOUNT', Tools::getValue('EPH_SUPPLIER_DEFAULT_ACCOUNT'));
        Configuration::updateValue('EPH_PURCHASE_DEFAULT_ACCOUNT', Tools::getValue('EPH_PURCHASE_DEFAULT_ACCOUNT'));
        Configuration::updateValue('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT', Tools::getValue('EPH_COLLECTED_VAT_DEFAULT_ACCOUNT'));
        Configuration::updateValue('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT', Tools::getValue('EPH_DEDUCTIBLE_VAT_DEFAULT_ACCOUNT'));

        $result = [
            'success' => true,
            'message' => $this->la('La société a été mise à jour avec succès'),
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessUpdateCompanyParams() {

        Configuration::updateValue('EPH_FIRST_ACCOUNT_START', Tools::getValue('EPH_FIRST_ACCOUNT_START'));
        Configuration::updateValue('EPH_FIRST_ACCOUNT_END', Tools::getValue('EPH_FIRST_ACCOUNT_END'));
        Configuration::updateValue('EPH_N-1_ACCOUNT_START', Tools::getValue('EPH_N-1_ACCOUNT_START'));
        Configuration::updateValue('EPH_N-1_ACCOUNT_END', Tools::getValue('EPH_N-1_ACCOUNT_END'));
        Configuration::updateValue('EPH_N_ACCOUNT_START', Tools::getValue('EPH_N_ACCOUNT_START'));
        Configuration::updateValue('EPH_N_ACCOUNT_END', Tools::getValue('EPH_N_ACCOUNT_END'));
        Configuration::updateValue('EPH_N1_ACCOUNT_START', Tools::getValue('EPH_N1_ACCOUNT_START'));
        Configuration::updateValue('EPH_N1_ACCOUNT_END', Tools::getValue('EPH_N1_ACCOUNT_END'));
        Configuration::updateValue('EPH_POST_ACCOUNT_START', Tools::getValue('EPH_POST_ACCOUNT_START'));
        Configuration::updateValue('EPH_POST_ACCOUNT_END', Tools::getValue('EPH_POST_ACCOUNT_END'));
        $id = (int) Tools::getValue('id_company');

        if (isset($id) && !empty($id)) {
            /** @var ObjectModel $object */
            $object = new $this->className($id);

            if (Validate::isLoadedObject($object)) {
                /* Specific to objects which must not be deleted */
                $oldPasswd = $object->passwd;

                foreach ($_POST as $key => $value) {

                    if (property_exists($object, $key) && $key != 'id_company') {

                        $object->{$key}

                        = $value;
                    }

                }

                $result = $object->update();

                if (!isset($result) || !$result) {
                    $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
                } else {
                    $result = [
                        'success' => true,
                        'message' => $this->la('La société a été mise à jour avec succès'),
                    ];

                }

                Logger::addLog(sprintf($this->la('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $object->id, true, (int) $this->context->employee->id);
            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
            }

        }

        $this->errors = array_unique($this->errors);

        if (count($this->errors)) {
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function updateOptionPsShopCountryId($value) {

        if (!$this->errors && $value) {
            $country = new Country($value, $this->context->language->id);

            if ($country->id) {
                Configuration::updateValue('EPH_SHOP_COUNTRY_ID', $value);
                Configuration::updateValue('EPH_SHOP_COUNTRY', $country->name);
            }

        }

    }

    /**
     * @param mixed $value
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function updateOptionPsShopStateId($value) {

        if (!$this->errors && $value) {
            $state = new State($value);

            if ($state->id) {
                Configuration::updateValue('EPH_SHOP_STATE_ID', $value);
                Configuration::updateValue('EPH_SHOP_STATE', $state->name);
            }

        }

    }

    public function ajaxProcessGetStatesInformation() {

        $idCountry = Tools::getValue('idCountry');
        $vatNumber = false;
        $zone = Country::isCeeMember($idCountry);

        if (Country::isCeeMember($idCountry)) {
            $vatNumber = true;
        }

        $idState = Tools::getValue('idState');

        if (Country::containsStates($idCountry)) {

            $states = State::getStatesByIdCountry($idCountry);

            if (is_array($states) and !empty($states)) {
                $list = '<option value="0">' . $this->la('Choose your state (if applicable)') . '</option>' . "\n";

                foreach ($states as $state) {
                    $list .= '<option value="' . (int) ($state['id_state']) . '" >' . $state['name'] . '</option>' . "\n";
                }

                $result = [
                    'hasState'  => true,
                    'list'      => $list,
                    'vatNumber' => $vatNumber,
                ];

            } else {
                $result = [
                    'hasState'  => false,
                    'vatNumber' => $vatNumber,
                ];

            }

        } else {
            $result = [
                'hasState'  => false,
                'vatNumber' => $vatNumber,
            ];

        }

        die(Tools::jsonEncode($result));

    }

}
