<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class EducationControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'education';

    protected $education;
    /** @var Category */
    protected $category;

    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'product.css');
        $this->addJS(_THEME_JS_DIR_ . 'product.js');
        Media::addJsDef([
            'AjaxEducationLink' => $this->context->link->getPageLink('my-education', true),

        ]);
    }

    public function postProcess() {

        parent::postProcess();
    }

    public function init() {

        parent::init();
		
		
        if ($idEducation = (int) Tools::getValue('id_education')) {
            $this->education = new Education($idEducation, true, $this->context->language->id);
        }

        if (!Validate::isLoadedObject($this->education)) {
            header('HTTP/1.1 404 Not Found');
            header('Status: 404 Not Found');
            $this->errors[] = Tools::displayError('Formation introuvable');
        } else {

            $this->canonicalRedirection();

        }

    }

    public function canonicalRedirection($canonicalUrl = '') {

        if (Tools::getValue('live_edit')) {
            return;
        }

        if (Validate::isLoadedObject($this->education)) {
            parent::canonicalRedirection($this->context->link->getEducationLink($this->education));
        }

    }
	
	public function display()
	{

            if (Module::isInstalled('jscomposer') && (bool) Module::isEnabled('jscomposer'))
            {
                   $this->education->description = JsComposer::do_shortcode( $this->education->description );
            }
            

            return parent::display();
	}

    public function initContent() {

        if (Tools::getValue('ajax')) {
            return parent::initContent();
        }

        parent::initContent();

        $this->education->description = $this->transformDescriptionWithImg($this->education->description);

        $this->assignCategory();

        $this->assignPrerequis();

        $this->assignPriceAndTax();

        $this->assignImages();

        $this->assignAttributesGroups();

        $this->assignAttributesCombinations();

        $packItems = EducationPack::isPack($this->education->id) ? EducationPack::getItemTable($this->education->id, $this->context->language->id, true) : [];
        $this->context->smarty->assign('packItems', $packItems);
        $this->context->smarty->assign('packs', EducationPack::getPacksTable($this->education->id, $this->context->language->id, true, 1));

        $returnLink = 'javascript: history.back();';

        if ($this->education->cache_is_pack) {
            $this->context->controller->addCSS(_THEME_CSS_DIR_ . 'product_list.css');
        }
		$idShop = $this->context->shop->id;
		if (Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('EPH_LOGO_PROGRAMME', null, null, $idShop);
		} else

		if (Configuration::get('PS_LOGO', null, null, $idShop) != false && file_exists(_PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop))) {
			$logo = _PS_IMG_DIR_ . Configuration::get('PS_LOGO', null, null, $idShop);
			$logo_path = _PS_IMG_ . Configuration::get('PS_LOGO', null, null, $idShop);
		}


        $this->context->smarty->assign(
            [

                'return_link'   => $returnLink,
                'education'     => $this->education,
				'certification' => new Certification($this->education->id_certification),
                'slots'         => EducationSession::getNextEducationSlot(),
                'supplies'      => EducationSupplies::getEducationSupplies(),
                'token'         => Tools::getToken(false),
                'display_ht'    => !Tax::excludeTaxeOption(),
                'jqZoomEnabled' => Configuration::get('PS_DISPLAY_JQZOOM'),
                'ENT_NOQUOTES'  => ENT_NOQUOTES,
				'logo_program'  => $logo_path,
				'tag_program'   => Configuration::get('EPH_TAG_PROGRAM'),
				'footer_program'   => Configuration::get('EPH_FOOTER_PROGRAM'),
				'HOOK_PRODUCT_CONTENT'     => Hook::exec('displayProductContent', ['education' => $this->education]),
				'HOOK_EXTRA_RIGHT'         => Hook::exec('displayRightColumnEducation'),
                'errors'        => $this->errors,
                'body_classes'  => [
                    $this->php_self . '-' . $this->education->id,
                    $this->php_self . '-' . $this->education->link_rewrite,
                    'category-' . (isset($this->category) ? $this->category->id : ''),
                    'category-' . (isset($this->category) ? $this->category->getFieldByLang('link_rewrite') : ''),
                ],

            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'education.tpl');
    }

    protected function transformDescriptionWithImg($desc) {

        $reg = '/\[img\-([0-9]+)\-(left|right)\-([a-zA-Z0-9-_]+)\]/';

        while (preg_match($reg, $desc, $matches)) {
            $linkLmg = $this->context->link->getEducationImageLink($this->education->link_rewrite, $this->education->id . '-' . $matches[1], $matches[3]);
            $class = $matches[2] == 'left' ? 'class="imageFloatLeft"' : 'class="imageFloatRight"';
            $htmlImg = '<img src="' . $linkLmg . '" alt="" ' . $class . '/>';
            $desc = str_replace($matches[0], $htmlImg, $desc);
        }

        return $desc;
    }

    protected function assignPrerequis() {

        if ($this->education->cache_default_attribute == 0) {
            $prerequis = new EducationPrerequis($this->education->id_education_prerequis, 1);
        } else {
            $prerequis = EducationPrerequis::getEducationPrerequis($this->education->id);
        }

        $this->context->smarty->assign(
            [
                'prerequis' => $prerequis,

            ]
        );
    }

    protected function assignCategory() {

        $this->category = new EducationType((int) $this->education->id_education_type, (int) $this->context->language->id);

        if (Validate::isLoadedObject($this->category)) {
            $path = Tools::getPath((int) $this->education->id_education_type, $this->education->name);
        }

        if (Validate::isLoadedObject($this->category)) {

            $this->context->smarty->assign(
                [
                    'path'     => $path,
                    'category' => $this->category,

                ]
            );
        }

    }

    protected function assignPriceAndTax() {

        $idStudent = (isset($this->context->student) ? (int) $this->context->student->id : 0);

        $idCountry = $idStudent ? (int) $this->context->student->id_country : (int) Tools::getCountry();

        // Tax
        $tax = (float) $this->education->getTaxesRate(new Address());
        $this->context->smarty->assign('tax_rate', $tax);

        $productPriceWithTax = Education::getPriceStatic($this->education->id, true, null, 6);

        if (Education::$_taxCalculationMethod == PS_TAX_INC) {
            $productPriceWithTax = Tools::ps_round($productPriceWithTax, 2);
        }

        $idCurrency = (int) $this->context->cookie->id_currency;
        $idProduct = (int) $this->education->id;
        $idShop = $this->context->shop->id;

        $address = new Address();
        $this->context->smarty->assign(
            [

                'productPriceWithTax' => (float) $productPriceWithTax,
                'no_tax'              => Tax::excludeTaxeOption() || !$this->education->getTaxesRate($address),
                'tax_enabled'         => Configuration::get('PS_TAX') && !Configuration::get('AEUC_LABEL_TAX_INC_EXC'),
            ]
        );
    }

    protected function assignImages() {

        $images = $this->education->getImages((int) $this->context->cookie->id_lang);
        $productImages = [];

        if (isset($images[0])) {
            $this->context->smarty->assign('mainImage', $images[0]);
        }

        foreach ($images as $k => $image) {

            if ($image['cover']) {
                $this->context->smarty->assign('mainImage', $image);
                $cover = $image;
                $cover['id_image_education'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->education->id . '-' . $image['id_image_education']) : $image['id_image_education']);
                $cover['id_image_only'] = (int) $image['id_image_education'];
            }

            $productImages[(int) $image['id_image_education']] = $image;
        }

        if (!isset($cover)) {

            if (isset($images[0])) {
                $cover = $images[0];
                $cover['id_image_education'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->education->id . '-' . $images[0]['id_image_education']) : $images[0]['id_image_education']);
                $cover['id_image_only'] = (int) $images[0]['id_image_education'];
            } else {
                $cover = [
                    'id_image_education' => $this->context->language->iso_code . '-default',
                    'legend'             => 'No picture',
                    'title'              => 'No picture',
                ];
            }

        }

        $size = ImageEducation::getSize(ImageType::getFormatedName('large'));
        $this->context->smarty->assign(
            [
                'have_image'  => (isset($cover['id_image_education']) && (int) $cover['id_image_education']) ? [(int) $cover['id_image_education']] : Education::getCover((int) Tools::getValue('id_education')),
                'cover'       => $cover,
                'imgWidth'    => (int) $size['width'],
                'mediumSize'  => ImageEducation::getSize(ImageType::getFormatedName('medium')),
                'largeSize'   => ImageEducation::getSize(ImageType::getFormatedName('large')),
                'homeSize'    => ImageEducation::getSize(ImageType::getFormatedName('home')),
                'cartSize'    => ImageEducation::getSize(ImageType::getFormatedName('cart')),
                'col_img_dir' => _PS_COL_IMG_DIR_,
            ]
        );

        if (count($productImages)) {
            $this->context->smarty->assign('images', $productImages);
        }

    }

    protected function assignAttributesGroups() {

        $colors = [];
        $groups = [];
        $fileProgram = '';

        // @todo (RM) should only get groups and not all declination ?
        $attributesGroups = $this->education->getAttributesGroups($this->context->language->id);

        if (is_array($attributesGroups) && $attributesGroups) {

            $combinationImages = $this->education->getDeclinaisonImages($this->context->language->id);

            $combinationPricesSet = [];

            foreach ($attributesGroups as $k => $row) {
                // Color management

                if (isset($row['is_color_group']) && $row['is_color_group'] && (isset($row['attribute_color']) && $row['attribute_color']) || (file_exists(_PS_COL_IMG_DIR_ . $row['id_attribute'] . '.jpg'))) {
                    $colors[$row['id_attribute']]['value'] = $row['attribute_color'];
                    $colors[$row['id_attribute']]['name'] = $row['attribute_name'];

                    if (!isset($colors[$row['id_attribute']]['attributes_quantity'])) {
                        $colors[$row['id_attribute']]['attributes_quantity'] = 0;
                    }

                    $colors[$row['id_attribute']]['attributes_quantity'] += (int) $row['quantity'];
                }

                if (!isset($groups[$row['id_attribute_group']])) {
                    $groups[$row['id_attribute_group']] = [
                        'group_name' => $row['group_name'],
                        'name'       => $row['public_group_name'],
                        'group_type' => $row['group_type'],
                        'default'    => -1,
                    ];
                }

                $groups[$row['id_attribute_group']]['attributes'][$row['id_attribute']] = $row['attribute_name'];

                if ($row['default_on'] && $groups[$row['id_attribute_group']]['default'] == -1) {
                    $groups[$row['id_attribute_group']]['default'] = (int) $row['id_attribute'];
                }

                $combinations[$row['id_education_attribute']]['attributes_values'][$row['id_education_prerequis']] = $row['id_education_prerequis'];
                $combinations[$row['id_education_attribute']]['attributes_values'][$row['id_attribute_group']] = $row['attribute_name'];
                $combinations[$row['id_education_attribute']]['attributes'][] = (int) $row['id_attribute'];
                $combinations[$row['id_education_attribute']]['price'] = (float) Tools::convertPriceFull($row['price'], null, $this->context->currency, false);

                // Call getPriceStatic in order to set $combination_specific_price

                if (!isset($combinationPricesSet[(int) $row['id_education_attribute']])) {
                    Education::getPriceStatic((int) $this->education->id, false, $row['id_education_attribute']);
                    $combinationPricesSet[(int) $row['id_education_attribute']] = true;
                }

                $combinations[$row['id_education_attribute']]['name'] = $row['attributeName'];
                $combinations[$row['id_education_attribute']]['description'] = $row['description'];
                $combinations[$row['id_education_attribute']]['description_short'] = $row['description_short'];
				$combinations[$row['id_education_attribute']]['programme'] = $row['programme'];
				$combinations[$row['id_education_attribute']]['versionProgram'] = $row['versionProgram'];
                $combinations[$row['id_education_attribute']]['reference'] = $row['reference'];
                $combinations[$row['id_education_attribute']]['days'] = $row['days'];
                $combinations[$row['id_education_attribute']]['hours'] = $row['hours'];
                $combinations[$row['id_education_attribute']]['id_education_prerequis'] = $row['id_education_prerequis'];

                $attachement = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                    (new DbQuery())
                        ->select('fileName')
                        ->from('education_programme')
                        ->where('`id_education` = ' . (int) $this->education->id)
                        ->where('`id_education_attribute` = ' . (int) $row['id_education_attribute'])
                );

                if ($attachement != '') {
                    $combinations[$row['id_education_attribute']]['fileProgram'] = '/fileProgram/' . $attachement;

                }

                if (!isset($combinationImages[$row['id_education_attribute']][0]['id_image'])) {
                    $combinations[$row['id_education_attribute']]['id_image'] = -1;
                } else {
                    $combinations[$row['id_education_attribute']]['id_image'] = $idImage = (int) $combinationImages[$row['id_education_attribute']][0]['id_image'];

                    if ($row['default_on']) {

                        if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                            $currentCover = $this->context->smarty->tpl_vars['cover']->value;
                        }

                        if (is_array($combinationImages[$row['id_education_attribute']])) {

                            foreach ($combinationImages[$row['id_education_attribute']] as $tmp) {

                                if (isset($currentCover) && $tmp['id_image'] == $currentCover['id_image_education']) {
                                    $combinations[$row['id_education_attribute']]['id_image'] = $idImage = (int) $tmp['id_image_education'];
                                    break;
                                }

                            }

                        }

                        if ($idImage > 0) {

                            if (isset($this->context->smarty->tpl_vars['images']->value)) {
                                $productImages = $this->context->smarty->tpl_vars['images']->value;
                            }

                            if (isset($productImages) && is_array($productImages) && isset($productImages[$idImage])) {
                                $productImages[$idImage]['cover'] = 1;
                                $this->context->smarty->assign('mainImage', $productImages[$idImage]);

                                if (count($productImages)) {
                                    $this->context->smarty->assign('images', $productImages);
                                }

                            }

                            if (isset($this->context->smarty->tpl_vars['cover']->value)) {
                                $cover = $this->context->smarty->tpl_vars['cover']->value;
                            }

                            if (isset($cover) && is_array($cover) && isset($productImages) && is_array($productImages)) {
                                $productImages[$cover['id_image_education']]['cover'] = 0;

                                if (isset($productImages[$idImage])) {
                                    $cover = $productImages[$idImage];
                                }

                                $cover['id_image'] = (Configuration::get('PS_LEGACY_IMAGES') ? ($this->education->id . '-' . $idImage) : (int) $idImage);
                                $cover['id_image_only'] = (int) $idImage;
                                $this->context->smarty->assign('cover', $cover);
                            }

                        }

                    }

                }

            }

            if (isset($combinations)) {

                foreach ($combinations as $idProductAttribute => $comb) {
                    $attributeList = '';

                    foreach ($comb['attributes'] as $idAttribute) {
                        $attributeList .= '\'' . (int) $idAttribute . '\',';
                    }

                    $attributeList = rtrim($attributeList, ',');
                    $combinations[$idProductAttribute]['list'] = $attributeList;
                }

            }

            $this->context->smarty->assign(
                [
                    'groups'            => $groups,
                    'colors'            => (count($colors)) ? $colors : false,
                    'combinations'      => isset($combinations) ? $combinations : [],
                    'combinationImages' => $combinationImages,
                ]
            );
        } else {
			$idProgramme = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
               (new DbQuery())
                ->select('id_education_programme')
                ->from('education_programme')
                ->where('`id_education` = ' . (int) $this->education->id)
            );
			if ($idProgramme >0) {
			   $attachement = new EducationProgramme($idProgramme);
			   $fileProgram = '/fileProgram/'.$attachement->fileName;
			  
           }
           

        }

        $this->context->smarty->assign(
            [
                'fileProgram' => $fileProgram,
            ]
        );
    }

    protected function assignAttributesCombinations() {

        $attributesCombinations = Education::getAttributesInformationsByEducation($this->education->id);

        if (is_array($attributesCombinations) && count($attributesCombinations)) {

            foreach ($attributesCombinations as &$ac) {

                foreach ($ac as &$val) {
                    $val = str_replace(Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'), '_', Tools::link_rewrite(str_replace([',', '.'], '-', $val)));
                }

            }

        } else {
            $attributesCombinations = [];
        }

        $this->context->smarty->assign(
            [
                'attributesCombinations'     => $attributesCombinations,
                'attribute_anchor_separator' => Configuration::get('PS_ATTRIBUTE_ANCHOR_SEPARATOR'),
            ]
        );
    }

    public function ajaxProcessRequestEducation() {

        $education = new StudentEducation();

        foreach ($_POST as $key => $value) {

            if (property_exists($education, $key) && $key != 'id_student_education') {

                if (Tools::getValue('id_student_education') && empty($value)) {
                    continue;
                }

                $education->{$key}
                = $value;
            }

        }

        $education->id_student = $this->context->student->id;
		$education->id_customer = $this->context->student->id_customer;
        $education->id_student_education_state = 1;
        $education->add();

        $return = [
            'success'  => true,
            'message'  => $this->l('La session de formation a été ajoué avec duccès'),
            'redirect' => $this->context->link->getPageLink('my-education', true),
        ];
        die(Tools::jsonEncode($return));
    }
	
	public function getEducation() {
        return $this->education;
    }

}
