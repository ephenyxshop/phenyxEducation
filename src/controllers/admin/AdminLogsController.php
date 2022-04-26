<?php

/**
 * Class AdminLogsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminLogsControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    /** @var string $_defaultOrderWay */
    protected $_defaultOrderWay = 'DESC';
    // @codingStandardsIgnoreEnd

    /**
     * AdminLogsControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'log';
        $this->className = ''hidden' => truer';
        $this->lang = false;
        $this->noLink = true;

        $this->fields_list = [
            'id_log'      => [
                'title' => $this->l('ID'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'employee'    => [
                'title'           => $this->l('Employee'),
                'havingFilter'    => true,
                'callback'        => 'displayEmployee',
                'callback_object' => $this,
            ],
            'severity'    => [
                'title' => $this->l('Severity (1-4)'),
                'align' => 'text-center',
                'class' => 'fixed-width-xs',
            ],
            'message'     => [
                'title' => $this->l('Message'),
            ],
            'object_type' => [
                'title' => $this->l('Object type'),
                'class' => 'fixed-width-sm',
            ],
            'object_id'   => [
                'title' => $this->l('Object ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'error_code'  => [
                'title'  => $this->l('Error code'),
                'align'  => 'center',
                'prefix' => '0x',
                'class'  => 'fixed-width-xs',
            ],
            'date_add'    => [
                'title' => $this->l('Date'),
                'align' => 'right',
                'type'  => 'datetime',
            ],
        ];

        $this->fields_options = [
            'general'             => [
                'title'  => $this->l('Logs by email'),
                'icon'   => 'icon-envelope',
                'fields' => [
                    'PS_LOGS_BY_EMAIL' => [
                        'title' => $this->l('Minimum severity level'),
                        'hint'  => $this->l('Enter "5" if you do not want to receive any emails.') . '<br />' . $this->l('Emails will be sent to the shop owner.'),
                        'cast'  => 'intval',
                        'type'  => 'text',
                    ],
                ],
                'submit' => ['title' => $this->l('Save')],
            ],
            'encrypted_exception' => [
                'title'  => $this->l('Decrypt an exception message'),
                'icon'   => 'icon-bug',
                'fields' => [
                    'encrypted_exception' => [
                        'title'                     => $this->l('Decrypt an exception message'),
                        'label'                     => $this->l('Paste your encrypted exception message here to see the actual message.'),
                        'type'                      => 'code',
                        'mode'                      => 'text',
                        'enableBasicAutocompletion' => true,
                        'enableSnippets'            => true,
                        'enableLiveAutocompletion'  => true,
                        'visibility'                => Shop::CONTEXT_ALL,
                        'auto_value'                => false,
                        'value'                     => '',
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Decrypt'),
                    'icon'  => 'process-icon-envelope',
                ],
            ],
        ];

        if (Tools::isSubmit('encrypted_exception')) {
            try {
                $markdown = Encryptor::getInstance()->decrypt(Tools::getValue('encrypted_exception'));
            } catch (Exception $e) {
                $markdown = 'kanker';
            }

            $this->fields_options = array_merge($this->fields_options, [
                'decrypted_exception' => [
                    'title'       => $this->l('Decrypted exception message'),
                    'icon'        => 'icon-bug',
                    'description' => $this->l('This is the decrypted exception message in markdown (can be directly posted on GitHub or the forum)'),
                    'fields'      => [
                        'decrypted_exception' => [
                            'title'                     => $this->l('Decrypted exception'),
                            'type'                      => 'code',
                            'mode'                      => 'markdown',
                            'maxLines'                  => substr_count($markdown, "\n") + 10,
                            'enableBasicAutocompletion' => true,
                            'enableSnippets'            => true,
                            'enableLiveAutocompletion'  => true,
                            'visibility'                => Shop::CONTEXT_ALL,
                            'auto_value'                => false,
                            'value'                     => $markdown,
                        ],
                    ],
                ],
            ]);
        }

        $this->list_no_link = true;
        $this->_select .= 'CONCAT(LEFT(e.firstname, 1), \'. \', e.lastname) employee';
        $this->_join .= ' LEFT JOIN ' . _DB_PREFIX_ . 'employee e ON (a.id_employee = e.id_employee)';
        $this->_use_found_rows = false;
        parent::__construct();
    }

    public function setMedia() {

        parent::setMedia();
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/js/' . $this->bo_theme . '/css/jquery-ui.css');
        $this->addJquery('3.4.1');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery-ui/jquery-ui.js');

    }

    /**
     * @return void
     *
     * @since 1.9.1.0
     */
    public function processDelete() {

        if (Logger::eraseAllLogs()) {
            Tools::redirectAdmin($this->context->link->getAdminLink('AdminLogs'));
        }

    }

    

    /**
     * @param mixed $value
     * @param array $tr
     *
     * @return mixed
     *
     * @since 1.9.1.0
     */
    public function displayEmployee($value, $tr) {

        $template = $this->context->smarty->createTemplate('controllers/logs/employee_field.tpl', $this->context->smarty);
        $employee = new Employee((int) $tr['id_employee']);
        $template->assign(
            [
                'employee_image' => $employee->getImage(),
                'employee_name'  => $value,
            ]
        );

        return $template->fetch();
    }

}
