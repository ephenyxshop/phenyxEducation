<?php

/**
 * Class CustomerCore
 *
 * @since 2.1.0.0
 */
class CustomerCore extends PhenyxObjectModel {

    protected static $_cache_customer_request = [];
	// @codingStandardsIgnoreStart
	/**
	 * @see PhenyxObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'customer',
		'primary' => 'id_customer',
		'fields'  => [
			'secure_key'                 => ['type' => self::TYPE_STRING, 'validate' => 'isMd5', 'copy_post' => false],
			'customer_code'              => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'lastname'                   => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'firstname'                  => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'birthname'                  => ['type' => self::TYPE_STRING, 'size' => 32],
			'email'                      => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
			'passwd'                     => ['type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'required' => true, 'size' => 60],
			'password'                   => ['type' => self::TYPE_STRING, 'size' => 60],
			'last_passwd_gen'            => ['type' => self::TYPE_STRING, 'copy_post' => false],
			'id_gender'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_tax_mode'                => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_stdaccount'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'birthday'                   => ['type' => self::TYPE_DATE],
			'newsletter'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'newsletter_date_add'        => ['type' => self::TYPE_DATE, 'copy_post' => false],
			'ip_registration_newsletter' => ['type' => self::TYPE_STRING, 'copy_post' => false],
			'optin'                      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'website'                    => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
			'company'                    => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'siret'                      => ['type' => self::TYPE_STRING, 'validate' => 'isSiret'],
			'ape'                        => ['type' => self::TYPE_STRING, 'validate' => 'isApe'],
			'vat_number'                 => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'activity_number'            => ['type' => self::TYPE_STRING,  'size' => 64],
			'outstanding_allow_amount'   => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat', 'copy_post' => false],
			'show_public_prices'         => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'id_risk'                    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false],
			'max_payment_days'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'copy_post' => false],
			'active'                     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'cach_last_education'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'deleted'                    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'note'                       => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'copy_post' => false, 'size' => 65000],
			'is_guest'                   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'birth_city'                 => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 64],
			'is_agent'                   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'id_default_group'           => ['type' => self::TYPE_INT, 'copy_post' => false],
			'id_lang'                    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
			'id_country'                 => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
            'is_admin'                   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],	
            'id_employee'                => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'copy_post' => false],
			'date_add'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];
	protected static $_defaultGroupId = [];
	protected static $_customerHasAddress = [];
	protected static $_customer_groups = [];
	
	/** @var string Secure key */
	public $secure_key;
	/** @var string protected note */
	public $note;
	/** @var int Gender ID */
	public $id_gender = 0;
	/** @var int TaxMode ID */
	public $id_tax_mode = 1;
	/** @var int Default group ID */
	public $id_default_group;
	/** @var int StdAccount ID */
	public $id_stdaccount = 355;
	/** @var int Current language used by the customer */
	public $id_lang;
	/** @var int Current Country of the customer */
	public $id_country;
	/** @var string Customer Code */
	public $customer_code;
	/** @var string Lastname */
	public $lastname;
	/** @var string Firstname */
	public $firstname;
	
	public $birthname;
	/** @var string Birthday (yyyy-mm-dd) */
	public $birthday = null;
	/** @var string e-mail */
	public $email;
	/** @var bool Newsletter subscription */
	public $newsletter;
	/** @var string Newsletter ip registration */
	public $ip_registration_newsletter;
	/** @var string Newsletter ip registration */
	public $newsletter_date_add;
	/** @var bool Opt-in subscription */
	public $optin;
	/** @var string WebSite * */
	public $website;
	/** @var string Company */
	public $company;
	/** @var string SIRET */
	public $siret;
	/** @var string APE */
	public $ape;
	/** @var string VAT number */
	public $vat_number;
	
	public $activity_number;
	/** @var float Outstanding allow amount (B2B opt) */
	public $outstanding_allow_amount = 0;
	/** @var int Show public prices (B2B opt) */
	public $show_public_prices = 0;
	/** @var int Risk ID (B2B opt) */
	public $id_risk;
	/** @var int Max payment day */
	public $max_payment_days = 0;
	/** @var int Password */
	public $passwd;
	
	public $password;
	/** @var string Datetime Password */
	public $last_passwd_gen;
	/** @var bool Status */
	public $active = true;
	
	public $cach_last_education;
	/** @var bool Status */
	public $is_guest = 0;
	
	public $is_agent;
    
    public $is_admin;
    
    public $id_employee;
	
	public $birth_city;
	/** @var bool True if carrier has been deleted (staying in database as deleted) */
	public $deleted = 0;
	/** @var string Object creation date */
	public $date_add;
	/** @var string Object last modification date */
	public $date_upd;
	public $years;
	public $days;
	public $months;
	/** @var int customer id_country as determined by geolocation */
	public $geoloc_id_country;
	/** @var int customer id_state as determined by geolocation */
	public $geoloc_id_state;
	/** @var string customer postcode as determined by geolocation */
	public $geoloc_postcode;
	/** @var bool is the customer logged in */
	public $logged = 0;
	/** @var int id_guest meaning the guest table, not the guest customer */
	public $id_guest;
	// @codingStandardsIgnoreEnd
	public $groupBox;

