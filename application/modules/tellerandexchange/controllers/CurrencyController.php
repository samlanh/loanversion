<?php 

class tellerandexchange_CurrencyController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/exchange';
	
    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
    	//clear all other sessions
    	Application_Form_FrmSessionManager::clearSessionSearch();
    	
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }

    public function indexAction()
    {
    	try{
    		$db = new Tellerandexchange_Model_DbTable_DbCurrency();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'status' => -1,
    					'from_date' =>date('Y-m-d'),
    					'to_date' => date('Y-m-d'),
    			);
    		}
    		$this->view->list_search = $search;
    		$rs_rows= $db->getAllCurrencyList($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("Currency KH","Currency EN","SYMBOL","STATUS");
    		$link=array(
    				'module'=>'tellerandexchange','controller'=>'currency','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('curr_namekh'=>$link,'curr_nameen'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Application_Form_FrmAdvanceSearch();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
      
    }
    public function addAction()
    {
    	try{
    		$db_rate=new Tellerandexchange_Model_DbTable_DbCurrency();
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db_rate->addCurrency($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/currency");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/currency/add");
    			}
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$pructis=new Tellerandexchange_Form_FrmCurrency();
    	$frm = $pructis->FrmCurrency();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmcurrency=$frm;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$db=new Tellerandexchange_Model_DbTable_DbCurrency();
    	try{
    		
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db->addCurrency($data);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/currency");
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$row = $db->getCurrencyBYID($id);
    	$this->view->row = $row;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record","/tellerandexchange/currency");
    	}
    	$pructis=new Tellerandexchange_Form_FrmCurrency();
    	$frm = $pructis->FrmCurrency($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmcurrency=$frm;
    	
    }

}











