<?php 
Class Loan_Form_FrmLoan extends Zend_Dojo_Form {
	protected $tr;
public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddLoan($data=null){
		
		$_isgroup = new Zend_Dojo_Form_Element_CheckBox('is_group');
		$_isgroup->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				'class'=>'fullside',
		));
		
		
		$_loan_code = new Zend_Dojo_Form_Element_TextBox('loan_code');
		$_loan_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$loan_number = $db->getLoanNumber();
		$_loan_code->setValue($loan_number);
		
		$_loan_codes = new Zend_Dojo_Form_Element_TextBox('loan_codes');
		$_loan_codes->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'readonly'=>true,
				'class'=>'fullside',
				'style'=>'color:red; font-weight: bold;'
		));
// 		$loan_numbers = $db->getLoanNumber();
// 		$_loan_codes->setValue($loan_numbers);
		
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
		
		$_group_code = new Zend_Dojo_Form_Element_FilteringSelect('group_code');
		$_group_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'onchange'=>'getmemberIdGroup();',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$group_opt = $db ->getGroupCodeById(1,1,1);
// 		$_group_code->setMultiOptions($group_opt);
		
		$get_laonnumber = new Zend_Dojo_Form_Element_FilteringSelect('get_laonnumber');
		$get_laonnumber->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getInfoByLoanNumber();getLoanInfoByLoanNumber();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$group_opt = $db->getLoanAllLoanNumber(1,1);
		$get_laonnumber->setMultiOptions($group_opt);
		
		$schedule_opt = new Zend_Dojo_Form_Element_FilteringSelect('reschedule_opt');
		$schedule_opt->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$opt = $db->getVewOptoinTypeByType(25,1,null,1);
		$schedule_opt->setMultiOptions($opt);
		
		
		$dbs = new Loan_Model_DbTable_DbLoanIL();
		
		$_customer_code = new Zend_Dojo_Form_Element_FilteringSelect('customer_code');
		$_customer_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'showPopupclient;',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$group_opt = $dbs ->getClient(1);//code,individual,option
		$group_opt = $db->getGroupCodeById(1,0,1);//code,individual,option
		$_customer_code->setMultiOptions($group_opt);
		
		$_customer_codes = new Zend_Dojo_Form_Element_TextBox('customer_codes');
		$_customer_codes->setAttribs(array(
				'dojoType'=>'dijit.form.textbox',
				'class'=>'fullside',
				'onchange'=>'showPopupclient;'
		));
		// 		$group_opt = $dbs ->getClient(1);//code,individual,option
// 		$_member = new Zend_Dojo_Form_Element_FilteringSelect('member');
// 		$_member->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'onchange'=>'showPopupclient();'
// 		));
		
// 		$options = $db->getGroupCodeById(2,0,1);
// 		$_member->setMultiOptions($options);
		
		
		
		$_members = new Zend_Dojo_Form_Element_TextBox('members');
		$_members->setAttribs(array(
				'dojoType'=>'dijit.form.textbox',
				'class'=>'fullside',
				//'onchange'=>'showPopupclient();'
		));
		// 		$options = $dbs->getClient(2);
		
		
		$_groupid = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$_groupid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
 				'onchange'=>'popupCheckClient();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				));
