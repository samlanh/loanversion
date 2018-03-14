<?php
class Reports_InstallmentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	function indexAction(){
		
	}
	function inventoryAction(){
		$db  = new Reports_Model_DbTable_DbInventory();
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
		$db  = new Reports_Model_DbTable_DbInventory();
		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search=array(
    				'adv_search' => '',
    				'supllier'=>'',
    				'branch_id'=>'',
    				'start_date'=> date('Y-m-d'),
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
		$db  = new Reports_Model_DbTable_DbInventory();
		$row = $db->getPurchaseById($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("Don't have record","/reports/installment/");
		}
		$this->view->purchase = $row;
		$this->view->purchaseDetail = $db->getPurchseDetail($id);
	}
}

