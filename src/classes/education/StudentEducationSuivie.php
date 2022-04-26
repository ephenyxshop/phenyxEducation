<?php

/**
 * Class StudentEducationSuivieCore
 *
 * @since 2.1.0.0
 */
class StudentEducationSuivieCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_education_suivie',
		'primary' => 'id_student_education_suivie',
		'fields'  => [
			'id_parent'                  => ['type' => self::TYPE_INT],
			'id_sale_agent'              => ['type' => self::TYPE_INT],
			'id_student_education'       => ['type' => self::TYPE_INT],
			'id_student_education_state' => ['type' => self::TYPE_INT],
			'type'                       => ['type' => self::TYPE_STRING],
			'id_employee'                => ['type' => self::TYPE_INT],
			'suivie_date'                => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'email_title'                => ['type' => self::TYPE_STRING],
			'email_content'              => ['type' => self::TYPE_HTML],
			'pdf_content'               => ['type' => self::TYPE_STRING],
			'content'                    => ['type' => self::TYPE_STRING],
			'sms_title'                  => ['type' => self::TYPE_STRING],
			'sms_content'                => ['type' => self::TYPE_STRING],
			'is_trouble'                 => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'is_solve'                   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'date_add'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	public $id_parent;
	public $id_sale_agent = 0;
	public $id_student_education_suivie;
	public $id_student_education;
	public $id_student_education_state;
	public $type;
	public $id_employee;
	public $suivie_date;
	public $email_title;
	public $email_content;
	public $pdf_content;
	public $content;
	public $sms_title;
	public $sms_content;
	public $is_trouble;
	public $is_solve;
	public $date_add;
	public $date_upd;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null) {

		parent::__construct($id);

	}

	public function add($autoDate = true, $nullValues = true) {

		$success = parent::add($autoDate, $nullValues);

		return $success;
	}

	public static function getEducationSuivieById($idSession) {

		$suivies = Db::getInstance()->executeS(
			(new DbQuery())
				->select('ses.*, state.template, e.firstname, e.lastname, sa.firstname as agent_firstname, sa.lastname as agent_lastname')
				->from('student_education_suivie', 'ses')
				->leftJoin('student_education_state_lang', 'state', 'state.id_student_education_state = ses.id_student_education_state')
				->leftJoin('employee', 'e', 'e.id_employee = ses.id_employee')
			->leftJoin('sale_agent', 'sa', 'sa.id_sale_agent = ses.id_sale_agent')
				->where('ses.`id_student_education` = ' . $idSession)
				->orderBy('ses.`suivie_date` ASC ')
		);

		return $suivies;

	}

}
