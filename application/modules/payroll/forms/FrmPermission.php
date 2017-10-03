<?php

class Payroll_Form_FrmPermission extends Zend_Dojo_Form
{
	protected $tr;
	protected $tvalidate =null;//text validate
	protected $filter=null;
	protected $t_num=null;
	protected $text=null;
	protected $tarea=null;
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		$this->tvalidate = 'dijit.form.ValidationTextBox';
		$this->filter = 'dijit.form.FilteringSelect';
		$this->text = 'dijit.form.TextBox';
		$this->tarea = 'dijit.form.SimpleTextarea';
	}

    public function frmPermission($data=null)
    {
    	$db = new Application_Model_DbTable_DbGlobal();
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	
    	$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
    	$_title->setAttribs(array('dojoType'=>$this->tvalidate,
    			'onkeyup'=>'this.submit()',
    			'placeholder'=>$this->tr->translate("SEARCH_STAFF_PERMISTION")
    			));
    	$_title->setValue($request->getParam("adv_search"));
    	
    	$_btn_search = new Zend_Dojo_Form_Element_SubmitButton('btn_search');
    	$_btn_search->setAttribs(array(
    			'dojoType'=>'dijit.form.Button',
    			'iconclass'=>'dijitIconSearch'
    	));
    	
    	
    	$_status_search=  new Zend_Dojo_Form_Element_FilteringSelect('status_search');
    	$_status_search->setAttribs(array('dojoType'=>$this->filter));
    	$_status_opt = array(
    			-1=>$this->tr->translate("ALL"),
    			1=>$this->tr->translate("ACTIVE"),
    			0=>$this->tr->translate("DACTIVE"));
    	$_status_search->setMultiOptions($_status_opt);
    	$_status_search->setValue($request->getParam("status_search"));
    	
    	$employee = new Zend_Dojo_Form_Element_FilteringSelect('employee');
    	$rows = $db ->getAllCOName();
    	$options=array(''=>"------SELECT------",-1=>"Add New");
    	if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
    	$employee->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'onchange'=>'popupCheckCO();'
    	));
    	$employee->setMultiOptions($options);
  
    	$approve_by = new Zend_Dojo_Form_Element_FilteringSelect('approve_by');
    	$rows = $db ->getAllCOName();
    	$options=array(''=>"------SELECT------",-1=>"Add New");
    	if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
    	$approve_by->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'onchange'=>'popupCheckCO();'
    	));
    	$approve_by->setMultiOptions($options);
    	
    	$request_date=new Zend_Dojo_Form_Element_DateTextBox('request_date');
    	$request_date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$request_date->setValue(date('Y-m-d'));
    	$db_type=new Application_Model_DbTable_DbGlobal();
    	$opt_type=$db_type->getVewOptoinTypeByType(7,1);
    	$type=new Zend_Dojo_Form_Element_FilteringSelect('type');
    	$type->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$type->setMultiOptions($opt_type);
    	$from_date=new Zend_Dojo_Form_Element_DateTextBox('from_date');
    	$from_date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$fromdate=$request->getParam('from_date');
    	if(empty($fromdate)){
    		$fromdate=date('Y-m-d');
    	}
    	$from_date->setValue($fromdate);
    	
    	
    	$to_date=new Zend_Dojo_Form_Element_DateTextBox('to_date');
    	$to_date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$todate=$request->getParam('to_date');
    	if(empty($todate)){
    		$todate=date('Y-m-d');
    	}
    	$to_date->setValue($todate);
    	
    	$time=new Zend_Dojo_Form_Element_TextBox('time');
    	$time->setAttribs(array(
    			'dojotype'=>'dijit.form.TextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$time->setValue('00:00');
    	$all_day=new Zend_Dojo_Form_Element_CheckBox('all_day');
    	$all_day->setAttribs(array(
    			'dojotype'=>'dijit.form.CheckBox',
    			'required'=>true,
    	));
    	
    	$paid_leave=new Zend_Dojo_Form_Element_CheckBox('paid_leave');
    	$paid_leave->setAttribs(array(
    			'dojotype'=>'dijit.form.CheckBox',
    			'required'=>true,
    	));
    	
    	$every_day=new Zend_Dojo_Form_Element_CheckBox('every_day');
    	$every_day->setAttribs(array(
    			'dojotype'=>'dijit.form.CheckBox',
    			'required'=>true,
    	));
    	
    	$reason=new Zend_Dojo_Form_Element_TextBox('reason');
    	$reason->setAttribs(array(
    			'dojotype'=>'dijit.form.TextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	
    	$branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
		$branch_id->setAttribs(array(
				'dojoType'=>'dijit.form.FilteringSelect',
				'class'=>'fullside',
				'required' =>'true'
		));
		$db = new Application_Model_DbTable_DbGlobal();
		$rows = $db->getAllBranchName();
		$options='';
		if(!empty($rows))foreach($rows AS $row){
			$options[$row['br_id']]=$row['branch_namekh'];
		}
		$branch_id->setMultiOptions($options);
		$_status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
		$_status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
		$_status_opt = array(
				1=>$this->tr->translate("ACTIVE"),
				0=>$this->tr->translate("DACTIVE"));
		$_status->setMultiOptions($_status_opt);
	
		$_id = new Zend_Form_Element_Hidden('id');
		if($data!=null){
			$employee->setValue($data['employee_id']);
			$branch_id->setValue($data['branch_id']);
			$employee->setValue($data['employee_id']);
			$approve_by->setValue($data['approve_by']);
			$request_date->setValue($data['request_date']);
			$type->setValue($data['permission_type']);
			$from_date->setValue($data['from_date']);
			$to_date->setValue($data['to_date']);
			$time->setValue($data['time']);
			$all_day->setValue($data['all_day']);
			$paid_leave->setValue($data['paid_leave']);
			$every_day->setValue($data['every_day']);
			$reason->setValue($data['reason']);
			$_status->setValue($data['status']);
			$_id->setValue($data['id']);
		}
		
		$this->addElements(array($_btn_search,$_title,$_status_search,$_status,$_id,$employee,$approve_by,$request_date,$type,$from_date,$to_date,$time,
				$all_day,$reason,$paid_leave,$every_day,$branch_id));
		return $this;
    }


}

