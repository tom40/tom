<?php
class Application_Form_AssignMultipleProofreader extends Zend_Form
{
    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');

    	// Add the id hidden element
    	$element = new Zend_Form_Element_Hidden('id');
    	$elements[] = $element;

        $mapper = new Application_Model_AdditionalServicesMapper;
    	$items  = $mapper->fetchAllForDropdown();

    	if (count($items) > 1) {
    		array_unshift($items, array('key' => '0', 'value' => '-- select --'));
    	}

    	$element = new Zend_Form_Element_Select('speaker_numbers_id');
    	$element->addMultiOptions($items)
    		->setRequired(true)
    		->setRegisterInArrayValidator(false);
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

