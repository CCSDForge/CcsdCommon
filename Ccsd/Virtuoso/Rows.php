<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 12/12/18
 * Time: 09:57
 */

namespace Ccsd\Virtuoso;

/**
 * Class Rows
 * @package Ccsd\Virtuoso
 */
class Rows implements \ArrayAccess,\Iterator
{
    /** @var string[] */
    private $links = [];
    /** @var  rowDef */
    private $vars = [];
    /** @var Row[] */
    private $rows = [];

    private $_currentRow = 0;
    /**
     * Rows constructor.
     * @param string[] $links
     * @param string[] $vars
     * @param \stdClass  $results
     */
    public function __construct($links, $vars, $results) {
        $this->links = $links;
        $this->vars  = new rowDef($vars);

        foreach ($results -> bindings as $binding) {
            $this -> rows[] = new Row($this -> vars, $binding);
        }
        $this->_currentRow = 0;
    }

    /**
     * For ArrayAccess
     * @param mixed $offset
     * @return bool
     */
    public function offsetExists($offset) {
        return isset($this->rows[$offset]);

    }

    /**
     * For ArrayAccess
     * @param int $offset
     * @param Row $value
     */
    public function offsetSet($offset, $value) {
        $this->rows[$offset] = $value;
    }
    /**
     * For ArrayAccess
     * @param int $offset
     * @return Row
     */
    public function offsetGet($offset) {
        if (isset($this->rows[$offset])) {
            return $this->rows[$offset];
        }
        return null;
    }
    /**
     * For ArrayAccess
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        if (isset($this->rows[$offset])) {
            unset($this->rows[$offset]);
        }
    }
    /**
     * @return mixed
     */
    public function key() {
        return $this->_currentRow;
    }
    /**
     * For Iterator
     * @return int|mixed
     */
    public function current() {
        return $this->rows[$this->_currentRow];
    }
    /**
     * for Iterator
     */
    public function next() {
        ++$this->_currentRow;
    }
    /**
     * for Iterator
     */
    public function rewind() {
        $this->_currentRow = 0;
    }
    /**
     * for Iterator
     */
    public function valid()
    {
        return $this->offsetGet($this->_currentRow);
    }
}