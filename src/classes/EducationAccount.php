<?php

/**
 * @since 2.1.0.0
 */
class EducationAccountCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_account',
		'primary' => 'id_education_account',
		'fields'  => [
			'id_education'            => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'sell_account_local'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'sell_account_cee'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'sell_account_export'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'sell_account_notax'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'purchase_account_local'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'purchase_account_cee'    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'purchase_account_import' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'purchase_account_notax'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
		],
	];

	public $id_education;
	public $sell_account_local = 816;
	public $sell_account_cee = 816;
	public $sell_account_export = 816;
	public $sell_account_notax = 816;
	public $purchase_account_local = 582;
	public $purchase_account_cee = 582;
	public $purchase_account_import = 582;
	public $purchase_account_notax = 582;

	public $sell_local;
	public $sell_cee;
	public $sell_export;
	public $sell_notax;
	public $purchase_local;
	public $purchase_cee;
	public $purchase_import;
	public $purchase_notax;

	/**
	 * EducationFamilyCore constructor.
	 *
	 * @param int|null $id
	 * @param int|null $idLang
	 * @param int|null $idShop
	 *
	 * @since 2.1.0.0
	 */
	public function __construct($id = null, $idLang = null, $idShop = null) {

		parent::__construct($id, $idLang, $idShop);
		$this->sell_local = $this->getAccount($this->sell_account_local);
		$this->sell_cee = $this->getAccount($this->sell_account_cee);
		$this->sell_export = $this->getAccount($this->sell_account_export);
		$this->sell_notax = $this->getAccount($this->sell_account_notax);
		$this->purchase_local = $this->getAccount($this->purchase_account_local);
		$this->purchase_cee = $this->getAccount($this->purchase_account_cee);
		$this->purchase_import = $this->getAccount($this->purchase_account_import);
		$this->purchase_notax = $this->getAccount($this->purchase_account_notax);
	}

	public function getAccount($idAccount) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('account')
				->from('stdaccount')
				->where('`id_stdaccount` = ' . $idAccount)
		);

	}

	public static function getEducationAccount($ideducation, $field) {

	
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select($field)
				->from('education_account')
				->where('`id_education` = ' . $ideducation)
		);

	}

	public static function getDefaultEducationAccount($field) {

		$account = new EducationAccount();
		return $account->$field;
	}

	public static function getIdEducationAccount($ideducation) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('id_education_account')
				->from('education_account')
				->where('`id_education` = ' . $ideducation)
		);

	}

	public function add($autoDate = true, $nullValues = true) {

		if (!parent::add($autoDate, $nullValues)) {
			return false;
		}

		return true;
	}

	public static function applyFamilyAccount($idEducation, EducationFamily $family) {

		$idEducationAccount = EducationAccount::getIdEducationAccount($idEducation);
		$educationAccount = new EducationAccount($idEducationAccount);
		$educationAccount->sell_account_local = $family->sell_account_local;
		$educationAccount->sell_account_cee = $family->sell_account_cee;
		$educationAccount->sell_account_export = $family->sell_account_export;
		$educationAccount->sell_account_notax = $family->sell_account_notax;
		$educationAccount->purchase_account_local = $family->purchase_account_local;
		$educationAccount->purchase_account_cee = $family->purchase_account_cee;
		$educationAccount->purchase_account_import = $family->purchase_account_import;
		$educationAccount->purchase_account_notax = $family->purchase_account_notax;

		if ($educationAccount->update()) {
			return true;
		}

		return false;

	}

}
