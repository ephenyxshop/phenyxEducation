<?php

/**
 * Class EducationLink
 *
 * @since 2.1.0.0
 */
class EducationLinkCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_link',
		'primary' => 'id_education_link',
		'fields'  => [
			'id_education'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_education_attribute' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'edof_link'              => ['type' => self::TYPE_STRING,  'size' => 256],
		],
	];

	public $id_education;
	public $id_education_attribute = 0;
	public $edof_link;

}
