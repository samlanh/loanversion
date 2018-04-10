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
	function nearlyourstockAction(){
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
		$row = $db->productNearlyOutStock($data);
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
		
		$frm = new Installment_Form_FrmInstallment();
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
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		
		$frm = new Installment_Form_FrmInstallment();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	function rptLoanOutstandingAction(){//loand out standing with /collection
		$db  = new Report_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}else {
			$search = array(
					'adv_search'=>'',
					'branch_id'=>'',
					'members'=>'',
					'category'=>-1,
					'currency_type'=>-1,
					'status_use'=>-1,
					'end_date'=>date('Y-m-d'));
		}
		$this->view->fordate = $search['end_date'];
		$this->view->outstandloan= $db->getAllOutstadingLoan($search);
		$frm = new Loan_Form_FrmSearchLoan();
	
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	
		$frm = new Installment_Form_FrmInstallment();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$formFilter = new Installment_Form_FrmProduct();
		$this->view->formFilter = $formFilter->add();
		Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	function rptLoancollectAction(){//list payment that collect from client
		$dbs = new Report_Model_DbTable_DbInventory();
		$frm = new Application_Form_FrmSearchGlobal();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
					'branch_id'=>0,
					'members'=>-1,
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'status' => -1,);
		}
// 		$db  = new Report_Model_DbTable_DbLoan();
		$this->view->date_show=$search['end_date'];
		$this->view->list_end_date=$search;
		
		$row = $dbs->getAllLnClient($search);
		$this->view->tran_schedule=$row;
		$this->view->loanlate_list =$dbs->getALLLoanlate($search);
	
		$this->view->list_end_dates = $search["end_date"];
		
		$frm = new Installment_Form_FrmInstallment();
		$frm = $frm->FrmAddLoan();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
		
		$formFilter = new Installment_Form_FrmProduct();
		$this->view->formFilter = $formFilter->add();
		Application_Model_Decorator::removeAllDecorator($formFilter);
		 
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	}
	function rptIncomestatementAction(){
		$key = new Application_Model_DbTable_DbKeycode();
		$this->view->data=$key->getKeyCodeMiniInv(TRUE);
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
			$search['status']=-1;
		}else{
			$search = array(
						
					'start_date'=> date('Y-m-d'),
					'end_date'=>date('Y-m-d'),
					'branch_id'		=>	-1,
					"currency_type"=>0,);
		}
	
		$income = array(
			'principal_paid'=>0,
 			'interest_paid'=>0,
 			'penalize_paid'=>0,
 			'service_paid'=>0,
 			'adminfee'=>0,
 			'other_fee'=>0,
 			'other_income'=>0,
 			'expense'=>0,
 			'badloan'=>0,
 			
 		);
		$dbInsta  = new Report_Model_DbTable_DbInventory();
		$InstallmentCollect = $dbInsta->getInstallmentCollectIcome($search);
		if (!empty($InstallmentCollect)) foreach ($InstallmentCollect as $rs){
			$income['interest_paid']=$rs['interest_paid'];
			$income['penalize_paid']=$rs["penalize_paid"];
			$income['principal_paid']=$rs["principal_paid"];
		}
		
	
		$this->view->rsincome=$income;
		$this->view->list_end_date=$search;
	
		$frm = new Loan_Form_FrmSearchGroupPayment();
		$fm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($fm);
		$this->view->frm_search = $fm;
	}
}

