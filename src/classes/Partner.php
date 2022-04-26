<?php

class PartnerCore extends ObjectModel {

	public static $definition = [
		'table'   => 'partner',
		'primary' => 'id_partner',
		'fields'  => [
			'company'                => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'lastname'               => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'firstname'              => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'email'                  => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'address_street'             => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'address_street2'            => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'address_city'               => ['type' => self::TYPE_STRING, 'size' => 64],
			'address_zipcode'            => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12],
			'phone'                      => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'phone_mobile'               => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'id_cms'                => ['type' => self::TYPE_INT],
			'active'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'date_add'               => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'               => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];

	
	public $lastname;
	public $firstname;
	public $email;
	public $company;
	public $siret;
	public $active = true;
	public $date_add;
	public $date_upd;
	public $phone;
	public $phone_mobile;
	public $address_street;
	public $address_street2;
	public $address_city;
	public $address_zipcode;
	public $id_cms;

	public function __construct($id = null) {

		parent::__construct($id);

		$this->image_dir = _PS_PARTNER_IMG_DIR_;

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
				ORDER BY `firstname` ASC';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
	}

	
}