// 		$options = $db->getGroupCodeById(2,1,1);//show name,show group,show option
// 		$_groupid->setMultiOptions($options);
		
		$_coid = new Zend_Dojo_Form_Element_FilteringSelect('co_id');
		$_coid->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$options = $db ->getAllCOName(1);
		$_coid->setMultiOptions($options);
		
		$_coids = new Zend_Dojo_Form_Element_FilteringSelect('co_ids');
		$_coids->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCO();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$optionss = $db ->getAllCOName(1);
		$_coids->setMultiOptions($optionss);
		
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$opt = array(2=>"Dollar",1=>'Khmer',3=>"Bath");
		$opt = $db->getVewOptoinTypeByType(15,1,3,1);
		$_currency_type->setMultiOptions($opt);
		$_currency_type->setValue(2);
		
		$_loan_type = new Zend_Dojo_Form_Element_FilteringSelect('loan_type');
		$_loan_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'loantype();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$opt = $db->getVewOptoinTypeByType(24,1,null,1);
		$opt[-1]="--- Add New---";
		$_loan_type->setMultiOptions($opt);
		
		$_loan_types = new Zend_Dojo_Form_Element_FilteringSelect('loan_types');
		$_loan_types->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onChange'=>'loantype();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$opts = $db->getVewOptoinTypeByType(24,1,null,1);
		$opt[-1]="--- Add New---";
		$_loan_types->setMultiOptions($opts);
		
		$_zone = new Zend_Dojo_Form_Element_FilteringSelect('zone');
		$_zone->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'zone();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$options = $db ->getZoneList(1);
		$options[-1]="--- Add New---";
		$_zone->setMultiOptions($options);
		
		$_zones = new Zend_Dojo_Form_Element_FilteringSelect('zones');
		$_zones->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'zone();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$optionsf = $db ->getZoneList(1);
		$optionsf[-1]="--- Add New---";
		$_zones->setMultiOptions($optionsf);
		
		$_loan_fee = new Zend_Dojo_Form_Element_NumberTextBox('loan_fee');
		$_loan_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$_loan_fee->setValue(0);
		
		$_loan_fees = new Zend_Dojo_Form_Element_NumberTextBox('loan_fees');
		$_loan_fees->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true
		));
		$_loan_fees->setValue(0);
		
		$_other_fee = new Zend_Dojo_Form_Element_NumberTextBox('other_fee');
		$_other_fee->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$_other_fee->setValue(0);
		
		$_other_fees = new Zend_Dojo_Form_Element_NumberTextBox('other_fees');
		$_other_fees->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required'=>true,
		));
		$_other_fees->setValue(0);
		
		$_time_collect = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect');
		$_time_collect->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'onkeyup'=>'getFirstPayment();'
		));
 		$_time_collect->setValue(1);
 		
 		$_time_collect_pri = new Zend_Dojo_Form_Element_NumberTextBox('amount_collect_pricipal');
 		$_time_collect_pri->setAttribs(array(
 				'dojoType'=>'dijit.form.NumberTextBox',
 				'class'=>'fullside',
 				'readonly'=>true,
 				'required'=>true
 		));
 		$_time_collect_pri->setValue(0);
 		
 		$dbs = new Loan_Model_DbTable_DbLoanss();
		$_amount = new Zend_Dojo_Form_Element_NumberTextBox('total_amount');
		$_amount->setAttribs(array(
						'dojoType'=>'dijit.form.NumberTextBox',
						'class'=>'fullside',
						'required' =>'true',
				        'onkeyup'=>'calCulateAdminFee();'
		));
		
		$_level = new Zend_Dojo_Form_Element_NumberTextBox('level');
		$_level->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_level->setValue(1);
		$_levels = new Zend_Dojo_Form_Element_NumberTextBox('levels');
		$_levels->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_levels->setValue(1);
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
// 		$_rate =  new Zend_Dojo_Form_Element_FilteringSelect("interest_rate");
// 		$_rate->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'autoComplete'=>"false",
// 				'queryExpr'=>'*${0}*',
// 				'invalidMessage'=>'អាចបញ្ជូលពី 1 ដល់'));
// 		$options = $db->getAllInterest(1);
// 		$_rate->setMultiOptions($options);
				
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
		//$_first_payment->setValue('2005-12-30');
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('date_line');
		$_dateline->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'readonly'=>true,
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		
		$_graice_pariod = new Zend_Dojo_Form_Element_TextBox('graice_pariod');
		$_graice_pariod->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'required'=>'true',
				'class'=>'fullside',
				'onKeyup'=>'CompareGraicePariod();'
				//'readOnly'=>true,
		));
		$_graice_pariod->setValue(0);
		
		$_collect_term = new Zend_Dojo_Form_Element_FilteringSelect('collect_termtype');
 		$_collect_term->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'changeGraicePeroid();',
 				'autoComplete'=>"false",
 				'queryExpr'=>'*${0}*',
		));
		$term_opt = $db->getVewOptoinTypeByType(14,1,3,1);
		$_collect_term->setMultiOptions($term_opt);
	
		$_payterm = new Zend_Dojo_Form_Element_FilteringSelect('payment_term');
		$_payterm->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$_payterm->setMultiOptions($term_opt);
		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'changeCollectType();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$options= array(1=>"Day",2=>"Week",3=>"Month");
