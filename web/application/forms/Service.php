<?php

/**
 * Created by PhpStorm.
 * User: joemiddleton
 * Date: 17/12/2013
 * Time: 15:56
 */

class Application_Form_Service extends Zend_Form
{

    /**
     * Service form
     *
     * @param Zend_Config $options the configuration options
     *
     * @return self
     */
    public function __construct($options = null)
    {
        parent::__construct($options);

        $elements = array(
            $this->_name(),
            $this->_description(),
            $this->_basePrice(),
            $this->_trainedIn(),
            $this->_typistGrade( 1 ),
            $this->_typistGrade( 2 ),
            $this->_typistGrade( 3 ),
            $this->_typistGrade( 4 ),
            $this->_typistGrade( 5 )
        );

        $this->removeDecorators($elements);
        $this->addElements($elements);
    }

    /**
     * Name form element
     *
     * @return Zend_Form_Element_Text
     */
    protected function _name()
    {
        $element = new Zend_Form_Element_Text( 'name' );

        $element->setRequired( true )
            ->addErrorMessage( 'Please enter a name' )
            ->addFilter( 'StripTags' )
            ->addValidator( 'NotEmpty', true )
            ->addFilter( 'StringTrim' );

        return $element;
    }

    /**
     * Description
     *
     * @return Zend_Form_Element_Textarea
     */
    protected function _description()
    {
        $element = new Zend_Form_Element_Textarea( 'description' );
        $element->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );

        return $element;
    }

    /**
     * Base price form element
     *
     * @return Zend_Form_Element_Text
     */
    protected function _basePrice()
    {
        $element = new Zend_Form_Element_Text( 'base_price' );

        $element->setRequired( true )
            ->addErrorMessage( 'Please enter a Base Price' )
            ->addFilter( 'StripTags' )
            ->addValidator( 'NotEmpty', true )
            ->addValidator( 'Float', true )
            ->addFilter( 'StringTrim' );

        return $element;
    }

    /**
     * Trained in Drop Down
     *
     * @return Zend_Form_Element_Select
     */
    protected function _trainedIn()
    {
        $options = array(
            ''   => '-- Select --',
            'S'  => 'Summaries',
            'N'  => 'Notes',
            'L'  => 'Legal',
            'F'  => 'Full',
            'NT' => 'Note Taking'
        );

        $element = new Zend_Form_Element_Select( 'training_code' );
        $element->addMultiOptions( $options )
            ->addFilter( 'StripTags' );

        return $element;
    }

    /**
     * Typist grade form element
     *
     * @param int $grade Grade number
     *
     * @return Zend_Form_Element_Text
     */
    protected function _typistGrade( $grade )
    {
        $element = new Zend_Form_Element_Text( 'typist_grade_' . $grade );

        $element->addFilter( 'StripTags' )
                ->addFilter( 'StringTrim' )
                ->addValidator( 'Float', true );

        return $element;
    }

    /**
     * Loops over all of the elements and removes the decorators.
     *
     * @param array &$elements form elements
     *
     * @return void
     */
    protected function removeDecorators(&$elements)
    {
        foreach ($elements as $element)
        {
            if ($element->getDecorator('label'))
            {
                $element->getDecorator('label')->setOption('tag', null);
            }

            if ($element->getDecorator('htmlTag'))
            {
                $element->removeDecorator('htmlTag');
            }

            $element->removeDecorator('Fieldset');
            $element->removeDecorator('DtDdWrapper');
        }
    }
} 