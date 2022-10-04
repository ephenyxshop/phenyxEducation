<?php

/**
 * Class AdminCmsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminCmsControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    public $id_cms_category;
    protected $category;
    protected $position_identifier = 'id_cms';
    /** @var CMS $object */
    public $object;
    // @codingStandardsIgnoreEnd

    /**
     * AdminCmsControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->bootstrap = true;
        $this->context = Context::getContext();
        $this->table = 'cms';
        $this->className = 'CMS';
        $this->lang = true;

        parent::__construct();
    }

    /**
     * Render form
     *
     * @return string
     *
     * @since 1.9.1.0
     */
    public function renderForm() {

        if (!$this->loadObject(true)) {
            return '';
        }

        $this->displayGrid = false;

        if (Validate::isLoadedObject($this->object)) {
            $this->display = 'edit';
        } else {
            $this->display = 'add';
        }

        $categories = CMSCategory::getCategories($this->context->language->id, false);
        $htmlCategories = CMSCategory::recurseCMSCategory($categories, $categories[0][1], 1, $this->getFieldValue($this->object, 'id_cms_category'), 1);

        $this->fields_form = [
            'tinymce' => true,
            'legend'  => [
                'title' => $this->la('CMS Page'),
                'icon'  => 'icon-folder-close',
            ],
            'input'   => [
                // custom template
                [
                    'type' => 'hidden',
                    'name' => 'ajax',
                ],
                [
                    'type' => 'hidden',
                    'name' => 'action',
                ],
                [
                    'type'    => 'select_category',
                    'label'   => $this->la('CMS Category'),
                    'name'    => 'id_cms_category',
                    'options' => [
                        'html' => $htmlCategories,
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Meta title'),
                    'name'     => 'meta_title',
                    'id'       => 'name', // for copyMeta2friendlyURL compatibility
                    'lang'     => true,
                    'required' => true,
                    'class'    => 'copyMeta2friendlyURL',
                    'hint'     => $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
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
                    'hint'  => [
                        $this->la('To add "tags" click in the field, write something, and then press "Enter."'),
                        $this->la('Invalid characters:') . ' &lt;&gt;;=#{}',
                    ],
                ],
                [
                    'type'     => 'text',
                    'label'    => $this->la('Friendly URL'),
                    'name'     => 'link_rewrite',
                    'required' => true,
                    'lang'     => true,
                    'hint'     => $this->la('Only letters and the hyphen (-) character are allowed.'),
                ],
                [
                    'type'         => 'textarea',
                    'class'        => 'wysiwyg',
                    'label'        => $this->la('Page content'),
                    'name'         => 'content',
                    'autoload_rte' => true,
                    'lang'         => true,
                    'rows'         => 5,
                    'cols'         => 40,
                    'hint'         => $this->la('Invalid characters:') . ' <>;=#{}',
                ],
                [
                    'type'     => 'switch',
                    'label'    => $this->la('Indexation by search engines'),
                    'name'     => 'indexation',
                    'required' => false,
                    'class'    => 't',
                    'is_bool'  => true,
                    'values'   => [
                        [
                            'id'    => 'indexation_on',
                            'value' => 1,
                            'label' => $this->la('Enabled'),
                        ],
                        [
                            'id'    => 'indexation_off',
                            'value' => 0,
                            'label' => $this->la('Disabled'),
                        ],
                    ],
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
            ],
            'submit'  => [
                'title' => $this->la('Save'),
            ],
            'buttons' => [
                'save_and_preview' => [
                    'name'  => 'viewcms',
                    'type'  => 'submit',
                    'title' => $this->la('Save and preview'),
                    'class' => 'btn btn-default pull-right',
                    'icon'  => 'process-icon-preview',
                ],
            ],
        ];

        
        $this->form_ajax =1;

        if ($this->object->id > 0) {
            $this->form_action = 'updateCms';
            $this->editObject = 'Edition d‘une page CMS';
        } else {
            $this->form_action = 'addPageCms';
            $this->editObject = 'Ajouter une nouvelle page CMS';
        }

        $this->tpl_form_vars = [
            'active' => $this->object->active,
            'EPH_ALLOW_ACCENTED_CHARS_URL', (int) Configuration::get('EPH_ALLOW_ACCENTED_CHARS_URL'),
        ];
        $this->extraJs = [
			_EPH_JS_DIR_.'tinymce/tinymce.min.js',
            _EPH_JS_DIR_.'tinymce.inc.js',
		];

        return parent::renderForm();
    }

}
