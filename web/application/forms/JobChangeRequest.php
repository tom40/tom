<?php

class Application_Form_JobChangeRequest extends Zend_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('job_id');
        $elements[] = $element;
        
        // Add an change request element
    	$element = new Zend_Form_Element_Textarea('change_request');
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->setAttrib('cols', '60')
    	->setAttrib('rows', '10');
    	$elements[] = $element;
        
        foreach ($elements as $element) {
        	$element->removeDecorator('label')
        	->removeDecorator('htmlTag')
        	->removeDecorator('description');
        	 
        	$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
        	$element->setAttrib('style', 'width:99%');
        	
        }
        
        $this->addElements($elements);
        
    }


}

