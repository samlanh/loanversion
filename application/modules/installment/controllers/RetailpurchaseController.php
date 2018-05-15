<?php
class Installment_RetailpurchaseController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
	private $type = array(1=>'service',2=>'program');
	public function init()
	{
		$this->tr = Application_Form_FrmLanguages::getCurrentlanguage();
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function start(){
		return ($this->getRequest()->getParam('limit_satrt',0));
	}
	public function indexAction(){
		try{
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
			$db =  new Installment_Model_DbTable_DbRetailPurchase();
			$rows = $db->getAllSupPurchase($search);
			$rs_rows=new Application_Model_GlobalClass();
			$rs_rows=$rs_rows->getImgActive($rows, BASE_URL);
			$list = new Application_Form_Frmtable();
// 			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 			$RECEIPT = $tr->translate("RECEIPT");
			$collumns = array("BRANCH","INVOICE_NO","SUPPLIER_NO","SUPPLIER_NAME","TEL","EMAIL","AMOUNT_DUE","DATE","STATUS");
			$link=array(
					'module'=>'installment','controller'=>'retailpurchase','action'=>'edit',
			);
// 			$link1=array(
// 					'module'=>'report','controller'=>'installments','action'=>'retailreceipt',
// 			);$RECEIPT=>$link1,
			$this->view->list=$list->getCheckList(10, $collumns, $rs_rows,array('invoice_no'=>$link,'branch_namekh'=>$link,'sup_name'=>$link,'supplier_no'=>$link,));
			}catch (Exception $e){
				echo $e->getMessage();exit();
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
			$form=new Installment_Form_FrmPurchase();
			$form=$form->searchPurchase();
			Application_Model_Decorator::removeAllDecorator($form);
			$this->view->form_search=$form;
		
	}
	public function addAction(){
	if($this->getRequest()->isPost()){
		$_data = $this->getRequest()->getPost();
		try{
				$db = new Installment_Model_DbTable_DbRetailPurchase();
				$row = $db->addPurchase($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/retailpurchase");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/installment/retailpurchase/add");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
		$_pur = new Installment_Model_DbTable_DbRetailPurchase();
		$pro=$_pur->getProductName();
		array_unshift($pro, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->product= $pro;
		
		$this->view->pu_code=$_pur->getPurchaseCode();
		$this->view->sup_ids=$_pur->getSuplierName();
		$this->view->buyer_ids=$_pur->getBuyerName();
// 		$this->view->bran_name=$_pur->getAllBranchName();
		
// 		$_pro = new Installment_Model_DbTable_DbProduct();
// 		$this->view->pro_code=$_pro->getProCode();
// 		$pro_cate = $_pro->getProductCategory();
// 		array_unshift($pro_cate, array('id'=>'-1' , 'name'=>$this->tr->translate("ADD_NEW")));
// 		$this->view->cat_rows = $pro_cate;
		
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchName();
		array_unshift($branch, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->bran_name = $branch;
		
// 		$fm = new Global_Form_Frmbranch();
// 		$frm = $fm->Frmbranch();
// 		Application_Model_Decorator::removeAllDecorator($frm);
// 		$this->view->frm_branch = $frm;
// 		echo 333;exit();
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$opt_status = $dbgb->getVewOptoinTypeByType(11);
		$this->view->sex = $opt_status;
	}
	public function editAction(){
		$id=$this->getRequest()->getParam('id');
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
// 			$_data['id']=$id;
			try{
				$db = new Installment_Model_DbTable_DbRetailPurchase();
				$row = $db->updatePurchase($_data);
				if(isset($_data['save_close'])){
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/installment/retailpurchase");
				}else{
					Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS","/installment/retailpurchase");
				}
				Application_Form_FrmMessage::message("INSERT_SUCCESS");
			}catch(Exception $e){
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
				echo $e->getMessage();
			}
		}
		$_pur = new Installment_Model_DbTable_DbRetailPurchase();
		$row = $_pur->getPurchaseByID($id);
		if (empty($row)){
			Application_Form_FrmMessage::Sucessfull("EMPTY_RECORD","/installment/retailpurchase");
		}
		$this->view->purchase = $row;
		$this->view->purchaseDetail = $_pur->getPurchaseDetailByID($id);
		
		$pro=$_pur->getProductName();
		array_unshift($pro, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->product= $pro;
		
		$this->view->pu_code=$_pur->getPurchaseCode();
		$this->view->sup_ids=$_pur->getSuplierName();
		$this->view->buyer_ids=$_pur->getBuyerName();
		
		$model = new Application_Model_DbTable_DbGlobal();
		$branch = $model->getAllBranchName();
		array_unshift($branch, array ( 'id' => -1,'name' =>$this->tr->translate("ADD_NEW")));
		$this->view->bran_name = $branch;
		
		$dbgb = new Application_Model_DbTable_DbGlobal();
		$opt_status = $dbgb->getVewOptoinTypeByType(11);
		$this->view->sex = $opt_status;
	}

    function getSupplierInfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Installment_Model_DbTable_DbRetailPurchase();
    		$row = $db->getSuplierInfo($data['sup_id']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
    function getbuyerinfoAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Installment_Model_DbTable_DbRetailPurchase();
    		$row = $db->getBuyerInfo($data['buyer_id']);
    		//array_unshift($makes, array ( 'id' => -1, 'name' => 'បន្ថែមថ្មី') );
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    }
    function addProductAction(){
    	if($this->getRequest()->isPost()){
    		$_data = $this->getRequest()->getPost();
    		$_dbmodel = new Installment_Model_DbTable_DbRetailPurchase();
    		$id = $_dbmodel->ajaxAddProduct($_data);
    		print_r(Zend_Json::encode($id));
    		exit();
    	}
    }

}