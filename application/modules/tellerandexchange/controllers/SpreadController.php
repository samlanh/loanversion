<?php 

class tellerandexchange_SpreadController extends Zend_Controller_Action
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
    		$db = new Tellerandexchange_Model_DbTable_DbSpread();
    		if($this->getRequest()->isPost()){
    			$search=$this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'client_id' => -1,
    					'status' => -1,
    					'from_date' =>date('Y-m-d'),
    					'to_date' => date('Y-m-d'),
    			);
    		}
    		$rs_rows= $db->getAllSpreadList($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("ពីប្រាក់","ទៅប្រាក់","ទិញចូល","លក់ចេញ","អត្រាកណ្តាល","កាលបរិច្ឆេទ","STATUS");
    		$link=array(
    				'module'=>'tellerandexchange','controller'=>'spread','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('buy_type'=>$link,
    				'sell_type'=>$link,'rate_in'=>$link,'rate_out'=>$link));
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

 
    public function checkRateAction(){
    	$db_rate=new Application_Model_DbTable_DbRate();
    	echo $db_rate->getCurrentRateJson();
    	exit();
    }
    protected function _helpfilteroption($data){
    	$tmp = array();
    	foreach ($data as $i =>$d){
    		foreach ($d as $key => $val){
    			$tmp[$i][$key] = $val;
    		}
    		$bath=0; $rail=0; $dollar=0;
    		if($d['symbol'] === "$"){
    			$bath=1; $rail=1;
    		}
    		elseif($d['symbol'] === "B"){
    			$dollar=1; $rail=1;
    		}
    		elseif($d['symbol'] === "R"){
    			$bath=1; $dollar=1;
    		}
    		$tmp[$i]["dollar"] = $dollar;
    		$tmp[$i]["bath"] = $bath;
    		$tmp[$i]["rail"] = $rail;
    	}
    	return $tmp;
    }
   
    public function addAction()
    {
    	try{
    		$db_rate=new Tellerandexchange_Model_DbTable_DbSpread();
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db_rate->addNewCurrencyExchange($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/spread");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/spread/add");
    			}
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    
    
    	}
    	$pructis=new Tellerandexchange_Form_FrmCurrencyExchange();
    	$frm = $pructis->FrmCurrencyExchange();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmExchange=$frm;
    }
    public function editAction()
    {
    	$id = $this->getRequest()->getParam('id');
    	$db_rate=new Tellerandexchange_Model_DbTable_DbSpread();
    	try{
    		if($this->getRequest()->isPost()){
    			$data=$this->getRequest()->getPost();
    			$db_rate->addNewCurrencyExchange($data);
    			if(!empty($data['saveclose'])){
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/spread");
    			}else{
    				Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/spread/add");
    			}
    		}
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		echo $e->getMessage();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    
    
    	}
    	$row = $db_rate->getSpreadById($id);
    	$this->view->row = $row;
    	if (empty($row)){
    		Application_Form_FrmMessage::Sucessfull("No Record","/tellerandexchange/spread");
    	}
    	$pructis=new Tellerandexchange_Form_FrmCurrencyExchange();
    	$frm = $pructis->FrmCurrencyExchange($row);
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frmExchange=$frm;
    }
//     public function editAction()
//     {
//     	$db_rate=new Tellerandexchange_Model_DbTable_DbSpread();
//     	if($this->getRequest()->isPost()){
//     		$formdata=$this->getRequest()->getPost();
//     		$db_rate->setNewRate($formdata);
//     	}
//     	$this->view->ratelist = $db_rate->getCurrentRate();
//     	$db_keycode = new Application_Model_DbTable_DbKeycode();
//     	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
//     	$session_user=new Zend_Session_Namespace('authloan');
//     	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
//     }
    public function incurandoutcuranAction(){
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Tellerandexchange_Model_DbTable_DbSpread();
    		$rs = $db->CheckCurrencyInCurrencyOut($data);
    		print_r(Zend_Json::encode($rs));
    		exit();
    	}
    }
    public function quickchangeAction()
    {
    	try{
    		$db_rate=new Tellerandexchange_Model_DbTable_DbSpread();
    		 
    		if($this->getRequest()->isPost()){
    			$formdata=$this->getRequest()->getPost();
    			//print_r($formdata);exit();
    			$db_rate->setNewRate($formdata);
    			Application_Form_FrmMessage::Sucessfull("EDIT_SUCESS","/tellerandexchange/spread/quickchange");
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











