<?php
class Installment_BalancestockController extends Zend_Controller_Action
{
public function init()
    {
        /* Initialize action controller here */
    	defined('BASE_URL')	|| define('BASE_URL', Zend_Controller_Front::getInstance()->getBaseUrl());
    }
    protected function GetuserInfoAction(){
    	$user_info = new Application_Model_DbTable_DbGetUserInfo();
    	$result = $user_info->getUserInfo();
    	return $result;
    }
	function updatecodeAction(){
		$db = new Installment_Model_DbTable_DbProduct();
		$db->getProductCoded();
	}
    public function indexAction()
    {
    	$db = new Installment_Model_DbTable_DbBalanceStock();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$check = $db->checkBalancStockCurrentDate($post['closingDate']);
					if (!empty($check)){
						Application_Form_FrmMessage::Sucessfull("Already Closing Stock This Month", '/installment/balancestock');
					}else{
						$db->addBalanceStock($post);
					}
					Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/installment/balancestock');
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
		$this->view->currentstock = $db->getSumaryStock();	
		$formCat = new Installment_Form_FrmCategory();
		$frmCat = $formCat->cat();
		$this->view->frmCat = $frmCat;
		Application_Model_Decorator::removeAllDecorator($frmCat);
			
			
	}
	
}

