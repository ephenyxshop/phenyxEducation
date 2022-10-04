<?php

/**
 * Class LinkCore
 *
 * @since 1.9.1.0
 *
 * Backwards compatible properties and methods (accessed via magic methods):
 * @property array|null $category_disable_rewrite
 */
class LinkCore {

    // @codingStandardsIgnoreStart
    public static $cache = ['page' => []];
    /** @var array|null $categoryDisableRewrite */
    protected static $categoryDisableRewrite = null;
    public $protocol_link;
    public $protocol_content;
    /** @var bool Rewriting activation */
    protected $allow;
    protected $url;
    protected $ssl_enable;
    protected $webpSupported = false;
    // @codingStandardsIgnoreEnd

    /**
     * Constructor (initialization only)
     *
     * @param null $protocolLink
     * @param null $protocolContent
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    public function __construct($protocolLink = null, $protocolContent = null) {

        $this->allow = (int) Configuration::get('EPH_REWRITING_SETTINGS');
        $this->url = $_SERVER['SCRIPT_NAME'];
        $this->protocol_link = $protocolLink;
        $this->protocol_content = $protocolContent;

        if (!defined('_EPH_BASE_URL_')) {
            define('_EPH_BASE_URL_', Tools::getShopDomain(true));
        }

        if (!defined('_EPH_BASE_URL_SSL_')) {
            define('_EPH_BASE_URL_SSL_', Tools::getShopDomainSsl(true));
        }

        if (static::$categoryDisableRewrite === null) {
            static::$categoryDisableRewrite = [Configuration::get('EPH_HOME_CATEGORY'), Configuration::get('EPH_ROOT_CATEGORY')];
        }

        $this->ssl_enable = Configuration::get('EPH_SSL_ENABLED');
        $this->webpSupported = $this->isWebPSupported();
    }

    
    public function &__get($property) {

        // Property to camelCase for backwards compatibility
        $camelCaseProperty = lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $property))));

        if (property_exists($this, $camelCaseProperty) && in_array($camelCaseProperty, ['categoryDisableRewrite'])) {
            return $this->$camelCaseProperty;
        }

        return $this->$property;
    }

    
    public function getProductDeletePictureLink($product, $idPicture) {

        $url = $this->getProductLink($product);

        return $url . ((strpos($url, '?')) ? '&' : '?') . 'deletePicture=' . $idPicture;
    }

    public function getAdminImageLink($name, $ids, $type = null)  {
        
		
       
        $split_ids = explode('-', $ids);
        $id_image = (isset($split_ids[1]) ? $split_ids[1] : $split_ids[0]);       
        
		$uri_path = $id_image.($type ? '-'.$type : '').'/'.$name.'.webp';

        return $this->getBaseFrontLink().$uri_path;
    }
    
    public function getModuleImageLink($module)  {
        
		$uri_path = 'includes/plugins'. DIRECTORY_SEPARATOR . $module->name. DIRECTORY_SEPARATOR . 'logo.png';

        return $this->getBaseFrontLink().$uri_path;
    }

    public function getProductLink($product, $alias = null, $category = null, $ean13 = null, $idLang = null,  $ipa = 0, $forceRoutes = false, $relativeProtocol = false, $addAnchor = false, $extraParams = []) {

        $dispatcher = Performer::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);

        if (!is_object($product)) {

            if (is_array($product) && isset($product['id_product'])) {
                $product = new Product($product['id_product'], false, $idLang);
            } else if ((int) $product) {
                $product = new Product((int) $product, false, $idLang);
            } else {
                throw new PhenyxShopException('Invalid product vars');
            }

        }

        // Set available keywords
        $params = [];
        $params['id'] = $product->id;
        $params['rewrite'] = (!$alias) ? $product->getFieldByLang('link_rewrite') : $alias;

        $params['ean13'] = (!$ean13) ? $product->ean13 : $ean13;
        $params['meta_keywords'] = Tools::str2url($product->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($product->getFieldByLang('meta_title'));

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'manufacturer')) {
            $params['manufacturer'] = Tools::str2url($product->isFullyLoaded ? $product->manufacturer_name : Manufacturer::getNameById($product->id_manufacturer));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'supplier')) {
            $params['supplier'] = Tools::str2url($product->isFullyLoaded ? $product->supplier_name : Supplier::getNameById($product->id_supplier));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'price')) {
            $params['price'] = $product->isFullyLoaded ? $product->price : Product::getPriceStatic($product->id, false, null, 6, null, false, true, 1, false, null, null, null, $product->specificPrice);
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'tags')) {
            $params['tags'] = Tools::str2url($product->getTags($idLang));
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'category')) {
            $params['category'] = (!is_null($product->category) && !empty($product->category)) ? Tools::str2url($product->category) : Tools::str2url($category);
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'reference')) {
            $params['reference'] = Tools::str2url($product->reference);
        }

        if ($dispatcher->hasKeyword('product_rule', $idLang, 'categories')) {
            $params['category'] = (!$category) ? $product->category : $category;
            $cats = [];
            $categoryDisableRewrite = static::$categoryDisableRewrite;

            foreach ($product->getParentCategories($idLang) as $cat) {

                if (!in_array($cat['id_category'], $categoryDisableRewrite)) {
                    //remove root and home category from the URL
                    $cats[] = $cat['link_rewrite'];
                }

            }

            $params['categories'] = implode('/', $cats);
        }

        $anchor = $ipa ? $product->getAnchor((int) $ipa, (bool) $addAnchor) : '';

        return $url . $dispatcher->createUrl('product_rule', $idLang, array_merge($params, $extraParams), $forceRoutes, $anchor);
    }
    
    public function getEducationLink($education, $alias = null, $type = null, $idLang = null, $ipa = 0, $forceRoutes = false, $relativeProtocol = false, $addAnchor = false, $extraParams = []) {

        $dispatcher = Performer::getInstance();

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);

        if (!is_object($education)) {

            if (is_array($education) && isset($education['id_education'])) {
                $education = new Education($education['id_education'], false, $idLang);
            } else
            if ((int) $education) {
                $education = new Education((int) $education, false, $idLang);
            } else {
                throw new PhenyxShopException('Invalid education vars');
            }

        }

        // Set available keywords
        $params = [];
        $params['id'] = $education->id;
        $params['rewrite'] = (!$alias) ? $education->getFieldByLang('link_rewrite') : $alias;

        $params['meta_keywords'] = Tools::str2url($education->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($education->getFieldByLang('meta_title'));

        if ($dispatcher->hasKeyword('education_rule', $idLang, 'type')) {
            $params['type'] = (!is_null($education->type) && !empty($education->type)) ? Tools::str2url($education->type) : Tools::str2url($type);
        }

        if ($dispatcher->hasKeyword('education_rule', $idLang, 'reference')) {
            $params['reference'] = Tools::str2url($education->reference);
        }

        $anchor = $ipa ? $education->getAnchor((int) $ipa, (bool) $addAnchor) : '';

        return $url . $dispatcher->createUrl('education_rule', $idLang, array_merge($params, $extraParams), $forceRoutes, $anchor);
    }    

    public function getBaseLink($ssl = null, $relativeProtocol = false) {

        static $forceSsl = null;

        if ($ssl === null) {

            if ($forceSsl === null) {
                $forceSsl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
            }

            $ssl = $forceSsl;
        }

        $company= Context::getContext()->company;

        if ($relativeProtocol) {
            	$base = '//' . ($ssl && $this->ssl_enable ? $company->domain_ssl : $company->domain);
        	} else {
            	$base = (($ssl && $this->ssl_enable) ? 'https://' . $company->domain_ssl : 'http://' . $company->domain);
        	}
            
        return $base . $company->getBaseURI();
    }
    
    public function getBaseAdminLink($ssl = null, $relativeProtocol = false) {

        static $forceSsl = null;

        if ($ssl === null) {

            if ($forceSsl === null) {
                $forceSsl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
            }

            $ssl = $forceSsl;
        }

        $company= Context::getContext()->company;

        $base = (($ssl && $this->ssl_enable) ? 'https://' . $company->domain_ssl : 'http://' . $company->domain);

        return $base . $company->getBaseURI();
    }
    
    public function getBaseFrontLink($ssl = null, $relativeProtocol = false) {

        static $forceSsl = null;

        if ($ssl === null) {

            if ($forceSsl === null) {
                $forceSsl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
            }

            $ssl = $forceSsl;
        }

        $company= Context::getContext()->company;

         if ($relativeProtocol) {
			 
			$base = '//' . ($ssl && $this->ssl_enable ? $company->domain_ssl : $company->domain);
            
        } else {
			$base = 'https://' . $company->domain_ssl;
            
        }

        return $base . $company->getBaseURI();
    }
    
    public function getAgentBaseLink($ssl = null, $relativeProtocol = false) {

        static $forceSsl = null;

        if ($ssl === null) {

            if ($forceSsl === null) {
                $forceSsl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
            }

            $ssl = $forceSsl;
        }

        $shop = Context::getContext()->company;

        $base = (($ssl && $this->ssl_enable) ? 'https://' . $shop->agent_url : 'http://' . $shop->agent_url);

        return $base . $shop->getBaseURI();
    }
    
    public function getLangLink($idLang = null, Context $context = null) {

        if (!$context) {
            $context = Context::getContext();
        }

        if ($idLang == Configuration::get('EPH_LANG_DEFAULT')) {
            return '';
        }

       

        if (!$idLang) {
            $idLang = $context->language->id;
        }

        $result = Language::getIsoById($idLang) . '/';
        return '';
    }
    
    public function getAdminLink($controller, $withToken = false) {

        $idLang = Context::getContext()->language->id;
		$controller = Tools::strReplaceFirst('.php', '', $controller);
        if(!is_null($controller) && (bool) Configuration::get('EPH_REWRITING_SETTINGS')) {
            $controller = strtolower($controller);
        }
        $params =  [];
		$ssl = true;
		$relativeProtocol = false;
		$uriPath = Performer::getInstance()->createUrl($controller, $idLang, $params);
		return $this->getBaseAdminLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null) . ltrim($uriPath, '/');
    }
    
    public function getPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false,  $relativeProtocol = false) {

        if (defined('_EPH_CEF_URL_')) {
			return $this->getAgentPageLink($controller, $ssl, $idLang, $request, $requestUrlEncode, $relativeProtocol);
		} else {
         //If $controller contains '&' char, it means that $controller contains request data and must be parsed first
        $p = strpos($controller, '&');

        if ($p !== false) {
            $request = substr($controller, $p + 1);
            $requestUrlEncode = false;
            $controller = substr($controller, 0, $p);
        }

        $controller = Tools::strReplaceFirst('.php', '', $controller);

        if (!$idLang) {
            $idLang = (int) Context::getContext()->language->id;
        }

        //need to be unset because getModuleLink need those params when rewrite is enable

        if (is_array($request)) {

            if (isset($request['module'])) {
                unset($request['module']);
            }

            if (isset($request['controller'])) {
                unset($request['controller']);
            }

        } else {
			
            $request = !empty($request) ? html_entity_decode($request) : $request;

            if ($requestUrlEncode) {
                $request = urlencode($request);
            }
			
			parse_str((string)$request, $request);
			
            
        }

        $uriPath = Performer::getInstance()->createUrl($controller, $idLang, $request, false, '');

        return $this->getBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null) . ltrim($uriPath, '/');
        }
    }
		
	public function getAgentPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false, $relativeProtocol = false) {

        //If $controller contains '&' char, it means that $controller contains request data and must be parsed first
        $file = fopen("testgetAgentPageLink.txt","w");
        $p = strpos($controller, '&');

        if ($p !== false) {
            $request = substr($controller, $p + 1);
            $requestUrlEncode = false;
            $controller = substr($controller, 0, $p);
        }

        $controller = Tools::strReplaceFirst('.php', '', $controller);

        if (!$idLang) {
            $idLang = (int) Context::getContext()->language->id;
        }

        //need to be unset because getModuleLink need those params when rewrite is enable

        if (is_array($request)) {

            if (isset($request['module'])) {
                unset($request['module']);
            }

            if (isset($request['controller'])) {
                unset($request['controller']);
            }

        } else {

            $request = !empty($request) ? html_entity_decode($request) : $request;

            if ($requestUrlEncode) {
                $request = urlencode($request);
            }

            parse_str((string) $request, $request);

        }

        $uriPath = Performer::getInstance()->createUrl($controller, $idLang, $request, false);
        fwrite($file,$uriPath);

        return $this->getAgentBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null) . ltrim($uriPath, '/');
    }
    
     public function getImageLink($name, $ids, $type = null, $format = 'jpg', $highDpi = false) {

        if (!$format) {
            $format = 'jpg';
        }
        $context = Context::getContext();
        
        $notDefault = false;
        
        $splitIds = explode('-', $ids);
        $idImage = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
       
        if ($this->allow == 1) {
            if($idImage > 0) {
                $uriPath = __EPH_BASE_URI__ . $idImage . ($type ? '-' . $type : '') .  '/' . $name . ($highDpi ? '2x.' : '.') . $format;
            } else {
                $uriPath = __EPH_BASE_URI__ . 'content/img/p/'.$context->language->iso_code. ($type ? '-default-' . $type : '')  . '.'.$format;
            }
            
        } else {
            $uriPath = _THEME_PROD_DIR_ . Image::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') .  ($highDpi ? '2x.' : '.') . $format;
        }
       
        $url = $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;

        if ($this->webpSupported) {
            return str_replace('.jpg', '.webp', $url);
        }

        return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
    
    public function getEducationImageLink($name, $ids, $type = null, $format = 'jpg', $highDpi = false) {

        if (!$format) {
            $format = 'jpg';
        }

        $notDefault = false;

        // legacy mode or default image
        $splitIds = explode('-', $ids);
        $idImage = (isset($splitIds[1]) ? $splitIds[1] : $splitIds[0]);
        
        if ($this->allow == 0) {

             $uriPath = __EPH_BASE_URI__ . $idImage . ($type ? '-' . $type : '') . '/' . $name . ($highDpi ? '2x.' : '.') . $format;
         
        } else {

            $uriPath = _THEME_EDUC_DIR_ . ImageEducation::getImgFolderStatic($idImage) . $idImage . ($type ? '-' . $type : '') .  ($highDpi ? '2x.' : '.') . $format;
        }

        if (file_exists(str_replace('.jpg', '.webp', _SHOP_ROOT_DIR_ . $uriPath))) {
            $uriPath = str_replace('.jpg', '.webp', $uriPath);
        }


        return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
    
    public function getMediaLink($filepath) {

        return $this->protocol_content . Tools::getMediaServer($filepath) . $filepath;
    }
    
    public function getCatImageLink($name, $idCategory, $type = null, $format = 'jpg', $highDpi = false) {

        if (!$format) {
            $format = 'jpg';
        }

        if ($this->allow == 1 && $type) {
            $uriPath = __EPH_BASE_URI__ . 'c/' . $idCategory . '-' . $type . '/' . $name . ($highDpi ? '2x.' : '.') . $format;
        } else {
            $uriPath = _THEME_CAT_DIR_ . $idCategory . ($type ? '-' . $type : '') . ($highDpi ? '2x.' : '.') . $format;
        }

        $url = $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;

        if ($this->webpSupported) {
            return str_replace('.jpg', '.webp', $url);
        }

        return $this->protocol_content . Tools::getMediaServer($uriPath) . $uriPath;
    }
    
    public function getLanguageLink($idLang, Context $context = null) {

        if (!$context) {
            $context = Context::getContext();
        }

        $params = $_GET;
        unset($params['isolang'], $params['controller']);

        if (!$this->allow) {
            $params['id_lang'] = $idLang;
        } else {
            unset($params['id_lang']);
        }

        $controller = Performer::getInstance()->getController();

        if (!empty($context->controller->php_self)) {
            $controller = $context->controller->php_self;
        }

        if ($controller == 'product' && isset($params['id_product'])) {
            return $this->getProductLink((int) $params['id_product'], null, null, null, (int) $idLang);
        } else
        if ($controller == 'category' && isset($params['id_category'])) {
            return $this->getCategoryLink((int) $params['id_category'], null, (int) $idLang);
        } else
        if ($controller == 'supplier' && isset($params['id_supplier'])) {
            return $this->getSupplierLink((int) $params['id_supplier'], null, (int) $idLang);
        } else
        if ($controller == 'manufacturer' && isset($params['id_manufacturer'])) {
            return $this->getManufacturerLink((int) $params['id_manufacturer'], null, (int) $idLang);
        } else
        if ($controller == 'cms' && isset($params['id_cms'])) {
            return $this->getCMSLink((int) $params['id_cms'], null, null, (int) $idLang);
        } else
        if ($controller == 'cms' && isset($params['id_cms_category'])) {
            return $this->getCMSCategoryLink((int) $params['id_cms_category'], null, (int) $idLang);
        } else
        if (isset($params['fc']) && $params['fc'] == 'module') {
            $module = Validate::isModuleName(Tools::getValue('module')) ? Tools::getValue('module') : '';

            if (!empty($module)) {
                unset($params['fc'], $params['module']);

                return $this->getModuleLink($module, $controller, $params, null, (int) $idLang);
            }

        }

        return $this->getPageLink($controller, null, $idLang, $params);
    }

    public function getCategoryLink($category, $alias = null, $idLang = null, $selectedFilters = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);

        if (!is_object($category)) {
            $category = new Category($category, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $category->id;
        $params['rewrite'] = (!$alias) ? $category->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($category->getFieldByLang('meta_keywords'));
        $params['meta_title'] = Tools::str2url($category->getFieldByLang('meta_title'));
        $cats = [];
        $categoryDisableRewrite = static::$categoryDisableRewrite;

        foreach ($category->getParentsCategories($idLang) as $cat) {

            if (!in_array($cat['id_category'], $categoryDisableRewrite)) {
                //remove root and home category from the URL
                $cats[] = $cat['link_rewrite'];
            }

        }

        array_shift($cats);
        $cats = array_reverse($cats);
        $params['categories'] = trim(implode('/', $cats), '/');

        // Selected filters are used by layered navigation modules
        $selectedFilters = is_null($selectedFilters) ? '' : $selectedFilters;

        if (empty($selectedFilters)) {
            $rule = 'category_rule';
        } else {
            $rule = 'layered_rule';
            $params['selected_filters'] = $selectedFilters;
        }

        return $url . Performer::getInstance()->createUrl($rule, $idLang, $params, $this->allow);
    }
    
    public function getEducationTypeLink($educationType, $alias = null, $id_lang = null,  $relative_protocol = false) {

        $dispatcher = Performer::getInstance();

        if (!$id_lang) {
            $id_lang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relative_protocol) . $this->getLangLink($id_lang, null);

        if (!is_object($educationType)) {

            if (is_array($educationType) && isset($educationType['id_education_type'])) {
                $educationType = new EducationType($educationType['id_education_type'], $id_lang);
            } else

            if ((int) $educationType) {
                $educationType = new EducationType((int) $educationType, $id_lang);
            } else {
                throw new PhenyxShopException('Invalid education type');
            }

        }

        // Set available keywords
        $params = [];

        $params['rewrite'] = (!$alias) ? $educationType->link_rewrite : $alias;
        $params['id'] = $educationType->id;
        return $url . $dispatcher->createUrl('education_type_rule', $id_lang, $params);
    }

    public function getSupplierLink($supplier, $alias = null, $idLang = null, $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);

        $dispatcher = Performer::getInstance();

        if (!is_object($supplier)) {

            if ($alias !== null && !$dispatcher->hasKeyword('supplier_rule', $idLang, 'meta_keywords') && !$dispatcher->hasKeyword('supplier_rule', $idLang, 'meta_title', $idCompany)) {
                return $url . $dispatcher->createUrl('supplier_rule', $idLang, ['id' => (int) $supplier, 'rewrite' => (string) $alias], $this->allow);
            }

            $supplier = new Supplier($supplier, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $supplier->id;
        $params['rewrite'] = (!$alias) ? $supplier->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($supplier->meta_keywords);
        $params['meta_title'] = Tools::str2url($supplier->meta_title);

        return $url . $dispatcher->createUrl('supplier_rule', $idLang, $params, $this->allow);
    }

    public function getManufacturerLink($manufacturer, $alias = null, $idLang = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);

        $dispatcher = Performer::getInstance();

        if (!is_object($manufacturer)) {

            if ($alias !== null && !$dispatcher->hasKeyword('manufacturer_rule', $idLang, 'meta_keywords') && !$dispatcher->hasKeyword('manufacturer_rule', $idLang, 'meta_title')) {
                return $url . $dispatcher->createUrl('manufacturer_rule', $idLang, ['id' => (int) $manufacturer, 'rewrite' => (string) $alias], $this->allow);
            }

            $manufacturer = new Manufacturer($manufacturer, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $manufacturer->id;
        $params['rewrite'] = (!$alias) ? $manufacturer->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($manufacturer->meta_keywords);
        $params['meta_title'] = Tools::str2url($manufacturer->meta_title);

        return $url . $dispatcher->createUrl('manufacturer_rule', $idLang, $params, $this->allow);
    }

    public function getCMSLink($cms, $alias = null, $ssl = null, $idLang = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        

        $url = $this->getBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null);
        $dispatcher = Performer::getInstance();

        if (!is_object($cms)) {
            $cms = new CMS($cms, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $cms->id;
        $params['rewrite'] = (!$alias) ? (is_array($cms->link_rewrite) ? $cms->link_rewrite[(int) $idLang] : $cms->link_rewrite) : $alias;
        $params['meta_keywords'] = '';
        $params['categories'] = $this->findCMSSubcategories($cms->id, $idLang);

        if (isset($cms->meta_keywords) && !empty($cms->meta_keywords)) {
            $params['meta_keywords'] = is_array($cms->meta_keywords) ? Tools::str2url($cms->meta_keywords[(int) $idLang]) : Tools::str2url($cms->meta_keywords);
        }

        $params['meta_title'] = '';

        if (isset($cms->meta_title) && !empty($cms->meta_title)) {
            $params['meta_title'] = is_array($cms->meta_title) ? Tools::str2url($cms->meta_title[(int) $idLang]) : Tools::str2url($cms->meta_title);
        }

        return $url . $dispatcher->createUrl('cms_rule', $idLang, $params, $this->allow);
    }
	
    public function getFrontCMSLink($cms, $alias = null, $ssl = null, $idLang = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseFrontLink($idCompany, $ssl, $relativeProtocol) . $this->getLangLink($idLang, null);
        $dispatcher = Performer::getInstance();

        if (!is_object($cms)) {
            $cms = new CMS($cms, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $cms->id;
        $params['rewrite'] = (!$alias) ? (is_array($cms->link_rewrite) ? $cms->link_rewrite[(int) $idLang] : $cms->link_rewrite) : $alias;
        $params['meta_keywords'] = '';
        $params['categories'] = $this->findCMSSubcategories($cms->id, $idLang);

        if (isset($cms->meta_keywords) && !empty($cms->meta_keywords)) {
            $params['meta_keywords'] = is_array($cms->meta_keywords) ? Tools::str2url($cms->meta_keywords[(int) $idLang]) : Tools::str2url($cms->meta_keywords);
        }

        $params['meta_title'] = '';

        if (isset($cms->meta_title) && !empty($cms->meta_title)) {
            $params['meta_title'] = is_array($cms->meta_title) ? Tools::str2url($cms->meta_title[(int) $idLang]) : Tools::str2url($cms->meta_title);
        }

        return $url . $dispatcher->createUrl('cms_rule', $idLang, $params, $this->allow);
    }	
	
	public function getPFGLink($pfg, $alias = null, $ssl = null, $idLang = null, $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }


        $url = $this->getBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null);
        $dispatcher = Performer::getInstance();

        if (!is_object($pfg)) {
            $pfg = new PFGModel($pfg, $idLang);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $pfg->id;
        $params['rewrite'] = (!$alias) ? (is_array($pfg->link_rewrite) ? $pfg->link_rewrite[(int) $idLang] : $pfg->link_rewrite) : $alias;

        return $url . $dispatcher->createUrl('pfg_rule', $idLang, $params, $this->allow);
    }
	
    protected function findCMSSubcategories($idCms, $idLang) {

        $sql = new DbQuery();
        $sql->select('`' . bqSQL(CMSCategory::$definition['primary']) . '`');
        $sql->from(bqSQL(CMS::$definition['table']));
        $sql->where('`' . bqSQL(CMS::$definition['primary']) . '` = ' . (int) $idCms);
        $idCmsCategory = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);

        if (empty($idCmsCategory)) {
            return '';
        }

        $subcategories = $this->findCMSCategorySubcategories($idCmsCategory, $idLang);

        return trim($subcategories, '/');
    }

    protected function findCMSCategorySubcategories($idCmsCategory, $idLang) {

        if (empty($idCmsCategory) || $idCmsCategory === 1) {
            return '';
        }

        $subcategories = '';

        while ($idCmsCategory > 1) {
            $subcategory = new CMSCategory($idCmsCategory);
            $subcategories = $subcategory->link_rewrite[$idLang] . '/' . $subcategories;
            $idCmsCategory = $subcategory->id_parent;
        }

        return trim($subcategories, '/');
    }

    public function getCMSCategoryLink($cmsCategory, $alias = null, $idLang = null,  $relativeProtocol = false) {

        if (empty($idLang)) {
            $idLang = Context::getContext()->language->id;
        }
        
        $url = $this->getBaseLink(null, $relativeProtocol) . $this->getLangLink($idLang, null);
        $dispatcher = Performer::getInstance();

        if (!is_object($cmsCategory)) {
            $cmsCategory = new CMSCategory($cmsCategory, $idLang);
        }

        if (is_array($cmsCategory->link_rewrite) && isset($cmsCategory->link_rewrite[(int) $idLang])) {
            $cmsCategory->link_rewrite = $cmsCategory->link_rewrite[(int) $idLang];
        }

        if (is_array($cmsCategory->meta_keywords) && isset($cmsCategory->meta_keywords[(int) $idLang])) {
            $cmsCategory->meta_keywords = $cmsCategory->meta_keywords[(int) $idLang];
        }

        if (is_array($cmsCategory->meta_title) && isset($cmsCategory->meta_title[(int) $idLang])) {
            $cmsCategory->meta_title = $cmsCategory->meta_title[(int) $idLang];
        }

        // Set available keywords
        $params = [];
        $params['id'] = $cmsCategory->id;
        $params['rewrite'] = (!$alias) ? $cmsCategory->link_rewrite : $alias;
        $params['meta_keywords'] = Tools::str2url($cmsCategory->meta_keywords);
        $params['meta_title'] = Tools::str2url($cmsCategory->meta_title);
        $idParent = $this->findCMSCategoryParent($cmsCategory->id_cms_category);

        if (empty($idParent)) {
            $params['categories'] = '';
        } else {
            $params['categories'] = $this->findCMSCategorySubcategories($idParent, $idLang);
        }

        return $url . $dispatcher->createUrl('cms_category_rule', $idLang, $params, $this->allow);
    }
    
    protected function findCMSCategoryParent($idCmsCategory) {

        $sql = new DbQuery();
        $sql->select('`id_parent`');
        $sql->from(bqSQL(CMSCategory::$definition['table']));
        $sql->where('`' . bqSQL(CMSCategory::$definition['primary']) . '` = ' . (int) $idCmsCategory);
        $idParent = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getValue($sql);

        if (empty($idParent)) {
            return 0;
        }

        return (int) $idParent;
    }

    public function getModuleLink($module, $controller = 'default', array $params = [], $ssl = null, $idLang = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

        $url = $this->getBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null);

        // Set available keywords
        $params['module'] = $module;
        $params['controller'] = $controller ? $controller : 'default';

        // If the module has its own route ... just use it !

        if (Performer::getInstance()->hasRoute('module-' . $module . '-' . $controller, $idLang)) {
            return $this->getPageLink('module-' . $module . '-' . $controller, $ssl, $idLang, $params);
        } else {
            return $url . Performer::getInstance()->createUrl('module', $idLang, $params, $this->allow);
        }

    }
    
    public function getFrontPageLink($controller, $ssl = null, $idLang = null, $request = null, $requestUrlEncode = false,  $relativeProtocol = false) {

        //If $controller contains '&' char, it means that $controller contains request data and must be parsed first
        $p = strpos($controller, '&');

        if ($p !== false) {
            $request = substr($controller, $p + 1);
            $requestUrlEncode = false;
            $controller = substr($controller, 0, $p);
        }

        $controller = Tools::strReplaceFirst('.php', '', $controller);

        if (!$idLang) {
            $idLang = (int) Context::getContext()->language->id;
        }

        //need to be unset because getModuleLink need those params when rewrite is enable

        if (is_array($request)) {

            if (isset($request['module'])) {
                unset($request['module']);
            }

            if (isset($request['controller'])) {
                unset($request['controller']);
            }

        } else {
			
            $request = !empty($request) ? html_entity_decode($request) : $request;

            if ($requestUrlEncode) {
                $request = urlencode($request);
            }
			
			parse_str((string)$request, $request);
			
            
        }

        $uriPath = Performer::getInstance()->createUrl($controller, $idLang, $request, false);

        return $this->getBaseFrontLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null) . ltrim($uriPath, '/');
    }
	
    public function goPage($url, $p) {

        $url = rtrim(str_replace('?&', '?', $url), '?');

        return $url . ($p == 1 ? '' : (!strstr($url, '?') ? '?' : '&') . 'p=' . (int) $p);
    }

    public function getPaginationLink($type, $idObject, $nb = false, $sort = false, $pagination = false, $array = false) {

        // If no parameter $type, try to get it by using the controller name

        if (!$type && !$idObject) {
            $methodName = 'get' . Performer::getInstance()->getController() . 'Link';

            if (method_exists($this, $methodName) && isset($_GET['id_' . Performer::getInstance()->getController()])) {
                $type = Performer::getInstance()->getController();
                $idObject = $_GET['id_' . $type];
            }

        }

        if ($type && $idObject) {
            $url = $this->{'get' . $type . 'Link'}

            ($idObject, null);
        } else {

            if (isset(Context::getContext()->controller->php_self)) {
                $name = Context::getContext()->controller->php_self;
            } else {
                $name = Performer::getInstance()->getController();
            }

            $url = $this->getPageLink($name);
        }

        $vars = [];
        $varsNb = ['n'];
        $varsSort = ['orderby', 'orderway'];
        $varsPagination = ['p'];

        foreach ($_GET as $k => $value) {

            if ($k != 'id_' . $type && $k != 'controller') {

                if (Configuration::get('EPH_REWRITING_SETTINGS') && ($k == 'isolang' || $k == 'id_lang')) {
                    continue;
                }

                $ifNb = (!$nb || ($nb && !in_array($k, $varsNb)));
                $ifSort = (!$sort || ($sort && !in_array($k, $varsSort)));
                $ifPagination = (!$pagination || ($pagination && !in_array($k, $varsPagination)));

                if ($ifNb && $ifSort && $ifPagination) {

                    if (!is_array($value)) {
                        $vars[urlencode($k)] = $value;
                    } else {

                        foreach (explode('&', http_build_query([$k => $value], '', '&')) as $key => $val) {
                            $data = explode('=', $val);
                            $vars[urldecode($data[0])] = $data[1];
                        }

                    }

                }

            }

        }

        if (!$array) {

            if (count($vars)) {
                return $url . (!strstr($url, '?') && ($this->allow == 1 || $url == $this->url) ? '?' : '&') . http_build_query($vars, '', '&');
            } else {
                return $url;
            }

        }

        $vars['requestUrl'] = $url;

        if ($type && $idObject) {
            $vars['id_' . $type] = (is_object($idObject) ? (int) $idObject->id : (int) $idObject);
        }

        if (!$this->allow == 1) {
            $vars['controller'] = Performer::getInstance()->getController();
        }

        return $vars;
    }

    public function addSortDetails($url, $orderby, $orderway) {

        return $url . (!strstr($url, '?') ? '?' : '&') . 'orderby=' . urlencode($orderby) . '&orderway=' . urlencode($orderway);
    }

    public function matchQuickLink($url) {

        $quicklink = $this->getQuickLink($url);

        if (isset($quicklink) && $quicklink === ($this->getQuickLink($_SERVER['REQUEST_URI']))) {
            return true;
        } else {
            return false;
        }

    }

    public static function getQuickLink($url) {

        $parsedUrl = parse_url($url);
        $output = [];

        if (is_array($parsedUrl) && isset($parsedUrl['query'])) {
            parse_str($parsedUrl['query'], $output);
            unset($output['token'], $output['conf'], $output['id_quick_access']);
        }

        return http_build_query($output);
    }

    public function isWebPSupported() {

        if (Configuration::get('WEBCONVERTOR_DEMO_MODE') == 1) {
            return false;
        }

        if (isset($_SERVER["HTTP_ACCEPT"])) {

            if (strpos($_SERVER["HTTP_ACCEPT"], "image/webp") > 0) {
                return true;
            }

            $agent = $_SERVER['HTTP_USER_AGENT'];

            if (strlen(strstr($agent, 'Firefox')) > 0) {
                return true;
            }

            if (strlen(strstr($agent, 'Edge')) > 0) {
                return true;
            }

        }

    }

    public function getEmployeeImageLink($id_employee = null) {

        if ($id_employee == null) {
            $id_employee = Context::getContext()->employee->id;
        }

        $ssl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
        $ssl_enable = Configuration::get('EPH_SSL_ENABLED');
        $company= Context::getContext()->company;
        $base = (($ssl && $ssl_enable) ? 'https://' . $company->domain_ssl : 'http://' . $company->domain);

        if (file_exists(_EPH_EMPLOYEE_IMG_DIR_ . $id_employee . '.jpg')) {
            $link = 'content/img/e/' . $id_employee . '.jpg';
        } else {

            $link = 'content/img/e/Unknown.png';

        }

        if ($this->webpSupported) {
            // return str_replace('.jpg', '.webp', $link);
        }

        return $link;

    }
    
    public static function getStaticBaseLink($id_shop = null, $ssl = null, $relative_protocol = false) {

        $ssl = (Configuration::get('EPH_SSL_ENABLED') && Configuration::get('EPH_SSL_ENABLED_EVERYWHERE'));
        $shop = Context::getContext()->company;
        $ssl_enable = Configuration::get('EPH_SSL_ENABLED');
        $base = (($ssl && $ssl_enable) ? 'https://' . $shop->domain_ssl : 'http://' . $shop->domain);
        return $base . $shop->getBaseURI();
    }
    
    public static function getStaticLangLink($id_lang = null, Context $context = null) {

        if (!$context) {
            $context = Context::getContext();
        }

        $allow = (int) Configuration::get('EPH_REWRITING_SETTINGS');

        

        if (!$id_lang) {
            $id_lang = $context->language->id;
        }

        return Language::getIsoById($id_lang) . '/';
    }
    
   
    

    public function getSponsorLink($sponsor, $alias = null, $ssl = null, $idLang = null,  $relativeProtocol = false) {

        if (!$idLang) {
            $idLang = Context::getContext()->language->id;
        }

       
        $url = $this->getBaseLink($ssl, $relativeProtocol) . $this->getLangLink($idLang, null);
        $dispatcher = Performer::getInstance();

        if (!is_object($sponsor)) {
            $sponsor = new SponsorPartner($sponsor);
        }

        // Set available keywords
        $params = [];
        $params['id'] = $sponsor->id;
        $params['rewrite'] = (!$alias) ? (is_array($sponsor->link_rewrite) ? $sponsor->link_rewrite : $sponsor->link_rewrite) : $alias;

        return $url . $dispatcher->createUrl('sponsor_rule', $idLang, $params, $this->allow);
    }

    
   
   

    

    

}
