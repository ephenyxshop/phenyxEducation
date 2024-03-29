<?php


/**
 * Represents the products kept in warehouses
 *
 * @since 1.9.1.0
 */
class StockCore extends PhenyxObjectModel
{
    // @codingStandardsIgnoreStart
    /** @var int identifier of the warehouse */
    public $id_warehouse;
    /** @var int identifier of the product */
    public $id_product;
    /** @var int identifier of the product attribute if necessary */
    public $id_product_attribute;
    /** @var string Product reference */
    public $reference;
    /** @var int Product EAN13 */
    public $ean13;
    /** @var string UPC */
    public $upc;
    /** @var int the physical quantity in stock for the current product in the current warehouse */
    public $physical_quantity;
    /** @var int the usable quantity (for sale) of the current physical quantity */
    public $usable_quantity;
    /** @var int the unit price without tax forthe current product */
    public $price_te;
    // @codingStandardsIgnoreEnd

    /**
     * @see PhenyxObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'stock',
        'primary' => 'id_stock',
        'fields'  => [
            'id_warehouse'         => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true ],
            'id_product'           => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true ],
            'id_product_attribute' => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedId', 'required' => true ],
            'reference'            => ['type' => self::TYPE_STRING, 'validate' => 'isReference'                      ],
            'ean13'                => ['type' => self::TYPE_STRING, 'validate' => 'isEan13'                          ],
            'upc'                  => ['type' => self::TYPE_STRING, 'validate' => 'isUpc'                            ],
            'physical_quantity'    => ['type' => self::TYPE_INT,    'validate' => 'isUnsignedInt', 'required' => true],
            'usable_quantity'      => ['type' => self::TYPE_INT,    'validate' => 'isInt',         'required' => true],
            'price_te'             => ['type' => self::TYPE_FLOAT,  'validate' => 'isPrice',       'required' => true],
        ],
    ];

    /**
     * @see PhenyxObjectModel::$webserviceParameters
     */
    protected $webserviceParameters = [
        'fields'        => [
            'id_warehouse'         => ['xlink_resource' => 'warehouses'                          ],
            'id_product'           => ['xlink_resource' => 'products'                            ],
            'id_product_attribute' => ['xlink_resource' => 'combinations'                        ],
            'real_quantity'        => ['getter'         => 'getWsRealQuantity', 'setter' => false],
        ],
        'hidden_fields' => [],
    ];

    /**
     * @see     PhenyxObjectModel::update()
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     * @param bool $nullValues
     *
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function update($nullValues = false)
    {
        $this->getProductInformations();

        return parent::update($nullValues);
    }

    /**
     * @see     PhenyxObjectModel::add()
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     *
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function add($autoDate = true, $nullValues = false)
    {
        $this->getProductInformations();

        return parent::add($autoDate, $nullValues);
    }

    /**
     * Webservice : used to get the real quantity of a product
     *
     * @return int
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getWsRealQuantity()
    {
        $manager = StockManagerFactory::getManager();
        $quantity = $manager->getProductRealQuantities($this->id_product, $this->id_product_attribute, $this->id_warehouse, true);

        return $quantity;
    }

    /**
     * @param int|null $idProduct
     * @param int|null $idProductAttribute
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function deleteStockByIds($idProduct = null, $idProductAttribute = null)
    {
        if (!$idProduct || !$idProductAttribute) {
            return false;
        }

        return Db::getInstance()->delete('stock', '`id_product` = '.(int) $idProduct.' AND `id_product_attribute` = '.(int) $idProductAttribute);
    }

    /**
     * @param int $idProduct
     * @param int $idProductAttribute
     * @param int $idWarehouse
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function productIsPresentInStock($idProduct = 0, $idProductAttribute = 0, $idWarehouse = 0)
    {
        if (!(int) $idProduct && !is_int($idProductAttribute) && !(int) $idWarehouse) {
            return false;
        }

        $result = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_stock`')
                ->from('stock')
                ->where('`id_warehouse` = '.(int) $idWarehouse)
                ->where('`id_product` = '.(int) $idProduct)
                ->where((int) $idProductAttribute ? '`id_product_attribute` = '.$idProductAttribute : '')
        );

        return (is_array($result) && !empty($result) ? true : false);
    }

    /**
     * Gets reference, ean13 and upc of the current product
     * Stores it in stock for stock_mvt integrity and history purposes
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function getProductInformations()
    {
        // if combinations
        if ((int) $this->id_product_attribute > 0) {
            $rows = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select('reference, ean13, upc')
                    ->from('product_attribute')
                    ->where('id_product = '.(int) $this->id_product)
                    ->where('id_product_attribute = '.(int) $this->id_product_attribute)
            );

            if (!is_array($rows)) {
                return;
            }

            foreach ($rows as $row) {
                $this->reference = $row['reference'];
                $this->ean13 = $row['ean13'];
                $this->upc = $row['upc'];
            }
        } else {
            // else, simple product

            $product = new Product((int) $this->id_product);
            if (Validate::isLoadedObject($product)) {
                $this->reference = $product->reference;
                $this->ean13 = $product->ean13;
                $this->upc = $product->upc;
            }
        }
    }
}
