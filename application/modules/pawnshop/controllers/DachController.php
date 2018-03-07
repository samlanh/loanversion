<?php
class Pawnshop_DachController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	private $sex=array(1=>'M',2=>'F');
	public function indexAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshopdach();
				$_dbmodel->addPawnshopdach($_data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop");

			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db_g = new Application_Model_DbTable_DbGlobal();
		$id = $this->getRequest()->getParam('id');
		$db = new Pawnshop_Model_DbTable_DbPawnshop();
		$row = $db->getPawnshopById($id);
		$this->view->rs = $row;
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm_loan=$frm->FrmAddLoan($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm_loan = $frm_loan;

		$frmpopup = new Application_Form_FrmPopupGlobal();

		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();

		$db = new Application_Model_DbTable_DbGlobal();
		$product = $db->getAllProduct();
	    $this->view->product_store=$product;
 
	    $fm = new Pawnshop_Form_Frmpawnproduct();
	    $frm = $fm->FrmViewType();
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->Form_Frmcallecterall = $frm;
  }
  function addAction()
  {
// 		if($this->getRequest()->isPost()){
// 			$_data = $this->getRequest()->getPost();
// 			try {
// 				$_dbmodel = new Pawnshop_Model_DbTable_DbPawnshop();
// 				$_dbmodel->addPawnshop($_data);
// 				if(!empty($_data['saveclose'])){
// 					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop");
// 				}else{
// 					Application_Form_FrmMessage::message("INSERT_SUCCESS");
// 				}
// 			}catch (Exception $e) {
// 				Application_Form_FrmMessage::message("INSERT_FAIL");
// 				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
// 			}
// 		}
// 		$frm = new Pawnshop_Form_FrmPawnshop();
// 		$frm_loan=$frm->FrmAddLoan();
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
}

