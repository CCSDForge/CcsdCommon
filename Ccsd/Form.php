<?php

/**
 * Class Ccsd_Form
 */
class Ccsd_Form extends Zend_Form
{
    private $_context = null;
    protected $_actions = false;

    /**
     * Constructor
     *
     * Registers form view helper as decorator
     *
     * @param mixed $options
     * @param string $context
     * @return void
     */
    public function __construct($options = null, $context = null)
    {
        $this->_context = $context;

        parent::__construct($options);
    }

    public function init()
    {
        $view = $this->getView();
        if (isset($view))
            $view->jQuery()->addStylesheet(CCSDLIB . "/css/ccsd_form.css");

        $this->loadDefaultDecorators();

        $this->addPrefixPath('ZendX_JQuery_Form_Element', 'ZendX/JQuery/Form/Element', Zend_Form::ELEMENT);
        $this->addPrefixPath('ZendX_JQuery_Form_Decorator', 'ZendX/JQuery/Form/Decorator', Zend_Form::DECORATOR);
        $this->addPrefixPath('Ccsd_Form_Element', 'Ccsd/Form/Element', Zend_Form::ELEMENT);
        $this->addPrefixPath('Ccsd_Form_Decorator', 'Ccsd/Form/Decorator', Zend_Form::DECORATOR);
        $this->addPrefixPath('Ccsd_Form_Decorator_Bootstrap', 'Ccsd/Form/Decorator/Bootstrap/', Zend_Form::DECORATOR);

        $this->addElementPrefixPath('Ccsd_Form', 'Ccsd/Form');
    }

    public function hasActions()
    {
        return $this->_actions;
    }

    public function setActions($b = false)
    {
        $this->_actions = $b;
        return $this;
    }

    public function createSubmitButton($name = "Enregistrer", $options = array())
    {
        $this->getDecorator("FormActions")->initSubmit($name, $options); //self::getDefaultTranslator()->translate($name);
        return $this;
    }

    public function createCancelButton($name = "Annuler", $options = array())
    {
        $this->getDecorator("FormActions")->initCancel($name, $options);// self::getDefaultTranslator()->translate($name);
        return $this;
    }

    /**
     * Retrieve all form element values
     *
     * @param bool $suppressArrayNotation
     * @return array
     */
    public function getValues($suppressArrayNotation = false)
    {
        $values = parent::getValues($suppressArrayNotation);

        foreach ($this->getElements() as $e) {
            if ($e instanceof Ccsd_Form_Element_Invisible || $e instanceof Ccsd_Form_Element_Hr) {
                unset ($values[$e->getName()]);
            }
        }

        return $values;
    }

    /**
     * Load the default decorators
     *
     * @return Zend_Form
     */
    public function loadDefaultDecorators()
    {
        if ($this->loadDefaultDecoratorsIsDisabled()) {
            return $this;
        }

        $decorators = $this->getDecorators();
        if (empty($decorators)) {
            $this->addDecorator('FormTinymceNew')
                ->addDecorator('FormElements')
                ->addDecorator('FormActions')
                ->addDecorator('Form')
                ->addDecorator('FormCss')
                ->addDecorator('FormJavascript')
                ->addDecorator('FormRequired', array('class' => 'col-md-9 col-md-offset-3 ccsd_form_required'));
        }
        return $this;
    }

    /**
     * Permet d'insérer un ous formulaire après un élément
     * @param Zend_Form $form
     * @param string $name
     * @param string $elem
     */
    public function insertSubForm(Zend_Form $form, $name, $elem)
    {
        $order = 0;
        foreach ($this->getElements() as $e) {
            $e->setOrder($order);
            $order++;
            if ($e->getName() == $elem) {
                $this->addSubForm($form, $name, $order);
                $order++;
            }
        }
    }

    /**
     * Add all elements' filters
     *
     * @param array $filters
     * @return Zend_Form
     */
    public function addElementFilters(array $filters)
    {
        foreach ($this->getElements() as $element) {
            $element->addFilters($filters);
        }
        return $this;
    }

    public function isValid($data)
    {
        $this->addElementFilters(array('Clean'));
        return parent::isValid($data);
    }

    /**
     * @param Ccsd_Form_Element | string $element
     */
    public function setRequire($element)
    {
        if (is_string($element)) {
            $element = $this->getElement($element);
        }
        $element->setRequired(true);
        $decorator = $element->getDecorator('Label');
        if ($decorator) {
            $decorator = new Ccsd_Form_Decorator_Label();
            $element->addDecorator($decorator);
        }
        /** @see  Zend_Form_Decorator_Label::__call */
        $decorator->setReqSuffix('<span class="icon-required">  *</span>');
        $decorator->setOption('class', 'col-md-3 control-label');
    }

    /**
     * Post traitement de get form
     *     - Permet de reprendre les require et de leur ajouter un suffix
     *     - ...
     */
    public function postGetForm()
    {
        foreach ($this->getElements() as $element) {
            /** @var Zend_Form_Element $element */
            if ($element->isRequired()) {
                $this->setRequire($element);
            }
        }
    }
}
