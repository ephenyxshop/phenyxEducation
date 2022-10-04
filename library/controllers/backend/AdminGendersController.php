<?php

/**
 * @property Gender $object
 */
class AdminGendersControllerCore extends AdminController {

    public function __construct() {

        $this->bootstrap = true;
        $this->table = 'gender';
        $this->className = 'Gender';
        $this->lang = true;
        $this->addRowAction('edit');
        $this->addRowAction('delete');

        $this->context = Context::getContext();

        if (!Tools::getValue('realedit')) {
            $this->deleted = false;
        }

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->la('Delete selected'),
                'confirm' => $this->la('Delete selected items?'),
                'icon'    => 'icon-trash',
            ],
        ];

        $this->default_image_height = 16;
        $this->default_image_width = 16;

        $this->fieldImageSettings = [
            'name' => 'image',
            'dir'  => 'genders',
        ];

        $this->fields_list = [
            'id_gender' => [
                'title' => $this->la('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name'      => [
                'title'      => $this->la('Social title'),
                'filter_key' => 'b!name',
            ],
            'type'      => [
                'title'           => $this->la('Gender'),
                'orderby'         => false,
                'type'            => 'select',
                'list'            => [
                    0 => $this->la('Male'),
                    1 => $this->la('Female'),
                    2 => $this->la('Neutral'),
                ],
                'filter_key'      => 'a!type',
                'callback'        => 'displayGenderType',
                'callback_object' => $this,
            ],
            'image'     => [
                'title'   => $this->la('Image'),
                'align'   => 'center',
                'image'   => 'genders',
                'orderby' => false,
                'search'  => false,
            ],
        ];

        parent::__construct();
    }

    public function initPageHeaderToolbar() {

        if (empty($this->display)) {
            $this->page_header_toolbar_btn['new_gender'] = [
                'href' => static::$currentIndex . '&addgender&token=' . $this->token,
                'desc' => $this->la('Add new title', null, null, false),
                'icon' => 'process-icon-new',
            ];
        }

        parent::initPageHeaderToolbar();
    }

    public function renderForm() {

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Social titles'),
                'icon'  => 'icon-male',
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->la('Social title'),
                    'name'     => 'name',
                    'lang'     => true,
                    'col'      => 4,
                    'hint'     => $this->la('Invalid characters:') . ' 0-9!&lt;&gt;,;?=+()@#"�{}_$%:',
                    'required' => true,
                ],
                [
                    'type'     => 'radio',
                    'label'    => $this->la('Gender'),
                    'name'     => 'type',
                    'required' => false,
                    'class'    => 't',
                    'values'   => [
                        [
                            'id'    => 'type_male',
                            'value' => 0,
                            'label' => $this->la('Male'),
                        ],
                        [
                            'id'    => 'type_female',
                            'value' => 1,
                            'label' => $this->la('Female'),
                        ],
                        [
                            'id'    => 'type_neutral',
                            'value' => 2,
                            'label' => $this->la('Neutral'),
                        ],
                    ],
                ],
                [
                    'type'  => 'file',
                    'label' => $this->la('Image'),
                    'name'  => 'image',
                    'col'   => 6,
                    'value' => true,
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Image width'),
                    'name'  => 'img_width',
                    'col'   => 2,
                    'hint'  => $this->la('Image width in pixels. Enter "0" to use the original size.'),
                ],
                [
                    'type'  => 'text',
                    'label' => $this->la('Image height'),
                    'name'  => 'img_height',
                    'col'   => 2,
                    'hint'  => $this->la('Image height in pixels. Enter "0" to use the original size.'),
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];

        /** @var Gender $obj */

        if (!($obj = $this->loadObject(true))) {
            return;
        }

        $this->fields_value = [
            'img_width'  => $this->default_image_width,
            'img_height' => $this->default_image_height,
            'image'      => $obj->getImage(),
        ];

        return parent::renderForm();
    }

    public function displayGenderType($value, $tr) {

        return $this->fields_list['type']['list'][$value];
    }

    protected function postImage($id) {

        if (isset($this->fieldImageSettings['name']) && isset($this->fieldImageSettings['dir'])) {

            if (!Validate::isInt(Tools::getValue('img_width')) || !Validate::isInt(Tools::getValue('img_height'))) {
                $this->errors[] = Tools::displayError('Width and height must be numeric values.');
            } else {

                if ((int) Tools::getValue('img_width') > 0 && (int) Tools::getValue('img_height') > 0) {
                    $width = (int) Tools::getValue('img_width');
                    $height = (int) Tools::getValue('img_height');
                } else {
                    $width = null;
                    $height = null;
                }

                return $this->uploadImage($id, $this->fieldImageSettings['name'], $this->fieldImageSettings['dir'] . '/', false, $width, $height);
            }

        }

        return !count($this->errors) ? true : false;
    }

    protected function afterImageUpload() {

        parent::afterImageUpload();

        if (($id_gender = (int) Tools::getValue('id_gender')) &&
            isset($_FILES) && count($_FILES) && file_exists(_EPH_GENDERS_DIR_ . $id_gender . '.jpg')) {
            $current_file = _EPH_TMP_IMG_DIR_ . 'gender_mini_' . $id_gender . '_' . $this->context->company->id . '.jpg';

            if (file_exists($current_file)) {
                unlink($current_file);
            }

        }

        return true;
    }

}
