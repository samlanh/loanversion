<?php
class Pawnshop_PaymentController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		try{
			$db = new Pawnshop_Model_DbTable_DbPayment();
		if($this->getRequest()->isPost()){
				$formdata=$this->getRequest()->getPost();
				$search = array(
						'advance_search' => $formdata['advance_search'],
						'client_name'=>$formdata['client_name'],
						'start_date'=>$formdata['start_date'],
						'end_date'=>$formdata['end_date'],
						'branch_id'		=>	$formdata['branch_id'],
						//'paymnet_type'	=> $formdata["paymnet_type"],
						);
			}
			else{
				$search = array(
						'adv_search' => '',
						'client_name' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						'branch_id'		=>	-1,
					);
			}
			$rs_rows= $db->getAllPawnPayment($search);
			$result = array();
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PAWN_CODE","CUSTOMER_NAME","RECIEPT_NO","PAID_PRINCIPAL","INTERREST_AMOUNT","TOTAL_PENELIZE","RECEIVE_AMOUNT","PAY_DATE","DAY_PAYMENT","PAYMENT_RECEIPT"
				);
			$link=array(
					'module'=>'pawnshop','controller'=>'payment','action'=>'edit',
			);
			$linkpawn=array(
					'module'=>'report','controller'=>'pawn','action'=>'recieptpayment',
			);
			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
			$reciept = $tr->translate("PAYMENT_RECEIPT");
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array($reciept =>$linkpawn,'team_group'=>$link,'loan_number'=>$link,'client_name'=>$link,'receipt_no'=>$link,'branch'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Pawnshop_Form_FrmSearchPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
  }
  function addAction()
  {
		  	$db = new Pawnshop_Model_DbTable_DbPayment();
		  	$db_global = new Application_Model_DbTable_DbGlobal();
		  	if($this->getRequest()->isPost()){
		  		$_data = $this->getRequest()->getPost();
		  		$identify = $_data["identity"];
		  		try {
		  			if($identify==""){
		  				Application_Form_FrmMessage::Sucessfull("Client no laon to pay!","/pawnshop/payment");
		  			}else {
		  				$db->addPawnpayment($_data);
		  			}
		  		}catch (Exception $e) {
		  			Application_Form_FrmMessage::message("INSERT_FAIL");
		  			$err =$e->getMessage();
		  			Application_Model_DbTable_DbUserLog::writeMessageError($err);
		  		}
		  	}
// 		  	$frm = new Loan_Form_FrmIlPayment();
// 		  	$frm_loan=$frm->FrmAddIlPayment();
// 		  	Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		  	$this->view->frm_ilpayment = $frm_loan;

  			$frm = new Pawnshop_Form_FrmPayment();
  			$frm_loan=$frm->FrmAddPayment();
  			Application_Model_Decorator::removeAllDecorator($frm_loan);
  			$this->view->frm_ilpayment = $frm_loan;
		  	
		  	$list = new Application_Form_Frmtable();
		  	$collumns = array("ថ្ងៃបង់ប្រាក់","ប្រាក់ត្រូវបង់","ប្រាក់ដើមត្រូវបង់","អាត្រាការប្រាក់","ប្រាក់ផាកពិន័យ","ប្រាក់បានបង់សរុប","សមតុល្យ","កំណត់សម្គាល់");
		  	$link=array(
		  			'module'=>'group','controller'=>'Client','action'=>'edit',
		  	);
		  	$this->view->list=$list->getCheckList(0, $collumns, array(),array('client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		  	
		  	$db_keycode = new Application_Model_DbTable_DbKeycode();
		  	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		  	
		  	$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		  	
// 		  	$this->view->client = $db->getAllClient();
// 		  	$this->view->clientCode = $db->getAllClientCode();
		  	
		  	$session_user=new Zend_Session_Namespace('authloan');
		  	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
		  	
// 		  	$this->view->loan_number = $db_global->getLoanNumberByBranch(1);
		  	$id = $this->getRequest()->getParam('id');
		  	if(!empty($id)){
		  		if(empty($id)){
		  			$id=0;
		  		}
		  		$this->view->rsid=$id;
		  		$db = new Loan_Model_DbTable_DbLoandisburse();
		  		$this->view->rsloan =  $db->getTranLoanByIdWithBranch($id,1);
		  	}
//   	$db = new Pawnshop_Model_DbTable_DbPayment();
//   	$db_global = new Application_Model_DbTable_DbGlobal();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$db->addPawnshopPayment($_data);
// 				if(!empty($_data['saveclose'])){
// 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop/payment");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 				}
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
// 		}
// 		$frm = new Pawnshop_Form_FrmPayment();
// 		$frm_loan=$frm->FrmAddPayment();
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_ilpayment = $frm_loan;
		
// 		$list = new Application_Form_Frmtable();
// 		$collumns = array("ឈ្មោះមន្ត្រីឥណទាន","ថ្ងៃបង់ប្រាក់","ប្រាក់ត្រូវបង់","ប្រាក់ដើមត្រូវបង់","អាត្រាការប្រាក់","ប្រាក់ផាកពិន័យ","ប្រាក់បានបង់សរុប","សមតុល្យ","កំណត់សម្គាល់");
// 		$link=array(
// 				'module'=>'group','controller'=>'Client','action'=>'edit',
// 		);
// 		$this->view->list=$list->getCheckList(0, $collumns, array(),array('client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		
// 		$db_keycode = new Application_Model_DbTable_DbKeycode();
// 		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$db_global = new Pawnshop_Model_DbTable_DbPayment();
		
		$this->view->client = $db_global->getClientNamebyBranch();
		$this->view->clientCode = $db_global->getClientCodebyBranch();
		
		$session_user=new Zend_Session_Namespace('authloan');
		$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
		$this->view->loan_number = $db_global->getPawnAccountNumber(1);
	}	
	function editAction()
	{
		//$this->_redirect("/pawnshop/payment");
		$id = $this->getRequest()->getParam("id");
	
		$db_global = new Application_Model_DbTable_DbGlobal();
			
		$db = new Loan_Model_DbTable_DbLoanILPayment();
		//$db1 = new Loan_Model_DbTable_DbGroupPayment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identify = $_data["identity"];
			try {
				if($identify==""){
					Application_Form_FrmMessage::Sucessfull("Client no laon to pay!","/loan/ilpayment/");
				}else{
					$db->updateIlPayment($_data);
					//Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/loan/ilpayment/");
				}
			}catch (Exception $e) {
				//echo $e->getMessage();
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
// 		$payment_il = $db->getIlPaymentByID($id);
// 		$this->view->ilPaymentById= $payment_il;
		
// 		$getIlDetail = $db->getIlDetail($id);
		
	/*	$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment(); */

		//test
		$frm = new Pawnshop_Form_FrmPayment();
		$frm_loan=$frm->FrmAddPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		//test

// 		$frm_loan=$frm->FrmAddIlPayment($payment_il);
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
// 		$this->view->ilPayent = $getIlDetail;
// 		$this->view->client_id=$payment_il["group_id"];
// 		$this->view->client_code=$payment_il["group_id"];
// 		$this->view->branch_id=$payment_il["branch_id"];
// 		$this->view->loan_number=$payment_il["loan_numbers"];
		
// 		$this->view->client = $db->getAllClient();
// 		$this->view->clientCode = $db->getAllClientCode();
		
// 		$db_keycode = new Application_Model_DbTable_DbKeycode();
// 		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
// 		$this->view->graiceperiod = $db_keycode->getSystemSetting(9);
		
// 		$session_user=new Zend_Session_Namespace('authloan');
// 		$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
		
// 		$this->view->loan_numbers = $db->getAllLoanNumberByBranch(1);
	}


// 	function getLoannumberAction(){
// 		if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$db = new Loan_Model_DbTable_DbLoanIL();
// 			$row = $db->getLoanPaymentByLoanNumber($data);
// 			print_r(Zend_Json::encode($row));
// 			exit();
// 		}
// 	}
	function getsavingpaymentAction(){//tab1
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Pawnshop_Model_DbTable_DbPayment();
			$row = $db->getPawnPaymentByID($data['pawnid']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getpawndetailAction(){//tab2
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Pawnshop_Model_DbTable_DbPayment();
			$row = $db->getAllLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getschedulepaidAction(){//tab3
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Pawnshop_Model_DbTable_DbPayment();
			$loan_number = $data["pawnid"];
			$row = $db->getschedulepaid($loan_number);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function addpaymentajaxAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Pawnshop_Model_DbTable_DbPayment();
			$receipt_id = $db->addPawnpayment($_data);
			$row = $db->getPawnPaymentByIdForPrint($receipt_id);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

