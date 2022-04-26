<?php

/**
 * @since 1.9.1.0
 */
class MailTemplateCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'mail_template',
        'primary' => 'id_mail_template',
        'fields'  => [
            'template' => ['type' => self::TYPE_STRING, 'required' => true],
			'target' => ['type' => self::TYPE_STRING, 'required' => true],
            'name'     => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'required' => true, 'size' => 128],
            'version'  => ['type' => self::TYPE_STRING, 'validate' => 'isString', 'size' => 64],
        ],
    ];
    public $template;
	public $target;
    public $name;
    public $version;
    // @codingStandardsIgnoreEnd
    public $content;

    /**
     * GenderCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     * @param int|null $idShop
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null, $idShop = null) {

        parent::__construct($id, $idLang, $idShop);

        if ($this->id) {
            $this->content = $this->getTemplateContent();
        }

    }

    public function getTemplateContent() {

        if (file_exists(_PS_ROOT_DIR_ . '/mails/fr/' . $this->template)) {

            return file_get_contents(_PS_ROOT_DIR_ . '/mails/fr/' . $this->template);
        }

    }
	
	public static function getObjectByTemplateName($template) {
		
		return Db::getInstance()->getValue(
			(new DbQuery())
			->select('`id_mail_template`')
			->from('mail_template')
			->where('`template` LIKE  \'' . $template.'\'')
		);
	}

}
