<?php

/**
 * Class MailCore
 *
 * @since 1.9.1.0
 */
class MailCore extends ObjectModel {

    const TYPE_HTML = 1;
    const TYPE_TEXT = 2;
    const TYPE_BOTH = 3;

    // @codingStandardsIgnoreStart
    /** @var string Recipient */
    public $recipient;
    /** @var string Template */
    public $template;
    /** @var string Subject */
    public $subject;
    /** @var int Language ID */
    public $id_lang;
    /** @var int Timestamp */
    public $date_add;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'mail',
        'primary' => 'id_mail',
        'fields'  => [
            'recipient' => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'copy_post' => false, 'required' => true, 'size' => 126],
            'template'  => ['type' => self::TYPE_STRING, 'validate' => 'isTplName', 'copy_post' => false, 'required' => true, 'size' => 62],
            'subject'   => ['type' => self::TYPE_STRING, 'validate' => 'isMailSubject', 'copy_post' => false, 'required' => true, 'size' => 254],
            'id_lang'   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false, 'required' => true],
            'date_add'  => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false, 'required' => true],
        ],
    ];

    /**
     * Send Email
     *
     * @param int    $idLang         Language ID of the email (to translate the template)
     * @param string $template       Template: the name of template not be a var but a string !
     * @param string $subject        Subject of the email
     * @param string $templateVars   Template variables for the email
     * @param string $to             To email
     * @param string $toName         To name
     * @param string $from           From email
     * @param string $fromName       To email
     * @param array  $fileAttachment Array with three parameters (content, mime and name). You can use an array of array to attach multiple files
     * @param bool   $mode_smtp      SMTP mode (deprecated)
     * @param string $templatePath   Template path
     * @param bool   $die            Die after error
     * @param int    $idShop         Shop ID
     * @param string $bcc            Bcc recipient (email address)
     * @param string $replyTo        Email address for setting the Reply-To header
     *
     * @return bool|int Whether sending was successful. If not at all, false, otherwise amount of recipients succeeded.
     * @throws PhenyxShopException
     */

    public static function SendSendInBlue(
        $idLang,
        $template,
        $subject,
        $templateVars,
        $to,
        $toName = null,
        $from = null,
        $fromName = null,
        $fileAttachment = null,
        $modeSmtp = null,
        $templatePath = _PS_MAIL_DIR_,
        $die = false,
        $idShop = null,
        $bcc = null,
        $replyTo = null,
        $replyToName = null,
        $bccName = null,
        $cc = null,
        $ccName = null
    ) {

        // allow hooks to modify input parameters
        $result = Hook::exec('actionEmailSendBefore', [
            'idLang'         => &$idLang,
            'template'       => &$template,
            'subject'        => &$subject,
            'templateVars'   => &$templateVars,
            'to'             => &$to,
            'toName'         => &$toName,
            'from'           => &$from,
            'fromName'       => &$fromName,
            'fileAttachment' => &$fileAttachment,
            'modeSmtp'       => &$modeSmtp,
            'templatePath'   => &$templatePath,
            'die'            => &$die,
            'idShop'         => &$idShop,
            'bcc'            => &$bcc,
            'replyTo'        => &$replyTo,
        ], null, true);

        if (is_array($result) && in_array(false, $result, true)) {
            return true;
        }

        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        $configuration = Configuration::getMultiple(
            [
                'PS_SHOP_EMAIL',
                'PS_SHOP_NAME',
                'PS_MAIL_TYPE',
            ],
            null,
            null,
            $idShop
        );

        $themePath = _PS_THEME_DIR_;

        // Get the path of theme by id_shop if exist

        if (is_numeric($idShop) && $idShop) {
            $shop = new Shop((int) $idShop);
            $themeName = $shop->getTheme();

            if (_THEME_NAME_ != $themeName) {
                $themePath = _PS_ROOT_DIR_ . '/themes/' . $themeName . '/';
            }

        }

        if (!isset($fromName) || !Validate::isMailName($fromName)) {
            $fromName = $configuration['PS_SHOP_NAME'];
            $sender['name'] = $configuration['PS_SHOP_NAME'];
        }

        if (!Validate::isMailName($fromName)) {
            $fromName = null;
        } else {
            $sender['name'] = $fromName;
        }

        // Sending an e-mail can be of vital importance for the merchant, when his password is lost for example, so we must not die but do our best to send the e-mail

        if (!isset($from) || !Validate::isEmail($from)) {
            //$from = $configuration['PS_SHOP_EMAIL'];
            $sender['email'] = $configuration['PS_SHOP_EMAIL'];
        }

        if (!Validate::isEmail($from)) {
            $from = null;
            //$sender['email'] = null;
        } else {
            $sender['email'] = Tools::convertEmailToIdn($from);
        }

        // It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem

        if (!is_array($to) && !Validate::isEmail($to)) {
            return static::logError(Tools::displayError('Error: parameter "to" is corrupted'), $die);
        }

        if (is_string($toName) && !empty($toName) && !Validate::isMailName($toName)) {
            $toName = null;
        } else
        if (!empty($toName)) {
            $recipient['name'] = $toName;
        } else {
            $recipient['name'] = $to;
        }

        if (is_array($to)) {

            foreach ($to as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($to)) {
            $recipient['email'] = Tools::convertEmailToIdn($to);
        }

        if (!is_null($bcc) && !is_array($bcc) && !Validate::isEmail($bcc)) {
            static::logError(Tools::displayError('Error: parameter "bcc" is corrupted'), $die);
            $bcc = null;
        }

        if (is_array($bcc)) {

            foreach ($bcc as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($bcc)) {
            $bcc['email'] = Tools::convertEmailToIdn($bcc);
        }

        if (is_string($bccName) && !empty($bccName) && !Validate::isMailName($bccName)) {
            $bccName = null;
        } else
        if (!empty($bbcName)) {
            $bcc['name'] = $bccName;
        }

        if (!is_null($cc) && !is_array($cc) && !Validate::isEmail($cc)) {
            static::logError(Tools::displayError('Error: parameter "cc" is corrupted'), $die);
            $cc = null;
        }

        if (is_array($cc)) {

            foreach ($cc as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($cc)) {
            //$cc = Tools::convertEmailToIdn($cc);
            $cc['email'] = Tools::convertEmailToIdn($cc);
        }

        if (is_string($ccName) && !empty($ccName) && !Validate::isMailName($ccName)) {
            $ccName = null;
        } else
        if (!empty($ccName)) {
            $cc['name'] = $ccName;
        }

        if (!is_null($replyTo) && !is_array($replyTo) && !Validate::isEmail($replyTo)) {
            static::logError(Tools::displayError('Error: parameter "replyTo" is corrupted'), $die);
            $replyTo = null;
        }

        if (is_array($replyTo)) {

            foreach ($replyTo as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($cc)) {
            $replyTo['email'] = Tools::convertEmailToIdn($replyTo);
        }

        if (is_string($replyToName) && !empty($replyToName) && !Validate::isMailName($replyToName)) {
            $replyToName = null;
        } else
        if (!empty($replyToName)) {
            $replyTo['name'] = $replyToName;
        }

        if (!is_array($templateVars)) {
            $templateVars = [];
        }

        // Do not crash for this error, that may be a complicated customer name

        if (!Validate::isTplName($template)) {
            return static::logError(Tools::displayError('Error: invalid e-mail template'), $die);
        }

        if (!Validate::isMailSubject($subject)) {
            return static::logError(Tools::displayError('Error: invalid e-mail subject'), $die);
        }

        $iso = Language::getIsoById((int) $idLang);
        $isoTemplate = $iso . '/' . $template;

        $moduleName = false;
        $overrideMail = false;

        if (preg_match('#' . $shop->physical_uri . 'modules/#', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath)) && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath), $res)) {
            $moduleName = $res[1];
        }

        if ($moduleName !== false && (file_exists($themePath . 'modules/' . $moduleName . '/mails/' . $isoTemplate . '.tpl') ||
            file_exists($themePath . 'modules/' . $moduleName . '/mails/' . $isoTemplate . '.tpl'))
        ) {
            $templatePath = $themePath . 'modules/' . $moduleName . '/mails/';
        } else
        if (file_exists($themePath . 'mails/' . $isoTemplate . '.tpl')) {
            $templatePath = $themePath . 'mails/';
            $overrideMail = true;
        }

        if (!file_exists($templatePath . $isoTemplate . '.tpl')) {
            return static::logError(Tools::displayError('Error - The following e-mail template is missing:') . ' ' . $templatePath . $isoTemplate . '.tpl', $die);
        }

        $templateHtml = '';
        $templateTxt = '';

        $subject = static::formatSubject($subject);

        $templateVars = array_map(['Tools', 'htmlentitiesDecodeUTF8'], $templateVars);
        $templateVars = array_map(['Tools', 'stripslashes'], $templateVars);

        if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
            $templateVars['shop_logo'] = Tools::getShopDomainSsl(true) . '/img/' . Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
        } else {

            if (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
                $templateVars['shop_logo'] = Tools::getShopDomainSsl(true) . '/img/' . Configuration::get('PS_LOGO', null, null, $idShop);
            } else {
                $templateVars['shop_logo'] = '';
            }

        }

        ShopUrl::cacheMainDomainForShop((int) $idShop);

        if ((Context::getContext()->link instanceof Link) === false) {
            Context::getContext()->link = new Link();
        }

        $templateVars['shop_name'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $idShop));
        $templateVars['shop_url'] = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, $idShop);
        $templateVars['my_account_url'] = Context::getContext()->link->getPageLink('my-account', true, Context::getContext()->language->id, null, false, $idShop);
        $templateVars['guest_tracking_url'] = Context::getContext()->link->getPageLink('guest-tracking', true, Context::getContext()->language->id, null, false, $idShop);
        $templateVars['history_url'] = Context::getContext()->link->getPageLink('history', true, Context::getContext()->language->id, null, false, $idShop);
        $templateVars['color'] = Tools::safeOutput(Configuration::get('PS_MAIL_COLOR', null, null, $idShop));
        // Get extra template_vars
        $extraTemplateVars = [];
        Hook::exec(
            'actionGetExtraMailTemplateVars', [
                'template'            => $template,
                'template_vars'       => $templateVars,
                'extra_template_vars' => &$extraTemplateVars,
                'id_lang'             => (int) $idLang,
            ], null, true
        );
        $templateVars = array_merge($templateVars, $extraTemplateVars);

        $context = Context::getContext();

        $tpl = $context->smarty->createTemplate($templatePath . $isoTemplate . '.tpl');

        foreach ($templateVars as $key => $value) {
            $key = str_replace('{', '', $key);
            $key = str_replace('}', '', $key);
            $tpl->assign($key, $value);
        }

        $content = $tpl->fetch();

        $postfields = [
            'sender' => $sender,
            'to'     => [$recipient],
        ];

        if (isset($bcc) && is_array($bcc)) {
            $postfields["bcc"] = $bcc;
        }

        if (isset($cc) && is_array($cc)) {
            $postfields['"cc"'] = $cc;
        }

        if (isset($replyTo) && is_array($replyTo)) {
            $postfields["replyTo"] = $replyTo;
        }

        $postfields["htmlContent"] = $content;
        $postfields["subject"] = $subject;
        $postfields['params'] = $templateVars;

        $result = Tools::sendEmail($postfields);

        return $result;

    }

    public static function Send(
        $idLang,
        $template,
        $subject,
        $templateVars,
        $to,
        $toName = null,
        $from = null,
        $fromName = null,
        $fileAttachment = null,
        $modeSmtp = null,
        $templatePath = _PS_MAIL_DIR_,
        $die = false,
        $idShop = null,
        $bcc = null,
        $replyTo = null
    ) {

        // allow hooks to modify input parameters
        $result = Hook::exec('actionEmailSendBefore', [
            'idLang'         => &$idLang,
            'template'       => &$template,
            'subject'        => &$subject,
            'templateVars'   => &$templateVars,
            'to'             => &$to,
            'toName'         => &$toName,
            'from'           => &$from,
            'fromName'       => &$fromName,
            'fileAttachment' => &$fileAttachment,
            'modeSmtp'       => &$modeSmtp,
            'templatePath'   => &$templatePath,
            'die'            => &$die,
            'idShop'         => &$idShop,
            'bcc'            => &$bcc,
            'replyTo'        => &$replyTo,
        ], null, true);

        // do NOT continue if any module returned false

        if (is_array($result) && in_array(false, $result, true)) {
            return true;
        }

        if (!$idShop) {
            $idShop = Context::getContext()->shop->id;
        }

        $configuration = Configuration::getMultiple(
            [
                'PS_SHOP_EMAIL',
                'PS_MAIL_METHOD',
                'PS_MAIL_SERVER',
                'PS_MAIL_USER',
                'PS_MAIL_PASSWD',
                'PS_SHOP_NAME',
                'PS_MAIL_SMTP_ENCRYPTION',
                'PS_MAIL_SMTP_PORT',
                'PS_MAIL_TYPE',
            ],
            null,
            null,
            $idShop
        );

        if ($configuration['PS_MAIL_METHOD'] == 4) {

            return Mail::SendSendInBlue(
                $idLang,
                $template,
                $subject,
                $templateVars,
                $to,
                $toName,
                $from,
                $fromName,
                $fileAttachment,
                $modeSmtp,
                $templatePath,
                $die,
                $idShop,
                $bcc,
                $replyTo,
                null,
                $bccName,
                $cc,
                $ccName
            );
        }

        $themePath = _PS_THEME_DIR_;

        // Get the path of theme by id_shop if exist

        if (is_numeric($idShop) && $idShop) {
            $shop = new Shop((int) $idShop);
            $themeName = $shop->getTheme();

            if (_THEME_NAME_ != $themeName) {
                $themePath = _PS_ROOT_DIR_ . '/frontShop/' . $themeName . '/';
            }

        }

        if (!isset($configuration['PS_MAIL_SMTP_ENCRYPTION']) || mb_strtolower($configuration['PS_MAIL_SMTP_ENCRYPTION']) === 'off') {
            $configuration['PS_MAIL_SMTP_ENCRYPTION'] = false;
        }

        if (!isset($configuration['PS_MAIL_SMTP_PORT'])) {
            $configuration['PS_MAIL_SMTP_PORT'] = 'default';
        }

        // Sending an e-mail can be of vital importance for the merchant, when his password is lost for example, so we must not die but do our best to send the e-mail

        if (!isset($from) || !Validate::isEmail($from)) {
            $from = $configuration['PS_SHOP_EMAIL'];
        }

        if (!Validate::isEmail($from)) {
            $from = null;
        }

        $from = Tools::convertEmailToIdn($from);

        // $from_name is not that important, no need to die if it is not valid

        if (!isset($fromName) || !Validate::isMailName($fromName)) {
            $fromName = $configuration['PS_SHOP_NAME'];
        }

        if (!Validate::isMailName($fromName)) {
            $fromName = null;
        }

        // It would be difficult to send an e-mail if the e-mail is not valid, so this time we can die if there is a problem

        if (!is_array($to) && !Validate::isEmail($to)) {
            return static::logError(Tools::displayError('Error: parameter "to" is corrupted'), $die);
        }

        if (is_array($to)) {

            foreach ($to as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($to)) {
            $to = Tools::convertEmailToIdn($to);
        }

        // if bcc is not null, make sure it's a vaild e-mail

        if (!is_null($bcc) && !is_array($bcc) && !Validate::isEmail($bcc)) {
            static::logError(Tools::displayError('Error: parameter "bcc" is corrupted'), $die);
            $bcc = null;
        }

        if (is_array($bcc)) {

            foreach ($bcc as &$address) {
                $address = Tools::convertEmailToIdn($address);
            }

        } else
        if (is_string($bcc)) {
            $bcc = Tools::convertEmailToIdn($bcc);
        }

        if (!is_array($templateVars)) {
            $templateVars = [];
        }

        // Do not crash for this error, that may be a complicated customer name

        if (is_string($toName) && !empty($toName) && !Validate::isMailName($toName)) {
            $toName = null;
        }

        if (!Validate::isTplName($template)) {
            return static::logError(Tools::displayError('Error: invalid e-mail template'), $die);
        }

        if (!Validate::isMailSubject($subject)) {
            return static::logError(Tools::displayError('Error: invalid e-mail subject'), $die);
        }

        /* Construct multiple recipients list if needed */
        $message = Swift_Message::newInstance();

        if (is_array($to) && isset($to)) {

            foreach ($to as $key => $addr) {
                $addr = trim($addr);

                if (!Validate::isEmail($addr)) {
                    return static::logError(Tools::displayError('Error: invalid e-mail address'), $die);
                }

                if (is_array($toName) && isset($toName[$key])) {
                    $addrName = $toName[$key];
                } else {
                    $addrName = $toName;
                }

                $addrName = (($addrName == null || $addrName == $addr || !Validate::isGenericName($addrName)) ? '' : self::mimeEncode($addrName));
                $message->addTo($addr, $addrName);
            }

            $toPlugin = $to[0];
        } else {
            /* Simple recipient, one address */
            $toPlugin = $to;
            $toName = (($toName == null || $toName == $to) ? '' : static::mimeEncode($toName));
            $message->addTo($to, $toName);
        }

        if (isset($bcc) && is_array($bcc)) {

            foreach ($bcc as $addr) {
                $addr = trim($addr);

                if (!Validate::isEmail($addr)) {
                    return static::logError(Tools::displayError('Error: invalid e-mail address'), $die);
                }

                $message->addBcc($addr);
            }

        } else
        if (isset($bcc)) {
            $message->addBcc($bcc);
        }

        try {
            /* Connect with the appropriate configuration */

            if ($configuration['PS_MAIL_METHOD'] == 2) {

                if (empty($configuration['PS_MAIL_SERVER']) || empty($configuration['PS_MAIL_SMTP_PORT'])) {
                    return static::logError(Tools::displayError('Error: invalid SMTP server or SMTP port'), $die);
                }

                $connection = Swift_SmtpTransport::newInstance($configuration['PS_MAIL_SERVER'], $configuration['PS_MAIL_SMTP_PORT'], $configuration['PS_MAIL_SMTP_ENCRYPTION'])
                    ->setUsername($configuration['PS_MAIL_USER'])
                    ->setPassword($configuration['PS_MAIL_PASSWD']);

            } else {
                $connection = Swift_MailTransport::newInstance();
            }

            if (!$connection) {
                return false;
            }

            $swift = Swift_Mailer::newInstance($connection);
            /* Get templates content */
            $iso = Language::getIsoById((int) $idLang);

            if (!$iso) {
                return static::logError(Tools::displayError('Error - No ISO code for email'), $die);
            }

            $isoTemplate = $iso . '/' . $template;

            $moduleName = false;
            $overrideMail = false;

            // get templatePath

            if (preg_match('#' . $shop->physical_uri . 'modules/#', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath)) && preg_match('#modules/([a-z0-9_-]+)/#ui', str_replace(DIRECTORY_SEPARATOR, '/', $templatePath), $res)) {
                $moduleName = $res[1];
            }

            if ($moduleName !== false && (file_exists($themePath . 'modules/' . $moduleName . '/mails/' . $isoTemplate . '.txt') ||
                file_exists($themePath . 'modules/' . $moduleName . '/mails/' . $isoTemplate . '.html'))
            ) {
                $templatePath = $themePath . 'modules/' . $moduleName . '/mails/';
            } else
            if (file_exists($themePath . 'mails/' . $isoTemplate . '.txt') || file_exists($themePath . 'mails/' . $isoTemplate . '.html')) {
                $templatePath = $themePath . 'mails/';
                $overrideMail = true;
            }

            if (!file_exists($templatePath . $isoTemplate . '.txt') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT)) {
                return static::logError(Tools::displayError('Error - The following e-mail template is missing:') . ' ' . $templatePath . $isoTemplate . '.txt', $die);
            } else
            if (!file_exists($templatePath . $isoTemplate . '.html') && ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML)) {
                return static::logError(Tools::displayError('Error - The following e-mail template is missing:') . ' ' . $templatePath . $isoTemplate . '.html', $die);
            }

            $templateHtml = '';
            $templateTxt = '';
            Hook::exec(
                'actionEmailAddBeforeContent', [
                    'template'      => $template,
                    'template_html' => &$templateHtml,
                    'template_txt'  => &$templateTxt,
                    'id_lang'       => (int) $idLang,
                ], null, true
            );
            $templateHtml .= file_get_contents($templatePath . $isoTemplate . '.html');
            $templateTxt .= strip_tags(html_entity_decode(file_get_contents($templatePath . $isoTemplate . '.txt'), null, 'utf-8'));
            Hook::exec(
                'actionEmailAddAfterContent', [
                    'template'      => $template,
                    'template_html' => &$templateHtml,
                    'template_txt'  => &$templateTxt,
                    'id_lang'       => (int) $idLang,
                ], null, true
            );

            if ($overrideMail && file_exists($templatePath . $iso . '/lang.php')) {
                include_once $templatePath . $iso . '/lang.php';
            } else
            if ($moduleName && file_exists($themePath . 'mails/' . $iso . '/lang.php')) {
                include_once $themePath . 'mails/' . $iso . '/lang.php';
            } else
            if (file_exists(_PS_MAIL_DIR_ . $iso . '/lang.php')) {
                include_once _PS_MAIL_DIR_ . $iso . '/lang.php';
            } else {
                return static::logError(Tools::displayError('Error - The language file is missing for:') . ' ' . $iso, $die);
            }

            /* Create mail and attach differents parts */
            $subject = static::formatSubject($subject);
            $message->setSubject($subject);

            $message->setCharset('utf-8');

            /* Set Message-ID - getmypid() is blocked on some hosting */
            $message->setId(Mail::generateId());

            if (!($replyTo && Validate::isEmail($replyTo))) {
                $replyTo = $from;
            }

            if (isset($replyTo) && $replyTo) {
                $message->setReplyTo(Tools::convertEmailToIdn($replyTo));
            }

            $templateVars = array_map(['Tools', 'htmlentitiesDecodeUTF8'], $templateVars);
            $templateVars = array_map(['Tools', 'stripslashes'], $templateVars);

            if (Configuration::get('PS_LOGO_MAIL') !== false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $idShop))) {
                $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO_MAIL', null, null, $idShop);
            } else {

                if (file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
                    $logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
                } else {
                    $templateVars['{shop_logo}'] = '';
                }

            }

            ShopUrl::cacheMainDomainForShop((int) $idShop);
            /* don't attach the logo as */

            if (isset($logo)) {
                $templateVars['{shop_logo}'] = $message->embed(Swift_Image::fromPath($logo));
            }

            if ((Context::getContext()->link instanceof Link) === false) {
                Context::getContext()->link = new Link();
            }

            $templateVars['{shop_name}'] = Tools::safeOutput(Configuration::get('PS_SHOP_NAME', null, null, $idShop));
            $templateVars['{shop_url}'] = Context::getContext()->link->getPageLink('index', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{my_account_url}'] = Context::getContext()->link->getPageLink('my-account', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{guest_tracking_url}'] = Context::getContext()->link->getPageLink('guest-tracking', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{history_url}'] = Context::getContext()->link->getPageLink('history', true, Context::getContext()->language->id, null, false, $idShop);
            $templateVars['{color}'] = Tools::safeOutput(Configuration::get('PS_MAIL_COLOR', null, null, $idShop));
            // Get extra template_vars
            $extraTemplateVars = [];
            Hook::exec(
                'actionGetExtraMailTemplateVars', [
                    'template'            => $template,
                    'template_vars'       => $templateVars,
                    'extra_template_vars' => &$extraTemplateVars,
                    'id_lang'             => (int) $idLang,
                ], null, true
            );
            $templateVars = array_merge($templateVars, $extraTemplateVars);
            $swift->registerPlugin(new Swift_Plugins_DecoratorPlugin([$toPlugin => $templateVars]));

            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_TEXT) {
                $message->addPart($templateTxt, 'text/plain', 'utf-8');
            }

            if ($configuration['PS_MAIL_TYPE'] == Mail::TYPE_BOTH || $configuration['PS_MAIL_TYPE'] == Mail::TYPE_HTML) {
                $message->addPart($templateHtml, 'text/html', 'utf-8');
            }

            if ($fileAttachment && !empty($fileAttachment)) {

                if (!is_array(current($fileAttachment))) {
                    $fileAttachment = [$fileAttachment];
                }

                foreach ($fileAttachment as $key => $attachment) {

                    if (isset($attachment['content']) && isset($attachment['name']) && isset($attachment['mime'])) {

                        $message->attach(Swift_Attachment::newInstance()->setFilename($attachment['name'])->setContentType($attachment['mime'])->setBody($attachment['content']));
                    }

                }

            }

            /* Send mail */
            $message->setFrom([$from => $fromName]);
            $shouldSend = $configuration['PS_MAIL_METHOD'] != 3;
            $send = $shouldSend ? $swift->send($message) : true;

            ShopUrl::resetMainDomainCache();

            if ($send && Configuration::get('PS_LOG_EMAILS')) {
                $mail = new Mail();
                $mail->template = mb_substr($template, 0, 62);
                $mail->subject = mb_substr($subject, 0, 254);
                $mail->id_lang = (int) $idLang;
                $recipientsTo = $message->getTo();
                $recipientsCc = $message->getCc();
                $recipientsBcc = $message->getBcc();

                if (!is_array($recipientsTo)) {
                    $recipientsTo = [];
                }

                if (!is_array($recipientsCc)) {
                    $recipientsCc = [];
                }

                if (!is_array($recipientsBcc)) {
                    $recipientsBcc = [];
                }

                foreach (array_merge($recipientsTo, $recipientsCc, $recipientsBcc) as $email => $recipientName) {
                    /** @var Swift_Address $recipient */
                    $mail->id = null;
                    $mail->recipient = mb_substr($email, 0, 126);
                    $mail->add();
                }

            }

            return $send;
        } catch (Swift_SwiftException $e) {
            Logger::addLog(
                'Swift Error: ' . $e->getMessage(),
                3,
                null,
                'Swift_Message'
            );

            return false;
        }

    }

    /**
     * MIME encode the string
     *
     * @param string $string  The string to encode
     * @param string $charset The character set to use
     * @param string $newline The newline character(s)
     *
     * @return mixed|string MIME encoded string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function mimeEncode($string, $charset = 'UTF-8', $newline = "\r\n") {

        if (!static::isMultibyte($string) && mb_strlen($string) < 75) {
            return $string;
        }

        $charset = mb_strtoupper($charset);
        $start = '=?' . $charset . '?B?';
        $end = '?=';
        $sep = $end . $newline . ' ' . $start;
        $length = 75 - mb_strlen($start) - mb_strlen($end);
        $length = $length - ($length % 4);

        if ($charset === 'UTF-8') {
            $parts = [];
            $maxchars = floor(($length * 3) / 4);
            $stringLength = mb_strlen($string);

            while ($stringLength > $maxchars) {
                $i = (int) $maxchars;
                $result = ord($string[$i]);

                while ($result >= 128 && $result <= 191) {
                    $result = ord($string[--$i]);
                }

                $parts[] = base64_encode(mb_substr($string, 0, $i));
                $string = mb_substr($string, $i);
                $stringLength = mb_strlen($string);
            }

            $parts[] = base64_encode($string);
            $string = implode($sep, $parts);
        } else {
            $string = chunk_split(base64_encode($string), $length, $sep);
            $string = preg_replace('/' . preg_quote($sep) . '$/', '', $string);
        }

        return $start . $string . $end;
    }

    /**
     * Check if a multibyte character set is used for the data
     *
     * @param string $data Data
     *
     * @return bool Whether the string uses a multibyte character set
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function isMultibyte($data) {

        $length = mb_strlen($data);

        for ($i = 0; $i < $length; $i++) {

            if (ord(($data[$i])) > 128) {
                return true;
            }

        }

        return false;
    }

    /**
     * @param null $idstring
     *
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected static function generateId($idstring = null) {

        $midparams = [
            'utctime'   => gmstrftime('%Y%m%d%H%M%S'),
            'randint'   => mt_rand(),
            'customstr' => (preg_match("/^(?<!\\.)[a-z0-9\\.]+(?!\\.)\$/iD", $idstring) ? $idstring : "swift"),
            'hostname'  => ((isset($_SERVER['SERVER_NAME']) && !empty($_SERVER['SERVER_NAME'])) ? $_SERVER['SERVER_NAME'] : php_uname('n')),
        ];

        return vsprintf("%s.%d.%s@%s", $midparams);
    }

    /**
     * Format email subject using email subject template
     *
     * @param $subject Unformatted email subject
     *
     * @return string
     *
     * @since   1.0.8
     * @version 1.0.8 Initial version
     */
    protected static function formatSubject($subject) {

        $idShop = Context::getContext()->shop->id;
        $template = Configuration::get('EPH_MAIL_SUBJECT_TEMPLATE', null, null, $idShop);

        if (!$template || strpos($template, '{subject}') === false) {
            $template = "[{shop_name}] {subject}";
        }

        if (preg_match_all('#\{[a-z0-9_]+\}#i', $template, $m)) {

            for ($i = 0, $total = count($m[0]); $i < $total; $i++) {
                $key = $m[0][$i];

                switch ($key) {
                case '{shop_name}':
                    $template = str_replace($key, Configuration::get('PS_SHOP_NAME', null, null, $idShop), $template);
                    break;
                case '{subject}':
                    $template = str_replace($key, $subject, $template);
                    break;
                }

            }

        }

        return $template;
    }

    /**
     * @param int $idMail Mail ID
     *
     * @return bool Whether removal succeeded
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     */
    public static function eraseLog($idMail) {

        return Db::getInstance()->delete('mail', 'id_mail = ' . (int) $idMail);
    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function eraseAllLogs() {

        return Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'mail');
    }

    /**
     * Send a test email
     *
     * @param bool        $smtpChecked    Is SMTP checked?
     * @param string      $smtpServer     SMTP Server hostname
     * @param string      $content        Content of the email
     * @param string      $subject        Subject of the email
     * @param bool        $type           Deprecated
     * @param string      $to             To email address
     * @param string      $from           From email address
     * @param string      $smtpLogin      SMTP login name
     * @param string      $smtpPassword   SMTP password
     * @param int         $smtpPort       SMTP Port
     * @param bool|string $smtpEncryption Encryption type. "off" or false disable encryption.
     *
     * @return bool|string True if succeeded, otherwise the error message
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function sendMailTest($smtpChecked, $smtpServer, $content, $subject, $type, $to, $from, $smtpLogin, $smtpPassword, $smtpPort = 25, $smtpEncryption) {

        //$result = false;
        $templateVars = null;
        $toName = null;
        $fromName = null;
        $fileAttachment = null;
        $modeSmtp = null;
        $templatePath = _PS_MAIL_DIR_;
        $die = false;
        $idShop = null;
        $bcc = null;
        $replyTo = null;
        $replyToName = null;
        $bccName = null;
        $cc = null;
        $ccName = null;

        if (Mail::Send(
            1,
            'test',
            $subject,
            $templateVars,
            $to,
            $toName,
            $from,
            $fromName,
            $fileAttachment,
            $modeSmtp,
            $templatePath,
            $die,
            $idShop,
            $bcc,
            $replyTo,
            $replyToName,
            $bccName,
            $cc,
            $ccName
        )) {
            die("ok");
        };

    }

    /**
     * This method is used to get the translation for email Object.
     * For an object is forbidden to use htmlentities,
     * we have to return a sentence with accents.
     *
     * @param string $string raw sentence (write directly in file)
     *
     * @return mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function l($string, $idLang = null, Context $context = null) {

        global $_LANGMAIL;

        if (!$context) {
            $context = Context::getContext();
        }

        if ($idLang == null) {
            $idLang = (!isset($context->language) || !is_object($context->language)) ? (int) Configuration::get('PS_LANG_DEFAULT') : (int) $context->language->id;
        }

        $isoCode = Language::getIsoById((int) $idLang);

        $fileCore = _PS_ROOT_DIR_ . '/mails/' . $isoCode . '/lang.php';

        if (file_exists($fileCore) && empty($_LANGMAIL)) {
            include $fileCore;
        }

        $fileTheme = _PS_THEME_DIR_ . 'mails/' . $isoCode . '/lang.php';

        if (file_exists($fileTheme)) {
            include $fileTheme;
        }

        if (!is_array($_LANGMAIL)) {
            return (str_replace('"', '&quot;', $string));
        }

        $key = str_replace('\'', '\\\'', $string);

        return str_replace('"', '&quot;', Tools::stripslashes((array_key_exists($key, $_LANGMAIL) && !empty($_LANGMAIL[$key])) ? $_LANGMAIL[$key] : $string));
    }

    /**
     * This method logs an error message and optionally terminates execution of
     * the script.
     *
     * @param string $message Error message to be logged.
     * @param bool   $die     Wether to die instead of returning.
     *
     * @return false This method always, if it returns, returns false.
     *
     * @since   1.0.7
     * @version 1.0.7 Initial version
     * @throws PhenyxShopException
     */
    private static function logError($message, $die) {

        Logger::addLog($message);

        if ($die) {
            die($message);
        } else {
            return false;
        }

    }

}
