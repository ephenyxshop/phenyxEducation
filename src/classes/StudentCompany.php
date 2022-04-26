<?php

/**
 * Class CustomerCore
 *
 * @since 2.1.0.0
 */
class StudentCompany extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_company',
		'primary' => 'id_student_company',
		'fields'  => [
			'company_name'      => ['type' => self::TYPE_STRING, 'size' => 128],
			'siret'             => ['type' => self::TYPE_STRING, 'validate' => 'isSiret'],
			'ape'               => ['type' => self::TYPE_STRING, 'validate' => 'isApe'],
			'id_gender'         => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'firstname'         => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'lastname'          => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'active'            => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'email'             => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'phone'             => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'address'           => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'address2'          => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'city'              => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 64],
			'zipcode'           => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12],
			'id_state'          => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_country'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_range_employee' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'notes'             => ['type' => self::TYPE_STRING, 'size' => 128],
			'date_add'          => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'          => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	public $company_name;
	public $siret;
	public $ape;
	public $id_gender;
	public $firstname;
	public $lastname;
	public $active;
	public $email;
	public $phone;
	public $address;
	public $address2;
	public $city;
	public $zipcode;
	public $id_state;
	public $id_country;
	public $id_range_employee;
	public $notes;
	public $date_add;
	public $date_upd;
	public $range_employee;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null) {

		parent::__construct($id);

	}

	public static function getEmployeeRange() {

		$return = [];
		$ranges = Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('range_employee')
				->orderBy('delimiter1 ASC')
		);

		foreach ($ranges as $range) {
			$step1 = $range['delimiter1'];
			$step2 = $range['delimiter2'];

			if (empty($step2)) {
				$step2 = ' et plus';
			} else {
				$step2 = ' à ' . $step2;
			}

			$return[$range['id_range_employee']] = $step1 . $step2;
		}

		return $return;
	}

}
