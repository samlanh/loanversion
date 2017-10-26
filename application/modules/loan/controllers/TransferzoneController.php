<?php
class Loan_TransferzoneController extends Zend_Controller_Action {
	
	public function init()
	{
		/* Initialize action controller here */
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
						'branch_name'=>-1,
						'co_code'=>-1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'),
						'txt_search'=>'',
						'status' => 1,
						'note'=>''
				);
			}
			$db = new Loan_Model_DbTable_DbTransferZone();
			$rs_rows= $db->getAllinfoZone($search);//call frome model
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","ZONE_NAME","TO_CO","DATE","NOTE","STATUS",);
			$link=array(
					'module'=>'loan','controller'=>'transferzone','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('from_zone'=>$link,'branch_name'=>$link,'co_name'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
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
 				$db = new Loan_Model_DbTable_DbTransferZone(); 
 				$db->insertTransferZone($_data);
 				if(isset($_data['btn_save_close'])){				 				
	 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transferzone/");
 				}
 				else{
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transferzone/add");
 				}
 			}catch (Exception $e) {
 				Application_Form_FrmMessage::message("INSERT_FAIL");
 				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
 		}
		$fm = new Loan_Form_FrmTransferzone();
		$frm = $fm->FrmTransfer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_transfer = $frm;
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbTransferZone();
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			$db->updatTransfer($post, $id);
 			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transferzone");
		}
		$data = $db->getAllinfoTransfer($id);
		$fm = new Loan_Form_FrmTransferzone();
		if(empty($data)){
			Application_Form_FrmMessage::Sucessfull("NO_DATA","/loan/transferzone");
		}
		$frm = $fm->FrmTransfer($data);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_transfer = $frm;
	}
// 	public function getLoaninfoAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db=new Loan_Model_DbTable_DbBadloan();
// 			$row=$db->getLoanInfo($data['loan_id']);
// 			print_r(Zend_Json::encode($row));
// 			exit();
// 		}
// 	}
}

