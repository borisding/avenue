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
    public function addRecord($message, $level, array $context);
    
    /**
     * Adding log for debug message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addDebug($message, array $context);
    
    /**
     * Adding log for info message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addInfo($message, array $context);
    
    /**
     * Adding log for notice message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addNotice($message, array $context);
    
    /**
     * Adding log for warning message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addWarning($message, array $context);
    
    /**
     * Adding log for error message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addError($message, array $context);
    
    /**
     * Adding log for critical message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addCritical($message, array $context);
    
    /**
     * Adding log for alert message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addAlert($message, array $context);
    
    /**
     * Adding log for emergency message.
     * 
     * @param mixed $message
     * @param array $context
     */
    public function addEmergency($message, array $context);
}