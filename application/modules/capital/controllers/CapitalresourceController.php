<?php
class Capital_CapitalResourceController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Capital_Model_DbTable_DbCapitalResource();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'search' => '',
						'status' => -1);
			}
			$rs_rows= $db->getAllCapitalDetail($search);
			$glClass = new Application_Model_GlobalClass();//status
 			$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL,true);
			$list = new Application_Form_Frmtable();
			$collumns = array("សាខា","ចំនួនប្រាក់ដុល្លា","ចំនួនប្រាក់រៀល","ចំនួនប្រាក់បាត","ចំនួនប្រាក់ដុល្លាពីមុន","ចំនួនប្រាក់រៀលពីមុន","ចំនួនប្រាក់បាតពីមុន","ប្រភេទដើមទុន","ថ្ងៃ","សម្គាល់","ស្ថានភាព");
			$link=array(
					'module'=>'capital','controller'=>'capitalresource','action'=>'edit'
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('branch_namekh'=>$link,'amount_dollar'=>$link,'amount_reil'=>$link,'amount_bath'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Capital_Form_FrmCapitale();
		$frm = $fm->frmSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db_acc = new Capital_Model_DbTable_DbCapitalResource();
			try {
				if(isset($data["save"])){
					$db = $db_acc->addCapitalResource($data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capitalresource/add');
				}elseif (isset($data["save_close"])){
					$db = $db_acc->addCapitalResource($data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capitalresource');
				}else {
					Application_Form_FrmMessage::redirectUrl("/capital/capital");
				}
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Capital_Form_FrmCapitale();
		$frm = $fm->frmCapitalResource();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
		
		/*dddddddddddddddddddddd*/		
	$db  = new Report_Model_DbTable_DbLoan();
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	$search = array(
 				'adv_search' => '',
 				'status_search' => -1,
 				'status' => -1,
 				'branch_id' => "",
 				'client_name' => "",
 				'co_id' => "",
 				'currency_type'=>-1,
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d')
 		);
 	
 	$this->view->loantotalcollect_list =$rs=$db->getCollectDailyPayment($search);
 
 	$this->view->rsincome= $db->getAllOtherIncomeReport($search);//call frome model
 	$this->view->rsexpense= $db->getAllExpenseReport($search);//call frome model
 	$this->view->LoanFee_list =$db->getALLLFee($search);
	}
	function editAction(){
		$db_deposite = new Capital_Model_DbTable_DbCapitalResource();
		if($this->getRequest()->isPost()){
			$_data = $this->getRequest()->getPost();
			try{
				if(isset($_data["save"])){
					$db_deposite->updateCapitalResource($_data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capitalresource/add');
				}elseif (isset($_data["save_close"])){
					$db_deposite->updateCapitalResource($_data);
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/capitalresource');
				}else {
					Application_Form_FrmMessage::redirectUrl("/capital/capitalresource");
				}
			}catch(Exception $e){
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db_deposite->getCapitalDetailById($id);
		$deposite=new Capital_Form_FrmCapitale();
		$frm = $deposite->frmCapital($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm=$frm;
	}
	
}
