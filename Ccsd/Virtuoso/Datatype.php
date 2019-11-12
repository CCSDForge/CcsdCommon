<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 12/12/18
 * Time: 10:16
 */

namespace Ccsd\Virtuoso;

/**
 * Class Datatype
 * @package Ccsd\Virtuoso
 *
 * Cette classe donne la conversion entre un type donne par Virtuoso vers le type php correspondant
 *
 * Exemple de type Virtuoso dans les resultats
 *  { "g":     { "type": "uri",
 *               "value": "https://data.archives-ouvertes.fr/author/" }	,
 *    "count": { "type": "typed-literal",
 *               "datatype": "http://www.w3.org/2001/XMLSchema#integer", "value": "27582255" }},
 *
 */
class Datatype
{
    static public $virtuosoDatatype2phptype = [
        "http://www.w3.org/2001/XMLSchema#integer" => 'int',

    ];

    static public $virtuosotype2phptype = [
        "uri" => 'string',
    ];

    /**
     * Retourne la valeur convertie suivant les types donnes par Virtuoso et les tableaux de correspondance
     * Renvoie toujours une valeur, si besoin la valeur brute non traitee.
     *
     * @param \stdClass $obj
     * @return mixed
     */
    static public function convert(\stdClass $obj) {
        $value = $obj->value;
        $type = null;
        if (property_exists($obj, 'datatype') && $obj -> datatype && array_key_exists($obj -> datatype, self::$virtuosoDatatype2phptype)) {
                $type = self::$virtuosoDatatype2phptype[$obj -> datatype];

        } else if (property_exists($obj, 'type') && $obj -> type && array_key_exists($obj -> type, self::$virtuosotype2phptype)) {
            $type =  self::$virtuosotype2phptype[$obj -> type];
        }
        if ($type)
            settype($value, $type);
        return $value;

    }
}