<?php

/**
 * Class EducationDownloadCore
 *
 * @since 1.9.1.0
 */
class EducationDownloadCore extends ObjectModel {

    // @codingStandardsIgnoreStart

    /** @deprecated 1.0.2 This cache is no longer used. */
    protected static $_educationIds = [];

    /** @var int Education id which download belongs */
    public $id_education = 0;
    /** @var string DisplayFilename the name which appear */
    public $display_filename = '';
    /** @var string PhysicallyFilename the name of the file on hard disk */
    public $filename = '';
    /** @var string DateDeposit when the file is upload */
    public $date_add = '0000-00-00 00:00:00';
    /** @var string DateExpiration deadline of the file */
    public $date_expiration = '0000-00-00 00:00:00';
    /** @var string NbDaysAccessible how many days the customer can access to file */
    public $nb_days_accessible = 0;
    /** @var string NbDownloadable how many time the customer can download the file */
    public $nb_downloadable = 0;
    /** @var bool Active if file is accessible or not */
    public $active = 1;
    /** @var bool is_shareable indicates whether the education can be shared */
    public $is_shareable = 0;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'education_download',
        'primary' => 'id_education_download',
        'fields'  => [
            'id_education'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'display_filename'   => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 255],
            'filename'           => ['type' => self::TYPE_STRING, 'validate' => 'isSha1', 'size' => 255],
            'date_add'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_expiration'    => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'nb_days_accessible' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'size' => 10],
            'nb_downloadable'    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'size' => 10],
            'active'             => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'is_shareable'       => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

    /**
     * Build a virtual education
     *
     * @param int $idEducationDownload Existing educationDownload id in order to load object (optional)
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($idEducationDownload = null) {

        parent::__construct($idEducationDownload);
        // @TODO check if the file is present on hard drive
    }

    /**
     * Check if download repository is writable
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function checkWritableDir() {

        return is_writable(_PS_DOWNLOAD_DIR_);
    }

    /**
     * Find a education's download. As class Education doesn't maintain it's
     * download, that's the way to find out wether there's a download and
     * which one it is.
     *
     * @param int  $idEducation Education ID.
     * @param bool $active    Wether only an active download or any download.
     *
     * @return int ID of the education download or 0 if there's none.
     *
     * @since   1.0.2 Removed caching, which ignored $active.
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getIdFromIdEducation($idEducation, $active = true) {

        $id = (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_education_download`')
                ->from('education_download')
                ->where('`id_education` = ' . (int) $idEducation)
                ->where($active ? '`active` = 1' : '')
                ->orderBy('`id_education_download` DESC')
        );

        // @deprecated 1.0.2
        static::$_educationIds[$idEducation] = $id;

        return $id;
    }

    /**
     * This method is allow to know if a feature is used or active
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function isFeatureActive() {

        return Configuration::get('PS_VIRTUAL_PROD_FEATURE_ACTIVE');
    }

    /**
     * Return the display filename from a physical filename
     *
     * @param string $filename Filename physically
     *
     * @return int Education the id for this virtual education
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     * @throws PhenyxShopException
     */
    public static function getIdFromFilename($filename) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_education_download`')
                ->from('education_download')
                ->where('`filename` = \'' . pSQL($filename) . '\'')
        );
    }

    /**
     * Return the filename from an id_education
     *
     * @param int $idEducation Education the id
     *
     * @return string Filename the filename for this virtual education
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getFilenameFromIdEducation($idEducation) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`filename`')
                ->from('education_download')
                ->where('`id_education` = ' . (int) $idEducation)
                ->where('`active` = 1')
        );
    }

    /**
     * Return the display filename from a physical filename
     *
     * @param string $filename Filename physically
     *
     * @return string Filename the display filename for this virtual education
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getFilenameFromFilename($filename) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`display_filename`')
                ->from('education_download')
                ->where('`filename` = \'' . pSQL($filename) . '\'')
        );
    }

    /**
     * Return a sha1 filename
     *
     * @return string Sha1 unique filename
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getNewFilename() {

        do {
            $filename = sha1(microtime());
        } while (file_exists(_PS_DOWNLOAD_DIR_ . $filename));

        return $filename;
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function update($nullValues = false) {

        if (parent::update($nullValues)) {
            // Refresh cache of feature detachable because the row can be deactive
            Configuration::updateGlobalValue('PS_VIRTUAL_PROD_FEATURE_ACTIVE', EducationDownload::isCurrentlyUsed($this->def['table'], true));

            return true;
        }

        return false;
    }

    /**
     * @param bool $deleteFile Deprecated. File gets always deleted.
     *
     * @return bool True on successful deletion of file and DB entry.
     *
     * @since   1.0.3 Deprecate $deleteFile in favor of always deleting it. A
     *                file without matching DB entry means just a leaked file.
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function delete($deleteFile = 999) {

        if ($deleteFile !== 999) {
            Tools::displayParameterAsDeprecated('deleteFile');
        }

        return $this->deleteFile() && parent::delete();
    }

    /**
     * Delete the file
     *
     * @param int $idEducationDownload : if we need to delete a specific education attribute file
     *
     * @return bool True if file didn't exist or was deleted successfully.
     *              False if the existing file couldn't get deleted.
     *
     * @since   1.0.3 Deprecate, but still handle $idEducationDownload. Code
     *                wanting to also delete the DB entry should use delete().
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function deleteFile($idEducationDownload = 999) {

        if ($idEducationDownload !== 999) {
            Tools::displayParameterAsDeprecated('idEducationDownload');

            // Retrocompatibility.

            if ($idEducationDownload) {
                $download = new EducationDownload($idEducationDownload);
                return $download->delete();
            }

        }

        $result = !$this->checkFile();

        if (!$result) {
            $result = @unlink(_PS_DOWNLOAD_DIR_ . $this->filename);

            if ($result) {
                $this->filename = '';
                $this->display_filename = '';
                $this->update();
            }

        }

        return $result;
    }

    /**
     * Check if file exists
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function checkFile() {

        if (!$this->filename) {
            return false;
        }

        return file_exists(_PS_DOWNLOAD_DIR_ . $this->filename);
    }

    /**
     * Return html link
     *
     * @param bool|string $class CSS selector
     * @param bool        $admin specific to backend
     * @param bool|bool   $hash  hash code in table order detail
     *
     * @return string Html all the code for print a link to the file
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getHtmlLink($class = false, $admin = true, $hash = false) {

        $link = $this->getTextLink($admin, $hash);
        $html = '<a href="' . $link . '" title=""';

        if ($class) {
            $html .= ' class="' . $class . '"';
        }

        $html .= '>' . $this->display_filename . '</a>';

        return $html;
    }

    /**
     * Return html link
     *
     * @param bool        $admin specific to backend (optional)
     * @param bool|string $hash  hash code in table order detail (optional)
     *
     * @return string Html all the code for print a link to the file
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getTextLink($admin = true, $hash = false) {

        $key = $this->filename . '-' . ($hash ? $hash : 'orderdetail');
        $link = ($admin) ? 'get-file-admin.php?' : _PS_BASE_URL_ . __PS_BASE_URI__ . 'index.php?controller=get-file&';
        $link .= ($admin) ? 'file=' . $this->filename : 'key=' . $key;

        return $link;
    }

    /**
     * Return a deadline
     *
     * @return string Datetime in SQL format
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getDeadline() {

        if (!(int) $this->nb_days_accessible) {
            return '0000-00-00 00:00:00';
        }

        $timestamp = strtotime('+' . (int) $this->nb_days_accessible . ' day');

        return date('Y-m-d H:i:s', $timestamp);
    }

    /**
     * Return a hash for control download access
     *
     * @return string Hash ready to insert in database
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getHash() {

        // TODO check if this hash not already in database
        return sha1(microtime() . $this->id);
    }

}
