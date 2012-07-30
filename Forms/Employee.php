<?php

/**
 * Application_Form_Employee form file.
* This is a sample generated files 
 */
class Application_Form_Employee extends Zend_Form
{

    public function __construct(Array $dataBusinessId, $options = null)
    {
        parent::__construct($options);
        
        $this->setName('frmEmployee');
        $this->setMethod('post');
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Employee name');
        $name->setAttrib('maxlength', 80);
        $name->setRequired(true);
        $name->addValidator(new Zend_Validate_NotEmpty());
        $this->addElement($name);
        
        $age = new Zend_Form_Element_Text('age');
        $age->setLabel('Employee age');
        $age->addValidator(new Zend_Validate_Int());
        $this->addElement($age);
        
        $businessId = new Zend_Form_Element_Select('business_id');
        $businessId->setLabel('Business');
        $businessId->setRequired(true);
        $businessId->addValidator(new Zend_Validate_NotEmpty());
        $businessId->addValidator(new Zend_Validate_Int());
        $businessId->addMultiOptions($dataBusinessId);
        $this->addElement($businessId);
        
        $submit = new Zend_Form_Element_Submit('bt_submit');
        $submit->setLabel('Save');
        $this->addElement($submit);
    }


}
