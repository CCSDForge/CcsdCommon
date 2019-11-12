<?php

/**
 * @see http://php.net/manual/en/datetime.createfromformat.php pour les formats de dates utilisables
 * @author Loic
 *
 *
 * Configuration INI :
 *
 * validators.0.validator = "Date"
 *
 * #Définition d'un format
 * validators.0.options.format = "Y-m-d"
 *
 * #Définition de plusieurs formats
 * validators.0.options.format.0 = "Y"
 * validators.0.options.format.1 = "Y-m"
 * validators.0.options.format.2 = "Y-m-d"
 *
 * #Définition d'une borne inférieure
 * validators.0.options.start = "2011-03-31"
 * validators.0.options.startFormat = "Y-m-d"
 *
 * Si le besoin est dde définir une borne inférieure, il est requis de renseigner à la fois $start (la valeur de cette borne inférieure)
 * mais aussi son format $startFormat - la comparaison se fait sur les timestamps respectifs.
 *
 */
class Ccsd_Form_Validate_Date extends Zend_Validate_Date {

    const WRONGDATE    = 'dateWrongDate';

    /**
     * Validation failure message template definitions
     *
     * @var array
     */
    protected $_messageTemplates = array(
        self::INVALID        => "Invalid type given. String, integer, array or Zend_Date expected",
        self::INVALID_DATE   => "'%value%' does not appear to be a valid date",
        self::FALSEFORMAT    => "'%value%' does not fit the date format '%format%'",
        self::WRONGDATE		 => "'%value%' must not be less than '%start%'",
    );

    /**
     * @var array
     */
    protected $_messageVariables = array(
        'format'  => '_format',
        'start'	  => '_start'
    );

    /**
     * Optional minimum date (included)
     *
     * @var string|null
     */
    protected $_start;

    /**
     * Optional minimum date format / Required if $_start given
     * @var string|null
     */
    protected $_startFormat;

    /**
     * Sets validator options
     *
     * @param  string|Zend_Config $options OPTIONAL
     * @return void
     */
    public function __construct($options = array())
    {
        parent::__construct ($options);

        if (array_key_exists('startFormat', $options)) {
            $this->setStartFormat($options['startFormat']);
        }

        if (array_key_exists('start', $options)) {
            $this->setStart($options['start']);
        }
    }

    /**
     * Returns the minimum date option
     *
     * @return DateTime|string|null
     */
    public function getStart()
    {
        return $this->_start;
    }

    /**
     * Sets the minimum date option
     *
     * @param  string|null $start
     * @return Zend_Validate_Date provides a fluent interface
     */
    public function setStart($start = null)
    {
        if (isset ($start) && !isset ($this->_startFormat)) {
            throw new Exception ("Minimum date format : null given, string expected");
        }

        if (isset ($start) && isset ($this->_startFormat)) {
            if (!($this->_start = DateTime::createFromFormat($this->_startFormat, $start)) instanceof DateTime) {
                throw new Exception ("Minimum date is not an instance of DateTime. Verify minimum date format or the value of minimum date");
            }

            if ($this->_start->format($this->_startFormat) != $start) {
                throw new Exception ("The format for minimum date is incorrect. Please verify.");
            }
        }

        return $this;
    }

    /**
     * Returns the minimum date format option
     *
     * @return string|null
     */
    public function getStartFormat()
    {
        return $this->_startFormat;
    }

    /**
     * Sets the minimum date format option
     *
     * @param  string|null $startFormat
     * @return Zend_Validate_Date provides a fluent interface
     */
    public function setStartFormat($startFormat = null)
    {
        $this->_startFormat = $startFormat;
        return $this;
    }

    /**
     * (non-PHPdoc)
     * @see Zend_Validate_Date::isValid()
     */
    public function isValid ($value)
    {
        //Pour éviter l'arrondi dans le cas du format YYYY-MM, on ajoute un jour
        $tovalidvalue = $value;
        if (preg_match('/^[0-9]{4}-[0-9]{2}$/', $value)) {
            $tovalidvalue .= "-01";
        }

        if (!is_array($this->_format)) {
            $this->_format = array ($this->_format);
        }

        $valid = false;
        $default_error = self::FALSEFORMAT;

        //Premier test de validation : le format d la valeur donnée
        foreach ($this->_format as $format) {
            if (($date = DateTime::createFromFormat($format, $tovalidvalue)) instanceof DateTime) {
                if (($valid = $valid || $date->format($format) == $tovalidvalue) === FALSE) {
                    $default_error = self::INVALID_DATE;
                } else {
                    $this->_setValue($date);
                    break;
                }
            }
        }

        if (!$valid) {
            $this->_error($default_error);
        }

        /* @var $start DateTime */
        $start = $this->getStart();

        /* @var $value DateTime */
        $date = $this->_value;

        if ($valid && isset ($date) && isset ($start) && $start->getTimeStamp() > $date->getTimeStamp()) {
            $this->_setValue($value);
            $this->_start = $start->format($this->getStartFormat());
            $this->_error(self::WRONGDATE);
            $valid = false;
        }

        return $valid;
    }
}