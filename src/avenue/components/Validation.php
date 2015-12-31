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
     * Validation class constructor.
     * 
     * @param App $app
     */
    public function __construct(App $app)
    {
        $this->app = $app;
    }
    
    /**
     * Check field input is not empty.
     * 
     * @param mixed $input
     * @return boolean
     */
    public function checkRequired($input)
    {
        if (is_string($input)) {
            $input = trim($input);
        }
    
        return !empty($input) === true;
    }

    /**
     * Check field input length is equal to expected.
     * 
     * @param string $input
     * @param integer $equal
     * @return boolean
     */
    public function checkEqualLength($input, $equal)
    {
        return $this->getLength((string)$input) === $equal;
    }
    
    /**
     * Check minimum field input length.
     * 
     * @param string $input
     * @param integer $minimum
     * @return boolean
     */
    public function checkMinLength($input, $minimum)
    {
        return $this->getLength((string)$input) >= $minimum;
    }
    
    /**
     * Check maximum field input length.
     *
     * @param string $input
     * @param integer $maximum
     * @return boolean
     */
    public function checkMaxLength($input, $maximum)
    {
        return $this->getLength((string)$input) <= $maximum;
    }
    
    /**
     * Check field input length is in between expected length.
     *
     * @param string $input
     * @param integer $between
     * @return boolean
     */
    public function checkBetweenLength($input, array $between)
    {
        if (count($between) !== 2) {
            throw new \InvalidArgumentException('Array expects 2 elements!');
        }
        
        $length = $this->getLength((string)$input);
        $minimum = $between[0];
        $maximum = $between[1];
        
        return ($length >= $minimum && $length <= $maximum);
    }
    
    /**
     * Return the calculated string length.
     *
     * @param mixed $value
     */
    protected function getLength($string)
    {
        return function_exists('mb_strlen') ? mb_strlen($string) : strlen($string);
    }
    
    /**
     * Check field value is valid email.
     * 
     * @param mixed $input
     * @return boolean
     */
    public function checkEmail($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Check field input is valid float by coercing it.
     * 
     * @param mixed $input
     * @return boolean
     */
    public function checkIsFloat($input)
    {
        return is_float($input + 0);
    }
    
    /**
     * Check field input is valid integer by coercing it.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsInteger($input)
    {
        return is_int($input + 0);
    }
    
    /**
     * Check field input is numeric.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsNumeric($input)
    {
        return is_numeric($input) !== false;
    }
    
    /**
     * Check field input is valid boolean type.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsBoolean($input)
    {
        return is_bool($input) !== false;
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
     * Check if all field inputs validation has passed and valid.
     */
    public function isValid()
    {
        //TODO
        print_r($this->result);
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
            throw new \LogicException(sprintf('Unsupported rule [%s] for validation.', $rule));
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