<?php

class Pawnshop_PawnproductController extends Zend_Controller_Action
{
	protected $tr;
	public function init()
    {
    	$this->tr=Application_Form_FrmLanguages::getCurrentlanguage();
    	header('content-type: text/html; charset=utf8');
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    public function indexAction()
    {
    	try{
    		$db = new Pawnshop_Model_DbTable_DbPawnproduct();
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'adv_search' => '',
    					'status_search' => -1);
    		}
    		$rs_rows= $db->getAllviewBYType($search);//call frome model
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("NAME_EN","NAME_KH","ពត៍មានលម្អិត","STATUS");
    		$link=array(
    				'module'=>'pawnshop','controller'=>'pawnproduct','action'=>'edit',
    		);
    		$this->view->list=$list->getCheckList(0, $collumns,$rs_rows,array('product_en'=>$link,'product_kh'=>$link));
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    	$frm = new Callecterall_Form_Frmcallecterall();
    	$frm = $frm->Frmcallecterall();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->frm_search = $frm;
    }
    public function addAction()
    {
    	if($this->getRequest()->isPost()){
    		$data=$this->getRequest()->getPost();
    		$db = new Pawnshop_Model_DbTable_DbPawnproduct();
    		try {
    			$db->addProduct($data);
    			Application_Form_FrmMessage::message('ការ​បញ្ចូល​​ជោគ​ជ័យ');
    			if(isset($data['save_close'])){
    				Application_Form_FrmMessage::redirectUrl('/pawnshop/pawnproduct');
    			}
    		} catch (Exception $e) {
    			Application_Form_FrmMessage::message("INSERT_FAIL");
    			$err = $e->getMessage();
    			Application_Model_DbTable_DbUserLog::writeMessageError($err);
    		}
    	}
    	$fm = new Pawnshop_Form_Frmpawnproduct();
    	$frm = $fm->FrmViewType();
    	Application_Model_Decorator::removeAllDecorator($frm);
    	$this->view->Form_Frmcallecterall = $frm;
    }
    public function editAction()
    {
    	$db = new Pawnshop_Model_DbTable_DbPawnproduct();
    	if($this->getRequest()->isPost()){
			$data=$this->getRequest()->getPost();
			try{
				$db->updatProduct($data);
				Application_Form_FrmMessage::message($this->tr->translate('EDIT_SUCCESS'));
				Application_Form_FrmMessage::redirectUrl('/pawnshop/pawnproduct');
			} catch (Exception $e) {
				Application_Form_FrmMessage::message("EDIT_FAIL");
				$err = $e->getMessage();
				Application_Model_DbTable_DbUserLog::writeMessageError($err);
			}
    	}
    	$id = $this->getRequest()->getParam('id');
    	$row  = $db->getListViewById($id);
    	$fm = new Pawnshop_Form_Frmpawnproduct();
    	$frm = $fm->FrmViewType($row);
	    Application_Model_Decorator::removeAllDecorator($frm);
	    $this->view->Form_Frmcallecterall = $frm;
	    $this->view->rs = $row;
    }
    function addprotypeAction(){
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    		$db = new Pawnshop_Model_DbTable_DbPawnproduct();
    		$row = $db->addProduct($data);
    		print_r(Zend_Json::encode($row));
    		exit();
    	}
    
    }
}
?>
