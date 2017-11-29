<?php
class Pawnshop_IndexController extends Zend_Controller_Action {
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
			$db = new Pawnshop_Model_DbTable_DbPawnshop();
			$rs_rows= $db->getAllIndividuleLoan($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","PAWN_CODE","CUSTOMER_NAME","COMUNE_NAME_EN","RECEIPT_NO","PAWN_AMOUNT","TERM_BORROW",
					"INTEREST_RATE","TOTAL_INTEREST","PAWN_DATE","PAWN_ENDDATE","STATUS");
			
			$link_info=array('module'=>'pawnshop','controller'=>'index','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link_info,'saving_number'=>$link_info,'payment_method'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
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
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshop();
				$_dbmodel->addPawnshop($_data);
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
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm_loan=$frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;

		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->frmpupopinfoclient = $frmpopup->frmPopupindividualclient();
		
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
		        'id' => -1,
		        'name' => '---Add New ---',
		) );
	    $this->view->co_name=$co_name;
	}	
	public function addloanAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Loan_Model_DbTable_DbLoan();
			$id = $db->addNewLoanGroup($data);
			$suc = array('sms'=>'ប្រាក់ឥណទានត្រូវបានបញ្ចូលដោយជោគជ័យ !');
			print_r(Zend_Json::encode($suc));
			exit();
		}
	}
	public function editAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshop();
				$_dbmodel->addPawnshop($_data);
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
		$db_g = new Application_Model_DbTable_DbGlobal();
		$id = $this->getRequest()->getParam('id');
		$db = new Pawnshop_Model_DbTable_DbPawnshop();
		$row = $db->getPawnshopById($id);
		
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;
		
		$frmpopup = new Application_Form_FrmPopupGlobal();
		$this->view->frmpupopinfoclient = $frmpopup->frmPopupindividualclient();
		
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
		
		$db = new Application_Model_DbTable_DbGlobal();
		$co_name = $db->getAllCoNameOnly();
		array_unshift($co_name,array(
				'id' => -1,
				'name' => '---Add New ---',
		) );
		$this->view->co_name=$co_name;
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try{
// 				$_dbmodel = new Loan_Model_DbTable_DbLoanIL();
// 				$_dbmodel->updateLoanById($_data);
// 				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/loan/index/index");
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($err =$e->getMessage());
// 			}
// 		}
// 		$id = $this->getRequest()->getParam('id');
// 		$db_g = new Application_Model_DbTable_DbGlobal();
// 		$rs = $db_g->getLoanFundExist($id);
// 		if($rs==true){ 	Application_Form_FrmMessage::Sucessfull("LOAN_FUND_EXIST","/loan/index/index");}
// 		$db = new Loan_Model_DbTable_DbLoanIL();
// 		$row = $db->getTranLoanByIdWithBranch($id,1);
// 		$frm = new Loan_Form_FrmLoan();
// 		$frm_loan=$frm->FrmAddLoan($row);
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_loan = $frm_loan;
// 		$this->view->datarow = $row;
		
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$this->view->allclient = $db->getAllClient();
// 		$this->view->allclient_number = $db->getAllClientNumber();

	}
	public function viewAction(){
// 		$this->_helper->layout()->disableLayout();
		$id = $this->getRequest()->getParam('id');
		$db_g = new Application_Model_DbTable_DbGlobal();
		if(empty($id)){
			Application_Form_FrmMessage::Sucessfull("RECORD_NOT_FUND","/loan/index/index");
		}
		$db = new Loan_Model_DbTable_DbLoanIL();
		$row = $db->getLoanviewById($id);
		$this->view->tran_rs = $row;
	}
	function getLoanlevelAction(){
		if($this->getRequest()->isPost()){
				$data = $this->getRequest()->getPost();
				$db = new Loan_Model_DbTable_DbLoanIL();
				$row = $db->getLoanLevelByClient($data['client_id'],$data['type']);
				print_r(Zend_Json::encode($row));
			    exit();
		}
		
	}
	public function getLoaninfoAction(){//from repayment schedule
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getLoanInfo($data['loan_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	function getloanBymemberidAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Loan_Model_DbTable_DbRepaymentSchedule();
			$row=$db->getLoanInfoBymemberId($data['member_id']);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}

	public function testAction($result=null,$table='ln_branch'){

	}
	function addpawnshoptestAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshop();
			$rows_return=$_dbmodel->addPawnshoptest($_data);
			print_r(Zend_Json::encode($rows_return));
			exit();
		}
		
	}
	function getloannumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$loan_number = $db->getPawnshoNumber($data);
			print_r(Zend_Json::encode($loan_number));
			exit();
		}
	}
function addNewloantypeAction(){
	if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$data['status']=1;
			$data['display_by']=1;
			$db = new Other_Model_DbTable_DbLoanType();
			$id = $db->addViewType($data);
			print_r(Zend_Json::encode($id));
			exit();
		}
	}
}
