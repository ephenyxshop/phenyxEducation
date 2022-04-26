<?php

/**
 * Class ContactControllerCore
 *
 * @since 1.8.1.0
 */
class ContactControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'contact';
    /** @var bool $ssl */
    public $ssl = true;
    // @codingStandardsIgnoreEnd
    protected $student;

    public function init() {

        parent::init();
        $this->student = $this->context->student;
    }

    /**
     * Start forms process
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function postProcess() {

        parent::postProcess();

        if (isset($this->student->id)) {
            $_POST = array_map('stripslashes', $this->student->getFields());
        }

    }

    /**
     * Get Order ID
     *
     * @return int Order ID
     *
     * @since 1.8.1.0
     */
    protected function getOrder() {

        $idOrder = false;
        $orders = Order::getByReference(Tools::getValue('id_order'));

        if ($orders) {

            foreach ($orders as $order) {
                $idOrder = (int) $order->id;
                break;
            }

        }

        return (int) $idOrder;
    }

    /**
     * Set media
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'contact-form.css');
        $this->addJS('https://www.google.com/recaptcha/api.js?render=6LfzxkAdAAAAAOh051AesXTup8XZbTY7VVycfoWZ');
        $this->addJS(_THEME_JS_DIR_ . 'contact-form.js');
        Media::addJsDef([
            'AjaxContactLink' => $this->context->link->getPageLink('contact', true),

        ]);
    }

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        parent::initContent();

        $email = Tools::convertEmailToIdn(Tools::safeOutput(
            Tools::getValue(
                'from',
                ((isset($this->context->cookie) && isset($this->context->cookie->email) && Validate::isEmail($this->context->cookie->email)) ? $this->context->cookie->email : '')
            )
        ));
        $this->context->smarty->assign(
            [
                'errors'          => $this->errors,
                'email'           => $email,
                'student'         => $this->student,
                'fileupload'      => Configuration::get('PS_CUSTOMER_SERVICE_FILE_UPLOAD'),
                'max_upload_size' => (int) Tools::getMaxUploadSize(),
            ]
        );

        $this->context->smarty->assign(
            [
                'contacts' => Contact::getContacts($this->context->language->id),
                'message'  => html_entity_decode(Tools::getValue('message')),
            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'contact-form.tpl');
    }

    public function ajaxProcessNewStudentMessage() {

        $student = $this->context->student;
        $email = Tools::getValue("email");

        if (!$student->id) {
            $student->getByEmail($email);
        }

        $ct = new StudentThread();

        if (isset($student->id)) {
            $ct->id_student = (int) $student->id;
        }

        $ct->id_shop = (int) $this->context->shop->id;
        $ct->id_student_education = (int) $idOrder;

        if ($idEducation = (int) Tools::getValue('id_education')) {
            $ct->id_education = $idEducation;
        }

        $ct->id_contact = (int) Tools::getValue('id_contact');
        $ct->id_lang = (int) $this->context->language->id;
        $ct->email = $email;
        $ct->object = Tools::getValue("objetc");
        $ct->name = Tools::getValue("name");
        $ct->phone = Tools::getValue("phone");
        $ct->status = 'open';
        $ct->token = Tools::passwdGen(12);
        $ct->add();

        if ($ct->id) {
            $message = Tools::getValue('message');
            $contact = new Contact($ct->id_contact);
            $cm = new StudentMessage();
            $cm->id_student_thread = $ct->id;
            $cm->message = $message;
            $cm->ip_address = (int) ip2long(Tools::getRemoteAddr());
            $cm->user_agent = $_SERVER['HTTP_USER_AGENT'];

            if (!$cm->add()) {
                $this->errors[] = Tools::displayError('An error occurred while sending the message.');
            }

            $tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/contact_form.tpl');
            $tpl->assign([
                'message' => Tools::nl2br(stripslashes($message)),
                'email'   => $email,
                'name'    => $ct->name,
            ]);
            $postfields = [
                'sender'      => [
                    'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
                    'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
                ],
                'to'          => [
                    [
                        'name'  => Tools::getValue("name"),
                        'email' => $email,
                    ],
                ],
                'cc'          => [
                    [
                        'name'  => "Service  Contact ".Configuration::get('PS_SHOP_NAME'),
                        'email' => $contact->email,
                    ],
                ],
                'subject'     => 'Votre message a été envoyé avec succès',
                "htmlContent" => $tpl->fetch(),
            ];
            $result = Tools::sendEmail($postfields);

            $return = [
                'success' => true,
                'message' => 'Votre message a été envoyé avec succès',
            ];

            die(Tools::jsonEncode($return));
        }

    }

}
