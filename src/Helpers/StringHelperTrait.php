<?php
namespace Avenue\Helpers;

trait StringHelperTrait
{
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
     * Return true if the input value is in expected alphanumeric character(s).
     *
     * @param  mixed $input
     * @return boolean
     */
    public function isAlnum($input)
    {
        return preg_match('/^[a-zA-Z0-9]+$/', $input) === 1;
    }

    /**
     * Return true if the input value is in expected alphabetic character(s).
     *
     * @param mixed $input
     * @return boolean
     */
    public function isAlpha($input)
    {
        return preg_match('/^[a-zA-Z]+$/', $input) === 1;
    }

    /**
     * Return true if the input value is in expected digit character(s).
     *
     * @param mixed $input
     */
    public function isDigit($input)
    {
        return preg_match('/^[0-9]+$/', $input) === 1;
    }

    /**
     * Quick check for valid method name. Basically, same with class regexp.
     *
     * @link: http://php.net/manual/en/language.oop5.basic.php
     * @param mixed $input
     * @return boolean
     */
    public function isValidMethodName($input)
    {
        return preg_match('/^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$/', $input) === 1;
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

    /**
     * Repeating a string based on the array's start/end index.
     * Then glue with specified delimiter.
     *
     * @param mixed $value
     * @param mixed $delimiter
     * @param mixed $start
     * @param mixed $end
     * @return string
     */
    public function fillRepeat($value, $delimiter, $start, $end)
    {
        return implode($delimiter, array_fill($start, $end, $value));
    }

}