<?php
use Defuse\Crypto\Crypto;
use \Curl\Curl;
/**
 * Class ActualiteCore
 *
 * @since 1.9.1.0
 */
class ActualiteCore extends ObjectModel {

    // @codingStandardsIgnoreStart
    /**
     * @see ObjectModel::$definition
     */
    public static $definition = [
        'table'     => 'actualite',
        'primary'   => 'id_actualite',
        'multilang' => true,
        'fields'    => [
            'actualite_date' => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'active'         => ['type' => self::TYPE_BOOL],
            'date_add'       => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],
            'date_upd'       => ['type' => self::TYPE_DATE, 'validate' => 'isDate'],

            /* Lang fields */
            'title'          => ['type' => self::TYPE_STRING, 'lang' => true, 'validate' => 'isGenericName', 'size' => 255],
            'content'        => ['type' => self::TYPE_HTML, 'lang' => true, 'validate' => 'isCleanHtml', 'size' => 3999999999999],
        ],
    ];
    /** @var string Name */
    public $actualite_date;
    public $active;
    public $date_add;
    public $date_upd;
    public $title;
    public $content;
	
	protected static $_cache_actualites = [];

    
	public static function getActualiteContent($idActualite, $idLang = null) {

        if (is_null($idLang)) {
            $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        }

        return Db::getInstance(_PS_USE_SQL_SLAVE_)->getRow(
            (new Db())
                ->select('`content`')
                ->from('actualite_lang')
                ->where('`id_actualite` = ' . (int) $idActualite)
                ->where('`id_lang` = ' . (int) $idLang)
        );
    }

    public function add($autoDate = true, $nullValues = false) {

        if (!parent::add($autoDate, true)) {
            return false;
        }

        return true;
    }

    public function update($nullValues = false) {

        if (PageCache::isEnabled()) {
            PageCache::invalidateEntity('actualite', $this->id);
        }

        if (parent::update($nullValues)) {
            return true;
        }

        return false;
    }

    public function delete() {

        if (PageCache::isEnabled()) {
            PageCache::invalidateEntity('actualite', $this->id);
        }

        if (parent::delete()) {
            return true;
        }

        return false;
    }

    public static function getActualites()  {
        
		$url = 'https://phenyx.education/veille';
		$string = Configuration::get('_EPHENYX_LICENSE_KEY_').'/'.Configuration::get('PS_SHOP_DOMAIN');
		$crypto_key = Tools::encrypt_decrypt('encrypt', $string, _PHP_ENCRYPTION_KEY_, _COOKIE_KEY_);
		
		$data_array = [
			'action' => 'getActualite',
			'crypto_key' => $crypto_key
		];

		$curl = new Curl();
		$curl->setDefaultJsonDecoder($assoc = true);
		$curl->setHeader('Content-Type', 'application/json');
		$curl->post($url, json_encode($data_array));
		$response = $curl->response;
		
		$response = Tools::jsonDecode(Tools::jsonEncode($response));
		return $response;
    }
	
	public static function getMasterActualite($idLang = null) {
		
		 if (is_null($idLang)) {
            $idLang = (int) Configuration::get('PS_LANG_DEFAULT');
        }
		
		if (!isset(static::$_cache_actualites[$idLang])) {
            static::$_cache_actualites = [];
		
			$result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS(
            	(new DbQuery())
            	->select('a.actualite_date, a.active, al.title, al.content')
            	->from('actualite', 'a')
				->leftJoin('actualite_lang', 'al', 'al.id_actualite = a.id_actualite AND al.id_lang = '.$idLang)
				->orderBy('a.actualite_date DESC')
        	);
			$_cache_actualites[$idLang][] = $result;
			
			return $result;
		}
		
		return $_cache_actualites[$idLang];
		
		
		
	}


}
