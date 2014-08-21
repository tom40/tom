<?php
class Application_Form_AssignMultipleTypist extends Zend_Form
{
    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');
    	
    	// Add the id hidden element
    	//$element = new Zend_Form_Element_Hidden('audio_job_id');
    	//$elements[] = $element;
    	
    	// Add the id hidden element
    	$element = new Zend_Form_Element_Hidden('id');
    	$elements[] = $element;
    	
//     	// Add a typist element
//     	$userMapper = new Application_Model_UserMapper();
//     	$users = $userMapper->fetchAllForDropdown(2);
//     	array_unshift($users, array('key' => '0', 'value' => '-- Not assigned --'));
    	
//     	$typistId = new Zend_Form_Element_Select('user_id');
//     	$typistId->setRequired(true)
//     	->addMultiOptions($users);
//     	$elements[] = $typistId;
    	
    	// Add a due date element
//     	$element = new Zend_Form_Element_Text('due_date');
//     	$element->setRequired(true)
//     	->addFilter('StripTags')
//     	->addFilter('StringTrim');
//     	$elements[] = $element;
    	
    	foreach ($elements as $element) {
    		$element->removeDecorator('label')
    		->removeDecorator('htmlTag')
    		->removeDecorator('description');
    		 
   			$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
    	}
    	
    	$this->addElements($elements);
    }
}

