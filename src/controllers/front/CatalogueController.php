<?php

/**
 * Class CatalogueControllerCore
 *
 * @since 1.8.1.0
 */
class CatalogueControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'catalogue';
    /** @var bool $ssl */
    public $ssl = true;
	
	public $display_header = false;
	
	public $display_footer = false;
	
	
    // @codingStandardsIgnoreEnd
  

    public function init() {

		
        parent::init();
       
    }

    /**
     * Start forms process
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function postProcess() {

        parent::postProcess();

        

    }

    /**
     * Get Order ID
     *
     * @return int Order ID
     *
     * @since 1.8.1.0
     */
   
    /**
     * Set media
     *
     * @return void
     *
     * @since 1.8.1.0
     */
    public function setMedia() {

        parent::setMedia();
		//$this->removeJquery('3.6.0');
		
       
    }
	
	public function ajaxProcessOpenCatalog() {
		
		$tpl = $this->context->smarty->createTemplate(_PS_THEME_DIR_ . 'catalogue.tpl');
		
		$this->pushJS([
			_THEME_JS_DIR_ . 'pdf/jquery.js',
			_THEME_JS_DIR_ . 'pdf/jquery_no_conflict.js',			
			_THEME_JS_DIR_ . 'pdf/turn.js',
			_THEME_JS_DIR_ . 'pdf/wait.js',
			_THEME_JS_DIR_ . 'pdf/jquery.mousewheel.js',
			_THEME_JS_DIR_ . 'pdf/jquery.fullscreen.js',
			_THEME_JS_DIR_ . 'pdf/jquery.address-1.6.min.js',
			_THEME_JS_DIR_ . 'pdf/pdf.js',
			_THEME_JS_DIR_ . 'pdf/onload.js'
			
		]);
		
		
		$this->pushCSS([
			_THEME_CSS_DIR_ . 'pdf/catalogue.css',
			_THEME_CSS_DIR_ . 'pdf/reste.css',
			_THEME_CSS_DIR_ . 'pdf/static.css',
			_THEME_CSS_DIR_ . 'pdf/elements.css',
			_THEME_CSS_DIR_ . 'pdf/preloader.css',
			_THEME_CSS_DIR_ . 'pdf/font-awesome.min.css'
		]);
	
		$tpl->assign([
			'extraJs'        => $this->push_js_files,
			'extracss'       => $this->push_css_files,

		]);
		
		$result = [
			'content_only' => true,
			'html' => $tpl->fetch(),
		];

		die(Tools::jsonEncode($result));
		
		
	}

    /**
     * Assign template vars related to page content
     *
     * @see FrontController::initContent()
     *
     * @since 1.8.1.0
     */
    public function initContent() {

        parent::initContent();
		
		$this->pushJS([
			_THEME_JS_DIR_ . 'pdf/jquery.js',
			_THEME_JS_DIR_ . 'pdf/jquery_no_conflict.js',			
			_THEME_JS_DIR_ . 'pdf/turn.js',
			_THEME_JS_DIR_ . 'pdf/wait.js',
			_THEME_JS_DIR_ . 'pdf/jquery.mousewheel.js',
			_THEME_JS_DIR_ . 'pdf/jquery.fullscreen.js',
			_THEME_JS_DIR_ . 'pdf/jquery.address-1.6.min.js',
			_THEME_JS_DIR_ . 'pdf/pdf.js',
			_THEME_JS_DIR_ . 'pdf/onload.js'
			
		]);
		
		
		$this->pushCSS([
			_THEME_CSS_DIR_ . 'pdf/catalogue.css',
			_THEME_CSS_DIR_ . 'pdf/reset.css',
			_THEME_CSS_DIR_ . 'pdf/static.css',
			_THEME_CSS_DIR_ . 'pdf/elements.css',
			_THEME_CSS_DIR_ . 'pdf/preloader.css',
			_THEME_CSS_DIR_ . 'pdf/font-awesome.min.css'
		]);
	
		
		
		 $this->context->smarty->assign(
            [
               'content_only' => true,
				'extraJs'        => $this->push_js_files,
			   'extracss'       => $this->push_css_files,
				'catalog_dir' => 'catalogue/catalogue.pdf'
            ]
        );

         

        $this->setTemplate(_PS_THEME_DIR_ . 'catalogue.tpl');
    }

}
