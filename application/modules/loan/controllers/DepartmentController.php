<?php
class Loan_DepartmentController extends Zend_Controller_Action {
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
			$db = new Payroll_Model_DbTable_DbDepartment();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'adv_search' => '',
						'status_search' => 1);
			}
			$rs_rows= $db->getAllStaffDepartment($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("DEPARTMENT_KH","DEPARTMENT_EN","STATUS");
			$link=array(
					'module'=>'loan','controller'=>'department','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('department_kh'=>$link,'department_en'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		
		$frm = new Payroll_Form_FrmDepartment();
		 $frm_partment=$frm->FrmAddDepartment();
		 Application_Model_Decorator::removeAllDecorator($frm_partment);
		 $this->view->frm_department = $frm_partment;
	}
	
//syheng
 function addAction(){
	  if($this->getRequest()->isPost()){
	  	$_data = $this->getRequest()->getPost();
	  	$db = new Payroll_Model_DbTable_DbDepartment();
	  	try {
	  	        $db->addDepartment($_data);
	  			if(!empty($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/department/index');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/department/add');
				}
				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/department/add');
	  	}catch(Exception $e){
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	  }
		 $frm = new Payroll_Form_FrmDepartment();
		 $frm_partment=$frm->FrmAddDepartment();
		 Application_Model_Decorator::removeAllDecorator($frm_partment);
		 $this->view->frm_department = $frm_partment;
   }
   function getpositionAction(){
   	
			   	 $db=new Other_Model_DbTable_DbMyPosition();
			     $rows=$db->getallPosition();
   	             $this->view->list=$rows;
   }
 
   function editAction(){
    $db = new Payroll_Model_DbTable_DbDepartment();
	   	if($this->getRequest()->isPost()){
	   		$_data = $this->getRequest()->getPost();
	   		try{
	   			$db->upDateDepartment($_data);
	   			Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/loan/department');
	   		}catch(Exception $e){
	   			Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
	   			$err =$e->getMessage();
	   			Application_Model_DbTable_DbUserLog::writeMessageError($err);
	   		}
	   	}
	   	$id = $this->getRequest()->getParam("id");//ចាប់ id from ln_position ;
	   	$row = $db->getDepartmemtById($id);
	   	if(empty($row)){
	   		$this->_redirect('loan/department');
	   	}
		 $frm = new Payroll_Form_FrmDepartment();
		 $frm_partment=$frm->FrmAddDepartment($row);
		 Application_Model_Decorator::removeAllDecorator($frm_partment);
		 $this->view->frm_department = $frm_partment;
		   	
   }
}

