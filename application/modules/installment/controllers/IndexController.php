<?php
class Installment_IndexController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
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
					'deposit'=>'',
					'client_name'=> -1,
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
			$db = new Installment_Model_DbTable_DbInstallment();
			$rs_rows= $db->getAllSale($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SALE_NO","CUSTOMER_NAME","PRODUCT_CATEGORY","ITEM_NAME","SELLING_PRICE",
					"SOLD_DATE","INVOICE_NO","SALE_TYPE","STATUS","SCHEDULE_PAYMENT","ADD_PAYMENT");
			$link=array(
					'module'=>'installment','controller'=>'index','action'=>'view',
			);
			$link_info=array('module'=>'installment','controller'=>'index','action'=>'edit',);
			$link_schedule=array('module'=>'report','controller'=>'loan','action'=>'rpt-paymentschedules',);
				
			$link_payment=array('module'=>'installment','controller'=>'payment','action'=>'add',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('បោះពុម្ភ'=>$link_schedule,'Click Here'=>$link_payment,'branch'=>$link,'loan_number'=>$link,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Installment_Form_FrmSearchInstallment();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$db = new Application_Model_DbTable_DbGlobal();
  }
  function addAction()
  {
  	$dbs = new Application_Model_DbTable_DbKeycode();
  	$rsd = $dbs->getKeyCodeMiniInv();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Installment_Model_DbTable_DbInstallment();//new
				$loan_id = $_dbmodel->addSaleInstallment($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/index/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/index/add");
				return $loan_id;
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Installment_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
			
        array_unshift($row_cat,array(
        'id' => -1,
        'name' => 'Add New',
        ) );
        $this->view->rs_cate=$row_cat;
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
	}
// 	public function submitloanAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$_dbmodel = new Loan_Model_DbTable_DbLoandisburse();//new
// 			$loan_id = $_dbmodel->addNewLoanIL($data);
// 			$suc = array('sms'=>'ប្រាក់ឥណទានត្រូវបានបញ្ចូលដោយជោគជ័យ !');
// 			print_r(Zend_Json::encode($loan_id));
// 			exit();
// 		}
// 	}	
	
// 	public function addloanAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db = new Loan_Model_DbTable_DbLoan();
// 			$id = $db->addNewLoanGroup($data);
// 			$suc = array('sms'=>'ប្រាក់ឥណទានត្រូវបានបញ្ចូលដោយជោគជ័យ !');
// 			print_r(Zend_Json::encode($suc));
// 			exit();
// 		}
// 	}
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				$_dbmodel = new Loan_Model_DbTable_DbLoandisburse();
				$_dbmodel->updateLoanById($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/index/index");
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		$rs = $db_g->getLoanFundExist($id);
		if($rs==true){ 	Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/index/index");}
		$db = new Loan_Model_DbTable_DbLoandisburse();
		$row = $db->getTranLoanByIdWithBranch($id,1);
		$frm = new Installment_Form_FrmLoan();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		$this->view->datarow = $row;
		
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->allclient = $db->getAllClient();
		$this->view->allclient_number = $db->getAllClientNumber();
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
	}
	public function viewAction(){
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_FUND","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLoandisburse();
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
// 	function getloanBymemberidAction(){
// 		if($this->getRequest()->isPost()){
// 			$data=$this->getRequest()->getPost();
// 			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
// 			$row=$db->getLoanInfoBymemberId($data['loan_id']);
// 			print_r(Zend_Json::encode($row));
// 			exit();
// 		}
// 	}
//     function getloannumberAction(){
//     			if($this->getRequest()->isPost()){
//     				$data = $this->getRequest()->getPost();
//     				$db = new Application_Model_DbTable_DbGlobal();
// 		            $loan_number = $db->getLoanNumber($data);
//     				print_r(Zend_Json::encode($loan_number));
//     				exit();
//     			}
//     }
	public function testAction($result=null,$table='ln_branch'){
	}
	function addloantestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
				$_dbmodel = new Loan_Model_DbTable_DbLoanILtest();
				$rows_return=$_dbmodel->addNewLoanILTest($_data);
				print_r(Zend_Json::encode($rows_return));
				exit();
		}
	}
// 	function addNewloantypeAction(){
// 	if($this->getRequest()->isPost()){
// 			$data = $this->getRequest()->getPost();
// 			$data['status']=1;
// 			$data['display_by']=1;
// 			$db = new Other_Model_DbTable_DbLoanType();
// 			$id = $db->addViewType($data);
// 			print_r(Zend_Json::encode($id));
// 			exit();
// 		}
// 	}
	/* vandy get Client Installment Information for show on Invoice  */
	function getclientinsinfoAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoanIL();
			$Client = $db->getInstallmentClientInfo($_data['clientID']);
			print_r(Zend_Json::encode($Client));
			exit();
		}
	}
}