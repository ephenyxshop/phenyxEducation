<?php

/**
 * @since 1.9.1.0
 */
class EducationTrackingCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'education_tracking',
        'primary'   => 'id_education_tracking',
        'fields'    => [
            'id_license' => ['type' => self::TYPE_INT, 'required' => true],
			'id_student_education' => ['type' => self::TYPE_INT, 'required' => true],
			'title' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 20],
			'lastname'                   => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'firstname'                  => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'birthname'                  => ['type' => self::TYPE_STRING, 'size' => 32],
			'email'                      => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'address1'        => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'required' => true, 'size' => 128],
            'address2'        => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
            'postcode'        => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12],
            'city'            => ['type' => self::TYPE_STRING,  'required' => true, 'size' => 64],
			'phone_mobile'    => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
			'supplyName'    => ['type' => self::TYPE_STRING, 'size' => 32],
			'session'        => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 128],
			'date_begin'        => ['type' => self::TYPE_DATE],
			'tracking'    => ['type' => self::TYPE_STRING, 'size' => 64],
			'date_send'                   => ['type' => self::TYPE_DATE],
			'date_receipt'                   => ['type' => self::TYPE_DATE],
        ],
    ];
    public $id_license;
    public $id_student_education;
	public $date_begin;
    // @codingStandardsIgnoreEnd
    public $title;
	public $lastname;
	/** @var string Firstname */
	public $firstname;
	
	public $birthname;
	public $email;
	public $address1;
    /** @var string Address second line (optional) */
    public $address2;
    /** @var string Postal code */
    public $postcode;
    /** @var string City */
    public $city;
	
	public $phone_mobile;
	
	public $supplyName;
	
	public $session;
	
	public $tracking;
	
	public $date_send;
	
	public $date_receipt;

    /**
     * GenderCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     * @param int|null $idShop
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, $idLang, $idShop);
    }
	
	public static function getTrackingByMonth($idLicense, $dateStart, $dateEnd) {
		
		
		return Db::getInstance()->executeS(
			(new DbQuery())
			->select('*')
			->from('education_tracking')
			->where('`id_license` = ' . $idLicense)
			->where('`date_begin` >= \'' . $dateStart . '\'')
			->where('`date_begin` <= \'' . $dateEnd . '\'')
		);
	}

    
	
	public static function trackingExist($idLicense, $idStudentEducation) {
		
		$idTracking = Db::getInstance()->getValue(
			(new DbQuery())
			->select('`id_education_tracking`')
			->from('education_tracking')
			->where('`id_license` = ' . $idLicense)
			->where('`id_student_education` = ' . $idStudentEducation)
		);
		
		if($idTracking > 0) {
			return true;
		}
		return false;
	}
}
