<?php

class SaleAgentCommissionCore extends ObjectModel {

	public static $definition = [
		'table'   => 'sale_agent_commission',
		'primary' => 'id_sale_agent_commission',
		'fields'  => [
			'id_sale_agent'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_session' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_student_education' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'amount'               => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
			'due'                  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'paid'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'invoice_number'       => ['type' => self::TYPE_STRING],
			'payment_date'         => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'id_bank_account'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'date_add'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];

	public $id_sale_agent;
	public $id_education_session;
	public $id_student_education;
	public $amount;
	public $due;
	public $paid;
	public $invoice_number;
	public $payment_date;
	public $id_bank_account;
	public $date_add;
	public $date_upd;

	public $sessionName;

	public function __construct($id = null) {

		parent::__construct($id);

		if ($this->id) {
			$this->sessionName = $this->getSessionName();
		}

	}

	public function add($autodate = true, $null_values = true) {

		if (!parent::add($autodate, $null_values)) {
			return false;
		}

		return true;
	}

	public function update($nullValues = false) {

		$success = parent::update(true);

		return $success;
	}

	public function delete() {

		return parent::delete();
	}

	public function getSessionName() {

		$session = new EducationSession($this->id_education_session);
		return $session->name;
	}

	public static function getIdCommissionbyIdSession($idEducationSession) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('id_sale_agent_commission')
				->from('sale_agent_commission')
				->where('id_student_education = ' . (int) $idEducationSession)
		);
	}

	public static function getIdCommissionbyIdAgent($idAgent, $idEducationSession) {

		$commissions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('sac.id_sale_agent_commission, sac.id_student_education, sac.amount, sac.due, sac.paid, sac.id_sale_agent, sac.invoice_number, sa.firstname as agent_firstname, sa.lastname as agent_lastname, sa.email as agent_email,CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, se.`id_education_session`, es.name as sessionName')
				->from('sale_agent_commission', 'sac')
				->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
				->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
				->where('sac.`id_education_session` = ' . $idEducationSession)
				->where('sac.`id_sale_agent` = ' . $idAgent)
				->where('sac.`due` = 1')
		);

		return $commissions;
	}

	public static function getBulkCommissionbyId($selection) {

		$commissions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('sac.id_sale_agent_commission, sac.id_student_education, sac.amount, sac.due, sac.paid, sac.id_sale_agent, sac.invoice_number, sa.firstname as agent_firstname, sa.lastname as agent_lastname, sa.email as agent_email,CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, se.`id_education_session`, es.name as sessionName')
				->from('sale_agent_commission', 'sac')
				->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
				->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
				->where('sac.`id_sale_agent_commission` IN (' . $selection . ')')
		);

		return $commissions;
	}

	public static function getSaleAgentStatement() {

		$datas = [];

		$agents = SaleAgent::getRemuneratadSaleAgents();
		$today = date("Y-m-d");

		foreach ($agents as $agent) {

			$commissions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, es.name as sessionName, sac.amount, sac.due, sac.paid, sac.invoice_number, sac.payment_date, ba.bank_name, ba.iban')
					->from('sale_agent_commission', 'sac')
					->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
					->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
					->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
					->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
					->leftJoin('bank_account', 'ba', 'ba.`id_bank_account` = sac.`id_bank_account`')
					->orderBy('es.`session_date` ')
					->where('sac.id_sale_agent = ' . $agent['id_sale_agent'])
					->where('sac.due = 1')
					->where('es.`session_date` < \'' . $today . '\'')
			);

			if (is_array($commissions) && count($commissions)) {
				$totalDue = 0;
				$totalPaid = 0;

				foreach ($commissions as &$commission) {

					if ($commission['due'] == 1 && $commission['paid'] == 0) {
						$totalDue = $totalDue + $commission['amount'];
					}

					if ($commission['due'] == 1 && $commission['paid'] == 1) {
						$totalPaid = $totalPaid + $commission['amount'];
					}

				}

				$datas[$agent['id_sale_agent']] = [
					'totalDue'    => $totalDue,
					'totalPaid'   => $totalPaid,
					'commissions' => $commissions,
				];
			}

		}

		return $datas;

	}

	public static function getSaleAgentDueStatement() {

		$datas = [];

		$agents = SaleAgent::getRemuneratadSaleAgents();
		$today = date("Y-m-d");

		foreach ($agents as $agent) {

			$commissions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, es.name as sessionName, sac.amount, sac.due, sac.paid, sac.invoice_number, sac.payment_date, se.id_student_education')
					->from('sale_agent_commission', 'sac')
					->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
					->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
					->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
					->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
					->orderBy('es.`session_date` ')
					->where('sac.id_sale_agent = ' . $agent['id_sale_agent'])
					->where('sac.due = 1')
					->where('sac.paid = 0')
					->where('es.`session_date` < \'' . $today . '\'')
			);

			if (is_array($commissions) && count($commissions)) {
				$totalDue = 0;

				foreach ($commissions as &$commission) {

					if ($commission['due'] == 1 && $commission['paid'] == 0) {
						$totalDue = $totalDue + $commission['amount'];
					}

				}

				$datas[$agent['id_sale_agent']] = [
					'totalDue'    => $totalDue,
					'commissions' => $commissions,
				];
			}

		}

		return $datas;

	}

	public static function getInvoiceBySaleAgent($idSaleAgent) {

		$saleAgent = new SaleAgent($idSaleAgent);

		$invoices = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('SUM(sa.`amount`) as invoice_total, sa.`invoice_number`, sa.`payment_date`, ba.bank_name, ba.iban ')
				->from('sale_agent_commission', 'sa')
				->leftJoin('bank_account', 'ba', 'ba.`id_bank_account` = sa.`id_bank_account`')
				->where('sa.id_sale_agent = ' . $saleAgent->id)
				->where('sa.paid = 1')
				->groupBy('sa.`invoice_number`')
				->orderBy('sa.`payment_date`')
		);

		foreach ($invoices as &$invoice) {

			if ($saleAgent->is_tax == 1) {
				$invoice['total_wtax'] = round($invoice['invoice_total'] * 1.2, 2);
			} else {
				$invoice['total_wtax'] = $invoice['invoice_total'];
			}

		}

		return $invoices;
	}

	public static function getCommissionDueBySaleAgent($idSaleAgent) {

		$today = date("Y-m-d");
		$datas = [];

		$saleAgent = new SaleAgent($idSaleAgent);
		$grandTotal = 0;
		$grandTotalWTax = 0;

		$sessions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('DISTINCT(es.id_education_session), name as sessionName')
				->from('sale_agent_commission', 'sac')
				->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
				->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->orderBy('es.`session_date` ')
				->where('sac.id_sale_agent = ' . $idSaleAgent)
				->where('sac.due = 1')
				->where('sac.paid = 0')
				->where('es.`session_date` < \'' . $today . '\'')
				->orderBy('es.id_education_session ASC')
		);

		if (is_array($sessions) && count($sessions)) {

			foreach ($sessions as $session) {

				$commissions = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
					(new DbQuery())
						->select('CONCAT(s.`firstname`, \' \', s.`lastname`) AS `student`, es.name as sessionName, sac.amount')
						->from('sale_agent_commission', 'sac')
						->leftJoin('sale_agent', 'sa', 'sa.`id_sale_agent` = sac.`id_sale_agent`')
						->leftJoin('student_education', 'se', 'se.`id_student_education` = sac.`id_student_education`')
						->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
						->leftJoin('customer', 's', 's.`id_customer` = se.`id_customer`')
						->orderBy('es.`session_date` ')
						->where('sac.id_sale_agent = ' . $idSaleAgent)
						->where('es.id_education_session = ' . $session['id_education_session'])
						->where('sac.due = 1')
						->where('sac.paid = 0')
						->where('es.`session_date` < \'' . $today . '\'')
				);

				if (is_array($commissions) && count($commissions)) {
					$totalDue = 0;

					foreach ($commissions as &$commission) {
						$totalDue = $totalDue + $commission['amount'];

						if ($saleAgent->is_tax == 1) {
							$commission['amount_wtax'] = round($commission['amount'] * 1.2, 2);
						} else {
							$commission['amount_wtax'] = $commission['amount'];
						}

					}

				}

				$grandTotal = $grandTotal + $totalDue;

				$datas[$session['sessionName']] = [
					'totalDue'   => $totalDue,
					'educations' => $commissions,
				];
			}

			if ($saleAgent->is_tax == 1) {
				$grandTotalWTax = round($grandTotal * 1.2, 2);
			} else {
				$grandTotalWTax = $grandTotal;
			}

			$datas['grandTotal'] = $grandTotal;
			$datas['grandTotalWTax'] = $grandTotalWTax;
		}

		return $datas;

	}

}
