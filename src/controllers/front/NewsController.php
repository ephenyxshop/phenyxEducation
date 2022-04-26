<?php

/**
 * Class IndexControllerCore
 *
 * @since 1.8.1.0
 */
class NewsControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'news';
    // @codingStandardsIgnoreEnd

    /**
     * Initialize content
     *
     * @return void
     *
     * @since 1.8.1.0
     */

    public function setMedia() {

        parent::setMedia();
        $this->addCSS(_THEME_CSS_DIR_ . 'index.css');
        $this->addCSS(_THEME_CSS_DIR_ . 'news.css');
        $this->removeCSS(_THEME_CSS_DIR_ . 'autoload/jquery-ui.css');
        $this->addJS('https://cdnjs.cloudflare.com/ajax/libs/prefixfree/1.0.7/prefixfree.min.js');
        $this->addJS(_THEME_JS_DIR_ . 'caroussel.js');
    }

    public function initContent() {

        parent::initContent();		

        $actualites = Actualite::getActualites();
        $this->context->smarty->assign(
            [
                'actualites' => $actualites,
            ]
        );

        $this->setTemplate(_PS_THEME_DIR_ . 'news.tpl');
    }
	
	public function sort_objects_by_date($actualites) {
		
		$result = [];
		$nbKey = count($actualites)-1;
		for($i=$nbKey; $i >=0; $i--) {
			$result[$i] = $actualites[$i];
		}
		
    	return $result;
		
	}
}
