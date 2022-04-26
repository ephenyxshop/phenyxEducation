<?php

/**
 * Class SupplierCore
 *
 * @since 1.9.1.0
 */
class EducationSupplierCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * Return name from id
     *
     * @param int $id_supplier Supplier ID
     *
     * @return string name
     */
    protected static $cache_name = [];

    public $id_supplier;
    public $name;
    public $description;
    public $date_add;
    public $date_upd;
    public $address1;
    public $address2;
    public $postcode;
    public $city;
    public $id_country = 8;
    public $active;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'education_supplier',
        'primary' => 'id_education_supplier',
        'fields'  => [
            'name'       => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'required' => true, 'size' => 64],
            'active'     => ['type' => self::TYPE_BOOL],
            'address1'   => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
            'address2'   => ['type' => self::TYPE_STRING, 'validate' => 'isAddress', 'size' => 128],
            'postcode'   => ['type' => self::TYPE_STRING, 'validate' => 'isPostCode', 'size' => 12],
            'city'       => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'size' => 64],
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'date_add'   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

        ],
    ];

    /**
     * SupplierCore constructor.
     *
     * @param null $id
     * @param null $idLang
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);

        $this->link_rewrite = $this->getLink();
        $this->image_dir = _PS_SUPP_IMG_DIR_;
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getLink() {

        return Tools::link_rewrite($this->name);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool Indicates whether adding succeeded
     */
    public function add($autoDate = true, $nullValues = true) {

        if (parent::add($autoDate, $nullValues)) {
            return true;
        }

        return false;
    }

    /**
     * @param null $nullValues
     *
     * @return bool Indicates whether updating succeeded
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function update($nullValues = null) {

        if (PageCache::isEnabled()) {
            PageCache::invalidateEntity('supplier', $this->id);
        }

        return parent::update($nullValues);
    }

    /**
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function delete() {

        if (PageCache::isEnabled()) {
            PageCache::invalidateEntity('supplier', $this->id);
        }

        return parent::delete();
    }

    /**
     * @param int $idSupplier
     *
     * @return mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getNameById($idSupplier) {

        // @codingStandardsIgnoreStart

        if (!isset(static::$cache_name[$idSupplier])) {
            static::$cache_name[$idSupplier] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('`name`')
                    ->from('education_supplier')
                    ->where('`id_education_supplier` = ' . (int) $idSupplier)
            );
        }

        return static::$cache_name[$idSupplier];
        // @codingStandardsIgnoreEnd
    }

    /**
     * @param string $name
     *
     * @return bool|int
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getIdByName($name) {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('`id_education_supplier`')
                ->from('education_supplier')
                ->where('`name` = \'' . pSQL($name) . '\'')
        );

        if (isset($result['id_education_supplier'])) {
            return (int) $result['id_education_supplier'];
        }

        return false;
    }

    /**
     * @param int $idSupplier
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function supplierExists($idSupplier) {

        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('id_education_supplier')
                ->from('education_supplier')
                ->where('id_education_supplier = ' . (int) $idSupplier)
        );

        return ($res > 0);
    }

    public static function getSupplier() {

        $supplier = [];
        $res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('id_education_supplier, name')
                ->from('education_supplier')
                ->orderBy('id_education_supplier ASC ')
        );

        foreach ($res as $sup) {
            $supplier[$sup['id_education_supplier']] = $sup['name'];
        }

        return $supplier;
    }

}
