<?php

class StudentEducationHistoryCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int Order id */
    public $id_order;
    /** @var int Order status id */
    public $id_order_state;
    /** @var int Employee id for this history entry */
    public $id_employee;
    /** @var string Object creation date */
    public $date_add;
    /** @var string Object last modification date */
    public $date_upd;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'student_education_history',
        'primary' => 'id_student_education_history',
        'fields'  => [
            'id_student_education'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_student_education_state' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_employee'                => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'date_add'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

}
