<?php

class Application_Form_AudioJobLinkCreate extends Zend_Form
{
    /**
     * Client ID
     * @var int
     */
    protected $_client;

    /**
     * Set client
     *
     * @param int $clientId Client ID
     *
     * @return void
     */
    public function setClient( $clientId )
    {
        $clientMapper = new Application_Model_ClientMapper();
        $this->_client = $clientMapper->fetchRow( $clientMapper->select()->where( 'id = ?' ), $clientId );
    }

    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');

    	// Add the job_id hidden element
    	$element = new Zend_Form_Element_Hidden('job_id');
    	$elements[] = $element;

    	// Add the file_name element
    	$element = new Zend_Form_Element_Text('file_name');
    	$element->setRequired(true)
	    	->addFilter('StripTags')
	    	->addFilter('StringTrim')
	    	->addValidator('StringLength', false, array(0, 255));
    	$elements[] = $element;

        $validator = new App_Validate_Uri;

    	// Add the link element
    	$element = new Zend_Form_Element_Text('link');
    	$element->setValue('http://')
    		->setRequired(true)
    		->addFilter('StripTags')
    		->addFilter('StringTrim')
    		->addValidator('StringLength', false, array(0, 255))
    		->addValidator($validator);
    	$elements[] = $element;

        // Add length hours element
        $element = new Zend_Form_Element_Text('length_hours');
        $element->setRequired(true)
            ->setValue('0')
	        ->addFilter('StripTags')
	        ->addFilter('StringTrim')
	        ->addValidator('StringLength', true, array(0, 255))
        	->addValidator('Int');
        $elements[] = $element;

        // Add length mins element
        $element = new Zend_Form_Element_Text('length_mins');
        $element->setRequired(true)
            ->setValue('0')
	        ->addFilter('StripTags')
	        ->addFilter('StringTrim')
	        ->addValidator('StringLength', true, array(0, 255))
        	->addValidator('Int');
        $elements[] = $element;

    	// set up standard gratethan zero validator
    	$valid  = new Zend_Validate_GreaterThan(array('min' => 0));
    	$valid->setMessage(
    	    'Please select an option',
    		Zend_Validate_GreaterThan::NOT_GREATER
    	);


    	// Add speakers numbers element

    	$element = new Zend_Form_Element_Select('speaker_numbers_id');
    	$element->setRequired(true)
    		->setRegisterInArrayValidator(false)
  			->addValidator($valid);
    	$elements[] = $element;

    	// Add audio quality element
    	$mapper = new Application_Model_AudioFileQualityMapper();
    	$items = $mapper->fetchAllForDropdown();

    	$element = new Zend_Form_Element_Select('audio_quality_id');
    		$element->addMultiOptions($items)
    		->setRequired(true)
    		->setRegisterInArrayValidator(false)
    		->setAttrib('onchange', 'checkAudioQualityWarnings();return false;')
  			->addValidator($valid);

    	$elements[] = $element;

    	// Add turnaround time element

    	$element = new Zend_Form_Element_Select('turnaround_time_id');
    	$element->setRequired(true)
    		->setRegisterInArrayValidator(false)
    		->setAttrib('onchange', 'checkTurnaroundTimeWarnings();return false;')
  			->addValidator($valid);
    	$elements[] = $element;


    	// Add transcription type element

    	$element = new Zend_Form_Element_Select('service_id');
    	$element->setRequired(true)
    		->setRegisterInArrayValidator(false)
  			->addValidator($valid);
    	$elements[] = $element;

    	// Add a client comment element
    	$element = new Zend_Form_Element_Textarea('client_comments');
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
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

