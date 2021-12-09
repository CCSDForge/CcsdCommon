<?php
/**
 * Created by PhpStorm.
 * User: iguay
 * Date: 05/07/18
 * Time: 11:21
 */
class Ccsd_Referentiels_Hceres_Equiv // extends Ccsd_Referentiels_Abstract
{
    // Constantes pour la table
    const TABLE         = 'REF_HCERES_EQUIV';
    const EQUIVID       = 'EQUIVID';
    const NEWHCERESID   = 'NEWHCERESID';
    const OLDHCERESID   = 'OLDHCERESID';

    protected $_table = self::TABLE;
    protected $_primary = self::EQUIVID;

    // Propriétés de l'objet
    /**
     * Equivalence id.
     *
     * @var integer
     */
    private $equivId = 0;
    /**
     * New entity id.
     *
     * @var integer
     */
    private $newHceresId = 0;
    /**
     * Old entity id.
     *
     * @var integer
     */
    private $oldHceresId = 0;

    /**
     * Lecture de la correspondance nacienne<=>nouvelle entité en base
     *
     * @param int $id
     * @param bool $recursive
     *
     * @return $this|null
     */
    public function load($iNewId)
    {
        if ($iNewId == null || !is_numeric($iNewId)) {
            return null;
        }

        $db = Zend_Db_Table_Abstract::getDefaultAdapter();

        $sql = $db->select()->from($this->_table)
            ->where(self::NEWHCERESID . ' = ?', (int)$iNewId);
        $row = $db->fetchRow($sql);

        if ($row) {
            $this->_data = $row;
            return $this;
        }

        return null;
    }


}