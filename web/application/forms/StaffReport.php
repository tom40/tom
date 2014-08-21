<?php
class Application_Form_StaffReport extends Zend_Form
{
    /**
     * Handle filter for the invoice export
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
            $this->startDate(),
            $this->endDate(),
            $this->client(),
            $this->audiojobStatus(),
            $this->_userType(),
            $this->_groupBy(),
            $this->submit()
        );

        $this->removeDecorators($elements);

        $this->addElements($elements);
    }

    /**
     * Start Date
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    private function startDate()
    {
        $element = new Zend_Form_Element_Text('date_start');
        $element->addFilter('StripTags')
            ->setAttrib('class', 'invoice-export-date')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * End Date
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    private function endDate()
    {
        $element = new Zend_Form_Element_Text('date_end');
        $element->addFilter('StripTags')
            ->setAttrib('class', 'invoice-export-date')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * Client
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    private function client()
    {
        $clientMapper  = new Application_Model_ClientMapper();
        $clients       = $clientMapper->fetchAllForDropdown();
        $defaultOption = array(
            'key'   => null,
            'value' => 'All',
            'show_warning' => 0
        );
        array_unshift($clients, $defaultOption);
        $element = new Zend_Form_Element_Select('client_id');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addMultiOptions($clients);
        return $element;
    }

    /**
     * Job Status
     *
     * @return Zend_Form_Element_MultiCheckbox
     */
    private function audiojobStatus()
    {
        $jobMapper     = new Application_Model_AudioJobMapper();
        $statuses     = $jobMapper->fetchAllStatusesForDropdown();
        $defaultOption = array(
            'key'   => null,
            'value' => 'All',
            'show_warning' => 0
        );
        array_unshift($statuses, $defaultOption);
        $element       = new Zend_Form_Element_Select('status_id');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addMultiOptions($statuses);
        return $element;
    }

    /**
     * Group by
     *
     * @return Zend_Form_Element_Radio
     */
    protected function _groupBy()
    {
        $options = array(
            'typists'      => 'Typists',
            'proofreaders' => 'Proofreaders'
        );

        $element = new Zend_Form_Element_Radio('group_by');
        $element->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setAttrib('class', 'extra-work')
            ->addMultiOptions($options)
            ->setValue('typists')
            ->addFilter('StringTrim');
        return $element;
    }

    /**
     * User Type
     *
     * @return Zend_Form_Element_MultiCheckbox for the user type
     */
    protected function _userType()
    {
        $options = array(
            'all'          => 'All',
            'typists'      => 'Typists',
            'proofreaders' => 'Proofreaders'
        );

        $element = new Zend_Form_Element_MultiCheckbox('user_type[]');
        $element->setLabel('User Type')
            ->addDecorator('Label', array('class' => 'reg_input_middle'))
            ->addErrorMessage('Please select a user type')
            ->setRequired(true)
            ->addFilter('StripTags')
            ->addValidator('NotEmpty', true)
            ->setAttrib('class', 'usertype_select')
            ->setValue(array('all', 'proofreaders', 'typists'))
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
        $element->setLabel('Download')
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