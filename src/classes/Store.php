<?php

/**
 * Class StoreCore
 *
 * @since 1.9.1.0
 */
class StoreCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /** @var int Country id */
    public $id_country;
    /** @var int State id */
    public $id_state;
    /** @var string Store name */
    public $name;
    /** @var string Address first line */
    public $address1;
    /** @var string Address second line (optional) */
    public $address2;
    /** @var string Postal code */
    public $postcode;
    /** @var string City */
    public $city;
    /** @var float Latitude */
    public $latitude;
    /** @var float Longitude */
    public $longitude;
    /** @var string Store hours (JSON encoded array) */
    public $hours;
    /** @var string Phone number */
    public $phone;
    /** @var string Fax number */
    public $fax;
    /** @var string Note */
    public $note;
    /** @var string e-mail */
    public $email;
    /** @var string Object creation date */
    public $date_add;
    /** @var string Object last modification date */
    public $date_upd;
    /** @var bool Store status */
    public $active = true;
    // @codingStandardsIgnoreEnd

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'store',
        'primary'   => 'id_store',
        'multilang' => true,
        'fields'    => [
            'id_country' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true],
            'id_state'   => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId'],
            'postcode'   => ['type' => self::TYPE_STRING, 'size' => 12],
            'city'       => ['type' => self::TYPE_STRING, 'validate' => 'isCityName', 'required' => true, 'size' => 64],
            'latitude'   => ['type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate', 'size' => 13],
            'longitude'  => ['type' => self::TYPE_FLOAT, 'validate' => 'isCoordinate', 'size' => 13],
            'phone'      => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 16],
            'fax'        => ['type' => self::TYPE_STRING, 'validate' => 'isPhoneNumber', 'size' => 16],
            'email'      => ['type' => self::TYPE_STRING, 'validate' => 'isEmail', 'size' => 255],
            'active'     => ['type' => self::TYPE_BOOL, 'validate' => 'isBool', 'required' => true],
            'date_add'   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'   => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

            /* Lang fields */
            'name'       => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'required' => true, 'size' => 255],
            'address1'   => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAddress', 'required' => true, 'size' => 255],
            'address2'   => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isAddress', 'size' => 255],
            'hours'      => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isJson', 'size' => 65000],
            'note'       => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 65000],
        ],
    ];

    protected $webserviceParameters = [
        'fields' => [
            'id_country' => ['xlink_resource' => 'countries'],
            'id_state'   => ['xlink_resource' => 'states'],
            'hours'      => ['getter' => 'getWsHours', 'setter' => 'setWsHours'],
        ],
    ];

    /**
     * StoreCore constructor.
     *
     * @param null $idStore
     * @param null $idLang
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function __construct($idStore = null, $idLang = null) {

        parent::__construct($idStore);
        $this->id_image = ($this->id && file_exists(_PS_STORE_IMG_DIR_ . (int) $this->id . '.jpg')) ? (int) $this->id : false;
        $this->image_dir = _PS_STORE_IMG_DIR_;
    }

    /**
     * @return string
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function getWsHours() {

        $hours = json_decode($this->hours, true);

        // Retrocompatibility for ephenyx <= 1.0.4.
        //
        // To get rid of this, introduce a data converter executed by the
        // upgrader over a couple of releases, making this obsolete.

        if (!$hours) {
            $hours = Tools::unSerialize($this->hours);
        }

        return implode(';', $hours);
    }

    /**
     * @param string $hours
     *
     * @return bool
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function setWsHours($hours) {

        $this->hours = json_encode(explode(';', $hours));

        return true;
    }

}
