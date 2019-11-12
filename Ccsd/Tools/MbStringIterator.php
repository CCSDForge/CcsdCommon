<?php
/**
 * Created by PhpStorm.
 * User: marmol
 * Date: 21/03/18
 * Time: 11:23
 */

class Ccsd_Tools_MbStringIterator implements Iterator
{
    private $iPos   = 0;
    private $iSize  = 0;
    private $sStr   = null;
    /** @var bool  */
    private $is_mbstring;

    /** Constructor
     * Ccsd_Tools_MbStringIterator constructor.
     * @param string $str
     */
    public function __construct(/*string*/ $str)
    {
        $this -> is_mbstring = ini_get('mbstring.func_overload') > 2;
        // Save the string
        $this->sStr     = $str;

        // Calculate the size of the current character
        $this->calculateSize();
    }

    // Calculate size
    private function calculateSize() {
        if ($this -> is_mbstring) {
            $this->iSize = 1;   // taille toujours egal a 1 en mbstring!
            return;
        }
        // If we're done already
        if(!isset($this->sStr[$this->iPos])) {
            return;
        }

        // Get the character at the current position
        $iChar  = ord($this->sStr[$this->iPos]);

        // If it's a single byte, set it to one
        if($iChar < 128) {
            $this->iSize    = 1;
        }

        // Else, it's multi-byte
        else {

            // Figure out how long it is
            if($iChar < 224) {
                $this->iSize = 2;
            } else if($iChar < 240){
                $this->iSize = 3;
            } else if($iChar < 248){
                $this->iSize = 4;
            } else if($iChar == 252){
                $this->iSize = 5;
            } else {
                $this->iSize = 6;
            }
        }
    }

    /**
     * @param string $string
     * @param int $offset
     * @return float|int
     */
    public function ordutf8($string, &$offset)
    {
        $char = substr($string, $offset, 1);
        $code = ord($char);
        $bytesnumber = 0;
        if ($code >= 128) {        //otherwise 0xxxxxxx
            if ($code < 224) $bytesnumber = 2;                //110xxxxx
            else if ($code < 240) $bytesnumber = 3;        //1110xxxx
            else if ($code < 248) $bytesnumber = 4;    //11110xxx
            $codetemp = $code - 192 - ($bytesnumber > 2 ? 32 : 0) - ($bytesnumber > 3 ? 16 : 0);
            for ($i = 2; $i <= $bytesnumber; $i++) {
                $offset++;
                $code2 = ord($string[$offset]) - 128;        //10xxxxxx
                $codetemp = $codetemp * 64 + $code2;
            }
            $code = $codetemp;
        }
        $offset += 1;
        if ($offset >= strlen($string)) $offset = -1;

        return $code;
    }
    /** Iterator interface  */
    public function current() {

        // If we're done
        if(!isset($this->sStr[$this->iPos])) {
            return false;
        }

        // Else if we have one byte
        else if($this->iSize == 1) {
            if ($this ->is_mbstring) {
                // En multibyte, ca marche avec substr, pas avec []!
                return substr($this->sStr, $this->iPos, $this->iSize);
            } else {
                return $this->sStr[$this->iPos];
            }
        }

        // Else, it's multi-byte
        else {
            return substr($this->sStr, $this->iPos, $this->iSize);
        }
    }

    /** Iterator interface  */
    public function key()
    {
        // Return the current position
        return $this->iPos;
    }

    /** Iterator interface  */
    public function next()
    {
        // Increment the position by the current size and then recalculate
        $this->iPos += $this->iSize;
        $this->calculateSize();
    }

    /** Iterator interface  */
    public function rewind()
    {
        // Reset the position and size
        $this->iPos     = 0;
        $this->calculateSize();
    }

    /** Iterator interface  */
    public function valid()
    {
        // Return if the current position is valid
        If ($this -> is_mbstring) {
            return strlen($this->sStr) > $this->iPos;
        } else {
            return isset($this->sStr[$this->iPos]);
        }
    }
}