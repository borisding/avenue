<?php
namespace Avenue;

use Avenue\App;
use Monolog\Logger;

class Log
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
        'debug' => Logger::DEBUG,
        // (200): Interesting events. Examples: User logs in, SQL logs.
        'info' => Logger::INFO,
        // (250): Normal but significant events.
        'notice' => Logger::NOTICE,
        // (300): Exceptional occurrences that are not errors.
        'warning' => Logger::WARNING,
        // (400): Runtime errors that do not require immediate action
        'error' => Logger::ERROR,
        // (500): Critical conditions.
        'critical' => Logger::CRITICAL,
        // (550): Action must be taken immediately.
        'alert' => Logger::ALERT,
        // (600): Emergency: system is unusable.
        'emergency' => Logger::EMERGENCY
    ];
    
    /**
     * Logger class constructor.
     * 
     * @param App $app
     * @param Logger $logger
     */
    public function __construct(App $app, Logger $logger)
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
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
     * @return \Avenue\Log
     */
    public function emergency($message, array $context = [])
    {
        $this->record($message, __FUNCTION__, $context);
        return $this;
    }
}