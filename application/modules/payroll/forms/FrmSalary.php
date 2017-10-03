<?php

class Payroll_Form_FrmSalary extends Zend_Dojo_Form
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
    public function frmaddSalary($data=null)
    {
    	
    	$request=Zend_Controller_Front::getInstance()->getRequest();
    	 
    	$_title = new Zend_Dojo_Form_Element_TextBox('adv_search');
    	$_title->setAttribs(array('dojoType'=>$this->tvalidate,
    			'onkeyup'=>'this.submit()',
    			'placeholder'=>$this->tr->translate("SEARCH_SALARY_INFO")
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

    	$db = new Application_Model_DbTable_DbGlobal();
    	$staff_name = new Zend_Dojo_Form_Element_FilteringSelect('staff_name');
    	$rows = $db ->getAllCOName();
    	$options=array(''=>"------Select------",-1=>"Add New");
    	if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
    	$staff_name->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'onchange'=>'popupCheckStaff(1);'
    	));
    	$staff_name->setMultiOptions($options);
    	
    	$db_Sex=new Application_Model_DbTable_DbGlobal();
    	$opt_Sex=$db_Sex->getVewOptoinTypeByType(8,1);
    	$Sex=new Zend_Dojo_Form_Element_FilteringSelect('sex');
    	$Sex->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'required'=>true,
    			'class'=>'fullside'
    	));
    	$Sex->setMultiOptions($opt_Sex);
    	
    	$position_=new Zend_Dojo_Form_Element_FilteringSelect('position');
    	$position_->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
//     			'dojoType'=>$this->filter,
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	
    	$db_position=new Application_Model_DbTable_DbGlobal();
    	$opt_position=$db_position->getAllStaffPosition(null,1);
    	$position_->setMultiOptions($opt_position);
    	$position_->setValue($request->getParam('position'));
    	
    	$Basic_salary=new Zend_Dojo_Form_Element_NumberTextBox('basic_salary');
    	$Basic_salary->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'required'=>true,
    			'class'=>'fullside'
    			));
    	$Basic_salary->setValue(0);
    	
    	
    	$date_start=new Zend_Dojo_Form_Element_DateTextBox('date_start');
    	$date_start->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'class'=>'fullside',
    			));
    	$date_start->setValue(date('Y-m-d'));
    	
    	$date_get_salary=new Zend_Dojo_Form_Element_DateTextBox('date_get_salary');
    	$date_get_salary->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'class'=>'fullside',
    			));
    	$date_get_salary->setValue(date('Y-m-d'));
    	
    	$date_end_contract=new Zend_Dojo_Form_Element_DateTextBox('date_end_contract');
    	$date_end_contract->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'class'=>'fullside',
    			));
    	$date_end_contract->setValue(date('Y-m-d'));
    	
    	$status=  new Zend_Dojo_Form_Element_FilteringSelect('status');
    	$status->setAttribs(array('dojoType'=>'dijit.form.FilteringSelect','class'=>'fullside',));
    	$status_opt = array(
    			1=>$this->tr->translate("ACTIVE"),
    			0=>$this->tr->translate("DACTIVE"));
    	$status->setMultiOptions($status_opt);
    	
    	$db = new Application_Model_DbTable_DbGlobal();
    	$_branch_id = new Zend_Dojo_Form_Element_FilteringSelect('branch_id');
    	$_branch_id->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required' =>'true'
    	));
    	$rows = $db->getAllBranchName();
    	$options=array(''=>"---ស្វែងរកសាខា---");
    	if(!empty($rows))
    		foreach($rows AS $row){
    		$options[$row['br_id']]=$row['branch_namekh'];
    	}
    	$_branch_id->setMultiOptions($options);
    	$_branch_id->setValue($request->getParam('branch_id'));
    	
    	
    	$staff_code=new Zend_Dojo_Form_Element_NumberTextBox('staff_code');
    	$staff_code->setAttribs(array(
    			'dojoType'=>'dijit.form.NumberTextBox',
    			'class'=>'fullside',
    			'required'=>true
    			));
    	
    	$for_month=new Zend_Dojo_Form_Element_FilteringSelect('for_month');
    	$for_month->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'required'=>true
    	));
    	$opt_month="";
    	for($i=1;$i<=12;$i++){
    		$opt_month[$i]=$i;
    	}
    	$for_month->setMultiOptions($opt_month);
    	
    	$employee = new Zend_Dojo_Form_Element_FilteringSelect('employee');
    	$rows = $db ->getAllCOName();
    	$options=array(''=>"---ស្វែងរកតាមរយៈឈ្មោះ---");
    	if(!empty($rows))foreach($rows AS $row) $options[$row['co_id']]=$row['co_khname'];
    	$employee->setAttribs(array(
    			'dojoType'=>'dijit.form.FilteringSelect',
    			'class'=>'fullside',
    			'onchange'=>'popupCheckCO();'
    	));
    	$employee->setMultiOptions($options);
    	$employee->setValue($request->getParam('employee'));
    	
    	$from_date=new Zend_Dojo_Form_Element_DateTextBox('from_date');
    	$from_date->setAttribs(array(
    			'dojoType'=>'dijit.form.DateTextBox',
    			'required'=>true,
    			'class'=>'fullside'
    	));
    	$fromdate=$request->getParam("from_date");
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
    	$todate=$request->getParam("to_date");
     	if(empty($todate)){
     		$todate=date('Y-m-d');
     	}

    	$to_date->setValue($todate);
    	
    	$_id = new Zend_Form_Element_Hidden('id');
    	if($data!=null){
    		$Basic_salary->setValue($data['basic_salary']);
    		$date_start->setValue($data['date_start']);
    		$date_get_salary->setValue($data['date_get_salary']);
    		$status->setValue($data['status']);
    		$_branch_id->setValue($data['branch_id']);
    		$_id->setValue($data['id']);
    	}
    	
		$this->addElements(array($from_date,$to_date,$employee,$_status_search,$_btn_search,$_title,$_id,$date_end_contract,$position_,$staff_code,$for_month,$status,$_branch_id,$_id,$staff_name,$Basic_salary,$date_start,$date_get_salary));
		return $this;
    }
}

