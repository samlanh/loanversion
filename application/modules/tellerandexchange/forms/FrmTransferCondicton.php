<?php

class Tellerandexchange_Form_FrmTransferCondicton extends Zend_Dojo_Form
{
	
    public function FrmTransferCondicton($data=null)
    {
    	$request=Zend_Controller_Front::getInstance()->getRequest();
        /* Form Elements & Other Definitions Here ... */
    	$db = new Tellerandexchange_Model_DbTable_DbSpread();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$_currency_id = new Zend_Dojo_Form_Element_FilteringSelect('currency_id');
		$_currency_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$rows = $db->getAllCurrencyType();
		$options='';
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['id']]=$row['curr_namekh'];
			}
		$_currency_id->setMultiOptions($options);
		
		
		
		$fromAmount=new Zend_Dojo_Form_Element_NumberTextBox('fromAmount');
		$fromAmount->setAttribs(array('class'=>'fullside','required' =>'true',
				'dojoType'=>'dijit.form.NumberTextBox',));
		$fromAmount->setValue(0);
		
		$toAmount=new Zend_Dojo_Form_Element_NumberTextBox('toAmount');
		$toAmount->setAttribs(array('class'=>'fullside','required' =>'true',
				'dojoType'=>'dijit.form.NumberTextBox'));
		$toAmount->setValue(0);
		
    	$totalFee=new Zend_Dojo_Form_Element_NumberTextBox('totalFee');
    	$totalFee->setAttribs(array('class'=>'fullside',
    			'dojoType'=>'dijit.form.NumberTextBox',));
    	$totalFee->setValue(0);
    	
    	$commisionFee=new Zend_Dojo_Form_Element_NumberTextBox('commisionFee');
    	$commisionFee->setAttribs(array('class'=>'fullside',
    			'dojoType'=>'dijit.form.NumberTextBox',));
    	$commisionFee->setValue(0);
    	
    	$note = new Zend_Dojo_Form_Element_Textarea("note");
    	$note->setAttribs(array(
    			'dojoType'=>'dijit.form.Textarea',
    			'class'=>'fullside',
    			'style'=>'width:99%;min-height:60px; font-size:inherit; font-family:inherit'
    	));
    	
    	$_status = new Zend_Dojo_Form_Element_FilteringSelect('status');
    	$_status ->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$options= array(1=>$tr->translate("ACTIVE"),0=>$tr->translate("DEACTIVE"));
    	$_status->setMultiOptions($options);
    	
    	$_currencySearch = new Zend_Dojo_Form_Element_FilteringSelect('currencySearch');
    	$_currencySearch->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true'
    	));
    	
    	$rows = $db->getAllCurrencyType();
    	$optionssd['-1']=$tr->translate("Choose Currency");
    	if(!empty($rows))foreach($rows AS $row){
    		$optionssd[$row['id']]=$row['curr_namekh'];
    	}
    	$_currencySearch->setMultiOptions($optionssd);
    	$_currencySearch->setValue($request->getParam("currencySearch"));
    	if (!empty($data)){
    		$_currency_id->setValue($data['currency_id']);
    		$fromAmount->setValue($data['fromAmount']);
    		$toAmount->setValue($data['toAmount']);
    		$totalFee->setValue($data['totalFee']);
    		$commisionFee->setValue($data['commisionFee']);
    		$note->setValue($data['note']);
    		$_status->setValue($data['status']);
    	}
    	
		$this->addElements(array(
				$_currency_id,
				$fromAmount,
				$toAmount,
				$totalFee,
				$commisionFee,
				$note,
				$_status,
				$_currencySearch
				));
		return $this;
    }


}

