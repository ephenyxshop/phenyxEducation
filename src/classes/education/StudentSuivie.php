<?php

/**
 * Class StudentEducationSuivieCore
 *
 * @since 2.1.0.0
 */
class StudentSuivieCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_suivie',
		'primary' => 'id_student_suivie',
		'fields'  => [
			'id_customer'                => ['type' => self::TYPE_INT, 'required' => true],
			'id_sale_agent'              => ['type' => self::TYPE_INT],
			'id_employee'                => ['type' => self::TYPE_INT, 'required' => true],
			'content'                    => ['type' => self::TYPE_STRING, 'required' => true],
			'date_add'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	
	public $id_customer;
	public $id_sale_agent = 0;
	public $id_employee;
	public $content;
	public $date_add;
	public $date_upd;

	/**
	 * StudentSuivieCore constructor.
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
	
	public static function getSuivieById($idCustomer) {

		
		$suivies = Db::getInstance()->executeS(
			(new DbQuery())
			->select('ss.*, case when ss.id_employee > 0 then e.firstname else sa.firstname end as firstname, case when ss.id_employee > 0 then e.lastname else sa.lastname end as lastname')
			->from('student_suivie', 'ss')
			->leftJoin('customer', 's', 's.id_customer = ss.id_customer')
			->leftJoin('employee', 'e', 'e.id_employee = ss.id_employee')
			->leftJoin('sale_agent', 'sa', 'sa.id_sale_agent = ss.id_sale_agent')
			->where(' ss.`id_customer` = ' . $idCustomer)
			->orderBy('ss.`date_add` ASC ')
		);

		return $suivies;

	}

	

}
