<?php

/**
 * Class HelperFormCore
 *
 * @since 1.8.1.0
 */
class HelperFormCore extends Helper {

    // @codingStandardsIgnoreStart
    /** @var int $id */
    public $id;
    /** @var bool $first_call */
    public $first_call = true;
    /** @var array of forms fields */
    protected $fields_form = [];
    /** @var array values of form fields */
    public $fields_value = [];
    /** @var string $name_controller */
    public $name_controller = '';
    /** @var string if not null, a title will be added on that list */
    public $title = null;
    /** @var string Used to override default 'submitAdd' parameter in form action attribute */
    public $submit_action;
    public $token;
    /** @var null|array $languages  */
    public $languages = null;
    public $default_form_language = null;
    public $allow_employee_form_lang = null;
    /** @var bool $show_cancel_button */
    public $show_cancel_button = false;
    /** @var string $back_url */
    public $back_url = '#';
	
	public $extraCss;
	
	public $extraJs;
	
	
    // @codingStandardsIgnoreEnd

    /**
     * HelperFormCore constructor.
     *
     * @since   1.8.1.0
     * @version 1.8.5.0
     */
    public function __construct() {

        $this->base_folder = 'helpers/form/';
       
		$this->base_tpl = 'form.tpl';
        parent::__construct();
    }

    /**
     * @param array $fieldsForm
     *
     * @return string
     *
     * @throws Exception
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @throws SmartyException
     * @since   1.8.1.0
     * @version 1.8.5.0
     */
    public function generateForm($fieldsForm) {

        $this->fields_form = $fieldsForm;

        return $this->generate();
    }

