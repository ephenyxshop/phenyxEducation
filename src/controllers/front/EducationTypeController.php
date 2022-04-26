<?php

/**
 * Class EducationTypeControllerCore
 *
 * @since 1.8.1.0
 */
class EducationTypeControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** string Internal controller name */
    public $php_self = 'educationtype';
    /** @var bool If set to false, student cannot view the current educationtype. */
    public $student_access = true;
    /** @var educationtype Current educationtype object */
    protected $educationtype;
    /** @var int Number of products in the current page. */
    protected $nbEducations;
    /** @var array Products to be displayed in the current page . */
    protected $cat_educations;
    // @codingStandardsIgnoreEnd

    /**
     * Sets default media for this controller
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();
        //$this->addCSS(_THEME_CSS_DIR_.'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'education_type.css');

    }

    /**
     * Redirects to canonical or "Not Found" URL
     *
     * @param string $canonicalUrl
     *
     * @since 1.8.1.0
     */

    public function canonicalRedirection($canonical_url = '') {

        if (Tools::getValue('live_edit')) {
            return;
        }

        if (Tools::getValue('ajax')) {
            return;
        }

        if (Validate::isLoadedObject($this->educationtype)) {
            parent::canonicalRedirection($this->context->link->getEducationTypeLink($this->educationtype));
        }

    }

    /**
     * Initializes controller
     *
     * @see   FrontController::init()
     * @throws PhenyxShopException
     *
     * @since 1.8.1.0
     */
    public function init() {

        // Get educationtype ID
        $ideducationtype = (int) Tools::getValue('id_education_type');

        if (!$ideducationtype || !Validate::isUnsignedId($ideducationtype)) {
            $this->errors[] = Tools::displayError('Missing educationtype ID');
        }

        // Instantiate educationtype
        $this->educationtype = new EducationType($ideducationtype, $this->context->language->id);

        parent::init();

    }
	
	public function display() {

            if (Module::isInstalled('jscomposer') && (bool) Module::isEnabled('jscomposer')) {
                   $this->educationtype->description = JsComposer::do_shortcode( $this->educationtype->description );
            }
            

            return parent::display();
	}

    public function initContent() {

        $description = $this->educationtype->description;
        parent::initContent();

        $this->setTemplate(_PS_THEME_DIR_ . 'educationtype.tpl');

        $this->assignEducationList();

        $this->context->smarty->assign(
            [
                'educationtype'     => $this->educationtype,
                'description'       => $this->educationtype->description,
                'descriptionUp'     => $this->educationtype->description_up,
                'descriptionBottom' => $this->educationtype->description_bottom,
                'educations'        => (isset($this->cat_educations) && $this->cat_educations) ? $this->cat_educations : null,
                'id_educationtype'  => (int) $this->educationtype->id,
                'path'              => Tools::getPath($this->educationtype->id),

            ]
        );
    }

    public function assignEducationList() {

        $this->context->smarty->assign('educationtypeNameComplement', '');
        $this->nbEducations = $this->educationtype->getEducations(null, $this->orderWay, true);

        $this->cat_educations = $this->educationtype->getEducations($this->context->language->id);

        $this->context->smarty->assign('nb_educations', $this->nbEducations);
    }

}
