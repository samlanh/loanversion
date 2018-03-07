<?php 
Class Installment_Form_FrmLoan extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddLoan($data=null){
		
		
		$_loan_code = new Zend_Dojo_Form_Element_TextBox('loan_code');
		$_loan_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$loan_number = $db->getLoanNumber();
		$_loan_code->setValue($loan_number);
		
		$_client_code = new Zend_Dojo_Form_Element_TextBox('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_members = new Zend_Dojo_Form_Element_TextBox('members');
		$_members->setAttribs(array(
				'dojoType'=>'dijit.form.textbox',
				'class'=>'fullside',
		));
		
		$_groupid = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$_groupid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
 				'onchange'=>'popupCheckClient();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				));
		
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$opt = $db->getVewOptoinTypeByType(15,1,3,1);
		$_currency_type->setMultiOptions($opt);
		$_currency_type->setValue(2);
		
		$_loan_fee = new Zend_Dojo_Form_Element_NumberTextBox('loan_fee');
		$_loan_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$_loan_fee->setValue(0);
		
		$_other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$_other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$_other_fee->setValue(0);
		
		$_time_collect = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect');
		$_time_collect->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'getFirstPayment();'
		));
 		$_time_collect->setValue(1);
 		
 		$dbs = new Loan_Model_DbTable_DbLoanss();
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
				        'onkeyup'=>'calCulateAdminFee();'
		));
		
		$_principle_paid = new Zend_Dojo_Form_Element_NumberTextBox('principle_paid');
		$_principle_paid->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true',
				//'onkeyup'=>'calCulateAdminFee();'
		));
		
		$_date_payment = new Zend_Dojo_Form_Element_DateTextBox('date_payment');
		$_date_payment->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true',
				//'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$_date_payment->setValue(date("Y-m-d"));
		
		
		$_rate =  new Zend_Dojo_Form_Element_NumberTextBox("interest_rate");
		$_rate->setAttribs(array(
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'required':true,
				'name':'interest_rate',
				constraints:{pattern:'#,###.####'},
				'class':'fullside',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់'
				"));
				
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
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'onchange'=>'checkReleaseDate();'
		));
		$s_date = date('Y-m-d');
		
		$session_user=new Zend_Session_Namespace('authloan');
		if($session_user->level!=1){
			$_releasedate->setAttribs(array(
					'readonly'=>true,
			));
		}
		
		$_releasedate->setValue($s_date);
		
		$_first_payment = new Zend_Dojo_Form_Element_DateTextBox('first_payment');
		$_first_payment->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'required' =>'true',
				'class'=>'fullside',
			    'onchange'=>'calCulateEndDate();',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
				
		));
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('date_line');
		$_dateline->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
	
		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
		$_payterm->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$_payterm->setMultiOptions($term_opt);
		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'changeCollectType();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));

		$_deposit = new Zend_Dojo_Form_Element_NumberTextBox('deposit');
		$_deposit->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				 
		));
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'filterClient();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		$rows = $db->getAllBranchName();
		$options=array(''=>'---Select Branch---');
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['br_id']]=$row['branch_namekh'];
			}
		$_branch_id->setMultiOptions($options);
		
		
		$_repayment_method = new Zend_Dojo_Form_Element_FilteringSelect('repayment_method');
		$_repayment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'chechPaymentMethod()',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$options = $db->getAllPaymentMethod(null,1);
		$_repayment_method->setMultiOptions($options);
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status_using');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$options= array(1=>"Active",0=>"Cancel");
		$_status->setMultiOptions($options);
		
		$_interest = new Zend_Dojo_Form_Element_TextBox("interest");
		$_interest->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_service_charge = new Zend_Dojo_Form_Element_TextBox("service_charge");
		$_service_charge->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_instalment_date = new Zend_Form_Element_Hidden("instalment_date");
		
		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
		
		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
		
		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_loan_code->setValue($data['loan_number']);
			$_loan_fee->setValue($data['admin_fee']);
			$_other_fee->setValue($data['other_fee']);
			$_releasedate->setValue($data['date_release']);
			$_period->setValue($data['total_duration']);
			$_first_payment->setValue($data['first_payment']);
			$_amount->setValue($data['total_capital']);
			$_currency_type->setValue($data['currency_type']);
			$_rate->setValue($data['interest_rate']);//
			$_rate->setAttribs(array(
					'data-dojo-props'=>"
					'value':'".$data['interest_rate']."'"));
			$_repayment_method->setValue($data['payment_method']);
			$_dateline->setValue($data['date_line']);
			$_pay_every->setValue($data['pay_term']);
			$_time_collect->setValue($data['amount_collect_principal']);
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($_date_payment,$_principle_paid,$_deposit,$_groupid,$_old_payterm,
				$_interest_rate,$_release_date,$_instalment_date,$_interest
				,$_service_charge
				,$_members,
				$_other_fee,$_client_code,$_time_collect,$_loan_fee
				,$_branch_id,$_currency_type,$_amount,$_rate,$_releasedate,$_status,$_period,
				$_first_payment,$_repayment_method,$_pay_every,$_loan_code,$_dateline,
				$_id));
		return $this;
	}	
}