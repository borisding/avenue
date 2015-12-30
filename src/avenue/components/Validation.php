<?php
// TODO: continue with adding more rules and validation message, label handling
namespace Avenue\Components;

use Avenue\App;

class Validation
{
    /**
     * App class instance.
     * 
     * @var mixed
     */
    protected $app;
    
    /**
     * List of field input.
     * 
     * @var array
     */
    protected $fields = [];
    
    /**
     * List of the input result
     * 
     * @var array
     */
    protected $result = [];
    
    /**
     * Validation core method's prefix.
     * 
     * @var string
     */
    const METHOD_PREFIX = 'check';
    
    /**
     * Check field value is not empty.
     * 
     * @param mixed $value
     */
    public function checkRequired($value)
    {
        if (is_string($value)) {
            $value = trim($value);
        }
        
        return !empty($value) === true;
    }
    
    /**
     * Validation class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Start validation by preparing input values.
     * 
     * @return \Avenue\Components\Validation
     */
    public function init()
    {
        if (func_num_args() === 0) {
            $this->fields = array_merge($_POST, $_GET, $_FILES);
        } else {
            array_filter(func_get_args(), function($arr) {
                if (!is_array($arr)) {
                    $arr = [];
                }
                
                $this->fields = array_merge($this->fields, $arr);
            });
        }
        
        return $this;
    }
    
    /**
     * Validation call magic method.
     * 
     * @param mixed $rule
     * @param array $params
     */
    public function __call($rule, array $params)
    {
        $methodName = static::METHOD_PREFIX . ucfirst($rule);
        
        if (!method_exists($this, $methodName)) {
            throw new \LogicException(sprintf('Validation class method %s does not exist.', $methodName));
        }
        
        // assign field input(s)
        $fields = [];
        
        if (is_array($params[0])) {
            $fields = $params[0];
        } else {
            $fields[0] = $params[0];
        }
        
        // rule values to check with, if any
        if (isset($params[1])) {
            $ruleValue = $params[1]; 
        } else {
            $ruleValue = '';
        }
        
        // go through respective fields
        array_filter($fields, function($field) use ($rule, $ruleValue, $methodName) {
            if (!isset($this->result[$field])) {
                $this->result[$field] = [];
            }
            
            $fieldValue = $this->app->arrGet($field, $this->fields, '');
            // invoke the the rule method by passing field input value and rule value to check with, if any
            // return the boolean result and store to the result list property
            $this->result[$field][$rule] = call_user_func_array([$this, $methodName], [$fieldValue, $ruleValue]);
        });
    }
}