    /**
     * @return string
     * @throws Exception
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @throws SmartyException
     * @since   1.8.1.0
     * @version 1.8.5.0
     */
    public function generate() {

        $this->tpl = $this->createTemplate($this->base_tpl);

        if (is_null($this->submit_action)) {
            $this->submit_action = 'submitAdd' . $this->table;
        }

        $categories = true;
        $color = true;
        $date = true;
        $tinymce = true;
        $textareaAutosize = true;

        foreach ($this->fields_form as $fieldsetKey => &$fieldset) {

            if (isset($fieldset['form']['tabs'])) {
                $tabs[] = $fieldset['form']['tabs'];
            }

            if (isset($fieldset['form']['input'])) {

                foreach ($fieldset['form']['input'] as $key => &$params) {
                    // If the condition is not met, the field will not be displayed

                    if (isset($params['condition']) && !$params['condition']) {
                        unset($this->fields_form[$fieldsetKey]['form']['input'][$key]);
                    }

                    switch ($params['type']) {
                    case 'select':
                        $fieldName = (string) $params['name'];
                        // If multiple select check that 'name' field is suffixed with '[]'

                        if (isset($params['multiple']) && $params['multiple'] && stripos($fieldName, '[]') === false) {
                            $params['name'] .= '[]';
                        }

                        break;

                    case 'categories':

                        if ($categories) {

                            if (!isset($params['tree']['id'])) {
                                throw new PhenyxShopException('Id must be filled for categories tree');
                            }

                            $tree = new HelperTreeCategories($params['tree']['id'], isset($params['tree']['title']) ? $params['tree']['title'] : null);

                            if (isset($params['name'])) {
                                $tree->setInputName($params['name']);
                            }

                            if (isset($params['tree']['selected_categories'])) {
                                $tree->setSelectedCategories($params['tree']['selected_categories']);
                            }

                            if (isset($params['tree']['disabled_categories'])) {
                                $tree->setDisabledCategories($params['tree']['disabled_categories']);
                            }

                            if (isset($params['tree']['root_category'])) {
                                $tree->setRootCategory($params['tree']['root_category']);
                            }

                            if (isset($params['tree']['use_search'])) {
                                $tree->setUseSearch($params['tree']['use_search']);
                            }

                            if (isset($params['tree']['use_checkbox'])) {
                                $tree->setUseCheckBox($params['tree']['use_checkbox']);
                            }

                            if (isset($params['tree']['set_data'])) {
                                $tree->setData($params['tree']['set_data']);
                            }

                            $this->context->smarty->assign('categories_tree', $tree->render());
                            $categories = false;
                        }

                        break;
					case 'image':
							
							$src = !empty($params['image']) ? $params['image'] : '<img src="'.$this->fields_value[$params['name']].'" id="image'.$params['name'].'"  alt="'.$params['label'].'">';
							
							$format = $params['format'];
							$html = '<div class="imageuploadify-container-image">'.$src.'</div><input id="'.$params['name'].'File" type="file" data-target="image'.$params['name'].'" accept="image/*" multiple>
							<script type="text/javascript">
								$(document).ready(function() {
									$("#'.$params['name'].'File").imageuploadify();
								});
							</script>';
							
						$params['paramImage'] = $html;
						break;

                    case 'file':
							
						
                        $uploader = new HelperUploader();
                        $uploader->setId(isset($params['id']) ? $params['id'] : null);
                        $uploader->setName($params['name']);
                        $uploader->setUrl(isset($params['url']) ? $params['url'] : null);
                        $uploader->setMultiple(isset($params['multiple']) ? $params['multiple'] : false);
                        $uploader->setUseAjax(isset($params['ajax']) ? $params['ajax'] : false);
                        $uploader->setMaxFiles(isset($params['max_files']) ? $params['max_files'] : null);

                        if (isset($params['files']) && $params['files']) {
                            $uploader->setFiles($params['files']);
                        } else if (isset($params['image']) && $params['image']) {
                            // Use for retrocompatibility
                            $uploader->setFiles(
                                [
                                    0 => [
                                        'type'       => HelperUploader::TYPE_IMAGE,
                                        'image'      => isset($params['image']) ? $params['image'] : null,
                                        'size'       => isset($params['size']) ? $params['size'] : null,
                                        'delete_url' => isset($params['delete_url']) ? $params['delete_url'] : null,
                                    ],
                                ]
                            );
                        }

                        if (isset($params['file']) && $params['file']) {
                            // Use for retrocompatibility
                            $uploader->setFiles(
                                [
                                    0 => [
                                        'type'         => HelperUploader::TYPE_FILE,
                                        'size'         => isset($params['size']) ? $params['size'] : null,
                                        'delete_url'   => isset($params['delete_url']) ? $params['delete_url'] : null,
                                        'download_url' => isset($params['file']) ? $params['file'] : null,
                                    ],
                                ]
                            );
                        }

                        if (isset($params['thumb']) && $params['thumb']) {
                            // Use for retrocompatibility
                            $uploader->setFiles(
                                [
                                    0 => [
                                        'type'  => HelperUploader::TYPE_IMAGE,
                                        'image' => isset($params['thumb']) ? '<img src="' . $params['thumb'] . '" alt="' . (isset($params['title']) ? $params['title'] : '') . '" title="' . (isset($params['title']) ? $params['title'] : '') . '" />' : null,
                                    ],
                                ]
                            );
                        }

                        $uploader->setTitle(isset($params['title']) ? $params['title'] : null);
                        $params['file'] = $uploader->render();
                        break;

                    case 'color':

                        if ($color) {
                            // Added JS file
                            $this->context->controller->addJqueryPlugin('colorpicker');
                            $color = false;
                        }

                        break;

                    case 'date':

                        if ($date) {
                            $this->context->controller->addJqueryUI('ui.datepicker');
                            $date = false;
                        }

                        break;

                    case 'textarea':

                        if ($tinymce) {
                            $iso = $this->context->language->iso_code;
                            $this->tpl_vars['iso'] = file_exists(_PS_CORE_DIR_ . '/js/tiny_mce/langs/' . $iso . '.js') ? $iso : 'en';
                            $this->tpl_vars['path_css'] = _THEME_CSS_DIR_;
                            $this->tpl_vars['ad'] = __PS_BASE_URI__ . basename(_PS_ADMIN_DIR_);
                            $this->tpl_vars['tinymce'] = true;

                            $this->context->controller->addJS(_PS_JS_DIR_ . 'tiny_mce/tiny_mce.js');
                            $this->context->controller->addJS(_PS_JS_DIR_ . 'admin/tinymce.inc.js');
                            $tinymce = false;
                        }

                        if ($textareaAutosize) {
                            $this->context->controller->addJqueryPlugin('autosize');
                            $textareaAutosize = false;
                        }

                        break;

                    case 'tags':
                        $this->context->controller->addJqueryPlugin('tagify');
                        break;

                    case 'code':
                        $this->context->controller->addJS(_PS_JS_DIR_ . 'ace/ace.js');
                        $this->context->controller->addCSS(_PS_JS_DIR_ . 'ace/aceinput.css');
                        break;

                    case 'shop':
                        $disableShops = isset($params['disable_shared']) ? $params['disable_shared'] : false;
                        $params['html'] = $this->renderAssoShop($disableShops);

                        if (Shop::getTotalShops(false) == 1) {

                            if ((isset($this->fields_form[$fieldsetKey]['form']['force']) && !$this->fields_form[$fieldsetKey]['form']['force']) || !isset($this->fields_form[$fieldsetKey]['form']['force'])) {
                                unset($this->fields_form[$fieldsetKey]['form']['input'][$key]);
                            }

                        }

                        break;
                    }

                }

            }

        }
		

        $this->tpl->assign(
            [
                'title'                 => $this->title,
                'toolbar_btn'           => $this->toolbar_btn,
                'show_toolbar'          => $this->show_toolbar,
                'toolbar_scroll'        => $this->toolbar_scroll,
                'submit_action'         => $this->submit_action,
                'firstCall'             => $this->first_call,
                'current'               => $this->currentIndex,
                'token'                 => $this->token,
                'table'                 => $this->table,
                'identifier'            => $this->identifier,
                'name_controller'       => $this->name_controller,
                'languages'             => $this->languages,
                'current_id_lang'       => $this->context->language->id,
                'defaultFormLanguage'   => $this->default_form_language,
                'allowEmployeeFormLang' => $this->allow_employee_form_lang,
                'form_id'               => $this->id,
                'tabs'                  => (isset($tabs)) ? $tabs : null,
                'fields'                => $this->fields_form,
                'fields_value'          => $this->fields_value,
                'required_fields'       => $this->getFieldsRequired(),
                'vat_number'            => Module::isInstalled('vatnumber') && file_exists(_PS_MODULE_DIR_ . 'vatnumber/ajax.php'),
                'module_dir'            => _MODULE_DIR_,
                'base_url'              => $this->context->shop->getBaseURL(),
                'contains_states'       => (isset($this->fields_value['id_country']) && isset($this->fields_value['id_state'])) ? Country::containsStates($this->fields_value['id_country']) : null,
                'show_cancel_button'    => $this->show_cancel_button,
                'back_url'              => $this->back_url,
				'_is_module'			=> $this->_is_module,
				'extraCss'				=> $this->extraCss,
				'extraJs'				=> $this->extraJs
            ]
        );

        return parent::generate();
    }

