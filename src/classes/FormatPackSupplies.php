<?php

class FormatPackSuppliesCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'formatpack_supplies',
		'primary' => 'id_formatpack_supplies',
		'fields'  => [
			'name'           => ['type' => self::TYPE_STRING, 'size' => 32],
			'pamp'           => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'required' => true],
			'sold'          => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'stock'          => ['type' => self::TYPE_INT, 'validate' => 'isInt'],
			'stock_previsionnel'          => ['type' => self::TYPE_INT, 'validate' => 'isInt'],	
		],
	];

	public $name;
	public $pamp;
	public $sold;
	public $stock;
	public $stock_previsionnel;
	
	
	public function __construct($id = null) {

		parent::__construct($id);
	}

	

}
