<?php

class Application_Form_ClientUserEdit extends Zend_Form
{

    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');
    	
    	// Add the id hidden element
    	$element = new Zend_Form_Element_Hidden('client_id');
    	$elements[] = $element;
    	
    	foreach ($elements as $element) {
    		$element->removeDecorator('label')
    		->removeDecorator('htmlTag')
    		->removeDecorator('description');
    		 
   			$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
    	}
    	
    	$this->addElements($elements);
    }
}

