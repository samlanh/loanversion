<?php 
Class Saving_Form_FrmLoan extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddLoan($data=null){
		$_loan_code = new Zend_Dojo_Form_Element_TextBox('loan_code');
		$_loan_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$loan_number = $db->getSavingNumber();
		$_loan_code->setValue($loan_number);
		
	
		$_client_code = new Zend_Dojo_Form_Element_TextBox('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		
		$dbs = new Loan_Model_DbTable_DbLoanIL();
		$_members = new Zend_Dojo_Form_Element_TextBox('members');
		$_members->setAttribs(array(
				'dojoType'=>'dijit.form.textbox',
				'class'=>'fullside',
		));
		
		$_level = new Zend_Dojo_Form_Element_NumberTextBox('level');
		$_level->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_level->setValue(1);
		
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = $db->getVewOptoinTypeByType(15,1,3,1);
		$_currency_type->setMultiOptions($opt);
		$_currency_type->setValue(2);
		
 		$dbs = new Loan_Model_DbTable_DbLoanss();
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
				        'onkeyup'=>'calCulateAdminFee();'
		));
		
		$_rate =  new Zend_Dojo_Form_Element_NumberTextBox("interest_rate");
		$_rate->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				));
		$_rate->setValue(2);
				
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onkeyup'=>'calCulatePeriod();'
		));
		$_period->setValue(12);
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('release_date');
		$_releasedate->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'checkReleaseDate();'
		));
		$s_date = date('Y-m-d');
		$_releasedate->setValue($s_date);
		
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('date_line');
		$_dateline->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>true,
		));
		
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
// 		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
// 		$_payterm->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$_payterm->setMultiOptions($term_opt);

		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'changeCollectType();'
		));
		$_pay_every->setValue(3);
		unset($term_opt[1]);unset($term_opt[2]);
		$_pay_every->setMultiOptions($term_opt);
		
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();'
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>'---Select Branch---');
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['br_id']]=$row['branch_namekh'];
			}
		$_branch_id->setMultiOptions($options);
		
		
		$account_type=new Zend_Dojo_Form_Element_FilteringSelect('account_type');
		$account_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'calCulatePeriod()'
		));
		$options = array(1=>"Hi-Growth Fixed Deposit",2=>"Hi-Income Fixed Deposit");
		$account_type->setMultiOptions($options);
		
		
		$withdrawal = new Zend_Dojo_Form_Element_TextBox("withdrawal");
		$withdrawal->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$receipt_num = new Zend_Dojo_Form_Element_TextBox("receipt_num");
		$receipt_num->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_first_payment = new Zend_Dojo_Form_Element_DateTextBox('first_payment');
		$_first_payment->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'calCulateEndDate();'
		
		));
		
		$_instalment_date = new Zend_Form_Element_Hidden("instalment_date");
// 		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
// 		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
// 		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_loan_code->setValue($data['saving_number']);
			$_level->setValue($data['level']);
			$receipt_num->setValue($data["reciept_no"]);
			$_amount->setValue($data['deposit_amount']);
			$_currency_type->setValue($data['currency_type']);
			$account_type->setValue($data['saving_method']);
			
			$_pay_every->setValue($data['term_type']);
			
			
			$_period->setValue($data['withdrawing']);
			$_rate->setValue($data['interest_rate']);//
			$_releasedate->setValue($data['saving_date']);
// 			$_rate->setAttribs(array(
// 					'data-dojo-props'=>"
// 					'value':'".$data['interest_rate']."'"));
			$_dateline->setValue($data['saving_close']);
			
// 			$_id->setValue($data['g_id']);
		}
		$this->addElements(array($_first_payment,$receipt_num,$withdrawal,$account_type,$_level,$_instalment_date,
			$_members,$_client_code
			,$_branch_id,$_currency_type,$_amount,$_rate,$_releasedate,$_period,
			$_pay_every,$_loan_code,$_dateline
			,$_id));
		return $this;
		
	}	
}