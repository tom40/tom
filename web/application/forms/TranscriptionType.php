<?php

class Application_Form_TranscriptionType extends Zend_Form
{

    /**
     * Handle instantiation of the TranscriptionType form with
     * the requisite validation rules.
     *
     * @param Zend_Config $options the configuration options
     *
     * @access public
     * @see Zend_Form::init()
     *
     * @return void
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $elements = array(
            $this->_transcriptionType(),
            $this->_transcriptionName(),
            $this->_transcriptionDescription(),
            $this->_trainingCode(),
            $this->_turnAroundTimes(),
            $this->_turnaroundTime(),
            $this->_submit()
        );

        $payrateElements = $this->_payrates();
        $elements = array_merge($elements, $payrateElements);

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Transcription Type
     *
     * @return Zend_Form_Element_Hidden the id
     */
    protected function _transcriptionType()
    {
        $element = new Zend_Form_Element_Hidden('id');
        $element->addFilter('StringTrim');
        return $element;
    }

    /**
     * Transcription type name
     *
     * @return Zend_Form_Element_Text
     */
    protected function _transcriptionName()
    {
        $element = new Zend_Form_Element_Text('name');
        $element->addFilter('StripTags')
            ->setRequired()
            ->setLabel('Name')
        	->addFilter('StringTrim')
        	->addValidator('StringLength', false, array(0, 255));
        return $element;
    }

    /**
     * Transcription type name
     *
     * @return Zend_Form_Element_Text
     */
    protected function _transcriptionDescription()
    {
        $element = new Zend_Form_Element_Textarea('description');
        $element->addFilter('StripTags')
            ->setLabel('Description')
        	->addFilter('StringTrim');
        return $element;
    }

    /**
     * Trained Bt
     *
     * @return Zend_Form_Element_MultiCheckbox for training
     */
    protected function _turnAroundTimes()
    {
        $taMapper = new Application_Model_TurnaroundTimeMapper;

        $options = array();
        $turnArounds = $taMapper->fetchAll();
        foreach ($turnArounds as $turnAround)
        {
            $options[$turnAround['id']] = $turnAround['name'];
        }

        $element = new Zend_Form_Element_MultiCheckbox('turnaround_times[]');
        $element->setLabel('Turnaround Times:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     *
     */
    protected function _payrates()
    {
        $payrateMapper = new Application_Model_TypistPayrateMapper();
        $payrates      = $payrateMapper->getPayrateGradeArray();

        $validate = new Zend_Validate_Digits();
        $validate->setMessage('Please enter a whole number');

        $elements = array();
        foreach ($payrates as $payrateId => $payrateName)
        {
            $element = new Zend_Form_Element_Text('payrate' . $payrateId);
            $element->addFilter('StripTags')
                ->setLabel($payrateName)
                ->addFilter('StringTrim')
                ->addValidator($validate);
            $elements[] = $element;
        }
        return $elements;
    }

    /**
     * Turnaround Types
     *
     * @return Zend_Form_Element_Select turnaround times
     */
    protected function _turnaroundTime()
    {
        $mapper = new Application_Model_TurnaroundTimeMapper();
    	$options = $mapper->fetchAllForDropdown();
        $element = new Zend_Form_Element_Select('turnaround_id');
        $element->setLabel('Default Turnaround Time:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Training code
     *
     * @return Zend_Form_Element_Text
     */
    protected function _trainingCode()
    {
        $abilityCodes = array_values( Application_Model_TypistMapper::abilitiesMap() );

        $element = new Zend_Form_Element_Text('training_code');
        $element->addFilter('StripTags')
            ->setLabel('Training Code')
            ->addFilter('StringTrim')
            ->addValidator(
                'InArray',
                true,
                array(
                'messages' => array(
                    Zend_Validate_InArray::NOT_IN_ARRAY => 'Please choose a valid training code (' . implode( ',', $abilityCodes ) . ').' ,
                ),
                'haystack' => $abilityCodes,
                'recursive' => true
            ));
        return $element;
    }

    /**
     * Creates a submit form for posting the form
     *
     * @return Zend_Form_Element_Submit submit button/element
     */
    protected function _submit()
    {
        $element = new Zend_Form_Element_Submit('submit');
        $element->setLabel('Save')
            ->setAttrib('class', 'reg_input_btn');

        return $element;
    }

    /**
     * Loops over all of the elements and removes the decorators.
     *
     * @param array &$elements form elements
     *
     * @return void
     */
    private function removeDecorators(&$elements)
    {
        foreach ($elements as $element) {
            if ($element->getDecorator('label')) {
                $element->getDecorator('label')->setOption('tag', null);
            }

            if ($element->getDecorator('htmlTag')) {
                $element->removeDecorator('htmlTag');
            }
            // Remove the fieldset and <dd>/<dt> decorators.
            $element->removeDecorator('Fieldset');
            $element->removeDecorator('DtDdWrapper');
        }
    }

}

