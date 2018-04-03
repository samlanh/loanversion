<?php

class Tellerandexchange_Form_FrmCurrencyExchange extends Zend_Dojo_Form
{
	
    public function FrmCurrencyExchange($data=null)
    {
        /* Form Elements & Other Definitions Here ... */
    	$db = new Tellerandexchange_Model_DbTable_DbSpread();
    	
    	$_in_cur_id = new Zend_Dojo_Form_Element_FilteringSelect('in_cur_id');
		$_in_cur_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'CheckCurrencyTO',
				'required' =>'true'
		));
		
		$rows = $db->getAllCurrencyType();
		$options='';
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['id']]=$row['curr_namekh'];
			}
		$_in_cur_id->setMultiOptions($options);
		
		$_out_cur_id = new Zend_Dojo_Form_Element_FilteringSelect('out_cur_id');
		$_out_cur_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'CheckCurrencyTO',
				'required' =>'true'
		));
		
		$rows = $db->getAllCurrencyType();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['curr_namekh'];
		}
		$_out_cur_id->setMultiOptions($options);
		
		
		$rate_in=new Zend_Dojo_Form_Element_NumberTextBox('rate_in');
		$rate_in->setAttribs(array('class'=>'fullside',
				'dojoType'=>'dijit.form.NumberTextBox','onKeyup'=>'calculateSpread();'));
		$rate_in->setValue(0);
		
		$spread=new Zend_Dojo_Form_Element_NumberTextBox('spread');
		$spread->setAttribs(array('class'=>'fullside',
				'dojoType'=>'dijit.form.NumberTextBox'));
		$spread->setValue(0);
		
    	$rate_out=new Zend_Dojo_Form_Element_NumberTextBox('rate_out');
    	$rate_out->setAttribs(array('class'=>'fullside',
    			'dojoType'=>'dijit.form.NumberTextBox','onKeyup'=>'calculateSpread();'));
    	$rate_out->setValue(0);
    	
    	$_active = new Zend_Dojo_Form_Element_FilteringSelect('active');
    	$_active ->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$options= array(1=>"ប្រើប្រាស់",0=>"មិនប្រើប្រាស់");
    	$_active->setMultiOptions($options);
    	if (!empty($data)){
    		$_in_cur_id->setValue($data['in_cur_id']);
    		$_out_cur_id->setValue($data['out_cur_id']);
    		$rate_in->setValue($data['rate_in']);
    		$spread->setValue($data['spread']);
    		$rate_out->setValue($data['rate_out']);
    		$_active->setValue($data['active']);
    	}
    	
		$this->addElements(array(
				$_in_cur_id,
				$_out_cur_id,
				$rate_in,
				$spread,
				$rate_out,
				$_active
				));
		return $this;
    }


}

