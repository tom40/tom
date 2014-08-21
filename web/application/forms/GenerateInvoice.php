<?php
/**
 * Created by PhpStorm.
 * User: joemiddleton
 * Date: 09/12/2013
 * Time: 16:34
 */

class Application_Form_GenerateInvoice extends Zend_Form
{

    /**
     * Handle instantiation of the GroupEmail form with
     * the requisite validation rules.
     *
     * @param Zend_Config $options the configuration options
     *
     * @access public
     * @see    Zend_Form::init()
     *
     * @return self
     */
    public function __construct( $options = null )
    {
        parent::__construct( $options );

        $element = new Zend_Form_Element_Text( 'staff_user_search' );
        $element->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );
        $elements[] = $element;

        $element = new Zend_Form_Element_Radio( 'staff_user' );
        $element->setRegisterInArrayValidator( false )
            ->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );
        $elements[] = $element;

        $element = new Zend_Form_Element_Text( 'start_date' );
        $element->setRequired( true )
            ->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );
        $elements[] = $element;

        $element = new Zend_Form_Element_Text( 'end_date' );
        $element->setRequired( true )
            ->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );
        $elements[] = $element;

        $element = new Zend_Form_Element_Checkbox( 'empty_invoice' );
        $element->addFilter( 'StripTags' )
            ->addFilter( 'StringTrim' );
        $elements[] = $element;

        $this->removeDecorators($elements);
        $this->addElements($elements);
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