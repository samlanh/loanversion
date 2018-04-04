<?php

class Tellerandexchange_Form_FrmCapitalAgent extends Zend_Dojo_Form
{
	
    public function FrmCapitalAgent($data=null)
    {
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
        /* Form Elements & Other Definitions Here ... */
    	$db = new Tellerandexchange_Model_DbTable_DbSpread();
    	$dbuser = new Application_Model_DbTable_DbUsers();
    	
    	$_currency = new Zend_Dojo_Form_Element_FilteringSelect('currency');
		$_currency->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'addRowCurrency();',
				'required' =>'true'
		));
		$rows = $db->getAllCurrencyType();
		$options='';
		$options['']=$tr->translate("Choose Currency");
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['id']]=$row['curr_namekh'];
			}
		$_currency->setMultiOptions($options);
		
		
		
		$_user = new Zend_Dojo_Form_Element_FilteringSelect('agent_id');
		$_user->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$rs = $dbuser->getUserListSelect();
		$options='';
		if(!empty($rs))foreach($rs AS $row){
			$options[$row['id']]=$row['name'];
		}
		$_user->setMultiOptions($options);
		
		$_for_date = new Zend_Dojo_Form_Element_DateTextBox('for_date');
		$_for_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
    	if (!empty($data)){
    		$_for_date->setValue(date("Y-m-d",strtotime($data['for_date'])));
    		$_user->setValue($data['agent_id']);
//     		$_currency->setValue($data['currency_id']);
    	}
    	
		$this->addElements(array(
				$_for_date,
				$_user,
				$_currency,
				));
		return $this;
    }


}

