<?php

/**
 * Class StudentThreadCore
 *
 * @since 1.9.1.0
 */
class StudentThreadCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int $id_contact */
    public $id_contact;
    /** @var int $id_student */
    public $id_student;
    /** @var int $id_order */
    public $id_student_education;
    /** @var int $id_product */
    public $id_education;
    /** @var bool $status */
    public $status;

    public $name;
    /** @var string $email */
    public $email;
    /** @var string $email */
    public $phone;

    public $object;
    /** @var string $token */
    public $token;
    /** @var string $date_add */
    public $date_add;
    /** @var string $date_upd */
    public $date_upd;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'student_thread',
        'primary' => 'id_student_thread',
        'fields'  => [
            'id_lang'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_contact'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_shop'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_student'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_student_education' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_education'         => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'name'                 => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'size' => 128],
            'email'                => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 254],
            'phone'                => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 32],
            'object'               => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
            'token'                => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true],
            'status'               => ['type' => self::TYPE_STRING],
            'date_add'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'             => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * @param int      $idStudent
     * @param int|null $read
     * @param int|null $idOrder
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getStudentMessages($idStudent, $read = null, $idStudentEducation = null) {

        $sql = (new DbQuery())
            ->select('*')
            ->from('student_thread', 'ct')
            ->leftJoin('student_message', 'cm', 'ct.`id_student_thread` = cm.`id_student_thread`')
            ->where('`id_student` = ' . (int) $idStudent);

        if ($read !== null) {
            $sql->where('cm.`read` = ' . (int) $read);
        }

        if ($idOrder !== null) {
            $sql->where('ct.`id_student_educationr` = ' . (int) $idStudentEducation);
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    public static function getIdStudentThreadByEmailAndIdOrder($email, $idStudentEducation) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('cm.`id_student_thread`')
                ->from('student_thread', 'cm')
                ->where('cm.`email` = \'' . pSQL($email) . '\'')
                ->where('cm.`id_shop` = ' . (int) Context::getContext()->shop->id)
                ->where('cm.`id_student_education` = ' . (int) $idStudentEducation)
        );
    }

    public static function getContacts() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('cl.*, COUNT(*) as `total`')
                ->select('(SELECT `id_student_thread` FROM `' . _DB_PREFIX_ . 'student_thread` ct2 WHERE status = "open" AND ct.`id_contact` = ct2.`id_contact`  ORDER BY `date_upd` ASC LIMIT 1) AS `id_student_thread`')
                ->from('student_thread', 'ct')
                ->leftJoin('contact_lang', 'cl', 'cl.`id_contact` = ct.`id_contact` AND cl.`id_lang` = ' . (int) Context::getContext()->language->id)
                ->where('ct.`status` = "open"')
                ->where('ct.`id_contact` IS NOT NULL')
                ->where('cl.`id_contact` IS NOT NULL ' . Shop::addSqlRestriction())
                ->groupBy('ct.`id_contact`')
                ->having('COUNT(*) > 0')
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
     */
    public static function getTotalStudentThreads($where = null) {

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('COUNT(*)')
                ->from('student_thread')
                ->where($where ?: '1')
        );
    }

    /**
     * @param int $idStudentThread
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getMessageStudentThreads($idStudentThread) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('ct.*, cm.*, cl.name subject, CONCAT(e.firstname, \' \', e.lastname) employee_name')
                ->select('CONCAT(c.firstname, \' \', c.lastname) student_name, c.firstname')
                ->from('student_thread', 'ct')
                ->leftJoin('student_message', 'cm', 'ct.`id_student_thread` = cm.`id_student_thread`')
                ->leftJoin('contact_lang', 'cl', 'cl.`id_contact` = ct.`id_contact` AND cl.`id_lang` = ' . (int) Context::getContext()->language->id)
                ->leftJoin('employee', 'e', 'e.`id_employee` = cm.`id_employee`')
                ->leftJoin('student', 'c', '(IFNULL(ct.`id_student`, ct.`email`) = IFNULL(c.`id_student`, c.`email`))')
                ->where('ct.`id_student_thread` = ' . (int) $idStudentThread)
                ->orderBy('cm.`date_add` ASC')
        );
    }

    /**
     * @param int $idStudentThread
     *
     * @return false|null|string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getNextThread($idStudentThread) {

        $context = Context::getContext();

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_student_thread`')
                ->from('student_thread', 'ct')
                ->where('ct.status = "open"')
                ->where('ct.`date_upd` = (SELECT date_add FROM ' . _DB_PREFIX_ . 'student_message WHERE (id_employee IS NULL OR id_employee = 0) AND id_student_thread = ' . (int) $idStudentThread . ' ORDER BY date_add DESC LIMIT 1)')
                ->where($context->cookie->{'student_threadFilter_cl!id_contact'}
                    ? 'ct.`id_contact` = ' . (int) $context->cookie->{'student_threadFilter_cl!id_contact'}
                    : '')
                ->where($context->cookie->{'student_threadFilter_l!id_lang'}
                    ? 'ct.`id_lang` = ' . (int) $context->cookie->{'student_threadFilter_l!id_lang'}
                    : '')
                ->orderBy('ct.`date_upd` ASC')
        );
    }

    /**
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getWsStudentMessages() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_student_message` AS `id`')
                ->from('student_message')
                ->where('`id_student_thread` = ' . (int) $this->id)
        );
    }

    /**
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function delete() {

        if (!Validate::isUnsignedId($this->id)) {
            return false;
        }

        $return = true;
        $result = Db::getInstance()->executeS(
            (new DbQuery())
                ->select('`id_student_message`')
                ->from('student_message')
                ->where('`id_student_thread` = ' . (int) $this->id)
        );

        if (count($result)) {

            foreach ($result as $res) {
                $message = new StudentMessage((int) $res['id_student_message']);

                if (!Validate::isLoadedObject($message)) {
                    $return = false;
                } else {
                    $return &= $message->delete();
                }

            }

        }

        $return &= parent::delete();

        return $return;
    }

}
