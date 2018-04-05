<?php
class Report_ExchangesController extends Zend_Controller_Action {
    public function init()
    {    	
     /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
  function indexAction(){
  	
  }
  
//   function  rptStaffAction(){
//   	$db  = new Report_Model_DbTable_DbParamater();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	if($this->getRequest()->isPost()){
//   		$data = $this->getRequest()->getPost();
//   		//print_r($db->getAllstaff($data));
//   		if(isset($data['btn_search'])){
//   			//print_r($data);exit();
//   			$this->view->staff_list = $db->getAllstaff($data);
  			
//   		}else{
//   		$collumn = array("CO_CODE","CO_KHNAME","CO_FIRSTNAME","SEX","EMAIL","BASIC_SALARY",
//   				"start_date","end_date","contract_no","shift","workingtime","position","tel",
//   				"basic_salary","national_id","address","degree","branch_name","note");
//   		$this->exportFileToExcel('ln_staff',$db->getAllstaff(),$collumn);
//   		}
//   	}else{
//   		$search = array('txtsearch' => '');
//   		$this->view->staff_list = $db->getAllstaff();
//   	}
//   	$frm=new Other_Form_FrmStaff();
//   	$row=$frm->FrmAddStaff();
//   	Application_Model_Decorator::removeAllDecorator($row);
//   	$this->view->frm_staff=$row;
//   }
//   function  rptVillageAction(){
//   	$db  = new Report_Model_DbTable_DbParamater();
  
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	if($this->getRequest()->isPost()){
//   		$search = $this->getRequest()->getPost();
//   	}else {
//   		$search = array('adv_search' => '',
//   				'search_status' => -1,
//   				'province_name'=>0,
//   				'district_name'=>'',
//   				'commune_name'=>'');
//   	}
//   	$this->view->village_list = $db->getAllVillage($search);
//   	$frm = new Other_Form_FrmVillage();
//   	$frms = $frm->FrmAddVillage();
//   	Application_Model_Decorator::removeAllDecorator($frms);
//   	$this->view->frm_village= $frms;
  	
// //   	$db= new Application_Model_DbTable_DbGlobal();
// //   	$this->view->district = $db->getAllDistricts();
// //   	$this->view->commune_name = $db->getCommune();
//   	$this->view->result = $search;
//   }
//   function rptZoneAction(){
//   	$db  = new Report_Model_DbTable_DbParamater();
//   	$this->view->zone_list = $db->getAllZone();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	$frm = new Other_Form_FrmZone();
//   	$frm_co=$frm->FrmAddZone();
//   	Application_Model_Decorator::removeAllDecorator($frm_co);
//   	$this->view->frm_zone = $frm_co;
//   	if($this->getRequest()->isPost()){
//   		$data = $this->getRequest()->getPost();
//   		//print_r($data);exit();
//   		if(isset($data['btn_search'])){
//   			$this->view->zone_list = $db->getAllZone($data);
//   		}else{
//   		$collumn = array("zone_id","zone_num","modify_date","status");
//   		$this->exportFileToExcel('ln_zone',$db->getAllZone(),$collumn);
//   		}
//   	}else $search = array('txtsearch' => '');
//   }
//   function rptHolidayAction(){
//   	$db  = new Report_Model_DbTable_DbParamater();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	$frm = new Other_Form_FrmHoliday();
//   	$frm = $frm->FrmAddHoliday();
//   	Application_Model_Decorator::removeAllDecorator($frm);
//   	$this->view->frm_holiday = $frm;
//   	if($this->getRequest()->isPost()){
//   		$data = $this->getRequest()->getPost();
//   		if(isset($data['btn_search'])){
//   			//print_r($data);exit();
//   			$this->view->holiday_list = $db->getAllHoliday($data);
//   			$a = $db->getAllHoliday($data);
//   		}else{
//   		//print_r($data);exit();
// 	  		$collumn = array("id","holiday_name","amount_day","start_date","end_date","status","modify_date","note");
// 	  		$this->exportFileToExcel('ln_holiday',$db->getAllHoliday(),$collumn);
//   		}
//   	}else {  		
//   		$data = array('adv_search' => '',
// 						'search_status' => -1,
// 						'start_date'=> date('Y-m-01'),
// 						'end_date'=>date('Y-m-d')); 
//   		$this->view->holiday_list = $db->getAllHoliday($data);
//   	}
//   }
//   public function exportFileToExcel($table,$data,$thead){
//   	$this->_helper->layout->disableLayout();
//   	$db = new Report_Model_DbTable_DbExportfile();
//   	$finalData = $db->getFileby($table,$data,$thead);
  	 
//   	$filename = APPLICATION_PATH . "/tmp/$table-" . date( "m-d-Y" ) . ".xlsx";
//   	$realPath = realpath( $filename );
//   	if ( false === $realPath ){
//   		touch( $filename );
//   		chmod( $filename, 0777 );
//   	}
//   	$filename = realpath( $filename );
//   	$handle = fopen( $filename, "w" );
//   	fputcsv( $handle, $thead, "\t" );
  	 
//   	$this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=utf-8" )
//   	->setRawHeader( "Content-Disposition: attachment; filename=excel.xls" )
//   	->setRawHeader( "Content-Transfer-Encoding: binary" )
//   	->setRawHeader( "Expires: 0" )
//   	->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
//   	->setRawHeader( "Pragma: public" )
//   	->setRawHeader( "Content-Length: " . filesize( $filename ) )
//   	->sendResponse();
  	 
//   	foreach ( $finalData AS $finalRow )
//   	{
//   		fputcsv( $handle,$finalRow, "\t" );
//   	}
  	 
//   	fclose( $handle );
//   	$this->_helper->viewRenderer->setNoRender();
//   	readfile( $filename );//exit();
    
//   }
//   function rptBranchAction(){
//   	$db  = new Report_Model_DbTable_DbParamater();
//   	$this->view->branch_list = $db->getAllBranch();
//   	$key = new Application_Model_DbTable_DbKeycode();
//   	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
//   	$fm = new Other_Form_Frmbranch();
//   	$frm = $fm->Frmbranch();
//   	Application_Model_Decorator::removeAllDecorator($frm);
//   	$this->view->frm_branch = $frm;
//   	if($this->getRequest()->isPost()){
//   		$search = $this->getRequest()->getPost();
//   		if(isset($search['btn_search'])){	
// 	  		$this->view->branch_list = $db->getAllBranch($search);
//   		}else {
//   			$collumn = array("br_id","branch_namekh","branch_nameen","br_address","branch_code","branch_tel",
//   				"status","fax","other","displayby");
//   			$this->exportFileToExcel('ln_branch',$db->getAllBranch(),$collumn);
//   		}
//   	}else $data = array('adv_search' => '');
//   }
  function rptdailyexchangesAction(){
  	$db  = new Report_Model_DbTable_DbExchanges();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'agent_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  			
  	}
  	$this->view->list_end_date=$search;
  	$this->view->CurrentCapital =$db->getCurrentCapitalAgent($search);
  	
  	$usr_mod = new Application_Model_DbTable_DbUsers();
  	$this->view->users = $usr_mod->getUserListSelect();
  	
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function rptLoanXchangeAction(){
  	$db  = new Report_Model_DbTable_DbExchanges();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'adv_search'=>'',
//   				'branch' => '',
//   				'client_name' =>'',
//   				'client_code'=>'',
//   				'Term'=>'',
//   				'status' =>'',
//   				'cash_type'=>'',
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  			
  	}
  	$this->view->list_end_date=$search;
  	$this->view->Loanxchange_list =$db->getAllxchange($search);
  	$this->view->buyin = $db->getTotalExchangeBuyIn($search);
  	$this->view->sellout = $db->getTotalExchangeSellout($search);
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
  function exchangereceiptAction(){
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	$db  = new Report_Model_DbTable_DbExchanges();
  	$id =$this->getRequest()->getParam('id');
  	$id = empty($id)?0:$id;
  	$row = $db->getAllxchangeBYID($id);
  	$this->view->Exchange = $row;
  }
  function capitalsummaryAction(){
  	$db  = new Report_Model_DbTable_DbExchanges();
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}else{
  		$search = array(
  				'agent_id'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  	}
  	$this->view->list_end_date=$search;
  	$this->view->CapitalCapital=$db->getCapitalSummary($search);
  	
  	$currencyEx = $db->getCurrencyFromXchage();
  	$this->view->currencyEx = $currencyEx;
  	$usr_mod = new Application_Model_DbTable_DbUsers();
  	$this->view->users = $usr_mod->getUserListSelect();
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
}

