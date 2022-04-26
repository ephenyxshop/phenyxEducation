<?php

/**
 * @since 1.9.1.0
 */
class CronJobsCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'cronjobs',
        'primary'   => 'id_cronjobs',
        'fields'    => [
			'id_license'        => ['type' => self::TYPE_INT,  'validate' => 'isUnsignedId'],
			'description' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'task' => ['type' => self::TYPE_STRING, 'validate' => 'isString'],
			'hour' => ['type' => self::TYPE_INT],
			'day' => ['type' => self::TYPE_INT],
			'month' => ['type' => self::TYPE_INT],
			'day_of_week' => ['type' => self::TYPE_INT],
			'updated_at' => ['type' => self::TYPE_DATE, 'validate' => 'isDate', 'copy_post' => false],
			'active'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

            /* Lang fields */
            
        ],
    ];
    public $id_license;
    // @codingStandardsIgnoreEnd
    public $description;
	public $task;
	public $hour;
	public $day;
	public $month;
	public $day_of_week;
	public $updated_at;
	public $active;
	
	public $licence;
	
	public $partner;

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
			$this->licence = new License($this->id_license);
			$this->partner = new PartnerCompany($this->licence->id_partner_company);
			
		}
    }
	
	public static function runTasksCrons()
    {
        
		$date = new DateTime("now", new DateTimeZone('America/New_York') );
		$query = 'SELECT * FROM '._DB_PREFIX_.'cronjobs WHERE `active` = 1';
        $crons = Db::getInstance()->executeS($query);

        if (is_array($crons) && (count($crons) > 0)) {
            foreach ($crons as &$cron) {
                if (CronJobs::shouldBeExecuted($cron) == true) {
					$license = new License($cron['id_license']);
					$result = $license->executeCronAction($cron['task']);
                    $query = 'UPDATE '._DB_PREFIX_.'cronjobs SET `updated_at` = NOW() WHERE `id_cronjobs` = \''.(int)$cron['id_cronjobs'].'\'';
                    Db::getInstance()->execute($query);
                }
            }
        }
		
		
    }
	
	public static function shouldBeExecuted($cron)
    {
        $hour = ($cron['hour'] == -1) ? date('H') : $cron['hour'];
        $day = ($cron['day'] == -1) ? date('d') : $cron['day'];
        $month = ($cron['month'] == -1) ? date('m') : $cron['month'];
        $day_of_week = ($cron['day_of_week'] == -1) ? date('D') : date('D', strtotime('Sunday +' . $cron['day_of_week'] . ' days'));

        $day = date('Y').'-'.str_pad($month, 2, '0', STR_PAD_LEFT).'-'.str_pad($day, 2, '0', STR_PAD_LEFT);
        $execution = $day_of_week.' '.$day.' '.str_pad($hour, 2, '0', STR_PAD_LEFT);
        $now = date('D Y-m-d H');

        return !(bool)strcmp($now, $execution);
    }

   

}
