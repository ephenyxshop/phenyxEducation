<?php
use Defuse\Crypto\Crypto;
use \Curl\Curl;

class LicenseCore extends ObjectModel {

	public $website;
	public $id_customer;
	public $purchase_key;
	public $ftp_user;
	public $crypto_key;
	public $ftp_passwd;
	public $user_ip;
	public $active;
	public $is_shop;
	public $is_education;
	public $date_add;
	public $date_upd;
	public $partner;
	public $partner_firstname;
	public $partner_lastname;
	public $partner_company;
	
	public $referentTopBars;

	public static $definition = [
		'table'   => 'license',
		'primary' => 'id_license',
		'fields'  => [
			'website'            => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
			'id_customer' => ['type' => self::TYPE_INT],
			'purchase_key'       => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'copy_post' => false],
			'ftp_user'           => ['type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'required' => true, 'size' => 60],
			'crypto_key'         => ['type' => self::TYPE_STRING, 'validate' => 'isPasswd', 'size' => 60],
			'ftp_passwd'         => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 60],
			'user_ip'            => ['type' => self::TYPE_STRING, 'copy_post' => false],
			'active'             => ['type' => self::TYPE_BOOL],
			'is_shop'  	=> ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'is_education'  	=> ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'date_add'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];

	public function __construct($id = null) {

		parent::__construct($id);

		if ($this->id) {
			$partner = new PartnerCompany($this->id_partner_company);
			$this->partner_firstname = $partner->firstname;
			$this->partner_lastname = $partner->lastname;
			$this->partner_company = $partner->company_name;
		}
		$this->referentTopBars = EmployeeMenu::getReferentTopBars();

	}

	public function add($autoDate = true, $nullValues = false) {

		$this->crypto_key = Tools::hash($this->purchase_key);
		return parent::add($autoDate, true);
	}
	
	public static function getLiceneCollection() {
		$collection = [];
		$licenses = Db::getInstance()->executeS(
			(new DbQuery())
			->select('id_license')
			->from('license')
			->where('`active` = 1')
		);
		
		foreach($licenses as $license) {
			
			$collection[] = new License($license['id_license']);
		}
		
		return $collection;
	}

	public static function generateSecureKey($website, $purchaseKey, $securekey) {

		$data = $website . '|' . $purchaseKey;
		$ciphertext = Crypto::encrypt($data, $securekey, true);
		return $ciphertext;
	}

	public static function checkLicenseValidity($website, $purchaseKey, $userIp, $requestLang, $psVersion, $ephVersion) {

		require_once _PS_CONFIG_DIR_ . 'bootstrap.php';
		$isCorrectWebsite = true;
		$isCorrectIp = true;
		$interval = 0;
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`purchase_key` = \'' . pSQL($purchaseKey) . '\'');

		$result = Db::getInstance()->getRow($sql);

		if (!empty($result)) {

			$id_license = (int) $result['id_license'];
			$secret_iv = Tools::hash($website);

			if (!empty($result['website']) && $result['website'] != $website) {
				$isCorrectWebsite = false;
			} else {
				$license = new License($id_license);

				if ($license->active == 0) {
					$interval = License::registerLicense($license, $userIp);
				} else {

					if ($license->user_ip != $userIp) {
						$isCorrectIp = false;
					}

					$expiryDate = new DateTime($license->date_exp);
					$currentTime = new DateTime("now");
					$interval = $currentTime->diff($expiryDate);
					$interval = $interval->format('%R%a');
				}

			}

		}

