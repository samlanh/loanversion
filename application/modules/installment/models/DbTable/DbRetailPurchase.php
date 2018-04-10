<?php

class Installment_Model_DbTable_DbRetailPurchase extends Zend_Db_Table_Abstract
{    
	protected $_name = 'ln_ins_supplier';
    public function getUserId(){
    	$session_user=new Zend_Session_Namespace('authstu');
    	return $session_user->user_id;    	 
    }
    function getAllSupPurchase($search=null){
    	$db = $this->getAdapter();
    	$tr = Application_Form_FrmLanguages::getCurrentlanguage();
    	$RECEIPT = $tr->translate("RECEIPT");
    	$sql=" SELECT sp.id,
    	(SELECT b.branch_namekh FROM `ln_branch` AS b WHERE b.br_id = sp.`branch_id` LIMIT 1) AS branch_namekh,
    	sp.invoice_no, s.`supplier_no`,s.sup_name,
    	 s.tel,s.`email`,sp.total_amount,sp.date,
    	 sp.status,'$RECEIPT'
	    FROM 
		ln_ins_supplier AS s,
		ln_ins_purchase AS sp
	     WHERE s.id=sp.supplier_id AND sp.type=2 ";
    	$from_date =(empty($search['start_date']))? '1': " sp.date >= '".$search['start_date']." 00:00:00'";
    	$to_date = (empty($search['end_date']))? '1': " sp.date <= '".$search['end_date']." 23:59:59'";
    	$sql.= " AND ".$from_date." AND ".$to_date;
    	if(!empty($search['title'])){
    		$s_where=array();
    		$s_search=addslashes(trim($search['title']));
    		$s_where[]= " s.invoice_no LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]="  s.sup_name LIKE '%{$s_search}%'";
    		$s_where[]= " s.tel LIKE '%{$s_search}%'";
    		$s_where[]= " s.email LIKE '%{$s_search}%'";
    		$s_where[]= " sp.total_amount LIKE '%{$s_search}%'";
    		$sql.=' AND ('.implode(' OR ', $s_where).')';
    	}
    	if(!empty($search['branch_id'])){
    		$sql.=" AND sp.branch_id=".$search['branch_id'];
    	}
    	if(!empty($search['supllier'])){
    		$sql.=" AND sp.supplier_id=".$search['supllier'];
    	}
    	if($search['status']>-1){
    		$sql.=" AND sp.status=".$search['status'];
    	}
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('branch_id');
    	$order=" ORDER BY id DESC";
    	return $db->fetchAll($sql.$order);
    }
    
