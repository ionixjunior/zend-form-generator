<?php

/**
 * Application_Form_Business form file.
 */
class Application_Form_Business extends Zend_Form
{

    public function __construct($options = null)
    {
        parent::__construct($options);
        
        $this->setName('frmBusiness');
        $this->setMethod('post');
        
        $name = new Zend_Form_Element_Text('name');
        $name->setLabel('Company name');
        $name->setAttrib('maxlength', 100);
        $name->setRequired(true);
        $name->addValidator(new Zend_Validate_NotEmpty());
        $this->addElement($name);
        
        $submit = new Zend_Form_Element_Submit('bt_submit');
        $submit->setLabel('Save');
        $this->addElement($submit);
    }


}
