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
 * @package    AT_Process
 * @license    http://code.google.com/p/php-at/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * AT_Process_Pool
 *
 * @category   AT
 * @package    AT_Process
 * @version    $Id$
 */
class AT_Process_Pool
{
    /**
     * Pool of running processes
     *
     * @var array
     */
    protected $_pool = array();

    /**
     * Process execution time
     *
     * @var integer
     */
    protected $_procExecTime;

    /**
     * Successful parses
     *
     * @var integer
     */
    protected $_successful = 0;

    /**
     * Failed parses
     *
     * @var integer
     */
    protected $_failed = 0;


    /**
     * @var array
     */
    protected $_haystack;

    /**
     * Constructor
     *
     * @param array $haystack
     * @param array $options
     */
    public function  __construct($haystack, $options)
    {
        $this->_haystack = $haystack;
        $this->_procExecTime = $options['procExecTime'];

        for ($i = 0; $i < $options['poolSize'] && !empty($this->_haystack); $i++) {
            $this->createNew();
        }
    }

    /**
     * Is pool empty
     *
     * @return bool
     */
    public function isEmpty()
    {
        return empty($this->_pool);
    }

    /**
     * Get success status count
     *
     * @return integer
     */
    public function getSuccessful()
    {
        return $this->_successful;
    }

    /**
     * Get failure status count
     *
     * @return integer
     */
    public function getFailed()
    {
        return $this->_failed;
    }

    /**
     * Check pools
     */
    public function check()
    {
        foreach ($this->_pool as $key => $process) {

            // Process is stoped
            if (!$process->getStatus('running')) {

                if ($process->isSuccessful()) {
                    AT_Log::log("Task #" . $process->getId() . " - Done");
                    $this->_successful++;
                } else {
                    AT_Log::log("Task #" . $process->getId() . " - Fail");
                    $this->_failed++;
                }
                $process->stop();

                // Replace process
                unset($this->_pool[$key]);

                if (false === $this->createNew()) {
                    $this->_failed++;
                }

            } else {
                // Process is frozen
                if ($process->getTime() > $this->_procExecTime) {
                    AT_Log::log('Task #' . $process->getId('id') . ' is frozen and would be terminated');

                    $process->terminate();

                    // replace process
                    if (false === $this->createNew()) {
                        $this->_failed++;
                    }
                }
            }
        }
    }

    /**
     * Create process inside pool
     *
     * @return bool|null
     */
    public function createNew()
    {
        $id = array_shift($this->_haystack);
        if (null === $id) {
            return null;
        }
        $process = new AT_Process($id);

        if ($process->run()) {
            AT_Log::log("Task #" . $process->getId() . ' - Started');

            $this->_pool[] = $process;
            return true;
        }
        AT_Log::log('Could not create process');

        return false;
    }
}