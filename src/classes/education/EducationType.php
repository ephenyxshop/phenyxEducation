<?php

/**
 * Class EducationTypeCore
 *
 * @since 2.1.0.0
 */
class EducationTypeCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'     => 'education_type',
		'primary'   => 'id_education_type',
		'multilang' => true,
		'fields'    => [
			'reference'          => ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
			'id_certification'           => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'active'             => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'date_add'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
			'date_upd'           => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

			'link_rewrite'       => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isLinkRewrite', 'required' => true, 'size' => 128],
			'meta_title'         => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 128],
			'meta_description'   => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 300],
			'name'               => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isName', 'required' => true],
			'description'        => ['type' => self::TYPE_STRING, 'lang' => true],
			'description_up'     => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
			'description_bottom' => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml'],
		],
	];

	public $id;
	public $reference;
	public $id_certification;
	protected static $_links = [];
	public $link_rewrite;
	public $meta_title;
	public $meta_description;
	public $name;
	public $description;
	public $description_up;
	public $description_bottom;
	public $active;
	public $date_add;
	public $date_upd;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null, $idLang = null) {

		parent::__construct($id, $idLang);

	}

	public function add($autoDate = true, $nullValues = false) {

		if (empty($this->reference)) {
			$this->reference = $this->generateReference();
		}

		if (!parent::add($autoDate, $nullValues)) {
			return false;
		}

		return true;
	}

	public static function getEducationType($idLang) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('`id_education_type`, `name`')
				->from('education_type_lang')
				->where('`id_lang` = ' . (int) $idLang)
				->orderBy('`id_education_type` ASC')
		);
	}

	public function getEducationOption() {

		$return = [];
		$options = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_option`')
				->from('education_option', 'c')
				->where('`id_education` = ' . $this->id)
		);

		foreach ($options as $option) {
			$return[] = new EducationOption($option['id_education_option']);
		}

		return $return;
	}

	public static function getLinkRewrite($idEducationType, $idLang) {

		if (!Validate::isUnsignedId($idEducationType) || !Validate::isUnsignedId($idLang)) {
			return false;
		}

		if (!isset(static::$_links[$idEducationType . '-' . $idLang])) {
			static::$_links[$idEducationType . '-' . $idLang] = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
				'
            SELECT cl.`link_rewrite`
            FROM `' . _DB_PREFIX_ . 'education_type_lang` cl
            WHERE `id_lang` = ' . (int) $idLang . '
            AND cl.`id_education_type` = ' . (int) $idEducationType
			);
		}

		return static::$_links[$idEducationType . '-' . $idLang];
	}

	public function generateReference() {

		return strtoupper(Tools::passwdGen(9, 'NO_NUMERIC'));
	}

	public static function getEducationTypesFields() {

		$fields = [
			[
				'title'    => 'ID',
				'dataIndx' => 'id_education_type',
				'dataType' => 'integer',
				'align'    => 'center',
				'maxWidth' => 20,
				'filter'   => [

					'crules' => [['condition' => "begin"]],
				],
			],

			[
				'title'    => 'Name',
				'width'    => 200,
				'dataIndx' => 'name',
				'dataType' => 'string',
				'align'    => 'left',
				'filter'   => [

					'crules' => [['condition' => "begin"]],
				],

			],

			[

				'dataIndx' => 'active',
				'dataType' => 'integer',
				'hidden'   => true,
				'filter'   => [
					'crules' => [['condition' => "equal"]],
				],

			],

			[
				'title'    => 'Publiée',
				'maxWidth' => 100,
				'dataIndx' => 'education_state',
				'align'    => 'center',
				'dataType' => 'html',
				'filter'   => [
					'attr'   => "id=\"activeSelector\", placeholder=" . '--Selectionner--' . " readonly",
					'crules' => [['condition' => "equal"]],
				],
			],

		];
		die(Tools::jsonEncode($fields));

	}

	public static function addNewEducationGroups() {

		$name = Tools::getValue('name');
		$description = Tools::getValue('description');
		$type = new EducationType();
		$type->name = $name;
		$type->description = $description;
		$type->active = 1;
		$type->add();
		$return = [
			'success' => true,
			'message' => 'Le nouveau type a été ajouté avec succès',
		];
		die(Tools::jsonEncode($return));
	}

	public function getEducations($idLang, $getTotal = false, $active = true, $random = false, $randomNumberProducts = 1, $checkAccess = true, Context $context = null) {

		if (!$context) {
			$context = Context::getContext();
		}

		$catsToSearchIn = [$this->id];

		/** Return only the number of products */

		if ($getTotal) {
			$sql = 'SELECT COUNT(DISTINCT(cp.`id_education`)) AS total
                    FROM `' . _DB_PREFIX_ . 'education` p
                    WHERE p.`id_education_type` IN (' . implode(',', $catsToSearchIn) . ')';

			return (int) Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue($sql);
		}

		$sql = 'SELECT p.*,   pl.`description`, pl.`description_short`,  pl.`link_rewrite`, pl.`meta_description`, pl.`meta_keywords`, pl.`meta_title`, pl.`name`, ie.`id_image_education` id_image,
        il.`legend` as legend
                FROM `' . _DB_PREFIX_ . 'education` p
                LEFT JOIN `' . _DB_PREFIX_ . 'education_lang` pl  ON (p.`id_education` = pl.`id_education`  AND pl.`id_lang` = ' . (int) $idLang . ')
                LEFT JOIN `' . _DB_PREFIX_ . 'image_education` ie   ON (ie.`id_education` = p.`id_education` AND ie.cover=1)
                LEFT JOIN `' . _DB_PREFIX_ . 'image_education_lang` il  ON (ie.`id_image_education` = il.`id_image_education`  AND il.`id_lang` = ' . (int) $idLang . ')
                WHERE p.active = 1 AND p.`cache_is_pack` = 0 AND p.`id_education_type` IN (' . implode(',', $catsToSearchIn) . ')';

		$results = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql, true, false);

		if (!$results) {
			return [];
		}

		/** Modify SQL result */
		return Education::getEducationsProperties($idLang, $results);
	}

	public static function getReferentEducationTypes() {

		$return = [];
		$educationTypes = Db::getInstance()->executeS(
			'SELECT `id_education_type`, `reference`
            FROM `' . _DB_PREFIX_ . 'education_type`
            ORDER BY `id_education_type`'
		);

		return $educationTypes;
	}

	public static function getIdEducationTypeByRef($reference) {

		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
			(new DbQuery())
				->select('`id_education_type`')
				->from('education_type')
				->where('`reference` LIKE \'' . $reference . '\'')
		);
	}

}
