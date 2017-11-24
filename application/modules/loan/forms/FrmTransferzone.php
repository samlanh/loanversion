<?php 
Class Loan_Form_FrmTransferzone extends Zend_Dojo_Form {
	protected $tr;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
	}
	public function FrmTransfer($data=null){
		
		$db = new Application_Model_DbTable_DbGlobal();
		$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
		$_title->setAttribs(array(
				'dojoType'=>$this->tvalidate,
				'onkeyup'=>'this.submit()',
				'placeholder'=>$this->tr->translate("SEARCH")
		));
		$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
		$_btn_search->setAttribs(array(
				'dojoType'=>'dijit.form.Button',
				'iconclass'=>'dijitIconSearch'
		));
		
		$request=Zend_Controller_Front::getInstance()->getRequest();
		$branch_name = new Zend_Dojo_Form_Element_FilteringSelect('branch_name');
    	$branch_name->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$rows = $db->getAllBranchName();
    	$options=array(''=>"------Select------");
    	if(!empty($rows))
    		foreach($rows AS $row){
    		$options[$row['br_id']]=$row['branch_namekh'];
    	}
    	$branch_name->setMultiOptions($options);
    	$branch_name->setValue($request->getParam('branch_id'));    	
    	
    	$co_name = new Zend_Dojo_Form_Element_FilteringSelect('co_code');
    	$co_name->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true',
    			'onchange'=>'getClientInfo(1);',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$db_co = new Loan_Model_DbTable_DbTransferCo();
    	$row_co = $db_co->getcoinfo();
    	$options_co =array(''=>"---Select From CO Code---");
    	if (!empty($row_co))
    		foreach ($row_co AS $row_cos){
    		$options_co[$row_cos['co_id']] = $row_cos['co_code'];
    		}
    	$co_name->setMultiOptions($options_co);
    	
    	$zo_name = new Zend_Dojo_Form_Element_FilteringSelect('zone_name');
    	$zo_name->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true',
    			'onchange'=>'getClientInfo(1);',
    			'autoComplete'=>"false",
    			'queryExpr'=>'*${0}*',
    	));
    	$db_zo = new Loan_Model_DbTable_DbTransferZone();
    	$row_zo = $db_zo->getzoneinfo();
    	$options_zo =array(''=>"---Select zone---");
    	if (!empty($row_co))
    		foreach ($row_zo AS $row_zo){
    		$options_zo[$row_zo['zone_id']] = $row_zo['zone_name'];
    	}
    	$zo_name->setMultiOptions($options_zo);
		
		$_date= new Zend_Dojo_Form_Element_DateTextBox('Date');
		$_date->setAttribs(array(
				'dojoType'=>'dijit.form.DateTextBox',
				'class'=>'fullside',
				'constraints'=>"{datePattern:'dd/MM/yyyy'}",
		));
		$_date->setValue(date('Y-m-d'));
		
		$db = new Loan_Model_DbTable_DbBadloan();
		$co_code = new Zend_Dojo_Form_Element_FilteringSelect('co_codes');
		$co_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));

		$formc_co = new Zend_Dojo_Form_Element_FilteringSelect('formc_co');
		$formc_co->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
			    'onchange'=>"getClientInfo(2);",
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				));
		$row_froms = $db_co->getcoinfo();
		$options_from =array(''=>"---Select From CO Name---");
		if (!empty($row_froms))
			foreach ($row_froms AS $row_from){
			$options_from[$row_from['co_id']] = $row_from['co_khname'];
		}
		$formc_co->setMultiOptions($options_from);
		
		$to_co = new Zend_Dojo_Form_Element_FilteringSelect('to_co');
		$to_co->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"getClientInfo(3);",
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$row_co = $db_co->getcoinfo();
		$options_co =array(''=>"---Select To CO Name---");
		if (!empty($row_co))
			foreach ($row_co AS $row_cos){
			$options_co[$row_cos['co_id']] = $row_cos['co_khname'];
		}
		$to_co->setMultiOptions($options_co);
		// 		$options = $db->getClientByTypes(2);
		// 		$formc_co->setMultiOptions($options);
		
		$to_co_code = new Zend_Dojo_Form_Element_FilteringSelect('to_co_code');
		$to_co_code->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'onchange'=>"getClientInfo(4);",
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$row_froms = $db_co->getcoinfo();
		$options_from =array(''=>"---Select To CO ---");
		if (!empty($row_froms))
			foreach ($row_froms AS $row_from){
			$options_from[$row_from['co_id']] = $row_from['co_code'];
		}
		$to_co_code->setMultiOptions($options_from);
		
		$note = new Zend_Dojo_Form_Element_Textarea('Note');
		$note ->setAttribs(array(
				'dojoType'=>'dijit.form.SimpleTextarea',
				'class'=>'fullside',
				'style'=>'width:98%',
				'required'=>true
		));
		$user_id = new Zend_Dojo_Form_Element_FilteringSelect('user_id');
		$user_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
		));
		$row_froms = $db_co->getcoinfo();
		$options_from =array(''=>"------Select------");
		if (!empty($row_froms))
			foreach ($row_froms AS $row_from){
		}
		$user_id->setMultiOptions($options_from);
		
		$_arr = array(1=>$this->tr->translate("ACTIVE"),0=>$this->tr->translate("DACTIVE"));
		$_status = new Zend_Dojo_Form_Element_FilteringSelect("status");
		$_status->setMultiOptions($_arr);
		$_status->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'required'=>'true',
				'missingMessage'=>'Invalid Module!',
				'autoComplete'=>"false",
				'queryExpr'=>'*${0}*',
				'class'=>'fullside'));
		
		if($data!=null){				
			$branch_name->setValue($data['branch_id']);
			$zo_name->setValue($data['from_zone']);
			$to_co->setValue($data['to_co']);			
			$_status->setValue($data['status']);
			$_date->setValue($data['date']);
			$note->setValue($data['note']);
			$to_co_code->setValue($data['to_co']);
		}		
		
		$this->addElements(array($zo_name,$_title,$_btn_search,$co_name,$_status,$branch_name,$_date,$co_code,$formc_co,$to_co,$to_co_code,$note,$user_id));
		return $this;
		
	}	
}