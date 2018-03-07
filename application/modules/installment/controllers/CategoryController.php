<?php
class Installment_categoryController extends Zend_Controller_Action
{
public function init()
    {
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
    public function indexAction()
    {
    	try{
    		if($this->getRequest()->isPost()){
    			$search = $this->getRequest()->getPost();
    		}
    		else{
    			$search = array(
    					'txt_search'=>'',
    					'repayment_method' => -1,
    					'branch_id' => -1,
    					'status' => -1,
    					'currency_type'=>-1,
    					'pay_every'=>-1,
    					'start_date'=> date('Y-m-d'),
    					'end_date'=>date('Y-m-d'),
    			);
    		}
    		$db = new Installment_Model_DbTable_DbCategory();
    		$rs_rows= $db->getAllCategory($search);
    		$glClass = new Application_Model_GlobalClass();
    		$rs_rows = $glClass->getImgActive($rs_rows, BASE_URL, true);
    		$list = new Application_Form_Frmtable();
    		$collumns = array("PRODUCT_CATEGORY","STATUS","BY_USER");
    		$link_info=array('module'=>'installment','controller'=>'category','action'=>'edit');
    		$this->view->list=$list->getCheckList(0, $collumns, $rs_rows,array('name'=>$link_info),0);
    	}catch (Exception $e){
    		Application_Form_FrmMessage::message("Application Error");
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
	}
	public function addAction()
	{
		if($this->getRequest()->isPost()) {
				$data = $this->getRequest()->getPost();
				$db = new Installment_Model_DbTable_DbCategory();
				$db->add($data);
				Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/installment/category');
			}
	}
public function editAction()
	{
		$id = ($this->getRequest()->getParam('id'))? $this->getRequest()->getParam('id'): '0';
		$db = new Installment_Model_DbTable_DbCategory();
		if($id==0){
			$this->_redirect('/installment/category');
		}
		if($this->getRequest()->isPost()) {
			$data = $this->getRequest()->getPost();
			$data["id"] = $id;
			$db->edit($data);
				Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/installment/category');
		}
		$this->view->rscate = $db->getCategory($id);
}
	public function addNewLocationAction(){
		$post=$this->getRequest()->getPost();
		$add_new_location = new Installment_Model_DbTable_DbCategory();
		$location_id = $add_new_location->addStockLocation($post);
		$result = array("LocationId"=>$location_id);
		if(!$result){
			$result = array('LocationId'=>1);
		}
		echo Zend_Json::encode($result);
		exit();
	}
	
}

