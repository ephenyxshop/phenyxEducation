<?php

class SaleAgentCore extends ObjectModel {

	public static $definition = [
		'table'   => 'sale_agent',
		'primary' => 'id_sale_agent',
		'fields'  => [
			'id_gender'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_student'             => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_customer'            => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'agent_code'            => ['type' => self::TYPE_STRING],
			'id_stdaccount'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'company'                => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'lastname'               => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'firstname'              => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'email'                  => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'passwd'                     => ['type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'required' => true, 'size' => 60],
			'password'                   => ['type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'size' => 60],
			'siret'                  => ['type' => self::TYPE_STRING, 'validate' => 'isSiret'],
			'active'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'contract'               => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'sale_commission_amount' => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'required' => true],
			'sale_commission_type'   => ['type' => self::TYPE_STRING, 'required' => true],
			'balance'                => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'cycle'                  => ['type' => self::TYPE_STRING, 'required' => true],
			'is_tax'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'log_in'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'last_timestamp'         => ['type' => self::TYPE_NOTHING],
			'date_add'               => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'               => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];

	public $id_gender;
	public $id_student;
	public $id_customer;
	public $agent_code;
	public $id_stdaccount = 335;
	public $lastname;
	public $firstname;
	public $email;
	public $passwd;
	public $password;
	public $company;
	public $siret;
	public $active = true;
	public $contract;
	public $sale_commission_amount;
	public $sale_commission_type = 'amount';
	public $balance;
	public $cycle;
	public $is_tax;
	public $log_in;

	public $last_timestamp;
	public $date_add;
	public $date_upd;

	public $phone_mobile;
	
	public $stdAccount;
	
	public $workin_plan = '{"monday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"tuesday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"wednesday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"thursday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"friday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"saturday":{"start":"09:00","end":"15:00","breaks":[{"start":"14:30","end":"15:00"}]},"sunday":null}';
	public $workin_break;
	public $working_plan_exceptions;

	public function __construct($id = null) {

		parent::__construct($id);

		if ($this->id) {
			$this->phone_mobile = $this->getPhoneMobile();
			$this->stdAccount = $this->getAccount();
			$this->workin_plan = $this->getWorkingPlan();
			$this->workin_break = $this->getWorkingBreak();
			$this->working_plan_exceptions = $this->getWorkingPlanException();
		}

		$this->image_dir = _PS_SALEAGENT_IMG_DIR_;

	}
	
	public function getWorkingPlan() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_plan')
				->from('sale_agent_settings')
				->where('`id_sale_agent` = ' . $this->id)
		);
	}
	public function getWorkingBreak() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_break')
				->from('sale_agent_settings')
				->where('`id_sale_agent` = ' . $this->id)
		);
	}
	
	public function getWorkingPlanException() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_plan_exceptions')
				->from('sale_agent_settings')
				->where('`id_sale_agent` = ' . $this->id)
		);
	}
	
	public function getAccount() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('account')
				->from('stdaccount')
				->where('`id_stdaccount` = ' . $this->id_stdaccount)
		);

	}

	public function add($autodate = true, $null_values = true) {

		$this->agent_code =  $this->generateAgentCode();
		$this->id_stdaccount = $this->generateSaleAgentAccount();
		
		$result = parent::add($autodate, $null_values);
		
		if($result) {
			$id_address = Address::getFirstCustomerAddressId($this->id_customer);
			if($id_address > 0) {
				$adress = new Address($id_address);
				$adress->duplicateObject();
				$adress->id_customer = 0;
				$adress->id_agent = $this->id;
				$adress->update();
			}
		}

		return true;
	}
	
	public function generateAgentCode() {
		
		$id_address = Address::getFirstCustomerAddressId($this->id_customer);
		$id_country = 8;
		if($id_address > 0) {
			$adress = new Address($id_address);
			$postcode = $adress->postcode;
			$id_country = $adress->id_country;		
		}

		$cc = Db::getInstance()->getValue('SELECT `id_sale_agent` FROM `' . _DB_PREFIX_ . 'sale_agent` ORDER BY `id_sale_agent` DESC') + 1;

		if (isset($postcode)) {

			if ($id_country != 8) {
				$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
			} else {
				$iso_code = substr($postcode, 0, 2);

				if ($iso_code >= 97) {
					$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
				}

			}

			$Shop_iso = 'CEF';
			return substr($postcode, 0, 2) . $Shop_iso . sprintf("%04s", $cc);
		} else {
			$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');

			$Shop_iso = 'CEF_' . $iso_code;

			return $Shop_iso . sprintf("%04s", $cc);
		}

	}
	
	public function generateSaleAgentAccount() {

		
		$idLang = Configuration::get('PS_LANG_DEFAULT');
		
		$name = '401' . $this->agent_code;
		
		
		$accountExist = StdAccount::getAccountByName($name);
		if($accountExist->id > 0) {
			$accountExist->id_stdaccount_type = 4;
			$accountExist->counterpart = 627;
			if($this->is_tax) {
				$accountExist->default_vat = 415;
			} else {
				$accountExist->vat_exonerate = 1;
			}
			$accountExist->name[$idLang] = $this->lastname.' '.$this->firstname;
			$accountExist->update();
			return $accountExist->id;
		} else {
			$account = new StdAccount();
			$account->account = '401' . $this->agent_code;
			$account->id_stdaccount_type = 4;
			$account->counterpart = 627;
			if($this->is_tax) {
				$account->default_vat = 415;
			} else {
				$account->vat_exonerate = 1;
			}
			$account->name[$idLang] = $this->lastname.' '.$this->firstname;
		
			$account->add();
			return $account->id;
		}
		
		
	}

	public function update($nullValues = false) {

		$success = parent::update(true);
		//$this->updateSaleAgentAccount();

		return $success;
	}

	public function delete() {

		Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'sale_agent_commission WHERE id_sale_agent=' . (int) $this->id);
		return parent::delete();
	}
	
	public function getCommissionDue() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('sac.id_sale_agent_commission, CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, es.name as sessionName, sac.amount, sac.due, se.id_student_education')
					->from('sale_agent_commission', 'sac')
					->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
					->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
					->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
					->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
					->orderBy('es.`session_date` ')
					->where('sac.id_sale_agent = ' . $this->id)
					->where('sac.due = 1')
					->where('sac.paid = 0')
			);
	}

	public function getPhoneMobile() {

		$student = new Customer($this->id_customer);
		return $student->phone_mobile;

	}
	
	public function updateSaleAgentAccount() {
		
		$account = new StdAccount($this->id_stdaccount);
		
		if($this->is_tax) {
			$account->default_vat = 415;
		} else {
			$account->vat_exonerate = 1;
		}
		$account->update();
	}
	
	

	public function addCommissions($commission) {

		Db::getInstance()->autoexecute(_DB_PREFIX_ . 'sale_agent_commission', $commission, 'INSERT');
	}

	public function updateCommissions($commission) {

		Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'sale_agent_commission WHERE id_sale_agent=' . (int) $this->id);
		Db::getInstance()->autoexecute(_DB_PREFIX_ . 'sale_agent_commission', $commission, 'INSERT');
	}

	public function getSaleAgentStudent() {

		$students = [];
		$context = Context::getContext();
		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_customer`')
				->from('student_education')
				->where('`id_sale_agent` = ' . $this->id)
				->where('`deleted` = 0')
		);

		foreach ($educations as $education) {
			
			$customer = new Customer($education['id_customer']);
			$id_address = Address::getFirstCustomerAddressId($customer->id);
			$address = new Address((int) $id_address);
			$customer->address_zipcode = $address->postcode;
			$customer->address_city = $address->city;
			$students[$education['id_customer']] = $customer;
		}

		return $students;
	}

	public function getStudentEducationByIdStudent($idStudent) {

		$return = [];
		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('s.id_student_education')
				->from('student_education', 's')
				->where('s.`id_customer` = ' . $idStudent)
				->where('s.`id_sale_agent` = ' . $this->id)
		);

		if (is_array($educations) && count($educations)) {

			foreach ($educations as $education) {

				$return[] = new StudentEducation($education['id_student_education']);
			}

		}

		return $return;

	}

	public function getStudentEducationByAgent($idStudent) {

		$context = Context::getContext();
		$return = [];

		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('s.*, es.`name` as sessionDate, est.`name` as state')
				->from('student_education', 's')
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = s.`id_student_education_state` AND est.`id_lang` = ' . $context->language->id)
				->leftJoin('education_session', 'es', 'es.`id_education_session` = s.`id_education_session`')
				->where('s.`id_customer` = ' . $idStudent)
				->where('s.`id_sale_agent` = ' . $this->id)
		);

		if (is_array($educations) && count($educations)) {

			foreach ($educations as $education) {
				$details = Education::getEducationDetails($education['id_education'], $education['id_education_attribute'], false);

				foreach ($details as $key => $value) {
					$education[$key] = $value;
				}

				$return[] = $education;
			}

		}

		return $return;

	}

	public function getCommission() {

		$saleCommission = [];
		$commissions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_sale_agent_commission')
				->from('sale_agent_commission')
				->where('id_sale_agent = ' . (int) $this->id)
				->orderBy('id_education_session')
				->orderBy('id_student_education')
		);

		foreach ($commissions as $commission) {
			$saleCommission[] = new SaleAgentCommission($commission['id_sale_agent_commission']);
		}

		return $saleCommission;
	}

	public function getHistoryCommissions() {

		$educations = [];
		$sessions = EducationSession::getClosedSessions();
		$context = Context::getContext();

		foreach ($sessions as $session) {

			$education = Db::getInstance()->executeS(
				(new DbQuery())
				->select('se.*, s.firstname, s.lastname, s.email, a.phone_mobile, es.name as sessionName, gl.name as title, est.`name` as state')
				->from('student_education', 'se')
				->leftJoin('customer', 's', 's.id_customer = se.id_customer')
				->leftJoin('address', 'a', 'a.id_customer = se.id_customer')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = s.`id_gender` AND gl.`id_lang` = ' . $context->language->id)
				->leftJoin('education_session', 'es', 'es.id_education_session = ' . (int) $session->id)
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = se.`id_student_education_state` AND est.`id_lang` = ' . $context->language->id)
				->where('se.id_education_session = ' . (int) (int) $session->id)
				->where('se.id_sale_agent = ' . (int) $this->id)
				->where('se.deleted = 0')
			);

			if (is_array($education) && count($education)) {

				foreach ($education as &$student) {

					$formations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

					foreach ($formations as $key => $value) {
						$student[$key] = $value;
					}

					if ($student['id_student_education_state'] > 3) {
						$student['valid'] = 'valid';
					} else {
						$student['valid'] = '';
					}

					$lenght = explode(":", $student['education_lenghts']);
					$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);
					$student['ratio'] = round($time * 100 / $student['hours'], 2);
				}

				$educations[$session->name][] = ['educations' => $education, 'total' => count($education) * $this->sale_commission_amount];
			}

		}

		return $educations;
	}

	public function getCurrentEducation() {

		$educations = [];
		$sessions = EducationSession::getCurrentSessions();

		$context = Context::getContext();
		
		if($this->id == 0) {
			return [];
		}
		
		
		foreach ($sessions as $session) {
			
			
			$education = Db::getInstance()->executeS(
				(new DbQuery())
				->select('se.*, s.firstname, s.lastname, s.email, a.phone_mobile, es.name as sessionName, gl.name as title, est.`name` as state')
				->from('student_education', 'se')
				->leftJoin('customer', 's', 's.id_customer = se.id_customer')
				->leftJoin('address', 'a', 'a.id_customer = se.id_customer')
				->leftJoin('gender_lang', 'gl', 'gl.`id_gender` = s.`id_gender` AND gl.`id_lang` = ' . $context->language->id)
				->leftJoin('education_session', 'es', 'es.id_education_session = ' . (int) $session->id)
				->leftJoin('student_education_state_lang', 'est', 'est.`id_student_education_state` = se.`id_student_education_state` AND est.`id_lang` = ' . $context->language->id)
				->where('se.id_education_session = ' . (int) (int) $session->id)
				->where('se.id_sale_agent = ' . (int) $this->id)
				->where('se.deleted = 0')
				->orderBy('es.session_date')
			);

			if (is_array($education) && count($education)) {
				$i = 0;
				$total = 0;

				foreach ($education as &$student) {

					$formations = Education::getEducationDetails($student['id_education'], $student['id_education_attribute'], false);

					foreach ($formations as $key => $value) {
						$student[$key] = $value;
					}

					if ((int) $student['id_student_education_state'] > 3) {
						$student['valid'] = 'valid';
						$i++;
					} else {
						$student['valid'] = 'unReached';
					}

					$lenght = explode(":", $student['education_lenghts']);
					$time = Tools::convertTimetoHex($lenght[0], $lenght[1]);
					if($student['hours'] > 0) {
						$student['ratio'] = round($time * 100 / $student['hours'], 2);					  
					} else {
						$student['ratio'] = 0;
					}
				}

				$total = count($education) * $this->sale_commission_amount;
				$totalValidate = $i * $this->sale_commission_amount;

				$educations[$session->name][] = ['educations' => $education, 'total' => $total, 'totalValidate' => $totalValidate];
			}

		}

		return $educations;
	}

	public function getSaleCommission($id) {

		$sql = 'SELECT `initial`, `refund`
				FROM `' . _DB_PREFIX_ . 'sale_agent_commission`
				WHERE id_sale_agent =' . $id;

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
	}

	public static function getProtocolMode() {

		if (isset($_SERVER['HTTPS'])) {

			if ($_SERVER['HTTPS'] == 1 || strtolower($_SERVER['HTTPS']) == 'https://') {
				return true;
			};
		}

		// $_SERVER['SSL'] exists only in some specific configuration

		if (isset($_SERVER['SSL'])) {

			if ($_SERVER['SSL'] == 1 || strtolower($_SERVER['SSL']) == 'https://') {
				return true;
			};
		}

		return false;
	}

	/**
	 * Return customers list
	 *
	 * @param null|bool $only_active Returns only active customers when true
	 * @return array Customers
	 */
	public static function getSaleAgents($only_active = true) {

		$sql = 'SELECT `id_sale_agent`, `email`, `firstname`, `lastname`
				FROM `' . _DB_PREFIX_ . 'sale_agent`
				WHERE 1 ' .
			($only_active ? ' AND `active` = 1' : '') . '
				ORDER BY `id_sale_agent` ASC';
		$agents =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		
		foreach($agents as &$agent) {
			
			$agent['settings'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('*')
				->from('sale_agent_settings')
				->where('`id_sale_agent` = '.$agent['id_sale_agent'])
			);
			
						
		}
		
		return $agents;
	}

	public static function getRemuneratadSaleAgents() {

		$sql = 'SELECT `id_sale_agent`
				FROM `' . _DB_PREFIX_ . 'sale_agent`
				WHERE sale_commission_amount > 0 ';

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}

	public static function getSaleAgentbyId($idAgent) {

		$sql = 'SELECT *
				FROM `' . _DB_PREFIX_ . 'sale_agent`
				WHERE id_sale_agent = ' . (int) $idAgent;
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);
	}

	public static function getSaleAgentbyIdStudent($idStudent) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_sale_agent`')
				->from('sale_agent')
				->where('`id_customer` = ' . $idStudent)
		);
	}

	public static function getOrderMargin($id_order) {

		$orderDetails = Db::getInstance()->ExecuteS('SELECT *
				FROM `' . _DB_PREFIX_ . 'order_detail`
				WHERE id_order = ' . (int) $id_order);

		$total_vt = 0;
		$total_ha = 0;

		foreach ($orderDetails as $detail) {
			$total_vt = $total_vt + $detail['unit_price_tax_excl'] * $detail['product_quantity'];
			$total_ha = $total_ha + $detail['purchase_supplier_price'] * $detail['product_quantity'];
		}

		$margin = $total_vt - $total_ha;
		return $margin;
	}

	public static function getAgentOrder($id_agent) {

		$orderDetails = Db::getInstance()->ExecuteS('SELECT sa.*, sac.*, c.firstname, c.lastname, c.company, o.date_add
				FROM `' . _DB_PREFIX_ . 'order_sale_agent` sa
				LEFT JOIN `' . _DB_PREFIX_ . 'sale_agent_commission` sac ON (sac.`id_sale_agent`= sa.`id_sale_agent`)
				LEFT JOIN `' . _DB_PREFIX_ . 'customer` c ON (c.`id_customer`= sa.`id_customer`)
				LEFT JOIN `' . _DB_PREFIX_ . 'orders` o ON (o.`id_order`= sa.`id_order`)
				WHERE sa.id_sale_agent = ' . (int) $id_agent);

		$total_due = 0;
		$total_paid = 0;

		foreach ($orderDetails as $order) {

			if ($order['cleared'] == 0) {
				$total_due = $total_due + $order['sale_commission'];
			} else {
				$total_paid = $total_paid + $order['sale_commission'];
			}

		}

		$orderDetails['total_due'] = $total_due;
		$orderDetails['total_paid'] = $total_paid;

		return $orderDetails;
	}

	public static function isFirstOrder($id_customer) {

		$result = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
		SELECT COUNT(`id_order`) AS used
		FROM `' . _DB_PREFIX_ . 'orders`
		WHERE `id_CUSTOMER` = ' . (int) $id_customer);

		return $result > 0 ? (int) $result : false;
	}

	public static function getRefund($id_order, $id_sale_agent) {

		$order = new Order($id_order);
		$isInitial = SaleAgent::isFirstOrder($order->id_customer);
		$refund = SaleAgent::getSaleCommission($id_sale_agent);

		if ($isInitial == 1) {
			$refund_percent = $refund['initial'];
		} else {
			$refund_percent = $refund['refund'];
		}

		$refund_amount = SaleAgent::getOrderMargin($order->id) * $refund_percent / 100;

		return $refund_amount;
	}

	public static function getBankAccount($idSaleAgent) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('bank_account')
				->where('`id_sale_agent` = ' . $idSaleAgent)
		);
	}
	
	public static function getAppointmentSaleAgent($idCef) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow((new DbQuery())
                        ->select('*')
                        ->from('sale_agent')
                        ->where('`id_sale_agent` =' . $idCef));
	}

}
