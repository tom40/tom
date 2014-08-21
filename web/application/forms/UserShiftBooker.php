<?php

class Application_Form_UserShiftBooker extends Zend_Form
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
            $this->onHoliday(),
            $this->shiftTime(),
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
     * On Holiday flag
     *
     * @return Zend_Form_Element_Radio the id
     */
    private function onHoliday()
    {
        $element = new Zend_Form_Element_Checkbox('on_holiday');
        $element->setLabel('On Holiday')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setValue('no')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Shift Time
     *
     * @return Zend_Form_Element_Select shift times
     */
    private function shiftTime()
    {
        $element = new Zend_Form_Element_MultiCheckbox('shift_time');
        $element->setLabel('Shift:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    public function populateShiftTime($selectedDate, $userType)
    {
        $dayName = date( "l", strtotime($selectedDate));
        $model   = Application_Model_DefaultShiftMapperFactory::getObject($userType);

        $options          = $model->fetchShifts($dayName, true);
        $shiftTimeElement = $this->getElement('shift_time');
        $shiftTimeElement->addMultiOptions($options);
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