    function updateStock($pro_id,$location_id,$qty_order){
    	$_db = new Application_Model_DbTable_DbGlobal();
    	$branch_id = $_db->getAccessPermission('brand_id');
    	
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM ln_ins_prolocation where pro_id=$pro_id AND location_id=$location_id  $branch_id";
    	$qty_stock = $db->fetchRow($sql);
    	
    	$this->_name="ln_ins_prolocation";
    	if(!empty($qty_stock)){
    		$qty = $qty_stock['qty'] + $qty_order;
    		$array = array(
    				'qty'=>$qty,
    				);
    		$where = " id = ".$qty_stock['id'];
    		$this->update($array, $where);
    	}elseif(empty($qty_stock)){
    		$this->_name="ln_ins_prolocation";
    		$_arrs = array(
    				'pro_id'=>$pro_id,
    				'location_id'=>$location_id,
    				'qty'=>$qty_order,
    		);
    		$this->insert($_arrs);
    	}else {}
}
    public function addPurchase($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
	    		$_arr = array(
    				'sup_name'		=> $_data['supplier_name'],
    				'sup_id'	    => $_data['purchase_no'],
    				'sex'			=> $_data['sex'],
    				'age'			=> $_data['age'],
    				'occupation'	=> $_data['occupation'],
    				'tel'			=> $_data['phone'],
    				'email'			=> $_data['email'],
    				'address'		=> $_data['address'],
    				'date'			=> date("Y-m-d"),
    				'user_id'		=> $this->getUserId()
	    		);
	    		if(!empty($_data['is_new_cu'])){
	    			$sup_id=$_data['sup_id'];
	    			$where=" id =".$_data['sup_id'];
	    			$this->update($_arr, $where);
	    		}else{
	    			$sup_id = $this->insert($_arr);
	    		}
	    		
	    		$_arrBuyer = array(
	    				'buyer_name'		=> $_data['buyerName'],
	    				'buyer_id'	    => $_data['purchase_no'],
	    				'occupation'		=> $_data['buyOccupation'],
	    				'address'		=> $_data['buyAddress'],
	    				'date'			=> date("Y-m-d"),
	    				'user_id'		=> $this->getUserId()
	    		);
	    		$this->_name='ln_ins_buyer';
	    		if(!empty($_data['is_new_buy'])){
	    			$buyer_id=$_data['buyer_id'];
	    			$where=" id =".$_data['buyer_id'];
	    			$this->update($_arrBuyer, $where);
	    		}else{
	    			$buyer_id = $this->insert($_arrBuyer);
	    		}
	    		
	    		$this->_name='ln_ins_purchase';
	    		$_arr = array(
	    				'buyer_id'		=> $buyer_id,
	    				'supplier_id'	=> $sup_id,
	    				'invoice_no'	=> $_data['purchase_no'],
	    				'total_amount'	=> $_data['price'],
	    				'branch_id'		=> $_data['branch'],
	    				'date'			=> $_data['purchase_date'],
	    				'user_id'		=> $this->getUserId(),
	    				'type'			=>2
	    		);
	    		$sup_proid=$this->insert($_arr);
	    		
	    		$this->_name='ln_ins_purchase_detail';
	    		$_arr = array(
	    				'po_id'=>$sup_proid,
	    				'pro_id'=>$_data['pro_id'],
	    				'qty'	=>1,
	    				'cost'	=>$_data['price'],
	    				'amount'=>$_data['price'],
	    				'note'	=>$_data['note'],
	    				'frame'	=>$_data['frame'],
	    				'engine'=>$_data['engine'],
	    				'color'	=>$_data['color'],
	    				'price'=>$_data['price'],
	    				'frame_no'	=>$_data['frame_no'],
	    		);
	    		$this->insert($_arr);
	    		//udate ថ្លៃដើម
	    		$this->updateProductCost($_data['pro_id'],$_data['branch'],1,$_data['price']);
	    		//udate ចំនួនថ្មី
	    		$this->updateStock($_data['pro_id'],$_data['branch'],1);
	    		
// 	    		$ids = explode(',', $_data['identity']);
// 	    		foreach ($ids as $i){
// 	    			$this->_name='ln_ins_purchase_detail';
// 	    				$_arr = array(
// 	    						'po_id'=>$sup_proid,
// 	    						'pro_id'=>$_data['product_name_'.$i],
// 	    						'qty'	=>$_data['qty_'.$i],
// 	    						'cost'	=>$_data['cost_'.$i],
// 	    						'amount'=>$_data['amount_'.$i],
// 	    						'note'	=>$_data['note_'.$i],
// 	    				);
// 	    				$this->insert($_arr);
// 	    				//udate ថ្លៃដើម
// 	    				$this->updateProductCost($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i],$_data['amount_'.$i]);
	    				
// 	    				//udate ចំនួនថ្មី
// 	    				$this->updateStock($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i]);
// 	    		}
    			$db->commit();
		   	}catch (Exception $e){
		   		$db->rollBack();
		   		echo $e->getMessage();
		   		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
		   		exit();
		   	}
    }
    public function updatePurchase($_data){
    	$db = $this->getAdapter();
    	$db->beginTransaction();
    	try{
    
    		$_arr = array(
    				'sup_name'		=> $_data['supplier_name'],
    				'sup_id'	    => $_data['purchase_no'],
    				'sex'			=> $_data['sex'],
    				'age'			=> $_data['age'],
    				'occupation'	=> $_data['occupation'],
    				'tel'			=> $_data['phone'],
    				'email'			=> $_data['email'],
    				'address'		=> $_data['address'],
    				'date'			=> date("Y-m-d"),
    				'user_id'		=> $this->getUserId()
    		);
    		if(!empty($_data['is_new_cu'])){
    			$sup_id=$_data['sup_id'];
    			$where=" id =".$_data['sup_id'];
    			$this->update($_arr, $where);
    		}else{
    			$sup_id = $this->insert($_arr);
    		}
    		
    		$_arrBuyer = array(
    				'buyer_name'		=> $_data['buyerName'],
    				'buyer_id'	    => $_data['purchase_no'],
    				'occupation'		=> $_data['buyOccupation'],
    				'address'		=> $_data['buyAddress'],
    				'date'			=> date("Y-m-d"),
    				'user_id'		=> $this->getUserId()
    		);
    		$this->_name='ln_ins_buyer';
    		if(!empty($_data['is_new_buy'])){
    			$buyer_id=$_data['buyer_id'];
    			$where=" id =".$_data['buyer_id'];
    			$this->update($_arrBuyer, $where);
    		}else{
    			$buyer_id = $this->insert($_arrBuyer);
    		}
    		
    		$this->_name='ln_ins_purchase';
    		$_arr = array(
    				'buyer_id'		=> $buyer_id,
    				'supplier_id'	=> $sup_id,
    				'invoice_no'	=> $_data['purchase_no'],
    				'total_amount'	=> $_data['price'],
    				'branch_id'		=> $_data['branch'],
    				'date'			=> $_data['purchase_date'],
    				'user_id'		=> $this->getUserId(),
    				'type'			=>2
    		);
    		$where = "id=".$_data['id'];
    		$this->update($_arr, $where);
    		$sup_proid=$_data['id'];
    		$oldDetail = $this->getPurchaseDetailByID($_data['id']);
    		if (!empty($oldDetail)) foreach ($oldDetail as $ss){
    			//udate ថ្លៃដើម
    			$this->updateProductCost($ss['pro_id'],$_data['branch'],-$ss['qty'],-$ss['amount']);
    			//udate ចំនួនថ្មី
    			$this->updateStock($ss['pro_id'],$_data['branch'],-$ss['qty']);
    		}
    		
    		$this->_name='ln_ins_purchase_detail';
    		if (!empty($_data['detailId'])){
    			
    			$_arr = array(
    					'po_id'=>$sup_proid,
    					'pro_id'=>$_data['pro_id'],
    					'qty'	=>1,
    					'cost'	=>$_data['price'],
    					'amount'=>$_data['price'],
    					'note'	=>$_data['note'],
    					'frame'	=>$_data['frame'],
    					'engine'=>$_data['engine'],
    					'color'	=>$_data['color'],
    					'price'=>$_data['price'],
    					'frame_no'	=>$_data['frame_no'],
    			);
    			$where = " id = ".$_data['detailId']." AND po_id = $sup_proid";
    			$this->_name='ln_ins_purchase_detail';
    			$this->update($_arr, $where);
    			//udate ថ្លៃដើម
    			$this->updateProductCost($_data['pro_id'],$_data['branch'],1,$_data['price']);
    			//udate ចំនួនថ្មី
    			$this->updateStock($_data['pro_id'],$_data['branch'],1);
    		}
//     		$ids = explode(',', $_data['identity']);
//     		$iddetail='';
//     		foreach ($ids as $i){
//     			if (empty($iddetail)){
//     				$iddetail = $_data['iddetail'.$i];
//     			}else{
//     				if (!empty($_data['iddetail'.$i])){
//     					$iddetail = $iddetail.",".$_data['iddetail'.$i];
//     				}
//     			}
//     		}
    
//     		$this->_name ='ln_ins_purchase_detail';
//     		$where1=" po_id=".$_data['id'];
//     		if (!empty($iddetail)){
//     			$where1.=" AND id NOT IN (".$iddetail.")";
//     		}
//     		$this->delete($where1);
//     		foreach ($ids as $i){
//     			$this->_name='ln_ins_purchase_detail';
//     			if (!empty($_data['iddetail'.$i])){
//     				$_arr = array(
//     						'po_id'=>$sup_proid,
//     						'pro_id'=>$_data['product_name_'.$i],
//     						'qty'	=>$_data['qty_'.$i],
//     						'cost'	=>$_data['cost_'.$i],
//     						'amount'=>$_data['amount_'.$i],
//     						'note'	=>$_data['note_'.$i],
//     				);
//     				$wheredetail=" po_id=".$_data['id']." AND id=".$_data['iddetail'.$i];
//     				$this->update($_arr,$wheredetail);
//     			}else{
//     				$_arr = array(
//     						'po_id'=>$sup_proid,
//     						'pro_id'=>$_data['product_name_'.$i],
//     						'qty'	=>$_data['qty_'.$i],
//     						'cost'	=>$_data['cost_'.$i],
//     						'amount'=>$_data['amount_'.$i],
//     						'note'	=>$_data['note_'.$i],
//     				);
//     				$this->insert($_arr);
//     			}
//     			//udate ថ្លៃដើម
//     			$this->updateProductCost($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i],$_data['amount_'.$i]);
    
//     			//udate ចំនួនថ្មី
//     			$this->updateStock($_data['product_name_'.$i],$_data['branch'],$_data['qty_'.$i]);
//     		}
    
    		$db->commit();
    	}catch (Exception $e){
    		Application_Model_DbTable_DbUserLog::writeMessageError($e->getMessage());
    		$db->rollBack();
    		echo $e->getMessage();
    		exit();
    	}
    }
    function updateProductCost($pro_id,$branch,$qty,$total_amount_purchase){
    	$db = $this->getAdapter();
    	$sql="SELECT 
    				p.id,
    				p.cost_price,
    				pl.qty
    			FROM
    				ln_ins_product AS p,
    				ln_ins_prolocation AS pl
    			WHERE 
    				p.id = pl.pro_id
    				AND p.status = 1
    				and p.id = $pro_id
    				and pl.location_id = $branch
    		";
    	$result = $db->fetchRow($sql);
    	
    	if(!empty($result)){
    		$total_amount_in_stock = $result['qty'] * $result['cost_price'];
    		$total_qty_sum = $result['qty'] + $qty;
    		
    		$last_cost = ($total_amount_in_stock + $total_amount_purchase)/$total_qty_sum;
			
    		$array = array(
    				"cost_price"=>$last_cost,
    				);
    		
    		$this->_name = "ln_ins_product";
    		$where = " id = ".$result['id'];
			$this->update($array, $where);
    	}
    }
    
    
    function updateStockBack($id){
    	//echo $id;exit();
    	$db = $this->getAdapter();
    	$sql = "SELECT 
				  sp.branch_id,
				  spd.id,
				  spd.pro_id,
				  spd.qty 
				FROM
				  ln_ins_purchase AS sp,
				  ln_ins_purchase_detail AS spd 
				WHERE 
				  sp.id = spd.supproduct_id 
				  AND sp.id = $id  ";
    	$result = $db->fetchAll($sql);
    	//print_r($result);
    	
    	if(!empty($result)){
    		foreach ($result as $row){
				$sql1 = "select id,pro_qty from ln_ins_prolocation where pro_id =".$row['pro_id']." and brand_id = ".$row['branch_id'] ;
    			//echo $sql1;exit();
				$result1 = $db->fetchRow($sql1);
				
				//print_r($result1);exit();
				
				$qty = $result1['pro_qty'] - $row['qty']; 
				
				//echo $qty;exit();
				
				$this->_name = "ln_ins_prolocation";
				$array = array(
						'pro_qty'=> $qty,
						);
				$where=" id = ".$result1['id'] ;				
				$this->update($array, $where);
				
				//print_r($result1);exit();
    		}
    	}
    }
    
