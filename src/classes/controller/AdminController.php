<?php

use PhpOffice\PhpSpreadsheet\Shared\Date;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Thunder\Shortcode\HandlerContainer\HandlerContainer;
use Thunder\Shortcode\Parser\RegularParser;
use Thunder\Shortcode\Processor\Processor;
use Thunder\Shortcode\Shortcode\ShortcodeInterface;

/**
 * Class AdminControllerCore
 *
 * @since 1.9.1.0
 */
class AdminControllerCore extends EphController {

    const LEVEL_VIEW = 1;
    const LEVEL_EDIT = 2;
    const LEVEL_ADD = 3;
    const LEVEL_DELETE = 4;

    // Cache file to make errors/warnings/informations/confirmations
    // survive redirects.
    const MESSAGE_CACHE_PATH = 'AdminControllerMessages.php';

    // @codingStandardsIgnoreStart
    /** @var string */
    public static $currentIndex;
    /** @var array Cache for translations */
    public static $cache_lang = [];

    public static $staticShortcodeHandler;
    /** @var string */
    public $path;
    /** @var string */
    public $content;
    /** @var array */
    public $warnings = [];
    /** @var array */
    public $informations = [];
    /** @var array */
    public $confirmations = [];
    /** @var string|false */
    public $shopShareDatas = false;
    /** @var array */
    public $_languages = [];
    /** @var int */
    public $default_form_language;
    /** @var bool */
    public $allow_employee_form_lang;
    /** @var string */
    public $layout = 'layout.tpl';
    /** @var bool */
    public $bootstrap = false;
    /** @var string */
    public $template = 'content.tpl';
    /** @var string Associated table name */
    public $table = 'configuration';

    public $tableName;

    public $targetController;
    /** @var string */
    public $list_id;
    /** @var string Associated object class name */
    public $className;
    /** @var array */
    public $tabAccess;

    public $tabOnclick;

    protected $tab_link;

    public $TitleBar;

    protected $tab_name;

    public $controllerLink;

    public $editObject;

    protected $closeTabButton;

    protected $identifier_value;

    protected $publicName;

    protected $displayBackOfficeHeader;

    protected $displayBackOfficeFooter;

    protected $tab_identifier;

    protected $scriptHook = true;

    protected $tab_liId;

    public $extracss;

    public $extra_vars;

    public $manageHeaderFields = false;

    public $vatRacines = [];

    protected $ajax_js;
    /** @var int Tab id */
    public $id = -1;
    /** @var bool */
    public $required_database = false;
    /** @var bool */
    public $displayGrid = false;
    /** @var bool */
    public $displayOptionGrid = false;
    /** @var string Security token */
    public $token;
    /** @var string "shop" or "group_shop" */
    public $shopLinkType;
    /** @var array */
    public $tpl_form_vars = [];
    /** @var array */
    public $tpl_list_vars = [];
    /** @var array */
    public $tpl_delete_link_vars = [];
    /** @var array */
    public $tpl_option_vars = [];
    /** @var array */
    public $tpl_view_vars = [];
    /** @var array */
    public $tpl_required_fields_vars = [];
    /** @var string|null */
    public $base_tpl_view = null;
    /** @var string|null */
    public $base_tpl_form = null;
    /** @var bool If you want more fieldsets in the form */
    public $multiple_fieldsets = false;
    /** @var array|false */
    public $fields_value = false;
    /** @var array Errors displayed after post processing */
    public $errors = [];
    /** @var bool Automatically join language table if true */
    public $lang = false;
    /** @var array Required_fields to display in the Required Fields form */
    public $required_fields = [];
    /** @var string */
    public $tpl_folder;
    /** @var array Name and directory where class image are located */
    public $fieldImageSettings = [];
    /** @var string Image type */
    public $imageType = 'jpg';
    /** @var string Current controller name without suffix */
    public $controller_name;
    /** @var int */
    public $multishop_context = -1;
    /** @var false */
    public $multishop_context_group = true;
    /** @var bool Bootstrap variable */
    public $show_page_header_toolbar = false;
    /** @var string Bootstrap variable */
    public $page_header_toolbar_title;
    /** @var array|Traversable Bootstrap variable */
    public $page_header_toolbar_btn = [];
    /** @var bool Bootstrap variable */
    public $show_form_cancel_button;
    /** @var string */
    public $admin_webpath;
    /** @var array */
    public $modals = [];
    /** @var string|array */
    protected $meta_title = [];
    /** @var string|false Object identifier inside the associated table */
    public $identifier = false;
    /** @var string */
    protected $identifier_name = 'name';
    /** @var string Default ORDER BY clause when $_orderBy is not defined */
    protected $_defaultOrderBy = false;
    /** @var string */
    protected $_defaultOrderWay = 'ASC';
    /** @var bool Define if the header of the list contains filter and sorting links or not */
    protected $list_simple_header;
    protected $tabList = false;
    /** @var array List to be generated */
    protected $fields_list;

    protected $fieldsList = false;

    protected $fieldsOptions = false;

    protected $fields_tablist = [];
    /** @var array Modules list filters */
    protected $filter_modules_list = null;
    /** @var array Modules list filters */
    protected $modules_list = [];
    /** @var array Edit form to be generated */
    protected $fields_form;
    /** @var array Override of $fields_form */
    protected $fields_form_override;
    /** @var string Override form action */
    protected $submit_action;
    /** @var array List of option forms to be generated */
    protected $fields_options = [];
    /** @var string */
    protected $shopLink;
    /** @var string SQL query */
    protected $_listsql = '';
    /** @var array Cache for query results */
    protected $_list = [];
    /** @var string|array Toolbar title */
    protected $toolbar_title;
    /** @var array List of toolbar buttons */
    protected $toolbar_btn = null;
    /** @var bool Scrolling toolbar */
    protected $toolbar_scroll = true;
    /** @var bool Set to false to hide toolbar and page title */
    protected $show_toolbar = true;
    /** @var bool Set to true to show toolbar and page title for options */
    protected $show_toolbar_options = false;
    /** @var int Number of results in list */
    protected $_listTotal = 0;
    /** @var array WHERE clause determined by filter fields */
    protected $_filter;
    /** @var string */
    protected $_filterHaving;
    /** @var array Temporary SQL table WHERE clause determined by filter fields */
    protected $_tmpTableFilter = '';
    /** @var array Number of results in list per page (used in select field) */
    protected $_pagination = [20, 50, 100, 300, 1000];
    /** @var int Default number of results in list per page */
    protected $_default_pagination = 50;
    /** @var string ORDER BY clause determined by field/arrows in list header */
    protected $_orderBy;
    /** @var string Order way (ASC, DESC) determined by arrows in list header */
    protected $_orderWay;
    /** @var array List of available actions for each list row - default actions are view, edit, delete, duplicate */
    protected $actions_available = ['view', 'edit', 'duplicate', 'delete'];
    /** @var array List of required actions for each list row */
    protected $actions = [];
    /** @var array List of row ids associated with a given action for witch this action have to not be available */
    protected $list_skip_actions = [];

    public $paragrid_option = [];

    public $paragrid = false;

    public $ajaxOptions;

    public $paramGridObj;

    public $paramGridVar;

    public $paramGridId;
    /* @var bool Don't show header & footer */
    protected $lite_display = false;
    /** @var bool List content lines are clickable if true */
    protected $list_no_link = false;
    /** @var bool */
    protected $allow_export = false;
    /** @var HelperList */
    protected $helper;

    protected $renderTab = false;
    /**
     * Actions to execute on multiple selections.
     *
     * Usage:
     *
     * [
     *      'actionName'    => [
     *      'text'          => $this->l('Message displayed on the submit button (mandatory)'),
     *      'confirm'       => $this->l('If set, this confirmation message will pop-up (optional)')),
     *      'anotherAction' => [...]
     * ];
     *
     * If your action is named 'actionName', you need to have a method named bulkactionName() that will be executed when the button is clicked.
     *
     * @var array
     */
    protected $bulk_actions;
    /* @var array Ids of the rows selected */
    protected $boxes;
    /** @var string Do not automatically select * anymore but select only what is necessary */
    protected $explicitSelect = false;
    /** @var string Add fields into data query to display list */
    protected $_select;
    /** @var string Join tables into data query to display list */
    protected $_join;
    /** @var string Add conditions into data query to display list */
    protected $_where;
    /** @var string Group rows into data query to display list */
    protected $_group;
    /** @var string Having rows into data query to display list */
    protected $_having;
    /** @var string Use SQL_CALC_FOUND_ROWS / FOUND_ROWS to count the number of records */
    protected $_use_found_rows = true;
    /** @var bool */
    protected $is_cms = false;
    /** @var string Identifier to use for changing positions in lists (can be omitted if positions cannot be changed) */
    protected $position_identifier;
    /** @var string|int */
    protected $position_group_identifier;
    /** @var bool Table records are not deleted but marked as deleted if set to true */
    protected $deleted = false;
    /**  @var bool Is a list filter set */
    protected $filter;
    /** @var bool */
    protected $noLink;
    /** @var bool|null */
    protected $specificConfirmDelete = null;
    /** @var bool */
    protected $colorOnBackground;
    /** @var bool If true, activates color on hover */
    protected $row_hover = true;
    /** @var string Action to perform : 'edit', 'view', 'add', ... */
    protected $action;
    /** @var string */
    protected $display;
    /** @var bool */
    protected $_includeContainer = true;
    /** @var array */
    protected $tab_modules_list = ['default_list' => [], 'slider_list' => []];
    /** @var string */
    protected $bo_theme;

    protected $themes;
    /** @var bool Redirect or not after a creation */
    protected $_redirect = true;
    /** @var ObjectModel Instantiation of the class associated with the AdminController */
    protected $object;
    /** @var int Current object ID */
    protected $id_object;
    /** @var array Current breadcrumb position as an array of tab names */
    protected $breadcrumbs;
    /** @var array */
    protected $list_natives_modules = [];
    /** @var array */
    protected $list_partners_modules = [];
    /** @var bool */
    protected $logged_on_addons = false;
    /** @var bool if logged employee has access to AdminImport */
    protected $can_import = false;
    /** @var array */
    protected $translationsTab = [];
    /** @var bool $isEphenyxUp */
    public static $isEphenyxUp = true;
    // @codingStandardsIgnoreEnd
    public $i18n = [];

    protected $_params = [];

    public $configurationField;

    public $activeSelector;

    protected $paragridScript;

    public $idController;

    public $windowHeight = 217;

    public $paramComplete;

    public $paramToolbar = [];

    public $paramTitle;

    public $paramContextMenu;

    public $paramChange = '';

    public $paramPageModel = [
        'type'       => '\'local\'',
        'rPP'        => 100,
        'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
    ];

    public $paramCreate = '';

    public $paramExtraFontcion = '';

    public $paramSelectModelType = 'row';

    public $columnBorders = 0;

    public $rowBorders = 0;

    public $showTop = 1;

    public $rowInit = '';

    public $rowDblClick = '';

    public $filterModel = [
        'on'          => true,
        'mode'        => '\'AND\'',
        'header'      => true,
        'type'        => '\'local\'',
        'menuIcon'    => 0,
        'gridOptions' => [
            'numberCell' => [
                'show' => 0,
            ],
            'width'      => '\'flex\'',
            'flex'       => [
                'one' => true,
            ],
        ],
    ];

    public $summaryData = '';

    public $refresh;

    public $editorBlur;

    public $editModel;

    public $groupModel;

    public $summaryTitle;

    public $sortModel;

    public $beforeSort;

    public $beforeFilter;

    public $beforeTableView;

    public $requestModel;

    public $requestComplementaryModel;

    public $uppervar;

    public $gridAfterLoadFunction;

    public $dropOn = false;

    public $dragOn = false;

    public $dragdiHelper;

    public $dragclsHandle;

    public $moveNode;

    public $dragModel;

    public $dropModel;

    public $treeModel;

    public $treeExpand;

