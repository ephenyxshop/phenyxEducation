<?php

/**
 * Class StudentEducationPrerequis
 *
 * @since 2.1.0.0
 */
class StudentEducationPrerequisCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_education_prerequis',
		'primary' => 'id_student_education_prerequis',
		'fields'  => [
			'id_student_education'   => ['type' => self::TYPE_INT, 'required' => true],
			'id_education_prerequis' => ['type' => self::TYPE_INT, 'required' => true],
			'content'                => ['type' => self::TYPE_STRING, 'required' => true],
			'score'                  => ['type' => self::TYPE_INT, 'required' => true],
			'date_add'               => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],

		],
	];

	public $id_student_education;
	public $id_education_prerequis;
	public $content;
	public $score;
	public $date_add;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null, $idLang = null) {

		parent::__construct($id, $idLang);
		$this->content = unserialize($this->content);

	}

	public function add($autoDate = false, $nullValues = false) {

		$success = parent::add($autoDate, $nullValues);

		return $success;
	}

}
