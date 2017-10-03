<?php
class Saving_IndexController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
		    if($this->getRequest()->isPost()){
 				$search = $this->getRequest()->getPost();
 			}
			else{
				$search = array(
						'txt_search'=>'',
						'customer_code'=> -1,
						'branch_id' => -1,
						'status' => -1,
						'currency_type'=>-1,
						'account_type'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Saving_Model_DbTable_DbSavingaccount();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SAVINIG_ACCUONT","CUSTOMER_NAME","COMUNE_NAME_EN","RECEIPT_NO","CURRENCY_TYPE","SAVING_AMOUNT","INTEREST_RATE","ប្រភេទ​​បញ្ញើ","PERIOD","TERM","LEVEL","STATUS");
			
			$link_info=array('module'=>'saving','controller'=>'index','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link_info,'loan_number'=>$link_info,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Saving_Form_FrmSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Saving_Model_DbTable_DbSavingaccount();
				$_dbmodel->addSavingAccount($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Saving_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}	
	public function editAction(){
		$id = $this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Saving_Model_DbTable_DbSavingaccount();
				$_dbmodel->updateLoanById($_data,$id);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/saving/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		
		$db_g = new Application_Model_DbTable_DbGlobal();
// 		$rs = $db_g->getLoanFundExist($id);
// 		if($rs==true){
// 			Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/index/index");
// 		}
		$db = new Saving_Model_DbTable_DbSavingaccount();
		$row = $db->getSavingById($id);
		$this->view->datarow = $row;
	
		$frm = new Saving_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
	}
	public function viewAction(){
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_FUND","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLoanIL();
		$row = $db->getLoanviewById($id);
		$this->view->tran_rs = $row;
	}
// 	function getLoanlevelAction(){
// 		if($this->getRequest()->isPost()){
// 				$data = $this->getRequest()->getPost();
// 				$db = new Loan_Model_DbTable_DbLoanIL();
// 				$row = $db->getLoanLevelByClient($data['client_id'],$data['type']);
// 				print_r(Zend_Json::encode($row));
// 			    exit();
// 		}
		
// 	}
// 	public function getLoaninfoAction(){//from repayment schedule
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
// 			$row=$db->getLoanInfo($data['loan_id']);
// 			print_r(Zend_Json::encode($row));
// 			exit();
// 		}
// 	}
	

    function getloannumberAction(){
    			if($this->getRequest()->isPost()){
    				$data = $this->getRequest()->getPost();
    				$db = new Application_Model_DbTable_DbGlobal();
		            $loan_number = $db->getSavingNumber($data);
    				print_r(Zend_Json::encode($loan_number));
    				exit();
    			}
    }
	function addsavingtestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Saving_Model_DbTable_DbSavingaccount();
			$rows_return=$_dbmodel->addSavingAccounttest($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
		
	}

}

