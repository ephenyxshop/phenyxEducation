<?php

class TopMenuColumnCore extends ObjectModel {

    public $id;
	public $reference;
	public $parent_reference;
	public $wrap_reference;
    public $id_topmenu_columns_wrap;
    public $id_topmenu;
    public $id_education_type;
    public $id_cms;
    public $id_cms_category;
    public $id_specific_page;
    public $custom_hook;
    public $name;
    public $link;
    public $active = 1;
    public $active_desktop = 1;
    public $active_mobile = 1;
    public $type;
    public $privacy;
    public $have_icon;
    public $image_type;
    public $image_legend;
    public $custom_class;
    public $img_value_over;
    public $value_over;
    public $value_under;
    public $id_topmenu_depend;
    public $target;
    public $is_column = true;
    public $position = 0;

    public static $definition = [
        'table'     => 'topmenu_columns',
        'primary'   => 'id_topmenu_column',
        'multishop' => false,
        'multilang' => true,
        'fields'    => [
			'reference'	 	=> ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'parent_reference'	 	=> ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'wrap_reference'	 	=> ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
            'id_topmenu_columns_wrap' => ['type' => self::TYPE_INT, 'required' => true],
            'id_topmenu'              => ['type' => self::TYPE_INT, 'required' => true],
            'id_education_type'       => ['type' => self::TYPE_INT],
            'id_cms'                  => ['type' => self::TYPE_INT],
            'id_cms_category'         => ['type' => self::TYPE_INT],
            'id_specific_page'        => ['type' => self::TYPE_INT],
            'custom_hook'             => ['type' => self::TYPE_STRING],
            'id_topmenu_depend'       => ['type' => self::TYPE_INT],
            'position'                => ['type' => self::TYPE_INT],
            'privacy'                 => ['type' => self::TYPE_INT],
            'active'                  => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'active_desktop'          => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'active_mobile'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
            'target'                  => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'type'                    => ['type' => self::TYPE_INT, 'required' => true],
            'custom_class'            => ['type' => self::TYPE_STRING, 'size' => 255],

            'name'                    => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName', 'size' => 255],
            'link'                    => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'size' => 255],
            'img_value_over'          => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'],
            'value_over'              => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'],
            'value_under'             => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'],
            'have_icon'               => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isBool'],
            'image_type'              => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString'],
            'image_legend'            => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCatalogName'],
        ],
    ];
    public function __construct($id_topmenu_column = null, $id_lang = null) {

        parent::__construct($id_topmenu_column, $id_lang);
        $this->chosen_groups = Tools::jsonDecode($this->chosen_groups);
    }
	
	public static function getReferentTopMenuColumn() {

		
		return Db::getInstance()->executeS(
			'SELECT `id_topmenu_column`, `reference`
            FROM `' . _DB_PREFIX_ . 'topmenu_columns`
            ORDER BY `id_topmenu_column`'
		);

	}
	
	public function add($autodate = true, $nullValues = false) {

        if (empty($this->reference)) {
            $this->reference = $this->generateReference();
        }
		if (empty($this->parent_reference)) {
			$topmenu = new TopMenu($this->id_topmenu);
			$this->parent_reference = $topmenu->reference;
		}
		if (empty($this->wrap_reference)) {
			$topmenuWrap = new TopMenuColumnWrap($this->id_topmenu_columns_wrap);
			$this->wrap_reference = $topmenuWrap->reference;
		}
        return parent::add($autodate, $nullValues);
    }

    public function getTranslationsFieldsChild() {

        parent::validateFieldsLang();
        $fieldsArray = [
            'name', 'link', 'have_icon', 'image_type', 'image_legend', 
        ];
        $fields = [];
        $languages = Language::getLanguages(false);
        $defaultLanguage = Configuration::get('PS_LANG_DEFAULT');

        foreach ($languages as $language) {
            $fields[$language['id_lang']]['id_lang'] = $language['id_lang'];
            $fields[$language['id_lang']][$this->identifier] = (int) $this->id;
            $fields[$language['id_lang']]['img_value_over'] = (isset($this->img_value_over[$language['id_lang']])) ? pSQL($this->img_value_over[$language['id_lang']], true) : '';
            $fields[$language['id_lang']]['value_over'] = isset($this->value_over[$language['id_lang']]) ? pSQL($this->value_over[$language['id_lang']], true) : '';
            $fields[$language['id_lang']]['value_under'] = isset($this->value_under[$language['id_lang']]) ? pSQL($this->value_under[$language['id_lang']], true) : '';

            foreach ($fieldsArray as $field) {

                if (!Validate::isTableOrIdentifier($field)) {
                    die(Tools::displayError());
                }

                if (isset($this->{$field}

                    [$language['id_lang']]) and !empty($this->{$field}

                    [$language['id_lang']])) {
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}

                        [$language['id_lang']]);
                } else

                if (in_array($field, $this->fieldsRequiredLang)) {
                    $fields[$language['id_lang']][$field] = pSQL($this->{$field}

                        [$defaultLanguage]);
                } else {
                    $fields[$language['id_lang']][$field] = '';
                }

            }

        }

        return $fields;
    }

    public function delete() {

        $languages = Language::getLanguages(false);

        foreach ($languages as $language) {

            if (file_exists(_PS_ROOT_DIR_ . '/modules/topmenu/column_icons/' . (int) $this->id . '-' . $language['iso_code'] . '.' . (isset($this->image_type[$language['id_lang']]) && !preg_match('/^i-(fa|mi)$/', $this->image_type[$language['id_lang']]) ? $this->image_type[$language['id_lang']] : 'jpg'))) {
                @unlink(_PS_ROOT_DIR_ . '/modules/topmenu/column_icons/' . (int) $this->id . '-' . $language['iso_code'] . '.' . (isset($this->image_type[$language['id_lang']]) && !preg_match('/^i-(fa|mi)$/', $this->image_type[$language['id_lang']]) ? $this->image_type[$language['id_lang']] : 'jpg'));
            }

        }

        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'topmenu_columns` WHERE `id_topmenu_column`=' . (int) $this->id);
        Db::getInstance()->Execute('DELETE FROM `' . _DB_PREFIX_ . 'topmenu_columns_lang` WHERE `id_topmenu_column`=' . (int) $this->id);
        $elements = TopMenuElements::getElementIds((int) $this->id);

        foreach ($elements as $id_topmenu_elements) {
            $obj = new TopMenuElements($id_topmenu_elements);
            $obj->delete();
        }

        

        if (Validate::isLoadedObject($productElementsObj)) {
            $productElementsObj->delete();
        }

        return true;
    }

    public static function getIdColumnCmsCategoryDepend($id_menu, $id_cms_category) {

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_topmenu_column`
                FROM `' . _DB_PREFIX_ . 'topmenu_columns`
                WHERE `id_topmenu_depend` = ' . (int) $id_menu . ' AND `id_cms_category` = ' . (int) $id_cms_category);
    }

    public static function getIdMenuByIdColumn($id_topmenu_column) {

        return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT `id_topmenu`
                FROM `' . _DB_PREFIX_ . 'topmenu_columns`
                WHERE `id_topmenu_column` = ' . (int) $id_topmenu_column . '');
    }

    public static function columnHaveDepend($id_topmenu_column) {

        $sql = 'SELECT `id_topmenu_column`
                FROM `' . _DB_PREFIX_ . 'topmenu_elements`
                WHERE `id_column_depend` = ' . (int) $id_topmenu_column;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getMenuColums($id_topmenu_columns_wrap, $id_lang, $active = true, $groupRestrict = false) {

        $sql_groups_join = '';
        $sql_groups_where = '';

        $sql = 'SELECT atmc.`id_topmenu_column` as id_column, atmc.*, atmcl.*,
                cl.link_rewrite, cl.meta_title, etl.name as category_name, etl.link_rewrite as category_link_rewrite
                FROM `' . _DB_PREFIX_ . 'topmenu_columns` atmc
                LEFT JOIN `' . _DB_PREFIX_ . 'topmenu_columns_lang` atmcl ON (atmc.`id_topmenu_column` = atmcl.`id_topmenu_column` AND atmcl.`id_lang` = ' . (int) $id_lang . ')
                 LEFT JOIN ' . _DB_PREFIX_ . 'education_type_lang etl ON (etl.id_education_type = atmc.id_education_type AND etl.id_lang = ' . (int) $id_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'cms c ON (c.id_cms = atmc.`id_cms`)
                ' . Shop::addSqlAssociation('cms', 'c', false) . '
                LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $id_lang . ')

                WHERE ' . ($active ? ' atmc.`active` = 1 AND (atmc.`active_desktop` = 1 || atmc.`active_mobile` = 1) AND ' : '') . ' atmc.`id_topmenu_columns_wrap` = ' . (int) $id_topmenu_columns_wrap . '
                ' . ($active ? 'AND ((atmc.`id_cms` = 0)
                OR c.id_cms IS NOT NULL )' : '')
            . $sql_groups_where . '
                GROUP BY atmc.`id_topmenu_column`
                ORDER BY atmc.`position`';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getMenuColumsByIdMenu($id_menu, $id_lang, $active = true, $groupRestrict = false) {

        $sql_groups_join = '';
        $sql_groups_where = '';

        $sql = 'SELECT atmc.*, atmcl.*,
                cl.link_rewrite, cl.meta_title,
                cal.link_rewrite as category_link_rewrite
                FROM `' . _DB_PREFIX_ . 'topmenu_columns` atmc
                LEFT JOIN `' . _DB_PREFIX_ . 'topmenu_columns_lang` atmcl ON (atmc.`id_topmenu_column` = atmcl.`id_topmenu_column` AND atmcl.`id_lang` = ' . (int) $id_lang . ')
                LEFT JOIN ' . _DB_PREFIX_ . 'cms c ON (c.id_cms = atmc.`id_cms`)
                ' . Shop::addSqlAssociation('cms', 'c', false) . '
                LEFT JOIN ' . _DB_PREFIX_ . 'cms_lang cl ON (c.id_cms = cl.id_cms AND cl.id_lang = ' . (int) $id_lang . ')
                WHERE ' . ($active ? ' atmc.`active` = 1 AND (atmc.`active_desktop` = 1 || atmc.`active_mobile` = 1) AND' : '') . ' atmc.`id_topmenu` = ' . (int) $id_menu . '
                ' . ($active ? 'AND (( atmc.`id_cms` = 0)
                OR c.id_cms IS NOT NULL)' : '')
            . $sql_groups_where . '
                AND atmc.`type` != 8
                GROUP BY atmc.`id_topmenu_column`
                ORDER BY atmc.`position`';

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getMenusColums($menus, $id_lang, $groupRestrict = false) {

        $columns = [];

        foreach ($menus as $columnsWrap) {

            foreach ($columnsWrap as $columnWrap) {
                $columnInfos = self::getMenuColums($columnWrap['id_topmenu_columns_wrap'], $id_lang, true, $groupRestrict);

                

                $columns[$columnWrap['id_topmenu_columns_wrap']] = $columnInfos;
            }

        }

        return $columns;
    }

    public static function getColumnIds($ids_wrap) {

        if (!is_array($ids_wrap)) {
            $ids_wrap = [(int) $ids_wrap];
        }

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS('
        SELECT `id_topmenu_column`
        FROM ' . _DB_PREFIX_ . 'topmenu_columns
        WHERE `id_topmenu_columns_wrap` IN(' . implode(',', array_map('intval', $ids_wrap)) . ')');
        $columns = [];

        foreach ($result as $row) {
            $columns[] = $row['id_topmenu_column'];
        }

        return $columns;
    }

    public static function getnbColumninWrap($idColumnWrap) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('SELECT COUNT(id_topmenu_column)  FROM `eph_topmenu_columns` WHERE `id_topmenu_columns_wrap` = ' . $idColumnWrap);
    }

    public static function getColumnsFromIdCms($idCms) {

        $sql = 'SELECT atp.`id_topmenu_column`
        FROM `' . _DB_PREFIX_ . 'topmenu_columns` atp
        WHERE atp.`active` = 1
        AND atp.`type` = 1
        AND atp.`id_cms` = ' . (int) $idCms;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getColumnsFromIdCmsCategory($idCmsCategory) {

        $sql = 'SELECT atp.`id_topmenu_column`
        FROM `' . _DB_PREFIX_ . 'topmenu_columns` atp
        WHERE atp.`active` = 1
        AND atp.`type` = 10
        AND atp.`id_cms_category` = ' . (int) $idCmsCategory;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function getColumnsFromIdProduct($idProduct) {

        $sql = 'SELECT atc.`id_topmenu_column`
        FROM `' . _DB_PREFIX_ . 'topmenu_columns` atc
        JOIN `' . _DB_PREFIX_ . 'topmenu_prod_column` atp
        ON atc.`id_topmenu_column` = atp.`id_topmenu_column`
        WHERE atc.`active` = 1
        AND atc.`type` = 8
        AND atp.`id_product` = ' . (int) $idProduct;
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->ExecuteS($sql);
    }

    public static function disableById($idColumn) {

        return Db::getInstance()->update('topmenu_columns', [
            'active' => 0,
        ], 'id_topmenu_column = ' . (int) $idColumn);
    }
	
	public static function getIdTopMenuColumnByRef($reference) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_topmenu_column`')
                ->from('topmenu_columns')
                ->where('`reference` LIKE \'' . $reference . '\'')
        );
    }
	
	public static function getIdParentWrapByRef($reference) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_topmenu_columns_wrap`')
                ->from('topmenu_columns_wrap')
                ->where('`reference` LIKE \'' . $reference . '\'')
        );
    }
	
	public static function getIdTopMenuByColumnRef($parentReference) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_topmenu`')
                ->from('topmenu')
                ->where('`reference` LIKE \'' . $parentReference . '\'')
        );
    }

}
