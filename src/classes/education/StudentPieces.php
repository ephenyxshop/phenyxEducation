<?php

/**
 * @since 2.1.0.0
 */
class StudentPiecesCore extends ObjectModel {

	const ROUND_ITEM = 1;
	const ROUND_LINE = 2;
	const ROUND_TOTAL = 3;

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'student_pieces',
		'primary' => 'id_student_piece',
		'fields'  => [
			'piece_type'                  => ['type' => self::TYPE_STRING, 'validate' => 'isMessage'],
			'id_currency'                 => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_student'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_customer'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_student_education'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_education_session'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_payment_mode'             => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education'                => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_education_attribute'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'education_tax_excl'          => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'education_tax_incl'          => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_discounts_tax_excl'    => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_discounts_tax_incl'    => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'shipping_tax_excl'           => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'shipping_tax_incl'           => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_with_freight_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_with_freight_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'tax_rate'                   => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'total_tax'                   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_tax_incl'              => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'piece_cost'                  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'piece_margin'                => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
			'total_paid'                  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'id_carrier'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'shipping_number'             => ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'],
			'piece_number'                => ['type' => self::TYPE_INT],
			'id_customer_piece' 		  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'validate'                    => ['type' => self::TYPE_BOOL],
			"is_book"                     => ['type' => self::TYPE_BOOL],
			'id_book_record'              => ['type' => self::TYPE_INT],
			'note'                        => ['type' => self::TYPE_STRING, 'validate' => 'isMessage'],
			'date_add'                    => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'date_upd'                    => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

		],
	];

	public $piece_type;
	public $id_currency;
	public $id_education_session;
	public $id_student;
	public $id_customer;
	public $id_student_education;
	public $id_payment_mode;
	public $id_education;
	public $id_education_attribute;
	public $education_tax_excl;
	public $education_tax_incl;
	public $total_discounts_tax_excl;
	public $total_discounts_tax_incl;
	public $shipping_tax_excl;
	public $shipping_tax_incl;
	public $total_with_freight_tax_excl;
	public $total_with_freight_tax_incl;
	public $discount_rate;
	public $tax_rate;
	public $total_tax;
	public $total_tax_incl;
	public $piece_cost;
	public $piece_margin;
	public $total_paid;
	public $id_carrier;
	public $shipping_number;
	public $piece_number;
	public $id_customer_piece;
	public $validate;
	public $is_book;
	public $id_book_record;
	public $note;
	public $date_add;
	public $date_upd;

	public $prefix;
	public $nameType;

	public $balance_due;

	public $date_echeance;

	public function __construct($id = null, $idLang = null) {

		parent::__construct($id, $idLang);

		if ($this->id) {
			$this->prefix = $this->getPrefix();
			$this->nameType = $this->getTypeName();
			$this->balance_due = $this->getBalanceDue();
			$date = new DateTime($this->date_add);
			$date->modify('+1 month');
			$this->date_echeance = $date->format('d/m/Y');
		}

	}

	public function getBalanceDue() {

		return $this->education_tax_incl - $this->total_paid;
	}

	public function getDiscountPercent() {

		return (1 - ($this->total_products_tax_excl / ($this->total_products_tax_excl - $this->total_discounts_tax_excl))) * 100;
	}

	public function getPrefix() {

		switch ($this->piece_type) {

		case 'QUOTATION':
			return $this->l('DE');
			break;
		case 'ORDER':
			return $this->l('BC');
			break;
		case 'INVOICE':
			return $this->l('FA');
			break;

		}

	}

	public function getTypeName() {

		switch ($this->piece_type) {

		case 'QUOTATION':
			return $this->l('Devis');
			break;
		case 'ORDER':
			return $this->l('Commande');
			break;

		case 'INVOICE':
			return $this->l('Facture');
			break;

		}

	}

	public function l($string, $idLang = null, Context $context = null) {

		$class = get_class($this);

		if (strtolower(substr($class, -4)) == 'core') {
			$class = substr($class, 0, -4);
		}

		return Translate::getClassTranslation($string, $class, $context);
	}

	public function getFields() {

		if (!$this->id_lang) {
			$this->id_lang = Configuration::get('PS_LANG_DEFAULT', null, null, $this->id_shop);
		}

		return parent::getFields();
	}

	public function add($autoDate = false, $nullValues = true) {

		$this->roundAmounts();
		$this->piece_number = $this->generateNewInvoiceNumber();
		$this->tax_rate = 20;
		
		$result = parent::add($autoDate, $nullValues);
		
		if($result) {
			//$this->id_customer_piece = $this->generateCustomerPiece();
		}

		return $result;
	}
	
	public function generateCustomerPiece() {
		
		$studentEducation = new StudentEducation($this->id_student_education);
		if($studentEducation->id_education_attribute > 0) {
			$education = new Declinaison($studentEducation->id_education_attribute);
		} else {
			$education = new Education($studentEducation->id_education);
		}
		
		$newPiece = new CustomerPieces();
		$newPiece->piece_type = $this->piece_type;
		$newPiece->is_education = 1;
		$newPiece->id_currency = $this->id_currency;
		$newPiece->id_customer = $this->id_customer;
		$newPiece->id_education_session = $this->id_education_session;
		$newPiece->id_student_education = $this->id_student_education;
		$newPiece->id_payment_mode = $this->id_payment_mode;
		$newPiece->base_tax_excl = $this->education_tax_excl;	
		$newPiece->total_products_tax_excl = $this->education_tax_excl;	
		$newPiece->total_products_tax_incl = $this->education_tax_incl;	
		$newPiece->total_with_freight_tax_excl = $this->education_tax_excl;	
		$newPiece->total_tax_excl = $this->education_tax_excl;	
		
		$newPiece->total_tax = $this->total_tax;
		$newPiece->total_tax_incl = $this->total_tax_incl;	
		$newPiece->piece_margin = $this->piece_margin;	
		$newPiece->total_paid = $this->total_paid;	
		$newPiece->conversion_rate = 1;
		$newPiece->total_paid = $this->total_paid;		
		$newPiece->validate = $this->validate;	
		$newPiece->is_book = $this->is_book;	
		$newPiece->id_book_record = $this->id_book_record;	
	
		$newPiece->id_shop = (int) $this->context->shop->id;
    	$newPiece->id_shop_group = (int) $this->context->shop->id_shop_group;
    	$newPiece->id_lang = $this->context->language->id;
    	$newPiece->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
   	 	$newPiece->round_type = Configuration::get('PS_ROUND_TYPE');
    	$newPiece->date_add = $this->date_add;
		
		$result = $newPiece->add();
		
		if($result) {
		
			$object = new CustomerPieceDetail();
        	$object->id_customer_piece = $newPiece->id;
        	$object->id_warehouse = 0;
			$object->id_education = $studentEducation->id_education;	
			$object->id_education_attribute = $studentEducation->id_education_attribute;	
			$object->product_name = $studentEducation->name;
			$object->product_quantity = 1;		
			$object->original_price_tax_excl = $studentPiece->education_tax_excl;
			$object->original_price_tax_incl = $studentPiece->education_tax_incl;		
			$object->unit_tax_excl = $studentPiece->education_tax_excl;
			$object->unit_tax_incl = $studentPiece->education_tax_incl;		
			$object->total_tax_excl = $studentPiece->education_tax_excl;
			$object->total_tax_incl = $studentPiece->education_tax_incl;	
			$object->tax_rate = $this->tax_rate;
			$object->total_tax = $studentPiece->total_tax;		
			$object->product_reference = $education->reference;
			$result = $object->add();		
		
		}
		
		return $newPiece->id;
	}

	public function update($nullValues = true) {

		$this->roundAmounts();

		if ($this->piece_type == 'INVOICE') {
			$this->updateRentability();
		}

		return parent::update($nullValues);

	}

	public function delete() {

		if ($this->piece_type == 'INVOICE' && $this->validate == 1) {
			return false;
		}

		$this->deleteCustomerPiece();

		return parent::delete();
	}

	public function updateRentability() {

		$shipping_number = Db::getInstance()->getValue(
			(new DbQuery())
				->select('shipping_number')
				->from('student_education')
				->where('`id_student_education` = ' . $this->id_student_education)
		);

		$idSupply = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_supplies`')
				->from('student_education')
				->where('`id_student_education` = ' . $this->id_student_education)
		);
		$supply = new EducationSupplies($idSupply);

		if (empty($shipping_number)) {
			$this->piece_cost = $supply->wholesale_price;
		} else {
			$this->piece_cost = $supply->wholesale_price + $supply->average_freight;
			$this->shipping_tax_excl = $supply->average_freight;
		}

		$this->piece_margin = $this->education_tax_excl - $this->piece_cost;

	}

	public function deleteCustomerPiece() {

		$customerPiece = new CustomerPieces($this->id_customer_piece);
		if(!$customerPiece->validate) {
			$customerPiece->delete();			
		}
	}

	public function getParentTransfert() {

		$idParent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('id_customer_piece')
				->from('customer_pieces')
				->where('`last_transfert` = ' . (int) $this->id)
		);

		if (!empty($idParent)) {
			Db::getInstance()->execute(
				'UPDATE `' . _DB_PREFIX_ . 'customer_pieces`
                SET `last_transfert` = 0
                WHERE `id_customer_piece` = ' . (int) $idParent
			);
			Db::getInstance()->execute(
				'UPDATE `' . _DB_PREFIX_ . 'customer_piece_detail`
                SET `id_customer_piece` = ' . (int) $idParent
			);
			return true;

		}

		return false;
	}

	public static function getRequest($pieceType = null) {

		$context = Context::getContext();
		

		$query = new DbQuery();
		$query->select('a.*, (a.education_tax_incl+a.shipping_tax_incl) as `total`, (a.education_tax_excl+a.shipping_tax_excl) as `total_tax_excl`, CONCAT(s.`firstname`, " ", s.`lastname`) AS `student`,
		(a.`education_tax_incl` + a.`shipping_tax_incl`  - a.`total_paid`) as `balanceDue`, case when a.validate = 1 then \'<div class="orderValidate"></div>\' else \'<div class="orderOpen"></div>\' end as validate, case when a.validate = 1 then 1 else 0 end as isLocked,  s.`id_country`, ad.`address1`, ad.`address2`, ad.`postcode`, ad.`city`, cl.`name` AS country, se.reference_edof, case when a.is_book = 1 then \'<div class="orderBook"><i class="icon icon-book" aria-hidden="true"></i></div>\' else \'<div class="orderUnBook"><i class="icon icon-times" aria-hidden="true" style="color:red;"></i></div>\' end as booked, case when a.is_book = 1 then 1 else 0 end as isBooked');
		$query->from('student_pieces', 'a');
		$query->leftJoin('customer', 's', 's.`id_customer` = a.`id_customer`');
		$query->leftJoin('address', 'ad', 'ad.`id_customer` = a.`id_customer`');
		$query->leftJoin('student_education', 'se', 'se.`id_student_education` = a.`id_student_education`');
		$query->leftJoin('country_lang', 'cl', 'cl.`id_country` = s.`id_country` AND cl.`id_lang` = ' . $context->language->id);

		if ($pieceType) {
			$query->where('a`piece_type` LIKE \'' . $pieceType . '\'');
		}

		$query->orderBy('a.`date_add` DESC');

		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);

		return $orders;

	}

	public static function getInvoiceDue() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('SUM(`education_tax_incl`)')
				->from('student_pieces')
				->where('`total_paid` = 0')
		);
	}

	public static function getInvoicePaid() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('SUM(`education_tax_incl`)')
				->from('student_pieces')
				->where('`total_paid` = `education_tax_incl`')
		);
	}

	public function generateNewInvoiceNumber() {

		if (empty($this->piece_number)) {
			$year = date('Y');
			$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				(new DbQuery())
					->select('`piece_number`')
					->from('customer_pieces')
					->where('`piece_type` LIKE \'INVOICE\'')
					->orderBy('`id_customer_piece` DESC')
			);

			if (empty($lastValue)) {
				$lastValue = 1;
				return $year . sprintf("%06s", $lastValue);
			}

			$test = substr($lastValue, 0, 4);

			if ($test == $year) {
				return $lastValue + 1;
			} else {
				$lastValue = 1;
				return $year . sprintf("%06s", $lastValue);
			}

		}

		return $this->piece_number;

	}

	public function roundAmounts() {

		foreach (static::$definition['fields'] as $fieldName => $field) {

			if ($field['type'] === static::TYPE_FLOAT && isset($this->$fieldName)) {
				$this->$fieldName = Tools::ps_round($this->$fieldName, _EPH_PRICE_DATABASE_PRECISION_);
			}

		}

	}

	public function deleteProduct(StudentPieces $customerPiece, CustomerPieceDetail $customerPieceDetail, $quantity) {

		if ($customerPiece->validate || !validate::isLoadedObject($customerPieceDetail)) {
			return false;
		}

		return $this->_deleteProduct($customerPieceDetail, (int) $quantity);
	}

	protected function _deleteProduct($customerPieceDetail, $quantity) {

		$productPriceTaxExcl = $customerPieceDetail->unit_price_tax_excl * $quantity;
		$productPriceTaxIncl = $customerPieceDetail->unit_price_tax_incl * $quantity;

		$this->total_products_tax_excl -= $productPriceTaxExcl;
		$this->total_products_tax_incl -= $productPriceTaxIncl;
		$this->roundAmounts();
		$customerPieceDetail->product_quantity -= (int) $quantity;

		if ($customerPieceDetail->product_quantity == 0) {

			if (!$customerPieceDetail->delete()) {
				return false;
			}

			return $this->update();
		} else {
			$customerPieceDetail->total_price_tax_incl -= $productPriceTaxIncl;
			$customerPieceDetail->total_price_tax_excl -= $productPriceTaxExcl;
		}

		return $customerPieceDetail->update() && $this->update();
	}

	public function getProductsDetail() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('customer_piece_detail', 'od')
				->leftJoin('product', 'p', 'p.`id_product` = od.`id_product`')
				->leftJoin('product_shop', 'ps', 'ps.`id_product` = od.`id_product`')
				->where('od.`id_customer_piece` = ' . (int) $this->id)
		);
	}

	public function getProducts($products = false, $selectedProducts = false, $selectedQty = false) {

		if (!$products) {
			$products = $this->getProductsDetail();
		}

		$resultArray = [];

		foreach ($products as $row) {
			// Change qty if selected

			if ($selectedQty) {
				$row['product_quantity'] = 0;

				if (is_array($selectedProducts) && !empty($selectedProducts)) {

					foreach ($selectedProducts as $key => $idProduct) {

						if ($row['id_customer_piece_detail'] == $idProduct) {
							$row['product_quantity'] = (int) $selectedQty[$key];
						}

					}

				}

				if (!$row['product_quantity']) {
					continue;
				}

			}

			// Add information for virtual product

			if ($row['download_hash'] && !empty($row['download_hash'])) {
				$row['filename'] = ProductDownload::getFilenameFromIdProduct((int) $row['id_product']);
				// Get the display filename
				$row['display_filename'] = ProductDownload::getFilenameFromFilename($row['filename']);
			}

			$row['id_address_delivery'] = $this->id_address_delivery;

			/* Stock product */
			$resultArray[(int) $row['id_customer_piece_detail']] = $row;
		}

		return $resultArray;
	}

	public function getVirtualProducts() {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('`id_product`, `id_product_attribute`, `download_hash`, `download_deadline`')
				->from('customer_piece_detail', 'od')
				->where('od.`id_customer_piece` = ' . (int) $this->id)
				->where('`download_hash` <> \'\'')
		);
	}

	public function isVirtual($strict = true) {

		$products = $this->getProducts();

		if (count($products) < 1) {
			return false;
		}

		$virtual = true;

		foreach ($products as $product) {

			if ($strict === false && (bool) $product['is_virtual']) {
				return true;
			}

			$virtual &= (bool) $product['is_virtual'];
		}

		return $virtual;
	}

	public static function getCustomerOrders($idCustomer, $showHiddenStatus = false, Context $context = null) {

		if (!$context) {
			$context = Context::getContext();
		}

		$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('o.*, (SELECT SUM(od.`product_quantity`) FROM `' . _DB_PREFIX_ . 'customer_piece_detail` od WHERE od.`id_customer_piece` = o.`id_customer_piece`) nb_products')
				->from('customer_pieces', 'o')
				->where('o.`piece_type` LIKE \'INVOICE\' AND o.`validate` = 1 AND o.`id_customer` = ' . (int) $idCustomer . ' ' . Shop::addSqlRestriction(Shop::SHARE_ORDER))
				->groupBy('o.`id_customer_piece`')
				->orderBy('o.`date_add` DESC')
		);

		if (!$res) {
			return [];
		}

		return $res;
	}

	public static function getOrdersbyIdCustomer($idCustomer) {

		$collection = new PhenyxShopCollection('StudentPieces');
		$collection->where('id_customer', '=', (int) $idCustomer);
		$collection->where('piece_type', 'like', 'INVOICE');

		foreach ($collection as $order) {
			$order->payment_mode = PaymentMode::getPaymentModeNameById($order->id_payment_mode);
			$order->balance_due = $order->total_tax_incl - $order->total_paid;
		}

		return $collection;
	}

	public static function getPieceIdEducationSession($idEducationSession) {

		$idPiece = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`id_student_piece`')
				->from('student_pieces')
				->where('`id_student_education` = ' . (int) $idEducationSession)
		);

		return new StudentPieces($idPiece);
	}

	public static function getOrderTotalbyIdCustomer($idCustomer) {

		$total = 0;
		$collection = new PhenyxShopCollection('StudentPieces');
		$collection->where('id_customer', '=', (int) $idCustomer);
		$collection->where('piece_type', 'like', 'INVOICE');

		foreach ($collection as $order) {
			$total = $total + $order->total_paid;
		}

		return $total;
	}

	public function getTotalProductsWithoutTaxes($products = false) {

		return $this->total_products_tax_excl;
	}

	public function getTotalProductsWithTaxes($products = false) {

		if ($this->total_products_tax_incl != '0.00' && !$products) {
			return $this->total_products_tax_incl;
		}

		/* Retro-compatibility (now set directly on the validateOrder() method) */

		if (!$products) {
			$products = $this->getProductsDetail();
		}

		$return = 0;

		foreach ($products as $row) {
			$return += $row['total_price_tax_incl'];
		}

		if (!$products) {
			$this->total_products_tax_incl = $return;
			$this->update();
		}

		return $return;
	}

	public static function getCustomerNbOrders($idCustomer) {

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('COUNT(`id_customer_piece`) AS `nb`')
				->from('customer_pieces')
				->where('`id_customer` = ' . (int) $idCustomer . ' ' . Shop::addSqlRestriction())
		);

		return isset($result['nb']) ? $result['nb'] : 0;
	}

	public function getTotalWeight() {

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('SUM(`product_weight` * `product_quantity`)')
				->from('customer_piece_detail')
				->where('`id_customer_piece` = ' . (int) $this->id)
		);

		return (float) $result;
	}

	public static function getInvoice($idInvoice) {

		Tools::displayAsDeprecated();

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
			(new DbQuery())
				->select('`invoice_number`, `id_customer_piece`')
				->from('orders')
				->where('`invoice_number` = ' . (int) $idInvoice)
		);
	}

	public function getWsOrderRows() {

		$result = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_customer_piece_detail` AS `id`')
				->select('`id_product`')
				->select('`product_price`')
				->select('`id_customer_piece`')
				->select('`id_product_attribute`')
				->select('`product_quantity`')
				->select('`product_name`')
				->select('`product_reference`')
				->select('`product_ean13`')
				->select('`product_upc`')
				->select('`unit_price_tax_incl`')
				->select('`unit_price_tax_excl`')
				->from('customer_piece_detail')
				->where('`id_customer_piece` = ' . (int) $this->id)
		);

		return $result;
	}

	public function deleteAssociations() {

		return Db::getInstance()->delete('customer_piece_detail', '`id_customer_piece` = ' . (int) $this->id) !== false;
	}

	public static function getLastInvoiceNumber() {

		$sql = 'SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \'' . _DB_NAME_ . '\' AND   TABLE_NAME   = \'' . _DB_PREFIX_ . 'customer_pieces\'';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
	}

	public static function getValidOrderState() {

		$validates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('id_order_state')
				->from('order_state')
				->where('logable = 1 ')
		);
		$return = [];

		foreach ($validates as $key => $value) {
			$return[] = $value['id_order_state'];
		}

		return implode(',', $return);
	}

	public static function getmergeOrderTable() {

		$context = Context::getContext();
		$syncOrder = [];

		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('id_student_education')
				->from('student_education')
				->orderBy('`date_add` ASC')
		);

		$todo = 0;
		$done = 0;

		if (!empty($orders)) {

			foreach ($orders as $order) {

				if (StudentPieces::isMergeOrderTable($order['id_student_education'])) {
					$done = $done + 1;
				} else {
					$syncOrder['todo'][] = $order['id_student_education'];
				}

			}

			if (isset($syncOrder['todo']) && is_array($syncOrder['todo'])) {
				$todo = count($syncOrder['todo']);
			}

			$syncOrder['done'] = $done;
			$syncOrder['total'] = $todo + $done;
		}

		return $syncOrder;

	}

	public static function getPieceCost(StudentEducation $studentEducation) {

		if ($studentEducation->id_education_session > 0) {
			$cost = Db::getInstance()->getValue(
				(new DbQuery())
					->select('plateform_cost')
					->from('education_attribute')
					->where('`id_education_attribute` = ' . $studentEducation->id_education_session)
			);

		} else {
			$cost = Db::getInstance()->getValue(
				(new DbQuery())
					->select('plateform_cost')
					->from('education')
					->where('`id_education` = ' . $studentEducation->id_education)
			);
		}

		$supply = 550;
		$piece_cost = $cost + $supply;

		return $piece_cost;
	}

	public static function mergeOrderTable($id_student_Education) {

		$context = Context::getContext();

		if (StudentPieces::isMergeOrderTable($id_student_Education)) {
			return true;
		}

		$education = new StudentEducation($id_student_Education);

		if ($education->id_education_session > 0) {
			$cost = Db::getInstance()->getValue(
				(new DbQuery())
					->select('plateform_cost')
					->from('education_attribute')
					->where('`id_education_attribute` = ' . $education->id_education_session)
			);

		} else {
			$cost = Db::getInstance()->getValue(
				(new DbQuery())
					->select('plateform_cost')
					->from('education')
					->where('`id_education` = ' . $education->id_education)
			);
		}

		$supply = 550;

		$dateEnd = str_replace('/', '-', $education->date_end);
		$piece_cost = 550;
		$date = new DateTime(date('Y-m-d', strtotime($dateEnd)));
		$date->modify('+4 days');
		$dateAdd = $date->format('Y-m-d');

		$pieceNumber = StudentPieces::generateInvoiceNumber($education);

		$piece = new StudentPieces();
		$piece->piece_type = 'INVOICE';
		$piece->id_currency = 1;
		$piece->id_student = $education->id_student;
		$piece->id_customer = $education->id_customer;
		$piece->id_student_education = $education->id;
		$piece->id_education_session = $education->id_education_session;
		$piece->id_payment_mode = 1;
		$piece->id_education = $education->id_education;
		$piece->id_education_attribute = $education->id_education_attribute;
		$piece->education_tax_excl = $education->price;
		$piece->education_tax_incl = $education->priceWTax;
		$piece->shipping_tax_excl = 0;
		$piece->shipping_tax_incl = 0;
		$piece->total_with_freight_tax_excl = $education->price;
		$piece->total_with_freight_tax_incl = $education->priceWTax;
		$piece->total_tax = $piece->education_tax_incl - $piece->education_tax_excl;
		$piece->total_tax_incl = $education->priceWTax;
		$piece->total_paid = 0;
		$piece->id_carrier = 0;
		$piece->shipping_number = 0;
		$piece->piece_number = $pieceNumber;
		$piece->piece_cost = $piece_cost;
		$piece->piece_margin = $piece->education_tax_excl - $piece_cost;
		$piece->validate = 0;
		$piece->date_add = $dateAdd;
		$piece->date_upd = $dateAdd;

		if ($piece->add()) {
			return true;
		} else {
			return false;
		}

	}

	public static function generateInvoiceNumber(StudentEducation $education) {

		$date = new dateTime($education->date_begin);
		$year = $date->format('Y');
		$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`piece_number`')
				->from('student_pieces')
				->orderBy('`id_student_piece` DESC')
		);

		if (empty($lastValue)) {
			$lastValue = 1;
			return $year . sprintf("%06s", $lastValue);
		}

		$test = substr($lastValue, 0, 4);

		if ($test == $year) {
			return $lastValue + 1;
		} else {
			$lastValue = 1;
			return $year . sprintf("%06s", $lastValue);
		}

	}

	public static function isMergeOrderTable($id_student_education) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`id_student_piece`')
				->from('student_pieces')
				->where('`id_student_education` = ' . (int) $id_student_education)
		);
	}

	public static function getIncrementByType($type) {

		$year = date('Y');
		$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`piece_number`')
				->from('student_pieces')
				->where('`piece_type` LIKE \'' . $type . '\'')
				->orderBy('`id_student_piece` DESC')
		);

		if (empty($lastValue)) {
			$lastValue = 1;
			return $year . sprintf("%06s", $lastValue);
		}

		$test = substr($lastValue, 0, 4);

		if ($test == $year) {
			return $lastValue + 1;
		} else {
			$lastValue = 1;
			return $year . sprintf("%06s", $lastValue);
		}

	}

	public static function getProductsOrderDetail($idOrder) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('od.*, odt.`id_tax`, odt.`total_amount` as `total_line_tax`, t.`rate` as `rateTaxe`')
				->from('order_detail', 'od')
				->leftjoin('order_detail_tax', 'odt', 'odt.`id_order_detail` = od.`id_order_detail`')
				->leftjoin('tax', 't', 't.`id_tax` = odt.`id_tax`')
				->where('od.`id_order` = ' . (int) $idOrder)
		);
	}

	public static function getPieceIdbyTransfert($lastTransfert) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('id_customer_piece')
				->from('customer_pieces')
				->where('`piece_number` = ' . (int) $lastTransfert)
		);
	}

	public static function generatePayment(Order $order, StudentPieces $piece) {

		$payments = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('*')
				->from('order_payment')
				->where('`order_reference` = \'' . pSQL($order->reference) . '\'')
		);

		if (is_array($payments) && sizeof($payments)) {

			foreach ($payments as $payment) {

				$piecePayment = new Payment();
				$piecePayment->id_currency = $payment['id_currency'];
				$piecePayment->amount = $payment['amount'];
				$piecePayment->id_payment_mode = $piece->id_payment_mode;
				$piecePayment->payment_method = $payment['payment_method'];
				$piecePayment->conversion_rate = $payment['conversion_rate'];
				$piecePayment->booked = 0;
				$piecePayment->date_add = $payment['date_add'];

				if ($piecePayment->add()) {
					$paymentDetail = new PaymentDetails();
					$paymentDetail->id_payment = $piecePayment->id;
					$paymentDetail->id_customer_piece = $piece->id;
					$paymentDetail->amount = $payment['amount'];
					$paymentDetail->date_add = $payment['date_add'];

					if ($paymentDetail->add()) {
						return true;
					} else {
						return false;
					}

				} else {
					return false;
				}

			}

		} else {

			$piecePayment = new Payment();
			$piecePayment->id_currency = $order->id_currency;
			$piecePayment->amount = $order->total_paid;
			$piecePayment->id_payment_mode = $piece->id_payment_mode;
			$piecePayment->payment_method = $order->payment;
			$piecePayment->conversion_rate = $order->conversion_rate;
			$piecePayment->booked = 0;
			$piecePayment->date_add = $order->date_add;

			if ($piecePayment->add()) {
				$paymentDetail = new PaymentDetails();
				$paymentDetail->id_payment = $piecePayment->id;
				$paymentDetail->id_customer_piece = $piece->id;
				$paymentDetail->amount = $order->total_paid;
				$paymentDetail->date_add = $order->date_add;

				if ($paymentDetail->add()) {
					return true;
				} else {
					return false;
				}

			} else {
				return false;
			}

		}

		return true;
	}

	public static function getInvoicesbyidSession($idSession) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('id_student_piece')
				->from('student_pieces')
				->where('`id_education_session` = ' . (int) $idSession)
		);
	}

	public static function getTotalbyrange($dateStart, $dateEnd) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('SUM(`education_tax_excl`) as totalHT, SUM(`education_tax_incl`) as totalTTC')
				->from('student_pieces')
				->where('`date_add` >= \'' . $dateStart . '\'')
				->where('`date_add` <= \'' . $dateEnd . '\'')
		);
	}

	public static function getTotalbyidSession($idSession) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('SUM(`education_tax_excl`) as totalHT, SUM(`education_tax_incl`) as totalTTC')
				->from('student_pieces')
				->where('`id_education_session` = ' . $idSession)
		);
	}

}
