<?php

/**
 * @since 2.1.0.0
 */
class CustomerPiecesCore extends ObjectModel {

    const ROUND_ITEM = 1;
    const ROUND_LINE = 2;
    const ROUND_TOTAL = 3;
	
    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'customer_pieces',
        'primary' => 'id_customer_piece',
        'fields'  => [
            'id_piece_origine'            => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'piece_type'               => ['type' => self::TYPE_STRING, 'validate' => 'isMessage'],
            'id_shop_group'            => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_shop'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_lang'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_currency'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_order'                 => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_customer'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_student'              => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'is_education'              => ['type' => self::TYPE_BOOL],
			'id_student_education'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_session'        => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'base_tax_excl'  		   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_products_tax_excl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_products_tax_incl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_shipping_tax_excl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_shipping_tax_incl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_with_freight_tax_excl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'shipping_no_subject'      => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'shipping_tax_subject'     => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'discount_rate'            => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_discounts_tax_excl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_discounts_tax_incl' => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_wrapping_tax_excl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'total_wrapping_tax_incl'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_tax_excl'  			   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_tax'  			   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'total_tax_incl'  			   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
			'piece_margin'			   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'id_payment_mode'          => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'module'          		   => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
            'total_paid'               => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice'],
            'id_carrier'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_address_delivery'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'id_address_invoice'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'conversion_rate'          => ['type' => self::TYPE_FLOAT, 'validate' => 'isFloat'],
            'shipping_number'          => ['type' => self::TYPE_STRING, 'validate' => 'isTrackingNumber'],
			'last_transfert'           => ['type' => self::TYPE_INT],
            'round_mode'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'round_type'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'piece_number'             => ['type' => self::TYPE_INT],
            'delivery_number'          => ['type' => self::TYPE_INT],
            'validate'                 => ['type' => self::TYPE_BOOL],
			'observation'              => ['type' => self::TYPE_STRING, 'validate' => 'isMessage'],
			'note'                     => ['type' => self::TYPE_STRING, 'validate' => 'isMessage'],
			"is_book"                     => ['type' => self::TYPE_BOOL],
			'id_book_record'              => ['type' => self::TYPE_INT],
            'date_add'                 => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'deadline_date'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'                 => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

        ],
    ];

	public $id_piece_origine;
    public $piece_type;
    public $id_shop_group;
    public $id_shop;
    public $id_lang;
    public $id_currency;
    public $id_order;
    public $id_customer;
	public $id_student;
	public $is_education;
	public $id_education_session;
	public $id_student_education;
	public $base_tax_excl;
    public $total_products_tax_excl;
    public $total_products_tax_incl;
    public $total_shipping_tax_excl;
    public $total_shipping_tax_incl;
	public $shipping_no_subject;
	public $shipping_tax_subject;
	public $total_with_freight_tax_excl;
	public $discount_rate;
    public $total_discounts_tax_excl;
    public $total_discounts_tax_incl;
    public $total_wrapping_tax_excl;
    public $total_wrapping_tax_incl;
	public $total_tax_excl;
	public $total_tax;
	public $total_tax_incl;
	public $piece_margin;
    public $id_payment_mode;
	public $module;
    public $total_paid;
    public $id_carrier;
    public $id_address_delivery;
    public $id_address_invoice;
    public $conversion_rate;
    public $shipping_number;
    public $round_mode;
    public $round_type;
    public $piece_number;
    public $delivery_number;
	public $last_transfert;
    public $validate;
	public $observation;
	public $note;
	public $is_book;
	public $id_book_record;
    public $date_add;
	public $deadline_date;
    public $date_upd;

    public $prefix;
	public $nameType;
	public $pieceOrigin;
	public $payment_mode;
	
	
	public $balance_due;
	
	
    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);

        if ($this->id) {
            $this->prefix = $this->getPrefix();
			$this->nameType = $this->getTypeName();
			$this->pieceOrigin = $this->getPieceOrigin();
			$this->balance_due = $this->getBalanceDue();
			$this->payment_mode = PaymentMode::getPaymentModeNameById($this->id_payment_mode);
        }

    }
	
	public function getBalanceDue() {
		
		return $this->total_tax_incl - $this->total_paid;
	}
	
	public function getPieceOrigin() {
		
		if($this->id_piece_origine > 0) {
			$piece = new CustomerPieces($this->id_piece_origine);
			return Translate::getClassTranslation($piece->nameType, 'CustomerPieces').' '.Translate::getClassTranslation($piece->prefix, 'CustomerPieces').$piece->piece_number;
		}
	}
	
	public function getDiscountPercent() {
		return (1- ($this->total_products_tax_excl/($this->total_products_tax_excl - $this->total_discounts_tax_excl)))*100;
	}

    public function getPrefix() {

        switch ($this->piece_type) {

        case 'QUOTATION':
            return $this->l('DE');
            break;
        case 'ORDER':
            return $this->l('CD');
            break;
        case 'DELIVERYFORM':
            return $this->l('BL');
            break;
        case 'DOWNPINVOICE':
            return $this->l('FAA');
            break;
        case 'INVOICE':
            return $this->l('FA');
            break;
        case 'ASSET':
            return $this->l('DE');
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
        case 'DELIVERYFORM':
            return $this->l('Bon de livraison');
            break;
        case 'DOWNPINVOICE':
            return $this->l('Facture Accompte');
            break;
        case 'INVOICE':
            return $this->l('Facture');
            break;
        case 'ASSET':
            return $this->l('Avoir');
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

        return parent::add($autoDate, $nullValues);
    }

    public function update($nullValues = true) {

        $this->roundAmounts();

        return parent::update($nullValues);
    }
	
	public function delete() {

		if($this->piece_type == 'INVOICE' && $this->validate == 1) {
			return false;
		}
		
		$this->deletePieceDetatil();

		return parent::delete();
	}
	
	public function deletePieceDetatil() {
		
		if($this->getParentTransfert()) {
			return true;
		}
		Db::getInstance()->execute(
				'DELETE FROM `' . _DB_PREFIX_ . 'customer_piece_detail` 
                WHERE `id_customer_piece` = ' . (int) $this->id
		);
		
	}
	
	public function getParentTransfert() {
		
		$idParent = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
            ->select('id_customer_piece')
            ->from('customer_pieces')
            ->where('`last_transfert` = '.(int) $this->id)
        );
		if(!empty($idParent)) {
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
	
	public static function getInvoicesbyidSession($idSession) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('id_customer_piece')
				->from('customer_pieces')
				->where('`id_education_session` = ' . (int) $idSession)
		);
	}
	
	public static function getRequest($pieceType = null) {

        $context = Context::getContext();
		$query = new DbQuery();
		$query->select('a.*, (a.total_products_tax_incl+a.total_shipping_tax_incl+a.total_wrapping_tax_incl) as `total` , CONCAT(c.`firstname`, " ", c.`lastname`) AS `customer`, c.customer_code  as customer_code, c.company, pml.`name` AS `paymentMode`,
		(a.`total_products_tax_incl` + a.`total_shipping_tax_incl` + a.`total_wrapping_tax_incl` - a.`total_paid`) as `balanceDue`, case when a.validate = 1 then \'<div class="orderValidate"></div>\' else \'<div class="orderOpen"></div>\' end as validate, case when a.validate = 1 then 1 else 0 end as isLocked,  ca.`id_country`, ca.`address1`, ca.`address2`, ca.`postcode`, ca.`city`, cl.`name` AS country, case when a.is_book = 1 then \'<div class="orderBook"><i class="icon icon-book" aria-hidden="true"></i></div>\' else \'<div class="orderUnBook"><i class="icon icon-times" aria-hidden="true" style="color:red;"></i></div>\' end as booked, case when a.is_book = 1 then 1 else 0 end as isBooked');
        $query->from('customer_pieces', 'a');
        $query->leftJoin('customer', 'c', 'c.`id_customer` = a.`id_customer`');
        $query->leftJoin('payment_mode_lang', 'pml', 'pml.`id_payment_mode` = a.`id_payment_mode` AND pml.`id_lang` = ' . $context->language->id);
		$query->leftJoin('address', 'ca', 'a.`id_address_delivery` = ca.`id_address`');
		$query->leftJoin('country_lang', 'cl', 'cl.`id_country` = ca.`id_country` AND cl.`id_lang` = ' . $context->language->id);
		if($pieceType) {
			$query->where('a`piece_type` LIKE \''.$pieceType.'\'');
		}
        $query->orderBy('a.`date_add` DESC');
		
		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($query);
		
		return $orders;
       

    }
	
	public function generateNewInvoiceNumber() {
		
		
		if(empty($this->piece_number)) {
			$year = date('Y');
			$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				(new DbQuery())
				->select('`piece_number`')
				->from('customer_pieces')
				->where('`piece_type` LIKE \'INVOICE\'')
				->orderBy('`id_customer_piece` DESC')
			);
		
			if(empty($lastValue)) {
				$lastValue =1;
				return $year.sprintf("%06s", $lastValue);
			}
			$test = substr($lastValue, 0, 4);
			if($test == $year) {
				return $lastValue+1;
			} else {
				$lastValue =1;
				return $year.sprintf("%06s", $lastValue);
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

    public function deleteProduct(CustomerPieces $customerPiece, CustomerPieceDetail $customerPieceDetail, $quantity) {

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

        
		$collection = new PhenyxShopCollection('CustomerPieces');
        $collection->where('id_customer', '=', (int) $idCustomer);
		$collection->where('piece_type', 'like', 'INVOICE');
		foreach ($collection as $order) {
			$order->payment_mode = PaymentMode::getPaymentModeNameById($order->id_payment_mode);
			$order->balance_due = $order->total_tax_incl - $order->total_paid;
		}

        return $collection;
    }
	
	public static function getOrderTotalbyIdCustomer($idCustomer) {

        $total = 0;
		$collection = new PhenyxShopCollection('CustomerPieces');
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
		
		$sql = 'SELECT `AUTO_INCREMENT` FROM  INFORMATION_SCHEMA.TABLES WHERE TABLE_SCHEMA = \''._DB_NAME_.'\' AND   TABLE_NAME   = \''._DB_PREFIX_.'customer_pieces\'';
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->execute($sql);
	}
	
	public static function mergeCartTable(Order $order, $pieceNumber, $valid = null) {
		
		$cart = new Cart($order->id_cart);
		if (!Validate::isLoadedObject($carte)) {
			return null;
		}
		
		$piece = new CustomerPieces();
		$piece->id_piece_origine = 0;
		$piece->piece_type = 'ORDER';
		$piece->id_shop_group = $cart->id_shop_group;
		$piece->id_shop = $cart->id_shop;
		$piece->id_lang = $cart->id_lang;
		$piece->id_currency = $cart->id_currency;
		$piece->id_order = $order->id;
		$piece->id_customer = $cart->id_customer;
		$piece->base_tax_excl = $order->total_products + $order->total_discounts_tax_excl;
		$piece->total_products_tax_excl = $order->total_products;
		$piece->total_products_tax_incl = $order->total_products_wt;
		$piece->total_shipping_tax_excl = $order->total_shipping_tax_excl;
		$piece->shipping_tax_subject = $order->total_shipping_tax_excl;
		$piece->total_with_freight_tax_excl = $order->total_products+$order->total_shipping_tax_excl; 
		$piece->total_shipping_tax_incl = $order->total_shipping_tax_incl;
		$piece->total_discounts_tax_excl = $order->total_discounts_tax_excl;
		$piece->total_discounts_tax_incl = $order->total_discounts_tax_incl;
		$piece->total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
		$piece->total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
		$piece->total_tax_excl = $order->total_products+$order->total_shipping_tax_excl+$order->total_wrapping_tax_excl;
		$piece->total_tax_incl = $order->total_products_wt+$order->total_shipping_tax_incl+$order->total_wrapping_tax_incl;
		$piece->total_tax = $piece->total_tax_incl-$piece->total_tax_excl;
		$piece->id_payment_mode = CustomerPieces::getPaymentModeByModule($order->module);
		$piece->module = $order->payment;
		$piece->total_paid = $order->total_paid;
		$piece->id_carrier = $order->id_carrier;
		$piece->id_address_delivery = $order->id_address_delivery;
    	$piece->id_address_invoice = $order->id_address_invoice;
    	$piece->conversion_rate = $order->conversion_rate;
    	$piece->shipping_number = $order->shipping_number;
    	$piece->round_mode = $order->round_mode;
    	$piece->round_type = $order->round_type;
    	$piece->piece_number = $cart->id;
    	$piece->delivery_number = $order->delivery_number;
		$piece->last_transfert = $pieceNumber;
		$piece->validate = $valid;
		$piece->date_add = $cart->date_add;
    	$piece->deadline_date = $cart->date_add;
    	$piece->date_upd = $cart->date_upd;
		if($piece->add()) {
			return $piece->id;
		} else {
			return false;
		}
		
		
		
	}
	
	public static function getValidOrderState() {
		
		$validates = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
            ->select('id_order_state')
            ->from('order_state')
			->where('logable = 1 ')
        );
		$return = [];
		foreach($validates as $key => $value) {
			$return[] = $value['id_order_state'];
		}
		
		return implode(',',$return);
	}
	
	public static function getmergeOrderTable() {
		
		
		$context = Context::getContext();
		
		
		$syncOrder = [];
		$osValid = CustomerPieces::getValidOrderState();
				
		$orders = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
            ->select('*')
            ->from('orders')
			->where('current_state IN ('.$osValid.')')
            ->orderBy('`date_add` ASC')
        );
		
		$todo = 0;
		$done = 0;
		if(!empty($orders)) {
			foreach($orders as $order) {
			
				if(CustomerPieces::isMergeOrderTable($order['id_order'])) {
					$done = $done+1;
				} else {
					$syncOrder['todo'][] = $order['id_order'];
				}
			
			}
			if(isset($syncOrder['todo']) && is_array($syncOrder['todo'])) {
				$todo =  count($syncOrder['todo']);
			}
			$syncOrder['done'] = $done;
			$syncOrder['total'] = $todo + $done;
		} 
		
		return $syncOrder;
		
	}
	
	public static function mergeNewOrder($idOrder) {
		
		
		
		if(CustomerPieces::isMergeOrderTable($idOrder)) {
				return true;
		}
		
		$osValid = CustomerPieces::getValidOrderState();
		
		$order = new Order($idOrder);
		$valid = 0;
		if(in_array($order->current_state, $osValid)) {
			$valid = 1;
		}
		$pieceNumber = CustomerPieces::generateInvoiceNumber($order);
		
		$pieceCart = CustomerPieces::mergeCartTable($order, $pieceNumber, $valid);
		
		$piece = new CustomerPieces();
		$piece->id_piece_origine = $pieceCart;
		$piece->piece_type = 'INVOICE';
		$piece->id_shop_group = $order->id_shop_group;
		$piece->id_shop = $order->id_shop;
		$piece->id_lang = $order->id_lang;
		$piece->id_currency = $order->id_currency;
		$piece->id_order = $order->id;
		$piece->id_customer = $order->id_customer;
		$piece->base_tax_excl = $order->total_products + $order->total_discounts_tax_excl;
		$piece->total_products_tax_excl = $order->total_products;
		$piece->total_products_tax_incl = $order->total_products_wt;
		$piece->total_shipping_tax_excl = $order->total_shipping_tax_excl;
		$piece->shipping_tax_subject = $order->total_shipping_tax_excl;
		$piece->total_with_freight_tax_excl = $order->total_products+$order->total_shipping_tax_excl; 
		$piece->total_shipping_tax_incl = $order->total_shipping_tax_incl;
		$piece->total_discounts_tax_excl = $order->total_discounts_tax_excl;
		$piece->total_discounts_tax_incl = $order->total_discounts;
		$piece->total_wrapping_tax_excl = $order->total_wrapping_tax_excl;
		$piece->total_wrapping_tax_incl = $order->total_wrapping_tax_incl;
		$piece->total_tax_excl = $order->total_products+$order->total_shipping_tax_excl+$order->total_wrapping_tax_excl;
		$piece->total_tax_incl = $order->total_products_wt+$order->total_shipping_tax_incl+$order->total_wrapping_tax_incl;
		$piece->total_tax = $piece->total_tax_incl-$piece->total_tax_excl;
		$piece->id_payment_mode = (CustomerPieces::getPaymentModeByModule($order->module) ? CustomerPieces::getPaymentModeByModule($order->module) : 1);
		$piece->module = $order->payment;
		$piece->total_paid = $order->total_paid;
		$piece->id_carrier = $order->id_carrier;
		$piece->id_address_delivery = $order->id_address_delivery;
    	$piece->id_address_invoice = $order->id_address_invoice;
    	$piece->conversion_rate = $order->conversion_rate;
    	$piece->shipping_number = $order->shipping_number;
    	$piece->round_mode = $order->round_mode;
    	$piece->round_type = $order->round_type;
    	$piece->piece_number = $pieceNumber;
    	$piece->delivery_number = $order->delivery_number;
		$piece->last_transfert = 0;
		$piece->observation = $order->getFirstMessage();
		$piece->validate = $valid;
		$piece->deadline_date = $order->date_add;
		$piece->date_add = $order->date_add;
    	$piece->date_upd = $order->date_upd;
		if($piece->add()) {
			if(CustomerPieces::generatePieceDetail($order->id, $piece->id)) {
				if(CustomerPieces::generatePayment($order, $piece))
				return true;
			}
			return false;
		} else {
			return false;
		}
		
	}
	
	
	public static function mergeOrderTable($id_student_Education) {
		
		
		if(CustomerPieces::isMergeOrderTable($id_student_Education)) {
				return true;
		}
		
		$context = Context::getContext();
		
		$studentEducation = new StudentEducation($id_student_Education);
		if($studentEducation->id_education_attribute > 0) {
			$education = new Declinaison($studentEducation->id_education_attribute);
		} else {
			$education = new Education($studentEducation->id_education);
		}
		
		$dateEnd = str_replace('/', '-', $studentEducation->date_end);
		$formatpack = new FormatPack($studentEducation->id_formatpack);
		$piece_cost = $formatpack->price;
		
		$margin =  $studentEducation->price -$piece_cost;
		if($margin <0) {
			$margin =0;
		}
		$date = new DateTime(date('Y-m-d', strtotime($dateEnd)));
		$date->modify('+4 days');
		$dateAdd = $date->format('Y-m-d');
		
		$newPiece = new CustomerPieces();
		$newPiece->piece_type = 'INVOICE';
		$newPiece->is_education = 1;
		$newPiece->id_currency = 1;
		$newPiece->id_customer = $studentEducation->id_customer;
		$newPiece->id_education_session = $studentEducation->id_education_session;
		$newPiece->id_student_education = $studentEducation->id;
		$newPiece->id_payment_mode = 1;
		$newPiece->base_tax_excl = $studentEducation->price;
		$newPiece->total_products_tax_excl = $studentEducation->price;
		$newPiece->total_products_tax_incl = $studentEducation->priceWTax;
		$newPiece->total_with_freight_tax_excl = $studentEducation->price;
		$newPiece->total_tax_excl = $studentEducation->price;
		
		$newPiece->total_tax = $newPiece->total_products_tax_incl - $newPiece->total_products_tax_excl;
		$newPiece->total_tax_incl = $studentEducation->priceWTax;
		$newPiece->piece_margin = $margin;
		$newPiece->total_paid = 0;	
		$newPiece->conversion_rate = 1;
		$newPiece->validate = 0;	
		$newPiece->is_book = 0;	
		$newPiece->id_book_record = 0;	
	
		$newPiece->id_shop = (int) $context->shop->id;
    	$newPiece->id_shop_group = (int) $context->shop->id_shop_group;
    	$newPiece->id_lang = $context->language->id;
    	$newPiece->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
   	 	$newPiece->round_type = Configuration::get('PS_ROUND_TYPE');
    	$newPiece->date_add = $dateAdd;
		
		$result = $newPiece->add();
		
		if($result) {
		
			$object = new CustomerPieceDetail();
        	$object->id_customer_piece = $newPiece->id;
        	$object->id_warehouse = 0;
			$object->reference_edof = $studentEducation->reference_edof;	
			$object->id_education = $studentEducation->id_education;	
			$object->id_formatpack = $studentEducation->id_formatpack;
			$object->id_education_attribute = $studentEducation->id_education_attribute;	
			$object->product_name = $studentEducation->name;
			$object->product_quantity = 1;		
			$object->original_price_tax_excl = $newPiece->total_products_tax_excl;
			$object->original_price_tax_incl = $newPiece->total_products_tax_incl;		
			$object->unit_tax_excl = $newPiece->total_products_tax_excl;
			$object->unit_tax_incl = $newPiece->total_products_tax_incl;		
			$object->total_tax_excl = $newPiece->total_products_tax_excl;
			$object->total_tax_incl = $newPiece->total_products_tax_incl;	
			$object->tax_rate = 20;
			$object->total_tax = $newPiece->total_tax;		
			$object->product_reference = $education->reference;
			$object->product_wholesale_price = $formatpack->price;
			$result = $object->add();
			
			if ($result) {
				return true;
			}
		
		}
		
		return false;
		
	}
	
	public static function generateInvoiceNumber(Order $order) {
		
		
		$date = new dateTime($order->date_add);
		$year =  $date->format('Y');
		$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
            ->select('`piece_number`')
            ->from('customer_pieces')
			->where('`piece_type` LIKE \'INVOICE\'')
            ->orderBy('`id_customer_piece` DESC')
        );
		
		if(empty($lastValue)) {
			$lastValue =1;
			return $year.sprintf("%06s", $lastValue);
		}
		$test = substr($lastValue, 0, 4);
		if($test == $year) {
			return $lastValue+1;
		} else {
			$lastValue =1;
			return $year.sprintf("%06s", $lastValue);
		}
		
	}
	
	public static function isMergeOrderTable($idOrder) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_customer_piece`')
                ->from('customer_pieces')
			   ->where('`piece_type` LIKE \'INVOICE\'')
                ->where('`id_order` = '.(int)$idOrder)
        );
	}
	
	public static function getIncrementByType($type) {
		
		
		
		$year = date('Y');
		$lastValue = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
            ->select('`piece_number`')
            ->from('customer_pieces')
			->where('`piece_type` LIKE \''.$type.'\'')
            ->orderBy('`id_customer_piece` DESC')
        );
		
		if(empty($lastValue)) {
			$lastValue =1;
			return $year.sprintf("%06s", $lastValue);
		}
		$test = substr($lastValue, 0, 4);
		if($test == $year) {
			return $lastValue+1;
		} else {
			$lastValue =1;
			return $year.sprintf("%06s", $lastValue);
		}
		
	}
	
	public static function getPaymentModeByModule($moduleName) {
		
		$module = Module::getInstanceByName($moduleName);
            if ($module instanceof Module) {
                return PaymentMode::getPaymentModeByModuleId($module->id, $moduleName);
            }
	}
	
	public static function generatePieceDetail($idOrder, $pieceCart) {
		
		$orderDeatils = CustomerPieces::getProductsOrderDetail($idOrder);
		if(!empty($orderDeatils)) {
			$error = false;
			foreach($orderDeatils as $detail) {
				
				$pieceDetail = new CustomerPieceDetail();
				$pieceDetail->id_customer_piece = $pieceCart;
				$pieceDetail->id_product = $detail['product_id'];
    			$pieceDetail->id_product_attribute = $detail['product_attribute_id'];
    			$pieceDetail->product_name = $detail['product_name'];
    			$pieceDetail->product_quantity = $detail['product_quantity'];
				$pieceDetail->original_price_tax_excl = $detail['original_product_price'];
				$pieceDetail->original_price_tax_incl = $detail['original_product_price']*(1+$detail['rateTaxe']/100);
    			$pieceDetail->unit_tax_excl = $detail['unit_price_tax_excl'];
				$pieceDetail->unit_tax_incl = $detail['unit_price_tax_incl'];
    			$pieceDetail->total_tax_excl = $detail['total_price_tax_excl'];
				$pieceDetail->total_tax = $detail['total_line_tax'];
    			$pieceDetail->total_tax_incl = $detail['total_price_tax_incl'];
    			$pieceDetail->reduction_percent = $detail['reduction_percent'];
    			$pieceDetail->reduction_amount_tax_excl = $detail['reduction_amount_tax_excl'];
    			$pieceDetail->reduction_amount_tax_incl = $detail['reduction_amount_tax_incl'];
    			$pieceDetail->product_ean13 = $detail['product_ean13'];
    			$pieceDetail->product_upc = $detail['product_upc'];
    			$pieceDetail->product_reference = $detail['product_reference'];
    			$pieceDetail->product_weight = $detail['product_weight'];
    			$pieceDetail->ecotax = $detail['ecotax'];
    			$pieceDetail->download_hash = $detail['download_hash'];
    			$pieceDetail->download_nb = $detail['download_nb'];
    			$pieceDetail->download_deadline = $detail['download_deadline'];
    			$pieceDetail->tax_rate = $detail['rateTaxe'];
    			$pieceDetail->id_tax_rules_group = $detail['id_tax'];
    			$pieceDetail->id_warehouse = $detail['id_warehouse'];
    			$pieceDetail->product_wholesale_price = $detail['original_wholesale_price'];
				if(!$pieceDetail->add()) {
					$error = true;
				}
			}
			if($error) {
				return false;
			}
			return true;
			
		}
		return true;
		
	}
	
	public static function getProductsOrderDetail($idOrder) {
		
       	return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
            ->select('od.*, odt.`id_tax`, odt.`total_amount` as `total_line_tax`, t.`rate` as `rateTaxe`')
            ->from('order_detail', 'od')
			->leftjoin('order_detail_tax', 'odt', 'odt.`id_order_detail` = od.`id_order_detail`')
			->leftjoin('tax', 't', 't.`id_tax` = odt.`id_tax`')
            ->where('od.`id_order` = '.(int) $idOrder)
        );
    }
	
	public static function getPieceIdbyTransfert($lastTransfert) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
            ->select('id_customer_piece')
            ->from('customer_pieces')
            ->where('`piece_number` = '.(int) $lastTransfert)
        );
	}
	
	public static function generatePayment(Order $order, CustomerPieces $piece) {
		
		
		$payments = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select('*')
                    ->from('order_payment')
                    ->where('`order_reference` = \''.pSQL($order->reference).'\'')
            );
		
		if(is_array($payments) && sizeof($payments)) {
			
			foreach($payments as $payment) {
			
				$piecePayment = new Payment();
				$piecePayment->id_currency = $payment['id_currency'];
    			$piecePayment->amount = $payment['amount'];
				$piecePayment->id_payment_mode = $piece->id_payment_mode;
    			$piecePayment->payment_method = $payment['payment_method'];
				$piecePayment->conversion_rate = $payment['conversion_rate'];
    			$piecePayment->booked = 0;
				$piecePayment->date_add = $payment['date_add'];
				if($piecePayment->add()) {
					$paymentDetail = new PaymentDetails();
					$paymentDetail->id_payment = $piecePayment->id;
    				$paymentDetail->id_customer_piece = $piece->id;
    				$paymentDetail->amount = $payment['amount'];
    				$paymentDetail->date_add = $payment['date_add'];
					if($paymentDetail->add()) {
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
			
			if($piecePayment->add()) {
				$paymentDetail = new PaymentDetails();
				$paymentDetail->id_payment = $piecePayment->id;
    			$paymentDetail->id_customer_piece = $piece->id;
    			$paymentDetail->amount = $order->total_paid;
    			$paymentDetail->date_add = $order->date_add;
				if($paymentDetail->add()) {
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
	
	public static  function generatePhenyxInvoices() {

		$context = Context::getContext();
		$educationPCSupply = new FormatPackSupplies(1);
		$educationIpadSupply = new FormatPackSupplies(2);
		$educationTabletteSupply = new FormatPackSupplies(3);
		$licenses = License::getLiceneCollection();
		$invoices = [];

		foreach ($licenses as $licence) {
			$invoices[$licence->id] = $licence->getPhenyxInvoices();
		}

		foreach ($invoices as $of => $sessions) {

			if (is_array($sessions) && count($sessions)) {
				$license = new License($of);
				$customer = new Customer($license->id_customer);
				$idAddress = Address::getFirstCustomerAddressId($customer->id);

				foreach ($sessions as $session => $details) {
					$piceCost1 = 0;
					$piceCost2 = 0;
					$piceCost3 = 0;
					$piceCost4 = 0;
					$totalPack1 = 0;
					$totalPack2 = 0;
					$totalPack3 = 0;
					$totalPack4 = 0;
					$totalPack5 = 0;
					$pack1 = 0;
					$pack2 = 0;
					$pack3 = 0;
					$pack4 = 0;
					$pack5 = 0;
					$supply1 = 0;
					$supply2 = 0;
					$supply3 = 0;
					$piceCost = 0;

					foreach ($details as $key => $detail) {
						$formatpack = new FormatPack($detail['id_formatpack']);
						if($formatpack->id ==1) {
							$totalPack1 = $totalPack1 + $formatpack->price;
							$pack1 ++;
							if($detail['id_education_supplies'] == 1) {
								$piceCost1 = $piceCost1 + $educationPCSupply->pamp;
								$supply1--;
							}
							if($detail['id_education_supplies'] == 2) {
								$piceCost1 = $piceCost1 + $educationIpadSupply->pamp;
								$supply2--;
							}
							if($detail['id_education_supplies'] == 6) {
								$piceCost1 = $piceCost1 + $educationTabletteSupply->pamp;
								$supply3--;
							}
						} else if($formatpack->id == 2) {
							$totalPack2 = $totalPack2 + $formatpack->price;
							$pack2++;
							if($detail['id_education_supplies'] == 1) {
								$piceCost2 = $piceCost2 + $educationPCSupply->pamp;
								$supply1--;
							}
							if($detail['id_education_supplies'] == 2) {
								$piceCost2 = $piceCost2 + $educationIpadSupply->pamp;
								$supply2--;
							}
							if($detail['id_education_supplies'] == 6) {
								$piceCost2 = $piceCost2 + $educationTabletteSupply->pamp;
								$supply3--;
							}
						} else if($formatpack->id == 3){
							$piceCost3 = $piceCost3 +80;
							$totalPack3 = $totalPack3 + $formatpack->price;
							$pack3++;
						} else if($formatpack->id == 4){
							$totalPack4 = $totalPack4 + $formatpack->price;
							$pack4++;
						} else if($formatpack->id == 5){
							$totalPack5 = $totalPack5 + $formatpack->price;
							$pack5++;
						}
						
						

						
						$id_education_session = $detail['id_education_session'];
						$session_date = $detail['session_date'];
					}
					
					$supply = new FormatPackSupplies(1);
					$supply->stock = $supply->stock + (int)$supply1;
					$supply->sold = $supply->sold -(int)$supply1;
					$supply->update();
				
					$supply = new FormatPackSupplies(2);
					$supply->stock = $supply->stock + (int)$supply2;
					$supply->sold = $supply->sold -(int)$supply2;
					$supply->update();
					$supply = new FormatPackSupplies(3);
					$supply->stock = $supply->stock + (int)$supply3;
					$supply->sold = $supply->sold -(int)$supply3;
					$supply->update();
					
					$total = $totalPack1 + $totalPack2 + $totalPack3;

					$newPiece = new CustomerPieces();
					$newPiece->piece_type = 'INVOICE';
					$newPiece->note = $session;
					$newPiece->is_education = 0;
					$newPiece->id_currency = 1;
					$newPiece->id_customer = $customer->id;
					$newPiece->id_address_delivery = $idAddress;
					$newPiece->id_address_invoice = $idAddress;
					$newPiece->id_education_session = $id_education_session;
					$newPiece->id_student_education = 0;
					$newPiece->id_payment_mode = 1;
					$newPiece->base_tax_excl = $total;
					$newPiece->total_products_tax_excl = $total;
					$newPiece->total_products_tax_incl = $total * 1.2;
					$newPiece->total_with_freight_tax_excl = $newPiece->total_products_tax_incl;
					$newPiece->total_tax_excl = $total;
					$newPiece->total_tax = $newPiece->total_products_tax_incl - $newPiece->total_products_tax_excl;
					$newPiece->total_tax_incl = $newPiece->total_products_tax_incl;
					$newPiece->piece_margin = $newPiece->total_products_tax_excl - $piceCost1 -$piceCost2 - $piceCost3 - $piceCost4;
					$newPiece->total_paid = 0;
					$newPiece->conversion_rate = 1;
					$newPiece->validate = 0;
					$newPiece->is_book = 0;
					$newPiece->id_book_record = 0;
					$newPiece->id_shop = (int) $context->shop->id;
					$newPiece->id_shop_group = (int) $context->shop->id_shop_group;
					$newPiece->id_lang = $context->language->id;
					$newPiece->round_mode = Configuration::get('PS_PRICE_ROUND_MODE');
					$newPiece->round_type = Configuration::get('PS_ROUND_TYPE');
					$newPiece->date_add = $session_date;
					$result = $newPiece->add();

					if ($result) {
						
						if($pack1 > 0) {
							
							$unitCost1 = round($piceCost1/$pack1, 3);
							$formatpack = new FormatPack(1);
							$object = new CustomerPieceDetail();
							$object->id_customer_piece = $newPiece->id;
							$object->id_warehouse = 0;
							$object->id_formatpack = $formatpack->id;
							$object->product_name = $formatpack->name[$context->language->id];
							$object->product_quantity = $pack1;
							$object->original_price_tax_excl = $formatpack->price;
							$object->original_price_tax_incl = $formatpack->price * 1.2;
							$object->unit_tax_excl = $formatpack->price;
							$object->unit_tax_incl = $object->original_price_tax_incl;
							$object->total_tax_excl = $object->original_price_tax_excl * $pack1;
							$object->total_tax_incl = $object->original_price_tax_incl *$pack1;
							$object->tax_rate = 20;
							$object->total_tax = $object->total_tax_incl - $object->total_tax_excl;
							$object->product_reference = $formatpack->reference;
							$object->product_wholesale_price = $unitCost1;
							$result = $object->add();
						}
						if($pack2 > 0) {
							$unitCost2 = round($piceCost2/$pack2,3);
							$formatpack = new FormatPack(2);
							$object = new CustomerPieceDetail();
							$object->id_customer_piece = $newPiece->id;
							$object->id_warehouse = 0;
							$object->id_formatpack = $formatpack->id;
							$object->product_name = $formatpack->name[$context->language->id];
							$object->product_quantity = $pack2;
							$object->original_price_tax_excl = $formatpack->price;
							$object->original_price_tax_incl = $formatpack->price * 1.2;
							$object->unit_tax_excl = $formatpack->price;
							$object->unit_tax_incl = $object->original_price_tax_incl;
							$object->total_tax_excl = $object->original_price_tax_excl * $pack2;
							$object->total_tax_incl = $object->original_price_tax_incl *$pack2;
							$object->tax_rate = 20;
							$object->total_tax = $object->total_tax_incl - $object->total_tax_excl;
							$object->product_reference = $formatpack->reference;
							$object->product_wholesale_price = $unitCost2;
							$result = $object->add();
						}
						if($pack3 > 0) {
							$unitCost3 = round($piceCost3/$pack3, 3);
							$formatpack = new FormatPack(3);
							$object = new CustomerPieceDetail();
							$object->id_customer_piece = $newPiece->id;
							$object->id_warehouse = 0;
							$object->id_formatpack = $formatpack->id;
							$object->product_name = $formatpack->name[$context->language->id];
							$object->product_quantity = $pack3;
							$object->original_price_tax_excl = $formatpack->price;
							$object->original_price_tax_incl = $formatpack->price * 1.2;
							$object->unit_tax_excl = $formatpack->price;
							$object->unit_tax_incl = $object->original_price_tax_incl;
							$object->total_tax_excl = $object->original_price_tax_excl * $pack3;
							$object->total_tax_incl = $object->original_price_tax_incl *$pack3;
							$object->tax_rate = 20;
							$object->total_tax = $object->total_tax_incl - $object->total_tax_excl;
							$object->product_reference = $formatpack->reference;
							$object->product_wholesale_price = $unitCost3;
							$result = $object->add();
						}
						
						if($pack4 > 0) {
							$formatpack = new FormatPack(4);
							$object = new CustomerPieceDetail();
							$object->id_customer_piece = $newPiece->id;
							$object->id_warehouse = 0;
							$object->id_formatpack = $formatpack->id;
							$object->product_name = $formatpack->name[$context->language->id];
							$object->product_quantity = $pack4;
							$object->original_price_tax_excl = $formatpack->price;
							$object->original_price_tax_incl = $formatpack->price * 1.2;
							$object->unit_tax_excl = $formatpack->price;
							$object->unit_tax_incl = $object->original_price_tax_incl;
							$object->total_tax_excl = $object->original_price_tax_excl * $pack4;
							$object->total_tax_incl = $object->original_price_tax_incl *$pack4;
							$object->tax_rate = 20;
							$object->total_tax = $object->total_tax_incl - $object->total_tax_excl;
							$object->product_reference = $formatpack->reference;
							$result = $object->add();
						}
						if($pack5 > 0) {
							$formatpack = new FormatPack(5);
							$object = new CustomerPieceDetail();
							$object->id_customer_piece = $newPiece->id;
							$object->id_warehouse = 0;
							$object->id_formatpack = $formatpack->id;
							$object->product_name = $formatpack->name[$context->language->id];
							$object->product_quantity = $pack5;
							$object->original_price_tax_excl = $formatpack->price;
							$object->original_price_tax_incl = $formatpack->price * 1.2;
							$object->unit_tax_excl = $formatpack->price;
							$object->unit_tax_incl = $object->original_price_tax_incl;
							$object->total_tax_excl = $object->original_price_tax_excl * $pack5;
							$object->total_tax_incl = $object->original_price_tax_incl *$pack5;
							$object->tax_rate = 20;
							$object->total_tax = $object->total_tax_incl - $object->total_tax_excl;
							$object->product_reference = $formatpack->reference;
							$result = $object->add();
						}

					}

				}

			}

		}

		return true;

	}
	
	public static function getPhenyxSupplies() {
		
		$licenses = License::getLiceneCollection();
		$invoices = [];

		foreach ($licenses as $licence) {
			$invoices[$licence->id] = $licence->getPhenyxSupplies();
		}
		
		$supply1 = 0;
		$supply2 = 0;
		$supply3 = 0;
		foreach ($invoices as $of => $sessions) {
			
			foreach ($sessions as $session => $details) {
				
				
				
				foreach ($details as $key => $detail) {
					if($detail['id_education_supplies'] == 1) {
						$supply1++;
					}
					if($detail['id_education_supplies'] == 2) {
						$supply2++;
					}
					if($detail['id_education_supplies'] == 6) {
						$supply3++;
					}

				}
				
			}
			
			
		}
		$supply = new FormatPackSupplies(1);
		$supply->stock_previsionnel = (int)$supply1;
		$supply->update();
				
		$supply = new FormatPackSupplies(2);
		$supply->stock_previsionnel =  (int)$supply2;
		$supply->update();
		$supply = new FormatPackSupplies(3);
		$supply->stock_previsionnel = (int)$supply3;
		$supply->update();
		
		return true;
		
	}


}
