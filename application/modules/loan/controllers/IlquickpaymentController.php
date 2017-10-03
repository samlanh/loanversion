<?php
class Loan_IlquickpaymentController extends Zend_Controller_Action {
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
			$db = new Loan_Model_DbTable_DbLoanILPayment();
		if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'advance_search' => $formdata['advance_search'],
						'client_name'=>$formdata['client_name'],
						'start_date'=>$formdata['start_date'],
						'end_date'=>$formdata['end_date'],
						'status'=>$formdata['status'],
						'branch_id'		=>	$formdata['branch_id'],
						'co_id'		=>	$formdata['co_id'],
						'paymnet_type'	=> $formdata["paymnet_type"],
						);
			}
			else{
				$search = array(
						'adv_search' => '',
						'client_name' => -1,
						'start_date'=> date('Y-m-01'),
						'end_date'=>date('Y-m-d'),
						'branch_id'		=>	-1,
						'co_id'		=> -1,
						'paymnet_type'	=> -1,
						'status'=>"",);
			}
			$rs_rows= $db->getAllQuickIndividuleLoan($search);
			$result = array();
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","CO_NAME","RECIEPT_NO","TOTAL_PRINCEPLE_PAYMENT","TOTAL_PAYMENT","RECEIVE_AMOUNT","TOTAL_INTEREST","PENALIZE AMOUNT","DATE_PAYMENT"
				);
			$link=array(
					'module'=>'loan','controller'=>'ilquickpayment','action'=>'edit',
			);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('co_name'=>$link,'receipt_no'=>$link,'branch'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchGroupPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
	}
  function addAction()
  {
  	$db = new Loan_Model_DbTable_DbLoanILPayment();
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db->quickPayment($data);
  		if(isset($data["save_new"])){
  			//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS!","/loan/ilquickpayment/add");
  		}elseif(isset($data["save_close"])){
  			//Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS!","/loan/ilquickpayment");
  		}
  	}
  	$frm = new Loan_Form_FrmIlPayment();
  	$frm_loan=$frm->quickPayment();
  	Application_Model_Decorator::removeAllDecorator($frm_loan);
  	$db_keycode = new Application_Model_DbTable_DbKeycode();
  	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
  	
  	$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
  	$this->view->frm_ilpayment = $frm_loan;
  	
  	$this->view->co = $db->getAllCo();
  }	
  
  function editAction()
  {
  	$id = $this->getRequest()->getParam("id");
  	$db = new Loan_Model_DbTable_DbLoanILPayment();
  	$db_global = new Application_Model_DbTable_DbGlobal();
  	if($this->getRequest()->isPost()){
  		$data = $this->getRequest()->getPost();
  		$db->editQuickPayment($id,$data);
//   		$db->cancelPaymnet($data);
  		if(isset($data["save_new"])){
  			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS!","/loan/ilquickpayment/add");
  		}elseif(isset($data["save_close"])){
  			Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS!","/loan/ilquickpayment");
  		}
  	}
//   	$last_pay_date = $db_global->getLastDatePayment($id);
//   	$current_date = $db_global->getCurrentDatePayment($id);
//   	if($current_date<$last_pay_date){
//   		Application_Form_FrmMessage::Sucessfull("WARNNING_EDIT_LOAN","/loan/ilquickpayment/");
//   	}
  	$quickPayment = $db->getIlQuickPaymentById($id);
  	$this->view->quickPayment = $quickPayment;
  	
  	$quickPaymentDetail = $db->getIlQuickPaymentDetailById($id);
  	$this->view->quickPaymentDetail = $quickPaymentDetail;
  	
  	$frm = new Loan_Form_FrmIlPayment();
  	$frm_loan=$frm->quickPayment();
  	Application_Model_Decorator::removeAllDecorator($frm_loan);
  	$db_keycode = new Application_Model_DbTable_DbKeycode();
  	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
  	 
  	$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
  	$this->view->frm_ilpayment = $frm_loan;
  	 
  	$this->view->co = $db->getAllCo();
  }
  function cancelPaymentAction()
  {
  	$db = new Loan_Model_DbTable_DbLoanILPayment();
  	if($this->getRequest()->isPost()){
  		$_data = $this->getRequest()->getPost();
  		$identity = $_data["identity"];
  		try {
  			$row = $db->cancelPaymnet($_data);
  			print_r(Zend_Json::encode($row));
  			exit();
  		}catch (Exception $e) {
  			$err =$e->getMessage();
  			Application_Model_DbTable_DbUserLog::writeMessageError($err);
  		}
  	}
  }
}

