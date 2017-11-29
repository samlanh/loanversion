<?php
class Loan_TransfercoloandController extends Zend_Controller_Action {
	
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
 						'branch_name'=>'',
 						'loan_number'=>'',
 						'loan_client'=>'',
 						'name_co'=>'',
 						'start_date'=> date('Y-m-01'),
 						'end_date'=>date('Y-m-d'),
 						'txt_search'=>'',
 						'status' => 1,
 						'note'=>''
 						);
 			}
 			$this->view->search=$search;
 			$db = new Loan_Model_DbTable_DbTransferCoClient();
 			$rs_rows= $db->getAllinfoCoLoan($search);//call frome model
 			
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","LOAN_NO","CUSTOMER_NAME","FROM_CO","TO_CO","DATE","NOTE","STATUS",);
 			$link=array(
					'module'=>'loan','controller'=>'transfercoloand','action'=>'edit',
 			);
 			$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('loan_number'=>$link,'branch_name'=>$link,'client_name'=>$link,'from_coname'=>$link,'to_coname'=>$link));
 		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			echo $e->getMessage();exit();
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
 				$db->insertTransferloan($_data);
 				if(isset($_data['btn_save_close'])){				 				
	 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoloand/");
 				}
 				else{
 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoloand/add");
 				}
 			}catch (Exception $e) {
 				Application_Form_FrmMessage::message("INSERT_FAIL");
 				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
 		}
		$fm = new Loan_Form_FrmTransferCoClient();
		$frm = $fm->FrmTransfer();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_transfer = $frm;
	}
	public function editAction()
	{
		// action body		
		$id = $this->getRequest()->getParam('id');
		$db = new Loan_Model_DbTable_DbTransferCoClient();
		if($this->getRequest()->isPost()){
			$post = $this->getRequest()->getPost();
			//print_r($post);exit(); 			
			if(isset($post['btn_save_close'])){
				$db->updatTransferloan($post, $id);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/transfercoloand/");
			}
		}
		//print_r($id);exit();		
		$data = $db->getAllinfoTransfer($id);
		$fm = new Loan_Form_FrmTransferCoClient();
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

