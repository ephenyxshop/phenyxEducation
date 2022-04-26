<?php

/**
 * Class PaymentCore
 *
 * @since 2.1.0.0
 */
class PaymentCore extends ObjectModel {

    // @codingStandardsIgnoreStart

    /** @var int $id_currency */
    public $id_currency;
    /** @var float $amount */
    public $amount;
    /** @var integer $id_payment_mode */
    public $id_payment_mode;
    /** @var bool $book */
    public $booked;

    public $id_book_record;
    /** @var string $date_add */
	public $payment_date;
	
    public $date_add;

    public $id_bank_account;

    public $payment_account;

    public $id_student_education;

    public $id_student;

    public $id_piece;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'payment',
        'primary' => 'id_payment',
        'fields'  => [
            'id_currency'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'amount'          => ['type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'required' => true],
            'id_payment_mode' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'booked'          => ['type' => self::TYPE_BOOL],
            'id_book_record'  => ['type' => self::TYPE_INT],
			'payment_date'         => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
            'date_add'        => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public function __construct($id = null) {

        parent::__construct($id);

        if ($this->id) {
            $this->id_bank_account = $this->getIdBank();
            $this->payment_account = $this->getPaymentAccount();
            $this->id_student = $this->getPaymentStudent();
            $this->id_student_education = $this->getPaymentEducation();
            $this->id_piece = $this->getPaymentPiece();

        }

    }

    public function getIdBank() {

        $paymentMode = new PaymentMode($this->id_payment_mode);
        return $paymentMode->id_bank_account;
    }

    public function getPaymentAccount() {

        $bank = new BankAccount($this->id_bank_account);
        return $bank->id_stdaccount;
    }

    public function getPaymentPiece() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('id_student_piece')
                ->from('payment_details')
                ->where('`id_payment` = ' . (int) $this->id)
        );

    }

    public function getPaymentStudent() {

        $studentPiece = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('id_student_piece')
                ->from('payment_details')
                ->where('`id_payment` = ' . (int) $this->id)
        );

        $education = new StudentPieces($studentPiece);
        return $education->id_student;
    }

    public function getPaymentEducation() {

        $studentPiece = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('id_student_piece')
                ->from('payment_details')
                ->where('`id_payment` = ' . (int) $this->id)
        );

        $education = new StudentPieces($studentPiece);
        return $education->id_student_education;
    }

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

    public static function getByCustomerPieceId($idCustomerPiece, $idLang) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('pd.*, p.`id_payment_mode`, pm.`name`')
                ->from('payment_details', 'pd')
                ->leftJoin('payment', 'p', 'p.`id_payment` = pd.`id_payment`')
                ->leftJoin('payment_mode_lang', 'pm', 'pm.`id_payment_mode` = p.`id_payment_mode` AND pm.`id_lang` = ' . (int) $idLang)
                ->where('pd.`id_student_piece` = ' . (int) $idCustomerPiece.' OR pd.`id_customer_piece` = ' . (int) $idCustomerPiece)
        );
    }

    public static function getTotalPaymentsByRange($dateStart, $dateEnd) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('SUM(`amount`)')
                ->from('payment')
                ->where('`date_add` >= \'' . $dateStart . '\'')
                ->where('`date_add` <= \'' . $dateEnd . '\'')
        );

    }

}
