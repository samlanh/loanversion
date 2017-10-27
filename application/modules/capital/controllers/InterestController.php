<?php
class Capital_InterestController extends Zend_Controller_Action {
	public function init()
	{
		/* Initialize action controller here */
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	
	public function indexAction(){
		try{
			$db = new Capital_Model_DbTable_DbInterestResource ();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'search' => '',
						'status' => -1);
			}
			$rs_rows= $db->getInterest($search);
			//$glClass = new Application_Model_GlobalClass();//status
 			//$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL,true);
			$list = new Application_Form_Frmtable();
			$collumns = array("INTEREST_ANME","INTEREST_VALUE");
			$link=array(
					'module'=>'capital','controller'=>'interest','action'=>'edit'
			);
			$this->view->list=$list->getCheckList(0,$collumns,$rs_rows,array('value'=>$link,'label'=>$link));
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$fm = new Capital_Form_FrmInterest();
		$frm = $fm->frmInterest();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db_acc = new Capital_Model_DbTable_DbInterestResource();
			try {
				$db = $db_acc->add($data);
				if(isset($data["save_close"])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/interest/');
				}elseif (isset($data["save_close"])){
					Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/interest/index');
				} 
				Application_Form_FrmMessage::redirectUrl("/capital/interest/add");
				 
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$fm = new Capital_Form_FrmInterest();
		$frm = $fm->frmInterest();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
	}
	
	public function editAction()
	{
		$db_acc = new Capital_Model_DbTable_DbInterestResource();
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			try {
				$db_acc->updateinterestId($data);
				Application_Form_FrmMessage::Sucessfull("ការ​បញ្ចូល​ជោគ​ជ័យ !",'/capital/interest/index');
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("ការ​បញ្ចូល​មិន​ជោគ​ជ័យ");
				$err =$e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
		}
		$id = $this->getRequest()->getParam("id");
		$row = $db_acc->getinterestId($id);
		
		$fm = new Capital_Form_FrmInterest();
		$frm = $fm->frmInterest($row);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm= $frm;
	}
	
}
