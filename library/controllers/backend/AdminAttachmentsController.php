<?php

/**
 * Class AdminAttachmentsControllerCore
 *
 * @since 1.9.1.0
 */
class AdminAttachmentsControllerCore extends AdminController {

    // @codingStandardsIgnoreStart
    public $bootstrap = true;

    protected $product_attachements = [];
    // @codingStandardsIgnoreEnd

    /**
     * AdminAttachmentsControllerCore constructor.
     *
     * @since 1.9.1.0
     */
    public function __construct() {

        $this->table = 'attachment';
        $this->className = 'Attachment';
        $this->lang = true;

        $this->addRowAction('edit');
        $this->addRowAction('view');
        $this->addRowAction('delete');

        $this->_select = 'IFNULL(virtual_product_attachment.products, 0) as products';
        $this->_join = 'LEFT JOIN (SELECT id_attachment, COUNT(*) as products FROM ' . _DB_PREFIX_ . 'product_attachment GROUP BY id_attachment) AS virtual_product_attachment ON a.id_attachment = virtual_product_attachment.id_attachment';
        $this->_use_found_rows = false;

        $this->fields_list = [
            'id_attachment' => [
                'title' => $this->la('ID'),
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ],
            'name'          => [
                'title' => $this->la('Name'),
            ],
            'file'          => [
                'title' => $this->la('File'),
            ],
            'file_size'     => [
                'title'    => $this->la('Size'),
                'callback' => 'displayHumanReadableSize',
            ],
            'products'      => [
                'title'      => $this->la('Associated with'),
                'suffix'     => $this->la('product(s)'),
                'filter_key' => 'virtual_product_attachment!products',
            ],
        ];

        $this->bulk_actions = [
            'delete' => [
                'text'    => $this->la('Delete selected'),
                'icon'    => 'icon-trash',
                'confirm' => $this->la('Delete selected items?'),
            ],
        ];

        parent::__construct();
    }

    /**
     * @param $size
     *
     * @return string
     *
     * @since 1.9.1.0
     */
    public static function displayHumanReadableSize($size) {

        return Tools::formatBytes($size);
    }

    /**
     * @since 1.9.1.0
     */
    public function setMedia() {

        parent::setMedia();

        $this->addJs(__EPH_BASE_URI__ . '/js/attachments.js');
        Media::addJsDefL('confirm_text', $this->la('This attachment is associated with the following products, do you really want to  delete it?', null, true, false));
    }

    

