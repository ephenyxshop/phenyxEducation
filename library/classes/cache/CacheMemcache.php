<?php

/**
 * Class CacheMemcacheCore
 *
 * @since 1.0.0
 */
class CacheMemcacheCore extends Cache {

    /**
     * @var Memcache
     */
    protected $memcache;

    /**
     * @var bool Connection status
     */
    protected $is_connected = false;

    /**
     * CacheMemcacheCore constructor.
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function __construct() {

        $this->connect();
    }

    /**
     * CacheMemcacheCore destructor.
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function __destruct() {

        $this->close();
    }

    /**
     * Connect to memcache server
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function connect() {

        if (class_exists('Memcache') && extension_loaded('memcache')) {
            $this->memcache = new Memcache();
        } else {
            return;
        }

        $servers = static::getMemcachedServers();

        if (!$servers) {
            return;
        }

        foreach ($servers as $server) {
            $this->memcache->addServer($server['ip'], $server['port'], true, (int) $server['weight']);
        }

        $this->is_connected = true;
    }

    /**
     * @see Cache::_set()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function _set($key, $value, $ttl = 0) {

        if (!$this->is_connected) {
            return false;
        }

        return $this->memcache->set($key, $value, 0, $ttl);
    }

    /**
     * @see Cache::_get()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function _get($key) {

        if (!$this->is_connected) {
            return false;
        }

        return $this->memcache->get($key);
    }

    /**
     * @see Cache::_exists()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function _exists($key) {

        if (!$this->is_connected) {
            return false;
        }

        return ($this->memcache->get($key) !== false);
    }

    /**
     * @see Cache::_delete()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function _delete($key) {

        if (!$this->is_connected) {
            return false;
        }

        return $this->memcache->delete($key);
    }

    /**
     * @see Cache::_writeKeys()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function _writeKeys() {

        if (!$this->is_connected) {
            return false;
        }

        return true;
    }

    /**
     * @see Cache::flush()
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function flush() {

        if (!$this->is_connected) {
            return false;
        }

        return $this->memcache->flush();
    }

    /**
     * Store a data in cache
     *
     * @param string $key
     * @param mixed  $value
     * @param int    $ttl
     *
     * @return bool
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function set($key, $value, $ttl = 0) {

        return $this->_set($key, $value, $ttl);
    }

    /**
     * Retrieve a data from cache
     *
     * @param string $key
     *
     * @return mixed
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function get($key) {

        return $this->_get($key);
    }

    /**
     * Check if a data is cached
     *
     * @param string $key
     *
     * @return bool
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    public function exists($key) {

        return $this->_exists($key);
    }

    /**
     * Delete one or several data from cache (* joker can be used, but avoid it !)
     *    E.g.: delete('*'); delete('my_prefix_*'); delete('my_key_name');
     *
     * @param string $key
     *
     * @return bool
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public function delete($key) {

        if ($key == '*') {
            $this->flush();
        } else if (strpos($key, '*') === false) {
            $this->_delete($key);
        } else {
            // Get keys (this code comes from Doctrine 2 project)
            $pattern = str_replace('\\*', '.*', preg_quote($key));
            $servers = $this->getMemcachedServers();

            if (is_array($servers) && count($servers) > 0 && method_exists('Memcache', 'getStats')) {
                $allSlabs = $this->memcache->getStats('slabs');
            }

            if (isset($allSlabs) && is_array($allSlabs)) {

                foreach ($allSlabs as $server => $slabs) {

                    if (is_array($slabs)) {

                        foreach (array_keys($slabs) as $i => $slabId) // $slab_id is not an int but a string, using the key instead ?
                        {

                            if (is_int($i)) {
                                $dump = $this->memcache->getStats('cachedump', (int) $i);

                                if ($dump) {

                                    foreach ($dump as $entries) {

                                        if ($entries) {

                                            foreach ($entries as $key => $data) {

                                                if (preg_match('#^' . $pattern . '$#', $key)) {
                                                    $this->_delete($key);
                                                }

                                            }

                                        }

                                    }

                                }

                            }

                        }

                    }

                }

            }

        }

        return true;
    }

    /**
     * Close connection to memcache server
     *
     * @return bool
     *
     * @since 1.0.0
     * @version 1.0.0 Initial version
     */
    protected function close() {

        if (!$this->is_connected) {
            return false;
        }

        return $this->memcache->close();
    }

    /**
     * Add a memcache server
     *
     * @param string $ip
     * @param int    $port
     * @param int    $weight
     *
     * @return bool
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     * @throws PhenyxShopException
     */
    public static function addServer($ip, $port, $weight) {

        return Db::getInstance()->execute('INSERT INTO ' . _DB_PREFIX_ . 'memcached_servers (ip, port, weight) VALUES(\'' . pSQL($ip) . '\', ' . (int) $port . ', ' . (int) $weight . ')', false);
    }

    /**
     * Get list of memcached servers
     *
     * @return array
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since   1.0.0
     * @version 1.0.0 Initial version
     */
    public static function getMemcachedServers() {

        return Db::getInstance(_EPH_USE_SQL_SLAVE_)->executeS('SELECT * FROM ' . _DB_PREFIX_ . 'memcached_servers', true, false);
    }

    /**
     * Delete a memcache server
     *
     * @param int $idServer
     *
     * @return bool
     *
     * @since   1.0.0
     * @version 1.0.0 Initial version
     * @throws PhenyxShopException
     */
    public static function deleteServer($idServer) {

        return Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'memcached_servers WHERE id_memcached_server=' . (int) $idServer);
    }

}
