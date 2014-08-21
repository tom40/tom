<?php

class Application_Form_ClientEdit extends Zend_Form
{

    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');

    	// Add the id hidden element
    	$element = new Zend_Form_Element_Hidden('id');
    	$elements[] = $element;

    	// Add the name element
    	$element = new Zend_Form_Element_Text('name');
    	$element->setRequired(true)
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', false, array(0, 50));
    	$elements[] = $element;

    	// Add the telephone element
    	$element = new Zend_Form_Element_Text('telephone');
    	$element
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', false, array(0, 50));
    	$elements[] = $element;

    	// Add the address element
    	$element = new Zend_Form_Element_Textarea('address');
    	$element
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->setAttrib('cols', '40')
    	->setAttrib('rows', '5');
    	$elements[] = $element;

    	// Add the postcode element
    	$element = new Zend_Form_Element_Text('postcode');
    	$element
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', false, array(0, 10))
    	->setAttrib('style', 'width:100px');
    	$elements[] = $element;

        $element = new Zend_Form_Element_Text('discount');
    	$element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 11))
            ->addValidator('Float')
            ->setAttrib('style', 'width:100px');
    	$elements[] = $element;

        $element = new Zend_Form_Element_Textarea('comments');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(0, 255))
            ->setAttrib('cols', '60')
            ->setAttrib('rows', '4');
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
