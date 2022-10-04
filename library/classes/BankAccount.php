<?php

class BankAccountCore extends PhenyxObjectModel {

	public $id;

	public $id_country;

	public $company_bank = 0;

	public $id_sale_agent = null;

	public $id_supplier = null;

	public $id_stdaccount = null;

	public $code;

	public $owner;

	public $bank_name;

	public $iban;

	public $swift;

	public $bban;

	public $ics;

	public $active;

	public $stdaccount;

	public $explodeIban;

	public $iban_cases;

	public $iban_lenghts;

	public static $definition = [
		'table'   => 'bank_account',
		'primary' => 'id_bank_account',
		'fields'  => [
			'id_country'    => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
			'company_bank'  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'id_sale_agent' => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
			'id_supplier'   => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
			'id_stdaccount' => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
			'code'          => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'owner'         => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
			'bank_name'     => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
			'iban'          => ['type' => self::TYPE_STRING],
			'bban'          => ['type' => self::TYPE_STRING],
			'swift'         => ['type' => self::TYPE_STRING, 'required' => true],
			'ics'           => ['type' => self::TYPE_STRING],
			'active'        => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
		],
	];

	public function __construct($id = null, $id_lang = null) {

		parent::__construct($id, $id_lang);

		if ($this->id) {
			$this->stdaccount = $this->getStdAccount();
			$this->explodeIban = Tools::str_rsplit($this->iban, 4);
			$this->iban_cases = count($this->explodeIban);
			$this->iban_lenghts = strlen($this->iban);
		}

	}

	public function getStdAccount() {

		if (!empty($this->id_stdaccount)) {
			$account = new StdAccount($this->id_stdaccount);
			return $account->account;
		}

	}
	
	public function isUsedForSaleAgent() {
		
		$isUsed = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('id_sale_agent_commission')
                ->from('sale_agent_commission')
                ->where('`id_bank_account` = ' . (int) $this->id)
        );
		if(is_array($isUsed) && count($isUsed)) {
			return false;
		}
		
		return true;

	}

	public function add($autodate = true, $null_values = false) {

		if (!parent::add($autodate, $null_values)) {
			return false;
		}

		return true;

	}

	public static function getBankAccounts($idLang = null) {

		if (is_null($idLang)) {
			$idLang = Context::getContext()->language->id;
		}

		$bankAccounts = new PhenyxShopCollection('BankAccount', $idLang);

		return $bankAccounts;
	}

	public static function getCountries($id_lang, $sepa = false) {

		$sql = 'SELECT cl.*, c.*
        FROM `' . _DB_PREFIX_ . 'bank_iban` c
        LEFT JOIN `' . _DB_PREFIX_ . 'country_lang` cl ON (c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $id_lang . ') ';

		if ($sepa) {
			$sql .= 'WHERE c.`sepa` = 1 ';
		}

		$sql .= 'ORDER BY c.iban DESC, cl.name ASC';
		return Db::getInstance()->ExecuteS($sql);
	}

	public static function hasIban($id_country) {

		return (Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'bank_iban`
            WHERE `id_country` =' . (int) $id_country));
	}

	public static function countryByIso($iso) {

		return (Db::getInstance()->getRow('
            SELECT *
            FROM `' . _DB_PREFIX_ . 'bank_iban`
            WHERE `iso_iban` = \'' . pSQL($iso) . '\''));
	}

	public static function getBankIdBySupplierId($idSupplier) {

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('id_bank_account')
				->from('bank_account')
				->where('id_supplier = ' . (int) $idSupplier)
		);
	}

	public static function getBanksBySupplierId($idSupplier) {

		$collection = new PhenyxShopCollection('BankAccount');
		$collection->where('id_supplier', '=', (int) $idSupplier);

		return $collection;
	}

	public static function getBanksByCustomerId($idCustomer) {

		$collection = new PhenyxShopCollection('BankAccount');
		$collection->where('id_student', '=', (int) $idCustomer);

		return $collection;
	}

}