	public $tarif;
	
	public $address1;
	
	public $address2;
	
	public $postcode;
	
	public $city;
	
	public $phone_mobile;
	
	public $title;

	
	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public function __construct($id = null) {

		$this->id_default_group = (int) Configuration::get('EPH_CUSTOMER_GROUP');
		parent::__construct($id);

		if ($this->id) {
			$this->tarif = $this->getTarifName();
			$this->stdAccount = $this->getAccount();
			$id_address = Address::getFirstCustomerAddressId($this->id);
			$address = new Address((int) $id_address);
			$this->phone_mobile = $address->phone_mobile;
			$this->address1 = $address->address1;
			$this->address2 = $address->address2;
			$this->postcode = $address->postcode;
			$this->city = $address->city;
			$this->title = $this->getTitle();
		}

	}
	
	public function getTitle() {

		$context = Context::getContext();
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('gender_lang')
				->where('`id_lang` = ' . $context->language->id)
				->where('`id_gender` = ' . $this->id_gender)
		);
	}
	
	

	public function getAccount() {

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('account')
				->from('stdaccount')
				->where('`id_stdaccount` = ' . $this->id_stdaccount)
		);

	}

	public function getTarifName() {

		$context = Context::getContext();
		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('name')
				->from('group_lang')
				->where('`id_lang` = ' . $context->language->id)
				->where('`id_group` = ' . $this->id_default_group)
		);

	}
	
	public static function getStudentbyEmail($email) {

		$sql = new DbQuery();
		$sql->select('id_customer');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`email` = \'' . pSQL($email) . '\' ');

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);
	}


	static public function getTarifs() {

		return (Db::getInstance()->ExecuteS('
		SELECT *
		FROM `' . _DB_PREFIX_ . 'group_lang`
        WHERE `id_lang` = ' . Context::getContext()->language->id));
	}
	
	public static function checkEmail($email) {
											  
		$checkEmail = Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_customer`')
                ->from('customer')
                ->where('`email` = \'' . $email . '\'')
        );

        if ($checkEmail > 0) {
            
			return false;
        }
		
		return true;
	}
    
    public static function getCustomerForCacheRequest() {
        
        
		if (!isset(static::$_cache_customer_request['customer'])) {
            $file = fopen("testgetCustomerForCacheRequest.txt", "w");
		      $query = new DbQuery();
		      $query->select('a.`id_customer`, a.`customer_code`, a.`firstname`, a.`lastname`, a.`email`, a.`company`, a.`id_gender`, a.`active` AS `active`, a.`id_default_group`, a.birthday, a.`is_agent` , grl.name AS `tarif`, a.date_add, gl.name as title,  case when a.active = 1 then \'<div class="p-active"></div>\' else \'<div class="p-inactive"></div>\' end as active, case when a.active = 1 then 1 else 0 end as enable, ad.phone, ad.phone_mobile, ad.address1, ad.postcode, ad.city, se.id_education');
		      $query->from('customer', 'a');
              $query->leftJoin('address', 'ad', '(select max(ad.id_address) from eph_address where ad.`id_customer` = a.`id_customer`)');
              $query->leftJoin('student_education', 'se', '(select max(se.id_student_education) from eph_student_education where se.`id_customer` = a.`id_customer`)');
		      $query->leftJoin('gender_lang', 'gl', 'a.`id_gender` = gl.`id_gender` AND gl.`id_lang` = ' . Context::getContext()->language->id);
		      $query->leftJoin('group_lang', 'grl', 'grl.`id_group` = a.`id_default_group` AND grl.`id_lang` = ' . Context::getContext()->language->id);
              $query->limit(2000);
        
              fwrite($file, $query);
              static::$_cache_customer_request['customer'] = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($query);
		      return static::$_cache_customer_request['customer'];
        }
        return static::$_cache_customer_request['customer'];
       
	}

	
	public static function generateCustomerCode($id_country, $postcode = null) {

		$cc = Db::getInstance()->getValue('SELECT `id_customer` FROM `' . _DB_PREFIX_ . 'customer` ORDER BY `id_customer` DESC') + 1;

		if (isset($postcode)) {

			if ($id_country != 8) {
				$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
			} else {
				$iso_code = substr($postcode, 0, 2);

				if ($iso_code >= 97) {
					$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');
				}

			}

			$Shop_iso = 'ST';
			return substr($postcode, 0, 2) . $Shop_iso . sprintf("%04s", $cc);
		} else {
			$iso_code = Db::getInstance()->getValue('SELECT `iso_code` FROM `' . _DB_PREFIX_ . 'country` WHERE `id_country`= ' . $id_country . '');

			$Shop_iso = 'ST_' . $iso_code;

			return $Shop_iso . sprintf("%04s", $cc);
		}

	}
	
	public static function generateCustomerAccount(Customer $student, $postcode = null) {

		$affectation = Configuration::get('EPH_STUDENT_AFFECTATION');
		$idLang = Configuration::get('EPH_LANG_DEFAULT');
		
		$cc = Db::getInstance()->getValue('SELECT `id_stdaccount` FROM `' . _DB_PREFIX_ . 'stdaccount` ORDER BY `id_stdaccount` DESC');
		$iso_code = substr($postcode, 0, 2);
		$account = new StdAccount();
		$account->account = '411'.$iso_code.'ST' . $cc;
		$account->id_stdaccount_type = 5;
		$account->name[$idLang] = $student->lastname.' '.$student->firstname;
		
		$account->add();
		return $account->id;
	}

	public static function getCustomers($onlyActive = null) {

		$sql = new DbQuery();
		$sql->select('`id_customer`, `email`, `firstname`, `lastname`');
		$sql->from(bqSQL(static::$definition['table']));

		if ($onlyActive) {
			$sql->where('`active` = 1');
		}

		$sql->orderBy('`id_customer` ASC');

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($sql);
	}
	
	public function getUnansweredEvaluation() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_student_evaluation`')
				->from('student_evaluation')
				->where('`id_customer` = ' . $this->id)
				->where('`answered` = 0')
		);

	}
	
	public function hasEducation() {

		return (bool) Db::getInstance()->getRow(
			(new DbQuery())
				->select('*')
				->from('student_education')
				->where('`id_customer` = ' . (int) $this->id)
				->where('`deleted` = 0')
				->orderBy('`id_education_session` DESC')
		);

	}
	
	public static function getCustomersByEmail($email) {

		$sql = new DbQuery();
		$sql->select('*');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`email` = \'' . pSQL($email) . '\'');

		return Db::getInstance()->executeS($sql);
	}
	
	public static function isBanned($idCustomer) {

		if (!Validate::isUnsignedId($idCustomer)) {
			return true;
		}

		$cacheId = 'Customer::isBanned_' . (int) $idCustomer;

		if (!Cache::isStored($cacheId)) {
			$sql = new DbQuery();
			$sql->select('`id_customer`');
			$sql->from(bqSQL(static::$definition['table']));
			$sql->where('`id_customer` = ' . (int) $idCustomer);
			$sql->where('`active` = 1');
			$sql->where('`deleted` = 0');
			$result = (bool) !Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow($sql);
			Cache::store($cacheId, $result);

			return $result;
		}

		return Cache::retrieve($cacheId);
	}
	
	public static function customerExists($email, $returnId = false, $ignoreGuest = true) {

		if (!Validate::isEmail($email)) {

			if (defined('_EPH_MODE_DEV_') && _EPH_MODE_DEV_) {
				die(Tools::displayError('Invalid email'));
			}

			return false;
		}

		$sql = new DbQuery();
		$sql->select('`id_customer`');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`email` = \'' . pSQL($email) . '\'');

		if ($ignoreGuest) {
			$sql->where('`is_guest` = 0');
		}

		$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);

		return ($returnId ? (int) $result : (bool) $result);
	}
	
	public static function customerHasAddress($idCustomer, $idAddress) {

		$key = (int) $idCustomer . '-' . (int) $idAddress;

		if (!array_key_exists($key, static::$_customerHasAddress)) {
			static::$_customerHasAddress[$key] = (bool) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
				'
			SELECT `id_address`
			FROM `' . _DB_PREFIX_ . 'address`
			WHERE `id_customer` = ' . (int) $idCustomer . '
			AND `id_address` = ' . (int) $idAddress . '
			AND `deleted` = 0'
			);
		}

		return static::$_customerHasAddress[$key];
	}
	
	public static function resetAddressCache($idCustomer, $idAddress) {

		$key = (int) $idCustomer . '-' . (int) $idAddress;

		if (array_key_exists($key, static::$_customerHasAddress)) {
			unset(static::$_customerHasAddress[$key]);
		}

	}

	/**
	 * Count the number of addresses for a customer
	 *
	 * @param int $idCustomer Customer ID
	 *
	 * @return int Number of addresses
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public static function getAddressesTotalById($idCustomer) {

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			'
			SELECT COUNT(`id_address`)
			FROM `' . _DB_PREFIX_ . 'address`
			WHERE `id_customer` = ' . (int) $idCustomer . '
			AND `deleted` = 0'
		);
	}

	/**
	 * Light back office search for customers
	 *
	 * @param string   $query Searched string
	 * @param null|int $limit Limit query results
	 *
	 * @return array|false|mysqli_result|null|PDOStatement|resource Corresponding customers
	 * @throws PhenyxShopDatabaseException
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public static function searchByName($query, $limit = null) {

		$sqlBase = 'SELECT *
				FROM `' . _DB_PREFIX_ . 'customer`';
		$sql = '(' . $sqlBase . ' WHERE `email` LIKE \'%' . pSQL($query) . '%\')';
		$sql .= ' UNION (' . $sqlBase . ' WHERE `id_customer` = ' . (int) $query. ')';
		$sql .= ' UNION (' . $sqlBase . ' WHERE `lastname` LIKE \'%' . pSQL($query) . '%\')';
		$sql .= ' UNION (' . $sqlBase . ' WHERE `firstname` LIKE \'%' . pSQL($query) . '%\')';
		$sql .= ' UNION (' . $sqlBase . ' WHERE `customer_code` LIKE \'%' . pSQL($query) . '%\')';

		if ($limit) {
			$sql .= ' LIMIT 0, ' . (int) $limit;
		}

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($sql);
	}

	/**
	 * Search for customers by ip address
	 *
	 * @param string $ip Searched string
	 *
	 * @since 2.1.0.0
	 * @return array|false|null|PDOStatement
	 */
	public static function searchByIp($ip) {

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
			'
		SELECT DISTINCT c.*
		FROM `' . _DB_PREFIX_ . 'customer` c
		LEFT JOIN `' . _DB_PREFIX_ . 'guest` g ON g.id_customer = c.id_customer
		LEFT JOIN `' . _DB_PREFIX_ . 'connections` co ON g.id_guest = co.id_guest
		WHERE co.`ip_address` = \'' . (int) ip2long(trim($ip)) . '\''
		);
	}

	/**
	 * @param int $idCustomer
	 *
	 * @return mixed|null|string
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public static function getDefaultGroupId($idCustomer) {

		if (!Group::isFeatureActive()) {
			static $psCustomerGroup = null;

			if ($psCustomerGroup === null) {
				$psCustomerGroup = Configuration::get('EPH_CUSTOMER_GROUP');
			}

			return $psCustomerGroup;
		}

		if (!isset(static::$_defaultGroupId[(int) $idCustomer])) {
			static::$_defaultGroupId[(int) $idCustomer] = Db::getInstance()->getValue(
				'
				SELECT `id_default_group`
				FROM `' . _DB_PREFIX_ . 'customer`
				WHERE `id_customer` = ' . (int) $idCustomer
			);
		}

		return static::$_defaultGroupId[(int) $idCustomer];
	}

	/**
	 * @param int       $idCustomer
	 * @param Cart|null $cart
	 *
	 * @return string
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public static function getCurrentCountry($idCustomer, Cart $cart = null) {

		if (!$cart) {
			$cart = Context::getContext()->cart;
		}

		if (!$cart || !$cart->{Configuration::get('EPH_TAX_ADDRESS_TYPE')}) {
			$idAddress = (int) Db::getInstance()->getValue(
				'
				SELECT `id_address`
				FROM `' . _DB_PREFIX_ . 'address`
				WHERE `id_customer` = ' . (int) $idCustomer . '
				AND `deleted` = 0 ORDER BY `id_address`'
			);
		} else {
			$idAddress = $cart->{Configuration::get('EPH_TAX_ADDRESS_TYPE')};
		}

		$ids = Address::getCountryAndState($idAddress);

		return (int) $ids['id_country'] ? $ids['id_country'] : Configuration::get('EPH_COUNTRY_DEFAULT');
	}

	/**
	 * @param bool $autoDate
	 * @param bool $nullValues
	 *
	 * @return bool
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public function add($autoDate = true, $nullValues = true) {

		
		$this->id_lang = ($this->id_lang) ? $this->id_lang : Context::getContext()->language->id;
		$this->birthday = (empty($this->years) ? $this->birthday : (int) $this->years . '-' . (int) $this->months . '-' . (int) $this->days);
		$this->secure_key = md5(uniqid(rand(), true));
		$this->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-' . Configuration::get('EPH_PASSWD_TIME_FRONT') . 'minutes'));

		if ($this->newsletter && !Validate::isDate($this->newsletter_date_add)) {
			$this->newsletter_date_add = date('Y-m-d H:i:s');
		}

		if ($this->id_default_group == Configuration::get('EPH_CUSTOMER_GROUP')) {

			if ($this->is_guest) {
				$this->id_default_group = (int) Configuration::get('EPH_GUEST_GROUP');
			} else {
				$this->id_default_group = (int) Configuration::get('EPH_CUSTOMER_GROUP');
			}

		}

		/* Can't create a guest customer, if this feature is disabled */

		if ($this->is_guest && !Configuration::get('EPH_GUEST_CHECKOUT_ENABLED')) {
			return false;
		}

		$success = parent::add($autoDate, $nullValues);
		$this->updateGroup($this->groupBox);
		

		return $success;
	}
	
	public function getEducations() {

		$return = [];

		if (!$this->id) {
			return $return;
		}

		$dateEnd = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month"));

		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('se.*')
				->from('student_education', 'se')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->where('`id_customer` = ' . (int) $this->id)
				->where('es.`session_date` > \'' . $dateEnd . '\'')
				->where('`deleted` = 0')
				->orderBy('`id_education_session` DESC')
		);

		if (is_array($educations) && count($educations)) {

			foreach ($educations as $education) {
				$return[] = new StudentEducation($education['id_student_education']);
			}

		}

		return $return;

	}
	
	public function getArchivedEducations() {

		$return = [];

		if (!$this->id) {
			return $return;
		}

		$dateEnd = date("Y-m-d", strtotime(date("Y-m-d", strtotime(date("Y-m-d"))) . "-1 month"));
		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('se.*')
				->from('student_education', 'se')
				->leftJoin('education_session', 'es', 'es.`id_education_session` = se.`id_education_session`')
				->where('`id_customer` = ' . (int) $this->id)
				->where('es.`session_date` <= \'' . $dateEnd . '\'')
				->where('`deleted` = 0')
				->orderBy('`id_education_session` DESC')
		);

		if (is_array($educations) && count($educations)) {

			foreach ($educations as $education) {
				$return[] = new StudentEducation($education['id_student_education']);
			}

		}

		return $return;

	}

	/**
	 * Update customer groups associated to the object
	 *
	 * @param array $list groups
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function updateGroup($list) {

		if ($list && !empty($list)) {
			$this->cleanGroups();
			$this->addGroups($list);
		} else {
			$this->addGroups([$this->id_default_group]);
		}

	}

	/**
	 * @return bool
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopDatabaseException
	 */
	public function cleanGroups() {

		return Db::getInstance()->delete('customer_group', 'id_customer = ' . (int) $this->id);
	}

	/**
	 * @param $groups
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function addGroups($groups) {

		foreach ($groups as $group) {
			$row = ['id_customer' => (int) $this->id, 'id_group' => (int) $group];
			Db::getInstance()->insert('customer_group', $row, false, true, Db::INSERT_IGNORE);
		}

	}

	/**
	 * @return bool
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function delete() {

		if ($this->hasEducation()) {
			$result = [
				'success' => false,
				'message' => 'Le client ne peut être supprimé car des formations sont enregistrée sur son compte.',
			];
		
			die(Tools::jsonEncode($result));
		}
		$account = new StdAccount($this->id_stdaccount);
		$account->delete();

		Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'customer_group` WHERE `id_customer` = ' . (int) $this->id);
		Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'address` WHERE `id_customer` = ' . (int) $this->id);
		


		return parent::delete();
	}

	/**
	 * Return customer addresses
	 *
	 * @param int $idLang Language ID
	 *
	 * @return array Addresses
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getAddresses($idLang) {

		
		$cacheId = 'Customer::getAddresses' . (int) $this->id . '-' . (int) $idLang ;

		if (!Cache::isStored($cacheId)) {
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('DISTINCT a.*, cl.`name` AS `country`, s.`name` AS `state`, s.`iso_code` AS `state_iso`')
					->from('address', 'a')
					->leftJoin('country', 'c', 'a.`id_country` = c.`id_country`')
					->leftJoin('country_lang', 'cl', 'c.`id_country` = cl.`id_country` AND cl.`id_lang` = ' . (int) $idLang)
					->leftJoin('state', 's', 's.`id_state` = a.`id_state`')
					->where('a.`id_customer` = ' . (int) $this->id)
					->where('a.`deleted` = 0')
			);
			Cache::store($cacheId, $result);

			return $result;
		}

		return Cache::retrieve($cacheId);
	}

	/**
	 * Return customer instance from its e-mail (optionally check password)
	 *
	 * @param string $email             E-mail
	 * @param string $plainTextPassword Password is also checked if specified
	 * @param bool   $ignoreGuest
	 *
	 * @return Customer|bool
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getByEmail($email, $plainTextPassword = null, $ignoreGuest = true) {

		if (!Validate::isEmail($email) || ($plainTextPassword && !Validate::isPasswd($plainTextPassword))) {
			die(Tools::displayError());
		}

		$sql = new DbQuery();
		$sql->select('*');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`email` = \'' . pSQL($email) . '\'');
		$sql->where('`deleted` = 0');

		if ($ignoreGuest) {
			$sql->where('`is_guest` = 0');
		}

		$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow($sql);

		if (!$result) {
			return false;
		}

		// If password is provided but doesn't match.

		if ($plainTextPassword && !password_verify($plainTextPassword, $result['passwd'])) {
			// Check if it matches the legacy md5 hashing and, if it does, rehash it.

			if (Validate::isMd5($result['passwd']) && $result['passwd'] === md5(_COOKIE_KEY_ . $plainTextPassword)) {
				$newHash = Tools::hash($plainTextPassword);
				Db::getInstance()->update(
					bqSQL(static::$definition['table']),
					[
						'passwd' => pSQL($newHash),
					],
					'`id_customer` = ' . (int) $result['id_customer']
				);
				$result['passwd'] = $newHash;
			} else {
				return false;
			}

		}

		$this->id = $result['id_customer'];

		foreach ($result as $key => $value) {

			if (property_exists($this, $key)) {
				$this->{$key}

				= $value;
			}

		}

		return $this;
	}

	/**
	 * Return several useful statistics about customer
	 *
	 * @return array Stats
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getStats() {

		$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('COUNT(`id_order`) AS `nb_orders`, SUM(`total_paid` / o.`conversion_rate`) AS `total_orders`')
				->from('orders', 'o')
				->where('o.`id_customer` = ' . (int) $this->id)
				->where('o.`valid` = 1')
		);

		$result2 = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('MAX(c.`date_add`) AS `last_visit`')
				->from('guest', 'g')
				->leftJoin('connections', 'c', 'c.`id_guest` = g.`id_guest`')
				->where('g.`id_customer` = ' . (int) $this->id)
		);

		$result3 = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('(YEAR(CURRENT_DATE)-YEAR(c.`birthday`)) - (RIGHT(CURRENT_DATE, 5) < RIGHT(c.`birthday`, 5)) AS `age`')
				->from('customer', 'c')
				->where('c.`id_customer` = ' . (int) $this->id)
		);

		$result['last_visit'] = $result2['last_visit'];
		$result['age'] = ($result3['age'] != date('Y') ? $result3['age'] : '--');

		return $result;
	}

	/**
	 * @return array|false|mysqli_result|null|PDOStatement|resource
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getLastEmails() {

		if (!$this->id) {
			return [];
		}

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('m.*, l.`name` as `language`')
				->from('mail', 'm')
				->leftJoin('lang', 'l', 'm.`id_lang` = l.`id_lang`')
				->where('`recipient` = \'' . pSQL($this->email) . '\'')
				->orderBy('m.`date_add` DESC')
				->limit(10)
		);
	}

	/**
	 * @return array|false|mysqli_result|null|PDOStatement|resource
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getLastConnections() {

		if (!$this->id) {
			return [];
		}

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('c.`id_connections`, c.`date_add`, COUNT(cp.`id_page`) AS `pages`')
				->select('TIMEDIFF(MAX(cp.time_end), c.date_add) AS time, http_referer,INET_NTOA(ip_address) AS ipaddress')
				->from('guest', 'g')
				->leftJoin('connections', 'c', 'c.`id_guest` = g.`id_guest`')
				->leftJoin('connections_page', 'cp', 'c.`id_connections` = cp.`id_connections`')
				->where('g.`id_customer` = ' . (int) $this->id)
				->groupBy('c.`id_connections`')
				->orderBy('c.`date_add` DESC')
				->limit(10)
		);
	}

	/**
	 * @param int $idCustomer
	 *
	 * @return int|null
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public function customerIdExists($idCustomer) {

		return Customer::customerIdExistsStatic((int) $idCustomer);
	}

	/**
	 * @param int $idCustomer
	 *
	 * @return int|null
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public static function customerIdExistsStatic($idCustomer) {

		$cacheId = 'Customer::customerIdExistsStatic' . (int) $idCustomer;

		if (!Cache::isStored($cacheId)) {
			$result = (int) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
				(new DbQuery())
					->select('`id_customer`')
					->from('customer', 'c')
					->where('c.`id_customer` = ' . (int) $idCustomer)
			);
			Cache::store($cacheId, $result);

			return $result;
		}

		return Cache::retrieve($cacheId);
	}

	/**
	 * @return array|mixed
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getGroups() {

		return Customer::getGroupsStatic((int) $this->id);
	}

	/**
	 * @param int $idCustomer
	 *
	 * @return array|mixed
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public static function getGroupsStatic($idCustomer) {

		if (!Group::isFeatureActive()) {
			return [Configuration::get('EPH_CUSTOMER_GROUP')];
		}

		// @codingStandardsIgnoreStart

		if ($idCustomer == 0) {
			static::$_customer_groups[$idCustomer] = [(int) Configuration::get('EPH_UNIDENTIFIED_GROUP')];
		}

		if (!isset(static::$_customer_groups[$idCustomer])) {
			static::$_customer_groups[$idCustomer] = [];
			$result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
				(new DbQuery())
					->select('cg.`id_group`')
					->from('customer_group', 'cg')
					->where('cg.`id_customer` = ' . (int) $idCustomer)
			);

			foreach ($result as $group) {
				static::$_customer_groups[$idCustomer][] = (int) $group['id_group'];
			}

		}

		return static::$_customer_groups[$idCustomer];
		// @codingStandardsIgnoreEnd
	}

	/**
	 * @deprecated since 1.0.0
	 *
	 * @return false
	 */
	public function isUsed() {

		Tools::displayAsDeprecated();

		return false;
	}

	/**
	 * @return array|false|mysqli_result|null|PDOStatement|resource
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getBoughtProducts() {

		return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('orders', 'o')
				->leftJoin('order_detail', 'od', 'o.`id_order` = od.`id_order`')
				->where('o.`valid` = 1')
				->where('o.`id_customer` = ' . (int) $this->id)
		);
	}

	/**
	 * @return bool
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function toggleStatus() {

		parent::toggleStatus();

		/* Change status to active/inactive */

		return Db::getInstance()->update(
			bqSQL(static::$definition['table']),
			[
				'date_upd' => ['type' => 'sql', 'value' => 'NOW()'],
			],
			'`' . bqSQL(static::$definition['primary']) . '` = ' . (int) $this->id
		);
	}

	/**
	 * @param int         $idLang
	 * @param string|null $password
	 *
	 * @return bool
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function transformToCustomer($idLang, $password = null) {

		if (!$this->isGuest()) {
			return false;
		}

		if (empty($password)) {
			$password = Tools::passwdGen(8, 'RANDOM');
		}

		if (!Validate::isPasswd($password)) {
			return false;
		}

		$this->is_guest = 0;
		$this->passwd = Tools::hash($password);
		$this->cleanGroups();
		$this->addGroups([Configuration::get('EPH_CUSTOMER_GROUP')]); // add default customer group

		if ($this->update()) {
			$tpl = Context::getContext()->smarty->createTemplate(_EPH_MAIL_DIR_ . '/fr/guest_to_customer.tpl');
			$tpl->assign([
				'firstname' => $this->firstname,
				'lastname'  => $this->lastname,
				'email'     => $this->email,
				'passwd'    => '*******',
			]);

			$postfields = [
				'sender'      => [
					'name'  => "Sevice Commerciale " . Configuration::get('EPH_SHOP_NAME'),
					'email' => Configuration::get('EPH_SHOP_EMAIL'),
				],
				'to'          => [
					[
						'name'  => $this->firstname . ' ' . $this->lastname,
						'email' => $this->email,
					],
				],

				'subject'     => 'Votre compte invité a été transformé en compte client',
				"htmlContent" => $tpl->fetch(),
			];
			Tools::sendEmail($postfields);

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 *
	 * @since 2.1.0.0
	 */
	public function isGuest() {

		return (bool) $this->is_guest;
	}

	/**
	 * @param bool $nullValues
	 *
	 * @return bool
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function update($nullValues = false) {

		$this->birthday = (empty($this->years) ? $this->birthday : (int) $this->years . '-' . (int) $this->months . '-' . (int) $this->days);

		if ($this->newsletter && !Validate::isDate($this->newsletter_date_add)) {
			$this->newsletter_date_add = date('Y-m-d H:i:s');
		}

		if (isset(Context::getContext()->controller) && Context::getContext()->controller->controller_type == 'admin') {
			$this->updateGroup($this->groupBox);
		}

		if ($this->deleted) {
			$addresses = $this->getAddresses((int) Configuration::get('EPH_LANG_DEFAULT'));

			foreach ($addresses as $address) {
				$obj = new Address((int) $address['id_address']);
				$obj->delete();
			}

		}
		
		$success = parent::update(true);
		
		$customer = new Student($this->id_student);
		$customer->id_gender = $this->id_gender;
		$customer->firstname = $this->firstname;
		$customer->lastname = $this->lastname;
		$customer->birthname = $this->birthname;
		$customer->email = $this->email;
		$customer->passwd = $this->passwd;
		$customer->password = $this->password;
		$customer->birthday = $this->birthday;
		$customer->active = $this->active;
		$customer->newsletter = $this->newsletter;
		$customer->newsletter_date_add = $this->newsletter_date_add;
		$customer->ip_registration_newsletter = $this->ip_registration_newsletter;
		$customer->notes = $this->note;
		$customer->cach_last_education = $this->cach_last_education;
		$customer->birth_city = $this->birth_city;
		
		try {
  			$result = $customer->update(); 
		}	catch(Exception $e) {
			
  
		}

		return $success;
	}

	/**
	 * @param string $passwd
	 *
	 * @return bool
	 *
	 * @since 2.1.0.0
	 */
	public function setWsPasswd($passwd) {

		if ($this->id == 0 || $this->passwd != $passwd) {
			$this->passwd = Tools::hash($passwd);
		}

		return true;
	}

	/**
	 * Check customer informations and return customer validity
	 *
	 * @since 2.1.0.0
	 *
	 * @param bool $withGuest
	 *
	 * @return bool customer validity
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public function isLogged() {

		
		return ($this->logged == 1 && $this->id && Validate::isUnsignedId($this->id) && Customer::checkPassword($this->id, $this->passwd));
	}

	/**
	 * Check if customer password is the right one
	 *
	 * @param int    $idCustomer
	 * @param string $plaintextOrHashedPassword Password
	 *
	 * @return bool result
	 *
	 * @since 2.1.0.0
	 *
	 * @todo    : adapt validation for hashed password
	 * @todo    : find out why both hashed and plaintext password are passed
	 * @throws PhenyxShopException
	 */
	public static function checkPassword($idCustomer, $plaintextOrHashedPassword) {

		if (!Validate::isUnsignedId($idCustomer)) {
			die(Tools::displayError());
		}

		if (Validate::isMd5($plaintextOrHashedPassword) || mb_substr($plaintextOrHashedPassword, 0, 4) === '$2y$') {
			$hashedPassword = $plaintextOrHashedPassword;

			return static::checkPasswordInDatabase($idCustomer, $hashedPassword);
		} else {
			$hashedPassword = Tools::encrypt($plaintextOrHashedPassword);

			if (static::checkPasswordInDatabase($idCustomer, $hashedPassword)) {
				return true;
			}

			$sql = new DbQuery();
			$sql->select('`passwd`');
			$sql->from(bqSQL(static::$definition['table']));
			$sql->where('`id_customer` = ' . (int) $idCustomer);

			$hashedPassword = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);

			return password_verify($plaintextOrHashedPassword, $hashedPassword);
		}

	}

	/**
	 * Check password validity via DB
	 *
	 * @param $idCustomer
	 * @param $hashedPassword
	 *
	 * @return bool
	 *
	 * @since 1.0.1
	 * @throws PhenyxShopException
	 */
	protected static function checkPasswordInDatabase($idCustomer, $hashedPassword) {

		$cacheId = 'Customer::checkPassword' . (int) $idCustomer . '-' . $hashedPassword;

		if (!Cache::isStored($cacheId)) {
			$sql = new DbQuery();
			$sql->select('`id_customer`');
			$sql->from(bqSQL(static::$definition['table']));
			$sql->where('`id_customer` = ' . (int) $idCustomer);
			$sql->where('`passwd` = \'' . pSQL($hashedPassword) . '\'');
			$result = (bool) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);
			Cache::store($cacheId, $result);

			return $result;
		}

		return Cache::retrieve($cacheId);
	}

	/**
	 * Logout
	 *
	 * @since 2.1.0.0
	 */
	public function logout() {

		Hook::exec('actionCustomerLogoutBefore', ['customer' => $this]);

		if (isset(Context::getContext()->cookie)) {
			Context::getContext()->cookie->logout();
		}

		$this->logged = 0;

		Hook::exec('actionCustomerLogoutAfter', ['customer' => $this]);
	}

	/**
	 * Soft logout, delete everything links to the customer
	 * but leave there affiliate's informations
	 *
	 * @since 2.1.0.0
	 */
	public function mylogout() {

		Hook::exec('actionCustomerLogoutBefore', ['customer' => $this]);

		if (isset(Context::getContext()->cookie)) {
			Context::getContext()->cookie->mylogout();
		}

		$this->logged = 0;

		Hook::exec('actionCustomerLogoutAfter', ['customer' => $this]);
	}

	/**
	 * @param bool $withOrder
	 *
	 * @return bool|int
	 *
	 * @throws PhenyxShopDatabaseException
	 * @throws PhenyxShopException
	 * @since 2.1.0.0
	 */
	public function getLastCart($withOrder = true) {

		$carts = Cart::getCustomerCarts((int) $this->id, $withOrder);

		if (!count($carts)) {
			return false;
		}

		$cart = array_shift($carts);
		$cart = new Cart((int) $cart['id_cart']);

		return ($cart->nbProducts() === 0 ? (int) $cart->id : false);
	}

	/**
	 * @return float
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxShopException
	 */
	public function getOutstanding() {

		$totalPaid = (float) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('SUM(oi.`total_paid_tax_incl`)')
				->from('order_invoice', 'oi')
				->leftJoin('orders', 'o', 'oi.`id_order` = o.`id_order`')
				->groupBy('o.`id_customer`')
				->where('o.`id_customer` = ' . (int) $this->id)
		);

		$totalRest = (float) Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('SUM(op.`amount`)')
				->from('order_payment', 'op')
				->leftJoin('order_invoice_payment', 'oip', 'op.`id_order_payment` = oip.`id_order_payment`')
				->leftJoin('orders', 'o', 'oip.`id_order` = o.`id_order`')
				->groupBy('o.`id_customer`')
				->where('o.`id_customer` = ' . (int) $this->id)
		);

		return $totalPaid - $totalRest;
	}

	
	
	public static function getStudentEducations($idCustomer) {

		$educations = [];
		$queries = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education`')
				->from('student_education', 's')
				->where('`id_customer` = ' . $idCustomer)
				->where('`deleted` = 0')
				->orderBy('`id_education_session` DESC')
		);

		if (is_array($queries) && count($queries)) {

			foreach ($queries as $query) {
				$educations[] = new StudentEducation($query['id_student_education']);
			}

		}

		return $educations;

	}
	
	public static function getLastStudentEducation($idCustomer) {

		return Db::getInstance()->getRow(
			(new DbQuery())
				->select('e.name')
				->from('student_education', 'se')
                ->leftJoin('education_lang', 'el', 'el.id_education = se.id_education AND el.id_lang = '.(int)Context::getContext()->language->id)
				->where('`se.id_customer` = ' . $idCustomer)
				->where('`se.deleted` = 0')
				->orderBy('`se.id_education_session` DESC')
		);

		
	}

}
