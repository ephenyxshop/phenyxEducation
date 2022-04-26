<?php

/**
 * Class EducationModuleCore
 *
 * @since 1.9.1.0
 */
class EducationModuleCore extends ObjectModel {

    public static $definition = [
        'table'     => 'education_module',
        'primary'   => 'id_education_module',
        'fields'    => [
            'id_education'           	=> ['type' => self::TYPE_INT,  'validate' => 'isUnsignedId', 'required' => true],
			'id_education_attribute'    => ['type' => self::TYPE_INT,  'validate' => 'isUnsignedId'],
            'id_module' 				=> ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'libelle'              		=> ['type' => self::TYPE_STRING, 'size' => 128],       
			'duree'              		=> ['type' => self::TYPE_FLOAT,  'validate' => 'isFloat'],

        ],
    ];
    /** @var int $id_education */
    public $id_education;

    public $id_education_attribute;
    /** @var string $reference */
    public $id_module;

    /** @var float $price */
    public $libelle;

    public $duree;
    /** @var string $available_date */

    public function __construct($id = null, $idLang = null) {
		
        parent::__construct($id, $idLang);

    }
	
	public static function getModulesbyEducationId($idEducation, $idEducationAttribbute = 0) {
		
		$modules = [];
		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
			->select('id_module')
			->from('education_module')
			->where('`id_education` = ' . $idEducation)
			->where('`id_education_attribute` = ' . $idEducationAttribbute)
		);
		
		foreach($results as $result) {
			$modules[] = $result['id_module'];
		}
		
		return '['.implode(",", $modules).']'.'<br>';
	}

    
}
