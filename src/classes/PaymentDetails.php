<?php

/**
 * Class PaymentCore
 *
 * @since 2.1.0.0
 */
class PaymentDetailsCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var string $order_reference */
    public $id_payment;
    /** @var int $id_currency */
    public $id_student_piece;
	
	public $id_customer_piece;
	
	public $id_supplier_piece;
    /** @var float $amount */
    public $amount;
    /** @var string $date_add */
    public $date_add;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'payment_details',
        'primary' => 'id_payment_detail',
        'fields'  => [
            'id_payment'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_student_piece' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_customer_piece' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_supplier_piece' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'amount'            => ['type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'required' => true],
            'date_add'          => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 2.1.0.0
     */
    public function add($autoDate = false, $nullValues = false) {

        if (parent::add($autoDate, $nullValues)) {
            return true;
        }

        return false;
    }
	
	public static function getByCustomerPieceId($idCustomerPiece) {

        return ObjectModel::hydrateCollection(
            'PaymentDetails',
            Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select('*')
                    ->from('payment_details')
                    ->where('`id_customer_piece` = ' . (int) $idCustomerPiece)
            )
        );
    }

}
