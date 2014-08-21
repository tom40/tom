<?php

class Application_Form_ResetPassword extends Zend_Form
{
    /**
     * Init method
     *
     * @return void
     */
    public function init()
    {
        $elements = array(
            $this->password(),
            $this->passwordConfirmation(),
            $this->passPhrase1(),
            $this->passPhrase2(),
            $this->passPhrase3(),
            $this->submit(),
        );
        $this->removeDecorators($elements);
        $this->addElements($elements);
    }

    /**
     * Creates a form field for the customer to enter there password
     *
     * @return Zend_Form_Element_Password password form field for the customers password
     */
    private function password()
    {
        $password = new Zend_Form_Element_Password('password');
        $password->setLabel('*Password')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->setAttrib('class', 'reg_input')
            ->addFilter('StringTrim')
            ->addValidator('NotEmpty', true)
            ->addValidator(new App_Validate_Password());
        return $password;
    }

    /**
     * Creates a form field for the user to confirm there password
     *
     * @return Zend_Form_Element_Password password confirmation form field
     */
    private function passwordConfirmation()
    {
        $passwordConfirmation = new Zend_Form_Element_Password('confirm_password');
        $passwordConfirmation->setLabel('*Confirm Password')
            ->setRequired(true)
            ->setAttrib('class', 'reg_input')
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator(new Zend_Validate_Identical(Zend_Controller_Front::getInstance()->getRequest()->getParam('password')));
        return $passwordConfirmation;
    }

    /**
     * Creates a form field for the user to confirm there password
     *
     * @return Zend_Form_Element_Password password confirmation form field
     */
    private function passPhrase1()
    {
        // Add the pass phrase element
        $element = new Zend_Form_Element_Password('pass_phrase_1');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
        return $element;
    }

    /**
     * Creates a form field for the user to confirm there password
     *
     * @return Zend_Form_Element_Password password confirmation form field
     */
    private function passPhrase2()
    {
        $element = new Zend_Form_Element_Password('pass_phrase_2');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
        return $element;
    }

    /**
     * Creates a form field for the user to confirm there password
     *
     * @return Zend_Form_Element_Password password confirmation form field
     */
    private function passPhrase3()
    {
        $element = new Zend_Form_Element_Password('pass_phrase_3');
        $element->addFilter('StripTags')
	        ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(3, 50))
            ->addValidator(new App_Validate_Passphrase);
        return $element;
    }

    /**
     * Creates a submit form for posting the form
     *
     * @return Zend_Form_Element_Submit submit button/element
     */
    private function submit()
    {
        $submit = new Zend_Form_Element_Submit('exsubmit');
        $submit->setLabel('Reset Password');
        $submit->class = 'submit-button';
        return $submit;
    }

    /**
     * Loops over all of the elements and removes the decorators.
     * @param array $elements form elements
     */
    private function removeDecorators(&$elements)
    {
        foreach ($elements as $element) {
        	$element->removeDecorator('label')
                    ->removeDecorator('htmlTag')
                    ->removeDecorator('description');

        	$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
        	$element->setAttrib('style', 'width:99%');
        }
    }


}

