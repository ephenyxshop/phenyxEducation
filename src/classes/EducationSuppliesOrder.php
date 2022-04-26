<?php

class EducationSuppliesOrderCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_supplies_order',
		'primary' => 'id_education_supplies_order',
		'fields'  => [
			'invoice_number'          => ['type' => self::TYPE_STRING, 'size' => 64],
			'id_education_supplier'   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'total_price'             => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_shipping_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_shipping_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_tax'               => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_price_tax_incl'    => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'date_add'                => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
		],
	];

	public $invoice_number;
	public $id_education_supplier;
	public $total_price;
	public $total_shipping_tax_excl;
	public $total_shipping_tax_incl;
	public $total_tax;
	public $total_price_tax_incl;
	public $date_add;
	public $supplier;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null) {

		parent::__construct($id);

		if ($full && $this->id) {
			$this->supplier = $this->getSupplierName();

		}

	}

	public function add($autoDate = false, $nullValues = false) {

		return parent::add($autoDate = false, $nullValues = false);
	}

	public function getSupplierName() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('education_supplier')
				->where('`education_supplier` = ' . $this->education_supplier)
		);
	}

}
