<?php

use \GuzzleHttp\Exception\RequestException;

/**
 * Class EmployeeCore
 *
 * @since 1.9.1.0
 */
class EmployeeCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var string Determine employee profile */
    public $id_profile;

    public $employee_type;
    /** @var string employee language */
    public $id_lang;
    /** @var string Lastname */
    public $lastname;
    /** @var string Firstname */
    public $firstname;
    /** @var string e-mail */
    public $email;
    /** @var string Password */
    public $passwd;

    public $password;
    /** @var datetime Password */
    public $last_passwd_gen;
    /** @var string $stats_date_from */
    public $stats_date_from;
    /** @var string $stats_date_to */
    public $stats_date_to;
    /** @var string $stats_compare_from */
    public $stats_compare_from;
    /** @var string $stats_compare_to */
    public $stats_compare_to;
    /** @var int $stats_compare_option */
    public $stats_compare_option = 1;
    /** @var string $preselect_date_range */
    public $preselect_date_range;
    /** @var string Display back office background in the specified color */
    public $bo_color;
    public $default_tab;
    /** @var string employee's chosen theme */
    public $bo_theme;
    /** @var string employee's chosen css file */
    public $bo_css = 'admin-theme.css';
    /** @var int employee desired screen width */
    public $bo_width;

    /* Deprecated */
    /** @var bool, false */
    public $bo_menu = 1;
    public $bo_show_screencast = false;
    /** @var bool Status */
    public $active = 1;
    /** @var bool Optin status */
    public $optin = 1;

    /* employee notifications */
    public $remote_addr;
    public $id_last_student_education;
    public $id_last_student_message;
    public $id_last_customer;
    protected $associated_shops = [];

    public $log_in;

    public $last_timestamp;
	
	public $workin_plan = '{"monday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"tuesday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"wednesday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"thursday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"friday":{"start":"09:00","end":"20:00","breaks":[{"start":"14:30","end":"15:00"}]},"saturday":{"start":"09:00","end":"15:00","breaks":[{"start":"14:30","end":"15:00"}]},"sunday":null}';
	public $workin_break;
	public $working_plan_exceptions;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'employee',
        'primary' => 'id_employee',
        'fields'  => [
            'employee_type'             => ['type' => self::TYPE_STRING],
            'lastname'                  => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
            'firstname'                 => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
            'email'                     => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'required' => true, 'size' => 128],
            'id_lang'                   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => true],
            'passwd'                    => ['type' => self::TYPE_STRING, 'validate' => 'isPasswdAdmin', 'required' => true, 'size' => 60],
            'password'                  => ['type' => self::TYPE_STRING],
            'last_passwd_gen'           => ['type' => self::TYPE_STRING],
            'active'                    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'optin'                     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'id_profile'                => ['type' => self::TYPE_INT, 'validate' => 'isInt', 'required' => true],
            'bo_color'                  => ['type' => self::TYPE_STRING, 'validate' => 'isColor', 'size' => 32],
            'default_tab'               => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
            'bo_theme'                  => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 32],
            'bo_css'                    => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 64],
            'bo_width'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'bo_menu'                   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'stats_date_from'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'stats_date_to'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'stats_compare_from'        => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'stats_compare_to'          => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'stats_compare_option'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'preselect_date_range'      => ['type' => self::TYPE_STRING, 'size' => 32],
            'id_last_student_education' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'id_last_student_message'   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'log_in'                    => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'last_timestamp'            => ['type' => self::TYPE_NOTHING],
            'id_last_customer'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
        ],
    ];

    /**
     * EmployeeCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     * @param int|null $idShop
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, null, $idShop);

        if (!is_null($idLang)) {
            $this->id_lang = (int) (Language::getLanguage($idLang) !== false) ? $idLang : Configuration::get('PS_LANG_DEFAULT');
        }

        if ($this->id) {
            $this->associated_shops = $this->getAssociatedShops();
			$this->workin_plan = $this->getWorkingPlan();
			$this->workin_break = $this->getWorkingBreak();
			$this->working_plan_exceptions = $this->getWorkingPlanException();
        }

        $this->image_dir = _PS_EMPLOYEE_IMG_DIR_;
    }
	
	public function getWorkingPlan() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_plan')
				->from('employee_settings')
				->where('`id_employee` = ' . $this->id)
		);
	}
	public function getWorkingBreak() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_break')
				->from('employee_settings')
				->where('`id_employee` = ' . $this->id)
		);
	}
	
	public function getWorkingPlanException() {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('working_plan_exceptions')
				->from('employee_settings')
				->where('`id_employee` = ' . $this->id)
		);
	}

    /**
     * Return list of employees
     *
     * @param bool $activeOnly Filter employee by active status
     *
     * @return array|false Employees or false
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getEmployees($activeOnly = true) {

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(static::$definition['table']));

        if ($activeOnly) {
            $sql->where('`active` = 1');
        }

        $sql->orderBy('`id_employee` ASC');

        $employees = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
		
		foreach($employees as &$employee) {
			
			$employee['settings'] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('*')
				->from('employee_settings')
				->where('`id_employee` = '.$employee['id_employee'])
			);
			
						
		}
		
		return $employees;
    }

    /**
     * @param string $email
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function employeeExists($email) {

        if (!Validate::isEmail($email)) {
            die(Tools::displayError());
        }

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_employee`')
                ->from('employee')
                ->where('`email` = \'' . pSQL($email) . '\'')
        );
    }

    /**
     * @param int  $idProfile
     * @param bool $activeOnly
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getEmployeesByProfile($idProfile, $activeOnly = false) {

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_profile` = ' . (int) $idProfile);

        if ($activeOnly) {
            $sql->where('`active` = 1');
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /**
     * @param int $idEmployee
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function setLastConnectionDate($idEmployee) {

        return Db::getInstance()->update(
            bqSQL(static::$definition['table']),
            [
                'last_connection_date' => ['type' => 'sql', 'value' => 'CURRENT_DATE()'],
            ],
            '`id_employee` = ' . (int) $idEmployee . ' AND `last_connection_date` < CURRENT_DATE()'
        );
    }

    /**
     * @see     ObjectModel::getFields()
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getFields() {

        if (empty($this->stats_date_from) || $this->stats_date_from == '0000-00-00') {
            $this->stats_date_from = date('Y-m-d', strtotime('-1 month'));
        }

        if (empty($this->stats_compare_from) || $this->stats_compare_from == '0000-00-00') {
            $this->stats_compare_from = null;
        }

        if (empty($this->stats_date_to) || $this->stats_date_to == '0000-00-00') {
            $this->stats_date_to = date('Y-m-d');
        }

        if (empty($this->stats_compare_to) || $this->stats_compare_to == '0000-00-00') {
            $this->stats_compare_to = null;
        }

        return parent::getFields();
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function add($autoDate = true, $nullValues = true) {

        $this->last_passwd_gen = date('Y-m-d H:i:s', strtotime('-' . Configuration::get('PS_PASSWD_TIME_BACK') . 'minutes'));
       

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Subscribe to the ephenyx newsletter. Also resets $this->optin on
     * failure.
     *
     * @return bool Wether un/registration was successful.
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @version 1.0.6 Added return value.
     */
    protected function saveOptin() {

        $success = true;

        if (!defined('EPH_INSTALLATION_IN_PROGRESS')) {

            if ($this->optin) {
                $context = Context::getContext();

                $guzzle = new \GuzzleHttp\Client([
                    'base_uri' => 'https://api.ephenyx.com',
                    'timeout'  => 20,
                    'verify'   => _PS_TOOL_DIR_ . 'cacert.pem',
                ]);

                try {
                    $body = $guzzle->post(
                        '/newsletter/', [
                            'json' => [
                                'email'    => $this->email,
                                'fname'    => $this->firstname,
                                'lname'    => $this->lastname,
                                'activity' => Configuration::get('PS_SHOP_ACTIVITY'),
                                'country'  => $context->country->iso_code,
                                'language' => $context->language->iso_code,
                                'URL'      => $context->shop->getBaseURL(),
                            ],
                        ]
                    )->getBody();
                } catch (RequestException $e) {
                    $success = false;
                    $this->optin = false;
                }

                if ((string) $body) {
                    // Service itsself wasn't successful.
                    $success = false;
                    $this->optin = false;
                }

            } else {
                // TODO: actually unregister
            }

        }

        return $success;
    }

    /**
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @version 1.0.8 Operate during initial shop installation as well.
     */
    protected function updateTextDirection() {

        if (defined('_PS_ADMIN_DIR_')) {
            $path = _PS_ADMIN_DIR_ . '/themes/' . $this->bo_theme . '/css/';
        } else {
            // Probably installation in progress.
            $path = _PS_ROOT_DIR_ . '/admin/themes/' . $this->bo_theme . '/css/';

            if (!is_dir($path)) {
                $path = _PS_ROOT_DIR_ . '/admin-dev/themes/' . $this->bo_theme . '/css/';

                if (!is_dir($path)) {
                    // Give up.
                    return;
                }

            }

        }

        $language = new Language($this->id_lang);

        if ($language->is_rtl && !strpos($this->bo_css, '_rtl')) {
            $boCss = preg_replace('/^(.*)\.css$/', '$1_rtl.css', $this->bo_css);
            $boCss = str_replace('schemes/', 'schemes_rtl/', $boCss);

            if (file_exists($path . $boCss)) {
                $this->bo_css = $boCss;
            }

        } else if (!$language->is_rtl && strpos($this->bo_css, '_rtl')) {
            $boCss = str_replace('_rtl', '', $this->bo_css);

            if (file_exists($path . $boCss)) {
                $this->bo_css = $boCss;
            }

        }

    }

    /**
     * Update the database record. Also used by AdminDashboardController for
     * newsletter registration.
     *
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function update($nullValues = false) {

        $success = true;

        if (empty($this->stats_date_from) || $this->stats_date_from == '0000-00-00') {
            $this->stats_date_from = date('Y-m-d');
        }

        if (empty($this->stats_date_to) || $this->stats_date_to == '0000-00-00') {
            $this->stats_date_to = date('Y-m-d');
        }

        $currentEmployee = new Employee((int) $this->id);

        if ($currentEmployee->optin != $this->optin
            || $currentEmployee->email != $this->email
            || !Configuration::get('EPH_STORE_REGISTERED')) {
            $success = $this->saveOptin();
        }

        $this->updateTextDirection();

        return $success && parent::update($nullValues);
    }

    /**
     * Return employee instance from its e-mail (optionally check password)
     *
     * @param string $email             E-mail
     * @param string $plainTextPassword Password is also checked if specified
     * @param bool   $activeOnly        Filter employee by active status
     *
     * @return Employee|bool Employee instance
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getByEmail($email, $plainTextPassword = null, $activeOnly = true) {

        if (!Validate::isEmail($email) || ($plainTextPassword && !Validate::isPasswdAdmin($plainTextPassword))) {
            return false;
        }

        $sql = new DbQuery();
        $sql->select('*');
        $sql->from('employee');
        $sql->where('`email` = \'' . pSQL($email) . '\'');

        if ($activeOnly) {
            $sql->where('`active` = 1');
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow($sql);

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
                    'id_employee = ' . (int) $result['id_employee']
                );
                $result['passwd'] = $newHash;
            } else {
                return false;
            }

        }

        $this->id = $result['id_employee'];
        $this->id_profile = $result['id_profile'];

        foreach ($result as $key => $value) {

            if (property_exists($this, $key)) {
                $this->{$key}
                = $value;
            }

        }

        return $this;
    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function isLastAdmin() {

        return ($this->isSuperAdmin()
            && Employee::countProfile($this->id_profile, true) == 1
            && $this->active
        );
    }

    /**
     * Check if current employee is super administrator
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function isSuperAdmin() {

        return $this->id_profile == _PS_ADMIN_PROFILE_;
    }

    /**
     * @param int  $idProfile
     * @param bool $activeOnly
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function countProfile($idProfile, $activeOnly = false) {

        $sql = new DbQuery();
        $sql->select('COUNT(*)');
        $sql->from(bqSQL(static::$definition['table']));
        $sql->where('`id_profile` = ' . (int) $idProfile);

        if ($activeOnly) {
            $sql->where('`active` = 1');
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * @param string $plainTextPassword
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setWsPasswd($plainTextPassword) {

        if ($this->id != 0) {

            if ($this->passwd != $plainTextPassword) {
                $this->passwd = Tools::hash($plainTextPassword);
            }

        } else {
            $this->passwd = Tools::hash($plainTextPassword);
        }

        return true;
    }

    /**
     * Check employee informations saved into cookie and return employee validity
     *
     * @return bool employee validity
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function isLoggedBack() {

        if (!Cache::isStored('isLoggedBack' . $this->id)) {
            /* Employee is valid only if it can be load and if cookie password is the same as database one */
            $result = (
                $this->id && Validate::isUnsignedId($this->id) && Employee::checkPassword($this->id, Context::getContext()->cookie->passwd)
                && (!isset(Context::getContext()->cookie->remote_addr) || Context::getContext()->cookie->remote_addr == ip2long(Tools::getRemoteAddr()) || !Configuration::get('PS_COOKIE_CHECKIP'))
            );
            Cache::store('isLoggedBack' . $this->id, $result);

            return $result;
        }

        return Cache::retrieve('isLoggedBack' . $this->id);
    }

    /**
     * Check if employee password is the right one
     *
     * @param int    $idEmployee
     * @param string $hashedPassword Password
     *
     * @return bool result
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function checkPassword($idEmployee, $hashedPassword) {

        if (!Validate::isUnsignedId($idEmployee) || !Validate::isPasswd($hashedPassword, 8)) {
            die(Tools::displayError());
        }

        $sql = new DbQuery();
        $sql->select('`id_employee`');
        $sql->from('employee');
        $sql->where('`id_employee` = ' . (int) $idEmployee);
        $sql->where('`active` = 1');
        $sql->where('`passwd` = \'' . pSQL($hashedPassword) . '\'');

        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
    }

    /**
     * Logout
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function logout() {

        if (isset(Context::getContext()->cookie)) {

            Context::getContext()->cookie->logout();
            Context::getContext()->cookie->write();
        }

        if ($this->id > 0) {
            $this->log_in = 0;
            $this->update();
        }

        $this->id = null;
    }

    /**
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function favoriteModulesList() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('module')
                ->from('module_preference')
                ->where('`id_employee` = ' . (int) $this->id)
                ->where('`favorite` = 1')
                ->where('`interest` = 1 OR `interest` IS NULL')
        );
    }

    /**
     * Check if the employee is associated to a specific shop
     *
     * @param int $idShop
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function hasAuthOnShop($idShop) {

        return $this->isSuperAdmin() || in_array($idShop, $this->associated_shops);
    }

    /**
     * Check if the employee is associated to a specific shop group
     *
     * @param int $idShopGroup
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function hasAuthOnShopGroup($idShopGroup) {

        if ($this->isSuperAdmin()) {
            return true;
        }

        foreach ($this->associated_shops as $idShop) {

            if ($idShopGroup == Shop::getGroupFromShop($idShop, true)) {
                return true;
            }

        }

        return false;
    }

    /**
     * Get default id_shop with auth for current employee
     *
     * @return int
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getImage() {

        if (!isset($this->id) || empty($this->id) || !file_exists(_PS_EMPLOYEE_IMG_DIR_ . $this->id . '.jpg')) {
            return '../img/e/' . 'Unknown.jpg';
        }

        return '../img/e/' . $this->id . '.jpg';
    }

    /**
     * @param string $element
     *
     * @return int
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getLastElementsForNotify($element) {

        $element = bqSQL($element);
        $max = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('MAX(`id_' . bqSQL($element) . '`) as `id_' . bqSQL($element) . '`')
                ->from(bqSQL($element) . ($element == 'order' ? 's' : ''))
        );

        // if no rows in table, set max to 0

        if ((int) $max < 1) {
            $max = 0;
        }

        return (int) $max;
    }
	
	public static function getAppointmentEmployee($idEmployee) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow((new DbQuery())
                        ->select('*')
                        ->from('employee')
                        ->where('`id_employee` =' . $idEmployee));
	}

}
