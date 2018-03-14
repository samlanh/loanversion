<?php
class Installment_ProductController extends Zend_Controller_Action
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
    	$db = new Installment_Model_DbTable_DbProduct();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
    	if($this->getRequest()->isPost()){
    		$data = $this->getRequest()->getPost();
    	}else{
    		$data = array(
    			'adv_search'=>	'',
    			'branch_id'	=>	-1,
    			'category'	=>	-1,
    			'start_date'=>	date("Y-m-d"),
    			'end_date'	=>	date("Y-m-d"),
    			'status'	=>  -1
    		);
    	}
    	$columns=array("BRANCH_NAME","ITEM_CODE","ITEM_NAME",
    			"PRODUCT_CATEGORY","CURRENT_QTY","COST_PRICE","SOLD_PRICE","USER","STATUS");
    	$rows = $db->getAllProduct($data);
		$link=array(
				'module'=>'installment','controller'=>'product','action'=>'edit',);
	
		$list = new Application_Form_Frmlist();
		$this->view->list=$list->getCheckList(0, $columns, $rows,array('item_name'=>$link,'item_code'=>$link,'barcode'=>$link,'branch'=>$link));
		
    	$formFilter = new Installment_Form_FrmProduct();
    	$this->view->formFilter = $formFilter->add();
    	Application_Model_Decorator::removeAllDecorator($formFilter);
	}
	public function addAction()
	{
		$db = new Installment_Model_DbTable_DbProduct();
			if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$db->add($post);
					if(isset($post["save_close"]))
					{
						Application_Form_FrmMessage::Sucessfull("INSERT_SUCCESS", '/installment/product');
					}else{
						Application_Form_FrmMessage::message("INSERT_SUCCESS");
					}
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
			}
			$rs_branch = $db->getBranch();
			$dbc = new Application_Model_GlobalClass();
			$this->view->branch = $dbc->getAllBranchOption();
			
			$formCat = new Installment_Form_FrmCategory();
			$frmCat = $formCat->cat();
			$this->view->frmCat = $frmCat;
			Application_Model_Decorator::removeAllDecorator($frmCat);
			
			$db = new Installment_Model_DbTable_DbProduct();
			$row_cat = $db->getCategory();
			
	        array_unshift($row_cat,array(
	        'id' => -1,
	        'name' => 'Add New',
	        ) );
	        $this->view->rs_cate=$row_cat;
	        $db = new Application_Model_GlobalClass();
	        $this->view->rsbranch = $db->getAllBranchOption();
	}
	public function editAction()
	{
		$id = $this->getRequest()->getParam("id"); 
		$db = new Installment_Model_DbTable_DbProduct();
		if($this->getRequest()->isPost()){ 
				try{
					$post = $this->getRequest()->getPost();
					$post["id"] = $id;
					$db->edit($post);
						Application_Form_FrmMessage::Sucessfull("EDIT_SUCCESS", '/installment/product');
				  }catch (Exception $e){
				  	Application_Form_FrmMessage::messageError("INSERT_ERROR",$err = $e->getMessage());
				  }
		}
		$this->view->rs_location = $db->getProductLocation($id);
// 		$this->view->rs_price = $db->getProductPrcie($id);
		$this->view->rspro =  $db->getProductById($id);
		
		
// 		$rs_branch = $db->getBranch();
// 		$this->view->branch = $rs_branch;
		$dbc = new Application_Model_GlobalClass();
		$this->view->branch = $dbc->getAllBranchOption();
			
		$formCat = new Installment_Form_FrmCategory();
		$frmCat = $formCat->cat();
		$this->view->frmCat = $frmCat;
		Application_Model_Decorator::removeAllDecorator($frmCat);
			
		$db = new Installment_Model_DbTable_DbProduct();
		$row_cat = $db->getCategory();
			
		array_unshift($row_cat,array(
				'id' => -1,
				'name' => 'Add New',
		) );
		$this->view->rs_cate=$row_cat;
		$db = new Application_Model_GlobalClass();
		$this->view->rsbranch = $db->getAllBranchOption();
	}
	
	function getproductbycateAction(){
		if($this->getRequest()->isPost()){
			$post=$this->getRequest()->getPost();
			$db = new Installment_Model_DbTable_DbProduct();
			$result =$db->getallProductbycate($post['category_id']);
			print_r(Zend_Json::encode($result));
			exit();
		}
	}
