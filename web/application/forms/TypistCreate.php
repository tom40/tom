<?php

class Application_Form_TypistCreate extends Zend_Form
{

	public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
//         $element = new Zend_Form_Element_Hidden('id');
//         $elements[] = $element;

        // Add the typist_id hidden element
        $element = new Zend_Form_Element_Hidden('typist_id');
        $elements[] = $element;

        // Add the proofreader_id hidden element
        $element = new Zend_Form_Element_Hidden('proofreader_id');
        $elements[] = $element;

        // Add an is_typist element
        $element = new Zend_Form_Element_Checkbox('is_typist');
        $elements[] = $element;

        // Add an is_proofreader element
        $element = new Zend_Form_Element_Checkbox('is_proofreader');
        $elements[] = $element;

        // Add the typing_speed element
        $element = new Zend_Form_Element_Text('typing_speed');
        $element->addFilter('StripTags')
        	->addFilter('StringTrim')
        	->addValidator('StringLength', false, array(0, 255));
        $elements[] = $element;

		// Add a typist grade element
        $typistMapper = new Application_Model_TypistMapper();
        $typistGrades = $typistMapper->fetchAllGradesForDropdown();

        $grades = new Zend_Form_Element_Select('typist_grade_id');
        $grades->setRequired(true)
        	->addMultiOptions($typistGrades);
        $elements[] = $grades;

        $payrateMapper = new Application_Model_TypistPayrateMapper();
        $payrates      = $payrateMapper->getPayrateGradeArray();

        $payratesElement = new Zend_Form_Element_Select('typist_payrate_id');
        $payratesElement->setRequired(true)
            ->addMultiOptions($payrates);
        $elements[] = $payratesElement;

        // Add a trained in summaries element
        $element = new Zend_Form_Element_Checkbox('trained_summaries');
        $elements[] = $element;

        // Add a trained in notes element
        $element = new Zend_Form_Element_Checkbox('trained_notes');
        $elements[] = $element;

        // Add a trained in summaries element
        $element = new Zend_Form_Element_Checkbox('trained_legal');
        $elements[] = $element;

        $element = new Zend_Form_Element_Checkbox('full');
        $elements[] = $element;

        $element = new Zend_Form_Element_Checkbox('note_taker');
        $elements[] = $element;

        // Add the typing comments element
        $element = new Zend_Form_Element_Textarea('typing_comments');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
        	->addValidator('StringLength', true, array(0, 255))
        	->setAttrib('cols', '60')
    		->setAttrib('rows', '4');
        $elements[] = $element;

        // Add an is_proofreader element
        $element = new Zend_Form_Element_Checkbox('is_proofreader');
        $elements[] = $element;

        // Add a proofreader grade element
        $proofreaderMapper = new Application_Model_ProofreaderMapper();
        $proofreaderGrades = $proofreaderMapper->fetchAllGradesForDropdown();

        $grades = new Zend_Form_Element_Select('proofreader_grade_id');
        $grades->setRequired(true)
        	->addMultiOptions($proofreaderGrades);
        $elements[] = $grades;

        // Add the proofreader comments element
        $element = new Zend_Form_Element_Textarea('proofreading_comments');
        $element->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', true, array(0, 255))
        ->setAttrib('cols', '60')
        ->setAttrib('rows', '4');
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

