<?php
use Defuse\Crypto\Crypto;
use \Curl\Curl;
class NotificationCore {

    public $types;

    public function __construct() {

        $this->types = [ 'customer', 'student_education', 'employee_message'];
    }

    public function getLastElements() {

        $notifications = [];
        $employeeInfos = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('`id_last_student_education`, `id_last_student_message`, `id_last_customer`, `id_last_employee_message`')
                ->from('employee')
                ->where('`id_employee` = ' . (int) Context::getContext()->cookie->id_employee)
        );

        foreach ($this->types as $type) {
            $notifications[$type] = Notification::getLastElementsIdsByType($type, $employeeInfos['id_last_' . $type]);
        }

        return $notifications;
    }

    public static function getLastElementsIdsByType($type, $idLastElement) {
       $file = fopen("testgetLastElementsIdsByType.txt","a");
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
        if($type == 'employee_message') {
            $result = EmployeeThread::getEmployeeNotification($idLastElement);
            $total = (is_array($result) && count($result)) ? count($result) : 0;
        } else {
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($sql, true, false);
            $total = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue('SELECT FOUND_ROWS()', false);
        }
        
        $json = ['total' => $total, 'results' => []];

        foreach ($result as $value) {
            $studentName = '';

            if (isset($value['firstname']) && isset($value['lastname'])) {
                $studentName = Tools::safeOutput($value['firstname'] . ' ' . $value['lastname']);
            } else
            if (isset($value['email'])) {
                $studentName = Tools::safeOutput($value['email']);
            }
            if (isset($value['employee'])) {
                $studentName = Tools::safeOutput($value['employee']);
            }

            $json['results'][] = [
                'id_student_education' => ((!empty($value['id_student_education'])) ? (int) $value['id_student_education'] : 0),
                'id_customer'           => ((!empty($value['id_customer'])) ? (int) $value['id_customer'] : 0),
                'id_student_message'   => ((!empty($value['id_student_message'])) ? (int) $value['id_student_message'] : 0),
                'id_student_thread'    => ((!empty($value['id_student_thread'])) ? (int) $value['id_student_thread'] : 0),
                'id_employee_thread'    => ((!empty($value['id_employee_thread'])) ? (int) $value['id_employee_thread'] : 0),
                'price'                => ((!empty($value['price'])) ? Tools::displayPrice((float) $value['price'], 1, false) . ' HT' : 0),
                'student_name'         => $studentName,
                'update_date'          => isset($value['date_upd']) ? (int) strtotime($value['date_upd']) * 1000 : 0,
                'original_subject'     => ((!empty($value['original_subject'])) ? $value['original_subject'] : 0),
                'subject'              => ((!empty($value['subject'])) ? $value['subject'] : 0),
            ];
        }
        fwrite($file, print_r($json, true));
        return $json;
    }

    public function updateEmployeeLastElement($type) {
        global $cookie;
        if (in_array($type, $this->types)) {
            if($type == 'employee_message') {
                $max = EmployeeThread::getMaxMessageId();
                 return Db::getInstance()->update('employee', [
                     'id_last_employee_message' => ['type' => 'sql', 'value' => $max], 
                 ], '`id_employee` = ' . (int) $cookie->id_employee
                );
            } else {
                return Db::getInstance()->update(
                'employee',
                [
                    'id_last_' . bqSQL($type) => ['type' => 'sql', 'value' => '(SELECT IFNULL(MAX(`id_' . $type . '`), 0) FROM `' . _DB_PREFIX_ . (($type == 'student_education') ? bqSQL($type) . 's' : bqSQL($type)) . '`)'],
                ],
                '`id_employee` = ' . (int) $cookie->id_employee
            );
            }
            
        }

        return false;
    }

}
