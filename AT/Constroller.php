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
 * @package    AT_Controller
 * @license    http://code.google.com/p/php-at/wiki/License   New BSD License
 * @version    $Id$
 */

/**
 * AT_Console
 *
 * @category   AT
 * @package    AT_Controller
 * @version    $Id$
 */
class AT_Controller
{
    /**
     * @var array
     */
    protected $_config = array(
        'checkInterval' => 1, //1s
        'logFile' => 'at.log', //log file
        'lockFile' => 'at.lock', //lock file
        'poolSize' => 3, //child processes amount
        'procExecTime' => 120, //2m
    );

    /**
     * @var array
     */
    protected $_tasks;

    /**
     * Constructor
     *
     * @param array $config
     * @param array $schedules
     */
    public function __construct(array $tasks)
    {
        $this->_tasks = $tasks;
    }

    /**
     * Set config
     *
     * @param array $config
     */
    public function setConfig(array $config)
    {
        $this->_config = array_merge($this->_config, $config);
        return $this;
    }

    /**
     * Dispatch
     *
     * @param AT_Console $console
     */
    public function dispatch(AT_Console $console)
    {
        AT_Log::setFilename($this->_config['logFile']);

        $doLock = true;

        $id = $console->getOption('id');
        $isChild = $console->hasOption('child');

        if ($isChild) {
            if (null === $id) {
                throw new Exception('Task ID not pecified');
            }
            $doLock = false;
        }

        if ($doLock) {
            $this->lock();
            AT_Log::reset();
        }

        if ($id) {
            $this->processAction($id, $isChild);
        } else {
            $this->poolAction();
        }

        if ($doLock) {
            $this->unlock();
        }
    }

    /**
     * Start pool action
     *
     *
     */
    public function poolAction()
    {
        AT_Log::log("Start pool");
        $pool = new AT_Process_Pool(array_keys($this->_tasks), $this->_config);

        $usleep = (int) $this->_config['checkInterval'];

        $offset = 0;
        $eol = '';

        // wait for pool
        while (!$pool->isEmpty()) {

            if ($logs = AT_Log::getLogs($offset)) {
                $offset += strlen($logs);
                echo $eol . $logs;
                $eol = '';
            } else {
                echo '.';
                $eol = PHP_EOL;
            }

            sleep($usleep);

            $pool->check();
        }

        $success = $pool->getSuccessful();
        $fail = $pool->getFailed();
        AT_Log::log("Pool is done. Successful: {$success}. Failed: {$fail}.");

        echo AT_Log::getLogs($offset);
    }

    /**
     * Start process action
     *
     * @param integer $id
     * @param boolen  $isChild
     * @throws Exception
     */
    public function processAction($id, $isChild = false)
    {
        if (!isset($this->_tasks[$id])) {
            throw new Exception("Task with ID '{$id}' not found");
        }

        ob_start();

        call_user_func($this->_tasks[$id], $id);

        if ($output = ob_get_clean()) {
            $output = PHP_EOL . 'Task #' . $id . ' output:' . PHP_EOL . $output . PHP_EOL;
        }
        AT_Log::log($output, false);

        if ($isChild) {
            echo AT_Process::SUCCESS_RESULT;
        } else {
            echo AT_Log::getLogs();
        }
    }

    /**
     * Lock the process file
     *
     * @throws Exception
     */
    public function lock()
    {
        if ($this->isLocked()) {
            throw new Exception('Process is locked');
        }

        $lock = $this->_config['lockFile'];
        if (!realpath($lock)) {
            touch($lock);
            chmod($lock, 0777);
        }
        $fp = fopen($lock, 'w');
        flock($fp, LOCK_EX);
    }

    /**
     * Unlock process
     *
     * @return boolen
     */
    public function unlock()
    {
        $fp = fopen($this->_config['lockFile'], 'w');
        return flock($fp, LOCK_UN);
    }

    /**
     * Is Process locked
     *
     * @return boolean
     */
    public function isLocked()
    {
        $fp = fopen($this->_config['lockFile'], 'w');

        if (flock($fp, LOCK_EX | LOCK_NB)) {
            flock($fp, LOCK_UN);
            return false;
        }
        return true;
    }

}