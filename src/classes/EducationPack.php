<?php


/**
 * Class PackCore
 */
class EducationPackCore extends Education
{
    protected static $cachePackItems = [];
    protected static $cacheIsPack = [];
    protected static $cacheIsPacked = [];

    /**
     * @param int $idEducation
     *
     * @return float|int
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     */
    public static function noPackPrice($idEducation)
    {
        $sum = 0;
        $priceDisplayMethod = !static::$_taxCalculationMethod;
        $items = EducationPack::getItems($idEducation, Configuration::get('PS_LANG_DEFAULT'));
        foreach ($items as $item) {
            /** @var Education $item */
            $sum += $item->getPrice($priceDisplayMethod, ($item->id_pack_education_attribute ? $item->id_pack_education_attribute : null)) * $item->pack_quantity;
        }

        return $sum;
    }

    /**
     * @param int $idEducation
     * @param int $idLang
     *
     * @return array|mixed
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function getItems($idEducation, $idLang)
    {
        if (!EducationPack::isFeatureActive()) {
            return [];
        }

        if (array_key_exists($idEducation, static::$cachePackItems)) {
            return static::$cachePackItems[$idEducation];
        }
        $result = Db::getInstance()->executeS('SELECT id_education_item, id_education_attribute_item, quantity FROM `'._DB_PREFIX_.'education_pack` where id_education_pack = '.(int) $idEducation);
        $arrayResult = [];
        foreach ($result as $row) {
            $p = new Education($row['id_education_item'], false, $idLang);
          
            $p->pack_quantity = $row['quantity'];
            $p->id_pack_education_attribute = (isset($row['id_education_attribute_item']) && $row['id_education_attribute_item'] ? $row['id_education_attribute_item'] : 0);
            if (isset($row['id_education_attribute_item']) && $row['id_education_attribute_item']) {
                $sql = 'SELECT agl.`name` AS group_name, al.`name` AS attribute_name
					FROM `'._DB_PREFIX_.'education_attribute` pa
					'.Shop::addSqlAssociation('education_attribute', 'pa').'
					LEFT JOIN `'._DB_PREFIX_.'education_attribute_combination` pac ON pac.`id_education_attribute` = pa.`id_education_attribute`
					LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
					LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
					LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int) Context::getContext()->language->id.')
					LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int) Context::getContext()->language->id.')
					WHERE pa.`id_education_attribute` = '.$row['id_education_attribute_item'].'
					GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
					ORDER BY pa.`id_education_attribute`';

                $combinations = Db::getInstance()->executeS($sql);
                foreach ($combinations as $k => $combination) {
                    $p->name .= ' '.$combination['group_name'].'-'.$combination['attribute_name'];
                }
            }
            $arrayResult[] = $p;
        }
        static::$cachePackItems[$idEducation] = $arrayResult;

        return static::$cachePackItems[$idEducation];
    }

    /**
     * This method is allow to know if a feature is used or active
     *
     * @since 1.5.0.1
     * @return bool
     * @throws PhenyxShopException
     */
    public static function isFeatureActive()
    {
        return Configuration::get('PS_PACK_FEATURE_ACTIVE');
    }

