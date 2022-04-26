<?php

/**
 * Class AdminCustomerThreadsControllerCore
 *
 * @since 1.8.1.0
 */
class AdminCustomerThreadsControllerCore extends AdminController {

    /**
     * AdminCustomerThreadsControllerCore constructor.
     *
     * @since 1.8.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'customer_thread';
        $this->className = 'CustomerThread';
        $this->publicName = $this->l('Customer Thread');
        $this->lang = false;

        $this->fields_options = [
            'contact' => [
                'title'  => $this->l('Contact options'),
                'fields' => [
                    'PS_CUSTOMER_SERVICE_FILE_UPLOAD' => [
                        'title' => $this->l('Allow file uploading'),
                        'hint'  => $this->l('Allow customers to upload files using the contact page.'),
                        'type'  => 'bool',
                    ],
                    'PS_CUSTOMER_SERVICE_SIGNATURE'   => [
                        'title' => $this->l('Default message'),
                        'hint'  => $this->l('Please fill out the message fields that appear by default when you answer a thread on the customer service page.'),
                        'type'  => 'textareaLang',
                        'lang'  => true,
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
            'general' => [
                'title'  => $this->l('Customer service options'),
                'fields' => [
                    'PS_SAV_IMAP_URL'                 => [
                        'title' => $this->l('IMAP URL'),
                        'hint'  => $this->l('URL for your IMAP server (ie.: mail.server.com).'),
                        'type'  => 'text',
                    ],
                    'PS_SAV_IMAP_PORT'                => [
                        'title'        => $this->l('IMAP port'),
                        'hint'         => $this->l('Port to use to connect to your IMAP server.'),
                        'type'         => 'text',
                        'defaultValue' => 143,
                    ],
                    'PS_SAV_IMAP_USER'                => [
                        'title' => $this->l('IMAP user'),
                        'hint'  => $this->l('User to use to connect to your IMAP server.'),
                        'type'  => 'text',
                    ],
                    'PS_SAV_IMAP_PWD'                 => [
                        'title' => $this->l('IMAP password'),
                        'hint'  => $this->l('Password to use to connect your IMAP server.'),
                        'type'  => 'text',
                    ],
                    'PS_SAV_IMAP_DELETE_MSG'          => [
                        'title' => $this->l('Delete messages'),
                        'hint'  => $this->l('Delete messages after synchronization. If you do not enable this option, the synchronization will take more time.'),
                        'type'  => 'bool',
                    ],
                    'PS_SAV_IMAP_CREATE_THREADS'      => [
                        'title' => $this->l('Create new threads'),
                        'hint'  => $this->l('Create new threads for unrecognized emails.'),
                        'type'  => 'bool',
                    ],
                    'PS_SAV_IMAP_OPT_NORSH'           => [
                        'title' => $this->l('IMAP options') . ' (/norsh)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Do not use RSH or SSH to establish a preauthenticated IMAP sessions.'),
                    ],
                    'PS_SAV_IMAP_OPT_SSL'             => [
                        'title' => $this->l('IMAP options') . ' (/ssl)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Use the Secure Socket Layer (TLS/SSL) to encrypt the session.'),
                    ],
                    'PS_SAV_IMAP_OPT_VALIDATE-CERT'   => [
                        'title' => $this->l('IMAP options') . ' (/validate-cert)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Validate certificates from the TLS/SSL server.'),
                    ],
                    'PS_SAV_IMAP_OPT_NOVALIDATE-CERT' => [
                        'title' => $this->l('IMAP options') . ' (/novalidate-cert)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Do not validate certificates from the TLS/SSL server. This is only needed if a server uses self-signed certificates.'),
                    ],
                    'PS_SAV_IMAP_OPT_TLS'             => [
                        'title' => $this->l('IMAP options') . ' (/tls)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Force use of start-TLS to encrypt the session, and reject connection to servers that do not support it.'),
                    ],
                    'PS_SAV_IMAP_OPT_NOTLS'           => [
                        'title' => $this->l('IMAP options') . ' (/notls)',
                        'type'  => 'bool',
                        'hint'  => $this->l('Do not use start-TLS to encrypt the session, even with servers that support it.'),
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
        ];

        parent::__construct();
        EmployeeConfiguration::updateValue('EXPERT_CUSTOMERTHREAD_SCRIPT', $this->generateParaGridScript(true));
        $this->paragridScript = EmployeeConfiguration::get('EXPERT_CUSTOMERTHREAD_SCRIPT');

        if (empty($this->paragridScript)) {
            EmployeeConfiguration::updateValue('EXPERT_CUSTOMERTHREAD_SCRIPT', $this->generateParaGridScript(true));
            $this->paragridScript = EmployeeConfiguration::get('EXPERT_CUSTOMERTHREAD_SCRIPT');
        }

        $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CUSTOMERTHREAD_FIELDS'), true);

        if (empty($this->configurationField)) {
            EmployeeConfiguration::updateValue('EXPERT_CUSTOMERTHREAD_FIELDS', Tools::jsonEncode($this->getCustomerThreadFields()));
            $this->configurationField = Tools::jsonDecode(EmployeeConfiguration::get('EXPERT_CUSTOMERTHREAD_FIELDS'), true);
        }

    }

    public function setMedia() {

        parent::setMedia();

        MediaAdmin::addJsDef([
            'AjaxLinkAdminCustomerThreads' => $this->context->link->getAdminLink($this->controller_name),

        ]);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/sav.css', 'all', 0);

        $this->addJS([
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqSelect/pqselect.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqgrid.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/localize/pq-localize-fr.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/pqTouch/pqtouch.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/jsZip-2.5.0/jszip.min.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/FileSaver.js',
            __PS_BASE_URI__ . $this->admin_webpath . '/js/pgrid/javascript-detect-element-resize/jquery.resize.js',

        ]);

    }

    /**
     * Initialize content
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        if (isset($_GET['filename']) && file_exists(_PS_UPLOAD_DIR_ . $_GET['filename']) && Validate::isFileName($_GET['filename'])) {
            static::openUploadedFile();
        }

        $this->paragrid = true;
        $this->paramGridObj = 'obj' . $this->className;
        $this->paramGridVar = 'grid' . $this->className;
        $this->paramGridId = 'grid_' . $this->controller_name;

        $this->TitleBar = $this->l('Client relations List');

        $this->context->smarty->assign([
            'manageHeaderFields' => true,
            'customHeaderFields' => $this->manageFieldsVisibility($this->configurationField),
            'allowExport'        => true,
            'fieldsExport'       => $this->getExportFields(),
            'controller'         => Tools::getValue('controller'),
            'tabScript'          => $this->generateTabScript(Tools::getValue('controller')),
            'gridId'             => 'grid_AdminCustomerThreads',
            'tableName'          => $this->table,
            'className'          => $this->className,
            'linkController'     => $this->context->link->getAdminLink($this->controller_name),
            'AjaxLink'           => $this->context->link->getAdminLink($this->controller_name),
            'paragridScript'     => $this->generateParaGridScript(),
            'titleBar'           => $this->TitleBar,
            'bo_imgdir'          => __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/img/',
            'idController'       => '',
        ]);

        parent::initContent();
    }

    public function generateParaGridScript($regenerate = false) {

        if (!empty($this->paragridScript) && !$regenerate) {
            return '<script type="text/javascript">' . PHP_EOL . $this->paragridScript . PHP_EOL . '</script>';
        }

        $contacts = '<div class="pq-theme"><select id="contactSelect"><option value="">' . $this->l('--Select--') . '</option>';

        foreach (Contact::getContacts($this->context->language->id) as $contact) {
            $contacts .= '<option value="' . $contact['id_contact'] . '">' . $contact['name'] . '</option>';
        }

        $contacts .= '</select></div>';

        $languages = '<div class="pq-theme"><select id="languageSelect"><option value="">' . $this->l('--Select--') . '</option>';

        foreach (Language::getLanguages() as $language) {
            $languages .= '<option value="' . $language['id_lang'] . '">' . $language['name'] . '</option>';
        }

        $languages .= '</select></div>';

        $showStatus = '<div class="pq-theme"><select id="showStatusSelect"><option value="">' . $this->l('--Select--') . '</option>';
        $showStatus .= '<option value="open" data-content="icon-circle text-success">' . $this->l('Open') . '</option>';
        $showStatus .= '<option value="closed" data-content="icon-circle text-danger">' . $this->l('Closed') . '</option>';
        $showStatus .= '<option value="pending1" data-content="icon-circle text-warning">' . $this->l('Pending 1') . '</option>';
        $showStatus .= '<option value="pending2" data-content="icon-circle text-warning">' . $this->l('Pending 2') . '</option>';
        $showStatus .= '</select></div>';

        $employees = '<div class="pq-theme"><select id="employeeSelect"><option value="">' . $this->l('--Select--') . '</option>';

        foreach (Employee::getEmployees() as $employee) {
            $employees .= '<option value="' . $employee['id_employee'] . '">' . $employee['firstname'] . ' ' . $employee['lastname'] . '</option>';
        }

        $employees .= '</select></div>';

        $showPrivate = '<div class="pq-theme"><select id="showPrivateSelect"><option value="">' . $this->l('--Select--') . '</option>';
        $showPrivate .= '<option value="0" data-content="icon-remove">' . $this->l('No') . '</option>';
        $showPrivate .= '<option value="1" data-content="icon-check">' . $this->l('Yes') . '</option>';
        $showPrivate .= '</select></div>';

        $gridExtraFunction = ['function buildContactFilter(){
            var contactSelect = $(\'#contactSelector\').parent().parent();
            $(contactSelect).empty();
            $(contactSelect).append(\'' . $contacts . '\');
            $(\'#contactSelect\' ).selectmenu({
                "change": function(event, ui) {
                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx:\'id_contact\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });

            var languagesSelect = $(\'#languageSelector\').parent().parent();
            $(languagesSelect).empty();
            $(languagesSelect).append(\'' . $languages . '\');
            $(\'#languageSelect\' ).selectmenu({
                "change": function(event, ui) {
                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx:\'id_lang\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });

            var showStatusSelect = $(\'#showStatusSelector\').parent().parent();
            $(showStatusSelect).empty();
            $(showStatusSelect).append(\'' . $showStatus . '\');
            $(\'#showStatusSelect\' ).selectmenu({
                "change": function(event, ui) {
                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx:\'status\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });

            var employeeSelect = $(\'#employeeSelector\').parent().parent();
            $(employeeSelect).empty();
            $(employeeSelect).append(\'' . $employees . '\');
            $(\'#employeeSelect\' ).selectmenu({
                "change": function(event, ui) {
                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx:\'id_employee\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });

            var showPrivateSelect = $(\'#showPrivateSelector\').parent().parent();
            $(showPrivateSelect).empty();
            $(showPrivateSelect).append(\'' . $showPrivate . '\');
            $(\'#showPrivateSelect\' ).selectmenu({
                "change": function(event, ui) {
                    grid' . $this->className . '.filter({
                        mode: \'AND\',
                        rules: [
                            { dataIndx:\'private\', condition: \'equal\', value: ui.item.value}
                        ]
                    });
                }
            });

            }'];

        $paragrid = new ParamGrid($this->className, $this->controller_name, $this->table, $this->identifier);

        $paragrid->height = 700;
        $paragrid->showNumberCell = 0;
        $paragrid->pageModel = [
            'type'       => '\'local\'',
            'rPP'        => 40,
            'rPPOptions' => [10, 20, 40, 50, 100, 200, 500],
        ];
        $paragrid->create = 'function (evt, ui) {
            buildHeadingAction(\'' . 'grid_' . $this->controller_name . '\', \'' . $this->controller_name . '\');
        }';
        $paragrid->rowInit = 'function (ui) {
            return {' . PHP_EOL . '
                    attr: \'data-link="\'+AjaxLink' . $this->controller_name . '+\'" data-class="' . $this->className . '" data-rowIndx="\' + ui.rowIndx+\'" data-object="\' + ui.rowData.' . $this->identifier . '+\' "\',
            };
        }';
        $paragrid->complete = 'function(){
         buildContactFilter();
        }';
        $paragrid->selectionModelType = 'row';
        $paragrid->showTitle = 1;
        $paragrid->title = '\'' . $this->l('Management of') . ' ' . $this->publicName . '\'';
        $paragrid->fillHandle = '\'all\'';
        $paragrid->contextMenuoption = [

            'view'       => [
                'name' => '\'' . $this->l('View the message from: ') . '\'' . '+rowData.email',
                'icon' => '"edit"',
            ],
            'sep'        => [
                'sep1' => "---------",
            ],
            'select'     => [
                'name' => '\'' . $this->l('Select all item') . '\'',
                'icon' => '"list-ul"',
            ],
            'unselect'   => [
                'name' => '\'' . $this->l('Unselect all item') . '\'',
                'icon' => '"list-ul"',
            ],
            'sep'        => [
                'sep2' => "---------",
            ],
            'delete'     => [
                'name' => '\'' . $this->l('Delete the selected message: ') . '\'' . '+rowData.email',
                'icon' => '"delete"',
            ],
            'bulkdelete' => [
                'name' => '\'' . $this->l('Delete all the selected message') . '\'',
                'icon' => '"delete"',
            ],

        ];

        $paragrid->filterModel = [
            'on'          => true,
            'mode'        => '\'OR\'',
            'header'      => true,
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

        $paragrid->gridExtraFunction = $gridExtraFunction;

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

    public function getCustomerThreadRequest() {

        $customerthreads = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('a.*, CONCAT(c.`firstname`," ",c.`lastname`) as `customer`, CONCAT(LEFT(e.`firstname`, 1),". ",e.`lastname`) AS `employee`, cl.`name` as `contact`, l.`name` as `language`, group_concat(message) as `messages`, cm.`private`, cm.`id_employee`')
                ->from('customer_thread', 'a')
                ->leftJoin('customer', 'c', 'c.`id_customer` = a.`id_customer`')
                ->leftJoin('customer_message', 'cm', 'cm.`id_customer_thread` = a.`id_customer_thread`')
                ->leftJoin('employee', 'e', 'e.`id_employee` = cm.`id_employee` AND cm.`id_customer_thread` = a.`id_customer_thread`')
                ->leftJoin('lang', 'l', 'l.`id_lang` = a.`id_lang`')
                ->leftJoin('contact_lang', 'cl', 'cl.`id_contact` = a.`id_contact` AND cl.`id_lang` = ' . (int) $this->context->language->id)
                ->groupBy('cm.id_customer_thread')
                ->orderBy('a.`date_upd` DESC')
        );
        $customerthreadLink = $this->context->link->getAdminLink($this->controller_name);

        foreach ($customerthreads as &$customerthread) {

            $customerthread['viewLink'] = $customerthreadLink . '&id_customer_thread=' . $customerthread['id_customer_thread'] . '&viewcustomer_thread';
            $customerthread['deleteLink'] = $customerthreadLink . '&id_customer_thread=' . $customerthread['id_customer_thread'] . '&id_object=' . $customerthread['id_customer_thread'] . '&deletecustomer_thread&action=deleteObject&ajax=true';

            $customerthread['messages'] = mb_substr($customerthread['messages'], 0, 40);

            if (empty($customerthread['customer'])) {
                $customerthread['customer'] = '--';
            }

            if (empty($customerthread['employee'])) {
                $customerthread['employee'] = '--';
            }

            switch ($customerthread['status']) {
            case 'open':
                $customerthread['showStatus'] = '<i class="icon-circle text-success"></i>';
                break;
            case 'closed':
                $customerthread['showStatus'] = '<i class="icon-circle text-danger"></i>';
                break;
            case 'pending1':
            case 'pending2':
                $customerthread['showStatus'] = '<i class="icon-circle text-warning"></i>';
                break;
            }

            switch ($customerthread['private']) {
            case '0':
                $customerthread['showPrivate'] = '<i class="icon-remove"></i>';
                break;
            default:
                $customerthread['showStatus'] = '<i class="icon-check"></i>';
                break;
            }

        }

        return $customerthreads;

    }

    public function ajaxProcessgetCustomerThreadRequest() {

        die(Tools::jsonEncode($this->getCustomerThreadRequest()));

    }

    public function getCustomerThreadFields() {

        return [
            [
                'title'      => $this->l('ID'),
                'width'      => 50,
                'dataIndx'   => 'id_customer_thread',
                'dataType'   => 'integer',
                'editable'   => false,
                'align'      => 'center',
                'filter'     => [
                    'crules' => [['condition' => "begin"]],
                ],
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 50,
                'dataIndx'   => 'viewLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'      => ' ',
                'width'      => 10,
                'dataIndx'   => 'addLink',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
            ],
            [
                'title'    => $this->l('Customer'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'customer',
                'halign'   => 'HORIZONTAL_LEFT',
                'dataType' => 'string',
                'filter'   => [
                    'crules' => [['condition' => "begin"]],

                ],
            ],
            [
                'title'    => $this->l('Email'),
                'width'    => 200,
                'dataIndx' => 'email',
                'dataType' => 'string',
                'align'    => 'left',
                'halign'   => 'HORIZONTAL_LEFT',
                'editable' => false,
                'hidden'   => false,
                'filter'   => [

                    'crules' => [['condition' => "begin"]],
                ],

            ],
            [

                'dataIndx'   => 'id_contact',
                'hidden'     => true,
                'hiddenable' => 'no',
                'dataType'   => 'integer',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],

                ],
            ],
            [
                'title'    => $this->l('Type'),
                'width'    => 150,
                'exWidth'  => 40,
                'dataIndx' => 'contact',
                'halign'   => 'HORIZONTAL_LEFT',
                'dataType' => 'string',
                'filter'   => [
                    'attr'   => "id=\"contactSelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'dataIndx'   => 'id_lang',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'title'    => $this->l('Language'),
                'width'    => 200,
                'dataIndx' => 'language',
                'dataType' => 'string',
                'editable' => false,
                'filter'   => [
                    'attr'   => "id=\"languageSelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'dataIndx'   => 'status',
                'dataType'   => 'string',
                'hidden'     => true,
                'hiddenable' => 'no',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],

            ],
            [
                'title'    => $this->l('Status'),
                'width'    => 150,
                'dataIndx' => 'showStatus',
                'align'    => 'center',
                'dataType' => 'html',
                'editable' => false,
                'filter'   => [
                    'attr'   => "id=\"showStatusSelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'hidden'     => true,
                'hiddenable' => 'no',
                'dataIndx'   => 'id_employee',
                'dataType'   => 'integer',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'title'    => $this->l('Employee'),
                'width'    => 200,
                'dataIndx' => 'employee',
                'dataType' => 'string',
                'editable' => false,
                'filter'   => [
                    'attr'   => "id=\"employeeSelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'title'    => $this->l('Messages'),
                'width'    => 200,
                'dataIndx' => 'messages',
                'dataType' => 'string',
                'editable' => false,
            ],
            [
                'dataIndx'   => 'private',
                'dataType'   => 'integer',
                'hidden'     => true,
                'hiddenable' => 'no',
                'filter'     => [
                    'crules' => [['condition' => "equal"]],
                ],
            ],

            [
                'title'    => $this->l('Private'),
                'width'    => 150,
                'exWidth'  => 20,
                'dataIndx' => 'showPrivate',
                'align'    => 'center',
                'dataType' => 'html',
                'editable' => false,
                'filter'   => [
                    'attr'   => "id=\"showPrivateSelector\", placeholder=" . $this->l('--Select--') . " readonly",
                    'crules' => [['condition' => "equal"]],
                ],
            ],
            [
                'title'    => $this->l('Last message'),
                'minWidth' => 150,
                'exWidth'  => 20,
                'dataIndx' => 'date_upd',
                'cls'      => 'rangeDate',
                'align'    => 'center',
                'dataType' => 'date',
                'format'   => 'dd/mm/yy',
                'editable' => false,
                'filter'   => [
                    'crules' => [['condition' => "between"]],
                ],
            ],
        ];

    }

    public function ajaxProcessgetCustomerThreadFields() {

        die(EmployeeConfiguration::get('EXPERT_CUSTOMERTHREAD_FIELDS'));
    }

    /**
     * Call the IMAP synchronization during the render process.
     */
    public function renderProcessSyncImap() {

        // To avoid an error if the IMAP isn't configured, we check the configuration here, like during
        // the synchronization. All parameters will exists.

        if (!(Configuration::get('PS_SAV_IMAP_URL')
            || Configuration::get('PS_SAV_IMAP_PORT')
            || Configuration::get('PS_SAV_IMAP_USER')
            || Configuration::get('PS_SAV_IMAP_PWD'))
        ) {
            return;
        }

        // Executes the IMAP synchronization.
        $syncErrors = $this->syncImap();

        // Show the errors.

        if (isset($syncErrors['hasError']) && $syncErrors['hasError']) {

            if (isset($syncErrors['errors'])) {

                foreach ($syncErrors['errors'] as &$error) {
                    $this->displayWarning($error);
                }

            }

        }

    }

