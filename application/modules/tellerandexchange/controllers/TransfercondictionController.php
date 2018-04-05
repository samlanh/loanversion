<?php 

class tellerandexchange_TransfercondictionController extends Zend_Controller_Action
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
    		$db = new Tellerandexchange_Model_DbTable_DbTransferCondiction();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'currencySearch' => -1,
    					'status' => -1,
    					'from_date' =>date('Y-m-d'),
    					'to_date' => date('Y-m-d'),
    			);
    		}
    		$rs_rows= $db->getAllTransferCondiction($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("Currency","From Amount","To Amount","Total Fee","Commision Fee","STATUS");
    		$link=array(
    				'module'=>'tellerandexchange','controller'=>'transfercondiction','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('currencyKH'=>$link,
    				'fromAmount'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Application_Form_FrmAdvanceSearch();
    	$frm = $frm->AdvanceSearch();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    	
    	$pructis=new Tellerandexchange_Form_FrmTransferCondicton();
    	$frm = $pructis->FrmTransferCondicton();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmExchange=$frm;
      
    }
    public function addAction()
    {
    	try{
    		$db_rate=new Tellerandexchange_Model_DbTable_DbTransferCondiction();
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db_rate->addCondictionTransfer($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/transfercondiction");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/transfercondiction/add");
    			}
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    
    
    	}
    	$pructis=new Tellerandexchange_Form_FrmTransferCondicton();
    	$frm = $pructis->FrmTransferCondicton();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmExchange=$frm;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$db_rate=new Tellerandexchange_Model_DbTable_DbTransferCondiction();
    	try{
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db_rate->addCondictionTransfer($data);
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/transfercondiction");
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$row = $db_rate->getTransferCondictionById($id);
    	$this->view->row = $row;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record","/tellerandexchange/transfercondiction");
    	}
    	$pructis=new Tellerandexchange_Form_FrmTransferCondicton();
    	$frm = $pructis->FrmTransferCondicton($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmExchange=$frm;
    }
    public function quickchangeAction()
    {
    	try{
    		$db_rate=new Tellerandexchange_Model_DbTable_DbTransferCondiction();
    		 
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    			//print_r($formdata);exit();
    			$db_rate->setNewRate($formdata);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/transfercondiction/quickchange");
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    
    
    	}
    	 
    	$this->view->ratelist = $db_rate->getCurrentRate();
    	$db_keycode = new Application_Model_DbTable_DbKeycode();
    	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
    	$session_user=new Zend_Session_Namespace('authloan');
    	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
    	
    	$dbEx = new Tellerandexchange_Model_DbTable_Dbexchange();
    	$this->view->allExchangeRate = $dbEx->getAllExchangeRate();
    }
}











