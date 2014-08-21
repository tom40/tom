<?php

class Application_Form_Login extends Zend_Form
{

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the title element
        $element = new Zend_Form_Element_Text('username');
        $element->setRequired(true)
	        ->addFilter('StripTags')
	        ->addFilter('StringTrim')
        	->addFilter('StringToLower')
	        ->addValidator('StringLength', false, array(8, 50))
            ->addErrorMessage('Please enter your username');
        $elements[] = $element;

        // Add the title element
        $element = new Zend_Form_Element_Password('password');
        $element->setRequired(true)
	        ->addFilter('StripTags')
	        ->addFilter('StringTrim')
	        ->addValidator('StringLength', false, array(1,16))
            ->addErrorMessage('Please enter your password');
        $elements[] = $element;

        // Add the pass phrase element
        $element = new Zend_Form_Element_Password('pass_phrase_1');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
        $elements[] = $element;

        $element = new Zend_Form_Element_Password('pass_phrase_2');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
        $elements[] = $element;

        $element = new Zend_Form_Element_Password('pass_phrase_3');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
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

