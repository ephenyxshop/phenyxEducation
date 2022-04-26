<?php

/**
 * @since 1.9.1.0
 */
class ServiceCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'service',
        'primary'   => 'id_service',
        'fields'    => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'tag_css'=> ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
        ],
    ];
    public $name;
    public $description;
    public $image;
	public $tag_css;
	public $active;

    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, $idLang, $idShop);

    }
	
	public static function getServices() {

		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('service')
				->orderBy('`id_service` ASC')
		);
		
	}
	
	public static function getServiceCollection() {

       return new PhenyxShopCollection('Service');
    }

}
