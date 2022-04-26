<?php

class EducationSuppliesCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_supplies',
		'primary' => 'id_education_supplies',
		'fields'  => [
			'name'               => ['type' => self::TYPE_STRING, 'size' => 32],
			'svg_file'           => ['type' => self::TYPE_NOTHING],
			'viewbox'           => ['type' => self::TYPE_STRING],			
			'is_furniture'       => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
		],
	];

	public $name;
	public $svg_file;
	public $viewbox;
	
	public $is_furniture;

	protected static $_prices = [];
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

		

	}

	

	public static function getEducationSupplies() {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_supplies`, `name`, `svg_file`, `viewbox`')
				->from('education_supplies')
				->where('is_furniture = 1')
		);
	}

	public static function getSupplies() {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_supplies`, `name`, `svg_file`')
				->from('education_supplies')
		);
	}

	public static function getPriceStatic(
		$idSupply,
		$usetax = true
	) {

		$context = Context::getContext();

		if (!Validate::isBool($usetax) || !Validate::isUnsignedId($idSupply)) {
			die(Tools::displayError());
		}

		$return = EducationSupplies::priceCalculation(
			$idSupply,
			$usetax
		);

		return $return;
	}

	public static function getAveragePricebySypply($supplyName) {

		$data = Db::getInstance()->getRow(
			(new DbQuery())
				->select('*')
				->from('education_supplies')
				->where('`name` LIKE \'' . $supplyName . '\'')
		);

		$price = $data['wholesale_price'] + $data['average_freight'] + $data['box_cost'];

		return $price;
	}

	public static function priceCalculation($idSupply, $useTax = true) {

		$context = Context::getContext();

		$sql = new DbQuery();
		$sql->select('`wholesale_price`');
		$sql->from('education_supplies');
		$sql->where('`id_education_supplies` = ' . (int) $idSupply);

		$price = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);

		$price = Tools::convertPrice($price, $context->currency->id);

		$address = new Address();

		// Tax
		$address->id_country = 8;
		$address->id_state = '';
		$address->postcode = '';

		$taxManager = TaxManagerFactory::getManager($address, EducationSupplies::getIdTaxRulesGroupByIdEducation((int) $idSupply, $context));
		$educationTaxCalculator = $taxManager->getTaxCalculator();

		// Add Tax

		if ($useTax) {
			$price = $educationTaxCalculator->addTaxes($price);
		}

		$price = Tools::ps_round($price, 2);

		if ($price < 0) {
			$price = 0;
		}

		static::$_prices[$cacheId] = $price;

		return static::$_prices[$cacheId];
	}

	public static function getIdTaxRulesGroupByIdEducation($idSupply, Context $context = null) {

		if (!$context) {
			$context = Context::getContext();
		}

		$key = 'education_supplies_id_tax_rules_group_' . (int) $idSupply;

		if (!Cache::isStored($key)) {
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				'
                            SELECT `id_tax_rules_group`
                            FROM `' . _DB_PREFIX_ . 'education_supplies`
                            WHERE `id_education_supplies` = ' . (int) $idSupply
			);
			Cache::store($key, (int) $result);

			return (int) $result;
		}

		return Cache::retrieve($key);
	}

	public function getIdTaxRulesGroup() {

		return $this->id_tax_rules_group;
	}

}
