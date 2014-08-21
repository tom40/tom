<?php

class Application_Form_ManageDefaultShift extends Zend_Form
{
    const TYPIST_SHIFT      = 'typist';
    const PROOFREADER_SHIFT = 'proofreader';

    /**
     * Handle instantiation of the TranscriptionType form with
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
            $this->id(),
            $this->userType(),
            $this->startDay(),
            $this->startTime(),
            $this->endDay(),
            $this->endTime(),
            $this->submit()
        );

        $this->removeDecorators($elements);
        $this->addElements($elements);
    }

    /**
     * ID
     *
     * @return Zend_Form_Element_Hidden the id
     */
    private function id()
    {
        $element = new Zend_Form_Element_Hidden('id');
        $element->addFilter('StringTrim');
        return $element;
    }

    /**
     * User Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function userType()
    {
        $options['typist']      = 'Typist';
        $options['proofreader'] = 'Proofreader';

        $element = new Zend_Form_Element_Select('user_type');
        $element->setLabel('User Type')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Get week days
     *
     * @return array
     */
    protected function _getWeekDays()
    {
        return array (
            1 => 'Monday',
            2 => 'Tuesday',
            3 => 'Wednesday',
            4 => 'Thursday',
            5 => 'Friday',
            6 => 'Saturday',
            7 => 'Sunday'
        );
    }

    /**
     * Start Day
     *
     * @return Zend_Form_Element_Select
     */
    private function startDay()
    {
        $options = $this->_getWeekDays();
        $element = new Zend_Form_Element_Select('start_day');
        $element->setLabel('Start Day')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * On Holiday flag
     *
     * @return Zend_Form_Element_Radio the id
     */
    private function startTime()
    {
        $element = new Zend_Form_Element_Text('start_time');
        $element->setLabel('Start Time')
            ->setRequired(true)
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');

        return $element;
    }

    /**
     * End Day
     *
     * @return Zend_Form_Element_Select
     */
    private function endDay()
    {
        $options = $this->_getWeekDays();
        $element = new Zend_Form_Element_Select('end_day');
        $element->setLabel('End Day')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * On Holiday flag
     *
     * @return Zend_Form_Element_Radio the id
     */
    private function endTime()
    {
        $element = new Zend_Form_Element_Text('end_time');
        $element->setLabel('End Time')
            ->setRequired(true)
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
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

