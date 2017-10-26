<?php

class Tellerandexchange_IncomeController extends Zend_Controller_Action
{
	const REDIRECT_URL = '/tellerandexchange/expense';
	
    public function init()
    {
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Accounting_Model_DbTable_DbTransactionIncome();
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    		}
    		else{
    			$formdata = array(
    					"adv_search"=>'',
    					"currency_type"=>-1,
    					'status'=>1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		
			$rs_rows= $db->getAllExpense($formdata);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("BRANCH_NAME","TITLE","CURRENCY","Invoice","​AMT_PAY","BRANCH_NOTE","DATE","STATUS");
    		$link=array(
    				'module'=>'tellerandexchange','controller'=>'income','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('branch_name'=>$link,'account_id'=>$link,'total_amount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Loan_Form_FrmSearchLoan();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Accounting_Model_DbTable_DbTransactionIncome();				
			try {
				$db = $db->addexpense($data);
				if(!empty($data['saveclose'])){
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/income");
				}else{
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/income/add");
				}
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/income/add");
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("INSERT_FAIL");
				Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
			}
		}
    	$pructis=new Accounting_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
    }
 
    public function editAction()
    {
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			$db = new Accounting_Model_DbTable_DbTransactionIncome();				
			try {
				$db = $db->updatExpense($data);				
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/income");	
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
		$id = $this->getRequest()->getParam('id');
		$db = new Accounting_Model_DbTable_DbTransactionIncome();
		$row  = $db->getexpensebyid($id);
    	$pructis=new Accounting_Form_Frmexpense();
    	$frm = $pructis->FrmAddExpense($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_expense=$frm;
		
    	
    }

}







