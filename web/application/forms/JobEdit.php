<?php

class Application_Form_JobEdit extends Zend_Form
{

	public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('id');
        $elements[] = $element;

        // Add client_id element
        $clientMapper = new Application_Model_ClientMapper();
        $clients = $clientMapper->fetchAllForDropdown();

        $element = new Zend_Form_Element_Select('client_id');
        $element->setRequired(true)
        ->addMultiOptions($clients)
        ->setAttrib('onchange', 'getClientUsers();return false;');
        $elements[] = $element;

        // Add a status element
        $jobMapper = new Application_Model_JobMapper();
        $jobStatuses = $jobMapper->fetchAllStatusesForDropdown();

        $status = new Zend_Form_Element_Select('status_id');
        $status->setRequired(true)
        	->addMultiOptions($jobStatuses);
        $elements[] = $status;

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

        $element = new Zend_Form_Element_Text('discount');
    	$element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 11))
            ->addValidator('Float')
            ->setAttrib('style', 'width:100px');
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
        $element->setRegisterInArrayValidator(false);
        $element->setAttrib('onchange', 'getUserColleagues();return false;');
        $element->addMultiOptions($contacts)
        ->addValidator(
        		               'Db_RecordExists',
    	false,
    	array(
        		                   'table'     => 'users',
        		                   'field'     => 'id',
        		                   'messages'	=> 'Please select a valid user'
    	)
    	);

        $elements[] = $element;


        // Add priority_id element
    	$prioritiesMapper = new Application_Model_PriorityMapper();
    	$jobPriorities = $prioritiesMapper->fetchAllForDropdown();

        $element = new Zend_Form_Element_Select('priority_id');
        $element->setRequired(true)
        ->addMultiOptions($jobPriorities);
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

        // Add an is_typist element
        $element = new Zend_Form_Element_Checkbox('email_each_transcript_on_complete');
        $element->setAttrib('style', 'width:20px');
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

        // Add speakers numbers element
        $mapper = new Application_Model_SpeakerNumbersMapper();
        $items = $mapper->fetchAllForDropdown();

        if (count($items) > 1) {
        	array_unshift($items, array('key' => '0', 'value' => '-- select --'));
        }

        $element = new Zend_Form_Element_Select('speaker_numbers_id');
        $element->addMultiOptions($items)
        ->setRegisterInArrayValidator(false);
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

        // Add turnaround time element
        $mapper = new Application_Model_TurnaroundTimeMapper();
        $items = $mapper->fetchAllForDropdown();

        if (count($items) > 1) {
        	array_unshift($items, array('key' => '0', 'value' => '-- select --'));
        }

        $element = new Zend_Form_Element_Select('turnaround_time_id');
        $element->addMultiOptions($items)
        ->setRegisterInArrayValidator(false);
        $elements[] = $element;


        // Add transcription type element
        $mapper = new Application_Model_TranscriptionTypeMapper();
        $items = $mapper->fetchAllForDropdown();

        if (count($items) > 1) {
        	array_unshift($items, array('key' => '0', 'value' => '-- select --'));
        }

        $element = new Zend_Form_Element_Select('transcription_type_id');
        $element->addMultiOptions($items)
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

