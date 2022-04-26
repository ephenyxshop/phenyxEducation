<?php

/**
 * Class EducationPrerequis
 *
 * @since 2.1.0.0
 */
class EducationPrerequisCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'     => 'education_prerequis',
		'primary'   => 'id_education_prerequis',
		'multilang' => true,
		'fields'    => [
			'name'      => ['type' => self::TYPE_STRING, 'required' => true],
			'content'   => ['type' => self::TYPE_STRING, 'required' => true],
			'version'   => ['type' => self::TYPE_STRING, 'required' => true],
			'min_score' => ['type' => self::TYPE_INT, 'required' => true],
			/* Lang fields */
			'header'    => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 3999999999999],
			'tags'      => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 3999999999999],
		],
	];

	public $name;
	public $content;
	public $version;
	public $min_score;
	public $header;
	public $tags;

	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($idEducation = null, $idLang = null, Context $context = null) {

		parent::__construct($idEducation, $idLang);
		$this->content = unserialize($this->content);

	}

	public function add($autoDate = false, $nullValues = false) {

		$this->content = serialize($this->content);

		if (!parent::add($autoDate, $nullValues)) {
			return false;
		}

		return true;
	}

	public function update($nullValues = false) {

		$this->content = serialize($this->content);
		return parent::update($nullValues);

	}

	public static function getEducationPrerequis($idEducation) {

		$result = [];
		$prerequis = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
			(new DbQuery())
				->select('id_education_prerequis')
				->from('education_attribute')
				->where('`id_education` = ' . $idEducation)
		);

		foreach ($prerequis as $prerequi) {
			$result[] = new EducationPrerequis($prerequi['id_education_prerequis'], 1);
		}

		return $result;
	}
	
	public static function getIdPrerequisByRef($reference)  {
       
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
            (new DbQuery())
                ->select('`id_education_prerequis`')
                ->from('education_prerequis')
				->where('`name` LIKE \''.$reference.'\'')
        );
    }

}
