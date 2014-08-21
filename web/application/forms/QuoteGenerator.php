<?php

class Application_Form_QuoteGenerator extends Zend_Form
{
    public function init()
    {
        $elements = array();

        $valid = new Zend_Validate_GreaterThan(array('min' => 0));
        $valid->setMessage(
            'Please select an option', Zend_Validate_GreaterThan::NOT_GREATER
        );

        $element = new Zend_Form_Element_Select('turnaround_time_id');
        $element->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->addValidator($valid);
        $elements[] = $element;

        $what = array(
            array(
                'key'   => 'None',
                'value' => '-- select --'
            ),
            array(
                'key'                        => 'Market research',
                'value'                      => 'Market research',
            ),
            array(
                'key'                        => 'Conference',
                'value'                      => 'Conference',
            ),
            array(
                'key'                        => 'Lecture',
                'value'                      => 'Lecture',
            ),
            array(
                'key'                        => 'Television/Radio Interview',
                'value'                      => 'Television/Radio Interview',
            ),
            array(
                'key'                        => 'Investigation or hearing',
                'value'                      => 'Investigation or hearing',
            ),
            array(
                'key'                        => 'Corporate meeeting',
                'value'                      => 'Corporate meeeting',
            ),
            array(
                'key'                        => '1',
                'value'                      => 'Other - Please specifiy',
            ),
        );

        $element = new Zend_Form_Element_Select('what');
        $element->addMultiOptions($what)
            ->setRequired(true)
            ->setRegisterInArrayValidator(false);
        $elements[] = $element;


        $element = new Zend_Form_Element_Text('what_other');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim');
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('email');
        $element->setLabel('Email')
        	->addFilter('StripTags')
        	->addFilter('StringTrim')
        	->addValidator('StringLength', true, array(0, 100))
        	->addValidator('EmailAddress');
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('discount');
        $element->setLabel('Discount (%)')
        	->addFilter('StripTags')
        	->addFilter('StringTrim');

        $elements[] = $element;

        $element = new Zend_Form_Element_Select('service_id');
        $element->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->addValidator($valid);

        $elements[] = $element;

        $element = new Zend_Form_Element_Select('speaker_numbers_id');
        $element->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->addValidator($valid);

        $elements[] = $element;

        foreach ($elements as $element)
        {
            $element->removeDecorator('label')
                ->removeDecorator('htmlTag')
                ->removeDecorator('description');

            $element->setAttrib('class', 'text ui-widget-content ui-corner-all');
        }

        $this->addElements($elements);
    }

}

