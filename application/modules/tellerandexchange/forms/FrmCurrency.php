<?php

class Tellerandexchange_Form_FrmCurrency extends Zend_Dojo_Form
{
	
    public function FrmCurrency($data=null)
    {
        /* Form Elements & Other Definitions Here ... */
		
    	$curr_namekh=new Zend_Dojo_Form_Element_ValidationTextBox('curr_namekh');
    	$curr_namekh->setAttribs(array('class'=>'fullside',
    			'required'=>true,
    			'dojoType'=>'dijit.form.ValidationTextBox'));
    	$curr_nameen=new Zend_Dojo_Form_Element_ValidationTextBox('curr_nameen');
    	$curr_nameen->setAttribs(array('class'=>'fullside',
    			'required'=>true,
    			'dojoType'=>'dijit.form.ValidationTextBox'));
    	$symbol=new Zend_Dojo_Form_Element_ValidationTextBox('symbol');
    	$symbol->setAttribs(array('class'=>'fullside',
    			'required'=>true,
    			'dojoType'=>'dijit.form.ValidationTextBox'));
    	
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
    	$_status ->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
    	$_status->setMultiOptions($options);
    	if (!empty($data)){
    		$curr_namekh->setValue($data['curr_namekh']);
    		$curr_nameen->setValue($data['curr_nameen']);
    		$symbol->setValue($data['symbol']);
    		$_status->setValue($data['status']);
    	}
    	
		$this->addElements(array(
			$curr_namekh,
    		$curr_nameen,
    		$symbol,
    		$_status,
				));
		return $this;
    }


}

