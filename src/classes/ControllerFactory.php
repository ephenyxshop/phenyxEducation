<?php
/**
 * 2007-2016 PhenyxShop
 *
 * ephenyx is an extension to the PhenyxShop e-commerce software developed by PhenyxShop SA
 * Copyright (C) 2017-2018 ephenyx
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@ephenyx.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PhenyxShop to newer
 * versions in the future. If you wish to customize PhenyxShop for your
 * needs please refer to https://www.ephenyx.com for more information.
 *
 *  @author    ephenyx <contact@ephenyx.com>
 *  @author    PhenyxShop SA <contact@PhenyxShop.com>
 *  @copyright 2017-2020 ephenyx
 *  @copyright 2007-2016 PhenyxShop SA
 *  @license   http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 *  PhenyxShop is an internationally registered trademark & property of PhenyxShop SA
 */

/**
 * Class ControllerFactoryCore
 *
 * @deprecated 1.0.0
 */
class ControllerFactoryCore
{
    /**
     * @deprecated since 1.0.0
     */
    public static function includeController($className)
    {
        Tools::displayAsDeprecated();

        if (!class_exists($className, false)) {
            require_once(_PS_CORE_DIR_.'/controllers/'.$className.'.php');
            if (file_exists(_PS_ROOT_DIR_.'/override/controllers/'.$className.'.php')) {
                require_once(_PS_ROOT_DIR_.'/override/controllers/'.$className.'.php');
            } else {
                $coreClass = new ReflectionClass($className.'Core');
                if ($coreClass->isAbstract()) {
                    eval('abstract class '.$className.' extends '.$className.'Core {}');
                } else {
                    eval('class '.$className.' extends '.$className.'Core {}');
                }
            }
        }
    }

    /**
     * @deprecated 1.0.0
     */
    public static function getController($className, $auth = false, $ssl = false)
    {
        static::includeController($className);

        return new $className($auth, $ssl);
    }
}
