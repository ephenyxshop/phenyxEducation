<?php

/**
 * @since 1.9.1.0
 */
class GodchildCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'godchild',
        'primary'   => 'id_godchild',
        'fields'    => [
            'firstname'             => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'lastname'              => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'required' => true, 'size' => 32],
			'email'                 => ['type' => self::TYPE_STRING, 'required' => true, 'size' => 128],
			'phone_mobile'          => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'required' => true, 'size' => 32],
			'id_education_type'     => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_student'         	=> ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
			'id_sponsor_partner'    => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId'],
			'validate'              => ['type' => self::TYPE_BOOL],
			'date_add'              => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
        ],
    ];
    public $firstname;
    public $lastname;
    public $email;
	public $phone_mobile;
	public $id_education_type;
	public $id_student;
	public $id_sponsor_partner;
	public $validate = 0;
	public $date_add;
	
	public $staut;
	
	public $date_format;

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
    public function __construct($id = null, $idLang = null) {

        parent::__construct($id, $idLang);
		if($this->id) {
			$date = new DateTime($this->date_add);
			$this->date_format = $date->format('d/m/Y');
			switch ($this->validate) {
  				case 0:
    				$this->staut = 'attente';
    				break;
  				case 1:
    				$this->staut = 'valide';
    				break;
				case 2:
    				$this->staut = 'refus';
    				break; 
			}
			
		}        
    }
	
	public static function getNbChildByIdStudent($idStudent, $idPartner) {
		
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('COUNT(*)')
				->from('godchild')
				->where('`id_student` = ' . $idStudent)
				->where('`id_sponsor_partner` = '.$idPartner)
			);			
	}
	
	public static function getNbChildValidateByIdStudent($idStudent, $idPartner) {
		
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('COUNT(*)')
				->from('godchild')
				->where('`id_student` = ' . $idStudent)
				->where('`id_sponsor_partner` = '.$idPartner)
				->where('`validate` = 1')
			);			
	}
	
	public static function getChildsByIdStudent($idStudent, $idPartner) {
		
		$return = [];
		$godsoons = Db::getInstance()->executeS(
			(new DbQuery())
			->select('*')
			->from('godchild')
			->where('`id_student` = ' . $idStudent)
			->where('`id_sponsor_partner` = '.$idPartner)
		);	
		
		if(is_array($godsoons) && count($godsoons)) {
			
			foreach($godsoons as $godsoon) {
			
				$return[] = new Godchild($godsoon['id_godchild']);
			}
		}
		
		return $return;		
		
	}

    
}