    /**
     * @param int $idEducation
     *
     * @return int
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function noPackWholesalePrice($idEducation)
    {
        $sum = 0;
        $items = EducationPack::getItems($idEducation, Configuration::get('PS_LANG_DEFAULT'));
        foreach ($items as $item) {
            $sum += $item->wholesale_price * $item->pack_quantity;
        }

        return $sum;
    }

    /**
     * @param int $idEducation
     *
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function isInStock($idEducation)
    {
        if (!EducationPack::isFeatureActive()) {
            return true;
        }

        $items = EducationPack::getItems((int) $idEducation, Configuration::get('PS_LANG_DEFAULT'));

        foreach ($items as $item) {
            /** @var Education $item */
            // Updated for 1.5.0
            if (Education::getQuantity($item->id) < $item->pack_quantity && !$item->isAvailableWhenOutOfStock((int) $item->out_of_stock)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param int  $idEducation
     * @param int  $idLang
     * @param bool $full
     *
     * @return array|false|null|PDOStatement
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function getItemTable($idEducation, $idLang, $full = false)
    {
        

        $context = Context::getContext();

        $sql = 'SELECT p.*,  pl.*, image_education.`id_image_education` id_image, il.`legend`, a.quantity AS pack_quantity,  a.id_education_pack, a.id_education_attribute_item
				FROM `'._DB_PREFIX_.'education_pack` a
				LEFT JOIN `'._DB_PREFIX_.'education` p ON p.id_education = a.id_education_item
				LEFT JOIN `'._DB_PREFIX_.'education_lang` pl
					ON p.id_education = pl.id_education
					AND pl.`id_lang` = '.(int) $idLang.'
				LEFT JOIN `'._DB_PREFIX_.'image_education` image_education
					ON (image_education.`id_education` = p.`id_education` AND image_education.cover=1)
				LEFT JOIN `'._DB_PREFIX_.'image_education_lang` il ON (image_education.`id_image_education` = il.`id_image_education` AND il.`id_lang` = '.(int) $idLang.')
				WHERE a.`id_education_pack` = '.(int) $idEducation.'
				GROUP BY a.`id_education_item`, a.`id_education_attribute_item`';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        foreach ($result as &$line) {
            if (isset($line['id_education_attribute_item']) && $line['id_education_attribute_item']) {
                $line['cache_default_attribute'] = $line['id_education_attribute'] = $line['id_education_attribute_item'];

                $sql = 'SELECT agl.`name` AS group_name, al.`name` AS attribute_name,  pai.`id_image` AS id_education_attribute_image
				FROM `'._DB_PREFIX_.'education_attribute` pa
				LEFT JOIN `'._DB_PREFIX_.'education_attribute_combination` pac ON pac.`id_education_attribute` = '.$line['id_education_attribute_item'].'
				LEFT JOIN `'._DB_PREFIX_.'attribute` a ON a.`id_attribute` = pac.`id_attribute`
				LEFT JOIN `'._DB_PREFIX_.'attribute_group` ag ON ag.`id_attribute_group` = a.`id_attribute_group`
				LEFT JOIN `'._DB_PREFIX_.'attribute_lang` al ON (a.`id_attribute` = al.`id_attribute` AND al.`id_lang` = '.(int) Context::getContext()->language->id.')
				LEFT JOIN `'._DB_PREFIX_.'attribute_group_lang` agl ON (ag.`id_attribute_group` = agl.`id_attribute_group` AND agl.`id_lang` = '.(int) Context::getContext()->language->id.')
				LEFT JOIN `'._DB_PREFIX_.'education_attribute_image` pai ON ('.$line['id_education_attribute_item'].' = pai.`id_education_attribute`)
				WHERE pa.`id_education` = '.(int) $line['id_education'].' AND pa.`id_education_attribute` = '.$line['id_education_attribute_item'].'
				GROUP BY pa.`id_education_attribute`, ag.`id_attribute_group`
				ORDER BY pa.`id_education_attribute`';

                $attrName = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

                if (isset($attrName[0]['id_education_attribute_image']) && $attrName[0]['id_education_attribute_image']) {
                    $line['id_image'] = $attrName[0]['id_education_attribute_image'];
                }
                $line['name'] .= "\n";
                foreach ($attrName as $value) {
                    $line['name'] .= ' '.$value['group_name'].'-'.$value['attribute_name'];
                }
            }
           
        }

        if (!$full) {
            return $result;
        }

        $arrayResult = [];
        foreach ($result as $prow) {
            if (!EducationPack::isPack($prow['id_education'])) {
                $prow['id_education_attribute'] = (int) $prow['id_education_attribute_item'];
                $arrayResult[] = Education::getEducationProperties($idLang, $prow);
            }
        }

        return $arrayResult;
    }

    /**
     * Is education a pack?
     *
     * @param int $idEducation
     *
     * @return bool
     * @throws PhenyxShopException
     */
    public static function isPack($idEducation)
    {
       
        if (!$idEducation) {
            return false;
        }

        if (!array_key_exists($idEducation, static::$cacheIsPack)) {
            $result = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'education_pack` WHERE id_education_pack = '.(int) $idEducation);
            static::$cacheIsPack[$idEducation] = ($result > 0);
        }

