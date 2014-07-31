<?php
namespace Kohkimakimoto\EArray;

/**
 * EArray is a small PHP class to provide convenient ways to access a PHP Array.
 *
 * @author Kohki Makimoto <kohki.makimoto@gmail.com>
 */
class EArray implements \ArrayAccess, \Iterator, \Countable
{
    /**
     * array data
     * @var array
     */
    protected $array;

    /**
     * Default delimiter
     * @var string
     */
    protected $delimiter;

    /**
     * Constructor
     * @param  array  $array
     * @param  string $delimiter
     * @return void
     */
    public function __construct(array $array = array(), $delimiter = "/")
    {
        $this->array = $array;
        $this->delimiter = $delimiter;
    }

    /**
     * Get a value
     * @param  mixed  $key
     * @param  mixed  $default
     * @param  string $delimiter
     * @return mixed
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
     * @param  mixed                        $key
     * @param  mixed                        $value
     * @param  string                       $delimiter
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
     * @param  mixed                        $key
     * @param  string                       $delimiter
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
     * Check key existing.
     * @param  mixed  $key
     * @param  string $delimiter
     * @return bool
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

    /**
     * Gat size of array.
     * @return int
     */
    public function size()
    {
        return count($this->array);
    }

    /**
     * Process each array elements
     * @param  callable                     $closure
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function each($closure)
    {
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException("The argument must be a closure");
        }

        $ref = new \ReflectionFunction($closure);
        if ($ref->getNumberOfParameters() === 1) {
            foreach ($this as $value) {
                call_user_func($closure, $value);
            }
        } else {
            foreach ($this as $key => $value) {
                call_user_func($closure, $key, $value);
            }
        }

        return $this;
    }

    /**
     * Process filter
     * @param  callable                     $closure
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function filter($closure)
    {
        if (!$closure instanceof \Closure) {
            throw new \RuntimeException("The argument must be a closure");
        }

        $new = new EArray();
        foreach ($this as $key => $value) {
            if (call_user_func($closure, $key, $value)) {
                $rawValue = $this->getRawValue($key);
                $new->set($key, $rawValue);
            }
        }

        return $new;
    }

    /**
     * Sort by value
     * @param  callable                     $closure
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function sortByValue($closure)
    {
        $array = $this->array;
        uasort($array, function ($one, $another) use ($closure) {
            if (is_array($one)) {
                $one = new EArray($one);
            }
            if (is_array($another)) {
                $another = new EArray($another);
            }

            return $closure($one, $another);
        });
        $new = new EArray($array);

        return $new;
    }

    /**
     * Sort by key
     * @param  callable                     $closure
     * @return \Kohkimakimoto\EArray\EArray
     */
    public function sortByKey($closure)
    {
        $array = $this->array;
        uksort($array, function ($one, $another) use ($closure) {
            return $closure($one, $another);
        });
        $new = new EArray($array);

        return $new;
    }

    /**
     * Set a default delimiter
     * @param  string $delimiter
     * @return void
     */
    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function __toString()
    {
        return print_r($this->toArray(), true);
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
     * @param  array $oneDimArray
     * @param  mixed $leafValue
     * @return array
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


    public function register($methodName, $closure)
    {

    }

    public function __call($name, $arguments)
    {

    }

    
    /**
     * Get keys
     * @return array
     */
    public function keys()
    {
        return array_keys($this->array);
    }

    /**
     * Get keys
     * @deprecated
     * @return array
     */
    public function getKeys()
    {
        return array_keys($this->array);
    }

    /**
     * Get a array
     * @return array
     */
    public function toArray()
    {
        return $this->array;
    }

    public function offsetSet($offset, $value)
    {
        $this->set($offset, $value);
    }

    public function offsetExists($offset)
    {
        return $this->exists($offset);
    }

    public function offsetUnset($offset)
    {
        $this->delete($offset);
    }

    public function offsetGet($offset)
    {
        return $this->get($offset);
    }

    public function current()
    {
        $ret = current($this->array);
        if (is_array($ret)) {
            $ret = new EArray($ret);
        }

        return $ret;
    }

    public function key()
    {
        return key($this->array);
    }

    public function next()
    {
        $ret = next($this->array);
        if (is_array($ret)) {
            $ret = new EArray($ret);
        }

        return $ret;
    }

    public function rewind()
    {
        reset($this->array);
    }

    public function valid()
    {
        return ($this->current() !== false);
    }

     public function count()
     {
        return count($this->array);
    }
}
