<?php

/**
 * Class MyStudentControllerCore
 *
 * @since 1.8.1.0
 */
class MyStudentControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var bool $auth */
    public $auth = true;
    /** @var string $php_self */
    public $php_self = 'my-student';
    /** @var string $authRedirection */
    public $authRedirection = 'my-student';
    /** @var bool $ssl */
    public $ssl = true;
    // @codingStandardsIgnoreEnd

    /**
     * Set media
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();

        $this->addCSS(_AGENT_CSS_DIR_ . 'my-student.css');
        $this->addCSS(_AGENT_CSS_DIR_ . 'product.css');
        $this->addJS(_AGENT_JS_DIR_ . 'my-student.js');
        Media::addJsDef([
            'AjaxMyStudentLink' => $this->context->link->getPageLink('my-student', true),
            'AjaxRegisterLink'  => $this->context->link->getPageLink('register-student', true),

        ]);
    }

    /**
     * Assign template vars related to page content
     *
     * @see   FrontController::initContent()
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        parent::initContent();
        $agent = new SaleAgent($this->context->cookie->id_agent);
        $students = $agent->getSaleAgentStudent();

        $this->context->smarty->assign(
            [
                'students' => $students,
            ]
        );

        $this->setTemplate(_PS_AGENT_DIR_ . 'my-student.tpl');
    }

    public function ajaxProcessEditStudent() {

        $id_student = Tools::getValue('idStudent');
        $student = new Customer($id_student);
		$id_address = Address::getFirstCustomerAddressId($student->id);
		$address = new Address((int) $id_address);
		$student->address_street = $address->address1;
		$student->address_streets = $address->address2;
		$student->address_zipcode = $address->postcode;
		$student->address_city = $address->city;
		$student->phone_mobile = $address->phone_mobile;

        $agent = new SaleAgent($this->context->cookie->id_agent);
        $this->setTemplate(_PS_AGENT_DIR_ . 'showStudent.tpl');

        $this->context->smarty->assign(
            [
                'educations' => $agent->getStudentEducationByIdStudent($id_student),
                'student'    => $student,
                'genders'    => Gender::getGenders(),
                'countries'  => Country::getCountries($this->context->language->id, true),
                'slots'      => EducationSession::getNextEducationSlot(),
            ]
        );

        $result = [
            'html'   => $this->context->smarty->fetch($this->template),
            'params' => $this->params,
        ];

        die(Tools::jsonEncode($result));
    }
	
	public function ajaxProcessUpdateStudent() {

        $id = (int) Tools::getValue('id_customer');

        if (isset($id) && !empty($id)) {
            /** @var ObjectModel $object */
            $student = new Customer($id);
            

            if (Validate::isLoadedObject($student)) {

                foreach ($_POST as $key => $value) {

                    if (property_exists($student, $key) && $key != 'id_customer') {

                        $student->{$key} = $value;
                    }

                }

                $result = $student->update();
				
				$id_address = Address::getFirstCustomerAddressId($student->id);
				$address = new Address((int) $id_address);
				$oldPhone = $address->phone_mobile;
				
				foreach ($_POST as $key => $value) {

                    if (property_exists($address, $key) && $key != 'id_address') {

                        

                        if ($key == 'phone_mobile' && Tools::getValue('id_address')) {

                            if ($value == $oldPhone) {
                                continue;
                            }

                            $mobile = str_replace(' ', '', Tools::getValue('phone_mobile'));

                            if (strlen($mobile) == 10 && $student->id_country == 8) {
                                $value = '+33' . substr($mobile, 1);
                            }

                        }

                        $address->{$key}
                        = $value;
                    }

                }
				$result = $address->update();
                if (!isset($result) || !$result) {
                    $this->errors[] = Tools::displayError('An error occurred while updating an object.') . ' <b>' . $this->table . '</b> (' . Db::getInstance()->getMsgError() . ')';
                } else {
                    $result = [
                        'success' => true,
                        'message' => $this->l('Mise à jour réussie'),
                    ];

                }

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
}
