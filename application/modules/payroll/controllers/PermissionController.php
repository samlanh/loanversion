<?php
class Payroll_PermissionController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	const REDIRECT_URL = '/payroll';
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
			$db = new Payroll_Model_DbTable_DbPermission();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => -1,
						'from_date' =>date('Y-m-d'),
						'to_date' => date('Y-m-d'),
						);
			}
			$rs_rows= $db->getAllPermission($search);
			$glClass = new Application_Model_GlobalClass();//status
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL,true);
			$list = new Application_Form_Frmtable();
			$collumns = array("STAFF","BRANCH","APPROVE_BY","DATE_REQUEST","TYPE","FROM_DATE",
					"TO_DATE","TIME","REASON","USER","DATE","STATUS");
			$link=array(
					'module'=>'payroll','controller'=>'permission','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('staff_name'=>$link,'branch_name'=>$link,'approve_by'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$fm_ = new Application_Form_FrmAdvanceSearch();
		$frm_ = $fm_->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm_);
		$this->view->frm_search = $frm_;
		$fm = new Payroll_Form_FrmPermission();
		$frm_permission=$fm->frmPermission();
		Application_Model_Decorator::removeAllDecorator($frm_permission);
		$this->view->frm_permistion = $frm_permission;
	}
	
   function addAction(){
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		$db_permission = new Payroll_Model_DbTable_DbPermission();
   		try{
   			$db_permission->addPermission($_data);
   			if(!empty($_data['save_new'])){
   				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
   			}else{
   				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/permission/index');
   			}
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
  
   	$frm = new Payroll_Form_FrmPermission();
   	$frm_permission=$frm->frmPermission();
   	Application_Model_Decorator::removeAllDecorator($frm_permission);
   	$this->view->frm_permistion = $frm_permission;
   }
   function editAction(){
   	$db_permission = new Payroll_Model_DbTable_DbPermission();
   	if($this->getRequest()->isPost()){
   		$_data = $this->getRequest()->getPost();
   		try{
   			$db_permission->addPermission($_data);
   			Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/payroll/permission');
   		}catch(Exception $e){
   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
   			$err =$e->getMessage();
   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
   		}
   	}
   	$id = $this->getRequest()->getParam("id");
   	$row = $db_permission->getPermissionById($id);
   	if(empty($row)){
   		$this->_redirect('payroll/permission');
   	}
   	$frm = new Payroll_Form_FrmPermission();
   	$frm_permission=$frm->frmPermission($row);
   	Application_Model_Decorator::removeAllDecorator($frm_permission);
   	$this->view->frm_permistion = $frm_permission;
   }
   public function addNewcoAction(){
   	if($this->getRequest()->isPost()){
   		$data = $this->getRequest()->getPost();
   		$data['status']=1;
   		$data['co_id']='';
   		$data['name_kh']='';
   		$db_co = new Other_Model_DbTable_DbCreditOfficer();
   		$id = $db_co->addCreditOfficer($data);
   		print_r(Zend_Json::encode($id));
   		exit();
   	}
   }
}

