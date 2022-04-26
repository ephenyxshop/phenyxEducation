<?php

/**
 * Class AdminLoginControllerCore
 *
 * @since 1.9.1.0
 */
class AdminLoginControllerCore extends AdminController {

    /**
     * AdminLoginControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->errors = [];
        $this->display_header = false;
        $this->display_footer = false;
        $this->meta_title = $this->l('Administration panel');
        $this->css_files = [];
        parent::__construct();
        $this->layout = _PS_ADMIN_DIR_ . DIRECTORY_SEPARATOR . 'template' . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . 'login' . DIRECTORY_SEPARATOR . 'layout.tpl';

        if (!headers_sent()) {
            header('Login: true');
        }

    }

    /**
     * Initialize content
     *
     * @return void
     *
     * @since 1.9.1.0
     */
    public function initContent() {

        if (!Tools::usingSecureMode() && Configuration::get('PS_SSL_ENABLED')) {
            // You can uncomment these lines if you want to force https even from localhost and automatically redirect
            // header('HTTP/1.1 301 Moved Permanently');
            // header('Location: '.Tools::getShopDomainSsl(true).$_SERVER['REQUEST_URI']);
            // exit();
            $clientIsMaintenanceOrLocal = in_array(Tools::getRemoteAddr(), array_merge(['127.0.0.1'], explode(',', Configuration::get('PS_MAINTENANCE_IP'))));
            // If ssl is enabled, https protocol is required. Exception for maintenance and local (127.0.0.1) IP

            if ($clientIsMaintenanceOrLocal) {
                $warningSslMessage = Tools::displayError('SSL is activated. However, your IP is allowed to enter unsecure mode for maintenance or local IP issues.');
            } else {
                $url = 'https://' . Tools::safeOutput(Tools::getServerName()) . Tools::safeOutput($_SERVER['REQUEST_URI']);
                $warningSslMessage = sprintf(
                    Translate::ppTags(
                        Tools::displayError('SSL is activated. Please connect using the following link to [1]log into secure mode (https://)[/1]', false),
                        ['<a href="%s">']
                    ),
                    $url
                );
            }

            $this->context->smarty->assign('warningSslMessage', $warningSslMessage);
        }

        if (file_exists(_PS_ADMIN_DIR_ . '/../install')) {
            $this->context->smarty->assign('wrong_install_name', true);
        }

        if (basename(_PS_ADMIN_DIR_) == 'admin' && file_exists(_PS_ADMIN_DIR_ . '/../admin/')) {
            $rand = 'admin' . sprintf('%03d', rand(0, 999)) . mb_strtolower(Tools::passwdGen(6)) . '/';

            if (@rename(_PS_ADMIN_DIR_ . '/../admin/', _PS_ADMIN_DIR_ . '/../' . $rand)) {
                Tools::redirectAdmin('../' . $rand);
            } else {
                $this->context->smarty->assign(
                    [
                        'wrong_folder_name' => true,
                    ]
                );
            }

        } else {
            $rand = basename(_PS_ADMIN_DIR_) . '/';
        }

        $this->context->smarty->assign(
            [
                'randomNb' => $rand,
                'adminUrl' => Tools::getCurrentUrlProtocolPrefix() . Tools::getShopDomain() . __PS_BASE_URI__ . $rand,
            ]
        );

        // Redirect to admin panel

        if (Tools::isSubmit('redirect') && Validate::isControllerName(Tools::getValue('redirect'))) {
            $this->context->smarty->assign('redirect', Tools::getValue('redirect'));
        } else {
            $tab = new Tab((int) $this->context->employee->default_tab);
            $this->context->smarty->assign('redirect', $this->context->link->getAdminLink($tab->class_name));
        }

        if ($nbErrors = count($this->errors)) {
            $this->context->smarty->assign(
                [
                    'errors'                    => $this->errors,
                    'nbErrors'                  => $nbErrors,
                    'shop_name'                 => Tools::safeOutput(Configuration::get('PS_SHOP_NAME')),
                    'disableDefaultErrorOutPut' => true,
                ]
            );
        }

        if ($email = Tools::getValue('email')) {
            $this->context->smarty->assign('email', $email);
        }

        if ($password = Tools::getValue('password')) {
            $this->context->smarty->assign('password', $password);
        }

        $this->setMedia();
        $this->initHeader();
        parent::initContent();
        $this->initFooter();

        //force to disable modals
        $this->context->smarty->assign('modals', null);
    }