    /**
     * Imap synchronization method.
     *
     * @return array Errors list.
     */
    public function syncImap() {

        if (!($url = Configuration::get('PS_SAV_IMAP_URL'))
            || !($port = Configuration::get('PS_SAV_IMAP_PORT'))
            || !($user = Configuration::get('PS_SAV_IMAP_USER'))
            || !($password = Configuration::get('PS_SAV_IMAP_PWD'))
        ) {
            return ['hasError' => true, 'errors' => ['IMAP configuration is not correct']];
        }

        $conf = Configuration::getMultiple(
            [
                'PS_SAV_IMAP_OPT_NORSH',
                'PS_SAV_IMAP_OPT_SSL',
                'PS_SAV_IMAP_OPT_VALIDATE-CERT',
                'PS_SAV_IMAP_OPT_NOVALIDATE-CERT',
                'PS_SAV_IMAP_OPT_TLS',
                'PS_SAV_IMAP_OPT_NOTLS',
            ]
        );

        $confStr = '';

        if ($conf['PS_SAV_IMAP_OPT_NORSH']) {
            $confStr .= '/norsh';
        }

        if ($conf['PS_SAV_IMAP_OPT_SSL']) {
            $confStr .= '/ssl';
        }

        if ($conf['PS_SAV_IMAP_OPT_VALIDATE-CERT']) {
            $confStr .= '/validate-cert';
        }

        if ($conf['PS_SAV_IMAP_OPT_NOVALIDATE-CERT']) {
            $confStr .= '/novalidate-cert';
        }

        if ($conf['PS_SAV_IMAP_OPT_TLS']) {
            $confStr .= '/tls';
        }

        if ($conf['PS_SAV_IMAP_OPT_NOTLS']) {
            $confStr .= '/notls';
        }

        if (!function_exists('imap_open')) {
            return ['hasError' => true, 'errors' => ['imap is not installed on this server']];
        }

        $mbox = @imap_open('{' . $url . ':' . $port . $confStr . '}', $user, $password);

        //checks if there is no error when connecting imap server
        $errors = imap_errors();

        if (is_array($errors)) {
            $errors = array_unique($errors);
        }

        $strErrors = '';
        $strErrorDelete = '';

        if (count($errors) && is_array($errors)) {
            $strErrors = '';

            foreach ($errors as $error) {
                $strErrors .= $error . ', ';
            }

            $strErrors = rtrim(trim($strErrors), ',');
        }

        //checks if imap connexion is active

        if (!$mbox) {
            return ['hasError' => true, 'errors' => ['Cannot connect to the mailbox :<br />' . ($strErrors)]];
        }

        //Returns information about the current mailbox. Returns FALSE on failure.
        $check = imap_check($mbox);

        if (!$check) {
            return ['hasError' => true, 'errors' => ['Fail to get information about the current mailbox']];
        }

        if ($check->Nmsgs == 0) {
            return ['hasError' => true, 'errors' => ['NO message to sync']];
        }

        $result = imap_fetch_overview($mbox, "1:{$check->Nmsgs}", 0);

        foreach ($result as $overview) {
            //check if message exist in database

            if (isset($overview->subject)) {
                $subject = $overview->subject;
            } else {
                $subject = '';
            }

            //Creating an md5 to check if message has been allready processed
            $md5 = md5($overview->date . $overview->from . $subject . $overview->msgno);
            $exist = Db::getInstance()->getValue(
                (new DbQuery())
                    ->select('`md5_header`')
                    ->from('customer_message_sync_imap')
                    ->where('`md5_header` = \'' . pSQL($md5) . '\'')
            );

            if ($exist) {

                if (Configuration::get('PS_SAV_IMAP_DELETE_MSG')) {

                    if (!imap_delete($mbox, $overview->msgno)) {
                        $strErrorDelete = ', Fail to delete message';
                    }

                }

            } else {
                //check if subject has id_order
                preg_match('/\#ct([0-9]*)/', $subject, $matches1);
                preg_match('/\#tc([0-9-a-z-A-Z]*)/', $subject, $matches2);
                $matchFound = false;

                if (isset($matches1[1]) && isset($matches2[1])) {
                    $matchFound = true;
                }

                $newCt = (Configuration::get('PS_SAV_IMAP_CREATE_THREADS') && !$matchFound && (strpos($subject, '[no_sync]') == false));

                if ($matchFound || $newCt) {

                    if ($newCt) {

                        if (!preg_match('/<(' . Tools::cleanNonUnicodeSupport('[a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]+[.a-z\p{L}0-9!#$%&\'*+\/=?^`{}|~_-]*@[a-z\p{L}0-9]+[._a-z\p{L}0-9-]*\.[a-z0-9]+') . ')>/', $overview->from, $result)
                            || !Validate::isEmail($from = Tools::convertEmailToIdn($result[1]))
                        ) {
                            continue;
                        }

                        // we want to assign unrecognized mails to the right contact category
                        $contacts = Contact::getContacts($this->context->language->id);

                        if (!$contacts) {
                            continue;
                        }

                        foreach ($contacts as $contact) {

                            if (strpos($overview->to, $contact['email']) !== false) {
                                $idContact = $contact['id_contact'];
                            }

                        }

                        if (!isset($idContact)) {
                            // if not use the default contact category
                            $idContact = $contacts[0]['id_contact'];
                        }

                        $customer = new Customer();
                        $client = $customer->getByEmail($from); //check if we already have a customer with this email
                        $ct = new CustomerThread();

                        if (isset($client->id)) {
                            //if mail is owned by a customer assign to him
                            $ct->id_customer = $client->id;
                        }

                        $ct->email = $from;
                        $ct->id_contact = $idContact;
                        $ct->id_lang = (int) Configuration::get('PS_LANG_DEFAULT');
                        $ct->id_shop = $this->context->shop->id; //new customer threads for unrecognized mails are not shown without shop id
                        $ct->status = 'open';
                        $ct->token = Tools::passwdGen(12);
                        $ct->add();
                    } else {
                        $ct = new CustomerThread((int) $matches1[1]);
                    }

                    //check if order exist in database

                    if (Validate::isLoadedObject($ct) && ((isset($matches2[1]) && $ct->token == $matches2[1]) || $newCt)) {
                        $message = imap_fetchbody($mbox, $overview->msgno, 1);

                        if (base64_encode(base64_decode($message)) === $message) {
                            $message = base64_decode($message);
                        }

                        $message = quoted_printable_decode($message);
                        $message = utf8_encode($message);
                        $message = quoted_printable_decode($message);
                        $message = nl2br($message);
                        $message = mb_substr($message, 0, (int) CustomerMessage::$definition['fields']['message']['size']);

                        $cm = new CustomerMessage();
                        $cm->id_customer_thread = $ct->id;

                        if (empty($message) || !Validate::isCleanHtml($message)) {
                            $strErrors .= Tools::displayError(sprintf('Invalid Message Content for subject: %1s', $subject));
                        } else {
                            $cm->message = $message;
                            $cm->add();
                        }

                    }

                }

                Db::getInstance()->insert(
                    'customer_message_sync_imap',
                    [
                        'md5_header' => pSQL($md5),
                    ]
                );
            }

        }

        imap_expunge($mbox);
        imap_close($mbox);

        if ($strErrors . $strErrorDelete) {
            return ['hasError' => true, 'errors' => [$strErrors . $strErrorDelete]];
        } else {
            return ['hasError' => false, 'errors' => ''];
        }

    }

  

