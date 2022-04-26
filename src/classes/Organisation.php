<?php

/**
 * Class CustomerCore
 *
 * @since 2.1.0.0
 */
class OrganisationCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'organisation',
		'primary' => 'id_organisation',
		'fields'  => [
			'company_name' => ['type' => self::TYPE_STRING, 'size' => 128],
			'siret'        => ['type' => self::TYPE_STRING, 'validate' => 'isSiret'],
			'ape'          => ['type' => self::TYPE_STRING, 'validate' => 'isApe'],
			'id_gender'    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'firstname'    => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'lastname'     => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'active'       => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'email'        => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'phone'        => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'phone_mobile' => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'address'      => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'address2'     => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
			'city'         => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 64],
			'zipcode'      => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12],
			'id_state'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_country'   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'notes'        => ['type' => self::TYPE_STRING, 'size' => 128],
			'date_add'     => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'     => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
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
	public $phone_mobile;
	public $address;
	public $address2;
	public $city;
	public $zipcode;
	public $id_state;
	public $id_country;
	public $notes;
	public $date_add;
	public $date_upd;

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

}