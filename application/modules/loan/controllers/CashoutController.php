<?php
class Loan_CashoutController extends Zend_Controller_Action {
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
						'repayment_method' => -1,
						'branch_id' => -1,
						'co_id' => -1,
						'status' => -1,
						'currency_type'=>-1,
						'pay_every'=>-1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
						 );
			}
			$db = new Loan_Model_DbTable_DbLoanreceipt();
			$rs_rows= $db->getAllIndividuleLoanreceipt($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","LOAN_NO","CUSTOMER_NAME","COMUNE_NAME_EN","LOAN_AMOUNT","INTEREST_RATE","REPAYMENT_TYPE","TERM_BORROW","RECEIPT_NO",
				"STATUS");
			$link=array(
					'module'=>'loan','controller'=>'index','action'=>'view',
			);
			$link_info=array('module'=>'loan','controller'=>'cashout','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link,'loan_number'=>$link,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Loan_Form_FrmSearchLoan();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  }
  function addAction()
  {
  	$db = new Loan_Model_DbTable_DbLoanreceipt();
  	$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->addRecieptclient($_data);
				if(!empty($_data['submit_close'])){
					Application_Form_FrmMessage::message("INSERT_SUCESS","/loan/cashout/");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		
		$list = new Application_Form_Frmtable();
		$collumns = array("ឈ្មោះមន្ត្រីឥណទាន","ថ្ងៃបង់ប្រាក់","ប្រាក់ត្រូវបង់","ប្រាក់ដើមត្រូវបង់","អាត្រាការប្រាក់","ប្រាក់ផាកពិន័យ","ប្រាក់បានបង់សរុប","សមតុល្យ","កំណត់សម្គាល់");
		$link=array(
				'module'=>'group','controller'=>'Client','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, array(),array('client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		$this->view->loan_number = $db_global->getLoanNumberByBranch(1);
	}	
	
	function editAction()
	{
// 		$id = $this->getRequest()->getParam("id");
// 		$db_global = new Application_Model_DbTable_DbGlobal();
// 		$db = new Loan_Model_DbTable_DbLoanreceipt();
// 		$db1 = new Loan_Model_DbTable_DbGroupPayment();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			$identify = $_data["identity"];
// 			try {
// 				if($identify==""){
// 					Application_Form_FrmMessage::Sucessfull("Client no laon to pay!","/loan/ilpayment/");
// 				}else{
// 					$db->updateIlPayment($_data);
// 				}
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
// 		}
// 		$payment_il = $db->getIlPaymentByID($id);
// 		$this->view->ilPaymentById= $payment_il;
		
// 		$getIlDetail = $db->getIlDetail($id);
		
// 		$frm = new Loan_Form_FrmIlPayment();
// 		$frm_loan=$frm->FrmAddIlPayment($payment_il);
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_ilpayment = $frm_loan;
		
// 		$this->view->ilPayent = $getIlDetail;
// 		$this->view->client_id=$payment_il["group_id"];
// 		$this->view->client_code=$payment_il["group_id"];
// 		$this->view->branch_id=$payment_il["branch_id"];
// 		$this->view->loan_number=$payment_il["loan_numbers"];
		
// 		$this->view->client = $db->getAllClient();
// 		$this->view->clientCode = $db->getAllClientCode();
		
// 		$this->view->loan_numbers = $db->getAllLoanNumberByBranch(1);
		
// 		$db = new Loan_Model_DbTable_DbLoanreceipt();
// 		$db_global = new Application_Model_DbTable_DbGlobal();
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$db->addRecieptclient($_data);
// 				if(!empty($_data['submit_close'])){
// 					Application_Form_FrmMessage::message("INSERT_SUCESS","/loan/cashout/");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCESS");
// 				}
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				$err =$e->getMessage();
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err);
// 			}
// 		}
// 		$frm = new Loan_Form_FrmIlPayment();
// 		$frm_loan=$frm->FrmAddIlPayment();
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_ilpayment = $frm_loan;
		
// 		$list = new Application_Form_Frmtable();
// 		$collumns = array("ឈ្មោះមន្ត្រីឥណទាន","ថ្ងៃបង់ប្រាក់","ប្រាក់ត្រូវបង់","ប្រាក់ដើមត្រូវបង់","អាត្រាការប្រាក់","ប្រាក់ផាកពិន័យ","ប្រាក់បានបង់សរុប","សមតុល្យ","កំណត់សម្គាល់");
// 		$link=array(
// 				'module'=>'group','controller'=>'Client','action'=>'edit',
// 		);
// 		$this->view->list=$list->getCheckList(0, $collumns, array(),array('client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		
// 		$this->view->client = $db->getAllClient();
// 		$this->view->clientCode = $db->getAllClientCode();
// 		$this->view->loan_number = $db_global->getLoanNumberByBranch(1);
		$db = new Loan_Model_DbTable_DbLoanreceipt();
		$id = $this->getRequest()->getParam("id");
		$payment_il = $db->getIlPaymentByID($id);
		$this->view->ilPaymentById= $payment_il;
// 		print_r($payment_il);
		
		$db = new Loan_Model_DbTable_DbLoanreceipt();
		$db_global = new Application_Model_DbTable_DbGlobal();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$db->addRecieptclient($_data);
				if(!empty($_data['submit_close'])){
					Application_Form_FrmMessage::message("INSERT_SUCESS","/loan/cashout/");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCESS");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$frm = new Loan_Form_FrmIlPayment();
		$frm_loan=$frm->FrmAddIlPayment();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_ilpayment = $frm_loan;
		
		$list = new Application_Form_Frmtable();
		$collumns = array("ឈ្មោះមន្ត្រីឥណទាន","ថ្ងៃបង់ប្រាក់","ប្រាក់ត្រូវបង់","ប្រាក់ដើមត្រូវបង់","អាត្រាការប្រាក់","ប្រាក់ផាកពិន័យ","ប្រាក់បានបង់សរុប","សមតុល្យ","កំណត់សម្គាល់");
		$link=array(
				'module'=>'group','controller'=>'Client','action'=>'edit',
		);
		$this->view->list=$list->getCheckList(0, $collumns, array(),array('client_number'=>$link,'name_kh'=>$link,'name_en'=>$link));
		
		$this->view->client = $db->getAllClient();
		$this->view->clientCode = $db->getAllClientCode();
		$this->view->loan_number = $db_global->getLoanNumberByBranch(1);
		
	}
	function cancelIlPayment(){
// 		$db = new Loan_Model_DbTable_DbLoanILPayment();
		$db = new Loan_Model_DbTable_DbGroupPayment();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$identity = $_data["identity"];
			try {
				$row = $db->cancelIlPayment($_data);
				print_r(Zend_Json::encode($row));
				exit();
			}catch (Exception $e) {
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
	}
	function ilQuickPaymentAction(){
		$db = new Loan_Model_DbTable_DbLoanILPayment();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			//print_r($data);
			$db->quickPayment($data);
			
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
	function getAllLoanByCoIdAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getAllLoanByCoId($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getLoannumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanIL();
			$row = $db->getLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function getLastPayDateAction(){// get last payment date in loan fundetail by for caculate interest in payoff for client 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLastPayDate($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	
	}
	function getLastPaymentDateByLoanAction(){// get last payment in client reciept money for caculate penalize for client 
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLastPaymentDate($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	
	}

	public function showBarcodesAction(){
		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		if(!empty($id)){
			$ids=explode(',', $id);
			$this->view->pro_id = $ids;
		}
		else{
			//$this->_redirect("/product/index/index");
		}
	
	}
	public function generateBarcodeAction(){
			$loan_code = $this->getRequest()->getParam('loan_code');
	// 		if(!empty($id)){
	// 			$db = new Application_Model_DbTable_DbGlobal();
	// 			$sql=" SELECT p_code FROM tb_product WHERE pro_id = ".$id." LIMIT 1 ";
	// 			$row=$db->getGlobalDbRow($sql);
	// 			$_itemcode=$row["p_code"];
				header('Content-type: image/png');
				
				$this->_helper->layout()->disableLayout();
				//$barcodeOptions = array('text' => "$_itemcode",'barHeight' => 30);
				$barcodeOptions = array('text' => "$loan_code",'barHeight' => 40);
				//'font' => 4(set size of label),//'barHeight' => 40//set height of img barcode
				$rendererOptions = array();
				$renderer = Zend_Barcode::factory(
						'code128', 'image', $barcodeOptions, $rendererOptions
				)->render();
	// 		}
		
		}
	
	function getIlLoanDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getAllIlLoanDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$row = $db->getAllLoanPaymentByLoanNumber($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function getLoanHasPayByLoanNumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanILPayment();
			$loan_number = $data["loan_number"];
			$row = $db->getLaonHasPayByLoanNumber($loan_number);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
}

