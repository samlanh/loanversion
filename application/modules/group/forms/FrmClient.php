<?php 
Class Group_Form_FrmClient extends Zend_Dojo_Form {
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
		
		
		$_relate_tel = new Zend_Dojo_Form_Element_TextBox('relate_tel');
		$_relate_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
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
		
		$_group = new Zend_Dojo_Form_Element_CheckBox('is_group');
		$_group->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				'onClick'=>'getGroupCode();',
				));
		
		$_group_code = new Zend_Dojo_Form_Element_TextBox('group_code');
		$_group_code->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
		
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$id_client = $db->getNewClientId();
		
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
	
		
		
		$_member = new Zend_Dojo_Form_Element_FilteringSelect('group_id');
		$_member->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'getGroupCode();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getClientByType(1);
		//print_r($rows);exit();
		$options=array(''=>"---Select Group Name---");
		if(!empty($rows))foreach($rows AS $row) $options[$row['client_id']]=$row['name_en'];
		$_member->setMultiOptions($options);
		
		$_namekh = new Zend_Dojo_Form_Element_TextBox('name_kh');
		$_namekh->setAttribs(array(
						'dojoType'=>'dijit.form.ValidationTextBox',
						'class'=>'fullside',
						'required' =>'true'
		));
		
 		$id_client = $db->getNewClientId();
		$_clientno = new Zend_Dojo_Form_Element_TextBox('client_no');
		$_clientno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
 				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
 		$_clientno->setValue($id_client);
	
		$_nameen = new Zend_Dojo_Form_Element_TextBox('name_en');
		$_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
// 				'required' =>'true'
		));
		
		$_join_with = new Zend_Dojo_Form_Element_TextBox('join_with');
		$_join_with->setAttribs(array(
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
// 		$opt = array(1=>"Male",2=>"Femail");
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
// 		$opt_client_d_type= $db->getVewOptoinTypeByType(23,1);
// 		$client_d_type->setMultiOptions($opt_client_d_type);
		
// 		$join_d_type = new Zend_Dojo_Form_Element_FilteringSelect('join_d_type');
// 		$join_d_type->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 		));
// 		$join_d_type->setMultiOptions($opt_client_d_type);
		
// 		$guarantor_d_type = new Zend_Dojo_Form_Element_FilteringSelect('guarantor_d_type');
// 		$guarantor_d_type->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 		));
// 		$guarantor_d_type->setMultiOptions($opt_client_d_type);
		
		$guarantor_address = new Zend_Dojo_Form_Element_TextBox('guarantor_address');
		$guarantor_address->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		///////////////////////////////////
		
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
// 		$_province->setValue($request->getParam('province'));
		
		
		$_district = new Zend_Dojo_Form_Element_FilteringSelect('district');
// 		$rows =  $db->getAllDistrict();
// 		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['dis_id']]=$row['district_name'];
// 		$_district->setMultiOptions($options);
		$_district->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckDistrict();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		$_commune = new Zend_Dojo_Form_Element_FilteringSelect('commune');
// 		$rows =  $db->getCommune();
		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['com_id']]=$row['commune_name'];
		$_commune->setMultiOptions($options);
		$_commune->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCommune();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		
		$_village = new Zend_Dojo_Form_Element_FilteringSelect('village');
		$_village->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'popupCheckVillage();',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
// 		$rows =  $db->getVillage();
		$options=array(''=>"------Select------",-1=>"Add New");
// 		if(!empty($rows))foreach($rows AS $row) $options[$row['vill_id']]=$row['village_name'];
		$_village->setMultiOptions($options);
		
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
		
// 		$_id_type = new Zend_Dojo_Form_Element_FilteringSelect('id_type');
// 		$_id_type->setAttribs(array(
// 				'dojoType'=>'dijit.form.FilteringSelect',
// 				'class'=>'fullside',
// 				'required' =>'true'
// 		));
// 		$rows =  $db->GetAllIDType();
// 		$_id_type->setMultiOptions($rows);
		
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
		
		
		$chackcall = new Zend_Dojo_Form_Element_CheckBox('chackcall');
		$chackcall->setAttribs(array(
				'dojoType'=>'dijit.form.CheckBox',
				//'checked'=>'checked'
		));
// 		$_id=new Zend_Form_Element_Hidden('id');
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
			$_member->setValue($data['parent_id']);
			$_group->setValue($data['is_group']);
			$_namekh->setValue($data['name_kh']);
			$_nameen->setValue($data['name_en']);
			$_sex->setValue($data['sex']);
			$_situ_status->setValue($data['sit_status']);
			$_province->setValue($data['pro_id']);
			$_district->setValue($data['dis_id']);
			$_commune->setValue($data['com_id']);
			$_village->setValue($data['village_id']);
			$_house->setValue($data['house']);
			$_street->setValue($data['street']);
// 			$_id_type->setValue($data['id_type']);
			$_id_no->setValue($data['id_number']);
			$_phone->setValue($data['phone']);
			$_spouse->setValue($data['spouse_name']);
			$_desc->setValue($data['remark']);
			$_status->setValue($data['status']);
			$_clientno->setValue($data['client_number']);	
			$photo->setValue($data['photo_name']);
			$_id->setValue($data['client_id']);
			$_group_code->setValue($data['group_code']);
			$job->setValue($data['job']);
			$national_id->setValue($data['nation_id']);
			$spouse_nationid->setValue($data['spouse_nationid']);
			$_join_with->setValue($data['join_with']);
			$_join_nation_id->setValue($data['join_nation_id']);
			$_relate_tel->setValue($data['join_tel']);
			$_releted->setValue($data['relate_with']);
			$_guarantor_with->setValue($data['guarantor_with']);
			$_guarantor_tel->setValue($data['guarantor_tel']);
            $client_d_type->setValue($data['client_d_type']);
// 			$join_d_type->setValue($data['join_d_type']);
// 			$guarantor_d_type->setValue($data['guarantor_d_type']);
			$guarantor_address->setValue($data['guarantor_address']);

			$_dob_Guarantor->setValue($data['dob_guarantor']);
			$dob_join_acc->setValue($data['dob_join_acc']);
			$_dob->setValue($data['dob']);
// 			print_r($data);
		}
		$this->addElements(array($client_d_type,$guarantor_address,$_relate_tel,$_guarantor_tel,$_guarantor_with,$_releted,$_join_nation_id,$_join_with,$spouse_nationid,$_id,$photo,$_spouse,$job,$national_id,$chackcall,$_group_code,$_branch_id,$_member,$_group,$_namekh,$_nameen,$_sex,$_situ_status,
				$_province,$_district,$_commune,$_village,$_house,$_street,$_id_no,
				$_phone,$_spouse,$_desc,$_status,$_clientno,$_dob,$dob_join_acc,$_dob_Guarantor,$clienttype_namekh,$clienttype_nameen));
		return $this;
		
	}	
}