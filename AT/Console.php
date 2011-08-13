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
 * @package    AT_Console
 * @license    http://code.google.com/p/php-at/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * AT_Console
 *
 * @category   AT
 * @package    AT_Console
 * @version    $Id$
 */
class AT_Console
{
    protected $_actions = array();

    protected $_options = array();

    protected $_progname;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->_progname = $_SERVER["argv"]['0'];

        unset($_SERVER["argv"]['0']);
    }

    /**
     * Parse input
     *
     * @return DbSync_Console
     */
    public function parse()
    {
        $searchActions =  true;

        foreach ($_SERVER["argv"] as $arg) {
            if (strpos($arg, '-') === 0) {
                $searchActions = false;
                $this->_options[trim($arg, '-')] = null;
            } elseif ($searchActions) {
                $this->_actions[] = $arg;
            } else {
                if (count($this->_options)) {
                    end($this->_options);

                    $optionName = key($this->_options);
                    if (!empty($this->_options[ $optionName ])) {
                        $this->_options[ $optionName ] = (array) $this->_options[ $optionName ];
                        $this->_options[ $optionName ][] = $arg;
                    } else {
                        $this->_options[ $optionName ] = $arg;
                    }
                }
            }
        }
        return $this;
    }

    /**
     * Get all actions
     *
     * @return array
     */
    public function getActions()
    {
        return $this->_actions;
    }

    /**
     * Get action
     *
     * @param integer $i
     * @param mixed $default
     */
    public function getAction($i = 0, $default = false)
    {
        if (isset($this->_actions[$i])) {
            return $this->_actions[$i];
        }
        return $default;
    }

    /**
     * Has action
     *
     * @param string $actionName
     * @return boolean
     */
    public function hasAction($actionName)
    {
        return in_array($actionName, $this->_actions);
    }

    /**
     * Get options
     *
     * @return array
     */
    public function getOptions()
    {
        return $this->_options;
    }

    /**
     * Get options
     *
     * @param string $name
     * @return mixed
     */
    public function getOption($name, $default = false)
    {
        if ($this->hasOption($name)) {
            return $this->_options[$name];
        }
        return false;
    }

    /**
     * Has option
     *
     * @param string $name
     * @return boolen
     */
    public function hasOption($name)
    {
        return array_key_exists($name, $this->_options);
    }

    /**
     * Get progname
     *
     * @return string
     */
    public function getProgname()
    {
        return $this->_progname;
    }
}