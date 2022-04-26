<?php

/**
 * @since 1.9.1.0
 */
class EventCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'events',
        'primary' => 'id_event',
        'fields'  => [
            'id_sale_agent' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_employee'   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'title'         => ['type' => self::TYPE_STRING],
            'description'   => ['type' => self::TYPE_STRING],
            'start'         => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'end'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'color'         => ['type' => self::TYPE_STRING, 'size' => 32],
			'start_datetime'  => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'end_datetime'  => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_add'      => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'      => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
    public $id_sale_agent;
    public $id_employee;
    public $title;
    public $description;
    public $start;
    public $end;
    public $color;
	public $start_datetime;
	public $end_datetime;	
    public $date_add;
    public $date_upd;

    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);

    }

    public function add($autoDate = true, $nullValues = false) {

        $return = parent::add($autoDate, true);
        return $return;
    }

    public static function listEvents() {

        return Db::getInstance()->executeS(
            (new DbQuery())
                ->select('*')
                ->from('events')
        );
    }
	
	public static function getEventIdCustomers($idEvent) {
		
		$customers = [];
		$attendees = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS((new DbQuery())
        	->select('id_customer')
            ->from('event_customers')
            ->where('`id_event` =' . $idEvent)
		);
		foreach($attendees as $customer) {
			$customers[] = $customer['id_customer'];
		}
		
		return $customers;
	}

}
