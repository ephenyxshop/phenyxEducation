<?php

class StudentEducationSynchCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int Order id */
    public $edof;
    /** @var int Order status id */
    public $altercampus;
    /** @var int Employee id for this history entry */
    public $speaking;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'   => 'student_education_synch',
        'primary' => 'id_student_education_synch',
        'fields'  => [
            'edof'        => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'altercampus' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'speaking'    => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];

    public static function isSessionSync() {

        $date = date('Y-m-d');
        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_student_education_synch`')
                ->from('student_education_synch')
                ->where('`edof` LIKE \'' . $date . '\' OR `altercampus` LIKE \'' . $date . '\' or `speaking` LIKE \'' . $date . '\'')
        );
    }

    public static function isEdofSync() {

        $date = date('Y-m-d');
        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_student_education_synch`')
                ->from('student_education_synch')
                ->where('`edof` LIKE \'' . $date . '\'')
        );
    }

    public static function isAlterSync() {

        $date = date('Y-m-d');
        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_student_education_synch`')
                ->from('student_education_synch')
                ->where('`altercampus` LIKE \'' . $date . '\'')
        );
    }

    public static function isSpeakingSync() {

        $date = date('Y-m-d');
        return Db::getInstance()->getValue(
            (new DbQuery())
                ->select('`id_student_education_synch`')
                ->from('student_education_synch')
                ->where('`speaking` LIKE \'' . $date . '\'')
        );
    }

}
