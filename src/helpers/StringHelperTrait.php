<?php
namespace Avenue\Helpers;

trait StringHelperTrait
{
    /**
     * Convert JSON data into array associative format.
     *
     * @param mixed $data
     */
    public function jsonToArr($data)
    {
        return json_decode($data, true);
    }
    
    /**
     * Convert JSON data into object format.
     *
     * @param mixed $data
     */
    public function jsonToObj($data)
    {
        return json_decode($data);
    }
    
    /**
     * Escaping and converting to html entities from input.
     *
     * @param mixed $input
     * @param string $flag
     * @param string $encoding
     * @param string $doubleEncode
     */
    public function escape($input, $flag = ENT_COMPAT, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return htmlentities($input, $flag, $encoding, $doubleEncode);
    }
    
    /**
     * Alias escaping and converting to html entities from input.
     *
     * @param mixed $input
     * @param string $flag
     * @param string $encoding
     * @param string $doubleEncode
     */
    public function e($input, $flag = ENT_COMPAT, $encoding = 'UTF-8', $doubleEncode = true)
    {
        return $this->escape($input, $flag, $encoding, $doubleEncode);
    }

    /**
     * Loop over data and escape it, respectively.
     *
     * @param array $data
     */
    public function escapeEach(array $data)
    {
        $escapedData = [];
    
        if ($this->arrIsAssoc($data)) {
    
            foreach ($data as $key => $value) {
                $escapedData[$key] = $this->escape($value);
            }
        } else {
            
            for ($i = 0, $len = count($data); $i < $len; $i++) {
                $escapedData[$i] = $this->escape($data[$i]);
            }
        }
        
        return $escapedData;
    }

    /**
     * Alias loop over data and escape it, respectively.
     *
     * @param array $data
     */
    public function ee(array $data)
    {
        return $this->escapeEach($data);
    }

    /**
     * Return true if the input value is in expected alphanumeric form.
     * 
     * @param  mixed $value
     * @return boolean
     */
    public function isInputAlnum($value)
    {
        return preg_match('/^[a-zA-Z0-9-_]+$/', $value) === 1;
    }
    
    /**
     * Compare two hashed string and return true if both are equal.
     * 
     * @param string $h1
     * @param string $h2
     * @return boolean
     */
    public function hashedCompare($h1, $h2)
    {
        if (strlen($h1) != strlen($h2)) {
            return false;
        }
        
        if (function_exists('hash_equals')) {
            return hash_equals($h1, $h2);
        }
        
        // alternative solution
        $res = $h1 ^ $h2;
        $ret = 0;
        
        for ($i = strlen($res) - 1; $i >= 0; $i--) {
            $ret |= ord($res[$i]);
        }
        
        return $ret === 0;
    }
}