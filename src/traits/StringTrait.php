<?php
namespace Avenue\Traits;

trait StringTrait
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
        }
    
        return $escapedData;
    }
}