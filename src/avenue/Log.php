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
            $config = $this->app->getConfig('logging');
            
            $this->channel = $this->app->arrGet('channel', $config, 'avenue.logging');
            $this->handlers = $this->app->arrGet('handlers', $config, []);
            $this->processors = $this->app->arrGet('processors', $config, []);
            
            $this->boot(new Logger($this->channel));
        }
    }
    
    /**
     * Get the monolog logger instance.
     */
    public function getMonolog()
    {
        return $this->monolog;
    }
    
    /**
     * Add log record by passing message, level and contect, if any.
     * 
     * @see \Avenue\LogInterface::record()
     */
    public function record($message, $level = 'warning', array $context = [])
    {
        $level = $this->app->arrGet($level, $this->levels, $this->levels['warning']);
        $this->monolog->addRecord($level, $message, $context);
        
        return $this;
    }
    
    /**
     * Log debug message.
     * 
     * @see \Avenue\LogInterface::debug()
     */
    public function debug($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log info message.
     * 
     * @see \Avenue\LogInterface::info()
     */
    public function info($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log notice message.
     * 
     * @see \Avenue\LogInterface::notice()
     */
    public function notice($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log warning message.
     * 
     * @see \Avenue\LogInterface::warning()
     */
    public function warning($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log error message.
     * 
     * @see \Avenue\LogInterface::error()
     */
    public function error($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log critical message.
     * 
     * @see \Avenue\LogInterface::critical()
     */
    public function critical($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log alert message.
     * 
     * @see \Avenue\LogInterface::alert()
     */
    public function alert($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log emergency message.
     * 
     * @see \Avenue\LogInterface::emergency()
     */
    public function emergency($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
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