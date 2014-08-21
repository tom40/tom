<?php

class Application_Form_AdHocCharge extends Zend_Form
{
    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');
        $elements = array();
    	// Add the job_id hidden element
    	$element = new Zend_Form_Element_Hidden('job_id');
    	$elements[] = $element;

    	$element = new Zend_Form_Element_Text('price');
    	$element->setRequired(true)
	    	->addFilter('StripTags')
	    	->addFilter('StringTrim')
            ->addValidator('float', true, array('locale' => 'en_US'));
    	$elements[] = $element;

    	// Add a client comment element
    	$element = new Zend_Form_Element_Textarea('description');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('cols', '60')
            ->setAttrib('rows', '4');
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