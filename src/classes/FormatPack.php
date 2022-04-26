<?php

/**
 * @since 1.9.1.0
 */
class FormatPackCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'formatpack',
        'primary'   => 'id_formatpack',
        'multilang' => true,
        'fields'    => [
            'id_tax_rules_group'        => ['type' => self::TYPE_INT, 'shop' => true, 'validate' => 'isUnsignedId'],
			'price'                     => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'required' => true],
            'wholesale_price'           => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
			'reference'                 => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'active'                    => ['type' => self::TYPE_BOOL, 'shop' => true, 'validate' => 'isBool'],
			'date_add'                  => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
            'date_upd'                  => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
            /* Lang fields */
            'name'                      => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128],
            'description'               => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
        ],
    ];
    public $id_tax_rules_group;
    public $price;
    public $wholesale_price;	
	public $reference;
	public $active;
	public $date_add;
	public $date_upd;
	
	public $name;
	public $description;

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

    /**
     * @param null $idLang
     *
     * @return PhenyxShopCollection
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getFormatPack($idLang = null) {

        if (is_null($idLang)) {
            $idLang = Context::getContext()->language->id;
        }

        $genders = new PhenyxShopCollection('FormatPack', $idLang);

        return $genders;
    }

   

}
