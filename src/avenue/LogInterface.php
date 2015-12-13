<?php
namespace Avenue;

interface LogInterface
{
    /**
     * Adding log record.
     * 
     * @param mixed $message
     * @param string $level
     * @param array $context
     */
    public function record($message, $level = 'warning', array $context);
    
    /**
     * Log for debug message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function debug($message, array $context);
    
    /**
     * Log for info message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function info($message, array $context);
    
    /**
     * Log for notice message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function notice($message, array $context);
    
    /**
     * Log for warning message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function warning($message, array $context);
    
    /**
     * Log for error message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function error($message, array $context);
    
    /**
     * Log for critical message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function critical($message, array $context);
    
    /**
     * Log for alert message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function alert($message, array $context);
    
    /**
     * Log for emergency message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function emergency($message, array $context);
}