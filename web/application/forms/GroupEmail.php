<?php

class Application_Form_GroupEmail extends Zend_Form
{

    /**
     * Handle instantiation of the GroupEmail form with
     * the requisite validation rules.
     *
     * @param Zend_Config $options the configuration options
     *
     * @access public
     * @see Zend_Form::init()
     *
     * @return self
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $elements = array(
            $this->userType(),
            $this->onShift(),
            $this->typistShiftTimes(),
            $this->proofreaderShiftTimes(),
            $this->trainedIn(),
            $this->typistGrade(),
            $this->proofreaderGrade(),
            $this->extraWork(),
            $this->getMessageTitle(),
            $this->getMessageBody(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * User Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function userType()
    {
        $options['all']          = 'All';
        $options['proofreader'] = 'Proofreaders';
        $options['typist']       = 'Typists';

        $element = new Zend_Form_Element_MultiCheckbox('user_type[]');
        $element->setLabel('User Type')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addErrorMessage('Please select a user type to email')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setAttrib('class', 'usertype_select')
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Work Shift Type Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the shift type
     */
    private function onShift()
    {
        $options['all']               = 'All';
        $options['today']             = 'On Shift Today';
        $options['not_assigned_work'] = 'On Shift Not Assigned Work';
        $options['not_on_shift']      = 'Not On Shift';

        $element = new Zend_Form_Element_MultiCheckbox('on_shift[]');
        $element->setLabel('On Shift:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->setAttrib('class', 'onshift_select')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Typist shift times
     *
     * @return Zend_Form_Element_Select for the shift time
     */
    private function typistShiftTimes()
    {
        $options[''] = 'All';

        $todaysDate  = date('N', strtotime(date('Y-m-d')));
        $shiftMapper = new Application_Model_TypistsDefaultShiftMapper();
        $shiftTimes  = $shiftMapper->getShiftTimesForDropdown($todaysDate);
        $options     = array_merge($options, $shiftTimes);

        $element = new Zend_Form_Element_Select('typist_shift_time[]');
        $element->setLabel('Typist Shift Time:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->setAttrib('class', 'shift_time_select')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Proofreader shift times
     *
     * @return Zend_Form_Element_Select for the shift time
     */
    private function proofreaderShiftTimes()
    {
        $options[''] = 'All';

        $todaysDate  = date('N', strtotime(date('Y-m-d')));
        $shiftMapper = new Application_Model_ProofreadersDefaultShiftMapper();
        $shiftTimes  = $shiftMapper->getShiftTimesForDropdown($todaysDate);
        $options     = array_merge($options, $shiftTimes);

        $element = new Zend_Form_Element_Select('proofreader_shift_time[]');
        $element->setLabel('Proofreader Shift Time:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setAttrib('class', 'shift_time_select')
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Trained Bt
     *
     * @return Zend_Form_Element_MultiCheckbox for training
     */
    private function trainedIn()
    {
        $options['all']               = 'All';
        $options['trained_notes']     = 'Notes';
        $options['trained_summaries'] = 'Summaries';
        $options['trained_legal']     = 'Legal';
        $options['full']              = 'Full';
        $options['note_taker']        = 'Note Taking';

        $element = new Zend_Form_Element_MultiCheckbox('trained_in[]');
        $element->setLabel('Trained On:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Typist Grade
     *
     * @return Zend_Form_Element_MultiCheckbox for grade
     */
    private function typistGrade()
    {
        // Add a typist grade element
        $typistMapper     = new Application_Model_TypistMapper();
        $gradeData['all'] = 'All';
        $typistGrades     = $typistMapper->fetchAllGradesForDropdown();
        $options          = array_merge($gradeData, $typistGrades);

        $element = new Zend_Form_Element_MultiCheckbox('typist_grade[]');
        $element->setLabel('Typist Grade:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Proofreader Grade
     *
     * @return Zend_Form_Element_MultiCheckbox for grade
     */
    private function proofreaderGrade()
    {
        // Add a typist grade element
        $mapper           = new Application_Model_ProofreaderMapper();
        $gradeData['all'] = 'All';
        $typistGrades     = $mapper->fetchAllGradesForDropdown();
        $options          = array_merge($gradeData, $typistGrades);

        $element = new Zend_Form_Element_MultiCheckbox('proofreader_grade[]');
        $element->setLabel('Proofreader Grade:')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Email Message Title (subject)
     *
     * @return Zend_Form_Element_Text form field for full name
     */
    private function getMessageTitle()
    {
        $element = new Zend_Form_Element_Text('message_title');
        $element->setLabel('Title')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addErrorMessage('Please enter the email title')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->setAttrib('class', 'largeinput')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Extra Work
     *
     * @return Zend_Form_Element_MultiCheckbox for the shift type
     */
    private function extraWork()
    {
        $options['yes'] = 'Yes';
        $options['no']  = 'No';
        $element = new Zend_Form_Element_Radio('extra_work');
        $element->setLabel('Is this for extra work?')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setAttrib('class', 'extra-work')
            ->addMultiOptions($options)
            ->setValue('no')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Email Body Content
     *
     * @return Zend_Form_Element_Textarea for the email body
     */
    private function getMessageBody()
    {
        $element = new Zend_Form_Element_Textarea('message_body');
        $element->setLabel('Body')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addErrorMessage('Please enter the email body')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->setAttrib('class', 'largeinput')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Creates a submit form for posting the form
     *
     * @return Zend_Form_Element_Submit submit button/element
     */
    private function submit()
    {
        $element = new Zend_Form_Element_Submit('submit');
        $element->setLabel('Send Email')
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

