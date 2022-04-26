<?php

/**
 * Class StudentMessageCore
 *
 * @since 1.9.1.0
 */
class StudentMessageCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int $id_student_thread */
    public $id_student_thread;
    /** @var int $id_employee */
    public $id_employee;
    /** @var string $message */
    public $message;
    /** @var string $file_name */
    public $file_name;
    /** @var string $ip_address */
    public $ip_address;
    /** @var string $user_agent */
    public $user_agent;
    /** @var int $private */
    public $private;
    /** @var string $date_add */
    public $date_add;
    /** @var string $date_upd*/
    public $date_upd;
    /** @var bool $read */
    public $read;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'student_message',
        'primary' => 'id_student_message',
        'fields'  => [
            'id_employee'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_student_thread' => ['type' => self::TYPE_INT],
            'ip_address'        => ['type' => self::TYPE_STRING, 'validate' => 'isIp2Long', 'size' => 15],
            'message'           => ['type' => self::TYPE_STRING, 'validate' => 'isCleanHtml', 'required' => true, 'size' => 16777216],
            'file_name'         => ['type' => self::TYPE_STRING],
            'user_agent'        => ['type' => self::TYPE_STRING],
            'private'           => ['type' => self::TYPE_INT],
            'date_add'          => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'          => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'read'              => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
        ],
    ];

    protected $webserviceParameters = [
        'fields' => [
            'id_employee'       => [
                'xlink_resource' => 'employees',
            ],
            'id_student_thread' => [
                'xlink_resource' => 'student_threads',
            ],
        ],
    ];

    /**
     * @param int  $idOrder
     * @param bool $private
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getMessagesByOrderId($idOrder, $private = true) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('cm.*')
                ->select('c.`firstname` AS `cfirstname`')
                ->select('c.`lastname` AS `clastname`')
                ->select('e.`firstname` AS `efirstname`')
                ->select('e.`lastname` AS `elastname`')
                ->select('(COUNT(cm.id_student_message) = 0 AND ct.id_student != 0) AS is_new_for_me')
                ->from('student_message', 'cm')
                ->leftJoin('student_thread', 'ct', 'ct.`id_student_thread` = cm.`id_student_thread`')
                ->leftJoin('student', 'c', 'ct.`id_student` = c.`id_student`')
                ->leftOuterJoin('employee', 'e', 'e.`id_employee` = cm.`id_employee`')
                ->where('ct.`id_order` = ' . (int) $idOrder)
                ->where($private ? 'cm.`private` = 0' : '')
                ->groupBy('cm.`id_student_message`')
                ->orderBy('cm.`date_add` DESC')
        );
    }

    /**
     * @param string|null $where
     *
     * @return int
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    public static function getTotalStudentMessages($where = null) {

        if (is_null($where)) {
            return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('student_message')
                    ->leftJoin('student_thread', 'ct', 'cm.`id_student_thread` = ct.`id_student_thread`')
                    ->where('1 ' . Shop::addSqlRestriction())
            );
        } else {
            return (int) Db::getInstance()->getValue(
                (new DbQuery())
                    ->select('COUNT(*)')
                    ->from('student_message', 'cm')
                    ->leftJoin('student_thread', 'ct', 'cm.`id_student_thread` = ct.`id_student_thread`')
                    ->where($where . Shop::addSqlRestriction())
            );
        }

    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function delete() {

        if (!empty($this->file_name)) {
            @unlink(_PS_UPLOAD_DIR_ . $this->file_name);
        }

        return parent::delete();
    }

}
