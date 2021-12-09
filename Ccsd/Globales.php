<?php

/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 21/08/17
 * Time: 14:53
 */
class Ccsd_Globales_Exception extends Exception{

}

/**
 * Class Ccsd_Globales
 *
 * This class was desined to replace Hal constant use.
 * In general, one execution can define constant because a value will not change during all the request.
 *
 * But if used in scripts or in phpunit test, you want to execute more than one request in on shot...
 * So execution constants cannot be define as php constants.
 *
 * We need to use some sort of globales variables.  But, like constants, we want to be sure that they will be define
 * and that they will be defined once.
 * So redefined a Globales will throw exception like when refining php constant.
 * But, here, we can reset the constant to be able to redefine it.  But this action is a special 'reset' action and
 * not just an affectation!
 *
 * init function is called once for the class.
 *
 * Globales declarations is shared accross all objects of Globales class
 * Globales objects have not object properties!
 * 
 */
class Ccsd_Globales
{
    private static $globales = array();
    private static $authorized_name = array();
    protected static $init = false;

    /**
     * You can :
     *     record some Global name directly in subclass
     *     define some value...
     * This function is called just once for class, at first object creation
     * Only use static obj or function that act staticaly
     *
     */
    protected function init() {
        // To define in subclasses
    }

    /**
     * Ccsd_Globales constructor.
     */
    public function __construct() {
        // Init just one time for class
        if (self::$init === false) {
            $this -> init();
            self::$init = true;
        }
    }
    /** Set value of globale $name
     *  Can change the value with __set! Must use reset if allready defined
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws Ccsd_Globales_Exception
     */
    public function __set($name, $value) {
        if ($this -> defined($name)) {
            throw new Ccsd_Globales_Exception('Allready defined');
        }
        if ($this -> is_recorded($name)) {
            return $this -> _internal_set($name, $value);
        } else {
            throw new Ccsd_Globales_Exception("Variable $name not recorded");
        }
    }

    /** Get value of globale $name
     * Throw exception if $name is not recorded or not defined
     * @param string $name
     * @return mixed
     * @throws Ccsd_Globales_Exception
     */
    public function __get($name) {
        if ($this -> defined($name)) {
            return self::$globales[$name];
        }
        if ($this ->  is_recorded($name)) {
            throw new Ccsd_Globales_Exception("Variable $name not set");
        } else {
            throw new Ccsd_Globales_Exception("Variable $name not recorded");
        }
    }

    /**
     * @param string $name
     * @param mixed $value
     * @return mixed
     * @throws Ccsd_Globales_Exception
     */
    public function reset($name, $value=null) {
        if ($value === null) {
            throw new Ccsd_Globales_Exception("Can reset a globale variable $name with Null value");
        }
        $this -> _internal_record($name);
        return $this -> _internal_set($name, $value);
    }

    /** do assign value to $name: no control at all
     * @param string $name
     * @param mixed $value
     * @return mixed
     */
    private function _internal_set($name, $value) {
        return self::$globales[$name] = $value;
    }

    /** do record of globale name, without control
     * @param string $name
     * @param mixed $value
     * @return void
     */
    private function _internal_record($name, $value = null) {
        if ($value !== null) {
            $this->__set($name, $value);
        }
    }
    /**
     * Return true if globale var has a value
     * @param string $name
     * @return bool
     */
    public function defined($name){
        return array_key_exists($name, self::$globales);
    }

    /**
     * Return true if globale is recorded (can have no value!)
     * @param string $name
     * @return bool
     */
    public function is_recorded($name) {
        return array_key_exists($name, self::$authorized_name);
    }
    /**
     * Record the new Globales name for futures usage
     * @param string $name
     * @param mixed $value
     * @return bool
     */
    public function record($name, $value = null) {
        if ($this -> is_recorded($name)) {
            // allready recorded
            return false;
        }
        // We define the name
        self::$authorized_name[$name] = true;
        $this -> _internal_record($name, $value);
        return true;
    }
}