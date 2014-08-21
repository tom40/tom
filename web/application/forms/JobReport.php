<?php
class Application_Form_JobReport extends Zend_Form
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
            $this->jobStatus(),
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
    private function jobStatus()
    {
        $jobMapper     = new Application_Model_JobMapper();
        $statusses     = $jobMapper->fetchAllStatusesForDropdown();
        $defaultOption = array(
            'key'   => null,
            'value' => 'All',
            'show_warning' => 0
        );
        array_unshift($statusses, $defaultOption);
    	$element       = new Zend_Form_Element_Select('status_id');
    	$element->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addMultiOptions($statusses);
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

