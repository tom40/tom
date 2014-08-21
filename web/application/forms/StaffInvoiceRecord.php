<?php
/**
 * Created by JetBrains PhpStorm.
 * User: joemiddleton
 * Date: 16/09/2013
 * Time: 09:06
 * To change this template use File | Settings | File Templates.
 */

class Application_Form_StaffInvoiceRecord extends Zend_Form
{

    public function init()
    {
        $valid  = new Zend_Validate_GreaterThan(array('min' => 0));
        $valid->setMessage(
            'Please select an option',
            Zend_Validate_GreaterThan::NOT_GREATER
        );
        $elements = array();

        $element = new Zend_Form_Element_Text('name');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 255));

        $elements[] = $element;

        $element = new Zend_Form_Element_Text('minutes_worked');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Int');
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('pay_per_minute');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('Int');

        $elements[] = $element;

        $element = new Zend_Form_Element_Text( 'total' );

        $element->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' )
            ->addValidator( 'Float' );

        $elements[] = $element;

        $element = new Zend_Form_Element_Select('turnaround_time_id');
        $element->setRequired(true)
                ->setRegisterInArrayValidator(false)
                ->addValidator($valid);
        $elements[] = $element;

        $element = new Zend_Form_Element_Select('transcription_type_id');
        $element->setRegisterInArrayValidator(false);
        $elements[] = $element;

        $element = new Zend_Form_Element_Select('service_id');
        $element->setRegisterInArrayValidator(false);
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