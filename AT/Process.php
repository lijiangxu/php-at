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
 * AT_Process
 *
 * @category   AT
 * @package    AT_Process
 * @version    $Id$
 */
class AT_Process
{
    const SUCCESS_CODE = 0;
    const SUCCESS_RESULT = '1';

    /**
     * @var string
     */
    protected $_cmd = './run --child true';

    /**
     * @var array
     */
    protected $_descriptors = array(
        1 => array("pipe", "w"),
        2 => array("pipe", "w")
    );

    /**
     * @var array
     */
    protected $_pipes = array();

    /**
     * @var integer
     */
    protected $_id;

    /**
     * @var resource
     */
    protected $_handler;

    /**
     * @var array
     */
    protected $_time = array('started' => null, 'stoped' => null);

    /**
     * @var string
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_status;

    /**
     * Constructor
     *
     * @param integer $id
     */
    public function __construct($id)
    {
        $this->_id = $id;
    }

    /**
     * Destructor
     *
     */
    public function __destruct()
    {
        $this->stop();
    }

    /**
     * Run
     *
     * @return boolen
     * @throws Exception
     */
    public function run()
    {
        if ($this->isRunning()) {
            throw new Exception('Process already is running');
        }
        $this->_handler = proc_open(
            $this->_cmd . " --id " . $this->getId(),
            $this->_descriptors,
            $this->_pipes
        );
        $this->_time['started'] = time();

        return $this->isRunning();
    }

    /**
     * Is running
     *
     * @return boolen
     */
    public function isRunning()
    {
        return is_resource($this->_handler);
    }

    /**
     * Get process status
     *
     * @param string $optionName
     * @return mixed
     */
    public function getStatus($optionName = null)
    {
        $status = $this->_status;
        if (!$status) {
            if ($this->isRunning()) {
                $status = proc_get_status($this->_handler);
                if (false === $status['running']) {
                    $this->_status = $status;
                    $this->_time['stoped'] = time();
                }
            }
        }

        if ($optionName) {
            return $status[$optionName];
        }
        return $status;
    }

    /**
     * Get response
     *
     * @return string
     */
    public function getResponse()
    {
        if ($this->isRunning()) {
            $this->_response = fgets($this->_pipes[1]);
        }
        return $this->_response;
    }

    /**
     * Is response successful
     *
     * @return boolen
     */
    public function isSuccessful()
    {
        if (!$this->isRunning()) {
            throw new Exception('Process is not running');
        }
        return ($this->getStatus('exitcode') === self::SUCCESS_CODE
            && self::SUCCESS_RESULT === $this->getResponse());
    }

    /**
     * Get process ID
     *
     * @return integer
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * Get process running time
     *
     * @return integer
     */
    public function getTime()
    {
        if ($this->_time['started']) {
            $to = $this->_time['stoped'];
            if (!$this->getStatus('running')) {
                $to = time();
            }
            return $to - $this->_time['stoped'];
        }
        return 0;
    }

    /**
     * Terminate process
     *
     * @param integer $sig
     * @return boolen
     */
    public function terminame($sig = 15)
    {
        if (!$this->isRunning() || !$this->getStatus('ruunning')) {
            throw new Exception('Process is not running');
        }
        return proc_terminate($this->_handler, (int) $sig);
    }

    /**
     * Stop process
     *
     */
    public function stop()
    {
        if ($this->isRunning()) {
            fclose($this->_pipes[1]);
            fclose($this->_pipes[2]);
            proc_close($this->_handler);
            $this->_time = array('started' => null, 'stoped' => null);
        }
    }
}