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
 * Class FileLoggerCore
 *
 * @since 1.9.1.0
 */
class FileLoggerCore extends AbstractLogger
{
    protected $filename = '';

    /**
     * Check if the specified filename is writable and set the filename
     *
     * @param string $filename
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
    */
    public function setFilename($filename)
    {
        if (is_writable(dirname($filename))) {
            $this->filename = $filename;
        } else {
            $this->filename = '';
        }
    }

    /**
     * Log the message
     *
     * @return string
     *
     * @since    1.0.0
     * @version  1.0.0 Initial version
     */
    public function getFilename()
    {
        return $this->filename;
    }

    /**
     * Write the message in the log file
     *
     * @param string $message
     * @param int    $level
     *
     * @return bool True on success, false on failure.
     *
     * @since 1.9.1.0
     * @version 1.8.1.0 Initial version
     */
    protected function logMessage($message, $level)
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }

        $formattedMessage = '*'.$this->level_value[$level].'* '."\t".date('Y/m/d - H:i:s').': '.$message."\r\n";

        $result = false;
        $path = $this->getFilename();
        if ($path) {
            $result = (bool) file_put_contents($path, $formattedMessage, FILE_APPEND);
        }

        return $result;
    }
}
