<?php

class Application_Form_JobCreate extends Zend_Form
{

    public function init()
    {
    	// Set the method for the display form to POST
    	$this->setMethod('post');

    	// Add client_id element
    	$clientMapper = new Application_Model_ClientMapper();
    	$clients = $clientMapper->fetchAllForDropdown();

    	if (count($clients) > 0) {
    		array_unshift($clients, array('key' => '0', 'value' => '-- select --'));
    	}

    	$element = new Zend_Form_Element_Select('client_id');
    	$element->setRequired(true)
    	->addMultiOptions($clients)
    	->setAttrib('onchange', 'getClientUsers();return false;');
    	$elements[] = $element;

    	// Add the title element
    	$element = new Zend_Form_Element_Text('title');
    	$element->setRequired(true)
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', false, array(0, 255));
    	$elements[] = $element;

    	// Add primary_contact_id element
    	// Note: just add --select-- option at this stage - we will populate the list once the client is chosen
    	$contacts = array(array('key' => '0', 'value' => '-- select --'));
    	$element = new Zend_Form_Element_Select('primary_user_id');
        $element->setRequired(true);
    	$element->setRegisterInArrayValidator(false);
    	$element->addMultiOptions($contacts);
        $element->setAttrib('onchange', 'getUserColleagues();return false;');
    	$elements[] = $element;

    	// Add a start date element
    	$element = new Zend_Form_Element_Text('job_start_date');
    	$element->setRequired(true)
    	->addFilter('StripTags')
    	->addFilter('StringTrim');
    	$elements[] = $element;

    	// Add a due date element
    	$element = new Zend_Form_Element_Text('job_due_date');
    	$element->setRequired(true)
    	->addFilter('StripTags')
    	->addFilter('StringTrim');
    	$elements[] = $element;

    	// Add the PO number elements
    	$element = new Zend_Form_Element_Text('po_number');
        $element->setRequired(true);
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', false, array(0, 50));
    	$elements[] = $element;

    	// Add estimated number of audio files element
    	$element = new Zend_Form_Element_Text('estimated_audio_files');
    	$element->setRequired(true)
    	->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('Digits');
    	$elements[] = $element;

    	// Add an is_typist element
    	$element = new Zend_Form_Element_Checkbox('email_each_transcript_on_complete');
    	$element->setAttrib('style', 'width:20px');
        $element->setValue('1');
    	$elements[] = $element;

    	// Add the additional transcript recipients
    	$element = new Zend_Form_Element_Textarea('additional_transcript_recipients');
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->addValidator('StringLength', true, array(0, 500))
    	->setAttrib('cols', '60')
    	->setAttrib('rows', '4');

    	$element->addFilter(new App_Filter_CommaSpaceSeparated());
    	$element->addValidator(new App_Validate_EmailAddresses());
    	$elements[] = $element;

    	// Add a client comment element
    	$element = new Zend_Form_Element_Textarea('client_comments');
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->setAttrib('cols', '60')
    	->setAttrib('rows', '4');
    	$elements[] = $element;

    	// Add an internal comment element
    	$element = new Zend_Form_Element_Textarea('internal_comments');
    	$element->addFilter('StripTags')
    	->addFilter('StringTrim')
    	->setAttrib('cols', '60')
    	->setAttrib('rows', '4');
    	$elements[] = $element;

        $element = new Zend_Form_Element_Text('discount');
    	$element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 11))
            ->addValidator('Float')
            ->setAttrib('style', 'width:100px');
    	$elements[] = $element;

    	// Add audio quality element
    	$mapper = new Application_Model_AudioFileQualityMapper();
    	$items = $mapper->fetchAllForDropdown();

    	if (count($items) > 1) {
    		array_unshift($items, array('key' => '0', 'value' => '-- select --'));
    	}

    	$element = new Zend_Form_Element_Select('audio_quality_id');
    	$element->addMultiOptions($items)
    	->setRegisterInArrayValidator(false)
    	->setAttrib('onchange', 'checkAudioQualityWarnings();return false;');
    	$elements[] = $element;


    	foreach ($elements as $element) {
    		$element->removeDecorator('label')
    		->removeDecorator('htmlTag')
    		->removeDecorator('description');

   			$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
    	}

    	$this->addElements($elements);

    }

    public function isValid($data)
    {
    	$this->getElement('client_id')
	    ->addValidator(
	               'Db_RecordExists',
	    	false,
	    	array(
	                   'table'     => 'clients',
	                   'field'     => 'id',
	    	)
    	);

    	$this->getElement('primary_user_id')
    	->addValidator(
    		               'Db_RecordExists',
    	false,
    	array(
    		                   'table'     => 'users',
    		                   'field'     => 'id',
    	)
    	);

    	return parent::isValid($data);
    }


}

