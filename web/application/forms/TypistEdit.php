<?php

class Application_Form_TypistEdit extends Zend_Form
{

	public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');
        
        // Add the id hidden element
//         $element = new Zend_Form_Element_Hidden('id');
//         $elements[] = $element;
        
        // Add the typing_speed element
        $element = new Zend_Form_Element_Text('typing_speed');
        $element->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', false, array(0, 255));
        $elements[] = $element;

//         // Add the username element
//         $element = new Zend_Form_Element_Text('username');
//         $element->setRequired(true)
//         ->addFilter('StripTags')
//         ->addFilter('StringTrim')
//         ->addValidator('StringLength', false, array(0, 255));
//         $elements[] = $element;

//        	// Add an email element
//         $element = new Zend_Form_Element_Text('email');
//         $element->setLabel('email')
//         	->setRequired(true)
//         	->addFilter('StripTags')
//         	->addFilter('StringTrim')
//         	->addValidator('StringLength', true, array(0, 255))
//         	->addValidator('EmailAddress');
//         $elements[] = $element;
        
        // Add a grade element
        $typistMapper = new Application_Model_TypistMapper();
        $typistGrades = $typistMapper->fetchAllGradesForDropdown();
        
        $grades = new Zend_Form_Element_Select('grade_id');
        $grades->setRequired(true)
        	->addMultiOptions($typistGrades);
        $elements[] = $grades;
        
        foreach ($elements as $element) {
        	$element->removeDecorator('label')
        	->removeDecorator('htmlTag')
        	->removeDecorator('description');
        	
       		$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
        }
        
        $this->addElements($elements);
        
    }


}