    /**
     * @param mixed    $value
     * @param Customer $customer
     *
     * @return string
     *
     * @since 1.8.1.0
     */
    public function printOptinIcon($value, $customer) {

        return ($value ? '<i class="icon-check"></i>' : '<i class="icon-remove"></i>');
    }

    /**
     * @return bool
     *
     * @since 1.8.1.0
     */
    public function postProcess() {

        if ($idCustomerThread = (int) Tools::getValue('id_customer_thread')) {

            if (($idContact = (int) Tools::getValue('id_contact'))) {
                Db::getInstance()->execute(
                    '
                    UPDATE ' . _DB_PREFIX_ . 'customer_thread
                    SET id_contact = ' . (int) $idContact . '
                    WHERE id_customer_thread = ' . (int) $idCustomerThread
                );
            }

            if ($idStatus = (int) Tools::getValue('setstatus')) {
                $statusArray = [1 => 'open', 2 => 'closed', 3 => 'pending1', 4 => 'pending2'];
                Db::getInstance()->execute(
                    '
                    UPDATE ' . _DB_PREFIX_ . 'customer_thread
                    SET status = "' . $statusArray[$idStatus] . '"
                    WHERE id_customer_thread = ' . (int) $idCustomerThread . ' LIMIT 1
                '
                );
            }

            if (isset($_POST['id_employee_forward'])) {
                $messages = Db::getInstance()->getRow(
                    '
                    SELECT ct.*, cm.*, cl.name subject, CONCAT(e.firstname, \' \', e.lastname) employee_name,
                        CONCAT(c.firstname, \' \', c.lastname) customer_name, c.firstname
                    FROM ' . _DB_PREFIX_ . 'customer_thread ct
                    LEFT JOIN ' . _DB_PREFIX_ . 'customer_message cm
                        ON (ct.id_customer_thread = cm.id_customer_thread)
                    LEFT JOIN ' . _DB_PREFIX_ . 'contact_lang cl
                        ON (cl.id_contact = ct.id_contact AND cl.id_lang = ' . (int) $this->context->language->id . ')
                    LEFT OUTER JOIN ' . _DB_PREFIX_ . 'employee e
                        ON e.id_employee = cm.id_employee
                    LEFT OUTER JOIN ' . _DB_PREFIX_ . 'customer c
                        ON (c.email = ct.email)
                    WHERE ct.id_customer_thread = ' . (int) Tools::getValue('id_customer_thread') . '
                    ORDER BY cm.date_add DESC
                '
                );
                $output = $this->displayMessage($messages, true, (int) Tools::getValue('id_employee_forward'));
                $cm = new CustomerMessage();
                $cm->id_employee = (int) $this->context->employee->id;
                $cm->id_customer_thread = (int) Tools::getValue('id_customer_thread');
                $cm->ip_address = (int) ip2long(Tools::getRemoteAddr());
                $currentEmployee = $this->context->employee;
                $idEmployee = (int) Tools::getValue('id_employee_forward');
                $employee = new Employee($idEmployee);
                $email = Tools::convertEmailToIdn(Tools::getValue('email'));
                $message = Tools::getValue('message_forward');

                if (($error = $cm->validateField('message', $message, null, [], true)) !== true) {
                    $this->errors[] = $error;
                } else
                if ($idEmployee && $employee && Validate::isLoadedObject($employee)) {
                    $params = [
                        '{messages}'  => stripslashes($output),
                        '{employee}'  => $currentEmployee->firstname . ' ' . $currentEmployee->lastname,
                        '{comment}'   => stripslashes(Tools::nl2br($_POST['message_forward'])),
                        '{firstname}' => $employee->firstname,
                        '{lastname}'  => $employee->lastname,
                    ];

                    if (Mail::Send(
                        $this->context->language->id,
                        'forward_msg',
                        Mail::l('Fwd: Customer message', $this->context->language->id),
                        $params,
                        $employee->email,
                        $employee->firstname . ' ' . $employee->lastname,
                        $currentEmployee->email,
                        $currentEmployee->firstname . ' ' . $currentEmployee->lastname,
                        null,
                        null,
                        _PS_MAIL_DIR_,
                        true
                    )) {
                        $cm->private = 1;
                        $cm->message = $this->l('Message forwarded to') . ' ' . $employee->firstname . ' ' . $employee->lastname . "\n" . $this->l('Comment:') . ' ' . $message;
                        $cm->add();
                    }

                } else
                if ($email && Validate::isEmail($email)) {
                    $params = [
                        '{messages}'  => Tools::nl2br(stripslashes($output)),
                        '{employee}'  => $currentEmployee->firstname . ' ' . $currentEmployee->lastname,
                        '{comment}'   => stripslashes($_POST['message_forward']),
                        '{firstname}' => '',
                        '{lastname}'  => '',
                    ];

                    if (Mail::Send(
                        $this->context->language->id,
                        'forward_msg',
                        Mail::l('Fwd: Customer message', $this->context->language->id),
                        $params,
                        $email,
                        null,
                        $currentEmployee->email,
                        $currentEmployee->firstname . ' ' . $currentEmployee->lastname,
                        null,
                        null,
                        _PS_MAIL_DIR_,
                        true
                    )) {
                        $cm->message = $this->l('Message forwarded to') . ' ' . Tools::convertEmailFromIdn($email) . "\n" . $this->l('Comment:') . ' ' . $message;
                        $cm->add();
                    }

                } else {
                    $this->errors[] = '<div class="alert error">' . Tools::displayError('The email address is invalid.') . '</div>';
                }

            }

            if (Tools::isSubmit('submitReply')) {
                $ct = new CustomerThread($idCustomerThread);

                ShopUrl::cacheMainDomainForShop((int) $ct->id_shop);

                $cm = new CustomerMessage();
                $cm->id_employee = (int) $this->context->employee->id;
                $cm->id_customer_thread = $ct->id;
                $cm->ip_address = (int) ip2long(Tools::getRemoteAddr());
                $cm->message = Tools::getValue('reply_message');

                if (($error = $cm->validateField('message', $cm->message, null, [], true)) !== true) {
                    $this->errors[] = $error;
                } else
                if (isset($_FILES) && !empty($_FILES['joinFile']['name']) && $_FILES['joinFile']['error'] != 0) {
                    $this->errors[] = Tools::displayError('An error occurred during the file upload process.');
                } else
                if ($cm->add()) {
                    $fileAttachment = null;

                    if (!empty($_FILES['joinFile']['name'])) {
                        $fileAttachment['content'] = file_get_contents($_FILES['joinFile']['tmp_name']);
                        $fileAttachment['name'] = $_FILES['joinFile']['name'];
                        $fileAttachment['mime'] = $_FILES['joinFile']['type'];
                    }

                    $customer = new Customer($ct->id_customer);
                    $params = [
                        '{reply}'     => Tools::nl2br(Tools::getValue('reply_message')),
                        '{link}'      => Tools::url(
                            $this->context->link->getPageLink('contact', true, null, null, false, $ct->id_shop),
                            'id_customer_thread=' . (int) $ct->id . '&token=' . $ct->token
                        ),
                        '{firstname}' => $customer->firstname,
                        '{lastname}'  => $customer->lastname,
                    ];
                    //#ct == id_customer_thread    #tc == token of thread   <== used in the synchronization imap
                    $contact = new Contact((int) $ct->id_contact, (int) $ct->id_lang);

                    if (Validate::isLoadedObject($contact)) {
                        $fromName = $contact->name;
                        $fromEmail = $contact->email;
                    } else {
                        $fromName = null;
                        $fromEmail = null;
                    }

                    if (Mail::Send(
                        (int) $ct->id_lang,
                        'reply_msg',
                        sprintf(Mail::l('An answer to your message is available #ct%1$s #tc%2$s', $ct->id_lang), $ct->id, $ct->token),
                        $params,
                        Tools::getValue('msg_email'),
                        null,
                        Tools::convertEmailToIdn($fromEmail),
                        $fromName,
                        $fileAttachment,
                        null,
                        _PS_MAIL_DIR_,
                        true,
                        $ct->id_shop
                    )) {
                        $ct->status = 'closed';
                        $ct->update();
                    }

                    Tools::redirectAdmin(
                        static::$currentIndex . '&id_customer_thread=' . (int) $idCustomerThread . '&viewcustomer_thread&token=' . Tools::getValue('token')
                    );
                } else {
                    $this->errors[] = Tools::displayError('An error occurred. Your message was not sent. Please contact your system administrator.');
                }

            }

        }

        return parent::postProcess();
    }

    /**
     * @param      $message
     * @param bool $email
     * @param null $idEmployee
     *
     * @return string
     *
     * @since 1.0.
     */
    protected function displayMessage($message, $email = false, $idEmployee = null) {

        $tpl = $this->createTemplate('message.tpl');

        $contacts = Contact::getContacts($this->context->language->id);
        $contactArray = [];

        foreach ($contacts as $contact) {
            $contactArray[$contact['id_contact']] = ['id_contact' => $contact['id_contact'], 'name' => $contact['name']];
        }

        $contacts = $contactArray;

        if (!$email) {

            if (!empty($message['id_product']) && empty($message['employee_name'])) {
                $idOrderProduct = Order::getIdOrderProduct((int) $message['id_customer'], (int) $message['id_product']);
            }

        }

        $message['date_add'] = Tools::displayDate($message['date_add'], null, true);
        $message['user_agent'] = strip_tags($message['user_agent']);
        $message['message'] = preg_replace(
            '/(https?:\/\/[a-z0-9#%&_=\(\)\.\? \+\-@\/]{6,1000})([\s\n<])/Uui',
            '<a href="\1">\1</a>\2',
            html_entity_decode(
                $message['message'],
                ENT_QUOTES,
                'UTF-8'
            )
        );

        $isValidOrderId = true;
        $order = new Order((int) $message['id_order']);

        if (!Validate::isLoadedObject($order)) {
            $isValidOrderId = false;
        }

        $tpl->assign(
            [
                'thread_url'        => Tools::getAdminUrl(basename(_PS_ADMIN_DIR_) . '/' . $this->context->link->getAdminLink('AdminCustomerThreads') . '&amp;id_customer_thread=' . (int) $message['id_customer_thread'] . '&amp;viewcustomer_thread=1'),
                'link'              => $this->context->link,
                'current'           => static::$currentIndex,
                'token'             => $this->token,
                'message'           => $message,
                'id_order_product'  => isset($idOrderProduct) ? $idOrderProduct : null,
                'email'             => Tools::convertEmailFromIdn($email),
                'id_employee'       => $idEmployee,
                'PS_SHOP_NAME'      => Configuration::get('PS_SHOP_NAME'),
                'file_name'         => file_exists(_PS_UPLOAD_DIR_ . $message['file_name']),
                'contacts'          => $contacts,
                'is_valid_order_id' => $isValidOrderId,
            ]
        );

        return $tpl->fetch();
    }

    /**
     * Render view
     *
     * @return string
     *
     * @since 1.8.1.0
     */

    public function renderView() {

        if (!$idCustomerThread = (int) Tools::getValue('id_customer_thread')) {
            return '';
        }

        if (!($thread = $this->loadObject())) {
            return '';
        }

        $this->context->cookie->{'customer_threadFilter_cl!id_contact'}

        = $thread->id_contact;

        $employees = Employee::getEmployees();

        $messages = CustomerThread::getMessageCustomerThreads($idCustomerThread);

        foreach ($messages as $key => $mess) {

            if ($mess['id_employee']) {
                $employee = new Employee($mess['id_employee']);
                $messages[$key]['employee_image'] = $employee->getImage();
            }

            if (isset($mess['file_name']) && $mess['file_name'] != '') {
                $messages[$key]['file_name'] = _THEME_PROD_PIC_DIR_ . $mess['file_name'];
            } else {
                unset($messages[$key]['file_name']);
            }

            if ($mess['id_product']) {
                $product = new Product((int) $mess['id_product'], false, $this->context->language->id);

                if (Validate::isLoadedObject($product)) {
                    $messages[$key]['product_name'] = $product->name;
                    $messages[$key]['product_link'] = $this->context->link->getAdminLink('AdminProducts') . '&updateproduct&id_product=' . (int) $product->id;
                }

            }

        }

        $nextThread = CustomerThread::getNextThread((int) $thread->id);

        $contacts = Contact::getContacts($this->context->language->id);

        $actions = [];

        if ($nextThread) {
            $nextThread = [
                'href' => static::$currentIndex . '&id_customer_thread=' . (int) $nextThread . '&viewcustomer_thread&token=' . $this->token,
                'name' => $this->l('Reply to the next unanswered message in this thread'),
            ];
        }

        if ($thread->status != 'closed') {
            $actions['closed'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=2&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Mark as "handled"'),
                'name'  => 'setstatus',
                'value' => 2,
            ];
        } else {
            $actions['open'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=1&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Re-open'),
                'name'  => 'setstatus',
                'value' => 1,
            ];
        }

        if ($thread->status != 'pending1') {
            $actions['pending1'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=3&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Mark as "pending 1" (will be answered later)'),
                'name'  => 'setstatus',
                'value' => 3,
            ];
        } else {
            $actions['pending1'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=1&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Disable pending status'),
                'name'  => 'setstatus',
                'value' => 1,
            ];
        }

        if ($thread->status != 'pending2') {
            $actions['pending2'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=4&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Mark as "pending 2" (will be answered later)'),
                'name'  => 'setstatus',
                'value' => 4,
            ];
        } else {
            $actions['pending2'] = [
                'href'  => static::$currentIndex . '&viewcustomer_thread&setstatus=1&id_customer_thread=' . (int) Tools::getValue('id_customer_thread') . '&viewmsg&token=' . $this->token,
                'label' => $this->l('Disable pending status'),
                'name'  => 'setstatus',
                'value' => 1,
            ];
        }

        if ($thread->id_customer) {
            $customer = new Customer($thread->id_customer);
            $orders = Order::getCustomerOrders($customer->id);

            if ($orders && count($orders)) {
                $totalOk = 0;
                $ordersOk = [];

                foreach ($orders as $key => $order) {

                    if ($order['valid']) {
                        $ordersOk[] = $order;
                        $totalOk += $order['total_paid_real'] / $order['conversion_rate'];
                    }

                    $orders[$key]['date_add'] = Tools::displayDate($order['date_add']);
                    $orders[$key]['total_paid_real'] = Tools::displayPrice($order['total_paid_real'], new Currency((int) $order['id_currency']));
                }

            }

            $products = $customer->getBoughtProducts();

            if ($products && count($products)) {

                foreach ($products as $key => $product) {
                    $products[$key]['date_add'] = Tools::displayDate($product['date_add'], null, true);
                }

            }

        }

        $timelineItems = $this->getTimeline($messages, $thread->id_order);
        $firstMessage = $messages[0];

        if (!$messages[0]['id_employee']) {
            unset($messages[0]);
        }

        $contact = '';

        foreach ($contacts as $c) {

            if ($c['id_contact'] == $thread->id_contact) {
                $contact = $c['name'];
            }

        }

        $this->tpl_view_vars = [
            'id_customer_thread'            => $idCustomerThread,
            'thread'                        => $thread,
            'actions'                       => $actions,
            'employees'                     => $employees,
            'current_employee'              => $this->context->employee,
            'messages'                      => $messages,
            'first_message'                 => $firstMessage,
            'contact'                       => $contact,
            'next_thread'                   => $nextThread,
            'orders'                        => isset($orders) ? $orders : false,
            'customer'                      => isset($customer) ? $customer : false,
            'products'                      => isset($products) ? $products : false,
            'total_ok'                      => isset($totalOk) ? Tools::displayPrice($totalOk, $this->context->currency) : false,
            'orders_ok'                     => isset($ordersOk) ? $ordersOk : false,
            'count_ok'                      => isset($ordersOk) ? count($ordersOk) : false,
            'PS_CUSTOMER_SERVICE_SIGNATURE' => str_replace('\r\n', "\n", Configuration::get('PS_CUSTOMER_SERVICE_SIGNATURE', (int) $thread->id_lang)),
            'timeline_items'                => $timelineItems,
            'AjaxBackLink'                  => $this->context->link->getAdminLink($this->controller_name),
        ];

        if ($nextThread) {
            $this->tpl_view_vars['next_thread'] = $nextThread;
        }

        return parent::renderView();
    }

    /**
     * Get timeline
     *
     * @param $messages
     * @param $idOrder
     *
     * @return array
     *
     * @since 1.8.1.0
     */
    public function getTimeline($messages, $idOrder) {

        $timeline = [];

        foreach ($messages as $message) {
            $product = new Product((int) $message['id_product'], false, $this->context->language->id);

            $content = '';

            if (!$message['private']) {
                $content .= $this->l('Message to: ') . ' <span class="badge">' . (!$message['id_employee'] ? $message['subject'] : $message['customer_name']) . '</span><br/>';
            }

            if (Validate::isLoadedObject($product)) {
                $content .= '<br/>' . $this->l('Product: ') . '<span class="label label-info">' . $product->name . '</span><br/><br/>';
            }

            $content .= Tools::safeOutput($message['message']);

            $timeline[$message['date_add']][] = [
                'arrow'            => 'left',
                'background_color' => '',
                'icon'             => 'icon-envelope',
                'content'          => $content,
                'date'             => $message['date_add'],
            ];
        }

        $order = new Order((int) $idOrder);

        if (Validate::isLoadedObject($order)) {
            $orderHistory = $order->getHistory($this->context->language->id);

            foreach ($orderHistory as $history) {
                $linkOrder = $this->context->link->getAdminLink('AdminOrders') . '&vieworder&id_order=' . (int) $order->id;

                $content = '<a class="badge" target="_blank" href="' . Tools::safeOutput($linkOrder) . '">' . $this->l('Order') . ' #' . (int) $order->id . '</a><br/><br/>';

                $content .= '<span>' . $this->l('Status:') . ' ' . $history['ostate_name'] . '</span>';

                $timeline[$history['date_add']][] = [
                    'arrow'            => 'right',
                    'alt'              => true,
                    'background_color' => $history['color'],
                    'icon'             => 'icon-credit-card',
                    'content'          => $content,
                    'date'             => $history['date_add'],
                    'see_more_link'    => $linkOrder,
                ];
            }

        }

        krsort($timeline);

        return $timeline;
    }

    /**
     * Render options
     *
     * @return string
     *
     * @since 1.8.1.0
     */
    public function renderOptions() {

        if (Configuration::get('PS_SAV_IMAP_URL')
            && Configuration::get('PS_SAV_IMAP_PORT')
            && Configuration::get('PS_SAV_IMAP_USER')
            && Configuration::get('PS_SAV_IMAP_PWD')
        ) {
            $this->tpl_option_vars['use_sync'] = true;
        } else {
            $this->tpl_option_vars['use_sync'] = false;
        }

        $this->paragrid = true;

        return parent::renderOptions();
    }

    /**
     * AdminController::getList() override
     *
     * @see AdminController::getList()
     *
     * @param int         $idLang
     * @param string|null $orderBy
     * @param string|null $orderWay
     * @param int         $start
     * @param int|null    $limit
     * @param int|bool    $idLangShop
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.1.0
     */
    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false) {

        parent::getList($idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);

        $nbItems = count($this->_list);

        for ($i = 0; $i < $nbItems; ++$i) {

            if (isset($this->_list[$i]['messages'])) {
                $this->_list[$i]['messages'] = Tools::htmlentitiesDecodeUTF8($this->_list[$i]['messages']);
            }

            if (isset($this->_list[$i]['email'])) {
                $this->_list[$i]['email'] = Tools::convertEmailFromIdn($this->_list[$i]['email']);
            }

        }

    }

    /**
     * @param $value
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.1.0
     */
    public function updateOptionPsSavImapOpt($value) {

        if ($this->tabAccess['edit'] != '1') {
            throw new PhenyxShopException(Tools::displayError('You do not have permission to edit this.'));
        }

        if (!$this->errors && $value) {
            Configuration::updateValue('PS_SAV_IMAP_OPT', implode('', $value));
        }

    }

    /**
     * @throws PhenyxShopException
     *
     * @since 1.8.1.0
     */
    public function ajaxProcessMarkAsRead() {

        if ($this->tabAccess['edit'] != '1') {
            throw new PhenyxShopException(Tools::displayError('You do not have permission to edit this.'));
        }

        $idThread = Tools::getValue('id_thread');
        $messages = CustomerThread::getMessageCustomerThreads($idThread);

        if (count($messages)) {
            Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'customer_message` set `read` = 1 WHERE `id_employee` = ' . (int) $this->context->employee->id . ' AND `id_customer_thread` = ' . (int) $idThread);
        }

    }

