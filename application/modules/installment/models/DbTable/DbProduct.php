<?php

class Installment_Model_DbTable_DbProduct extends Zend_Db_Table_Abstract
{
    protected $_name = 'ln_ins_product';
    public function setName($name)
    {
    	$this->_name=$name;
    }
	public function getUserId(){
		return Application_Model_DbTable_DbGlobal::GlobalgetUserId();
	}
  public function getCategory(){
  	$db = $this->getAdapter();
  	$sql = "SELECT b.`id`,b.`name` FROM `ln_ins_category` AS b WHERE b.`status`=1 AND b.`name`!='' ORDER BY b.`name` ASC ";
  	return $db->fetchAll($sql);
  }
  public function getProducttype(){
  	$db = $this->getAdapter();
  	$sql = "SELECT b.`id`,b.`name` FROM `ln_ins_producttype` AS b WHERE b.`status`=1 AND b.`name`!='' ";
  	return $db->fetchAll($sql);
  }
  public function getProductCode(){
  	$db =$this->getAdapter();
  	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre = "PID";
  	for($i = $acc_no;$i<5;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
  }
  public function getProductbarcode(){
  	$db =$this->getAdapter();
  	$sql=" SELECT id FROM $this->_name ORDER BY id DESC LIMIT 1 ";
  	$acc_no = $db->fetchOne($sql);
  	$new_acc_no= (int)$acc_no+1;
  	$acc_no= strlen((int)$acc_no+1);
  	$pre = "884";
  	for($i = $acc_no;$i<6;$i++){
  		$pre.='0';
  	}
  	return $pre.$new_acc_no;
 }
 function getAllProduct($data){
  	$db = $this->getAdapter();
  	$db_globle = new Application_Model_DbTable_DbGlobal();
	$user_id = $this->getUserId();
  	$sql ="SELECT 
			  p.`id`,
			  (SELECT branch_namekh FROM `ln_branch` AS b WHERE b.br_id=pl.`location_id` LIMIT 1) AS branch,
			  p.`item_code`,
			  p.`item_name` ,
			  (SELECT c.name FROM `ln_ins_producttype` AS  c WHERE c.id=p.`product_type` LIMIT 1) AS product_type,
			  (SELECT c.name FROM `ln_ins_category` AS  c WHERE c.id=p.`cate_id` LIMIT 1) AS cat,
			  SUM(pl.`qty`) AS qty,cost_price,selling_price,
			  (SELECT `first_name` FROM `rms_users` WHERE rms_users.`id`=p.`user_id` LIMIT 1) AS user_name,
  			  (SELECT v.`name_en` FROM ln_view AS v WHERE v.`type`=3  AND p.`status`=v.`key_code` LIMIT 1) AS status
			FROM
			  `ln_ins_product` AS p ,
			  `ln_ins_prolocation` AS pl
			WHERE p.`id`=pl.`pro_id` ";
  	$from_date =(empty($data['start_date']))? '1': " p.create_date >= '".$data['start_date']." 00:00:00'";
  	$to_date = (empty($data['end_date']))? '1': " p.create_date <= '".$data['end_date']." 23:59:59'";
  	$where = " AND ".$from_date." AND ".$to_date;
  	if($data["adv_search"]!=""){
		$string = str_replace(' ','',$data['adv_search']);
  		$s_where=array();
  		$s_search = addslashes(trim($string));
  		$s_where[]= " REPLACE(p.item_name,' ','') LIKE '%{$s_search}%'";
  		$s_where[]=" REPLACE(p.item_code,' ','') LIKE '%{$s_search}%'";
  		$where.=' AND ('.implode(' OR ', $s_where).')';
  	}
  	if($data["branch_id"]>0){
  		$where.=' AND pl.`location_id`='.$data["branch_id"];
  	}
  	if($data["category"]>0){
  		$where.=' AND p.cate_id='.$data["category"];
  	}
  	if($data["product_type"]>0){
  		$where.=' AND p.product_type='.$data["product_type"];
  	}
  	if($data["status"]!=-1){
  		$where.=' AND p.status='.$data["status"];
  	}
  	$location = $db_globle->getAccessPermission('pl.`location_id`');
  	$group_by = " GROUP BY p.id,pl.`location_id` DESC ";
  	return $db->fetchAll($sql.$where.$location.$group_by);
  }  
  function getProductById($id){
  	$db = $this->getAdapter();
  	$sql ="SELECT 
			  *
			FROM
			  `ln_ins_product` AS p 
			WHERE p.id = $id ";
  	return $db->fetchRow($sql);
  }
  function getProductLocation($pro_id){
  	$db = $this->getAdapter();
  	$sql = "SELECT 
			  pl.`id`,
			  pl.`pro_id`,
			  pl.`qty`,
			  pl.`qty_warning`,
			  pl.`location_id`,
			  pl.note,
			  b. branch_namekh AS `name` 
			FROM
			  `ln_ins_prolocation` AS pl,
			  `ln_branch` AS b
			WHERE pl.`pro_id` = $pro_id
			  AND pl.`location_id` = b.`br_id` ";
  	$db_globle = new Application_Model_DbTable_DbGlobal();
  	$sql.= $db_globle->getAccessPermission('pl.`location_id`');
  	
  	return $db->fetchAll($sql);
  }
  // Insert and  Update section
    public function add($data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
		$user_info = new Application_Model_DbTable_DbGetUserInfo();
		$result = $user_info->getUserInfo();
		$session_user=new Zend_Session_Namespace('authloan');
		$request=Zend_Controller_Front::getInstance()->getRequest();
		 $level = $result["level"];
    	try {
    		$arr = array(
    			'item_name'		=>	$data["name"],
    			'item_code'		=>	$data["pro_code"],
//     			'barcode'		=>	$data["barcode"],
    			'cate_id'		=>	$data["category_id"],
    			'product_type'	=>	$data["product_type"],
    			'cost_price'=>$data["cost_price"],
    			'selling_price'=>$data["selling_price"],
    			'user_id'		=>	$this->getUserId(),
    			'note'			=>	$data["description"],
    			'create_date'		=>	date("Y-m-d"),
    				
    		);
    		$this->_name="ln_ins_product";
    		$id = $this->insert($arr);
			
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				$arr1 = array(
    					'pro_id'			=>	$id,
    					'location_id'		=>	$data["branch_id".$i],
    					'qty'				=>	$data["total_qty_".$i],
    					'qty_warning'		=>	$data["alert_qty".$i],
    					'last_mod_userid'	=>	$this->getUserId(),
    					'last_mod_date'		=>	new Zend_Date(),
    					'last_mod_userid'   =>  $this->getUserId(),
    				);
    				$this->_name = "ln_ins_prolocation";
    				$this->insert($arr1);
    			 }
    		  }
    		$db->commit();
    	}catch (Exception $e){
    		$db->rollBack();
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    	}
    }
    public function edit($data){
	    	$db = $this->getAdapter();
	    	$db->beginTransaction();
    	      try {
    		     $arr = array(
    				'item_name'		=>	$data["name"],
	    			'item_code'		=>	$data["pro_code"],
	    			'cate_id'		=>	$data["category_id"],
	    			'product_type'	=>	$data["product_type"],
	    			'cost_price'	=>  $data["cost_price"],
	    			'selling_price'	=>  $data["selling_price"],
	    			'user_id'		=>	$this->getUserId(),
	    			'note'			=>	$data["description"],
	//     			'status'		=>	$data["status"],
    		     );
    		
    		$where = $db->quoteInto("id=?", $data["id"]);
    		$this->update($arr, $where);
    
    		$identitys = explode(',',$data['identity']);
    		$detailId="";
    		if (!empty($identitys)){
	    		foreach ($identitys as $i){
	    			if (empty($detailId)){
	    				if (!empty($data['detailid'.$i])){
	    					$detailId = $data['detailid'.$i];
	    				}
	    			}else{
	    				if (!empty($data['detailid'.$i])){
	    					$detailId= $detailId.",".$data['detailid'.$i];
	    				}
	    			}
	    		}
    		}
    		$this->_name="ln_ins_prolocation";
    		$where="pro_id = ".$data["id"];
    		if (!empty($detailId)){
    			$where.=" AND id NOT IN ($detailId) ";
    		}
    		$this->delete($where);
    		
    		if(!empty($data['identity'])){
    			$identitys = explode(',',$data['identity']);
    			foreach($identitys as $i)
    			{
    				if (!empty($data['detailid'.$i])){
    					$arr1 = array(
    							'pro_id'			=>	$data["id"],
    							'location_id'		=>	$data["branch_id".$i],
    							'qty'				=>	$data["total_qty_".$i],
    							'qty_warning'		=>	$data["alert_qty".$i],
    							'last_mod_userid'	=>	$this->getUserId(),
    							'last_mod_date'		=>	new Zend_Date(),
    							'last_mod_userid'   =>  $this->getUserId(),
    					);
    					$this->_name = "ln_ins_prolocation";
    					$where =" id =".$data['detailid'.$i];
    					$this->update($arr1, $where);
    				}else{
	    				$arr1 = array(
	    					'pro_id'			=>	$data["id"],
	    					'location_id'		=>	$data["branch_id".$i],
	    					'qty'				=>	$data["total_qty_".$i],
	    					'qty_warning'		=>	$data["alert_qty".$i],
	    					'last_mod_userid'	=>	$this->getUserId(),
	    					'last_mod_date'		=>	new Zend_Date(),
	    					'last_mod_userid'   =>  $this->getUserId(),
	    				);
	    				$this->_name = "ln_ins_prolocation";
	    				$this->insert($arr1);
    				}
    			}
    		}
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e);
    		$db->rollBack();
    	}
    }
    public function getProductInfoDetail($id){//for view item detail
    	$db=$this->getAdapter();
    	$sql = "SELECT p.pro_id,p.cate_id,p.stock_type,p.item_name,p.item_code,p.price_per_qty,p.brand_id,
    	p.photo,p.is_avaliable,p.remark,c.Name,branch_namekh As branch_name
    	FROM ln_ins_product AS p
    	INNER JOIN ln_ins_category AS c ON c.CategoryID=p.cate_id
    	INNER JOIN tb_branch AS b ON b.branch_id=p.brand_id
    	WHERE p.pro_id=".$id." LIMIT 1";
    	$rows = $db->fetchRow($sql);
    	return ($rows);
    }
	public function getallProductbycate($data){
		$cate_id = $data['category_id'];
		$branch_id = $data['branch_id'];
		$db=$this->getAdapter();
// 		$sql = "SELECT id ,item_name AS name FROM `ln_ins_product` WHERE cate_id=".$cate_id;
		$sql="SELECT p.id ,p.item_name AS name
			FROM `ln_ins_prolocation` AS ip,
			`ln_ins_product` AS p
			WHERE 
			p.`id` = ip.`pro_id` AND
			ip.`location_id` =$branch_id AND p.`cate_id`=$cate_id";
		return $db->fetchAll($sql);
	}
}