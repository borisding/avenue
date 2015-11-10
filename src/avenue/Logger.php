<?php
namespace Avenue;

use Avenue\App;
use Monolog\Logger as MonoLogger;

class Logger
{    
    /**
     * Avenue class instance.
     *
     * @var mixed
     */
    protected $app;
    
    /**
     * Monolog Logger instance.
     * 
     * @var mixed
     */
    protected $logger;
    
    /**
     * Respective logging levels.
     *
     * @var array
     */
    protected $levels = [
        // (100): Detailed debug information.
        'debug' => MonoLogger::DEBUG,
        // (200): Interesting events. Examples: User logs in, SQL logs.
        'info' => MonoLogger::INFO,
        // (250): Normal but significant events.
        'notice' => MonoLogger::NOTICE,
        // (300): Exceptional occurrences that are not errors.
        'warning' => MonoLogger::WARNING,
        // (400): Runtime errors that do not require immediate action
        'error' => MonoLogger::ERROR,
        // (500): Critical conditions.
        'critical' => MonoLogger::CRITICAL,
        // (550): Action must be taken immediately.
        'alert' => MonoLogger::ALERT,
        // (600): Emergency: system is unusable.
        'emergency' => MonoLogger::EMERGENCY
    ];
    
    /**
     * Logger class constructor.
     * 
     * @param App $app
     * @param MonoLogger $logger
     */
    public function __construct(App $app, MonoLogger $logger)
    {
        $this->app = $app;
        $this->logger = $logger;
    }
    
    /**
     * Recording the log message, level, and context.
     * 
     * @param mixed $message
     * @param string $level
     * @param array $context
     * @return \Avenue\Logger
     */
    public function record($message, $level = 'debug', array $context = [])
    {
        $level = $this->app->arrGet($level, $this->levels, $this->levels['debug']);
        $this->logger->addRecord($level, $message, $context);
        
        return $this;
    }
    
    /**
     * Log debug message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function debug($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log info message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function info($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log notice message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function notice($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log warning message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function warning($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log error message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function error($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log critical message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function critical($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log alert message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function alert($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
    
    /**
     * Log emergency message.
     * 
     * @param mixed $message
     * @param array $context
     * @return \Avenue\Logger
     */
    public function emergency($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
}