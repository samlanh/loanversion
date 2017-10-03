<?php

class Tellerandexchange_TransferController extends Zend_Controller_Action
{

	const REDIRECT_URL = '/tellerandexchange';
	
    private $statuslist = array(
    	'ផ្ញើរ',
    	'ផ្ញើររួច',
        'ទទួល',
        'បើក​រួច',
        'មោឃៈ',
        'ផុត​កំណត់'
        );

    private $loanlist = array(
        'មិនជំពាក់',
        'ជំពាក់'
        );
        
    private $tran_typelist = array(
        'ផ្ញើរប្រាក់',
        'ដកប្រាក់'
        );

    public function init()
    {
        /* Initialize action controller here */
    	header('content-type: text/html; charset=utf8');
    	
    	
    }

    public function indexAction()
    {
	      $db_tran=new Application_Model_DbTable_DbMoneyTransactions();
	      if($this->getRequest()->isPost()){        	
	        $formdata=$this->getRequest()->getPost();
	      }
	      else{
	        	$formdata = array(
	        			"adv_search"=>'',
	        			"currency_type"=>-1,
	        			"status"=>-1,
	        			'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
	             );
	        }
	       $rs =  $db_tran->getTransactionLis($formdata);
	       $glClass = new Application_Model_GlobalClass();
	       $rs_rows = $glClass->getImgActive($rs, BASE_URL, true);
	       $list = new Application_Form_Frmtable();
	       $collumns = array("ឈ្មោះ​អ្នក​ផ្ញើរ","ឈ្មោះ​អ្នក​ទទួល","លេខ​​​អ្នក​ទទួល","ប្រភេទ​ប្រតិបត្តិការណ៍","ប្រភេទ​ប្រាក់","ចំនួនទឺកប្រាក់","កម្រៃជើងសារ","ថ្ងៃ​ប្រតិបត្តិ","ស្ថានការណ៍");
	       $link=array(
	       		'module'=>'tellerandexchange','controller'=>'transfer','action'=>'edit',
	       );
	       $this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('sender_name'=>$link,'reciever_tel'=>$link));
	       
	       $frm = new Loan_Form_FrmSearchLoan();
	       $frm = $frm->AdvanceSearch();
	       Application_Model_Decorator::removeAllDecorator($frm);
	       $this->view->frm_search = $frm;
    }
    public function editAction()
    {
        $mt_id = $this->getRequest()->getParam('id');
        $mt_id = (empty($mt_id))? 0 : $mt_id; 
        
        $session_user=new Zend_Session_Namespace('auth');
        $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
        
        $this->view->tran_typelist = $this->tran_typelist;
		$cur = new Application_Model_DbTable_DbCurrencies();
		$this->view->currency = $cur->getCurrencyList();
		
		
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db_mtran=new Application_Model_DbTable_DbMoneyTransactions();
				$db = $db_mtran->insertTransfer($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/transfer");
						
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}     
		$db_mtran=new Application_Model_DbTable_DbMoneyTransactions();
		$this->view->rs = $db_mtran->getTransactionDetailByID($mt_id);
    }

    public function addAction()
    {
        $session_user=new Zend_Session_Namespace('auth');
        $this->view->user_name = $session_user->last_name .' '. $session_user->first_name;
        
        $db_keycode = new Application_Model_DbTable_DbKeycode();
		$this->view->keycode = $db_keycode->getKeyCodeMiniInv();
		
		$cur = new Application_Model_DbTable_DbCurrencies();
		$this->view->currency = $cur->getCurrencyList();
		$this->view->invoice_no = Application_Model_GlobalClass::getInvoiceNo();
		$this->view->tran_typelist = $this->tran_typelist;
		
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db_mtran=new Application_Model_DbTable_DbMoneyTransactions();
				$db = $db_mtran->insertTransfer($data);	
				if(!empty($data['savenew'])){
					Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
				}else{
					Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/transfer/index');
				}			
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}
        
    }

    public function viewAction()
    {
        // action body
        //Get value from url
        $mt_id = $this->getRequest()->getParam('mt_id');
        
        //Get Data form database
        $db_tran=new Application_Model_DbTable_DbMoneyTransactions();
		
        $this->view->traninfo = $db_tran->getTransactionDetailByIDView($mt_id);        
    }

    

}











