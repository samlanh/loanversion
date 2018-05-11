<?php
class Pawnshop_RescheduleController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		$this->_redirect("/pawnshop/reschedule/add");
// 		try{
// 		    if($this->getRequest()->isPost()){
//  				$search = $this->getRequest()->getPost();
//  			}
// 			else{
// 				$search = array(
// 						'txt_search'=>'',
// 						'members'=> -1,
// 						'product_id' => -1,
// 						'branch_id' => -1,
// 						'co_id' => -1,
// 						'status' => -1,
// 						'currency_type'=>-1,
// 						'start_date'=> date('Y-m-d'),
// 						'end_date'=>date('Y-m-d'),);
// 			}
// 			$db = new Pawnshop_Model_DbTable_DbPawnshop();
// 			$rs_rows= $db->getAllPawnshop($search);
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
// 			$list = new Application_Form_Frmtable();
// 			$collumns = array("BRANCH_NAME","PAWN_CODE","CUSTOMER_NAME","RECEIPT","PAWN_AMOUNT","TERM_BORROW",
// 					"INTEREST RATE","PRODUCT_NAME","PAWN_DATE","PAWN_ENDDATE","STATUS");
			
// 			$link_info=array('module'=>'pawnshop','controller'=>'index','action'=>'edit',);
// 			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch'=>$link_info,'loan_number'=>$link_info,'receipt_num'=>$link_info,'client_name_kh'=>$link_info,'client_name_en'=>$link_info,'total_capital'=>$link_info),0);
// 		}catch (Exception $e){
// 			Application_Form_FrmMessage::message("Application Error");
// 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 		}	
// 		$frm = new Pawnshop_Form_FrmPawnshop();
// 		$frm = $frm->FrmAddLoan();
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_search = $frm;
  }
  function addAction()
  {
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbReschedule();
				$pawnshopID = $_dbmodel->addReschedulePawnshop($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop/reschedule/add");
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
		$product = $db->getAllProduct();
		array_unshift($product,array(
		        'id' => -1,
		        'name' => '---Add New ---',
		) );
	    $this->view->product_store=$product;
	    
	    $fm = new Pawnshop_Form_Frmpawnproduct();
	    $frm = $fm->FrmViewType();
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->Form_Frmcallecterall = $frm;
	    
	    $session_user=new Zend_Session_Namespace('authloan');
	    $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
	    $this->view->leveluser = $session_user->level;
	    
	    $key = new Application_Model_DbTable_DbKeycode();
	    $this->view->data=$key->getKeyCodeMiniInv(TRUE);
	    
	    $db_global = new Pawnshop_Model_DbTable_DbPayment();
	    $this->view->loan_number = $db_global->getPawnAccountNumber(1);
	    
	    $id = $this->getRequest()->getParam('id');
	    $id = empty($id)?0:$id;
	    $this->view->id = $id;
	}
	public function editAction(){
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshop();
// 				$_dbmodel->updatePawnshop($_data);
// 				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/pawnshop");
	
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
// 		$db_g = new Application_Model_DbTable_DbGlobal();
// 		$id = $this->getRequest()->getParam('id');
// 		$db = new Pawnshop_Model_DbTable_DbPawnshop();
// 		$row = $db->getPawnshopById($id);
// 		$this->view->rs = $row;
// 		$frm = new Pawnshop_Form_FrmPawnshop();
// 		$frm_loan=$frm->FrmAddLoan($row);
// 		Application_Model_Decorator::removeAllDecorator($frm_loan);
// 		$this->view->frm_loan = $frm_loan;
	
	
// 		$frmpopup = new Application_Form_FrmPopupGlobal();
// 		$this->view->frmpupopinfoclient = $frmpopup->frmPopupindividualclient();
	
// 		$db = new Setting_Model_DbTable_DbLabel();
// 		$this->view->setting=$db->getAllSystemSetting();
	
// 		$db = new Application_Model_DbTable_DbGlobal();
// 		$product = $db->getAllProduct();
// 		array_unshift($product,array(
// 		        'id' => -1,
// 		        'name' => '---Add New ---',
// 		) );
// 	    $this->view->product_store=$product;
	    
// 	    $fm = new Pawnshop_Form_Frmpawnproduct();
// 	    $frm = $fm->FrmViewType();
// 	    Application_Model_Decorator::removeAllDecorator($frm);
// 	    $this->view->Form_Frmcallecterall = $frm;
	}	
	function getloanBybranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Pawnshop_Model_DbTable_DbPawnshop();
			$pro_id = $db->getPawnIdByBranch($data['branch_id']);
			print_r(Zend_Json::encode($pro_id));
			exit();
		}
	}
	function getproductDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Pawnshop_Model_DbTable_DbSale();
			$pro_id = $db->getProductDetail($data['pawn_id']);
			print_r(Zend_Json::encode($pro_id));
			exit();
		}
	}
}

