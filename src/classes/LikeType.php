<?php

class LikeTypeCore extends ObjectModel {

    public $id;

    public $like_type;

    public $name;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'activity_like_type',
        'primary'   => 'id_activity_like_type',
        'multilang' => true,
        'fields'    => [
            'like_type' => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],

            'name'      => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true, 'size' => 32],
        ],
    ];

    public function __construct($id = null, $id_lang = null) {

        parent::__construct($id, $id_lang);
        $this->image_dir = _PS_LIKE_IMG_DIR_;

    }

    public function add($autodate = true, $null_values = true) {

        $success = parent::add($autodate, $null_values);
        return $success;
    }

    public function update($nullValues = false) {

        return parent::update(true);
    }

    public static function getLikeTypess($id_lang = null) {

        return Db::getInstance()->executeS('SELECT `id_activity_like_type`, `name` FROM `' . _DB_PREFIX_ . 'activity_like_type_lang` WHERE id_lang = ' . $id_lang);
    }

    public function getImage() {

        if (!isset($this->id) || empty($this->id) || !file_exists(_PS_LIKE_IMG_DIR_ . $this->id . '.gif')) {
            return _PS_THEME_LIKE_DIR_ . 'Unknown.gif';
        }

        return _PS_THEME_LIKE_DIR_ . $this->id . '.gif';
    }

}
