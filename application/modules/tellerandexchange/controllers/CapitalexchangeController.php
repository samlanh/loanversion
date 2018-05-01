<?php

class Tellerandexchange_CapitalexchangeController extends Zend_Controller_Action
{
	const REDIRECT_URL='/tellerandexchange/capitalexchange';
	public function init()
	{
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL') || define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
	public function indexAction(){
		try{
// 			$session_transfer=new Zend_Session_Namespace('search_xhcange');
// 			$session_user=new Zend_Session_Namespace('authloan');
// 			$user_id = $session_user->user_id;
			
			$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
			if($this->getRequest()->isPost()){
				$search=$this->getRequest()->getPost();
			}
			else{
				$search = array(
						'agent_id' => '',
						'status' => -1,
						'from_date' =>date('Y-m-d'),
						'to_date' => date('Y-m-d'),
				);
			}
			$this->view->list_search=$search;
			$rs_rows= $db->getCapitalAgency($search);
			
// 			$glClass = new Application_Model_GlobalClass();
// 			$rs_rows = $glClass->getImgActive($rs_row, BASE_URL, true);
			$list = new Application_Form_Frmtable();
			
			$collumns = array("Agent Name","Currency","Amount","Date");
			$link=array(
					'module'=>'tellerandexchange','controller'=>'capitalexchange','action'=>'edit',
			);
// 			$tr = Application_Form_FrmLanguages::getCurrentlanguage();
// 			$exchange=$tr->translate("Exchange Receipt");
// 			'agency'=>$link,'currencyKH'=>$link
			$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array());
			
			$usr_mod = new Application_Model_DbTable_DbUsers();
			$this->view->users = $usr_mod->getUserListSelect();
// 			$this->view->user_id = $user_id;
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Application_Form_FrmAdvanceSearch();
		$frm = $frm->AdvanceSearch();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm_search = $frm;
	}
	public function addAction()
	{
		$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
		try {
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db->addCapitalCurrency($data);
				if(!empty($data['save_new'])){
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/add');
				}else{	
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL);
				}
			}
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Tellerandexchange_Form_FrmCapitalAgent();
		$frm = $frm->FrmCapitalAgent();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function getcurrencyrowAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
			$rs = $db->getCurrencyRow($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	public function editAction()
	{
		$this->_redirect(self::REDIRECT_URL);
		$id = $this->getRequest()->getParam('id',0);
		$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
		try {
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db->editCapitalCurrency($data);
				$this->_redirect(self::REDIRECT_URL);
					Application_Form_FrmMessage::Sucessfull('EDIT_SUCESS', self::REDIRECT_URL);
			}
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$thisrow = $db->getCapitalByID($id);
		if (empty($thisrow)){
			Application_Form_FrmMessage::Sucessfull('No Record', self::REDIRECT_URL);
		}
		$this->view->capitaldetail = $db->getCapitalByAgentIDAndDate($thisrow);
		$frm = new Tellerandexchange_Form_FrmCapitalAgent();
		$frm = $frm->FrmCapitalAgent($thisrow);
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function getcurrencyroweditAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
			$rs = $db->getCurrencyRowEdit($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
//  public function editAction()
//  {
//  	$id = $this->getRequest()->getParam('id',0);
//  	$db_exc=new Tellerandexchange_Model_DbTable_DbxChangeMoney();
//  	if($this->getRequest()->isPost()){
//  		$formdata=$this->getRequest()->getPost();
 		
//  		try {
// //  			$formdata['id']=$id;
//  				$id = $db_exc->editExchange($formdata);
//  				Application_Form_FrmMessage::message("EDIT_SUCESS");
//  				$this->_redirect('tellerandexchange/xchanges');
//  		} catch (Exception $e) {
//  			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
//  			echo $e->getMessage();exit();
//  		}
//  	}
//  	// action body
//  	//Get value from url
 	
//  	$session_user=new Zend_Session_Namespace('authloan');
//  	$this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
 	
//  	$db_keycode = new Application_Model_DbTable_DbKeycode();
//  	$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
 	
//  	$cur = new Application_Model_DbTable_DbCurrencies();
//  	$currency = $cur->getCurrencyList();
 	
//  	$this->view->currency = $this->_helpfilteroption($currency);
// //  	$this->view->inv_no = Application_Model_GlobalClass::getInvoiceNo();
 	
//  	$rs=$db_exc->getxchangById($id);
// //  	print_r($rs);

//  	$this->view->dataedit=$rs;
 	
//  	$dbEx = new Tellerandexchange_Model_DbTable_Dbexchange();
//  	$this->view->allExchangeRate = $dbEx->getAllExchangeRate();
//  }
 
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
	function gettoexchangeAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Tellerandexchange_Model_DbTable_Dbexchange();
			$rs = $db->getToExchange($data['from_amount_type']);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	function getratevalueAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Tellerandexchange_Model_DbTable_Dbexchange();
			$rs = $db->getExchangeRateValue($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
	
	public function withdrawalAction()
	{
		$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
		try {
			if($this->getRequest()->isPost()){
				$data=$this->getRequest()->getPost();
				$db->withdrawalcapital($data);
				if(!empty($data['save_new'])){
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL . '/add');
				}else{
					Application_Form_FrmMessage::Sucessfull('INSERT_SUCCESS', self::REDIRECT_URL);
				}
			}
		}catch (Exception $e){
			Application_Form_FrmMessage::message("Application Error");
			echo $e->getMessage();exit();
			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		}
		$frm = new Tellerandexchange_Form_FrmCapitalAgent();
		$frm = $frm->FrmCapitalAgent();
		Application_Model_Decorator::removeAllDecorator($frm);
		$this->view->frm = $frm;
	}
	function getcurrencyrowwithdrawalAction(){
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			$db = new Tellerandexchange_Model_DbTable_DbCapitalAgent();
			$rs = $db->getCurrencyRowEditWithdrawal($data);
			print_r(Zend_Json::encode($rs));
			exit();
		}
	}
  }