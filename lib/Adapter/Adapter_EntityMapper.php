<?php
/*
* 2018-2020 Ephenyx Digital LTD
*
* NOTICE OF LICENSE
*
* This source file is subject to the Open Software License (OSL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/osl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PhenyxShop to newer
* versions in the future. If you wish to customize PhenyxShop for your
* needs please refer to http://ephenyx.com for more information.
*
*  @author Ephenyx Digital LTD <contact@ephenyx.com>
*  @copyright  2018-2020 Pphenyx Digital LTD
*  @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
*  International Registered Trademark & Property of phenyx Digital LTD
*/

/**
 * Class Adapter_EntityMapper
 *
 * @since 1.9.1.0
 */
// @codingStandardsIgnoreStart
class Adapter_EntityMapper
{
    // @codingStandardsIgnoreEnd

    /**
     * Load ObjectModel
     *
     * @param int    $id
     * @param int    $idLang
     * @param object $entity
     * @param mixed  $entityDefs
     * @param int    $idShop
     * @param bool   $shouldCacheObjects
     *
     * @throws PhenyxShopDatabaseException
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    public function load($id, $idLang, $entity, $entityDefs, $idShop, $shouldCacheObjects)
    {
        // Load object from database if object id is present
        $cacheId = 'objectmodel_'.$entityDefs['classname'].'_'.(int) $id.'_'.(int) $idShop.'_'.(int) $idLang;

        if (!$shouldCacheObjects || !Cache::isStored($cacheId)) {
            $sql = new DbQuery();
            $sql->from($entityDefs['table'], 'a');
            $sql->where('a.`'.bqSQL($entityDefs['primary']).'` = '.(int) $id);

            // Get lang informations
            if ($idLang && isset($entityDefs['multilang']) && $entityDefs['multilang']) {
                $sql->leftJoin($entityDefs['table'].'_lang', 'b', 'a.`'.bqSQL($entityDefs['primary']).'` = b.`'.bqSQL($entityDefs['primary']).'` AND b.`id_lang` = '.(int) $idLang);
                if ($idShop && !empty($entityDefs['multilang_shop'])) {
                    $sql->where('b.`id_shop` = '.(int) $idShop);
                }
            }

            // Get shop informations
            

            if ($objectData = Db::getInstance()->getRow($sql)) {
                if (!$idLang && isset($entityDefs['multilang']) && $entityDefs['multilang']) {
                    $sql = (new DbQuery())
                        ->select('*')
                        ->from($entityDefs['table'].'_lang')
                        ->where('`'.$entityDefs['primary'].'` = '.(int) $id)
                        ->where(($idShop && $entity->isLangMultishop()) ? '`id_shop` = '.(int) $idShop : null);

                    if ($objectDatasLang = Db::getInstance()->executeS($sql)) {
                        foreach ($objectDatasLang as $row) {
                            foreach ($row as $key => $value) {
                                if ($key !== $entityDefs['primary']
                                    && $key !== 'id_lang' && $key !== 'id_shop'
                                    && property_exists($entity, $key)) {
                                    if (!isset($objectData[$key]) || !is_array($objectData[$key])) {
                                        $objectData[$key] = [];
                                    }

                                    $objectData[$key][$row['id_lang']] = $value;
                                }
                            }
                        }
                    }
                }
                $entity->id = (int) $id;
                foreach ($objectData as $key => $value) {
                    if (property_exists($entity, $key)) {
                        $entity->{$key} = $value;
                    } else {
                        unset($objectData[$key]);
                    }
                }
                if ($shouldCacheObjects) {
                    Cache::store($cacheId, $objectData);
                }
            }
        } else {
            $objectData = Cache::retrieve($cacheId);
            if ($objectData) {
                $entity->id = (int) $id;
                foreach ($objectData as $key => $value) {
                    $entity->{$key} = $value;
                }
            }
        }
    }
}
