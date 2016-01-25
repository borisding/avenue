<?php
namespace Avenue\Components;

use Closure;
use DateTime;
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
    protected $results = [];

    /**
     * List of validation errors.
     *
     * @var array
     */
    protected $errors = [];

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
     * Check field input is required.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsRequired($input)
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
     * @param mixed $input
     * @param array $range
     * @throws \InvalidArgumentException
     * @return boolean
     */
    public function checkInRangeLength($input, array $range)
    {
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('Array expects 2 elements!');
        }

        $length = $this->getLength((string)$input);
        $minimum = $range[0];
        $maximum = $range[1];

        return $length >= $minimum && $length <= $maximum;
    }

    /**
     * Check numeric field input is in between expected range.
     *
     * @param mixed $input
     * @param array $range
     * @throws \InvalidArgumentException
     * @return boolean
     */
    public function checkInRange($input, array $range)
    {
        if (count($range) !== 2) {
            throw new \InvalidArgumentException('Array expects 2 elements!');
        }

        $minimum = $range[0];
        $maximum = $range[1];
        $input =  $input + 0;

        return $input >= $minimum && $input <= $maximum;
    }

    /**
     * Check field input is valid email.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsEmail($input)
    {
        return filter_var($input, FILTER_VALIDATE_EMAIL) !== false;
    }

    /**
     * Check field input is valid URL.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsUrl($input)
    {
        return filter_var($input, FILTER_VALIDATE_URL) !== false;
    }

    /**
     * Check field input is valid IP address.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsIp($input)
    {
        return filter_var($input, FILTER_VALIDATE_IP) !== false;
    }

    /**
     * Check field input is scalar type.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsScalar($input)
    {
        return is_scalar($input) !== false;
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
     * Check field input is valid date.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsDate($input)
    {
        return strtotime($input) !== false;
    }

    /**
     * Check date is before than targeted date.
     * If targeted field not found, will compare with current date.
     *
     * @param mixed $date
     * @param mixed $field
     * @return boolean
     */
    public function checkIsDateBefore($date, $field)
    {
        $target = $this->getFields($field, 'now');
        return $this->checkIsDate($date) && strtotime($date) < strtotime($target);
    }

    /**
     * Check date is after than targeted date.
     * If targeted field not found, will compare with current date.
     *
     * @param mixed $date
     * @param mixed $field
     * @return boolean
     */
    public function checkIsDateAfter($date, $field)
    {
        $target = $this->getFields($field, 'now');
        return $this->checkIsDate($date) && strtotime($date) > strtotime($target);
    }

    /**
     * Check date is equal with targeted date.
     * If targeted field not found, will compare with current date.
     *
     * @param mixed $date
     * @param mixed $field
     * @return boolean
     */
    public function checkIsDateEqual($date, $field)
    {
        $target = $this->getFields($field, 'now');
        return $this->checkIsDate($date) && strtotime($date) == strtotime($target);
    }

    /**
     * Check date is as expected date format.
     *
     * @param mixed $date
     * @param mixed $format
     * @return boolean
     */
    public function checkIsDateAsFormat($date, $format)
    {
        $dt = DateTime::createFromFormat($format, $date);
        return $dt && $dt->format($format) == $date;
    }

    /**
     * Check and compare both field 1 and field 2 inputs.
     *
     * @param mixed $input1
     * @param mixed $field
     */
    public function checkBothEqual($input1, $field)
    {
        $input2 = $this->getFields($field);
        return !is_null($input2) && $input1 == $input2;
    }

    /**
     * Check for alphabetic field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsAlpha($input)
    {
        return ctype_alpha($input) !== false;
    }

    /**
     * Check for alphanumeric field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsAlnum($input)
    {
        return ctype_alnum($input) !== false;
    }

    /**
     * Check for digit field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsDigit($input)
    {
        return ctype_digit($input) !== false;
    }

    /**
     * Check for hexadecimal digit field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsHexadecimal($input)
    {
        return ctype_xdigit($input) !== false;
    }

    /**
     * Check for upper case field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsUpper($input)
    {
        return ctype_upper($input) !== false;
    }

    /**
     * Check for lower case field input.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsLower($input)
    {
        return ctype_lower($input) !== false;
    }

    /**
     * Check for field input not giving whitespace only.
     *
     * @param mixed $input
     * @return boolean
     */
    public function checkIsNotWhitespace($input)
    {
        return ctype_space($input) === false;
    }

    /**
     * Implement regular expression match.
     *
     * @param mixed $input
     * @param mixed $pattern
     * @return boolean
     */
    public function checkMatch($input, $pattern)
    {
        return preg_match($pattern, $input) !== 0;
    }

    /**
     * Implement custom rule via callback by accepting the value.
     * Callback should return boolean.
     *
     * @param mixed $input
     * @param Closure $callback
     */
    public function checkCustom($input, Closure $callback)
    {
        return $callback($input);
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
     * Start validation by preparing input values.
     *
     * @return \Avenue\Components\Validation
     */
    public function init()
    {
        if (func_num_args()) {
            array_filter(func_get_args(), function($arr) {
                if (!is_array($arr)) {
                    $arr = [];
                }

                $this->fields = array_merge($this->fields, $arr);
            });
        } else {
            $this->fields = array_merge($_POST, $_GET, $_FILES);
        }

        return $this;
    }

    /**
     * Check if all field inputs validation has passed and valid.
     * Store to errors list if particular rule failed for field input.
     *
     * @return boolean
     */
    public function hasPassed()
    {
        $config = $this->app->getConfig('validation');

        foreach ($this->results as $field => $rules) {

            foreach ($rules as $rule => $status) {

                if ($status) {
                    $valid = true;
                    continue;
                }

                $valid = false;
                $fieldRule = $field . '.' . $rule;

                if (!isset($this->errors[$fieldRule])) {
                    $this->errors[$fieldRule] = [];
                }

                // populate the label
                if (isset($config[$field]) && isset($config[$field]['label'])) {
                    $label = $config[$field]['label'];
                } else {
                    $label = $field;
                }

                // populate the message
                if (isset($config[$field]) && isset($config[$field][$rule])) {
                    $message = $config[$field][$rule];
                } else {
                    $message = sprintf('%s is invalid for rule %s.', $label, $rule);
                }

                $message = preg_replace('/{label}/i', $label, $message);
                $this->errors[$fieldRule]= $message;
            }
        }

        return $valid;
    }

    /**
     * Return the populated error messages for field input(s).
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Get particular field input based on the key, w/o default value if not found.
     * Return all field inputs if key is empty.
     *
     * @param mixed $key
     * @param mixed $default
     */
    public function getFields($key = null, $default = null)
    {
        if (empty($key)) {
            return $this->fields;
        }

        return $this->app->arrGet($key, $this->fields, $default);
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
            if (!isset($this->results[$field])) {
                $this->results[$field] = [];
            }

            $fieldValue = $this->getFields($field, '');
            // invoke the the rule method by passing field input value and rule value to check with, if any
            // return the boolean result and store to the result list property
            $this->results[$field][$rule] = call_user_func_array([$this, $methodName], [$fieldValue, $ruleValue]);
        });
    }
}