<?php

abstract class Ccsd_Referentiels_Structure_Abstract
{
    abstract function getParentCount();

    abstract function setParentCount($new_count);

    abstract function addParent($oneParent, $code);
}