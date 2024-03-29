<?php

/**
 * Class LanguageCore
 *
 * @since 1.9.1.0
 */
class LanguageCore extends PhenyxObjectModel {

    // @codingStandardsIgnoreStart
    /** @var array Languages cache */
    protected static $_checkedLangs;
    protected static $_LANGUAGES;
    protected static $countActiveLanguages = [];
    protected static $_cache_language_installation = null;
    /** @var string Name */
    public $name;
    /** @var string 2-letter iso code */
    public $iso_code;
    /** @var string 5-letter iso code */
    public $language_code;
    /** @var string date format http://http://php.net/manual/en/function.date.php with the date only */
    public $date_format_lite = 'Y-m-d';
    /** @var string date format http://http://php.net/manual/en/function.date.php with hours and minutes */
    public $date_format_full = 'Y-m-d H:i:s';
    /** @var bool true if this language is right to left language */
    public $is_rtl = false;
    /** @var bool Status */
    public $active = true;
    // @codingStandardsIgnoreEnd

    /**
     * @see PhenyxObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'lang',
        'primary' => 'id_lang',
        'fields'  => [
            'name'             => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'iso_code'         => ['type' => self::TYPE_STRING, 'validate' => 'isLanguageIsoCode', 'required' => true, 'size' => 2],
            'language_code'    => ['type' => self::TYPE_STRING, 'validate' => 'isLanguageCode', 'size' => 5],
            'active'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_rtl'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'date_format_lite' => ['type' => self::TYPE_STRING, 'validate' => 'isPhpDateFormat', 'required' => true, 'size' => 32],
            'date_format_full' => ['type' => self::TYPE_STRING, 'validate' => 'isPhpDateFormat', 'required' => true, 'size' => 32],
        ],
    ];
   
    protected $translationsFilesAndVars = [
        'fields' => '_FIELDS',
        'errors' => '_ERRORS',
        'admin'  => '_LANGADM',
        'pdf'    => '_LANGPDF',
        'tabs'   => 'tabs',
    ];

    /**
     * LanguageCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null) {

        parent::__construct($id);
    }

    /**
     * Returns an array of language IDs
     *
     * @param bool     $active Select only active languages
     * @param int|bool $idCompany Shop ID
     *
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getIDs($active = true) {

        return static::getLanguages($active, true);
    }

    /**
     * Returns available languages
     *
     * @param bool     $active  Select only active languages
     * @param int|bool $idCompany  Shop ID
     * @param bool     $idsOnly If true, returns an array of language IDs
     *
     * @return array Languages
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getLanguages($active = true, $idsOnly = false) {

        if (!static::$_LANGUAGES) {
            Language::loadLanguages();
        }

        $languages = [];

        foreach (static::$_LANGUAGES as $language) {

            if ($active && !$language['active']) {
                continue;
            }

            $languages[] = $idsOnly ? $language['id_lang'] : $language;
        }

        return $languages;
    }

    /**
     * Load all languages in memory for caching
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function loadLanguages() {

        static::$_LANGUAGES = [];

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('l.*')
                ->from('lang', 'l')
        );

        foreach ($result as $row) {

            if (!isset(static::$_LANGUAGES[(int) $row['id_lang']])) {
                static::$_LANGUAGES[(int) $row['id_lang']] = $row;
            }

            //static::$_LANGUAGES[(int) $row['id_lang']] = true;
        }

    }

    /**
     * @param $idLang
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getLanguage($idLang) {

        if (!isset(static::$_LANGUAGES[$idLang])) {
            return false;
        }

        return static::$_LANGUAGES[(int) ($idLang)];
    }

    /**
     * @param string $isoCode
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getLanguageCodeByIso($isoCode) {

        if (!Validate::isLanguageIsoCode($isoCode)) {
            die(Tools::displayError('Fatal error: ISO code is not correct') . ' ' . Tools::safeOutput($isoCode));
        }

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`language_code`')
                ->from('lang')
                ->where('`iso_code` = \'' . pSQL(strtolower($isoCode)) . '\'')
        );
    }

    /**
     * @param string $code
     *
     * @return bool|Language
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getLanguageByIETFCode($code) {

        if (!Validate::isLanguageCode($code)) {
            die(sprintf(Tools::displayError('Fatal error: IETF code %s is not correct'), Tools::safeOutput($code)));
        }

        // $code is in the form of 'xx-YY' where xx is the language code
        // and 'YY' a country code identifying a variant of the language.
        $langCountry = explode('-', $code);
        // Get the language component of the code
        $lang = $langCountry[0];

        // Find the id_lang of the language.
        // We look for anything with the correct language code
        // and sort on equality with the exact IETF code wanted.
        // That way using only one query we get either the exact wanted language
        // or a close match.
        $idLang = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_lang`, IF(language_code = \'' . pSQL($code) . '\', 0, LENGTH(language_code)) as found')
                ->from('lang')
                ->where('LEFT(`language_code`, 2) = \'' . pSQL($lang) . '\'')
                ->orderBy('`found` ASC')
        );

        // Instantiate the Language object if we found it.

        if ($idLang) {
            return new Language($idLang);
        } else {
            return false;
        }

    }

    /**
     * Return array (id_lang, iso_code)
     *
     * @param bool $active
     *
     * @return array Language (id_lang, iso_code)
     * @throws PhenyxShopException
     * @since    1.0.0
     * @version  1.0.0 Initial version
     */
    public static function getIsoIds($active = true) {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_lang`, `iso_code`')
                ->from('lang')
                ->where($active ? '`active` = 1' : '')
        );
    }

    /**
     * @param string $from
     * @param string $to
     *
     * @return bool
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function copyLanguageData($from, $to) {

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS('SHOW TABLES FROM `' . _DB_NAME_ . '`');

        foreach ($result as $row) {

            if (preg_match('/_lang/', $row['Tables_in_' . _DB_NAME_]) && $row['Tables_in_' . _DB_NAME_] != _DB_PREFIX_ . 'lang') {
                $result2 = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
                    (new DbQuery())
                        ->select('*')
                        ->from(bqSQL($row['Tables_in_' . _DB_NAME_]))
                        ->where('`id_lang` = ' . (int) $from)
                );

                if (!count($result2)) {
                    continue;
                }

                Db::getInstance()->delete(bQSQL($row['Tables_in_' . _DB_NAME_]), '`id_lang` = ' . (int) $to);
                $query = 'INSERT INTO `' . $row['Tables_in_' . _DB_NAME_] . '` VALUES ';

                foreach ($result2 as $row2) {
                    $query .= '(';
                    $row2['id_lang'] = $to;

                    foreach ($row2 as $field) {
                        $query .= (!is_string($field) && $field == null) ? 'NULL,' : '\'' . pSQL($field, true) . '\',';
                    }

                    $query = rtrim($query, ',') . '),';
                }

                $query = rtrim($query, ',');
                Db::getInstance()->execute($query);
            }

        }

        return true;
    }

    /**
     * @param $iso_code
     *
     * @return bool|mixed
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function isInstalled($iso_code) {

        if (static::$_cache_language_installation === null) {
            static::$_cache_language_installation = [];
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select('`id_lang`, `iso_code`')
                    ->from('lang')
            );

            foreach ($result as $row) {
                static::$_cache_language_installation[$row['iso_code']] = $row['id_lang'];
            }

        }

        return (isset(static::$_cache_language_installation[$iso_code]) ? static::$_cache_language_installation[$iso_code] : false);
    }

    /**
     * Check if more on than one language is activated
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function isMultiLanguageActivated($idCompany = null) {

        return (Language::countActiveLanguages($idCompany) > 1);
    }

    /**
     * @param null $idCompany
     *
     * @return mixed
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function countActiveLanguages($idCompany = null) {

        

        if (!isset(static::$countActiveLanguages)) {
            static::$countActiveLanguages = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(DISTINCT l.`id_lang`)')
                    ->from('lang', 'l')                    
                    
                    ->where('l.`active` = 1')
            );
        }

        return static::$countActiveLanguages;
    }

    /**
     * @param array $modulesList
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function updateModulesTranslations(Array $modulesList) {

        $languages = Language::getLanguages(false);

        foreach ($languages as $lang) {
            $gz = false;
            $filesListing = [];

            foreach ($modulesList as $moduleName) {
                $filegz = _EPH_TRANSLATIONS_DIR_ . $lang['iso_code'] . '.gzip';

                clearstatcache();

                if (@filemtime($filegz) < (time() - (24 * 3600))) {

                    if (Language::downloadAndInstallLanguagePack($lang['iso_code'], null, null, false) !== true) {
                        break;
                    }

                }

                $gz = new Archive_Tar($filegz, true);
                $filesList = Language::getLanguagePackListContent($lang['iso_code'], $gz);

                foreach ($filesList as $i => $file) {

                    if (strpos($file['filename'], 'plugins/' . $moduleName . '/') !== 0) {
                        unset($filesList[$i]);
                    }

                }

                foreach ($filesList as $file) {

                    if (isset($file['filename']) && is_string($file['filename'])) {
                        $filesListing[] = $file['filename'];
                    }

                }

            }

            if ($gz) {
                $gz->extractList($filesListing, _EPH_TRANSLATIONS_DIR_ . '../', '');
            }

        }

    }

    /**
     * @param string      $iso
     * @param string|null $version
     * @param array|null  $params
     * @param bool        $install
     *
     * @return array|bool
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function downloadAndInstallLanguagePack($iso, $version = null, $params = null, $install = true) {

        if (!Validate::isLanguageIsoCode((string) $iso)) {
            return false;
        }

        if ($version == null) {
            $version = _EPH_VERSION_;
        }

        $version = implode('.', array_map('intval', explode('.', $version, 3)));

        $langPack = false;
        $errors = [];
        $file = _EPH_TRANSLATIONS_DIR_ . (string) $iso . '.gzip';
        $guzzle = new GuzzleHttp\Client([
            'base_uri' => "https://translations.ephenyx.com/packs/{$version}/",
            'timeout'  => 20,
            'verify'   => _EPH_TOOL_DIR_ . 'cacert.pem',
        ]);

        try {
            $langPackLink = (string) $guzzle->get("{$iso}.json")->getBody();
        } catch (Exception $e) {
            $langPackLink = false;
        }

        if (!$langPackLink) {
            $errors[] = Tools::displayError('Language pack cannot be downloaded from ephenyx.com.');
        } else if (!$langPack = json_decode($langPackLink)) {
            $errors[] = Tools::displayError('Error occurred when language was checked according to your ephenyx version.');
        } else if (!static::checkAndAddLanguage((string) $iso, $langPack, false, $params)) {
            $errors[] = sprintf(Tools::displayError('An error occurred while creating the language: %s'), (string) $iso);
        }

        if (!Language::getIdByIso($iso, true)) {
            return $errors;
        }

        $success = null;

        if (isset($langPack->name)) {
            try {
                $guzzle->get("{$iso}.gzip", ['sink' => $file]);
                $success = true;
            } catch (Exception $e) {
                $success = false;
            }

            if ($success && !@file_exists($file)) {

                if (!is_writable($file)) {
                    $errors[] = Tools::displayError('Server does not have permissions for writing.') . ' (' . $file . ')';
                }

            }

        }

        if (!file_exists($file)) {
            $errors[] = Tools::displayError('No translations pack available for your version.');
        } else if ($install) {
            $gz = new Archive_Tar($file, true);
            $fileList = AdminTranslationsController::filterTranslationFiles(Language::getLanguagePackListContent((string) $iso, $gz));
            $filePaths = AdminTranslationsController::filesListToPaths($fileList);
            $i = 0;
            $tmpArray = [];

            foreach ($filePaths as $filePath) {
                $path = dirname($filePath);

                if (is_dir(_EPH_TRANSLATIONS_DIR_ . '../' . $path) && !is_writable(_EPH_TRANSLATIONS_DIR_ . '../' . $path) && !in_array($path, $tmpArray)) {
                    $errors[] = (!$i++ ? Tools::displayError('Translation pack cannot be extracted.') . ' ' : '') . Tools::displayError('The server does not have permissions for writing.') . ' ' . sprintf(Tools::displayError('Please check rights for %s'), $path);
                    $tmpArray[] = $path;
                }

            }

            if (!$gz->extractList(AdminTranslationsController::filesListToPaths($fileList), _EPH_TRANSLATIONS_DIR_ . '../')) {
                $errors[] = sprintf(Tools::displayError('Cannot decompress the translation file for the following language: %s'), (string) $iso);
            }

            // Clear smarty modules cache
            Tools::clearCache();
            // Reset cache
            Language::loadLanguages();
            AdminTranslationsController::checkAndAddMailsFiles((string) $iso, $fileList);
            AdminTranslationsController::addNewTabs((string) $iso, $fileList);
        }

        if ($success) {
            @unlink($file);
        }

        return count($errors) ? $errors : true;
    }

    /**
     * @param string      $iso
     * @param Archive_Tar $tar
     *
     * @return array|bool|int|null
     */
    public static function getLanguagePackListContent($iso, $tar) {

        $key = 'Language::getLanguagePackListContent_' . $iso;

        if (!Cache::isStored($key)) {

            if (!$tar instanceof Archive_Tar) {
                return false;
            }

            $result = $tar->listContent();
            Cache::store($key, $result);

            return $result;
        }

        return Cache::retrieve($key);
    }

    /**
     * @param string        $isoCode
     * @param Language|bool $langPack
     * @param bool          $onlyAdd
     * @param array|null    $paramsLang
     *
     * @throws PhenyxShopException
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function checkAndAddLanguage($isoCode, $langPack = false, $onlyAdd = false, $paramsLang = null) {

        if (!Validate::isLanguageIsoCode($isoCode)) {
            return false;
        }

        if (Language::getIdByIso($isoCode, true)) {
            return true;
        }

        // Initialize the language
        $lang = new Language();
        $lang->iso_code = mb_strtolower($isoCode);
        $lang->language_code = $isoCode; // Rewritten afterwards if the language code is available
        $lang->active = true;

        // If the language pack has not been provided, retrieve it from translations.ephenyx.com

        if (!$langPack) {
            $version = implode('.', array_map('intval', explode('.', _EPH_VERSION_, 3)));
            $guzzle = new GuzzleHttp\Client([
                'base_uri' => "https://translations.ephenyx.com/packs/{$version}/",
                'timeout'  => 20,
                'verify'   => _EPH_TOOL_DIR_ . 'cacert.pem',
            ]);

            try {
                $lowerIso = mb_strtolower($isoCode);
                $langPack = json_decode((string) $guzzle->get("{$lowerIso}.json")->getBody());
            } catch (Exception $e) {
                $langPack = false;
            }

        }

        // If a language pack has been found or provided, prefill the language object with the value

        if ($langPack) {

            foreach (get_object_vars($langPack) as $key => $value) {

                if ($key != 'iso_code' && isset(Language::$definition['fields'][$key])) {
                    $lang->$key = $value;
                }

            }

        }

        // Use the values given in parameters to override the data retrieved automatically

        if ($paramsLang !== null && is_array($paramsLang)) {

            foreach ($paramsLang as $key => $value) {

                if ($key != 'iso_code' && isset(Language::$definition['fields'][$key])) {
                    $lang->$key = $value;
                }

            }

        }

        if (!$lang->name && $lang->iso_code) {
            $lang->name = $lang->iso_code;
        }

        if (!$lang->validateFields() || !$lang->validateFieldsLang() || !$lang->add(true, false, $onlyAdd)) {
            return false;
        }

        if (isset($paramsLang['allow_accented_chars_url']) && in_array($paramsLang['allow_accented_chars_url'], ['1', 'true'])) {
            Configuration::updateGlobalValue('EPH_ALLOW_ACCENTED_CHARS_URL', 1);
        }

        Language::_copyNoneFlag((int) $lang->id);

        $filesCopy = [
            '/en.jpg',
            '/en-default-' . ImageType::getFormatedName('thickbox') . '.jpg',
            '/en-default-' . ImageType::getFormatedName('home') . '.jpg',
            '/en-default-' . ImageType::getFormatedName('large') . '.jpg',
            '/en-default-' . ImageType::getFormatedName('medium') . '.jpg',
            '/en-default-' . ImageType::getFormatedName('small') . '.jpg',
            '/en-default-' . ImageType::getFormatedName('scene') . '.jpg',
        ];

        foreach ([_EPH_CAT_IMG_DIR_, _EPH_MANU_IMG_DIR_, _EPH_PROD_IMG_DIR_, _EPH_SUPP_IMG_DIR_] as $to) {

            foreach ($filesCopy as $file) {
                @copy(_EPH_ROOT_DIR_ . '/img/l' . $file, $to . str_replace('/en', '/' . $isoCode, $file));
            }

        }

        return true;
    }

    /**
     * Return id from iso code
     *
     * @param string $isoCode Iso code
     * @param bool   $noCache
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getIdByIso($isoCode, $noCache = false) {

        if (!Validate::isLanguageIsoCode($isoCode)) {
            die(Tools::displayError('Fatal error: ISO code is not correct') . ' ' . Tools::safeOutput($isoCode));
        }

        $key = 'Language::getIdByIso_' . $isoCode;

        if ($noCache || !Cache::isStored($key)) {
            $idLang = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('`id_lang`')
                    ->from('lang')
                    ->where('`iso_code` = \'' . pSQL($isoCode) . '\'')
            );

            Cache::store($key, $idLang);

            return $idLang;
        }

        return Cache::retrieve($key);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     * @param bool $onlyAdd
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function add($autoDate = true, $nullValues = false, $onlyAdd = false) {

        if (!parent::add($autoDate, $nullValues)) {
            return false;
        }

        if ($onlyAdd) {
            return true;
        }

        // create empty files if they not exists
        $this->_generateFiles();

        // Set default language routes
        Configuration::updateValue('EPH_ROUTE_education_rule', [$this->id => '{type:/}{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_education_type_rule', [$this->id => '{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_layered_rule', [$this->id => 'categorie/{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_supplier_rule', [$this->id => '{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_manufacturer_rule', [$this->id => '{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_cms_rule', [$this->id => 'info/{categories:/}{rewrite}']);
        Configuration::updateValue('EPH_ROUTE_cms_category_rule', [$this->id => 'info/{categories:/}{rewrite}']);

        $this->loadUpdateSQL();

        return true;
    }

    /**
     * Generate translations files
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     */
    protected function _generateFiles($newIso = null) {

        $isoCode = $newIso ? $newIso : $this->iso_code;

        if (!file_exists(_EPH_TRANSLATIONS_DIR_ . $isoCode)) {

            if (@mkdir(_EPH_TRANSLATIONS_DIR_ . $isoCode)) {
                @chmod(_EPH_TRANSLATIONS_DIR_ . $isoCode, 0777);
            }

        }

        foreach ($this->translationsFilesAndVars as $file => $var) {
            $pathFile = _EPH_TRANSLATIONS_DIR_ . $isoCode . '/' . $file . '.php';

            if (!file_exists($pathFile)) {

                if ($file != 'tabs') {
                    @file_put_contents(
                        $pathFile, '<?php
    global $' . $var . ';
    $' . $var . ' = array();
?>'
                    );
                } else {
                    @file_put_contents(
                        $pathFile, '<?php
    $' . $var . ' = array();
    return $' . $var . ';
?>'
                    );
                }

            }

            @chmod($pathFile, 0777);
        }

    }

    /**
     * loadUpdateSQL will create default lang values when you create a new lang, based on default id lang
     *
     * @return bool true if succeed
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function loadUpdateSQL() {

        $tables = Db::getInstance()->executeS('SHOW TABLES LIKE \'' . str_replace('_', '\\_', _DB_PREFIX_) . '%\_lang\' ');
        $langTables = [];

        foreach ($tables as $table) {

            foreach ($table as $t) {

                if ($t != _DB_PREFIX_ . 'configuration_lang') {
                    $langTables[] = $t;
                }

            }

        }

        $return = true;

       
        return $return;
    }

    /**
     * @param int        $id
     * @param int|string $iso
     *
     * @return bool
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     * @since 1.0.2 Made public
     *        Accept an ID or ISO, ID has a higher priority
     */
    public static function _copyNoneFlag($id, $iso = null) {

        if ($id) {
            static::loadLanguages();

            if ($databaseIso = Language::getIsoById($id)) {
                $iso = $databaseIso;
            }

        }

        if ($iso) {

            if (file_exists(_EPH_ROOT_DIR_ . '/img/flags/' . strtolower($iso) . '.png')) {
                return ImageManager::resize(
                    _EPH_ROOT_DIR_ . '/img/flags/' . strtolower($iso) . '.png',
                    _EPH_ROOT_DIR_ . '/img/l/' . $id . '.jpg',
                    null,
                    null,
                    'jpg',
                    true
                );
            }

        }

        return copy(_EPH_ROOT_DIR_ . '/img/l/none.jpg', _EPH_ROOT_DIR_ . '/img/l/' . $id . '.jpg');
    }

    /**
     * @see     PhenyxObjectModel::getFields()
     * @return array
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getFields() {

        $this->iso_code = strtolower($this->iso_code);

        if (empty($this->language_code)) {
            $this->language_code = $this->iso_code;
        }

        return parent::getFields();
    }

    /**
     * Move translations files after editing language iso code
     *
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function moveToIso($newIso) {

        if ($newIso == $this->iso_code) {
            return true;
        }

        if (file_exists(_EPH_TRANSLATIONS_DIR_ . $this->iso_code)) {
            rename(_EPH_TRANSLATIONS_DIR_ . $this->iso_code, _EPH_TRANSLATIONS_DIR_ . $newIso);
        }

        if (file_exists(_EPH_MAIL_DIR_ . $this->iso_code)) {
            rename(_EPH_MAIL_DIR_ . $this->iso_code, _EPH_MAIL_DIR_ . $newIso);
        }

        $modulesList = Module::getModulesDirOnDisk();

        foreach ($modulesList as $moduleDir) {

            if (file_exists(_EPH_MODULE_DIR_ . $moduleDir . '/mails/' . $this->iso_code)) {
                rename(_EPH_MODULE_DIR_ . $moduleDir . '/mails/' . $this->iso_code, _EPH_MODULE_DIR_ . $moduleDir . '/mails/' . $newIso);
            }

            if (file_exists(_EPH_MODULE_DIR_ . $moduleDir . '/' . $this->iso_code . '.php')) {
                rename(_EPH_MODULE_DIR_ . $moduleDir . '/' . $this->iso_code . '.php', _EPH_MODULE_DIR_ . $moduleDir . '/' . $newIso . '.php');
            }

        }

        foreach (Theme::getThemes() as $theme) {
            /** @var Theme $theme */
            $themeDir = $theme->directory;

            if (file_exists(_EPH_ALL_THEMES_DIR_ . $themeDir . '/lang/' . $this->iso_code . '.php')) {
                rename(_EPH_ALL_THEMES_DIR_ . $themeDir . '/lang/' . $this->iso_code . '.php', _EPH_ALL_THEMES_DIR_ . $themeDir . '/lang/' . $newIso . '.php');
            }

            if (file_exists(_EPH_ALL_THEMES_DIR_ . $themeDir . '/mails/' . $this->iso_code)) {
                rename(_EPH_ALL_THEMES_DIR_ . $themeDir . '/mails/' . $this->iso_code, _EPH_ALL_THEMES_DIR_ . $themeDir . '/mails/' . $newIso);
            }

            foreach ($modulesList as $module) {

                if (file_exists(_EPH_ALL_THEMES_DIR_ . $themeDir . '/plugins/' . $module . '/' . $this->iso_code . '.php')) {
                    rename(_EPH_ALL_THEMES_DIR_ . $themeDir . '/plugins/' . $module . '/' . $this->iso_code . '.php', _EPH_ALL_THEMES_DIR_ . $themeDir . '/plugins/' . $module . '/' . $newIso . '.php');
                }

            }

        }

    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function checkFiles() {

        return Language::checkFilesWithIsoCode($this->iso_code);
    }

    /**
     * This functions checks if every files exists for the language $iso_code.
     * Concerned files are those located in translations/$iso_code/
     * and translations/mails/$iso_code .
     *
     * @param mixed $isoCode
     *
     * @return bool true if all files exists
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function checkFilesWithIsoCode($isoCode) {

        if (isset(static::$_checkedLangs[$isoCode]) && static::$_checkedLangs[$isoCode]) {
            return true;
        }

        foreach (array_keys(Language::getFilesList($isoCode, _THEME_NAME_, false, false, false, true)) as $key) {

            if (!file_exists($key)) {
                return false;
            }

        }

        static::$_checkedLangs[$isoCode] = true;

        return true;
    }

    /**
     * @param string      $isoFrom
     * @param string      $themeFrom
     * @param bool|string $isoTo
     * @param bool|string $themeTo
     * @param bool        $select
     * @param bool        $check
     * @param bool        $modules
     *
     * @return array
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getFilesList($isoFrom, $themeFrom, $isoTo = false, $themeTo = false, $select = false, $check = false, $modules = false) {

        if (empty($isoFrom)) {
            die(Tools::displayError());
        }

        $copy = ($isoTo && $themeTo) ? true : false;

        $lPathFrom = _EPH_TRANSLATIONS_DIR_ . (string) $isoFrom . '/';
        $tPathFrom = _EPH_ROOT_DIR_ . _EPH_THEMES_DIR_ . (string) $themeFrom . '/';
        $pPathFrom = _EPH_ROOT_DIR_ . _EPH_THEMES_DIR_ . (string) $themeFrom . '/pdf/';
        $mPathFrom = _EPH_MAIL_DIR_ . (string) $isoFrom . '/';

        if ($copy) {
            $lPathTo = _EPH_TRANSLATIONS_DIR_ . (string) $isoTo . '/';
            $tPathTo = _EPH_ROOT_DIR_ . _EPH_THEMES_DIR_ . (string) $themeTo . '/';
            $pPathTo = _EPH_ROOT_DIR_ . _EPH_THEMES_DIR_ . (string) $themeTo . '/pdf/';
            $mPathTo = _EPH_MAIL_DIR_ . (string) $isoTo . '/';
        }

        $lFiles = ['admin.php', 'errors.php', 'fields.php', 'pdf.php', 'tabs.php'];

        // Added natives mails files
        $mFiles = [
            'account.html', 'account.txt',
            'backoffice_order.html', 'backoffice_order.txt',
            'bankwire.html', 'bankwire.txt',
            'cheque.html', 'cheque.txt',
            'contact.html', 'contact.txt',
            'contact_form.html', 'contact_form.txt',
            'credit_slip.html', 'credit_slip.txt',
            'download_product.html', 'download_product.txt',
            'employee_password.html', 'employee_password.txt',
            'forward_msg.html', 'forward_msg.txt',
            'guest_to_customer.html', 'guest_to_customer.txt',
            'in_transit.html', 'in_transit.txt',
            'log_alert.html', 'log_alert.txt',
            'newsletter.html', 'newsletter.txt',
            'order_canceled.html', 'order_canceled.txt',
            'order_conf.html', 'order_conf.txt',
            'order_customer_comment.html', 'order_customer_comment.txt',
            'order_merchant_comment.html', 'order_merchant_comment.txt',
            'order_return_state.html', 'order_return_state.txt',
            'outofstock.html', 'outofstock.txt',
            'password.html', 'password.txt',
            'password_query.html', 'password_query.txt',
            'payment.html', 'payment.txt',
            'payment_error.html', 'payment_error.txt',
            'preparation.html', 'preparation.txt',
            'refund.html', 'refund.txt',
            'reply_msg.html', 'reply_msg.txt',
            'shipped.html', 'shipped.txt',
            'test.html', 'test.txt',
            'voucher.html', 'voucher.txt',
            'voucher_new.html', 'voucher_new.txt',
            'order_changed.html', 'order_changed.txt',
        ];

        $number = -1;

        $files = [];
        $filesTr = [];
        $filesTheme = [];
        $filesMail = [];
        $filesModules = [];

        // When a copy is made from a theme in specific language
        // to an other theme for the same language,
        // it's avoid to copy Translations, Mails files
        // and modules files which are not override by theme.

        if (!$copy || $isoFrom != $isoTo) {
            // Translations files

            if (!$check || ($check && (string) $isoFrom != 'en')) {

                foreach ($lFiles as $file) {
                    $filesTr[$lPathFrom . $file] = ($copy ? $lPathTo . $file : ++$number);
                }

            }

            if ($select == 'tr') {
                return $filesTr;
            }

            $files = array_merge($files, $filesTr);

            // Mail files

            if (!$check || ($check && (string) $isoFrom != 'en')) {
                $filesMail[$mPathFrom . 'lang.php'] = ($copy ? $mPathTo . 'lang.php' : ++$number);
            }

            foreach ($mFiles as $file) {
                $filesMail[$mPathFrom . $file] = ($copy ? $mPathTo . $file : ++$number);
            }

            if ($select == 'mail') {
                return $filesMail;
            }

            $files = array_merge($files, $filesMail);

            // Modules

            if ($modules) {
                $modList = Module::getModulesDirOnDisk();

                foreach ($modList as $mod) {
                    $modDir = _EPH_MODULE_DIR_ . $mod;
                    // Lang file

                    if (file_exists($modDir . '/translations/' . (string) $isoFrom . '.php')) {
                        $filesModules[$modDir . '/translations/' . (string) $isoFrom . '.php'] = ($copy ? $modDir . '/translations/' . (string) $isoTo . '.php' : ++$number);
                    } else if (file_exists($modDir . '/' . (string) $isoFrom . '.php')) {
                        $filesModules[$modDir . '/' . (string) $isoFrom . '.php'] = ($copy ? $modDir . '/' . (string) $isoTo . '.php' : ++$number);
                    }

                    // Mails files
                    $modMailDirFrom = $modDir . '/mails/' . (string) $isoFrom;
                    $modMailDirTo = $modDir . '/mails/' . (string) $isoTo;

                    if (file_exists($modMailDirFrom)) {
                        $dirFiles = scandir($modMailDirFrom);

                        foreach ($dirFiles as $file) {

                            if (file_exists($modMailDirFrom . '/' . $file) && $file != '.' && $file != '..' && $file != '.svn') {
                                $filesModules[$modMailDirFrom . '/' . $file] = ($copy ? $modMailDirTo . '/' . $file : ++$number);
                            }

                        }

                    }

                }

                if ($select == 'modules') {
                    return $filesModules;
                }

                $files = array_merge($files, $filesModules);
            }

        } else if ($select == 'mail' || $select == 'tr') {
            return $files;
        }

        // Theme files

        if (!$check || ($check && (string) $isoFrom != 'en')) {
            $filesTheme[$tPathFrom . 'lang/' . (string) $isoFrom . '.php'] = ($copy ? $tPathTo . 'lang/' . (string) $isoTo . '.php' : ++$number);

            // Override for pdf files in the theme

            if (file_exists($pPathFrom . 'lang/' . (string) $isoFrom . '.php')) {
                $filesTheme[$pPathFrom . 'lang/' . (string) $isoFrom . '.php'] = ($copy ? $pPathTo . 'lang/' . (string) $isoTo . '.php' : ++$number);
            }

            $moduleThemeFiles = (file_exists($tPathFrom . 'plugins/') ? scandir($tPathFrom . 'plugins/') : []);

            foreach ($moduleThemeFiles as $module) {

                if ($module !== '.' && $module != '..' && $module !== '.svn' && file_exists($tPathFrom . 'plugins/' . $module . '/translations/' . (string) $isoFrom . '.php')) {
                    $filesTheme[$tPathFrom . 'plugins/' . $module . '/translations/' . (string) $isoFrom . '.php'] = ($copy ? $tPathTo . 'plugins/' . $module . '/translations/' . (string) $isoTo . '.php' : ++$number);
                }

            }

        }

        if ($select == 'theme') {
            return $filesTheme;
        }

        $files = array_merge($files, $filesTheme);

        // Return
        return $files;
    }

    /**
     * @param array $selection
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function deleteSelection($selection) {

        if (!is_array($selection)) {
            die(Tools::displayError());
        }

        $result = true;

        foreach ($selection as $id) {
            $language = new Language($id);
            $result = $result && $language->delete();
        }

        return $result;
    }

    /**
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function delete() {

       

            if (empty($this->iso_code)) {
                $this->iso_code = Language::getIsoById($this->id);
            }

            // Database translations deletion
            $result = Db::getInstance()->executeS('SHOW TABLES FROM `' . _DB_NAME_ . '`');

            foreach ($result as $row) {

                if (isset($row['Tables_in_' . _DB_NAME_]) && !empty($row['Tables_in_' . _DB_NAME_]) && preg_match('/' . preg_quote(_DB_PREFIX_) . '_lang/', $row['Tables_in_' . _DB_NAME_])) {

                    if (!Db::getInstance()->delete(bqSQL($row['Tables_in_' . _DB_NAME_]), '`id_lang` = ' . (int) $this->id)) {
                        return false;
                    }

                }

            }

            // Delete tags
            Db::getInstance()->delete('tag', '`id_lang` = ' . (int) $this->id);

            // Delete search words
            Db::getInstance()->delete('search_word', '`id_lang` = ' . (int) $this->id);

            // Files deletion

            foreach (Language::getFilesList($this->iso_code, _THEME_NAME_, false, false, false, true, true) as $key => $file) {

                if (file_exists($key)) {
                    unlink($key);
                }

            }

            $modList = scandir(_EPH_MODULE_DIR_);

            foreach ($modList as $mod) {
                Language::recurseDeleteDir(_EPH_MODULE_DIR_ . $mod . '/mails/' . $this->iso_code);
                $files = @scandir(_EPH_MODULE_DIR_ . $mod . '/mails/');

                if (count($files) <= 2) {
                    Language::recurseDeleteDir(_EPH_MODULE_DIR_ . $mod . '/mails/');
                }

                if (file_exists(_EPH_MODULE_DIR_ . $mod . '/' . $this->iso_code . '.php')) {
                    unlink(_EPH_MODULE_DIR_ . $mod . '/' . $this->iso_code . '.php');
                    $files = @scandir(_EPH_MODULE_DIR_ . $mod);

                    if (count($files) <= 2) {
                        Language::recurseDeleteDir(_EPH_MODULE_DIR_ . $mod);
                    }

                }

            }

            if (file_exists(_EPH_MAIL_DIR_ . $this->iso_code)) {
                Language::recurseDeleteDir(_EPH_MAIL_DIR_ . $this->iso_code);
            }

            if (file_exists(_EPH_TRANSLATIONS_DIR_ . $this->iso_code)) {
                Language::recurseDeleteDir(_EPH_TRANSLATIONS_DIR_ . $this->iso_code);
            }

            $images = [
                '.jpg',
                '-default-' . ImageType::getFormatedName('thickbox') . '.jpg',
                '-default-' . ImageType::getFormatedName('home') . '.jpg',
                '-default-' . ImageType::getFormatedName('large') . '.jpg',
                '-default-' . ImageType::getFormatedName('medium') . '.jpg',
                '-default-' . ImageType::getFormatedName('small') . '.jpg',
            ];
            $imagesDirectories = [_EPH_CAT_IMG_DIR_, _EPH_MANU_IMG_DIR_, _EPH_PROD_IMG_DIR_, _EPH_SUPP_IMG_DIR_];

            foreach ($imagesDirectories as $imageDirectory) {

                foreach ($images as $image) {

                    if (file_exists($imageDirectory . $this->iso_code . $image)) {
                        unlink($imageDirectory . $this->iso_code . $image);
                    }

                    if (file_exists(_EPH_ROOT_DIR_ . '/img/l/' . $this->id . '.jpg')) {
                        unlink(_EPH_ROOT_DIR_ . '/img/l/' . $this->id . '.jpg');
                    }

                }

            }

        

        if (!parent::delete()) {
            return false;
        }

        return true;
    }

    /**
     * Return iso code from id
     *
     * @param int $idLang Language ID
     *
     * @return string Iso code
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getIsoById($idLang) {

        if (isset(static::$_LANGUAGES[(int) $idLang]['iso_code'])) {
            return static::$_LANGUAGES[(int) $idLang]['iso_code'];
        }

        return false;
    }

    /**
     * @param $dir
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function recurseDeleteDir($dir) {

        if (!is_dir($dir)) {
            return false;
        }

        if ($handle = @opendir($dir)) {

            while (false !== ($file = readdir($handle))) {

                if ($file != '.' && $file != '..') {

                    if (is_dir($dir . '/' . $file)) {
                        Language::recurseDeleteDir($dir . '/' . $file);
                    } else if (file_exists($dir . '/' . $file)) {
                        @unlink($dir . '/' . $file);
                    }

                }

            }

            closedir($handle);
        }

        if (is_writable($dir)) {
            rmdir($dir);
        }

    }

    /**
     * Return an array of theme
     *
     * @return array([theme dir] => array('name' => [theme name]))
     * @deprecated 1.0.0
     * @throws PhenyxShopException
     */
    protected function _getThemesList() {

        Tools::displayAsDeprecated();

        static $themes = [];

        if (empty($themes)) {
            $installedThemes = Theme::getThemes();

            foreach ($installedThemes as $theme) {
                /** @var Theme $theme */
                $themes[$theme->directory] = ['name' => $theme->name];
            }

        }

        return $themes;
    }

}
