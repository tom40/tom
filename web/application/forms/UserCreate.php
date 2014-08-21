<?php

class Application_Form_UserCreate extends Zend_Form
{

	public function init()
    {
        // Set the method for the display form to POST
        $this->setMethod('post');

        // Add the id hidden element
        $element = new Zend_Form_Element_Hidden('id');
        $elements[] = $element;

        // Add the name element
        $element = new Zend_Form_Element_Text('name');
        $element->setRequired(true)
        ->addFilter('StripTags')
        ->addFilter('StringTrim')
        ->addValidator('StringLength', false, array(0, 50));
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

       	// Add an email element
        $element = new Zend_Form_Element_Text('email');
        $element->setLabel('email')
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

        // Add the acl group element
        $aclGroupMapper = new Application_Model_AclGroupMapper();
        $aclGroups = $aclGroupMapper->fetchAllForDropdown();
        // grab the valid values for use in validator later on
        $aclGroupsValid = $aclGroups;
        array_unshift($aclGroups, array('key' => '0', 'value' => '-- select --'));

        $aclGroup = new Zend_Form_Element_Select('acl_group_id');
        $aclGroup->setRequired(true)
        ->addMultiOptions($aclGroups)
        ->addValidator('InArray', true, array(
            'messages' => array(
                Zend_Validate_InArray::NOT_IN_ARRAY => 'Select one of the available type options',
             ),
        	'haystack' => $aclGroupsValid,
        	'recursive' => true));
        $elements[] = $aclGroup;

        // Add the general comments element
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