        return static::$cacheIsPack[$idEducation];
    }

    /**
     * @param int  $idEducation
     * @param int  $idLang
     * @param bool $full
     * @param null $limit
     *
     * @return array|false|null|PDOStatement
     * @throws PhenyxShopException
     */
    public static function getPacksTable($idEducation, $idLang, $full = false, $limit = null)
    {
        if (!EducationPack::isFeatureActive()) {
            return [];
        }

        $packs = Db::getInstance()->getValue(
            '
		SELECT GROUP_CONCAT(a.`id_education_pack`)
		FROM `'._DB_PREFIX_.'education_pack` a
		WHERE a.`id_education_item` = '.(int) $idEducation
        );

        if (!(int) $packs) {
            return [];
        }

        $context = Context::getContext();

        $sql = '
		SELECT p.*,  pl.*, image_shop.`id_image_education` id_image, il.`legend`, IFNULL(education_attribute_shop.id_education_attribute, 0) id_education_attribute
		FROM `'._DB_PREFIX_.'education` p
		NATURAL LEFT JOIN `'._DB_PREFIX_.'education_lang` pl
		LEFT JOIN `'._DB_PREFIX_.'education_attribute` education_attribute_shop
	   		ON (p.`id_education` = education_attribute_shop.`id_education` AND education_attribute_shop.`default_on` = 1 )
		LEFT JOIN `'._DB_PREFIX_.'image_education` image_shop
			ON (image_shop.`id_education` = p.`id_education` AND image_shop.cover=1)
		LEFT JOIN `'._DB_PREFIX_.'image_education_lang` il ON (image_shop.`id_image_education` = il.`id_image_education` AND il.`id_lang` = '.(int) $idLang.')
		WHERE pl.`id_lang` = '.(int) $idLang.'
			AND p.`id_education` IN ('.$packs.')
		GROUP BY p.id_education';
        if ($limit) {
            $sql .= ' LIMIT '.(int) $limit;
        }
        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        if (!$full) {
            return $result;
        }

        $arrayResult = [];
        foreach ($result as $row) {
            if (!EducationPack::isPacked($row['id_education'])) {
                $arrayResult[] = Education::getEducationProperties($idLang, $row);
            }
        }

        return $arrayResult;
    }

    /**
     * Is education in a pack?
     * If $id_education_attribute specified, then will restrict search on the given combination,
     * else this method will match a education if at least one of all its combination is in a pack.
     *
     * @param int      $idEducation
     * @param bool|int $idEducationAttribute Optional combination of the education
     *
     * @return bool
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    public static function isPacked($idEducation, $idEducationAttribute = false)
    {

       
        if ($idEducationAttribute === false) {
            $cacheKey = $idEducation.'-0';
            if (!array_key_exists($cacheKey, static::$cacheIsPacked)) {
                $result = Db::getInstance()->getValue('SELECT COUNT(*) FROM `'._DB_PREFIX_.'education_pack` WHERE id_education_item = '.(int) $idEducation);
                static::$cacheIsPacked[$cacheKey] = ($result > 0);
            }

            return static::$cacheIsPacked[$cacheKey];
        } else {
            $cacheKey = $idEducation.'-'.$idEducationAttribute;
            if (!array_key_exists($cacheKey, static::$cacheIsPacked)) {
                $result = Db::getInstance()->getValue(
                    'SELECT COUNT(*) FROM `'._DB_PREFIX_.'education_pack` WHERE id_education_item = '.((int) $idEducation).' AND
					id_education_attribute_item = '.((int) $idEducationAttribute)
                );
                static::$cacheIsPacked[$cacheKey] = ($result > 0);
            }

            return static::$cacheIsPacked[$cacheKey];
        }
    }

    /**
     * @param int $idEducation
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @throws PhenyxShopException
     */
    public static function deleteItems($idEducation)
    {
        return Db::getInstance()->update('education', ['cache_is_pack' => 0], 'id_education = '.(int) $idEducation) &&
            Db::getInstance()->execute('DELETE FROM `'._DB_PREFIX_.'education_pack` WHERE `id_education_pack` = '.(int) $idEducation) &&
            Configuration::updateGlobalValue('PS_EDUCATION_PACK_FEATURE_ACTIVE', EducationPack::isCurrentlyUsed());
    }

    /**
     * This method is allow to know if a Pack entity is currently used
     *
     * @since 1.5.0
     *
     * @param string $table
     * @param bool   $hasActiveColumn
     *
     * @return bool
     * @throws PhenyxShopException
     */
    public static function isCurrentlyUsed($table = null, $hasActiveColumn = false)
    {
        // We dont't use the parent method because the identifier isn't id_pack
        return (bool) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            '
			SELECT `id_education_pack`
			FROM `'._DB_PREFIX_.'education_pack`
		'
        );
    }

    /**
     * Add an item to the pack
     *
     * @param int $idEducation
     * @param int $idItem
     * @param int $qty
     * @param int $idAttributeItem
     *
     * @return bool true if everything was fine
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    public static function addItem($idEducation, $idItem, $qty, $idAttributeItem = 0)
    {
        $idAttributeItem = (int) $idAttributeItem ? (int) $idAttributeItem : Education::getDefaultAttribute((int) $idItem);

        return Db::getInstance()->update('education', ['cache_is_pack' => 1], 'id_education = '.(int) $idEducation) &&
            Db::getInstance()->insert(
                'education_pack',
                [
                    'id_education_pack'           => (int) $idEducation,
                    'id_education_item'           => (int) $idItem,
                    'id_education_attribute_item' => (int) $idAttributeItem,
                    'quantity'                  => (int) $qty,
                ]
            )
            && Configuration::updateGlobalValue('PS_EDUCATION_PACK_FEATURE_ACTIVE', '1');
    }

    /**
     * @param int $idEducationOld
     * @param int $idEducationNew
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @throws PhenyxShopException
     */
    public static function duplicate($idEducationOld, $idEducationNew)
    {
        Db::getInstance()->execute(
            'INSERT INTO `'._DB_PREFIX_.'education_pack` (`id_education_pack`, `id_education_item`, `id_education_attribute_item`, `quantity`)
		(SELECT '.(int) $idEducationNew.', `id_education_item`, `id_education_attribute_item`, `quantity` FROM `'._DB_PREFIX_.'education_pack` WHERE `id_education_pack` = '.(int) $idEducationOld.')'
        );

        // If return query result, a non-pack education will return false
        return true;
    }

    /**
     * For a given pack, tells if it has at least one education using the advanced stock management
     *
     * @param int $idEducation id_pack
     *
     * @return bool
     */
    public static function usesAdvancedStockManagement($idEducation)
    {
         return false;
    }

    /**
     * For a given pack, tells if all educations using the advanced stock management
     *
     * @param int $idEducation id_pack
     *
     * @return bool
     */
    public static function allUsesAdvancedStockManagement($idEducation)
    {
        return false;
    }

    /**
     * Returns Packs that conatins the given education in the right declinaison.
     *
     * @param integer $idItem          Education item id that could be contained in a|many pack(s)
     * @param integer $idAttributeItem The declinaison of the education
     * @param integer $idLang
     *
     * @return array[Education] Packs that contains the given education
     */
    public static function getPacksContainingItem($idItem, $idAttributeItem, $idLang)
    {
        if (!EducationPack::isFeatureActive() || !$idItem) {
            return [];
        }

        $query = 'SELECT `id_education_pack`, `quantity` FROM `'._DB_PREFIX_.'education_pack`
			WHERE `id_education_item` = '.((int) $idItem);
        
         $query .= ' AND `id_education_attribute_item` = '.((int) $idAttributeItem);
        
        $result = Db::getInstance()->executeS($query);
        $arrayResult = [];
        foreach ($result as $row) {
            $p = new Education($row['id_education_pack'], true, $idLang);
            $arrayResult[] = $p;
        }

        return $arrayResult;
    }
}