//  	function updateProduct($_data,$id){
//     	//print_r($_data);exit();
//     	$db = $this->getAdapter();
//     	$db->beginTransaction();
//     	try{ 
//     		$this->updateStockBack($id);
    		
//     		$this->_name = "ln_ins_purchase";
// 	    		$_arr = array(
// 	    				'sup_name'		=>$_data['supplier_name'],
// 	    				'purchase_no'	=>$_data['purchase_no'],
// 	    				//'sup_old_new'	=>$_data['category_id'],
// 	    				'sex'			=>$_data['sex'],
// 	    				'tel'			=>$_data['phone'],
// 	    				'email'			=>$_data['email'],
// 	    				'address'		=>$_data['address'],
// 	    				'amount_due'	=>$_data['amount_due'],
// 	    				'status'		=>$_data['status'],
// 	    				'date'			=>date("Y-m-d"),
// 	    				'user_id'		=>$this->getUserId()
// 	    				);
	    		 
// 	    			$sup_id=$_data['sup_id'];
// 	    			$where=" id =".$_data['sup_id'];
// 	    			$this->update($_arr, $where);
	    		
// 	    		$this->_name='ln_ins_purchase';
// 	    		$_arr = array(
// 	    				'sup_id'		=>$sup_id,
// 	    				'supplier_no'	=>$_data['purchase_no'],
// 	    				'amount_due'	=>$_data['amount_due'],
// 	    				'branch_id'		=>$_data['branch_id'],
// 	    				'date'			=>date("Y-m-d"),
// 	    				'status'		=>$_data['status'],
// 	    				'user_id'		=>$this->getUserId()
// 	    		);
// 	    		$where=" id =".$_data['id'];
// 	    		$this->update($_arr, $where);
	    		
