<?php 
Class Pawnshop_Form_FrmPawnshop extends Zend_Dojo_Form {
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
		$loan_number = $db->getPawnshoNumber();
		$_loan_code->setValue($loan_number);
		
		
		$_loan_codes = new Zend_Dojo_Form_Element_TextBox('loan_codes');
		$_loan_codes->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		
		$_client_code = new Zend_Dojo_Form_Element_TextBox('client_code');
		$_client_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));

		$_client_codes = new Zend_Dojo_Form_Element_TextBox('client_codes');
		$_client_codes->setAttribs(array(
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
				'data-dojo-Type'=>'dijit.form.NumberTextBox',
				'data-dojo-props'=>"
				'required':true,
				'name':'interest_rate',
				'value':3,
				'class':'fullside',
				'invalidMessage':'អាចបញ្ជូលពី 1 ដល់'"));
				
		$_period = new Zend_Dojo_Form_Element_NumberTextBox('period');
		$_period->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'required' =>'true',
				'class'=>'fullside',
				'onkeyup'=>'calCulatePeriod();'
		));
		$_period->setValue(1);
		
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
		));
		
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
		$_payterm->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_payterm->setMultiOptions($term_opt);
		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				//'onchange'=>'changeCollectType();'
				'onchange'=>'calCulatePeriod();'
		));
		$_pay_every->setValue(3);
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
		
		$_repayment_method = new Zend_Dojo_Form_Element_FilteringSelect('repayment_method');
		$_repayment_method->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'chechPaymentMethod()'
		));
		$options = $db->getAllPaymentMethod(null,1);
		$_repayment_method->setMultiOptions($options);
		
		$account_type=new Zend_Dojo_Form_Element_FilteringSelect('product_id');
		$account_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'calCulatePeriod()'
		));
		$options=array();
		$rows = $db->getAllProduct();
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['id']]=$row['product_kh'];
		}
		$account_type->setMultiOptions($options);
		
		
		$_status = new Zend_Dojo_Form_Element_FilteringSelect('status_using');
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$options= array(1=>"Active",0=>"Cancel");
		$_status->setMultiOptions($options);
		
		$_interest = new Zend_Dojo_Form_Element_TextBox("interest");
		$_interest->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$_estimate = new Zend_Dojo_Form_Element_NumberTextBox("estimatevalue");
		$_estimate->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_estimate->setValue(0);
		
		$_display=  new Zend_Form_Element_Textarea('description');
		$_display->setAttribs(array(
				//'dojoType'=>'dijit.form.Textarea',
				'class'=>'fullside',
				'style'=>'height:100px !important;'));
		
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
		$_release_date = new Zend_Form_Element_Hidden("old_release_date");
		$_interest_rate = new Zend_Form_Element_Hidden("old_rate");
		$_old_payterm = new Zend_Form_Element_Hidden("old_payterm");
		
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
			$_loan_code->setValue($data['saving_number']);
// 			$_loan_codes->setValue($data['loan_number']);
			$_level->setValue($data['level']);
			$_releasedate->setValue($data['saving_date']);
			$_period->setValue($data['period']);
			$_amount->setValue($data['release_amount']);
			$_currency_type->setValue($data['currency_type']);
			$_rate->setValue($data['interest_rate']);//
			$_rate->setAttribs(array(
					'data-dojo-props'=>"
					'value':'".$data['interest_rate']."'"));
// 			$_repayment_method->setValue($data['payment_method']);
			$_dateline->setValue($data['saving_close']);
			$_pay_every->setValue($data['term_type']);
			$_id->setValue($data['id']);
			$_status->setValue($data['status']);
		}
		$this->addElements(array($_display,$_estimate,$_first_payment,$receipt_num,$withdrawal,$account_type,$_level,$_old_payterm,$_interest_rate,$_release_date,$_instalment_date,
				$_interest,
				$_client_codes,$_loan_codes,$_members,
				$_client_code,$_branch_id,$_currency_type,$_amount,$_rate,$_releasedate
				,$_payterm,$_status,$_period,$_repayment_method,$_pay_every,$_loan_code,
				$_dateline,$_id));
		return $this;
		
	}	
}