// 		$_pay_every->setMultiOptions($options);
		$_pay_every->setValue(3);
		$_pay_every->setMultiOptions($term_opt);
		$_every_payamount = new Zend_Dojo_Form_Element_FilteringSelect('every_payamount');
		$_every_payamount->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$options= array(2=>"After",1=>"Before",3=>"Normal");
		$_every_payamount->setMultiOptions($options);
		
		$_time= new Zend_Dojo_Form_Element_TextBox('time');
		$_time->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$set_time='10:00-11:00 AM';
		$_time->setValue($set_time);
		
		$_paybefore = new Zend_Dojo_Form_Element_NumberTextBox('pay_before');
		$_paybefore->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'	
		));
		$_paybefore->setValue(0);
		
		$_pay_late = new Zend_Dojo_Form_Element_NumberTextBox('pay_late');
		$_pay_late->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		$_pay_late->setValue(0);
		$arr=$db->getSystemSetting('interest_late');
		$_pay_late->setValue($arr['value']);
		
// 		print_r($arr);exit();
		
		$_deposit = new Zend_Dojo_Form_Element_NumberTextBox('deposit');
		$_deposit->setAttribs(array(
				'dojoType'=>'dijit.form.NumberTextBox',
				'class'=>'fullside',
				 
		));
// 		$_deposit->setValue(0);
		
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
		
// 		$_branch_ids = new Zend_Dojo_Form_Element_FilteringSelect('branch_ids');
// 		$_branch_ids->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true',
// 				'onchange'=>'filterClient();'
// 		));
		
// 		$rows = $db->getAllBranchName();
// 		$optionsa=array(''=>'---Select Branch---');
// 		if(!empty($rows))foreach($rows AS $row){
// 			$optionsa[$row['br_id']]=$row['branch_namekh'];
// 		}
// 		$_branch_ids->setMultiOptions($optionsa);
		
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
// 		$options= array(1=>"Befor",2=>"After",3=>"Cancel");
		$options= array(1=>"Active",0=>"Cancel");
		$_status->setMultiOptions($options);
		
		$_interest = new Zend_Dojo_Form_Element_TextBox("interest");
		$_interest->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
		));
		
		$penalize = new Zend_Dojo_Form_Element_TextBox("penalize");
		$penalize->setAttribs(array(
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
			$_loan_codes->setValue($data['loan_number']);
			$_level->setValue($data['level']);
			$_loan_fee->setValue($data['admin_fee']);
			$_other_fee->setValue($data['other_fee']);
			$_group_code->setValue($data['customer_id']);
			$_customer_code->setValue($data['customer_id']);
			$_coid->setValue($data['co_id']);
			$_zone->setValue($data['zone_id']);
			$_releasedate->setValue($data['date_release']);
			$_period->setValue($data['total_duration']);
			$_first_payment->setValue($data['first_payment']);
			$_time->setValue($data['time_collect']);
			$_every_payamount->setValue($data['holiday']);
			$_amount->setValue($data['total_capital']);
			$_currency_type->setValue($data['currency_type']);
			$_rate->setValue($data['interest_rate']);//
			$_rate->setAttribs(array(
					'data-dojo-props'=>"
					'value':'".$data['interest_rate']."'"));
			$_repayment_method->setValue($data['payment_method']);
			$_graice_pariod->setValue($data['graice_period']);
			//$_time_collect_pri->setValue($data['semi']);
			$_dateline->setValue($data['date_line']);
			$_pay_every->setValue($data['pay_term']);
			$_time_collect->setValue($data['amount_collect_principal']);
			$_collect_term->setValue($data['collect_typeterm']);
			$_pay_late->setValue($data['holiday']);
			$_paybefore->setValue($data['holiday']);
			$_id->setValue($data['id']);
			//$_deposit->setValue($data['deposit']);
			$_group_code->setValue($data['customer_id']);
// 			$_groupid->setValue($data['client_id']);
			$get_laonnumber->setvalue($data['id']);
			$_status->setValue($data['status']);
			
			
// 			print_r($data);
		}
		$this->addElements(array($_deposit,$_groupid,$_old_payterm,$_interest_rate,$_release_date,$_instalment_date,$_interest,$penalize,$_service_charge,$schedule_opt,$_loan_types,$_loan_fees,$_other_fees,$_zones
				,$_client_codes,$_loan_codes,$_members,$_customer_codes,$_levels,$_coids,$get_laonnumber,$_loan_type,
				$_other_fee,$_isgroup,$_client_code,$_time_collect,$_loan_fee,$_level,$_paybefore,
				$_pay_late,$_branch_id,$_coid,$_currency_type,$_zone,$_amount,$_rate,$_releasedate
				,$_payterm,$_every_payamount,$_time,$_time_collect_pri,$_status,$_graice_pariod,$_period,
				$_first_payment,$_repayment_method,$_pay_every,$_loan_code,$_collect_term,$_dateline,
				$_group_code,$_customer_code,$_id));
		return $this;
		
	}	
}