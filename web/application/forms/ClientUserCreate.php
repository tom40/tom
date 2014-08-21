<?php

class Application_Form_ClientUserCreate extends Zend_Form
{

    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');
    	
    	// Add client_id element
    	$clientMapper = new Application_Model_ClientMapper();
    	$clients = $clientMapper->fetchAllForDropdown();
    	$clientsValid = $clients;
    	array_unshift($clients, array('key' => '0', 'value' => '-- select --'));
    	
    	$element = new Zend_Form_Element_Select('client_id');
    	$element->setRequired(true)
    	->addMultiOptions($clients)
    	->addValidator('InArray', true, array(
			'messages' => array(
    			Zend_Validate_InArray::NOT_IN_ARRAY => 'Select a client',
    		),
    	    'haystack' => $clientsValid,
    		'recursive' => true
    	));
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

