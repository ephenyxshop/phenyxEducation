<?php

/**
 * Class EducationSession
 *
 * @since 2.1.0.0
 */
class EducationSessionCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'   => 'education_session',
		'primary' => 'id_education_session',
		'fields'  => [
			'session_date'               => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'name'                       => ['type' => self::TYPE_STRING, 'required' => true],
			'publiPost'                  => ['type' => self::TYPE_BOOL],
			'educLaunch'                 => ['type' => self::TYPE_BOOL],
			'sessionOpen'                => ['type' => self::TYPE_BOOL],
			'sessionEnded'               => ['type' => self::TYPE_BOOL],
			'sessionClosed'              => ['type' => self::TYPE_BOOL],
			'id_alter'                   => ['type' => self::TYPE_INT],
			'is_invoiced'                => ['type' => self::TYPE_BOOL],
			'active'                     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'date_add'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
			'date_upd'                   => ['type' => self::TYPE_DATE, 'shop' => true, 'validate' => 'isDate'],
		],
	];

	public $session_date;
	public $name;
	public $publiPost;
	public $educLaunch;
	public $sessionOpen = 1;
	public $sessionClosed;
	public $sessionEnded;
	public $id_alter;
	public $is_invoiced;
	public $active = 1;
	public $date_add;
	public $date_upd;
	public $state;
	public $nbInscription;
	public $turnover;
	/**
	 * CustomerCore constructor.
	 *
	 * @param int|null $id
	 *
	 * @since 2.1.0.0
	 * @throws PhenyxException
	 */
	public function __construct($id = null) {

		parent::__construct($id);

		if ($this->id) {
			$this->nbInscription = StudentEducation::getNbAttendees($this->id);
			$this->turnover = StudentEducation::getSessionTurnover($this->id);
		}

	}

	

	public function add($autoDate = true, $nullValues = false) {

		if (!parent::add($autoDate, $nullValues)) {
			return false;
		}

		return true;
	}
	
	public function update($nullValues = false) {

		
		$session = new EducationSession($this->id);
		if($session->session_date != $this->session_date) {
			$Newdate = DateTime::createFromFormat('Y-m-d', $this->session_date);			
			$this->name = 'Session du ' . EducationSession::convertinFrench($Newdate->format("d F Y"));
		}
		

		return parent::update($nullValues);

	}

	public static function getEducationSession() {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('id_education_session, name')
				->from('education_session')
				->orderBy('`session_date` DESC')
		);
	}

	public static function getLastEducatinOpen() {

		return Db::getInstance()->getRow(
			(new DbQuery())
				->select('id_education_session, session_date')
				->from('education_session')
				->where('sessionOpen = 1')
				->orderBy('`id_education_session` DESC')
		);
	}

	public static function getLastEducatClosed() {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('id_education_session, session_date')
				->from('education_session')
				->where('sessionClosed = 1')
		);
	}

	public static function getClosedSessions() {

		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-7 days');
		$dateRefer = $date->format('Y-m-d');
		$closeSessions = [];
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
			->select('id_education_session')
			->from('education_session')
			->where('session_date <= \'' . $dateRefer . '\'')
			->orderBy('session_date')
		);

		foreach ($sessions as $session) {
			$closeSessions[] = new EducationSession($session['id_education_session']);
		}

		return $closeSessions;
	}

	public static function getCurrentSessions() {

		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-7 days');
		$dateRefer = $date->format('Y-m-d');
		$currentSession = [];
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
			->select('id_education_session')
			->from('education_session')
			->where('session_date >= \'' . $dateRefer . '\'')
			->orderBy('session_date')
		);

		foreach ($sessions as $session) {
			$currentSession[] = new EducationSession($session['id_education_session']);
		}

		return $currentSession;
	}

	public static function getFilledEducationSession() {

		$idSessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('DISTINCT(`id_education_session`)')
				->from('student_education')
				->where('`deleted` = 0')
				->orderBy('`date_start` DESC')
		);

		$return = [];

		foreach ($idSessions as $session) {
			$return[$session['id_education_session']] = Db::getInstance()->getValue(
				(new DbQuery())
					->select('name')
					->from('education_session')
					->where('id_education_session = ' . $session['id_education_session'])
			);
		}

		return $return;
	}
	
	public static function getDropDownEducationSession() {
		
		$today = date("Y-m-d");
		
		$return = [];
		
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_session`, name')
				->from('education_session')
				->where('`session_date` >= \'' . $today . '\'')
				->orderBy('`session_date` DESC')
		);
		
		foreach($sessions as $session) {
			$return[] = [
				'dateSession' => $session['name'],
				'id_education_session' => $session['id_education_session'],
			];
		}
		
		return $return;
	}
	
	public static function getDropDownDateStart() {
		
		$today = date("Y-m-d");
		
		$return = [];
		
		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_session`, session_date')
				->from('education_session')
				->where('`session_date` >= \'' . $today . '\'')
				->orderBy('`session_date` DESC')
		);
		
		foreach($sessions as $session) {
			$return[] = [
				'date_start' => $session['session_date'],
				'id_education_session' => $session['id_education_session'],
			];
		}
		
		return $return;
	}

	public static function getInvoicedEducationSession() {

		$today = date("Y-m-d");
		$date = new DateTime($today);
		$date->modify('-30 days');
		$invoiceDate = $date->format('Y-m-d');

		$sessions = Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_education_session`')
				->from('education_session')
				->where('`session_date` <= \'' . $invoiceDate . '\'')
				->orderBy('`session_date` DESC')
		);

		$return = [];

		foreach ($sessions as $session) {
			$return[$session['id_education_session']] = Db::getInstance()->getValue(
				(new DbQuery())
					->select('name')
					->from('education_session')
					->where('id_education_session = ' . $session['id_education_session'])
			);
		}

		return $return;
	}

	public static function getDatesToCreate() {

		
		$configDays = str_replace('\\', '', Configuration::get('PS_SESSION_DAY'));
	
		
		$sessionDays = unserialize($configDays);
		
		$dateToCreate = [];
		$date = Db::getInstance()->getValue(
			(new DbQuery())
				->select('session_date')
				->from('education_session')
				->orderBy('`session_date` DESC')
		);
		if(!empty($date)) {
			$today = date("Y-m-d");
		
			$sessions = Db::getInstance()->executeS(
				(new DbQuery())
				->select('session_date')
				->from('education_session')
				->where('`session_date`  <= \'' . $date . '\'')
				->where('`session_date`  > \'' . $today . '\'')
				->orderBy('`session_date` ASC')
			);
			$datetime1 = '';
			$datetime2 = '';
			$date1;
			foreach($sessions as $key => $session) {
	
				if($key > 0) {
					$datetime1 = date_create($date1);
					$datetime2 = date_create($session['session_date']);
					$interval = date_diff($datetime1, $datetime2);
					$days = $interval->format('%d');		
					if($days > 7) {
						$inter = (int)$days/7;
						for($i= 1; $i < $inter; $i++) {
							$newDate = new DateTime($date1);
							$newDate->modify('+7 days');
							$missing = $newDate->format('Y-m-d');
							$holidays = EducationSession::getHolidays($newDate->format("Y"));
							$day = mktime(0, 0, 0, $newDate->format("m"), $newDate->format("d"), $newDate->format("Y"));

							if (in_array($day, $holidays)) {
								continue;
							}
							
							$date1 = $missing;
							$session = 'Session du ' . EducationSession::convertinFrench($newDate->format("d F Y"));
							$dateToCreate[] = [

								'session_date' => date_format($newDate, 'Y-m-d'),
								'name'         => $session,

							];
						}
						$date1 = $missing;
					} else {
						
						$date1 = $session['session_date'];
					}
		
				} else {
					$date1 = $session['session_date'];
					
				}
			}
			$datenow = date("Y-m-d");
			$phpdate = strtotime($date);
			$mysqldate = date('Y-m-d', $phpdate);
			$origin = date_create($datenow);
			$target = date_create($mysqldate);
			$interval = date_diff($origin, $target);
			$interval = $interval->format('%R%a');

			if ($interval < 90) {
				$delta = (int) ((90 - $interval));
				

				for ($i = 1; $i <= $delta; $i++) {
					
					$Newdate = DateTime::createFromFormat('Y-m-d', $date);
					$Newdate->modify('+' . $i . ' day');
					if(in_array($Newdate->format('w'), $sessionDays)) {
						$holidays = EducationSession::getHolidays($Newdate->format("Y"));
						$day = mktime(0, 0, 0, $Newdate->format("m"), $Newdate->format("d"), $Newdate->format("Y"));

						if (in_array($day, $holidays)) {
							continue;
						}

						$session = 'Session du ' . EducationSession::convertinFrench($Newdate->format("d F Y"));
						$dateToCreate[] = [

							'session_date' => date_format($Newdate, 'Y-m-d'),
							'name'         => $session,

						];
					}
				}

			}
			
			
		}
		
		return $dateToCreate;
			
		
	}

	public static function convertinFrench($date) {

		return str_replace(
			['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
			['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Aout', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
			$date
		);
	}

	public static function getHolidays($year = null) {

		if ($year === null) {
			$year = intval(strftime('%Y'));
		}

		$easterDate = easter_date($year);
		$easterDay = date('j', $easterDate);
		$easterMonth = date('n', $easterDate);
		$easterYear = date('Y', $easterDate);

		$holidays = [
			// Jours feries fixes
			mktime(0, 0, 0, 1, 1, $year), // 1er janvier
			mktime(0, 0, 0, 5, 1, $year), // Fete du travail
			mktime(0, 0, 0, 5, 8, $year), // Victoire des allies
			mktime(0, 0, 0, 7, 14, $year), // Fete nationale
			mktime(0, 0, 0, 8, 15, $year), // Assomption
			mktime(0, 0, 0, 11, 1, $year), // Toussaint
			mktime(0, 0, 0, 11, 11, $year), // Armistice
			mktime(0, 0, 0, 12, 25, $year), // Noel

			// Jour feries qui dependent de paques
			mktime(0, 0, 0, $easterMonth, $easterDay + 1, $easterYear), // Lundi de paques
			mktime(0, 0, 0, $easterMonth, $easterDay + 39, $easterYear), // Ascension
			mktime(0, 0, 0, $easterMonth, $easterDay + 50, $easterYear), // Pentecote
		];

		sort($holidays);

		return $holidays;
	}

	public static function generateSessionDate() {

		
		$dateToCreates = EducationSession::getDatesToCreate();
		
				
		if (is_array($dateToCreates) && count($dateToCreates)) {

			foreach ($dateToCreates as $dateToCreate) {
				$session = new EducationSession();
				$session->session_date = $dateToCreate['session_date'];
				$session->name = $dateToCreate['name'];
				$session->add();

			}

		}

	}

	public static function getNextEducationSlot() {

		$date = new DateTime(date('Y-m-d'));
		$date->modify('+15 days');
		$date = $date->format('Y-m-d');
		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_session')
				->where('`session_date` >= \'' . $date . '\'')
				->where('`sessionClosed` = 0')
				->where('`active` =1')
				->orderBy('`session_date` ASC')
		);

	}
	
	public static function getForwardedEducations($idSession) {
		
		
		$session = new EducationSession($idSession);
		
		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('*')
				->from('education_session')
				->where('`session_date` > \'' . $session->session_date . '\'')
				->where('`active` = 1')
				->orderBy('`session_date` ASC')
		);
	}
	
	public static function getIdSesseddioinbyDate($dateStart) {
		
		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('id_education_session')
				->from('education_session')
				->where('`session_date` = \'' . $dateStart . '\'')
		);
		
	}

}
