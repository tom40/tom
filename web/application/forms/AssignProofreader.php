<?php
class Application_Form_AssignProofreader extends Zend_Form
{
   /**
     * Handle instantiation of the GroupEmail form with
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
            $this->dueDay(),
            $this->dueTime(),
            $this->comment(),
            $this->search(),
            $this->shiftDay(),
            $this->shiftTime(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Comment
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function id()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Hidden('id');
    	$element->addFilter('StripTags')
                ->addFilter('StringTrim');
    	return $element;
    }

    /**
     * Due Date
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function dueDay()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Select('due_day');
    	$element->addFilter('StripTags')
                ->setAttrib('id', 'change-date-day')
                ->setAttrib('onchange', 'changeDate()')
                ->addFilter('StringTrim');
    	return $element;
    }

    /**
     * Due Date
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function dueTime()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Select('due_time');
    	$element->addFilter('StripTags')
                ->setAttrib('id', 'change-date-hour')
                ->setAttrib('onchange', 'changeDate()')
                ->addFilter('StringTrim');
    	return $element;
    }

    /**
     * Comment
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function comment()
    {
    	$element = new Zend_Form_Element_Textarea('comment');
    	$element->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator('StringLength', true, array(0, 255))
                ->setAttrib('cols', '40')
                ->setAttrib('rows', '4');
    	return $element;
    }

        /**
     * Comment
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function shiftDay()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Select('shift_day');
    	$element->addFilter('StripTags')
                ->addFilter('StringTrim');
    	return $element;
    }

    /**
     * Shift Time
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function shiftTime()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Select('shift_time');
    	$element->addFilter('StripTags')
                ->addFilter('StringTrim');
    	return $element;
    }

    /**
     * Comment
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function search()
    {
    	// Add a due date element
    	$element = new Zend_Form_Element_Text('search');
    	$element->addFilter('StripTags')
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
        $element->setLabel('Send Email')
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

