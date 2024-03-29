<?php

/**
 * Class CustomerPieceStateCore
 *
 * @since 1.9.1.0
 */
class CustomerPieceStateCore extends PhenyxObjectModel {

    // @codingStandardsIgnoreStart
    /** @var string Name */
    public $name;
    /** @var string Template name if there is any e-mail to send */
    public $template;
    /** @var bool Send an e-mail to customer ? */
    public $send_email;
    /** @var string $module_name */
    public $module_name;
    /** @var bool Allow customer to view and download invoice when order is at this state */
    public $invoice;
    /** @var string Display state in the specified color */
    public $color;
    /** @var bool $unremovable */
    public $unremovable;
    /** @var bool Log authorization */
    public $logable;
    /** @var bool Delivery */
    public $delivery;
    /** @var bool Hidden */
    public $hidden;
    /** @var bool Shipped */
    public $shipped;
    /** @var bool Paid */
    public $paid;
    /** @var bool Attach PDF Invoice */
    public $pdf_invoice;
    /** @var bool Attach PDF Delivery Slip */
    public $pdf_delivery;
    /** @var bool True if carrier has been deleted (staying in database as deleted) */
    public $deleted = 0;
    // @codingStandardsIgnoreEnd

    /**
     * @see PhenyxObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'customer_piece_state',
        'primary'   => 'id_customer_piece_state',
        'multilang' => true,
        'fields'    => [
            'send_email'   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'module_name'  => ['type' => self::TYPE_STRING, 'validate' => 'isModuleName'],
            'invoice'      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'color'        => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
            'logable'      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'shipped'      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'unremovable'  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'delivery'     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'hidden'       => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'paid'         => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'pdf_delivery' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'pdf_invoice'  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'deleted'      => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

            /* Lang fields */
            'name'         => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 64],
            'template'     => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isTplName', 'size' => 64],
        ],
    ];

    protected $webserviceParameters = [
        'fields' => [
            'unremovable' => [],
            'delivery'    => [],
            'hidden'      => [],
        ],
    ];

    const FLAG_NO_HIDDEN = 1; /* 00001 */
    const FLAG_LOGABLE = 2; /* 00010 */
    const FLAG_DELIVERY = 4; /* 00100 */
    const FLAG_SHIPPED = 8; /* 01000 */
    const FLAG_PAID = 16; /* 10000 */

    /**
     * Get all available order statuses
     *
     * @param int $idLang Language id for status name
     *
     * @return array Order statuses
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getCustomerPieceStates($idLang) {

        $cacheId = 'CustomerPieceState::getCustomerPieceStates_' . (int) $idLang;

        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select('*')
                    ->from('customer_piece_state', 'os')
                    ->leftJoin('customer_piece_state_lang', 'osl', 'os.`id_customer_piece_state` = osl.`id_customer_piece_state`')
                    ->where('osl.`id_lang` = ' . (int) $idLang)
                    ->where('`deleted` = 0')
                    ->orderBy('`name` ASC')
            );
            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Check if we can make a invoice when order is in this state
     *
     * @param int $idCustomerPieceState State ID
     *
     * @return bool availability
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function invoiceAvailable($idCustomerPieceState) {

        $result = false;

        if (Configuration::get('EPH_INVOICE')) {
            $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('`invoice`')
                    ->from('customer_piece_state')
                    ->where('`id_customer_piece_state` = ' . (int) $idCustomerPieceState)
            );
        }

        return (bool) $result;
    }

    /**
     * @return bool
     */
    public function isRemovable() {

        return !($this->unremovable);
    }

}