// 	public function addCategoryAction(){
// 		if($this->getRequest()->isPost()){
// 			try {
// 				$post=$this->getRequest()->getPost();
// 				$db = new Product_Model_DbTable_DbCategory();
// 				$cat_id =$db->addNew($post);
// 				$result = array('cat_id'=>$cat_id);
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}catch (Exception $e){
// 				$result = array('err'=>$e->getMessage());
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}
// 		}
// 	}

// 	public function addMeasureAction(){
// 		if($this->getRequest()->isPost()){
// 			try {
// 				$post=$this->getRequest()->getPost();
// 				$db = new Product_Model_DbTable_DbMeasure();
// 				if(empty($post['measure_name'])){
// 					$post['measure_name']=$post['name'];
// 				}
// 				$measure_id =$db->addNew($post);
// 				$result = array('measure_id'=>$measure_id);
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}catch (Exception $e){
// 				$result = array('err'=>$e->getMessage());
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}
// 		}
// 	}
	
// 	public function addOtherAction(){
// 		if($this->getRequest()->isPost()){
// 			try {
// 				$post=$this->getRequest()->getPost();
// 				$db = new Product_Model_DbTable_DbOther();
// 				$other_id =$db->addNew($post);
// 				$result = array('other_id'=>$other_id);
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}catch (Exception $e){
// 				$result = array('err'=>$e->getMessage());
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}
// 		}
// 	}
// 	public function addNewproudctAction(){
// 		if($this->getRequest()->isPost()){
// 			try {
// 				$post=$this->getRequest()->getPost();
// 				$db = new Installment_Model_DbTable_DbProduct();
// 				$pro_id =$db->addAjaxProduct($post);
// 				$result = array('pro_id'=>$pro_id);
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}catch (Exception $e){
// 				$result = array('err'=>$e->getMessage());
// 				echo Zend_Json::encode($result);
// 				exit();
// 			}
// 		}
// 	}
	
// 	function outstockAction(){
// 		$db = new Installment_Model_DbTable_DbProduct();
//     	if($this->getRequest()->isPost()){
//     		$data = $this->getRequest()->getPost();
//     	}else{
//     		$data = array(
//     			'ad_search'	=>	'',
//     			'branch'	=>	'',
//     			'brand'		=>	'',
//     			'category'	=>	'',
//     			'model'		=>	'',
//     			'color'		=>	'',
//     			'size'		=>	'',
//     			'status'	=>	1
//     		);
//     	}
//     	$this->view->product = $db->getAllProductOutStock($data);
//     	$formFilter = new Installment_Form_FrmProduct();
//     	$this->view->formFilter = $formFilter->productFilter();
//     	Application_Model_Decorator::removeAllDecorator($formFilter);
// 	}
	
// 	function lowstockAction(){
// 		$db = new Installment_Model_DbTable_DbProduct();
//     	if($this->getRequest()->isPost()){
//     		$data = $this->getRequest()->getPost();
//     	}else{
//     		$data = array(
//     			'ad_search'	=>	'',
//     			'branch'	=>	'',
//     			'brand'		=>	'',
//     			'category'	=>	'',
//     			'model'		=>	'',
//     			'color'		=>	'',
//     			'size'		=>	'',
//     			'status'	=>	1
//     		);
//     	}
//     	$this->view->product = $db->getAllProductLowStock($data);
//     	$formFilter = new Installment_Form_FrmProduct();
//     	$this->view->formFilter = $formFilter->productFilter();
//     	Application_Model_Decorator::removeAllDecorator($formFilter);
// 	}
	
}

