<?php

/**
 * Class Smarty_CacheResource_Mysql
 *
 * @since 1.9.1.0
 */
class Smarty_CacheResource_Mysql extends Smarty_CacheResource_Custom {

    /**
     * fetch cached content and its modification time from data source
     *
     * @param string $id        unique cache content identifier
     * @param string $name      template name
     * @param string $cacheId   cache id
     * @param string $compileId compile id
     * @param string $content   cached content
     * @param int    $mtime     cache modification timestamp (epoch)
     *
     * @return void
     *
     * @throws PhenyxShopDatabaseException
     * @throws PhenyxShopException
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function fetch($id, $name, $cacheId, $compileId, &$content, &$mtime) {

        $row = Db::getInstance()->getRow('SELECT modified, content FROM ' . _DB_PREFIX_ . 'smarty_cache WHERE id_smarty_cache = "' . pSQL($id, true) . '"');

        if ($row) {
            $content = $row['content'];
            $mtime = strtotime($row['modified']);
        } else {
            $content = null;
            $mtime = null;
        }

    }

    /**
     * Fetch cached content's modification timestamp from data source
     *
     * @note    implementing this method is optional. Only implement it if modification times can be accessed faster than loading the complete cached content.
     *
     * @param string $id        unique cache content identifier
     * @param string $name      template name
     * @param string $cacheId   cache id
     * @param string $compileId compile id
     *
     * @return int|boolean timestamp (epoch) the template was modified, or false if not found
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function fetchTimestamp($id, $name, $cacheId, $compileId) {

        $value = Db::getInstance()->getValue('SELECT modified FROM ' . _DB_PREFIX_ . 'smarty_cache WHERE id_smarty_cache = "' . pSQL($id, true) . '"');
        $mtime = strtotime($value);

        return $mtime;
    }

    /**
     * Save content to cache
     *
     * @param string   $id        unique cache content identifier
     * @param string   $name      template name
     * @param string   $cacheId   cache id
     * @param string   $compileId compile id
     * @param int|null $expTime   seconds till expiration time in seconds or null
     * @param string   $content   content to cache
     *
     * @return bool success
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     */
    protected function save($id, $name, $cacheId, $compileId, $expTime, $content) {

        Db::getInstance()->execute(
            '
        REPLACE INTO ' . _DB_PREFIX_ . 'smarty_cache (id_smarty_cache, name, cache_id, content)
        VALUES (
            "' . pSQL($id, true) . '",
            "' . pSQL(sha1($name)) . '",
            "' . pSQL($cacheId, true) . '",
            "' . pSQL($content, true) . '"
        )'
        );

        return (bool) Db::getInstance()->Affected_Rows();
    }

    /**
     * Delete content from cache
     *
     * @param string   $name      template name
     * @param string   $cacheId   cache id
     * @param string   $compileId compile id
     * @param int|null $expTime   seconds till expiration or null
     *
     * @return int number of deleted caches
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     * @throws PhenyxShopException
     * @throws PhenyxShopException
     */
    protected function delete($name, $cacheId, $compileId, $expTime) {

        // delete the whole cache

        if ($name === null && $cacheId === null && $compileId === null && $expTime === null) {
            // returning the number of deleted caches would require a second query to count them
            Db::getInstance()->execute('TRUNCATE TABLE ' . _DB_PREFIX_ . 'smarty_cache');

            return -1;
        }

        $where = [];

        if ($name !== null) {
            $where[] = 'name = "' . pSQL(sha1($name)) . '"';
        }

        if ($expTime !== null) {
            $where[] = 'modified < DATE_SUB(NOW(), INTERVAL ' . (int) $expTime . ' SECOND)';
        }

        if ($cacheId !== null) {
            $where[] = '(cache_id  = "' . pSQL($cacheId, true) . '" OR cache_id LIKE "' . pSQL($cacheId . '|%', true) . '")';
        }

        Db::getInstance()->execute('DELETE FROM ' . _DB_PREFIX_ . 'smarty_cache WHERE ' . implode(' AND ', $where));

        return Db::getInstance()->Affected_Rows();
    }

}
