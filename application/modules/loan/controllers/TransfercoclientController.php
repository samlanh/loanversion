<?php
class Loan_TransfercoClientController extends Zend_Controller_Action {
	
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction()
	{
	try{
			if($this->getRequest()->isPost()){
				$search = $this->getRequest()->getPost();
			}else{
				$search = array(
						'branch_name'=>'',
						'loan_client'=>'',
						'name_co'=>'',
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'),
						'txt_search'=>'',
						'status' => 1,
						'note'=>''
				);
			}
 			$db = new Loan_Model_DbTable_DbTransferCoClient(); 
 			$rs_rows= $db->getAllinfoCo($search);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CUSTOMER_CODE","CUSTOMER_NAME","TO_CO","DATE","NOTE","STATUS",);
 			$link=array(
					'module'=>'loan','controller'=>'transfercoclient','action'=>'edit',
 			);
 			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'client_name'=>$link,'to_coname'=>$link));
 		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
 			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
 		}
 		$fm = new Loan_Form_FrmTransferCoClient();
 		$frm = $fm->FrmTransfer();
 		Application_Model_Decorator::removeAllDecorator($frm);
 		$this->view->frm_transfer = $frm;
	}
	public function addAction(){
		if($this->getRequest()->isPost()){//check condition return true click submit button			
 			$_data = $this->getRequest()->getPost();
 			try {		
 				$db = new Loan_Model_DbTable_DbTransferCoClient();
 				$db->insertTransfer($_data);
 				if(isset($_data['btn_save'])){				 				
	 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoclient/add");
 				}
 				elseif (isset($_data['btn_save_close'])){
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoclient/");
 				}
 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoclient/add");
 			}catch (Exception $e) {
 				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
 		}
		$fm = new Loan_Form_FrmTransferCoClient();
		$frm = $fm->FrmTransfer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_transfer = $frm;
	}
	public function editAction()
	{		
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbTransferCoClient();
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();		
			if(isset($post['btn_save'])){
				$db->updatTransfer($post, $id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/transfercoclient/");
			}
		}		
		$data = $db->getAllinfoTransfer($id);
		$fm = new Loan_Form_FrmTransferCoClient();
		if(empty($data)){
			Application_Form_FrmMessage::Sucessfull("Can not get data","/loan/transfercoclient/");
		}
		$frm = $fm->FrmTransfer($data);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_transfer = $frm;
	}
	public function getLoaninfoAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbBadloan();
			$row=$db->getLoanInfo($data['loan_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
}

