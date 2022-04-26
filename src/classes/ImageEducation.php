<?php

/**
 * Class ImageCore
 *
 * @since 1.9.1.0
 */
class ImageEducationCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int access rights of created folders (octal) */
    protected static $access_rights = 0775;
    /** @var array $_cacheGetSize */
    protected static $_cacheGetSize = [];
    /** @var int Image ID */
    public $id_image_education;
	
	public $reference;
    /** @var int Education ID */
    public $id_education;
    /** @var int Position used to order images of the same education */
    public $position;
    /** @var bool Image is cover */
    public $cover;
    /** @var string Legend */
    public $legend;
    /** @var string image extension */
    public $image_format = 'jpg';
    /** @var string path to index.php file to be copied to new image folders */
    public $source_index;
    /** @var string image folder */
    protected $folder;
    /** @var string image path without extension */
    protected $existing_path;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'image_education',
        'primary'   => 'id_image_education',
        'multilang' => true,
        'fields'    => [
			'reference'	 	=> ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
            'id_education' => ['type' => self::TYPE_INT, 'shop' => 'both', 'validate' => 'isUnsignedId', 'required' => true],
            'position'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt'],
            'cover'        => ['type' => self::TYPE_BOOL, 'allow_null' => true, 'validate' => 'isBool', 'shop' => true],

            'legend'       => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128],
        ],
    ];

    /**
     * ImageCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);
        $this->image_dir = _PS_EDUC_IMG_DIR_;
        $this->source_index = _PS_EDUC_IMG_DIR_ . 'index.php';
    }

    /**
     * Return first image (by position) associated with a education attribute
     *
     * @param int $idShop             Shop ID
     * @param int $idLang             Language ID
     * @param int $idEducation          Education ID
     * @param int $idEducationAttribute Education Attribute ID
     *
     * @return array
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getBestImageAttribute($idLang, $idEducation, $idEducationAttribute) {

        $cacheId = 'ImageEducation::getBestImageAttribute' . '-' . (int) $idEducation . '-' . (int) $idEducationAttribute . '-' . (int) $idLang;

        if (!Cache::isStored($cacheId)) {
            $row = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                (new DbQuery())
                    ->select('i.`id_image_education`, il.`legend`')
                    ->from('image_education', 'i')
                    ->innerJoin('education_attribute_image', 'pai', 'pai.`id_image_education` = i.`id_image_education` AND pai.`id_education_attribute` = ' . (int) $idEducationAttribute)
                    ->leftJoin('image_lang', 'il', 'i.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $idLang)
                    ->where('i.`id_education` = ' . (int) $idEducation)
                    ->orderBy('i.`position` ASC')
            );

            Cache::store($cacheId, $row);
        } else {
            $row = Cache::retrieve($cacheId);
        }

        return $row;
    }

    /**
     * Return available images for a education
     *
     * @param int $idLang             Language ID
     * @param int $idEducation          Education ID
     * @param int $idEducationAttribute Education Attribute ID
     *
     * @return array Images
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getImages($idLang, $idEducation, $idEducationtAttribute = null) {

        $attributeFilter = ($idEducationtAttribute ? ' AND ai.`id_education_attribute` = ' . (int) $idEducationtAttribute : '');
        $sql = 'SELECT *
            FROM `' . _DB_PREFIX_ . 'image_education` i
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (i.`id_image_education` = il.`id_image_education`)';

        if ($idEducationtAttribute) {
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_image` ai ON (i.`id_image_education` = ai.`id_image_education`)';
        }

        $sql .= ' WHERE i.`id_education` = ' . (int) $idEducation . ' AND il.`id_lang` = ' . (int) $idLang . $attributeFilter . '
            ORDER BY i.`position` ASC';

        return Db::getInstance()->executeS($sql);
    }

    /**
     * Check if a education has an image available
     *
     * @param int $idLang             Language ID
     * @param int $idEducation          Education ID
     * @param int $idEducationAttribute Education Attribute ID
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function hasImages($idLang, $idEducation, $idEducationAttribute = null) {

        $attributeFilter = ($idEducationAttribute ? ' AND ai.`id_education_attribute` = ' . (int) $idEducationAttribute : '');
        $sql = 'SELECT 1
            FROM `' . _DB_PREFIX_ . 'image_education` i
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (i.`id_image_education` = il.`id_image_education`)';

        if ($idEducationAttribute) {
            $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_image` ai ON (i.`id_image_education` = ai.`id_image_education`)';
        }

        $sql .= ' WHERE i.`id_education` = ' . (int) $idEducation . ' AND il.`id_lang` = ' . (int) $idLang . $attributeFilter;

        return (bool) Db::getInstance()->getValue($sql);
    }

    /**
     * Return Images
     *
     * @return array Images
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getAllImages() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_image_education`, `id_education`')
                ->from('image_education')
                ->orderBy('`id_image_education` ASC')
        );
    }

    /**
     * Return number of images for a education
     *
     * @param int $idEducation Education ID
     *
     * @return int number of images
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getImagesTotal($idEducation) {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('COUNT(`id_image_education`) AS `total`')
                ->from('image_education')
                ->where('`id_education` = ' . (int) $idEducation)
        );

        return $result['total'];
    }

    /**
     * Delete education cover
     *
     * @param int $idEducation Education ID
     *
     * @return bool result
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function deleteCover($idEducation) {

        if (!Validate::isUnsignedId($idEducation)) {
            die(Tools::displayError());
        }

        if (file_exists(_PS_TMP_IMG_DIR_ . 'education_' . $idEducation . '.jpg')) {
            unlink(_PS_TMP_IMG_DIR_ . 'education_' . $idEducation . '.jpg');
        }

        return (Db::getInstance()->update(
            'image_education',
            [
                'cover' => ['type' => 'sql', 'value' => 'NULL'],
            ],
            '`id_education` = ' . (int) $idEducation,
            0,
            true
        ) &&
            Db::getInstance()->update(
                'image_education',
                [
                    'cover' => ['type' => 'sql', 'value' => 'NULL'],
                ],
                ' `id_education` = ' . (int) $idEducation
            ));
    }

    /**
     *Get education cover
     *
     * @param int $idEducation Education ID
     *
     * @return bool result
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getCover($idEducation) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from('image_education')
                ->where('`id_education` = ' . (int) $idEducation)
                ->where('`cover` = 1')
        );
    }

    /**
     *Get global education cover
     *
     * @param int $idEducation Education ID
     *
     * @return bool result
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getGlobalCover($idEducation) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from('image_education', 'i')
                ->where('i.`id_education` = ' . (int) $idEducation)
                ->where('i.`cover` = 1')
        );
    }

    /**
     * Copy images from a education to another
     *
     * @param int   $idEducationOld Source education ID
     * @param bool  $idEducationNew Destination education ID
     * @param array $combinationImages
     *
     * @return bool
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function duplicateEducationImages($idEducationOld, $idEducationNew, $combinationImages) {

        $imageTypes = ImageType::getImagesTypes('educations');
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_image_education`')
                ->from('image_education')
                ->where('`id_education` = ' . (int) $idEducationOld)
        );

        foreach ($result as $row) {
            $imageOld = new ImageEducation($row['id_image_education']);
            $imageNew = clone $imageOld;
            unset($imageNew->id);
            $imageNew->id_education = (int) $idEducationNew;

            // A new id is generated for the cloned image when calling add()

            if ($imageNew->add()) {
                $newPath = $imageNew->getPathForCreation();

                foreach ($imageTypes as $imageType) {

                    if (file_exists(_PS_EDUC_IMG_DIR_ . $imageOld->getExistingImgPath() . '-' . $imageType['name'] . '.jpg')) {

                        if (!Configuration::get('PS_LEGACY_IMAGES')) {
                            $imageNew->createImgFolder();
                        }

                        copy(
                            _PS_EDUC_IMG_DIR_ . $imageOld->getExistingImgPath() . '-' . $imageType['name'] . '.jpg',
                            $newPath . '-' . $imageType['name'] . '.jpg'
                        );

                    }

                }

                if (file_exists(_PS_EDUC_IMG_DIR_ . $imageOld->getExistingImgPath() . '.jpg')) {
                    copy(_PS_EDUC_IMG_DIR_ . $imageOld->getExistingImgPath() . '.jpg', $newPath . '.jpg');
                }

                static::replaceAttributeImageAssociationId($combinationImages, (int) $imageOld->id, (int) $imageNew->id);

                // Duplicate shop associations for images
                $imageNew->duplicateShops($idEducationOld);
            } else {
                return false;
            }

        }

        return ImageEducation::duplicateAttributeImageAssociations($combinationImages);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function add($autoDate = true, $nullValues = false) {

        if ($this->position <= 0) {
            $this->position = ImageEducation::getHighestPosition($this->id_education) + 1;
        }
		if (empty($this->reference)) {
            $this->reference = $this->generateReference();
        }

        if ($this->cover) {
            $this->cover = 1;
        } else {
            $this->cover = null;
        }

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Return highest position of images for a education
     *
     * @param int $idEducation Education ID
     *
     * @return int highest position of images
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getHighestPosition($idEducation) {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('MAX(`position`) AS `max`')
                ->from('image_education')
                ->where('`id_education` = ' . (int) $idEducation)
        );

        return $result['max'];
    }

    /**
     * Returns the path where a education image should be created (without file format)
     *
     * @return string path
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getPathForCreation() {

        if (!$this->id) {
            return false;
        }

        if (Configuration::get('PS_LEGACY_IMAGES')) {

            if (!$this->id_education) {
                return false;
            }

            $path = $this->id_education . '-' . $this->id;
        } else {
            $path = $this->getImgPath();
            $this->createImgFolder();
        }

        return _PS_EDUC_IMG_DIR_ . $path;
    }

    /**
     * Create parent folders for the image in the new filesystem
     *
     * @return bool success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function createImgFolder() {

        if (!$this->id) {
            return false;
        }

        if (!file_exists(_PS_EDUC_IMG_DIR_ . $this->getImgFolder())) {
            // Apparently sometimes mkdir cannot set the rights, and sometimes chmod can't. Trying both.
            // @codingStandardsIgnoreStart
            $success = @mkdir(_PS_EDUC_IMG_DIR_ . $this->getImgFolder(), static::$access_rights, true);
            $chmod = @chmod(_PS_EDUC_IMG_DIR_ . $this->getImgFolder(), static::$access_rights);
            // @codingStandardsIgnoreEnd

            // Create an index.php file in the new folder

            if (($success || $chmod)
                && !file_exists(_PS_EDUC_IMG_DIR_ . $this->getImgFolder() . 'index.php')
                && file_exists($this->source_index)
            ) {
                return @copy($this->source_index, _PS_EDUC_IMG_DIR_ . $this->getImgFolder() . 'index.php');
            }

        }

        return true;
    }

    /**
     * @param $combinationImages
     * @param $savedId
     * @param $idImage
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected static function replaceAttributeImageAssociationId(&$combinationImages, $savedId, $idImage) {

        if (!isset($combinationImages['new']) || !is_array($combinationImages['new'])) {
            return;
        }

        foreach ($combinationImages['new'] as $idEducationAttribute => $imageIds) {

            foreach ($imageIds as $key => $imageId) {

                if ((int) $imageId == (int) $savedId) {
                    $combinationImages['new'][$idEducationAttribute][$key] = (int) $idImage;
                }

            }

        }

    }

    /**
     * Duplicate education attribute image associations
     *
     * @param array $combinationImages
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function duplicateAttributeImageAssociations($combinationImages) {

        if (!isset($combinationImages['new']) || !is_array($combinationImages['new'])) {
            return true;
        }

        $insert = [];

        foreach ($combinationImages['new'] as $idEducationAttribute => $imageIds) {

            foreach ($imageIds as $imageId) {
                $insert[] = [
                    'id_education_attribute' => (int) $idEducationAttribute,
                    'id_image_education'     => (int) $imageId,
                ];
            }

        }

        return DB::getInstance()->insert('education_attribute_image', $insert);
    }

    /**
     * @param array  $params
     * @param Smarty $smarty
     *
     * @return mixed
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getWidth($params, $smarty) {

        $result = static::getSize($params['type']);

        return $result['width'];
    }

    /**
     * @param mixed $type
     *
     * @return mixed
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getSize($type) {

        if (!isset(static::$_cacheGetSize[$type]) || static::$_cacheGetSize[$type] === null) {
            static::$_cacheGetSize[$type] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
                (new DbQuery())
                    ->select('`width`, `height`')
                    ->from('image_type')
                    ->where('`name` = \'' . pSQL($type) . '\'')
            );
        }

        return static::$_cacheGetSize[$type];
    }

    /**
     * @param array  $params
     * @param Smarty $smarty
     *
     * @return mixed
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getHeight($params, $smarty) {

        $result = static::getSize($params['type']);

        return $result['height'];
    }

    /**
     * Clear all images in tmp dir
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function clearTmpDir() {

        foreach (scandir(_PS_TMP_IMG_DIR_) as $d) {

            if (preg_match('/(.*)\.jpg$/', $d)) {
                unlink(_PS_TMP_IMG_DIR_ . $d);
            }

        }

    }

    /**
     * Recursively deletes all education images in the given folder tree and removes empty folders.
     *
     * @param string $path   folder containing the education images to delete
     * @param string $format image format
     *
     * @return bool success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function deleteAllImages($path, $format = 'jpg') {

        if (!$path || !$format || !is_dir($path)) {
            return false;
        }

        foreach (scandir($path) as $file) {

            if (preg_match('/^[0-9]+(\-(.*))?\.' . $format . '$/', $file)) {
                unlink($path . $file);
            } else if (is_dir($path . $file) && (preg_match('/^[0-9]$/', $file))) {
                ImageEducation::deleteAllImages($path . $file . '/', $format);
            }

        }

        // Can we remove the image folder?

        if (is_numeric(basename($path))) {
            $removeFolder = true;

            foreach (scandir($path) as $file) {

                if (($file != '.' && $file != '..' && $file != 'index.php')) {
                    $removeFolder = false;
                    break;
                }

            }

            if ($removeFolder) {
                // we're only removing index.php if it's a folder we want to delete

                if (file_exists($path . 'index.php')) {
                    @unlink($path . 'index.php');
                }

                @rmdir($path);
            }

        }

        return true;
    }

    /**
     * Move all legacy education image files from the image folder root to their subfolder in the new filesystem.
     * If max_execution_time is provided, stops before timeout and returns string "timeout".
     * If any image cannot be moved, stops and returns "false"
     *
     * @param int $maxExecutionTime
     *
     * @return mixed success or timeout
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function moveToNewFileSystem($maxExecutionTime = 0) {

        $startTime = time();
        $image = null;
        $tmpFolder = 'duplicates/';

        foreach (scandir(_PS_EDUC_IMG_DIR_) as $file) {
            // matches the base education image or the thumbnails

            if (preg_match('/^([0-9]+\-)([0-9]+)(\-(.*))?\.jpg$/', $file, $matches)) {
                // don't recreate an image object for each image type

                if (!$image || $image->id !== (int) $matches[2]) {
                    $image = new ImageEducation((int) $matches[2]);
                }

                // image exists in DB and with the correct education?

                if (Validate::isLoadedObject($image) && $image->id_education == (int) rtrim($matches[1], '-')) {
                    // create the new folder if it does not exist

                    if (!$image->createImgFolder()) {
                        return false;
                    }

                    // if there's already a file at the new image path, move it to a dump folder
                    // most likely the preexisting image is a demo image not linked to a education and it's ok to replace it
                    $newPath = _PS_EDUC_IMG_DIR_ . $image->getImgPath() . (isset($matches[3]) ? $matches[3] : '') . '.jpg';

                    if (file_exists($newPath)) {

                        if (!file_exists(_PS_EDUC_IMG_DIR_ . $tmpFolder)) {
                            // @codingStandardsIgnoreStart
                            @mkdir(_PS_EDUC_IMG_DIR_ . $tmpFolder, static::$access_rights);
                            @chmod(_PS_EDUC_IMG_DIR_ . $tmpFolder, static::$access_rights);
                        }

                        $tmp_path = _PS_EDUC_IMG_DIR_ . $tmpFolder . basename($file);

                        if (!@rename($newPath, $tmp_path) || !file_exists($tmp_path)) {
                            // @codingStandardsIgnoreEnd
                            return false;
                        }

                    }

                    // move the image

                    if (!@rename(_PS_EDUC_IMG_DIR_ . $file, $newPath) || !file_exists($newPath)) {
                        return false;
                    }

                }

            }

            if ((int) $maxExecutionTime != 0 && (time() - $startTime > (int) $maxExecutionTime - 4)) {
                return 'timeout';
            }

        }

        return true;
    }

    /**
     * Try to create and delete some folders to check if moving images to new file system will be possible
     *
     * @return bool success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function testFileSystem() {

        $folder1 = _PS_EDUC_IMG_DIR_ . 'testfilesystem/';
        $testFolder = $folder1 . 'testsubfolder/';
        // check if folders are already existing from previous failed test

        if (file_exists($testFolder)) {
            @rmdir($testFolder);
            @rmdir($folder1);
        }

        if (file_exists($testFolder)) {
            return false;
        }

        // @codingStandardsIgnoreStart
        @mkdir($testFolder, static::$access_rights, true);
        @chmod($testFolder, static::$access_rights);
        // @codingStandardsIgnoreEnd

        if (!is_writeable($testFolder)) {
            return false;
        }

        @rmdir($testFolder);
        @rmdir($folder1);

        if (file_exists($folder1)) {
            return false;
        }

        return true;
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function update($nullValues = false) {

        if ($this->cover) {
            $this->cover = 1;
        } else {
            $this->cover = null;
        }

        return parent::update($nullValues);
    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    public function delete() {

        if (!parent::delete()) {
            return false;
        }

        if ($this->hasMultishopEntries()) {
            return true;
        }

        if (!$this->deleteEducationAttributeImage() || !$this->deleteImage()) {
            return false;
        }

        // update positions
        Db::getInstance()->execute('SET @position:=0', false);
        Db::getInstance()->execute(
            'UPDATE `' . _DB_PREFIX_ . 'image_education` SET position=(@position:=@position+1)
                                    WHERE `id_education` = ' . (int) $this->id_education . ' ORDER BY position ASC'
        );

        return true;
    }

    /**
     * Delete Image - Education attribute associations for this image
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     */
    public function deleteEducationAttributeImage() {

        return Db::getInstance()->delete('education_attribute_image', '`id_image_education` = ' . (int) $this->id);
    }

    /**
     * Delete the education image from disk and remove the containing folder if empty
     * Handles both legacy and new image filesystems
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     * @param bool $forceDelete
     *
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function deleteImage($forceDelete = false) {

        if (!$this->id) {
            return false;
        }

        // Delete base image

        if (file_exists($this->image_dir . $this->getExistingImgPath() . '.' . $this->image_format)) {
            unlink($this->image_dir . $this->getExistingImgPath() . '.' . $this->image_format);
        } else {
            return false;
        }

        $filesToDelete = [];

        // Delete auto-generated images
        $imageTypes = ImageType::getImagesTypes();

        foreach ($imageTypes as $imageType) {
            $filesToDelete[] = $this->image_dir . $this->getExistingImgPath() . '-' . $imageType['name'] . '.' . $this->image_format;

        }

        // delete index.php
        $filesToDelete[] = $this->image_dir . $this->getImgFolder() . 'index.php';
        // Delete tmp images
        $filesToDelete[] = _PS_TMP_IMG_DIR_ . 'education_' . $this->id_education . '.' . $this->image_format;
        $filesToDelete[] = _PS_TMP_IMG_DIR_ . 'education_mini_' . $this->id_education . '.' . $this->image_format;

        foreach ($filesToDelete as $file) {

            if (file_exists($file) && !@unlink($file)) {
                return false;
            }

        }

        // Can we delete the image folder?

        if (is_dir($this->image_dir . $this->getImgFolder())) {
            $deleteFolder = true;

            foreach (scandir($this->image_dir . $this->getImgFolder()) as $file) {

                if (($file != '.' && $file != '..')) {
                    $deleteFolder = false;
                    break;
                }

            }

        }

        if (isset($deleteFolder) && $deleteFolder) {
            @rmdir($this->image_dir . $this->getImgFolder());
        }

        return true;
    }

    /**
     * Returns image path in the old or in the new filesystem
     *
     * @return string image path
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function getExistingImgPath() {

        if (!$this->id) {
            return false;
        }

        if (!$this->existing_path) {

            if (Configuration::get('PS_LEGACY_IMAGES') && file_exists(_PS_EDUC_IMG_DIR_ . $this->id_education . '-' . $this->id . '.' . $this->image_format)) {
                $this->existing_path = $this->id_education . '-' . $this->id;
            } else {
                $this->existing_path = $this->getImgPath();
            }

        }

        return $this->existing_path;
    }

    /**
     * Returns the path to the image without file extension
     *
     * @return string path
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getImgPath() {

        if (!$this->id) {
            return false;
        }

        $path = $this->getImgFolder() . $this->id;

        return $path;
    }

    /**
     * Returns the path to the folder containing the image in the new filesystem
     *
     * @return string path to folder
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getImgFolder() {

        if (!$this->id) {
            return false;
        }

        if (!$this->folder) {
            $this->folder = ImageEducation::getImgFolderStatic($this->id);
        }

        return $this->folder;
    }

    /**
     * Returns the path to the folder containing the image in the new filesystem
     *
     * @param mixed $idImage
     *
     * @return string path to folder
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getImgFolderStatic($idImage) {

        if (!is_numeric($idImage)) {
            return false;
        }

        $folders = str_split((string) $idImage);

        return implode('/', $folders) . '/';
    }

    /**
     * Reposition image
     *
     * @param int  $position  Position
     * @param bool $direction Direction
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @deprecated since version 1.0.0 use ImageEducation::updatePosition() instead
     */
    public function positionImage($position, $direction) {

        Tools::displayAsDeprecated();

        $position = (int) $position;
        $direction = (int) $direction;

        // temporary position
        $highPosition = ImageEducation::getHighestPosition($this->id_education) + 1;

        Db::getInstance()->update(
            'image',
            [
                'position' => (int) $highPosition,
            ],
            '`id_education` = ' . (int) $this->id_education . ' AND `position` = ' . ($direction ? $position - 1 : $position + 1)
        );

        Db::getInstance()->update(
            'image',
            [
                'position' => ['type' => 'sql', 'value' => '`position`' . ($direction ? '-1' : '+1')],
            ],
            '`id_image_education` = ' . (int) $this->id
        );

        Db::getInstance()->update(
            'image',
            [
                'position' => (int) $this->position,
            ],
            '`id_education` = ' . (int) $this->id_education . ' AND `position` = ' . (int) $highPosition
        );
    }

    /**
     * Change an image position and update relative positions
     *
     * @param int $way      position is moved up if 0, moved down if 1
     * @param int $position new position of the moved image
     *
     * @return int success
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function updatePosition($way, $position) {

        if (!isset($this->id) || !$position) {
            return false;
        }

        // < and > statements rather than BETWEEN operator
        // since BETWEEN is treated differently according to databases
        $result = Db::getInstance()->update(
            'image',
            [
                'position' => ['type' => 'sql', 'value' => '`position` ' . ($way ? '- 1' : '+ 1')],
            ],
            '`position` ' . ($way ? '> ' . (int) $this->position . ' AND `position` <= ' . (int) $position : '< ' . (int) $this->position . ' AND `position` >= ' . (int) $position) . ' AND `id_education`=' . (int) $this->id_education
        ) && Db::getInstance()->update(
            'image',
            [
                'position' => (int) $position,
            ],
            '`id_image_education` = ' . (int) $this->id_image_education
        );

        return $result;
    }

    public static function getEducationImages($idImage, $path) {

        $iterator = new AppendIterator();

        $iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/img/educ/' . $path));

        foreach ($iterator as $file) {

            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

            if ($ext == 'jpg') {
                $content = file_get_contents($file->getPathname());
                $base64String = 'data:image/' . $type . ';base64,' . base64_encode($content);
                $return[$file->getFilename()] = $base64String;
            }

        }

        return $return;
    }

    public static function getEducationImageOnDisk($idImage, $path) {

        $iterator = new AppendIterator();

        $iterator->append(new DirectoryIterator(_PS_ROOT_DIR_ . '/img/educ/' . $path));

        foreach ($iterator as $file) {

            $ext = pathinfo($file->getFilename(), PATHINFO_EXTENSION);

            if ($ext == 'jpg' || $ext == 'webp') {
                $return[$file->getFilename()] = $file->getPathname();
            }

        }

        return $return;
    }

    public static function upgradeImage(ImageEducation $imageObj, $key, Education $education, $savePath) {

        $imagesTypes = ImageType::getImagesTypes('education');

        if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder())) {

            $toDel = scandir(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder());

            foreach ($toDel as $d) {

                foreach ($imagesTypes as $imageType) {

                    if (preg_match('/^[0-9]+\-' . $imageType['name'] . '\.(jpg|webp)$/', $d) || (count($imagesTypes) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.(jpg|webp)$/', $d))) {

                        if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $d)) {
                            unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $d);
                        }

                    }

                }

            }

            if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.jpg')) {
                unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.jpg');
            }

            if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.webp')) {
                unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.webp');
            }

        }

        $imageObj->id_education = (int) ($education->id);
        $imageObj->position = 1;
        $imageObj->cover = 1;
        $imageObj->update();

        $newPath = $imageObj->getPathForCreation();
        $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

        foreach ($imagesTypes as $imageType) {

            ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '.' . $imageObj->image_format, $imageType['width'], $imageType['height'], $imageObj->image_format);

            if ($generateHighDpiImages) {
                ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $imageObj->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $imageObj->image_format);
            }

        }

        unlink($savePath);
        unset($savePath);
        $imageObj->update();
    }

    public static function upgradeDeclinaisonImage(ImageEducation $imageObj, $key, Education $education, $savePath) {

        $savePath = _PS_UPLOAD_DIR_ . $key . '.jpg';
        $imagesTypes = ImageType::getImagesTypes('education');

        if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder())) {
            $toDel = scandir(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder());

            foreach ($toDel as $d) {

                foreach ($imagesTypes as $imageType) {

                    if (preg_match('/^[0-9]+\-' . $imageType['name'] . '\.(jpg|webp)$/', $d) || (count($imagesTypes) > 1 && preg_match('/^[0-9]+\-[_a-zA-Z0-9-]*\.(jpg|webp)$/', $d))) {

                        if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $d)) {
                            unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $d);
                        }

                    }

                }

            }

            if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.jpg')) {
                unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.jpg');
            }

            if (file_exists(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.webp')) {
                unlink(_PS_EDUC_IMG_DIR_ . $imageObj->getImgFolder() . $key . '.webp');
            }

        }

        $newPath = $imageObj->getPathForCreation();
        $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

        foreach ($imagesTypes as $imageType) {

            ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '.' . $imageObj->image_format, $imageType['width'], $imageType['height'], $imageObj->image_format);

            if ($generateHighDpiImages) {
                ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $imageObj->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $imageObj->image_format);
            }

        }

        unlink($savePath);
        unset($savePath);
        $imageObj->update();
    }

    public static function saveNewImage(Education $education, $savePath, $value) {

        $image = new ImageEducation();
        $image->id_education = (int) ($education->id);
        $image->position = 0;
        $image->cover = $value['cover'];

        foreach (Language::getIDs(false) as $idLang) {
            $image->legend[(int) $idLang] = $education->name[(int) $idLang];
        }

        if ($image->add()) {

            $newPath = $image->getPathForCreation();
            $imagesTypes = ImageType::getImagesTypes('education');
            $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

            foreach ($imagesTypes as $imageType) {
                ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format);

                if ($generateHighDpiImages) {
                    ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format);
                }

            }

            unlink($savePath);
            unset($savePath);
        }

    }

    public static function saveNewDeclinaisonImage(Education $education, Combination $combination, $savePath, $value) {

        
        $image = new ImageEducation();
        $image->id_education = (int) ($education->id);
        $image->position = 0;
        $image->cover = $value['cover'];

        foreach (Language::getIDs(false) as $idLang) {
            $image->legend[(int) $idLang] = $combination->name[(int) $idLang];
        }

        if ($image->add()) {
           
            $imagesTypes = ImageType::getImagesTypes('education');
            $newPath = $image->getPathForCreation();
            $generateHighDpiImages = (bool) Configuration::get('PS_HIGHT_DPI');

            foreach ($imagesTypes as $imageType) {
                ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '.' . $image->image_format, $imageType['width'], $imageType['height'], $image->image_format);

                if ($generateHighDpiImages) {
                    ImageManager::resize($savePath, $newPath . '-' . stripslashes($imageType['name']) . '2x.' . $image->image_format, (int) $imageType['width'] * 2, (int) $imageType['height'] * 2, $image->image_format);
                }

            }

            $sql = 'INSERT INTO `' . _DB_PREFIX_ . 'education_attribute_image` (`id_education_attribute`, `id_image`) VALUES(' . (int) $combination->id . ', ' . (int) $image->id . ')';
           
            DB::getInstance()->execute($sql);
        }

    }
	
	public static function imageExist($idImageEducation) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('*')
                ->from('image_education')
                ->where('`id_image_education` = ' . $idImageEducation)
        );
	}

}