		return VersionController::checkLicense($website, $purchaseKey, $userIp, $requestLang, $psVersion, $ephVersion, $isCorrectWebsite, $isCorrectIp, $interval);

	}

	public static function registerLicense(License $license, $userIp) {

		$license->active = 1;
		$license->user_ip = $userIp;
		$license->date_exp = date('Y-m-d H:i:s', strtotime('+1 years'));

		if ($license->update()) {
			return 365;
		}

	}

	public static function checkLicense($purchaseKey, $website) {
		
		$sql = new DbQuery();
		$sql->select('*');
		$sql->from(bqSQL(static::$definition['table']));
		$sql->where('`purchase_key` = \'' . pSQL($purchaseKey) . '\'');
		$result = Db::getInstance()->getRow($sql);
		if (!empty($result)) {
			$id_license = (int) $result['id_license'];
			$license = new License($id_license);
			if($license->website == $website && $license->active) {
				return true;
			}
			
			return false;

		}

	}

	public function encrypt_decrypt($action, $string, $secret_key, $secret_iv) {

		$output = false;
		$encrypt_method = "AES-256-CBC";
		$key = hash('sha256', $secret_key);
		$iv = substr(hash('sha256', $secret_iv), 0, 16);

		if ($action == 'encrypt') {
			$output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
			$output = base64_encode($output);
		} else
		if ($action == 'decrypt') {
			$output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
		}

		return $output;
	}

	public static function generateLicenceKey() {

		$tokens = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$segment_chars = 5;
		$num_segments = 4;
		$key_string = '';

		for ($i = 0; $i < $num_segments; $i++) {
			$segment = '';

			for ($j = 0; $j < $segment_chars; $j++) {
				$segment .= $tokens[rand(0, 35)];
			}

			$key_string .= $segment;

			if ($i < ($num_segments - 1)) {
				$key_string .= '-';
			}

		}

		return $key_string;
	}
	
	

	public static function generateReferenceFiles() {

		$recursive_directory = ['administration/js', 'administration/template', 'administration/themes', 'app/classes', 'app/controllers', 'mails', 'modules', 'override', 'pdfTemplate','smsTemplate',  'webephenyx'];
		$iterator = new AppendIterator();

		foreach ($recursive_directory as $key => $directory) {
			$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(_PS_ROOT_DIR_ . '/' . $directory . '/')));

		}

		$iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/app'));
		$iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/'));
		$iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/administration'));

		foreach ($iterator as $file) {
			/** @var DirectoryIterator $file */
			$filePath = $file->getPathname();
			$filePath = str_replace(_PS_ROOT_DIR_, '', $filePath);

			if (in_array($file->getFilename(), ['.', '..', 'index.php', '.htaccess', 'dwsync.xml', 'settings.inc.php', 'defines.inc.php', 'License.php', 'EducationTracking.php', 'CronJobs.php', 'PhenyxDataBase.php', 'PhenyxController.php', 'PhenyxClasse.php', 'licences.js', 'licences.tpl', 'AdminEducationTrackingsController.php', 'AdminAgendaController.php', 'AdminCronJobsController.php', 'AdminPartnerCompaniesController.php', 'AdminFormatPackController.php', 'AdminLicencesController.php', 'AdminPhenyxDataBaseController.php', 'veille.php', 'cronjobs.tpl', 'license.tpl', 'root.css'])) {
				continue;
			}

			$ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

			if ($ext == 'txt') {
				continue;
			}

			if ($ext == 'zip') {
				continue;
			}

			if (strpos($file->getFilename(), 'truc') !== false) {
				continue;
			}

			if (strpos($filePath, 'partner_companies') !== false) {
				continue;
			}
			if (strpos($filePath, 'cron_jobs') !== false) {
				continue;
			}

			if (strpos($filePath, 'controllers/licences') !== false) {
				continue;
			}
			

			if (is_dir($file->getPathname())) {
				continue;
			}

			$md5List[$filePath] = md5_file($file->getPathname());
		}

		file_put_contents(
			_PS_CONFIG_DIR_ . 'json/files.json',
			json_encode($md5List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);

	}
	
	public static function generateEducationReferenceFiles() {

		$recursive_directory = ['administration/js', 'administration/template', 'administration/themes', 'app/classes', 'app/controllers', 'mails', 'modules', 'pdfTemplate', 'smsTemplate',  'webephenyx'];
		$iterator = new AppendIterator();

		foreach ($recursive_directory as $key => $directory) {
			$iterator->append(new RecursiveIteratorIterator(new RecursiveDirectoryIterator(_PS_ROOT_DIR_ . '/' . $directory . '/')));

		}

		$iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/app'));
		$iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/'));

		foreach ($iterator as $file) {
			/** @var DirectoryIterator $file */
			$filePath = $file->getPathname();
			$filePath = str_replace(_PS_ROOT_DIR_, '', $filePath);

			if (in_array($file->getFilename(), ['.', '..', 'index.php', '.htaccess', 'dwsync.xml', 'settings.inc.php', 'defines.inc.php', 'License.php', 'EducationTracking.php', 'PhenyxDataBase.php', 'PhenyxController.php', 'PhenyxClasse.php', 'licences.js', 'licences.tpl', 'AdminEducationTrackingsController.php', 'AdminPartnerCompaniesController.php', 'AdminLicencesController.php', 'AdminPhenyxDataBaseController.php', 'root.css'])) {
				continue;
			}

			$ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

			if ($ext == 'txt') {
				continue;
			}

			if ($ext == 'zip') {
				continue;
			}

			if (strpos($file->getFilename(), 'truc') !== false) {
				continue;
			}

			if (strpos($filePath, 'partner_companies') !== false) {
				continue;
			}

			if (strpos($filePath, 'controllers/licences') !== false) {
				continue;
			}
			

			if (is_dir($file->getPathname())) {
				continue;
			}

			$md5List[$filePath] = md5_file($file->getPathname());
		}

		file_put_contents(
			_PS_CONFIG_DIR_ . 'json/files.json',
			json_encode($md5List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
		);

	}

	public function compareSqlTable($table) {

		$dbParams = $this->getdBParam();

		$context = Context::getContext();


		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		

		$current = Db::getInstance()->executeS(
			(new DbQuery())
				->select('a.*, l.*')
				->from($table, 'a')
				->leftJoin($table . '_lang', 'l', 'l.`id_' . $table . '` = a.`id_' . $table . '` AND l.`id_lang` = ' . $context->language->id)
		);

		$distant = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->executeS(
			(new DbQuery())
				->select('a.*, l.*')
				->from($table, 'a')
				->leftJoin($table . '_lang', 'l', 'l.`id_' . $table . '` = a.`id_' . $table . '` AND l.`id_lang` = ' . $context->language->id)
		);
		$currentTable = [];

		foreach ($current as $index => $contentLines) {

			foreach ($contentLines as $key => $line) {

				if ($key == 'id_' . $table) {
					continue;
				}

				$currentTable[$index][] = [$key => $line];
			}

		}

		

	}

	

	public static function generateSqlTable($table) {

		$date = time();

		$request = '';

		$idLicenseReferer = Configuration::get('EPH_TOPBAR_REFERER');
		$licenceReferer = new License($idLicenseReferer);
		$dbParams = $licenceReferer->getdBParam();
		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		$schema = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->executeS('SHOW CREATE TABLE `' . _DB_PREFIX_ . $table . '`');

		if (count($schema) != 1 || !isset($schema[0]['Table']) || !isset($schema[0]['Create Table'])) {
			fclose($fp);
			return false;
		}

		$request .= 'DROP TABLE IF EXISTS `' . $schema[0]['Table'] . '`;' . PHP_EOL;

		$request .= $schema[0]['Create Table'] . ";\n\n";

		$data = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->query('SELECT * FROM `' . $schema[0]['Table'] . '`');
		$sizeof = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->NumRows();
		$lines = explode("\n", $schema[0]['Create Table']);

		if ($data && $sizeof > 0) {
			// Export the table data

			$request .= 'INSERT INTO `' . $schema[0]['Table'] . "` VALUES\n";
			$i = 1;

			while ($row = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->nextRow($data)) {
				$s = '(';

				foreach ($row as $field => $value) {
					$tmp = "'" . pSQL($value, true) . "',";

					if ($tmp != "'',") {
						$s .= $tmp;
					} else {

						foreach ($lines as $line) {

							if (strpos($line, '`' . $field . '`') !== false) {

								if (preg_match('/(.*NOT NULL.*)/Ui', $line)) {
									$s .= "'',";
								} else {
									$s .= 'NULL,';
								}

								break;
							}

						}

					}

				}

				$s = rtrim($s, ',');

				if ($i % 200 == 0 && $i < $sizeof) {
					$s .= ");\nINSERT INTO `" . $schema[0]['Table'] . "` VALUES\n";
				} else
				if ($i < $sizeof) {
					$s .= "),\n";
				} else {
					$s .= ");\n";
				}

				$request .= $s;
				++$i;
			}

		}

		return $request;
	}

	public static function getJsonFileFromWebSite($website) {

		unlink(_PS_CONFIG_DIR_ . 'json/' . $website . '.json');
		
		$data_array = [];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post('https://' . $website . '/api', json_encode($data_array));
		$md5List = $curl->response;

		if (is_array($md5List)) {
			file_put_contents(
				_PS_CONFIG_DIR_ . 'json/' . $website . '.json',
				json_encode($md5List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
			);
			return true;
		}

		return false;

	}
	
	public function cleanDirectory($dir) {
		
		$url = 'https://' . $this->website . '/api';
	
		$data_array = [
			'action' => 'cleanDirectory',
			'directory' => $dir
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getFrontJsonFile() {

		
		$url = 'https://' . $this->website . '/api';
	
		$data_array = [
			'action' => 'getFrontJsonFile',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setTimeout(6000);
		$curl->post($url, json_encode($data_array));
		$md5List = $curl->response;
		if (is_array($md5List)) {
			file_put_contents(
				_PS_CONFIG_DIR_ . 'json/front-' . $this->id . '.json',
				json_encode($md5List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
			);
			return true;
		}

		return false;

	}
	
	public function cleanEmptyDirectory() {

		
		$url = 'https://' . $this->website . '/api';
	
		$data_array = [
			'action' => 'cleanEmptyDirectory',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setTimeout(6000);
		$curl->post($url, json_encode($data_array));
		

	}


	public function getJsonFile() {

		$url = 'https://' . $this->website . '/api';
	
		$data_array = [
			'action' => 'getJsonFile',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->setTimeout(6000);
		$curl->post($url, json_encode($data_array));
		$md5List = $curl->response;
		if (is_array($md5List)) {
			file_put_contents(
				_PS_CONFIG_DIR_ . 'json/' . $this->id . '.json',
				json_encode($md5List, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
			);
			return true;
		}

		return false;

	}

	public function getdBParam() {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getdBParam',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}
	
	public function getNeededSupplies() {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getNeededSupplies',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getExpeditionFile() {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getExpeditionFile',
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}

	public function pushTopMenu($topMenu) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'  => 'pushTopMenu',
			'topMenu' => $topMenu,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}
	
	public function pushFrontTopMenu($topMenu) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'  => 'pushFrontTopMenu',
			'topMenu' => $topMenu,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}
	
	public function pushFrontTopMenuWrap($topMenuWrap) {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'  => 'pushFrontTopMenuWrap',
			'topMenu' => $topMenuWrap,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
		
	}
	
	public function pushFrontTopMenuColumn($topMenuColumn) {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'  => 'pushFrontTopMenuColumn',
			'topMenu' => $topMenuColumn,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}

	public function synchTopMenu($topMenu) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'  => 'synchTopMenu',
			'topMenu' => $topMenu,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function pushEducationType($educationType) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'        => 'pushEducationType',
			'educationType' => $educationType,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function synchEducationType($educationType) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'        => 'synchEducationType',
			'educationType' => $educationType,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function pushEducation(Education $education, $imageToPush, $attributeToUpdate) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'            => 'pushEducation',
			'education'         => $education,
			'imageToPush'       => $imageToPush,
			'attributeToUpdate' => $attributeToUpdate,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function pushEducationToUpdate(Education $education, $imageToPush, $attributeToUpdate) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'            => 'educationToUpdate',
			'education'         => $education,
			'imageToPush'       => $imageToPush,
			'attributeToUpdate' => $attributeToUpdate,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function pushDeclinaison(Declinaison $combinationToPush) {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action'            => 'pushDeclinaison',
			'combinationToPush' => $combinationToPush,
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function pushSqlRequest($query, $method) {

		$url = 'https://' . $this->website . '/api';
		
		$data_array = [
			'action' => 'pushSqlRequest',
			'query'  => $query,
			'method' => $method,
		];
		
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}

	public function getMissingEducation() {

		$referentEducations = Education::getReferentEducations();
		$missingEducation = [];

		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentEducations as $education) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_education')
					->from('education')
					->where('`reference` LIKE \'' . $education['reference'] . '\'')
			);

			if ($exist > 0) {
				continue;
			} else {
				$missingEducation[] = json_decode(json_encode($education), true);
			}

		}

		return $missingEducation;
	}

	public function getExistingEducation() {

		$referentEducations = Education::getReferentEducations();

		$existingEducation = [];

		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentEducations as $education) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_education')
					->from('education')
					->where('`reference` LIKE \'' . $education['reference'] . '\'')
			);

			if ($exist > 0) {
				$existingEducation[] = json_decode(json_encode($education), true);
			}

		}

		return $existingEducation;
	}
	
	public function getMissingTopMenu() {
		
		$referentTopMenus = TopMenu::getReferentTopMenu();
		
		$missingMenus = [];
		
		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentTopMenus as $menu) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_topmenu')
					->from('topmenu')
					->where('`reference` LIKE \'' . $menu['reference'] . '\'')
			);

			if ($exist > 0) {
				continue;
			} else {
				$missingMenus[] = json_decode(json_encode(new TopMenu($menu['id_topmenu'])), true);
			}

		}

		return $missingMenus;
	}
	
	public function getMissingTopMenuColumn() {
		
		$referentTopMenus = TopMenuColumn::getReferentTopMenuColumn();
		
		$missingMenus = [];
		
		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentTopMenus as $menu) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_topmenu_column')
					->from('topmenu_columns')
					->where('`reference` LIKE \'' . $menu['reference'] . '\'')
			);

			if ($exist > 0) {
				continue;
			} else {
				$missingMenus[] = json_decode(json_encode(new TopMenuColumn($menu['id_topmenu_column'])), true);
			}

		}

		return $missingMenus;
	}
	
	public function getMissingTopMenuWrap() {
		
		$referentTopMenus = TopMenuColumnWrap::getReferentTopMenuWrap();
		
		$missingMenus = [];
		
		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentTopMenus as $menu) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_topmenu_columns_wrap')
					->from('topmenu_columns_wrap')
					->where('`reference` LIKE \'' . $menu['reference'] . '\'')
			);

			if ($exist > 0) {
				continue;
			} else {
				$missingMenus[] = json_decode(json_encode(new TopMenuColumnWrap($menu['id_topmenu_columns_wrap'])), true);
			}

		}

		return $missingMenus;
	}

	

	public function getExistingEducationType() {

		$referentEducationTypes = EducationType::getReferentEducationTypes();

		$existingEducationType = [];

		$dbParams = $this->getdBParam();

		$dbName = $dbParams['_DB_NAME_'];
		$dbPasswd = $dbParams['_DB_PASSWD_'];
		$dbUser = $dbParams['_DB_USER_'];

		foreach ($referentEducationTypes as $education) {

			$exist = Db::getCrmInstance($dbUser, $dbPasswd, $dbName)->getValue(
				(new DbQuery())
					->select('id_education_type')
					->from('education_type')
					->where('`reference` LIKE \'' . $education['reference'] . '\'')
			);

			if ($exist > 0) {
				$existingEducationType[] = json_decode(json_encode(new EducationType($education['id_education_type'])), true);
			}

		}

		return $existingEducationType;
	}

	

	
	public function getCertificationFile($idMonth) {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getCertificationFile',
			'idMonth' => $idMonth
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getPhenyxInvoices() {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getPhenyxInvoices'
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getPhenyxSupplies() {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getPhenyxSupplies'
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getPhenyxPrevisionnel($dateFrom, $dateTo) {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getPhenyxPrevisionnel',
			'dateFrom' => $dateFrom,
			'dateTo' => $dateTo
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getMissingEducationType() {

		$referentEducationTypes = EducationType::getReferentEducationTypes();
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getMissingEducationType',
			'referentEducationTypes' => $referentEducationTypes
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
		

		
	}
	
	public function getExistingTopBar() {

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getExistingTopBar',
			'referentTopBars' => $this->referentTopBars
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	
	public function getMissingTopBar() {
		
		$url = 'https://' . $this->website . '/api';
		$data_array = [
			'action' => 'getMissingTopBar',
			'referentTopBars' => $this->referentTopBars
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}
	
	public function getDistantTables() {

		
		$currentTables = Db::getInstance()->executeS('SHOW TABLES');

		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => 'getDistantTables',
			'currentTables' => $currentTables
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;

	}
	
	public function executeCronAction($cronAction) {
		
		$url = 'https://' . $this->website . '/api';

		$data_array = [
			'action' => $cronAction
		];
		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		return $curl->response;
	}

}
