<?php

class NotificationCore {

    public $types;

    public function __construct() {

        $this->types = ['student_message', 'customer', 'student_education'];
    }

    public function getLastElements() {

        $notifications = [];
        $employeeInfos = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('`id_last_student_education`, `id_last_student_message`, `id_last_customer`')
                ->from('employee')
                ->where('`id_employee` = ' . (int) Context::getContext()->cookie->id_employee)
        );

        foreach ($this->types as $type) {
            $notifications[$type] = Notification::getLastElementsIdsByType($type, $employeeInfos['id_last_' . $type]);
        }

        return $notifications;
    }

    public static function getLastElementsIdsByType($type, $idLastElement) {

        switch ($type) {
        case 'student_education':
            $sql = (new DbQuery())
                ->select('SQL_CALC_FOUND_ROWS o.`id_student_education`, o.`id_customer`, o.`price`')
                ->select('o.`date_upd`, c.`firstname`, c.`lastname`')
                ->from('student_education', 'o')
                ->leftJoin('customer', 'c', 'c.`id_customer` = o.`id_customer`')
                ->where('`id_student_education` > ' . (int) $idLastElement)
                ->orderBy('`id_student_education` DESC')
                ->limit(5);
            break;

        case 'student_message':
            $sql = (new DbQuery())
                ->select('SQL_CALC_FOUND_ROWS c.`id_student_message`, ct.`id_student`, ct.`id_student_thread`')
                ->select('ct.`email`, c.`date_add` AS `date_upd`')
                ->from('student_message', 'c')
                ->leftJoin('student_thread', 'ct', 'c.`id_student_thread` = ct.`id_student_thread`')
                ->where('c.`id_student_message` > ' . (int) $idLastElement)
                ->where('c.`id_employee` = 0')
                ->where('ct.`id_shop` IN (' . implode(', ', Shop::getContextListShopID()) . ')')
                ->orderBy('c.`id_student_message` DESC')
                ->limit(5);
            break;
        default:
            $sql = (new DbQuery())
                ->select('SQL_CALC_FOUND_ROWS t.`id_' . bqSQL($type) . '`, t.*')
                ->from(bqSQL($type), 't')
                ->where('t.`id_' . bqSQL($type) . '` > ' . (int) $idLastElement)
                ->orderBy('t.`id_' . bqSQL($type) . '` DESC')
                ->limit(5);
            break;
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        $total = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()', false);
        $json = ['total' => $total, 'results' => []];

        foreach ($result as $value) {
            $studentName = '';

            if (isset($value['firstname']) && isset($value['lastname'])) {
                $studentName = Tools::safeOutput($value['firstname'] . ' ' . $value['lastname']);
            } else
            if (isset($value['email'])) {
                $studentName = Tools::safeOutput($value['email']);
            }

            $json['results'][] = [
                'id_student_education' => ((!empty($value['id_student_education'])) ? (int) $value['id_student_education'] : 0),
                'id_customer'           => ((!empty($value['id_customer'])) ? (int) $value['id_customer'] : 0),
                'id_student_message'   => ((!empty($value['id_student_message'])) ? (int) $value['id_student_message'] : 0),
                'id_student_thread'    => ((!empty($value['id_student_thread'])) ? (int) $value['id_student_thread'] : 0),
                'price'                => ((!empty($value['price'])) ? Tools::displayPrice((float) $value['price'], 1, false) . ' HT' : 0),
                'student_name'         => $studentName,
                'update_date'          => isset($value['date_upd']) ? (int) strtotime($value['date_upd']) * 1000 : 0,
            ];
        }

        return $json;
    }

    public function updateEmployeeLastElement($type) {

        global $cookie;

        if (in_array($type, $this->types)) {
            // We update the last item viewed
            return Db::getInstance()->update(
                'employee',
                [
                    'id_last_' . bqSQL($type) => ['type' => 'sql', 'value' => '(SELECT IFNULL(MAX(`id_' . $type . '`), 0) FROM `' . _DB_PREFIX_ . (($type == 'order') ? bqSQL($type) . 's' : bqSQL($type)) . '`)'],
                ],
                '`id_employee` = ' . (int) $cookie->id_employee
            );
        }

        return false;
    }

}
