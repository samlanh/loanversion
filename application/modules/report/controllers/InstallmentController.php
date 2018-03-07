<?php
class Report_InstallmentController extends Zend_Controller_Action {
	private $activelist = array('មិនប្រើ​ប្រាស់', 'ប្រើ​ប្រាស់');
    public function init()
    {    	
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}
function indexAction(){
  	
}
function rptLoanDisburseAction(){//release all loan
  	$db  = new Report_Model_DbTable_Dbpawn();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'adv_search'=>'',
  				'branch_id'=>'',
  				'members'=>'',
  				'product_id'=>-1,
  				'currency_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'));
  	}
  	$this->view->loanrelease_list=$db->getAllLoan($search);
  	$this->view->list_end_date=$search;
  	
  	$frm = new Pawnshop_Form_FrmPawnshop();
  	$frm = $frm->FrmAddLoan();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  } 
function rptLoancollectAction(){//list payment that collect from client
  	$dbs = new Report_Model_DbTable_DbpawnCollect();
  	$frm = new Application_Form_FrmSearchGlobal();
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();
  	}
  	else{
  		$search = array(
  				'branch_id'=>0,
  				'client_name'=>'',
  				'co_id'=>0,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d'),
  				'status' => -1,);
  	}
  	$db  = new Report_Model_DbTable_Dbpawn();
  	$this->view->date_show=$search['end_date'];
  	$this->view->list_end_date=$search;
  	
  	$row = $dbs->getAllLnClient($search);
  	
  	$this->view->tran_schedule=$row;
  	$this->view->loanlate_list =$db->getALLLoanlate($search);
  	 
  	$this->view->list_end_dates = $search["end_date"];
  	
  	$frm = new Pawnshop_Form_FrmPawnshop();
  	$frm = $frm->FrmAddLoan();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  }
  
function rptPaymentAction(){
  	$db  = new Report_Model_DbTable_Dbpawn();	
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	if($this->getRequest()->isPost()){
		$search = $this->getRequest()->getPost();
	}else {
		$search = array(
			'adv_search' => '',
			'status_search' => -1,
			'status' => -1,
			'branch_id' => -1,
			'members' => -1,
			'start_date'=> date('Y-m-d'),
			'end_date'=>date('Y-m-d'));
	}
	$this->view->loantotalcollect_list =$rs=$db->getALLLoanPayment($search);
	$this->view->list_end_date = $search;
	
	$frm = new Pawnshop_Form_FrmPawnshop();
	$frm = $frm->FrmAddLoan();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
}
  
function rptLoanLateAction(){
  	if($this->getRequest()->isPost()){
  		$search = $this->getRequest()->getPost();		
  	}else {
  		$search = array(
  				'adv_search'		=>	"",
  				'end_date' => date('Y-m-d'),
  				'status' => -1,
  				'branch_id'		=>	0,
  				'co_id'=>0
  		);
  	}
  	$db  = new Report_Model_DbTable_Dbpawn();
  	$this->view->loanlate_list =$db->getALLLoanlate($search);
  	
  	$key = new Application_Model_DbTable_DbKeycode();
  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
  	
  	$this->view->list_end_date = $search["end_date"];
  	
  	$frm = new Loan_Form_FrmSearchLoan();
  	$frm = $frm->AdvanceSearch();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;
  }
