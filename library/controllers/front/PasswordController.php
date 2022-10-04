<?php

/**
 * Class PasswordControllerCore
 *
 * @since 1.8.1.0
 */
class PasswordControllerCore extends FrontController
{
    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'password';
    /** @var bool $auth */
    public $auth = false;
    // @codingStandardsIgnoreEnd

    
    public function postProcess()
    {
        if (Tools::isSubmit('email')) {
            if (!($email = trim(Tools::getValue('email'))) || !Validate::isEmail($email)) {
                $this->errors[] = Tools::displayError('Invalid email address.');
            } else {
                $customer = new Customer();
                $customer->getByemail($email);
                if (!Validate::isLoadedObject($customer)) {
                    $this->errors[] = Tools::displayError('There is no account registered for this email address.');
                } elseif (!$customer->active) {
                    $this->errors[] = Tools::displayError('You cannot regenerate the password for this account.');
                } elseif ((strtotime($customer->last_passwd_gen.'+'.($minTime = (int) Configuration::get('EPH_PASSWD_TIME_FRONT')).' minutes') - time()) > 0) {
                    $this->errors[] = sprintf(Tools::displayError('You can regenerate your password only every %d minute(s)'), (int) $minTime);
                } else {
					$confirmation = Tools::getValue('confirmation');
                    $this->context->smarty->assign(['confirmation' => $confirmation, 'customer_email' => $customer->email]);
                    
                }
            }
        } 
    }

    public function initContent()
    {
        parent::initContent();
        $this->setTemplate(_EPH_THEME_DIR_.'password.tpl');
    }
}
