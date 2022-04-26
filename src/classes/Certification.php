<?php

/**
 * @since 1.9.1.0
 */
class CertificationCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'certification',
        'primary'   => 'id_certification',
        'fields'    => [
            'name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
			'certification_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
            'description' => ['type' => self::TYPE_HTML, 'validate' => 'isCleanHtml', 'required' => true],
            'certification_url' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
            'technique_name' => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 64],
            'image' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'active' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
        ],
    ];
    public $name;
    public $certification_name;
    public $description;
    public $certification_url;
    public $technique_name;
    public $image;
	public $active;

    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, $idLang, $idShop);

    }
	
	public static function getCertifications() {

		$certifications = [];
		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('certification')
				->orderBy('`id_certification` ASC')
		);
		$certifications[0] =[ 'name' => 'Sans Certification'];
		foreach ($result as $row) {
			$certifications[$row['id_certification']] = $row;
		}

		
		return $certifications;
	}
	
	public static function getCerificationCollection() {

       return new PhenyxShopCollection('Certification');
    }

}