    /**
     * @since 1.9.1.0
     */
    public function renderView() {

        if (($obj = $this->loadObject(true)) && Validate::isLoadedObject($obj)) {
            $link = $this->context->link->getPageLink('attachment', true, null, 'id_attachment=' . $obj->id);
            Tools::redirectLink($link);
        }

        return $this->displayWarning($this->la('File not found'));
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     */
    public function renderForm() {

        if (($obj = $this->loadObject(true)) && Validate::isLoadedObject($obj)) {
            /** @var Attachment $obj */
            $link = $this->context->link->getPageLink('attachment', true, null, 'id_attachment=' . $obj->id);

            if (file_exists(_EPH_DOWNLOAD_DIR_ . $obj->file)) {
                $size = round(filesize(_EPH_DOWNLOAD_DIR_ . $obj->file) / 1024);
            }

        }

        $this->fields_form = [
            'legend' => [
                'title' => $this->la('Attachment'),
                'icon'  => 'icon-paper-clip',
            ],
            'input'  => [
                [
                    'type'     => 'text',
                    'label'    => $this->la('Filename'),
                    'name'     => 'name',
                    'required' => true,
                    'lang'     => true,
                    'col'      => 4,
                ],
                [
                    'type'  => 'textarea',
                    'label' => $this->la('Description'),
                    'name'  => 'description',
                    'lang'  => true,
                    'col'   => 6,
                ],
                [
                    'type'     => 'file',
                    'file'     => isset($link) ? $link : null,
                    'size'     => isset($size) ? $size : null,
                    'label'    => $this->la('File'),
                    'name'     => 'file',
                    'required' => true,
                    'col'      => 6,
                ],
            ],
            'submit' => [
                'title' => $this->la('Save'),
            ],
        ];

        return parent::renderForm();
    }

    /**
     * @param int  $idLang
     * @param null $orderBy
     * @param null $orderWay
     * @param int  $start
     * @param null $limit
     * @param bool $idLangShop
     *
     * @since 1.9.1.0
     */
    public function getList($idLang, $orderBy = null, $orderWay = null, $start = 0, $limit = null, $idLangShop = false) {

        parent::getList((int) $idLang, $orderBy, $orderWay, $start, $limit, $idLangShop);

        if (count($this->_list)) {
            $this->product_attachements = Attachment::getProductAttached((int) $idLang, $this->_list);

            $listProductList = [];

            foreach ($this->_list as $list) {
                $productList = '';

                if (isset($this->product_attachements[$list['id_attachment']])) {

                    foreach ($this->product_attachements[$list['id_attachment']] as $product) {
                        $productList .= $product . ', ';
                    }

                    $productList = rtrim($productList, ', ');
                }

                $listProductList[$list['id_attachment']] = $productList;
            }

            // Assign array in list_action_delete.tpl
            $this->tpl_delete_link_vars = [
                'product_list'         => $listProductList,
                'product_attachements' => $this->product_attachements,
            ];
        }

    }

    /**
     * @return bool|null
     *
     * @since 1.9.1.0
     */
    public function postProcess() {

        if (_EPH_MODE_DEMO_) {
            $this->errors[] = Tools::displayError('This functionality has been disabled.');

            return null;
        }

        if (Tools::isSubmit('submitAdd' . $this->table)) {
            $id = (int) Tools::getValue('id_attachment');

            if ($id && $a = new Attachment($id)) {
                $_POST['file'] = $a->file;
                $_POST['mime'] = $a->mime;
            }

            if (!count($this->errors)) {

                if (isset($_FILES['file']) && is_uploaded_file($_FILES['file']['tmp_name'])) {

                    if ($_FILES['file']['size'] > (Configuration::get('EPH_ATTACHMENT_MAXIMUM_SIZE') * 1024 * 1024)) {
                        $this->errors[] = sprintf(
                            $this->la('The file is too large. Maximum size allowed is: %1$d kB. The file you are trying to upload is %2$d kB.'),
                            (Configuration::get('EPH_ATTACHMENT_MAXIMUM_SIZE') * 1024),
                            number_format(($_FILES['file']['size'] / 1024), 2, '.', '')
                        );
                    } else {

                        do {
                            $uniqid = sha1(microtime());
                        } while (file_exists(_EPH_DOWNLOAD_DIR_ . $uniqid));

                        if (!move_uploaded_file($_FILES['file']['tmp_name'], _EPH_DOWNLOAD_DIR_ . $uniqid)) {
                            $this->errors[] = $this->la('Failed to copy the file.');
                        }

                        $_POST['file_name'] = $_FILES['file']['name'];
                        @unlink($_FILES['file']['tmp_name']);

                        if (!sizeof($this->errors) && isset($a) && file_exists(_EPH_DOWNLOAD_DIR_ . $a->file)) {
                            unlink(_EPH_DOWNLOAD_DIR_ . $a->file);
                        }

                        $_POST['file'] = $uniqid;
                        $_POST['mime'] = $_FILES['file']['type'];
                    }

                } else
                if (array_key_exists('file', $_FILES) && (int) $_FILES['file']['error'] === 1) {
                    $maxUpload = (int) ini_get('upload_max_filesize');
                    $maxPost = (int) ini_get('post_max_size');
                    $uploadMb = min($maxUpload, $maxPost);
                    $this->errors[] = sprintf(
                        $this->la('The file %1$s exceeds the size allowed by the server. The limit is set to %2$d MB.'),
                        '<b>' . $_FILES['file']['name'] . '</b> ',
                        '<b>' . $uploadMb . '</b>'
                    );
                } else
                if (!isset($a) || (isset($a) && !file_exists(_EPH_DOWNLOAD_DIR_ . $a->file))) {
                    $this->errors[] = $this->la('Upload error. Please check your server configurations for the maximum upload size allowed.');
                }

            }

            $this->validateRules();
        }

        $return = parent::postProcess();

        if (!$return && isset($uniqid) && file_exists(_EPH_DOWNLOAD_DIR_ . $uniqid)) {
            unlink(_EPH_DOWNLOAD_DIR_ . $uniqid);
        }

        return $return;
    }

}
