<?php

class SponsorPartnerCore extends ObjectModel {

	public static $definition = [
		'table'   => 'sponsor_partner',
		'primary' => 'id_sponsor_partner',
		'fields'  => [
			'name'                => ['type' => self::TYPE_STRING, 'validate' => 'isGenericName'],
			'link_rewrite'        => ['type' => self::TYPE_STRING, 'validate' => 'isLinkRewrite', 'size' => 128],
			'logo'        		  => ['type' => self::TYPE_STRING],
			'target'        	  => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'description_short'   => ['type' => self::TYPE_HTML, 'validate' => 'isString'],
			'description'         => ['type' => self::TYPE_HTML, 'validate' => 'isString'],
			'message_sponsor'     => ['type' => self::TYPE_HTML, 'validate' => 'isString', 'lang' => true, 'required' => false],
			'message_child'       => ['type' => self::TYPE_HTML, 'validate' => 'isString', 'lang' => true, 'required' => false],
			'date_add'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'date_upd'            => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
		],
	];

	
	public $name;
	public $link_rewrite;
	public $logo;
	public $target;
	public $description_short;
	public $description;
	public $message_sponsor;
	public $message_child;
	public $date_add;
	public $date_upd;

	public function __construct($id = null) {

		parent::__construct($id);

	}

	public function add($autodate = true, $null_values = true) {

		
		$this->link_rewrite = Tools::link_rewrite($this->name);
		if (!parent::add($autodate, $null_values)) {
			return false;
		}

		return true;
	}

	public function update($nullValues = false) {
		$this->link_rewrite = Tools::link_rewrite($this->name);
		$success = parent::update(true);

		return $success;
	}

	public function delete() {

		
		return parent::delete();
	}


	

	
}
