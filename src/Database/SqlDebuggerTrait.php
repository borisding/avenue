<?php
namespace Avenue\Database;

trait SqlDebuggerTrait
{
    /**
     * Debug by passing sql statement and data.
     * Print out raw SQL with actual value(s).
     *
     * @param  mixed $sql
     * @param  array  $data
     * @return string
     */
    public function debug($sql, array $data)
    {
        foreach ($data as $param => $value) {
            if (is_string($value)) {
                $value = sprintf("'%s'", $value);
            } elseif (is_bool($value)) {
                $value = $value ? 'TRUE' : 'FALSE';
            } elseif (is_null($value)) {
                $value = 'NULL';
            }

            if (is_int($param)) {
                $sql = preg_replace('/\?/', $value, $sql, 1);
            } else {
                $sql = str_replace($param, $value, $sql);
            }
        }

        return sprintf('[SQL] %s', $sql);
    }
}
