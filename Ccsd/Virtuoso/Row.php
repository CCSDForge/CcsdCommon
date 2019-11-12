<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 12/12/18
 * Time: 09:57
 */

namespace Ccsd\Virtuoso;

/**
 * Class Row
 * @package Ccsd\Virtuoso
 */

class Row
{
    private $rowDef = null;
    private $binding = null;

    /**
     * Row constructor.
     * @param rowDef $rowDef
     * @param \stdClass $binding   donnee par json_decode
     */
    public  function __construct($rowDef, $binding) {
        $this -> rowDef = $rowDef;
        $this -> binding = $binding;
    }

    /**
     * Retourne la valeur, si possible typee en fonction de datatype
     * @see Datatype
     * @param $name
     * @return mixed
     *   null si le champs n'existe pas.
     */
    public function __get($name) {
        if ($this->rowDef-> in_array($name)) {
            return Datatype::convert($this -> binding -> $name);
        }
        return null;
    }
}