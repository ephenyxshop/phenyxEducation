<?php

/**
 * Class TranslatedConfigurationCore
 *
 * @since 1.9.1.0
 */
class TranslatedConfigurationCore extends Configuration {

    public $value = [];

    public static $definition = [
        'table'     => 'configuration',
        'primary'   => 'id_configuration',
        'multilang' => true,
        'fields'    => [
            'name'          => ['type' => self::TYPE_STRING, 'validate' => 'isConfigName', 'required' => true, 'size' => 32],
            'id_shop_group' => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
            'id_shop'       => ['type' => self::TYPE_NOTHING, 'validate' => 'isUnsignedId'],
            'value'         => ['type' => self::TYPE_STRING, 'lang' => true],
            'date_add'      => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'      => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
        ],
    ];
    protected $webserviceParameters = [
        'objectNodeName'  => 'translated_configuration',
        'objectsNodeName' => 'translated_configurations',
        'fields'          => [
            'value'    => [],
            'date_add' => [],
            'date_upd' => [],
        ],
    ];

    /**
     * TranslatedConfigurationCore constructor.
     *
     * @param int|null $id
     * @param int|null $idLang
     *
     * @throws Adapter_Exception
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($id = null, $idLang = null) {

        $this->def = PhenyxObjectModel::getDefinition($this);
        // Check if the id configuration is set in the configuration_lang table.
        // Otherwise configuration is not set as translated configuration.

        if ($id !== null) {
            $idTranslated = Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS(
                (new DbQuery())
                    ->select(bqSQL(static::$definition['primary']))
                    ->from(bqSQL(static::$definition['table']) . '_lang')
                    ->where('`' . bqSQL(static::$definition['primary']) . '` = ' . (int) $id)
                    ->limit(1, 0)
            );

            if (empty($idTranslated)) {
                $id = null;
            }

        }

        parent::__construct($id, $idLang);
    }

    /**
     * @param bool $autoDate
     * @param bool $nullValues
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function add($autoDate = true, $nullValues = false) {

        return $this->update($nullValues);
    }

    /**
     * @param bool $nullValues
     *
     * @return bool
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     */
    public function update($nullValues = false) {

        $ishtml = false;

        foreach ($this->value as $i18NValue) {

            if (Validate::isCleanHtml($i18NValue)) {
                $ishtml = true;
                break;
            }

        }

        Configuration::updateValue($this->name, $this->value, $ishtml);

        $lastInsert = Db::getInstance(_EPH_USE_SQL_SLAVE_)->getRow(
            (new DbQuery())
                ->select('`id_configuration` AS `id`')
                ->from('configuration')
                ->where('`name` = \'' . pSQL($this->name) . '\'')
        );

        if ($lastInsert) {
            $this->id = $lastInsert['id'];
        }

        return true;
    }

    /**
     * @param string $sqlJoin
     * @param string $sqlFilter
     * @param string $sqlSort
     * @param string $sqlLimit
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getWebserviceObjectList($sqlJoin, $sqlFilter, $sqlSort, $sqlLimit) {

        $query = '
        SELECT DISTINCT main.`' . $this->def['primary'] . '` FROM `' . _DB_PREFIX_ . $this->def['table'] . '` main
        ' . $sqlJoin . '
        WHERE id_configuration IN
        (   SELECT id_configuration
            FROM ' . _DB_PREFIX_ . $this->def['table'] . '_lang
        ) ' . $sqlFilter . '
        ' . ($sqlSort != '' ? $sqlSort : '') . '
        ' . ($sqlLimit != '' ? $sqlLimit : '') . '
        ';

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS($query);
    }

}
