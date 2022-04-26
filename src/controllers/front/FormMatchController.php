<?php

/**
 * Class FormMatchControllerCore
 *
 * @since 1.8.1.0
 */
class FormMatchControllerCore extends FrontController {

    // @codingStandardsIgnoreStart
    /** @var string $php_self */
    public $php_self = 'form-match';
    /** @var bool $ssl */
    public $ssl = true;
    // @codingStandardsIgnoreEnd
    

    
    public function init() {

       	$form = new PFGModel(1, 1);
		$link = $this->context->link->getPFGLink($form, $form->link_rewrite);
		Tools::redirect($link);

    }

   
    

}
