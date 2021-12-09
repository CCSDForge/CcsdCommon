<?php


namespace Ccsd\Db;

/**
 * Class Rows
 * Proxy for Zend3  to be deprecated after migration
 * @package Ccsd\Db
 *
 */
class Rows implements \Iterator
{
    /** @var  \Laminas\Db\ResultSet\ResultSet */
    public $object = null;

    /** @param \Laminas\Db\Adapter\Driver\ResultInterface */
    public function __construct($result)
    {
        $this->object = $result;
    }

    /**
     * @param $method
     * @param $args
     */
    public function __call($method, $args)
    {
        // Run before code here
        // Invoke original method on our proxied object
        call_user_func_array(array($this->object, $method), $args);
        // Run after code here
    }

    /**
     * Proxy to property of object
     * @param $property
     * @return mixed
     */
    public function __get($property)
    {
        // Run before code here
        // Invoke original method on our proxied object
        return ($this->object->$property);
        // Run after code here
    }
    /**  */
    public function toArray() {
        $res = [];
        foreach ($this->object as $v) {
            $res[] = $v;
        }
        return $res;
    }
    /**  */
    public function count() {
        return $this->object->count();
    }
    /**  */
    public function next()
    {
        $this->object->next();
    }
    /**  */
    public function valid()
    {
        return $this->object->valid();
    }
    /**  */
    public function rewind()
    {
        $this->object->rewind();
    }
    /**  */
    public function current()
    {
        return $this->object->current();
    }
    /**  */
    public function key()
    {
        return $this->object->key();
    }
}