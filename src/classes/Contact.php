<?php

/**
 * Class ContactCore
 *
 * @since 1.9.1.0
 */
class ContactCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    public $id;
    /** @var string Name */
    public $name;
    /** @var string e-mail */
    public $email;
    /** @var string Detailed description */
    public $description;
    public $student_service;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'contact',
        'primary'   => 'id_contact',
        'multilang' => true,
        'fields'    => [
            'email'           => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 128],
            'student_service' => ['type' => self::TYPE_BOOL, 'validate' => 'isBool'],

            /* Lang fields */
            'name'            => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 32],
            'description'     => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml'],
        ],
    ];

    /**
     * Return available contacts
     *
     * @param int $idLang Language ID
     *
     * @return array Contacts
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public static function getContacts($idLang) {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('*')
                ->from('contact', 'c')
                ->join(Shop::addSqlAssociation('contact', 'c', false))
                ->leftJoin('contact_lang', 'cl', 'c.`id_contact` = cl.`id_contact` AND cl.`id_lang` = ' . (int) $idLang)
                ->where('contact_shop.`id_shop` IN (' . implode(', ', array_map('intval', Shop::getContextListShopID())) . ')')
                ->groupBy('c.`id_contact`')
                ->orderBy('`name` ASC')
        );
    }

    /**
     * Return available categories contacts
     *
     * @return array Contacts
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public static function getCategoriesContacts() {

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            (new DbQuery())
                ->select('cl.*')
                ->from('contact', 'ct')
                ->join(Shop::addSqlAssociation('contact', 'ct', false))
                ->leftJoin('contact_lang', 'cl', 'cl.`id_contact` = ct.`id_contact` AND cl.`id_lang` = ' . (int) Context::getContext()->language->id)
                ->where('ct.`student_service` = 1')
                ->where('contact_shop.`id_shop` IN (' . implode(', ', array_map('intval', Shop::getContextListShopID())) . ')')
                ->groupBy('ct.`id_contact`')
        );
    }
}
