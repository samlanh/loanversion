<?php 
Class Installment_Form_FrmClient extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmAddClient($data=null){
		$_spouse = new Zend_Dojo_Form_Element_TextBox('spouse');
		$_spouse->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_releted = new Zend_Dojo_Form_Element_TextBox('relate_with');
		$_releted->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$clienttype_nameen= new Zend_Dojo_Form_Element_DateTextBox('clienttype_nameen');
		$clienttype_nameen->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox','class'=>'fullside',
		));
		$clienttype_namekh= new Zend_Dojo_Form_Element_DateTextBox('clienttype_namekh');
		$clienttype_namekh->setAttribs(array('dojoType'=>'dijit.form.ValidationTextBox','required' =>'true',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",'class'=>'fullside'
		));
		$dob_join_acc= new Zend_Dojo_Form_Element_DateTextBox('dob_join_acc');
		$dob_join_acc->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
				'class'=>'fullside',
		));
		$_dob_Guarantor= new Zend_Dojo_Form_Element_DateTextBox('dob_guarantor');
		$_dob_Guarantor->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",'class'=>'fullside',
		));
		$_dob= new Zend_Dojo_Form_Element_DateTextBox('dob_client');
		$_dob->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",'class'=>'fullside',
		));
		
		$_guarantor_tel = new Zend_Dojo_Form_Element_TextBox('guarantor_tel');
		$_guarantor_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_guarantor_with = new Zend_Dojo_Form_Element_TextBox('guarantor_with');
		$_guarantor_with->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'Onchange'=>'getFunction();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options=array(''=>"---Select Branch Name---");
		if(!empty($rows))foreach($rows AS $row) $options[$row['br_id']]=$row['displayby']==1?$row['branch_namekh']:$row['branch_nameen'];
		$_branch_id->setMultiOptions($options);
	
		$_namekh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$_namekh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'));
		
 		$id_client = $db->countinstallmentClient();
		$_clientno = new Zend_Dojo_Form_Element_TextBox('client_no');
		$_clientno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'style'=>'color:red;'));
 		$_clientno->setValue($id_client);
	
		$_nameen = new Zend_Dojo_Form_Element_TextBox('name_en');
		$_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_join_nation_id = new Zend_Dojo_Form_Element_TextBox('join_nation_id');
		$_join_nation_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_sex = new Zend_Dojo_Form_Element_FilteringSelect('sex');
		$_sex->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(11,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_sex->setMultiOptions($opt_status);
		
		
		$_situ_status = new Zend_Dojo_Form_Element_FilteringSelect('situ_status');
		$_situ_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
		));
		$opt_status = $db->getVewOptoinTypeByType(5,1);
		unset($opt_status[-1]);
		unset($opt_status['']);
		$_situ_status->setMultiOptions($opt_status);
		
		$client_d_type = new Zend_Dojo_Form_Element_FilteringSelect('client_d_type');
		$client_d_type->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		$guarantor_address = new Zend_Dojo_Form_Element_TextBox('guarantor_address');
		$guarantor_address->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province');
		$_province->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'filterDistrict();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row){
			if($row['province_en_name']=="ភ្នំពេញ"){
				//exit();
				$_province->setValue($row['province_id']);
			}
			$options[$row['province_id']]=$row['province_en_name'];
		}
		$_province->setMultiOptions($options);
		
		$_house = new Zend_Dojo_Form_Element_TextBox('house');
		$_house->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
		));
		
		$_street = new Zend_Dojo_Form_Element_TextBox('street');
		$_street->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				//'required' =>'true'
		));
		
		$_id_no = new Zend_Dojo_Form_Element_TextBox('id_no');
		$_id_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		
		$_phone = new Zend_Dojo_Form_Element_TextBox('phone');
		$_phone->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$_spouse = new Zend_Dojo_Form_Element_TextBox('spouse');
		$_spouse->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		$photo=new Zend_Form_Element_File('photo');
		$photo->setAttribs(array(
		));
		$job = new Zend_Dojo_Form_Element_FilteringSelect('job');
		$job->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupJobOption();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$jobrs = $db->getJobName();
		$options=array(''=>"------Select------",-1=>"Add New");
		if(!empty($jobrs))foreach($jobrs AS $row) $options[$row['job']]=$row['job'];
		$job->setMultiOptions($options);
		
		$national_id=new Zend_Dojo_Form_Element_TextBox('national_id');
		$national_id->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				));
		
		$spouse_nationid=new Zend_Dojo_Form_Element_TextBox('spouse_nationid');
		$spouse_nationid->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		
		$_id = new Zend_Form_Element_Hidden("id");
		$_desc = new Zend_Dojo_Form_Element_TextBox('desc');
		$_desc->setAttribs(array('dojoType'=>'dijit.form.TextBox','class'=>'fullside',
				'style'=>'width:98%;min-height:50px;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
// 		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
// 			print_r($data);
			$_branch_id->setValue($data['branch_id']);
			$_namekh->setValue($data['name_kh']);
			$_nameen->setValue($data['name_en']);
			$_sex->setValue($data['sex']);
			$_situ_status->setValue($data['sit_status']);
			$_province->setValue($data['pro_id']);
			$_house->setValue($data['house']);
			$_street->setValue($data['street']);

			$_phone->setValue($data['phone']);
			$_spouse->setValue($data['guarantor_name']);
			$_desc->setValue($data['remark']);
			$_status->setValue($data['status']);
			$_clientno->setValue($data['client_number']);	
			$photo->setValue($data['photo_name']);
			$_id->setValue($data['client_id']);
			$job->setValue($data['job']);
			$national_id->setValue($data['nation_id']);
			$_guarantor_with->setValue($data['guarantor_with']);
			$_guarantor_tel->setValue($data['guarantor_tel']);
            $client_d_type->setValue($data['client_d_type']);
			$guarantor_address->setValue($data['guarantor_address']);

			$_dob_Guarantor->setValue($data['dob_guarantor']);
//			$dob_join_acc->setValue($data['dob_join_acc']);
			$spouse_nationid->setValue($data['guarantor_nationid']);
//			$_join_nation_id->setValue($data['join_nation_id']);
// 			$_releted->setValue($data['relate_with']);
// 			$_id_no->setValue($data['id_number']);
			$_dob->setValue($data['dob']);
		}
		$this->addElements(array($client_d_type,$guarantor_address,$_guarantor_tel,$_guarantor_with,$_releted,$_join_nation_id
				,$spouse_nationid,$_id,$photo,$_spouse,$job,$national_id,$_branch_id,$_namekh,$_nameen,$_sex,$_situ_status,
				$_province,$_house,$_street,$_id_no,
				$_phone,$_spouse,$_desc,$_status,$_clientno,$_dob,$dob_join_acc,$_dob_Guarantor,$clienttype_namekh,$clienttype_nameen));
		return $this;
		
	}	
}