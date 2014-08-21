<?php

class Application_Form_ClientTranscriptionType extends Zend_Form
{

    /**
     * Handle instantiation of the Application_Form_ClientTranscriptionType form with
     * the requisite validation rules.
     *
     * @param Zend_Config $options the configuration options
     *
     * @access public
     * @see Zend_Form::init()
     *
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $elements = array(
            $this->transcriptionType(),
            $this->description(),
            $this->sortOrder(),
            $this->defaultTurnaroundTime(),
            $this->turnaroundTimes(),
            $this->clientId(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Client Id
     *
     * @return Zend_Form_Element_Hidden the client id
     */
    private function clientId()
    {
        $element = new Zend_Form_Element_Hidden('client_id');
        $element->addFilter('StringTrim');
        return $element;
    }

    /**
     * Transcription Type
     *
     * @return Zend_Form_Element_Text
     */
    private function transcriptionType()
    {
        $element = new Zend_Form_Element_Text('transcription_type');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Description
     *
     * @return Zend_Form_Element_Textarea
     */
    private function description()
    {
        $element = new Zend_Form_Element_Textarea('description');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Sort Order
     *
     * @return Zend_Form_Element_Text
     */
    private function sortOrder()
    {
        $element = new Zend_Form_Element_Text('sort_order');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Turnaround Types
     *
     * @return Zend_Form_Element_Select turnaround times
     */
    private function defaultTurnaroundTime()
    {
        $mapper = new Application_Model_TurnaroundTimeMapper();
    	$options = $mapper->fetchAllForDropdown();
        $element = new Zend_Form_Element_Select('default_turnaround_time');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Turnaround Types
     *
     * @return Zend_Form_Element_Select turnaround times
     */
    private function turnaroundTimes()
    {
        $mapper = new Application_Model_TurnaroundTimeMapper();
    	$options = $mapper->fetchAllForDropdown();
        $element = new Zend_Form_Element_MultiCheckbox('turnaround_times[]');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Creates a submit form for posting the form
     *
     * @return Zend_Form_Element_Submit submit button/element
     */
    private function submit()
    {
        $element = new Zend_Form_Element_Submit('submit');
        $element->setLabel('Save')
            ->setAttrib('class', 'reg_input_btn');

        return $element;
    }

    /**
     * Loops over all of the elements and removes the decorators.
     *
     * @param array &$elements form elements
     *
     * @return void
     */
    private function removeDecorators(&$elements)
    {
        foreach ($elements as $element) {
            if ($element->getDecorator('label')) {
                $element->getDecorator('label')->setOption('tag', null);
            }

            if ($element->getDecorator('htmlTag')) {
                $element->removeDecorator('htmlTag');
            }
            // Remove the fieldset and <dd>/<dt> decorators.
            $element->removeDecorator('Fieldset');
            $element->removeDecorator('DtDdWrapper');
        }
    }

}

