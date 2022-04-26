<?php

/**
 * Class EducationProgrammeCore
 *
 * @since 1.9.1.0
 */
class EducationProgrammeCore extends ObjectModel {

    
    public $reference;
    public $id_education;
    public $id_education_attribute;
    public $fileName;
    public $version;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'education_programme',
        'primary'   => 'id_education_programme',
        'fields'    => [
			'reference'               	=> ['type' => self::TYPE_STRING, 'validate' => 'isReference', 'size' => 32],
            'id_education'   			=> ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'id_education_attribute'   	=> ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
            'fileName'   				=> ['type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'size' => 128],
			'version'				    => ['type' => self::TYPE_STRING, 'size' => 128],
        ],
    ];

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function add($autoDate = false, $nullValues = false) {

        if (empty($this->reference)) {
            $this->reference = $this->generateReference();
        }

        return parent::add($autoDate, $nullValues);
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function update($nullValues = false) {

        $this->file_size = filesize(_PS_PROGRAM_DIR_ . $this->file);

        return parent::update($nullValues);
    }

    	
	public static function getFileName($idEducation, $idEducationAttribute = 0) {
		
		return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`fileName`')
                ->from('education_programme')
                ->where('`id_education` = ' . $idEducation)
                ->where('`id_education_attribute` = ' . $idEducationAttribute)
        );
	}

    
	public static function getProgrammeIdByIdEducation($idEducation) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('id_education_programme')
                    ->from('education_programme')
                    ->where('`id_education` = ' . (int) $idEducation)
            );
	}
	
	public static function getProgrammeIdByIdDeclinaison($idEducation, $idDeclinaison) {
		
		return Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue(
                (new DbQuery())
                    ->select('id_education_programme')
                    ->from('education_programme')
                    ->where('`id_education` = ' . (int) $idEducation)
					->where('`id_education_attribute` = ' . (int) $idDeclinaison)
            );
	}

}
