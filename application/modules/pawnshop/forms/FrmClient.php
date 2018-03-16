<?php 
Class Pawnshop_Form_FrmClient extends Zend_Dojo_Form {
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
		$clienttype_nameen->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		$clienttype_namekh= new Zend_Dojo_Form_Element_DateTextBox('clienttype_namekh');
		$clienttype_namekh->setAttribs(array('dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		$_dob= new Zend_Dojo_Form_Element_DateTextBox('dob_client');
		$_dob->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		
		$_relate_tel = new Zend_Dojo_Form_Element_TextBox('relate_tel');
		$_relate_tel->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		
		$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$_branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'Onchange'=>'getFunction();'
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
						'required' =>'true'
		));
		
 		//$id_client = $db->getNewClientId();
		$_clientno = new Zend_Dojo_Form_Element_TextBox('client_no');
		$_clientno->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				'readonly'=>'readonly',
				'style'=>'color:red;'
		));
//  		$_clientno->setValue($id_client);
	
		$_nameen = new Zend_Dojo_Form_Element_ValidationTextBox('name_en');
		$_nameen->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
				
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
		));
		
		$_province = new Zend_Dojo_Form_Element_FilteringSelect('province');
		$_province->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'filterDistrict();',
				
		));
		
		$rows =  $db->getAllProvince();
		$options=array($this->tr->translate("SELECT_PROVINCE")); //array(''=>"------Select Province------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['province_id']]=$row['province_en_name'];
		$_province->setMultiOptions($options);
		
		$_district = new Zend_Dojo_Form_Element_FilteringSelect('district');
		$_district->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckDistrict();'
		));
		
		$_commune = new Zend_Dojo_Form_Element_FilteringSelect('commune');
		$options=array(''=>"------Select------",-1=>"Add New");
		$_commune->setMultiOptions($options);
		$_commune->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>'popupCheckCommune();'
		));
		
		$_village = new Zend_Dojo_Form_Element_FilteringSelect('village');
		$_village->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true',
				'onchange'=>'popupCheckVillage();'
		));
		$rows =  $db->getVillage();
		$options=array(''=>"------Select------",-1=>"Add New");
		if(!empty($rows))foreach($rows AS $row) $options[$row['vill_id']]=$row['village_name'];
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
		));
		
		$_id_no = new Zend_Dojo_Form_Element_TextBox('id_no');
		$_id_no->setAttribs(array(
				'dojoType'=>'dijit.form.ValidationTextBox',
				'class'=>'fullside',
				'required' =>'true'
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
		$job = new Zend_Dojo_Form_Element_TextBox('job');
		$job->setAttribs(array(
				'dojoType'=>'dijit.form.TextBox',
				'class'=>'fullside',
		));
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
				'style'=>'width:96%;min-height:50px;'));
		
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
		
		$dob_join_acc= new Zend_Dojo_Form_Element_DateTextBox('dob_join_acc');
		$dob_join_acc->setAttribs(array('dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}"
		));
		
		if($data!=null){
			$_branch_id->setValue($data['branch_id']);
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
			$_id_no->setValue($data['id_number']);
			$_phone->setValue($data['phone']);
			$_status->setValue($data['status']);
			$_clientno->setValue($data['client_number']);	
			$photo->setValue($data['photo_name']);
			$_id->setValue($data['client_id']);
			$job->setValue($data['job']);
			$national_id->setValue($data['nation_id']);
			$_join_with->setValue($data['join_with']);
			$_join_nation_id->setValue($data['join_nation_id']);
			$_relate_tel->setValue($data['join_tel']);
			$_releted->setValue($data['relate_with']);
            $client_d_type->setValue($data['client_d_type']);
			$_dob->setValue($data['dob']);
		}
		$this->addElements(array($dob_join_acc,$client_d_type,$_relate_tel,$_releted,$_join_nation_id,$_join_with,$spouse_nationid,$_id,$photo,$_spouse,$job,$national_id,$_branch_id,$_namekh,$_nameen,$_sex,$_situ_status,
				$_province,$_district,$_commune,$_village,$_house,$_street,$_id_no,
				$_phone,$_spouse,$_desc,$_status,$_clientno,$_dob,$clienttype_namekh,$clienttype_nameen));
		return $this;
		
	}	
}