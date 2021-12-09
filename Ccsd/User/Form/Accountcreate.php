<?php

/**
 * Formulaire de création de compte
 * @author rtournoy
 *
 */
class Ccsd_User_Form_Accountcreate extends Ccsd_Form
{

    private $iniFile = '';
    /** @var string|null  */
    private $iniSection = null;

    const ACCOUNT_CREATED_SUCCESS = 'Compte créé';

    const ACCOUNT_CREATED_FAIL = 'Échec de la création du compte';

    /**
     * Ccsd_User_Form_Accountcreate constructor.
     * @param null $options
     * @param null $context
     */
    public function __construct($options = null, $context = null)
    {
        if (array_key_exists('ini', $options)) {
            $this->iniFile = $options['ini'];
            unset($options['ini']);
        }
        if (array_key_exists('section', $options)) {
            $this->iniSection = $options['section'];
            unset($options['section']);
        }
        parent::__construct($options, $context);
    }

    public function init ()
    {
        parent::init();
        $config = new Zend_Config_Ini($this->iniFile, $this->iniSection);
        if ($this->iniSection === null) {
            $this->iniSection = 'main';
        }
        $section = $config->get($this->iniSection);
        $this->setConfig($section);
        $elem = $this->getElement('USERNAME');
        // Controle du login existant
        if ($elem) {
            $options = array(
                    'table' => 'T_UTILISATEURS',
                    'field' => 'USERNAME',
                    'adapter' => Ccsd_Db_Adapter_Cas::getAdapter()
            );
            $validator = new Zend_Validate_Db_NoRecordExists($options);
            $elem->addValidator($validator);
        }

        $email = $this->getElement('EMAIL');
        // Controle du mail existant
        if ($email) {
            $options = array(
                'table' => 'T_UTILISATEURS',
                'field' => 'EMAIL',
                'adapter' => Ccsd_Db_Adapter_Cas::getAdapter()
            );
            $validator = new Zend_Validate_Db_NoRecordExists($options);
            $validator -> setMessage("A record matching email (%value%) was found.  Use login retrieve tools");
            $email->addValidator($validator);
        }
    }
}



