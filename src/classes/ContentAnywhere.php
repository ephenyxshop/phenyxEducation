<?php

class ContentAnywhere extends ObjectModel {

    public $id_vccontentanywhere;
    public $active = 1;
    public $hook_name;
    public $display_type;
    public $prd_page;
    public $prd_specify;
    public $cat_page;
    public $content_type;
    public $modules_list;
    public $module_hook_list;
    public $cat_specify;
    public $cms_page;
    public $cms_specify;
    public $exception;
    public $exception_type;
    public $position;
    public $title;
    public $content;
    public $skip_modules = ['jscomposer', 'revsliderPhenyxShop', 'smartshortcode'];
    public static $definition = [
        'table'     => 'vccontentanywhere',
        'primary'   => 'id_vccontentanywhere',
        'multilang' => true,
        'fields'    => [
            'display_type'     => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'content_type'     => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'modules_list'     => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'module_hook_list' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'prd_page'         => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'prd_specify'      => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'cat_page'         => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'cat_specify'      => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'cms_page'         => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'cms_specify'      => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
            'position'         => ['type' => self::TYPE_INT],
            'active'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'hook_name'        => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true],

            'title'            => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isString', 'required' => true],
            'content'          => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isString'],
            'exception'        => ['type' => self::TYPE_HTML, 'validate' => 'isString'],
            'exception_type'   => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
        ],
    ];

    public function __construct($id = null, $id_lang = null, $id_shop = null) {

        Shop::addTableAssociation('vccontentanywhere', ['type' => 'shop']);
        parent::__construct($id, $id_lang, $id_shop);
    }

    public function add($autodate = true, $null_values = false) {

        if ($this->position <= 0) {
            $this->position = ContentAnywhere::getHigherPosition() + 1;
        }

        if (!parent::add($autodate, $null_values) || !Validate::isLoadedObject($this)) {
            return false;
        }

        $this->clearCache();
        return true;
    }

    public static function getHigherPosition() {

        $sql = 'SELECT MAX(`position`)
                FROM `' . _DB_PREFIX_ . 'vccontentanywhere`';
        $position = DB::getInstance()->getValue($sql);
        return (is_numeric($position)) ? $position : -1;
    }

    public static function GetInstance() {

        $ins = new ContentAnywhere();
        return $ins;
    }

    public function GetVcContentAnyWhereByHookPageFilter($hook_name = '', $page = '', $id_page_value = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ' AND v.`active` = 1)
                WHERE ';

        $id_page_value = pSQL($id_page_value);

        // There is a opening ( brace, which need to close properly..remember.
        $sql .= 'v.`hook_name` = "' . $hook_name . '" AND v.`active` = 1 AND ( ';
        // with Set exception is ON
        $sql .= '( v.`exception_type` = 1 AND v.`exception` LIKE "%' . $page . '%") ';
        // without exception, but Show All Page in ON
        $sql .= 'OR ( v.`exception_type` != 1 AND v.`display_type`= 1 ) ';

        // without exception, but Show All Page in OFF

        // Show All Product Page is ON

        if ($page == 'product') {
            $sql .= 'OR ( v.`exception_type` != 1 AND v.`display_type` = 0  AND v.`prd_page` = 1 ) ';
        }

        // Show All Category Page is ON

        if ($page == 'category') {
            $sql .= 'OR ( v.`exception_type` != 1 AND v.`display_type` = 0  AND v.`cat_page` = 1 ) ';
        }

        // Show All CMS Page is ON

        if ($page == 'cms') {
            $sql .= 'OR ( v.`exception_type` != 1 AND v.`display_type` = 0  AND v.`cms_page` = 1 ) ';
        }

        // closing brace ) //'v.`hook_name` = "' . $hook_name . '" AND v.`active` = 1 AND ( '
        $sql .= ') ';

        // filters for product, category and cms page where cms_page, cat_page, prd_page are OFF

        if ($id_page_value && ($page == 'product' || $page == 'category' || $page == 'cms')) {

            $sql .= 'UNION
             SELECT v_1.*,vl_1.content,vs_1.id_shop FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v_1
                        INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl_1 ON (v_1.`id_vccontentanywhere` = vl_1.`id_vccontentanywhere` AND vl_1.`id_lang` = ' . $id_lang . ')
                        INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs_1 ON (v_1.`id_vccontentanywhere` = vs_1.`id_vccontentanywhere` AND vs_1.`id_shop` = ' . $id_shop . ' AND v_1.`active` = 1)';

            $sql .= ' INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_filter` vs_f_1 ON (vs_f_1.`id_vccontentanywhere` = v_1.`id_vccontentanywhere` )';

            $sql .= ' WHERE ';

            $sql .= 'v_1.`hook_name` = "' . $hook_name . '" AND v_1.active = 1 AND v_1.`exception_type` != 1';

            if ($page == 'product') {
                $sql .= ' AND (vs_f_1.page = 1 AND vs_f_1.id_specify_page = ' . $id_page_value . ')';
            } else if ($page == 'category') {
                $sql .= ' AND (vs_f_1.page = 2 AND vs_f_1.id_specify_page = ' . $id_page_value . ')';
            } else if ($page == 'cms') {
                $sql .= ' AND (vs_f_1.page = 3 AND vs_f_1.id_specify_page = ' . $id_page_value . ')';
            }

        }

        $sql .= ' ORDER BY `position` ASC';
        $results = Db::getInstance()->executeS($sql);
        return $this->ContentFilterEngine($results);
    }

    public function GetVcContentAnyWhereByHook($hook_name = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAll($hook_name = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 1 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function ModHookExec($mod_name = '', $hook_name = '') {

        $results = '';

        if (Module::isInstalled($mod_name) && Module::isEnabled($mod_name)) {
            $mod_ins = Module::getInstanceByName($mod_name);

            if (!is_object($mod_ins)) {
                return $results;
            }

            $context = Context::getContext();
            $retro_hook_name = Hook::getRetroHookName($hook_name);
            $params = ['cookie' => $context->cookie, 'cart' => $context->cart];

            if (is_callable([
                $mod_ins,
                'hook' . $hook_name,
            ])) {
                $mod_method = 'hook' . $hook_name;
                $results = $mod_ins->$mod_method($params);
            } else if (is_callable([
                $mod_ins,
                'hook' . $retro_hook_name,
            ])) {
                $mod_retro_method = 'hook' . $retro_hook_name;
                $results = $mod_ins->$mod_retro_method($params);
            }

        } else {
            $results = '<strong>' . $mod_name . '</strong> is not install. Please Install <strong>' . $mod_name . '</strong> Module.';
        }

        return $results;
    }

    public function GetVcContentByAllPRD($hook_name = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`prd_page` = 1 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllCAT($hook_name = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`cat_page` = 1 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllCMS($hook_name = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`cms_page` = 1 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllCMSID($hook_name = '', $id_cms = 1) {

        $reslt = [];
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`cms_page` = 0 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);

            if (isset($results) && !empty($results)) {
                $i = 0;

                foreach ($results as $result) {

                    if (isset($result['cms_specify']) && !empty($result['cms_specify'])) {
                        $cms_specify = explode(',', $result['cms_specify']);

                        if (isset($cms_specify) && !empty($cms_specify)) {

                            if (in_array($id_cms, $cms_specify)) {
                                $reslt[$i] = $result;
                            }

                        }

                    }

                    $i++;
                }

            }

            $outputs = $this->ContentFilterEngine($reslt);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllCATID($hook_name = '', $id_category = 1) {

        $reslt = [];
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`cat_page` = 0 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);

            if (isset($results) && !empty($results)) {
                $i = 0;

                foreach ($results as $result) {

                    if (isset($result['cat_specify']) && !empty($result['cat_specify'])) {
                        $cat_specify = explode(',', $result['cat_specify']);

                        if (isset($cat_specify) && !empty($cat_specify)) {

                            if (in_array($id_category, $cat_specify)) {
                                $reslt[$i] = $result;
                            }

                        }

                    }

                    $i++;
                }

            }

            $outputs = $this->ContentFilterEngine($reslt);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllPRDID($hook_name = '', $id_product = 1) {

        $reslt = [];
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vl.content,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`prd_page` = 0 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);

            if (isset($results) && !empty($results)) {
                $i = 0;

                foreach ($results as $result) {

                    if (isset($result['prd_specify']) && !empty($result['prd_specify'])) {
                        $prd_specify = explode('-', $result['prd_specify']);

                        if (isset($prd_specify) && !empty($prd_specify)) {
                            unset($prd_specify[count($prd_specify) - 1]);

                            if (in_array($id_product, $prd_specify)) {
                                $reslt[$i] = $result;
                            }

                        }

                    }

                    $i++;
                }

            }

            $outputs = $this->ContentFilterEngine($reslt);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function GetVcContentByAllPRDCATID($hook_name = '', $id_prd_cat = 1) {

        $reslt = [];
        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`display_type` = 0 AND ';
        $sql .= ' v.`prd_page` = 0 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);

            if (isset($results) && !empty($results)) {
                $i = 0;

                foreach ($results as $result) {

                    if (isset($result['prd_specify']) && !empty($result['prd_specify'])) {
                        $prd_specify = explode(',', $result['prd_specify']);

                        if (isset($prd_specify) && !empty($prd_specify)) {

                            if (in_array('CAT_' . $id_prd_cat, $prd_specify)) {
                                $reslt[$i] = $result;
                            }

                        }

                    }

                    $i++;
                }

            }

            $outputs = $this->ContentFilterEngine($reslt);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public static function getProductsByCategoryID($category_id, $id_lang = null, $id_shop = null, $limit = false, $order_by = 'id_product', $order_way = "DESC") {

        $context = Context::getContext();
        $id_lang = is_null($id_lang) ? $context->language->id : $id_lang;
        $id_shop = is_null($id_shop) ? $context->shop->id : $id_shop;
        $id_supplier = '';
        $active = true;
        $front = true;
        $sql = 'SELECT p.*, product_shop.*, stock.out_of_stock, IFNULL(stock.quantity, 0) as quantity, MAX(product_attribute_shop.id_product_attribute) id_product_attribute, product_attribute_shop.minimal_quantity AS product_attribute_minimal_quantity, pl.`description`, pl.`description_short`, pl.`available_now`,
                                    pl.`available_later`, pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, MAX(image_shop.`id_image`) id_image,
                                    il.`legend`, m.`name` AS manufacturer_name, cl.`name` AS category_default,
                                    DATEDIFF(product_shop.`date_add`, DATE_SUB(NOW(),
                                    INTERVAL ' . (Validate::isUnsignedInt(Configuration::get('PS_NB_DAYS_NEW_PRODUCT')) ? Configuration::get('PS_NB_DAYS_NEW_PRODUCT') : 20) . '
                        DAY)) > 0 AS new, product_shop.price AS orderprice
                FROM `' . _DB_PREFIX_ . 'category_product` cp
                LEFT JOIN `' . _DB_PREFIX_ . 'product` p
                    ON p.`id_product` = cp.`id_product`
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute` pa
                ON (p.`id_product` = pa.`id_product`)
                ' . Shop::addSqlAssociation('product_attribute', 'pa', false, 'product_attribute_shop.`default_on` = 1') . '
                ' . Product::sqlStock('p', 'product_attribute_shop', false, $context->shop) . '
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl
                    ON (product_shop.`id_category_default` = cl.`id_category`
                    AND cl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('cl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl
                    ON (p.`id_product` = pl.`id_product`
                    AND pl.`id_lang` = ' . (int) $id_lang . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image` i
                    ON (i.`id_product` = p.`id_product`)' .
        Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il
                    ON (image_shop.`id_image` = il.`id_image`
                    AND il.`id_lang` = ' . (int) $id_lang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m
                    ON m.`id_manufacturer` = p.`id_manufacturer`
                WHERE product_shop.`id_shop` = ' . (int) $context->shop->id . '
                    AND cp.`id_category` = ' . (int) $category_id
            . ($active ? ' AND product_shop.`active` = 1' : '')
            . ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '')
            . ($id_supplier ? ' AND p.id_supplier = ' . (int) $id_supplier : '')
            . ' GROUP BY product_shop.id_product';

        if (empty($order_by) || $order_by == 'position') {
            $order_by_prefix = 'cp';
        }

        if (empty($order_way)) {
            $order_way = 'DESC';
        }

        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } else
        if ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }

        $sql .= " ORDER BY {$order_by_prefix}.{$order_by} {$order_way}";

        if (!empty($limit) && is_numeric($limit)) {
            $sql .= " LIMIT {$limit}";
        }

        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (!$result) {
                return [];
            }

            $outputs = Product::getProductsProperties($id_lang, $result);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function getSimpleProducts() {

        $context = Context::getContext();
        $id_lang = (int) Context::getContext()->language->id;
        $front = true;

        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        $sql = 'SELECT p.`id_product`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                ' . ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') . '
                ORDER BY pl.`name`';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $outputs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public function getFilterValueByContentAnyWhereId($id, $page) {

        $sql = 'SELECT page, id_specify_page
                FROM `' . _DB_PREFIX_ . 'vccontentanywhere_filter`

                 WHERE  `id_vccontentanywhere` = ' . (int) $id . ' AND page =' . $page;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $rs;
    }

    public function getProductsById($ids) {

        $context = Context::getContext();
        $id_lang = (int) Context::getContext()->language->id;
        $front = true;

        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        if (empty($ids)) {
            return false;
        }

        $sqlids = '';

        if (is_string($ids)) {
            $ids = explode('-', $ids);
            unset($ids[count($ids) - 1]);

            foreach ($ids as $k => $id) {

                if ($k > 0) {
                    $sqlids .= ',';
                }

                $sqlids .= $id;
            }

        } else {

            foreach ($ids as $k => $id) {
                // print_r($id['id_specify_page']);

                if ($k > 0) {
                    $sqlids .= ',';
                }

                $sqlids .= $id['id_specify_page'];
            }

        }

        $limit = Tools::getValue('limit') ? pSQL(Tools::getValue('limit')) : 60;

        $sql = 'SELECT p.`id_product`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                ' . ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ' AND p.`id_product` IN(' . $sqlids . ')' .
            'ORDER BY pl.`name` LIMIT ' . $limit;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        $rslt = [];

        foreach ($rs as $i => $r) {
            $rslt[$i]['id_product'] = $r['id_product'];
            $rslt[$i]['name'] = $r['name'];
            $i++;
        }

        return $rslt;
    }

    public function getProductsByName() {

        $context = Context::getContext();
        $id_lang = (int) $context->language->id;
        $front = true;

        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        $q = Tools::getValue('q');
        $exid = Tools::getValue('excludeIds');
        $limit = Tools::getValue('limit');
        $exSql = '';

        if (!empty($exid)) {
            $exid = substr($exid, strlen($exid) - 1) == ',' ? substr($exid, 0, strrpos($exid, ',')) : $exid;
            $exSql .= ' AND p.`id_product` NOT IN(';
            $exSql .= $exid;
            $exSql .= ') ';
        }

        $sql = 'SELECT p.`id_product`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                ' . ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
        ' AND pl.`name` LIKE "%' . pSQL($q) . '%" ' . $exSql .
            'ORDER BY pl.`name` LIMIT ' . $limit;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        $rslt = '';

        foreach ($rs as $r) {
            $rslt .= $r['name'] . '&nbsp;|';
            $rslt .= $r['id_product'] . "\n";
        }

        return $rslt;
    }

    public function getCatsByName() {

        $context = Context::getContext();
        $id_lang = (int) $context->language->id;
        $limit = Tools::getValue('limit');
        $q = Tools::getValue('q');
        $exid = Tools::getValue('excludeIds');

        $exSql = '';

        if (!empty($exid)) {
            $exid = substr($exid, strlen($exid) - 1) == ',' ? substr($exid, 0, strrpos($exid, ',')) : $exid;
            $exSql .= ' AND p.`id_category` NOT IN(';
            $exSql .= $exid;
            $exSql .= ') ';
        }

        $sql = 'SELECT p.`id_category`, pl.`name`
                FROM `' . _DB_PREFIX_ . 'category` p
                LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` pl ON (p.`id_category` = pl.`id_category` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang . '
                 AND pl.`name` LIKE "%' . pSQL($q) . '%" ' . $exSql .
            'ORDER BY pl.`name` ASC LIMIT ' . $limit;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        $rslt = '';

        foreach ($rs as $r) {
            $rslt .= $r['name'] . '&nbsp;|';
            $rslt .= $r['id_category'] . "\n";
        }

        return $rslt;
    }

    public function getManufacturersByName() {

//        $context = Context::getContext();

        $q = Tools::getValue('q');
        $exid = Tools::getValue('excludeIds');
        $limit = Tools::getValue('limit');
        $exSql = '';

        if (!empty($exid)) {
            $exid = substr($exid, strlen($exid) - 1) == ',' ? substr($exid, 0, strrpos($exid, ',')) : $exid;
            $exSql .= ' AND p.`id_manufacturer` NOT IN(';
            $exSql .= $exid;
            $exSql .= ') ';
        }

        $sql = 'SELECT p.`id_manufacturer`, p.`name`
                FROM `' . _DB_PREFIX_ . 'manufacturer` p
                    ' . Shop::addSqlAssociation('manufacturer', 'p') . '
                WHERE
                  p.`name` LIKE "%' . pSQL($q) . '%" ' . $exSql .
            'ORDER BY p.`name` ASC LIMIT ' . $limit;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        $rslt = '';

        foreach ($rs as $r) {
            $rslt .= $r['name'] . '&nbsp;|';
            $rslt .= $r['id_manufacturer'] . "\n";
        }

        return $rslt;
    }

    public function getSuppliersByName() {

//        $context = Context::getContext();

        $q = Tools::getValue('q');
        $exid = Tools::getValue('excludeIds');
        $limit = Tools::getValue('limit');
        $exSql = '';

        if (!empty($exid)) {
            $exid = substr($exid, strlen($exid) - 1) == ',' ? substr($exid, 0, strrpos($exid, ',')) : $exid;
            $exSql .= ' AND p.`id_supplier` NOT IN(';
            $exSql .= $exid;
            $exSql .= ') ';
        }

        $sql = 'SELECT p.`id_supplier`, p.`name`
                FROM `' . _DB_PREFIX_ . 'supplier` p
                    ' . Shop::addSqlAssociation('supplier', 'p') . '
                WHERE
                  p.`name` LIKE "%' . pSQL($q) . '%" ' . $exSql .
            'ORDER BY p.`name` ASC LIMIT ' . $limit;

        $rs = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);
        $rslt = '';

        foreach ($rs as $r) {
            $rslt .= $r['name'] . '&nbsp;|';
            $rslt .= $r['id_supplier'] . "\n";
        }

        return $rslt;
    }

    public static function getSelectedCategories($id_categories) {

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $table_identifier = 'c';
        $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'category` c';

        if (Tools::version_compare(_PS_VERSION_, '1.6.1.0', '<')) {
            $sql .= Shop::addSqlAssociation('category', 'c');
        }

        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl ON c.`id_category` = cl.`id_category`';

        if (Tools::version_compare(_PS_VERSION_, '1.6.1.0', '>=')) {
            $sql .= Shop::addSqlRestrictionOnLang('cl');
        }

        $sql .= ' WHERE 1 ' . ($id_lang ? 'AND `id_lang` = ' . (int) $id_lang : '') . '
            AND c.`id_category` IN(' . $id_categories . ')
            AND `active` = 1
            ORDER BY FIELD(' . $table_identifier . '.id_category,' . $id_categories . ')';

        $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        return $result;
    }

    public static function getSelectedProducts($id_products, $order_by = null, $order_way = null) {

        $context = Context::getContext();
        $id_lang = $context->language->id;
        $front = true;

        if (!in_array($context->controller->controller_type, ['front', 'modulefront'])) {
            $front = false;
        }

        $str = $id_products;

        if ($order_by == 'id_product' || $order_by == 'price' || $order_by == 'date_add' || $order_by == 'date_upd') {
            $order_by_prefix = 'p';
        } else if ($order_by == 'name') {
            $order_by_prefix = 'pl';
        }

        $sql = 'SELECT p.*, product_shop.*, pl.*, image_shop.`id_image`, il.`legend`, m.`name` AS manufacturer_name, s.`name` AS supplier_name
                FROM `' . _DB_PREFIX_ . 'product` p
                ' . Shop::addSqlAssociation('product', 'p') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` pl ON (p.`id_product` = pl.`id_product` ' . Shop::addSqlRestrictionOnLang('pl') . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'manufacturer` m ON (m.`id_manufacturer` = p.`id_manufacturer`)
                LEFT JOIN `' . _DB_PREFIX_ . 'supplier` s ON (s.`id_supplier` = p.`id_supplier`)
                                LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_product` = p.`id_product`)' .
        Shop::addSqlAssociation('image', 'i', false, 'image_shop.cover=1') . '
                LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il ON (i.`id_image` = il.`id_image` AND il.`id_lang` = ' . (int) Context::getContext()->language->id . ')
                WHERE pl.`id_lang` = ' . (int) $id_lang .
            ' AND p.`id_product` IN( ' . $str . ')' .
            ($front ? ' AND product_shop.`visibility` IN ("both", "catalog")' : '') .
            ' AND ((image_shop.id_image IS NOT NULL OR i.id_image IS NULL) OR (image_shop.id_image IS NULL AND i.cover=1))' .
            ' AND product_shop.`active` = 1';

        if (!empty($order_by) && isset($order_by_prefix)) {
            $sql .= " ORDER BY {$order_by_prefix}.{$order_by} {$order_way}";
        }

        $rq = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

        if (!$rq) {
            return [];
        }

        return Product::getProductsProperties($id_lang, $rq);
    }

    public function getproduct() {

        $rslt[0]['id_product'] = '0';
        $rslt[0]['name'] = 'None';
        $rs = $this->getSimpleProducts();
        $i = 1;

        foreach ($rs as $r) {
            $rslt[$i]['id_product'] = $r['id_product'];
            $rslt[$i]['name'] = $r['name'];
            $i++;
        }

        return $rslt;
    }

    public function getAllCMSPage() {

        $results = [];
        $results[0]['id_cms'] = 'none';
        $results[0]['name'] = 'None';
        $i = 1;
        $allcategories = CMS::listCms();

        foreach ($allcategories as $value) {
            $results[$i]['id_cms'] = $value['id_cms'];
            $results[$i]['name'] = $value['meta_title'];
            $i++;
        }

        return $results;
    }

    public function GetModuleHook($module_name = '', $hook_name = '') {

        $results = '';

        if (isset($module_name) && !empty($module_name)) {
            $hooks = $this->getModuleHooks($module_name);

            if (isset($hooks) && !empty($hooks)) {

                foreach ($hooks as $hook) {

                    if (isset($hook_name) && !empty($hook_name) && $hook['name'] == $hook_name) {
                        $results .= '<option value="' . $hook['name'] . '"selected="selected">' . $hook['name'] . "</option>";
                    } else {
                        $results .= '<option value="' . $hook['name'] . '">' . $hook['name'] . "</option>";
                    }

                }

            }

        }

        return $results;
    }

    public function getActiveModules() {

        $id_shop = Context::getcontext()->shop->id;
        $sql = 'SELECT m.`name` FROM `' . _DB_PREFIX_ . 'module` m
        JOIN `' . _DB_PREFIX_ . 'module_shop` ms ON (m.`id_module` = ms.`id_module` AND ms.`id_shop` = ' . (int) ($id_shop) . ')
            WHERE m.active=1';

        $results = Db::getInstance()->executeS($sql);

        $return = [];

        if (!empty($results)) {

            foreach ($results as $module) {
                $return[] = $module['name'];
            }

        }

        return $return;
    }

    public function GetAllModules() {

        $activeModules = $this->getActiveModules();

        require dirname(__FILE__) . '/../config/modules_list.php';

        $include = Configuration::get('vc_include_modules');
        $exclude = Configuration::get('vc_exclude_modules');

        if (!empty($include)) {
            $include = explode("\n", $include);

            foreach ($include as $inc) {
                $inc = trim($inc);

                if (Validate::isModuleName($inc) && in_array($inc, $activeModules) && !in_array($inc, $modules_list) && !in_array($inc, $this->skip_modules)
                ) {
                    $modules_list[] = $inc;
                }

            }

        }

        if (!empty($exclude)) {
            $exclude = explode("\n", $exclude);

            foreach ($exclude as $inc) {
                $inc = trim($inc);

                if (Validate::isModuleName($inc) && in_array($inc, $activeModules) && ($index = array_search($inc, $modules_list)) !== FALSE
                ) {
                    unset($modules_list[$index]);
                }

            }

        }

        return $modules_list;
    }

    public function GetAllFilterModules() {

        $results = [];

        $AllModules = $this->GetAllModules();

        if (isset($AllModules) && !empty($AllModules)) {
            $i = 0;

            foreach ($AllModules as $mod) {
//                if(!in_array($mod['name'],$this->skip_modules)){

                if ($this->getModuleHooks($mod)) {
                    $results[$i]['id'] = $mod;
                    $results[$i]['name'] = Module::getModuleName($mod);
                    $i++;
                }

//                }
            }

        }

        return $results;
    }

    public function GetAllHooks() {

        $results = [];
        $support_hooks = [];
        require _PS_MODULE_DIR_ . 'jscomposer/include/helpers/support_hooks.php';

        if (isset($support_hooks) && !empty($support_hooks)) {
            $i = 0;

            foreach ($support_hooks as $value) {

                if (!empty($value)) {
                    $results[$i]['id'] = Hook::getRetroHookName($value);
                    $results[$i]['name'] = Hook::getRetroHookName($value);
                    $i++;
                }

            }

        }

        return $results;
    }

    public function getModuleHooks($module = '') {

        $support_hooks = [];
        require _PS_MODULE_DIR_ . 'jscomposer/include/helpers/support_hooks.php';
        $module_Ins = Module::getInstanceByName($module);
        $hooks = [];

        if (isset($support_hooks) && !empty($support_hooks)) {

            foreach ($support_hooks as $support_hook) {
                $support_retro_hook = Hook::getRetroHookName($support_hook);

                if (is_callable([$module_Ins, 'hook' . $support_hook]) || is_callable([$module_Ins, 'hook' . $support_retro_hook])) {

                    if (empty($support_retro_hook)) {
                        $support_retro_hook = $support_hook;
                    }

//                    $hooks[] = $support_retro_hook;
                    $hooks[] = ['id' => $support_retro_hook, 'name' => $support_retro_hook];
                }

            }

        }

//        $results = $this->GetHookName($hooks);

        return $hooks;
    }

    public function getModuleHookbyedit($module = '') {

        $reslt = [];
        $support_hooks = [];
        require _PS_MODULE_DIR_ . 'jscomposer/include/helpers/support_hooks.php';
        $module_Ins = Module::getInstanceByName($module);
        $hooks = [];

        if (isset($support_hooks) && !empty($support_hooks)) {

            foreach ($support_hooks as $support_hook) {
                $support_retro_hook = Hook::getRetroHookName($support_hook);

                if (is_callable([$module_Ins, 'hook' . $support_hook]) || is_callable([$module_Ins, 'hook' . $support_retro_hook])) {

                    if (empty($support_retro_hook)) {
                        $support_retro_hook = $support_hook;
                    }

                    $hooks[] = ['id' => $support_retro_hook, 'name' => $support_retro_hook];
                }

            }

        }

        return $hooks;

//        $results = $this->GetHookName($hooks);
        //        if(isset($results) && !empty($results)){
        //            $i=0;
        //            foreach($results as $vchook){
        //               $reslt[$i]['name'] = $vchook['name'];
        //               $reslt[$i]['id'] = $vchook['name'];
        //               $i++;
        //            }
        //        }
        //        return $reslt;
    }

    public function GetHookName($hooks = []) {

        $results = [];

        if (isset($hooks) && !empty($hooks)) {
            $sql = 'SELECT `id_hook`, `name`
            FROM `' . _DB_PREFIX_ . 'hook`
            WHERE `name` IN (\'' . implode("','", $hooks) . '\')';
            $cache_id = md5($sql);

            if (!Cache::isStored($cache_id)) {
                $results = Db::getInstance()->ExecuteS($sql);
                Cache::store($cache_id, $results);
            }

            return Cache::retrieve($cache_id);
        } else {
            return $results;
        }

    }

    public function GetModulesList($type = 'module', $mod_name = '') {

        $GetAllmodules_list = [];
        $modules_list = $this->getAllModules();

        if ($type == 'module') {

            if (isset($modules_list) && !empty($modules_list)) {
                $i = 0;

                foreach ($modules_list as $key => $value) {

                    if (Module::isInstalled($key)) {
                        $GetAllmodules_list[$i]['id'] = $key;
                        $GetAllmodules_list[$i]['name'] = $key;
                        $i++;
                    }

                }

            }

        } else if ($type == 'hook') {

            if (isset($modules_list[$mod_name]) && !empty($modules_list[$mod_name])) {
                $i = 0;

                foreach ($modules_list[$mod_name] as $key => $value) {

                    if (Module::isInstalled($key)) {
                        $GetAllmodules_list[$i]['id'] = $value;
                        $GetAllmodules_list[$i]['name'] = $value;
                        $i++;
                    }

                }

            }

        }

        return $GetAllmodules_list;
    }

    public function getProductCategories($id_product = 1) {

        $reslt = [];
        $sql = 'SELECT cp.`id_category` AS id
            FROM `' . _DB_PREFIX_ . 'category_product` cp
            LEFT JOIN `' . _DB_PREFIX_ . 'category` c ON (c.id_category = cp.id_category)
            ' . Shop::addSqlAssociation('category', 'c') . '
            WHERE cp.`id_product` = ' . (int) $id_product;
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);

            if (isset($results) && !empty($results)) {

                foreach ($results as $result) {
                    $reslt[] = $result['id'];
                }

            }

            Cache::store($cache_id, $reslt);
        }

        return Cache::retrieve($cache_id);
    }

    public function getAllProductsByCats() {

        $results = [];
        $results[0]['id_product'] = 'none';
        $results[0]['name'] = 'None';
        $i = 1;
        $allcategories = $this->generateCategoriesOption(Category::getNestedCategories(null, (int) Context::getContext()->language->id, true));

        foreach ($allcategories as $value) {
            $results[$i]['id_product'] = 'CAT_' . $value['id_category'];
            $results[$i]['name'] = 'Category-------' . $value['name'];
            $catproducts = self::getProductsByCategoryID($value['id_category']);

            if (isset($catproducts) && !empty($catproducts)) {

                foreach ($catproducts as $catproduct) {
                    $i++;
                    $results[$i]['id_product'] = 'PRD_' . $catproduct['id_product'];
                    $results[$i]['name'] = $catproduct['name'];
                }

            }

            $i++;
        }

        return $results;
    }

    public function generatesubCategoriesOption($categories, $items_to_skip = null) {

        $subcatvals = [];
        $spacer_size = '5';
        $this->element_index++;

        foreach ($categories as $key => $category) {
            $this->smartcat[$this->element_index]['id_category'] = $category['id_category'];
            $this->smartcat[$this->element_index]['name'] = str_repeat('&nbsp;', $spacer_size * (int) $category['level_depth']) . $category['name'];

            if (isset($category['children'])) {
                $this->generatesubCategoriesOption($category['children']);
            }

            $this->element_index++;
        }

        return true;
    }

    public function generateCategoriesOption($categories, $items_to_skip = null) {

        $subcatvals = [];
        $spacer_size = '3';
        $this->smartcat[0]['id_category'] = 'none';
        $this->smartcat[0]['name'] = 'None';
        $this->element_index = 1;

        foreach ($categories as $key => $category) {
            $this->smartcat[$this->element_index]['id_category'] = $category['id_category'];
            $this->smartcat[$this->element_index]['name'] = str_repeat('&nbsp;', $spacer_size * (int) $category['level_depth']) . $category['name'];

            if (isset($category['children'])) {
                $this->generatesubCategoriesOption($category['children']);
            }

            $this->element_index++;
        }

        return $this->smartcat;
    }

    public function ContentFilterEngine($results = []) {

        $outputs = [];

        if (isset($results) && !empty($results)) {
            $i = 0;

            foreach ($results as $vcvalues) {

                foreach ($vcvalues as $vckey => $vcval) {

                    if ($vckey == 'content') {
                        $outputs[$i][$vckey] = JsComposer::vc_content_filter($vcval);
                    } else {
                        $outputs[$i][$vckey] = $vcval;
                    }

                }

                $i++;
            }

        }

        return $outputs;
    }

    public function updatePosition($way, $position) {

        if (!$res = Db::getInstance()->executeS('
            SELECT `id_vccontentanywhere`, `position`
            FROM `' . _DB_PREFIX_ . 'vccontentanywhere`
            ORDER BY `position` ASC'
        )) {
            return false;
        }

        foreach ($res as $vccontentanywhere) {
            if ((int) $vccontentanywhere['id_vccontentanywhere'] == (int) $this->id) {
                $moved_vccontentanywhere = $vccontentanywhere;
            }
        }

        if (!isset($moved_vccontentanywhere) || !isset($position)) {
            return false;
        }

        $query_1 = ' UPDATE `' . _DB_PREFIX_ . 'vccontentanywhere`
        SET `position`= `position` ' . ($way ? '- 1' : '+ 1') . '
        WHERE `position`
        ' . ($way ? '> ' . (int) $moved_vccontentanywhere['position'] . ' AND `position` <= ' . (int) $position : '< ' . (int) $moved_vccontentanywhere['position'] . ' AND `position` >= ' . (int) $position . '
        ');
        $query_2 = ' UPDATE `' . _DB_PREFIX_ . 'vccontentanywhere`
        SET `position` = ' . (int) $position . '
        WHERE `id_vccontentanywhere` = ' . (int) $moved_vccontentanywhere['id_vccontentanywhere'];
        return (Db::getInstance()->execute($query_1) && Db::getInstance()->execute($query_2));
    }

    public function GetVcContentByAllException($hook_name = '', $page = '') {

        $id_lang = (int) Context::getContext()->language->id;
        $id_shop = (int) Context::getContext()->shop->id;
        $sql = 'SELECT v.*,vs.id_shop,v.id_vccontentanywhere FROM `' . _DB_PREFIX_ . 'vccontentanywhere` v
                    INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_lang` vl ON (v.`id_vccontentanywhere` = vl.`id_vccontentanywhere` AND vl.`id_lang` = ' . $id_lang . ')
                    INNER JOIN `' . _DB_PREFIX_ . 'vccontentanywhere_shop` vs ON (v.`id_vccontentanywhere` = vs.`id_vccontentanywhere` AND vs.`id_shop` = ' . $id_shop . ')
                    WHERE ';

        if (isset($hook_name) && !empty($hook_name)) {
            $hook_retro_name = Hook::getRetroHookName($hook_name);
            $sql .= '( v.`hook_name` = "' . $hook_name . '" or v.`hook_name` = "' . $hook_retro_name . '") AND ';
        }

        $sql .= ' v.`exception_type` = 1 AND  v.`exception` LIKE "%' . $page . '%"  AND ';
        $sql .= ' v.`display_type` = 1 AND ';
        $sql .= ' v.`active` = 1 ORDER BY v.`position` ASC';
        $cache_id = md5($sql);

        if (!Cache::isStored($cache_id)) {
            $results = Db::getInstance()->executeS($sql);
            $outputs = $this->ContentFilterEngine($results);
            Cache::store($cache_id, $outputs);
        }

        return Cache::retrieve($cache_id);
    }

    public static function displayModuleExceptionList() {

        $results = [];
        $controllers = Dispatcher::getControllers(_PS_FRONT_CONTROLLER_DIR_);
        ksort($controllers);
        $i = 0;

        if (isset($controllers) && !empty($controllers)) {

            foreach ($controllers as $key => $value) {
                $results[$i]['id_exception'] = $key;
                $results[$i]['name'] = $key;
                $i++;
            }

        }

        $all_modules_controllers = Dispatcher::getModuleControllers('front');

        if (isset($all_modules_controllers) && !empty($all_modules_controllers)) {

            foreach ($all_modules_controllers as $module => $modules_controllers) {

                if (isset($modules_controllers) && !empty($modules_controllers)) {

                    foreach ($modules_controllers as $cont) {
                        $results[$i]['id_exception'] = 'module-' . $module . '-' . $cont;
                        $results[$i]['name'] = 'module-' . $module . '-' . $cont;
                        $i++;
                    }

                }

            }

        }

        return $results;
    }

    /**
     * Delete product accessories
     *
     * @return mixed Deletion result
     */
    public function deleteContentAnywherProductAccessories($option_page) {

        return Db::getInstance()->execute('DELETE FROM `' . _DB_PREFIX_ . 'vccontentanywhere_filter` WHERE `id_vccontentanywhere` = ' . (int) $this->id . ' AND page = ' . $option_page);
    }

}
