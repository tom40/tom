<?php

class Application_Form_AdminShiftBooker extends Zend_Form
{
    const TYPIST_SHIFT      = 'typist';
    const PROOFREADER_SHIFT = 'proofreader';

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
            $this->id(),
            $this->userType(),
            $this->shiftDate(),
            $this->weekView(),
            $this->shiftStatus(),
            $this->name(),
            $this->ability(),
            $this->_typistGrade(),
            $this->submit()
        );

        $this->removeDecorators($elements);
        $this->addElements($elements);
    }

    /**
     * ID
     *
     * @return Zend_Form_Element_Hidden the id
     */
    private function id()
    {
        $element = new Zend_Form_Element_Hidden('id');
        $element->addFilter('StringTrim');
        return $element;
    }

    /**
     * User Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function userType()
    {
        $options['typist']      = 'Typists';
        $options['proofreader'] = 'Proofreaders';

        $element = new Zend_Form_Element_Select('user_type');
        $element->setLabel('User Type')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * On Holiday flag
     *
     * @return Zend_Form_Element_Radio the id
     */
    private function shiftDate()
    {
        $element = new Zend_Form_Element_Text('shift_date');
        $element->setLabel('Shift Date')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * On Holiday flag
     *
     * @return Zend_Form_Element_Radio the id
     */
    private function shiftStatus()
    {
        $options[Application_Model_UsersShiftMapper::BOOKED_SHIFT_STATUS]  = 'Booked';
        $options[Application_Model_UsersShiftMapper::HOLIDAY_SHIFT_STATUS] = 'Holiday';
        $options['blank']   = 'Blank';

        $element = new Zend_Form_Element_MultiCheckbox('shift_status[]');
        $element->setLabel('Status')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Shift Time
     *
     * @return Zend_Form_Element_Select shift times
     */
    private function name()
    {
        $element = new Zend_Form_Element_Text('name');
        $element->setLabel('Name')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    private function weekView()
    {
        $element = new Zend_Form_Element_Checkbox('week_view');
        $element->setLabel('Week')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * User Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    private function ability()
    {
        $options['trained_summaries'] = 'Summaries';
        $options['trained_notes']     = 'Notes';
        $options['trained_legal']     = 'Legal';
        $options['full']              = 'Full';
        $options['note_taker']        = 'Note Taking';

        $element = new Zend_Form_Element_MultiCheckbox('ability[]');
        $element->setLabel('Trained In')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
            ->addFilter('StringTrim');
        return $element;
    }

    protected function _typistGrade()
    {
        $typistMapper = new Application_Model_TypistMapper;
        $grades       = $typistMapper->fetchGrades();
        $options      = array();

        foreach ($grades as $grade)
        {
            $options[$grade['id']] = $grade['name'];
        }
        $element = new Zend_Form_Element_MultiCheckbox('grade[]');
        $element->setLabel('Grade')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->addMultiOptions($options)
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
        $element->setLabel('Search')
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

