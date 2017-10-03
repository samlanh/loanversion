<?php

class Tellerandexchange_SalecardController extends Zend_Controller_Action
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
		header('content-type: text/html; charset=utf8');
		defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
	}

    public function indexAction()
    {
	      $db_tran=new Tellerandexchange_Model_DbTable_Dbsalescard();
	      if($this->getRequest()->isPost()){        	
	        $formdata=$this->getRequest()->getPost();
	      }
	      else{
	        	$formdata = array(
	        			"adv_search"=>'',
	        			'start_date'=> date('Y-m-d'),
						'end_date'=>date('Y-m-d'),
	             );
	        }
	       $rs =  $db_tran->getAllSaleTransaction($formdata);
	       $list = new Application_Form_Frmtable();
	       $collumns = array("ចំណង់ជើង","កាល​បរិច្ឆេទលក់","ប្រភេទប្រាក់","ចំនួន​ទឹក​ប្រាក់លក់បាន","កម្រៃសេវាជាទទួលបាន","សម្គាល់");
	       $link=array(
	       		'module'=>'tellerandexchange','controller'=>'salecard','action'=>'edit',
	       );
	       $this->view->list=$list->getCheckList(0, $collumns, $rs,array('title'=>$link,'sale_date'=>$link,'commission'=>$link,'amount_sale'=>$link));
	       
	       $frm = new Loan_Form_FrmSearchLoan();
	       $frm = $frm->AdvanceSearch();
	       Application_Model_Decorator::removeAllDecorator($frm);
	       $this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	$cur = new Application_Model_DbTable_DbCurrencies();
    	$this->view->currency = $cur->getCurrencyList();
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		try {
    			$db_mtran=new Tellerandexchange_Model_DbTable_Dbsalescard();
    			$db = $db_mtran->addsaleCard($data);
    			if(!empty($data['savenew'])){
    				Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			}else{
    				Application_Form_FrmMessage::Sucessfull('ការ​បញ្ចូល​​ជោគ​ជ័យ', self::REDIRECT_URL . '/salecard');
    			}
    		} catch (Exception $e) {
    			$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
    		}
    	}
    }
    public function editAction()
    {
       
		$cur = new Application_Model_DbTable_DbCurrencies();
		$this->view->currency = $cur->getCurrencyList();
		$db_mtran=new Tellerandexchange_Model_DbTable_Dbsalescard();
		if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();	
			try {
				$db = $db_mtran->addsaleCard($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS","/tellerandexchange/transfer");
						
			} catch (Exception $e) {
				$this->view->msg = 'ការ​បញ្ចូល​មិន​ជោគ​ជ័យ';
			}
		}     
		$mt_id = $this->getRequest()->getParam('id');
		$mt_id = (empty($mt_id))? 0 : $mt_id;
		
		$this->view->rs = $db_mtran->getTransactionByID($mt_id);
    }
}











