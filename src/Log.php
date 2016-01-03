<?php
namespace Avenue;

use Avenue\App;
use Avenue\LogInterface;
use Monolog\Logger;

class Log implements LogInterface
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * Monolog class instance.
     * 
     * @var mixed
     */
    protected $monolog;
    
    /**
     * Log channel.
     * 
     * @var mixed
     */
    protected $channel;
    
    /**
     * List of handlers.
     * 
     * @var array
     */
    protected $handlers = [];
    
    /**
     * List of processors.
     * 
     * @var unknown
     */
    protected $processors = [];
    
    /**
     * Default logging configuration.
     * 
     * @var array
     */
    protected $config = [
        'channel' => 'avenue.logging',
        'handlers' => [],
        'processors' => []
    ];
    
    /**
     * List of log level.
     * 
     * @var array
     */
    protected $levels = [
        // (100): Detailed debug information.
        'debug'     => Logger::DEBUG,
        // (200): Interesting events. Examples: User logs in, SQL logs.
        'info'      => Logger::INFO,
        // (250): Normal but significant events.
        'notice'    => Logger::NOTICE,
        // (300): Exceptional occurrences that are not errors.
        'warning'   => Logger::WARNING,
        // (400): Runtime errors that do not require immediate action
        'error'     => Logger::ERROR,
        // (500): Critical conditions.
        'critical'  => Logger::CRITICAL,
        // (550): Action must be taken immediately.
        'alert'     => Logger::ALERT,
        // (600): Emergency: system is unusable.
        'emergency' => Logger::EMERGENCY
    ];
    
    /**
     * Log class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
        
        if (empty($this->monolog)) {
            $this->config = array_merge($this->config, $this->app->getConfig('logging'));
            $this->channel = $this->config['channel'];
            $this->handlers = $this->config['handlers'];
            $this->processors = $this->config['processors'];
            $this->boot(new Logger($this->channel));
        }
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addRecord()
     */
    public function addRecord($message, $level = 'warning', array $context = [])
    {
        $level = $this->app->arrGet($level, $this->levels, $this->levels['warning']);
        $this->monolog->addRecord($level, $message, $context);
        
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addDebug()
     */
    public function addDebug($message, array $context = [])
    {
        $this->addRecord($message, 'debug', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addInfo()
     */
    public function addInfo($message, array $context = [])
    {
        $this->addRecord($message, 'info', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addNotice()
     */
    public function addNotice($message, array $context = [])
    {
        $this->addRecord($message, 'notice', $context);
        return $this;
    }

    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addWarning()
     */
    public function addWarning($message, array $context = [])
    {
        $this->addRecord($message, 'warning', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addError()
     */
    public function addError($message, array $context = [])
    {
        $this->addRecord($message, 'error', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addCritical()
     */
    public function addCritical($message, array $context = [])
    {
        $this->addRecord($message, 'critical', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addAlert()
     */
    public function addAlert($message, array $context = [])
    {
        $this->addRecord($message, 'alert', $context);
        return $this;
    }
    
    /**
     * {@inheritDoc}
     * 
     * @see \Avenue\LogInterface::addEmergency()
     */
    public function addEmergency($message, array $context = [])
    {
        $this->addRecord($message, 'emergency', $context);
        return $this;
    }
    
    /**
     * Boot the monolog based on the handlers and processors.
     * 
     * @param Logger $monolog
     */
    protected function boot(Logger $monolog)
    {
        // instantiate monolog logger instance
        $this->monolog = $monolog;
        
        // push each assigned handler
        foreach ($this->handlers as $handler) {
            $this->monolog->pushHandler($handler);
        }
        
        // push each assigned processor
        foreach ($this->processors as $processor) {
            $this->monolog->pushProcessor($handler);
        }
    }
}