<?php
class Loan_CoController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/loan';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Other_Model_DbTable_DbCreditOfficer();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => 1);
			}
			$rs_rows= $db->getAllCreditOfficer($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("CODE","NAME_KH","NAME_EN","NATIONAL_ID","ADDRESS","PHONE",
					"EMAIL","DEGREE","DEPARTMENT","ANNUAL_LIVES","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'co','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('co_code'=>$link,'co_khname'=>$link,'co_engname'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$fm = new Other_Form_FrmCO();
   		$frm_co=$fm->FrmAddCO();
   		Application_Model_Decorator::removeAllDecorator($frm_co);
   		$this->view->frm_co = $frm_co;
	
	}
	
	public function settingAction(){
	try{
		$db_dept=new Global_Model_DbTable_DbDept();
		if($this->getRequest()->isPost()){
			$_data=$this->getRequest()->getPost();
			$search = array(
					'title' => $_data['title'],
					'status' => $_data['status_search']);
			$limit = $dept_session->limit;
		}
		else{
			$search='';
		}
		 $_db = new Global_Model_DbTable_DbSetting();
		 $rs_rows = $_db->getAllSetting($search);
			 
		 $list = new Application_Form_Frmtable();
		 $collumns = array("KEY_VALUE","KEY_VALUE","BY_USER");
		 $link=array(
					'module'=>'global','controller'=>'index','action'=>'setting-edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('keyvalue'=>$link));
			    	
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	    	$frm = new Global_Form_FrmSearchMajor();
	    	$frm = $frm->FrmSetting();
	    	Application_Model_Decorator::removeAllDecorator($frm);
	    	$this->view->frm_search = $frm;
	}
	public function settingEditAction(){
		$frm = new Global_Form_FrmSearchMajor();
		$_model = new Global_Model_DbTable_DbSetting();
		if($this->getRequest()->isPost()){
			try{
				$_data = $this->getRequest()->getPost();
				$_model->AddNewSetting($_data);
				Application_Form_FrmMessage::Sucessfull("/global/index/setting","ការបញ្ជូលដោយជោយជ័យ");
			}catch (Exception $e){
				Application_Form_FrmMessage::message("ការបញ្ជូលបរាជ័យ");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam("id");
		$data=null;
		if(!empty($id)){
			$data=$_model->getSettingById($id);
			$frm = $frm->FrmAddSetting($data);
			Application_Model_Decorator::removeAllDecorator($frm);
			$this->view->frm_setting = $frm;
			
		}else{
			Application_Form_FrmMessage::getUrl("/global/index/setting");
		}
	}
	
   function addAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$db_co = new Other_Model_DbTable_DbCreditOfficer();
   		 
   		try{
   			$db_co->addCreditOfficer($_data);
   				if(!empty($_data['save_close'])){
   					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/co/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/co/add');
				}
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/co/add');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$frm = new Other_Form_FrmCO();
   	$frm_co=$frm->FrmAddCO();
   	Application_Model_Decorator::removeAllDecorator($frm_co);
   	$this->view->frm_co = $frm_co;
   	
   	$frmpopup = new Application_Form_FrmPopupGlobal();
   	$this->view->frmpopupdepartment = $frmpopup->frmPopupDepartment();
   }
   
   function editAction(){
   	$db_co = new Other_Model_DbTable_DbCreditOfficer();
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		try{
   			$db_co->addCreditOfficer($_data);
   			Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/loan/co');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$id = $this->getRequest()->getParam("id");
   	$row = $db_co->getCOById($id);
   	$this->view->row=$row;
   	$this->view->photo = $row['photo'];
   	if(empty($row)){
   		$this->_redirect('payroll/co');
   	}
   	$frm = new Other_Form_FrmCO();
   	$frm_co=$frm->FrmAddCO($row);
   	Application_Model_Decorator::removeAllDecorator($frm_co);
   	$this->view->frm_co = $frm_co;
   }
   
   public function addNewcoAction(){
   	if($this->getRequest()->isPost()){
   		$data = $this->getRequest()->getPost();
//    		$data['status']=1;
//    		$data['co_id']='';
//    		$data['name_kh']='';
   		$db_co = new Other_Model_DbTable_DbCreditOfficer();
   		$id = $db_co->addCoByAjax($data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
   
   public function addnewdepartmentAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Payroll_Model_DbTable_DbDepartment();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->addDepartmentPop($_data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
   
   function getstaffcodeAction(){
   	if($this->getRequest()->isPost()){
   		$db = new Application_Model_DbTable_DbGlobal();
   		$_data = $this->getRequest()->getPost();
   		$id = $db->getStaffNumberByBranch($_data['branch_id']);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}
