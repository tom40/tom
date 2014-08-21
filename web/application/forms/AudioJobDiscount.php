<?php

class Application_Form_AudioJobDiscount extends Zend_Form
{

    /**
     * Form init function
     *
     * @return void
     */
    public function init()
    {
        $element = new Zend_Form_Element_Text('audio_job_discount');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 11))
            ->addValidator('Float')
            ->setAttrib('style', 'width:100px');

        $this->addElement( $element );
    }

}