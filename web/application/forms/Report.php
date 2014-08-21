<?php

class Application_Form_Report extends Zend_Form
{

    /**
     * Handle instantiation of the TranscriptionType form with
     * the requisite validation rules.
     *
     * @param Zend_Config $options the configuration options
     *
     * @access public
     * @see Zend_Form::init()
     *
     * @return self
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $elements = array(
            $this->reportId(),
            $this->score(),
            $this->comment(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Turnaround Types
     *
     * @return Zend_Form_Element_Select turnaround times
     */
    private function reportId()
    {
        $element = new Zend_Form_Element_Hidden('id');
        return $element;
    }

    /**
     * Turnaround Types
     *
     * @return Zend_Form_Element_Select turnaround times
     */
    private function score()
    {
        $element = new Zend_Form_Element_Text('score');
        $element->setLabel('Score:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->setRequired(true)
            ->addErrorMessage('Please enter a score')
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
    private function comment()
    {
        $element = new Zend_Form_Element_Textarea('comment');
        $element->setLabel('Comment:')
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

