<?php
namespace Kohkimakimoto\EArray;

/**
 * EArray is a small PHP class to provide convenient ways to access a PHP Array.
 *
 * @author Kohki Makimoto <kohki.makimoto@gmail.com>
 */
class EArray implements \ArrayAccess, \Iterator, \Countable
{
    const ORDER_LOW_TO_HIGH = 1;
    const ORDER_HIGHT_TO_LOW = -1;

    /**
     * array data
     * @var [type]
     */
    protected $array;
    
    /**
     * Default delimiter
     * @var [type]
     */
    protected $delimiter;

    /**
     * Constructor
     * @param Array $array
     */
    public function __construct($array = array(), $delimiter = "/")
    {
        if (!is_array($array)) {
            throw new \RuntimeException("You need to pass Array to constructor.");
        }
        $this->array = $array;
        $this->delimiter = $delimiter;
    }
    
    /**
     * Get a value
     * @param unknown $key
     */
    public function get($key, $default = null, $delimiter = null)
    {
        $ret = $this->getRawValue($key, $default, $delimiter);
        if (is_array($ret)) {
            $ret = new EArray($ret);
        }
        return $ret;
    }

    /**
     * Set a value
     * @param type $key 
     * @param type $value 
     * @param type $delimiter 
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function set($key, $value, $delimiter = null)
    {   
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        if (strpos($key, $delimiter) === false) {
            $this->array[$key] = $value;
            return $this;
        }

        $array = $this->array;

        $keys = explode($delimiter, $key);
        $lastKeyIndex = count($keys) - 1;
        $ref = &$array;
        foreach (explode($delimiter, $key) as $i => $k) {
            array_shift($keys);
            if (isset($ref[$k])) {
                if ($i === $lastKeyIndex) {
                    // last key
                    $ref[$k] = $value;
                } else {
                    $ref = &$ref[$k];
                }
            } else {
                if (is_array($ref)) {
                    $ref[$k] = $this->convertMultidimentional($keys, $value);
                } else {
                    throw new \RuntimeException("Couldn't set a value");
                }
                break;
            }
        }

        $this->array = $array;
        return $this;
    }

    /**
     * Delete a key
     * @param String $key 
     * @param String $delimiter 
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function delete($key, $delimiter = null)
    {
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        if (strpos($key, $delimiter) === false) {
            unset($this->array[$key]);
            return $this;
        }

        $array = $this->array;

        $keys = explode($delimiter, $key);
        $lastKeyIndex = count($keys) - 1;
        $ref = &$array;
        foreach (explode($delimiter, $key) as $i => $k) {
            array_shift($keys);
            if (isset($ref[$k])) {
                if ($i === $lastKeyIndex) {
                    // last key
                    unset($ref[$k]);
                } else {
                    $ref = &$ref[$k];
                }
            } else {
                throw new \RuntimeException("There is not the key '$k'");
            }
        }

        $this->array = $array;
        return $this;
    }

    /**
     * Cheking exists key
     * @param type $key 
     * @param type $delimiter 
     * @return type
     */
    public function exists($key, $delimiter = null)
    {
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        $array = $this->array;

        foreach (explode($delimiter, $key) as $k) {

            if (isset($array[$k])) {
                $array = $array[$k];
            } else {
                return false;
            }
        }
        return true;
    }

    public function each($closure)
    {
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException("The argument must be a closure");
        }

        foreach ($this as $key => $value) {
            call_user_func($closure, $key, $value);
        }

        return $this;
    }

    public function filter($closure)
    {
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException("The argument must be a closure");
        }

        $new = new EArray();
        foreach ($this as $key => $value) {
            if(call_user_func($closure, $key, $value)) {
                $rawValue = $this->getRawValue($key);
                $new->set($key, $rawValue);
            }
        }

        return $new;
    }

    /**
     * Set a default delimiter
     * @param String $delimiter
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function __toString()
    {
        return print_r($this, true);
    }

    protected function getRawValue($key, $default = null, $delimiter = null)
    {
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        $array = $this->array;

        foreach (explode($delimiter, $key) as $k) {
            $array = isset($array[$k]) ? $array[$k] : $default;
        }

        return $array;
    }


    /**
     * Convert one dimensional array into multidimensional array
     */
    protected function convertMultidimentional($oneDimArray, $leafValue)
    {
        $multiDimArray = array();
        $ref = &$multiDimArray;
        foreach ($oneDimArray as $key) {
            $ref[$key] = array();
            $ref = &$ref[$key];
        }
        $ref = $leafValue;

        return $multiDimArray;
    }

    /**
     * Get keys
     * @return array keys
     */
    public function getKeys()
    {
        return array_keys($this->array);
    }

    /**
     * Sort a array.
     * @param  String $key
     * @param  String $delimiter
     * @return EArray $earray
     */
    public function sort($key = null, $delimiter = null)
    {
        return $this->doSort($key, $delimiter, self::ORDER_LOW_TO_HIGH);
    }

    /**
     * Reverse sort a array.
     * @param  String $key
     * @param  String $delimiter
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function rsort($key = null, $delimiter = null)
    {
        return $this->doSort($key, $delimiter, self::ORDER_HIGHT_TO_LOW);
    }

    protected function doSort($key = null, $delimiter = null, $order = 1)
    {
        if ($delimiter === null) {
            $delimiter = $this->delimiter;
        }

        uasort($this->array, function($one, $another) use ($key, $delimiter, $order) {

            $oneValue = null;
            if (is_array($one)) {
                $one = new EArray($one);
                $oneValue = $one->get($key, 0, $delimiter);
            } else {
                $oneValue = $one;
            }

            $anotherValue = null;
            if (is_array($another)) {
                $another = new EArray($another);
                $anotherValue = $another->get($key, 0, $delimiter);
            } else {
                $anotherValue = $another;
            }

            $cmp = 0;
            if (is_numeric($oneValue) && is_numeric($anotherValue)) {
                $oneValue = floatval($oneValue);
                $anotherValue = floatval($anotherValue);
                if ($oneValue == $anotherValue) {
                    $cmp = 0;
                } else {
                    $cmp = ($oneValue < $anotherValue) ? -1 : 1;
                }
            } else {
                $cmp = strcmp($oneValue, $anotherValue);
            }

            if ($order === EArray::ORDER_HIGHT_TO_LOW) {
                $cmp = -$cmp;
            }

            return $cmp;
        });

        return $this;
    }

    /**
    * Get a array.
    * @return array:
    */
    public function toArray()
    {
        return $this->array;
    }

    public function offsetSet($offset, $value) {
        $this->set($offset, $value);
    }
    
    public function offsetExists($offset) {
        return $this->exists($offset);
    }

    public function offsetUnset($offset) {
        $this->delete($offset);
    }

    public function offsetGet($offset) {
        return $this->get($offset);
    }

    public function current() {
        $ret = current($this->array);
        if (is_array($ret)) {
            $ret = new EArray($ret);
        }
        return $ret;
    }
    
    public function key() {
        return key($this->array);
    }
    
    public function next() {
        $ret = next($this->array);
        if (is_array($ret)) {
            $ret = new EArray($ret);
        }
        return $ret;
    }

    public function rewind() {
        reset($this->array);
    }

    public function valid() {
        return ($this->current() !== false);
    }

     public function count() {
        return count($this->array);
    }
}
