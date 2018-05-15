<?php
class Installment_GeneralsalesController extends Zend_Controller_Action {
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
					'member'=> -1,
					'branch_id' => -1,
					'status' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
				);
			}
			$db = new Installment_Model_DbTable_DbGeneralSale();
			$rs_rows= $db->getAllSale($search);
			$glClass = new Application_Model_GlobalClass();
			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			$collumns = array("BRANCH_NAME","SALE_NO","CUSTOMER_NAME","TOTAL_AMOUNT","PAID","BALANCE",
					"SOLD_DATE","NOTE","STATUS");
			$link=array(
					'module'=>'installment','controller'=>'index','action'=>'view',
			);
			$link_info=array('module'=>'installment','controller'=>'generalsales','action'=>'edit',);
// 			$link_schedule=array('module'=>'report','controller'=>'loan','action'=>'rpt-paymentschedules',);
				
// 			$link_payment=array('module'=>'installment','controller'=>'payment','action'=>'add',);
// 			'បោះពុម្ភ'=>$link_schedule,'Click Here'=>$link_payment,
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('branchNamekh'=>$link_info,'saleNO'=>$link_info,'name_kh'=>$link_info),0);
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}	
		$frm = new Installment_Form_FrmSearchInstallment();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		$db = new Application_Model_DbTable_DbGlobal();
		$this->view->rs = $search;
		
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
		array_unshift($row_cat,array(
				'id' => -1,
				'name' => 'ជ្រើសរើសប្រភេទ',
		) );
		$this->view->rs_cate=$row_cat;
  }
  function addAction()
  {
  	$dbs = new Application_Model_DbTable_DbKeycode();
  	$rsd = $dbs->getKeyCodeMiniInv();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$_dbmodel = new Installment_Model_DbTable_DbGeneralSale();//new
				$loan_id = $_dbmodel->addGeneralSale($_data);
				if(!empty($_data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/generalsales");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/generalsales/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/generalsales/add");
				return $loan_id;
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$frm = new Installment_Form_FrmGeneralSale();
		$frm_loan=$frm->FrmAddGeneralSale();
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
		
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
        array_unshift($row_cat,array('id' => -1, 'name' => 'ជ្រើសរើសប្រភេទផលិតផល',) );
        $this->view->rs_cate=$row_cat;
        
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
	}
	function getproductAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db=new Installment_Model_DbTable_DbGeneralSale();
			$row=$db->getProductInfo($data);
			print_r(Zend_Json::encode($row));
			exit();
		}
	}
	
	function getreceiptnumberAction(){
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
			$db = new Application_Model_DbTable_DbGlobal();
			$loan_number = $db->getGeneralSaleNumber($data['branch_id']);
			print_r(Zend_Json::encode($loan_number));
			exit();
		}
	}
	function editAction()
	{
		$dbs = new Application_Model_DbTable_DbKeycode();
		$rsd = $dbs->getKeyCodeMiniInv();
		$_dbmodel = new Installment_Model_DbTable_DbGeneralSale();//new
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try {
				$loan_id = $_dbmodel->updateGeneralSale($_data);
				Application_Form_FrmMessage::Sucessfull("UPDATE_SUCCESS","/installment/generalsales");
				return $loan_id;
			}catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$id = $this->getRequest()->getParam('id');
		$row = $_dbmodel->getGeneralsaleById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("EMPTY_RECORD","/installment/generalsales");
		}
		$this->view->row =$row;
		$rowdetail = $_dbmodel->getGeneraldetailSaleById($id);
		$this->view->detail = $rowdetail;
		
		$frm = new Installment_Form_FrmGeneralSale();
		$frm_loan=$frm->FrmAddGeneralSale($row);
		Application_Model_Decorator::removeAllDecorator($frm_loan);
		$this->view->frm = $frm_loan;
	
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
		array_unshift($row_cat,array('id' => -1, 'name' => 'ជ្រើសរើសប្រភេទផលិតផល',) );
		$this->view->rs_cate=$row_cat;
	
		$db = new Setting_Model_DbTable_DbLabel();
		$this->view->setting=$db->getAllSystemSetting();
	}
}