<?php

/**
 * Class StudentEducationStepCore
 *
 * @since 2.1.0.0
 */
class StudentEducationStepCore extends ObjectModel {

	// @codingStandardsIgnoreStart
	/**
	 * @see ObjectModel::$definition
	 */
	public static $definition = [
		'table'     => 'student_education_state',
		'primary'   => 'id_student_education_state',
		'multilang' => true,
		'fields'    => [
			'color'              => ['type' => self::TYPE_STRING, 'validate' => 'isColor'],
			'send_email'         => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'send_sms'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'send_agent_sms'     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'send_agent_email'   => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'pdf_mail'           => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'is_suivie'          => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],
			'invoice'            => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

			'name'               => ['type' => self::TYPE_STRING, 'validate' => 'isName', 'lang' => true, 'required' => true],

			'template'           => ['type' => self::TYPE_STRING, 'lang' => true],
			'sms_template'       => ['type' => self::TYPE_STRING, 'lang' => true],
			'description'        => ['type' => self::TYPE_STRING, 'lang' => true],
			'suivie'             => ['type' => self::TYPE_STRING, 'lang' => true],
			'agent_template'     => ['type' => self::TYPE_STRING, 'lang' => true],
			'agent_sms_template' => ['type' => self::TYPE_STRING, 'lang' => true],
			'agent_description'  => ['type' => self::TYPE_STRING, 'lang' => true],

		],
	];

	public $color;
	public $send_email;
	public $send_sms;
	public $send_agent_sms;
	public $send_agent_email;
	public $pdf_mail;
	public $is_suivie;
	public $invoice;
	public $name;
	public $template;
	public $sms_template;
	public $description;
	public $suivie;
	public $agent_template;
	public $agent_sms_template;
	public $agent_description;

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

	public static function getEducationStep() {

		$context = Context::getContext();
		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('`id_student_education_state`, `name`')
				->from('student_education_state_lang')
				->where('`id_lang` = ' . $context->language->id)
				->orderBy('`id_student_education_state` ASC ')
		);
	}

	public static function getTemplate($idStep, $idLang) {

		$template = '';
		$sms_template = '';
		$send_mail = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`send_email`')
				->from('student_education_state')
				->where('`id_student_education_state` = ' . $idStep)
		);

		if ($send_mail = 1) {
			$template = Db::getInstance()->getValue(
				(new DbQuery())
					->select('`template`')
					->from('student_education_state_lang')
					->where('`id_lang` = ' . $idLang)
					->where('`id_student_education_state` = ' . $idStep)
			);
		}

		$send_sms = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`send_sms`')
				->from('student_education_state')
				->where('`id_student_education_state` = ' . $idStep)
		);

		if ($send_sms = 1) {
			$sms_template = Db::getInstance()->getValue(
				(new DbQuery())
					->select('`sms_template`')
					->from('student_education_state_lang')
					->where('`id_lang` = ' . $idLang)
					->where('`id_student_education_state` = ' . $idStep)
			);
		}

		return [
			'template'     => $template,
			'sms_template' => $sms_template,
		];

	}

	public static function getAgentTemplate($idStep, $idLang) {

		$template = '';
		$sms_template = '';
		$send_mail = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`send_agent_email`')
				->from('student_education_state')
				->where('`id_student_education_state` = ' . $idStep)
		);

		if ($send_mail = 1) {
			$template = Db::getInstance()->getValue(
				(new DbQuery())
					->select('`agent_template`')
					->from('student_education_state_lang')
					->where('`id_lang` = ' . $idLang)
					->where('`id_student_education_state` = ' . $idStep)
			);
		}

		$send_sms = Db::getInstance()->getValue(
			(new DbQuery())
				->select('`send_agent_sms`')
				->from('student_education_state')
				->where('`id_student_education_state` = ' . $idStep)
		);

		if ($send_sms = 1) {
			$sms_template = Db::getInstance()->getValue(
				(new DbQuery())
					->select('`agent_sms_template`')
					->from('student_education_state_lang')
					->where('`id_lang` = ' . $idLang)
					->where('`id_student_education_state` = ' . $idStep)
			);
		}

		return [
			'template'     => $template,
			'sms_template' => $sms_template,
		];

	}

	public static function getTopic($idStep, $idLang) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`description`')
				->from('student_education_state_lang')
				->where('`id_lang` = ' . $idLang)
				->where('`id_student_education_state` = ' . $idStep)
		);
	}

	public static function getAgentTopic($idStep, $idLang) {

		return Db::getInstance()->getValue(
			(new DbQuery())
				->select('`agent_description`')
				->from('student_education_state_lang')
				->where('`id_lang` = ' . $idLang)
				->where('`id_student_education_state` = ' . $idStep)
		);
	}

	public static function getEducationStepHistory($student_education, $idLang) {

		return Db::getInstance()->executeS(
			(new DbQuery())
				->select('h.*, es.*, esl.name as statename, e.lastname, e.firstname')
				->from('student_education_history', 'h')
				->leftJoin('student_education_state', 'es', 'es.id_student_education_state = h.id_student_education_state')
				->leftJoin('student_education_state_lang', 'esl', 'esl.id_student_education_state = h.id_student_education_state AND esl.id_lang = ' . $idLang)
				->leftJoin('employee', 'e', 'e.id_employee = h.id_employee')
				->where('h.`id_student_education` = ' . $student_education)
				->orderBy('h.`date_add` DESC ')
		);
	}

}