// 	    		$this->_name='ln_ins_purchase_detail';
// 	    		$where=" supproduct_id =".$_data['id'];
// 	    		$this->delete($where);
// 	    		$ids = explode(',', $_data['identity']);
// 	    		foreach ($ids as $i){
// 	    			$this->_name='ln_ins_purchase_detail';
// 	    				$_arr = array(
// 	    						'supproduct_id'	=>$_data['id'],
// 	    						'pro_id'		=>$_data['product_name_'.$i],
// 	    						'qty'			=>$_data['qty_'.$i],
// 	    						'cost'			=>$_data['cost_'.$i],
// 	    						'date'			=>date("Y-m-d"),
// 	    						'amount'		=>$_data['amount_'.$i],
// 	    						'note'			=>$_data['note_'.$i],
// 	    				);
// 	    				$this->insert($_arr);
	    				
// 	    			$this->updateStock($_data['product_name_'.$i],$_data['branch_id'],$_data['qty_'.$i]);
	    				
// 	    		}
//     			$db->commit();
// 		   	}catch (Exception $e){
// 		   		echo $e->getMessage();
// 		   		$db->rollBack();
// 		   	}
//     }
    function getProductNames(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.brand_id,p.pro_name AS `name` FROM ln_ins_product AS p,ln_ins_prolocation AS pl
 				WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
        $rows=$db->fetchAll($sql);
        
        array_unshift($rows,array('id' => '',"name"=>"Please select product name"));
        $options = '';
        if(!empty($rows))foreach($rows as $value){
        	$options .= '<option value="'.$value['id'].'" >'.htmlspecialchars($value['name'], ENT_QUOTES).'</option>';
        }
        return $options;
    }
    
    function getProductName(){
    	$db=$this->getAdapter();
    	$sql="SELECT p.id,pl.location_id,p.item_name AS `name` FROM ln_ins_product AS p,
    	ln_ins_prolocation AS pl
    	WHERE p.id=pl.pro_id AND p.status=1  ";
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	$sql.=$dbp->getAccessPermission('brand_id');
    	$sql.=" GROUP BY p.id ORDER BY id DESC ";
    	return $db->fetchAll($sql);
    }
    function getPurchaseCode(){
    	$db = $this->getAdapter();
    	$sql="SELECT id FROM ln_ins_purchase WHERE STATUS=1 ORDER BY id DESC LIMIT 1";
    	$acc_no = $db->fetchOne($sql);
    	$new_acc_no= (int)$acc_no+1;
    	$acc_no= strlen((int)$acc_no+1);
    	$pre='PU-';
    	for($i = $acc_no;$i<4;$i++){
    		$pre.='0';
    	}
    	return $pre.$new_acc_no;
    }
    function getSuplierName(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,sup_name FROM ln_ins_supplier WHERE STATUS=1 ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function getBuyerName(){
    	$db=$this->getAdapter();
    	$sql="SELECT id,buyer_name FROM ln_ins_buyer WHERE STATUS=1 ORDER BY id DESC";
    	return $db->fetchAll($sql);
    }
    function getSuplierInfo($id){
    	$db=$this->getAdapter();
    	$sql="SELECT *
				FROM ln_ins_supplier
    		 WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getBuyerInfo($id){
    	$db=$this->getAdapter();
    	$sql="SELECT *
    	FROM ln_ins_buyer
    	WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getProductById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT * FROM ln_ins_product WHERE id=$id";
    	return $db->fetchRow($sql);
    }
    function getSupplierById($id){
    	$db=$this->getAdapter();
    	$sql="SELECT s.id,s.sup_name,s.purchase_no,s.sex,s.tel,s.email,s.address,sp.amount_due,sp.branch_id
		       FROM ln_ins_purchase AS s,ln_ins_purchase AS sp
		       WHERE s.id=sp.sup_id AND sp.id=$id";
    	return $db->fetchRow($sql);
    }
    function getSupplierProducts($id){
    	$db=$this->getAdapter();
    	$sql="SELECT id,supproduct_id,pro_id,qty,cost,amount,note,status,
		(SELECT p.pro_name FROM ln_ins_product AS p WHERE p.id=pro_id LIMIT 1) AS pro_name
    	FROM ln_ins_purchase_detail WHERE supproduct_id=$id";
    	return $db->fetchAll($sql);
    }
    function getAllBranch(){
    	$db = $this->getAdapter();
//     	$sql="select br_id as id, CONCAT(branch_nameen) as name from rms_branch where status=1 ";
    	
    	$dbp = new Application_Model_DbTable_DbGlobal();
    	return $dbp->getAllBranchName();
//     	$sql.=$dbp->getAccessPermission('br_id');
//     	return $db->fetchAll($sql);
    }
    
    public function ajaxAddProduct($data){
    	$db = $this->getAdapter();
    	$session_user=new Zend_Session_Namespace('authstu');
    	$userName=$session_user->user_name;
    	$GetUserId= $session_user->user_id;
    	$_arr = array(
    			'pro_name'	=>$data['product_name'],
    			'pro_code'	=>$data['product_code'],
    			'cat_id'	=>$data['category_id'],
    			'pro_price'	=>$data['pro_price'],
    			'cost'		=>$data['cost'],
    			'pro_des'	=>$data['descript'],
    			'pro_type'	=>$data['pro_type'],
    			'status'	=>$data['p_status'],
    			'date'		=>date("Y-m-d"),
    			'user_id'	=>$this->getUserId()
    	);
    	$this->_name = "ln_ins_product";
    	$pro_id = $this->insert($_arr);
    	
    	$_arr = array(
    			'pro_id'=>$pro_id,
    			'brand_id'=>$data['location_id'],
    			'pro_qty'=>0,
    			'total_amount'=>0,
    			'note'=>'',
    	);
    	$this->_name='ln_ins_prolocation';
    	$this->insert($_arr);
    	
    	$array = array(
    			'ser_cate_id'	=>$pro_id,
    			'title'			=>$data['product_name'],
    			'description'	=>$data['descript'],
    			'price'			=>$data['pro_price'],
    			'cost'			=>$data['cost'],
    			'status'		=>1,
    			'create_date'	=>date("Y-m-d H:i:s"),
    			'user_id'		=>$this->getUserId(),
    			'type'			=>1, // type=1 => product
    			'pro_type'		=>$data['pro_type'], // 1=cut stock , 2=cut stock later
    	);
    	$this->_name='rms_program_name';
    	$this->insert($array);
    	return $pro_id;
    }
    function getPurchaseByID($purchaseID){
    	$db = $this->getAdapter();
    	$sql="SELECT * FROM ln_ins_purchase WHERE id=$purchaseID";
    	return $db->fetchRow($sql);
    }
    function getPurchaseDetailByID($purchaseID){
    	$db = $this->getAdapter();
    	$sql="SELECT pd.*,
		(SELECT p.item_name FROM `ln_ins_product` AS p WHERE p.id = pd.`pro_id` LIMIT 1) AS item_name
    	FROM `ln_ins_purchase_detail` AS pd WHERE pd.`po_id` =$purchaseID";
    	return $db->fetchAll($sql);
    }
    
}