<?php

/**
 * Class AdminEducationsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminEducationsControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    /** @var int Max image size for upload
     * As of 1.5 it is recommended to not set a limit to max image size
     */
    protected $max_file_size = null;
    protected $max_image_size = null;

    protected $_category;
    /**
     * @var string name of the tab to display
     */
    protected $tab_display;
    protected $tab_display_module;

    /**
     * The order in the array decides the order in the list of tab. If an element's value is a number, it will be preloaded.
     * The tabs are preloaded from the smallest to the highest number.
     *
     * @var array Education tabs.
     */
    protected $available_tabs = [];

    /** @var string $default_tab */
    protected $default_tab = 'Informations';

    /** @var array $available_tabs_lang */
    protected $available_tabs_lang = [];

    /** @var string $position_identifier */
    protected $position_identifier = 'id_education';

    /** @var array $submitted_tabs */
    protected $submitted_tabs;

    /** @var int $id_current_category */
    protected $id_current_category;

    /** @var Education $object */
    public $object;

    // @codingStandardsIgnoreEnd

    protected $ajax_json = [
        'success' => false,
        'message' => null,
    ];

    public $typeSelector;

    public $availableSelector;

    public $specificPriceFields;

    public $imageLinks;

    public $educationRequest;

    public $declinaisonFields;

    /**
     * AdminEducationsControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'education';
        $this->className = 'Education';
        $this->lang = true;
        $this->publicName = $this->l('Nos Formations');
        $this->context = Context::getContext();

        parent::__construct();
        $this->context = Context::getContext();
        EmployeeConfiguration::updateValue('EXPERT_EDUCATIONS_SCRIPT', $this->generateParaGridScript());
        $this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONS_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_EDUCATIONS_SCRIPT', $this->generateParaGridScript());
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_EDUCATIONS_SCRIPT');
        }

        EmployeeConfiguration::updateValue('EXPERT_EDUCATIONS_FIELDS', Tools::jsonEncode($this->getEducationFields()));
        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONS_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_EDUCATIONS_FIELDS', Tools::jsonEncode($this->getEducationFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONS_FIELDS'), true);
        }

        $this->declinaisonFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATION_DECLINAISON_FIELDS'), true);

        EmployeeConfiguration::updateValue('EXPERT_EDUCATION_DECLINAISON_FIELDS', Tools::jsonEncode($this->getDeclinaisonFields()));

        if (empty($this->declinaisonFields)) {
            EmployeeConfiguration::updateValue('EXPERT_EDUCATION_DECLINAISON_FIELDS', Tools::jsonEncode($this->getDeclinaisonFields()));
            $this->declinaisonFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATION_DECLINAISON_FIELDS'), true);
        }

        $this->educationRequest = $this->getEducationRequest();

        $this->imageType = 'jpg';
        $this->_defaultOrderBy = 'position';
        $this->max_file_size = (int) (Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE') * 1000000);
        $this->max_image_size = (int) Configuration::get('PS_PRODUCT_PICTURE_MAX_SIZE');
        $this->allow_export = true;

        // @since 1.5 : translations for tabs
        $this->available_tabs_lang = [
            'Informations' => $this->l('Information'),
            'Prices'       => $this->l('Prix'),
            'Seo'          => $this->l('SEO'),
            'Images'       => $this->l('Images'),
            'Declinaisons' => $this->l('Déclinaisons'),
            'Attachments'  => $this->l('Programme de Formation'),
            'Accounting'   => $this->l('Comptabilité'),
            'Prerequis'    => $this->l('Prérequis'),
        ];

        if ($this->context->shop->getContext() != Shop::CONTEXT_GROUP) {
            $this->available_tabs = array_merge($this->available_tabs, [
                'Informations' => 0,
                'Prices'       => 1,
                'Seo'          => 2,
                'Images'       => 10,
                'Declinaisons' => 6,
                'Attachments'  => 3,
                'Accounting'   => 8,
                'Prerequis'    => 11,
            ]);
        }

        // Sort the tabs that need to be preloaded by their priority number
        asort($this->available_tabs, SORT_NUMERIC);

        /* Adding tab if modules are hooked */
        $modulesList = Hook::getHookModuleExecList('displayAdminEducationsExtra');

        if (is_array($modulesList) && count($modulesList) > 0) {

            foreach ($modulesList as $m) {
                $this->available_tabs['Module' . ucfirst($m['module'])] = 23;
                $this->available_tabs_lang['Module' . ucfirst($m['module'])] = Module::getModuleName($m['module']);
            }

        }

        if (Tools::getValue('reset_filter_category')) {
            $this->context->cookie->id_category_educations_filter = false;
        }

        if (Shop::isFeatureActive() && $this->context->cookie->id_category_educations_filter) {
            $category = new Category((int) $this->context->cookie->id_category_educations_filter);

            if (!$category->inShop()) {
                $this->context->cookie->id_category_educations_filter = false;
                Tools::redirectAdmin($this->context->link->getAdminLink('AdminEducations'));
            }

        }

    }

  
    public function setAjaxMedia() {

        return $this->pushJS([
            $this->admin_webpath . '/js/jquery.tablednd.js',
            $this->admin_webpath . '/js/educations.js',
            $this->admin_webpath . '/js/attributes.js',
            $this->admin_webpath . '/js/educationprice.js',
            $this->admin_webpath . '/js/tiny_mce/tiny_mce.js',
            $this->admin_webpath . '/js/tinymce.inc.js',
            $this->admin_webpath . '/js/dnd.js',
            $this->admin_webpath . '/js/jquery/ui/jquery.ui.progressbar.min.js',
            $this->admin_webpath . '/js/vendor/spin.js',
            $this->admin_webpath . '/js/vendor/ladda.js',
        ]);
    }

    public function initProcess() {

        if (Tools::isSubmit('submitAddeducationAndStay') || Tools::isSubmit('submitAddeducation')) {
            // Clean up possible education type changes.
            $typeEducation = (int) Tools::getValue('type_education');
            $idEducation = (int) Tools::getValue('id_education');

            if ($typeEducation !== Education::PTYPE_PACK) {

                if (!EducationPack::deleteItems($idEducation)) {
                    $this->errors[] = Tools::displayError('Cannot delete education pack items.');
                };
            }

            if ($typeEducation !== Education::PTYPE_VIRTUAL) {
                $idEducationDownload = EducationDownload::getIdFromIdEducation($idEducation, false);

                if ($idEducationDownload) {
                    $educationDownload = new EducationDownload($idEducationDownload);

                    if (!$educationDownload->delete()) {
                        $this->errors[] = Tools::displayError('Cannot delete education download.');
                    }

                }

            }

        }

        // Delete a education in the download folder

        if (Tools::isSubmit('submitAddEducationAndPreview')) {
            // Education preview
            $this->display = 'edit';
            $this->action = 'save';

            if (Tools::getValue('id_education')) {
                $this->id_object = Tools::getValue('id_education');
                $this->object = new Education((int) Tools::getValue('id_education'));
            }

        } else

        if (Tools::getIsset('duplicate' . $this->table)) {
            // Education duplication

            if ($this->tabAccess['add'] === '1') {
                $this->action = 'duplicate';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }

        } else

        if (Tools::getValue('id_image') && Tools::getValue('ajax')) {
            // Education images management

            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'image';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (Tools::isSubmit('submitEducationAttribute')) {
            // Education attributes management

            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'educationAttribute';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (Tools::isSubmit('submitPricesModification')) {
            // Education specific prices management NEVER USED

            if ($this->tabAccess['add'] === '1') {
                $this->action = 'pricesModification';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to add this.');
            }

        } else

        if (Tools::isSubmit('deleteSpecificPrice')) {

            if ($this->tabAccess['delete'] === '1') {
                $this->action = 'deleteSpecificPrice';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to delete this.');
            }

        } else

        if (Tools::isSubmit('submitSpecificPricePriorities')) {

            if ($this->tabAccess['edit'] === '1') {
                $this->action = 'specificPricePriorities';
                $this->tab_display = 'prices';
            } else {
                $this->errors[] = Tools::displayError('You do not have permission to edit this.');
            }

        } else

        if (Tools::isSubmit('id_education')) {
            $postMaxSize = Tools::getMaxUploadSize(Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE') * 1024 * 1024);

            if ($postMaxSize && isset($_SERVER['CONTENT_LENGTH']) && $_SERVER['CONTENT_LENGTH'] && $_SERVER['CONTENT_LENGTH'] > $postMaxSize) {
                $this->errors[] = sprintf(Tools::displayError('The uploaded file exceeds the "Maximum size for a downloadable education" set in preferences (%1$dMB) or the post_max_size/ directive in php.ini (%2$dMB).'), number_format((Configuration::get('PS_LIMIT_UPLOAD_FILE_VALUE'))), ($postMaxSize / 1024 / 1024));
            }

        }

        if (!$this->action) {
            parent::initProcess();
        } else {
            $this->id_object = (int) Tools::getValue($this->identifier);
        }

        if (isset($this->available_tabs[Tools::getValue('key_tab')])) {
            $this->tab_display = Tools::getValue('key_tab');
        }

        // Set tab to display if not decided already

        if (!$this->tab_display && $this->action) {

            if (in_array($this->action, array_keys($this->available_tabs))) {
                $this->tab_display = $this->action;
            }

        }

        // And if still not set, use default

        if (!$this->tab_display) {

            if (in_array($this->default_tab, $this->available_tabs)) {
                $this->tab_display = $this->default_tab;
            } else {
                $this->tab_display = key($this->available_tabs);
            }

        }

    }

    public function initContent($token = null) {

        if ($this->display == 'edit' || $this->display == 'add') {
            $this->fields_form = [];

            // Check if Module

            if (substr($this->tab_display, 0, 6) == 'Module') {
                $this->tab_display_module = strtolower(substr($this->tab_display, 6, mb_strlen($this->tab_display) - 6));
                $this->tab_display = 'Modules';
            }

            if (method_exists($this, 'initForm' . $this->tab_display)) {
                $this->tpl_form = 'controllers/educations/' . strtolower($this->tab_display) . '.tpl';
            }

            if ($this->ajax) {
                $this->content_only = true;
            } else {
                $educationTabs = [];

                // tab_display defines which tab to display first

                if (!method_exists($this, 'initForm' . $this->tab_display)) {
                    $this->tab_display = $this->default_tab;
                }

                foreach ($this->available_tabs as $educationTab => $value) {

                    $educationTabs[$educationTab] = [
                        'id'       => $educationTab,
                        'selected' => (strtolower($educationTab) == strtolower($this->tab_display) || (isset($this->tab_display_module) && 'module' . $this->tab_display_module == mb_strtolower($educationTab))),
                        'name'     => $this->available_tabs_lang[$educationTab],
                        'href'     => $this->context->link->getAdminLink('AdminEducations') . '&id_education=' . (int) Tools::getValue('id_education') . '&action=' . $educationTab,
                    ];
                }

                $this->tpl_form_vars['education_tabs'] = $educationTabs;
            }

        } else {

            $this->displayGrid = true;
            $this->paramGridObj = 'obj' . $this->className;
            $this->paramGridVar = 'grid' . $this->className;
            $this->paramGridId = 'grid_' . $this->controller_name;

            $this->context->smarty->assign([
                'controller'         => Tools::getValue('controller'),
                'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
                'gridId'             => 'grid_AdminEducations',
                'manageHeaderFields' => false,
                'allowExport'        => true,
                'fieldsExport'       => $this->getExportFields(),
                'tableName'          => $this->table,
                'className'          => $this->className,
                'linkController'     => $this->context->link->getAdminLink($this->controller_name),
                'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
                'educTypes'          => EducationType::getEducationType($this->context->language->id),
                'is_category_filter' => (bool) $this->id_current_category,
                'paragridScript'     => $this->generateParaGridScript(),
                'id_lang_default'    => Configuration::get('PS_LANG_DEFAULT'),
            ]);

        }

        // @todo module free
        $this->tpl_form_vars['vat_number'] = file_exists(_PS_MODULE_DIR_ . 'vatnumber/ajax.php');
        $this->tpl_form_vars['titleBar'] = $this->l('Educations management');
        $this->context->smarty->assign([
            'titleBar'  => $this->l('Education management'),
            'bo_imgdir' => '/themes/' . $this->bo_theme . '/img/',
        ]);

        parent::initContent();
    }

    public function generateParaGridScript() {

        $this->typeSelector = '<div class="pq-theme"><select id="typeSelect"><option value="">' . $this->l('--Select--') . '</option>';

        foreach (EducationType::getEducationType($this->context->language->id) as $type) {
            $this->typeSelector .= '<option value="' . $type['id_education_type'] . '">' . $type['name'] . '</option>';
        }

        $this->typeSelector .= '</select></div>';

        $this->paramExtraFontcion = ['

            function buildEducationFilter(){

                $("#educationTypeSelect").selectmenu({
                    "change": function(event, ui) {
                        grid' . $this->className . '.filter({
                            mode: \'AND\',
                            rules: [
                                { dataIndx:"id_education_type", condition: "equal", value: ui.item.value}
                            ]
                        });
                    },
                });
            }
            function viewProgramme(idEducation) {
                $.ajax({
                    type: "GET",
                    url: AjaxLinkAdminEducations,
                    data: {
                        action: "viewProgramme",
                        idEducation: idEducation,
                        ajax: true
                    },
                    async: false,
                    dataType: "json",
                    success: function success(data) {
                        window.open(data.fileExport, "_blank");
                    }
                });
            }',
        ];
        $this->paramPageModel = [
            'type'       => '\'local\'',
            'rPP'        => 40,
            'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
        ];
        $this->paramToolbar = [
            'items' => [

                ['type' => '\'separator\''],

                [
                    'type'     => '\'button\'',
                    'icon'     => '\'ui-icon-disk\'',
                    'label'    => '\'' . $this->l('Add new Education') . '\'',
                    'cls'      => '\'buttonCleanProductPosition changes ui-button ui-widget ui-state-default ui-corner-all ui-button-text-only pull-right\'',
                    'listener' => 'function () {' . PHP_EOL . '
                           addAjaxObject("' . $this->controller_name . '");' . PHP_EOL . '
                          }' . PHP_EOL,
                ],

            ],
        ];

        $this->paramTitle = '\'' . $this->l('Liste de nos Formations') . '\'';
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
                        "add": {
                            name: \'' . $this->l('Ajouter une nouvelle formation') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                 addAjaxObject("' . $this->controller_name . '");
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Modifier la formation : ') . '\'' . '+rowData.reference,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                editAjaxObject("' . $this->controller_name . '", rowData.id_education)
                            }
                        },
                        "programme": {
                            name : \'' . $this->l('Visualiser le programme : ') . '\'' . '+rowData.reference,
                            icon: "edit",
                            visible: function(key, opt) {
                                if (rowData.cache_default_attribute == 0) {
                                    return true;
                                }
                                return false;
                            },
                            callback: function(itemKey, opt, e) {
                                viewProgramme(rowData.id_education);
                            }
                        },
                        "sep1": "---------",
                        "delete": {
                            name : \'' . $this->l('Supprimer la formation : ') . '\'' . '+rowData.reference,
                            icon: "delete",
                            callback: function(itemKey, opt, e) {
                               // deleteEducation(rowData.id_education);
                                deleteObject("' . $this->controller_name . '", "' . $this->className . '", "Supprimer une Formation", "Etes vous sure de vouloir supprimer la formation "+rowData.name+ " ?", "Oui", "Annuler",rowData.id_education);
                            }
                        },



                    },
                };
            }',
            ]];
        return parent::generateParaGridScript();
    }

    public function getEducationRequest() {

        $educations = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('e.*, el.`name`,  tl.`name` as `Tax`, t.rate, case when e.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as education_state, et.`name` as type, c.name as certificationName')
                ->from('education', 'e')
                ->leftJoin('education_lang', 'el', 'el.`id_education` = e.`id_education` AND el.`id_lang` = ' . (int) $this->context->language->id)
                ->leftJoin('education_type_lang', 'et', 'e.`id_education_type` = et.`id_education_type`')
                ->leftJoin('certification', 'c', 'c.`id_certification` = e.`id_certification`')
                ->leftJoin('tax_rules_group', 'tl', 'tl.`id_tax_rules_group` = e.`id_tax_rules_group`')
                ->leftJoin('tax', 't', 't.`id_tax` = tl.`id_tax_rules_group`')
                ->orderBy('e.id_education_type ASC')
                ->orderBy('e.id_education ASC')
        );
        $educationLink = $this->context->link->getAdminLink($this->controller_name);

        foreach ($educations as &$education) {
            $education['openLink'] = $educationLink . '&id_education=' . $education['id_education'] . '&updateeducation';
            $education['date'] = DateTime::createFromFormat('Y-m-d', $education['date_add']);
            $education['FinalPrice'] = $education['price'] * (1 + $education['rate'] / 100);
        }

        return $educations;

    }

    public function ajaxProcessgetEducationRequest() {

        die(Tools::jsonEncode($this->getEducationRequest()));

    }

    public function getEducationFields() {

        $select = $this->l('--Select--');
        return [
            [
                'title'    => $this->l('ID'),
                'dataIndx' => 'id_education',
                'maxWidth' => 50,
                'dataType' => 'integer',
                'align'    => 'center',
                'maxWidth' => 20,
                'filter'   => [

                    'crules' => [['condition' => "begin"]],
                ],
            ],
            [
                'title'    => '',
                'dataIndx' => 'cache_default_attribute',
                'dataType' => 'integer',
                'hidden'   => true,

            ],
            [
                'title'    => '',
                'dataIndx' => 'id_education_type',
                'dataType' => 'integer',
                'hidden'   => true,

            ],
            [
                'title'    => $this->l('Famille de formation'),
                'maxWidth' => 200,
                'dataIndx' => 'type',
                'dataType' => 'string',
                'align'    => 'left',

            ],

            [
                'title'    => $this->l('Référence'),
                'maxWidth' => 200,
                'dataIndx' => 'reference',
                'dataType' => 'string',
                'align'    => 'left',
                'filter'   => [

                    'crules' => [['condition' => "contain"]],
                ],
                'editor'   => [
                    'type' => 'textarea',
                ],

            ],
            [
                'title'    => $this->l('Désignation'),
                'maxWidth' => 400,
                'dataIndx' => 'name',
                'dataType' => 'string',
                'align'    => 'left',
                'filter'   => [

                    'crules' => [['condition' => "contain"]],
                ],
                'editor'   => [
                    'type' => 'textarea',
                ],

            ],

            [
                'title'        => $this->l('Tarif HT'),

                'dataIndx'     => 'price',
                'align'        => 'right',
                'valign'       => 'center',
                'dataType'     => 'float',
                'numberFormat' => '#,##0.00_-"€"',
                'editable'     => false,
                'format'       => "#.###,00 € ",
                'updatable'    => false,
            ],
            [
                'title'        => $this->l('Tarif TTC'),

                'dataIndx'     => 'FinalPrice',
                'align'        => 'right',
                'valign'       => 'center',
                'dataType'     => 'float',
                'numberFormat' => '#,##0.00_-"€"',
                'editable'     => false,
                'format'       => "#.###,00 € ",
                'updatable'    => false,
            ],

            [
                'title'    => $this->l('Certification'),
                'maxWidth' => 400,
                'dataIndx' => 'certificationName',
                'dataType' => 'string',
                'align'    => 'left',
                'filter'   => [

                    'crules' => [['condition' => "contain"]],
                ],

            ],

            [

                'title'    => '',
                'dataIndx' => 'active',
                'dataType' => 'integer',
                'hidden'   => true,
                'filter'   => [
                    'crules' => [['condition' => "equal"]],
                ],

            ],

            [
                'title'    => $this->l('Publiée'),
                'maxWidth' => 200,
                'dataIndx' => 'education_state',
                'align'    => 'center',
                'dataType' => 'html',

            ],

        ];

    }

    public function ajaxProcessgetEducationFields() {

        die(EmployeeConfiguration::get('EXPERT_EDUCATIONS_FIELDS'));
    }

    public function getDeclinaisonFields() {

        return [
            [
                'title'      => '',
                'dataIndx'   => 'id_education_attribute',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => '',
                'dataIndx'   => 'addLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => '',
                'dataIndx'   => 'openLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => '',
                'dataIndx'   => 'deleteLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],

            [
                'title'    => $this->l('Reference'),
                'width'    => 100,
                'dataIndx' => 'reference',
                'align'    => 'left',
                'editable' => true,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->l('Name'),
                'width'    => 100,
                'dataIndx' => 'name',
                'align'    => 'left',
                'editable' => true,
                'dataType' => 'string',
            ],
            [
                'title'    => $this->l('Attribute - value pair'),
                'width'    => 200,
                'dataIndx' => 'attributes',
                'dataType' => 'html',
                'editable' => false,
            ],

            [
                'title'    => $this->l('Price'),
                'width'    => 150,
                'dataIndx' => 'price',
                'align'    => 'right',
                'dataType' => 'float',
                'format'   => '€ #,###.00',
            ],

            [
                'title'    => $this->l('Default'),
                'width'    => 50,
                'dataIndx' => 'default_on',
                'align'    => 'center',
                'dataType' => 'html',
            ],

        ];
    }

    public function ajaxProcessGetDeclinaisonFields() {

        die(EmployeeConfiguration::get('EXPERT_EDUCATION_DECLINAISON_FIELDS'));
    }

    public function manageFieldsVisibility($fields) {

        return parent::manageFieldsVisibility($fields);
    }

    public function getDeclinaisonRequest($idEducation) {

        if ($idEducation > 0) {

            $education = new Education($idEducation);

            $combinations = $education->getAttributeDeclinaisons($this->context->language->id);

            $groups = [];

            if (is_array($combinations)) {
                $educationLink = $this->context->link->getAdminLink($this->controller_name);

                foreach ($combinations as &$combination) {

                    $combination['attributes'] = $education->name[$this->context->language->id] . '<br>' . $combination['group_name'] . ' :' . $combination['attribute_name'];
                    $combination['price'] = $combination['price'] + $education->price;
                    $combination['addLink'] = $educationLink . '&action=addNewCombination&ajax=true';
                    $combination['openLink'] = $educationLink . '&id_education_attribute=' . $combination['id_education_attribute'] . '&id_education=' . $idEducation . '&action=editEducationAttribute&ajax=true';
                    $combination['deleteLink'] = $educationLink . '&id_education_attribute=' . $combination['id_education_attribute'] . '&id_education=' . $idEducation . '&action=deleteEducationAttribute&ajax=true';

                }

            }

        }

        return $combinations;

    }

    public function ajaxProcessGetDeclinaisonRequest() {

        $object = Tools::getValue('id_education');

        die(Tools::jsonEncode($this->getDeclinaisonRequest($object)));
    }

    public function ajaxProcessEditEducationAttribute() {

        $idEducation = (int) Tools::getValue('id_education');
        $idEducationAttribute = (int) Tools::getValue('id_education_attribute');

        if ($idEducation && Validate::isUnsignedId($idEducation) && Validate::isLoadedObject($education = new Education((int) $idEducation))) {

            if (!$this->default_form_language) {
                $this->getLanguages();
            }

            $data = $this->createTemplate('controllers/educations/editdeclinaison.tpl');
            $combinations = $education->getAttributeDeclinaisonsById($idEducationAttribute, $this->context->language->id);

            $combos = Declinaison::getComboDeclinaison($idEducation, $this->context->language->id, $idEducationAttribute);
            $declinaison = new Declinaison($idEducationAttribute);
            $data->assign('declinaison', $declinaison);
            $data->assign('declinaisonIdAttributeGroup', $combinations['id_attribute_group']);
            $listattributes = [];
            $combinationReturn = [];
            $combinationName = '';

            foreach ($combinations as $key => $combination) {

                if ($key == 0) {

                    foreach ($combination as $k => $value) {
                        $combinationReturn[$k] = $value;
                    }

                }

            }

            foreach ($combinations as $key => $combination) {
                $combinations[$key]['attributes'][] = [$combination['group_name'], $combination['attribute_name'], $combination['id_attribute']];
                $combinationName .= $combination['group_name'] . ':' . $combination['attribute_name'] . ' - ';
                array_push($listattributes, [
                    'group_name'     => $combination['group_name'],
                    'attribute_name' => $combination['attribute_name'],
                    'id_attribute'   => $combination['id_attribute'],
                ]);
            }

            $combinationReturn['attributes'] = $listattributes;

            $combinationName = substr($combinationName, 0, -3);
            $attributeJs = [];
            $attributes = Attributes::getAttributes($this->context->language->id, true);

            foreach ($attributes as $k => $attribute) {
                $attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
            }

            foreach ($attributeJs as $k => $ajs) {
                natsort($attributeJs[$k]);
            }

            $currency = $this->context->currency;

            $data->assign('attributeJs', $attributeJs);
            $data->assign('combos', $combos);
            $data->assign('attributes_groups', AttributeGroup::getAttributesGroups($this->context->language->id));

            $data->assign('currency', $currency);

            $images = ImageEducation::getImages($this->context->language->id, $education->id);
            $data->assign('tax_exclude_option', Tax::excludeTaxeOption());

            $i = 0;
            $type = ImageType::getByNameNType('%', 'educations', 'height');

            if (isset($type['name'])) {
                $data->assign('imageType', $type['name']);
            } else {
                $data->assign('imageType', ImageType::getFormatedName('small'));
            }

            $data->assign('imageWidth', (isset($imageType['width']) ? (int) ($imageType['width']) : 64) + 25);

            foreach ($images as $k => $image) {
                $images[$k]['obj'] = new ImageEducation($image['id_image_education']);
                ++$i;
            }

            $data->assign('images', $images);

            $data->assign($this->tpl_form_vars);
            $data->assign(
                [
                    'combinations'          => $combinationReturn,
                    'education'             => $education,
                    'formatpacks'           => FormatPack::getFormatPack($this->context->language->id),
                    '_THEME_EDUC_DIR_'      => _THEME_EDUC_DIR_,
                    'languages'             => $this->_languages,
                    'default_form_language' => $this->default_form_language,
                    'id_lang'               => $this->context->language->id,
                    'bo_imgdir'             => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
                    'collectionPrerequis'   => new PhenyxShopCollection('EducationPrerequis'),
                ]
            );

            $result = [
                'html'        => $data->fetch(),
                'combination' => $combinationReturn,
                'education'   => $education,
            ];
            die(Tools::jsonEncode($result));

        }

    }

    public function ajaxProcessAddObject() {

        $targetController = $this->targetController;
        $this->object = new Education();
        $data = $this->createTemplate('controllers/educations/addEducation.tpl');
        $_GET['addeducation'] = '';

        $extracss = $this->pushCSS([
            __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fancy_fileupload.css',
            __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/imageuploadify.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/colorpicker/colorpicker.css',

        ]);

        $pusjJs = $this->pushJS([
            $this->admin_webpath . '/js/jquery.tablednd.js',
            $this->admin_webpath . '/js/educations.js',
            $this->admin_webpath . '/js/attributes.js',
            $this->admin_webpath . '/js/educationprice.js',
            $this->admin_webpath . '/js/tiny_mce/tiny_mce.js',
            $this->admin_webpath . '/js/tinymce.inc.js',
            $this->admin_webpath . '/js/dnd.js',
            $this->admin_webpath . '/js/imageuploadify.min.js',
            $this->admin_webpath . '/js/pdfuploadify.min.js',
            $this->admin_webpath . '/js/colorpicker/colorpicker.js',

        ]);
        $scripHeader = Hook::exec('displayBackOfficeHeader', []);
        $scriptFooter = Hook::exec('displayBackOfficeFooter', []);

        $educationTabs = [];

        foreach ($this->available_tabs as $educationTab => $value) {
            $this->tab_display = $educationTab;
            $this->tpl_form = 'controllers/educations/' . strtolower($this->tab_display) . '.tpl';
            $educationTabs[$educationTab] = [
                'id'       => $educationTab,
                'selected' => (strtolower($educationTab) == strtolower($this->default_tab)),
                'name'     => $this->available_tabs_lang[$educationTab],
                'content'  => $this->renderForm(),
            ];
        }

        $data->assign([
            'scripHeader'                 => $scripHeader,
            'scriptFooter'                => $scriptFooter,
            'pusjJs'                      => $pusjJs,
            'extracss'                    => $extracss,
            'education_tabs'              => $educationTabs,
            'education'                   => $this->object,
            'formatpacks'                 => FormatPack::getFormatPack($this->context->language->id),
            'languages'                   => $this->_languages,
            'allowEmployeeFormLang'       => $this->allow_employee_form_lang,
            'id_education'                => $this->object->id,
            'id_lang_default'             => Configuration::get('PS_LANG_DEFAULT'),
            'has_declinaisons'            => $this->object->hasAttributes(),
            'post_data'                   => json_encode($_POST),
            'save_error'                  => !empty($this->errors),
            'mod_evasive'                 => Tools::apacheModExists('evasive'),
            'mod_security'                => Tools::apacheModExists('security'),
            'ps_force_friendly_education' => Configuration::get('PS_FORCE_FRIENDLY_PRODUCT'),
            'tinymce'                     => true,
            'iso'                         => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
            'path_css'                    => _THEME_CSS_DIR_,
            'ad'                          => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
            'education_type'              => (int) Tools::getValue('type_education', $this->object->getEducationType()),
            'controller'                  => $this->controller_name,
        ]);

        $li = '<li id="uperAdd' . $targetController . '" data-controller="AdminDashboard"><a href="#contentAdd' . $targetController . '">Ajouter une Formation</a><button type="button" class="close tabdetail" data-id="uperAdd' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
        $html = '<div id="contentAdd' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $data->fetch() . '</div>';

        $result = [
            'li'   => $li,

            'html' => $html,
        ];

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessEditObject() {

        $targetController = $this->targetController;
		if ($this->tabAccess['edit'] == 1) {   
        $idEducation = Tools::getValue('idObject');
        $this->object = new Education($idEducation);
        $data = $this->createTemplate('controllers/educations/editEducation.tpl');
        $this->identifier = 'id_education';
        $_GET['id_education'] = $idEducation;
        $_GET['updateeducation'] = 1;

        $extracss = $this->pushCSS([
            __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/fancy_fileupload.css',
            __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/imageuploadify.min.css',
            $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/colorpicker/colorpicker.css',

        ]);

        $pusjJs = $this->pushJS([
            $this->admin_webpath . '/js/jquery.tablednd.js',
            $this->admin_webpath . '/js/educations.js',
            $this->admin_webpath . '/js/attributes.js',
            $this->admin_webpath . '/js/educationprice.js',
            $this->admin_webpath . '/js/tiny_mce/tiny_mce.js',
            $this->admin_webpath . '/js/tinymce.inc.js',
            $this->admin_webpath . '/js/dnd.js',
            $this->admin_webpath . '/js/imageuploadify.min.js',
            $this->admin_webpath . '/js/pdfuploadify.min.js',
            $this->admin_webpath . '/js/codemirror/codemirror.js',

        ]);
        $scripHeader = Hook::exec('displayBackOfficeHeader', []);
        $scriptFooter = Hook::exec('displayBackOfficeFooter', []);

        $educationTabs = [];

        foreach ($this->available_tabs as $educationTab => $value) {
            $this->tab_display = $educationTab;
            $this->tpl_form = 'controllers/educations/' . strtolower($this->tab_display) . '.tpl';
            $educationTabs[$educationTab] = [
                'id'       => $educationTab,
                'selected' => (strtolower($educationTab) == strtolower($this->default_tab)),
                'name'     => $this->available_tabs_lang[$educationTab],
                'content'  => $this->renderForm(),
            ];
        }

        $data->assign([
            'scripHeader'                 => $scripHeader,
            'scriptFooter'                => $scriptFooter,
            'pusjJs'                      => $pusjJs,
            'formatpacks'                 => FormatPack::getFormatPack($this->context->language->id),
            'extracss'                    => $extracss,
            'education_tabs'              => $educationTabs,
            'education'                   => $this->object,
            'languages'                   => $this->_languages,
            'allowEmployeeFormLang'       => $this->allow_employee_form_lang,
            'id_education'                => $this->object->id,
            'id_lang_default'             => Configuration::get('PS_LANG_DEFAULT'),
            'has_declinaisons'            => $this->object->hasAttributes(),
            'post_data'                   => json_encode($_POST),
            'save_error'                  => !empty($this->errors),
            'mod_evasive'                 => Tools::apacheModExists('evasive'),
            'mod_security'                => Tools::apacheModExists('security'),
            'ps_force_friendly_education' => Configuration::get('PS_FORCE_FRIENDLY_PRODUCT'),
            'tinymce'                     => true,
            'iso'                         => file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en',
            'path_css'                    => _THEME_CSS_DIR_,
            'ad'                          => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
            'education_type'              => (int) Tools::getValue('type_education', $this->object->getEducationType()),
            'controller'                  => $this->controller_name,
        ]);

        $html = $data->fetch() . PHP_EOL;

        $li = '<li id="uperEdit' . $targetController . '" data-controller="AdminDashboard"><a href="#contentEdit' . $targetController . '">Visualiser ou modifier cette formation</a><button type="button" class="close tabdetail" data-id="uperEdit' . $targetController . '"><i class="icon icon-times-circle"></i></button></li>';
        $html = '<div id="contentEdit' . $targetController . '" class="panel col-lg-12" style="display; flow-root;">' . $scripHeader . $html . $scriptFooter . '</div>';

        $result = [
			'success' => true,
            'li'   => $li,
            'html' => $html,
        ];
		} else {
            $result = [
				'success' => false,
				'message'   => 'Votre profile administratif ne vous permet pas d‘éditer les Formations',
			];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessupdateVisibility() {

        $headerFields = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_EDUCATIONS_FIELDS'), true);
        $visibility = Tools::getValue('visibilities');

        foreach ($headerFields as $key => $headerField) {
            $hidden = '';

            foreach ($headerField as $field => $value) {

                if ($field == 'dataIndx') {

                    if ($visibility[$value] == 1) {
                        $hidden = false;
                    } else

                    if ($visibility[$value] == 0) {
                        $hidden = true;
                    }

                }

            }

            $headerField['hidden'] = $hidden;

            $headerFields[$key] = $headerField;
        }

        $headerFields = Tools::jsonEncode($headerFields);
        EmployeeConfiguration::updateValue('EXPERT_EDUCATIONS_FIELDS', $headerFields);
        die($headerFields);
    }

    public function ajaxProcessUpdateEducationPositions() {

        $positions = Tools::getValue('positions');
        $idObject = Tools::getvalue('idObject');
        $idCategory = Tools::getValue('idParent');
        $stopIndex = Tools::getValue('stopIndex');
        $this->movePosition($idCategory, $idObject, $stopIndex);

        foreach ($positions as $education => $position) {

            if (!$this->movePosition($idCategory, $education, $position)) {
                $this->errors[] = Tools::displayError('A problem occur with moving Education positions ');
            }

        }

        if (empty($this->errors)) {
            $this->educationRequest = $this->getEducationRequest();
            $result = [
                'success' => true,
                'message' => $this->l('Education position has been successfully updated.'),
            ];
        } else {
            $this->errors = array_unique($this->errors);
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];

        }

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessAddnewDeclinaison() {

        $id_education = Tools::getValue('id_education');
        $education = new Education($id_education);
        $id_attribute = Tools::getValue('attribute');

        if (!Tools::getIsset('attribute_declinaison_list') || Tools::isEmpty(Tools::getValue('attribute_combination_list'))) {
            $this->errors[] = Tools::displayError('You must add at least one attribute.');
        }

        if (Validate::isLoadedObject($education)) {

            if (($idEducationAttribute = (int) Tools::getValue('id_education_attribute')) || ($idEducationAttribute = $education->educationAttributeExists(Tools::getValue('attribute_declinaison_list'), false, null, true, true))) {

            } else {

                if ($education->educationAttributeExists(Tools::getValue('attribute_declinaison_list'))) {

                    $this->errors[] = Tools::displayError('This combination already exists.');
                } else {
                    fwrite($file, "Step 5" . PHP_EOL);
                    $declinaison = new Declinaison();
                    $declinaison->id_education = (int) $education->id;
                    $declinaison->reference = pSQL(Tools::getValue('attribute_reference'));
                    $declinaison->price = (float) Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact');
                    $declinaison->default_on = (int) Tools::getValue('attribute_default');
                    $declinaison->days = (int) Tools::getValue('attribute_days');
                    $declinaison->hours = (int) Tools::getValue('attribute_hours');
                    $declinaison->id_formatpack = (int) Tools::getValue('attribute_id_formatpack');
                    $declinaison->id_education_prerequis = (int) Tools::getValue('attribute_id_education_prerequis');
                    $declinaison->wholesale_price = (float) Tools::getValue('attribute_wholesale_price');
                    $declinaison->versionProgram = Tools::getValue('attribute_versionProgram');

                    foreach (Language::getIDs(false) as $idLang) {
                        $declinaison->name[$idLang] = Tools::getValue('attribute_name_' . $idLang);
                        $declinaison->description[$idLang] = Tools::getValue('attribute_description_' . $idLang);
                        $declinaison->description_short[$idLang] = Tools::getValue('attribute_description_short_' . $idLang);
                        $declinaison->programme[$idLang] = Tools::getValue('attribute_programme_' . $idLang);
                    }

                    $result = $declinaison->add();

                    if (!isset($result) || !$result) {
                        $this->errors[] = Tools::displayError('An error occurred while adding declinaison.');
                    } else {
                        $link = new EducationLink();
                        $link->id_education = $declinaison->id_education;
                        $link->id_education_attribute = $declinaison->id;
                        $link->edof_link = pSQL(Tools::getValue('attribute_edof_link'));
                        $link->add();
                        $idImages = Tools::getValue('id_image_attr');

                        if (is_array($idImages) && count($idImages)) {
                            $declinaison->setImages($idImages);
                        }

                        $result = Db::getInstance()->execute('
                                INSERT INTO `' . _DB_PREFIX_ . 'education_attribute_combination` (`id_attribute`, `id_education_attribute`) VALUES (' . $id_attribute . ', ' . $declinaison->id . ')');
                    }

                }

            }

        }

        $this->errors = array_unique($this->errors);

        if (!empty($this->errors)) {
            $result = [
                'success' => false,
                'message' => $this->error,
            ];
        } else {
            $result = [
                'success'     => true,
                'idEducation' => $education->id,
                'message'     => $this->l('Declinaisons has been added with success'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessUpdateDeclinaison() {

        $id_education = Tools::getValue('id_education');
        $id_education_attribute = Tools::getValue('id_education_attribute');
        $education = new Education($id_education);
        $declinaison = new Declinaison($id_education_attribute);

        if (Validate::isLoadedObject($education) && Validate::isLoadedObject($declinaison)) {

            if (!Tools::getIsset('attribute_declinaison_list') || Tools::isEmpty(Tools::getValue('attribute_combination_list'))) {
                $this->errors[] = Tools::displayError('You must add at least one attribute.');
            }

            if ($declinaison->id_education != $id_education) {

                $this->errors[] = Tools::displayError('a essential parameter is going wrong.');

            } else {
                $default_status = $declinaison->default_on;

                $new_status = (int) Tools::getValue('attribute_default');

                $defaultAttribute = $education->getDefaultIdEducationAttribute();

                if ($declinaison->id == $defaultAttribute) {

                    if ($new_status == 0) {
                        $new_status = 1;
                    }

                } else

                if ($new_status == 1) {
                    Db::getInstance()->execute(
                        'UPDATE `' . _DB_PREFIX_ . 'education_attribute`
                        SET `default_on` = 0
                        WHERE `id_education_attribute` = ' . (int) $defaultAttribute
                    );
                }

                $declinaison->reference = pSQL(Tools::getValue('attribute_reference'));
                $declinaison->price = (float) Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact');
                $declinaison->default_on = $new_status;
                $declinaison->days = (int) Tools::getValue('attribute_days');
                $declinaison->hours = (int) Tools::getValue('attribute_hours');
                $declinaison->id_formatpack = (int) Tools::getValue('attribute_id_formatpack');
                $declinaison->id_education_prerequis = (int) Tools::getValue('attribute_id_education_prerequis');
                $declinaison->wholesale_price = (float) Tools::getValue('attribute_wholesale_price');
                $declinaison->link = Tools::getValue('attribute_link');
                $declinaison->education_link = Tools::getValue('attribute_education_link');
                $declinaison->id_plateforme = Tools::getValue('attribute_id_plateforme');
                $declinaison->versionProgram = Tools::getValue('attribute_versionProgram');
                $declinaison->is_combo = Tools::getValue('is_combo');
                $parents = Tools::getValue('attribute_parents');

                if (is_array($parents) && count($parents)) {
                    $declinaison->parents = Tools::jsonEncode($parents);
                }

                foreach (Language::getIDs(false) as $idLang) {
                    $declinaison->name[$idLang] = Tools::getValue('attribute_name_' . $idLang);
                    $declinaison->description[$idLang] = Tools::getValue('attribute_description_' . $idLang);
                    $declinaison->description_short[$idLang] = Tools::getValue('attribute_description_short_' . $idLang);
                    $declinaison->programme[$idLang] = Tools::getValue('attribute_programme_' . $idLang);
                }

                $result = $declinaison->update();

                if (!isset($result) || !$result) {
                    $this->errors[] = Tools::displayError('An error occurred while updating declinaison.');
                } else {

                    if ($declinaison->id_education_link > 0) {
                        $link = new EducationLink($declinaison->id_education_link);
                    } else {
                        $link = new EducationLink();
                    }

                    $link = new EducationLink($declinaison->id_education_link);
                    $link->id_education = $declinaison->id_education;
                    $link->id_education_attribute = $declinaison->id;
                    $link->edof_link = pSQL(Tools::getValue('attribute_edof_link'));

                    if ($declinaison->id_education_link > 0) {
                        $link->update();
                    } else {
                        $link->add();
                    }

                    $idImages = Tools::getValue('id_image_attr');

                    if (is_array($idImages) && count($idImages)) {
                        $declinaison->setImages($idImages);
                    }

                    Education::updateDefaultAttribute((int) $education->id);
                    $result = [
                        'success'     => true,
                        'idEducation' => $education->id,
                        'message'     => $this->l('Declinaisons has been updated with success'),
                    ];
                    die(Tools::jsonEncode($result));
                }

            }

        } else {
            $this->errors[] = Tools::displayError('An error occurred loading the object.');
        }

        $this->errors = array_unique($this->errors);

        if (!empty($this->errors)) {
            $result = [
                'success' => false,
                'message' => $this->error,
            ];
        }

        die(Tools::jsonEncode($result));

    }

    public function buildDeclinaisonScript($idEducation) {

        $className = 'Declinaison';
        $table = 'education_attribute';
        $controller_name = "AdminDeclinaisons";
        $identifier = 'id_education_attribute';

        $paragrid = new ParamGrid($className, $this->controller_name, $table, $identifier);
        $paragrid->paramTable = $table;
        $paragrid->paramController = $this->controller_name;
        $paragrid->requestModel = '{
            location: "remote",
            dataType: "json",
            method: "GET",
            recIndx: "id_education_attribute",
            url: AjaxLinkAdminEducations+"&action=getDeclinaisonRequest&id_education=' . $idEducation . '&ajax=1",
            getData: function (dataJSON) {
                return { data: dataJSON };
                }


        }';
        $paragrid->height = '700';

        //$paragrid->ajaxUrl = 'AjaxLinkAdminEducations + "&action=getProductRequest&ajax=1&idCategory="+getURLParameter(\'idCategory\')';
        $paragrid->showNumberCell = 0;
        $paragrid->pageModel = [
            'type'       => '\'local\'',
            'rPP'        => 40,
            'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
        ];

        $paragrid->rowInit = 'function (ui) {
            return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $controller_name . '+\'" data-class="' . $className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $identifier . '+\' "\',
            };
        }';

        $paragrid->selectionModelType = 'row';

        $paragrid->showTitle = 1;
        $paragrid->title = '\'Declinaisons\'';
        $paragrid->fillHandle = '\'all\'';
        $paragrid->contextMenu = [
            '#grid_' . $controller_name => [
                'selector'  => '\'.pq-body-outer .pq-grid-row\'',
                'animation' => [
                    'duration' => 250,
                    'show'     => '\'fadeIn\'',
                    'hide'     => '\'fadeOut\'',
                ],
                'build'     => 'function($triggerElement, e){

                var rowIndex = $($triggerElement).attr("data-rowIndx");
                var rowData = ' . 'grid' . $className . '.getRowData( {rowIndx: rowIndex} );
                selected = selgrid' . $className . '.getSelection().length;
                var dataLenght = grid' . $className . '.option(\'dataModel.data\').length;
                return {
                    callback: function(){},
                    items: {
                        "add": {
                            name: \'' . $this->l('Ajouter une nouvelle déclinaison') . ' \',
                            icon: "add",
                            callback: function(itemKey, opt, e) {
                                var datalink = rowData.addLink;
                                openAjaxProductLink(datalink);
                            }
                        },
                        "edit": {
                            name : \'' . $this->l('Visualiser ou modifier la déclinaison: ') . '\'' . '+rowData.reference,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                var datalink = rowData.openLink;
                                console.log(datalink);
                                openAjaxProductLink(datalink);
                            }
                        },
                        "trash": {
                            name : \'' . $this->l('Supprimer la déclinaison: ') . '\'' . '+rowData.reference,
                            icon: "edit",
                            callback: function(itemKey, opt, e) {
                                var datalink = rowData.deleteLink;
                                console.log(datalink);
                                openAjaxProductLink(datalink);
                            }
                        },


                    },
                };
            }',
            ]];

        $option = $paragrid->generateParaGridOption();
        return $paragrid->generateParagridScript();
    }

    public function ajaxProcessCleanPositions() {

        $idCategory = Tools::getValue('idCategory');
        $educations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_education`, `position`')
                ->from('category_education')
                ->where('`id_category` = ' . (int) $idCategory)
                ->orderBy('`position` ASC'));

        foreach ($educations as $education) {

            if (Education::existInDatabase($education['id_education'])) {
                continue;
            } else {
                Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'category_education` WHERE `id_education` =' . (int) $education['id_education']);
            }

        }

        $educations = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_education`, `position`')
                ->from('category_education')
                ->where('`id_category` = ' . (int) $idCategory)
                ->orderBy('`position` ASC'));
        $k = 1;

        foreach ($educations as $education) {

            if (!$this->movePosition($idCategory, $education['id_education'], $k)) {
                $this->errors[] = Tools::displayError('A problem occur with cleaning Education positions ');
            } else {
                $k++;
            }

        }

        if (empty($this->errors)) {
            $this->educationRequest = $this->getEducationRequest();
            $result = [
                'success' => true,
                'message' => $this->l('Education position has been successfully updated.'),
            ];
        } else {
            $this->errors = array_unique($this->errors);
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];

        }

        die(Tools::jsonEncode($result));

    }

    public function movePosition($idCategory, $idEducation, $k) {

        $result = Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'category_education`
            SET `position`= ' . $k . '
            WHERE `id_category` =' . (int) $idCategory . ' AND `id_education` = ' . $idEducation);

        if (!$result) {
            return false;
        }

        return true;

    }

    public function ajaxProcessGetCountriesOptions() {

        if (!$res = Country::getCountriesByIdShop((int) Tools::getValue('id_shop'), (int) $this->context->language->id)) {
            return;
        }

        $tpl = $this->createTemplate('specific_prices_shop_update.tpl');
        $tpl->assign(
            [
                'option_list' => $res,
                'key_id'      => 'id_country',
                'key_value'   => 'name',
            ]
        );

        $this->content = $tpl->fetch();
    }

    public function ajaxProcessGetCurrenciesOptions() {

        if (!$res = Currency::getCurrenciesByIdShop((int) Tools::getValue('id_shop'))) {
            return;
        }

        $tpl = $this->createTemplate('specific_prices_shop_update.tpl');
        $tpl->assign(
            [
                'option_list' => $res,
                'key_id'      => 'id_currency',
                'key_value'   => 'name',
            ]
        );

        $this->content = $tpl->fetch();
    }

    public function ajaxProcessGetGroupsOptions() {

        if (!$res = Group::getGroups((int) $this->context->language->id, (int) Tools::getValue('id_shop'))) {
            return;
        }

        $tpl = $this->createTemplate('specific_prices_shop_update.tpl');
        $tpl->assign(
            [
                'option_list' => $res,
                'key_id'      => 'id_group',
                'key_value'   => 'name',
            ]
        );

        $this->content = $tpl->fetch();
    }

    public function processDuplicate() {

        if (Validate::isLoadedObject($education = new Education((int) Tools::getValue('id_education')))) {
            $idEducationOld = $education->id;

            if (empty($education->price) && Shop::getContext() == Shop::CONTEXT_GROUP) {
                $shops = ShopGroup::getShopsFromGroup(Shop::getContextShopGroupID());

                foreach ($shops as $shop) {

                    if ($education->isAssociatedToShop($shop['id_shop'])) {
                        $education_price = new Education($idEducationOld, false, null, $shop['id_shop']);
                        $education->price = $education_price->price;
                    }

                }

            }

            unset($education->id);
            unset($education->id_education);
            $education->indexed = 0;
            $education->active = 0;

            if ($education->add()
                && ($combinationImages = Education::duplicateAttributes($idEducationOld, $education->id)) !== false
                && GroupReduction::duplicateReduction($idEducationOld, $education->id)
                && Education::duplicateAccessories($idEducationOld, $education->id)
                && Education::duplicateSpecificPrices($idEducationOld, $education->id)
                && EducationPack::duplicate($idEducationOld, $education->id)
                && Education::duplicateTags($idEducationOld, $education->id)
                && Education::duplicateDownload($idEducationOld, $education->id)

            ) {

                if ($education->hasAttributes()) {
                    Education::updateDefaultAttribute($education->id);
                } else {
                    // Set stock quantity
                    $quantityAttributeOld = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('`quantity`')
                            ->from('stock_available')
                            ->where('`id_education` = ' . (int) $idEducationOld)
                            ->where('`id_education_attribute` = 0')
                    );
                    StockAvailable::setQuantity((int) $education->id, 0, (int) $quantityAttributeOld);
                }

                if (!Tools::getValue('noimage') && !ImageEducation::duplicateEducationImages($idEducationOld, $education->id, $combinationImages)) {
                    $this->errors[] = Tools::displayError('An error occurred while copying images.');
                } else {
                    Hook::exec('actionEducationAdd', ['id_education' => (int) $education->id, 'education' => $education]);

                    if (in_array($education->visibility, ['both', 'search']) && Configuration::get('PS_SEARCH_INDEXATION')) {
                        Search::indexation(false, $education->id);
                    }

                    $this->redirect_after = static::$currentIndex . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&conf=19&token=' . $this->token;
                }

            } else {
                $this->errors[] = Tools::displayError('An error occurred while creating an object.');
            }

        }

    }

    public function processDelete() {

        if (Validate::isLoadedObject($object = $this->loadObject()) && isset($this->fieldImageSettings)) {
            /** @var Education $object */
            // check if request at least one object with noZeroObject

            if (isset($object->noZeroObject) && count($taxes = call_user_func([$this->className, $object->noZeroObject])) <= 1) {
                $this->errors[] = Tools::displayError('You need at least one object.') . ' <b>' . $this->table . '</b><br />' . Tools::displayError('You cannot delete all of the items.');
            } else {
                /*
                                                                             * @since 1.5.0
                                                                             * It is NOT possible to delete a education if there is/are currently:
                                                                             * - a physical stock for this education
                                                                             * - supply order(s) for this education
                */

                if (!count($this->errors)) {

                    if ($object->delete()) {
                        $idCategory = (int) Tools::getValue('id_category');
                        $categoryUrl = empty($idCategory) ? '' : '&id_category=' . (int) $idCategory;
                        Logger::addLog(sprintf($this->l('%s deletion', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $object->id, true, (int) $this->context->employee->id);
                        $this->redirect_after = static::$currentIndex . '&conf=1&token=' . $this->token . $categoryUrl;
                    } else {
                        $this->errors[] = Tools::displayError('An error occurred during deletion.');
                    }

                }

            }

        } else {
            $this->errors[] = Tools::displayError('An error occurred while deleting the object.') . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
        }

    }

    protected function loadObject($opt = false) {

        $result = parent::loadObject($opt);

        if ($result && Validate::isLoadedObject($this->object)) {

            if (Shop::getContext() == Shop::CONTEXT_SHOP && Shop::isFeatureActive()) {
                $defaultEducation = new Education((int) $this->object->id, false, null, (int) $this->object->id_shop_default);
                $def = ObjectModel::getDefinition($this->object);

                foreach ($def['fields'] as $field_name => $row) {

                    if (is_array($defaultEducation->$field_name)) {

                        foreach ($defaultEducation->$field_name as $key => $value) {
                            $this->object->{$field_name}

                            [$key] = $value;
                        }

                    } else {
                        $this->object->$field_name = $defaultEducation->$field_name;
                    }

                }

            }

        }

        return $result;
    }

    public function processImage() {

        $idImage = (int) Tools::getValue('id_image');
        $image = new ImageEducation((int) $idImage);

        if (Validate::isLoadedObject($image)) {
            /* Update education image/legend */
            // @todo : move in processEditEducationImage

            if (Tools::getIsset('editImage')) {

                if ($image->cover) {
                    $_POST['cover'] = 1;
                }

                $_POST['id_image'] = $image->id;
            } else

            if (Tools::getIsset('coverImage')) {
                /* Choose education cover image */
                ImageEducation::deleteCover($image->id_education);
                $image->cover = 1;

                if (!$image->update()) {
                    $this->errors[] = Tools::displayError('You cannot change the education\'s cover image.');
                } else {
                    $educationId = (int) Tools::getValue('id_education');
                    @unlink(_PS_TMP_IMG_DIR_ . 'education_' . $educationId . '.jpg');
                    @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . $educationId . '_' . $this->context->shop->id . '.jpg');
                    $this->redirect_after = static::$currentIndex . '&id_education=' . $image->id_education . '&id_category=' . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&action=Images&addeducation' . '&token=' . $this->token;
                }

            } else

            if (Tools::getIsset('imgPosition') && Tools::getIsset('imgDirection')) {
                /* Choose education image position */
                $image->updatePosition(Tools::getValue('imgDirection'), Tools::getValue('imgPosition'));
                $this->redirect_after = static::$currentIndex . '&id_education=' . $image->id_education . '&id_category=' . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&add' . $this->table . '&action=Images&token=' . $this->token;
            }

        } else {
            $this->errors[] = Tools::displayError('The image could not be found. ');
        }

    }

    public function processPosition() {

        /** @var Education $object */

        if (!Validate::isLoadedObject($object = $this->loadObject())) {
            $this->errors[] = Tools::displayError('An error occurred while updating the status for an object.') . ' <b>' . $this->table . '</b> ' . Tools::displayError('(cannot load object)');
        } else

        if (!$object->updatePosition((int) Tools::getValue('way'), (int) Tools::getValue('position'))) {
            $this->errors[] = Tools::displayError('Failed to update the position.');
        } else {
            $category = new Category((int) Tools::getValue('id_category'));

            if (Validate::isLoadedObject($category)) {
                Hook::exec('actionCategoryUpdate', ['category' => $category]);
            }

            $this->redirect_after = static::$currentIndex . '&' . $this->table . 'Orderby=position&' . $this->table . 'Orderway=asc&action=Customization&conf=5' . (($idCategory = (Tools::getIsset('id_category') ? (int) Tools::getValue('id_category') : '')) ? ('&id_category=' . $idCategory) : '') . '&token=' . Tools::getAdminTokenLite('AdminEducations');
        }

    }

    protected function isTabSubmitted($tabName) {

        if (!is_array($this->submitted_tabs)) {
            $this->submitted_tabs = Tools::getValue('submitted_tabs');
        }

        if (is_array($this->submitted_tabs) && in_array($tabName, $this->submitted_tabs)) {
            return true;
        }

        return false;
    }

    public function postProcess() {

        if (!$this->redirect_after) {
            parent::postProcess();
        }

        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/select2/select2_locale_' . $this->context->language->iso_code . '.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js');

        $this->addCSS(
            [
                __PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/timepicker/jquery-ui-timepicker-addon.css',
            ]
        );

    }

    public function ajaxProcessDefaultEducationAttribute() {

        if ($this->tabAccess['edit'] === '1') {

            if (Validate::isLoadedObject($education = new Education((int) Tools::getValue('id_education')))) {
                $education->deleteDefaultAttributes();
                $education->setDefaultAttribute((int) Tools::getValue('id_education_attribute'));
                $json = [
                    'status'  => 'ok',
                    'message' => $this->_conf[4],
                ];
            } else {
                $json = [
                    'status'  => 'error',
                    'message' => $this->l('You cannot make this the default attribute.'),
                ];
            }

            $this->ajaxDie(json_encode($json));
        }

    }

    public function ajaxPreProcess() {

        if (Tools::getIsset('update' . $this->table) && Tools::getIsset('id_' . $this->table)) {
            $this->display = 'edit';
            $this->action = Tools::getValue('action');
        }

    }

    public function ajaxProcessGetUpdateForm() {

        $dataIndx = Tools::getValue('dataIndx');

        switch ($dataIndx) {
        case 'brand':
            $select = $this->brandSelector;
            break;
        case 'familyName':
            $select = $this->familySelector;
            break;
        case 'Tax':
            $select = $this->taxSelector;
            break;
        case 'CategoryName':
            $select = $this->buildCategoriesSelector();
            break;
        case 'active':
            $select = $this->activeSelector;
            break;
        default:
            $select = '';
        }

        $field = $this->getUpdatableFieldType($dataIndx);

        $fieldType = $field['dataForm'];
        $data = $this->createTemplate('fieldUpdate.tpl');
        $currency = new Currency($this->context->currency->id);
        $this->context->smarty->assign([
            'selector'  => $select,
            'fieldType' => $field['dataForm'],
            'title'     => $field['titleForm'],
            'field'     => $field['title'],
            'dataIndx'  => $dataIndx,
            'currency'  => $currency,
        ]);

        $return = [
            'tpl' => $data->fetch(),
        ];

        die(Tools::jsonEncode($return));

    }

    public function ajaxProcessBulkUpdateEducation() {

        $fieldType = Tools::getValue('fieldType');
        $dataIndx = Tools::getValue('dataIndx');
        $category = Tools::getValue('category');
        $allCategory = Tools::getValue('allCategory');

        if ($allCategory == 1) {
            $request = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('SELECT `id_education` FROM `' . _DB_PREFIX_ . 'category_education` WHERE `id_category` = ' . (int) $category);
            $educations = [];

            foreach ($request as $result) {
                $educations[] = $result['id_education'];
            }

        } else {
            $educations = explode(",", Tools::getValue('educations')[0]);
        }

        if ($fieldType == 'select') {
            $selectValue = Tools::getValue('selectValue');

            switch ($dataIndx) {
            case 'brand':
                $dataIndx = 'id_manufacturer';
                break;
            case 'familyName':
                $dataIndx = 'id_education_family';
                break;
            case 'Tax':
                $dataIndx = 'id_tax_rules_group';
                break;
            case 'CategoryName':
                $dataIndx = 'id_category_default';
                break;
            case 'active':
                break;

            }

            foreach ($educations as $key => $education) {
                $updateEducation = new Education($education);

                if (Validate::isLoadedObject($updateEducation)) {
                    $updateEducation->$dataIndx = $selectValue;

                    if (!$updateEducation->update()) {
                        $this->errors[] = Tools::displayError('An error happen updating education.');
                    }

                } else {
                    $this->errors[] = Tools::displayError('An error happen to load the education.');
                }

            }

        } else {
            $updateBehavior = Tools::getValue('updateBehavior');
            $updateType = Tools::getValue('updateType');
            $amount = Tools::getValue('amount');

            if ($updateType == 'percent') {

                if ($updateBehavior == 'increase') {
                    $coef = 1 + $amount / 100;
                } else

                if ($updateBehavior == 'decrease') {
                    $coef = 1 - $amount / 100;
                }

                foreach ($educations as $key => $education) {
                    $updateEducation = new Education($education);

                    if (Validate::isLoadedObject($updateEducation)) {
                        $updateEducation->$dataIndx = $updateEducation->$dataIndx * $coef;

                        if (!$updateEducation->update()) {
                            $this->errors[] = Tools::displayError('An error happen updating education.');
                        }

                    } else {
                        $this->errors[] = Tools::displayError('An error happen to load the education.');
                    }

                }

            } else {

                if ($updateBehavior == 'decrease') {
                    $amount = -$amount;
                }

                foreach ($educations as $key => $education) {
                    $updateEducation = new Education($education);

                    if (Validate::isLoadedObject($updateEducation)) {
                        $updateEducation->$dataIndx = $updateEducation->$dataIndx + $amount;

                        if (!$updateEducation->update()) {
                            $this->errors[] = Tools::displayError('An error happen updating education.');
                        }

                    } else {
                        $this->errors[] = Tools::displayError('An error happen to load the education.');

                    }

                }

            }

        }

        if (count($this->errors)) {
            $result = [
                'success' => false,
                'message' => implode(PHP_EOL, $this->errors),
            ];
        } else {
            $result = [
                'success' => true,
                'message' => $this->l('The selection has been successfully updated'),
            ];
        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessUpdateEducationImageShopAsso() {

        $idEducation = Tools::getValue('id_education');

        if (($idImage = Tools::getValue('id_image')) && ($idShop = (int) Tools::getValue('id_shop'))) {

            if (Tools::getValue('active') == 'true') {
                $res = Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'image_shop (`id_education`, `id_image`, `id_shop`, `cover`) VALUES(' . (int) $idEducation . ', ' . (int) $idImage . ', ' . (int) $idShop . ', NULL)');
            } else {
                $res = Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'image_shop WHERE `id_image` = ' . (int) $idImage . ' AND `id_shop` = ' . (int) $idShop);
            }

        }

        // Clean covers in image table

        if (isset($idShop)) {
            $countCoverImage = Db::getInstance()->getValue(
                '
            SELECT COUNT(*) FROM ' . _DB_PREFIX_ . 'image i
            INNER JOIN ' . _DB_PREFIX_ . 'image_shop ish ON (i.id_image = ish.id_image AND ish.id_shop = ' . (int) $idShop . ')
            WHERE i.cover = 1 AND i.`id_education` = ' . (int) $idEducation
            );

            if (!$idImage) {
                $idImage = Db::getInstance()->getValue(
                    '
                    SELECT i.`id_image` FROM ' . _DB_PREFIX_ . 'image i
                    INNER JOIN ' . _DB_PREFIX_ . 'image_shop ish ON (i.id_image = ish.id_image AND ish.id_shop = ' . (int) $idShop . ')
                    WHERE i.`id_education` = ' . (int) $idEducation
                );
            }

            if ($countCoverImage < 1) {
                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'image i SET i.cover = 1 WHERE i.id_image = ' . (int) $idImage . ' AND i.`id_education` = ' . (int) $idEducation . ' LIMIT 1');
            }

            // Clean covers in image_shop table
            $countCoverImageShop = Db::getInstance()->getValue(
                '
                SELECT COUNT(*)
                FROM ' . _DB_PREFIX_ . 'image_shop ish
                WHERE ish.`id_education` = ' . (int) $idEducation . ' AND ish.id_shop = ' . (int) $idShop . ' AND ish.cover = 1'
            );

            if ($countCoverImageShop < 1) {
                Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'image_shop ish SET ish.cover = 1 WHERE ish.id_image = ' . (int) $idImage . ' AND ish.`id_education` = ' . (int) $idEducation . ' AND ish.id_shop =  ' . (int) $idShop . ' LIMIT 1');
            }

        }

        if (isset($res) && $res) {
            $this->jsonConfirmation($this->_conf[27]);
        } else {
            $this->jsonError(Tools::displayError('An error occurred while attempting to associate this image with your shop. '));
        }

    }

    public function ajaxProcessUpdateImagePosition() {

        if ($this->tabAccess['edit'] === '0') {
            $this->ajaxDie(json_encode(['error' => $this->l('You do not have the right permission')]));
        }

        $res = false;

        if ($json = Tools::getValue('json')) {
            // If there is an exception, at least the response is in JSON format.
            $this->json = true;

            $res = true;
            $json = stripslashes($json);
            $images = json_decode($json, true);

            foreach ($images as $id => $position) {
                /*
                                                                             * If the the image is not associated with the currently
                                                                             * selected shop, the fields that are also in the image_shop
                                                                             * table (like id_education and cover) cannot be loaded properly,
                                                                             * so we have to load them separately.
                */
                $img = new ImageEducation((int) $id);
                $def = $img::$definition;
                $sql = 'SELECT * FROM `' . _DB_PREFIX_ . $def['table'] . '` WHERE `' . $def['primary'] . '` = ' . (int) $id;
                $fields_from_table = Db::getInstance()->getRow($sql);

                foreach ($def['fields'] as $key => $value) {

                    if (!$value['lang']) {
                        $img->{$key}

                        = $fields_from_table[$key];
                    }

                }

                $img->position = (int) $position;
                $res &= $img->update();
            }

        }

        if ($res) {
            $this->jsonConfirmation($this->_conf[25]);
        } else {
            $this->jsonError(Tools::displayError('An error occurred while attempting to move this picture.'));
        }

    }

    public function ajaxProcessUpdateCover() {

        if ($this->tabAccess['edit'] === '0') {
            $this->ajaxDie(json_encode(['error' => $this->l('You do not have the right permission')]));
        }

        ImageEducation::deleteCover((int) Tools::getValue('id_education'));
        $id_image = (int) Tools::getValue('id_image');

        /*
                                             * If the the image is not associated with the currently selected shop,
                                             * the fields that are also in the image_shop table (like id_education and
                                             * cover) cannot be loaded properly, so we have to load them separately.
        */
        $img = new ImageEducation($id_image);
        $def = $img::$definition;
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . $def['table'] . '` WHERE `' . $def['primary'] . '` = ' . $id_image;
        $fields_from_table = Db::getInstance()->getRow($sql);

        foreach ($def['fields'] as $key => $value) {

            if (!$value['lang']) {
                $img->{$key}

                = $fields_from_table[$key];
            }

        }

        $img->cover = 1;

        @unlink(_PS_TMP_IMG_DIR_ . 'education_' . (int) $img->id_education . '.jpg');
        @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . (int) $img->id_education . '_' . $this->context->shop->id . '.jpg');

        if ($img->update()) {
            $this->jsonConfirmation($this->_conf[26]);
        } else {
            $this->jsonError(Tools::displayError('An error occurred while attempting to update the cover picture.'));
        }

    }

    public function ajaxProcessDeleteEducationImage() {

        $this->display = 'content';
        $res = true;
        /* Delete education image */
        $image = new ImageEducation((int) Tools::getValue('id_image'));
        $this->content['id'] = $image->id;
        $res &= $image->delete();
        // if deleted image was the cover, change it to the first one

        if (!ImageEducation::getGlobalCover($image->id_education)) {
            $res &= Db::getInstance()->execute(
                '
            UPDATE `' . _DB_PREFIX_ . 'image_education` i
            SET i.`cover` = 1
            WHERE i.`id_education` = ' . (int) $image->id_education . ' LIMIT 1'
            );
        }

        if (file_exists(_PS_TMP_IMG_DIR_ . 'education_' . $image->id_education . '.jpg')) {
            $res &= @unlink(_PS_TMP_IMG_DIR_ . 'education_' . $image->id_education . '.jpg');
        }

        if (file_exists(_PS_TMP_IMG_DIR_ . 'education_mini_' . $image->id_education . '_' . $this->context->shop->id . '.jpg')) {
            $res &= @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . $image->id_education . '_' . $this->context->shop->id . '.jpg');
        }

        if ($res) {
            $this->jsonConfirmation($this->_conf[7]);
        } else {
            $this->jsonError(Tools::displayError('An error occurred while attempting to delete the education image.'));
        }

    }

    public function addEducationImage($education, $method = 'auto') {

        /* Updating an existing education image */

        if ($idImage = (int) Tools::getValue('id_image')) {
            $image = new ImageEducation((int) $idImage);

            if (!Validate::isLoadedObject($image)) {
                $this->errors[] = Tools::displayError('An error occurred while loading the object image.');
            } else {

                if (($cover = Tools::getValue('cover')) == 1) {
                    ImageEducation::deleteCover($education->id);
                }

                $image->cover = $cover;
                $this->validateRules('Image');
                $this->copyFromPost($image, 'image');

                if (count($this->errors) || !$image->update()) {
                    $this->errors[] = Tools::displayError('An error occurred while updating the image.');
                } else

                if (isset($_FILES['image_education']['tmp_name']) && $_FILES['image_education']['tmp_name'] != null) {
                    $this->copyImage($education->id, $image->id, $method);
                }

            }

        }

        if (isset($image) && Validate::isLoadedObject($image) && !file_exists(_PS_EDUC_IMG_DIR_ . $image->getExistingImgPath() . '.' . $image->image_format)) {
            $image->delete();
        }

        if (count($this->errors)) {
            return false;
        }

        @unlink(_PS_TMP_IMG_DIR_ . 'education_' . $education->id . '.jpg');
        @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . $education->id . '_' . $this->context->shop->id . '.jpg');

        return ((isset($idImage) && is_int($idImage) && $idImage) ? $idImage : false);
    }

    protected function copyFromPost(&$object, $table) {

        parent::copyFromPost($object, $table);

        if (get_class($object) != 'Education') {
            return;
        }

        /* Additional fields */

        foreach (Language::getIDs(false) as $idLang) {

            if (isset($_POST['meta_keywords_' . $idLang])) {
                $_POST['meta_keywords_' . $idLang] = $this->_cleanMetaKeywords(mb_strtolower($_POST['meta_keywords_' . $idLang]));
                // preg_replace('/ *,? +,* /', ',', strtolower($_POST['meta_keywords_'.$id_lang]));
                $object->meta_keywords[$idLang] = $_POST['meta_keywords_' . $idLang];
            }

        }

    }

    protected function _cleanMetaKeywords($keywords) {

        if (!empty($keywords) && $keywords != '') {
            $out = [];
            $words = explode(',', $keywords);

            foreach ($words as $wordItem) {
                $wordItem = trim($wordItem);

                if (!empty($wordItem) && $wordItem != '') {
                    $out[] = $wordItem;
                }

            }

            return ((count($out) > 0) ? implode(',', $out) : '');
        } else {
            return '';
        }

    }

    public function copyImage($idEducation, $idImage, $method = 'auto') {

        if (!isset($_FILES['image_education']['tmp_name'])) {
            return false;
        }

        if ($error = ImageManager::validateUpload($_FILES['image_education'])) {
            $this->errors[] = $error;
        } else {
            $highDpi = (bool) Configuration::get('PS_HIGHT_DPI');

            $image = new ImageEducation($idImage);

            if (!$newPath = $image->getPathForCreation()) {
                $this->errors[] = Tools::displayError('An error occurred while attempting to create a new folder.');
            }

            if (!($tmpName = tempnam(_PS_TMP_IMG_DIR_, 'PS')) || !move_uploaded_file($_FILES['image_education']['tmp_name'], $tmpName)) {
                $this->errors[] = Tools::displayError('An error occurred during the image upload process.');
            } else

            if (!ImageManager::resize($tmpName, $newPath . '.' . $image->image_format)) {
                $this->errors[] = Tools::displayError('An error occurred while copying the image.');
            } else

            if ($method == 'auto') {
                $imagesTypes = ImageType::getImagesTypes('educations');

                foreach ($imagesTypes as $k => $imageType) {

                    if (!ImageManager::resize(
                        $tmpName,
                        $newPath . '-' . stripslashes($imageType['name']) . '.' . $image->image_format,
                        (int) $imageType['width'],
                        (int) $imageType['height'],
                        $image->image_format
                    )) {
                        $this->errors[] = Tools::displayError('An error occurred while copying this image:') . ' ' . stripslashes($imageType['name']);
                    } else {

                        if ($highDpi) {
                            ImageManager::resize(
                                $tmpName,
                                $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format,
                                (int) $imageType['width'] * 2,
                                (int) $imageType['height'] * 2,
                                $image->image_format
                            );
                        }

                        if (ImageManager::webpSupport()) {
                            ImageManager::resize(
                                $tmpName,
                                $newPath . '-' . stripslashes($imageType['name']) . '.webp',
                                (int) $imageType['width'],
                                (int) $imageType['height'],
                                'webp'
                            );

                            if ($highDpi) {
                                ImageManager::resize(
                                    $tmpName,
                                    $newPath . '-' . stripslashes($imageType['name']) . '2x.webp',
                                    (int) $imageType['width'] * 2,
                                    (int) $imageType['height'] * 2,
                                    'webp'
                                );
                            }

                        }

                        if ((int) Configuration::get('EPH_IMAGES_LAST_UPD_EDUCATIONS') < $idEducation) {
                            Configuration::updateValue('EPH_IMAGES_LAST_UPD_EDUCATIONS', $idEducation);
                        }

                    }

                }

            }

            @unlink($tmpName);
            Hook::exec('actionWatermark', ['id_image' => $idImage, 'id_education' => $idEducation]);
        }

    }

    public function processAdd() {

        $this->checkEducation();

        if (!empty($this->errors)) {
            $this->display = 'add';

            return false;
        }

        $this->object = new $this->className();

        $this->copyFromPost($this->object, $this->table);

        if ($this->object->add()) {
            Logger::addLog(sprintf($this->l('%s addition', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $this->object->id, true, (int) $this->context->employee->id);

            $this->updatePackItems($this->object);
            $this->updateDownloadEducation($this->object);

            if (Configuration::get('PS_FORCE_ASM_NEW_PRODUCT') && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && $this->object->getType() != Education::PTYPE_VIRTUAL) {
                $this->object->advanced_stock_management = 1;
                $this->object->save();
                $idShops = Shop::getContextListShopID();

                foreach ($idShops as $idShop) {
                    StockAvailable::setEducationDependsOnStock($this->object->id, true, (int) $idShop, 0);
                }

            }

            if (empty($this->errors)) {
                $languages = Language::getLanguages(false);

                Hook::exec('actionEducationAdd', ['id_education' => (int) $this->object->id, 'education' => $this->object]);

                if (in_array($this->object->visibility, ['both', 'search']) && Configuration::get('PS_SEARCH_INDEXATION')) {
                    Search::indexation(false, $this->object->id);
                }

                if (Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT') != 0 && Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                    $warehouseLocationEntity = new WarehouseEducationLocation();
                    $warehouseLocationEntity->id_education = $this->object->id;
                    $warehouseLocationEntity->id_education_attribute = 0;
                    $warehouseLocationEntity->id_warehouse = Configuration::get('PS_DEFAULT_WAREHOUSE_NEW_PRODUCT');
                    $warehouseLocationEntity->location = pSQL('');
                    $warehouseLocationEntity->save();
                }

                // Save and preview

                if (Tools::isSubmit('submitAddEducationAndPreview')) {
                    $this->redirect_after = $this->getPreviewUrl($this->object);
                }

                // Save and stay on same form

                if ($this->display == 'edit') {
                    $this->redirect_after = static::$currentIndex . '&id_education=' . (int) $this->object->id . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&updateeducation&conf=3&key_tab=' . Tools::safeOutput(Tools::getValue('key_tab')) . '&token=' . $this->token;
                } else {
                    // Default behavior (save and back)
                    $this->redirect_after = static::$currentIndex . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&conf=3&token=' . $this->token;
                }

            } else {
                $this->object->delete();
                // if errors : stay on edit page
                $this->display = 'edit';
            }

        } else {
            $this->errors[] = Tools::displayError('An error occurred while creating an object.') . ' <b>' . $this->table . '</b>';
        }

        return $this->object;
    }

    public function checkEducation() {

        $className = 'Education';
        // @todo : the call_user_func seems to contains only statics values (className = 'Education')
        $rules = call_user_func([$this->className, 'getValidationRules'], $this->className);
        $defaultLanguage = new Language((int) Configuration::get('PS_LANG_DEFAULT'));
        $languages = Language::getLanguages(false);

        // Check required fields

        foreach ($rules['required'] as $field) {

            if (!$this->isEducationFieldUpdated($field)) {
                continue;
            }

            if (($value = Tools::getValue($field)) == false && $value != '0') {

                if (Tools::getValue('id_' . $this->table) && $field == 'passwd') {
                    continue;
                }

                $this->errors[] = sprintf(
                    Tools::displayError('The %s field is required.'),
                    call_user_func([$className, 'displayFieldName'], $field, $className)
                );
            }

        }

        // Check multilingual required fields

        foreach ($rules['requiredLang'] as $fieldLang) {

            if ($this->isEducationFieldUpdated($fieldLang, $defaultLanguage->id) && !Tools::getValue($fieldLang . '_' . $defaultLanguage->id)) {
                $this->errors[] = sprintf(
                    Tools::displayError('This %1$s field is required at least in %2$s'),
                    call_user_func([$className, 'displayFieldName'], $fieldLang, $className),
                    $defaultLanguage->name
                );
            }

        }

        // Check fields sizes

        foreach ($rules['size'] as $field => $maxLength) {

            if ($this->isEducationFieldUpdated($field) && ($value = Tools::getValue($field)) && mb_strlen($value) > $maxLength) {
                $this->errors[] = sprintf(
                    Tools::displayError('The %1$s field is too long (%2$d chars max).'),
                    call_user_func([$className, 'displayFieldName'], $field, $className),
                    $maxLength
                );
            }

        }

        if (Tools::getIsset('description_short') && $this->isEducationFieldUpdated('description_short')) {
            $saveShort = Tools::getValue('description_short');
            $_POST['description_short'] = strip_tags(Tools::getValue('description_short'));
        }

        // Check description short size without html
        $limit = (int) Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT');

        if ($limit <= 0) {
            $limit = 400;
        }

        foreach ($languages as $language) {

            if ($this->isEducationFieldUpdated('description_short', $language['id_lang']) && ($value = Tools::getValue('description_short_' . $language['id_lang']))) {

                if (mb_strlen(strip_tags($value)) > $limit) {
                    $this->errors[] = sprintf(
                        Tools::displayError('This %1$s field (%2$s) is too long: %3$d chars max (current count %4$d).'),
                        call_user_func([$className, 'displayFieldName'], 'description_short'),
                        $language['name'],
                        $limit,
                        mb_strlen(strip_tags($value))
                    );
                }

            }

        }

        // Check multilingual fields sizes

        foreach ($rules['sizeLang'] as $fieldLang => $maxLength) {

            foreach ($languages as $language) {
                $value = Tools::getValue($fieldLang . '_' . $language['id_lang']);

                if ($value && mb_strlen($value) > $maxLength) {
                    $this->errors[] = sprintf(
                        Tools::displayError('The %1$s field is too long (%2$d chars max).'),
                        call_user_func([$className, 'displayFieldName'], $fieldLang, $className),
                        $maxLength
                    );
                }

            }

        }

        if ($this->isEducationFieldUpdated('description_short') && isset($_POST['description_short'])) {
            $_POST['description_short'] = $saveShort;
        }

        // Check fields validity

        foreach ($rules['validate'] as $field => $function) {

            if ($this->isEducationFieldUpdated($field) && ($value = Tools::getValue($field))) {
                $res = true;

                if (mb_strtolower($function) == 'iscleanhtml') {

                    if (!Validate::$function($value, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                        $res = false;
                    }

                } else

                if (!Validate::$function($value)) {
                    $res = false;
                }

                if (!$res) {
                    $this->errors[] = sprintf(
                        Tools::displayError('The %s field is invalid.'),
                        call_user_func([$className, 'displayFieldName'], $field, $className)
                    );
                }

            }

        }

        // Check multilingual fields validity

        foreach ($rules['validateLang'] as $fieldLang => $function) {

            foreach ($languages as $language) {

                if ($this->isEducationFieldUpdated($fieldLang, $language['id_lang']) && ($value = Tools::getValue($fieldLang . '_' . $language['id_lang']))) {

                    if (!Validate::$function($value, (int) Configuration::get('PS_ALLOW_HTML_IFRAME'))) {
                        $this->errors[] = sprintf(
                            Tools::displayError('The %1$s field (%2$s) is invalid.'),
                            call_user_func([$className, 'displayFieldName'], $fieldLang, $className),
                            $language['name']
                        );
                    }

                }

            }

        }

    }

    protected function isEducationFieldUpdated($field, $idLang = null) {

        // Cache this condition to improve performances
        static $isActivated = null;

        if (is_null($isActivated)) {
            $isActivated = Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP && $this->id_object;
        }

        if (!$isActivated) {
            return true;
        }

        $def = ObjectModel::getDefinition($this->object);

        if (!$this->object->isMultiShopField($field) && is_null($idLang) && isset($def['fields'][$field])) {
            return true;
        }

        if (is_null($idLang)) {
            return !empty($_POST['multishop_check'][$field]);
        } else {
            return !empty($_POST['multishop_check'][$field][$idLang]);
        }

    }

    public function updateAccessories($education) {

        $education->deleteAccessories();

        if ($accessories = Tools::getValue('inputAccessories')) {
            $accessoriesId = array_unique(explode('-', $accessories));

            if (count($accessoriesId)) {
                array_pop($accessoriesId);
                $education->changeAccessories($accessoriesId);
            }

        }

    }

    public function updatePackItems($education) {

        EducationPack::deleteItems($education->id);
        // lines format: QTY x ID-QTY x ID

        if (Tools::getValue('type_education') == 1) {

            $education->setDefaultAttribute(0); //reset cache_default_attribute
            $items = Tools::getValue('inputPackItems');

            $lines = array_unique(explode('-', $items));

            if (count($lines)) {

                foreach ($lines as $line) {

                    if (!empty($line)) {
                        $itemIdAttribute = 0;
                        count($array = explode('x', $line)) == 3 ? list($qty, $itemId, $itemIdAttribute) = $array : list($qty, $itemId) = $array;

                        if ($qty > 0 && isset($itemId)) {

                            if (EducationPack::isPack((int) $itemId || $education->id == (int) $itemId)) {
                                $this->errors[] = Tools::displayError('You can\'t add education packs into a pack');
                            } else

                            if (!EducationPack::addItem((int) $education->id, (int) $itemId, (int) $qty, (int) $itemIdAttribute)) {
                                $this->errors[] = Tools::displayError('An error occurred while attempting to add educations to the pack.');
                            }

                        }

                    }

                }

            }

        }

    }

    public function updateDownloadEducation($education, $edit = 999) {

        if ($edit !== 999) {
            Tools::displayParameterAsDeprecated('edit');
        }

        $idEducationDownload = EducationDownload::getIdFromIdEducation($education->id, false);

        if (!$idEducationDownload) {
            $idEducationDownload = (int) Tools::getValue('virtual_education_id');
        }

        if (Tools::getValue('type_education') == Education::PTYPE_VIRTUAL
            && Tools::getValue('is_virtual_file') == 1) {

            if (isset($_FILES['virtual_education_file_uploader']) && $_FILES['virtual_education_file_uploader']['size'] > 0) {
                $filename = EducationDownload::getNewFilename();
                $helper = new HelperUploader('virtual_education_file_uploader');
                $helper->setPostMaxSize(Tools::getOctets(ini_get('upload_max_filesize')))
                    ->setSavePath(_PS_DOWNLOAD_DIR_)->upload($_FILES['virtual_education_file_uploader'], $filename);
            } else {
                $filename = Tools::getValue('virtual_education_filename', EducationDownload::getNewFilename());
            }

            $education->setDefaultAttribute(0); //reset cache_default_attribute

            $active = Tools::getValue('virtual_education_active');
            $isShareable = Tools::getValue('virtual_education_is_shareable');
            $name = Tools::getValue('virtual_education_name');
            $nbDays = Tools::getValue('virtual_education_nb_days');
            $nbDownloable = Tools::getValue('virtual_education_nb_downloable');
            $expirationDate = Tools::getValue('virtual_education_expiration_date');
            // This whould allow precision up to the second, not supported by
            // the datepicker in the GUI, yet.
            //if ($expirationDate
            //    && !preg_match('/\d{1,2}\:\d{1,2}/', $expirationDate)) {
            //    // No time given should mean the end of the day.
            //    $dateExpiration .= ' 23:59:59';
            //}

            if ($expirationDate) {
                // We want the end of the given day.
                $expirationDate .= ' 23:59:59';
            }

            $download = new EducationDownload($idEducationDownload);
            $download->id_education = (int) $education->id;
            $download->display_filename = $name;
            $download->filename = $filename;
            $download->date_expiration = $expirationDate;
            $download->nb_days_accessible = (int) $nbDays;
            $download->nb_downloadable = (int) $nbDownloable;
            $download->active = (int) $active;
            $download->is_shareable = (int) $isShareable;

            if ($download->save()) {
                return true;
            }

        } else {
            // Delete the download and its file.

            if ($idEducationDownload) {
                $educationDownload = new EducationDownload($idEducationDownload);

                return $educationDownload->delete();
            }

        }

        return false;
    }

    public function getPreviewUrl(Education $education) {

        $idLang = Configuration::get('PS_LANG_DEFAULT', null, null, $this->context->shop->id);

        if (!Validate::isLoadedObject($education) || !$education->id_category_default) {
            return $this->l('Unable to determine the preview URL. This education has not been linked with a category, yet.');
        }

        if (!ShopUrl::getMainShopDomain()) {
            return false;
        }

        $isRewriteActive = (bool) Configuration::get('PS_REWRITING_SETTINGS');
        $previewUrl = $this->context->link->getEducationLink(
            $education,
            $this->getFieldValue($education, 'link_rewrite', $this->context->language->id),
            Category::getLinkRewrite($this->getFieldValue($education, 'id_category_default'), $this->context->language->id),
            null,
            $idLang,
            (int) $this->context->shop->id,
            0,
            $isRewriteActive
        );

        if (!$education->active) {
            $adminDir = dirname($_SERVER['PHP_SELF']);
            $adminDir = substr($adminDir, strrpos($adminDir, '/') + 1);
            $previewUrl .= ((strpos($previewUrl, '?') === false) ? '?' : '&') . 'adtoken=' . $this->token . '&ad=' . $adminDir . '&id_employee=' . (int) $this->context->employee->id;
        }

        return $previewUrl;
    }

    public function processStatus() {

        $this->loadObject(true);

        if (!Validate::isLoadedObject($this->object)) {
            return false;
        }

        if (($error = $this->object->validateFields(false, true)) !== true) {
            $this->errors[] = $error;
        }

        if (($error = $this->object->validateFieldsLang(false, true)) !== true) {
            $this->errors[] = $error;
        }

        if (count($this->errors)) {
            return false;
        }

        $res = parent::processStatus();

        $query = trim(Tools::getValue('bo_query'));
        $searchType = (int) Tools::getValue('bo_search_type');

        if ($query) {
            $this->redirect_after = preg_replace('/[\?|&](bo_query|bo_search_type)=([^&]*)/i', '', $this->redirect_after);
            $this->redirect_after .= '&bo_query=' . $query . '&bo_search_type=' . $searchType;
        }

        return $res;
    }

    public function ajaxProcessUpdate() {

        $existingEducation = $this->object;

        $this->checkEducation();

        if (!empty($this->errors)) {
            $this->display = 'edit';

            return false;
        }

        $id = (int) Tools::getValue('id_' . $this->table);
        /* Update an existing education */

        if (isset($id) && !empty($id)) {
            /** @var Education $object */
            $object = new $this->className((int) $id);
            $this->object = $object;

            if (Validate::isLoadedObject($object)) {
                $educationTypeBefore = $object->getEducationType();
                $this->copyFromPost($object, $this->table);
                $object->indexed = 0;

                if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
                    $object->setFieldsToUpdate((array) Tools::getValue('multishop_check', []));
                }

                // Duplicate declinaisons if not associated to shop

                if ($this->context->shop->getContext() == Shop::CONTEXT_SHOP) {
                    $isAssociatedToShop = false;
                    $declinaisons = Education::getEducationAttributesIds($object->id);

                } else {
                    $isAssociatedToShop = true;
                }

                if ($object->update()) {
                    // If the education doesn't exist in the current shop but exists in another shop

                    Logger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $this->object->id, true, (int) $this->context->employee->id);

                    if (in_array($this->context->shop->getContext(), [Shop::CONTEXT_SHOP, Shop::CONTEXT_ALL])) {

                        if ($this->isTabSubmitted('Declinaisons')) {
                            $this->processEducationAttribute();
                        }

                        if ($this->isTabSubmitted('Images')) {
                            $this->processImageLegends();
                        }

                        if ($this->isTabSubmitted('Accounting')) {
                            $this->processRealyingAccount();
                        }

                        $this->updatePackItems($object);
                        // Disallow avanced stock management if the education become a pack

                        if ($educationTypeBefore == Education::PTYPE_VIRTUAL && $object->getType() == Education::PTYPE_PACK) {

                        }

                        $this->updateDownloadEducation($object);

                    }

                    if (empty($this->errors)) {

                        if (in_array($object->visibility, ['both', 'search']) && Configuration::get('PS_SEARCH_INDEXATION')) {
                            Search::indexation(false, $object->id);
                        }

                        // Save and preview

                        if (Tools::isSubmit('submitAddEducationAndPreview')) {
                            $this->redirect_after = $this->getPreviewUrl($object);
                        } else {
                            $page = (int) Tools::getValue('page');
                            // Save and stay on same form

                            if ($this->display == 'edit') {

                            } else {
                                // Default behavior (save and back)
                                $this->redirect_after = static::$currentIndex . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&conf=4' . ($page > 1 ? '&submitFiltereducation=' . (int) $page : '') . '&token=' . $this->token;
                            }

                        }

                    }

                    // if errors : stay on edit page
                    else {
                        $this->display = 'edit';
                    }

                } else {

                    if (!$isAssociatedToShop && isset($declinaisons) && $declinaisons) {

                        foreach ($declinaisons as $idDeclinaison) {
                            $combination = new Declinaison((int) $idDeclinaison['id_education_attribute']);
                            $combination->delete();
                        }

                    }

                    $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
                }

            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Tools::displayError('The object cannot be loaded. ') . ')';
            }

            return $object;
        }

    }

    public function processUpdate() {

        $existingEducation = $this->object;

        $this->checkEducation();

        if (!empty($this->errors)) {
            $this->display = 'edit';

            return false;
        }

        $id = (int) Tools::getValue('id_' . $this->table);
        /* Update an existing education */

        if (isset($id) && !empty($id)) {
            /** @var Education $object */
            $object = new $this->className((int) $id);
            $this->object = $object;

            if (Validate::isLoadedObject($object)) {
                $educationTypeBefore = $object->getEducationType();
                $this->copyFromPost($object, $this->table);
                $object->indexed = 0;

                if (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP) {
                    $object->setFieldsToUpdate((array) Tools::getValue('multishop_check', []));
                }

                // Duplicate declinaisons if not associated to shop

                if ($this->context->shop->getContext() == Shop::CONTEXT_SHOP) {
                    $isAssociatedToShop = false;
                    $declinaisons = Education::getEducationAttributesIds($object->id);

                } else {
                    $isAssociatedToShop = true;
                }

                if ($object->update()) {
                    // If the education doesn't exist in the current shop but exists in another shop

                    Logger::addLog(sprintf($this->l('%s modification', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $this->object->id, true, (int) $this->context->employee->id);

                    if (in_array($this->context->shop->getContext(), [Shop::CONTEXT_SHOP, Shop::CONTEXT_ALL])) {

                        if ($this->isTabSubmitted('Declinaisons')) {
                            $this->processEducationAttribute();
                        }

                        if ($this->isTabSubmitted('Images')) {
                            $this->processImageLegends();
                        }

                        if ($this->isTabSubmitted('Accounting')) {
                            $this->processRealyingAccount();
                        }

                        $this->updatePackItems($object);
                        // Disallow avanced stock management if the education become a pack

                        if ($educationTypeBefore == Education::PTYPE_VIRTUAL && $object->getType() == Education::PTYPE_PACK) {

                        }

                        $this->updateDownloadEducation($object);

                    }

                    if (empty($this->errors)) {

                        if (in_array($object->visibility, ['both', 'search']) && Configuration::get('PS_SEARCH_INDEXATION')) {
                            Search::indexation(false, $object->id);
                        }

                        // Save and preview

                        if (Tools::isSubmit('submitAddEducationAndPreview')) {
                            $this->redirect_after = $this->getPreviewUrl($object);
                        } else {
                            $page = (int) Tools::getValue('page');
                            // Save and stay on same form

                            if ($this->display == 'edit') {

                            } else {
                                // Default behavior (save and back)
                                $this->redirect_after = static::$currentIndex . (Tools::getIsset('id_category') ? '&id_category=' . (int) Tools::getValue('id_category') : '') . '&conf=4' . ($page > 1 ? '&submitFiltereducation=' . (int) $page : '') . '&token=' . $this->token;
                            }

                        }

                    }

                    // if errors : stay on edit page
                    else {
                        $this->display = 'edit';
                    }

                } else {

                    if (!$isAssociatedToShop && isset($declinaisons) && $declinaisons) {

                        foreach ($declinaisons as $idDeclinaison) {
                            $combination = new Declinaison((int) $idDeclinaison['id_education_attribute']);
                            $combination->delete();
                        }

                    }

                    $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
                }

            } else {
                $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Tools::displayError('The object cannot be loaded. ') . ')';
            }

            return $object;
        }

    }

    public function processEducationAttribute() {

        // Don't process if the combination fields have not been submitted

        if (!Tools::getValue('attribute_combination_list')) {
            return;
        }

        if (Validate::isLoadedObject($education = $this->object)) {

            if ($this->isEducationFieldUpdated('attribute_price') && (!Tools::getIsset('attribute_price') || Tools::getIsset('attribute_price') == null)) {
                $this->errors[] = Tools::displayError('The price attribute is required.');
            }

            if (!Tools::getIsset('attribute_combination_list') || Tools::isEmpty(Tools::getValue('attribute_combination_list'))) {
                $this->errors[] = Tools::displayError('You must add at least one attribute.');
            }

            $arrayChecks = [
                'reference'          => 'isReference',
                'supplier_reference' => 'isReference',
                'location'           => 'isReference',
                'ean13'              => 'isEan13',
                'upc'                => 'isUpc',
                'wholesale_price'    => 'isPrice',
                'price'              => 'isPrice',
                'ecotax'             => 'isPrice',
                'quantity'           => 'isInt',
                'weight'             => 'isUnsignedFloat',
                'unit_price_impact'  => 'isPrice',
                'default_on'         => 'isBool',
                'minimal_quantity'   => 'isUnsignedInt',
                'available_date'     => 'isDateFormat',
            ];

            foreach ($arrayChecks as $property => $check) {

                if (Tools::getValue('attribute_' . $property) !== false && !call_user_func(['Validate', $check], Tools::getValue('attribute_' . $property))) {
                    $this->errors[] = sprintf(Tools::displayError('Field %s is not valid'), $property);
                }

            }

            if (!count($this->errors)) {

                if (!isset($_POST['attribute_wholesale_price'])) {
                    $_POST['attribute_wholesale_price'] = 0;
                }

                if (!isset($_POST['attribute_price_impact'])) {
                    $_POST['attribute_price_impact'] = 0;
                }

                if (!isset($_POST['attribute_weight_impact'])) {
                    $_POST['attribute_weight_impact'] = 0;
                }

                if (!isset($_POST['attribute_ecotax'])) {
                    $_POST['attribute_ecotax'] = 0;
                }

                if (Tools::getValue('attribute_default')) {
                    $education->deleteDefaultAttributes();
                }

                // Change existing one

                if (($idEducationAttribute = (int) Tools::getValue('id_education_attribute')) || ($idEducationAttribute = $education->educationAttributeExists(Tools::getValue('attribute_combination_list'), false, null, true, true))) {

                    if ($this->tabAccess['edit'] === '1') {

                        if ($this->isEducationFieldUpdated('available_date_attribute') && (Tools::getValue('available_date_attribute') != '' && !Validate::isDateFormat(Tools::getValue('available_date_attribute')))) {
                            $this->errors[] = Tools::displayError('Invalid date format.');
                        } else {
                            $education->updateAttribute(
                                (int) $idEducationAttribute,
                                $this->isEducationFieldUpdated('attribute_wholesale_price') ? Tools::getValue('attribute_wholesale_price') : null,
                                $this->isEducationFieldUpdated('attribute_price_impact') ? Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact') : null,
                                $this->isEducationFieldUpdated('attribute_weight_impact') ? Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact') : null,
                                $this->isEducationFieldUpdated('attribute_unit_impact') ? Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact') : null,
                                $this->isEducationFieldUpdated('attribute_ecotax') ? Tools::getValue('attribute_ecotax') : null,
                                Tools::getValue('id_image_attr'),
                                Tools::getValue('attribute_reference'),
                                Tools::getValue('attribute_ean13'),
                                $this->isEducationFieldUpdated('attribute_default') ? Tools::getValue('attribute_default') : null,
                                Tools::getValue('attribute_location'),
                                Tools::getValue('attribute_upc'),
                                $this->isEducationFieldUpdated('attribute_minimal_quantity') ? Tools::getValue('attribute_minimal_quantity') : null,
                                $this->isEducationFieldUpdated('available_date_attribute') ? Tools::getValue('available_date_attribute') : null,
                                false
                            );
                            StockAvailable::setEducationDependsOnStock((int) $education->id, $education->depends_on_stock, null, (int) $idEducationAttribute);
                            StockAvailable::setEducationOutOfStock((int) $education->id, $education->out_of_stock, null, (int) $idEducationAttribute);
                        }

                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to add this.');
                    }

                }

                // Add new
                else {

                    if ($this->tabAccess['add'] === '1') {
                        /** @var Education $education */

                        if ($education->educationAttributeExists(Tools::getValue('attribute_combination_list'))) {
                            $this->errors[] = Tools::displayError('This combination already exists.');
                        } else {
                            $idEducationAttribute = $education->addDeclinaisonEntity(
                                Tools::getValue('attribute_wholesale_price'),
                                Tools::getValue('attribute_price') * Tools::getValue('attribute_price_impact'),
                                Tools::getValue('attribute_weight') * Tools::getValue('attribute_weight_impact'),
                                Tools::getValue('attribute_unity') * Tools::getValue('attribute_unit_impact'),
                                Tools::getValue('attribute_ecotax'),
                                0,
                                Tools::getValue('id_image_attr'),
                                Tools::getValue('attribute_reference'),
                                null,
                                Tools::getValue('attribute_ean13'),
                                Tools::getValue('attribute_default'),
                                Tools::getValue('attribute_location'),
                                Tools::getValue('attribute_upc'),
                                Tools::getValue('attribute_minimal_quantity'),
                                [],
                                Tools::getValue('available_date_attribute')
                            );
                            StockAvailable::setEducationDependsOnStock((int) $education->id, $education->depends_on_stock, null, (int) $idEducationAttribute);
                            StockAvailable::setEducationOutOfStock((int) $education->id, $education->out_of_stock, null, (int) $idEducationAttribute);
                        }

                    } else {
                        $this->errors[] = Tools::displayError('You do not have permission to') . '<hr>' . Tools::displayError('edit here.');
                    }

                }

                if (!count($this->errors)) {
                    $combination = new Declinaison((int) $idEducationAttribute);
                    $combination->setAttributes(Tools::getValue('attribute_combination_list'));

                    // images could be deleted before
                    $idImages = Tools::getValue('id_image_attr');

                    if (!empty($idImages)) {
                        $combination->setImages($idImages);
                    }

                    $education->checkDefaultAttributes();

                    if (Tools::getValue('attribute_default')) {
                        Education::updateDefaultAttribute((int) $education->id);

                        if (isset($idEducationAttribute)) {
                            $education->cache_default_attribute = (int) $idEducationAttribute;
                        }

                    }

                }

            }

        }

    }

    public function processImageLegends() {

        if (Tools::getValue('key_tab') == 'Images' && Tools::getValue('submitAddeducationAndStay') == 'update_legends' && Validate::isLoadedObject($education = new Education((int) Tools::getValue('id_education')))) {
            $idImage = (int) Tools::getValue('id_caption');
            $languageIds = Language::getIDs(false);

            foreach ($_POST as $key => $val) {

                if (preg_match('/^legend_([0-9]+)/i', $key, $match)) {

                    foreach ($languageIds as $idLang) {

                        if ($val && $idLang == $match[1]) {
                            Db::getInstance()->execute('UPDATE ' . _DB_PREFIX_ . 'image-education_lang SET legend = "' . pSQL($val) . '" WHERE ' . ($idImage ? 'id_image_education = ' . (int) $idImage : 'EXISTS (SELECT 1 FROM ' . _DB_PREFIX_ . 'image_education WHERE ' . _DB_PREFIX_ . 'image.id_image_education = ' . _DB_PREFIX_ . 'image_education_lang.id_image AND id_education = ' . (int) $education->id . ')') . ' AND id_lang = ' . (int) $idLang);
                        }

                    }

                }

            }

        }

    }

    public function ajaxProcessUpdateByVal() {

        $idEducation = (int) Tools::getValue('idEducation');
        $field = Tools::getValue('field');
        $fieldValue = Tools::getValue('fieldValue');

        $education = new Education($idEducation);
        $classVars = get_class_vars(get_class($education));

        if (isset($classVars['definition']['fields'])) {
            $fields = $classVars['definition']['fields'];
        }

        if (Validate::isLoadedObject($education)) {

            if (array_key_exists('lang', $fields[$field]) && $fields[$field]['lang']) {
                $idLang = Context::getContext()->language->id;
                $education->{$field}

                [(int) $idLang] = $fieldValue;

            } else {
                $education->$field = $fieldValue;
            }

            $result = $education->update();

            if (!isset($result) || !$result) {
                $this->errors[] = Tools::displayError('An error occurred while updating the education.');
            } else {
                $result = [
                    'success' => true,
                    'message' => $this->l('Update successful'),
                ];
            }

        } else {

            $this->errors[] = Tools::displayError('An error occurred while loading the education.');
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

    public function ajaxProcessEducationManufacturers() {

        $manufacturers = Manufacturer::getManufacturers();
        $jsonArray = [];

        if ($manufacturers) {

            foreach ($manufacturers as $manufacturer) {
                $tmp = ["optionValue" => $manufacturer['id_manufacturer'], "optionDisplay" => htmlspecialchars(trim($manufacturer['name']))];
                $jsonArray[] = json_encode($tmp);
            }

        }

        die('[' . implode(',', $jsonArray) . ']');
    }

    


    public function renderForm() {

        if ($this->object->id > 0) {
            // This nice code (irony) is here to store the education name, because the row after will erase education name in multishop context
            $this->education_name = $this->object->name[$this->context->language->id];
        }

        if (!method_exists($this, 'initForm' . $this->tab_display)) {
            return '';
        }

        $education = $this->object;

        // Education for multishop
        $this->context->smarty->assign('bullet_common_field', '');

        if (Shop::isFeatureActive() && $this->display == 'edit') {

            if (Shop::getContext() != Shop::CONTEXT_SHOP) {
                $this->context->smarty->assign(
                    [
                        'display_multishop_checkboxes' => true,
                        'multishop_check'              => Tools::getValue('multishop_check'),
                    ]
                );
            }

            if (Shop::getContext() != Shop::CONTEXT_ALL) {
                $this->context->smarty->assign('bullet_common_field', '<i class="icon-circle text-orange"></i>');
                $this->context->smarty->assign('display_common_field', true);
            }

        }

        $this->tpl_form_vars['tabs_preloaded'] = $this->available_tabs;

        $this->getLanguages();

        $this->tpl_form_vars['id_lang_default'] = Configuration::get('PS_LANG_DEFAULT');

        $this->tpl_form_vars['currentIndex'] = static::$currentIndex;
        $this->tpl_form_vars['display_multishop_checkboxes'] = (Shop::isFeatureActive() && Shop::getContext() != Shop::CONTEXT_SHOP && $this->display == 'edit');
        $this->fields_form = [''];

        $this->tpl_form_vars['token'] = $this->token;
        $this->tpl_form_vars['declinaisonImagesJs'] = $this->getDeclinaisonImagesJs();
        $this->tpl_form_vars['PS_ALLOW_ACCENTED_CHARS_URL'] = (int) Configuration::get('PS_ALLOW_ACCENTED_CHARS_URL');
        $this->tpl_form_vars['post_data'] = json_encode($_POST);
        $this->tpl_form_vars['save_error'] = !empty($this->errors);
        $this->tpl_form_vars['mod_evasive'] = Tools::apacheModExists('evasive');
        $this->tpl_form_vars['mod_security'] = Tools::apacheModExists('security');
        $this->tpl_form_vars['ps_force_friendly_education'] = Configuration::get('PS_FORCE_FRIENDLY_PRODUCT');

        $this->tpl_form_vars['platforms'] = Platform::getPlatforms();

        // autoload rich text editor (tiny mce)
        $this->tpl_form_vars['tinymce'] = true;
        $iso = $this->context->language->iso_code;
        $this->tpl_form_vars['iso'] = file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en';
        $this->tpl_form_vars['path_css'] = _THEME_CSS_DIR_;
        $this->tpl_form_vars['ad'] = __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_);

        if (Validate::isLoadedObject(($this->object))) {
            $idEducation = (int) $this->object->id;
        } else {
            $idEducation = (int) Tools::getvalue('id_education');
        }

        $page = (int) Tools::getValue('page');

        $this->tpl_form_vars['form_action'] = $this->context->link->getAdminLink('AdminEducations') . '&' . ($idEducation ? 'updateeducation&id_education=' . (int) $idEducation : 'addeducation') . ($page > 1 ? '&page=' . (int) $page : '');
        $this->tpl_form_vars['id_education'] = $idEducation;

        // Transform configuration option 'upload_max_filesize' in octets
        $uploadMaxFilesize = Tools::getOctets(ini_get('upload_max_filesize'));

        // Transform configuration option 'upload_max_filesize' in MegaOctets
        $uploadMaxFilesize = ($uploadMaxFilesize / 1024) / 1024;

        $this->tpl_form_vars['upload_max_filesize'] = $uploadMaxFilesize;
        $this->tpl_form_vars['country_display_tax_label'] = $this->context->country->display_tax_label;
        $this->tpl_form_vars['has_declinaisons'] = $this->object->hasAttributes();
        $this->education_exists_in_shop = true;

        if ($this->display == 'edit' && Validate::isLoadedObject($education) && Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP && !$education->isAssociatedToShop($this->context->shop->id)) {
            $this->education_exists_in_shop = false;

            if ($this->tab_display == 'Informations') {
                $this->displayWarning($this->l('Warning: The education does not exist in this shop'));
            }

            $defaultEducation = new Education();
            $definition = ObjectModel::getDefinition($education);

            foreach ($definition['fields'] as $fieldName => $field) {

                if (isset($field['shop']) && $field['shop']) {
                    $education->$fieldName = ObjectModel::formatValue($defaultEducation->$fieldName, $field['type']);
                }

            }

        }

        $this->tpl_form_vars['education_type'] = (int) Tools::getValue('type_education', $education->getEducationType());

        // let's calculate this once for all

        if (!Validate::isLoadedObject($this->object) && Tools::getValue('id_education')) {
            $this->errors[] = 'Unable to load object';
        } else {
            $this->_displayDraftWarning($this->object->active);

            // if there was an error while saving, we don't want to lose posted data

            if (!empty($this->errors)) {
                $this->copyFromPost($this->object, $this->table);
            }

            $this->initPack($this->object);
            $this->{'initForm' . $this->tab_display}

            ($this->object);
            $this->tpl_form_vars['education'] = $this->object;

            if ($this->ajax) {

                if (!isset($this->tpl_form_vars['custom_form'])) {
                    throw new PhenyxShopException('custom_form empty for action ' . $this->tab_display);
                } else {
                    return $this->tpl_form_vars['custom_form'];
                }

            }

        }

        $parent = parent::renderForm();
        $this->addJqueryPlugin(['fancybox', 'typewatch']);

        return $parent;
    }

    public function getDeclinaisonImagesJS() {

        /** @var Education $obj */

        if (!($obj = $this->loadObject(true))) {
            return '';
        }

        $content = 'var combination_images = new Array();';

        if (!$allDeclinaisonImages = $obj->getDeclinaisonImages($this->context->language->id)) {
            return $content;
        }

        foreach ($allDeclinaisonImages as $idEducationAttribute => $combinationImages) {
            $i = 0;
            $content .= 'combination_images[' . (int) $idEducationAttribute . '] = new Array();';

            foreach ($combinationImages as $combinationImage) {
                $content .= 'combination_images[' . (int) $idEducationAttribute . '][' . $i++ . '] = ' . (int) $combinationImage['id_image'] . ';';
            }

        }

        return $content;
    }

    protected function _displayDraftWarning($active) {

        $content = '<div class="warn draft" style="' . ($active ? 'display:none' : '') . '">
                <span>' . $this->l('Your education will be saved as a draft.') . '</span>
                <a href="#" class="btn btn-default pull-right" onclick="submitAddEducationAndPreview()" ><i class="icon-external-link-sign"></i> ' . $this->l('Save and preview') . '</a>
                <input type="hidden" name="fakeSubmitAddEducationAndPreview" id="fakeSubmitAddEducationAndPreview" />
            </div>';
        $this->tpl_form_vars['draft_warning'] = $content;
    }

    protected function initPack(Education $education) {

        $this->tpl_form_vars['is_pack'] = ($education->id && EducationPack::isPack($education->id)) || Tools::getValue('type_education') == Education::PTYPE_PACK;
        $education->packItems = EducationPack::getItems($education->id, $this->context->language->id);

        $inputPackItems = '';

        if (Tools::getValue('inputPackItems')) {
            $inputPackItems = Tools::getValue('inputPackItems');
        } else {

            if (is_array($education->packItems)) {

                foreach ($education->packItems as $packItem) {
                    $inputPackItems .= $packItem->pack_quantity . 'x' . $packItem->id . '-';
                }

            }

        }

        $this->tpl_form_vars['input_pack_items'] = $inputPackItems;

        $inputNamepackItems = '';

        if (Tools::getValue('namePackItems')) {
            $inputNamepackItems = Tools::getValue('namePackItems');
        } else {

            if (is_array($education->packItems)) {

                foreach ($education->packItems as $packItem) {
                    $inputNamepackItems .= $packItem->pack_quantity . ' x ' . $packItem->name . '¤';
                }

            }

        }

        $this->tpl_form_vars['input_namepack_items'] = $inputNamepackItems;
    }

    public function ajaxProcessUploadEducationFile() {

        if (isset($_FILES['educationFile']['name']) && !empty($_FILES['educationFile']['name']) && !empty($_FILES['educationFile']['tmp_name'])) {

            $reader = new \PhpOffice\PhpSpreadsheet\Reader\Xlsx();
            $reader->setReadDataOnly(true);
            $Upload['content'] = Tools::file_get_contents($_FILES['educationFile']['tmp_name']);
            $Upload['name'] = $_FILES['educationFile']['name'];
            $Upload['mime'] = $_FILES['educationFile']['type'];
            $dir = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'fileimport' . DIRECTORY_SEPARATOR;
            $fileName = $_FILES['educationFile']['name'];
            $uploadfile = $dir . basename($fileName);
            $sourcePath = $_FILES['educationFile']['tmp_name'];
            $spreadsheet = $reader->load($sourcePath);
            $sheetDatas = $spreadsheet->getActiveSheet()->toArray();

            if (is_array($sheetDatas) && is_array($sheetDatas[0])) {
                $columns = count($sheetDatas[0]);
            }

            move_uploaded_file($sourcePath, $uploadfile);
            $this->identifier_value = 'importFile';
            $this->tab_identifier = 'detail' . $this->controller_name . '-' . $this->identifier_value;
            $this->tab_link = 'tab-' . $this->controller_name . '-' . $this->identifier_value;
            $this->tab_liId = 'detail-' . $this->controller_name . '-' . $this->identifier_value;
            $this->closeTabButton = '<button type="button" class="close tabdetail" data-id="' . $this->tab_liId . '" ><i class="icon-times-circle" aria-hidden="true"></i></button>';
            $this->displayBackOfficeHeader = '';
            $this->displayBackOfficeFooter = '';
            $this->ajax_js = '';
            $this->scriptHook = '';
            $this->tab_name = 'educationFile';
            $this->paragrid = false;

            $this->content = $data->fetch();

            $this->ajaxTabDisplay();

        }

    }

    public function haveThisAccessory($accessoryId, $accessories) {

        foreach ($accessories as $accessory) {

            if ((int) $accessory['id_education'] == (int) $accessoryId) {
                return true;
            }

        }

        return false;
    }

    public function initFormPrerequis($education) {

        $data = $this->createTemplate($this->tpl_form);
        $declinaisons = [];
        $prerequis = '';

        if ($education->cache_default_attribute > 0) {

            $declinaisons = $education->getAttributeDeclinaisons($this->context->language->id);

            foreach ($declinaisons as &$declinaison) {
                $declinaison['prerequis'] = new EducationPrerequis($declinaison['id_education_prerequis']);
            }

        } else {

            $prerequis = new EducationPrerequis($education->id_education_prerequis);
        }

        $isoTinyMce = $this->context->language->iso_code;
        $isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');
        $data->assign(
            [

                'education'           => $education,
                'hasattribut'         => $education->cache_default_attribute,
                'declinaisons'        => $declinaisons,
                'prerequis'           => $prerequis,
                'collectionPrerequis' => new PhenyxShopCollection('EducationPrerequis'),
                'languages'           => $this->_languages,
                'ad'                  => dirname($_SERVER['PHP_SELF']),
                'iso_tiny_mce'        => $isoTinyMce,
                'id_lang'             => $this->context->language->id,
                'link'                => $this->context->link,
                'bo_imgdir'           => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
            ]
        );

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function ajaxProcessSavePrerequisField() {

        $idPrerequis = Tools::getValue('idPrerequis');
        $prerequis = new EducationPrerequis($idPrerequis);
        $content = Tools::getValue('content');
        $curentContent = $prerequis->content;

        $curentSize = sizeof($curentContent);
        $curentContent[$curentSize] = $content[0];

        $key = count($content) - 1;
        $prerequis->content = serialize($curentContent);
        $prerequis->update();

        if ($content[0]['type'] == 'input') {

            $html2 = '<tr>
                        <td colspan="3">
                        <table width="100%" border="0">
                            <tr>
                                <td>' . $content[0]['values'] . '</td>
                                <td><input type="text" name="' . $content[0]['name'] . '" value=""></td>
                            </tr>
                        </table>
                        </td>
                    </tr>';
        }

        if ($content[0]['type'] == 'bool') {

            $html2 = '<tr>
                        <td>' . $content[0]['values'] . '</td>
                        <td class="smiley"><input type="radio"  class="scorePoint" name="' . $content[0]['name'] . '" value="0"></td>
                        <td class="smiley"><input type="radio" class="scorePoint" name="' . $content[0]['name'] . '" value="1"></td>
                    </tr>';

        }

        $html = '<tr id="content_' . $key . '">
                        <td>' . $content[$key]['type'] . '</td>
                        <td>' . $content[$key]['name'] . '</td>
                        <td>' . $content[$key]['values'] . '</td>
                        <td align="center"><button class="buttonremoveFields" onClick="removeFields(' . $key . ')"><i class="icon icon-trash" aria-hidden="true"></i></button></td>
                    </tr>';

        $result = [
            'success' => true,
            'message' => 'Le champs a été ajouté avec succès',
            'html'    => $html,
            'html2'   => $html2,
        ];

        die(Tools::jsonEncode($result));
    }

    public function initFormAccounting($obj) {

        $data = $this->createTemplate($this->tpl_form);
        $education = $obj;

        $data->assign(
            [

                'education' => $education,
                'link'      => $this->context->link,
            ]
        );

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function processRealyingAccount() {

        if ($id = (int) Tools::getValue($this->identifier)) {
            $id_education_account = Tools::getValue('id_education_account');

            $productAccount = new EducationAccount($id_education_account);
            $productAccount->sell_account_local = Tools::getValue('sell_account_local');
            $productAccount->sell_account_cee = Tools::getValue('sell_account_cee');
            $productAccount->sell_account_export = Tools::getValue('sell_account_export');
            $productAccount->sell_account_notax = Tools::getValue('sell_account_notax');
            $productAccount->purchase_account_local = Tools::getValue('purchase_account_local');
            $productAccount->purchase_account_cee = Tools::getValue('purchase_account_cee');
            $productAccount->purchase_account_import = Tools::getValue('purchase_account_import');
            $productAccount->purchase_account_notax = Tools::getValue('purchase_account_notax');

            if (!$productAccount->update()) {
                $this->errors[] = Tools::displayError('An error occurred while updating product relaying accounts.');
            }

        }

    }

    public function initFormPrices($obj) {

        $data = $this->createTemplate($this->tpl_form);
        $education = $obj;

        if ($obj->id) {

            $currencies = Currency::getCurrencies();
            $attributes = $obj->getAttributesGroups((int) $this->context->language->id);
            $declinaisons = [];

            foreach ($attributes as $attribute) {
                $declinaisons[$attribute['id_education_attribute']]['id_education_attribute'] = $attribute['id_education_attribute'];

                if (!isset($declinaisons[$attribute['id_education_attribute']]['attributes'])) {
                    $declinaisons[$attribute['id_education_attribute']]['attributes'] = '';
                }

                $declinaisons[$attribute['id_education_attribute']]['attributes'] .= $attribute['attribute_name'] . ' - ';

                $declinaisons[$attribute['id_education_attribute']]['price'] = Tools::displayPrice(
                    Tools::convertPrice(
                        Education::getPriceStatic((int) $obj->id, false, $attribute['id_education_attribute']),
                        $this->context->currency
                    ),
                    $this->context->currency
                );
            }

            foreach ($declinaisons as &$combination) {
                $combination['attributes'] = rtrim($combination['attributes'], ' - ');
            }

            $data->assign(
                [

                    'currencies'   => $currencies,
                    'declinaisons' => $declinaisons,
                    'multi_shop'   => Shop::isFeatureActive(),
                    'link'         => new Link(),
                    'pack'         => new EducationPack(),
                ]
            );
        } else {
            $this->displayWarning($this->l('You must save this education before adding specific pricing'));
            $education->id_tax_rules_group = (int) Education::getIdTaxRulesGroupMostUsed();

        }

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

        // prices part
        $data->assign(
            [
                'link'                    => $this->context->link,
                'currency'                => $currency = $this->context->currency,
                'tax_rules_groups'        => $taxRulesGroups,
                'taxesRatesByGroup'       => $taxRates,
                'tax_exclude_taxe_option' => Tax::excludeTaxeOption(),
            ]
        );

        $education->price = Tools::convertPrice($education->price, $this->context->currency, true, $this->context);

        $data->assign('ps_tax', Configuration::get('PS_TAX'));

        $data->assign('country_display_tax_label', $this->context->country->display_tax_label);
        $data->assign(
            [
                'currency', $this->context->currency,
                'education' => $education,
                'token'     => $this->token,
            ]
        );

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    protected function _applyTaxToEcotax($education) {

        if ($education->ecotax) {
            $education->ecotax = Tools::ps_round($education->ecotax * (1 + Tax::getEducationEcotaxRate() / 100), 2);
        }

    }

    public function initFormSeo($education) {

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        $data = $this->createTemplate($this->tpl_form);

        $context = $this->context;
        $rewrittenLinks = [];

        if (!Validate::isLoadedObject($education) || !$education->id_category_default) {

            foreach ($this->_languages as $language) {
                $rewrittenLinks[(int) $language['id_lang']] = [$this->l('Unable to determine the preview URL. This education has not been linked with a category, yet.')];
            }

        } else {

            foreach ($this->_languages as $language) {
                $rewrittenLinks[(int) $language['id_lang']] = explode(
                    '[REWRITE]',
                    $context->link->getEducationLink($education->id, '[REWRITE]', (int) $education->id_category_default)
                );
            }

        }

        $data->assign(
            [
                'education'             => $education,
                'languages'             => $this->_languages,
                'id_lang'               => $this->context->language->id,
                'ps_ssl_enabled'        => Configuration::get('PS_SSL_ENABLED'),
                'curent_shop_url'       => $this->context->shop->getBaseURL(),
                'default_form_language' => $this->default_form_language,
                'rewritten_links'       => $rewrittenLinks,
                'link'                  => $this->context->link,
            ]
        );

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function initFormPack($education) {

        $data = $this->createTemplate($this->tpl_form);

        // If pack items have been submitted, we want to display them instead of the actuel content of the pack
        // in database. In case of a submit error, the posted data is not lost and can be sent again.

        if (Tools::getValue('namePackItems')) {

            $inputPackItems = Tools::getValue('inputPackItems');
            $inputNamepackItems = Tools::getValue('namePackItems');
            $packItems = $this->getPackItems();
        } else {

            $education->packItems = EducationPack::getItems($education->id, $this->context->language->id);

            $packItems = $this->getPackItems($education);

            $inputNamepackItems = '';
            $inputPackItems = '';

            foreach ($packItems as $packItem) {
                $inputPackItems .= $packItem['pack_quantity'] . 'x' . $packItem['id'] . 'x' . $packItem['id_education_attribute'] . '-';
                $inputNamepackItems .= $packItem['pack_quantity'] . ' x ' . $packItem['name'] . '¤';
            }

        }

        $data->assign(
            [
                'input_pack_items'     => $inputPackItems,
                'input_namepack_items' => $inputNamepackItems,
                'pack_items'           => $packItems,
                'education_type'       => (int) Tools::getValue('type_education', $education->getEducationType()),
            ]
        );

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function getPackItems($education = null) {

        $packItems = [];

        if (!$education) {
            $namesInput = Tools::getValue('namePackItems');
            $idsInput = Tools::getValue('inputPackItems');

            if (!$namesInput || !$idsInput) {
                return [];
            }

            // ids is an array of string with format : QTYxID
            $ids = array_unique(explode('-', $idsInput));
            $names = array_unique(explode('¤', $namesInput));

            if (!empty($ids)) {
                $length = count($ids);

                for ($i = 0; $i < $length; $i++) {

                    if (!empty($ids[$i]) && !empty($names[$i])) {
                        list($packItems[$i]['pack_quantity'], $packItems[$i]['id']) = explode('x', $ids[$i]);
                        $explodedName = explode('x', $names[$i]);
                        $packItems[$i]['name'] = $explodedName[1];
                    }

                }

            }

        } else {

            if (is_array($education->packItems)) {
                $i = 0;

                foreach ($education->packItems as $packItem) {

                    $packItems[$i]['id'] = $packItem->id;
                    $packItems[$i]['pack_quantity'] = $packItem->pack_quantity;
                    $packItems[$i]['name'] = $packItem->name;
                    $packItems[$i]['reference'] = $packItem->reference;
                    $packItems[$i]['id_education_attribute'] = isset($packItem->id_pack_education_attribute) && $packItem->id_pack_education_attribute ? $packItem->id_pack_education_attribute : 0;
                    $cover = $packItem->id_pack_education_attribute ? Education::getDeclinaisonImageById($packItem->id_pack_education_attribute, $this->context->language->id) : Education::getCover($packItem->id);
                    $packItems[$i]['image'] = $this->context->link->getEducationImageLink($packItem->link_rewrite, $cover['id_image'], 'small_default');
                    // @todo: don't rely on 'home_default'
                    //$path_to_image = _PS_IMG_DIR_.'p/'.ImageEducation::getImgFolderStatic($cover['id_image']).(int)$cover['id_image'].'.jpg';
                    //$pack_items[$i]['image'] = ImageManager::thumbnail($path_to_image, 'pack_mini_'.$pack_item->id.'_'.$this->context->shop->id.'.jpg', 120);
                    $i++;
                }

            }

        }

        return $packItems;
    }

    public function initFormAttachments($obj) {

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        $data = $this->createTemplate($this->tpl_form);
        $data->assign('default_form_language', $this->default_form_language);

        if ((bool) $obj->id) {

            $filename = [];
            $isoTinyMce = $this->context->language->iso_code;
            $isoTinyMce = (file_exists(_PS_JS_DIR_ . 'tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');
            $hasDeclinaison = false;

            $declinaisons = $obj->getAttributeDeclinaisons($this->context->language->id);

            if (is_array($declinaisons) && count($declinaisons)) {
                $hasDeclinaison = true;

                foreach ($declinaisons as $declinaison) {
                    $detail = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('fileName')
                            ->from('education_programme')
                            ->where('`id_education` = ' . (int) $obj->id)
                            ->where('`id_education_attribute` = ' . (int) $declinaison['id_education_attribute'])
                    );

                    if (!empty($detail)) {

                        $filename[] = [
                            'link'                   => '<a download="programme' . str_replace(' ', '', $detail) . '.pdf" id="translationFile" class="btn btn-default" onClick="window.open(\'../fileProgram/programme' . str_replace(' ', '', $detail) . '.pdf\')">Visualise le fichier ' . $detail . '</a>',
                            'fileName'               => $detail,
                            'id_education'           => $obj->id,
                            'id_education_attribute' => $declinaison['id_education_attribute'],
                        ];
                    }

                }

            } else {

                $idProgramme = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    (new DbQuery())
                        ->select('id_education_programme')
                        ->from('education_programme')
                        ->where('`id_education` = ' . (int) $obj->id)
                );

                if ($idProgramme > 0) {
                    $filename[] = new EducationProgramme($idProgramme);
                }

            }

            $data->assign(
                [
                    'obj'                   => $obj,
                    'id_education'          => $obj->id,
                    'table'                 => $this->table,
                    'ad'                    => __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_),
                    'iso_tiny_mce'          => $isoTinyMce,
                    'languages'             => $this->_languages,
                    'jsonLanguages'         => Tools::jsonEncode($this->_languages),
                    'id_lang'               => $this->context->language->id,
                    'default_form_language' => (int) Configuration::get('PS_LANG_DEFAULT'),
                    'hasDeclinaison'        => $hasDeclinaison,
                    'declinaisons'          => $declinaisons,
                    'filename'              => $filename,
                    'link'                  => $this->context->link,
                    'bo_imgdir'             => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',

                ]
            );

        } else {
            $this->displayWarning($this->l('You must save this product before adding attachements.'));
        }

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function initFormInformations($education) {

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        $data = $this->createTemplate($this->tpl_form);

        $currency = $this->context->currency;

        $data->assign(
            [
                'languages'             => $this->_languages,
                'default_form_language' => $this->default_form_language,
                'currency'              => $currency,
            ]
        );
        $this->object = $education;
        //$this->display = 'edit';
        $data->assign('education_name_redirected', Education::getEducationName((int) $education->id_education_redirected, null, (int) $this->context->language->id));
        /*
                                            * Form for adding a virtual education like software, mp3, etc...
        */
        $educationDownload = new EducationDownload();

        if ($idEducationDownload = $educationDownload->getIdFromIdEducation($this->getFieldValue($education, 'id'))) {
            $educationDownload = new EducationDownload($idEducationDownload);
        }

        $education->{'educationDownload'}

        = $educationDownload;

        $educationProps = [];
        // global informations
        array_push($educationProps, 'reference', 'id_manufacturer');

        // specific / detailled information
        array_push(
            $educationProps,
            // physical education
            'width', 'height', 'weight', 'active',
            // virtual education
            'is_virtual', 'cache_default_attribute',

            'uploadable_files', 'text_fields'
        );
        // prices
        array_push(
            $educationProps,
            'price',
            'wholesale_price',
            'id_tax_rules_group',
            'unit_price_ratio',
            'on_sale',
            'unity',
            'minimum_quantity',
            'additional_shipping_cost',
            'available_now',
            'available_later',
            'available_date'
        );

        foreach ($educationProps as $prop) {
            $education->$prop = $this->getFieldValue($education, $prop);
        }

        $education->name['class'] = 'updateCurrentText';

        if (!$education->id || Configuration::get('PS_FORCE_FRIENDLY_PRODUCT')) {
            $education->name['class'] .= ' copy2friendlyUrl';
        }

        $images = ImageEducation::getImages($this->context->language->id, $education->id);

        if (is_array($images)) {

            foreach ($images as $k => $image) {
                $images[$k]['src'] = $this->context->link->getEducationImageLink($education->link_rewrite[$this->context->language->id], $education->id . '-' . $image['id_image_education'], ImageType::getFormatedName('small'));
            }

            $data->assign('images', $images);
        }

        $data->assign('imagesTypes', ImageType::getImagesTypes('education'));

        $data->assign('is_in_pack', (int) EducationPack::isPacked($education->id));

        $data->assign('education_type', $education->getEducationType());

        $data->assign('educationType', EducationType::getEducationType($this->default_form_language));

        // TinyMCE
        $isoTinyMce = $this->context->language->iso_code;
        $isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');
        $data->assign(
            [
                'ad'                          => dirname($_SERVER['PHP_SELF']),
                'iso_tiny_mce'                => $isoTinyMce,
                'id_lang'                     => $this->context->language->id,
                'education'                   => $education,
                'formatpacks'                 => FormatPack::getFormatPack($this->context->language->id),
                'token'                       => $this->token,
                'currency'                    => $currency,
                'link'                        => $this->context->link,
                'certifications'              => new PhenyxShopCollection('Certification'),
                'PS_PRODUCT_SHORT_DESC_LIMIT' => Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') ? Configuration::get('PS_PRODUCT_SHORT_DESC_LIMIT') : 400,
                'collectionPrerequis'         => new PhenyxShopCollection('EducationPrerequis'),
            ]
        );
        $data->assign($this->tpl_form_vars);

        $this->tpl_form_vars['education'] = $education;
        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    protected function getCarrierList() {

        $carrierList = Carrier::getCarriers($this->context->language->id, false, false, false, null, Carrier::ALL_CARRIERS);

        if ($education = $this->loadObject(true)) {
            /** @var Education $education */
            $carrierSelectedList = $education->getCarriers();

            foreach ($carrierList as &$carrier) {

                foreach ($carrierSelectedList as $carrierSelected) {

                    if ($carrierSelected['id_reference'] == $carrier['id_reference']) {
                        $carrier['selected'] = true;
                        continue;
                    }

                }

            }

        }

        return $carrierList;
    }

    public function ajaxProcessAddEducationImage() {

        static::$currentIndex = 'index.php?tab=AdminEducations';

        $education = new Education((int) Tools::getValue('id_education'));

        $legends = Tools::getValue('legend');

        if (!is_array($legends)) {
            $legends = (array) $legends;
        }

        if (!Validate::isLoadedObject($education)) {

            $files = [];
            $files[0]['error'] = Tools::displayError('Cannot add image because education creation failed.');
        }

        $imageUploader = new HelperImageUploader('file');
        $imageUploader->setAcceptTypes(['jpeg', 'gif', 'png', 'jpg'])->setMaxSize($this->max_image_size);
        $files = $imageUploader->process();

        foreach ($files as &$file) {
            $image = new ImageEducation();
            $image->id_education = (int) ($education->id);
            $image->position = ImageEducation::getHighestPosition($education->id) + 1;

            foreach ($legends as $key => $legend) {

                if (!empty($legend)) {
                    $image->legend[(int) $key] = $legend;
                }

            }

            if (!ImageEducation::getCover($image->id_education)) {
                $image->cover = 1;
            } else {
                $image->cover = 0;
            }

            if (($validate = $image->validateFieldsLang(false, true)) !== true) {
                $file['error'] = Tools::displayError($validate);
            }

            if (isset($file['error']) && (!is_numeric($file['error']) || $file['error'] != 0)) {
                continue;
            }

            if (!$image->add()) {
                $file['error'] = Tools::displayError('Error while creating additional image');
            } else {

                if (!$newPath = $image->getPathForCreation()) {

                    $file['error'] = Tools::displayError('An error occurred during new folder creation');
                    continue;
                }

                $error = 0;

                if (!ImageManager::resize($file['save_path'], $newPath . '.' . $image->image_format, null, null, 'jpg', false, $error)) {

                    switch ($error) {
                    case ImageManager::ERROR_FILE_NOT_EXIST:
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file does not exist anymore.');
                        break;

                    case ImageManager::ERROR_FILE_WIDTH:
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file width is 0px.');
                        break;

                    case ImageManager::ERROR_MEMORY_LIMIT:
                        $file['error'] = Tools::displayError('An error occurred while copying image, check your memory limit.');
                        break;

                    default:
                        $file['error'] = Tools::displayError('An error occurred while copying image.');
                        break;
                    }

                    continue;
                } else {
                    $imagesTypes = ImageType::getImagesTypes('education');
                    $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

                    foreach ($imagesTypes as $imageType) {

                        if (!ImageManager::resize($file['save_path'], $newPath . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                            $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                            continue;
                        }

                        if ($generateHighDpiImages) {

                            if (!ImageManager::resize($file['save_path'], $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                                $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                                continue;
                            }

                        }

                    }

                }

                unlink($file['save_path']);
                //Necesary to prevent hacking
                unset($file['save_path']);

                if (!$image->update()) {
                    $file['error'] = Tools::displayError('Error while updating status');
                    continue;
                }

                $file['status'] = 'ok';
                $file['id'] = $image->id;
                $file['position'] = $image->position;
                $file['cover'] = $image->cover;
                $file['legend'] = $image->legend;
                $file['path'] = $image->getExistingImgPath();

                @unlink(_PS_TMP_IMG_DIR_ . 'education_' . (int) $education->id . '.jpg');
                @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . (int) $education->id . '_' . $this->context->shop->id . '.jpg');
            }

        }

        $this->ajaxDie(json_encode([$imageUploader->getName() => $files]));
    }

    public function ajaxProcessAddNewEducationImage() {

        static::$currentIndex = 'index.php?tab=AdminEducations';

        $education = new Education((int) Tools::getValue('id_education'));

        $legends = Tools::getValue('legend');

        if (!is_array($legends)) {
            $legends = (array) $legends;
        }

        if (!Validate::isLoadedObject($education)) {

            $files = [];
            $files[0]['error'] = Tools::displayError('Cannot add image because education creation failed.');
        }

        $imageUploader = new HelperImageUploader('image');
        $imageUploader->setAcceptTypes(['jpeg', 'gif', 'png', 'jpg'])->setMaxSize($this->max_image_size);
        $files = $imageUploader->process();

        foreach ($files as &$file) {
            $image = new ImageEducation();
            $image->id_education = (int) ($education->id);
            $image->position = ImageEducation::getHighestPosition($education->id) + 1;

            foreach ($legends as $key => $legend) {

                if (!empty($legend)) {
                    $image->legend[(int) $key] = $legend;
                }

            }

            if (!ImageEducation::getCover($image->id_education)) {
                $image->cover = 1;
            } else {
                $image->cover = 0;
            }

            if (($validate = $image->validateFieldsLang(false, true)) !== true) {
                $file['error'] = Tools::displayError($validate);
            }

            if (isset($file['error']) && (!is_numeric($file['error']) || $file['error'] != 0)) {
                continue;
            }

            if (!$image->add()) {
                $file['error'] = Tools::displayError('Error while creating additional image');
            } else {

                if (!$newPath = $image->getPathForCreation()) {

                    $file['error'] = Tools::displayError('An error occurred during new folder creation');
                    continue;
                }

                $error = 0;

                if (!ImageManager::resize($file['save_path'], $newPath . '.' . $image->image_format, null, null, 'jpg', false, $error)) {

                    switch ($error) {
                    case ImageManager::ERROR_FILE_NOT_EXIST:
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file does not exist anymore.');
                        break;

                    case ImageManager::ERROR_FILE_WIDTH:
                        $file['error'] = Tools::displayError('An error occurred while copying image, the file width is 0px.');
                        break;

                    case ImageManager::ERROR_MEMORY_LIMIT:
                        $file['error'] = Tools::displayError('An error occurred while copying image, check your memory limit.');
                        break;

                    default:
                        $file['error'] = Tools::displayError('An error occurred while copying image.');
                        break;
                    }

                    continue;
                } else {
                    $imagesTypes = ImageType::getImagesTypes('education');
                    $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

                    foreach ($imagesTypes as $imageType) {

                        if (!ImageManager::resize($file['save_path'], $newPath . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format)) {
                            $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                            continue;
                        }

                        if ($generateHighDpiImages) {

                            if (!ImageManager::resize($file['save_path'], $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format)) {
                                $file['error'] = Tools::displayError('An error occurred while copying image:') . ' ' . stripslashes($imageType['name']);
                                continue;
                            }

                        }

                    }

                }

                unlink($file['save_path']);
                //Necesary to prevent hacking
                unset($file['save_path']);

                if (!$image->update()) {
                    $file['error'] = Tools::displayError('Error while updating status');
                    continue;
                }

                $file['status'] = 'ok';
                $file['id'] = $image->id;
                $file['position'] = $image->position;
                $file['cover'] = $image->cover;
                $file['legend'] = $image->legend;
                $file['path'] = $image->getExistingImgPath();

                @unlink(_PS_TMP_IMG_DIR_ . 'education_' . (int) $education->id . '.jpg');
                @unlink(_PS_TMP_IMG_DIR_ . 'education_mini_' . (int) $education->id . '_' . $this->context->shop->id . '.jpg');
            }

        }

        $result = [
            $imageUploader->getName() => $files,
        ];
        die(Tools::jsonEncode($result));
    }

    public function initFormImages($obj) {

        $data = $this->createTemplate($this->tpl_form);

        if ((bool) $obj->id) {

            if ($this->education_exists_in_shop) {
                $data->assign('education', $this->loadObject());

                $countImages = Db::getInstance()->getValue(
                    '
                    SELECT COUNT(id_education)
                    FROM ' . _DB_PREFIX_ . 'image_education
                    WHERE id_education = ' . (int) $obj->id
                );

                $images = ImageEducation::getImages($this->context->language->id, $obj->id);

                if (is_array($images) && count($images)) {

                    foreach ($images as $k => $image) {
                        $images[$k] = new ImageEducation($image['id_image_education']);
                    }

                }

                if ($this->context->shop->getContext() == Shop::CONTEXT_SHOP) {
                    $currentShopId = (int) $this->context->shop->id;
                } else {
                    $currentShopId = 0;
                }

                $languages = Language::getLanguages(true);
                $imageUploader = new HelperImageUploader('file');
                $imageUploader->setMultiple(!(Tools::getUserBrowser() == 'Apple Safari' && Tools::getUserPlatform() == 'Windows'))
                    ->setUseAjax(true)->setUrl($this->context->link->getAdminLink('AdminEducations') . '&ajax=1&id_education=' . (int) $obj->id . '&action=addEducationImage');

                $data->assign(
                    [
                        'education'           => $obj,
                        'countImages'         => $countImages,
                        'id_education'        => (int) Tools::getValue('id_education'),
                        'id_category_default' => null,
                        'images'              => $images,
                        'iso_lang'            => $languages[0]['iso_code'],
                        'token'               => $this->token,
                        'table'               => $this->table,
                        'max_image_size'      => $this->max_image_size / 1024 / 1024,
                        'currency'            => $this->context->currency,
                        'current_shop_id'     => $currentShopId,
                        'languages'           => $this->_languages,
                        'default_language'    => (int) Configuration::get('PS_LANG_DEFAULT'),
                        'image_uploader'      => $imageUploader->render(),
                        '_THEME_EDUC_DIR_'    => _THEME_EDUC_DIR_,
                        'link'                => $this->context->link,
                    ]
                );

                $type = ImageType::getByNameNType('%', 'educations', 'height');

                if (isset($type['name'])) {
                    $data->assign('imageType', $type['name']);
                } else {
                    $data->assign('imageType', ImageType::getFormatedName('small'));
                }

            } else {
                $this->displayWarning($this->l('You must save the education in this shop before adding images.'));
            }

        } else {
            $this->displayWarning($this->l('You must save this education before adding images.'));
        }

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function initFormDeclinaisons($obj) {

        return $this->initFormAttributes($obj);
    }

    public function ajaxProcessLaunchNewDeclinaison() {

        $id_education = Tools::getValue('id_education');

        $education = new Education($id_education);

        $data = $this->createTemplate('controllers/educations/adddeclinaison.tpl');

        $combos = Declinaison::getComboDeclinaison($idEducation, $this->context->language->id);

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        if (Validate::isLoadedObject($education)) {

            $attributeJs = [];

            $attributes = Attributes::getAttributes($this->context->language->id, true);

            foreach ($attributes as $k => $attribute) {
                $attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
            }

            foreach ($attributeJs as $k => $ajs) {
                natsort($attributeJs[$k]);
            }

            $currency = $this->context->currency;
            $jsAttribute = [];

            foreach ($attributeJs as $key => $values) {
                $attributeToPush = [];

                foreach ($values as $k => $value) {
                    $attributeToPush[$k] = $value;
                }

                $jsAttribute[$key] = $attributeToPush;
            }

            $data->assign('attributeJs', $attributeJs);
            $data->assign('jsAttribute', $jsAttribute);
            $data->assign('attributes_groups', AttributeGroup::getAttributesGroups($this->context->language->id));
            $data->assign('combos', $combos);

            $data->assign('currency', $currency);

            $images = ImageEducation::getImages($this->context->language->id, $education->id);

            $data->assign('tax_exclude_option', Tax::excludeTaxeOption());
            $data->assign('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT'));
            $isoTinyMce = $this->context->language->iso_code;
            $isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');
            $currency = $this->context->currency;

            $data->assign(
                [
                    'ad'                    => dirname($_SERVER['PHP_SELF']),
                    'iso_tiny_mce'          => $isoTinyMce,
                    'id_lang'               => $this->context->language->id,
                    'token'                 => $this->token,
                    'languages'             => $this->_languages,
                    'default_form_language' => $this->default_form_language,
                    'bo_imgdir'             => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
                    'collectionPrerequis'   => new PhenyxShopCollection('EducationPrerequis'),
                ]
            );

            $i = 0;
            $type = ImageType::getByNameNType('%', 'educations', 'height');

            if (isset($type['name'])) {
                $data->assign('imageType', $type['name']);
            } else {
                $data->assign('imageType', ImageType::getFormatedName('small'));
            }

            $data->assign('imageWidth', (isset($imageType['width']) ? (int) ($imageType['width']) : 64) + 25);

            foreach ($images as $k => $image) {
                $images[$k]['obj'] = new ImageEducation($image['id_image_education']);
                ++$i;
            }

            $data->assign('images', $images);

            $data->assign($this->tpl_form_vars);
            $data->assign(
                [

                    'combinationScript'  => $this->buildDeclinaisonScript($education->id),
                    'declinaisonFields'  => EmployeeConfiguration::get('EXPERT_EDUCATION_DECLINAISON_FIELDS'),
                    '_THEME_EDUC_DIR_'   => _THEME_EDUC_DIR_,
                    'education'          => $education,
                    'formatpacks'        => FormatPack::getFormatPack($this->context->language->id),
                    'token_generator'    => Tools::getAdminTokenLite('AdminAttributeGenerator'),
                    'combination_exists' => (count(AttributeGroup::getAttributesGroups($this->context->language->id)) > 0 && $education->hasAttributes()),
                ]
            );

        }

        $result = [
            'html' => $data->fetch(),
        ];
        die(Tools::jsonEncode($result));

    }

    public function initFormAttributes($education) {

        $data = $this->createTemplate($this->tpl_form);

        if (!$this->default_form_language) {
            $this->getLanguages();
        }

        if (Validate::isLoadedObject($education)) {

            if ($this->education_exists_in_shop) {

                $attributeJs = [];

                $attributes = Attributes::getAttributes($this->context->language->id, true);

                foreach ($attributes as $k => $attribute) {
                    $attributeJs[$attribute['id_attribute_group']][$attribute['id_attribute']] = $attribute['name'];
                }

                foreach ($attributeJs as $k => $ajs) {
                    natsort($attributeJs[$k]);
                }

                $currency = $this->context->currency;
                $jsAttribute = [];

                foreach ($attributeJs as $key => $values) {
                    $attributeToPush = [];

                    foreach ($values as $k => $value) {
                        $attributeToPush[$k] = $value;
                    }

                    $jsAttribute[$key] = $attributeToPush;
                }

                $data->assign('attributeJs', $attributeJs);
                $data->assign('jsAttribute', $jsAttribute);
                $data->assign('attributes_groups', AttributeGroup::getAttributesGroups($this->context->language->id));

                $data->assign('currency', $currency);

                $images = ImageEducation::getImages($this->context->language->id, $education->id);

                $data->assign('tax_exclude_option', Tax::excludeTaxeOption());
                $data->assign('ps_weight_unit', Configuration::get('PS_WEIGHT_UNIT'));
                $isoTinyMce = $this->context->language->iso_code;
                $isoTinyMce = (file_exists(_PS_ROOT_DIR_ . '/js/tiny_mce/langs/' . $isoTinyMce . '.js') ? $isoTinyMce : 'en');
                $currency = $this->context->currency;

                $data->assign(
                    [
                        'ad'                    => dirname($_SERVER['PHP_SELF']),
                        'iso_tiny_mce'          => $isoTinyMce,
                        'id_lang'               => $this->context->language->id,
                        'token'                 => $this->token,
                        'languages'             => $this->_languages,
                        'default_form_language' => $this->default_form_language,
                        'link'                  => $this->context->link,

                    ]
                );

                $i = 0;
                $type = ImageType::getByNameNType('%', 'educations', 'height');

                if (isset($type['name'])) {
                    $data->assign('imageType', $type['name']);
                } else {
                    $data->assign('imageType', ImageType::getFormatedName('small'));
                }

                $data->assign('imageWidth', (isset($imageType['width']) ? (int) ($imageType['width']) : 64) + 25);

                foreach ($images as $k => $image) {
                    $images[$k]['obj'] = new ImageEducation($image['id_image_education']);
                    ++$i;
                }

                $data->assign('images', $images);

                $data->assign($this->tpl_form_vars);
                $declinaisons = $education->getAttributeDeclinaisons($this->context->language->id);
                $programmes = Declinaison::getProgrammeCollection($education->id, $this->context->language->id);
                $prerequis = Declinaison::getPrerequisCollection($education->id);

                $filename = [];

                foreach ($declinaisons as $declinaison) {
                    $idProgramme = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                        (new DbQuery())
                            ->select('id_education_programme')
                            ->from('education_programme')
                            ->where('`id_education` = ' . (int) $education->id)
                            ->where('`id_education_attribute` = ' . (int) $declinaison['id_education_attribute'])
                    );

                    if ($idProgramme > 0) {
                        $filename[] = new EducationProgramme($idProgramme);
                    }

                }

                $data->assign(
                    [

                        'combinationScript'   => $this->buildDeclinaisonScript($education->id),
                        'declinaisons'        => $declinaisons,
                        'declinaisonFields'   => EmployeeConfiguration::get('EXPERT_EDUCATION_DECLINAISON_FIELDS'),
                        '_THEME_EDUC_DIR_'    => _THEME_EDUC_DIR_,
                        'education'           => $education,
                        'token_generator'     => Tools::getAdminTokenLite('AdminAttributeGenerator'),
                        'combination_exists'  => (count(AttributeGroup::getAttributesGroups($this->context->language->id)) > 0 && $education->hasAttributes()),
                        'programmes'          => Tools::jsonEncode($programmes),
                        'filename'            => $filename,
                        'bo_imgdir'           => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
                        'has_declinaisons'    => $this->object->hasAttributes(),
                        'collectionPrerequis' => new PhenyxShopCollection('EducationPrerequis'),
                        'prerequis'           => Tools::jsonEncode($prerequis),
                    ]
                );

            } else {
                $this->displayWarning($this->l('You must save the education in this shop before adding declinaisons.'));
            }

        } else {
            $data->assign('education', $education);
            $data->assign('_THEME_EDUC_DIR_', _THEME_EDUC_DIR_);
            $this->displayWarning($this->l('You must save this education before adding declinaisons.'));
        }

        $this->tpl_form_vars['custom_form'] = $data->fetch();
    }

    public function initFormModules($obj) {

        $idModule = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_module`')
                ->from('module')
                ->where('`name` = \'' . pSQL($this->tab_display_module) . '\'')
        );
        $this->tpl_form_vars['custom_form'] = Hook::exec('displayAdminEducationsExtra', [], (int) $idModule);
    }

    public function getL($key) {

        $trad = [
            'Default category:'                                                   => $this->l('Default category'),
            'Catalog:'                                                            => $this->l('Catalog'),
            'Consider changing the default category.'                             => $this->l('Consider changing the default category.'),
            'ID'                                                                  => $this->l('ID'),
            'Name'                                                                => $this->l('Name'),
            'Mark all checkbox(es) of categories in which education is to appear' => $this->l('Mark the checkbox of each categories in which this education will appear.'),
        ];

        return $trad[$key];
    }

    public function ajaxProcessCheckEducationName() {

        if ($this->tabAccess['view'] === '1') {
            $search = Tools::getValue('q');
            $id_lang = Tools::getValue('id_lang');
            $limit = Tools::getValue('limit');

            if ($this->context->shop->getContext() != Shop::CONTEXT_SHOP) {
                $result = false;
            } else {
                $result = Db::getInstance()->executeS(
                    '
                    SELECT DISTINCT pl.`name`, p.`id_education`, pl.`id_shop`
                    FROM `' . _DB_PREFIX_ . 'education` p
                    LEFT JOIN `' . _DB_PREFIX_ . 'education_shop` ps ON (ps.id_education = p.id_education AND ps.id_shop =' . (int) $this->context->shop->id . ')
                    LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` pl
                        ON (pl.`id_education` = p.`id_education` AND pl.`id_lang` = ' . (int) $id_lang . ')
                    WHERE pl.`name` LIKE "%' . pSQL($search) . '%" AND ps.id_education IS NULL
                    GROUP BY pl.`id_education`
                    LIMIT ' . (int) $limit
                );
            }

            $this->ajaxDie(json_encode($result));
        }

    }

    public function ajaxProcessUpdatePositions() {

        if ($this->tabAccess['edit'] === '1') {
            $way = (int) (Tools::getValue('way'));
            $id_education = (int) Tools::getValue('id_education');
            $id_category = (int) Tools::getValue('id_category');
            $positions = Tools::getValue('education');
            $page = (int) Tools::getValue('page');
            $selected_pagination = (int) Tools::getValue('selected_pagination');

            if (is_array($positions)) {

                foreach ($positions as $position => $value) {
                    $pos = explode('_', $value);

                    if ((isset($pos[1]) && isset($pos[2])) && ($pos[1] == $id_category && (int) $pos[2] === $id_education)) {

                        if ($page > 1) {
                            $position = $position + (($page - 1) * $selected_pagination);
                        }

                        if ($education = new Education((int) $pos[2])) {

                            if (isset($position) && $education->updatePosition($way, $position)) {
                                $category = new Category((int) $id_category);

                                if (Validate::isLoadedObject($category)) {
                                    hook::Exec('categoryUpdate', ['category' => $category]);
                                }

                                echo 'ok position ' . (int) $position . ' for education ' . (int) $pos[2] . "\r\n";
                            } else {
                                echo '{"hasError" : true, "errors" : "Can not update education ' . (int) $id_education . ' to position ' . (int) $position . ' "}';
                            }

                        } else {
                            echo '{"hasError" : true, "errors" : "This education (' . (int) $id_education . ') can t be loaded"}';
                        }

                        break;
                    }

                }

            }

        }

    }

    public function ajaxProcessPublishEducation() {

        if ($this->tabAccess['edit'] === '1') {

            if ($id_education = (int) Tools::getValue('id_education')) {
                $bo_education_url = dirname($_SERVER['PHP_SELF']) . '/index.php?tab=AdminEducations&id_education=' . $id_education . '&updateeducation&token=' . $this->token;

                if (Tools::getValue('redirect')) {
                    die($bo_education_url);
                }

                $education = new Education((int) $id_education);

                if (!Validate::isLoadedObject($education)) {
                    die('error: invalid id');
                }

                $education->active = 1;

                if ($education->save()) {
                    die($bo_education_url);
                } else {
                    die('error: saving');
                }

            }

        }

    }

    public function displayPreviewLink($id, $token = null, $name = null) {

        $tpl = $this->createTemplate('helpers/list/list_action_preview.tpl');

        if (!array_key_exists('Bad SQL query', static::$cache_lang)) {
            static::$cache_lang['Preview'] = $this->l('Preview', 'Helper');
        }

        $tpl->assign(
            [
                'href'   => $this->getPreviewUrl(new Education((int) $id)),
                'action' => static::$cache_lang['Preview'],
            ]
        );

        return $tpl->fetch();
    }

    protected function processBulkDelete() {

        if ($this->tabAccess['delete'] === '1') {

            if (is_array($this->boxes) && !empty($this->boxes)) {
                $object = new $this->className();

                if (isset($object->noZeroObject) &&
                    // Check if all object will be deleted
                    (count(call_user_func([$this->className, $object->noZeroObject])) <= 1 || count($_POST[$this->table . 'Box']) == count(call_user_func([$this->className, $object->noZeroObject])))
                ) {
                    $this->errors[] = Tools::displayError('You need at least one object.') . ' <b>' . $this->table . '</b><br />' . Tools::displayError('You cannot delete all of the items.');
                } else {
                    $success = 1;
                    $educations = Tools::getValue($this->table . 'Box');

                    if (is_array($educations) && ($count = count($educations))) {
                        // Deleting educations can be quite long on a cheap server. Let's say 1.5 seconds by education (I've seen it!).

                        if (intval(ini_get('max_execution_time')) < round($count * 1.5)) {
                            ini_set('max_execution_time', round($count * 1.5));
                        }

                        if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT')) {
                            $stockManager = StockManagerFactory::getManager();
                        }

                        foreach ($educations as $id_education) {
                            $education = new Education((int) $id_education);

                            if (!count($this->errors)) {

                                if ($education->delete()) {
                                    Logger::addLog(sprintf($this->l('%s deletion', 'AdminTab', false, false), $this->className), 1, null, $this->className, (int) $education->id, true, (int) $this->context->employee->id);
                                } else {
                                    $success = false;
                                }

                            } else {
                                $success = 0;
                            }

                        }

                    }

                    if ($success) {
                        $id_category = (int) Tools::getValue('id_category');
                        $category_url = empty($id_category) ? '' : '&id_category=' . (int) $id_category;
                        $this->redirect_after = static::$currentIndex . '&conf=2&token=' . $this->token . $category_url;
                    } else {
                        $this->errors[] = Tools::displayError('An error occurred while deleting this selection.');
                    }

                }

            } else {
                $this->errors[] = Tools::displayError('You must select at least one element to delete.');
            }

        } else {
            $this->errors[] = Tools::displayError('You do not have permission to delete this.');
        }

    }

    protected function updateAssoShop($idObject) {

        return;
    }

    protected function _getFinalPrice($specificPrice, $educationPrice, $taxRate) {

        return $this->object->getPrice(false, $specificPrice['id_education_attribute'], 2);
    }

    protected function _displayUnavailableEducationWarning() {

        $content = '<div class="alert">
            <span>' . $this->l('Your education will be saved as a draft.') . '</span>
                <a href="#" class="btn btn-default pull-right" onclick="submitAddEducationAndPreview()" ><i class="icon-external-link-sign"></i> ' . $this->l('Save and preview') . '</a>
                <input type="hidden" name="fakeSubmitAddEducationAndPreview" id="fakeSubmitAddEducationAndPreview" />
            </div>';
        $this->tpl_form_vars['warning_unavailable_education'] = $content;
    }

    public function ajaxProcessAutoCompleteSearch() {

        $keyword = Tools::getValue('keyword', false);

        if (!$keyword || $keyword == '' || Tools::strlen($keyword) < 1) {
            exit();
        }

        if ($pos = strpos($keyword, ' (ref:')) {
            $keyword = Tools::substr($keyword, 0, $pos);
        }

        $exclude_ids = Tools::getValue('excludeIds', false);

        if ($exclude_ids && $exclude_ids != 'NaN') {
            $exclude_ids = implode(',', array_map('intval', explode(',', $exclude_ids)));
        } else {
            $exclude_ids = '';
        }

        // Excluding downloadable educations from packs because download from pack is not supported
        $exclude_virtuals = (bool) Tools::getValue('excludeVirtuals', false);
        $exclude_packs = (bool) Tools::getValue('exclude_packs', false);
        $acc = (bool) Tools::isSubmit('excludeIds');

        if ($items && $acc) {
            $results = [];
            header('Content-Type: application/json');

            foreach ($items as $item) {
                $results[] = [
                    'education' => trim($item['name']) . (!empty($item['reference']) ? ' (ref: ' . $item['reference'] . ')' : ''),
                    'id'        => (int) $item['id_education']];

            }

            die(Tools::jsonEncode(array_values($results)));
        } else

        if ($items) {
            // packs
            $results = [];

            foreach ($items as $item) {
                $education = [
                    'id'    => (int) $item['id_education'],
                    'name'  => $item['name'],
                    'ref'   => (!empty($item['reference']) ? $item['reference'] : ''),
                    'image' => str_replace('http://', Tools::getShopProtocol(), Context::getContext()->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], ImageType::getFormatedName('home'))),
                ];
                array_push($results, $education);
            }

            die($results);
        } else {
            Tools::jsonEncode(new stdClass());
        }

    }

    public function ajaxProcessAutoCompletePack() {

        $query = Tools::getValue('search', false);

        if (!$query or $query == '' or strlen($query) < 1) {
            die();
        }

        if ($pos = strpos($query, ' (ref:')) {
            $query = substr($query, 0, $pos);
        }

        $exclude = [];
        $excludeIds = Tools::getValue('excludeIds', false);

        if ($excludeIds && $excludeIds != 'NaN') {

            foreach ($excludeIds as $key => $values) {
                $exclude[] = [
                    'id'                     => $values[0],
                    'id_education_attribute' => $values[1],
                ];
            }

            $excludeIds = implode(',', array_map('intval', explode(',', $excludeIds)));

        } else {
            $excludeIds = '';
            $excludePackItself = Tools::getValue('packItself', false);
        }

        $exclude_packs = (bool) Tools::getValue('exclude_packs', true);

        $request = new DbQuery();
        $request->select(' p.`id_education`, pl.`link_rewrite`, p.`reference`, pl.`name`, image_education.`id_image_education` id_image, il.`legend`, p.`cache_default_attribute`');
        $request->from('education', 'p');
        $request->leftJoin('education_lang', 'pl', 'pl.id_education = p.id_education AND pl.id_lang = ' . (int) $this->context->language->id);
        $request->leftJoin('image_education', 'image_education', 'image_education.`id_education` = p.`id_education` AND image_education.cover=1 ');
        $request->leftJoin('image_education_lang', 'il', 'image_education.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $this->context->language->id);
        $request->where('(pl.name LIKE \'%' . pSQL($query) . '%\' OR p.reference LIKE \'%' . pSQL($query) . '%\')');

        if (!empty($excludePackItself)) {
            $request->where('p.id_education <> ' . $excludePackItself);
        }

        $request->where('p.cache_is_pack IS NULL OR p.cache_is_pack = 0 ');
        $request->groupBy('p.id_education');

        $items = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($request);

        if ($items) {
            // packs
            $results = [];

            foreach ($items as $item) {

                if ($item['cache_default_attribute']) {
                    $request = new DbQuery();
                    $request->select('pa.`id_education_attribute`, pa.`reference`, ag.`id_attribute_group`, pai.`id_image`, agl.`name` AS group_name, al.`name` AS attribute_name, a.`id_attribute`');
                    $request->from('education_attribute', 'pa');
                    $request->leftJoin('education_attribute_combination', 'pac', 'pac.`id_education_attribute` = pa.`id_education_attribute`');
                    $request->leftJoin('attribute', 'a', 'a.`id_attribute` = pac.`id_attribute`');
                    $request->leftJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`');
                    $request->leftJoin('attribute_lang', 'al', 'a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $this->context->language->id);
                    $request->leftJoin('attribute_group_lang', 'agl', 'ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $this->context->language->id);
                    $request->leftJoin('education_attribute_image', 'pai', 'pai.`id_education_attribute` = pa.`id_education_attribute`');
                    $request->where('pa.`id_education` = ' . (int) $item['id_education']);
                    $request->groupBy('pa.`id_education_attribute`, ag.`id_attribute_group`');
                    $request->orderBy('pa.`id_education_attribute`');

                    $combinations = Db::getInstance()->executeS($request);

                    if (!empty($combinations)) {

                        foreach ($combinations as $k => $combination) {

                            if ((array_search($item['id_education'], array_column($exclude, 'id')) !== false) &&
                                (array_search($combination['id_education_attribute'], array_column($exclude, 'id_education_attribute')) !== false)) {
                                continue;
                            }

                            $results[$combination['id_education_attribute']]['id'] = $item['id_education'];
                            $results[$combination['id_education_attribute']]['id_education_attribute'] = $combination['id_education_attribute'];
                            !empty($results[$combination['id_education_attribute']]['name']) ? $results[$combination['id_education_attribute']]['name'] .= ' ' . $combination['group_name'] . '-' . $combination['attribute_name'] : $results[$combination['id_education_attribute']]['name'] = $item['name'] . ' ' . $combination['group_name'] . '-' . $combination['attribute_name'];

                            if (!empty($combination['reference'])) {
                                $results[$combination['id_education_attribute']]['ref'] = $combination['reference'];
                            } else {
                                $results[$combination['id_education_attribute']]['ref'] = !empty($item['reference']) ? $item['reference'] : '';
                            }

                            if (empty($results[$combination['id_education_attribute']]['image'])) {
                                $results[$combination['id_education_attribute']]['image'] = str_replace('http://', Tools::getShopProtocol(), $this->context->link->getEducationImageLink($item['link_rewrite'], $combination['id_image'], 'small_default'));
                            }

                        }

                    } else {
                        $education = [
                            'id'    => (int) ($item['id_education']),
                            'name'  => $item['name'],
                            'ref'   => (!empty($item['reference']) ? $item['reference'] : ''),
                            'image' => str_replace('http://', Tools::getShopProtocol(), $this->context->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], 'samll_default')),
                        ];

                    }

                } else {

                    if ((array_search($item['id_education'], array_column($exclude, 'id')) !== false) &&
                        (array_search(0, array_column($exclude, 'id_education_attribute')) !== false)) {
                        continue;
                    }

                    $education = [
                        'id'    => (int) ($item['id_education']),
                        'name'  => $item['name'],
                        'ref'   => (!empty($item['reference']) ? $item['reference'] : ''),
                        'image' => str_replace('http://', Tools::getShopProtocol(), $this->context->link->getEducationImageLink($item['link_rewrite'], $item['id_image'], 'small_default')),
                    ];
                    array_push($results, $education);
                }

            }

            $results = array_values($results);

            die(Tools::jsonEncode($results));

        } else {
            json_encode(new stdClass);
        }

    }

    public function ajaxProcessSaveProduct() {

        $id_education = Tools::getValue('id_education');

        if ($id_education > 0) {
            $education = new Education($id_education);

            if (Validate::isLoadedObject($education)) {

                foreach ($_POST as $key => $value) {

                    if (property_exists($education, $key) && $key != 'id_education') {
                        $education->{$key}

                        = $value;
                    }

                }

                $classVars = get_class_vars(get_class($education));
                $fields = [];

                if (isset($classVars['definition']['fields'])) {
                    $fields = $classVars['definition']['fields'];
                }

                foreach ($fields as $field => $params) {

                    if (array_key_exists('lang', $params) && $params['lang']) {

                        foreach (Language::getIDs(false) as $idLang) {

                            if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                                if (!isset($education->{$field}) || !is_array($education->{$field})) {
                                    $education->{$field}

                                    = [];
                                }

                                $education->{$field}

                                [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                            }

                        }

                    }

                }

                $result = $education->update();

                if ($result) {

                    if ($education->cache_default_attribute == 0 && $education->id_education_link > 0) {
                        $link = new EducationLink($education->id_education_link);
                        $link->id_education = $education->id;
                        $link->id_education_attribute = 0;
                        $link->edof_link = Tools::getValue('edof_link');
                        $link->update();
                    } else
                    if ($education->cache_default_attribute == 0 && !$education->id_education_link) {
                        $link = new EducationLink();
                        $link->id_education = $education->id;
                        $link->id_education_attribute = 0;
                        $link->edof_link = Tools::getValue('edof_link');
                        $link->add();
                    }

                    $return = [
                        'success' => true,
                        'message' => $this->l('Cette formation a été mise à jour avec succès'),
                    ];
                } else {
                    $return = [
                        'success' => false,
                        'message' => $this->l('Une erreur s’est produite en essayant de mettre à jour cette formation'),
                    ];
                }

            } else {
                $return = [
                    'success' => false,
                    'message' => $this->l('Une erreur s’est produite en essayant de de charger cette formation'),
                ];
            }

        } else {

            $education = new Education();

            foreach ($_POST as $key => $value) {

                if (property_exists($education, $key) && $key != 'id_education') {
                    $education->{$key}
                    = $value;
                }

            }

            $classVars = get_class_vars(get_class($education));
            $fields = [];

            if (isset($classVars['definition']['fields'])) {
                $fields = $classVars['definition']['fields'];
            }

            foreach ($fields as $field => $params) {

                if (array_key_exists('lang', $params) && $params['lang']) {

                    foreach (Language::getIDs(false) as $idLang) {

                        if (Tools::isSubmit($field . '_' . (int) $idLang)) {

                            if (!isset($education->{$field}) || !is_array($education->{$field})) {
                                $education->{$field}
                                = [];
                            }

                            $education->{$field}
                            [(int) $idLang] = Tools::getValue($field . '_' . (int) $idLang);
                        }

                    }

                }

            }

            $result = $education->add();

            if ($result) {

                $link = new EducationLink();
                $link->id_education = $education->id;
                $link->id_education_attribute = 0;
                $link->edof_link = Tools::getValue('edof_link');
                $link->add();

                $return = [
                    'success'      => true,
                    'message'      => $this->l('Cette formation a été crée avec succès'),
                    'id_education' => $education->id,
                ];
            } else {
                $return = [
                    'success' => false,
                    'message' => $this->l('Une erreur s’est produite lors de la création de cette formation'),
                ];
            }

        }

        die(Tools::jsonEncode($return));

    }

    public function ajaxProcessDeleteEducation() {

        $file = fopen("testProcessDeleteEducation.txt", "w");
        $idEducation = Tools::getValue('idEducation');
        fwrite($file, $idEducation . php8eol);

        $isUsed = Education::isUsedEducation($idEducation);

        if ($isUsed > 0) {
            $return = [
                'success' => false,
                'message' => $this->l('Cette formation a déjà été utilisée, vous ne pouvez pas la supprimer'),
            ];

        } else {
            $education = new Education($idEducation);
            $education->delete();
            $return = [
                'success' => true,
                'message' => $this->l('La formation a été supprimée avec succes'),
            ];
        }

        die(Tools::jsonEncode($return));
    }

    public function ajaxProcessDeleteEducationAttribute() {

        if ($this->tabAccess['delete'] === '1') {
            $idEducation = (int) Tools::getValue('id_education');
            $idEducationAttribute = (int) Tools::getValue('id_education_attribute');

        } else {
            $json = [
                'status'  => 'error',
                'message' => $this->l('You do not have permission to delete this.'),
            ];
        }

        $this->ajaxDie(json_encode($json));
    }

    public function ajaxProcessGenerateProgrammeFile() {

        $idEducation = Tools::getValue('idEducation');
        $education = new Education($idEducation);
        $version = Tools::getValue('version');

        $pdfUploader = new HelperUploader('programUrl');
        $idProgramme = EducationProgramme::getProgrammeIdByIdEducation($education->id);

        $files = $pdfUploader->process();

        if (is_array($files) && count($files)) {

            foreach ($files as $image) {
                $destinationFile = _PS_PROGRAM_DIR_ . $education->name[$this->context->language->id] . ' ' . $version . '.pdf';
                $fileName = $education->name[$this->context->language->id] . ' ' . $version . '.pdf';

                if (copy($image['save_path'], $destinationFile)) {

                    if ($idProgramme > 0) {
                        $educationProgramme = new EducationProgramme($idProgramme);
                        $educationProgramme->fileName = $fileName;
                        $educationProgramme->version = $version;
                        $educationProgramme->update();
                    } else {
                        $educationProgramme = new EducationProgramme();
                        $educationProgramme->id_education = $education->id;
                        $educationProgramme->fileName = $fileName;
                        $educationProgramme->version = $version;
                        $educationProgramme->add();
                    }

                    $link = '<a download="' . $fileName . '" id="translationFile" class="btn btn-default" onClick="window.open(\'../fileProgram/' . $fileName . '\')">Visualise le fichier ' . $fileName . '</a>';
                    $html = ' <tr id="' . $educationProgramme->id . '"><td>' . $education->name[$this->context->language->id] . '</td><td>' . $link . '</td></tr>';
                    $result = [
                        'success'     => true,
                        'html'        => $html,
                        'idEducation' => $educationProgramme->id,
                    ];
                } else {
                    $result = [
                        'success' => false,
                        'message' => 'Problème copy file',
                    ];
                }

            }

        } else {
            $result = [
                'success' => false,
                'message' => 'Problème upload',
            ];
        }

        die(Tools::jsonEncode($result));

    }

    public function ajaxProcessGenerateDeclinaisonProgrammeFile() {

        $idEducation = Tools::getValue('idEducation');
        $idAttribute = Tools::getValue('idAttribute');
        $version = Tools::getValue('version');
        $education = new Education($idEducation);
        $declinaison = new Declinaison($idAttribute);

        $idProgramme = EducationProgramme::getProgrammeIdByIdDeclinaison($education->id, $declinaison->id);

        if ($declinaison->is_combo) {

            if (is_array($declinaison->parents)) {
                $fileName = [];

                foreach ($declinaison->parents as $idDeclinaison) {
                    $fileName[] = EducationProgramme::getFileName($idEducation, $idDeclinaison);
                }

                if (count($fileName)) {
                    $name = $declinaison->name[$this->context->language->id] . ' ' . $version . '.pdf';
                    $pdf = new \Ephenyxshop\PDFMerger\PDFMerger;

                    foreach ($fileName as $file) {
                        $pdf->addPDF(_PS_PROGRAM_DIR_ . $file);
                    }

                    $pdf->merge('file', _PS_PROGRAM_DIR_ . $name);

                    if ($idProgramme > 0) {
                        $educationProgramme = new EducationProgramme($idProgramme);
                        $educationProgramme->fileName = $name;
                        $educationProgramme->version = $version;
                        $educationProgramme->update();
                    } else {
                        $educationProgramme = new EducationProgramme();
                        $educationProgramme->id_education = $education->id;
                        $educationProgramme->id_education_attribute = $declinaison->id;
                        $educationProgramme->fileName = $name;
                        $educationProgramme->version = $version;
                        $educationProgramme->add();
                    }
					$declinaison->update();
                    $link = '<a download="' . $name . '" id="translationFile" class="btn btn-default" onClick="window.open(\'../fileProgram/' . $name . '\')">Visualise le fichier ' . $name . '</a>';
                    $html = ' <tr id="' . $educationProgramme->id . '"><td>' . $declinaison->name[$this->context->language->id] . '</td><td>' . $link . '</td></tr>';
                    $result = [
                        'success'     => true,
                        'html'        => $html,
                        'idEducation' => $educationProgramme->id,
                    ];
                }

            }

        } else {
            $pdfUploader = new HelperUploader('declinaisonUrl');

            $files = $pdfUploader->process();

            if (is_array($files) && count($files)) {

                foreach ($files as $image) {
                    $destinationFile = _PS_PROGRAM_DIR_ . $declinaison->name[$this->context->language->id] . ' ' . $version . '.pdf';
                    $fileName = $declinaison->name[$this->context->language->id] . ' ' . $version . '.pdf';

                    if (copy($image['save_path'], $destinationFile)) {

                        if ($idProgramme > 0) {
                            $educationProgramme = new EducationProgramme($idProgramme);
                            $educationProgramme->fileName = $fileName;
                            $educationProgramme->version = $version;
                            $educationProgramme->update();
                        } else {
                            $educationProgramme = new EducationProgramme();
                            $educationProgramme->id_education = $education->id;
                            $educationProgramme->id_education_attribute = $declinaison->id;
                            $educationProgramme->fileName = $fileName;
                            $educationProgramme->version = $version;
                            $educationProgramme->add();
                        }

                        $link = '<a download="' . $fileName . '" id="translationFile" class="btn btn-default" onClick="window.open(\'../fileProgram/' . $fileName . '\')">Visualise le fichier ' . $fileName . '</a>';
                        $html = ' <tr id="' . $educationProgramme->id . '"><td>' . $declinaison->name[$this->context->language->id] . '</td><td>' . $link . '</td></tr>';
                        $result = [
                            'success'     => true,
                            'html'        => $html,
                            'idEducation' => $educationProgramme->id,
                        ];
                    } else {
                        $result = [
                            'success' => false,
                            'message' => 'Problème copy file',
                        ];
                    }

                }

            } else {
                $result = [
                    'success' => false,
                    'message' => 'Problème upload',
                ];
            }

        }

        die(Tools::jsonEncode($result));
    }

    public function ajaxProcessViewProgramme() {

        $file = fopen("testProcessViewProgramme.html", "w");
        $idEducation = Tools::getValue('idEducation');

        $education = new Education($idEducation);
        $programme = $education->programme[$this->context->language->id];
        $idShop = $this->context->shop->id;

        if (Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop))) {
            $logo = _PS_IMG_DIR_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop);
            $logo_path = _PS_IMG_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop);
        } else

        if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
            $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
            $logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
        }

        $width = 0;
        $height = 0;

        if (!empty($logo_path)) {
            list($width, $height) = getimagesize(_PS_ROOT_DIR_ . $logo_path);
        }

        $maximumHeight = 100;

        if ($height > $maximumHeight) {
            $ratio = $maximumHeight / $height;
            $height *= $ratio;
            $width *= $ratio;
        }

        $mpdf = new \Mpdf\Mpdf([
            'margin_left'   => 10,
            'margin_right'  => 10,
            'margin_top'    => 40,
            'margin_bottom' => 30,
            'margin_header' => 10,
            'margin_footer' => 10,
        ]);
        fwrite($file, $education->programme[$this->context->language->id]);

        $data = $this->createTemplate('controllers/educations/pdf/headertemplate.tpl');

        $data->assign(
            [
                'logo_path' => $logo_path,
            ]
        );
        $mpdf->SetHTMLHeader($data->fetch());

        $data = $this->createTemplate('controllers/educations/pdf/footertemplate.tpl');

        $data->assign(
            [
                'version' => $education->versionProgram,
                'company' => $this->context->company,
                'tags'    => Configuration::get('EPH_FOOTER_PROGRAM'),
                'contact' => Configuration::get('EPH_TAG_PROGRAM'),
            ]
        );
        $mpdf->SetHTMLFooter($data->fetch(), 'O');

        $data = $this->createTemplate('controllers/educations/pdf/pdf.css.tpl');

        $stylesheet = $data->fetch();

        $filePath = _PS_PROGRAM_DIR_;
        $fileName = "Programme Formation " . $education->name[$this->context->language->id] . '.pdf';

        $mpdf->WriteHTML($stylesheet, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($programme);

        $mpdf->Output($filePath . $fileName, 'F');

        $response = [
            'fileExport' => '../fileProgram/' . $fileName,
        ];
        die(Tools::jsonEncode($response));

    }

    public function ajaxProcessUploadProgramFile() {

        $name = 'file';

        $id_education = Tools::getValue('id_education');
        $id_education_attribute = Tools::getValue('id_education_attribute');
        $fileName = 'programme' . str_replace(' ', '', Tools::getValue('formationName')) . '.pdf';
        $formationName = Tools::getValue('formationName');

        if (isset($_FILES[$name]['name'])) {

            $Upload = [];
            $Upload['content'] = Tools::file_get_contents($_FILES[$name]['tmp_name']);
            $Upload['name'] = $_FILES[$name]['name'];
            $Upload['mime'] = $_FILES[$name]['type'];
            $sourcePath = $_FILES[$name]['tmp_name'];
            $uploadfile = _PS_PROGRAM_DIR_ . $fileName;
            move_uploaded_file($sourcePath, $uploadfile);

            $sql = 'INSERT INTO `eph_education_programme` VALUES ([value-1],[value-2],[value-3])';
            Db::getInstance(_PS_USE_SQL_SLAVE_)->execute(
                (new DbQuery())
                    ->type('DELETE')
                    ->from('education_programme')
                    ->where('`id_education` = ' . (int) $id_education)
                    ->where('`id_education_attribute` = ' . (int) $id_education_attribute)
            );
            $sqlValues = [
                'id_education'           => (int) $id_education,
                'id_education_attribute' => (int) $id_education_attribute,
                'fileName'               => pSQL($formationName),
            ];

            $result = Db::getInstance()->insert('education_programme', $sqlValues);

            $link = '<a download="' . $fileName . '" id="translationFile" class="btn btn-default" onClick="window.open(\'../fileProgram/' . $fileName . '\')">Visualise le fichier ' . $education->name[$this->context->language->id] . '</a>';
            $html = ' <tr><td>' . $education->name[$this->context->language->id] . '</td><td>' . $link . '</td></tr>';

            $result = [
                'success' => true,
                'html'    => $html,
            ];

            die(Tools::jsonEncode($result));
        }

    }

}