function rptLoanOutstandingAction(){//loand out standing with /collection
	    $db  = new Report_Model_DbTable_Dbpawn();
	  	if($this->getRequest()->isPost()){
	  		$search = $this->getRequest()->getPost();
	  	}else {
	  		$search = array(
	  			'adv_search'=>'',
	  			'branch_id'=>'',
	  			'members'=>'',
	  			'product_id'=>-1,
	  			'currency_type'=>-1,
	  			'status_use'=>-1,
	  			'end_date'=>date('Y-m-d'));
	  	}
	  	$this->view->fordate = $search['end_date'];
	  	$this->view->outstandloan= $db->getAllOutstadingLoan($search);
	  	$frm = new Loan_Form_FrmSearchLoan();
	  	
	  	$key = new Application_Model_DbTable_DbKeycode();
	  	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
	  	
	  	$frm = new Pawnshop_Form_FrmPawnshop();
	  	$frm = $frm->FrmAddLoan();
	  	Application_Model_Decorator::removeAllDecorator($frm);
	  	$this->view->frm_search = $frm;
}
public function paymentscheduleListAction(){
	try{
		$db = new Report_Model_DbTable_DbRptPaymentSchedule();
		if($this->getRequest()->isPost()){
			$search = $this->getRequest()->getPost();
		}
		else{
			$search = array(
					'adv_search' => '',
					'status_search' => -1,
					'client_id' => -1,
					'status' => -1,
					'from_date' =>date('Y-m-d'),
					'to_date' => date('Y-m-d'),
			);
		}
		$rs_rows = $db->getAllClientPaymentListRpt($search);
		
		$glClass = new Application_Model_GlobalClass();
		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
		
		$collumns = array("BRANCH_NAME","LOAN_NO","CLIENT_NO","CUSTOMER_NAME","LOAN_AMOUNT","AMIN_FEE","INTEREST RATE","TERM_BORROW","METHOD","TIME_COLLECT","ZONE","CO_NAME",
				"STATUS");
		$link=array(
				'module'=>'report','controller'=>'loan','action'=>'rpt-paymentschedules',
		);
		$list = new Application_Form_Frmtable();
		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array(
				'total_capital'=>$link,'loan_number'=>$link,'client_number'=>$link));
				
		}catch (Exception $e){
		Application_Form_FrmMessage::message("Application Error");
		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
	}
	
	$frm = new Loan_Form_FrmSearchLoan();
	$frm = $frm->AdvanceSearch();
	Application_Model_Decorator::removeAllDecorator($frm);
	$this->view->frm_search = $frm;
}
public function exportFileToExcel($table,$data,$thead){
	$this->_helper->layout->disableLayout();
	$db = new Report_Model_DbTable_DbExportfile();
	$finalData = $db->getFileby($table,$data,$thead);
	$filename = APPLICATION_PATH . "/tmp/$table-" . date( "m-d-Y" ) . ".xlsx";
	$realPath = realpath( $filename );
	if ( false === $realPath ){
		touch( $filename );
		chmod( $filename, 0777 );
	}
	$filename = realpath( $filename );
	$handle = fopen( $filename, "w" );
	fputcsv( $handle, $thead, "\t" );
	$this->getResponse()->setRawHeader( "Content-Type: application/vnd.ms-excel; charset=utf-8" )
	->setRawHeader( "Content-Disposition: attachment; filename=excel.xls" )
	->setRawHeader( "Content-Transfer-Encoding: binary" )
	->setRawHeader( "Expires: 0" )
	->setRawHeader( "Cache-Control: must-revalidate, post-check=0, pre-check=0" )
	->setRawHeader( "Pragma: public" )
	->setRawHeader( "Content-Length: " . filesize( $filename ) )
	->sendResponse();
	foreach ( $finalData AS $finalRow )
	{
		fputcsv( $handle,$finalRow, "\t" );
	}
	fclose( $handle );
	$this->_helper->viewRenderer->setNoRender();
	readfile( $filename );//exit();
}
function rptPaymentschedulesAction(){
	$db = new Report_Model_DbTable_Dbpawn();
	$id =$this->getRequest()->getParam('id');
	$row = $db->getPaymentSchedule($id);
	$this->view->tran_schedule=$row;
	if(empty($row)){
		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/rpt-loan-disburse');
	}
	$db = new Application_Model_DbTable_DbGlobal();
	$rs = $db->getClientPawnshop($id);

	$this->view->client =$rs;
	
	$day_inkhmer = $db->getDayInkhmerBystr(null);
	$this->view->day_inkhmer = $day_inkhmer;
	
	$key = new Application_Model_DbTable_DbKeycode();
	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 }
 function rptUpdatepaymentAction(){
 	if($this->getRequest()->isPost()){
 		$_data = $this->getRequest()->getPost();
 		try {
 			$_dbmodel = new Loan_Model_DbTable_DbLoanIL();
 			$_dbmodel->updatePaymentStatus($_data);
 			Application_Form_FrmMessage::Sucessfull("UPDATE_SUCESS","/report/loan/rpt-loan-disburse");
 		}catch (Exception $e) {
 			Application_Form_FrmMessage::message("INSERT_FAIL");
 			Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
 		}
 	}
 	$db = new Report_Model_DbTable_DbRptPaymentSchedule();
 	$id =$this->getRequest()->getParam('id');
 	$row = $db->getPaymentSchedule($id);
 	$this->view->tran_schedule=$row;
 	if(empty($row)){
 		Application_Form_FrmMessage::Sucessfull("RECORD_NOT_EXIST",'/report/loan/paymentschedule-list');
 	}
 	$db = new Application_Model_DbTable_DbGlobal();
 	$rs = $db->getClientByMemberId($id);
 	
 	$this->view->client =$rs;
 	$frm = new Application_Form_FrmSearchGlobal();
 	$form = $frm->FrmSearchLoadSchedule();
 	Application_Model_Decorator::removeAllDecorator($form);
 	$this->view->form_filter = $form;
 	$day_inkhmer = $db->getDayInkhmerBystr(null);
 	$this->view->day_inkhmer = $day_inkhmer;
 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	$this->view->id = $id;
 
 }
 function rptLoanIncomeAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 	 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 	$search = array(
	 	'adv_search' => '',
	 	'client_name' => -1,
	 	'start_date'=> date('Y-m-d'),
	 	'end_date'=>date('Y-m-d'),
 		'branch_id'		=>	-1,
		'co_id'		=> -1,
		'paymnet_type'	=> -1,
 		'status'=>"",);
			
 	}
 	$this->view->LoanCollectionco_list =$db->getALLLoanIcome($search);
 	$this->view->LoanFee_list =$db->getALLLTotalFee($search);
 	
 	$this->view->list_end_date=$search;
 	$frm = new Loan_Form_FrmSearchGroupPayment();
 	$fm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($fm);
 	$this->view->frm_search = $fm;
 }
 function rptLoanPayoffAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 	 
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
	 	$search = array(
 			'adv_search' => "",
 			'branch_id'	  => -1,
 			'member'=>-1,
	 		'currency_type'=>-1,
		 	'start_date'  => date('Y-m-d'),
		 	'end_date'    => date('Y-m-d'),
			'paymnet_type'=> -1,);
 	}
 	$this->view->LoanCollectionco_list =$db->getALLLoanPayoff($search);
 	$this->view->list_end_date=$search;
 	
 	$frm = new Pawnshop_Form_FrmPawnshop();
 	$frm = $frm->FrmAddLoan();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 }
 function rptLoanExpectIncomeAction(){
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 		$search = array(
 				'adv_search'=>'',
  				'branch_id'=>'',
  				'members'=>'',
  				'product_id'=>-1,
  				'currency_type'=>-1,
  				'start_date'=> date('Y-m-d'),
  				'end_date'=>date('Y-m-d')
 				);
 	}
 	$this->view->list_end_date=$search;
 	$db  = new Report_Model_DbTable_Dbpawn();
 	$this->view->LoanCollectionco_list =$db->getALLLoanExpectIncome($search);
 	
 	$frm = new Pawnshop_Form_FrmPawnshop();
  	$frm = $frm->FrmAddLoan();
  	Application_Model_Decorator::removeAllDecorator($frm);
  	$this->view->frm_search = $frm;	
 }
 function rptWritoffAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 		
 	}else{
 			$search = array(
 				'adv_search'=>'',
 				'branch_id'=>'',
 				'members'=>'',
 				'product_id'=>-1,
 				'currency_type'=>'',
 				'start_date'=> date('Y-m-d'),
 				'end_date'=>date('Y-m-d'));
 	}
 	$this->view->LoanCollectionco_list =$db->getALLWritoff($search);
 	$this->view->list_end_date=$search;
 	
 	$frm = new Pawnshop_Form_FrmPawnshop();
 	$frm = $frm->FrmAddLoan();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_loan = $frm;
 }

function rptPaymentHistoryAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else {
 		$search = array(
 			'adv_search' => "",
 			'branch_id'	  => -1,
 			'members'=>-1,
	 		'currency_type'=>-1,
		 	'start_date'  => date('Y-m-d'),
		 	'end_date'    => date('Y-m-d'),
			'paymnet_type'=> -1,);
 	}
 	$this->view->loantotalcollect_list =$db->getAllPaymentHistory($search);
 	$this->view->list_end_date=$search;
 	
 	$frm = new Pawnshop_Form_FrmPawnshop();
 	$frm = $frm->FrmAddLoan();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 	
 }
 function rptIncomestatementAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 		
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 		$search['status']=-1;
 	}else{
 		$search = array(
 		 	
 		 	'start_date'=> date('Y-m-d'),
 		 	'end_date'=>date('Y-m-d'),
 			'branch_id'		=>	-1,
 			"currency_type"=>0,);
 	}
 	
 	$income = array(
 			'interest_paidr'=>0,
 			'penalize_paidr'=>0,
 			'service_paidr'=>0,
 			
 			'interest_paidd'=>0,
 			'penalize_paidd'=>0,
 			'service_paidd'=>0,
 			
 			'interest_paidb'=>0,
 			'penalize_paidb'=>0,
 			'service_paidb'=>0,
 			
 			'adminfee_d'=>0,
 			'other_feed'=>0,
 			
 			'adminfee_r'=>0,
 			'other_feer'=>0,
 			
 			'adminfee_b'=>0,
 			'other_feeb'=>0,
 			
 			'other_incomer'=>0,
 			'other_incomed'=>0,
 			'other_incomeb'=>0,
 			
 			'expense_d'=>0,
 			'expense_r'=>0,
 			'expense_b'=>0,
 			
 			'badloan_d'=>0,
 			'badloan_r'=>0,
 			'badloan_b'=>0,
 			
 		);
 	$incomecollect = $db->getLoanCollectIcome($search);
 	if(!empty($incomecollect)){
 		foreach($incomecollect as $row){
 			if($row['currency_type']==1){//riel
 				$income['interest_paidr']=$row['interest_paid'];
 				$income['penalize_paidr']=$row["penalize_paid"];
 				$income['service_paidr']=$row["service_paid"];
 			}elseif($row['currency_type']==2){//dollar
 				$income['interest_paidd']=$row['interest_paid'];
 				$income['penalize_paidd']=$row["penalize_paid"];
 				$income['service_paidd']=$row["service_paid"];
 			}else{//bath
 				$income['interest_paidb']=$row['interest_paid'];
 				$income['penalize_paidb']=$row["penalize_paid"];
 				$income['service_paidb']=$row["service_paid"];
 			}
 		}
 	}
 	
 	$rsotherfee = $db->getLoanadminFeeIcome($search);
 	if(!empty($rsotherfee)){
 		foreach($rsotherfee as $row){
 			if($row['curr_type']==1){//riel
 				$income['adminfee_r']=$row['admin_fee'];
 				$income['other_feer']=$row["other_fee"];
 			}elseif($row['curr_type']==2){//dollar
 				$income['adminfee_d']=$row['admin_fee'];
 				$income['other_feed']=$row["other_fee"];
 			}else{//bath
 				$income['adminfee_b']=$row['admin_fee'];
 				$income['other_feeb']=$row["other_fee"];
 			}
 		}
 	}
 	
 	$rsincome = $db->getAllOtherIncomeReport($search);
 	if(!empty($rsincome)){
 		foreach($rsincome as $row){
 			if($row['curr_type']==1){//riel
 				$income['other_incomer']=$row['total_amount'];
 			}elseif($row['curr_type']==2){//dollar
 				$income['other_incomed']=$row['total_amount'];
 			}else{//bath
 				$income['other_incomeb']=$row['total_amount'];
 			}
 		}
 	}
 	$rsexpense = $db->getExpenseincomereport($search);
 	if(!empty($rsexpense)){
 		foreach($rsexpense as $row){
 			if($row['curr_type']==1){//riel
 				$income['expense_r']=$row['total_amount'];
 			}elseif($row['curr_type']==2){//dollar
 				$income['expense_d']=$row['total_amount'];
 			}else{//bath
 				$income['expense_b']=$row['total_amount'];
 			}
 		}
 	}
 	
 	$rsbadloan = $db->getALLTotalWritoff($search);
 	if(!empty($rsbadloan)){
 		foreach($rsbadloan as $row){
 			if($row['curr_type']==1){//riel
 				$income['badloan_r']=$row['total_amount'];
 			}elseif($row['curr_type']==2){//dollar
 				$income['badloan_d']=$row['total_amount'];
 			}else{//bath
 				$income['badloan_b']=$row['total_amount'];
 			}
 		}
 	}
 	
	$this->view->rsincome=$income;
 	$this->view->list_end_date=$search;
 	
 	$frm = new Loan_Form_FrmSearchGroupPayment();
 	$fm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($fm);
 	$this->view->frm_search = $fm;
 }
 function rptDailypaymentAction(){
 	$db  = new Report_Model_DbTable_Dbpawn();
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else {
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
 	}
 	$this->view->loantotalcollect_list =$db->getCollectDailyPayment($search);
 	$this->view->rsincome= $db->getTotalOtherIncomeReport($search);//call frome model
 	$this->view->rsexpense= $db->getAllExpenseReport($search);//call frome model
 	$this->view->LoanFee_list =$db->getALLLFee($search);
 	
 	$this->view->list_end_date = $search;
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frm = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_search = $frm;
 }
 function rptLoanareaAction(){
 	$db  = new Report_Model_DbTable_DbpawnCollect();
 	$key = new Application_Model_DbTable_DbKeycode();
 	$this->view->data=$key->getKeyCodeMiniInv(TRUE);
 	if($this->getRequest()->isPost()){
 		$search = $this->getRequest()->getPost();
 	}else{
 		$search = array(
 				'adv_search'=>'',
 				'branch_id' => '',
 				'client_name' =>'',
 				'client_code'=>'',
 				'status' =>'',
 				'currency_type'=>'',
 				'start_date'=>date('Y-m-d'),
 				'end_date'=>date('Y-m-d'));
 	}
 	$this->view->LoanCollectionco_list = $db->getALLParBYCO($search);
 	$this->view->search=$search;
 	$frm = new Loan_Form_FrmSearchLoan();
 	$frms = $frm->AdvanceSearch();
 	Application_Model_Decorator::removeAllDecorator($frm);
 	$this->view->frm_loan = $frm;
 }
 
}

