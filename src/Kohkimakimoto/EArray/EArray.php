<?php
namespace Kohkimakimoto\EArray;

class EArray implements \ArrayAccess
{
    protected $array;

    /**
     * Constructor
     * @param Array $array
     */
    public function __construct($array = array())
    {
        $this->array = $array;
    }
    
    /**
     * Get a value
     * @param unknown $key
     */
    public function get($key, $default = null, $delimiter = '/')
    {
        $array = $this->array;

        foreach (explode($delimiter, $key) as $k) {
          $array = isset($array[$k]) ? $array[$k] : $default;
        }

        if (is_array($array)) {
            $array = new EArray($array);
        }

        return $array;
    }

    /**
    * Set a value.
    * @param unknown $key
    * @param unknown $value
    */
    public function set($key, $value)
    {
        $this->array[$key] = $value;
    }

    /**
     * Delete a value.
     * @param unknown $key
     */
    public function delete($key)
    {
        unset($this->array[$key]);
    }

    /**
    * Get a array.
    * @return array:
    */
    public function getArray()
    {
        return $this->array;
    }


    public function offsetSet($offset, $value) {
        $this->array[$offset] = $value;
    }
    
    public function offsetExists($offset) {
        return isset($this->array[$offset]);
    }

    public function offsetUnset($offset) {
        unset($this->array[$offset]);
    }

    public function offsetGet($offset) {
        return isset($this->array[$offset]) ? $this->array[$offset] : null;
    }
}