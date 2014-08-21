<?php

class Application_Form_AudioJobEdit extends Zend_Form
{
    /**
     * Client ID
     * @var int
     */
    protected $_clientId;

    /**
     * Audio job ID string
     * @var string
     */
    protected $_audioJobIds;

    public function __construct($clientId = null, $audioJobIds = null)
    {
        $this->_clientId    = $clientId;
        $this->_audioJobIds = $audioJobIds;
        parent::__construct();
    }

    public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('id');
        $element->setValue($this->_audioJobIds);

        $this->_sharedForm();

        if (count(explode('-', $this->_audioJobIds)) < 2)
        {
            $this->_singleForm();
        }

        $elements[] = $element;
        $this->_addElements($elements);
    }

    /**
     * Elements available to single and shared forms
     *
     * @return null
     */
    protected function _sharedForm()
    {
        // set up standard grater than zero validator
        $valid  = new Zend_Validate_GreaterThan(array('min' => 0));
        $valid->setMessage(
            'Please select an option',
            Zend_Validate_GreaterThan::NOT_GREATER
        );

        $element = new Zend_Form_Element_Select('speaker_numbers_id');
        $element->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->addValidator($valid);
        $elements[] = $element;

        // Add audio quality element
        $mapper = new Application_Model_AudioFileQualityMapper();
        $items = $mapper->fetchAllForDropdown();

        if (count($items) > 1) {
            array_unshift($items, array('key' => '0', 'value' => '-- select --'));
        }

        $element = new Zend_Form_Element_Select('audio_quality_id');
        $element->addMultiOptions($items)
            ->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->setAttrib('onchange', 'checkAudioQualityWarnings();return false;')
            ->addValidator($valid);

        $elements[] = $element;

        $element = new Zend_Form_Element_Select('turnaround_time_id');
        $element->setRequired(true)
            ->setRegisterInArrayValidator(false)
            ->addValidator($valid);
        $elements[] = $element;

        $element = new Zend_Form_Element_Select('transcription_type_id');
        $element->setRegisterInArrayValidator(false);
        $elements[] = $element;

        $element = new Zend_Form_Element_Select('service_id');
        $element->setRegisterInArrayValidator(false);
        $elements[] = $element;

        $this->_addElements($elements);
    }

    /**
     * Elements available to a single audio job only
     *
     * @return null
     */
    protected function _singleForm()
    {
        // Add file_name element
        $element = new Zend_Form_Element_Text('file_name');
        $element->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(0, 255));
        $elements[] = $element;

        // Add length hours element
        $element = new Zend_Form_Element_Text('length_hours');
        $element->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(0, 255))
            ->addValidator('Int');
        $elements[] = $element;

        // Add length mins element
        $element = new Zend_Form_Element_Text('length_mins');
        $element->setRequired(true)
            ->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(0, 255))
            ->addValidator('Int');
        $elements[] = $element;

        // Add a client comments element
        $element = new Zend_Form_Element_Textarea('client_comments');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('cols', '60')
            ->setAttrib('rows', '4');
        $elements[] = $element;

        // Add an internal comments element
        $element = new Zend_Form_Element_Textarea('internal_comments');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->setAttrib('cols', '60')
            ->setAttrib('rows', '4');
        $elements[] = $element;

        $this->_addElements($elements);
    }

    /**
     * Add elements to form
     *
     * @param array $elements Array of form elements
     *
     * @return null
     */
    protected function _addElements($elements)
    {
        foreach ($elements as $element)
        {
        	$element->removeDecorator('label')
        	    ->removeDecorator('htmlTag')
        	    ->removeDecorator('description');

       		$element->setAttrib('class', 'text ui-widget-content ui-corner-all');
        }

        $this->addElements($elements);
    }


}