    /**
     * AdminControllerCore constructor.
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function __construct() {

        global $timer_start;
        $this->timer_start = $timer_start;
        // Has to be remove for the next PhenyxShop version
        global $token, $globalShortcodeHandler;

        $messageCachePath = _PS_CACHE_DIR_ . '/' . static::MESSAGE_CACHE_PATH
        . '-' . Tools::getValue('token');

        if (is_readable($messageCachePath)) {
            include $messageCachePath;
            unlink($messageCachePath);
        }

        $this->controller_type = 'admin';
        $this->controller_name = get_class($this);

        if (strpos($this->controller_name, 'Controller')) {
            $this->controller_name = substr($this->controller_name, 0, -10);
        }

        parent::__construct();

        if ($this->multishop_context == -1) {
            $this->multishop_context = Shop::CONTEXT_ALL | Shop::CONTEXT_GROUP | Shop::CONTEXT_SHOP;
        }

        $path = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'themes' . DIRECTORY_SEPARATOR;

        foreach (scandir($path) as $theme) {

            if ($theme[0] != '.' && is_dir($path . $theme) && (@filemtime($path . $theme . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . 'admin-theme.css'))) {
                $this->themes[] = [
                    'id'   => $theme,
                    'name' => ucfirst($theme),
                ];

                // Add all available styles.

            }

        }

        $defaultThemeName = 'blacktie';

        if (defined('_PS_BO_DEFAULT_THEME_') && _PS_BO_DEFAULT_THEME_
            && @filemtime(_PS_BO_ALL_THEMES_DIR_ . _PS_BO_DEFAULT_THEME_ . DIRECTORY_SEPARATOR . 'template')
        ) {
            $defaultThemeName = _PS_BO_DEFAULT_THEME_;
        }

        $this->bo_theme = ((Validate::isLoadedObject($this->context->employee)
            && $this->context->employee->bo_theme) ? $this->context->employee->bo_theme : $defaultThemeName);

        if (!@filemtime(_PS_BO_ALL_THEMES_DIR_ . $this->bo_theme)) {
            $this->bo_theme = $defaultThemeName;
        }

        $this->bo_css = ((Validate::isLoadedObject($this->context->employee)
            && $this->context->employee->bo_css) ? $this->context->employee->bo_css : 'admin-theme.css');

        if (!@filemtime(_PS_BO_ALL_THEMES_DIR_ . $this->bo_theme . DIRECTORY_SEPARATOR . 'css' . DIRECTORY_SEPARATOR . $this->bo_css)) {
            $this->bo_css = 'admin-theme.css';
        }

        $this->context->company = new Company(Configuration::get('EPH_COMPANY_ID'));
        $this->context->smarty->setTemplateDir(
            [
                _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'template',
                _PS_OVERRIDE_DIR_ . 'controllers' . DIRECTORY_SEPARATOR . 'admin' . DIRECTORY_SEPARATOR . 'templates',
            ]
        );

        $this->id = EmployeeMenu::getIdFromClassName($this->controller_name);
        $this->token = Tools::getAdminToken($this->controller_name . (int) $this->id . (int) $this->context->employee->id);
        $token = $this->token;

        $this->activeSelector = '<div class="pq-theme"><select id="activeSelect" class="selectmenu"><option value="">' . $this->l('--Select--') . '</option><option value="0">' . $this->l('Disable') . '</option><option value="1">' . $this->l('Enable') . '</option></select></div>';

        $this->_conf = [
            1  => $this->l('Successful deletion'),
            2  => $this->l('The selection has been successfully deleted.'),
            3  => $this->l('Successful creation'),
            4  => $this->l('Successful update'),
            5  => $this->l('The status has been successfully updated.'),
            6  => $this->l('The settings have been successfully updated.'),
            7  => $this->l('The image was successfully deleted.'),
            8  => $this->l('The module was successfully downloaded.'),
            9  => $this->l('The thumbnails were successfully regenerated.'),
            10 => $this->l('The message was successfully sent to the customer.'),
            11 => $this->l('Comment successfully added'),
            12 => $this->l('Module(s) installed successfully.'),
            13 => $this->l('Module(s) uninstalled successfully.'),
            14 => $this->l('The translation was successfully copied.'),
            15 => $this->l('The translations have been successfully added.'),
            16 => $this->l('The module transplanted successfully to the hook.'),
            17 => $this->l('The module was successfully removed from the hook.'),
            18 => $this->l('Successful upload'),
            19 => $this->l('Duplication was completed successfully.'),
            20 => $this->l('The translation was added successfully, but the language has not been created.'),
            21 => $this->l('Module reset successfully.'),
            22 => $this->l('Module deleted successfully.'),
            23 => $this->l('Localization pack imported successfully.'),
            24 => $this->l('Localization pack imported successfully.'),
            25 => $this->l('The selected images have successfully been moved.'),
            26 => $this->l('Your cover image selection has been saved.'),
            27 => $this->l('The image\'s shop association has been modified.'),
            28 => $this->l('A zone has been assigned to the selection successfully.'),
            29 => $this->l('Successful upgrade'),
            30 => $this->l('A partial refund was successfully created.'),
            31 => $this->l('The discount was successfully generated.'),
            32 => $this->l('Successfully signed in'),
            33 => $this->l('The selected accessory have successfully been moved'),
            34 => $this->l('The selected combination have successfully saved'),
            35 => $this->l('Accessory discount have been successfully saved'),
            36 => $this->l('Accessories have been successfully copied into thie product'),
        ];

        if (!$this->identifier) {
            $this->identifier = 'id_' . $this->table;
        }

        if (!$this->_defaultOrderBy) {
            $this->_defaultOrderBy = $this->identifier;
        }

        $this->tabAccess = Profile::getProfileAccess($this->context->employee->id_profile, $this->id);

        if ($this->controller_name == 'AdminDashboard') {
            $_POST['token'] = $this->token;
        }

        if (!Shop::isFeatureActive()) {
            $this->shopLinkType = '';
        }

        $this->override_folder = Tools::toUnderscoreCase(substr($this->controller_name, 5)) . '/';

        $this->tpl_folder = Tools::toUnderscoreCase(substr($this->controller_name, 5)) . '/';

        $this->initShopContext();

        $this->context->currency = new Currency(Configuration::get('PS_CURRENCY_DEFAULT'));

        $this->admin_webpath = str_ireplace(_PS_CORE_DIR_, '', _PS_ADMIN_DIR_);
        $this->admin_webpath = preg_replace('/^' . preg_quote(DIRECTORY_SEPARATOR, '/') . '/', '', $this->admin_webpath);

        $this->logged_on_addons = false;

        if (isset($this->context->cookie->username_addons) && isset($this->context->cookie->password_addons) && !empty($this->context->cookie->username_addons) && !empty($this->context->cookie->password_addons)) {
            $this->logged_on_addons = true;
        }

        if (isset($this->context->cookie->is_contributor) && (int) $this->context->cookie->is_contributor === 1) {
            $this->context->mode = Context::MODE_STD_CONTRIB;
        } else {
            $this->context->mode = Context::MODE_STD;
        }

        $this->vatRacines = ['40', '41', '6', '7'];

        $this->context->smarty->assign(
            [
                'context_mode'     => $this->context->mode,
                'logged_on_addons' => $this->logged_on_addons,
                'can_import'       => $this->can_import,
            ]
        );

        $this->_params = [
            'shopUrl'     => ShopUrl::getMainShopDomain(),
            'purchaseKey' => Configuration::get('_EPHENYX_LICENSE_KEY_'),
            'UserIpAddr'  => $this->getUserIpAddr(),
            'ephenyxV'    => _EPH_VERSION_,
            'v'           => _PS_VERSION_,
            'lang'        => $this->context->language->iso_code,
        ];

        $this->shortCodeControllers = Tools::jsonDecode(Configuration::get('VC_ENQUEUED_CONTROLLERS'), true);
        $this->seetingsMaps = Tools::buildMaps();

        $this->initTranslations();

        $this->paramCreate = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
    }

    public function generateParaGridScript() {

        $paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);
        $paragrid->paramTable = $this->table;
        $paragrid->paramController = $this->controller_name;

        $paragrid->uppervar = $this->uppervar;

        $paragrid->requestModel = $this->requestModel;
        $paragrid->requestComplementaryModel = $this->requestComplementaryModel;
        $paragrid->windowHeight = $this->windowHeight;
        $paragrid->showNumberCell = 0;
        $paragrid->pageModel = $this->paramPageModel;
        $paragrid->showTop = $this->showTop;

        $paragrid->create = $this->paramCreate;

        $paragrid->refresh = $this->refresh;

        $paragrid->complete = $this->paramComplete;
        $paragrid->selectionModelType = $this->paramSelectModelType;

        $paragrid->toolbar = $this->paramToolbar;

        $paragrid->columnBorders = $this->columnBorders;
        $paragrid->rowBorders = $this->rowBorders;

        $paragrid->filterModel = $this->filterModel;

        $paragrid->editorBlur = $this->editorBlur;

        $paragrid->rowInit = $this->rowInit;
        $paragrid->rowDblClick = $this->rowDblClick;
        $paragrid->change = $this->paramChange;
        $paragrid->showTitle = 1;
        $paragrid->title = $this->paramTitle;
        $paragrid->fillHandle = '\'all\'';
        $paragrid->summaryData = $this->summaryData;
        $paragrid->editModel = $this->editModel;

        $paragrid->sortModel = $this->sortModel;
        $paragrid->beforeSort = $this->beforeSort;
        $paragrid->beforeFilter = $this->beforeFilter;
        $paragrid->beforeTableView = $this->beforeTableView;

        $paragrid->dropOn = $this->dropOn;

        $paragrid->dragOn = $this->dragOn;

        $paragrid->dragdiHelper = $this->dragdiHelper;

        $paragrid->dragclsHandle = $this->dragclsHandle;

        $paragrid->dragModel = $this->dragModel;

        $paragrid->dropModel = $this->dropModel;

        $paragrid->moveNode = $this->moveNode;

        $paragrid->treeModel = $this->treeModel;

        $paragrid->treeExpand = $this->treeExpand;

        $paragrid->groupModel = $this->groupModel;

        $paragrid->summaryTitle = $this->summaryTitle;

        $paragrid->contextMenu = $this->paramContextMenu;

        $paragrid->gridExtraFunction = $this->paramExtraFontcion;

        $paragrid->gridAfterLoadFunction = $this->gridAfterLoadFunction;

        $option = $paragrid->generateParaGridOption();
        $script = $paragrid->generateParagridScript();

        $this->paragridScript = $script;
        return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
    }

    public function generateParaGridOption() {

        return true;

    }

    protected function initTranslations() {

        $group_accessory_link = '';

        if (($this->context->controller instanceof AdminProductsController) || ($this->context->controller instanceof AdminHsMultiAccessoriesGroupProController)) {
            $group_accessory_link = $this->context->link->getAdminLink($this->class_controller_admin_group);
        }

        $this->i18n = [
            'add_to_cart'                                                                                                               => $this->l('Add to cart'),
            'an_error_occurred_while_attempting_to_move_this_accessory'                                                                 => $this->l('An error occurred while attempting to move this accessory.'),
            'select_accessory'                                                                                                          => $this->l('Select an accessory'),
            'there_is_not_any_accessory_group'                                                                                          => sprintf($this->l("There is not any accessory group. Let's %s create the first one. %s"), '<a href="' . $group_accessory_link . '">', '</a>'),
            'id'                                                                                                                        => $this->l('ID'),
            'group_name'                                                                                                                => $this->l('Group name'),
            'active'                                                                                                                    => $this->l('Active'),
            'multi_accessories'                                                                                                         => $this->l('Multi Accessories'),
            'delete_selected_items'                                                                                                     => $this->l('Delete selected items'),
            'display_icon_out_of_stock_at_the_front_end'                                                                                => $this->l('Display icon out of stock at the front end'),
            'checkbox'                                                                                                                  => $this->l('Checkbox'),
            'dropdown'                                                                                                                  => $this->l('Dropdown'),
            'radio'                                                                                                                     => $this->l('Radio'),
            'settings'                                                                                                                  => $this->l('Settings'),
            'display_style'                                                                                                             => $this->l('Display style'),
            'define_how_accessories_look_like_at_product_page'                                                                          => $this->l('Define how accessories look like at product page.'),
            'display_images_along_with_each_accessory'                                                                                  => $this->l('Display images along with each accessory.'),
            'display_price_along_with_each_accessory'                                                                                   => $this->l('Display price along with each accessory.'),
            'tell_your_customers_a_summary'                                                                                             => $this->l('Tell your customers a summary of which accessories to pick up and how much to pay.'),
            'add_an_icon_where_people_can_read_description_instead_of_open_that_accessory'                                              => $this->l('Add an icon where people can read description instead of open that accessory.'),
            'title_of_accessory_block_at_product_page'                                                                                  => $this->l('Title of accessory block at product page.'),
            'show_images'                                                                                                               => $this->l('Show images'),
            'show_price'                                                                                                                => $this->l('Show price'),
            'show_short_description'                                                                                                    => $this->l('Show short description'),
            'show_price_table'                                                                                                          => $this->l('Show price table'),
            'show_total_price_instead_of_the_main_product_price_at_the_product_list_page'                                               => $this->l('Show total price instead of the main product price at the product list page'),
            'show_total_price_main_product_price_required_accessories_price_instead_of_the_main_product_price_at_the_product_list_page' => $this->l('Show total price (main product price + required accessories price) instead of the main product price at the product list page.'),
            'title'                                                                                                                     => $this->l('Title'),
            'save'                                                                                                                      => $this->l('Save'),
            'open_new_tab'                                                                                                              => $this->l('Open in a new tab'),
            'view'                                                                                                                      => $this->l('view'),
            'must_have_accessories'                                                                                                     => $this->l('Must-have accessories'),
            'save_and_stay'                                                                                                             => $this->l('Save and stay'),
            'cancel'                                                                                                                    => $this->l('Cancel'),
            'sub_total'                                                                                                                 => $this->l('Sub total'),
            'you_have_to_select_at_least_1_accessory_in_this_group'                                                                     => $this->l('You have to select at least 1 accessory in this group'),
            'quantity'                                                                                                                  => $this->l('Quantity'),
            'edit_group'                                                                                                                => $this->l('Edit group'),
            'add_a_new_accessory_group'                                                                                                 => $this->l('Add a new accessory group'),
            'default_quantity'                                                                                                          => $this->l('Default qty'),
            'group'                                                                                                                     => $this->l('Group'),
            'name'                                                                                                                      => $this->l('Name'),
            'active'                                                                                                                    => $this->l('Active:'),
            'enabled'                                                                                                                   => $this->l('Enabled'),
            'disabled'                                                                                                                  => $this->l('Disabled'),
            'ok'                                                                                                                        => $this->l('ok'),
            'error'                                                                                                                     => $this->l('error'),
            'search_for_items'                                                                                                          => $this->l('Search for items ...'),
            'search_for_a_product'                                                                                                      => $this->l('Search for a product ...'),
            'accessory_group'                                                                                                           => $this->l('Accessory group'),
            'invalid_characters'                                                                                                        => $this->l('Invalid characters:'),
            'alert_message'                                                                                                             => $this->l('Alert message'),
            'tell_your_customer_when_they_dont_choose_any_accessories_to_buy_together_with_main_product'                                => $this->l('Tell your customer when they don\'t choose any accessories to buy together with main product.'),
            'apply_fancybox_to_images'                                                                                                  => $this->l('Apply Fancybox to images'),
            'show_accessory_images_in_a_fancybox'                                                                                       => $this->l('Show accessory images in a Fancybox.'),
            'image_size_in_fancybox'                                                                                                    => $this->l('Image size in Fancybox'),
            'display_prices_along_with_each_accessory'                                                                                  => $this->l('Display prices along with each accessory.'),
            'change_the_main_item_s_price_accordingly'                                                                                  => $this->l('Change the main item\'s price accordingly'),
            'whenever_an_accessory_is_added_or_removed_the_main_item_s_price_is_changed_and_your_customers_clearly_know_the_amount'     => $this->l('Whenever an accessory is added or removed, the main item\'s price is changed, and your customers clearly know the amount.'),
            'add_custom_quantity_to_basket'                                                                                             => $this->l('Add custom quantity to basket'),
            'allow_customer_add_custom_quantity_of_each_accessory_to_basket'                                                            => $this->l('Allow customer add custom quantity of each accessory to basket.'),
            'allow_your_customers_to_change_item_quantity'                                                                              => $this->l('Allow your customers to change item quantity.'),
            'buy_main_product_accessories_together'                                                                                     => $this->l('Buy main product & accessories together'),
            'tell_your_customers_that_they_need_to_buy_main_product_and_accessories_together'                                           => $this->l('Tell your customers that they need to buy main product and accessories together.'),
            'tell_your_customers_that_this_accessory_is_out_of_stock'                                                                   => $this->l('Tell your customers that this accessory is out of stock'),
            'add_each_accessory_to_basket'                                                                                              => $this->l('Add each accessory to basket'),
            'allow_customer_add_separated_accessory_to_basket'                                                                          => $this->l('Allow customer add separated accessory to basket.'),
            'open_accessories_in_a_new_tab'                                                                                             => $this->l('Open accessories in a new tab'),
            'global_update'                                                                                                             => $this->l('Global update'),
            'select_a_combination_optional'                                                                                             => $this->l('Select a combination (optional)'),
            'click_to_view_details'                                                                                                     => $this->l('Click to view details'),
            'you_must_save_this_product_before_adding_accessories'                                                                      => $this->l('You must save this product before adding accessories'),
            'update_successful'                                                                                                         => $this->l('Update successful'),
            'use_default'                                                                                                               => $this->l('Use default'),
            'accessory_is_out_of_stock'                                                                                                 => $this->l('Oops! This item is out of stock.'),
            'there_is_not_enough_product_in_stock'                                                                                      => $this->l('There is not enough product in stock.'),
            'yes'                                                                                                                       => $this->l('Yes'),
            'you_do_not_have_the_right_permission'                                                                                      => $this->l('You do not have the right permission'),
            'no'                                                                                                                        => $this->l('No'),
            'use_default'                                                                                                               => $this->l('Use default'),
            'let_me_specify'                                                                                                            => $this->l('Let me specify'),
            'buy_main_product_accessory_together'                                                                                       => $this->l('Buy main product accessory together'),
            'product_settings'                                                                                                          => $this->l('Product settings'),
            'required'                                                                                                                  => $this->l('Required?'),
            'if_the_text_displayed_text_when_backordering_is_allowed_in_product_edit_page_is_empty'                                     => $this->l('If the text "Displayed text when backordering is allowed" in product edit page is empty , this message will be displayed.'),
            'displayed_text_when_backordering_is_allowed'                                                                               => $this->l('Displayed text when backordering is allowed'),
            'out_of_stock_but_backordering_is_allowed'                                                                                  => $this->l('Out of stock but backordering is allowed.'),
            'out_of_stock'                                                                                                              => $this->l('Out of stock'),
            'only_use_custom_displayed_names_for_this_product'                                                                          => $this->l('Only use custom displayed names for this product'),
            'otherwise_wherever_that_accessory_is_displayed'                                                                            => $this->l('Otherwise, wherever that accessory is displayed (in Multi Accessories block only), they share the same displayed name.'),
            'advanced_settings_for_this_product_only'                                                                                   => $this->l('Advanced settings (for this product only)'),
            'accessory'                                                                                                                 => $this->l('Accessory'),
            'displayed_name'                                                                                                            => $this->l('Displayed name'),
            'price'                                                                                                                     => $this->l('Price'),
            'min_qty'                                                                                                                   => $this->l('Min qty'),
            'invalid_product'                                                                                                           => $this->l('Invalid product'),
            'oops_something_goes_wrong'                                                                                                 => $this->l('Oops! Something goes wrong!'),
            'min_quantity_must_be_less_than_available_quantity'                                                                         => $this->l('Minimum quantity must be less than available quantity.'),
            'default_quantity_should_be_greater_than_or_equal_to_minimum_quantity'                                                      => $this->l('Default quantity should be greater than or equal to minimum quantity.'),
            'quantity_must_be_greater_than_or_equal_to_minimum_quantity'                                                                => $this->l('Quantity must be greater than or equal to {0}.'),
            'oops_cannot_update_accessory'                                                                                              => $this->l('Oops! Cannot update accessory'),
            'position'                                                                                                                  => $this->l('Position'),
            'action'                                                                                                                    => $this->l('Action'),
            'item_inside'                                                                                                               => $this->l('%s item inside'),
            'items_inside'                                                                                                              => $this->l('%s items inside'),
            'click_to_edit'                                                                                                             => $this->l('Click to edit'),
            'there_is_no_accessory_in_this_group'                                                                                       => $this->l('There is no accessory in this group.'),
            'there_isnt_enough_product_in_stock'                                                                                        => $this->l('There isn\'t enough product in stock.'),
            'discount'                                                                                                                  => $this->l('Discount'),
            'final_price'                                                                                                               => $this->l('Final Price'),
            'amount'                                                                                                                    => $this->l('amount'),
            'percent'                                                                                                                   => $this->l('%'),
            'discount_for_accessory'                                                                                                    => $this->l('Discount for accessory %s'),
            'only_valid_when_buying_with_main_product'                                                                                  => $this->l('Only valid when buying with main product '),
            'can_not_save_cart_rule'                                                                                                    => $this->l('Can\'t save cart rule'),
            'this_rule_is_applied_for_product_level'                                                                                    => $this->l('This rule is applied for product level'),
            'copy_accessories_from'                                                                                                     => $this->l('Copy accessories from'),
            'copy_accessories'                                                                                                          => $this->l('Copy accessories'),
            'you_are_about_to_copy_accessories_from_another_product_to_this_product'                                                    => $this->l('You are about to copy accessories from another product to this product. Do you want to keep current accessories of this product?'),
            'cannot_copy_accessories'                                                                                                   => $this->l('Cannot copy accessories'),
            'invalid_product'                                                                                                           => $this->l('Invalid product'),
            'yes'                                                                                                                       => $this->l('Yes'),
            'no'                                                                                                                        => $this->l('No'),
            'cancel'                                                                                                                    => $this->l('Cancel'),
            'none'                                                                                                                      => $this->l('None'),
            'display_combination_info_in_price_table'                                                                                   => $this->l('Display combination info in price table'),
            'collapse_expand_accessory_groups'                                                                                          => $this->l('Collapse/expand accessory groups'),
            'expand_all_groups'                                                                                                         => $this->l('Expand all groups'),
            'expand_the_first_group'                                                                                                    => $this->l('Expand the first group'),
            'collapse_all_groups'                                                                                                       => $this->l('Collapse all groups'),
            'free'                                                                                                                      => $this->l('Free'),
            'there_was_a_connecting_problem'                                                                                            => $this->l('There was a connecting problem. Please check your internet connection and try again.'),
            'request_time_out'                                                                                                          => $this->l('Request time out.'),
            'requested_page_not_found'                                                                                                  => $this->l('Requested page not found.'),
            'internal_server_error'                                                                                                     => $this->l('Internal server error.'),
            'ajax_request_is_aborted'                                                                                                   => $this->l('Ajax request is aborted.'),
            'add_multi_accessories_for_multi_products'                                                                                  => $this->l('Add multi accessories for multi products'),
            'select_categories_products'                                                                                                => $this->l('Select categories products'),
            'filter_by_category'                                                                                                        => $this->l('Filter by category'),
            'select_all'                                                                                                                => $this->l('Select all'),
            'product_name'                                                                                                              => $this->l('Product name'),
            'image'                                                                                                                     => $this->l('Image'),
            'products'                                                                                                                  => $this->l('Products'),
            'accessories'                                                                                                               => $this->l('Accessories'),
            'accessory_name'                                                                                                            => $this->l('Accessory name'),
            'assign'                                                                                                                    => $this->l('Assign'),
            'product_categories'                                                                                                        => $this->l('Product categories'),
            'accessory_categories'                                                                                                      => $this->l('Accessory categories'),
            'select_an_accessory_group'                                                                                                 => $this->l('Select an accessory group'),
            'get_products_accessories'                                                                                                  => $this->l('Get products and accessories'),
            'please_select_at_least_1_accessory'                                                                                        => $this->l('Please select at least 1 accessory.'),
            'please_select_at_least_1_accessory_category'                                                                               => $this->l('Please select at least 1 accessory category.'),
            'please_select_at_least_1_product'                                                                                          => $this->l('Please select at least 1 product.'),
            'please_select_at_least_1_product_category'                                                                                 => $this->l('Please select at least 1 product category.'),
            'please_select_a_group_accessory'                                                                                           => $this->l('Please select a group accessory.'),
            'there_is_no_product'                                                                                                       => $this->l('There is no product.'),
            'there_is_no_accessory'                                                                                                     => $this->l('There is no accessory.'),
            'display_accessories_out_of_stock_at_the_front_end'                                                                         => $this->l('Display accessories & combinations out of stock at the front end'),
            'display_or_hide_accessories_out_of_stock_at_the_front_end'                                                                 => $this->l('Display or hide accessories & combinations out of stock at the front end'),
            'please_uncheck_all_categories_after_that_select_1_or_2_categories_and_filter_again'                                        => $this->l('Please uncheck all categories after that select 1 or 2 categories and filter again.'),
        ];
        $this->context->smarty->assign('hs_i18n', $this->i18n);
    }

    public function getEaLang() {

        $lang['page_title'] = 'Prendre rendez-vous avec ...';
        $lang['service_and_provider'] = 'Choisissez une prestation et un ex??cutant';
        $lang['select_service'] = 'Choisissez une prestation';
        $lang['select_provider'] = 'Choisissez un ex??cutant';
        $lang['duration'] = 'Dur??e';
        $lang['minutes'] = 'Minutes';
        $lang['price'] = 'Prix';
        $lang['back'] = 'Retour';
        $lang['appointment_date_and_time'] = 'Choisissez la date et l\'heure de votre rendez-vous';
        $lang['no_available_hours'] = 'Il n\'y a pas d\'heures de rendez-vous disponibles pour la date s??lectionn??e. Choisissez une autre date s\'il vous pla??t.';
        $lang['appointment_hour_missing'] = 'S\'il vous pla??t, choisissez une heure de rendez-vous avant de pouvoir poursuivre.';
        $lang['customer_information'] = 'Remplissez vos informations';
        $lang['first_name'] = 'Pr??nom';
        $lang['last_name'] = 'Nom';
        $lang['email'] = 'Email';
        $lang['phone_number'] = 'Num??ro de t??l??phone';
        $lang['phone'] = 'Phone';
        $lang['address'] = 'Adresse';
        $lang['city'] = 'Ville';
        $lang['zip_code'] = 'Code postal';
        $lang['notes'] = 'Commentaires';
        $lang['language'] = 'Langue';
        $lang['no_language'] = 'Pas de langue';
        $lang['fields_are_required'] = 'Les champs avec * sont obligatoires';
        $lang['appointment_confirmation'] = 'Confirmez votre rendez-vous';
        $lang['confirm'] = 'Confirmation';
        $lang['update'] = 'Mise ?? jour';
        $lang['cancel_appointment_hint'] = 'Appuyer sur le bouton "Annuler" pour supprimer un rendez-vous de l\'agenda.';
        $lang['cancel'] = 'Annuler';
        $lang['appointment_registered'] = 'Votre rendez-vous a ??t?? enregistr?? avec succ??s .';
        $lang['cancel_appointment_title'] = 'Annuler le rendez-vous';
        $lang['appointment_cancelled'] = 'Votre rendez-vous a bien ??t?? annul?? .';
        $lang['appointment_cancelled_title'] = 'Rendez-vous annul??';
        $lang['reason'] = 'Motif';
        $lang['appointment_removed_from_schedule'] = 'Le rendez-vous suivant a ??t?? supprim?? de l\'agenda.';
        $lang['appointment_details_was_sent_to_you'] = 'Un email reprenant les d??tails de votre rendez-vous vient de vous ??tre envoy??.';
        $lang['add_to_google_calendar'] = 'Ajouter ?? Google Calendar';
        $lang['appointment_booked'] = 'Votre rendez-vous a ??t?? confirm?? avec succ??s .';
        $lang['thank_you_for_appointment'] = 'Merci de votre prise de rendez-vous avec nous. Vous trouvez ci-joint les d??tails de votre rendez-vous. Si n??cessaire, faites les changements souhait??s en cliquant sur le lien du rendez-vous.';
        $lang['appointment_details_title'] = 'D??tails du rendez-vous';
        $lang['customer_details_title'] = 'Informations client';
        $lang['service'] = 'Prestations';
        $lang['provider'] = 'Ex??cutant';
        $lang['customer'] = 'Client';
        $lang['start'] = 'D??but';
        $lang['end'] = 'Fin';
        $lang['name'] = 'Nom';
        $lang['appointment_link_title'] = 'Lien du rendez-vous';
        $lang['success'] = 'R??ussi .';
        $lang['appointment_added_to_google_calendar'] = 'Votre rendez-vous a ??t?? ajout?? ?? votre compte calendrier Google.';
        $lang['view_appointment_in_google_calendar'] = 'Cliquez ici pour voir votre rendez-vous dans le calendrier Google.';
        $lang['appointment_added_to_your_plan'] = 'Un nouveau rendez-vous a ??t?? ajout?? ?? votre planning.';
        $lang['appointment_link_description'] = 'Vous pouvez faires des modifications en cliquant sur le lien suivant.';
        $lang['appointment_locked'] = 'Modification impossible .';
        $lang['appointment_locked_message'] = 'Le rendez-vous ne peut pas ??tre modifi?? moins de {$limit} heures avant.';
        $lang['appointment_not_found'] = 'Rendez-vous introuvable .';
        $lang['appointment_does_not_exist_in_db'] = 'Le rendez-vous demand?? n\'existe plus dans la base de donn??es syst??me.';
        $lang['display_calendar'] = 'Afficher le calendrier.';
        $lang['calendar'] = 'Calendrier';
        $lang['users'] = 'Utilisateurs';
        $lang['settings'] = 'Param??tres';
        $lang['log_out'] = 'D??connexion';
        $lang['synchronize'] = 'Synchronisation';
        $lang['enable_sync'] = 'Activer la synchronisation';
        $lang['disable_sync'] = 'D??sactiver la synchronisation';
        $lang['disable_sync_prompt'] = 'Are you sure that you want to disable the calendar synchronization?';
        $lang['reload'] = 'Actualiser';
        $lang['appointment'] = 'Rendez-vous';
        $lang['unavailable'] = 'Indisponible';
        $lang['week'] = 'Semaine';
        $lang['month'] = 'Mois';
        $lang['today'] = 'Ajourd\'hui';
        $lang['not_working'] = 'Pas en fonction';
        $lang['break'] = 'Pause';
        $lang['add'] = 'Ajouter';
        $lang['edit'] = 'Editer';
        $lang['hello'] = 'Bonjour';
        $lang['all_day'] = 'Toute la journ??e';
        $lang['manage_appointment_record_hint'] = 'G??rer tous les enregistrements de rendez-vous des ex??cutants et des prestations actives.';
        $lang['select_filter_item_hint'] = 'Choisir un ex??cutant ou une prestation et visualisez les rendez-vous sur l\'agenda.';
        $lang['enable_appointment_sync_hint'] = 'Activer la synchronisation des rendez-vous avec le calendrier Google de l\'Ex??cutant.';
        $lang['manage_customers_hint'] = 'G??rer les clients enregistr??s et voir leur historique de rendez-vous.';
        $lang['manage_services_hint'] = 'G??rer les prestations et les cat??gories actives du syst??me.';
        $lang['manage_users_hint'] = 'G??rer les utilisateurs back office (administrateurs, ex??cutant, secr??taires).';
        $lang['settings_hint'] = 'R??gler les param??tres syst??me et utilisateurs.';
        $lang['log_out_hint'] = 'D??connexion du syst??me.';
        $lang['unavailable_periods_hint'] = 'Durant les p??riodes d\'indisponibilit?? l\'Ex??cutant n\'acceptera pas de nouvelle prestation.';
        $lang['new_appointment_hint'] = 'Cr??er un nouveau rendez-vous et stocker le dans la base de donn??es.';
        $lang['reload_appointments_hint'] = 'Actualiser le calendrier des rendez-vous.';
        $lang['trigger_google_sync_hint'] = 'D??marrer la proc??dure de synchronisation du calendrier Google.';
        $lang['appointment_updated'] = 'Rendez-vous mis ?? jour avec succ??s .';
        $lang['undo'] = 'D??faire';
        $lang['appointment_details_changed'] = 'Les d??tails du rendez-vous ont bien ??t?? modifi??s.';
        $lang['appointment_changes_saved'] = 'Les modifications du rendez-vous ont ??t?? enregistr??es .';
        $lang['save'] = 'Enregistrer';
        $lang['new'] = 'Nouveau';
        $lang['select'] = 'Choisir';
        $lang['hide'] = 'Cacher';
        $lang['type_to_filter_customers'] = 'Filtrer les clients.';
        $lang['clear_fields_add_existing_customer_hint'] = 'Effacer tous les champs et entrer un nouveau client.';
        $lang['pick_existing_customer_hint'] = 'Rechercher un client existant.';
        $lang['new_appointment_title'] = 'Nouveau rendez-vous';
        $lang['edit_appointment_title'] = 'Editer rendez-vous';
        $lang['delete_appointment_title'] = 'Effacer rendez-vous';
        $lang['write_appointment_removal_reason'] = 'S\'il vous pla??t, veuillez bien prendre quelques secondes pour nous expliquer les raisons de l\'annulation du rendez-vous.';
        $lang['appointment_saved'] = 'Rendez-vous sauvegard?? avec succ??s .';
        $lang['new_unavailable_title'] = 'Nouvelle p??riode d\'indisponibilit??';
        $lang['edit_unavailable_title'] = 'Editer une p??riode d\'indisponibilit??';
        $lang['unavailable_saved'] = 'P??riode d\'indisponibilit?? sauvegard??e avec succ??s .';
        $lang['start_date_before_end_error'] = 'La date de d??but est ult??rieure ?? la date de fin .';
        $lang['invalid_duration'] = 'Invalid duration.';
        $lang['invalid_email'] = 'Adresse email non valide .';
        $lang['customers'] = 'Clients';
        $lang['details'] = 'D??tails';
        $lang['no_records_found'] = 'Pas d\'enregistrement trouv??...';
        $lang['services'] = 'Prestations';
        $lang['duration_minutes'] = 'Dur??e (Minutes)';
        $lang['currency'] = 'Monnaie';
        $lang['category'] = 'Cat??gorie';
        $lang['no_category'] = 'Pas de cat??gorie';
        $lang['description'] = 'Description';
        $lang['categories'] = 'Cat??gories';
        $lang['admins'] = 'Administrateurs';
        $lang['providers'] = 'Ex??cutants';
        $lang['secretaries'] = 'Secr??taires';
        $lang['mobile_number'] = 'T??l??phone portable';
        $lang['mobile'] = 'Mobile';
        $lang['state'] = '??tat / Pays';
        $lang['username'] = 'Nom d\'utilisateur';
        $lang['password'] = 'Mot de passe';
        $lang['retype_password'] = 'R??inscription du mot de passe';
        $lang['receive_notifications'] = 'Recevoir les notifications';
        $lang['passwords_mismatch'] = 'Les 2 mots de passe ne correspondent pas .';
        $lang['admin_saved'] = 'Administrateur enregistr?? avec succ??s .';
        $lang['provider_saved'] = 'Ex??cutant enregistr?? avec succ??s .';
        $lang['secretary_saved'] = 'Secr??taire enregistr??e avec succ??s .';
        $lang['admin_deleted'] = 'Administrateur supprim?? avec succ??s .';
        $lang['provider_deleted'] = 'Ex??cutant supprim?? avec succ??s .';
        $lang['secretary_deleted'] = 'Secr??taire supprim??e avec succ??s .';
        $lang['service_saved'] = 'Prestation enregistr??e avec succ??s .';
        $lang['service_category_saved'] = 'Cat??gorie de prestation enregistr??e avec succ??s .';
        $lang['service_deleted'] = 'Prestation supprim??e avec succ??s .';
        $lang['service_category_deleted'] = 'Cat??gorie de prestation supprim??e avec succ??s .';
        $lang['customer_saved'] = 'Client enregistr?? avec succ??s .';
        $lang['customer_deleted'] = 'Client supprim?? avec succ??s .';
        $lang['current_view'] = 'Vue normale';
        $lang['working_plan'] = 'Planning de travail';
        $lang['reset_plan'] = 'Red??marrer le planning';
        $lang['monday'] = 'Lundi';
        $lang['tuesday'] = 'Mardi';
        $lang['wednesday'] = 'Mercredi';
        $lang['thursday'] = 'Jeudi';
        $lang['friday'] = 'Vendredi';
        $lang['saturday'] = 'Samedi';
        $lang['sunday'] = 'Dimanche';
        $lang['breaks'] = 'Pauses';
        $lang['add_breaks_during_each_day'] = 'Ajoutez ici les p??riodes de pause pour chaque jour. Pendant ces pauses l\'ex??cutant n\'acceptera pas de rendez-vous...';
        $lang['day'] = 'Jour';
        $lang['days'] = 'Jours';
        $lang['actions'] = 'Actions';
        $lang['reset_working_plan_hint'] = 'Restaurer les valeurs d\'origine du planning de travail.';
        $lang['company_name'] = 'Nom de la soci??t??';
        $lang['company_name_hint'] = 'Le nom de la soci??t?? sera affich?? et utilis?? un peu partout dans le syst??me (obligatoire).';
        $lang['company_email'] = 'Email de la soci??t??';
        $lang['company_email_hint'] = 'Ceci sera l\'adresse email de la soci??t??. Elle sera utilis??e comme adresse d\'envoi et de r??ponse par le syst??me de messagerie ??lectronique (obligatoire).';
        $lang['company_link'] = 'Site web de la soci??t??';
        $lang['company_link_hint'] = 'Le lien de la soci??t?? doit pointer vers le site web officiel de la soci??t?? (obligatoire).';
        $lang['go_to_booking_page'] = 'Aller ?? la page de rendez-vous';
        $lang['settings_saved'] = 'Param??tres sauvegard??s avec succ??s .';
        $lang['general'] = 'G??n??ral';
        $lang['business_logic'] = 'Logique commerciale';
        $lang['current_user'] = 'Utilisateur actuel';
        $lang['about_app'] = 'Au sujet d\'Easy!Appointments';
        $lang['edit_working_plan_hint'] = 'Indiquer ici les jours et les heures pendant lesquels votre soci??t?? accepte les rendez-vous. Il est possible de fixer vous-m??me un rendez-vous en dehors des heures de travail tandis que les clients ne pourront pas prendre d\'eux-m??mes un rendez-vous en dehors des p??riodes de travail indiqu??es ici. Ce planning de travail sera celui propos?? par d??faut pour chaque nouvel enregistrement. Toutefois il vous sera possible de changer s??par??ment chaque planning de travail individuel en l\'??ditant. Apr??s cela vous pouvez encore ajouter les p??riodes de pause.';
        $lang['edit_breaks_hint'] = 'Indiquer ici les p??riodes des pauses quotidiennes. Ces pauses seront disponibles ?? chaque nouvel ex??cutant.';
        $lang['book_advance_timeout'] = 'Param??tres de r??servation';
        $lang['book_advance_timeout_hint'] = 'Les r??servations ne peuvent pas ??tre cr????es, modifi??es ou annul??es moins de ## heures avant le rendez-vous.';
        $lang['timeout_minutes'] = 'D??lai de r??servation (en minutes)';
        $lang['about_app_info'] = 'Easy!Appointments est une application Web hautement personnalisable qui permet ?? vos clients de prendre rendez-vous avec vous via le web. En outre, elle offre la possibilit?? de synchroniser vos donn??es avec un calendrier Google afin que vous puissiez les utiliser avec d\'autres services. Easy!Appointments est un projet open source et vous pouvez le t??l??charger et l\'installer m??me pour un usage commercial. Easy!Appointments fonctionnera sans probl??me avec votre site web existant car il peut ??tre install?? dans un dossier sp??cifique du serveur et bien s??r, les deux sites peuvent partager la m??me base de donn??es.';
        $lang['current_version'] = 'Version actuelle';
        $lang['support'] = 'Support';
        $lang['about_app_support'] = 'Si vous rencontrez des probl??mes pour installer ou configurer l\'application, allez chercher les r??ponses dans le groupe Google officiel. Vous pouvez ??galement avoir besoin de cr??er une demande sur la page code de Google pour permettre l\'avanc??e du projet.';
        $lang['official_website'] = 'Site Web officiel';
        $lang['google_plus_community'] = 'Communaut?? Google+';
        $lang['support_group'] = 'Groupe de soutient';
        $lang['project_issues'] = 'Questions sur le projet';
        $lang['license'] = 'Licence';
        $lang['about_app_license'] = 'Easy!Appointments est enregistr?? sous licence GPLv3. En utilisant le code d\'Easy!Appointments, quelqu\'en soit l\'usage, vous ??tes tenu d\'accepter les termes d??crits dans l\'URL suivante:';
        $lang['logout_success'] = 'Vous avez bien ??t?? d??connect?? ! Cliquez sur l\'un des boutons suivants pour naviguer dans les diff??rentes pages';
        $lang['book_appointment_title'] = 'Carnet de rendez-vous';
        $lang['backend_section'] = 'Section back office';
        $lang['you_need_to_login'] = 'Bonjour ! Vous devez vous connecter pour voir les pages back office.';
        $lang['enter_username_here'] = 'Entrez votre nom d\'utilisateur ici ...';
        $lang['enter_password_here'] = 'Entrez votre mot de passe ici ...';
        $lang['login'] = 'Connexion ';
        $lang['forgot_your_password'] = 'Mot de passe oubli?? ?';
        $lang['login_failed'] = 'La connexion a ??chou??. S\'il vous plait entrez les informations d\'identification correctes et r??-essayez.';
        $lang['type_username_and_email_for_new_password'] = 'Inscrivez votre nom d\'utilisateur et adresse email pour recevoir un nouveau mot de passe.';
        $lang['enter_email_here'] = 'Entrez votre email ici ...';
        $lang['regenerate_password'] = 'R??g??n??ration du mot de passe';
        $lang['go_to_login'] = 'Retourner ?? la page de connexion';
        $lang['new_password_sent_with_email'] = 'Votre nouveau mot de passe vous a ??t?? envoy?? par email.';
        $lang['new_account_password'] = 'Nouveau mot de passe du compte';
        $lang['new_password_is'] = 'Votre nouveau mot de passe est $password. Conservez cet email afin de pouvoir retrouver votre mot de passe si n??cessaire. Vous pouvez aussi modifier ce mot de passe par un nouveau dans la page des param??tres.';
        $lang['delete_record_prompt'] = '??tes-vous s??r de vouloir supprimer cet enregistrement ? Cette action est irr??versible .';
        $lang['delete_admin'] = 'Supprimer l\'administrateur';
        $lang['delete_customer'] = 'Supprimer le client';
        $lang['delete_service'] = 'Supprimer la prestation';
        $lang['delete_category'] = 'Supprimer la cat??gorie de prestation';
        $lang['delete_provider'] = 'Supprimer un ex??cutant';
        $lang['delete_secretary'] = 'Supprimer une secr??taire';
        $lang['delete_appointment'] = 'Supprimer un rendez-vous';
        $lang['delete_unavailable'] = 'Supprimer une p??riode d\'indisponibilit??';
        $lang['delete'] = 'Supprimer';
        $lang['unexpected_issues'] = 'R??sultats inattendus';
        $lang['unexpected_issues_message'] = 'L\'op??ration n\'a pu ??tre termin??e ?? cause de r??sultats inattendus.';
        $lang['close'] = 'Fermer';
        $lang['page_not_found'] = 'Page non trouv??e';
        $lang['page_not_found_message'] = 'Malheureusement la page demand??e n\'existe pas. V??rifiez l\'URL de votre navigateur ou naviguez vers une autre page en utilisant les boutons ci-dessous.';
        $lang['error'] = 'Erreur';
        $lang['no_privileges'] = 'Aucun privil??ges';
        $lang['no_privileges_message'] = 'Vous n\'avez pas les privil??ges n??cessaires pour voir cette page. Veuillez s\'il vous pla??t naviguez vers une section diff??rente.';
        $lang['backend_calendar'] = 'Back office du calendrier';
        $lang['start_date_time'] = 'Date/Heure de D??but';
        $lang['end_date_time'] = 'Date/Heure de Fin';
        $lang['licensed_under'] = 'Licenci?? sous';
        $lang['unexpected_issues_occurred'] = 'Une erreur inattendue est survenue .';
        $lang['service_communication_error'] = 'Erreur de communication avec le serveur, Veuillez s\'il vous plait r??essayer.';
        $lang['no_privileges_edit_appointments'] = 'Vous n\'avez pas les privil??ges n??cessaires pour modifier les rendez-vous.';
        $lang['unavailable_updated'] = 'La p??riode d\'indisponibilit?? a bien ??t?? actualis??e .';
        $lang['appointments'] = 'Rendez-vous';
        $lang['unexpected_warnings'] = 'Avertissements inattendus';
        $lang['unexpected_warnings_message'] = 'Op??ration termin??e mais des avertissements sont survenus.';
        $lang['filter'] = 'Filtrer';
        $lang['clear'] = 'Effacer';
        $lang['uncategorized'] = 'Non cat??goris??';
        $lang['username_already_exists'] = 'Ce nom d\'utilisateur existe d??j??.';
        $lang['password_length_notice'] = 'Le mot de passe doit avoir au moins $number caract??res de long.';
        $lang['general_settings'] = 'Param??tres g??n??raux';
        $lang['personal_information'] = 'Informations personnelles';
        $lang['system_login'] = 'Connexion syst??me';
        $lang['user_settings_are_invalid'] = 'Param??tres utilisateur non valides ! V??rifiez vos param??tres et essayez de nouveau.';
        $lang['add_break'] = 'Ajouter une Pause';
        $lang['january'] = 'Janvier';
        $lang['february'] = 'F??vrier';
        $lang['march'] = 'Mars';
        $lang['april'] = 'Avril';
        $lang['may'] = 'Mai';
        $lang['june'] = 'Juin';
        $lang['july'] = 'Juillet';
        $lang['august'] = 'Ao??t';
        $lang['september'] = 'Septembre';
        $lang['october'] = 'Octobre';
        $lang['november'] = 'Novembre';
        $lang['december'] = 'D??cembre';
        $lang['previous'] = 'Pr??c??dent';
        $lang['next'] = 'Suivant';
        $lang['now'] = 'Maintenant';
        $lang['select_time'] = 'Choisir l\'Heure';
        $lang['time'] = 'Heure du RDV';
        $lang['hour'] = 'Heure';
        $lang['minute'] = 'Minute';
        $lang['google_sync_completed'] = 'La synchronisation Google s\'est termin??e avec succ??s .';
        $lang['google_sync_failed'] = 'La synchronisation Google a ??chou?? : ??chec de connexion avec le serveur.';
        $lang['select_google_calendar'] = 'Choisir un calendrier Google';
        $lang['select_google_calendar_prompt'] = 'S??lectionnez le calendrier souhait?? pour synchroniser votre rendez-vous. Si vous ne s??lectionnez pas de calendrier sp??cifique, le calendrier par d??faut sera s??lectionn?? pour vous.';
        $lang['google_calendar_selected'] = 'Le calendrier Google a ??t?? s??lectionn?? avec succ??s .';
        $lang['oops_something_went_wrong'] = 'Oups ! Une erreur s\'est produite .';
        $lang['could_not_add_to_google_calendar'] = 'Votre rendez-vous ne peux pas ??tre ajout?? ?? votre Calendrier Google.';
        $lang['ea_update_success'] = 'Easy!Appointments ?? ??t?? mis ?? jour avec succ??s .';
        $lang['require_captcha'] = 'CAPTCHA obligatoire';
        $lang['require_captcha_hint'] = 'Lorsque l\'option est activ??e, les clients doivent taper un code de v??rification CAPTCHA avant de pouvoir r??server ou mettre ?? jour un rendez-vous.';
        $lang['captcha_is_wrong'] = 'Le code de v??rification CAPTCHA est erron??, merci de r??essayer.';
        $lang['any_provider'] = 'Toute personne disponible';
        $lang['requested_hour_is_unavailable'] = 'Cette heure de rendez n\'est malheureusement pas disponible. Merci de s??lectionner une autre heure pour votre rendez-vous.';
        $lang['customer_notifications'] = 'Notifications aux clients';
        $lang['customer_notifications_hint'] = 'D??finit si les clients re??oivent des notifications par email chaque fois qu\'il y a un changement d\'horaire de l\'un de leurs rendez-vous.';
        $lang['date_format'] = 'Format des Dates';
        $lang['date_format_hint'] = 'Change le format d\'affichage des dates (D - Jour, M - Mois, Y - Ann??e).';
        $lang['time_format'] = 'Format de l\'Heure';
        $lang['time_format_hint'] = 'Change le format d\'affichage de l\'Heure (H - Heures, M - Minutes).';
        $lang['first_weekday'] = 'Premier jour de la semaine';
        $lang['first_weekday_hint'] = 'D??finit le premier jour de la semaine calendaire.';
        $lang['google_analytics_code_hint'] = 'Renseigner l\'ID Google Analytics ?? utiliser dans la page des r??servations.';
        $lang['availabilities_type'] = 'Type de disponibilit??s';
        $lang['flexible'] = 'Flexible';
        $lang['fixed'] = 'Fixe';
        $lang['attendants_number'] = 'Nombre de participants';
        $lang['reset_working_plan'] = 'Restaurer les valeurs d\'origine du planning de travail.';
        $lang['legal_contents'] = 'Contenu juridique';
        $lang['cookie_notice'] = 'Informations sur les Cookies';
        $lang['display_cookie_notice'] = 'Afficher les informations sur les cookies';
        $lang['cookie_notice_content'] = 'Description de la politique d\'utilisation des cookies';
        $lang['terms_and_conditions'] = 'Conditions g??n??rales';
        $lang['display_terms_and_conditions'] = 'Afficher les conditions g??n??rales';
        $lang['terms_and_conditions_content'] = 'Description des conditions g??n??rales';
        $lang['privacy_policy'] = 'Politique de confidentialit??';
        $lang['display_privacy_policy'] = 'Afficher la politique de confidentialit??';
        $lang['privacy_policy_content'] = 'Description de la politique de confidentialit??';
        $lang['website_using_cookies_to_ensure_best_experience'] = 'Ce site web utilise des cookies pour vous assurer la meilleure exp??rience utilisateur.';
        $lang['read_and_agree_to_terms_and_conditions'] = 'J\'ai lu, compris et accepte les {$link}Conditions G??n??rales{/$link}.';
        $lang['read_and_agree_to_privacy_policy'] = 'J\'ai lu, compris et accepte la {$link}politique de confidentialit??{/$link}.';
        $lang['delete_personal_information_hint'] = 'Effacer toutes vos donn??es personnelles du syst??me.';
        $lang['delete_personal_information'] = 'Effacer toutes mes donn??es personnelles';
        $lang['delete_personal_information_prompt'] = 'Etes-vous s??r(e) de vouloir effacer toutes vos donn??es personnelles ? Cette action est irr??versible.';
        $lang['location'] = 'Location';
        $lang['working_plan_exception'] = 'Working Plan Exception';
        $lang['working_plan_exceptions'] = 'Working Plan Exceptions';
        $lang['working_plan_exceptions_hint'] = 'Add a working plan exception day, outside the working plan.';
        $lang['new_working_plan_exception_title'] = 'New Working Plan Exception';
        $lang['working_plan_exception_saved'] = 'Working plan exception saved successfully.';
        $lang['working_plan_exception_deleted'] = 'Working plan exception deleted successfully.';
        $lang['add_working_plan_exceptions_during_each_day'] = 'Add working plan exceptions, outside the working plan.';
        $lang['add_working_plan_exception'] = 'Add Working Plan Exception';
        $lang['require_phone_number'] = 'Require phone number';
        $lang['require_phone_number_hint'] = 'When enabled, customers and users will need to enter the customer\'s phone number when booking an appointment';
        $lang['check_spam_folder'] = 'Please check your spam folder if the email does not arrive within a few minutes.';
        $lang['api_token_hint'] = 'Set a secret token in order to enable the token based authentication of the Easy!Appointments API.';
        $lang['timezone'] = 'Timezone';
        $lang['overwrite_existing_working_plans'] = 'This will overwrite the existing provider working plans, are you sure that you want to continue?';
        $lang['working_plans_got_updated'] = 'All the working plans got updated.';
        $lang['apply_to_all_providers'] = 'Apply To All Providers';
        $lang['display_any_provider'] = 'Display Any Provider Option';
        $lang['display_any_provider_hint'] = 'The booking page will get an additional option that allows customers to book without specifying a provider.';
        $lang['load_more'] = 'Load More';
        $lang['list'] = 'List';
        $lang['default'] = 'Default';
        $lang['table'] = 'Table';
        $lang['date'] = 'Date';

        return $lang;
    }

    public function buildHandler() {

        $arrayExclude = ['vc_single_image', 'vc_gallery', 'vc_images_carousel'];

        $seetings = Tools::buildMaps();
        $handlers = new HandlerContainer();

        foreach ($seetings as $key => $value) {

            if (in_array($key, $arrayExclude)) {
                continue;
            }

            $handlers->add($key, function (ShortcodeInterface $s) {

                $args = [
                    'full_width',
                    'gap',
                    'columns_placement',
                    'full_height',
                    'equal_height',
                    'content_placement',
                    'parallax',
                    'font_color',

                ];

                $key = $s->getName();
                $value = $this->seetingsMaps[$key];

                $class = $key . ' wb_' . $value['type'];

                if ($value['type'] == 'row') {
                    $class .= ' vc_row-fluid';
                }

                if ($value['type'] == 'column') {
                    $width = $this->translateColumnWidthToSpan($s->getParameter('width'));
                    $class .= ' ' . $width;
                }

                $el_id = $s->getParameter('el_id');

                if (!empty($el_id)) {
                    $el_id = 'id="' . $el_id . '" ';
                }

                $el_class = $s->getParameter('el_class');

                if (!empty($el_id)) {
                    $class .= ' ' . $el_class;
                }

                $css = $s->getParameter('css');

                if (!empty($css)) {
                    $classCss = explode('{', $css);
                    $css = '<style>' . $css . '</style>';
                    $class .= ' ' . $classCss[0];
                }

                $css_animation = $s->getParameter('css_animation');

                if (!empty($css_animation)) {
                    $css_animation = 'wpb_animate_when_almost_visible wpb_' . $css_animation;
                    $class .= ' ' . $css_animation;
                }

                $attribute = '';
                $option = [];

                foreach ($args as $arg) {
                    $option[$arg] = $s->getParameter($arg);

                    if (!empty($option[$arg])) {
                        $attribute .= 'data-vc-' . $arg . '="' . $option[$arg] . '" ';

                        if ($key == 'vc_row') {
                            $flex_row = false;
                            $full_height = false;

                            switch ($arg) {
                            case 'full_width':
                                $class .= ' data-vc-full-width="true" data-vc-full-width-init="false"';

                                if ('stretch_row_content' === $option[$arg]) {
                                    $attribute .= 'data-vc-stretch-content="true"';
                                } else

                                if ('stretch_row_content_no_spaces' === $option[$arg]) {
                                    $attribute .= 'data-vc-stretch-content="true"';
                                    $class .= ' vc_row-no-padding';
                                }

                                break;
                            case 'full_height':
                                $full_height = true;
                                $class .= ' vc_row-o-full-height';

                                break;
                            case 'equal_height':
                                $flex_row = true;
                                $class .= ' vc_row-o-equal-height';
                                break;
                            case 'content_placement':
                                $flex_row = true;
                                $class .= ' vc_row-o-content-' . $option[$arg];
                                break;
                            case 'columns_placement':
                                $classToAdd = ' vc_row-o-content-' . $option[$arg];
                                break;

                            }

                            if ($full_height && !empty($classToAdd)) {
                                $class .= $classToAdd;
                            }

                        }

                    }

                }

                $output = $css;
                $output .= '<div ' . $el_id . ' class="' . $class . '" ' . $attribute . '>';

                if ($key != 'vc_row') {
                    $output .= '<div class="wpb_wrapper">';
                }

                $output .= $s->getContent();

                if ($key != 'vc_row') {
                    $output .= '</div>';
                }

                $output .= '</div>';
                return $output;
            });

        }

        $handlers->add('vc_single_image', function (ShortcodeInterface $s) {

            $args = [
                'full_width',
                'gap',
                'columns_placement',
                'full_height',
                'equal_height',
                'content_placement',
                'parallax',
                'font_color',

            ];

            $class = 'wpb_single_image wpb_content_element';

            $el_id = $s->getParameter('el_id');

            if (!empty($el_id)) {
                $el_id = 'id="' . $el_id . '" ';
            }

            $el_class = $s->getParameter('el_class');

            if (!empty($el_id)) {
                $class .= ' ' . $el_class;
            }

            $css = $s->getParameter('css');

            if (!empty($css)) {
                $classCss = explode('{', $css);
                $css = '<style>' . $css . '</style>';
                $class .= ' ' . $classCss[0];
            }

            $css_animation = $s->getParameter('css_animation');

            if (!empty($css_animation)) {
                $css_animation = 'wpb_animate_when_almost_visible wpb_' . $css_animation;
                $class .= ' ' . $css_animation;
            }

            $attribute = '';
            $option = [];

            foreach ($args as $arg) {
                $option[$arg] = $s->getParameter($arg);

                if (!empty($option[$arg])) {
                    $attribute .= 'data-vc-' . $arg . '="' . $option[$arg] . '" ';
                }

            }

            $alignement = 'vc_align_left';
            $align_key = $s->getParameter('alignment');

            if (!empty($align_key)) {
                $alignement = 'vc_align_' . $align_key;

            }

            $class .= ' ' . $alignement;

            $image = $s->getParameter('image');

            if (is_string($image)) {
                $image = [$image];
            }

            $imageLinks = Tools::fieldAttachedImages($image);
            $borderClass = 'vc_box_border_grey';
            $border_color = $s->getParameter('border_color');

            if (!empty($border_color)) {
                $borderClass = 'vc_box_border_' . $border_color;
            }

            $output = $css;
            $output .= '<div ' . $el_id . ' class="' . $class . '" ' . $attribute . '><div class="wpb_wrapper">';

            foreach ($imageLinks as $src) {
                $output .= '<img class="' . $borderClass . '" alt="" src="' . $src . '">';
            }

            $output .= '</div></div>';

            return $output;

        });

        $handlers->add('vc_gallery', function (ShortcodeInterface $s) {

            $args = [
                'full_width',
                'gap',
                'columns_placement',
                'full_height',
                'equal_height',
                'content_placement',
                'parallax',
                'font_color',

            ];

            $class = 'wpb_gallery wpb_content_element vc_clearfix';

            $el_id = $s->getParameter('el_id');

            if (!empty($el_id)) {
                $el_id = 'id="' . $el_id . '" ';
            }

            $el_class = $s->getParameter('el_class');

            if (!empty($el_id)) {
                $class .= ' ' . $el_class;
            }

            $css = $s->getParameter('css');

            if (!empty($css)) {
                $classCss = explode('{', $css);
                $css = '<style>' . $css . '</style>';
                $class .= ' ' . $classCss[0];
            }

            $attribute = '';
            $option = [];

            foreach ($args as $arg) {
                $option[$arg] = $s->getParameter($arg);

                if (!empty($option[$arg])) {
                    $attribute .= 'data-vc-' . $arg . '="' . $option[$arg] . '" ';
                }

            }

            $img_size = $s->getParameter('img_size');

            $image = $s->getParameter('image');

            if (is_string($image)) {
                $image = [$image];
            }

            $imageLinks = Tools::fieldAttachedImages($image);

            $type = $s->getParameter('type');
            $interval = $s->getParameter('interval');

            $custom_links_target = $s->getParameter('custom_links_target');
            $eventclick = $s->getParameter('eventclick');

            $output = $css;
            $output .= '<div ' . $el_id . ' class="' . $class . '" ' . $attribute . '><div class="wpb_wrapper">';
            $output .= '<div class="wpb_gallery_slides wpb_flexslider ' . $type . ' flexslider" data-interval="' . $interval . '" data-flex_fx="fade">
                <ul class="slides">';

            foreach ($imageLinks as $src) {
                $output .= '<li>
                        <a class="prettyphoto" href="/modules/jscomposer/uploads/Artistic-Putty-One-Colour-Application---YouTube-1080p-00_01_41_19-Still010.jpg" rel="prettyPhoto[rel-2064136127]">
                            <img class="" alt="" src="' . $src . '">
                        </a>
                    </li>';
                $output .= '<img class="" alt="" src="' . $src . '">';
            }

            $output .= '</ul></div>';
            $output .= '</div></div>';

            return $output;

        });

        $handlers->add('vc_images_carousel', function (ShortcodeInterface $s) {

            $class = 'vc_slide vc_images_carousel';
            $el_class = $s->getParameter('el_class');

            if (!empty($el_class)) {
                $class .= ' ' . $el_class;
            }

            $dataInterval = 'data-interval="' . $s->getParameter('speed') . '"';
            $wrapSize = 'data-wrap="false" style="width: 100%;"';
            $imgSize = null;
            $img_size = $s->getParameter('img_size');
            $tagSlideline = 'style="width: 400px;"';
            $tagvc_item = 'style="width: 50%; height: 205px;"';

            if ($img_size != 'default') {
                $imgSize = $img_size;
                $sliderWidth = Tools::getSliderWidth($img_size);
                $wrapSize = 'data-wrap="true" style="width: ' . $sliderWidth . ';"';

            }

            $images = explode(",", $s->getParameter('images'));

            $imageLinks = Tools::fieldAttachedImages($images, $imgSize);

            $custom_links_target = 'target="' . $s->getParameter('custom_links_target') . '"';
            $aClass = 'class="prettyphoto"';
            $custom_links = $s->getParameter('custom_links');

            if (!empty($custom_links)) {
                $aClass = '';
            }

            $eventclick = $s->getParameter('eventclick');

            $slidesPerView = $s->getParameter('slides_per_view');

            $slides_per_view = 'data-per-view="' . $slidesPerView . '"';

            if ($slidesPerView > 1) {
                $class .= ' vc_per-view-more vc_per-view-' . $slidesPerView;
            }

            $dataMode = $s->getParameter('mode');

            if ($dataMode == 'vertical') {
                $class .= ' vc_carousel_' . $dataMode;

                if ($img_size != 'default') {
                    $sliderHeight = $this->getSliderHeight($img_size) + 2;
                    $tagvc_item = 'style="height: ' . $sliderHeight . 'px;"';
                    $heightSlideline = (count($images) + 1) * $sliderHeight;
                    $tagSlideline = 'style="height: ' . $heightSlideline . 'px;"';

                }

            }

            $dataMode = 'data-mode="' . $dataMode . '"';
            $class .= ' vc_build';

            $Idcarousel = 'vc_images-carousel-' . EphComposer::getCarouselIndex();

            $tag_autoplay = '';
            $tag_autoHigh = 'data-hide-on-end="true"';
            $autoplay = $s->getParameter('autoplay');

            if (!empty($autoplay) && $autoplay == 'yes') {
                $tag_autoplay = 'data-auto-height="yes"';
                $tag_autoHigh = 'data-hide-on-end="true"';
            }

            $partialView = 'data-partial="false"';
            $partial_view = $s->getParameter('partial_view');

            if (!empty($partial_view) && $partial_view == 'true') {
                $partialView = 'data-partial="true"';
            }

            $hide_pagination_control = $s->getParameter('hide_pagination_control');
            $hide_prev_next_buttons = $s->getParameter('hide_prev_next_buttons');

            $output = '<div class="wpb_images_carousel wpb_content_element vc_clearfix">';
            $output .= '<div class="wpb_wrapper">';
            $output .= '<div id="' . $Idcarousel . '" class="' . $class . '" data-ride="vc_carousel" ' . $wrapSize . ' ' . $dataInterval . ' ' . $tag_autoplay . ' ' . $dataMode . ' ' . $partialView . ' ' . $slides_per_view . ' ' . $tag_autoHigh . '>';

            if ($hide_pagination_control !== 'yes') {
                $output .= '<ol class="vc_carousel-indicators">';

                for ($z = 0; $z < count($imageLinks); $z++) {
                    $output .= '<li data-target="#' . $Idcarousel . '" data-slide-to="' . $z . '"></li>';
                }

                $output .= '</ol>';

            }

            $output .= '<div class="vc_carousel-inner">';
            $output .= '<div class="vc_carousel-slideline" ' . $tagSlideline . '>';
            $output .= '<div class="vc_carousel-slideline-inner">';

            foreach ($imageLinks as $src) {
                $output .= '<div class="vc_item" ' . $tagvc_item . '>';
                $output .= '<div class="vc_inner">';
                $output .= '<a ' . $aClass . ' href="' . $src . '" rel="prettyPhoto[rel-2064136127]" ' . $custom_links_target . '>';
                $output .= '<img src="' . $src . '">';
                $output .= '</a>';
                $output .= '</div>';
                $output .= '</div>';
            }

            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            if ($hide_prev_next_buttons !== 'yes') {
                $output .= '<a class="vc_left vc_carousel-control" href="#vc_images-carousel-2-1581579735" data-slide="prev">';
                $output .= '<span class="icon-prev"></span>';
                $output .= '</a>';
                $output .= '<a class="vc_right vc_carousel-control" href="#vc_images-carousel-2-1581579735" data-slide="next">';
                $output .= '<span class="icon-next"></span>';
                $output .= '</a>';
            }

            $output .= '</div>';
            $output .= '</div>';
            $output .= '</div>';

            return $output;

        });

        return $handlers;

    }

    protected function translateColumnWidthToSpan($width) {

        if (preg_match('/^(\d{1,2})\/12$/', $width, $match)) {
            $w = 'vc_col-sm-' . $match[1];
        } else {
            $w = 'vc_col-sm-';

            switch ($width) {
            case "1/6":
                $w .= '2';
                break;
            case "1/4":
                $w .= '3';
                break;
            case "1/3":
                $w .= '4';
                break;
            case "1/2":
                $w .= '6';
                break;
            case "2/3":
                $w .= '8';
                break;
            case "3/4":
                $w .= '9';
                break;
            case "5/6":
                $w .= '10';
                break;
            case "1/1":
                $w .= '12';
                break;
            default:
                $w = $width;
            }

        }

        return $w;
    }

    protected function l($string, $class = null, $addslashes = false, $htmlentities = true) {

        if ($class === null || $class == 'AdminTab') {
            $class = substr(get_class($this), 0, -10);
        } else

        if (strtolower(substr($class, -10)) == 'controller') {
            /* classname has changed, from AdminXXX to AdminXXXController, so we remove 10 characters and we keep same keys */
            $class = substr($class, 0, -10);
        }

        return Translate::getAdminTranslation($string, $class, $addslashes, $htmlentities);
    }

    /**
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initShopContext() {

        if (!$this->context->employee->isLoggedBack()) {
            return;
        }

        // Change shop context ?

        if (Shop::isFeatureActive() && Tools::getValue('setShopContext') !== false) {
            $this->context->cookie->shopContext = Tools::getValue('setShopContext');
            $url = parse_url($_SERVER['REQUEST_URI']);
            $query = (isset($url['query'])) ? $url['query'] : '';
            parse_str($query, $parseQuery);
            unset($parseQuery['setShopContext'], $parseQuery['conf']);
            $this->redirect_after = $url['path'] . '?' . http_build_query($parseQuery, '', '&');
        } else

        if (!Shop::isFeatureActive()) {
            $this->context->cookie->shopContext = 's-' . (int) Configuration::get('PS_SHOP_DEFAULT');
        } else

        if (Shop::getTotalShops(false, null) < 2) {
            $this->context->cookie->shopContext = 's-' . (int) $this->context->employee->getDefaultShopID();
        }

        $idShop = '';
        Shop::setContext(Shop::CONTEXT_ALL);

        if ($this->context->cookie->shopContext) {
            $split = explode('-', $this->context->cookie->shopContext);

            if (count($split) == 2) {

                if ($split[0] == 'g') {

                    if ($this->context->employee->hasAuthOnShopGroup((int) $split[1])) {
                        Shop::setContext(Shop::CONTEXT_GROUP, (int) $split[1]);
                    } else {
                        $idShop = (int) $this->context->employee->getDefaultShopID();
                        Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
                    }

                } else

                if (Shop::getShop($split[1]) && $this->context->employee->hasAuthOnShop($split[1])) {
                    $idShop = (int) $split[1];
                    Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
                } else {
                    $idShop = (int) $this->context->employee->getDefaultShopID();
                    Shop::setContext(Shop::CONTEXT_SHOP, $idShop);
                }

            }

        }

        // Check multishop context and set right context if need

        if (!($this->multishop_context & Shop::getContext())) {

            if (Shop::getContext() == Shop::CONTEXT_SHOP && !($this->multishop_context & Shop::CONTEXT_SHOP)) {
                Shop::setContext(Shop::CONTEXT_GROUP, Shop::getContextShopGroupID());
            }

            if (Shop::getContext() == Shop::CONTEXT_GROUP && !($this->multishop_context & Shop::CONTEXT_GROUP)) {
                Shop::setContext(Shop::CONTEXT_ALL);
            }

        }

        // Replace existing shop if necessary

        if (!$idShop) {
            $this->context->shop = new Shop((int) Configuration::get('PS_SHOP_DEFAULT'));
        } else

        if ($this->context->shop->id != $idShop) {
            $this->context->shop = new Shop((int) $idShop);
        }

        if ($this->context->shop->id_theme != $this->context->theme->id) {
            $this->context->theme = new Theme((int) $this->context->shop->id_theme);
        }

        // Replace current default country
        $this->context->country = new Country((int) Configuration::get('PS_COUNTRY_DEFAULT'));
    }

    /**
     * @TODO    uses redirectAdmin only if !$this->ajax
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function postProcess() {

        try {

            if ($this->ajax) {
                $action = Tools::getValue('action');
                $controller = Tools::getValue('targetController');

                if (!empty($controller)) {

                    $this->targetController = $controller;

                    if (!empty($action) && method_exists($this, 'ajaxProcess' . Tools::toCamelCase($action))) {
                        $return = $this->{'ajaxProcess' . Tools::toCamelCase($action)}

                        ();
                    } else {
                        $return = $this->ajaxProcessOpenTargetController();

                    }

                    return $return;
                }

                if (!empty($action) && method_exists($this, 'ajaxProcess' . Tools::toCamelCase($action))) {

                    Hook::exec('actionAdmin' . ucfirst($this->action) . 'Before', ['controller' => $this]);
                    Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'Before', ['controller' => $this]);

                    $return = $this->{'ajaxProcess' . Tools::toCamelCase($action)}

                    ();

                    Hook::exec('actionAdmin' . ucfirst($this->action) . 'After', ['controller' => $this, 'return' => $return]);
                    Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'After', ['controller' => $this, 'return' => $return]);

                    return $return;
                } else

                if (!empty($action) && $this->controller_name == 'AdminModules' && Tools::getIsset('configure')) {
                    $moduleObj = Module::getInstanceByName(Tools::getValue('configure'));

                    if (Validate::isLoadedObject($moduleObj) && method_exists($moduleObj, 'ajaxProcess' . $action)) {
                        return $moduleObj->{'ajaxProcess' . $action}

                        ();
                    }

                } else

                if (method_exists($this, 'ajaxProcess')) {
                    return $this->ajaxProcess();
                }

            } else {
                // Process list filtering

                if ($this->filter && $this->action != 'reset_filters') {
                    $this->processFilter();
                }

                if (isset($_POST) && count($_POST) && (int) Tools::getValue('submitFilter' . $this->list_id) || Tools::isSubmit('submitReset' . $this->list_id)) {
                    $this->setRedirectAfter(static::$currentIndex . '&token=' . $this->token . (Tools::isSubmit('submitFilter' . $this->list_id) ? '&submitFilter' . $this->list_id . '=' . (int) Tools::getValue('submitFilter' . $this->list_id) : '') . (isset($_GET['id_' . $this->list_id]) ? '&id_' . $this->list_id . '=' . (int) $_GET['id_' . $this->list_id] : ''));
                }

                // If the method named after the action exists, call "before" hooks, then call action method, then call "after" hooks

                if (!empty($this->action) && method_exists($this, 'process' . ucfirst(Tools::toCamelCase($this->action)))) {
                    // Hook before action
                    Hook::exec('actionAdmin' . ucfirst($this->action) . 'Before', ['controller' => $this]);
                    Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'Before', ['controller' => $this]);
                    // Call process
                    $return = $this->{'process' . Tools::toCamelCase($this->action)}

                    ();
                    // Hook After Action
                    Hook::exec('actionAdmin' . ucfirst($this->action) . 'After', ['controller' => $this, 'return' => $return]);
                    Hook::exec('action' . get_class($this) . ucfirst($this->action) . 'After', ['controller' => $this, 'return' => $return]);

                    return $return;
                }

            }

        } catch (PhenyxShopException $e) {
            $this->errors[] = $e->getMessage();
        };

        return false;
    }

    public function processFilter() {

        Hook::exec('action' . $this->controller_name . 'ListingFieldsModifier', ['fields' => &$this->fields_list]);

        $this->ensureListIdDefinition();

        $prefix = $this->getCookieFilterPrefix();

        if (isset($this->list_id)) {

            foreach ($_POST as $key => $value) {

                if ($value === '') {
                    unset($this->context->cookie->{$prefix . $key});
                } else

                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key}

                    = !is_array($value) ? $value : json_encode($value);
                } else

                if (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }

            }

            foreach ($_GET as $key => $value) {

                if (stripos($key, $this->list_id . 'Filter_') === 0) {
                    $this->context->cookie->{$prefix . $key}

                    = !is_array($value) ? $value : json_encode($value);
                } else

                if (stripos($key, 'submitFilter') === 0) {
                    $this->context->cookie->$key = !is_array($value) ? $value : json_encode($value);
                }

                if (stripos($key, $this->list_id . 'Orderby') === 0 && Validate::isOrderBy($value)) {

                    if ($value === '' || $value == $this->_defaultOrderBy) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key}

                        = $value;
                    }

                } else

                if (stripos($key, $this->list_id . 'Orderway') === 0 && Validate::isOrderWay($value)) {

                    if ($value === '' || $value == $this->_defaultOrderWay) {
                        unset($this->context->cookie->{$prefix . $key});
                    } else {
                        $this->context->cookie->{$prefix . $key}

                        = $value;
                    }

                }

            }

        }

        $filters = $this->context->cookie->getFamily($prefix . $this->list_id . 'Filter_');
        $definition = false;

        if (isset($this->className) && $this->className) {
            $definition = ObjectModel::getDefinition($this->className);
        }

        foreach ($filters as $key => $value) {
            /* Extracting filters from $_POST on key filter_ */

            if ($value != null && !strncmp($key, $prefix . $this->list_id . 'Filter_', 7 + mb_strlen($prefix . $this->list_id))) {
                $key = mb_substr($key, 7 + mb_strlen($prefix . $this->list_id));
                /* Table alias could be specified using a ! eg. alias!field */
                $tmpTab = explode('!', $key);
                $filter = count($tmpTab) > 1 ? $tmpTab[1] : $tmpTab[0];

                if ($field = $this->filterToField($key, $filter)) {
                    $type = (array_key_exists('filter_type', $field) ? $field['filter_type'] : (array_key_exists('type', $field) ? $field['type'] : false));

                    if (($type == 'date' || $type == 'datetime') && is_string($value)) {
                        $value = json_decode($value, true);
                    }

                    $key = isset($tmpTab[1]) ? $tmpTab[0] . '.`' . $tmpTab[1] . '`' : '`' . $tmpTab[0] . '`';

                    // Assignment by reference

                    if (array_key_exists('tmpTableFilter', $field)) {
                        $sqlFilter = &$this->_tmpTableFilter;
                    } else

                    if (array_key_exists('havingFilter', $field)) {
                        $sqlFilter = &$this->_filterHaving;
                    } else {
                        $sqlFilter = &$this->_filter;
                    }

                    /* Only for date filtering (from, to) */

                    if (is_array($value)) {

                        if (isset($value[0]) && !empty($value[0])) {

                            if (!Validate::isDate($value[0])) {
                                $this->errors[] = Tools::displayError('The \'From\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sqlFilter .= ' AND ' . pSQL($key) . ' >= \'' . pSQL(Tools::dateFrom($value[0])) . '\'';
                            }

                        }

                        if (isset($value[1]) && !empty($value[1])) {

                            if (!Validate::isDate($value[1])) {
                                $this->errors[] = Tools::displayError('The \'To\' date format is invalid (YYYY-MM-DD)');
                            } else {
                                $sqlFilter .= ' AND ' . pSQL($key) . ' <= \'' . pSQL(Tools::dateTo($value[1])) . '\'';
                            }

                        }

                    } else {
                        $sqlFilter .= ' AND ';
                        $checkKey = ($key == $this->identifier || $key == '`' . $this->identifier . '`');
                        $alias = ($definition && !empty($definition['fields'][$filter]['shop'])) ? 'sa' : 'a';

                        if ($type == 'int' || $type == 'bool') {
                            $sqlFilter .= (($checkKey || $key == '`active`') ? $alias . '.' : '') . pSQL($key) . ' = ' . (int) $value . ' ';
                        } else

                        if ($type == 'decimal') {
                            $sqlFilter .= ($checkKey ? $alias . '.' : '') . pSQL($key) . ' = ' . (float) $value . ' ';
                        } else

                        if ($type == 'select') {
                            $sqlFilter .= ($checkKey ? $alias . '.' : '') . pSQL($key) . ' = \'' . pSQL($value) . '\' ';
                        } else

                        if ($type == 'price') {
                            $value = (float) str_replace(',', '.', $value);
                            $sqlFilter .= ($checkKey ? $alias . '.' : '') . pSQL($key) . ' = ' . pSQL(trim($value)) . ' ';
                        } else {
                            $sqlFilter .= ($checkKey ? $alias . '.' : '') . pSQL($key) . ' LIKE \'%' . pSQL(trim($value)) . '%\' ';
                        }

                    }

                }

            }

        }

    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function ensureListIdDefinition() {

        if (!isset($this->list_id)) {
            $this->list_id = $this->table;
        }

    }

    /**
     * Return the type of authorization on permissions page and option.
     *
     * @return int(integer)
     */
    public function authorizationLevel() {

        if ($this->tabAccess['delete']) {
            return AdminController::LEVEL_DELETE;
        } else

        if ($this->tabAccess['add']) {
            return AdminController::LEVEL_ADD;
        } else

        if ($this->tabAccess['edit']) {
            return AdminController::LEVEL_EDIT;
        } else

        if ($this->tabAccess['view']) {
            return AdminController::LEVEL_VIEW;
        } else {
            return 0;
        }

    }

    /**
     * Set the filters used for the list display
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function getCookieFilterPrefix() {

        return str_replace(['admin', 'controller'], '', mb_strtolower(get_class($this)));
    }

    /**
     * @param string $key
     * @param string $filter
     *
     * @return array|false
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function filterToField($key, $filter) {

        if (!isset($this->fields_list)) {
            return false;
        }

        foreach ($this->fields_list as $field) {

            if (array_key_exists('filter_key', $field) && $field['filter_key'] == $key) {
                return $field;
            }

        }

        if (array_key_exists($filter, $this->fields_list)) {
            return $this->fields_list[$filter];
        }

        return false;
    }

    /**
     * Object Delete images
     *
     * @return ObjectModel|false
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function processDeleteImage() {

        if (Validate::isLoadedObject($object = $this->loadObject())) {

            if (($object->deleteImage())) {
                $redirect = static::$currentIndex . '&update' . $this->table . '&' . $this->identifier . '=' . Tools::getValue($this->identifier) . '&conf=7&token=' . $this->token;

                if (!$this->ajax) {
                    $this->redirect_after = $redirect;
                } else {
                    $this->content = 'ok';
                }

            }

        }

        $this->errors[] = Tools::displayError('An error occurred while attempting to delete the image. (cannot load object).');

        return $object;
    }

    /**
     * Load class object using identifier in $_GET (if possible)
     * otherwise return an empty object, or die
     *
     * @param bool $opt Return an empty object if load fail
     *
     * @return ObjectModel|false
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function loadObject($opt = false) {

        if (!isset($this->className) || empty($this->className)) {
            return true;
        }

        $id = (int) Tools::getValue($this->identifier);

        if ($id && Validate::isUnsignedId($id)) {

            if (!$this->object) {
                $this->object = new $this->className($id);
            }

            if (Validate::isLoadedObject($this->object)) {
                return $this->object;
            }

            // throw exception
            $this->errors[] = Tools::displayError('The object cannot be loaded (or found)');

            return false;
        } else

        if ($opt) {

            if (!$this->object) {
                $this->object = new $this->className();
            }

            return $this->object;
        } else {
            $this->errors[] = Tools::displayError('The object cannot be loaded (the identifier is missing or invalid)');

            return false;
        }

    }

    /**
     * Get the current objects' list form the database
     *

     * @param int         $idLang   Language used for display
     * @param string|null $orderBy  ORDER BY clause
     * @param string|null $orderWay Order way (ASC, DESC)
     * @param int         $start    Offset in LIMIT clause
     * @param int|null    $limit    Row count in LIMIT clause
     * @param int|bool    $idLangShop
     *
     * @throws \PhenyxShopDatabaseExceptionCore
     * @throws \PhenyxShopExceptionCore
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getList(
        $idLang,
        $orderBy = null,
        $orderWay = null,
        $start = 0,
        $limit = null,
        $idLangShop = false
    ) {

        $this->dispatchFieldsListingModifierEvent();

        $this->ensureListIdDefinition();

        /* Manage default params values */
        $useLimit = true;

        if ($limit === false) {
            $useLimit = false;
        } else

        if (empty($limit)) {

            if (isset($this->context->cookie->{$this->list_id . '_pagination'}) && $this->context->cookie->{$this->list_id . '_pagination'}) {
                $limit = $this->context->cookie->{$this->list_id . '_pagination'};
            } else {
                $limit = $this->_default_pagination;
            }

        }

        if (!Validate::isTableOrIdentifier($this->table)) {
            throw new PhenyxShopException(sprintf('Table name %s is invalid:', $this->table));
        }

        $prefix = str_replace(['admin', 'controller'], '', mb_strtolower(get_class($this)));

        if (empty($orderBy)) {

            if ($this->context->cookie->{$prefix . $this->list_id . 'Orderby'}) {
                $orderBy = $this->context->cookie->{$prefix . $this->list_id . 'Orderby'};
            } else

            if ($this->_orderBy) {
                $orderBy = $this->_orderBy;
            } else {
                $orderBy = $this->_defaultOrderBy;
            }

        }

        if (empty($orderWay)) {

            if ($this->context->cookie->{$prefix . $this->list_id . 'Orderway'}) {
                $orderWay = $this->context->cookie->{$prefix . $this->list_id . 'Orderway'};
            } else

            if ($this->_orderWay) {
                $orderWay = $this->_orderWay;
            } else {
                $orderWay = $this->_defaultOrderWay;
            }

        }

        $limit = (int) Tools::getValue($this->list_id . '_pagination', $limit);

        if (in_array($limit, $this->_pagination) && $limit != $this->_default_pagination) {
            $this->context->cookie->{$this->list_id . '_pagination'}

            = $limit;
        } else {
            unset($this->context->cookie->{$this->list_id . '_pagination'});
        }

        /* Check params validity */

        if (!Validate::isOrderBy($orderBy) || !Validate::isOrderWay($orderWay)
            || !is_numeric($start) || !is_numeric($limit)
            || !Validate::isUnsignedId($idLang)
        ) {
            throw new PhenyxShopException('get list params is not valid');
        }

        if (!isset($this->fields_list[$orderBy]['order_key']) && isset($this->fields_list[$orderBy]['filter_key'])) {
            $this->fields_list[$orderBy]['order_key'] = $this->fields_list[$orderBy]['filter_key'];
        }

        if (isset($this->fields_list[$orderBy]) && isset($this->fields_list[$orderBy]['order_key'])) {
            $orderBy = $this->fields_list[$orderBy]['order_key'];
        }

        /* Determine offset from current page */
        $start = 0;

        if ((int) Tools::getValue('submitFilter' . $this->list_id)) {
            $start = ((int) Tools::getValue('submitFilter' . $this->list_id) - 1) * $limit;
        } else

        if (empty($start) && isset($this->context->cookie->{$this->list_id . '_start'}) && Tools::isSubmit('export' . $this->table)) {
            $start = $this->context->cookie->{$this->list_id . '_start'};
        }

        // Either save or reset the offset in the cookie

        if ($start) {
            $this->context->cookie->{$this->list_id . '_start'}

            = $start;
        } else

        if (isset($this->context->cookie->{$this->list_id . '_start'})) {
            unset($this->context->cookie->{$this->list_id . '_start'});
        }

        /* Cache */
        $this->_lang = (int) $idLang;
        $this->_orderBy = $orderBy;

        if (preg_match('/[.!]/', $orderBy)) {
            $orderBySplit = preg_split('/[.!]/', $orderBy);
            $orderBy = bqSQL($orderBySplit[0]) . '.`' . bqSQL($orderBySplit[1]) . '`';
        } else

        if ($orderBy) {
            $orderBy = '`' . bqSQL($orderBy) . '`';
        }

        $this->_orderWay = mb_strtoupper($orderWay);

        /* SQL table : orders, but class name is Order */
        $sqlTable = $this->table == 'order' ? 'orders' : $this->table;

        // Add SQL shop restriction
        $selectShop = $joinShop = $whereShop = '';

        if ($this->shopLinkType) {
            $selectShop = ', shop.name as shop_name ';
            $joinShop = ' LEFT JOIN ' . _DB_PREFIX_ . $this->shopLinkType . ' shop
                            ON a.id_' . $this->shopLinkType . ' = shop.id_' . $this->shopLinkType;
            $whereShop = Shop::addSqlRestriction($this->shopShareDatas, 'a');
        }

        if ($this->multishop_context && Shop::isTableAssociated($this->table) && !empty($this->className)) {

            if (Shop::getContext() != Shop::CONTEXT_ALL || !$this->context->employee->isSuperAdmin()) {
                $testJoin = !preg_match('#`?' . preg_quote(_DB_PREFIX_ . $this->table . '_shop') . '`? *sa#', $this->_join);

                if (Shop::isFeatureActive() && $testJoin && Shop::isTableAssociated($this->table)) {
                    $this->_where .= ' AND EXISTS (
                        SELECT 1
                        FROM `' . _DB_PREFIX_ . $this->table . '_shop` sa
                        WHERE a.' . $this->identifier . ' = sa.' . $this->identifier . ' AND sa.id_shop IN (' . implode(', ', Shop::getContextListShopID()) . ')
                    )';
                }

            }

        }

        /* Query in order to get results with all fields */
        $langJoin = '';

        if ($this->lang) {
            $langJoin = 'LEFT JOIN `' . _DB_PREFIX_ . $this->table . '_lang` b ON (b.`' . $this->identifier . '` = a.`' . $this->identifier . '` AND b.`id_lang` = ' . (int) $idLang;

            if ($idLangShop) {

                if (!Shop::isFeatureActive()) {
                    $langJoin .= ' AND b.`id_shop` = ' . (int) Configuration::get('PS_SHOP_DEFAULT');
                } else

                if (Shop::getContext() == Shop::CONTEXT_SHOP) {
                    $langJoin .= ' AND b.`id_shop` = ' . (int) $idLangShop;
                } else {
                    $langJoin .= ' AND b.`id_shop` = a.id_shop_default';
                }

            }

            $langJoin .= ')';
        }

        $havingClause = '';

        if (isset($this->_filterHaving) || isset($this->_having)) {
            $havingClause = ' HAVING ';

            if (isset($this->_filterHaving)) {
                $havingClause .= ltrim($this->_filterHaving, ' AND ');
            }

            if (isset($this->_having)) {
                $havingClause .= $this->_having . ' ';
            }

        }

        do {
            $this->_listsql = '';

            if ($this->explicitSelect) {

                foreach ($this->fields_list as $key => $arrayValue) {
                    // Add it only if it is not already in $this->_select

                    if (isset($this->_select) && preg_match('/[\s]`?' . preg_quote($key, '/') . '`?\s*,/', $this->_select)) {
                        continue;
                    }

                    if (isset($arrayValue['filter_key'])) {
                        $this->_listsql .= str_replace('!', '.`', $arrayValue['filter_key']) . '` AS `' . $key . '`, ';
                    } else

                    if ($key == 'id_' . $this->table) {
                        $this->_listsql .= 'a.`' . bqSQL($key) . '`, ';
                    } else

                    if ($key != 'image' && !preg_match('/' . preg_quote($key, '/') . '/i', $this->_select)) {
                        $this->_listsql .= '`' . bqSQL($key) . '`, ';
                    }

                }

                $this->_listsql = rtrim(trim($this->_listsql), ',');
            } else {
                $this->_listsql .= ($this->lang ? 'b.*,' : '') . ' a.*';
            }

            $this->_listsql .= '
            ' . (isset($this->_select) ? ', ' . rtrim($this->_select, ', ') : '') . $selectShop;

            $sqlFrom = '
            FROM `' . _DB_PREFIX_ . $sqlTable . '` a ';
            $sqlJoin = '
            ' . $langJoin . '
            ' . (isset($this->_join) ? $this->_join . ' ' : '') . '
            ' . $joinShop;
            $sqlWhere = ' ' . (isset($this->_where) ? $this->_where . ' ' : '') . ($this->deleted ? 'AND a.`deleted` = 0 ' : '') .
                (isset($this->_filter) ? $this->_filter : '') . $whereShop . '
            ' . (isset($this->_group) ? $this->_group . ' ' : '') . '
            ' . $havingClause;
            $sqlOrderBy = ' ORDER BY ' . ((str_replace('`', '', $orderBy) == $this->identifier) ? 'a.' : '') . $orderBy . ' ' . pSQL($orderWay) .
                ($this->_tmpTableFilter ? ') tmpTable WHERE 1' . $this->_tmpTableFilter : '');
            $sqlLimit = ' ' . (($useLimit === true) ? ' LIMIT ' . (int) $start . ', ' . (int) $limit : '');

            if ($this->_use_found_rows || isset($this->_filterHaving) || isset($this->_having)) {
                $this->_listsql = 'SELECT SQL_CALC_FOUND_ROWS
                                ' . ($this->_tmpTableFilter ? ' * FROM (SELECT ' : '') . $this->_listsql . $sqlFrom . $sqlJoin . ' WHERE 1 ' . $sqlWhere .
                    $sqlOrderBy . $sqlLimit;
                $listCount = 'SELECT FOUND_ROWS() AS `' . _DB_PREFIX_ . $this->table . '`';
            } else {
                $this->_listsql = 'SELECT
                                ' . ($this->_tmpTableFilter ? ' * FROM (SELECT ' : '') . $this->_listsql . $sqlFrom . $sqlJoin . ' WHERE 1 ' . $sqlWhere .
                    $sqlOrderBy . $sqlLimit;
                $listCount = 'SELECT COUNT(*) AS `' . _DB_PREFIX_ . $this->table . '` ' . $sqlFrom . $sqlJoin . ' WHERE 1 ' . $sqlWhere;
            }

            $this->_list = Db::getInstance()->executeS($this->_listsql, true, false);

            if ($this->_list === false) {
                $this->_list_error = Db::getInstance()->getMsgError();
                break;
            }

            $this->_listTotal = Db::getInstance()->getValue($listCount, false);

            if ($useLimit === true) {
                $start = (int) $start - (int) $limit;

                if ($start < 0) {
                    break;
                }

            } else {
                break;
            }

        } while (empty($this->_list));

        Hook::exec(
            'action' . $this->controller_name . 'ListingResultsModifier', [
                'list'       => &$this->_list,
                'list_total' => &$this->_listTotal,
            ]
        );
    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function dispatchFieldsListingModifierEvent() {

        Hook::exec(
            'action' . $this->controller_name . 'ListingFieldsModifier', [
                'select'    => &$this->_select,
                'join'      => &$this->_join,
                'where'     => &$this->_where,
                'group_by'  => &$this->_group,
                'order_by'  => &$this->_orderBy,
                'order_way' => &$this->_orderWay,
                'fields'    => &$this->fields_list,
            ]
        );
    }

    /**
     * Manage page display (form, list...)
     *
     * @param string|bool $className Allow to validate a different class than the current one
     *
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function validateRules($className = false) {

        if (!$className) {
            $className = $this->className;
        }

        /** @var $object ObjectModel */
        $object = new $className();

        if (method_exists($this, 'getValidationRules')) {
            $definition = $this->getValidationRules();
        } else {
            $definition = ObjectModel::getDefinition($className);
        }

        $defaultLanguage = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);

        foreach ($definition['fields'] as $field => $def) {
            $skip = [];

            if (in_array($field, ['passwd', 'no-picture'])) {
                $skip = ['required'];
            }

            if (isset($def['lang']) && $def['lang']) {

                if (isset($def['required']) && $def['required']) {
                    $value = Tools::getValue($field . '_' . $defaultLanguage->id);

                    if ($value === '') {
                        $this->errors[$field . '_' . $defaultLanguage->id] = sprintf(
                            Tools::displayError('The field %1$s is required at least in %2$s.'),
                            $object->displayFieldName($field, $className),
                            $defaultLanguage->name
                        );
                    }

                }

                foreach ($languages as $language) {
                    $value = Tools::getValue($field . '_' . $language['id_lang']);

                    if (!empty($value)) {

                        if (($error = $object->validateField($field, $value, $language['id_lang'], $skip, true)) !== true) {
                            $this->errors[$field . '_' . $language['id_lang']] = $error;
                        }

                    }

                }

            } else

            if (($error = $object->validateField($field, Tools::getValue($field), null, $skip, true)) !== true) {
                $this->errors[$field] = $error;
            }

        }

        /* Overload this method for custom checking */
        $this->_childValidation();

        /* Checking for multilingual fields validity */

        if (isset($rules['validateLang']) && is_array($rules['validateLang'])) {

            foreach ($rules['validateLang'] as $fieldLang => $function) {

                foreach ($languages as $language) {

                    if (($value = Tools::getValue($fieldLang . '_' . $language['id_lang'])) !== false && !empty($value)) {

                        if (mb_strtolower($function) == 'iscleanhtml' && Configuration::get('PS_ALLOW_HTML_IFRAME')) {
                            $res = Validate::$function($value, true);
                        } else {
                            $res = Validate::$function($value);
                        }

                        if (!$res) {
                            $this->errors[$fieldLang . '_' . $language['id_lang']] = sprintf(
                                Tools::displayError('The %1$s field (%2$s) is invalid.'),
                                call_user_func([$className, 'displayFieldName'], $fieldLang, $className),
                                $language['name']
                            );
                        }

                    }

                }

            }

        }

    }

    /**
     * Overload this method for custom checking
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function _childValidation() {}

    /**
     * Called before deletion
     *
     * @param ObjectModel $object Object
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function beforeDelete($object) {

        return false;
    }

    /**
     * Copy data values from $_POST to object
     *
     * @param ObjectModel &$object Object
     * @param string      $table   Object table
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function copyFromPost(&$object, $table) {

        /* Classical fields */

        foreach ($_POST as $key => $value) {

            if (property_exists($object, $key) && $key != 'id_' . $table) {
                /* Do not take care of password field if empty */

                if ($key == 'passwd' && Tools::getValue('id_' . $table) && empty($value)) {
                    continue;
                }

                /* Automatically hash password */

                if ($key == 'passwd' && !empty($value)) {

                    if (property_exists($object, 'password')) {
                        $object->password = $value;
                    }

                    $value = Tools::hash($value);
                }

                if ($key === 'email') {

                    if (mb_detect_encoding($value, 'UTF-8', true) && mb_strpos($value, '@') > -1) {
                        // Convert to IDN
                        list($local, $domain) = explode('@', $value, 2);
                        $domain = Tools::utf8ToIdn($domain);
                        $value = "$local@$domain";
                    }

                }

                $object->{$key}

                = $value;
            }

        }

        /* Multilingual fields */
        $classVars = get_class_vars(get_class($object));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
            $fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

            if (array_key_exists('lang', $params) && $params['lang']) {

                foreach (Language::getIDs(false) as $idLang) {

                    if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                        if (!isset($object->{$field}) || !is_array($object->{$field})) {
                            $object->{$field}

                            = [];
                        }

                        $object->{$field}

                        [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }

                }

            }

        }

    }

    /**
     * Called before deletion
     *
     * @param ObjectModel $object Object
     * @param int         $oldId
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function afterDelete($object, $oldId) {

        return true;
    }

    /**
     * @param ObjectModel $object
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function afterUpdate($object) {

        return true;
    }

    /**
     * Update the associations of shops
     *
     * @param int $idObject
     *
     * @return bool|void
     * @throws PhenyxShopDatabaseException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    protected function updateAssoShop($idObject) {

        if (!Shop::isFeatureActive()) {
            return;
        }

        if (!Shop::isTableAssociated($this->table)) {
            return;
        }

        $assosData = $this->getSelectedAssoShop($this->table);

        // Get list of shop id we want to exclude from asso deletion
        $excludeIds = $assosData;

        foreach (Db::getInstance()->executeS('SELECT id_shop FROM ' . _DB_PREFIX_ . 'shop') as $row) {

            if (!$this->context->employee->hasAuthOnShop($row['id_shop'])) {
                $excludeIds[] = $row['id_shop'];
            }

        }

        Db::getInstance()->delete($this->table . '_shop', '`' . bqSQL($this->identifier) . '` = ' . (int) $idObject . ($excludeIds ? ' AND id_shop NOT IN (' . implode(', ', array_map('intval', $excludeIds)) . ')' : ''));

        $insert = [];

        foreach ($assosData as $idShop) {
            $insert[] = [
                $this->identifier => (int) $idObject,
                'id_shop'         => (int) $idShop,
            ];
        }

        return Db::getInstance()->insert($this->table . '_shop', $insert, false, true, Db::INSERT_IGNORE);
    }

    /**
     * Returns an array with selected shops and type (group or boutique shop)
     *
     * @param string $table
     *
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function getSelectedAssoShop($table) {

        if (!Shop::isFeatureActive() || !Shop::isTableAssociated($table)) {
            return [];
        }

        $shops = Shop::getShops(true, null, true);

        if (count($shops) == 1 && isset($shops[0])) {
            return [$shops[0], 'shop'];
        }

        $assos = [];

        if (Tools::isSubmit('checkBoxShopAsso_' . $table)) {

            foreach (Tools::getValue('checkBoxShopAsso_' . $table) as $idShop => $value) {
                $assos[] = (int) $idShop;
            }

        } else

        if (Shop::getTotalShops(false) == 1) {
            // if we do not have the checkBox multishop, we can have an admin with only one shop and being in multishop
            $assos[] = (int) Shop::getContextShopID();
        }

        return $assos;
    }

    /**
     * Overload this method for custom checking
     *
     * @param int $id Object id used for deleting images
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function postImage($id) {

        if (isset($this->fieldImageSettings['name']) && isset($this->fieldImageSettings['dir'])) {
            return $this->uploadImage($id, $this->fieldImageSettings['name'], $this->fieldImageSettings['dir'] . '/');
        } else

        if (!empty($this->fieldImageSettings)) {

            foreach ($this->fieldImageSettings as $image) {

                if (isset($image['name']) && isset($image['dir'])) {
                    $this->uploadImage($id, $image['name'], $image['dir'] . '/');
                }

            }

        }

        return !count($this->errors) ? true : false;
    }

    /**
     * @param int         $id
     * @param string      $name
     * @param string      $dir
     * @param string|bool $ext
     * @param int|null    $width
     * @param int|null    $height
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function uploadImage($id, $name, $dir, $ext = false, $width = null, $height = null) {

        if (isset($_FILES[$name]['tmp_name']) && !empty($_FILES[$name]['tmp_name'])) {
            // Delete old image

            if (Validate::isLoadedObject($object = $this->loadObject())) {
                $object->deleteImage();
            } else {
                return false;
            }

            // Check image validity
            $maxSize = isset($this->max_image_size) ? $this->max_image_size : 0;

            if ($error = ImageManager::validateUpload($_FILES[$name], Tools::getMaxUploadSize($maxSize))) {
                $this->errors[] = $error;
            }

            $tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS');

            if (!$tmpName) {
                return false;
            }

            if (!move_uploaded_file($_FILES[$name]['tmp_name'], $tmpName)) {
                return false;
            }

            // Evaluate the memory required to resize the image: if it's too much, you can't resize it.

            if (!ImageManager::checkImageMemoryLimit($tmpName)) {
                $this->errors[] = Tools::displayError('Due to memory limit restrictions, this image cannot be loaded. Please increase your memory_limit value via your server\'s configuration settings. ');
            }

            // Copy new image

            if (empty($this->errors) && !ImageManager::resize($tmpName, _PS_IMG_DIR_ . $dir . $id . '.' . $this->imageType, (int) $width, (int) $height, ($ext ? $ext : $this->imageType))) {
                $this->errors[] = Tools::displayError('An error occurred while uploading the image.');
            }

            if (count($this->errors)) {
                return false;
            }

            if ($this->afterImageUpload()) {
                unlink($tmpName);

                return true;
            }

            return false;
        }

        return true;
    }

    /**
     * Check rights to view the current tab
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function afterImageUpload() {

        return true;
    }

    /**
     * Called before Add
     *
     * @param ObjectModel $object Object
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function beforeAdd($object) {

        return true;
    }

    protected function beforeUpdate($object) {

        return true;
    }

    /**
     * @param ObjectModel $object
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function afterAdd($object) {

        return true;
    }

    /**
     * Change object required fields
     *
     * @return ObjectModel
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     */
    public function processUpdateFields() {

        if (!is_array($fields = Tools::getValue('fieldsBox'))) {
            $fields = [];
        }

        /** @var $object ObjectModel */
        $object = new $this->className();

        if (!$object->addFieldsRequiredDatabase($fields)) {
            $this->errors[] = Tools::displayError('An error occurred when attempting to update the required fields.');
        } else {
            $this->redirect_after = static::$currentIndex . '&conf=4&token=' . $this->token;
        }

        return $object;
    }

    /**
     * Change object position
     *
     * @return ObjectModel|false
     */
    public function processPosition() {

        if (!Validate::isLoadedObject($object = $this->loadObject())) {
            $this->errors[] = Tools::displayError('An error occurred while updating the status for an object.') .
            ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
        } else

        if (!$object->updatePosition((int) Tools::getValue('way'), (int) Tools::getValue('position'))) {
            $this->errors[] = Tools::displayError('Failed to update the position.');
        } else {
            $idIdentifierStr = ($idIdentifier = (int) Tools::getValue($this->identifier)) ? '&' . $this->identifier . '=' . $idIdentifier : '';
            $redirect = static::$currentIndex . '&' . $this->table . 'Orderby=position&' . $this->table . 'Orderway=asc&conf=5' . $idIdentifierStr . '&token=' . $this->token;
            $this->redirect_after = $redirect;
        }

        return $object;
    }

    /**
     * Cancel all filters for this tab
     *
     * @param int|null $listId
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function processResetFilters($listId = null) {

        if ($listId === null) {
            $listId = isset($this->list_id) ? $this->list_id : $this->table;
        }

        $prefix = str_replace(['admin', 'controller'], '', mb_strtolower(get_class($this)));
        $filters = $this->context->cookie->getFamily($prefix . $listId . 'Filter_');

        foreach ($filters as $cookieKey => $filter) {

            if (strncmp($cookieKey, $prefix . $listId . 'Filter_', 7 + mb_strlen($prefix . $listId)) == 0) {
                $key = substr($cookieKey, 7 + mb_strlen($prefix . $listId));

                if (is_array($this->fields_list) && array_key_exists($key, $this->fields_list)) {
                    $this->context->cookie->$cookieKey = null;
                }

                unset($this->context->cookie->$cookieKey);
            }

        }

        if (isset($this->context->cookie->{'submitFilter' . $listId})) {
            unset($this->context->cookie->{'submitFilter' . $listId});
        }

        if (isset($this->context->cookie->{$prefix . $listId . 'Orderby'})) {
            unset($this->context->cookie->{$prefix . $listId . 'Orderby'});
        }

        if (isset($this->context->cookie->{$prefix . $listId . 'Orderway'})) {
            unset($this->context->cookie->{$prefix . $listId . 'Orderway'});
        }

        $_POST = [];
        $this->_filter = false;
        unset($this->_filterHaving);
        unset($this->_having);
    }

    /**
     * Check if the token is valid, else display a warning page
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function checkAccess() {

        if (!$this->checkToken()) {
            // If this is an XSS attempt, then we should only display a simple, secure page
            // ${1} in the replacement string of the regexp is required,
            // because the token may begin with a number and mix up with it (e.g. $17)
            $url = preg_replace('/([&?]token=)[^&]*(&.*)?$/', '${1}' . $this->token . '$2', $_SERVER['REQUEST_URI']);

            if (false === strpos($url, '?token=') && false === strpos($url, '&token=')) {
                $url .= '&token=' . $this->token;
            }

            if (strpos($url, '?') === false) {
                $url = str_replace('&token', '?controller=AdminDashboard&token', $url);
            }

            $this->context->smarty->assign('url', htmlentities($url));

            return false;
        }

        return true;
    }

    /**
     * Check for security token
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function checkToken() {

        $token = Tools::getValue('token');

        if (!empty($token) && $token === $this->token) {
            return true;
        }

        if (count($_POST) || !isset($_GET['controller']) || !Validate::isControllerName($_GET['controller']) || $token) {

            return false;
        }

        foreach ($_GET as $key => $value) {

            if (is_array($value) || !in_array($key, ['controller', 'controllerUri'])) {

                return false;
            }

        }

        $cookie = $this->context->cookie;
        $whitelist = ['date_add', 'id_lang', 'id_employee', 'email', 'profile', 'passwd', 'remote_addr', 'shopContext', 'collapse_menu', 'checksum'];

        foreach ($cookie->getAll() as $key => $value) {

            if (!in_array($key, $whitelist)) {
                unset($cookie->$key);
            }

        }

        $cookie->write();

        return true;
    }

    /**
     * @return void
     *
     * @throws Exception
     * @throws SmartyException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function displayAjax() {

        if ($this->json) {
            $this->context->smarty->assign(
                [
                    'json'   => true,
                    'status' => $this->status,
                ]
            );
        }

        $this->layout = 'layout-ajax.tpl';
        $this->display_header = false;
        $this->display_header_javascript = false;
        $this->display_footer = false;

        return $this->display();
    }

    public function generateTabScript($controller) {

        return '<script type="text/javascript">' . PHP_EOL . '
                    $(document).ready(function(){' . PHP_EOL . '
                        $( "#content_' . $controller . '").tabs({' . PHP_EOL . '
                            show: { effect: "blind", duration: 800 },' . PHP_EOL . '
                        });' . PHP_EOL . '
                    });' . PHP_EOL . '
                </script>' . PHP_EOL;
    }

    /**
     * @return void
     * @throws Exception
     * @throws SmartyException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function display() {

        $this->context->smarty->assign(
            [
                'display_header'            => $this->display_header,
                'display_header_javascript' => $this->display_header_javascript,
                'display_footer'            => $this->display_footer,
                'js_def'                    => MediaAdmin::getJsDef(),
                'controller'                => Tools::getValue('controller'),
                'display'                   => $this->display,
            ]
        );

        // Use page title from meta_title if it has been set else from the breadcrumbs array

        if (!$this->meta_title) {
            $this->meta_title = $this->toolbar_title;
        }

        if (is_array($this->meta_title)) {
            $this->meta_title = strip_tags(implode(' ' . Configuration::get('PS_NAVIGATION_PIPE') . ' ', $this->meta_title));
        }

        $this->context->smarty->assign('meta_title', $this->meta_title);

        $templateDirs = $this->context->smarty->getTemplateDir();

        // Check if header/footer have been overriden
        $dir = $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR . trim($this->override_folder, '\\/') . DIRECTORY_SEPARATOR;
        $moduleListDir = $this->context->smarty->getTemplateDir(0) . 'helpers' . DIRECTORY_SEPARATOR . 'modules_list' . DIRECTORY_SEPARATOR;

        $headerTpl = file_exists($dir . 'header.tpl') ? $dir . 'header.tpl' : 'header.tpl';
        $pageHeaderToolbar = file_exists($dir . 'page_header_toolbar.tpl') ? $dir . 'page_header_toolbar.tpl' : 'page_header_toolbar.tpl';
        $footerTpl = file_exists($dir . 'footer.tpl') ? $dir . 'footer.tpl' : 'footer.tpl';
        $modalModuleList = file_exists($moduleListDir . 'modal.tpl') ? $moduleListDir . 'modal.tpl' : 'modal.tpl';
        $tplAction = $this->tpl_folder . $this->display . '.tpl';

        // Check if action template has been overriden

        foreach ($templateDirs as $template_dir) {

            if (file_exists($template_dir . DIRECTORY_SEPARATOR . $tplAction) && $this->display != 'view' && $this->display != 'options') {

                if (method_exists($this, $this->display . Tools::toCamelCase($this->className))) {
                    $this->{$this->display . Tools::toCamelCase($this->className)}

                    ();
                }

                $this->context->smarty->assign('content', $this->context->smarty->fetch($tplAction));
                break;
            }

        }

        if (is_array($this->content)) {
            $this->template = 'content-tab.tpl';
            $this->context->smarty->assign([
                'controller' => Tools::getValue('controller'),
                'tabScript'  => $this->generateTabScript(Tools::getValue('controller')),
            ]);
        }

        if ($this->displayGrid) {
            $this->template = 'content-grid.tpl';
        }

        if ($this->displayOptionGrid) {
            $this->template = 'options-grid.tpl';
        }

        if (!$this->ajax) {
            $template = $this->createTemplate($this->template);
            $page = $template->fetch();
        } else {
            $page = $this->content;
        }

        if ($conf = Tools::getValue('conf')) {
            $this->context->smarty->assign('conf', $this->json ? json_encode($this->_conf[(int) $conf]) : $this->_conf[(int) $conf]);
        }

        foreach (['errors', 'warnings', 'informations', 'confirmations'] as $type) {

            if (!is_array($this->$type)) {
                $this->$type = (array) $this->$type;
            }

            $this->context->smarty->assign($type, $this->json ? json_encode(array_unique($this->$type)) : array_unique($this->$type));
        }

        if ($this->show_page_header_toolbar && !$this->lite_display) {
            $this->context->smarty->assign(
                [
                    'page_header_toolbar' => $this->context->smarty->fetch($pageHeaderToolbar),
                    'modal_module_list'   => $this->context->smarty->fetch($modalModuleList),
                ]
            );
        }

        $this->context->smarty->assign(
            [
                'page'   => $this->json ? json_encode($page) : $page,
                'header' => $this->context->smarty->fetch($headerTpl),
                'footer' => $this->context->smarty->fetch($footerTpl),
            ]
        );

        $this->smartyOutputContent($this->layout);
    }

    public function ajaxTabDisplay() {

        $return = [
            'liTab'            => '<li id="' . $this->tab_liId . '" data-controller="' . $this->controller_name . '" class="controller_detail">
                    <a href="#' . $this->tab_link . '" ' . $this->tabOnclick . '  id="' . $this->tab_identifier . '" class="' . $this->controller_name . '-icone" title="' . $this->publicName . '" data-controller="' . $this->controller_name . '">
                    ' . $this->tab_name .
            $this->closeTabButton . '</a>' .
            '</li>',
            'tabPane'          => '<div id="' . $this->tab_link . '" class="main-controller" data-name="' . $this->TitleBar . '" data-link="' . $this->controllerLink . '"  data-controller="' . $this->controller_name . '" >' . $this->content . '</div>',
            'controller'       => $this->controller_name,
            'idController'     => $this->tab_identifier,
            'identifier'       => $this->identifier_value,
            'jsFile'           => $this->ajax_js,
            'paragrid'         => $this->paragrid,
            'scriptHook'       => $this->scriptHook,
            'backOfficeHeader' => $this->displayBackOfficeHeader,
            'backOfficeFooter' => $this->displayBackOfficeFooter,
            'controllerLink'   => $this->controllerLink,
            'title'            => $this->publicName,
        ];

        die(Tools::jsonEncode($return));

    }

    public function ajaxDisplay() {

        $this->layout = 'ajaxlayout.tpl';
        $controller = Tools::getValue('controller');

        $this->context->smarty->assign(
            [
                'display_header'            => $this->display_header,
                'display_header_javascript' => $this->display_header_javascript,
                'display_footer'            => $this->display_footer,
                'controller'                => Tools::getValue('controller'),
                'linkController'            => $this->context->link->getAdminLink(Tools::getValue('controller')),

            ]
        );

        // Use page title from meta_title if it has been set else from the breadcrumbs array

        if (!$this->meta_title) {
            $this->meta_title = $this->toolbar_title;
        }

        if (is_array($this->meta_title)) {
            $this->meta_title = strip_tags(implode(' ' . Configuration::get('PS_NAVIGATION_PIPE') . ' ', $this->meta_title));
        }

        $this->context->smarty->assign('meta_title', $this->meta_title);

        $templateDirs = $this->context->smarty->getTemplateDir();

        // Check if header/footer have been overriden
        $dir = $this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR . trim($this->override_folder, '\\/') . DIRECTORY_SEPARATOR;
        $moduleListDir = $this->context->smarty->getTemplateDir(0) . 'helpers' . DIRECTORY_SEPARATOR . 'modules_list' . DIRECTORY_SEPARATOR;

        $headerScript = file_exists($dir . 'headerScript.tpl') ? $dir . 'headerScript.tpl' : 'headerScript.tpl';

        if ($this->show_page_header_toolbar) {
            $pageHeaderToolbar = file_exists($dir . 'page_header_toolbar.tpl') ? $dir . 'page_header_toolbar.tpl' : 'page_header_toolbar.tpl';
        }

        $footerTpl = file_exists($dir . 'footerScript.tpl') ? $dir . 'footerScript.tpl' : 'footerScript.tpl';
        $modalModuleList = file_exists($moduleListDir . 'modal.tpl') ? $moduleListDir . 'modal.tpl' : 'modal.tpl';
        $tplAction = $this->tpl_folder . $this->display . '.tpl';

        foreach ($templateDirs as $template_dir) {

            if (file_exists($template_dir . DIRECTORY_SEPARATOR . $tplAction) && $this->display != 'view' && $this->display != 'options') {

                if (method_exists($this, $this->display . Tools::toCamelCase($this->className))) {
                    $this->{$this->display . Tools::toCamelCase($this->className)}

                    ();
                }

                $this->context->smarty->assign('content', $this->context->smarty->fetch($tplAction));
                break;
            } else {
                $this->context->smarty->assign('content', $this->content);
            }

        }

        if ($this->paragrid && !$this->fieldsOptions) {
            $this->template = 'content-grid.tpl';
            $this->context->smarty->assign([
                'paragrid' => true,
            ]);
        } else

        if (is_array($this->content)) {

            $this->template = 'content-tab.tpl';
            $this->context->smarty->assign([
                'controller' => Tools::getValue('controller'),
                'tabScript'  => $this->generateTabScript(Tools::getValue('controller')),
            ]);
        }

        $template = $this->createTemplate($this->template);

        $page = $template->fetch();

        if ($conf = Tools::getValue('conf')) {
            $this->context->smarty->assign('conf', $this->json ? json_encode($this->_conf[(int) $conf]) : $this->_conf[(int) $conf]);
        }

        foreach (['errors', 'warnings', 'informations', 'confirmations'] as $type) {

            if (!is_array($this->$type)) {
                $this->$type = (array) $this->$type;
            }

            $this->context->smarty->assign($type, $this->json ? json_encode(array_unique($this->$type)) : array_unique($this->$type));
        }

        if ($this->show_page_header_toolbar) {
            $this->context->smarty->assign(
                [
                    'page_header_toolbar' => $this->context->smarty->fetch($pageHeaderToolbar),
                ]
            );
        }

        if ($this->show_header_script) {
            $this->context->smarty->assign(
                [
                    'header_script' => $this->context->smarty->fetch($headerScript),
                ]
            );

        }

        $this->context->smarty->assign(
            [
                'page' => $this->json ? json_encode($page) : $page,

            ]
        );

        if ($this->show_footer_script) {
            $this->context->smarty->assign(
                [
                    'footer' => $this->context->smarty->fetch($footerTpl),
                ]
            );

        }

        return $this->smartyOutputContent($this->layout);
    }

    public function combineCss() {

        $return = '';

        if (!empty($this->css_files)) {

            foreach ($this->css_files as $key => $css_file) {

                $return .= '<link rel="stylesheet" href="' . $key . '" type="text/css" media="all">' . PHP_EOL;
            }

        }

        return $return;

    }

    public function compressJs() {

        if (is_array($this->js_headfiles)) {
            $return = '';

            foreach ($this->js_headfiles as $js_file) {
                $return = $return . file_get_contents(_PS_CORE_DIR_ . $js_file);
            }

            return '<script type="text/javascript">' . PHP_EOL . JSMin::minify($return) . PHP_EOL . '</script>';
        }

    }

    public function compileJsDefController($curJsDefs, $js_defs) {

        $headJsDef = [];

        if (is_array($curJsDefs) && count($curJsDefs)) {

            foreach ($curJsDefs as $key => $value) {
                $value = explode("=", $value);
                $key = trim($value[0]);
                $value = trim($value[1]);
                $headJsDef[$key] = $value;
            }

        }

        $jsDef = '<script type="text/javascript"  data-script="headJsDef">' . PHP_EOL;

        if (is_array($js_defs) && count($js_defs)) {

            foreach ($js_defs as $key => $js_def) {

                if (isset($headJsDef[$key])) {
                    continue;
                }

                if (is_array($js_def)) {

                    $jsDef .= 'var ' . $key . ' = ' . Tools::jsonEncode($js_def) . PHP_EOL;
                } else

                if (is_int($js_def)) {
                    $jsDef .= 'var ' . $key . ' = ' . $js_def . PHP_EOL;
                } else {
                    $jsDef .= 'var ' . $key . ' = \'' . $js_def . '\'' . PHP_EOL;
                }

            }

        }

        $jsDef .= '</script>';
        return $jsDef;
    }

    /**
     * Create a template from the override file, else from the base file.
     *
     * @param string $tplName filename
     *
     * @return Smarty_Internal_Template
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function createTemplate($tplName) {

        // Use override tpl if it exists
        // If view access is denied, we want to use the default template that will be used to display an error

        if ($this->viewAccess() && $this->override_folder) {

            if (!Configuration::get('PS_DISABLE_OVERRIDES') && file_exists($this->context->smarty->getTemplateDir(1) . DIRECTORY_SEPARATOR . $this->override_folder . $tplName)) {
                return $this->context->smarty->createTemplate($this->override_folder . $tplName, $this->context->smarty);
            } else

            if (file_exists($this->context->smarty->getTemplateDir(0) . 'controllers' . DIRECTORY_SEPARATOR . $this->override_folder . $tplName)) {
                return $this->context->smarty->createTemplate('controllers' . DIRECTORY_SEPARATOR . $this->override_folder . $tplName, $this->context->smarty);
            }

        }

        return $this->context->smarty->createTemplate($this->context->smarty->getTemplateDir(0) . $tplName, $this->context->smarty);
    }

    public function manageFieldsVisibility($fields) {

        $return = [];

        if (is_array($fields)) {

            foreach ($fields as $field) {
                $name = '';
                $hidden = false;
                $hiddenable = 'yes';

                foreach ($field as $key => $value) {

                    if ($key == 'title') {
                        $name = $value;
                    }

                    if ($key == 'hidden') {
                        $hidden = $value;
                    }

                    if ($key == 'hiddenable') {
                        $hiddenable = $value;
                    }

                }

                $return[$name] = $field;
                $return[$name]['hidden'] = $hidden;
                $return[$name]['hiddenable'] = $hiddenable;
            }

        }

        return $return;
    }

    /**
     * Check rights to view the current tab
     *
     * @param bool $disable
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function viewAccess($disable = false) {

        if ($disable) {
            return true;
        }

        if ($this->tabAccess['view'] == 1) {
            return true;
        }

        return false;
    }

    /**
     * Assign smarty variables for the header
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initHeader() {

        header('Cache-Control: no-store, no-cache');

        // Multishop
        $isMultishop = Shop::isFeatureActive();

        // Quick access

        $topbars = EmployeeMenu::getEmployeeMenus($this->context->language->id, 1);

        foreach ($topbars as $index => $tab) {

            if (!EmployeeMenu::checkTabRights($tab['id_employee_menu']) || !$tab['visible']) {
                unset($topbars[$index]);
                continue;
            }

            $topbars[$index]['function'] = $tab['function'];
            $topbars[$index]['name'] = $tab['name'];
            $tabs[$index]['img'] = $img;
            $subTabs = EmployeeMenu::getEmployeeMenus($this->context->language->id, $tab['id_employee_menu']);

            foreach ($subTabs as $index2 => $subTab) {

                if (!EmployeeMenu::checkTabRights($subTab['id_employee_menu'])) {
                    unset($subTabs[$index2]);
                    continue;
                }

                if ((bool) $subTab['active']) {

                    $subTabs[$index2]['function'] = $subTab['function'];
                    $subTabs[$index2]['name'] = $subTab['name'];
                    $subTabs[$index2]['img'] = $img;
                }

                $terTabs = EmployeeMenu::getEmployeeMenus($this->context->language->id, $subTab['id_employee_menu']);

                foreach ($terTabs as $index3 => $terTab) {

                    if (!EmployeeMenu::checkTabRights($terTab['id_employee_menu'])) {
                        unset($terTabs[$index3]);
                        continue;
                    }

                    if ((bool) $terTab['active']) {

                        $terTabs[$index3]['function'] = $terTab['function'];
                        $terTabs[$index3]['name'] = $terTab['name'];
                        $terTabs[$index3]['img'] = $img;
                    }

                }

                $subTabs[$index2]['sub_tabs'] = array_values($terTabs);

            }

            $topbars[$index]['sub_tabs'] = array_values($subTabs);
        }

        if (Validate::isLoadedObject($this->context->employee)) {

            $helperShop = new HelperShop();

            $this->context->smarty->assign(
                [
                    'autorefresh_notifications' => Configuration::get('PS_ADMINREFRESH_NOTIFICATION'),
                    'round_mode'                => Configuration::get('PS_PRICE_ROUND_MODE'),
                    'employee'                  => $this->context->employee,
                    'multi_shop'                => Shop::isFeatureActive(),
                    'shop_list'                 => $helperShop->getRenderedShopList(),
                    'shop'                      => $this->context->shop,
                    'shop_group'                => new ShopGroup((int) Shop::getContextShopGroupID()),
                    'is_multishop'              => $isMultishop,
                    'multishop_context'         => $this->multishop_context,
                    'default_tab_link'          => $this->context->link->getAdminLink(EmployeeMenu::getClassNameById((int) $this->context->employee->default_tab)),
                    'login_link'                => $this->context->link->getAdminLink('AdminLogin'),
                    'collapse_menu'             => isset($this->context->cookie->collapse_menu) ? (int) $this->context->cookie->collapse_menu : 0,
                ]
            );
        } else {
            $this->context->smarty->assign('default_tab_link', $this->context->link->getAdminLink('AdminDashboard'));
        }

        // Shop::initialize() in config.php may empty $this->context->shop->virtual_uri so using a new shop instance for getBaseUrl()
        $this->context->shop = new Shop((int) $this->context->shop->id);

        $this->context->smarty->assign(
            [
                'img_dir'            => _PS_IMG_,
                'themes'             => $this->themes,
                'bo_img_dir'         => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
                'iso'                => $this->context->language->iso_code,
                'class_name'         => $this->className,
                'iso_user'           => $this->context->language->iso_code,
                'country_iso_code'   => $this->context->country->iso_code,
                'version'            => _PS_VERSION_,
                'lang_iso'           => $this->context->language->iso_code,
                'full_language_code' => $this->context->language->language_code,
                'link'               => $this->context->link,
                'shop_name'          => Configuration::get('PS_SHOP_NAME'),
                'base_url'           => $this->context->shop->getBaseURL(),
                'topbars'            => $topbars,
                'pic_dir'            => _THEME_PROD_PIC_DIR_,
                'controller_name'    => htmlentities(Tools::getValue('controller')),
                'currentIndex'       => static::$currentIndex,
                'bootstrap'          => $this->bootstrap,
                'default_language'   => (int) Configuration::get('PS_LANG_DEFAULT'),
            ]
        );

    }

    /**
     * Declare an action to use for each row in the list
     *
     * @param string $action
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function addRowAction($action) {

        $action = strtolower($action);
        $this->actions[] = $action;
    }

    /**
     * Add an action to use for each row in the list
     *
     * @param string $action
     * @param array  $list
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function addRowActionSkipList($action, $list) {

        $action = strtolower($action);
        $list = (array) $list;

        if (array_key_exists($action, $this->list_skip_actions)) {
            $this->list_skip_actions[$action] = array_merge($this->list_skip_actions[$action], $list);
        } else {
            $this->list_skip_actions[$action] = $list;
        }

    }

    /**
     * Assign smarty variables for all default views, list and form, then call other init functions
     *
     * @return void
     *
     * @throws Exception
     * @throws PhenyxShopException
     * @throws SmartyException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function initContent() {

        if (!$this->viewAccess()) {
            $this->errors[] = Tools::displayError('You do not have permission to view this.');

            return;
        }

        if ($this->fields_list && is_array($this->fields_list)) {
            $this->fieldsList = true;
        }

        if ($this->fields_options && is_array($this->fields_options)) {
            $this->fieldsOptions = true;
        }

        $this->getLanguages();
        $this->initTabModuleList();

        if ($this->display == 'edit' || $this->display == 'add') {

            if (!$this->loadObject(true)) {
                return;
            }

            $this->content .= $this->renderForm();
        } else

        if ($this->display == 'view') {
            // Some controllers use the view action without an object

            if ($this->className) {
                $this->loadObject(true);
            }

            $this->content .= $this->renderView();
        } else

        if ($this->display == 'details') {
            $this->content .= $this->renderDetails();
        } else

        if (!$this->ajax) {

            if ($this->tabList == true) {
                $this->content = $this->renderList();
            } else {
                $this->content .= $this->renderModulesList();
                $this->content .= $this->renderKpis();

                if ($this->fieldsList == true && $this->fieldsOptions == true) {
                    $this->content .= $this->renderOptions();
                } else {
                    $this->content .= $this->renderList();
                    $this->content .= $this->renderOptions();
                }

            }

            // if we have to display the required fields form

            if ($this->required_database) {
                $this->content .= $this->displayRequiredFields();
            }

        }

        $messenger = Configuration::get('PS_MESSENGER_FEATURE_ACTIVE');

        if ($messenger == 1) {
            $this->context->smarty->assign(
                [

                    'onlines' => $this->getMemberOnline(),
                ]
            );
        }

        $this->context->smarty->assign(
            [
                'maintenance_mode'          => !(bool) Configuration::get('PS_SHOP_ENABLE'),
                'content'                   => $this->content,
                'lite_display'              => $this->lite_display,
                'url_post'                  => static::$currentIndex . '&token=' . $this->token,
                'show_page_header_toolbar'  => $this->show_page_header_toolbar,
                'page_header_toolbar_title' => $this->page_header_toolbar_title,
                'title'                     => $this->page_header_toolbar_title,
                'toolbar_btn'               => $this->page_header_toolbar_btn,
                'page_header_toolbar_btn'   => $this->page_header_toolbar_btn,
                'controller'                => Tools::getValue('controller'),
                'bo_imgdir'                 => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
                'messenger'                 => $messenger,
                'link'                      => $this->context->link,
                'versionTheme'              => Configuration::get('_EPHENYX_THEME_VERSION_'),
            ]
        );

        MediaAdmin::addJsDef([
            'AjaxLinkBackUsers'   => $this->context->link->getAdminLink('AdminBackUsers'),
			'AjaxLinkEmployees'   => $this->context->link->getAdminLink('AdminEmployees'),
            'shop_path'           => 'https://' . ShopUrl::getMainShopDomainSSL(),
            'AjaxLinkAdminStates' => $this->context->link->getAdminLink('AdminStates'),
            'currentLang'         => $this->context->language->id,
            'AjaxMemberId'        => $this->context->employee->id,

        ]);
    }

    public function ajaxProcessGetAdminLinkController() {

        $controller = Tools::getValue('souceController');
        $link = $this->context->link->getAdminLink($controller);
        $return = [
            'link' => $link,
        ];

        die(Tools::jsonEncode($return));

    }

    /**
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getLanguages() {

        $cookie = $this->context->cookie;
        $this->allow_employee_form_lang = (int) Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG');

        if ($this->allow_employee_form_lang && !$cookie->employee_form_lang) {
            $cookie->employee_form_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        $langExists = false;
        $this->_languages = Language::getLanguages(false);

        foreach ($this->_languages as $lang) {

            if (isset($cookie->employee_form_lang) && $cookie->employee_form_lang == $lang['id_lang']) {
                $langExists = true;
            }

        }

        $this->default_form_language = $langExists ? (int) $cookie->employee_form_lang : (int) Configuration::get('PS_LANG_DEFAULT');

        foreach ($this->_languages as $k => $language) {
            $this->_languages[$k]['is_default'] = (int) ($language['id_lang'] == $this->default_form_language);
        }

        return $this->_languages;
    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function addToolBarModulesListButton() {

        $this->filterTabModuleList();

        if (is_array($this->tab_modules_list['slider_list']) && count($this->tab_modules_list['slider_list'])) {
            $this->toolbar_btn['modules-list'] = [
                'href' => '#',
                'desc' => $this->l('Recommended Modules and Services'),
            ];
        }

    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function filterTabModuleList() {

        static $listIsFiltered = null;

        if ($listIsFiltered !== null) {
            return;
        }

        libxml_use_internal_errors(true);

        $allModuleList = [];

        libxml_clear_errors();

        $this->tab_modules_list['slider_list'] = array_intersect($this->tab_modules_list['slider_list'], $allModuleList);

        $listIsFiltered = true;
    }

    /**
     * Init tab modules list and add button in toolbar
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function initTabModuleList() {

        $this->tab_modules_list = EmployeeMenu::getTabModulesList($this->id);

        if (is_array($this->tab_modules_list['default_list']) && count($this->tab_modules_list['default_list'])) {
            $this->filter_modules_list = $this->tab_modules_list['default_list'];
        } else

        if (is_array($this->tab_modules_list['slider_list']) && count($this->tab_modules_list['slider_list'])) {
            $this->addToolBarModulesListButton();
            $this->addPageHeaderToolBarModulesListButton();
            $this->context->smarty->assign(
                [
                    'tab_modules_list'      => implode(',', $this->tab_modules_list['slider_list']),
                    'admin_module_ajax_url' => $this->context->link->getAdminLink('AdminModules'),
                    'back_tab_modules_list' => $this->context->link->getAdminLink(Tools::getValue('controller')),
                    'tab_modules_open'      => (int) Tools::getValue('tab_modules_open'),
                ]
            );
        }

    }

    /**
     * @param string $file
     * @param int    $timeout
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function isFresh($file, $timeout = 604800) {

        if (($time = @filemtime(_PS_ROOT_DIR_ . $file)) && filesize(_PS_ROOT_DIR_ . $file) > 0) {
            return ((time() - $time) < $timeout);
        }

        return false;
    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function addPageHeaderToolBarModulesListButton() {

        $this->filterTabModuleList();

        if (is_array($this->tab_modules_list['slider_list']) && count($this->tab_modules_list['slider_list'])) {
            $this->page_header_toolbar_btn['modules-list'] = [
                'href' => '#',
                'desc' => $this->l('Recommended Modules and Services'),
            ];
        }

    }

    /**
     * Add an entry to the meta title.
     *
     * @param string $entry New entry.
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function addMetaTitle($entry) {

        // Only add entry if the meta title was not forced.

        if (is_array($this->meta_title)) {
            $this->meta_title[] = $entry;
        }

    }

    public function recurseShortCode($content) {

        $handlers = self::$staticShortcodeHandler;
        $processor = new Processor(new RegularParser(), $handlers);
        return $processor->process($content);
    }

    /**
     * Function used to render the form for this controller
     *
     * @return string
     * @throws Exception
     * @throws SmartyException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function renderForm() {

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        if (Tools::getValue('submitFormAjax')) {
            $this->content .= $this->context->smarty->fetch('form_submit_ajax.tpl');
        }

        if ($this->fields_form && is_array($this->fields_form)) {

            if (!$this->multiple_fieldsets) {
                $this->fields_form = [['form' => $this->fields_form]];
            }

            // For add a fields via an override of $fields_form, use $fields_form_override

            if (is_array($this->fields_form_override) && !empty($this->fields_form_override)) {
                $this->fields_form[0]['form']['input'] = array_merge($this->fields_form[0]['form']['input'], $this->fields_form_override);
            }

            $fieldsValue = $this->getFieldsValue($this->object);

            Hook::exec(
                'action' . $this->controller_name . 'FormModifier', [
                    'fields'       => &$this->fields_form,
                    'fields_value' => &$fieldsValue,
                    'form_vars'    => &$this->tpl_form_vars,
                ]
            );

            if ($this->tabList == true) {
                $this->tpl_form_vars['controller'] = Tools::getValue('controller');
                $this->tpl_form_vars['tabScript'] = $this->generateTabScript(Tools::getValue('controller'));
            }

            $helper = new HelperForm($this);
            $this->setHelperDisplay($helper);
            $helper->fields_value = $fieldsValue;
            $helper->submit_action = $this->submit_action;
            $helper->tpl_vars = $this->getTemplateFormVars();
            $helper->show_cancel_button = (isset($this->show_form_cancel_button)) ? $this->show_form_cancel_button : ($this->display == 'add' || $this->display == 'edit');

            $back = Tools::safeOutput(Tools::getValue('back', ''));

            if (empty($back)) {
                $back = static::$currentIndex . '&token=' . $this->token;
            }

            if (!Validate::isCleanHtml($back)) {
                die(Tools::displayError());
            }

            $helper->back_url = $back;
            !is_null($this->base_tpl_form) ? $helper->base_tpl = $this->base_tpl_form : '';

            if ($this->tabAccess['view']) {

                if (Tools::getValue('back')) {
                    $helper->tpl_vars['back'] = Tools::safeOutput(Tools::getValue('back'));
                } else {
                    $helper->tpl_vars['back'] = Tools::safeOutput(Tools::getValue(static::$currentIndex . '&token=' . $this->token));
                }

            }

            $form = $helper->generateForm($this->fields_form);

            return $form;
        }

    }

    /**
     * Return the list of fields value
     *
     * @param ObjectModel $obj Object
     *
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getFieldsValue($obj) {

        foreach ($this->fields_form as $fieldset) {

            if (isset($fieldset['form']['input'])) {

                foreach ($fieldset['form']['input'] as $input) {

                    if (!isset($this->fields_value[$input['name']])) {

                        if (isset($input['type']) && $input['type'] == 'shop') {

                            if ($obj->id) {
                                $result = Shop::getShopById((int) $obj->id, $this->identifier, $this->table);

                                foreach ($result as $row) {
                                    $this->fields_value['shop'][$row['id_' . $input['type']]][] = $row['id_shop'];
                                }

                            }

                        } else

                        if (isset($input['lang']) && $input['lang']) {

                            foreach ($this->_languages as $language) {
                                $fieldValue = $this->getFieldValue($obj, $input['name'], $language['id_lang']);

                                if (empty($fieldValue)) {

                                    if (isset($input['default_value']) && is_array($input['default_value']) && isset($input['default_value'][$language['id_lang']])) {
                                        $fieldValue = $input['default_value'][$language['id_lang']];
                                    } else

                                    if (isset($input['default_value'])) {
                                        $fieldValue = $input['default_value'];
                                    }

                                }

                                $this->fields_value[$input['name']][$language['id_lang']] = $fieldValue;
                            }

                        } else {
                            $fieldValue = $this->getFieldValue($obj, $input['name']);

                            if ($fieldValue === false && isset($input['default_value'])) {
                                $fieldValue = $input['default_value'];
                            }

                            $this->fields_value[$input['name']] = $fieldValue;
                        }

                    }

                }

            }

        }

        return $this->fields_value;
    }

    /**
     * Return field value if possible (both classical and multilingual fields)
     *
     * Case 1 : Return value if present in $_POST / $_GET
     * Case 2 : Return object value
     *
     * @param ObjectModel $obj    Object
     * @param string      $key    Field name
     * @param int|null    $idLang Language id (optional)
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getFieldValue($obj, $key, $idLang = null) {

        if ($idLang) {
            $defaultValue = (isset($obj->id) && $obj->id && isset($obj->{$key}

                [$idLang])) ? $obj->{$key}

            [$idLang] : false;
        } else {
            $defaultValue = isset($obj->{$key}) ? $obj->{$key}

            : false;
        }

        $return = Tools::getValue($key . ($idLang ? '_' . $idLang : ''), $defaultValue);

        return Tools::getValue($key . ($idLang ? '_' . $idLang : ''), $defaultValue);
    }

    /**
     * This function sets various display options for helper list
     *
     * @param Helper $helper
     *
     * @return void
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setHelperDisplay(Helper $helper) {

        // tocheck

        if ($this->object && $this->object->id) {
            $helper->id = $this->object->id;
        }

        // @todo : move that in Helper
        $helper->title = '';
        $helper->toolbar_btn = $this->toolbar_btn;
        $helper->show_toolbar = $this->show_toolbar;
        $helper->toolbar_scroll = $this->toolbar_scroll;
        $helper->override_folder = $this->tpl_folder;
        $helper->actions = $this->actions;
        $helper->simple_header = $this->list_simple_header;
        $helper->bulk_actions = $this->bulk_actions;
        $helper->currentIndex = static::$currentIndex;
        $helper->className = $this->className;
        $helper->table = $this->table;
        $helper->name_controller = Tools::getValue('controller');
        $helper->orderBy = $this->_orderBy;
        $helper->orderWay = $this->_orderWay;
        $helper->listTotal = $this->_listTotal;
        $helper->shopLink = $this->shopLink;
        $helper->shopLinkType = $this->shopLinkType;
        $helper->identifier = $this->identifier;
        $helper->token = $this->token;
        $helper->languages = $this->_languages;
        $helper->specificConfirmDelete = $this->specificConfirmDelete;
        $helper->imageType = $this->imageType;
        $helper->no_link = $this->list_no_link;
        $helper->colorOnBackground = $this->colorOnBackground;
        $helper->ajax_params = (isset($this->ajax_params) ? $this->ajax_params : null);
        $helper->default_form_language = $this->default_form_language;
        $helper->allow_employee_form_lang = $this->allow_employee_form_lang;
        $helper->multiple_fieldsets = $this->multiple_fieldsets;
        $helper->row_hover = $this->row_hover;
        $helper->position_identifier = $this->position_identifier;
        $helper->position_group_identifier = $this->position_group_identifier;
        $helper->controller_name = $this->controller_name;
        $helper->list_id = isset($this->list_id) ? $this->list_id : $this->table;
        $helper->bootstrap = $this->bootstrap;

        // For each action, try to add the corresponding skip elements list
        $helper->list_skip_actions = $this->list_skip_actions;

        $this->helper = $helper;
    }

    public function getUserIpAddr() {

        return $_SERVER['SERVER_ADDR'];
    }

    /**
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplateFormVars() {

        return $this->tpl_form_vars;
    }

    /**
     * Override to render the view page
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function renderView() {

        $helper = new HelperView($this);
        $this->setHelperDisplay($helper);
        $helper->tpl_vars = $this->getTemplateViewVars();

        if (!is_null($this->base_tpl_view)) {
            $helper->base_tpl = $this->base_tpl_view;
        }

        $view = $helper->generateView();

        return $view;
    }

    /**
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplateViewVars() {

        return $this->tpl_view_vars;
    }

    /**
     * Override to render the view page
     *
     * @return string|false
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function renderDetails() {

        return $this->renderList();
    }

    /**
     * Function used to render the list to display for this controller
     *
     * @return string|false
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopExceptionCore
     */
    public function renderList() {

        if ($this->displayGrid) {
            return false;
        }

        if (!($this->fields_list && is_array($this->fields_list))) {
            return false;
        }

        $this->getList($this->context->language->id);

        // If list has 'active' field, we automatically create bulk action

        if (isset($this->fields_list) && is_array($this->fields_list) && array_key_exists('active', $this->fields_list)
            && !empty($this->fields_list['active'])
        ) {

            if (!is_array($this->bulk_actions)) {
                $this->bulk_actions = [];
            }

            $this->bulk_actions = array_merge(
                [
                    'enableSelection'  => [
                        'text' => $this->l('Enable selection'),
                        'icon' => 'icon-power-off text-success',
                    ],
                    'disableSelection' => [
                        'text' => $this->l('Disable selection'),
                        'icon' => 'icon-power-off text-danger',
                    ],
                    'divider'          => [
                        'text' => 'divider',
                    ],
                ],
                $this->bulk_actions
            );
        }

        $helper = new HelperList();

        // Empty list is ok

        if (!is_array($this->_list)) {
            $this->displayWarning($this->l('Bad SQL query', 'Helper') . '<br />' . htmlspecialchars($this->_list_error));

            return false;
        }

        $this->setHelperDisplay($helper);
        $helper->_default_pagination = $this->_default_pagination;
        $helper->_pagination = $this->_pagination;
        $helper->tpl_vars = $this->getTemplateListVars();
        $helper->tpl_delete_link_vars = $this->tpl_delete_link_vars;

        // For compatibility reasons, we have to check standard actions in class attributes

        foreach ($this->actions_available as $action) {

            if (!in_array($action, $this->actions) && isset($this->$action) && $this->$action) {
                $this->actions[] = $action;
            }

        }

        $helper->is_cms = $this->is_cms;
        $helper->sql = $this->_listsql;
        $helper->tableName = $this->tableName;
        $helper->listScript = $this->generateListScript();
        $list = $helper->generateList($this->_list, $this->fields_list);

        return $list;
    }

    public function generateListScript() {

        $script = '$("#table-' . $this->tableName . '>tbody").selectable({
                            filter: "tr",

                    });' . PHP_EOL;

        return $script;
    }

    public function renderTabList() {

        if (!($this->fields_tablist && !is_array($this->fields_tablist))) {
            return false;
        }

        $this->_list = [];

        foreach ($this->fields_tablist as $field_list) {
            $this->fields_list = $field_list;
            $this->_list[] = $this->renderList();
        }

        $helper = new HelperTab();

        $list = $helper->generateTab($this->_list, $this->fields_tablist);

        return $list;
    }

    /**
     * Add a warning message to display at the top of the page
     *
     * @param string $msg
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function displayWarning($msg) {

        $this->warnings[] = $msg;
    }

    /**
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTemplateListVars() {

        return $this->tpl_list_vars;
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function renderModulesList() {

        // Load cached modules (from the `tbupdater` module)
        $jsonModules = false;
        $updater = Module::getInstanceByName('tbupdater');

        if (Validate::isLoadedObject($updater)) {
            /** @var TbUpdater $updater */
            $jsonModules = $updater->getCachedModulesInfo();
        }

        if ($jsonModules) {

            foreach ($jsonModules as $moduleName => $jsonModule) {
                /** @var array $jsonModules */

                if (isset($jsonModule['type']) && $jsonModule['type'] === 'partner') {
                    $this->list_partners_modules = $moduleName;
                } else {
                    $this->list_natives_modules = $moduleName;
                }

            }

        }

        if ($this->getModulesList($this->filter_modules_list)) {
            $tmp = [];

            foreach ($this->modules_list as $key => $module) {

                if ($module->active) {
                    $tmp[] = $module;
                    unset($this->modules_list[$key]);
                }

            }

            $this->modules_list = array_merge($tmp, $this->modules_list);

            foreach ($this->modules_list as $key => $module) {

                if (in_array($module->name, $this->list_partners_modules)) {
                    $this->modules_list[$key]->type = 'addonsPartner';
                }

                if (isset($module->description_full) && trim($module->description_full) != '') {
                    $module->show_quick_view = true;
                }

            }

            $helper = new Helper();

            return $helper->renderModulesList($this->modules_list);
        }

    }

    /**
     * @param array|string $filterModulesList
     *
     * @return bool
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getModulesList($filterModulesList) {

        if (!is_array($filterModulesList) && !is_null($filterModulesList)) {
            $filterModulesList = [$filterModulesList];
        }

        if (is_null($filterModulesList) || !count($filterModulesList)) {
            return false;
        }

        //if there is no modules to display just return false;

        $allModules = Module::getModulesOnDisk(true);
        $this->modules_list = [];

        foreach ($allModules as $module) {
            $perm = true;

            if ($module->id) {
                $perm &= Module::getPermissionStatic($module->id, 'configure');
            } else {
                $idAdminModule = EmployeeMenu::getIdFromClassName('AdminModules');
                $access = Profile::getProfileAccess($this->context->employee->id_profile, $idAdminModule);

                if (!$access['edit']) {
                    $perm &= false;
                }

            }

            if (in_array($module->name, $filterModulesList) && $perm) {
                $this->fillModuleData($module, 'array');
                $this->modules_list[array_search($module->name, $filterModulesList)] = $module;
            }

        }

        ksort($this->modules_list);

        if (count($this->modules_list)) {
            return true;
        }

        return false; //no module found on disk just return false;
    }

    /**
     * @param string $fileToRefresh
     * @param string $externalFile
     *
     * @return bool
     */
    public function refresh($fileToRefresh, $externalFile) {

        $guzzle = new GuzzleHttp\Client([
            'timeout' => 5,
            'verify'  => _PS_TOOL_DIR_ . 'cacert.pem',
        ]);

        if (static::$isEphenyxUp) {
            try {
                $content = (string) $guzzle->get($externalFile)->getBody();

                return (bool) file_put_contents(_PS_ROOT_DIR_ . $fileToRefresh, $content);
            } catch (Exception $e) {
                static::$isEphenyxUp = false;

                return false;
            }

        }

        return false;
    }

    /**
     * @param Module      $module
     * @param string      $outputType
     * @param string|null $back
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function fillModuleData(&$module, $outputType = 'link', $back = null) {

        /** @var Module $obj */
        $obj = null;

        if ($module->onclick_option) {
            $obj = new $module->name();
        }

        // Fill module data
        $module->logo = '../../img/questionmark.png';

        if (@filemtime(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . basename(_PS_MODULE_DIR_) . DIRECTORY_SEPARATOR . $module->name . DIRECTORY_SEPARATOR . 'logo.gif')) {
            $module->logo = 'logo.gif';
        }

        if (@filemtime(_PS_ROOT_DIR_ . DIRECTORY_SEPARATOR . basename(_PS_MODULE_DIR_) . DIRECTORY_SEPARATOR . $module->name . DIRECTORY_SEPARATOR . 'logo.png')) {
            $module->logo = 'logo.png';
        }

        $linkAdminModules = $this->context->link->getAdminLink('AdminModules', true);

        $module->options['install_url'] = $linkAdminModules . '&install=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&module_name=' . $module->name . '&anchor=' . ucfirst($module->name);
        $module->options['update_url'] = $linkAdminModules . '&update=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&module_name=' . $module->name . '&anchor=' . ucfirst($module->name);
        $module->options['uninstall_url'] = $linkAdminModules . '&uninstall=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&module_name=' . $module->name . '&anchor=' . ucfirst($module->name);

        $module->optionsHtml = $this->displayModuleOptions($module, $outputType, $back);

        $module->options['uninstall_onclick'] = ((!$module->onclick_option) ?
            ((empty($module->confirmUninstall)) ? 'return confirm(\'' . $this->l('Do you really want to uninstall this module?') . '\');' : 'return confirm(\'' . addslashes($module->confirmUninstall) . '\');') :
            $obj->onclickOption('uninstall', $module->options['uninstall_url']));

        if ((Tools::getValue('module_name') == $module->name || in_array($module->name, explode('|', Tools::getValue('modules_list')))) && (int) Tools::getValue('conf') > 0) {
            $module->message = $this->_conf[(int) Tools::getValue('conf')];
        }

        if ((Tools::getValue('module_name') == $module->name || in_array($module->name, explode('|', Tools::getValue('modules_list')))) && (int) Tools::getValue('conf') > 0) {
            unset($obj);
        }

    }

    /**
     * Display modules list
     *
     * @param Module      $module
     * @param string      $outputType (link or select)
     * @param string|null $back
     *
     * @return string|array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function displayModuleOptions($module, $outputType = 'link', $back = null) {

        if (!isset($module->enable_device)) {
            $module->enable_device = Context::DEVICE_COMPUTER | Context::DEVICE_TABLET | Context::DEVICE_MOBILE;
        }

        $this->translationsTab['confirm_uninstall_popup'] = (isset($module->confirmUninstall) ? $module->confirmUninstall : $this->l('Do you really want to uninstall this module?'));

        if (!isset($this->translationsTab['Disable this module'])) {
            $this->translationsTab['Disable this module'] = $this->l('Disable this module');
            $this->translationsTab['Enable this module for all shops'] = $this->l('Enable this module for all shops');
            $this->translationsTab['Disable'] = $this->l('Disable');
            $this->translationsTab['Enable'] = $this->l('Enable');
            $this->translationsTab['Disable on mobiles'] = $this->l('Disable on mobiles');
            $this->translationsTab['Disable on tablets'] = $this->l('Disable on tablets');
            $this->translationsTab['Disable on computers'] = $this->l('Disable on computers');
            $this->translationsTab['Display on mobiles'] = $this->l('Display on mobiles');
            $this->translationsTab['Display on tablets'] = $this->l('Display on tablets');
            $this->translationsTab['Display on computers'] = $this->l('Display on computers');
            $this->translationsTab['Reset'] = $this->l('Reset');
            $this->translationsTab['Configure'] = $this->l('Configure');
            $this->translationsTab['Delete'] = $this->l('Delete');
            $this->translationsTab['Install'] = $this->l('Install');
            $this->translationsTab['Uninstall'] = $this->l('Uninstall');
            $this->translationsTab['Would you like to delete the content related to this module ?'] = $this->l('Would you like to delete the content related to this module ?');
            $this->translationsTab['This action will permanently remove the module from the server. Are you sure you want to do this?'] = $this->l('This action will permanently remove the module from the server. Are you sure you want to do this?');
            $this->translationsTab['Remove from Favorites'] = $this->l('Remove from Favorites');
            $this->translationsTab['Mark as Favorite'] = $this->l('Mark as Favorite');
        }

        $linkAdminModules = $this->context->link->getAdminLink('AdminModules', true);
        $modulesOptions = [];

        $configureModule = [
            'href'    => $linkAdminModules . '&configure=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&ajax=1&module_name=' . urlencode($module->name),
            'onclick' => $module->onclick_option && isset($module->onclick_option_content['configure']) ? $module->onclick_option_content['configure'] : '',
            'title'   => '',
            'text'    => 'Configure',
            'cond'    => $module->id && isset($module->is_configurable) && $module->is_configurable,
            'icon'    => 'wrench',
        ];

        $deactivateModule = [
            'href'  => $linkAdminModules . '&action=disableModule&ajax=1&module_name=' . urlencode($module->name) . '&tab_module=' . $module->tab,
            'title' => Shop::isFeatureActive() ? htmlspecialchars($module->active ? $this->translationsTab['Disable this module'] : $this->translationsTab['Enable this module for all shops']) : '',
            'text'  => 'Disable',
            'cond'  => $module->id,
            'icon'  => 'off',
        ];
        $activateModule = [
            'href'  => $linkAdminModules . '&action=enableModule&ajax=1&module_name=' . urlencode($module->name) . '&tab_module=' . $module->tab,
            'title' => Shop::isFeatureActive() ? htmlspecialchars($module->active ? $this->translationsTab['Disable this module'] : $this->translationsTab['Enable this module for all shops']) : '',
            'text'  => 'Enable',
            'cond'  => $module->id,
            'icon'  => 'off',
        ];
        $linkResetModule = $linkAdminModules . '&action=resetModule&ajax=1&module_name=' . urlencode($module->name);

        $isResetReady = false;

        if (Validate::isModuleName($module->name)) {

            if (method_exists(Module::getInstanceByName($module->name), 'reset')) {
                $isResetReady = true;
            }

        }

        $resetModule = [
            'href'    => $linkResetModule,
            'onclick' => $module->onclick_option && isset($module->onclick_option_content['reset']) ? $module->onclick_option_content['reset'] : '',
            'title'   => '',
            'text'    => 'Reset',
            'cond'    => $module->id && $module->active,
            'icon'    => 'undo',
            'class'   => ($isResetReady ? 'reset_ready' : ''),
        ];

        $deleteModule = [
            'href'  => $linkAdminModules . '&action=deleteModule&ajax=1&module_name=' . urlencode($module->name),
            'title' => '',
            'text'  => 'Delete',
            'cond'  => true,
            'icon'  => 'trash',
            'class' => 'text-danger',
        ];

        $displayMobile = [
            'href'    => $linkAdminModules . '&module_name=' . urlencode($module->name) . '&' . ($module->enable_device & Context::DEVICE_MOBILE ? 'disable_device' : 'enable_device') . '=' . Context::DEVICE_MOBILE . '&tab_module=' . $module->tab,
            'onclick' => '',
            'title'   => htmlspecialchars($module->enable_device & Context::DEVICE_MOBILE ? $this->translationsTab['Disable on mobiles'] : $this->translationsTab['Display on mobiles']),
            'text'    => $module->enable_device & Context::DEVICE_MOBILE ? 'DisableOnMobiles' : 'DisplayOnMobiles',
            'cond'    => $module->id,
            'icon'    => 'mobile',
        ];

        $displayTablet = [
            'href'    => $linkAdminModules . '&module_name=' . urlencode($module->name) . '&' . ($module->enable_device & Context::DEVICE_TABLET ? 'disable_device' : 'enable_device') . '=' . Context::DEVICE_TABLET . '&tab_module=' . $module->tab,
            'onclick' => '',
            'title'   => htmlspecialchars($module->enable_device & Context::DEVICE_TABLET ? $this->translationsTab['Disable on tablets'] : $this->translationsTab['Display on tablets']),
            'text'    => $module->enable_device & Context::DEVICE_TABLET ? 'DisableOnTablets' : 'DisplayOnTablets',
            'cond'    => $module->id,
            'icon'    => 'tablet',
        ];

        $displayComputer = [
            'href'    => $linkAdminModules . '&module_name=' . urlencode($module->name) . '&' . ($module->enable_device & Context::DEVICE_COMPUTER ? 'disable_device' : 'enable_device') . '=' . Context::DEVICE_COMPUTER . '&tab_module=' . $module->tab,
            'onclick' => '',
            'title'   => htmlspecialchars($module->enable_device & Context::DEVICE_COMPUTER ? $this->translationsTab['Disable on computers'] : $this->translationsTab['Display on computers']),
            'text'    => $module->enable_device & Context::DEVICE_COMPUTER ? 'DisableOnComputers' : 'DisplayOnComputers',
            'cond'    => $module->id,
            'icon'    => 'desktop',
        ];

        $install = [
            'href'    => $linkAdminModules . '&install=' . urlencode($module->name) . '&action=installModule&ajax=1&module_name=' . $module->name . '&anchor=' . ucfirst($module->name) . (!is_null($back) ? '&back=' . urlencode($back) : ''),
            'onclick' => '',
            'title'   => $this->translationsTab['Install'],
            'text'    => 'Install',
            'cond'    => $module->id,
            'icon'    => 'plus-sign-alt',
        ];

        $uninstall = [
            'href'    => $linkAdminModules . '&uninstall=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&module_name=' . $module->name . '&anchor=' . ucfirst($module->name) . (!is_null($back) ? '&back=' . urlencode($back) : ''),
            'onclick' => (isset($module->onclick_option_content['uninstall']) ? $module->onclick_option_content['uninstall'] : 'return confirm(\'' . $this->translationsTab['confirm_uninstall_popup'] . '\');'),
            'title'   => $this->translationsTab['Uninstall'],
            'text'    => 'Uninstall',
            'cond'    => $module->id,
            'icon'    => 'minus-sign-alt',
        ];

        $removeFromFavorite = [
            'href'        => '#',
            'class'       => 'action_unfavorite toggle_favorite',
            'onclick'     => '',
            'title'       => $this->translationsTab['Remove from Favorites'],
            'text'        => 'RemoveFromFavorites',
            'cond'        => $module->id,
            'icon'        => 'star',
            'data-value'  => '0',
            'data-module' => $module->name,
        ];

        $markAsFavorite = [
            'href'        => '#',
            'class'       => 'action_favorite toggle_favorite',
            'onclick'     => '',
            'title'       => $this->translationsTab['Mark as Favorite'],
            'text'        => 'MarkAsFavorite',
            'cond'        => $module->id,
            'icon'        => 'star',
            'data-value'  => '1',
            'data-module' => $module->name,
        ];

        $modulesOptions[] = $configureModule;
        $modulesOptions[] = $deactivateModule;
        $modulesOptions[] = $activateModule;
        $modulesOptions[] = $displayMobile;
        $modulesOptions[] = $displayTablet;
        $modulesOptions[] = $displayComputer;
        $modulesOptions[] = $resetModule;
        $modulesOptions[] = $install;
        $modulesOptions[] = $uninstall;

        if (isset($module->preferences) && isset($module->preferences['favorite']) && $module->preferences['favorite'] == 1) {
            $removeFromFavorite['style'] = '';
            $markAsFavorite['style'] = 'display:none;';
            $modulesOptions[] = $removeFromFavorite;
            // $modulesOptions[] = $markAsFavorite;
        } else {
            $markAsFavorite['style'] = '';
            $removeFromFavorite['style'] = 'display:none;';
            // $modulesOptions[] = $removeFromFavorite;
            $modulesOptions[] = $markAsFavorite;
        }

        if ($module->id == 0) {
            $install['cond'] = 1;
            $install['flag_install'] = 1;
            $modulesOptions[] = $install;
        }

        //$modulesOptions[] = $divider;
        $modulesOptions[] = $deleteModule;

        $return = '';
        $gridReturn = [];

        foreach ($modulesOptions as $optionName => $option) {

            if ($option['cond']) {

                if ($outputType == 'link') {
                    $return .= '<li><a class="' . $optionName . ' action_module';
                    $return .= '" href="' . $option['href'] . (!is_null($back) ? '&back=' . urlencode($back) : '') . '"';
                    $return .= ' onclick="' . $option['onclick'] . '"  title="' . $option['title'] . '"><i class="icon-' . (isset($option['icon']) && $option['icon'] ? $option['icon'] : 'cog') . '"></i>&nbsp;' . $option['text'] . '</a></li>';
                    $gridReturn[$option['text']] = $option['href'];
                } else

                if ($outputType == 'array') {

                    if (!is_array($return)) {
                        $return = [];
                    }

                    $html = '<a class="';

                    $isInstall = isset($option['flag_install']) ? true : false;

                    if (isset($option['class'])) {
                        $html .= $option['class'];
                    }

                    if ($isInstall) {
                        $html .= ' btn btn-success';
                    }

                    if (!$isInstall && count($return) == 0) {
                        $html .= ' btn btn-default';
                    }

                    $html .= '"';

                    if (isset($option['data-value'])) {
                        $html .= ' data-value="' . $option['data-value'] . '"';
                    }

                    if (isset($option['data-module'])) {
                        $html .= ' data-module="' . $option['data-module'] . '"';
                    }

                    if (isset($option['style'])) {
                        $html .= ' style="' . $option['style'] . '"';
                    }

                    $html .= ' href="' . htmlentities($option['href']) . (!is_null($back) ? '&back=' . urlencode($back) : '') . '" onclick="' . $option['onclick'] . '"  title="' . $option['title'] . '"><i class="icon-' . (isset($option['icon']) && $option['icon'] ? $option['icon'] : 'cog') . '"></i> ' . $option['text'] . '</a>';
                    $return[] = $html;
                } else

                if ($outputType == 'select') {
                    $return .= '<option id="' . $optionName . '" data-href="' . htmlentities($option['href']) . (!is_null($back) ? '&back=' . urlencode($back) : '') . '" data-onclick="' . $option['onclick'] . '">' . $option['text'] . '</option>';
                }

            }

        }

        if ($outputType == 'select') {
            $return = '<select id="select_' . $module->name . '">' . $return . '</select>';
        }

        return $gridReturn;
    }

    /**
     *
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function renderKpis() {}

    /**
     * Function used to render the options for this controller
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function renderOptions() {

        Hook::exec(
            'action' . $this->controller_name . 'OptionsModifier', [
                'options'     => &$this->fields_options,
                'option_vars' => &$this->tpl_option_vars,
            ]
        );

        if ($this->fieldsList == true) {

            $this->tpl_option_vars['fieldList'] = $this->renderList();
            $this->tpl_option_vars['titleList'] = $this->l('List') . ' ' . $this->toolbar_title[0];

        }

        if ($this->fields_options && is_array($this->fields_options)) {

            if (isset($this->display) && $this->display != 'options' && $this->display != 'list') {
                $this->show_toolbar = false;
            } else {
                $this->display = 'options';
            }

            $this->tpl_option_vars['controller'] = Tools::getValue('controller');

            unset($this->toolbar_btn);
            $helper = new HelperOptions($this);
            $this->setHelperDisplay($helper);
            $helper->id = $this->id;
            $helper->tpl_vars = $this->tpl_option_vars;

            if ($this->paragrid == true) {
                $helper->isParagrid = true;
            }

            $options = $helper->generateOptions($this->fields_options);
            return $options;
        }

    }

    /**
     * Prepare the view to display the required fields form
     *
     * @return string|void
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function displayRequiredFields() {

        if (!$this->tabAccess['add'] || !$this->tabAccess['delete'] === '1' || !$this->required_database) {
            return;
        }

        $helper = new Helper();
        $helper->currentIndex = static::$currentIndex;
        $helper->token = $this->token;
        $helper->override_folder = $this->override_folder;

        return $helper->renderRequiredFields($this->className, $this->identifier, $this->required_fields);
    }

    /**
     * Initialize the invalid doom page of death
     *
     * @return void
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initCursedPage() {

        $this->layout = 'invalid_token.tpl';
    }

    /**
     * Assign smarty variables for the footer
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initFooter() {

        //RTL Support
        //rtl.js overrides inline styles
        //iso_code.css overrides default fonts for every language (optional)

        if ($this->context->language->is_rtl) {
            $this->addJS(_PS_JS_DIR_ . 'rtl.js');
            $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->context->language->iso_code . '.css', 'all', false);
        }

        // We assign js and css files on the last step before display template, because controller can add many js and css files
        $this->context->smarty->assign('css_files', $this->css_files);
        $this->context->smarty->assign('js_files', array_unique($this->js_files));

        $this->context->smarty->assign(
            [
                'ps_version'  => _PS_VERSION_,
                'ephversion'  => _EPH_VERSION_,
                'timer_start' => $this->timer_start,
                'iso_is_fr'   => strtoupper($this->context->language->iso_code) == 'FR',
                'modals'      => $this->renderModal(),
            ]
        );
    }

    /**
     * @return string
     * @throws Exception
     * @throws SmartyException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function renderModal() {

        $modal_render = '';

        if (is_array($this->modals) && count($this->modals)) {

            foreach ($this->modals as $modal) {
                $this->context->smarty->assign($modal);
                $modal_render .= $this->context->smarty->fetch('modal.tpl');
            }

        }

        return $modal_render;
    }

    /**
     * @deprecated
     */
    public function setDeprecatedMedia() {}

    public function setAjaxMedia() {}

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setMedia() {

        //Bootstrap
        $attributeJs = [];

        $attributes = Attributes::getAttributes($this->context->language->id, true);

        foreach ($attributes as $k => $attribute) {
            $attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
        }

        foreach ($attributeJs as $k => $ajs) {
            natsort($attributeJs[$k]);
        }

        $jsAttribute = [];

        foreach ($attributeJs as $key => $values) {
            $attributeToPush = [];

            foreach ($values as $k => $value) {
                $attributeToPush[$k] = $value;
            }

            $jsAttribute[$key] = $attributeToPush;
        }

        $messenger = Configuration::get('PS_MESSENGER_FEATURE_ACTIVE');

        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/' . $this->bo_css, 'all', 0);

        $this->addCSS(
            [
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/jquery-ui.css',
            ]
        );
        $this->addCSS(
            [

                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/pgrid/pqSelect/pqselect.min.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/pgrid/pqgrid.min.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/pgrid/pqgrid.ui.min.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/jquery.fileupload-ui.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/overrides.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/font-awesome.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/menu.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/tabs.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/confirm-box.css',
            ]
        );

        // Le temps du travail sur l???UI du BO on multiplie les CSS pour bien mener le chantier :
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/vars.css', 'all', PHP_INT_MAX);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/ctrl.css', 'all', PHP_INT_MAX);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/colors.css', 'all', PHP_INT_MAX);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fancybox.css', 'all', PHP_INT_MAX);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fancy_fileupload.css', 'all', 0);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/messenger/bootstrap-table.min.css', 'all', 0);

        $this->addJquery();

        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/ui/jquery-ui.js');

        $this->addjQueryPlugin(['scrollTo', 'alerts', 'chosen', 'autosize', 'fancybox', 'contextMenu', 'ajaxfileupload', 'date', 'tagify', 'select2', 'validate', 'dropdownmenu']);
        $this->addjQueryPlugin('growl', null, false);

        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/select2/select2_locale_' . $this->context->language->iso_code . '.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js');

        $this->addCSS(
            [
                __PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/timepicker/jquery-ui-timepicker-addon.css',
            ]
        );

        if ($messenger == 1) {
            $this->addCSS(
                [
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/messenger/messenger.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/messenger/messengeruploadify.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fonts.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/joypixels-sprite-24.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/joypixels-sprite-32.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/joypixels-sprite-40.css',
                    __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/joypixels-sprite-64.css',
                ]
            );

            $this->addJS(
                [

                    __PS_BASE_URI__ . $this->admin_webpath . '/js/messenger/ion.sound.min.js',
                    __PS_BASE_URI__ . $this->admin_webpath . '/js/messenger/messenger.js',
                    __PS_BASE_URI__ . $this->admin_webpath . '/js/messenger/messengeruploadify.js',

                ]
            );
        }

        MediaAdmin::addJsDef(['host_mode' => false]);
        // @since 1.0.2
        MediaAdmin::addJsDef(['currencyModes' => Currency::getModes()]);

        MediaAdmin::addJsDef([
            'id_lang_default' => Configuration::get('PS_LANG_DEFAULT'),
            'jsAttribute'     => $jsAttribute,
            'languages'       => Language::getLanguages(false),

        ]);

        MediaAdmin::addJsDef([
            'currencyModes'        => Currency::getModes(),
            'AjaxAdminLink'        => $this->context->link->getAdminLink('AdminDashboard'),
            'tagFrom'              => $this->l('From: '),
            'tagTo'                => $this->l('To: '),
            'stdAccountTypes'      => StdAccount::getAccountRacineType(),
            'stdAccountSubTypes'   => StdAccount::getAccountSubType(),
            'stdBanksAccounts'     => ['name' => $this->l('Banks account'), 'racine' => 512],
            'stdProfitsAccounts'   => ['name' => $this->l('Profits account'), 'racine' => 7],
            'stdExpensesAccounts'  => ['name' => $this->l('Expense account'), 'racine' => 6],
            'stdVATAccounts'       => ['name' => $this->l('VAT account'), 'racine' => 445],
            'stNewSupplieraccount' => ['name' => $this->l('Supplier account'), 'racine' => 401],
            'accountDataModel'     => $this->getShortStdAccountFields(),
            'vatRacines'           => $this->vatRacines,
            'expRacine'            => 40,
            'profitRacine'         => 41,
            'postAccountingStart'  => Configuration::get('EPH_POST_ACCOUNT_START'),
            'postAccountingEnd'    => Configuration::get('EPH_POST_ACCOUNT_END'),
        ]);

        $this->addJS(
            [

                __PS_BASE_URI__ . $this->admin_webpath . '/js/admin.js?v=' . _PS_VERSION_,
                __PS_BASE_URI__ . $this->admin_webpath . '/js/tools.js?v=' . _PS_VERSION_,
                __PS_BASE_URI__ . $this->admin_webpath . '/js/menu.js',
            ]
        );

        MediaAdmin::addJsDefL('actionEdit', $this->l('Edit selected items'));
        MediaAdmin::addJsDefL('actionView', $this->l('View selected items'));
        MediaAdmin::addJsDefL('actionDuplicate', $this->l('Duplicate selected items', null, true, false));
        MediaAdmin::addJsDefL('actionDelete', $this->l('Delete selected items', null, true, false));
        MediaAdmin::addJsDefL('actionDetail', $this->l('View details of selected item', null, true, false));
        MediaAdmin::addJsDefL('selectAll', $this->l('Select all item', null, true, false));
        MediaAdmin::addJsDefL('unselectAll', $this->l('Deselect all item', null, true, false));
        MediaAdmin::addJsDefL('bulkDiscount', $this->l('Apply discount in bulk', null, true, false));
        MediaAdmin::addJsDefL('hasChild', $this->l('The selected item as sub component, you cannot delete it!'));
        MediaAdmin::addJsDefL('hasNoChild', $this->l('The selected does not have detail!', null, true, false));

        //loads specific javascripts for the admin theme
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/bootstrap.min.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/modernizr.min.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/modernizr-loads.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/moment-with-langs.min.js');

        $this->addJS([
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqSelect/pqselect.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqgrid.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/localize/pq-localize-fr.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqTouch/pqtouch.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/jsZip-2.5.0/jszip.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/FileSaver.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/javascript-detect-element-resize/detect-element-resize.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/javascript-detect-element-resize/jquery.resize.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/fancybox.umd.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/jquery.fileupload.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/jquery.fileupload-process.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/jquery.fileupload-validate.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/themeuploadify.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pqgrid_custom.js',
            _PS_ADMIN_DIR_ . '/js/fontawesome/js/fontawesome.min.js',
            _PS_ADMIN_DIR_ . '/js/fontawesome/js/solid.min.js',

        ]);

        if (!$this->lite_display) {
            $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/help.js');
        }

        if (!Tools::getValue('submitFormAjax')) {
            $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/notifications.js');
        }

        // Execute Hook AdminController SetMedia
        Hook::exec('actionAdminControllerSetMedia');
    }

    /**
     * Init context and dependencies, handles POST and GET
     *
     * @since 1.9.1.0
     */
    public function init() {

        // Has to be removed for the next PhenyxShop version
        global $currentIndex;

        parent::init();

        if (Tools::getValue('ajax')) {
            $this->ajax = '1';
        }

        /* Server Params */
        $protocol_link = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';
        $protocol_content = (Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) ? 'https://' : 'http://';

        $this->context->link = new Link($protocol_link, $protocol_content);

        if (isset($_GET['logout'])) {
            $this->context->employee->logout();
        }

        if (isset($this->context->cookie->last_activity)) {
            $shortExpire = defined('_EPH_COOKIE_SHORT_EXPIRE_') ? _EPH_COOKIE_SHORT_EXPIRE_ : 900;

            if ((int) $this->context->cookie->last_activity + (int) $shortExpire < time()) {
                $this->context->employee->logout();
            } else {
                $this->context->cookie->last_activity = time();
            }

        }

        if ($this->controller_name != 'AdminLogin' && (!isset($this->context->employee) || !$this->context->employee->isLoggedBack())) {

            if (isset($this->context->employee)) {
                $this->context->employee->logout();
            }

            $email = false;

            if (Tools::getValue('email') && Validate::isEmail(Tools::getValue('email'))) {
                $email = Tools::getValue('email');
            }

            Tools::redirectAdmin($this->context->link->getAdminLink('AdminLogin') . ((!isset($_GET['logout']) && $this->controller_name != 'AdminNotFound' && Tools::getValue('controller')) ? '&redirect=' . $this->controller_name : '') . ($email ? '&email=' . $email : ''));
        }

        // Set current index
        $current_index = 'index.php' . (($controller = Tools::getValue('controller')) ? '?controller=' . $controller : '');

        if ($back = Tools::getValue('back')) {
            $current_index .= '&back=' . urlencode($back);
        }

        static::$currentIndex = $current_index;
        $currentIndex = $current_index;

        if ((int) Tools::getValue('liteDisplaying')) {
            $this->display_header = false;
            $this->display_header_javascript = true;
            $this->display_footer = false;
            $this->content_only = false;
            $this->lite_display = true;
        }

        if ($this->ajax && method_exists($this, 'ajaxPreprocess')) {
            $this->ajaxPreProcess();
        }

        $this->context->smarty->assign(
            [
                'table'            => $this->table,
                'current'          => static::$currentIndex,
                'token'            => $this->token,
                'host_mode'        => 0,
                'stock_management' => (int) Configuration::get('PS_STOCK_MANAGEMENT'),
            ]
        );

        if ($this->display_header) {
            $this->context->smarty->assign('displayBackOfficeHeader', Hook::exec('displayBackOfficeHeader', []));
        }

        $this->context->smarty->assign(
            [
                'displayBackOfficeTop' => Hook::exec('displayBackOfficeTop', []),
                'submit_form_ajax'     => (int) Tools::getValue('submitFormAjax'),
            ]
        );

        Employee::setLastConnectionDate($this->context->employee->id);

        self::$staticShortcodeHandler = $globalShortcodeHandler = $this->buildHandler();

        $this->initProcess();
        $this->initModal();
    }

    public function getExportFields() {

        if (method_exists($this, 'get' . $this->className . 'Fields')) {

            $fields = [];
            $gridFields = $this->{'get' . $this->className . 'Fields'}

            ();

            if (is_array($gridFields) && count($gridFields)) {

                foreach ($gridFields as $grifField) {

                    if (isset($grifField['hidden']) && $grifField['hidden'] && isset($grifField['hiddenable']) && $grifField['hiddenable'] == 'no') {
                        continue;
                    }

                    if (isset($grifField['dataIndx'])) {
                        $fields[$grifField['dataIndx']] = $grifField['title'];
                    }

                }

            }

            return $fields;

        }

        return false;

    }

    public function getUpdatableFields() {

        if (method_exists($this, 'get' . $this->className . 'Fields')) {
            $fields = [];
            $gridFields = $this->{'get' . $this->className . 'Fields'}

            ();

            if (is_array($gridFields) && count($gridFields)) {

                foreach ($gridFields as $grifField) {

                    if (isset($grifField['hidden']) && $grifField['hidden'] && isset($grifField['hiddenable']) && $grifField['hiddenable'] == 'no') {
                        continue;
                    }

                    if (isset($grifField['updatable']) && !$grifField['updatable']) {
                        continue;
                    }

                    if (isset($grifField['dataIndx'])) {
                        $fields[$grifField['dataIndx']] = $grifField['title'];
                    }

                }

            }

            return $fields;

        }

        return false;

    }

    public function getUpdatableFieldType($dataIndx) {

        if (method_exists($this, 'get' . $this->className . 'Fields')) {

            $gridFields = $this->{'get' . $this->className . 'Fields'}

            ();

            if (is_array($gridFields) && count($gridFields)) {

                foreach ($gridFields as $grifField) {

                    if ($grifField['dataIndx'] == $dataIndx) {
                        return $grifField;
                    }

                }

            }

        }

        return false;

    }

    public function removeRequestFields($requests) {

        if (method_exists($this, 'get' . $this->className . 'Fields')) {

            $objects = [];

            $fields = [];
            $gridFields = $this->{'get' . $this->className . 'Fields'}

            ();

            foreach ($gridFields as $grifField) {
                $fields[] = $grifField['dataIndx'];
            }

            foreach ($requests as $key => $object) {

                foreach ($object as $field => $value) {

                    if (in_array($field, $fields)) {
                        $objects[$key][$field] = $value;
                    }

                }

            }

            return $objects;
        }

        return $requests;
    }

    public function getExportFormatFields() {

        if (method_exists($this, 'get' . $this->className . 'Fields')) {

            $fields = [];
            $gridFields = $this->{'get' . $this->className . 'Fields'}

            ();

            if (is_array($gridFields) && count($gridFields)) {

                foreach ($gridFields as $grifField) {

                    if (isset($grifField['hidden']) && $grifField['hidden'] && isset($grifField['hiddenable']) && $grifField['hiddenable'] == 'no') {
                        continue;
                    }

                    if (isset($grifField['dataIndx'])) {

                        if (isset($grifField['exWidth'])) {
                            $fields[$grifField['dataIndx']]['width'] = $grifField['exWidth'];
                        }

                        if (isset($grifField['halign'])) {
                            $fields[$grifField['dataIndx']]['halign'] = $grifField['halign'];
                        } else {
                            $fields[$grifField['dataIndx']]['halign'] = 'Alignment::HORIZONTAL_LEFT';
                        }

                        if (isset($grifField['numberFormat'])) {
                            $fields[$grifField['dataIndx']]['numberFormat'] = $grifField['numberFormat'];
                        }

                        if (isset($grifField['dataType']) && $grifField['dataType'] == 'date') {
                            $fields[$grifField['dataIndx']]['date'] = true;

                        }

                        if (isset($grifField['exportType']) && $grifField['exportType'] == 'Image') {
                            $fields[$grifField['dataIndx']]['image'] = true;

                        }

                    }

                }

            }

            return $fields;

        }

        return false;

    }

    public function ajaxProcessExcelExport() {

        $currentClass = Tools::getValue('currentClass');
        $exportField = Tools::getValue('exportField');

        if (class_exists($currentClass)) {
            $this->className = $currentClass;
            $fieldEntetes = [];
            $fieldsExports = $this->getExportFields();

            foreach ($fieldsExports as $key => $field) {

                if (in_array($key, $exportField)) {
                    $fieldEntetes[$key] = $field;
                }

            }

            if (method_exists($this, 'get' . $this->className . 'Request')) {
                $values = $this->{'get' . $this->className . 'Request'}

                ();
                $this->generateExcelSheet($fieldEntetes, $values, $exportField);
            }

        }

    }

    public function generateExcelSheet($fieldEntetes, $values, $valuesKey) {

        $shopUrl = Tools::getShopProtocol() . ShopUrl::getMainShopDomain($this->context->shop->id) . DIRECTORY_SEPARATOR;
        $name = $this->l('File') . '-' . $this->publicName . '.xlsx';
        $tag = $this->l('File') . ' ' . $this->publicName;

        $nbEntete = sizeof($fieldEntetes);

        $spreadsheet = new Spreadsheet();
        $spreadsheet->getProperties()->setCreator('Ephenyx Shop')
            ->setTitle($this->className)
            ->setSubject($this->context->language->id)
            ->setDescription($this->l('Listing') . ' ' . $this->className);

        $drawing = new Drawing();
        $drawing->setName('Logo Ephenyx Shop');
        $drawing->setPath(_PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/img/ephenyx-avatar-header_shopname.png');
        $drawing->setHeight(80);
        $drawing->setCoordinates('A1');
        $drawing->setWorksheet($spreadsheet->getActiveSheet());
        $letter = chr(64 + $nbEntete);
        $spreadsheet->getActiveSheet()->mergeCells('A1:' . $letter . '4');
        $fieldFormat = $this->getExportFormatFields();
        $i = 5;
        $j = 1;

        foreach ($fieldEntetes as $key => $value) {
            $letter = chr(64 + $j);

            $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getFont()->setBold(true);
            $spreadsheet->setActiveSheetIndex(0)
                ->setCellValue($letter . $i, $value);
            $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            if (isset($fieldFormat[$key])) {

                if (isset($fieldFormat[$key]['width'])) {
                    $spreadsheet->getActiveSheet(0)->getColumnDimension($letter)->setWidth($fieldFormat[$key]['width']);
                }

            }

            $j++;
        }

        $i++;

        foreach ($values as $object) {

            $j = 1;

            foreach ($fieldEntetes as $key => $field) {
                $letter = chr(64 + $j);

                if (isset($fieldFormat[$key]['numberFormat'])) {
                    $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_CURRENCY_EUR_SIMPLE);
                }

                if ($key == 'active') {

                    $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $drawing = new Drawing();
                    $drawing->setName('Active Field');
                    $pos = strpos($object[$key], 'inactive');

                    if ($pos !== false) {
                        $drawing->setPath(_PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/img/icons/no.png');
                    } else {
                        $drawing->setPath(_PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/img/icons/ok.png');
                    }

                    $drawing->setHeight(16);
                    $drawing->setCoordinates($letter . $i);
                    $drawing->setWorksheet($spreadsheet->getActiveSheet());
                    $j++;
                    continue;
                }

                if (isset($fieldFormat[$key]['halign'])) {

                    switch ($fieldFormat[$key]['halign']) {
                    case 'HORIZONTAL_CENTER':
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        break;
                    case 'HORIZONTAL_LEFT':
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        break;
                    case 'HORIZONTAL_RIGHT':
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
                        break;
                    case 'HORIZONTAL_JUSTIFY':
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_JUSTIFY);
                        break;
                    case 'HORIZONTAL_FILL':
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_FILL);
                        break;
                    default:
                        $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
                        break;
                    }

                }

                if (isset($fieldFormat[$key]['date']) && $fieldFormat[$key]['date']) {
                    $date = new DateTime($object[$key]);
                    $object[$key] = $date->format('d/m/Y');
                }

                if (isset($fieldFormat[$key]['image']) && $fieldFormat[$key]['image']) {

                    $image = str_replace('<img src="', '', explode("-", explode("/", str_replace($shopUrl, '', $object[$key]))[0])[0]);

                    $path = Image::getImgFolderStatic($image) . $image . '-cart_default.jpg';

                    if (file_exists(_PS_PROD_IMG_DIR_ . $path)) {
                        $src = _PS_PROD_IMG_DIR_ . $path;
                    } else {
                        $src = _PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/img/default.jpg';
                    }

                    $drawing = new Drawing();
                    $drawing->setName('Image' . $image);
                    $drawing->setPath($src);
                    $drawing->setHeight(80);
                    $drawing->setOffsetX(50);
                    $drawing->setOffsetY(10);
                    $drawing->setCoordinates($letter . $i);
                    $drawing->setWorksheet($spreadsheet->getActiveSheet());
                    $spreadsheet->getActiveSheet(0)->getRowDimension($i)->setRowHeight(
                        $drawing->getHeight()
                    );
                    $spreadsheet->getActiveSheet()->getStyle($letter . $i)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
                    $j++;
                    continue;

                }

                $spreadsheet->setActiveSheetIndex(0)
                    ->setCellValue($letter . $i, $object[$key]);

                $spreadsheet->getActiveSheet(0)->getStyle($letter . $i)->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);

                $j++;
            }

            $spreadsheet->getActiveSheet(0)->getStyle('A' . $i . ':' . $letter . $i)->getAlignment()->setWrapText(true);

            $i++;
        }

        $spreadsheet->getActiveSheet(0)->setTitle($tag);
        $spreadsheet->setActiveSheetIndex(0);
        $filePath = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'fileExport' . DIRECTORY_SEPARATOR;
        $fileSave = new Xlsx($spreadsheet);
        $fileSave->save($filePath . $name);
        $fileToUpload = 'fileExport' . DIRECTORY_SEPARATOR . $name;

        $result = [
            'link' => '<a download="' . $name . '" id="objectFile" class="btn btn-default" href="' . $fileToUpload . '"><i class="process-export-excel"></i>' . $this->l('Click here to Download the file') . '</a>',
        ];
        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessUpdateEmployeeTheme() {

        $theme = Tools::getValue('theme');
        $this->context->employee->bo_theme = $theme;
        $this->context->employee->update();
        die(true);
    }

    public function getJsContent($jsDef) {

        $jsContent = '';

        foreach ($jsDef as $jsfile) {
            $jsContent = $jsContent . file_get_contents(_PS_ROOT_DIR_ . '/' . $jsfile) . PHP_EOL;
        }

        return '<script type="text/javascript">' . PHP_EOL . $jsContent . PHP_EOL . '</script>';
    }

    public function combineJs($curJs, $dataScript) {

        $return = '';

        if (is_array($curJs)) {

            foreach ($this->js_files as $key => $js_file) {

                if (in_array($js_file, $curJs)) {
                    unset($this->js_files[$key]);
                }

            }

        }

        if (!empty($this->js_files)) {

            foreach ($this->js_files as $key => $js_file) {
                $return .= '<script type="text/javascript" src="' . $js_file . '" data-script="' . $dataScript . '"></script>' . PHP_EOL;
            }

        }

        return $return;

    }

    /**
     * Retrieve GET and POST value and translate them to actions
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initProcess() {

        $this->ensureListIdDefinition();

        // Manage list filtering

        if (Tools::isSubmit('submitFilter' . $this->list_id)
            || $this->context->cookie->{'submitFilter' . $this->list_id}

            !== false
            || Tools::getValue($this->list_id . 'Orderby')
            || Tools::getValue($this->list_id . 'Orderway')
        ) {
            $this->filter = true;
        }

        $this->id_object = (int) Tools::getValue($this->identifier);

        /* Delete object image */

        if (isset($_GET['deleteImage'])) {

            if ($this->tabAccess['delete'] === '1') {
                $this->action = 'delete_image';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }

        } else

        if (isset($_GET['delete' . $this->table])) {
            /* Delete object */

            if ($this->tabAccess['delete'] === '1') {
                $this->action = 'delete';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }

        } else

        if ((isset($_GET['status' . $this->table]) || isset($_GET['status'])) && Tools::getValue($this->identifier)) {
            /* Change object statuts (active, inactive) */

            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'status';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (isset($_GET['position'])) {
            /* Move an object */

            if ($this->tabAccess['edit'] == '1') {
                $this->action = 'position';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (Tools::isSubmit('submitAdd' . $this->table)
            || Tools::isSubmit('submitAdd' . $this->table . 'AndStay')
            || Tools::isSubmit('submitAdd' . $this->table . 'AndPreview')
            || Tools::isSubmit('submitAdd' . $this->table . 'AndBackToParent')
        ) {
            // case 1: updating existing entry

            if ($this->id_object) {

                if ($this->tabAccess['edit'] === '1') {
                    $this->action = 'save';

                    if (Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
                        $this->display = 'edit';
                    } else {
                        $this->display = 'list';
                    }

                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                }

            } else {
                // case 2: creating new entry

                if ($this->tabAccess['add'] === '1') {
                    $this->action = 'save';

                    if (Tools::isSubmit('submitAdd' . $this->table . 'AndStay')) {
                        $this->display = 'edit';
                    } else {
                        $this->display = 'list';
                    }

                } else {
                    $this->errors[] = Tools::displayError('You do not have permission to add this.');
                }

            }

        } else

        if (isset($_GET['add' . $this->table])) {

            if ($this->tabAccess['add'] === '1') {
                $this->action = 'new';
                $this->display = 'add';
                $this->displayGrid = false;
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }

        } else

        if (isset($_GET['update' . $this->table]) && isset($_GET[$this->identifier])) {
            $this->display = 'edit';
            $this->displayGrid = false;

            if ($this->tabAccess['edit'] !== '1') {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (isset($_GET['view' . $this->table])) {

            if ($this->tabAccess['view'] === '1') {
                $this->display = 'view';
                $this->action = 'view';
                $this->displayGrid = false;
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to view this.');
            }

        } else

        if (isset($_GET['details' . $this->table])) {

            if ($this->tabAccess['view'] === '1') {
                $this->display = 'details';
                $this->action = 'details';
                $this->displayGrid = false;
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to view this.');
            }

        } else

        if (isset($_GET['export' . $this->table])) {

            if ($this->tabAccess['view'] === '1') {
                $this->action = 'export';
            }

        } else

        if (isset($_POST['submitReset' . $this->list_id])) {
            /* Cancel all filters for this tab */
            $this->action = 'reset_filters';
        } else

        if (Tools::isSubmit('submitOptions' . $this->table) || Tools::isSubmit('submitOptions')) {
            /* Submit options list */
            $this->display = 'options';

            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'update_options';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (Tools::getValue('action') && method_exists($this, 'process' . ucfirst(Tools::toCamelCase(Tools::getValue('action'))))) {
            $this->action = Tools::getValue('action');
        } else

        if (Tools::isSubmit('submitFields') && $this->required_database && $this->tabAccess['add'] === '1' && $this->tabAccess['delete'] === '1') {
            $this->action = 'update_fields';
        } else

        if (is_array($this->bulk_actions)) {
            $submit_bulk_actions = array_merge(
                [
                    'enableSelection'  => [
                        'text' => $this->l('Enable selection'),
                        'icon' => 'icon-power-off text-success',
                    ],
                    'disableSelection' => [
                        'text' => $this->l('Disable selection'),
                        'icon' => 'icon-power-off text-danger',
                    ],
                ], $this->bulk_actions
            );

            foreach ($submit_bulk_actions as $bulk_action => $params) {

                if (Tools::isSubmit('submitBulk' . $bulk_action . $this->table) || Tools::isSubmit('submitBulk' . $bulk_action)) {

                    if ($bulk_action === 'delete') {

                        if ($this->tabAccess['delete'] === '1') {
                            $this->action = 'bulk' . $bulk_action;
                            $this->boxes = Tools::getValue($this->table . 'Box');

                            if (empty($this->boxes) && $this->table == 'attribute') {
                                $this->boxes = Tools::getValue($this->table . '_valuesBox');
                            }

                        } else {
                            $this->errors[] = Tools::displayError('You do not have permission to delete this.');
                        }

                        break;
                    } else

                    if ($this->tabAccess['edit'] === '1') {
                        $this->action = 'bulk' . $bulk_action;
                        $this->boxes = Tools::getValue($this->table . 'Box');
                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                    }

                    break;
                } else

                if (Tools::isSubmit('submitBulk')) {

                    if ($bulk_action === 'delete') {

                        if ($this->tabAccess['delete'] === '1') {
                            $this->action = 'bulk' . $bulk_action;
                            $this->boxes = Tools::getValue($this->table . 'Box');
                        } else {
                            $this->errors[] = Tools::displayError('You do not have permission to delete this.');
                        }

                        break;
                    } else

                    if ($this->tabAccess['edit'] === '1') {
                        $this->action = 'bulk' . Tools::getValue('select_submitBulk');
                        $this->boxes = Tools::getValue($this->table . 'Box');
                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to edit this.');
                    }

                    break;
                }

            }

        } else

        if (!empty($this->fields_options) && empty($this->fields_list)) {
            $this->display = 'options';
        }

    }

    /**
     * @throws Exception
     * @throws SmartyException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function initModal() {

        if ($this->logged_on_addons) {
            $this->context->smarty->assign(
                [
                    'logged_on_addons' => 1,
                    'username_addons'  => $this->context->cookie->username_addons,
                ]
            );
        }

        // Iso needed to generate Addons login
        $iso_code_caps = strtoupper($this->context->language->iso_code);

        $this->context->smarty->assign(
            [
                'check_url_fopen' => (ini_get('allow_url_fopen') ? 'ok' : 'ko'),
                'check_openssl'   => (extension_loaded('openssl') ? 'ok' : 'ko'),
                'add_permission'  => 1,
            ]
        );

        //Force override translation key
        $this->context->override_controller_name_for_translations = 'AdminModules';

        //After override translation, remove it
        $this->context->override_controller_name_for_translations = null;
    }

    /**
     * Display object details
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function viewDetails() {}

    /**
     * Shortcut to set up a json success payload
     *
     * @param string $message Success message
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function jsonConfirmation($message) {

        $this->json = true;
        $this->confirmations[] = $message;

        if ($this->status === '') {
            $this->status = 'ok';
        }

    }

    /**
     * Shortcut to set up a json error payload
     *
     * @param string $message Error message
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function jsonError($message) {

        $this->json = true;
        $this->errors[] = $message;

        if ($this->status === '') {
            $this->status = 'error';
        }

    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function ajaxProcessGetModuleQuickView() {

        $modules = Module::getModulesOnDisk();

        foreach ($modules as $module) {

            if ($module->name == Tools::getValue('module')) {
                break;
            }

        }

        $url = $module->url;

        if (isset($module->type) && ($module->type == 'addonsPartner' || $module->type == 'addonsNative')) {
            $url = $this->context->link->getAdminLink('AdminModules') . '&install=' . urlencode($module->name) . '&tab_module=' . $module->tab . '&module_name=' . $module->name . '&anchor=' . ucfirst($module->name);
        }

        $this->context->smarty->assign(
            [
                'displayName'            => $module->displayName,
                'image'                  => $module->image,
                'nb_rates'               => (int) $module->nb_rates[0],
                'avg_rate'               => (int) $module->avg_rate[0],
                'badges'                 => $module->badges,
                'compatibility'          => $module->compatibility,
                'description_full'       => $module->description_full,
                'additional_description' => $module->additional_description,
                'is_addons_partner'      => (isset($module->type) && ($module->type == 'addonsPartner' || $module->type == 'addonsNative')),
                'url'                    => $url,
                'price'                  => $module->price,

            ]
        );
        // Fetch the translations in the right place - they are not defined by our current controller!
        $this->context->override_controller_name_for_translations = 'AdminModules';
        $this->smartyOutputContent('controllers/modules/quickview.tpl');
        die(1);
    }

    /**
     * Update options and preferences
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function processUpdateOptions() {

        $this->beforeUpdateOptions();

        $languages = Language::getLanguages(false);

        $hideMultishopCheckbox = (Shop::getTotalShops(false, null) < 2) ? true : false;

        foreach ($this->fields_options as $categoryData) {

            if (!isset($categoryData['fields'])) {
                continue;
            }

            $fields = $categoryData['fields'];

            foreach ($fields as $field => $values) {

                if (isset($values['type']) && $values['type'] == 'selectLang') {

                    foreach ($languages as $lang) {

                        if (Tools::getValue($field . '_' . strtoupper($lang['iso_code']))) {
                            $fields[$field . '_' . strtoupper($lang['iso_code'])] = [
                                'type'       => 'select',
                                'cast'       => 'strval',
                                'identifier' => 'mode',
                                'list'       => $values['list'],
                            ];
                        }

                    }

                }

            }

            // Validate fields

            foreach ($fields as $field => $values) {
                // We don't validate fields with no visibility

                if (!$hideMultishopCheckbox && Shop::isFeatureActive() && isset($values['visibility']) && $values['visibility'] > Shop::getContext()) {
                    continue;
                }

                // Check if field is required

                if ((!Shop::isFeatureActive() && isset($values['required']) && $values['required'])
                    || (Shop::isFeatureActive() && isset($_POST['multishopOverrideOption'][$field]) && isset($values['required']) && $values['required'])
                ) {

                    if (isset($values['type']) && $values['type'] == 'textLang') {

                        foreach ($languages as $language) {

                            if (($value = Tools::getValue($field . '_' . $language['id_lang'])) == false && (string) $value != '0') {
                                $this->errors[] = sprintf(Tools::displayError('field %s is required.'), $values['title']);
                            }

                        }

                    } else

                    if (($value = Tools::getValue($field)) == false && (string) $value != '0') {
                        $this->errors[] = sprintf(Tools::displayError('field %s is required.'), $values['title']);
                    }

                }

                // Check field validator

                if (isset($values['type']) && $values['type'] == 'textLang') {

                    foreach ($languages as $language) {

                        if (Tools::getValue($field . '_' . $language['id_lang']) && isset($values['validation'])) {
                            $valuesValidation = $values['validation'];

                            if (!Validate::$valuesValidation(Tools::getValue($field . '_' . $language['id_lang']))) {
                                $this->errors[] = sprintf(Tools::displayError('field %s is invalid.'), $values['title']);
                            }

                        }

                    }

                } else

                if (Tools::getValue($field) && isset($values['validation'])) {
                    $valuesValidation = $values['validation'];

                    if (!Validate::$valuesValidation(Tools::getValue($field))) {
                        $this->errors[] = sprintf(Tools::displayError('field %s is invalid.'), $values['title']);
                    }

                }

                // Set default value

                if (Tools::getValue($field) === false && isset($values['default'])) {
                    $_POST[$field] = $values['default'];
                }

            }

            if (!count($this->errors)) {

                foreach ($fields as $key => $options) {

                    if (Shop::isFeatureActive() && isset($options['visibility']) && $options['visibility'] > Shop::getContext()) {
                        continue;
                    }

                    if (!$hideMultishopCheckbox && Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_ALL && empty($options['no_multishop_checkbox']) && empty($_POST['multishopOverrideOption'][$key])) {
                        Configuration::deleteFromContext($key);
                        continue;
                    }

                    // check if a method updateOptionFieldName is available
                    $methodName = 'updateOption' . Tools::toCamelCase($key, true);

                    if (method_exists($this, $methodName)) {
                        $this->$methodName(Tools::getValue($key));
                    } else

                    if (isset($options['type']) && in_array($options['type'], ['textLang', 'textareaLang'])) {
                        $list = [];

                        foreach ($languages as $language) {
                            $keyLang = Tools::getValue($key . '_' . $language['id_lang']);
                            $val = (isset($options['cast']) ? $options['cast']($keyLang) : $keyLang);

                            if ($this->validateField($val, $options)) {

                                if (Validate::isCleanHtml($val)) {
                                    $list[$language['id_lang']] = $val;
                                } else {
                                    $this->errors[] = Tools::displayError('Can not add configuration ' . $key . ' for lang ' . Language::getIsoById((int) $language['id_lang']));
                                }

                            }

                        }

                        Configuration::updateValue($key, $list, isset($values['validation']) && isset($options['validation']) && $options['validation'] == 'isCleanHtml' ? true : false);
                    } else

                    if (isset($options['json']) && $options['json']) {
                        Configuration::updateValue($key, implode(",", Tools::getValue($key)));
                    } else {
                        $val = (isset($options['cast']) ? $options['cast'](Tools::getValue($key)) : Tools::getValue($key));

                        if ($this->validateField($val, $options)) {

                            if ($options['type'] === 'code') {
                                Configuration::updateValue($key, $val, true);
                            } else

                            if (Validate::isCleanHtml($val)) {
                                Configuration::updateValue($key, $val);
                            } else {
                                $this->errors[] = Tools::displayError('Can not add configuration ' . $key);
                            }

                        }

                    }

                }

            }

        }

        $this->display = 'list';

        if (empty($this->errors)) {
            $this->confirmations[] = $this->_conf[6];
        }

    }

    /**
     * Can be overridden
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function beforeUpdateOptions() {}

    /**
     * @param mixed $value
     * @param array $field
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function validateField($value, $field) {

        if (isset($field['validation'])) {
            $valid_method_exists = method_exists('Validate', $field['validation']);

            if ((!isset($field['empty']) || !$field['empty'] || (isset($field['empty']) && $field['empty'] && $value)) && $valid_method_exists) {
                $field_validation = $field['validation'];

                if (!Validate::$field_validation($value)) {
                    $this->errors[] = Tools::displayError($field['title'] . ' : Incorrect value');

                    return false;
                }

            }

        }

        return true;
    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function redirect() {

        if ($this->errors || $this->warnings
            || $this->informations || $this->confirmations) {
            $token = Tools::getValue('token');
            $messageCachePath = _PS_CACHE_DIR_ . '/' . static::MESSAGE_CACHE_PATH
                . '-' . $token;

            file_put_contents($messageCachePath, '<?php
                $this->errors = ' . var_export($this->errors, true) . ';
                $this->warnings = ' . var_export($this->warnings, true) . ';
                $this->informations = ' . var_export($this->informations, true) . ';
                $this->confirmations = ' . var_export($this->confirmations, true) . ';
            ');
        }

        Tools::redirectAdmin($this->redirect_after);
    }

    /**
     * Add a info message to display at the top of the page
     *
     * @param string $msg
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function displayInformation($msg) {

        $this->informations[] = $msg;
    }

    /**
     * Enable multiple items
     *
     * @return bool true if success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function processBulkEnableSelection() {

        return $this->processBulkStatusSelection(1);
    }

    /**
     * Toggle status of multiple items
     *
     * @param bool $status
     *
     * @return bool true if success
     * @throws PhenyxShopException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function processBulkStatusSelection($status) {

        $result = true;

        if (is_array($this->boxes) && !empty($this->boxes)) {

            foreach ($this->boxes as $id) {
                /** @var ObjectModel $object */
                $object = new $this->className((int) $id);
                $object->setFieldsToUpdate(['active' => true]);
                $object->active = (int) $status;
                $result &= $object->update();
            }

        }

        return $result;
    }

    /**
     * Disable multiple items
     *
     * @return bool true if success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function processBulkDisableSelection() {

        return $this->processBulkStatusSelection(0);
    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function processBulkAffectZone() {

        $result = false;

        if (is_array($this->boxes) && !empty($this->boxes)) {
            /** @var Country|State $object */
            $object = new $this->className();
            $result = $object->affectZoneToSelection(Tools::getValue($this->table . 'Box'), Tools::getValue('zone_to_affect'));

            if ($result) {
                $this->redirect_after = static::$currentIndex . '&conf=28&token=' . $this->token;
            }

            $this->errors[] = Tools::displayError('An error occurred while assigning a zone to the selection.');
        } else {
            $this->errors[] = Tools::displayError('You must select at least one element to assign a new zone.');
        }

        return $result;
    }

    public function deployArrayScript($option, $value, $sub = false) {

        if ($sub) {

            if (is_string($option) && is_array($value) && !Tools::is_assoc($value)) {
                $jsScript = $option . ': [' . PHP_EOL;

                foreach ($value as $suboption => $value) {

                    if (is_array($value)) {
                        $jsScript .= '          ' . $this->deployArrayScript($suboption, $value, true);
                    } else

                    if (is_string($suboption)) {
                        $jsScript .= '          ' . $suboption . ': ' . $value . ',' . PHP_EOL;
                    } else {
                        $jsScript .= '          ' . $value . ',' . PHP_EOL;
                    }

                }

                $jsScript .= '          ],' . PHP_EOL;
                return $jsScript;

            } else {

                if (is_string($option)) {
                    $jsScript = $option . ': {' . PHP_EOL;
                } else {
                    $jsScript = ' {' . PHP_EOL;
                }

            }

        } else {

            if (is_string($option)) {
                $jsScript = $option . ': {' . PHP_EOL;
            } else {

                $jsScript = ' {' . PHP_EOL;
            }

        }

        foreach ($value as $suboption => $value) {

            if (is_array($value)) {
                $jsScript .= '          ' . $this->deployArrayScript($suboption, $value, true);
            } else

            if (is_string($suboption)) {
                $jsScript .= '          ' . $suboption . ': ' . $value . ',' . PHP_EOL;
            } else {
                $jsScript .= '          ' . $value . ',' . PHP_EOL;
            }

        }

        if ($sub) {
            $jsScript .= '          },' . PHP_EOL;
        } else {
            $jsScript .= '      },' . PHP_EOL;
        }

        return $jsScript;

    }

    public function generateCustomerCode($id_country, $postcode = null) {

        $cc = Db::getInstance()->getValue('SELECT `id_student` FROM `' . _DB_PREFIX_ . 'student` ORDER BY `id_student` DESC') + 1;

        if (isset($postcode)) {

            if ($id_country != 8) {
                $iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
            } else {
                $iso_code = substr($postcode, 0, 2);

                if ($iso_code >= 97) {
                    $iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
                }

            }

            $Shop_iso = 'ST';
            return substr($postcode, 0, 2) . $Shop_iso . sprintf("%04s", $cc);
        } else {
            $iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');

            $Shop_iso = 'ST_' . $iso_code;

            return $Shop_iso . sprintf("%04s", $cc);
        }

    }

    public function ajaxProcessAddNewAccount() {

        $idType = Tools::getValue('idType');
        $data = $this->createTemplate('newAccount.tpl');
        $rowIndx = Tools::getValue('rowIndx');
        $type = '';

        if (Validate::isUnsignedId($idType)) {
            $type = StdAccount::getTypeByidType($idType);
        }

        $data->assign('type', $type);
        $data->assign('rowIndx', $rowIndx);

        $result = [
            'html' => $data->fetch(),
        ];

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessCreateNewAccount() {

        $data = $this->createTemplate('createNewAccount.tpl');
        $result = [
            'html' => $data->fetch(),
        ];

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessSaveModalNewAccount() {}

    public function ajaxProcessProceedNewAccount() {

        $rowIndx = Tools::getValue('rowIndx');
        $stdaccount = new StdAccount();

        foreach ($_POST as $key => $value) {

            if (property_exists($stdaccount, $key) && $key != 'id_stdaccount') {

                if (Tools::getValue('id_stdaccount') && empty($value)) {
                    continue;
                }

                $stdaccount->{$key}

                = $value;

            }

        }

        $classVars = get_class_vars(get_class($stdaccount));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
            $fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

            if (array_key_exists('lang', $params) && $params['lang']) {

                foreach (Language::getIDs(false) as $idLang) {

                    if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                        if (!isset($stdaccount->{$field}) || !is_array($stdaccount->{$field})) {
                            $stdaccount->{$field}

                            = [];
                        }

                        $stdaccount->{$field}

                        [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }

                }

            }

        }

        $result = $stdaccount->add();
        $stdaccount = new StdAccount($stdaccount->id, $this->context->language->id);

        $return = [
            'success'    => true,
            'rowIndx'    => $rowIndx,
            'stdaccount' => $stdaccount,
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessDeleteAccount() {

        $id_stdaccount = Tools::getValue('idAccount');

        $stdaccount = new StdAccount($id_stdaccount);
        $result = $stdaccount->delete();
        $return = [
            'success' => true,
            'message' => $this->l('Le compte a ??t?? supprim?? avec succ??s'),
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessProceedEditAccount() {

        $id_stdaccount = Tools::getValue('id_stdaccount');

        $stdaccount = new StdAccount($id_stdaccount);

        foreach ($_POST as $key => $value) {

            if (property_exists($stdaccount, $key) && $key != 'id_stdaccount') {

                if (Tools::getValue('id_stdaccount') && empty($value)) {
                    continue;
                }

                $stdaccount->{$key}

                = $value;

            }

        }

        $classVars = get_class_vars(get_class($stdaccount));
        $fields = [];

        if (isset($classVars['definition']['fields'])) {
            $fields = $classVars['definition']['fields'];
        }

        foreach ($fields as $field => $params) {

            if (array_key_exists('lang', $params) && $params['lang']) {

                foreach (Language::getIDs(false) as $idLang) {

                    if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                        if (!isset($stdaccount->{$field}) || !is_array($stdaccount->{$field})) {
                            $stdaccount->{$field}

                            = [];
                        }

                        $stdaccount->{$field}

                        [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                    }

                }

            }

        }

        $result = $stdaccount->update();

        $return = [
            'success' => true,
            'message' => $this->l('Le compte a ??t?? mis ?? jour avec succ??s'),
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessEditAccount() {

        $idAccount = Tools::getValue('idAccount');

        $stdaccount = new StdAccount($idAccount, 1);
        $data = $this->createTemplate('editAccount.tpl');
        $data->assign('stdaccount', $stdaccount);

        $result = [
            'html' => $data->fetch(),
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessViewbookRecord() {

        $idBookRecord = Tools::getValue('idBookRecord');
        $record = new BookRecords($idBookRecord);
        $details = BookRecords::getRecordDetailsById($record->id);
        $script = $this->generateBookDetailsScript($record->id);
        $diaries = BookDiary::getBookDiary();
        $data = $this->createTemplate('viewbookRecord.tpl');
        $data->assign('record', $record);
        $data->assign('details', $details);
        $data->assign('diaries', $diaries);
        $data->assign('paragridScript', $script);
        $data->assign('controller', $this->controller_name);

        $result = [
            'success' => true,
            'html'    => $data->fetch(),
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessNewViewbookRecord() {

        $script = $this->generateNewBookDetailsScript();
        $diaries = BookDiary::getBookDiary();
        $data = $this->createTemplate('newbookRecord.tpl');
        $data->assign('today', date("Y-m-d"));
        $data->assign('diaries', $diaries);
        $data->assign('paragridScript', $script);
        $data->assign('controller', $this->controller_name);

        $result = [
            'success' => true,
            'html'    => $data->fetch(),
        ];

        die(Tools::jsonEncode($result));
    }

    public function generateNewBookDetailsScript() {

        $paragrid = new ParamGrid('NewBookRecords', $this->controller_name, 'book_record_details', 'id_book_record_details');

        $paragrid->paramGridObj = 'objNewBookRecords';
        $paragrid->paramGridVar = 'gridNewBookRecords';
        $paragrid->paramGridId = 'grid_AdminNewBookRecords';
        $paragrid->requestModel = '{
            location: "remote",
            dataType: "json",
            method: "GET",
            recIndx: "id_book_record_details",
            url: AjaxLink' . $this->controller_name . '+"&action=getNewBookRecordsRequest&ajax=1",
            getData: function (dataJSON) {
                return { data: dataJSON };
            }


        }';
        $paragrid->selectionModelType = 'row';
        $paragrid->height = '350';
        $paragrid->heightModel = 'getHeightModel() {
            var offset = $("#headerActionRow").height()+$("#tableNewRecord").height()+$("#company_fieldset_accounting_date").height()+300;
            console.log(offset);
            console.log($(window).height());
            return screenHeight = $(window).height()-offset;
        };';
        $paragrid->columnBorders = 1;

        $paragrid->showNumberCell = 1;
        $paragrid->change = 'function(evt, ui) {
            //proceedListChange(evt, ui);

        }';
        $paragrid->cellSave = 'function(evt, ui) {

            if(ui.dataIndx == "libelle") {
                if(ui.rowData.id_stdaccount_type == 5) {

                    jumpGridCell(ui.rowIndx, "debit")
                }
                if(ui.rowData.id_stdaccount_type == 4) {

                    jumpGridCell(ui.rowIndx, "credit")
                }
            }
            console.log(ui);
            if(ui.dataIndx == "credit" || ui.dataIndx == "debit") {
                var amount = 0;
                var credit = 0;
                var debit = 0;
                var solde = 0;
                var rowIndex = ui.rowIndx+1;
                if(ui.rowData.id_stdaccount_type == 4) {

                    amount = ui.rowData.credit;
                    debit = amount*0.2;
                    solde = amount - debit;

                    var rowData = addNewRecordsLine(ui.rowData.default_vat, ui.rowData.defaultVatCode, ui.rowData.defaultVatName, debit, credit, ui.rowData.libelle);
                    var rowIndx = window[\'gridNewBookRecords\'].addRow({newRow: rowData, rowIndx: rowIndex});
                    solde = amount - debit;
                    rowIndex = rowIndex +1;
                    rowData = addNewRecordsLine(ui.rowData.counterpart, ui.rowData.counterPartCode, ui.rowData.counterPartName, solde, credit, ui.rowData.libelle);
                    rowIndx = window[\'gridNewBookRecords\'].addRow({newRow: rowData, rowIndx: rowIndex});
                    if(ui.rowData.counterpart == 0) {
                        jumpGridCell(rowIndx, "account");
                    }
                    window[\'gridNewBookRecords\'].goToPage({rowIndx: rowIndx});

                }
                if(ui.rowData.id_stdaccount_type == 5) {

                    amount = ui.rowData.debit;
                    credit = amount*0.2;


                    var rowData = addNewRecordsLine(ui.rowData.default_vat, ui.rowData.defaultVatCode, ui.rowData.defaultVatName, debit, credit, ui.rowData.libelle);

                    var rowIndx = window[\'gridNewBookRecords\'].addRow({newRow: rowData, rowIndx: rowIndex});
                    solde = amount - credit;
                    rowIndex = rowIndex +1;
                    rowData = addNewRecordsLine(ui.rowData.counterpart, ui.rowData.counterPartCode, ui.rowData.counterPartName, debit, solde, ui.rowData.libelle);
                    rowIndx = window[\'gridNewBookRecords\'].addRow({newRow: rowData, rowIndx: rowIndex});
                    if(ui.rowData.counterpart == 0) {
                        jumpGridCell(rowIndx, "account")
                    }
                    window[\'gridNewBookRecords\'].goToPage({rowIndx: rowIndx});

                }
                rowData =  addEmptyRecordsLine();
                rowIndex = rowIndex +1;
                rowIndx = window[\'gridNewBookRecords\'].addRow({rowData: rowData, rowIndx: rowIndex});
                window[\'gridNewBookRecords\'].goToPage({rowIndx: rowIndx});
                jumpGridCell(rowIndx, "account");
                 this.refresh();

            }

        }';
        $paragrid->complete = 'function(){

        window[\'gridNewBookRecords\'].editCell( { rowIndx: 0, dataIndx: "account" } );


        }';

        $paragrid->groupModel = [
            'on'           => true,
            'grandSummary' => true,
            'header'       => 0,
        ];
        $paragrid->summaryTitle = [
            'sum' => '"{0}"',
        ];
        $paragrid->showTitle = 1;
        $paragrid->title = '""';
        $paragrid->fillHandle = '\'all\'';

        $option = $paragrid->generateParaGridOption();
        $this->paragridScript = $paragrid->generateParagridScript();

        return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
    }

    public function ajaxProcessGetNewBookRecordsFields() {

        $fields = [

            [
                'title'    => $this->l('Date'),
                'width'    => 150,
                'dataIndx' => 'date_add',
                'align'    => 'center',
                'valign'   => 'center',
                'dataType' => 'date',
                'format'   => 'dd/mm/yy',
                'editable' => true,
                'editor'   => [
                    'type' => "textbox",
                    'init' => 'dateEditor',
                ],
            ],

            [
                'title'    => $this->l('N?? de Compte'),
                'width'    => 150,
                'dataIndx' => 'account',
                'dataType' => 'string',
                'editable' => true,
                'editor'   => [
                    'type' => "textbox",
                    'init' => 'autoCompleteAccount',
                ],
            ],
            [
                'title'    => $this->l('Libell??'),
                'width'    => 100,
                'dataIndx' => 'libelle',
                'dataType' => 'string',
                'editable' => true,
            ],
            [
                'title'    => $this->l('Montant D??bit'),
                'width'    => 150,
                'dataIndx' => 'debit',
                'dataType' => 'float',
                'format'   => '# ##0,00',
                'editable' => true,
                'summary'  => [
                    'type' => 'sum',
                ],
            ],
            [
                'title'    => $this->l('Montant Cr??dit'),
                'width'    => 150,
                'dataIndx' => 'credit',
                'dataType' => 'float',
                'format'   => '# ##0,00',
                'editable' => true,
                'summary'  => [
                    'type' => 'sum',
                ],
            ],
            [
                'title'    => $this->l('Intitul?? du compte'),
                'width'    => 150,
                'dataIndx' => 'name',
                'dataType' => 'string',
            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'id_stdaccount',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'id_stdaccount_type',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'vat_exonerate',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'default_vat',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'defaultVatName',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'counterpart',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'counterPartName',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'defaultVatCode',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'counterPartCode',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],
            [
                'title'      => ' ',
                'dataIndx'   => 'resolve',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',

            ],

        ];

        die(Tools::jsonEncode($fields));

    }

    public function ajaxProcessGetNewBookRecordsRequest() {

        $details[] = [
            'date_add' => date("Y-m-d"),
            'account'  => '',
            'libelle'  => '',
            'debit'    => '',
            'credit'   => '',
            'name'     => '',
            'resolve'  => 0,
        ];

        for ($i = 1; $i < 28; $i++) {
            $details[] = [
                'date_add' => '',
                'account'  => '',
                'libelle'  => '',
                'debit'    => '',
                'credit'   => '',
                'name'     => '',
                'resolve'  => 0,
            ];
        }

        die(Tools::jsonEncode($details));
    }

    public function generateBookDetailsScript($idBookRecord) {

        $paragrid = new ParamGrid('BookRecordDetails', $this->controller_name, 'book_record_details', 'id_book_record_details');

        $paragrid->paramGridObj = 'objBookRecordDetails';
        $paragrid->paramGridVar = 'gridBookRecordDetails';
        $paragrid->paramGridId = 'grid_BookRecordDetails';
        $paragrid->requestModel = '{
            location: "remote",
            dataType: "json",
            method: "GET",
            recIndx: "id_book_record_details",
            url: AjaxLink' . $this->controller_name . '+"&action=getBookRecordDetailsRequest&id_book_record=' . $idBookRecord . '&ajax=1",
            getData: function (dataJSON) {
                return { data: dataJSON };
                }


        }';

        $paragrid->height = '350';
        $paragrid->columnBorders = 1;

        $paragrid->showNumberCell = 0;

        $paragrid->groupModel = [
            'on'           => true,
            'grandSummary' => true,
            'header'       => 0,
        ];
        $paragrid->summaryTitle = [
            'sum' => '"{0}"',
        ];
        $paragrid->showTitle = 1;
        $paragrid->title = '""';
        $paragrid->fillHandle = '\'all\'';

        $option = $paragrid->generateParaGridOption();
        $this->paragridScript = $paragrid->generateParagridScript();

        return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
    }

    public function ajaxProcessGetBookRecordDetailsRequest() {

        $idLang = Context::getContext()->language->id;

        $id_book_record = Tools::getValue('id_book_record');

        $details = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('brd.*, s.account, sl.`name` as name')
                ->from('book_record_details', 'brd')
                ->leftJoin('stdaccount', 's', 's.`id_stdaccount` = brd.`id_stdaccount`')
                ->leftJoin('stdaccount_lang', 'sl', 'sl.`id_stdaccount` = brd.`id_stdaccount` AND sl.`id_lang` = ' . (int) $idLang)
                ->where('brd.`id_book_record` = ' . (int) $id_book_record)
        );

        die(Tools::jsonEncode($details));
    }

    public function ajaxProcessGetBookRecordDetailsFields() {

        $fields = [
            [

                'dataIndx' => 'id_book_record_details',
                'dataType' => 'integer',
                'editable' => false,
                'hidden'   => true,
            ],
            [
                'title'    => $this->l('N?? de Compte'),
                'width'    => 150,
                'dataIndx' => 'account',
                'dataType' => 'string',
            ],

            [
                'title'    => $this->l('Libell??'),
                'width'    => 100,
                'dataIndx' => 'libelle',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->l('Montant D??bit'),
                'width'    => 150,
                'dataIndx' => 'debit',
                'dataType' => 'float',
                'format'   => '# ##0,00',
                'summary'  => [
                    'type' => 'sum',
                ],
            ],
            [
                'title'    => $this->l('Montant Cr??dit'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'credit',
                'dataType' => 'float',
                'format'   => '# ##0,00',
                'summary'  => [
                    'type' => 'sum',
                ],
            ],
            [
                'title'    => $this->l('Intitul?? du compte'),
                'width'    => 150,
                'dataIndx' => 'name',
                'dataType' => 'string',
            ],

        ];

        die(Tools::jsonEncode($fields));

    }

    public function ajaxProcessUpdateCompany() {

        $id = (int) Tools::getValue('id_company');

        if (isset($id) && !empty($id)) {
            /** @var ObjectModel $object */
            $object = new Company($id);

        } else {

            $object = new Company();
        }

        /* Specific to objects which must not be deleted */

        foreach ($_POST as $key => $value) {

            if (property_exists($object, $key) && $key != 'id_company') {

                $object->{$key}

                = $value;
            }

        }

        if (isset($id) && !empty($id)) {
            $result = $object->update();

        } else {
            $result = $object->add();
        }

        if (!isset($result) || !$result) {
            $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
        } else {
            $result = [
                'success' => true,
                'message' => $this->l('La soci??t?? a ??t?? mise ?? jour avec succ??s'),
            ];

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

    public function ajaxProcessUpdateLastTimeStamp() {

        $this->context->employee->last_timestamp = time();
        $this->context->employee->update();

        die(true);
    }

    public function getMemberOnline() {

        $curentTimestamp = time() - 3000;

        $onlines = [];

        $employees = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_employee`, `firstname`, `lastname`, `email`, `last_timestamp`')
                ->from('employee')
        );

        foreach ($employees as $index => &$employee) {

            if ($employee['id_employee'] == $this->context->employee->id) {

                continue;
            }

            $employee['online'] = false;

            if ($employee['last_timestamp'] > $curentTimestamp) {
                $employee['online'] = true;
            }

            if (file_exists(_PS_EMPLOYEE_IMG_DIR_ . $employee['id_employee'] . '.jpg')) {
                $employee['image_link'] = 'img/e/' . $employee['id_employee'] . '.jpg';
            } else {
                $employee['image_link'] = 'img/e/Unknown.png';
            }

            $employee['type'] = 'employee';
            $employee['id_member'] = $employee['id_employee'];
            $onlines[] = $employee;
        }

        $saleAgents = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_sale_agent`, `firstname`, `lastname`, `email`, `last_timestamp`')
                ->from('sale_agent')
        );

        foreach ($saleAgents as &$saleAgent) {
            $saleAgent['online'] = false;
            $agent = new SaleAgent($saleAgent['id_sale_agent']);

            if ($saleAgent['last_timestamp'] > $curentTimestamp) {
                $saleAgent['online'] = true;
            }

            if (file_exists(_PS_SALEAGENT_IMG_DIR_ . $agent->id_student . '.jpg')) {
                $saleAgent['image_link'] = _PS_IMG_ . 'sa/' . $agent->id_student . '.jpg';
            } else {
                $saleAgent['image_link'] = _PS_IMG_ . 'sa/Unknown.png';
            }

            $saleAgent['type'] = 'agent';
            $saleAgent['id_member'] = $agent->id;
            $onlines[] = $saleAgent;
        }

        return $onlines;
    }

    public function ajaxProcessOpenSession() {

        $targetType = Tools::getValue('targetType');
        $idTarget = Tools::getValue('idTarget');
        $session = MessengerSession::openSession('employee', $this->context->employee->id, $targetType, $idTarget);

        $data = $this->createTemplate('messenger.tpl');
        $senderImageLink = $this->context->link->getEmployeeImageLink($this->context->employee->id);

        if ($targetType == 'agent') {
            $target = new SaleAgent($idTarget);
            $imageLink = $this->context->link->getProfilImageLink($target->id_student);

        } else

        if ($targetType == 'employee') {
            $imageLink = $this->context->link->getEmployeeImageLink($idTarget);
            $target = new Employee($idTarget);
        }

        $data->assign([
            'session'         => $session,
            'senderImageLink' => $senderImageLink,
            'senderNickName'  => $this->context->employee->firstname . ' ' . $this->context->employee->lastname,
            'id_sender'       => $this->context->employee->id,
            'senderType'      => 'employee',
            'id_friend'       => $idTarget,
            'targetType'      => $targetType,
            'nickname'        => $target->firstname . ' ' . $target->lastname,
            'link'            => $this->context->link,
            'imageLink'       => $imageLink,
            'target'          => $target,
        ]);
        $messages = [];

        $return = [
            'smarty'    => $data->fetch(),
            'messages'  => $messages,
            'idSession' => $session->id,
            'target'    => $targetType . '-' . $idTarget,
        ];
        die(Tools::jsonEncode($return));

    }

    public function ajaxProcessCloseSession() {

        $idSession = Tools::getValue('session');
        $session = new MessengerSession($idSession);

        if ($session->id_employee == $this->context->employee->id) {
            $session->active = 0;
        } else {
            $session->active_for_target = 1;
        }

        $session->update();
        die(true);

    }

    public function ajaxProcessDownSession() {

        $idSession = Tools::getValue('session');

        $session = new MessengerSession($idSession);

        if ($session->id_employee == $this->context->employee->id) {
            $session->close = 1;
        } else {
            $session->close_for_target = 1;
        }

        $session->update();
        die(true);

    }

    public function ajaxProcessUpSession() {

        $idSession = Tools::getValue('session');
        $session = new MessengerSession($idSession);
        $session->close = 0;
        $session->update();
        die(true);

    }

    public function ajaxProcessCheckSession() {

        $id_employee = $this->context->employee->id;
        $sessions = MessengerSession::getActiveSession($id_employee, 'employee');
        $return = [
            'sessions' => $sessions,
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessLoadSession() {

        $id_employee = $this->context->employee->id;
        $sessions = MessengerSession::getActiveSession($id_employee, 'employee');
        $senderImageLink = $this->context->link->getEmployeeImageLink($this->context->employee->id);
        $html = '';

        foreach ($sessions as $session) {

            if ($session->initiateur == 'employee') {

                if ($session->id_target_employee > 0) {
                    $idTarget = $session->id_target_employee;
                    $imageLink = $this->context->link->getEmployeeImageLink($idTarget);
                    $target = new Employee($idTarget);
                    $targetType = 'employee';
                }

                if ($session->id_target_agent > 0) {
                    $idTarget = $session->id_target_agent;
                    $target = new SaleAgent($idTarget);
                    $imageLink = $this->context->link->getProfilImageLink($target->id_student);
                    $targetType = 'agent';
                }

            } else {
                $idTarget = $session->id_sale_agent;
                $target = new SaleAgent($idTarget);
                $imageLink = $this->context->link->getProfilImageLink($target->id_student);
                $targetType = 'agent';
            }

            $data = $this->createTemplate('messenger.tpl');
            $data->assign([
                'session'         => $session,
                'senderImageLink' => $senderImageLink,
                'senderNickName'  => $this->context->employee->firstname . ' ' . $this->context->employee->lastname,
                'id_sender'       => $this->context->employee->id,
                'senderType'      => 'employee',
                'id_friend'       => $idTarget,
                'targetType'      => $targetType,
                'nickname'        => $target->firstname . ' ' . $target->lastname,
                'link'            => $this->context->link,
                'imageLink'       => $imageLink,
                'target'          => $target,
            ]);
            $html .= $data->fetch();
        }

        $return = [
            'smarty' => $html,
        ];
        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessOpenEmotji() {

        $target = Tools::getValue('target');
        $categories = EmojioneCategory::getEmojiCategories();
        $catalogues = EmojioneCategory::getEmojiCatalogue($categories);
        $data = $this->createTemplate('emotji.tpl');
        $data->assign([
            'categories' => $categories,
            'catalogues' => $catalogues,
            'target'     => $target,
            'link'       => $this->context->link,
        ]);

        $return = [
            'smarty' => $data->fetch(),
            'target' => $target,
        ];

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessSendChat() {

        $message = new Messenger();

        $name = 'image';

        if (is_array($_FILES)) {

            $fileUpload = [];

            foreach ($_FILES[$name]['name'] as $key => $val) {
                $pictureCount++;
                $Upload = [];
                $rand = Tools::passwdGen(36);
                $Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name'][$key]);
                $Upload['name'] = $_FILES[$name]['name'][$key];
                $Upload['mime'] = $_FILES[$name]['type'][$key];

                $uploadfile = _PS_MESSENGER_DIR_ . basename($rand . '.jpg');
                $sourcePath = $_FILES[$name]['tmp_name'][$key];

                move_uploaded_file($sourcePath, $uploadfile);

                $fileUpload[] = basename($rand . '.jpg');

            }

            $message->image_source = $fileUpload;

        }

        $application = 'application';

        if (is_array($_FILES) && isset($_FILES[$application]['name']) && !empty($_FILES[$application]['name']) && !empty($_FILES[$application]['tmp_name'])) {

            $fileCount = 0;

            if (is_array($_FILES)) {
                $dir = _PS_MESSENGER_DIR_;
                $output = '';

                foreach ($_FILES[$application]['name'] as $key => $val) {

                    $Upload = [];

                    $Upload['content'] = Tools::file_get_contents($_FILES[$application]['tmp_name'][$key]);
                    $Upload['name'] = $_FILES[$application]['name'][$key];
                    $Upload['mime'] = $_FILES[$application]['type'][$key];

                    $uploadfile = $dir . basename($_FILES[$application]['name'][$key]);
                    $sourcePath = $_FILES[$application]['tmp_name'][$key];
                    $file_ext = strtolower(end(explode('.', $_FILES[$application]['name'][$key])));

                    $rand = Messenger::getContentMessengerFile($_FILES[$application]['name'][$key], $file_ext);

                    move_uploaded_file($sourcePath, $uploadfile);
                    $fileUpload = basename($_FILES[$application]['name'][$key]);

                }

                $message->file_source = $rand;
            }

        }

        $message->id_messenger_session = Tools::getValue('idSession');
        $message->from_id_sender = Tools::getValue('from_id_sender');
        $message->to_id_sender = Tools::getValue('to_id_sender');
        $message->sender_type = 'employee';
        $message->target_type = Tools::getValue('target_type');
        $message->avatar = Tools::getValue('avatar');
        $message->nickname = Tools::getValue('nickname');
        $content = Tools::getValue('message');

        if (!empty($content)) {
            $message->message_content = Tools::getValue('message');
        }

        if ($urlLink = Tools::getValue('urlLink')) {

            $message->link_source = Tools::getContentLink($urlLink);

            if (empty($content)) {

                $message->message_content = Tools::getContentLinkTitle($urlLink);
            }

        }

        $message->message_type = 'text';

        $message->add();
        $message = new Messenger($message->id);
        $data = $this->createTemplate('message.tpl');
        $data->assign([
            'message'    => $message,
            'targetType' => $message->sender_type,
            'id_friend'  => $message->from_id_sender,
            'id_sender'  => $this->context->employee->id,
        ]);

        $return = [
            'smarty' => $data->fetch(),
        ];

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessUpdateMessenger() {

        $newMessage = [];
        $id_member = Tools::getValue('id_member');
        $messages = MessengerSession::getNewMessage($id_member);

        foreach ($messages as $key => $values) {

            foreach ($values as $message) {
                $data = $this->context->smarty->createTemplate(_PS_AGENT_DIR_ . 'message.tpl');
                $data->assign([
                    'message'    => $message,
                    'targetType' => $message->sender_type,
                    'id_friend'  => $message->from_id_sender,
                    'id_sender'  => $this->context->employee->id,
                ]);
                $newMessage[] = [
                    'message' => $message,
                    'target'  => $key,
                    'smarty'  => $data->fetch(),
                ];
            }

        }

        die(Tools::jsonEncode($newMessage));
    }

    public function ajaxProcessgetReaDMessage() {

        $id_member = Tools::getValue('id_member');
        $messages = MessengerSession::checkReadMessage($id_member);
        die(Tools::jsonEncode($messages));
    }

    public function ajaxProcessReadMessage() {

        $id_messenger = Tools::getValue('id_messenger');
        $message = new Messenger($id_messenger);
        $message->seen = 1;
        $message->update();
        die(true);
    }

    public function ajaxProcessRecdMessage() {

        $id_messenger = Tools::getValue('id_messenger');
        $message = new Messenger($id_messenger);
        $message->recd = 1;
        $message->update();
        die(true);
    }

    public function ajaxProcessRefreshMemberOnline() {

        $curentTimestamp = time() - 3000;

        $onlines = [];

        $employees = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_employee`, `last_timestamp`')
                ->from('employee')
        );

        foreach ($employees as $index => &$employee) {

            if ($employee['id_employee'] == $this->context->employee->id) {

                continue;
            }

            $onlines['employee-' . $employee['id_employee']] = false;

            if ($employee['last_timestamp'] > $curentTimestamp) {
                $onlines['employee-' . $employee['id_employee']] = true;
            }

        }

        $saleAgents = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_sale_agent`,  `last_timestamp`')
                ->from('sale_agent')
        );

        foreach ($saleAgents as &$saleAgent) {
            $onlines['agent-' . $saleAgent['id_sale_agent']] = false;

            if ($saleAgent['last_timestamp'] > $curentTimestamp) {
                $onlines['agent-' . $saleAgent['id_sale_agent']] = true;
            }

        }

        die(Tools::jsonEncode($onlines));
    }

    public function ajaxProcessOpenGraph() {

        $url = Tools::getValue('url');
        $postIndex = 0;

        if ($index = Tools::getValue('index')) {
            $postIndex = $index;
        }

        if (!empty($url) && filter_var($url, FILTER_VALIDATE_URL)) {

            if (strpos($url, 'youtube') !== false) {

                $video_id = explode("?v=", $url);
                $video_id = $video_id[1];
                $data = file_get_contents("https://www.googleapis.com/youtube/v3/videos?key=AIzaSyDKlcFaIJQnNTpVdYCxRRnKGQ19VqDiJBY&part=snippet&id=" . $video_id);
                $json = json_decode($data);
                $title = $json->items[0]->snippet->title;
                $body_content = $json->items[0]->snippet->description;
                $image_url = [];
                $image_url[0] = $json->items[0]->snippet->thumbnails->medium->url;

            } else {

                $html = Tools::file_get_contents_curl($url);

                $image_url = [];

                //parsing begins here:
                $doc = new DOMDocument();
                @$doc->loadHTML($html);

                $metas = $doc->getElementsByTagName('meta');

                for ($i = 0; $i < $metas->length; $i++) {
                    $meta = $metas->item($i);

                    if ($meta->getAttribute('property') == 'og:title') {
                        $title = $meta->getAttribute('content');
                    }

                    if ($meta->getAttribute('property') == 'og:image') {
                        $image_url[0] = $meta->getAttribute('content');
                    }

                    if ($meta->getAttribute('name') == 'description') {
                        $body_content = $meta->getAttribute('content');
                    }

                }

                if (empty($title)) {
                    $nodes = $doc->getElementsByTagName('title');
                    $title = $nodes->item(0)->nodeValue;
                }

                if (empty($image_url[0])) {

                    $content = Tools::file_get_html($url);

                    foreach ($content->find('img') as $element) {

                        if (filter_var($element->src, FILTER_VALIDATE_URL)) {
                            list($width, $height) = getimagesize($element->src);

                            if ($width > 150 || $height > 150) {
                                $image_url[0] = $element->src;
                                break;
                            }

                        }

                    }

                }

            }

            $image_div = "";

            if (!empty($image_url[0])) {
                $image_div = "<div class='image-extract col-lg-3 col-xs-12'>" .
                    "<input type='hidden' id='index' value='0'/>" .
                    "<img id='image_url' src='" . $image_url[0] . "' />";

                if (count($image_url) > 1) {
                    $image_div .= "<div>" .
                    "<input type='button' class='btnNav' id='prev-extract' onClick=navigateImage(" . json_encode($image_url) . ",'prev') disabled />" .
                    "<input type='button' class='btnNav' id='next-extract' target='_blank' onClick=navigateImage(" . json_encode($image_url) . ",'next') />" .
                        "</div>";
                }

                $image_div .= "</div>";

            }

            if ($title_only = Tools::getValue('title_only') == 1) {
                $output = $image_div . "<div class='content-extract col-lg-9 col-xs-12'>" .
                    "<h3><a href='" . $url . "' target='_blank' style='color:white;'>" . $title . "</a></h3>" .
                    "</div>";
            } else {
                $output = $image_div . "<div class='content-extract col-lg-9 col-xs-12'>" .
                    "<h3><a href='" . $url . "' target='_blank'>" . $title . "</a></h3>" .
                    "<div>" . $body_content . "</div>" .
                    "</div>";
            }

            $return = [
                'html'  => $output,
                'index' => $postIndex,

            ];

            die(Tools::jsonEncode($return));

        }

    }

    public function ajaxProcessGetAutoCompleteCity() {

        $query = Tools::getValue('search');

        $results = Address::getAutoCompleteCity($query);

        die(Tools::jsonEncode($results));

    }

    public function ajaxProcessCheckReadMessage() {

        $newMessage = [];
        $id_member = Tools::getValue('id_member');

        $messages = MessengerSession::checkReadMessage('employee', $this->context->employee->id);

        foreach ($messages as $key => $values) {

            foreach ($values as $message) {
                $newMessage[] = [
                    'message' => $message,
                    'target'  => $key,
                ];
            }

        }

        die(Tools::jsonEncode($newMessage));
    }

    public function ajaxProcessTypperWrite() {

        $target = Tools::getValue('target');
        $current = explode('-', $target);
        $idSession = MessengerSession::findSession($this->context->employee->id, $current[1]);
        $session = new MessengerSession($idSession);

        if ($session->id_employee == $this->context->employee->id) {
            $session->initiateur_istypping = 1;
        } else {

            $session->target_istypping = 1;
        }

        $session->update();

    }

    public function ajaxprocesstypperStopWrite() {

        $target = Tools::getValue('target');
        $current = explode('-', $target);
        $idSession = MessengerSession::findSession($this->context->employee->id, $current[1]);
        $session = new MessengerSession($idSession);

        if ($session->id_employee == $this->context->employee->id) {
            $session->initiateur_istypping = 0;
        } else {

            $session->target_istypping = 0;
        }

        $session->update();
    }

    public function ajaxProcessEditSlider() {

        $data = $this->createTemplate('controllers/script.tpl');
        $link = Tools::getValue('link');
        $id = Tools::getValue('id');

        $extraJs = $this->getJsContent([
            $this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/wp-specs.js',
            $this->admin_webpath . '/js/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
            $this->admin_webpath . '/js/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider.transitions.js',
            $this->admin_webpath . '/js/slider.js',
            $this->admin_webpath . '/js/ls-admin-slider-builder.js',
            $this->admin_webpath . '/js/layerslider.transition.gallery.js',
            $this->admin_webpath . '/js/layerslider.timeline.js',
            $this->admin_webpath . '/js/layerslider.origami.js',
            $this->admin_webpath . '/js/jquery.minicolors.js',
            $this->admin_webpath . '/js/air-datepicker.min.js',
            $this->admin_webpath . '/js/html2canvas.min.js',
        ]);

        $extracss = $this->pushCSS([
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.transitiongallery.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.timeline.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.origami.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/jquery.minicolors.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/air-datepicker.min.css',
        ]);
        $data->assign([
            'extraJs'    => $extraJs,
            'extracss'   => $extracss,
            'link'       => $this->context->link,
            'controller' => $this->controller_name,
        ]);

        $_POST['controller'] = $this->controller_name;
        $this->ajax = false;

        $li = '<li id="uperEditSlider" data-controller="AdminDashboard"><a href="#contentEditSlider">Edition de un Slider</a><button type="button" class="close tabdetail" data-id="uperEditSlider"><i class="icon icon-times-circle"></i></button></li>';

        //$this->initProcess();
        $this->initContent();

        $this->ajaxLayout = true;

        $this->content = $this->ajaxDisplay($link);

        $html = '<div id="contentEdit" class="panel col-lg-12" style="display; flow-root;"><div id="areaListe">' . $this->content . '</div><div id="areaEdit" style="display:none"></div>' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,
            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessOpenSlider() {

        $data = $this->createTemplate('controllers/script.tpl');

        $extraJs = $this->getJsContent([
            $this->admin_webpath . '/js/wp-pointer.min.js',
            $this->admin_webpath . '/js/wp-specs.js',
            $this->admin_webpath . '/js/greensock.js',
            $this->admin_webpath . '/js/km-ui.js',
            $this->admin_webpath . '/js/ls-admin-common.js',
            $this->admin_webpath . '/js/codemirror.js',
            $this->admin_webpath . '/js/css.js',
            $this->admin_webpath . '/js/javascript.js',
            $this->admin_webpath . '/js/foldcode.js',
            $this->admin_webpath . '/js/foldgutter.js',
            $this->admin_webpath . '/js/brace-fold.js',
            $this->admin_webpath . '/js/active-line.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
            $this->admin_webpath . '/js/ls-admin-sliders.js',
            $this->admin_webpath . '/js/layerslider.webshopworks.jquery.js',
            $this->admin_webpath . '/js/layerslider.transitions.js',
            $this->admin_webpath . '/js/slider.js',
        ]);

        $extracss = $this->pushCSS([
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/global.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/dashicons.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin_new.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/km-ui.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/codemirror.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/solarized.mod.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/layerslider.css',
        ]);

        $data->assign([
            'extraJs'    => $extraJs,
            'extracss'   => $extracss,
            'link'       => $this->context->link,
            'controller' => $this->controller_name,
        ]);

        $_POST['controller'] = $this->controller_name;
        //$this->ajax = false;

        $li = '<li id="uperAdminLayerSlider" data-controller="AdminDashboard"><a href="#contentAdminLayerSlider">Edition de un Slider</a><button type="button" class="close tabdetail" data-id="uperAdminLayerSlider"><i class="icon icon-times-circle"></i></button></li>';

        //$this->initProcess();
        //$this->initContent();

        $this->ajaxLayout = true;

        $this->content = $this->ajaxDisplay();

        $html = '<div id="contentAdminLayerSlider" class="panel col-lg-12" style="display; flow-root;"><div id="areaListe">' . $this->content . '</div><div id="areaEdit" style="display:none"></div>' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,
            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessOpenContentAnywhere() {

        $data = $this->createTemplate('controllers/script.tpl');

        $data->assign([

            'link'       => $this->context->link,
            'controller' => $this->controller_name,
        ]);

        $_POST['controller'] = $this->controller_name;
        $this->ajax = false;

        $li = '<li id="uper' . $this->controller_name . '" data-controller="AdminDashboard"><a href="#content' . $this->controller_name . '">Gestion de contenu</a><button type="button" class="close tabdetail" data-id="uper' . $this->controller_name . '"><i class="icon icon-times-circle"></i></button></li>';

        //$this->initProcess();
        $this->initContent();

        $this->ajaxLayout = true;

        $this->content = $this->ajaxDisplay($this->controller_name);

        $html = '<div id="content' . $this->controller_name . '" class="panel col-lg-12" style="display; flow-root;"><div id="areaListe">' . $this->content . '</div><div id="areaEdit" style="display:none"></div>' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,
            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function getShortStdAccountFields() {

        $fields = [
            [

                'dataIndx' => 'id_stdaccount',
                'dataType' => 'integer',
                'editable' => false,
                'hidden'   => true,
            ],

            [
                'title'    => $this->l('Num??ro de Compte'),
                'width'    => 100,
                'dataIndx' => 'account',
                'dataType' => 'string',
                'filter'   => [
                    'crules' => [['condition' => "begin"]],
                ],
            ],
            [
                'title'    => $this->l('Nom'),
                'width'    => 150,
                'dataIndx' => 'name',
                'dataType' => 'string',
            ],
            [
                'title'    => $this->l('D??scription'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'description',
                'dataType' => 'string',
            ],

        ];

        return $fields;

    }

    public function ajaxProcessOpenTargetController() {

        $targetController = $this->targetController;

        $this->paragridScript = $this->generateParaGridScript();
        $this->setAjaxMedia();

        $data = $this->createTemplate('controllers/' . $this->table . '.tpl');

        foreach ($this->extra_vars as $key => $value) {
            $data->assign($key, $value);
        }

        $data->assign([
            'paragridScript'     => $this->paragridScript,
            'manageHeaderFields' => $this->manageHeaderFields,
            'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
            'controller'         => $this->controller_name,
            'tableName'          => $this->table,
            'className'          => $this->className,
            'link'               => $this->context->link,
            'id_lang_default'    => Configuration::get('PS_LANG_DEFAULT'),
            'extraJs'            => $this->push_js_files,
            'extracss'           => $this->extracss,
            'tabs'               => $this->ajaxOptions,
            'bo_imgdir'          => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
        ]);

        $li = '<li id="uper' . $targetController . '" data-controller="AdminDashboard"><a href="#content' . $targetController . '">' . $this->publicName . '</a><button type="button" class="close tabdetail" data-id="uper' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
        $html = '<div id="content' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,

            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessEditObject() {

        $targetController = $this->targetController;

        if ($this->tabAccess['edit'] == 1) {

            $idObject = Tools::getValue('idObject');

            $_GET[$this->identifier] = $idObject;
            $_GET['update' . $this->table] = "";
            $scripHeader = Hook::exec('displayBackOfficeHeader', []);
            $scriptFooter = Hook::exec('displayBackOfficeFooter', []);

            $html = $this->renderForm();
            $li = '<li id="uperEdit' . $targetController . '" data-controller="AdminDashboard"><a href="#contentEdit' . $targetController . '">' . $this->editObject . '</a><button type="button" class="close tabdetail" data-id="uperEdit' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
            $html = '<div id="contentEdit' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $scripHeader . $html . $scriptFooter . '</div>';

            $result = [
                'success' => true,
                'li'      => $li,
                'html'    => $html,
            ];
        } else {
            $result = [
                'success' => false,
                'message' => 'Votre profile administratif ne vous permet pas d?????diter cette objet',
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessAddObject() {

        $targetController = $this->targetController;

        $_GET['add' . $this->table] = "";

        $scripHeader = Hook::exec('displayBackOfficeHeader', []);
        $scriptFooter = Hook::exec('displayBackOfficeFooter', []);
        $html = $this->renderForm();

        $li = '<li id="uperAdd' . $targetController . '" data-controller="AdminDashboard"><a href="#contentAdd' . $targetController . '">' . $this->editObject . '</a><button type="button" class="close tabdetail" data-id="uperAdd' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
        $html = '<div id="contentAdd' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $scripHeader . $html . $scriptFooter . '</div>';

        $result = [
            'li'   => $li,

            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessDeleteObject() {

        $idObject = Tools::getValue('idObject');

        $this->object = new $this->className($idObject);

        $this->object->delete();

        $result = [
            'success' => true,
            'message' => 'La suppression s???est d??roul??e avec succ??s.',
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessUpdateObject() {

        $idObject = Tools::getValue($this->identifier);
        $this->object = new $this->className($idObject);

        $this->copyFromPost($this->object, $this->table);

        $this->beforeUpdate($this->object);

        $result = $this->object->update();

        $this->afterUpdate($this->object);

        if ($result) {

            $return = [
                'success' => true,
                'message' => sprintf($this->l('L???objet de type % a ??t?? mise ?? jour avec succ??s'), $this->className),
            ];
        } else {
            $return = [
                'success' => false,
                'message' => $this->l('Une erreur s\'est produite en essayant de mettre ?? jour cet objet'),
            ];
        }

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessAddNewAjaxObject() {

        $this->object = new $this->className();

        $this->copyFromPost($this->object, $this->table);

        $this->beforeAdd($this->object);

        $result = $this->object->update();

        $this->afterAdd($this->object);

        if ($result) {
            $this->afterAdd($this->object);

        } else {
            $return = [
                'success' => false,
                'message' => $this->l('Une erreur s\'est produite en essayant d???ajouter cet objet'),
            ];
            die(Tools::jsonEncode($return));
        }

    }

}