    /**
     * Set media
     *
     * @return void
     *
     * @since 1.9.1.0
     */
    public function setMedia() {

        $this->addJquery();
        $this->addjqueryPlugin('validate');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/jquery/plugins/validate/localization/messages_' . $this->context->language->iso_code . '.js');
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/admin-theme.css', 'all', 0);
        $this->addCSS(__PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/overrides.css', 'all', PHP_INT_MAX);
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/spin.js');
        $this->addJS(__PS_BASE_URI__ . $this->admin_webpath . '/js/vendor/ladda.js');
        MediaAdmin::addJsDef(['img_dir' => _PS_IMG_]);
        MediaAdmin::addJsDefL('one_error', $this->l('There is one error.', null, true, false));
        MediaAdmin::addJsDefL('more_errors', $this->l('There are several errors.', null, true, false));

        Hook::exec('actionAdminLoginControllerSetMedia');
    }

    /**
     * Check token
     *
     * Always true to make this page publicly accessible
     *
     * @return bool
     */
    public function checkToken() {

        return true;
    }

    /**
     * All BO users can access the login page
     *
     * Always returns true to make this page publicly accessible
     *
     * @param bool $disable Not used
     *
     * @return bool
     */
    public function viewAccess($disable = false) {

        return true;
    }

    /**
     * Post processing
     *
     * @return void
     *
     * @since 1.9.1.0
     */
    public function postProcess() {

        if (Tools::isSubmit('submitLogin')) {
            $this->processLogin();
        } else if (Tools::isSubmit('submitForgot')) {
            $this->processForgot();
        }

    }

    /**
     * Process login
     *
     * @return void
     *
     * @since 1.9.1.0
     */
    public function processLogin() {

        /* Check fields validity */
        $passwd = trim(Tools::getValue('passwd'));
        $email = trim(Tools::getValue('email'));

        if (!Validate::isEmail($email)) {
            $this->errors[] = Tools::displayError('Invalid email address.');
        }

        if (!Validate::isPasswdAdmin($passwd)) {
            $this->errors[] = Tools::displayError('Invalid password.');
        }

        if (!count($this->errors)) {
            // Find employee
            $this->context->employee = new Employee();
            $isEmployeeLoaded = $this->context->employee->getByEmail($email, $passwd);
            $employeeAssociatedShop = $this->context->employee->getAssociatedShops();

            if (!$isEmployeeLoaded
                || (!$employeeAssociatedShop && !$this->context->employee->isSuperAdmin())) {
                $this->errors[] = Tools::displayError('The employee does not exist, or the password provided is incorrect.');
                $this->context->employee->logout();
            } else {
                Logger::addLog(sprintf($this->l('Back Office connection from %s', 'AdminTab', false, false), Tools::getRemoteAddr()), 1, null, '', 0, true, (int) $this->context->employee->id);
                $this->context->employee->log_in = 1;
                $this->context->employee->last_timestamp = time();
                $this->context->employee->update();

                $this->context->employee->remote_addr = (int) ip2long(Tools::getRemoteAddr());
                // Update cookie
                $cookie = $this->context->cookie;
                $cookie->id_employee = $this->context->employee->id;
                $cookie->email = $this->context->employee->email;
                $cookie->profile = $this->context->employee->id_profile;
                $cookie->passwd = $this->context->employee->passwd;
                $cookie->remote_addr = $this->context->employee->remote_addr;

                if (!Tools::getValue('stay_logged_in')) {
                    $cookie->last_activity = time();
                } else {
                    // Needed in some edge cases, see Github issue #399.
                    unset($cookie->last_activity);
                }

                $cookie->write();

                // If there is a valid controller name submitted, redirect to it

                if (isset($_POST['redirect']) && Validate::isControllerName($_POST['redirect'])) {
                    $url = $this->context->link->getAdminLink($_POST['redirect']);
                } else {
                    $tab = new Tab((int) $this->context->employee->default_tab);
                    $url = $this->context->link->getAdminLink($tab->class_name);
                }

                if (Tools::isSubmit('ajax')) {
                    $this->ajaxDie(json_encode(['hasErrors' => false, 'redirect' => $url]));
                } else {
                    $this->redirect_after = $url;
                }

            }

        }

        if (Tools::isSubmit('ajax')) {
            $this->ajaxDie(json_encode(['hasErrors' => true, 'errors' => $this->errors]));
        }

    }

    /**
     * Process password forgotten
     *
     * @return void
     *
     * @since 1.9.1.0
     */
    public function processForgot() {

        $employee = new Employee();
        $employeeExists = false;
        $nextEmailTime = PHP_INT_MAX;

        if (_PS_MODE_DEMO_) {
            $this->errors[] = Tools::displayError('This functionality has been disabled.');
        } else {
            $email = trim(Tools::getValue('email_forgot'));

            if (!Validate::isEmail($email)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
            } else {
                $employeeExists = $employee->getByEmail($email);

                if ($employeeExists) {
                    $nextEmailTime = strtotime($employee->last_passwd_gen . '+' . Configuration::get('PS_PASSWD_TIME_BACK') . ' minutes');
                }

            }

        }

        if (!count($this->errors)
            && $employeeExists
            && $nextEmailTime < time()) {
            $pwd = Tools::passwdGen(10, 'RANDOM');
            $employee->passwd = Tools::hash($pwd);
            $employee->last_passwd_gen = date('Y-m-d H:i:s', time());

            $tpl = $context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/employee_password.tpl');
            $tpl->assign([
                'email'     => $employee->email,
                'lastname'  => $employee->lastname,
                'firstname' => $employee->firstname,
                'passwd'    => $passwd,

            ]);
            $postfields = [
                'sender'      => [
                    'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
                    'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
                ],
                'to'          => [
                    [
                        'name'  => $employee->firstname . ' ' . $employee->lastname,
                        'email' => $employee->email,
                    ],
                ],

                'subject'     => 'Votre nouveau mot de passe',
                "htmlContent" => $tpl->fetch(),
            ];

            $result = Tools::sendEmail($postfields);
        }

        if (!count($this->errors)) {
            $this->ajaxDie(json_encode([
                'hasErrors' => false,
                'confirm'   => sprintf($this->l('A new password has been emailed to the given email address, if it wasn\'t done within the last %s minutes before.', 'AdminTab', false, false), Configuration::get('PS_PASSWD_TIME_BACK')),
            ]));
        } else if (Tools::isSubmit('ajax')) {
            $this->ajaxDie(json_encode(['hasErrors' => true, 'errors' => $this->errors]));
        }

    }

}
