<?php
class Report_InstallmentsController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	function indexAction(){
		
	}
	function saleAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'adv_search' => '',
    				'supllier'=>'',
    				'branch_id'=>'',
    				'start_date'=> "",
    				'end_date'=>date('Y-m-d'),
    				'status'=>-1,
    			);
    		}
		$row = $db->getSaleInventory($search);
		$this->view->sale = $row;
		$this->view->search = $search;
		$form=new Installment_Form_FrmSale();
		$form=$form->searchSale();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function saleinvoiceAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db  = new Report_Model_DbTable_DbInventory();
		$row = $db->getSaleInventoryById($id);
		if (empty($row)){
			$this->_redirect("/report/installments");
		}
		$this->view->sale = $row;
		
	}
	function agreementAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db  = new Report_Model_DbTable_DbInventory();
		$row = $db->getSaleInventoryById($id);
		if (empty($row)){
			$this->_redirect("/report/installments");
		}
		$this->view->sale = $row;
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function salescheduleAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db  = new Report_Model_DbTable_DbInventory();
		$row = $db->getSaleInventoryById($id);
		if (empty($row)){
			$this->_redirect("/report/installments");
		}
		$this->view->sale = $row;
		$this->view->schedule = $db->getSaleInventorySchedule($id);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function inventoryAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'adv_search'=>	'',
					'branch_id'	=>	-1,
					'category'	=>	-1,
					'status'	=>  -1
			);
		}
		$row = $db->getInventory($data);
		$this->view->inventory = $row;
		$formFilter = new Installment_Form_FrmProduct();
		$this->view->formFilter = $formFilter->add();
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function purchaseAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'adv_search' => '',
    				'supllier'=>'',
    				'branch_id'=>'',
    				'start_date'=> "",
    				'end_date'=>date('Y-m-d'),
    				'status'=>-1,
    			);
    		}
		$row = $db->getAllInventoryPurchase($search);
		$this->view->purchse = $row;
		$this->view->search = $search;
		$form=new Installment_Form_FrmPurchase();
		$form=$form->searchPurchase();
		Application_Model_Decorator::removeAllDecorator($form);
		$this->view->form_search=$form;
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function purchasedetailAction(){
		$id=$this->getRequest()->getParam('id');
		$id = empty($id)?0:$id;
		$db  = new Report_Model_DbTable_DbInventory();
		$row = $db->getPurchaseById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("Don't have record","/report/installments");
		}
		$this->view->purchase = $row;
		$this->view->purchaseDetail = $db->getPurchseDetail($id);
	}
	function sumarystockAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
			$data = $this->getRequest()->getPost();
		}else{
			$data = array(
					'adv_search'=>	'',
					'branch_id'	=>	-1,
					'category'	=>	-1,
					'status'	=>  -1,
					'start_date'=> '',
					'end_date'=>date('Y-m-d'),
			);
		}
// 		$row = $db->getInventory($data);
// 		$this->view->inventory = $row;
		$summaryStock= $db->getSumaryStock($data);
		$this->view->sumaryStok = $summaryStock;
		$formFilter = new Installment_Form_FrmProduct();
		$this->view->formFilter = $formFilter->add();
		Application_Model_Decorator::removeAllDecorator($formFilter);
		
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function rptPaymentAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
					'adv_search' => '',
					'status_search' => -1,
					'status' => -1,
					'branch_id' => -1,
					'members' => -1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'));
		}
		$this->view->loantotalcollect_list =$rs=$db->getALLInstallmentPayment($search);
		$this->view->list_end_date = $search;
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	function rptPaymentHistoryAction(){
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
					'adv_search' => "",
					'branch_id'	  => -1,
					'members'=>-1,
		 		'currency_type'=>-1,
			 	'start_date'  => date('Y-m-d'),
			 	'end_date'    => date('Y-m-d'),
					'paymnet_type'=> -1,);
		}
		$search['orderBy']="1";
		$this->view->loantotalcollect_list =$db->getALLInstallmentPayment($search);
		$this->view->list_end_date=$search;
	
		$frm = new Pawnshop_Form_FrmPawnshop();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	
	}
}

