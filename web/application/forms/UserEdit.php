<?php

class Application_Form_UserEdit extends Zend_Form
{
    protected $_userId;

    public function __construct($userId)
    {
        $this->_userId = $userId;
    }

	public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('id');
        $elements[] = $element;

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('acl_group_id');
        $elements[] = $element;

        // Add the name element
        $element = new Zend_Form_Element_Text('name');
        //$where = array('users', 'name', $where);

        $element->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', false, array(0, 50));
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('sort_code');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 50));
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('acc_no');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 50));
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('acc_name');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 255));
        $elements[] = $element;

        $element = new Zend_Form_Element_Text('utr_no');

        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', false, array(0, 255));
        $elements[] = $element;


        $element = new Zend_Form_Element_Textarea('address');
        $element->addFilter('StripTags')
            ->addFilter('StringTrim')
            ->addValidator('StringLength', true, array(0, 255))
            ->setAttrib('cols', '60')
            ->setAttrib('rows', '4');
        $elements[] = $element;

        // Add the username element
        $element = new Zend_Form_Element_Text('username');
        $element->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', false, array(8, 50))
        ->addValidator(
            'Db_NoRecordExists',
        	false,
        	array(
            	'table'     => 'users',
                'field'     => 'username',
                'exclude' => array(
        			'field' => 'id',
        		    'value' => $this->_userId
        		),
                'messages'	=> 'Username already exists'
        	)
        );
        $elements[] = $element;

        // Add the password element
        $element = new Zend_Form_Element_Password('password');
        $element->setRequired(true)
                ->addFilter('StripTags')
                ->addFilter('StringTrim')
                ->addValidator(new App_Validate_Password);
        $elements[] = $element;

        // Add the password confirmation element
        $element = new Zend_Form_Element_Password('password_confirm');
        $element->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('Identical', false, array('token' => 'password', 'messages' => 'Password fields do not match'));
        $elements[] = $element;

        $element = new Zend_Form_Element_Checkbox( 'phrase_opt_in' );
        $element->addFilter('StripTags')
            ->addFilter('StringTrim');
        $elements[] = $element;

       	// Add an email element
        $element = new Zend_Form_Element_Text('email');
        $element
        	->setRequired(true)
        	->addFilter('StripTags')
        	->addFilter('StringTrim')
        	->addValidator('StringLength', true, array(0, 100))
        	->addValidator('EmailAddress')
        	->addValidator(
        		'Db_NoRecordExists',
        		false,
        		array(
                   	'table'     => 'users',
                    'field'     => 'email',
                    'exclude' => array(
                		'field' => 'id',
                	    'value' => $this->_userId
        			),
                    'messages'	=> 'Email address already exists'
        		)
        );
        $elements[] = $element;

        // Add an alternative email element
        $element = new Zend_Form_Element_Text('email_alternative');
        $element
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', true, array(0, 100))
        ->addValidator('EmailAddress');
        $elements[] = $element;

        // Add a landline element
        $element = new Zend_Form_Element_Text('landline');
        $element
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', true, array(0, 50))
        ->addValidator('Alnum', true, array('allowWhiteSpace' => true));
        $elements[] = $element;

        // Add a mobile element
        $element = new Zend_Form_Element_Text('mobile');
        $element
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', true, array(0, 50))
        ->addValidator('Alnum', true, array('allowWhiteSpace' => true));
        $elements[] = $element;

        $element = new Zend_Form_Element_Textarea('comments');
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

