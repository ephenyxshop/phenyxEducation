<?php

/**
 * Class CustomerCore
 *
 * @since 2.1.0.0
 */
class EducationCore extends ObjectModel {

	const CUSTOMIZE_FILE = 0;
	const CUSTOMIZE_TEXTFIELD = 1;
	/**
	 * Note:  prefix is "PTYPE" because TYPE_ is used in ObjectModel (definition)
	 */
	const PTYPE_SIMPLE = 0;
	const PTYPE_PACK = 1;
	const PTYPE_VIRTUAL = 2;

	public static $_taxCalculationMethod = null;
	protected static $_prices = [];
	protected static $_pricesLevel2 = [];
	protected static $_incat = [];

	protected static $producPropertiesCache = [];

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'     => 'education',
		'primary'   => 'id_education',
		'multilang' => true,
		'fields'    => [
			'id_education_type'       => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_prerequis'  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_platform'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_level'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_manufacturer'         => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_supplier'             => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_certification'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'reference'               => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'supplier_reference'      => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'price'                   => ['type' => self::TYPE_FLOAT, 'validate' => 'isPrice', 'required' => true],
			'wholesale_price'           => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice'],
			'id_formatpack'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'days'                    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_tax_rules_group'      => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'hours'                   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'education_link'          => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
			'id_plateforme'           => ['type' => self::TYPE_STRING],
			'active'                  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'cache_is_pack'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'cache_has_attachments'   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'versionProgram'          => ['type' => self::TYPE_STRING, 'size' => 64],
			'cache_default_attribute' => ['type' => self::TYPE_INT],
			'date_add'                => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'date_upd'                => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'id_education_redirected' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'product_color'			  => ['type' => self::TYPE_STRING, 'size' => 32],
			'is_edof'                    => ['type' => self::TYPE_BOOL],
			'has_formapack'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

			'meta_description'        => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 300],
			'meta_keywords'           => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
			'meta_title'              => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128],
			'link_rewrite'            => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'size' => 128],
			'name'                    => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'required' => true, 'size' => 128],
			'description'             => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
			'description_short'       => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
			'programme'       		  => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],

		],
	];

	public $id_education_type;
	public $id_education_prerequis;
	public $id_platform;
	public $id_education_level;
	public $id_manufacturer;
	public $id_supplier;
	public $id_certification;
	public $reference;
	public $supplier_reference;
	public $price;
	public $wholesale_price;
	public $days;
	public $hours;
	public $education_link;
	public $id_plateforme;
	public $active;
	public $versionProgram;
	public $cache_is_pack;
	public $cache_has_attachments;
	public $product_color;
	public $is_edof;
	public $has_formapack;
	public $cache_default_attribute;
	public $date_add;
	public $date_upd;
	public $is_education;
	public $id_tax_rules_group = 1;
	public $id_education_redirected = 0;
	public $id_formatpack;
	public $name;
	public $description_short;
	public $description;
	public $programme;
	public $meta_description;
	public $meta_keywords;
	public $meta_title;
	public $link_rewrite;
	public $type;
	public $level;
	public $pack_stock_type = 3;
	// @codingStandardsIgnoreEnd
	public $tax_rate;
	public $packItems;
	public $isFullyLoaded = false;

	public $category;

	public $id_education_account;
	public $sell_account_local;
	public $sell_account_cee;
	public $sell_account_export;
	public $sell_account_notax;
	public $purchase_account_local;
	public $purchase_account_cee;
	public $purchase_account_import;
	public $purchase_account_notax;
	public $sell_local;
	public $sell_cee;
	public $sell_export;
	public $sell_notax;
	public $purchase_local;
	public $purchase_cee;
	public $purchase_import;
	public $purchase_notax;
	public $id_education_link;
	public $edof_link;
	public $reference_type;
	
	public $certification;
	
	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($idEducation = null, $full = false, $idLang = null, Context $context = null) {

		parent::__construct($idEducation, $idLang);

		if ($this->id) {
			$this->id_education_account = EducationAccount::getIdEducationAccount($this->id);
			$this->sell_account_local = EducationAccount::getEducationAccount($this->id, 'sell_account_local');
			$this->sell_account_cee = EducationAccount::getEducationAccount($this->id, 'sell_account_cee');
			$this->sell_account_export = EducationAccount::getEducationAccount($this->id, 'sell_account_export');
			$this->sell_account_notax = EducationAccount::getEducationAccount($this->id, 'sell_account_notax');
			$this->purchase_account_local = EducationAccount::getEducationAccount($this->id, 'purchase_account_local');
			$this->purchase_account_cee = EducationAccount::getEducationAccount($this->id, 'purchase_account_cee');
			$this->purchase_account_import = EducationAccount::getEducationAccount($this->id, 'purchase_account_import');
			$this->purchase_account_notax = EducationAccount::getEducationAccount($this->id, 'purchase_account_notax');
			$this->type = $this->getType();
			$this->edof_link = $this->getEdofLink();
			$this->id_education_link = $this->getIdEdofLink();
			$this->reference_type = $this->getReferenceType();
			$certification = new Certification($this->id_certification);
			$this->certification = $certification->name;

		} else {

			$this->sell_account_local = EducationAccount::getDefaultEducationAccount('sell_account_local');
			$this->sell_account_cee = EducationAccount::getDefaultEducationAccount('sell_account_cee');
			$this->sell_account_export = EducationAccount::getDefaultEducationAccount('sell_account_export');
			$this->sell_account_notax = EducationAccount::getDefaultEducationAccount('sell_account_notax');
			$this->purchase_account_local = EducationAccount::getDefaultEducationAccount('purchase_account_local');
			$this->purchase_account_cee = EducationAccount::getDefaultEducationAccount('purchase_account_cee');
			$this->purchase_account_import = EducationAccount::getDefaultEducationAccount('purchase_account_import');
			$this->purchase_account_notax = EducationAccount::getDefaultEducationAccount('purchase_account_notax');
		}

		if ($full && $this->id) {
			$this->isFullyLoaded = $full;
			$this->tax_rate = $this->getTaxesRate(new Address());

		}

		if ($this->id_education_type) {
			$this->category = EducationType::getLinkRewrite((int) $this->id_education_type, (int) $idLang);
		}

		$this->sell_local = $this->getAccount($this->sell_account_local);
		$this->sell_cee = $this->getAccount($this->sell_account_cee);
		$this->sell_export = $this->getAccount($this->sell_account_export);
		$this->sell_notax = $this->getAccount($this->sell_account_notax);
		$this->purchase_local = $this->getAccount($this->purchase_account_local);
		$this->purchase_cee = $this->getAccount($this->purchase_account_cee);
		$this->purchase_import = $this->getAccount($this->purchase_account_import);
		$this->purchase_notax = $this->getAccount($this->purchase_account_notax);

	}
	
	
	public static function getDaysEducation($idEducation, $idAttribute) {
		
		if($idAttribute > 0) {
			
			return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`days`')
				->from('education_attribute')
				->where('`id_education` = ' . $idEducation)
				->where('`id_education_attribute` = '.$idAttribute)
			);			
		}
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`days`')
				->from('education')
				->where('`id_education` = ' . $idEducation)
			);		
	}

	public function getReferenceType() {

		$educationType = new EducationType($this->id_education_type);
		return $educationType->reference;
	}

	public function getEdofLink() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`edof_link`')
				->from('education_link')
				->where('`id_education` = ' . $this->id)
				->where('`id_education_attribute` = 0')
		);
	}

	public function getIdEdofLink() {

		$idLink =  Db::getInstance()->getValue(
			(new DbQuery())
				->select('`id_education_link`')
				->from('education_link')
				->where('`id_education` = ' . $this->id)
				->where('`id_education_attribute` = 0')
		);
		
		if(empty($idLink)) {
			$idLink = 0;
		}
		
		return $idLink;
	}

	public function getTaxesRate(Address $address = null) {

		if (!$address || !$address->id_country) {
			$address = Address::initialize();
		}

		$taxManager = TaxManagerFactory::getManager($address, $this->id_tax_rules_group);
		$taxCalculator = $taxManager->getTaxCalculator();

		return $taxCalculator->getTotalRate();
	}

	public function getType() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('education_type_lang')
				->where('`id_lang` = ' . Context::getContext()->language->id)
				->where('`id_education_type` = ' . $this->id_education_type)
		);
	}

	public function getAccount($idAccount) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('account')
				->from('stdaccount')
				->where('`id_stdaccount` = ' . $idAccount)
		);

	}

	public function checkDefaultAttributes() {

		if (!$this->id) {
			return false;
		}

		if (Db::getInstance()->getValue(
			'SELECT COUNT(*)
                FROM `' . _DB_PREFIX_ . 'education_attribute` pa
                WHERE pa.`default_on` = 1
                AND pa.`id_education` = ' . (int) $this->id
		)
		) {
			Db::getInstance()->execute(
				'UPDATE ' . _DB_PREFIX_ . 'education_attribute pa
                    SET pa.default_on = NULL
                    WHERE pa.id_education=' . (int) $this->id
			);
		}

		$row = Db::getInstance()->getRow(
			'
            SELECT pa.id_education
            FROM `' . _DB_PREFIX_ . 'education_attribute` pa
            WHERE pa.`default_on` = 1
                AND pa.`id_education` = ' . (int) $this->id
		);

		if ($row) {
			return true;
		}

		$mini = Db::getInstance()->getRow(
			'
        SELECT MIN(pa.id_education_attribute) AS `id_attr`
        FROM `' . _DB_PREFIX_ . 'education_attribute` pa
            WHERE pa.`id_education` = ' . (int) $this->id
		);

		if (!$mini) {
			return false;
		}

		$sql = 'UPDATE `' . _DB_PREFIX_ . 'education_attribute` SET `default_on` = 1 WHERE `id_education_attribute` = ' . (int) $mini['id_attr'];

		if (!Db::getInstance()->execute($sql)) {
			return false;
		}

		return true;
	}

	public function getPrice(
		$tax = true,
		$idEducationAttribute = null,
		$decimals = 6,
		$divisor = null,
		$onlyReduc = false,
		$usereduc = true,
		$quantity = 1
	) {

		return Education::getPriceStatic((int) $this->id, $tax, $idEducationAttribute, $decimals, $divisor, $onlyReduc, $usereduc, $quantity);
	}

	public static function getDeclinaisonImageById($idEducationAttribute, $idLang) {

		if (!$idEducationAttribute) {
			return false;
		}

		$result = Db::getInstance()->executeS(
			'
            SELECT pai.`id_image`, pai.`id_education_attribute`, il.`legend`
            FROM `' . _DB_PREFIX_ . 'education_attribute_image` pai
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (il.`id_image_education` = pai.`id_image`)
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education` i ON (i.`id_image_education` = pai.`id_image`)
            WHERE pai.`id_education_attribute` = ' . (int) $idEducationAttribute . ' AND il.`id_lang` = ' . (int) $idLang . ' ORDER BY i.`position` LIMIT 1'
		);

		if (!$result) {
			return false;
		}

		return $result[0];
	}

	public function getEducationType() {

		if (!$this->id) {
			return 2;
		}

		if (EducationPack::isPack($this->id)) {
			return Education::PTYPE_PACK;
		}

		if ($this->is_education) {
			return Education::PTYPE_VIRTUAL;
		}

		return 2;
	}

	public function getLevel() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`name`')
				->from('education_level')
				->where('`id_education_level` = ' . $this->id_education_level)
		);
	}

	public function getAttributesGroups($idLang) {

		$sql = 'SELECT ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, agl.`public_name` AS public_group_name,
                    a.`id_attribute`, al.`name` AS attribute_name, a.`color` AS attribute_color, pa.`id_education_attribute`,
                   pa.`price`, pa.`default_on`, pa.`reference`, pa.`days`, pa.`hours`, pa.id_education_prerequis, pa.`versionProgram`, ag.`group_type`, pal.name as attributeName, pal.description, pal.description_short, pal.programme
                FROM `' . _DB_PREFIX_ . 'education_attribute` pa
				LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_lang` pal ON (pal.`id_education_attribute` = pa.`id_education_attribute` AND pal.id_lang = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON (pac.`id_education_attribute` = pa.`id_education_attribute`)
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON (a.`id_attribute` = pac.`id_attribute`)
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON (ag.`id_attribute_group` = a.`id_attribute_group`)
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute`)
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group`)
                WHERE pa.`id_education` = ' . (int) $this->id . '
                    AND al.`id_lang` = ' . (int) $idLang . '
                    AND agl.`id_lang` = ' . (int) $idLang . '
                GROUP BY id_attribute_group, id_education_attribute
                ORDER BY agl.`name` ASC, al.`id_attribute` ASC';

		return Db::getInstance()->executeS($sql);
	}

	public function add($autoDate = true, $nullValues = false) {

		if (!parent::add($autoDate, $nullValues)) {
			return false;
		}
		$account = new EducationAccount();
		$account->id_education = $this->id;
		$account->add();

		return true;
	}

	public function getEducationOption() {

		$return = [];
		$options = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_option`')
				->from('education_option', 'c')
				->where('`id_education` = ' . $this->id)
		);

		foreach ($options as $option) {
			$return[] = new EducationOption($option['id_education_option']);
		}

		return $return;
	}

	public static function getEducationsProperties($idLang, $queryResult) {

		$resultsArray = [];

		if (is_array($queryResult)) {

			foreach ($queryResult as $row) {

				if ($row2 = Education::getEducationProperties($idLang, $row)) {
					$resultsArray[] = $row2;
				}

			}

		}

		return $resultsArray;
	}

	public static function getEducationProperties($idLang, $row, Context $context = null) {

		if (!$row['id_education']) {
			return false;
		}

		if ($context == null) {
			$context = Context::getContext();
		}

		$idEducationAttribute = $row['id_education_attribute'] = (!empty($row['id_education_attribute']) ? (int) $row['id_education_attribute'] : null);

		if ($idEducationAttribute === null
			&& ((isset($row['cache_default_attribute']) && ($ipaDefault = $row['cache_default_attribute']) !== null)
				|| ($ipaDefault = Education::getDefaultAttribute($row['id_education'])))
		) {
			$idEducationAttribute = $row['id_education_attribute'] = $ipaDefault;
		}

		if (!isset($row['id_education_attribute'])) {
			$idEducationAttribute = $row['id_education_attribute'] = 0;
		}

		// Tax
		$usetax = Tax::excludeTaxeOption();

		$cacheKey = $row['id_education'] . '-' . $idEducationAttribute . '-' . $idLang . '-' . (int) $usetax;

		if (isset($row['id_education_pack'])) {
			$cacheKey .= '-pack' . $row['id_education_pack'];
		}

		if (isset(static::$educationPropertiesCache[$cacheKey])) {
			return array_merge($row, static::$educationPropertiesCache[$cacheKey]);
		}

		// Datas

		$row['link'] = $context->link->getEducationLink((int) $row['id_education']);

		$row['attribute_price'] = 0;

		if ($idEducationAttribute) {
			$row['attribute_price'] = (float) Education::getEducationAttributePrice($idEducationAttribute);
		}

		$row['price_tax_exc'] = Education::getPriceStatic(
			(int) $row['id_education'],
			false,
			$idEducationAttribute,
			(static::$_taxCalculationMethod == PS_TAX_EXC ? 2 : 6)
		);

		if (static::$_taxCalculationMethod == PS_TAX_EXC) {
			$row['price_tax_exc'] = Tools::ps_round($row['price_tax_exc'], 2);
			$row['price'] = Education::getPriceStatic(
				(int) $row['id_education'],
				true,
				$idEducationAttribute,
				6
			);
			$row['price_without_reduction'] = Education::getPriceStatic(
				(int) $row['id_education'],
				false,
				$idEducationAttribute,
				2,
				null,
				false,
				false
			);
		} else {
			$row['price'] = Tools::ps_round(
				Education::getPriceStatic(
					(int) $row['id_education'],
					true,
					$idEducationAttribute,
					6
				),
				(int) Configuration::get('PS_PRICE_DISPLAY_PRECISION')
			);
			$row['price_without_reduction'] = Education::getPriceStatic(
				(int) $row['id_education'],
				true,
				$idEducationAttribute,
				6,
				null,
				false,
				false
			);
		}

		$row['id_image_education'] = Education::defineEducationImage($row, $idLang);

		// Pack management
		$row['pack'] = (!isset($row['cache_is_pack']) ? EducationPack::isPack($row['id_education']) : (int) $row['cache_is_pack']);
		$row['packItems'] = $row['pack'] ? EducationPack::getItemTable($row['id_education'], $idLang) : [];
		$row['nopackprice'] = $row['pack'] ? EducationPack::noPackPrice($row['id_education']) : 0;

		$row['quantity'] = 0;

		$row['customization_required'] = false;

		if (isset($row['customizable']) && $row['customizable'] && Customization::isFeatureActive()) {

			if (count(Education::getRequiredCustomizableFieldsStatic((int) $row['id_education']))) {
				$row['customization_required'] = true;
			}

		}

		$row = Education::getTaxesInformations($row, $context);
		static::$producPropertiesCache[$cacheKey] = $row;

		return static::$producPropertiesCache[$cacheKey];
	}

	public static function defineEducationImage($row, $idLang) {

		if (isset($row['id_image']) && $row['id_image']) {
			return $row['id_education'] . '-' . $row['id_image'];
		}

		return Language::getIsoById((int) $idLang) . '-default';
	}

	public function getDeclinaisonImages($idLang) {

		$educationAttributes = Db::getInstance()->executeS(
			'SELECT `id_education_attribute`
            FROM `' . _DB_PREFIX_ . 'education_attribute`
            WHERE `id_education` = ' . (int) $this->id
		);

		if (!$educationAttributes) {
			return false;
		}

		$ids = [];

		foreach ($educationAttributes as $educationAttribute) {
			$ids[] = (int) $educationAttribute['id_education_attribute'];
		}

		$result = Db::getInstance()->executeS(
			'
            SELECT pai.`id_image`, pai.`id_education_attribute`, il.`legend`
            FROM `' . _DB_PREFIX_ . 'education_attribute_image` pai
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (il.`id_image_education` = pai.`id_image`)
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education` i ON (i.`id_image_education` = pai.`id_image`)
            WHERE pai.`id_education_attribute` IN (' . implode(', ', $ids) . ') AND il.`id_lang` = ' . (int) $idLang . ' ORDER BY i.`position`'
		);

		if (!$result) {
			return false;
		}

		$images = [];

		foreach ($result as $row) {
			$images[$row['id_education_attribute']][] = $row;
		}

		return $images;
	}

	public function hasAttributes() {

		$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('COUNT(*)')
				->from('education_attribute', 'pa')
				->where('pa.`id_education` = ' . (int) $this->id)
		);

		return (int) $result;
	}

	public function getDefaultIdEducationAttribute() {

		return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			'
            SELECT `id_education_attribute`
            FROM `' . _DB_PREFIX_ . 'education_attribute`
            WHERE `id_education` = ' . (int) $this->id . ' AND `default_on` = 1'
		);
	}

	public static function getEducationsList() {

		$educations = Db::getInstance()->executeS(
			(new DbQuery())
				->select('e.*, case when e.active = 1 then \'<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>\' else \'<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>\' end as education_state, et.`name` as type')
				->from('education', 'e')
				->leftJoin('education_type', 'et', 'e.`id_education_type` = et.`id_education_type`')
				->orderBy('e.id_education ASC')
		);

		if (is_array($educations && count($educations))) {

			foreach ($educations as &$education) {

				$education['date'] = DateTime::createFromFormat('Y-m-d', $education['date_add']);

				if ($education['has_option'] == 1) {
					$education['option'] = '<div class="p-active"><i class="fa fa-check" aria-hidden="true" style="color:green;"></i></div>';

				} else {
					$education['option'] = '<div class="p-inactive"><i class="fa fa-times" aria-hidden="true" style="color:red;"></i></div>';
				}

				$education['description'] = preg_replace("/\[(.*?)\]/", '', $education['description']);
			}

		}

		die(Tools::jsonEncode($educations));
	}

	public static function getEducationsFields() {

		$fields = [
			[
				'title'    => 'ID',
				'dataIndx' => 'id_education',
				'dataType' => 'integer',
				'align'    => 'center',
				'maxWidth' => 20,
				'filter'   => [

					'crules' => [['condition' => "begin"]],
				],
			],

			[

				'dataIndx' => 'id_education_type',
				'dataType' => 'integer',
				'hidden'   => true,

			],
			[
				'title'    => 'Famille de formation',
				'width'    => 200,
				'dataIndx' => 'type',
				'dataType' => 'string',
				'align'    => 'left',
				'filter'   => [

					'crules' => [['condition' => "begin"]],
				],

			],

			[
				'title'    => 'Désignation',
				'width'    => 200,
				'dataIndx' => 'name',
				'dataType' => 'string',
				'align'    => 'left',
				'filter'   => [

					'crules' => [['condition' => "begin"]],
				],

			],
			[
				'title'    => 'Possède des options',
				'maxWidth' => 100,
				'dataIndx' => 'option',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"activeSelector\", placeholder=" . '--Selectionner--' . " readonly",
					'crules' => [['condition' => "equal"]],
				],
			],
			[
				'title'        => 'Trarif',

				'dataIndx'     => 'price',
				'align'        => 'right',
				'valign'       => 'center',
				'dataType'     => 'float',
				'numberFormat' => '#,##0.00_-"€"',
				'editable'     => false,
				'format'       => "#.###,00 € ",
				'updatable'    => false,
			],

			[

				'dataIndx' => 'active',
				'dataType' => 'integer',
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "equal"]],
				],

			],

			[
				'title'    => 'Publiée',
				'maxWidth' => 100,
				'dataIndx' => 'education_state',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"activeSelector\", placeholder=" . '--Selectionner--' . " readonly",
					'crules' => [['condition' => "equal"]],
				],
			],

		];
		die(Tools::jsonEncode($fields));

	}

	public static function getEducationName($idEducation, $idEducationAttribute = null, $idLang = null) {

		// use the lang in the context if $id_lang is not defined

		if (!$idLang) {
			$idLang = (int) Context::getContext()->language->id;
		}

		// creates the query object
		$query = new DbQuery();

		// selects different names, if it is a combination

		if ($idEducationAttribute) {
			$query->select('IFNULL(CONCAT(pl.name, \' : \', GROUP_CONCAT(DISTINCT agl.`name`, \' - \', al.name SEPARATOR \', \')),pl.name) as name');
		} else {
			$query->select('DISTINCT pl.name as name');
		}

		// adds joins & where clauses for combinations

		if ($idEducationAttribute) {
			$query->from('education_attribute', 'pa');
			$query->innerJoin('education_lang', 'pl', 'pl.id_education = pa.id_education AND pl.id_lang = ' . (int) $idLang);
			$query->leftJoin('education_attribute_combination', 'pac', 'pac.id_education_attribute = pa.id_education_attribute');
			$query->leftJoin('attribute', 'atr', 'atr.id_attribute = pac.id_attribute');
			$query->leftJoin('attribute_lang', 'al', 'al.id_attribute = atr.id_attribute AND al.id_lang = ' . (int) $idLang);
			$query->leftJoin('attribute_group_lang', 'agl', 'agl.id_attribute_group = atr.id_attribute_group AND agl.id_lang = ' . (int) $idLang);
			$query->where('pa.id_education = ' . (int) $idEducation . ' AND pa.id_education_attribute = ' . (int) $idEducationAttribute);
		} else {
			// or just adds a 'where' clause for a simpleeducation

			$query->from('education_lang', 'pl');
			$query->where('pl.id_education = ' . (int) $idEducation);
			$query->where('pl.id_lang = ' . (int) $idLang);
		}

		return Db::getInstance()->getValue($query);
	}

	public static function getPriceStatic(
		$idEducation,
		$usetax = true,
		$idEducationAttribute = null,
		$decimals = 6,
		$divisor = null,
		$onlyReduc = false,
		$usereduc = true,
		$quantity = 1,
		$forceAssociatedTax = false,
		&$specificPriceOutput = null,
		Context $context = null
	) {

		if (!$context) {
			$context = Context::getContext();
		}

		$curCart = $context->cart;

		if ($divisor !== null) {
			Tools::displayParameterAsDeprecated('divisor');
		}

		if (!Validate::isBool($usetax) || !Validate::isUnsignedId($idEducation)) {
			die(Tools::displayError());
		}

		if (Tax::excludeTaxeOption()) {
			$usetax = false;
		}

		$return = Education::priceCalculation(
			$context->shop->id,
			$idEducation,
			$idEducationAttribute,
			$usetax,
			$decimals
		);

		return $return;
	}

	public function getAnchor($idEducationAttribute, $withId = false) {

		$attributes = static::getAttributesParams($this->id, $idEducationAttribute);
		$anchor = '#';
		$sep = Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR');

		foreach ($attributes as &$a) {

			foreach ($a as &$b) {
				$b = str_replace($sep, '_', Tools::link_rewrite($b));
			}

			$anchor .= '/' . ($withId && isset($a['id_attribute']) && $a['id_attribute'] ? (int) $a['id_attribute'] . $sep : '') . $a['group'] . $sep . $a['name'];
		}

		return $anchor;
	}

	public static function getAttributesParams($idEducation, $idEducationAttribute) {

		$idLang = (int) Context::getContext()->language->id;
		$cacheId = 'Education::getAttributesParams_' . (int) $idEducation . '-' . (int) $idEducationAttribute . '-' . (int) $idLang;

		// if blocklayered module is installed we check if user has set custom attribute name

		if (Module::isInstalled('blocklayered') && Module::isEnabled('blocklayered')) {
			$nbCustomValues = Db::getInstance()->executeS(
				'
            SELECT DISTINCT la.`id_attribute`, la.`url_name` AS `name`
            FROM `' . _DB_PREFIX_ . 'attribute` a
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac
                ON (a.`id_attribute` = pac.`id_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute` pa
                ON (pac.`id_education_attribute` = pa.`id_education_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'layered_indexable_attribute_lang_value` la
                ON (la.`id_attribute` = a.`id_attribute` AND la.`id_lang` = ' . (int) $idLang . ')
            WHERE la.`url_name` IS NOT NULL AND la.`url_name` != \'\'
            AND pa.`id_education` = ' . (int) $idEducation . '
            AND pac.`id_education_attribute` = ' . (int) $idEducationAttribute
			);

			if (!empty($nbCustomValues)) {
				$tabIdAttribute = [];

				foreach ($nbCustomValues as $attribute) {
					$tabIdAttribute[] = $attribute['id_attribute'];

					$group = Db::getInstance()->executeS(
						'
                    SELECT a.`id_attribute`, g.`id_attribute_group`, g.`url_name` AS `group`
                    FROM `' . _DB_PREFIX_ . 'layered_indexable_attribute_group_lang_value` g
                    LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a
                        ON (a.`id_attribute_group` = g.`id_attribute_group`)
                    WHERE a.`id_attribute` = ' . (int) $attribute['id_attribute'] . '
                    AND g.`id_lang` = ' . (int) $idLang . '
                    AND g.`url_name` IS NOT NULL AND g.`url_name` != \'\''
					);

					if (empty($group)) {
						$group = Db::getInstance()->executeS(
							'
                        SELECT g.`id_attribute_group`, g.`name` AS `group`
                        FROM `' . _DB_PREFIX_ . 'attribute_group_lang` g
                        LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a
                            ON (a.`id_attribute_group` = g.`id_attribute_group`)
                        WHERE a.`id_attribute` = ' . (int) $attribute['id_attribute'] . '
                        AND g.`id_lang` = ' . (int) $idLang . '
                        AND g.`name` IS NOT NULL'
						);
					}

					$result[] = array_merge($attribute, $group[0]);
				}

				$valuesNotCustom = Db::getInstance()->executeS(
					'
                SELECT DISTINCT a.`id_attribute`, a.`id_attribute_group`, al.`name`, agl.`name` AS `group`
                FROM `' . _DB_PREFIX_ . 'attribute` a
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                    ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                    ON (a.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac
                    ON (a.`id_attribute` = pac.`id_attribute`)
                LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute` pa
                    ON (pac.`id_education_attribute` = pa.`id_education_attribute`)
                WHERE pa.`id_education` = ' . (int) $idEducation . '
                AND pac.id_EDUCATION_attribute = ' . (int) $idEducationAttribute . '
                AND a.`id_attribute` NOT IN(' . implode(', ', $tabIdAttribute) . ')'
				);

				return array_merge($valuesNotCustom, $result);
			}

		}

		if (!Cache::isStored($cacheId)) {
			$result = Db::getInstance()->executeS(
				'
            SELECT a.`id_attribute`, a.`id_attribute_group`, al.`name`, agl.`name` AS `group`
            FROM `' . _DB_PREFIX_ . 'attribute` a
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (al.`id_attribute` = a.`id_attribute` AND al.`id_lang` = ' . (int) $idLang . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac
                ON (pac.`id_attribute` = a.`id_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute` pa
                ON (pa.`id_education_attribute` = pac.`id_education_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                ON (a.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $idLang . ')
            WHERE pa.`id_education` = ' . (int) $idEducation . '
                AND pac.`id_education_attribute` = ' . (int) $idEducationAttribute . '
                AND agl.`id_lang` = ' . (int) $idLang
			);
			Cache::store($cacheId, $result);
		} else {
			$result = Cache::retrieve($cacheId);
		}

		return $result;
	}

	public static function priceCalculation(
		$idShop,
		$idEducation,
		$idEducationAttribute,
		$useTax,
		$decimals
	) {

		$cacheId = (int) $idEducation . '-' . (int) $idShop . '-' . (int) $idEducationAttribute . '-' . ($useTax ? '1' : '0') . '-' . (int) $decimals;
		$cacheId2 = $idEducation . '-' . $idShop;
		$context = Context::getContext();

		if (!isset(static::$_pricesLevel2[$cacheId2])) {
			$sql = new DbQuery();
			$sql->select('p.`price`');
			$sql->from('education', 'p');
			$sql->where('p.`id_education` = ' . (int) $idEducation);

			$sql->select('IFNULL(education_attribute.id_education_attribute,0) id_education_attribute, education_attribute.`price` AS attribute_price, education_attribute.default_on');
			$sql->leftJoin('education_attribute', 'education_attribute', '(education_attribute.id_education = p.id_education )');

			$res = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

			if (is_array($res) && count($res)) {

				foreach ($res as $row) {
					$arrayTmp = [
						'price'           => $row['price'],
						'attribute_price' => (isset($row['attribute_price']) ? $row['attribute_price'] : null),
					];
					static::$_pricesLevel2[$cacheId2][(int) $row['id_education_attribute']] = $arrayTmp;

					if (isset($row['default_on']) && $row['default_on'] == 1) {
						static::$_pricesLevel2[$cacheId2][0] = $arrayTmp;
					}

				}

			}

		}

		if (!isset(static::$_pricesLevel2[$cacheId2][(int) $idEducationAttribute])) {
			return;
		}

		$result = static::$_pricesLevel2[$cacheId2][(int) $idEducationAttribute];

		$price = (float) $result['price'];

		$price = Tools::convertPrice($price, $context->currency->id);
		// Attribute price

		if (is_array($result)) {
			$attributePrice = Tools::convertPrice($result['attribute_price'] !== null ? (float) $result['attribute_price'] : 0, $context->currency->id);
			// If you want the default combination, please use NULL value instead

			if ($idEducationAttribute !== false) {
				$price += $attributePrice;
			}

		}

		$address = new Address();

		// Tax
		$address->id_country = 8;
		$address->id_state = '';
		$address->postcode = '';

		$taxManager = TaxManagerFactory::getManager($address, Education::getIdTaxRulesGroupByIdEducation((int) $idEducation, $context));
		$educationTaxCalculator = $taxManager->getTaxCalculator();

		// Add Tax

		if ($useTax) {
			$price = $educationTaxCalculator->addTaxes($price);
		}

		$price = Tools::ps_round($price, $decimals);

		if ($price < 0) {
			$price = 0;
		}

		static::$_prices[$cacheId] = $price;

		return static::$_prices[$cacheId];
	}

	public static function getTaxesInformations($row, Context $context = null) {

		static $address = null;

		if ($context === null) {
			$context = Context::getContext();
		}

		if ($address === null) {
			$address = new Address();
		}

		$address->id_country = (int) $context->country->id;
		$address->id_state = 0;
		$address->postcode = 0;

		$taxManager = TaxManagerFactory::getManager($address, Education::getIdTaxRulesGroupByIdEducation((int) $row['id_education'], $context));
		$row['rate'] = $taxManager->getTaxCalculator()->getTotalRate();
		$row['tax_name'] = $taxManager->getTaxCalculator()->getTaxesName();

		return $row;
	}

	public static function getIdTaxRulesGroupByIdEducation($idEducation, Context $context = null) {

		if (!$context) {
			$context = Context::getContext();
		}

		$key = 'education_id_tax_rules_group_' . (int) $idEducation;

		if (!Cache::isStored($key)) {
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				'
                            SELECT `id_tax_rules_group`
                            FROM `' . _DB_PREFIX_ . 'education`
                            WHERE `id_education` = ' . (int) $idEducation
			);
			Cache::store($key, (int) $result);

			return (int) $result;
		}

		return Cache::retrieve($key);
	}

	public function getIdTaxRulesGroup() {

		return $this->id_tax_rules_group;
	}

	public static function getIdTaxRulesGroupMostUsed() {

		return Db::getInstance()->getValue(
			'
                    SELECT id_tax_rules_group
                    FROM (
                        SELECT COUNT(*) n, p.id_tax_rules_group
                        FROM ' . _DB_PREFIX_ . 'education p
                        JOIN ' . _DB_PREFIX_ . 'tax_rules_group trg ON (p.id_tax_rules_group = trg.id_tax_rules_group)
                        WHERE trg.active = 1 AND trg.deleted = 0
                        GROUP BY p.id_tax_rules_group
                        ORDER BY n DESC
                        LIMIT 1
                    ) most_used'
		);
	}

	public static function getEducationAttributesIds($idEducation, $shopOnly = false) {

		return Db::getInstance()->executeS(
			'
        SELECT pa.id_education_attribute
        FROM `' . _DB_PREFIX_ . 'education_attribute` pa
        WHERE pa.`id_education` = ' . (int) $idEducation
		);
	}

	public function setDefaultAttribute($idEducationAttribute) {

		$result = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'education_attribute` SET `default_on` = 1 WHERE `id_education_attribute` = ' . $idEducationAttribute);

		$result = Db::getInstance()->execute('UPDATE `' . _DB_PREFIX_ . 'education` SET `cache_default_attribute` = ' . (int) $idEducationAttribute . ' WHERE `id_education` = ' . (int) $this->id);

		$this->cache_default_attribute = (int) $idEducationAttribute;

		return $result;
	}

	public static function getCover($idEducation, Context $context = null) {

		if (!$context) {
			$context = Context::getContext();
		}

		$cacheId = 'Education::getCover_' . (int) $idEducation;

		if (!Cache::isStored($cacheId)) {
			$sql = 'SELECT `id_image_education`
                    FROM `' . _DB_PREFIX_ . 'image_education`
                    WHERE `id_education` = ' . (int) $idEducation . '
                    AND `cover` = 1';
			$result = Db::getInstance()->getRow($sql);
			Cache::store($cacheId, $result);

			return $result;
		}

		return Cache::retrieve($cacheId);
	}

	public function getAttributeDeclinaisonsById($idEducationAttribute, $idLang) {

		$sql = 'SELECT pa.*, ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name, pal.`name`, pal.`description`, pal.`description_short`,
                    a.`id_attribute`
                FROM `' . _DB_PREFIX_ . 'education_attribute` pa
                LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON pac.`id_education_attribute` = pa.`id_education_attribute`
				LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_lang` pal ON pal.`id_education_attribute` = pa.`id_education_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $idLang . ')
                WHERE pa.`id_education` = ' . (int) $this->id . '
                AND pa.`id_education_attribute` = ' . (int) $idEducationAttribute . '
                GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
                ORDER BY pa.`id_education_attribute`';

		return Db::getInstance()->executeS($sql);

	}

	public function getAttributeDeclinaisons($idLang) {

		$sql = 'SELECT pa.*,  ag.`id_attribute_group`, ag.`is_color_group`, agl.`name` AS group_name, al.`name` AS attribute_name,
                    a.`id_attribute`, pal.`name`
                FROM `' . _DB_PREFIX_ . 'education_attribute` pa
				LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_lang` pal ON pal.`id_education_attribute` = pa.`id_education_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON pac.`id_education_attribute` = pa.`id_education_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute` a ON a.`id_attribute` = pac.`id_attribute`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) $idLang . ')
                WHERE pa.`id_education` = ' . (int) $this->id . '
                GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
                ORDER BY a.`position`';

		return Db::getInstance()->executeS($sql);

	}

	public function educationAttributeExists($attributesList, $currentEducationAttribute = false, Context $context = null, $returnId = false) {

		if ($context === null) {
			$context = Context::getContext();
		}

		$result = Db::getInstance()->executeS(
			'SELECT pac.`id_attribute`, pac.`id_education_attribute`
            FROM `' . _DB_PREFIX_ . 'education_attribute` pa
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac ON (pac.`id_education_attribute` = pa.`id_education_attribute`)
            WHERE pa.`id_education` = ' . (int) $this->id);

		/* If something's wrong */

		if (!$result || empty($result)) {
			return false;
		}

		/* Education attributes simulation */
		$educationAttributes = [];

		foreach ($result as $educationAttribute) {
			$educationAttributes[$educationAttribute['id_education_attribute']][] = $educationAttribute['id_attribute'];
		}

		/* Checking education's attribute existence */

		foreach ($educationAttributes as $key => $educationAttribute) {

			if (count($educationAttribute) == count($attributesList)) {
				$diff = false;

				for ($i = 0; $diff == false && isset($educationAttribute[$i]); $i++) {

					if (!in_array($educationAttribute[$i], $attributesList) || $key == $currentEducationAttribute) {
						$diff = true;
					}

				}

				if (!$diff) {

					if ($returnId) {
						return $key;
					}

					return true;
				}

			}

		}

		return false;
	}

	public function updateAttribute($idEducationAttribute, $wholesalePrice, $price, $idImages, $reference, $default) {

		$declinaison = new Declinaison($idEducationAttribute);

		if (!$updateAllFields) {
			$combination->setFieldsToUpdate(
				[
					'price'             => !is_null($price),
					'wholesale_price'   => !is_null($wholesalePrice),
					'ecotax'            => !is_null($ecotax),
					'weight'            => !is_null($weight),
					'unit_price_impact' => !is_null($unit),
					'default_on'        => !is_null($default),
					'minimal_quantity'  => !is_null($minimalQuantity),
					'available_date'    => !is_null($availableDate),

				]
			);
		}

		$price = str_replace(',', '.', $price);
		$weight = str_replace(',', '.', $weight);

		$combination->price = (float) $price;
		$combination->wholesale_price = (float) $wholesalePrice;
		$combination->ecotax = (float) $ecotax;
		$combination->weight = (float) $weight;
		$combination->unit_price_impact = (float) $unit;
		$combination->reference = pSQL($reference);
		$combination->location = pSQL($location);
		$combination->ean13 = pSQL($ean13);
		$combination->upc = pSQL($upc);
		$combination->default_on = (int) $default;
		$combination->minimal_quantity = (int) $minimalQuantity;
		$combination->available_date = $availableDate ? pSQL($availableDate) : '0000-00-00';

		if (count($idShopList)) {
			$combination->id_shop_list = $idShopList;
		}

		$combination->save();

		if (is_array($idImages) && count($idImages)) {
			$combination->setImages($idImages);
		}

		$idDefaultAttribute = (int) Education::updateDefaultAttribute($this->id);

		if ($idDefaultAttribute) {
			$this->cache_default_attribute = $idDefaultAttribute;
		}

		// Sync stock Reference, EAN13 and UPC for this attribute

		if (Configuration::get('PS_ADVANCED_STOCK_MANAGEMENT') && StockAvailable::dependsOnStock($this->id, Context::getContext()->shop->id)) {
			Db::getInstance()->update(
				'stock',
				[
					'reference' => pSQL($reference),
					'ean13'     => pSQL($ean13),
					'upc'       => pSQL($upc),
				],
				'id_education = ' . $this->id . ' AND id_education_attribute = ' . (int) $idEducationAttribute
			);
		}

		Hook::exec('actionEducationAttributeUpdate', ['id_education_attribute' => (int) $idEducationAttribute]);

		return true;
	}

	public static function updateDefaultAttribute($idEducation) {

		$idDefaultAttribute = (int) Education::getDefaultAttribute($idEducation, 0, true);

		$result &= Db::getInstance()->update(
			'education',
			[
				'cache_default_attribute' => $idDefaultAttribute,
			],
			'id_education = ' . (int) $idEducation
		);

		if ($result && $idDefaultAttribute) {
			return $idDefaultAttribute;
		} else {
			return $result;
		}

	}

	public static function getDefaultAttribute($idEducation, $minimumQuantity = 0, $reset = false) {

		static $combinations = [];

		if ($reset && isset($combinations[$idEducation])) {
			unset($combinations[$idEducation]);
		}

		if (!isset($combinations[$idEducation])) {
			$combinations[$idEducation] = [];
		}

		$sql = 'SELECT id_education_attribute
                FROM ' . _DB_PREFIX_ . 'education_attribute
                WHERE id_education = ' . (int) $idEducation;

		$resultNoFilter = Db::getInstance()->getValue($sql);

		if (!$resultNoFilter) {

			return 0;
		}

		$sql = 'SELECT id_education_attribute
                FROM ' . _DB_PREFIX_ . 'education_attribute
         		WHERE default_on = 1 AND id_education = ' . (int) $idEducation;
		$result = Db::getInstance()->getValue($sql);

		if (!$result) {
			$sql = 'SELECT id_education_attribute
                    FROM ' . _DB_PREFIX_ . 'education_attribute
					WHERE id_education = ' . (int) $idEducation;

			$result = Db::getInstance()->getValue($sql);
		}

		if (!$result) {
			$sql = 'SELECT id_education_attribute
                    FROM ' . _DB_PREFIX_ . 'education_attribute
                    WHERE `default_on` = 1
                    AND id_education = ' . (int) $idEducation;

			$result = Db::getInstance()->getValue($sql);
		}

		if (!$result) {
			$result = $resultNoFilter;
		}

		return $result;
	}

	public function setImages($idsImage) {

		if (Db::getInstance()->delete('education_attribute_image', '`id_education_attribute` = ' . (int) $this->id) === false) {
			return false;
		}

		if (is_array($idsImage) && count($idsImage)) {
			$sqlValues = [];

			foreach ($idsImage as $value) {
				$sqlValues[] = [
					'id_education_attribute' => (int) $this->id,
					'id_image_education'     => (int) $value,
				];
			}

			if (is_array($sqlValues) && count($sqlValues)) {
				Db::getInstance()->insert('education_attribute_image', $sqlValues);
			}

		}

		return true;
	}

	public static function getEducationLink($idEducation, $idEducationAttribute) {

		if ($idEducationAttribute == 0) {
			return Db::getInstance()->getValue('SELECT education_link
			FROM `' . _DB_PREFIX_ . 'education`
			WHERE id_education = ' . (int) $idEducation);
		} else {
			return Db::getInstance()->getValue('SELECT education_link
			FROM `' . _DB_PREFIX_ . 'education_attribute`
			WHERE id_education_attribute = ' . (int) $idEducationAttribute . ' AND id_education = ' . (int) $idEducation);
		}

	}

	public static function getEducationDetails($idEducation, $idEducationAttribute, $withPrice = true) {

		$education = [];
		$context = Context::getContext();

		if ($idEducationAttribute == 0) {
			$result = Db::getInstance()->getRow('SELECT e.id_education, e.id_education_type as educationType, e.id_education_prerequis, e.reference, e.price, e.wholesale_price as formaPack, cer.name as certification, e.days, e.hours, e.id_platform as educationPlatform, edl.edof_link as reservationLink, p.education_link as courseLink, el.name, t.rate
			FROM `' . _DB_PREFIX_ . 'education` e
			LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` el ON el.id_education = e.id_education AND el.id_lang = ' . $context->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'platform` p ON p.`id_platform` = e.`id_platform`
			LEFT JOIN `' . _DB_PREFIX_ . 'certification` cer ON cer.`id_certification` = e.`id_certification`
			LEFT JOIN `' . _DB_PREFIX_ . 'education_link` edl ON edl.`id_education` = e.`id_education`
			LEFT JOIN `' . _DB_PREFIX_ . 'tax_rules_group` tl ON tl.`id_tax_rules_group` = e.`id_tax_rules_group`
			LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON t.`id_tax` = tl.`id_tax_rules_group`
			WHERE e.id_education = ' . (int) $idEducation);
			$price = $result['price'];
			$priceWTax = $price * (1 + $result['rate'] / 100);
		} else {
			$result = Db::getInstance()->getRow('SELECT e.id_education, e.id_education_type as educationType, e.id_education_prerequis, ea.reference,  cer.name as certification, e.price, ea.wholesale_price as formaPack, e.id_platform as educationPlatform, ea.price as attributePrice, ea.days, ea.hours, el.edof_link as reservationLink, p.education_link as courseLink, eal.name, t.rate
			FROM `' . _DB_PREFIX_ . 'education_attribute` ea
			LEFT JOIN `' . _DB_PREFIX_ . 'education` e ON e.id_education = ea.id_education
			LEFT JOIN `' . _DB_PREFIX_ . 'platform` p ON p.`id_platform` = e.`id_platform`
			LEFT JOIN `' . _DB_PREFIX_ . 'certification` cer ON cer.`id_certification` = e.`id_certification`
			LEFT JOIN `' . _DB_PREFIX_ . 'education_link` el ON el.`id_education_attribute` = ea.`id_education_attribute`
			LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_lang` eal ON eal.id_education_attribute = ea.id_education_attribute AND eal.id_lang = ' . $context->language->id . '
			LEFT JOIN `' . _DB_PREFIX_ . 'tax_rules_group` tl ON tl.`id_tax_rules_group` = e.`id_tax_rules_group`
			LEFT JOIN `' . _DB_PREFIX_ . 'tax` t ON t.`id_tax` = tl.`id_tax_rules_group`
			WHERE ea.id_education_attribute = ' . (int) $idEducationAttribute . ' AND ea.id_education = ' . (int) $idEducation);
			$price = $result['price'] + $result['attributePrice'];
			$priceWTax = $price * (1 + $result['rate'] / 100);
		}

		$education['id_education'] = $idEducation;
		$education['id_education_attribute'] = $idEducationAttribute;
		$education['reference'] = $result['reference'];
		$education['days'] = $result['days'];
		$education['hours'] = $result['hours'];
		$education['name'] = $result['name'];
		$education['courseLink'] = $result['courseLink'];
		$education['reservationLink'] = $result['reservationLink'];
		$education['educationType'] = $result['educationType'];
		$education['id_education_prerequis'] = $result['id_education_prerequis'];
		$education['certificationName'] = $result['certification'];
		$education['educationPlatform'] = $result['educationPlatform'];
		$education['tax_rate'] = $result['rate'];
		$education['formaPack'] = $result['formaPack'];

		if ($withPrice) {
			$education['price'] = round($price, 2);
			$education['priceWTax'] = round($priceWTax);
		}

		$education['rate'] = $result['rate'];

		return $education;

	}

	public static function updateCacheAttachment($idEducation) {

		$value = (bool) Db::getInstance()->getValue(
			'
                                SELECT id_attachment
                                FROM ' . _DB_PREFIX_ . 'education_PROGRAMME
                                WHERE id_education=' . (int) $idEducation
		);

		return Db::getInstance()->update(
			'education',
			['cache_has_attachments' => (int) $value],
			'id_education = ' . (int) $idEducation
		);
	}

	public static function getEducationAttributePrice($idEducationAttribute) {

		return Declinaison::getPrice($idEducationAttribute);
	}

	public function getImages($idLang, Context $context = null) {

		return Db::getInstance()->executeS(
			'
            SELECT i.`cover`, i.`id_image_education`, il.`legend`, i.`position`, i.`reference`
            FROM `' . _DB_PREFIX_ . 'image_education` i
            LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il ON (i.`id_image_education` = il.`id_image_education` AND il.`id_lang` = ' . (int) $idLang . ')
            WHERE i.`id_education` = ' . (int) $this->id . '
            ORDER BY `position`'
		);
	}

	public static function getAttributesInformationsByEducation($idEducation) {

		$result = Db::getInstance()->executeS(
			'
            SELECT DISTINCT a.`id_attribute`, a.`id_attribute_group`, al.`name` AS `attribute`, agl.`name` AS `group`
            FROM `' . _DB_PREFIX_ . 'attribute` a
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_lang` al
                ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = ' . (int) Context::getContext()->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'attribute_group_lang` agl
                ON (a.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = ' . (int) Context::getContext()->language->id . ')
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute_combination` pac
                ON (a.`id_attribute` = pac.`id_attribute`)
            LEFT JOIN `' . _DB_PREFIX_ . 'education_attribute` pa
                ON (pac.`id_education_attribute` = pa.`id_education_attribute`)
            WHERE pa.`id_education` = ' . (int) $idEducation
		);

		return $result;
	}

	public static function getReferentEducations() {

		$return = [];
		$educations = Db::getInstance()->executeS(
			'SELECT `id_education`, `reference`, `cache_default_attribute`
            FROM `' . _DB_PREFIX_ . 'education`
            ORDER BY `id_education`'
		);
		
		foreach ($educations as &$education) {
			$education['attributes'] = [];

			if ($education['cache_default_attribute'] > 0) {
				$education['attributes'] = Db::getInstance()->executeS(
					'SELECT `id_education_attribute`, `reference`
            	FROM `' . _DB_PREFIX_ . 'education_attribute`
            	WHERE `id_education` = ' . (int) $education['id_education']
				);
				$combinations = [];
				foreach($education['attributes'] as $key => $attribute) {
					$declinaison = new Declinaison($attribute['id_education_attribute']);
					$combination = $declinaison->getAttributeCombinationValues();
					$combinations[] = $combination;
				}
				$education['attributes']['combinations'] = $combinations;
			}

		}

		return $educations;
	}
	
	public static function getIdEducationByRef($reference)  {
       
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_education`')
                ->from('education')
				->where('`reference` LIKE \''.$reference.'\'')
        );
    }
	
	public static function isUsedEducation($idEducation)  {
       
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('COUNT(`id_student_education`)')
                ->from('student_education')
				->where('`id_education` = '.$idEducation)
        );
    }
	
	public static function getNbTotalEducatiion() {
												  
		$nonAttributes = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
             ->select('COUNT(`id_education`)')
             ->from('education')
			 ->where('`cache_default_attribute` = 0')
			 ->where('`active` = 1')
        );
		
		$attributes = 
			Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('COUNT(`id_education_attribute`)')
                ->from('education_attribute')
        );
		
		$nbEducation = $nonAttributes+$attributes;
		return $nbEducation;
	}

}
