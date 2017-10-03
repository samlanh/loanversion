<?php 
Class Saving_Form_FrmSearch extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function AdvanceSearch($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
// 		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
// 		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect'));
// 		$_status_opt = array(
// 				-1=>$this->tr->translate("ALL"),
// 				1=>$this->tr->translate("ACTIVE"),
// 				0=>$this->tr->translate("DACTIVE"));
// 		$_status->setMultiOptions($_status_opt);
// 		$_status->setValue($request->getParam("status"));
		
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'onkeyup'=>'this.submit()',
				'class'=>'fullside',
				'placeholder'=>$this->tr->translate("ADVANCE_SEARCH")
		));
		$_title->setValue($request->getParam("adv_search"));
		
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch',
				'class'=>'fullside',
		));
				
// 		$_customer_code = new Zend_Dojo_Form_Element_FilteringSelect('customer_code');
// 		$_customer_code->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'onchange'=>'getmemberIdGroup();',
// 				'class'=>'fullside',
// 		));
// 		$group_opt = $db ->getGroupCodeById(1,0,1);//code,individual,option
// 		$_customer_code->setMultiOptions($group_opt);
		
// 		$_customer_code->setValue($request->getParam("customer_code"));
		
// 		$_member = new Zend_Dojo_Form_Element_FilteringSelect('member');
// 		$_member->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'onchange'=>'checkMember()',
// 				'class'=>'fullside',
// 		));
// 		$options = $db->getGroupCodeById(2,0,1);
// 		$_member->setMultiOptions($options);
// 		$_member->setValue($request->getParam("member"));
		
		$_currency_type = new Zend_Dojo_Form_Element_FilteringSelect('currency_type');
		$_currency_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt = array(-1=>"--Select Currency Type--",2=>"Dollar",1=>'Khmer',3=>"Bath");
		$_currency_type->setMultiOptions($opt);
		$_currency_type->setValue($request->getParam("currency_type"));
		
		
		
		$_releasedate = new Zend_Dojo_Form_Element_DateTextBox('start_date');
		$_releasedate->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'onchange'=>'CalculateDate();'));
		$_date = $request->getParam("start_date");
		
		if(empty($_date)){
			$_date = date('Y-m-d');
		}
		$_releasedate->setValue($_date);
		
		
		$_dateline = new Zend_Dojo_Form_Element_DateTextBox('end_date');
		$_dateline->setAttribs(array('dojoType'=>'dijit.form.DateTextBox','required'=>'true','class'=>'fullside',
// 				'class'=>'fullside',
		));
		$_date = $request->getParam("end_date");
		
		if(empty($_date)){
			$_date = date("Y-m-d");
		}
		$_dateline->setValue($_date);
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		
		$rows = $db->getAllBranchName();
		$options=array(-1=>'---Select Branch---');
			if(!empty($rows))foreach($rows AS $row){
				$options[$row['br_id']]=$row['branch_namekh'];
			}
		$_branch_id->setMultiOptions($options);
		$_branch_id->setValue($request->getParam("branch_id"));
		
		$_pay_every = new Zend_Dojo_Form_Element_FilteringSelect('pay_every');
		$_pay_every->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'changeCollectType();'
		));
		
		$term_opt = $db->getVewOptoinTypeByType(14,1,3);
		unset($term_opt[-1]);
		$_pay_every->setMultiOptions($term_opt);
// 		$_pay_every->setValue(3);
		$_pay_every->setValue($request->getParam('pay_every'));
		
		$client_name = new Zend_Dojo_Form_Element_FilteringSelect("client_name");
		$opt_client = array(''=>'ជ្រើសរើស ឈ្មោះអតិថិជន');
		$rows = $db->getAllClient();
		if(!empty($rows))foreach($rows AS $row){
			$opt_client[$row['id']]=$row['name'];
		}
		$client_name->setMultiOptions($opt_client);
		$client_name->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$client_name->setValue($request->getParam("client_name"));
		
		$account_type=new Zend_Dojo_Form_Element_FilteringSelect('account_type');
		$account_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required' =>'true',
				'class'=>'fullside',
				'onchange'=>'calCulatePeriod()'
		));
		$options = array(0=>"ជ្រើសរើស ប្រភេទ",1=>"Hi-Growth Fixed Deposit",2=>"Hi-Income Fixed Deposit");
		$account_type->setMultiOptions($options);
		$account_type->setValue($request->getParam("account_type"));
		
		if($data!=null){
			//print_r($data);
			$_branch_id->setValue($data['member_id']);
			$_member->setValue($data['client_id']);
			$_releasedate->setValue($data['date_release']);
			$_currency_type->setValue($data['payment_method']);
			$client_name->setValue($data['client_name']);
		}
		$this->addElements(array($account_type,$client_name,$_pay_every,$_title,$_branch_id,$_currency_type
				,$_releasedate,$_dateline,$_btn_search));
		return $this;
		
	}	
	
}