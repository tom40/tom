<?php

class Application_Form_AdditionalService extends Zend_Form
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
            $this->name(),
            $this->description(),
            $this->price(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Name
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function name()
    {
        $element = new Zend_Form_Element_Text('name');
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
     * @return Zend_Form_Element_Text
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
     * Price
     *
     * @return Zend_Form_Element_Text
     */
    private function price()
    {
        $element = new Zend_Form_Element_Text('price');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addValidator('float', true, array('locale' => 'en_US'))
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

