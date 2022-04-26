<?php

/**
 * Class DeclinaisonCore
 *
 * @since 1.9.1.0
 */
class DeclinaisonCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'education_attribute',
        'primary'   => 'id_education_attribute',
        'multilang' => true,
        'fields'    => [
            'id_education'           => ['type' => self::TYPE_INT, 'shop' => 'both', 'validate' => 'isUnsignedId', 'required' => true],
            'id_education_prerequis' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'reference'              => ['type' => self::TYPE_STRING, 'size' => 32],
            'price'                  => ['type' => self::TYPE_FLOAT, 'validate' => 'isNegativePrice', 'size' => 20],
			'wholesale_price'    => ['type' => self::TYPE_FLOAT, 'shop' => true, 'validate' => 'isPrice', 'size' => 27],
			'id_formatpack'               => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'days'                   => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'hours'                  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'education_link'         => ['type' => self::TYPE_STRING, 'validate' => 'isUrl'],
            'id_plateforme'          => ['type' => self::TYPE_STRING],
            'versionProgram'         => ['type' => self::TYPE_STRING, 'size' => 64],
            'is_combo'               => ['type' => self::TYPE_BOOL, 'allow_null' => true, 'validate' => 'isBool'],
			'parents'                => ['type' => self::TYPE_STRING],
			'default_on'             => ['type' => self::TYPE_BOOL, 'allow_null' => true, 'validate' => 'isBool'],			

            'name'                   => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'size' => 128],
            'description'            => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
            'description_short'      => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
			'programme'       		 => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],

        ],
    ];
    /** @var int $id_education */
    public $id_education;

    public $id_education_prerequis;
    /** @var string $reference */
    public $reference;

    /** @var float $price */
    public $price;
	
	public $wholesale_price;

    public $days;
    public $hours;
    public $education_link;
    public $id_plateforme;
    public $versionProgram;
	public $is_combo = 0;
	public $parents;
    public $id_formatpack;
    /** @var bool $default_on */
    public $default_on;

    public $name;
    public $description_short;
    public $description;
	public $programme;
    public $images = [];
    public $id_education_link;
    public $edof_link;
    public $combinations;
    /** @var string $available_date */

    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);

        if ($this->id) {
            $this->images = $this->getImages();
            $this->edof_link = $this->getEdofLink();
            $this->id_education_link = $this->getIdEdofLink();
            $this->combinations = $this->getAttributeCombinationValues();
			$this->parents = Tools::jsonDecode($this->parents);
			if($this->is_combo) {
				$this->programme = $this->getComboProgram();
			}
        }

    }
	
	public function getComboProgram() {
		
		$context = Context::getContext();
		
		if(is_array($this->parents) && count($this->parents)) {
			$program = '<br>';
			foreach($this->parents as $idAttribute) {
				$declinaison = new Declinaison($idAttribute);				
				$program .= $declinaison->programme[$context->language->id].'<br>';
			}
			return str_replace('&lt;', '',$program);
		}
		
		return $this->programme;
	}

    public function getEdofLink() {

        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`edof_link`')
                ->from('education_link')
                ->where('`id_education` = ' . $this->id_education)
                ->where('`id_education_attribute` = ' . $this->id)
        );
    }

    public function getIdEdofLink() {

        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_education_link`')
                ->from('education_link')
                ->where('`id_education_attribute` = ' . $this->id)
                ->where('`id_education` = ' . $this->id_education)
        );
    }

    public function getImages() {

        $return = [];
        $images = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_image`')
                ->from('education_attribute_image')
                ->where('`id_education_attribute` = ' . (int) $this->id)
        );

        foreach ($images as $image) {
            $return[] = $image['id_image'];
        }

        return array_values($return);
    }

    /**
     * This method is allowed to know if a Combination entity is currently used
     *
     * @param string|null $table
     * @param bool        $hasActiveColumn
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function isCurrentlyUsed($table = null, $hasActiveColumn = false) {

        return parent::isCurrentlyUsed('education_attribute');
    }

    /**
     * For a given education_attribute reference, returns the corresponding id
     *
     * @param int    $idEducation
     * @param string $reference
     *
     * @return int id
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getIdByReference($idEducation, $reference) {

        if (empty($reference)) {
            return 0;
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('pa.id_education_attribute')
                ->from('education_attribute', 'pa')
                ->where('pa.reference LIKE \'%' . pSQL($reference) . '%\'')
                ->where('pa.id_education = ' . (int) $idEducation)
        );
    }
	
	public static function getComboDeclinaison($idEducation, $id_lang, $idEducationAttribute = 0) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('pa.id_education_attribute, pa.reference, pal.name')
                ->from('education_attribute', 'pa')
			    ->leftJoin('education_attribute_lang', 'pal', 'pal.id_education_attribute = pa.id_education_attribute AND pal.id_lang = '.$id_lang)
				->leftJoin('education_attribute_combination', 'pac', 'pac.id_education_attribute = pa.id_education_attribute')
				->leftJoin('attribute', 'a', 'a.`id_attribute` = pac.`id_attribute`')
				->where('pa.is_combo = 0')
                ->where('pa.id_education = ' . (int) $idEducation)
			 	->where('pa.id_education_attribute != ' . (int) $idEducationAttribute)
				->orderBy('a.position')
        );
		
	}

    /**
     * Retrive the price of combination
     *
     * @param int $idEducationAttribute
     *
     * @return float mixed
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public static function getPrice($idEducationAttribute) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`price`')
                ->from('education_attribute')
                ->where('`id_education_attribute` = ' . (int) $idEducationAttribute)
        );
    }

    /**
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function delete() {

        if (!parent::delete()) {
            return false;
        }

        // Removes the education from StockAvailable, for the current shop
        StockAvailable::removeEducationFromStockAvailable((int) $this->id_education, (int) $this->id);

        if ($specificPrices = SpecificPrice::getByEducationId((int) $this->id_education, (int) $this->id)) {

            foreach ($specificPrices as $specificPrice) {
                $price = new SpecificPrice((int) $specificPrice['id_specific_price']);
                $price->delete();
            }

        }

        if (!$this->hasMultishopEntries() && !$this->deleteAssociations()) {
            return false;
        }

        $this->deleteFromSupplier($this->id_education);
        Education::updateDefaultAttribute($this->id_education);

        return true;
    }

    /**
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     */
    public function deleteAssociations() {

        $result = Db::getInstance()->delete('education_attribute_combination', '`id_education_attribute` = ' . (int) $this->id);
        $result &= Db::getInstance()->delete('cart_education', '`id_education_attribute` = ' . (int) $this->id);
        $result &= Db::getInstance()->delete('education_attribute_image', '`id_education_attribute` = ' . (int) $this->id);

        return $result;
    }

    /**
     * @param int $idEducation
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     */
    public function deleteFromSupplier($idEducation) {

        return Db::getInstance()->delete('education_supplier', 'id_education = ' . (int) $idEducation . ' AND id_education_attribute = ' . (int) $this->id);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function add($autoDate = true, $nullValues = false) {

        if ($this->default_on) {
            $this->default_on = 1;
        } else {
            $this->default_on = null;
        }

        if (!parent::add($autoDate, $nullValues)) {
            return false;
        }

        return true;
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function update($nullValues = false) {

        if ($this->default_on) {
            $this->default_on = 1;
        } else {
            $this->default_on = null;
        }
		
		if($this->is_combo) {
			$this->programme = $this->getComboProgram();
		}

        $return = parent::update($nullValues);

        return $return;
    }

    /**
     * @param int[] $idsAttribute
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setAttributes($idsAttribute) {

        $result = $this->deleteAssociations();

        if ($result && !empty($idsAttribute)) {
            $sqlValues = [];

            foreach ($idsAttribute as $value) {
                $sqlValues[] = [
                    'id_attribute'           => (int) $value,
                    'id_education_attribute' => (int) $this->id,
                ];
            }

            $result = Db::getInstance()->insert('education_attribute_combination', $sqlValues);
        }

        return $result;
    }

    /**
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getWsEducationOptionValues() {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('a.`id_attribute` AS `id`')
                ->from('education_attribute_combination', 'a')
                ->where('a.`id_education_attribute` = ' . (int) $this->id)
        );

        return $result;
    }

    public function getAttributeCombinationValues() {

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_attribute`')
                ->from('education_attribute_combination')
                ->where('`id_education_attribute` = ' . (int) $this->id)
        );

        return $result;
    }

    /**
     * @param array $idsImage
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function setImages($idsImage) {

        if (Db::getInstance()->delete('education_attribute_image', '`id_education_attribute` = ' . (int) $this->id) === false) {
            return false;
        }

        if (is_array($idsImage) && count($idsImage)) {
            $sqlValues = [];

            foreach ($idsImage as $value) {
                $sqlValues[] = [
                    'id_education_attribute' => (int) $this->id,
                    'id_image'               => (int) $value,
                ];
            }

            if (is_array($sqlValues) && count($sqlValues)) {
                Db::getInstance()->insert('education_attribute_image', $sqlValues);
            }

        }

        return true;
    }

    /**
     * @param int $idLang
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getAttributesName($idLang) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('al.*')
                ->from('education_attribute_combination', 'pac')
                ->innerJoin('attribute_lang', 'al', 'pac.`id_attribute` = al.`id_attribute`')
                ->where('al.`id_lang` = ' . (int) $idLang)
                ->where('pac.`id_education_attribute` = ' . (int) $this->id)
        );
    }

    /**
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getColorsAttributes() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('a.`id_attribute`')
                ->from('education_attribute_combination', 'pac')
                ->innerJoin('attribute', 'a', 'pac.`id_attribute` = a.`id_attribute`')
                ->innerJoin('attribute_group', 'ag', 'ag.`id_attribute_group` = a.`id_attribute_group`')
                ->where('pac.`id_education_attribute` = ' . (int) $this->id)
                ->where('ag.`is_color_group` = 1')
        );
    }

    public static function getIdDeclinaisonByRef($reference) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_education_attribute`')
                ->from('education_attribute')
                ->where('`reference` LIKE \'' . $reference . '\'')
        );
    }
	
	public static function getProgrammeCollection($idEducation, $idLang) {
		
		$programme = [];
		$request =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('ea.`id_education_attribute`, ea.is_combo, ea.parents, ea.`versionProgram`, eal.`programme`')
                ->from('education_attribute', 'ea')
                ->leftJoin('education_attribute_lang', 'eal', 'eal.`id_education_attribute` = ea.`id_education_attribute` AND eal.`id_lang` = '.$idLang)
				->where('ea.`id_education` = ' . $idEducation)
        );
		
		foreach($request as &$value) {
			$program = '';
			if($value['is_combo']) {
				$parents = Tools::jsonDecode($value['parents']);
				if(is_array($parents)) {
					foreach($parents as $idAttribute) {
						$declinaison = new Declinaison($idAttribute);
						$program .= $declinaison->programme[$idLang].'<br>';
					}
					$value['programme'] = $program;
				}
			}
			$programme[$value['id_education_attribute']] = $value;
		}
		
		
		
		return $programme;
	}
	
	public static function getPrerequisCollection($idEducation) {
		
		$prerequis = [];
		$request =  Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('`id_education_attribute`, id_education_prerequis')
                ->from('education_attribute')
				->where('`id_education` = ' . $idEducation)
        );
		
		foreach($request as &$value) {
			$prerequis[$value['id_education_attribute']] = $value;
		}		
		
		return $prerequis;
	}


}
