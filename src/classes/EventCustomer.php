<?php

/**
 * @since 1.9.1.0
 */
class EventCustomerCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'event_customers',
        'primary'   => 'id_event_customer',
        'fields'    => [
            
			'id_event'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_customer'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'date_add'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
        ],
    ];
   
    public $id_event;
	public $id_customer;
	public $date_add;
	public $date_upd;

    
    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, $idLang, $idShop);

        
    }
	
	public function add($autoDate = true, $nullValues = false) {

        if (!parent::add($autoDate, $nullValues)) {
            return false;
        }

        return true;
    }
	
	public static function purgeAppointment($id_event) {
		
		Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'event_customers` WHERE `id_event`= ' . $id_event);
	}

   
    

    
}
