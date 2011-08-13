<?php
/**
 * Asynchronous Tasks
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://code.google.com/p/php-at/wiki/License
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to maks.slesarenko@gmail.com so we can send you a copy immediately.
 *
 * @category   AT
 * @package    AT_Log
 * @license    http://code.google.com/p/php-at/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * AT_Log
 *
 * @category   AT
 * @package    AT_Log
 * @version    $Id$
 */
class AT_Log
{
    /**
     * @var resource
     */
    protected static $_filename;

    /**
     * Set log filename
     *
     * @param string $filename
     */
    public static function setFilename($filename)
    {
        self::$_filename = $filename;
    }

    /**
     * Reset log file
     *
     */
    public static function reset()
    {
        if (self::isEnabled()) {
            if (file_exists(self::$_filename)) {
                unlink(self::$_filename);
            }
            touch(self::$_filename);
            chmod(self::$_filename, 0777);
        }
    }

    /**
     * Is log filename set
     *
     * @return boolen
     */
    public static function isEnabled()
    {
        return null != self::$_filename;
    }

    /**
     * Log text message
     *
     * @param string $message
     */
    public static function log($message, $formated = true)
    {
        if (!self::isEnabled()) {
            return;
        }
        if ($formated) {
            $message = date('m/d H:i:s') . ' ' . $message;
        }

        file_put_contents(self::$_filename, $message . PHP_EOL, FILE_APPEND);
    }

    /**
     * Get logs
     *
     * @param integer $offset
     * @return string
     */
    public static function getLogs($offset = 0)
    {
        if (!self::isEnabled()) {
            return;
        }
        return file_get_contents(self::$_filename, false, null, $offset);
    }
}