    /**
     * Return true if there are required fields
     *
     * @return bool
     *
     * @since 1.8.1.0
     * @version 1.8.5.0
     */
    public function getFieldsRequired() {

        foreach ($this->fields_form as $fieldset) {

            if (isset($fieldset['form']['input'])) {

                foreach ($fieldset['form']['input'] as $input) {

                    if (!empty($input['required']) && $input['type'] != 'radio') {
                        return true;
                    }

                }

            }

        }

        return false;
    }
	
	 public function generateTabScript($controller) {

        return '<script type="text/javascript">' . PHP_EOL . '
                    $(document).ready(function(){' . PHP_EOL . '
                        $( "#content_' . $controller . '").tabs({' . PHP_EOL . '
                            show: { effect: "blind", duration: 800 },' . PHP_EOL . '
                        });' . PHP_EOL . '
                    });' . PHP_EOL . '
                </script>' . PHP_EOL;
    }

    /**
     * Render an area to determinate shop association
     *
     * @param bool $disableShared
     * @param null $templateDirectory
     *
     * @return string
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since   1.8.1.0
     * @version 1.8.5.0
     */
    public function renderAssoShop($disableShared = false, $templateDirectory = null) {

        if (!Shop::isFeatureActive()) {
            return '';
        }

        $assos = [];

        if ((int) $this->id) {

            foreach (Db::getInstance()->executeS(
                (new DbQuery())
                ->select('`id_shop`, `' . bqSQL($this->identifier) . '`')
                ->from(bqSQL($this->table) . '_shop')
                ->where('`' . bqSQL($this->identifier) . '` = ' . (int) $this->id)
            ) as $row) {
                $assos[$row['id_shop']] = $row['id_shop'];
            }

        } else {

            switch (Shop::getContext()) {
            case Shop::CONTEXT_SHOP:
                $assos[Shop::getContextShopID()] = Shop::getContextShopID();
                break;

            case Shop::CONTEXT_GROUP:

                foreach (Shop::getShops(false, Shop::getContextShopGroupID(), true) as $idShop) {
                    $assos[$idShop] = $idShop;
                }

                break;

            default:

                foreach (Shop::getShops(false, null, true) as $idShop) {
                    $assos[$idShop] = $idShop;
                }

                break;
            }

        }

        /*$nb_shop = 0;
                    foreach ($tree as &$value)
                    {
                        $value['disable_shops'] = (isset($value[$disable_shared]) && $value[$disable_shared]);
                        $nb_shop += count($value['shops']);
        */

        $tree = new HelperTreeShops('shop-tree', 'Shops');

        if (isset($templateDirectory)) {
            $tree->setTemplateDirectory($templateDirectory);
        }

        $tree->setSelectedShops($assos);
        $tree->setAttribute('table', $this->table);

        return $tree->render();
    }

}
