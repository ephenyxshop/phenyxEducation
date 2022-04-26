<?php

class PlatformCore extends ObjectModel {

	public static $definition = [
		'table'   => 'platform',
		'primary' => 'id_platform',
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
			'siret'                  => ['type' => self::TYPE_STRING, 'validate' => 'isSiret'],
			'plateform_cost'          => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'education_link'          => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
			'has_webservice'          => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'plarform_token'                 => ['type' => self::TYPE_NOTHING],
			'webservice_link'		=> ['type' => self::TYPE_STRING],
			'identifiant'                => ['type' => self::TYPE_STRING, 'size' => 64],
			'password'                => ['type' => self::TYPE_STRING, 'size' => 64],
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
	public $has_webservice;
	public $plarform_token;
	public $webservice_link;
	public $active = true;
	public $plateform_cost;
	public $education_link;
	public $date_add;
	public $date_upd;
	public $phone;
	public $phone_mobile;
	public $address_street;
	public $address_street2;
	public $address_city;
	public $address_zipcode;
	public $identifiant;
	public $password;

	public function __construct($id = null) {

		parent::__construct($id);

		

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


	public static function getPlatforms() {

        return new PhenyxShopCollection('Platform');
    }

	
}
