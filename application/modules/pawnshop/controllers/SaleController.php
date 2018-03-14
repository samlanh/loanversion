<?php
class Pawnshop_SaleController extends Zend_Controller_Action {
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
						'branch_id' => -1,
						'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
					);
			}
			$db = new Pawnshop_Model_DbTable_DbSale();
			$rs_rows= $db->getAllPawnSale($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","INVOICE_NO","CUSTOMER_NAME","SEX","PHONE",
					"ADDRESS","PRODUCT_NAME","SELLING_PRICE","DESCRIPTION","SELLING_DATE","USER","STATUS");
			
			$link_info=array('module'=>'pawnshop','controller'=>'sale','action'=>'edit',);
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('branch_name'=>$link_info,'invoice_no'=>$link_info,'customer_name'=>$link_info,'pawn_name'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
		}	
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
  	}
  	
  	function addAction(){
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbSale();
				$_dbmodel->addSalePawn($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/pawnshop/sale");
				}else{
					Application_Form_FrmMessage::message("INSERT_SUCCESS","/pawnshop/sale/add");
				}
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				echo $e->getMessage();
			}
		}

		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranchName();
		
	}
	public function editAction(){
		$id = $this->getRequest()->getParam("id");
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Pawnshop_Model_DbTable_DbSale();
				$_dbmodel->updateSalePawn($_data,$id);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/pawnshop/sale");
	
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->branch = $db->getAllBranchName();
		
		$_db = new Pawnshop_Model_DbTable_DbSale();
		$this->view->row = $_db->getPawnSaleById($id);
	}	
	
	function getProductIdByBranchAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Pawnshop_Model_DbTable_DbSale();
			$pro_id = $db->getProductIdByBranch($data['branch_id']);
			print_r(Zend_Json::encode($pro_id));
			exit();
		}
	}
	
	function getProductIdByBranchEditAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Pawnshop_Model_DbTable_DbSale();
			$pro_id = $db->getProductIdByBranchEdit($data['branch_id']);
			print_r(Zend_Json::encode($pro_id));
			exit();
		}
	}
	
	function getProductDetailAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db=new Pawnshop_Model_DbTable_DbSale();
			$pro_id = $db->getProductDetail($data['pawn_id']);
			print_r(Zend_Json::encode($pro_id));
			exit();
		}
	}
	
}

