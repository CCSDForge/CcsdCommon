<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 12/12/18
 * Time: 10:10
 */

namespace Ccsd\Virtuoso;

/**
 * Class rowDef
 * @package Ccsd\Virtuoso
 * Un table de nom de colonne lie a une ligne de reponse
 */
class rowDef extends \ArrayObject
{
    /**
     * Implementation de in_array
     * @param $value
     * @return bool
     */
    public function in_array($value) {
        return in_array($value, $this->getArrayCopy());
    }


}