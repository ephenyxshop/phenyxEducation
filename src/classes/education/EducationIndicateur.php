<?php

/**
 * Class EducationIndicateur
 *
 * @since 2.1.0.0
 */
class EducationIndicateurCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_indicateur',
		'primary' => 'id_education_indicateur',
		'fields'  => [
			'id_education'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_education_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_education_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'name'                    => ['type' => self::TYPE_STRING, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128],
			'qty'                => ['type' => self::TYPE_INT],
			'score'                => ['type' => self::TYPE_FLOAT],
			'date_upd'             => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	public $id_education;
	public $id_education_attribute = 0;
	public $name;
	public $qty;
	public $score;
	public $date_up;
	
	public static function getIdByeducation($id_education, $id_education_attribute) {
		
		return Db::getInstance()->getValue(
  			(new DbQuery())
			->select('`id_education_indicateur`')
			->from('education_indicateur')
    		->where('`id_education` = '.$id_education)
			->where('`id_education_attribute` = '.$id_education_attribute)
		);
	}

}
