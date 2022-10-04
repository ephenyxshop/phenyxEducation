<?php

/**
 * Class AdminCmsCategoriesControllerCore
 *
 * @since 1.8.1.0
 */
class AdminCmsCategoriesControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    /** @var object CMSCategory() instance for navigation */
    protected $cms_category;

    protected $position_identifier = 'id_cms_category_to_move';
    // @codingStandardsIgnoreEnd

    /**
     * AdminCmsCategoriesControllerCore constructor.
     *
     * @since 1.8.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->is_cms = true;
        $this->table = 'cms_category';
        $this->list_id = 'cms_category';
        $this->className = 'CMSCategory';
        $this->publicName = $this->la('CMS Categories');

        parent::__construct();
    }

    /**
     * @return string|null
     *
     * @since 1.8.1.0
     */
    public function renderForm() {

        if (!$this->loadObject(true)) {
            return '';
        }

        $this->display = 'edit';
       

        if (!$this->loadObject(true)) {
            return null;
        }

        $categories = CMSCategory::getCategories($this->context->language->id, false);
        $htmlCategories = CMSCategory::recurseCMSCategory($categories, $categories[0][1], 1, $this->getFieldValue($this->object, 'id_parent'), 1);

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('CMS Category'),
                'icon'  => 'icon-folder-close',
            ],
            'input'  => [
                [
                    'type' => 'hidden',
                    'name' => 'ajax',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'action',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Name'),
                    'name'     => 'name',
                    'class'    => 'copyMeta2friendlyURL',
                    'required' => true,
                    'lang'     => true,
                    'hint'     => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Displayed'),
                    'name'     => 'active',
                    'required' => false,
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'active_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'active_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
                ],
                // custom template
                [
                    'type'    => 'select_category',
                    'label'   => $this->la('Parent CMS Category'),
                    'name'    => 'id_parent',
                    'options' => [
                        'html' => $htmlCategories,
                    ],
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->la('Description'),
                    'name'  => 'description',
                    'lang'  => true,
                    'rows'  => 5,
                    'cols'  => 40,
                    'hint'  => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Meta title'),
                    'name'  => 'meta_title',
                    'lang'  => true,
                    'hint'  => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Meta description'),
                    'name'  => 'meta_description',
                    'lang'  => true,
                    'hint'  => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                ],
                [
                    'type'  => 'tags',
                    'label' => $this->la('Meta keywords'),
                    'name'  => 'meta_keywords',
                    'lang'  => true,
                    'hint'  => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Friendly URL'),
                    'name'     => 'link_rewrite',
                    'required' => true,
                    'lang'     => true,
                    'hint'     => $this->la('Only letters and the minus (-) character are allowed.'),
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];

        

        $this->fields_value['ajax'] = 1;

        if ($this->id > 0) {
            $this->fields_value['action'] = 'updateCmsCategorie';
            $this->editObject = 'Edition d‘une catégorie CMS';
        } else {
            $this->fields_value['action'] = 'addPageCmsCategorie';
            $this->editObject = 'Ajouter une nouvelle catégorie CMS';
        }
         $this->extraJs = [
			_EPH_JS_DIR_.'tinymce/tinymce.min.js',
            _EPH_JS_DIR_.'tinymce.inc.js',
		];

        $this->tpl_form_vars['EPH_ALLOW_ACCENTED_CHARS_URL'] = (int) Configuration::get('EPH_ALLOW_ACCENTED_CHARS_URL');

        return parent::renderForm();
    }

}
