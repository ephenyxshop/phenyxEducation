<?php

/**
 * @since 1.9.1.0
 */
class StudentEvaluationCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_evaluation',
		'primary' => 'id_student_evaluation',
		'fields'  => [
			'id_student'           => ['type' => self::TYPE_INT, 'required' => true],
			'id_customer'           => ['type' => self::TYPE_INT],
			'id_student_education' => ['type' => self::TYPE_INT, 'required' => true],
			'score'                => ['type' => self::TYPE_INT],
			'content'              => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'type'                 => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 20],
			'answered'             => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'copy_post' => false],
			'date_add'             => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'             => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];
	public $id_student;
	public $id_customer;
	public $id_student_education;
	public $score;
	public $content;
	public $type;
	public $date_add;
	public $date_upd;

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

	public static function getUnansweredEvaluation($idStudent) {

		$link = false;
		$evaluation = Db::getInstance()->getRow(
			(new DbQuery())
				->select('id_student_evaluation, id_student_education')
				->from('student_evaluation')
				->where('`id_customer` = ' . $idStudent)
				->where('`answered` = 0')
				->orderBy('`id_student_evaluation` DESC')
		);

		if (isset($evaluation['id_student_evaluation']) && $evaluation['id_student_evaluation'] > 0) {
			$link = Context::getContext()->link->getPageLink('evaluation', true, Context::getContext()->language->id, ['idStudentEducation' => $idStudentEducation, 'idEvaluation' => $idEvaluation], false, 1);
		}

		return $link;

	}

	public static function getEvaluationHotByIdSession($idSession) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_student_evaluation`')
				->from('student_evaluation')
				->where('`id_student_education` =' . $idSession)
				->where('`type` LIKE "hot"')
		);
	}

	public static function getHotScoring() {

		$score = Db::getInstance()->getRow(
			(new DbQuery())
				->select('COUNT(`id_student_evaluation`) as total, SUM(`score`) as totalScore')
				->from('student_evaluation')
				->where('`type` LIKE "hot"')
				->where('`answered` = 1')
		);

		if ($score['total'] > 0) {
			$average = $score['totalScore'] / $score['total'];

			return round($average * 20 / 24, 2);
		}

		return null;
	}

}
