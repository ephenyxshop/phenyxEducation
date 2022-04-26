<?php


class AdminFrontSliderControllerCore extends AdminController
{
    
	public function __construct() {

		$this->bootstrap = true;
		$this->table = 'layerslider';
		$this->className = 'FrontSlider';
		$this->publicName = $this->l('Gestion des Slides Front Office');

		$this->context = Context::getContext();

		parent::__construct();
		
		
	}
	
	public function postProcess() {
        parent::postProcess();
        
    }

    public function initPageHeaderToolbar() {
        // hide header toolbar
    }

    public function setMedia() {
        parent::setMedia();
		
		MediaAdmin::addJsDef([
			'AjaxLinkAdminFrontSlider' => $this->context->link->getAdminLink('AdminFrontSlider'),

		]);

        
		$this->addCSS(
            [

                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-pointer.min.css',
                __PS_BASE_URI__ . $this->admin_webpath . '/themes/' . $this->bo_theme . '/css/wp-specs.css',
            ]
        );
		$this->addJS(
                [
                   __PS_BASE_URI__ . $this->admin_webpath . '/js/wp-pointer.min.js',
                   __PS_BASE_URI__ . $this->admin_webpath . '/js/wp-specs.js',
					__PS_BASE_URI__ . $this->admin_webpath . '/js/layerSlider.js',
                ]
            );
		
		$GLOBALS['ls_token'] = $this->token;
        $GLOBALS['ls_screen'] = (object) array(
          'id' => 'toplevel_page_layerslider',
          'base' => 'toplevel_page_layerslider'
        );
        // simulate wp page
        ${'_GET'}['page'] = 'layerslider';

        
		require_once _PS_CLASS_DIR_.'frontslider/helper.php';
       
        if (isset(${'_COOKIE'}['ls-login'])) {
            $this->content = '<script>
                var doc = window.parent.document, $ = window.parent.jQuery;
                doc.cookie = "ls-login=; expires=Thu, 01 Jan 1970 00:00:01 GMT;";
                $(".ls-publish button").click();
                $(doc.getElementById("wpwrap")).css({ opacity: "", pointerEvents: "" });
                $(doc.getElementById("ls-login")).remove();
            </script>';
        } else {
            require_once _PS_CLASS_DIR_.'frontslider/default.php';
        }

        $this->context->smarty->unregisterFilter('output', 'smartyPackJSinHTML');
		
    }

    public function display()
    {
        $tmpl = '<script type="text/html" id="tmpl-template-store">
            <div id="ls-importing-modal-window">
                <header>
                    <h1>Template Store</h1>
                    <b class="dashicons dashicons-no"></b>
                </header>
                <div class="km-ui-modal-scrollable">
                    <p>
                        '.'Premium templates are only available after you connected your site with PhenyxShop\'s marketplace.'.'
                        <a href="https://www.youtube.com/watch?v=SLFFWyY2NYM" target="_blank" style="font-size:13px">Check this video for more details.</a>
                    </p>
                    <button class="button button-primary button-hero" id="btn-connect-ps">Connect to PhenyxShop Addons</button>
                </div>
            </div>
        </script>';
        $this->context->smarty->assign(array('content' => $tmpl.$this->content));
        $this->display_footer = false;

        parent::display();
    }
	
	public function ajaxProcessRemoveSlider() {
		
		$file = fopen("testProcessRemoveSlider.txt","w");
		$idLayerSlider = Tools::getValue('idSlider');
		fwrite($file, $idLayerSlider.PHP_EOL);
		$slider = new FrontSlider((int) $idLayerSlider);
		fwrite($file, print_r($slider, true));
		$result = $slider->delete();
		
		if($result) {
			$return = [
				'success' => true
			];
		} else {
			$return = [
				'success' => false
			];
		}
		
		die(Tools::jsonEncode($return));
	}
}
