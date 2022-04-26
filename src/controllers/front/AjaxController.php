<?php

/**
 * Class MyAccountControllerCore
 *
 * @since 1.8.1.0
 */
class AjaxControllerCore extends FrontController {

	/** @var string $php_self */
	public $php_self = 'ajax';
	/** @var string $authRedirection */
	public $authRedirection = 'index';
	/** @var bool $ssl */
	public $ssl = true;

	public function initContent() {

		parent::initContent();

	}

	public function postProcess() {

		parent::postProcess();

		if ($action = Tools::getValue('action') && $action = 'generatePassword') {

			if (($token = Tools::getValue('token')) && ($idStudent = (int) Tools::getValue('id_student'))) {

				$email = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
					(new DbQuery())
						->select('c.`email`')
						->from('student', 'c')
						->where('c.`id_student` = ' . (int) $idStudent)
				);

				if ($email) {

					$student = new Student();
					$student->getByemail($email);
					$verif = md5($student->password);

					if (!Validate::isLoadedObject($student)) {
						$this->errors[] = Tools::displayError('Student account not found');
					} else
					if ($verif == $token) {
						$password = Tools::generateStrongPassword();
						$student->password = $password;
						$student->passwd = Tools::hash($password);

						if ($student->update()) {
							$tpl = $this->context->smarty->createTemplate(_PS_MAIL_DIR_ . '/fr/password.tpl');
							$tpl->assign([
								'email'     => $student->email,
								'lastname'  => $student->lastname,
								'firstname' => $student->firstname,
								'passwd'    => $password,
							]);
							$postfields = [
								'sender'      => [
									'name'  => "Service  Administratif ".Configuration::get('PS_SHOP_NAME'),
									'email' => 'no-reply@'.Configuration::get('PS_SHOP_URL'),
								],
								'to'          => [
									[
										'name'  => $student->firstname . ' ' . $student->lastname,
										'email' => $student->email,
									],
								],
								'subject'     => 'Votre nouveau mot de passe',
								"htmlContent" => $tpl->fetch(),
							];
							$result = Tools::sendEmail($postfields);
							Tools::redirect('index');
						} else {
							$this->errors[] = Tools::displayError('An error occurred with your account, which prevents us from sending you a new password. Please report this issue using the contact form.');
						}

					}

				} else {
					$this->errors[] = Tools::displayError('We cannot regenerate your password with the data you\'ve submitted.');
				}

			}

		}

	}

}