    /**
     * Call the IMAP synchronization during an AJAX process.
     *
     * @throws PhenyxShopException
     *
     * @since 1.8.1.0
     */
    public function ajaxProcessSyncImap() {

        if ($this->tabAccess['edit'] != '1') {
            throw new PhenyxShopException(Tools::displayError('You do not have permission to edit this.'));
        }

        if (Tools::isSubmit('syncImapMail')) {
            $this->ajaxDie(json_encode($this->syncImap()));
        }

    }

    protected function openUploadedFile() {

        $filename = $_GET['filename'];

        $extensions = [
            '.txt'  => 'text/plain',
            '.rtf'  => 'application/rtf',
            '.doc'  => 'application/msword',
            '.docx' => 'application/msword',
            '.pdf'  => 'application/pdf',
            '.zip'  => 'multipart/x-zip',
            '.png'  => 'image/png',
            '.jpeg' => 'image/jpeg',
            '.gif'  => 'image/gif',
            '.jpg'  => 'image/jpeg',
        ];

        $extension = false;

        foreach ($extensions as $key => $val) {

            if (substr(mb_strtolower($filename), -4) == $key || substr(mb_strtolower($filename), -5) == $key) {
                $extension = $val;
                break;
            }

        }

        if (!$extension || !Validate::isFileName($filename)) {
            die(Tools::displayError());
        }

        if (ob_get_level() && ob_get_length() > 0) {
            ob_end_clean();
        }

        header('Content-Type: ' . $extension);
        header('Content-Disposition:attachment;filename="' . $filename . '"');
        readfile(_PS_UPLOAD_DIR_ . $filename);
        die;
    }

    /**
     * @param $content
     *
     * @return string
     *
     * @since 1.8.1.0
     */
    protected function displayButton($content) {

        return '<div><p>' . $content . '</p></div>';
    }

}
